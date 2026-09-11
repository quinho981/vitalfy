<?php

namespace Tests\Unit;

use App\Support\ClinicalFactsValidator;
use PHPUnit\Framework\TestCase;

/**
 * BE-R23-05 (ai-vitalfy/action-plans/backend/R23.md): os dez casos de
 * ataque da subtarefa 9, mais o caso feliz da transcrição de exemplo de
 * SH-R23-01. Nenhum toca rede ou banco.
 */
class ClinicalFactsValidatorTest extends TestCase
{
    private const TRANSCRIPT = 'Paciente relata dor no peito há três dias. '
        . 'Diz que a dor piora quando realiza esforço. '
        . 'Médico informa que irá solicitar um exame.';

    private const TEMPLATE_ID = 12;

    private function sections(): array
    {
        return [
            ['key' => 'queixa_principal', 'label' => 'Queixa Principal', 'render' => 'prose'],
            ['key' => 'hda', 'label' => 'História da Doença Atual', 'render' => 'prose'],
            [
                'key' => 'exames_relevantes',
                'label' => 'Exames Relevantes',
                'render' => 'prose',
                'status_enum' => ['realizado', 'solicitado', 'mencionado_sem_especificacao'],
            ],
            [
                'key' => 'diagnostico_cid',
                'label' => 'Impressão Diagnóstica (CID)',
                'render' => 'cid',
                'status_enum' => ['hipotese', 'estabelecido', 'descartado'],
            ],
            ['key' => 'orientacoes', 'label' => 'Orientações', 'render' => 'list'],
        ];
    }

    private function basePayload(array $overrides = []): array
    {
        // Shallow merge de propósito: 'sections' é uma lista, e
        // array_replace_recursive fundiria índices numéricos em vez de
        // substituir a lista inteira quando o teste passa overrides.
        return array_merge([
            'schema_version' => 'clinical-facts/1',
            'template_id' => self::TEMPLATE_ID,
            'title' => ['text' => 'Dor torácica aos esforços', 'source_key' => 'queixa_principal'],
            'sections' => [
                [
                    'key' => 'queixa_principal',
                    'items' => [
                        [
                            'text' => 'Paciente refere dor torácica há três dias.',
                            'status' => 'relatado',
                            'speaker' => 1,
                            'evidence' => 'Paciente relata dor no peito há três dias',
                        ],
                    ],
                ],
            ],
        ], $overrides);
    }

    public function test_caso_feliz_mantem_fatos_ancorados_e_normaliza_secoes_ausentes_para_vazio(): void
    {
        $payload = $this->basePayload([
            'sections' => [
                [
                    'key' => 'queixa_principal',
                    'items' => [
                        [
                            'text' => 'Paciente refere dor torácica há três dias.',
                            'status' => 'relatado',
                            'speaker' => 1,
                            'evidence' => 'Paciente relata dor no peito há três dias',
                        ],
                    ],
                ],
                [
                    'key' => 'exames_relevantes',
                    'items' => [
                        ['text' => null, 'status' => 'mencionado_sem_especificacao', 'speaker' => null, 'evidence' => 'irá solicitar um exame'],
                    ],
                ],
            ],
        ]);

        $result = (new ClinicalFactsValidator())->validate($payload, self::TEMPLATE_ID, $this->sections(), self::TRANSCRIPT);

        $this->assertTrue($result->valid);

        $byKey = self::collectByKey($result->facts['sections']);

        $this->assertCount(1, $byKey['queixa_principal']);
        $this->assertCount(1, $byKey['exames_relevantes']);
        $this->assertNull($byKey['exames_relevantes'][0]['text']);
        // seções declaradas e não mencionadas pelo modelo saem vazias, não ausentes
        $this->assertSame([], $byKey['hda']);
        $this->assertSame([], $byKey['diagnostico_cid']);
        $this->assertSame([], $byKey['orientacoes']);
        $this->assertSame('queixa_principal', $result->facts['title']['source_key']);
    }

    public function test_fato_com_evidencia_inventada_e_descartado_por_ancora_sem_invalidar_payload(): void
    {
        // Dois fatos ancorados de verdade ao lado do inventado, para que o
        // descarte de 1 item (33%) fique abaixo do limiar de 40% da decisão
        // 5 e o teste isole o comportamento por item, não a invalidação do
        // payload inteiro (que teria dominado com um único item ruim).
        $payload = $this->basePayload([
            'sections' => [
                [
                    'key' => 'queixa_principal',
                    'items' => [
                        ['text' => 'Paciente refere dor torácica.', 'status' => 'relatado', 'speaker' => 1, 'evidence' => 'dor no peito'],
                        ['text' => 'A dor piora aos esforços.', 'status' => 'relatado', 'speaker' => 1, 'evidence' => 'a dor piora quando realiza esforço'],
                        // inventado: nenhuma menção a febre na transcrição.
                        ['text' => 'Paciente relata febre alta.', 'status' => 'relatado', 'speaker' => 1, 'evidence' => 'paciente relata febre alta persistente'],
                    ],
                ],
            ],
        ]);

        $result = (new ClinicalFactsValidator())->validate($payload, self::TEMPLATE_ID, $this->sections(), self::TRANSCRIPT);

        $this->assertTrue($result->valid);
        $this->assertSame(1, $result->stats['items_dropped_anchor']);
        $this->assertCount(2, self::collectByKey($result->facts['sections'])['queixa_principal']);
    }

    public function test_evidencia_de_outra_consulta_e_descartada_por_ancora(): void
    {
        $payload = $this->basePayload([
            'sections' => [
                [
                    'key' => 'queixa_principal',
                    'items' => [
                        ['text' => 'Paciente refere tosse seca.', 'status' => 'relatado', 'speaker' => 1, 'evidence' => 'estou com tosse seca ha uma semana'],
                    ],
                ],
            ],
        ]);

        $result = (new ClinicalFactsValidator())->validate($payload, self::TEMPLATE_ID, $this->sections(), self::TRANSCRIPT);

        $this->assertSame(1, $result->stats['items_dropped_anchor']);
    }

    public function test_evidencia_parafraseada_nao_citacao_literal_e_descartada(): void
    {
        $payload = $this->basePayload([
            'sections' => [
                [
                    'key' => 'queixa_principal',
                    'items' => [
                        // Transcrição diz "dor no peito há três dias"; isto é uma
                        // paráfrase ("desde", reordenado), não citação literal.
                        ['text' => 'Paciente refere dor torácica.', 'status' => 'relatado', 'speaker' => 1, 'evidence' => 'sente dor no peito desde ha tres dias'],
                    ],
                ],
            ],
        ]);

        $result = (new ClinicalFactsValidator())->validate($payload, self::TEMPLATE_ID, $this->sections(), self::TRANSCRIPT);

        $this->assertSame(1, $result->stats['items_dropped_anchor']);
    }

    public function test_text_preenchido_com_status_mencionado_sem_especificacao_e_descartado(): void
    {
        $payload = $this->basePayload([
            'sections' => [
                [
                    'key' => 'queixa_principal',
                    'items' => [
                        [
                            'text' => 'Paciente refere dor torácica há três dias.',
                            'status' => 'relatado',
                            'speaker' => 1,
                            'evidence' => 'Paciente relata dor no peito há três dias',
                        ],
                    ],
                ],
                [
                    'key' => 'exames_relevantes',
                    'items' => [
                        // status diz "sem especificação", mas o modelo nomeou o exame -- exatamente o que esse status existe para impedir.
                        ['text' => 'Hemograma completo.', 'status' => 'mencionado_sem_especificacao', 'speaker' => null, 'evidence' => 'irá solicitar um exame'],
                    ],
                ],
            ],
        ]);

        $result = (new ClinicalFactsValidator())->validate($payload, self::TEMPLATE_ID, $this->sections(), self::TRANSCRIPT);

        $this->assertTrue($result->valid);
        $this->assertSame(1, $result->stats['items_dropped_schema']);
        $this->assertSame([], self::collectByKey($result->facts['sections'])['exames_relevantes']);
    }

    public function test_status_fora_do_enum_da_secao_e_descartado(): void
    {
        $payload = $this->basePayload([
            'sections' => [
                [
                    'key' => 'exames_relevantes',
                    'items' => [
                        // "hipotese" é do enum de diagnostico_cid, não de exames_relevantes.
                        ['text' => 'Exame mencionado.', 'status' => 'hipotese', 'speaker' => null, 'evidence' => 'irá solicitar um exame'],
                    ],
                ],
            ],
        ]);

        $result = (new ClinicalFactsValidator())->validate($payload, self::TEMPLATE_ID, $this->sections(), self::TRANSCRIPT);

        $this->assertSame(1, $result->stats['items_dropped_schema']);
    }

    public function test_secao_com_key_que_o_template_nao_declara_e_descartada_sem_invalidar_payload(): void
    {
        $payload = $this->basePayload([
            'sections' => [
                [
                    'key' => 'queixa_principal',
                    'items' => [
                        [
                            'text' => 'Paciente refere dor torácica há três dias.',
                            'status' => 'relatado',
                            'speaker' => 1,
                            'evidence' => 'Paciente relata dor no peito há três dias',
                        ],
                    ],
                ],
                [
                    'key' => 'secao_inventada_pelo_modelo',
                    'items' => [
                        ['text' => 'Fato qualquer.', 'status' => 'relatado', 'speaker' => null, 'evidence' => 'dor no peito'],
                    ],
                ],
            ],
        ]);

        $result = (new ClinicalFactsValidator())->validate($payload, self::TEMPLATE_ID, $this->sections(), self::TRANSCRIPT);

        $this->assertTrue($result->valid);
        $this->assertSame(1, $result->stats['items_dropped_unknown_section']);
        $keys = array_column($result->facts['sections'], 'key');
        $this->assertNotContains('secao_inventada_pelo_modelo', $keys);
        $this->assertCount(1, self::collectByKey($result->facts['sections'])['queixa_principal']);
    }

    public function test_secao_declarada_ausente_do_payload_sai_vazia_nao_ausente(): void
    {
        // basePayload só popula queixa_principal; hda, exames_relevantes,
        // diagnostico_cid e orientacoes nunca aparecem no payload do modelo.
        $result = (new ClinicalFactsValidator())->validate($this->basePayload(), self::TEMPLATE_ID, $this->sections(), self::TRANSCRIPT);

        $this->assertTrue($result->valid);
        $byKey = self::collectByKey($result->facts['sections']);
        $this->assertArrayHasKey('hda', $byKey);
        $this->assertSame([], $byKey['hda']);
        $this->assertSame([], $byKey['diagnostico_cid']);
    }

    public function test_template_id_de_outro_template_invalida_o_payload_inteiro(): void
    {
        $payload = $this->basePayload(['template_id' => 999]);

        $result = (new ClinicalFactsValidator())->validate($payload, self::TEMPLATE_ID, $this->sections(), self::TRANSCRIPT);

        $this->assertFalse($result->valid);
        $this->assertSame([], $result->facts);
        $this->assertNotNull($result->reason);
    }

    public function test_array_com_500_itens_trunca_no_limite_de_40_por_secao(): void
    {
        $items = [];
        for ($i = 0; $i < 500; $i++) {
            $items[] = ['text' => 'Paciente refere dor torácica.', 'status' => 'relatado', 'speaker' => 1, 'evidence' => 'dor no peito'];
        }

        $payload = $this->basePayload(['sections' => [['key' => 'queixa_principal', 'items' => $items]]]);

        $result = (new ClinicalFactsValidator())->validate($payload, self::TEMPLATE_ID, $this->sections(), self::TRANSCRIPT);

        $this->assertTrue($result->valid);
        $this->assertCount(40, self::collectByKey($result->facts['sections'])['queixa_principal']);
        $this->assertSame(460, $result->stats['items_truncated']);
        $this->assertSame(500, $result->stats['items_in']);
    }

    public function test_json_de_medical_analysis_de_insights_passado_por_engano_e_rejeitado(): void
    {
        // Formato de generateInsightsAI() -- nenhuma chave deste plano.
        $insightsPayload = [
            'medical_analysis' => [
                'red_flags' => [],
                'case_severity' => ['verde'],
                'brief_description' => ['consulta de rotina'],
                'possible_diagnoses' => [],
                'suggested_cid_codes' => [],
                'suggested_exams' => [],
                'suggested_conducts' => [],
                'missing_clinical_information' => [],
            ],
        ];

        $result = (new ClinicalFactsValidator())->validate($insightsPayload, self::TEMPLATE_ID, $this->sections(), self::TRANSCRIPT);

        $this->assertFalse($result->valid);
        $this->assertStringContainsString('schema_version', $result->reason);
    }

    public function test_taxa_de_descarte_por_ancora_acima_de_40_por_cento_invalida_o_payload(): void
    {
        $items = [];
        for ($i = 0; $i < 10; $i++) {
            // 5 ancoradas, 5 inventadas => 50% de descarte por âncora.
            $items[] = $i < 5
                ? ['text' => 'Paciente refere dor torácica.', 'status' => 'relatado', 'speaker' => 1, 'evidence' => 'dor no peito']
                : ['text' => 'Paciente relata febre alta.', 'status' => 'relatado', 'speaker' => 1, 'evidence' => 'febre alta persistente e calafrios'];
        }

        $payload = $this->basePayload(['sections' => [['key' => 'queixa_principal', 'items' => $items]]]);

        $result = (new ClinicalFactsValidator())->validate($payload, self::TEMPLATE_ID, $this->sections(), self::TRANSCRIPT);

        $this->assertFalse($result->valid);
        $this->assertStringContainsString('âncora', $result->reason);
    }

    private static function collectByKey(array $sections): array
    {
        $out = [];
        foreach ($sections as $section) {
            $out[$section['key']] = $section['items'];
        }
        return $out;
    }
}
