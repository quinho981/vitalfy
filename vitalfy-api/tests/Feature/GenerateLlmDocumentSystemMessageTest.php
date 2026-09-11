<?php

namespace Tests\Feature;

use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateCategory;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LucianoTonet\GroqLaravel\Facades\Groq;
use Tests\TestCase;

/**
 * BE-R23-09 (ai-vitalfy/action-plans/backend/R23.md), "Como validar": prova
 * de mutação que faltava na suíte de BE-R23-01. tests/Unit/PromptDelimitationTest
 * testa buildTemplatePayload() isoladamente com o system instructions
 * passado manualmente pelo teste — nunca prova que
 * DocumentService::generateLlmDocument() de fato PASSA
 * clinicalDocumentSystemInstructions() para ele. Removida essa linha, os
 * testes de BE-R23-01 continuavam verdes; só este, que chama
 * generateLlmDocument() de ponta a ponta com o Groq mockado na facade
 * (exige o container do Laravel, por isso é Feature e não Unit), pega.
 */
class GenerateLlmDocumentSystemMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_generateLlmDocument_envia_system_antes_do_user_com_as_regras_permanentes(): void
    {
        $category = DocumentTemplateCategory::create(['name' => 'Categoria Teste']);
        $template = DocumentTemplate::create([
            'name' => 'Modelo Teste',
            'category_id' => $category->id,
            'content' => '<h2><strong>{titulo}</strong></h2><p></p>{context}',
        ]);

        $captured = null;

        Groq::shouldReceive('chat->completions->create')
            ->once()
            ->andReturnUsing(function (array $payload) use (&$captured) {
                $captured = $payload;
                return ['choices' => [['message' => ['content' => '<h2><strong>Título</strong></h2><p></p>']]]];
            });

        (new DocumentService())->generateLlmDocument(
            [['text' => 'paciente: dor de cabeça.']],
            $template->id
        );

        $this->assertNotNull($captured, 'a facade Groq não foi chamada');
        $this->assertCount(2, $captured['messages']);
        $this->assertSame('system', $captured['messages'][0]['role']);
        $this->assertSame('user', $captured['messages'][1]['role']);
        $this->assertStringContainsString('INSTRUÇÕES OBRIGATÓRIAS', $captured['messages'][0]['content']);
        $this->assertStringNotContainsString('INSTRUÇÕES OBRIGATÓRIAS', $captured['messages'][1]['content']);
    }
}
