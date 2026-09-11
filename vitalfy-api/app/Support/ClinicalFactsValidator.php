<?php

namespace App\Support;

/**
 * BE-R23-05 (ai-vitalfy/action-plans/backend/R23.md) — a tarefa que sustenta
 * o plano inteiro: um fato sem evidência literal na transcrição não
 * sobrevive, sem perguntar nada a nenhum modelo. Contrato completo em
 * ai-vitalfy/action-plans/shared/R23.md#sh-r23-01, decisões 2 e 3.
 *
 * Lógica pura, sem IO — mesmo critério de PlanLimits, AudioLimits e
 * FeatureFlags. Não conhece Groq, HTTP nem Eloquent; document_templates.sections
 * chega como argumento, nunca lido do banco por esta classe.
 */
class ClinicalFactsValidator
{
    private const SCHEMA_VERSION = 'clinical-facts/1';

    /**
     * Decisão 5 de SH-R23-01: "mais de 40% dos itens extraídos descartados"
     * por âncora invalida o payload inteiro. Provisório — SH-R23-01 subtarefa
     * 1 confirma ou corrige este número contra a linha de base real de
     * BE-R23-02, que depende de documentos de produção ainda não existentes
     * neste checkout.
     */
    private const ANCHOR_DROP_THRESHOLD = 0.40;

    private const MAX_ITEMS_PER_SECTION = 40;
    private const MAX_TEXT_LENGTH = 300;
    private const MAX_EVIDENCE_LENGTH = 600;
    private const NULL_TEXT_STATUS = 'mencionado_sem_especificacao';
    private const DEFAULT_STATUS_ENUM = ['relatado'];

    /**
     * @param array $facts JSON decodificado de ClinicalFactsExtractor::extract(), sem validação prévia
     * @param array $declaredSections document_templates.sections do template pedido
     * @param string $transcriptText transcrição já mesclada (mergeContextChunks), não normalizada
     */
    public function validate(array $facts, int $templateId, array $declaredSections, string $transcriptText): ClinicalFactsValidationResult
    {
        $stats = [
            'items_in' => 0,
            'items_kept' => 0,
            'items_dropped_anchor' => 0,
            'items_dropped_schema' => 0,
            'items_dropped_unknown_section' => 0,
            'items_truncated' => 0,
        ];

        // Condição 2 (decisão 3): schema_version ausente ou desconhecida.
        if (($facts['schema_version'] ?? null) !== self::SCHEMA_VERSION) {
            return ClinicalFactsValidationResult::invalid('schema_version ausente ou desconhecida', $stats);
        }

        // Condição 3: template_id ausente ou diferente do template pedido.
        if (($facts['template_id'] ?? null) !== $templateId) {
            return ClinicalFactsValidationResult::invalid('template_id ausente ou diferente do template pedido', $stats);
        }

        // Condição 4: sections ausente, não sendo array, ou nenhuma key
        // existindo em document_templates.sections.
        if (!isset($facts['sections']) || !is_array($facts['sections'])) {
            return ClinicalFactsValidationResult::invalid('sections ausente ou não é array', $stats);
        }

        $declaredByKey = [];
        foreach ($declaredSections as $section) {
            $declaredByKey[$section['key']] = $section;
        }

        if (empty($declaredByKey)) {
            return ClinicalFactsValidationResult::invalid('template sem seções declaradas', $stats);
        }

        $normalizedTranscript = $this->normalize($transcriptText);

        // Toda seção declarada existe na saída, vazia quando for o caso —
        // ausente e vazio precisam ser a mesma coisa para quem consome
        // (decisão 2).
        $keptFactsBySection = array_fill_keys(array_keys($declaredByKey), []);
        $anyDeclaredKeyMatched = false;

        foreach ($facts['sections'] as $sectionPayload) {
            $key = is_array($sectionPayload) ? ($sectionPayload['key'] ?? null) : null;
            $items = is_array($sectionPayload) ? ($sectionPayload['items'] ?? []) : [];
            $items = is_array($items) ? $items : [];

            if ($key === null || !array_key_exists($key, $declaredByKey)) {
                $stats['items_in'] += count($items);
                $stats['items_dropped_unknown_section'] += count($items);
                continue;
            }

            $anyDeclaredKeyMatched = true;
            $allowedStatuses = $declaredByKey[$key]['status_enum'] ?? self::DEFAULT_STATUS_ENUM;
            $keptForSection = 0;

            foreach ($items as $item) {
                $stats['items_in']++;

                if ($keptForSection >= self::MAX_ITEMS_PER_SECTION) {
                    $stats['items_truncated']++;
                    continue;
                }

                $validated = $this->validateItem($item, $allowedStatuses, $normalizedTranscript, $stats);

                if ($validated === null) {
                    continue;
                }

                $keptFactsBySection[$key][] = $validated;
                $keptForSection++;
                $stats['items_kept']++;
            }
        }

        if (!$anyDeclaredKeyMatched) {
            return ClinicalFactsValidationResult::invalid(
                'nenhuma key de sections existe em document_templates.sections',
                $stats
            );
        }

        // Condição 5: taxa de descarte por âncora acima do limiar da decisão 5.
        $anchorDropRate = $stats['items_in'] > 0
            ? $stats['items_dropped_anchor'] / $stats['items_in']
            : 0.0;

        if ($anchorDropRate > self::ANCHOR_DROP_THRESHOLD) {
            return ClinicalFactsValidationResult::invalid(
                sprintf(
                    'taxa de descarte por âncora %.0f%% acima do limiar de %.0f%%',
                    $anchorDropRate * 100,
                    self::ANCHOR_DROP_THRESHOLD * 100
                ),
                $stats
            );
        }

        // Condição 6: zero fatos válidos ao fim, com transcrição não vazia.
        if ($stats['items_kept'] === 0 && trim($transcriptText) !== '') {
            return ClinicalFactsValidationResult::invalid('zero fatos válidos com transcrição não vazia', $stats);
        }

        $title = $this->validateTitle($facts['title'] ?? null, $keptFactsBySection);

        $sectionsOut = [];
        foreach ($keptFactsBySection as $key => $items) {
            $sectionsOut[] = ['key' => $key, 'items' => $items];
        }

        return ClinicalFactsValidationResult::valid([
            'schema_version' => self::SCHEMA_VERSION,
            'template_id' => $templateId,
            'title' => $title,
            'sections' => $sectionsOut,
        ], $stats);
    }

    /**
     * mb_strtolower, remoção de acentos (iconv//TRANSLIT — sem dependência
     * de intl), colapso de espaço/quebra em espaço único, remoção de
     * pontuação de borda. Nada além — quanto mais esperta a normalização,
     * mais fácil um falso positivo passar (SH-R23-01, decisão 3).
     */
    public function normalize(string $text): string
    {
        $text = mb_strtolower($text);

        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($transliterated !== false) {
            $text = $transliterated;
        }

        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text, " \t\n\r\0\x0B.,;:!?\"'“”‘’()[]{}-");

        return trim($text);
    }

    private function validateItem(mixed $item, array $allowedStatuses, string $normalizedTranscript, array &$stats): ?array
    {
        if (!is_array($item)) {
            $stats['items_dropped_schema']++;
            return null;
        }

        $text = $item['text'] ?? null;
        $status = $item['status'] ?? null;
        $evidence = $item['evidence'] ?? null;
        $speaker = $item['speaker'] ?? null;
        $code = $item['code'] ?? null;

        if (!is_string($status) || !in_array($status, $allowedStatuses, true)) {
            $stats['items_dropped_schema']++;
            return null;
        }

        if ($text !== null && !is_string($text)) {
            $stats['items_dropped_schema']++;
            return null;
        }

        // text: null só é permitido com status mencionado_sem_especificacao,
        // e a implicação vale nos dois sentidos (decisão 2): esse status
        // existe justamente para o modelo não ter onde escrever o nome do
        // exame/medicação. Se ele preencher text mesmo assim, é exatamente
        // a invenção que o status deveria impedir — descartado, não
        // silenciosamente aceito com o nome vazando.
        if ($text === null && $status !== self::NULL_TEXT_STATUS) {
            $stats['items_dropped_schema']++;
            return null;
        }

        if ($text !== null && $status === self::NULL_TEXT_STATUS) {
            $stats['items_dropped_schema']++;
            return null;
        }

        if (!is_string($evidence) || trim($evidence) === '') {
            $stats['items_dropped_schema']++;
            return null;
        }

        $normalizedEvidence = $this->normalize($evidence);

        if ($normalizedEvidence === '' || !str_contains($normalizedTranscript, $normalizedEvidence)) {
            $stats['items_dropped_anchor']++;
            return null;
        }

        $validated = [
            'text' => $text !== null ? mb_substr($text, 0, self::MAX_TEXT_LENGTH) : null,
            'status' => $status,
            'speaker' => is_int($speaker) ? $speaker : null,
            'evidence' => mb_substr($evidence, 0, self::MAX_EVIDENCE_LENGTH),
        ];

        // code (decisão 6, exceção do CID): só passa quando presente e
        // string — a seção render=cid é o único lugar em que isto importa,
        // e é o montador (BE-R23-07) que decide o que fazer com ele.
        if (is_string($code) && $code !== '') {
            $validated['code'] = $code;
        }

        return $validated;
    }

    /**
     * title.source_key precisa apontar para uma seção que sobreviveu com
     * pelo menos um item; senão o título é descartado e BE-R23-07 usa
     * document_templates.name como fallback (decisão 2).
     */
    private function validateTitle(mixed $title, array $keptFactsBySection): ?array
    {
        if (!is_array($title)) {
            return null;
        }

        $text = $title['text'] ?? null;
        $sourceKey = $title['source_key'] ?? null;

        if (!is_string($text) || trim($text) === '' || !is_string($sourceKey)) {
            return null;
        }

        if (empty($keptFactsBySection[$sourceKey] ?? [])) {
            return null;
        }

        return [
            'text' => mb_substr($text, 0, self::MAX_TEXT_LENGTH),
            'source_key' => $sourceKey,
        ];
    }
}
