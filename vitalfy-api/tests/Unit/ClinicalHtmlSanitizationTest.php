<?php

namespace Tests\Unit;

use App\Services\DocumentService;
use PHPUnit\Framework\TestCase;

/**
 * BE-R10-03 (ai-vitalfy/risks.md#r10): sanitizeClinicalHtml() é a única
 * defesa contra o caminho de PDF (Browsershot), que nunca passa pelo
 * nginx-proxy — CSP e headers HTTP (BE-R10-01/BE-R10-02) não o alcançam.
 */
class ClinicalHtmlSanitizationTest extends TestCase
{
    public function test_script_tag_e_removido(): void
    {
        $service = new DocumentService();

        $result = $service->sanitizeClinicalHtml('<script>alert(1)</script><p>texto legítimo</p>');

        $this->assertStringNotContainsString('<script', $result);
        $this->assertStringContainsString('<p>texto legítimo</p>', $result);
    }

    public function test_atributo_onerror_e_removido_junto_com_a_tag_nao_permitida(): void
    {
        $service = new DocumentService();

        $result = $service->sanitizeClinicalHtml('<img src=x onerror=alert(2)><p>ok</p>');

        $this->assertStringNotContainsString('onerror', $result);
        $this->assertStringNotContainsString('<img', $result);
        $this->assertStringContainsString('<p>ok</p>', $result);
    }

    public function test_link_javascript_e_removido(): void
    {
        $service = new DocumentService();

        $result = $service->sanitizeClinicalHtml('<a href="javascript:alert(3)">clique</a>');

        $this->assertStringNotContainsString('javascript:', $result);
        $this->assertStringNotContainsString('<a ', $result);
        $this->assertStringContainsString('clique', $result);
    }

    public function test_tags_do_allowlist_sobrevivem_intactas(): void
    {
        $service = new DocumentService();

        $document = '<h2><strong>Anamnese</strong></h2><p>Paciente relata dor de cabeça.</p>'
            . '<h3><strong>Conduta</strong></h3><ul><li>Repouso</li><li>Hidratação</li></ul>';

        $result = $service->sanitizeClinicalHtml($document);

        $this->assertSame($document, $result);
    }
}
