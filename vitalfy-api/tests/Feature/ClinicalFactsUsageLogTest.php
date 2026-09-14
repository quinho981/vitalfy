<?php

namespace Tests\Feature;

use App\Services\ClinicalFactsExtractor;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Psr7\Response;
use LucianoTonet\GroqLaravel\Facades\Groq;
use Tests\TestCase;

/**
 * O bloco `usage` da resposta do Groq era descartado por
 * llmResponseByTemplate(), e sem ele nenhuma das alavancas de
 * ai-vitalfy/CAPACITY.md é observável de dentro da aplicação: não dá para
 * saber se o caching automático pegou o prefixo, quanto custou o
 * raciocínio, nem alimentar os sinais numéricos de SH-R23-02.
 *
 * Feature e não Unit porque precisa da facade Groq mockada, que exige o
 * container — mesmo motivo de GenerateLlmDocumentSystemMessageTest. Não há
 * banco envolvido, então sem RefreshDatabase.
 */
class ClinicalFactsUsageLogTest extends TestCase
{
    private function sections(): array
    {
        return [
            ['key' => 'queixa_principal', 'label' => 'Queixa Principal', 'render' => 'prose'],
        ];
    }

    private function mockGroq(array $resposta): void
    {
        Groq::shouldReceive('baseUrl')->andReturn('https://api.groq.com/openai/v1');
        Groq::shouldReceive('apiKey')->andReturn('chave-de-teste');
        Groq::shouldReceive('makeRequest')
            ->once()
            ->andReturn(new Response(200, [], json_encode($resposta)));
    }

    private function factsJson(): string
    {
        return json_encode([
            'schema_version' => 'clinical-facts/1',
            'template_id' => 7,
            'title' => ['text' => 'Consulta', 'source_key' => 'queixa_principal'],
            'sections' => ['queixa_principal' => []],
        ]);
    }

    public function test_tokens_da_extracao_aparecem_no_log_incluindo_a_taxa_de_cache(): void
    {
        $this->mockGroq([
            'choices' => [['message' => ['content' => $this->factsJson()]]],
            'usage' => [
                'prompt_tokens' => 2445,
                'completion_tokens' => 1646,
                'prompt_tokens_details' => ['cached_tokens' => 1790],
                'completion_tokens_details' => ['reasoning_tokens' => 1324],
            ],
        ]);

        Log::spy();

        (new ClinicalFactsExtractor(new DocumentService()))
            ->extract([['text' => 'paciente: dor de cabeça.']], 7, $this->sections());

        Log::shouldHaveReceived('info')
            ->once()
            ->withArgs(function (string $message, array $context) {
                return $message === 'document.facts.extraction'
                    && $context['prompt_tokens'] === 2445
                    && $context['cached_tokens'] === 1790
                    && $context['completion_tokens'] === 1646
                    && $context['reasoning_tokens'] === 1324;
            });
    }

    /**
     * Hoje a conta não devolve `prompt_tokens_details` — o campo nem aparece
     * na resposta (medido em 13/09/2026). O log precisa registrar null e
     * seguir, nunca estourar: é justamente esse null que vai virar número no
     * dia em que o caching ligar.
     */
    public function test_usage_ausente_ou_incompleto_vira_null_sem_quebrar_a_extracao(): void
    {
        $this->mockGroq([
            'choices' => [['message' => ['content' => $this->factsJson()]]],
            'usage' => [
                'prompt_tokens' => 2445,
                'completion_tokens' => 1646,
            ],
        ]);

        Log::spy();

        $facts = (new ClinicalFactsExtractor(new DocumentService()))
            ->extract([['text' => 'paciente: dor de cabeça.']], 7, $this->sections());

        $this->assertSame('clinical-facts/1', $facts['schema_version']);

        Log::shouldHaveReceived('info')
            ->once()
            ->withArgs(function (string $message, array $context) {
                return $message === 'document.facts.extraction'
                    && $context['prompt_tokens'] === 2445
                    && $context['cached_tokens'] === null
                    && $context['reasoning_tokens'] === null;
            });
    }
}
