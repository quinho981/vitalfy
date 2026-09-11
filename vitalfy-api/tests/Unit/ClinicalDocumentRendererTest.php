<?php

namespace Tests\Unit;

use App\Support\ClinicalDocumentRenderer;
use PHPUnit\Framework\TestCase;

/**
 * BE-R23-07 (ai-vitalfy/action-plans/backend/R23.md): os sete casos da
 * seção "Como validar" — determinismo, seção vazia, ordem,
 * mencionado_sem_especificacao, CID, XSS, sem rede. Nenhum toca banco ou
 * rede; "sem rede" aqui significa literalmente que a classe não tem como
 * chamar o Groq — ela não recebe nem injeta nenhum client.
 */
class ClinicalDocumentRendererTest extends TestCase
{
    private function sections(): array
    {
        return [
            ['key' => 'queixa_principal', 'label' => 'Queixa Principal', 'render' => 'prose'],
            ['key' => 'hda', 'label' => 'História da Doença Atual', 'render' => 'prose'],
            [
                'key' => 'exames_relevantes',
                'label' => 'Exames Relevantes',
                'render' => 'prose',
            ],
            [
                'key' => 'diagnostico_cid',
                'label' => 'Impressão Diagnóstica (CID)',
                'render' => 'cid',
            ],
            ['key' => 'orientacoes', 'label' => 'Orientações', 'render' => 'list'],
        ];
    }

    private function factsFor(array $sectionItems, ?array $title = null): array
    {
        $sections = [];
        foreach ($sectionItems as $key => $items) {
            $sections[] = ['key' => $key, 'items' => $items];
        }

        return [
            'schema_version' => 'clinical-facts/1',
            'template_id' => 12,
            'title' => $title,
            'sections' => $sections,
        ];
    }

    public function test_mesmos_fatos_produzem_o_mesmo_html_em_execucoes_repetidas(): void
    {
        $facts = $this->factsFor([
            'queixa_principal' => [['text' => 'Paciente refere dor torácica há três dias.', 'status' => 'relatado']],
        ], ['text' => 'Dor torácica', 'source_key' => 'queixa_principal']);

        $renderer = new ClinicalDocumentRenderer();

        $first = $renderer->render($facts, $this->sections(), 'Template Genérico');
        $second = $renderer->render($facts, $this->sections(), 'Template Genérico');

        $this->assertSame($first, $second);
    }

    public function test_secao_sem_itens_nao_emite_h3_nem_vazio_nem_com_titulo_sozinho(): void
    {
        $facts = $this->factsFor([
            'queixa_principal' => [['text' => 'Paciente refere dor torácica.', 'status' => 'relatado']],
            'hda' => [],
            'exames_relevantes' => [],
            'diagnostico_cid' => [],
            'orientacoes' => [],
        ]);

        $html = (new ClinicalDocumentRenderer())->render($facts, $this->sections(), 'Template Genérico');

        $this->assertSame(1, substr_count($html, '<h3>'));
        $this->assertStringNotContainsString('História da Doença Atual', $html);
        $this->assertStringNotContainsString('Orientações', $html);
        $this->assertStringNotContainsString('Impressão Diagnóstica', $html);
    }

    public function test_secoes_saem_na_ordem_declarada_nao_na_ordem_em_que_o_modelo_devolveu(): void
    {
        // Ordem invertida deliberadamente na entrada de fatos.
        $facts = $this->factsFor([
            'orientacoes' => [['text' => 'Retornar em caso de piora.', 'status' => 'relatado']],
            'queixa_principal' => [['text' => 'Paciente refere dor torácica.', 'status' => 'relatado']],
        ]);

        $html = (new ClinicalDocumentRenderer())->render($facts, $this->sections(), 'Template Genérico');

        $queixaPos = strpos($html, 'Queixa Principal');
        $orientacoesPos = strpos($html, 'Orientações');

        $this->assertNotFalse($queixaPos);
        $this->assertNotFalse($orientacoesPos);
        $this->assertLessThan($orientacoesPos, $queixaPos);
    }

    public function test_mencionado_sem_especificacao_emite_frase_fixa_sem_nome_de_exame(): void
    {
        $facts = $this->factsFor([
            'exames_relevantes' => [['text' => null, 'status' => 'mencionado_sem_especificacao']],
        ]);

        $html = (new ClinicalDocumentRenderer())->render($facts, $this->sections(), 'Template Genérico');

        $this->assertStringContainsString('Mencionada a necessidade de Exames Relevantes, sem especificação registrada na consulta.', $html);
        $this->assertStringNotContainsString('hemograma', mb_strtolower($html));
    }

    public function test_cid_filtra_status_e_omite_secao_sem_item_elegivel(): void
    {
        $facts = $this->factsFor([
            'diagnostico_cid' => [
                ['text' => 'Hipertensão arterial.', 'status' => 'estabelecido', 'code' => 'I10'],
                ['text' => 'Diabetes mellitus.', 'status' => 'descartado', 'code' => 'E11'],
            ],
        ]);

        $html = (new ClinicalDocumentRenderer())->render($facts, $this->sections(), 'Template Genérico');

        $this->assertStringContainsString('I10 — Hipertensão arterial.', $html);
        $this->assertStringNotContainsString('Diabetes mellitus', $html);

        // Sem nenhum item elegível, a seção inteira some.
        $onlyDiscarded = $this->factsFor([
            'diagnostico_cid' => [['text' => 'Diabetes mellitus.', 'status' => 'descartado', 'code' => 'E11']],
        ]);
        $htmlDiscarded = (new ClinicalDocumentRenderer())->render($onlyDiscarded, $this->sections(), 'Template Genérico');

        $this->assertStringNotContainsString('Impressão Diagnóstica', $htmlDiscarded);
    }

    public function test_texto_com_tags_html_sai_escapado_sem_alterar_contagem_de_tags_do_documento(): void
    {
        $facts = $this->factsFor([
            'queixa_principal' => [
                ['text' => '<script>alert(1)</script>', 'status' => 'relatado'],
                ['text' => 'Fecho a seção </h3> indevidamente.', 'status' => 'relatado'],
            ],
        ]);

        $html = (new ClinicalDocumentRenderer())->render($facts, $this->sections(), 'Template Genérico');

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('&lt;/h3&gt;', $html);
        // Exatamente o <h3> real da seção — nenhum extra "aberto" pelo texto do fato.
        $this->assertSame(1, substr_count($html, '<h3>'));
        $this->assertSame(1, substr_count($html, '</h3>'));
    }

    public function test_renderer_nao_recebe_nenhum_cliente_de_rede(): void
    {
        // A própria assinatura do construtor/render() é a prova: nenhum
        // parâmetro aceita um client Groq, e a classe não importa a facade.
        $renderer = new ClinicalDocumentRenderer();
        $reflection = new \ReflectionClass($renderer);

        $this->assertSame([], $reflection->getConstructor()?->getParameters() ?? []);
        $this->assertStringNotContainsString('Groq', (string) file_get_contents((new \ReflectionClass($renderer))->getFileName()));
    }

    public function test_titulo_ausente_usa_nome_do_template_como_fallback(): void
    {
        $facts = $this->factsFor([
            'queixa_principal' => [['text' => 'Paciente refere dor torácica.', 'status' => 'relatado']],
        ], null);

        $html = (new ClinicalDocumentRenderer())->render($facts, $this->sections(), 'Cardiologia');

        $this->assertStringContainsString('<h2><strong>Cardiologia</strong></h2>', $html);
    }

    public function test_render_list_envolve_os_li_em_ul(): void
    {
        $facts = $this->factsFor([
            'orientacoes' => [
                ['text' => 'Retornar em caso de piora.', 'status' => 'relatado'],
                ['text' => 'Manter repouso.', 'status' => 'relatado'],
            ],
        ]);

        $html = (new ClinicalDocumentRenderer())->render($facts, $this->sections(), 'Template Genérico');

        $this->assertStringContainsString('<ul><li>Retornar em caso de piora.</li><li>Manter repouso.</li></ul>', $html);
    }

    /**
     * Golden-file da subtarefa 4 de SH-R23-01
     * (ai-vitalfy/action-plans/shared/R23.md#sh-r23-01, "Execução da
     * curadoria"): mesma transcrição de exemplo, mesmos fatos esperados,
     * HTML registrado por escrito antes desta implementação existir.
     */
    public function test_golden_file_da_transcricao_de_exemplo_de_sh_r23_01(): void
    {
        $facts = $this->factsFor([
            'queixa_principal' => [[
                'text' => 'Paciente refere dor torácica há três dias.',
                'status' => 'relatado',
            ]],
            'hda' => [[
                'text' => 'A dor piora aos esforços.',
                'status' => 'relatado',
            ]],
            'exames_relevantes' => [[
                'text' => null,
                'status' => 'mencionado_sem_especificacao',
            ]],
            'diagnostico_cid' => [],
            'orientacoes' => [],
        ], ['text' => 'Dor torácica aos esforços', 'source_key' => 'queixa_principal']);

        $html = (new ClinicalDocumentRenderer())->render($facts, $this->sections(), 'Template Genérico');

        $expected = '<h2><strong>Dor torácica aos esforços</strong></h2><p></p>'
            . '<h3><strong>Queixa Principal</strong></h3><p>Paciente refere dor torácica há três dias.</p><p></p>'
            . '<h3><strong>História da Doença Atual</strong></h3><p>A dor piora aos esforços.</p><p></p>'
            . '<h3><strong>Exames Relevantes</strong></h3><p>Mencionada a necessidade de Exames Relevantes, sem especificação registrada na consulta.</p><p></p>';

        $this->assertSame($expected, $html);
    }
}
