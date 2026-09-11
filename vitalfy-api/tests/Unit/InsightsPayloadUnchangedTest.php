<?php

namespace Tests\Unit;

use App\Services\DocumentService;
use PHPUnit\Framework\TestCase;

/**
 * BE-R23-09 (ai-vitalfy/action-plans/backend/R23.md): o teste de
 * não-regressão de AI Insights. Monta o payload que
 * DocumentService::generateInsightsAI() produziria (mesmos argumentos de
 * llmResponseByTemplate() ali: forceJsonFormat=true, reasoningEffort=
 * 'medium', sem systemInstructions) e compara com o snapshot literal
 * capturado antes de qualquer tarefa de R23 tocar este caminho.
 *
 * Uma mensagem, role: user, nenhum system. Qualquer refatoração futura
 * que vaze uma mensagem system para o caminho de insights quebra aqui.
 *
 * Mudar deliberadamente a redação de config('prompts.ai_insights') exige
 * regenerar __snapshots__/insights_payload.json — é o preço aceito de um
 * snapshot literal: qualquer alteração pede revisão humana explícita, não
 * só a adição de um `system`.
 */
class InsightsPayloadUnchangedTest extends TestCase
{
    private function prompts(): array
    {
        return require __DIR__ . '/../../config/prompts.php';
    }

    private function snapshot(): array
    {
        return json_decode(
            file_get_contents(__DIR__ . '/__snapshots__/insights_payload.json'),
            true
        );
    }

    private function buildInsightsPayload(): array
    {
        $documentService = new DocumentService();
        $context = [['text' => 'médico: bom dia. paciente: dor de cabeça.']];

        return $documentService->buildTemplatePayload(
            $context,
            $this->prompts()['ai_insights'],
            forceJsonFormat: true,
            reasoningEffort: 'medium'
            // systemInstructions omitido de propósito: generateInsightsAI()
            // nunca passa esse argumento.
        );
    }

    public function test_payload_de_insights_e_uma_unica_mensagem_user_sem_system(): void
    {
        $payload = $this->buildInsightsPayload();

        $this->assertCount(1, $payload['messages']);
        $this->assertSame('user', $payload['messages'][0]['role']);
        $this->assertSame(['type' => 'json_object'], $payload['response_format']);
        $this->assertSame('medium', $payload['reasoning_effort']);
        $this->assertSame(0.4, $payload['temperature']);
    }

    public function test_payload_de_insights_bate_com_o_snapshot_literal_anterior_a_r23(): void
    {
        $payload = $this->buildInsightsPayload();

        $this->assertSame($this->snapshot(), $payload);
    }
}
