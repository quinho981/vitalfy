<?php

namespace App\Support;

/**
 * BE-R23-07 (ai-vitalfy/action-plans/backend/R23.md): o HTML do documento é
 * função determinística dos fatos validados e de document_templates.sections
 * — nenhuma chamada a LLM. Algoritmo completo em
 * ai-vitalfy/action-plans/shared/R23.md#sh-r23-01, decisão 4.
 *
 * Lógica pura, sem IO, sem Eloquent, sem service injetado — mesmo critério
 * de PlanLimits, AudioLimits, FeatureFlags e ClinicalFactsValidator.
 * Testável em tests/Unit/ sem banco, sem rede.
 *
 * Escapa cada text/label/code com e() na montagem (defesa 1). O chamador
 * (ProcessGenerateDocumentPipeline) ainda passa o resultado por
 * DocumentService::sanitizeClinicalHtml() antes de persistir (defesa 2, R10)
 * — redundância deliberada, não removível: ver "Segurança" em
 * backend/R23.md#be-r23-07.
 */
class ClinicalDocumentRenderer
{
    private const CID_ELIGIBLE_STATUSES = ['hipotese', 'estabelecido'];
    private const NULL_TEXT_STATUS = 'mencionado_sem_especificacao';

    /**
     * @param array $facts resultado válido de ClinicalFactsValidator::validate() (->facts)
     * @param array $sections document_templates.sections do template usado
     */
    public function render(array $facts, array $sections, string $templateName): string
    {
        $itemsByKey = $this->indexItemsByKey($facts['sections'] ?? []);

        $title = $facts['title']['text'] ?? $templateName;
        $html = '<h2><strong>' . e($title) . '</strong></h2><p></p>';

        foreach ($sections as $section) {
            $items = $itemsByKey[$section['key']] ?? [];

            if (empty($items)) {
                // Seção sem fato não aparece -- nem título, nem corpo. É a
                // linha que substitui a instrução em prosa que 46/55
                // templates pedem hoje.
                continue;
            }

            $html .= $this->renderSection($section, $items);
        }

        return $html;
    }

    private function indexItemsByKey(array $factSections): array
    {
        $out = [];
        foreach ($factSections as $section) {
            $out[$section['key']] = $section['items'] ?? [];
        }
        return $out;
    }

    private function renderSection(array $section, array $items): string
    {
        $label = e($section['label'] ?? '');

        return match ($section['render'] ?? 'prose') {
            'list' => $this->renderList($label, $items, $section),
            'cid' => $this->renderCid($label, $items),
            default => $this->renderProse($label, $items, $section),
        };
    }

    private function renderProse(string $escapedLabel, array $items, array $section): string
    {
        $sentences = array_filter(array_map(
            fn (array $item) => $this->itemText($item, $section),
            $items
        ), fn (string $s) => $s !== '');

        if (empty($sentences)) {
            return '';
        }

        return "<h3><strong>{$escapedLabel}</strong></h3><p>" . implode(' ', $sentences) . '</p><p></p>';
    }

    private function renderList(string $escapedLabel, array $items, array $section): string
    {
        $listItems = array_filter(array_map(
            fn (array $item) => $this->itemText($item, $section),
            $items
        ), fn (string $s) => $s !== '');

        if (empty($listItems)) {
            return '';
        }

        $lis = implode('', array_map(fn (string $text) => "<li>{$text}</li>", $listItems));

        return "<h3><strong>{$escapedLabel}</strong></h3><ul>{$lis}</ul><p></p>";
    }

    /**
     * Só itens com status hipotese ou estabelecido chegam à lista (decisão
     * 6). status: descartado é dado clínico real (diagnóstico afastado) mas
     * nunca aparece aqui — o renderer não sabe para qual seção de prosa
     * redirecioná-lo; é orientação para o prompt de extração (BE-R23-04)
     * colocar esse fato numa seção de prosa quando o template tiver uma,
     * não algo que este montador resolve entre seções.
     */
    private function renderCid(string $escapedLabel, array $items): string
    {
        $lis = [];

        foreach ($items as $item) {
            if (!in_array($item['status'] ?? null, self::CID_ELIGIBLE_STATUSES, true)) {
                continue;
            }

            $text = $item['text'] ?? null;
            if (!is_string($text) || $text === '') {
                continue;
            }

            $code = $item['code'] ?? null;
            $prefix = is_string($code) && $code !== '' ? e($code) . ' — ' : '';

            $lis[] = '<li>' . $prefix . e($text) . '</li>';
        }

        if (empty($lis)) {
            return '';
        }

        return "<h3><strong>{$escapedLabel}</strong></h3><ul>" . implode('', $lis) . '</ul><p></p>';
    }

    /**
     * text: null com status mencionado_sem_especificacao emite a frase fixa
     * do montador, parametrizada pelo label da seção — nunca o texto que o
     * modelo não tinha onde escrever (decisão 2/4).
     */
    private function itemText(array $item, array $section): string
    {
        $text = $item['text'] ?? null;

        if ($text === null) {
            if (($item['status'] ?? null) === self::NULL_TEXT_STATUS) {
                $label = $section['label'] ?? '';
                return e("Mencionada a necessidade de {$label}, sem especificação registrada na consulta.");
            }

            return '';
        }

        if (!is_string($text) || $text === '') {
            return '';
        }

        return e($text);
    }
}
