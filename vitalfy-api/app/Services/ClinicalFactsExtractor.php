<?php

namespace App\Services;

use App\Exceptions\ClinicalFactsExtractionException;
use Illuminate\Support\Facades\Log;

/**
 * BE-R23-04 (ai-vitalfy/action-plans/backend/R23.md): uma transcrição vira
 * o JSON de fatos de SH-R23-01, organizado nas seções que o template
 * declara — sem gerar documento nenhum. Não valida o resultado: validação
 * é BE-R23-05, e separar as duas é o que torna as duas testáveis.
 */
class ClinicalFactsExtractor
{
    /**
     * Teto explícito de tokens de saída. Sem ele o Groq aplica o default de
     * 2048, que o gpt-oss-20b consome inteiro no canal de raciocínio antes
     * de emitir um caractere de JSON — ver o comentário em
     * DocumentService::buildTemplatePayload(). 8192 cobre com folga o pior
     * caso medido (3.464 tokens de completion no template de 10 seções).
     */
    private const MAX_COMPLETION_TOKENS = 8192;

    public function __construct(private DocumentService $documentService)
    {
    }

    /**
     * @param array $conversation transcripts.conversation, no formato que organizeUtterances() produz
     * @param array $sections document_templates.sections do template pedido (decisão 1 de SH-R23-01)
     * @return array o JSON decodificado, sem validação
     *
     * @throws ClinicalFactsExtractionException erro de rede, timeout ou JSON indecodificável
     */
    public function extract(array $conversation, int $templateId, array $sections): array
    {
        $userTemplate = str_replace(
            ['{sections}', '{template_id}'],
            [$this->serializeSections($sections), (string) $templateId],
            config('prompts.clinical_facts_user')
        );

        $usage = null;

        try {
            $raw = $this->documentService->llmResponseByTemplate(
                $conversation,
                $userTemplate,
                forceJsonFormat: true,
                // Medido em 13/09/2026, depois que o transporte direto
                // passou a de fato entregar este parâmetro: `medium` gasta
                // ~2.200 tokens de raciocínio contra ~750 de `low`, sem
                // diferença observável na extração — as mesmas seções, os
                // mesmos fatos, a mesma aderência ao schema. A tarefa é
                // copiar e classificar o que foi dito, não deliberar.
                reasoningEffort: 'low',
                systemInstructions: config('prompts.clinical_facts_system'),
                temperature: 0.0,
                maxCompletionTokens: self::MAX_COMPLETION_TOKENS,
                jsonSchema: $this->buildResponseSchema($sections),
                usage: $usage
            );
        } catch (\Throwable $e) {
            throw new ClinicalFactsExtractionException(
                'Falha ao chamar o Groq na extração factual: ' . $e->getMessage(),
                previous: $e
            );
        }

        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            throw new ClinicalFactsExtractionException('JSON de fatos indecodificável.');
        }

        $decoded = $this->sectionsMapToList($decoded);

        // cached_tokens é o único sinal de que o caching automático do Groq
        // pegou o prefixo estável (system + lista de seções). Vem null
        // enquanto a conta não tiver caching ativo — foi o caso na medição
        // de 13/09/2026, em que prompt_tokens_details nem aparecia na
        // resposta. Ver ai-vitalfy/CAPACITY.md.
        Log::info('document.facts.extraction', [
            'template_id' => $templateId,
            'facts_raw_items' => $this->countRawItems($decoded),
            'prompt_tokens' => $usage['prompt_tokens'] ?? null,
            'cached_tokens' => $usage['prompt_tokens_details']['cached_tokens'] ?? null,
            'completion_tokens' => $usage['completion_tokens'] ?? null,
            'reasoning_tokens' => $usage['completion_tokens_details']['reasoning_tokens'] ?? null,
        ]);

        return $decoded;
    }

    /**
     * Serialização determinística das seções, na ordem do array — dois
     * documentos do mesmo template precisam produzir o mesmo prompt.
     * Público pelo mesmo motivo de DocumentService::buildTemplatePayload():
     * é o ponto de teste puro (BE-R23-09, ClinicalFactsPromptTest), sem
     * tocar config() nem rede.
     */
    public function serializeSections(array $sections): string
    {
        $lines = [];

        foreach ($sections as $section) {
            $lines[] = "- key: {$section['key']}";
            $lines[] = "  label: {$section['label']}";

            if (($section['render'] ?? null) === 'cid') {
                $lines[] = "  render: cid (preencher também o campo \"code\" com o código CID do diagnóstico dito)";
            }

            if (!empty($section['hints'])) {
                $lines[] = '  hints: ' . implode('; ', $section['hints']);
            }

            $statusEnum = $section['status_enum'] ?? null;
            $lines[] = $statusEnum
                ? '  status_enum: ' . implode(' | ', $statusEnum)
                : '  status_enum: relatado (única opção)';
        }

        return implode("\n", $lines);
    }

    /**
     * BE-R23-04: o contrato de saída deixa de ser pedido em prosa e passa a
     * ser schema que a própria API faz cumprir (`strict: true`).
     *
     * Motivo empírico, medido contra o Groq real: com `json_object` o
     * gpt-oss-20b achata {"key": X, "items": [...]} em elementos soltos do
     * array — "historia_da_doenca_atual", ":", {"items": [...]} — que é JSON
     * sintaticamente válido e estruturalmente destruído. O validador
     * aceitava esse payload e guardava 1 de 10 itens, com
     * facts_fallback_at nulo: documento truncado sem nenhum sinal de erro.
     *
     * `sections` como OBJETO indexado pelas keys elimina a classe inteira:
     * o nome da seção vira propriedade do schema, não um campo que o modelo
     * reescreve a cada item. `additionalProperties: false` mais todas as
     * keys em `required` também matam a key inventada e a key com typo
     * (visto: "historico_da_doenca_atual" no lugar de "historia_...").
     *
     * `status` recebe a união dos status_enum declarados pelo template — a
     * checagem de qual status vale em qual seção continua sendo do
     * ClinicalFactsValidator, que é quem tem essa informação por seção.
     *
     * Público pelo mesmo motivo de serializeSections(): é o ponto de teste
     * puro, sem rede.
     */
    public function buildResponseSchema(array $sections): array
    {
        $keys = [];
        $statuses = [];

        foreach ($sections as $section) {
            $keys[] = $section['key'];

            foreach ($section['status_enum'] ?? ['relatado'] as $status) {
                $statuses[$status] = true;
            }
        }

        $item = [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['text', 'status', 'speaker', 'evidence', 'code'],
            'properties' => [
                'text' => ['type' => ['string', 'null']],
                'status' => ['type' => 'string', 'enum' => array_keys($statuses)],
                'speaker' => ['type' => ['integer', 'null']],
                'evidence' => ['type' => 'string'],
                'code' => ['type' => ['string', 'null']],
            ],
        ];

        // O objeto de fato aparece UMA vez em $defs e cada seção o
        // referencia. Repeti-lo inline nas N propriedades de seção custa
        // tokens de entrada em toda chamada: medido em 13/09/2026 no
        // template de 10 seções, 5.550 contra 2.182 caracteres de schema, e
        // 3.361 contra 2.508 tokens de prompt — 853 tokens por documento,
        // pagos por um schema que o modelo leria igual das duas formas.
        $sectionProperties = [];

        foreach ($keys as $key) {
            $sectionProperties[$key] = [
                'type' => 'array',
                'items' => ['$ref' => '#/$defs/fato'],
            ];
        }

        return [
            'name' => 'clinical_facts',
            'strict' => true,
            'schema' => [
                'type' => 'object',
                'additionalProperties' => false,
                'required' => ['schema_version', 'template_id', 'title', 'sections'],
                '$defs' => ['fato' => $item],
                'properties' => [
                    'schema_version' => ['type' => 'string', 'enum' => ['clinical-facts/1']],
                    'template_id' => ['type' => 'integer'],
                    'title' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['text', 'source_key'],
                        'properties' => [
                            'text' => ['type' => 'string'],
                            'source_key' => ['type' => 'string', 'enum' => $keys],
                        ],
                    ],
                    'sections' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => $keys,
                        'properties' => $sectionProperties,
                    ],
                ],
            ],
        ];
    }

    /**
     * Traduz o `sections` em forma de mapa (o que o schema acima obriga o
     * modelo a devolver) para a lista de {key, items} de clinical-facts/1.
     *
     * A conversão mora aqui, e não no validador, de propósito: o contrato
     * congelado em SH-R23-01 é o que fica PERSISTIDO em
     * transcripts.clinical_facts e o que ClinicalDocumentRenderer consome.
     * O formato de fio da extração é detalhe de BE-R23-04. Mantendo a
     * fronteira de extract() na forma v1, ClinicalFactsValidator,
     * ClinicalDocumentRenderer, o golden-file test e os mocks de pipeline
     * seguem intocados.
     *
     * Payload fora de forma passa adiante sem conversão — quem recusa com
     * a razão exata é o validador (condição 4), não esta função.
     *
     * Público pelo mesmo motivo de serializeSections().
     */
    public function sectionsMapToList(array $decoded): array
    {
        $sections = $decoded['sections'] ?? null;

        if (!is_array($sections) || $sections === [] || array_is_list($sections)) {
            return $decoded;
        }

        $list = [];

        foreach ($sections as $key => $items) {
            $list[] = [
                'key' => $key,
                'items' => is_array($items) ? $items : [],
            ];
        }

        $decoded['sections'] = $list;

        return $decoded;
    }

    private function countRawItems(array $decoded): int
    {
        $count = 0;

        foreach ($decoded['sections'] ?? [] as $section) {
            $count += count($section['items'] ?? []);
        }

        return $count;
    }
}
