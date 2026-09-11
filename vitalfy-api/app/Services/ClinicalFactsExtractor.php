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

        try {
            $raw = $this->documentService->llmResponseByTemplate(
                $conversation,
                $userTemplate,
                forceJsonFormat: true,
                reasoningEffort: 'medium',
                systemInstructions: config('prompts.clinical_facts_system'),
                temperature: 0.0
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

        Log::info('document.facts.extraction', [
            'template_id' => $templateId,
            'facts_raw_items' => $this->countRawItems($decoded),
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

    private function countRawItems(array $decoded): int
    {
        $count = 0;

        foreach ($decoded['sections'] ?? [] as $section) {
            $count += count($section['items'] ?? []);
        }

        return $count;
    }
}
