<?php

namespace Tests\Unit;

use App\Services\ClinicalFactsExtractor;
use App\Services\DocumentService;
use PHPUnit\Framework\TestCase;

/**
 * BE-R23-04: o schema estrito que substituiu o `json_object`, e a tradução
 * do mapa que ele obriga de volta para a forma de clinical-facts/1.
 *
 * O que estes testes travam é uma regressão medida contra o Groq real, não
 * hipotética: com `json_object` o gpt-oss-20b achatava
 * {"key": X, "items": [...]} em elementos soltos do array
 * ("historia_da_doenca_atual", ":", {"items": [...]}), o payload continuava
 * sendo JSON válido, e o ClinicalFactsValidator o aprovava guardando 1 de
 * 10 itens — documento truncado com facts_fallback_at nulo, sem sinal
 * nenhum de erro. `sections` como objeto com `additionalProperties: false`
 * remove a forma em que esse erro pode ser expresso.
 *
 * Sem bootstrap do Laravel: nada aqui toca config(), rede ou banco.
 */
class ClinicalFactsSchemaTest extends TestCase
{
    private function extractor(): ClinicalFactsExtractor
    {
        return new ClinicalFactsExtractor(new DocumentService());
    }

    private function sections(): array
    {
        return [
            ['key' => 'queixa_principal', 'label' => 'Queixa Principal', 'render' => 'prose'],
            [
                'key' => 'exames',
                'label' => 'Exames',
                'render' => 'list',
                'status_enum' => ['solicitado', 'realizado', 'mencionado_sem_especificacao'],
            ],
            [
                'key' => 'diagnostico_cid',
                'label' => 'Impressão Diagnóstica (CID)',
                'render' => 'cid',
                'status_enum' => ['hipotese', 'estabelecido', 'descartado'],
            ],
        ];
    }

    public function test_sections_e_objeto_indexado_pelas_keys_e_nao_array(): void
    {
        $schema = $this->extractor()->buildResponseSchema($this->sections())['schema'];
        $sections = $schema['properties']['sections'];

        $this->assertSame('object', $sections['type']);
        $this->assertSame(
            ['queixa_principal', 'exames', 'diagnostico_cid'],
            array_keys($sections['properties'])
        );
        $this->assertSame('array', $sections['properties']['queixa_principal']['type']);
    }

    /**
     * A key inventada e a key com typo ("historico_da_doenca_atual" no
     * lugar de "historia_...", vista na saída real) morrem aqui, na API,
     * em vez de chegarem ao validador como itens de seção desconhecida.
     */
    public function test_key_fora_do_template_e_impossivel_de_representar(): void
    {
        $schema = $this->extractor()->buildResponseSchema($this->sections())['schema'];
        $sections = $schema['properties']['sections'];

        $this->assertFalse($sections['additionalProperties']);
        $this->assertSame(
            ['queixa_principal', 'exames', 'diagnostico_cid'],
            $sections['required']
        );
    }

    public function test_status_usa_a_uniao_dos_enums_do_template_com_relatado_como_default(): void
    {
        $schema = $this->extractor()->buildResponseSchema($this->sections())['schema'];
        $item = $schema['$defs']['fato'];

        $this->assertSame(
            [
                'relatado',
                'solicitado',
                'realizado',
                'mencionado_sem_especificacao',
                'hipotese',
                'estabelecido',
                'descartado',
            ],
            $item['properties']['status']['enum']
        );
    }

    public function test_item_tem_os_cinco_campos_obrigatorios_e_fecha_para_campos_extras(): void
    {
        $schema = $this->extractor()->buildResponseSchema($this->sections())['schema'];
        $item = $schema['$defs']['fato'];

        $this->assertSame(['text', 'status', 'speaker', 'evidence', 'code'], $item['required']);
        $this->assertFalse($item['additionalProperties']);
        $this->assertSame(['string', 'null'], $item['properties']['text']['type']);
        $this->assertSame(['string', 'null'], $item['properties']['code']['type']);
        $this->assertSame('string', $item['properties']['evidence']['type']);
    }

    /**
     * O objeto de fato repetido inline em cada seção custava 853 tokens de
     * entrada por chamada no template de 10 seções (medido em 13/09/2026).
     * Uma definição, N referências — se alguém voltar a inlinar, a conta
     * volta junto.
     */
    public function test_fato_e_definido_uma_vez_e_referenciado_por_secao(): void
    {
        $schema = $this->extractor()->buildResponseSchema($this->sections())['schema'];

        $this->assertArrayHasKey('fato', $schema['$defs']);

        foreach ($schema['properties']['sections']['properties'] as $key => $section) {
            $this->assertSame(
                ['$ref' => '#/$defs/fato'],
                $section['items'],
                "seção {$key} deveria referenciar \$defs/fato, não repetir o objeto"
            );
        }

        // `evidence` só existe dentro do objeto de fato: se ele aparecer
        // na serialização de sections, o objeto voltou a ser inline.
        $this->assertStringNotContainsString(
            'evidence',
            json_encode($schema['properties']['sections']),
            'o objeto de fato vazou inline para dentro de sections'
        );
    }

    public function test_title_source_key_so_aceita_secao_declarada(): void
    {
        $schema = $this->extractor()->buildResponseSchema($this->sections())['schema'];

        $this->assertSame(
            ['queixa_principal', 'exames', 'diagnostico_cid'],
            $schema['properties']['title']['properties']['source_key']['enum']
        );
    }

    public function test_schema_e_estrito_e_recusa_campo_de_topo_inventado(): void
    {
        $block = $this->extractor()->buildResponseSchema($this->sections());

        $this->assertTrue($block['strict']);
        $this->assertFalse($block['schema']['additionalProperties']);
        $this->assertSame(
            ['schema_version', 'template_id', 'title', 'sections'],
            $block['schema']['required']
        );
    }

    public function test_mapa_vira_lista_de_key_items_preservando_ordem_e_itens(): void
    {
        $decoded = $this->extractor()->sectionsMapToList([
            'schema_version' => 'clinical-facts/1',
            'template_id' => 14,
            'sections' => [
                'queixa_principal' => [['text' => 'Dor de cabeça.', 'status' => 'relatado']],
                'exames' => [],
            ],
        ]);

        $this->assertSame([
            ['key' => 'queixa_principal', 'items' => [['text' => 'Dor de cabeça.', 'status' => 'relatado']]],
            ['key' => 'exames', 'items' => []],
        ], $decoded['sections']);

        $this->assertSame('clinical-facts/1', $decoded['schema_version']);
        $this->assertSame(14, $decoded['template_id']);
    }

    /**
     * A conversão precisa ser idempotente: um payload já na forma v1 (o que
     * os mocks de pipeline devolvem, e o que uma extração antiga
     * persistida tem) passa intacto.
     */
    public function test_payload_ja_na_forma_v1_passa_intacto(): void
    {
        $v1 = [
            'schema_version' => 'clinical-facts/1',
            'sections' => [
                ['key' => 'queixa_principal', 'items' => []],
            ],
        ];

        $this->assertSame($v1, $this->extractor()->sectionsMapToList($v1));
    }

    /**
     * Payload fora de forma não é consertado nem rejeitado aqui — quem
     * recusa com a razão exata é o ClinicalFactsValidator (condição 4).
     */
    public function test_sections_ausente_ou_fora_de_forma_passa_sem_conversao(): void
    {
        $extractor = $this->extractor();

        $this->assertSame(['template_id' => 14], $extractor->sectionsMapToList(['template_id' => 14]));
        $this->assertSame(['sections' => 'nada'], $extractor->sectionsMapToList(['sections' => 'nada']));
        $this->assertSame(['sections' => []], $extractor->sectionsMapToList(['sections' => []]));
    }

    public function test_valor_de_secao_que_nao_e_array_vira_lista_vazia(): void
    {
        $decoded = $this->extractor()->sectionsMapToList([
            'sections' => ['queixa_principal' => 'texto solto'],
        ]);

        $this->assertSame([['key' => 'queixa_principal', 'items' => []]], $decoded['sections']);
    }
}
