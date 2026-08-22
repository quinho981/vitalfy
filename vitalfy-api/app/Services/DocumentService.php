<?php

namespace App\Services;

use App\Jobs\ProcessGenerateInsightsAI;
use App\Models\Document;
use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\Log;
use LucianoTonet\GroqLaravel\Facades\Groq;

class DocumentService
{
    protected const MODEL_NAME = 'openai/gpt-oss-20b';

    public function createDocumentAndDispatchInsights(array $request): Document
    {
        $documentContent = $this->generateLlmDocument($request['conversation'], $request['template']);

        $document = Document::create([
            'document_template_id' => $request['template'],
            'patient' => $request['patient'],
            'result' => $documentContent,
            'transcript_id' => $request['transcript_id']
        ]);

        ProcessGenerateInsightsAI::dispatch($document->id, $request['conversation']);

        return $document;
    }

    public function generateLlmDocument(array $context, int $templateId): string
    {   
        $template = DocumentTemplate::findOrFail($templateId);

        $response = $this->llmResponseByTemplate($context, $template->content);
        
        return $response;
    }

    public function llmResponseByTemplate(array $context, string $template, bool $forceJsonFormat = false, string $reasoningEffort = 'low'): string
    {
        $context = $this->mergeContextChunks($context);

        $prompt = str_replace('{context}', $context, $template);

        if (!$forceJsonFormat) {
            $prompt = $this->antiHallucinationGuardrails() . "\n\n" . $prompt
                . "\n\nLembre-se: utilize apenas o que está explícito na transcrição acima. Não invente informações.";
        }

        $payload = [
            'model' => self::MODEL_NAME,
            'temperature' => 0.2,
            'top_p' => 0.9,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ],
            ],
        ];

        if (str_starts_with(self::MODEL_NAME, 'openai/gpt-oss')) {
            $payload['reasoning_effort'] = $reasoningEffort;
        }

        if ($forceJsonFormat) {
            $payload['response_format'] = [ 'type' => 'json_object' ];
        }

        try {
            $response = Groq::chat()->completions()->create($payload);
        } catch (\Throwable $e) {
            Log::error('Erro no Groq: ' . $e->getMessage());
            throw $e;
        }

        return $response['choices'][0]['message']['content'];
    }

    private function antiHallucinationGuardrails(): string
    {
        return <<<TEXT
        INSTRUÇÕES OBRIGATÓRIAS — leia com atenção antes de gerar o documento:
        - Reproduza SOMENTE o que foi dito explicitamente pelo médico e pelo paciente na transcrição abaixo. Você não tem acesso a nenhuma informação além do texto fornecido.
        - Não invente, complete ou infira exames, medicações, diagnósticos, CIDs, condutas ou orientações que não tenham sido citados literalmente na transcrição.
        - Se o médico mencionar a necessidade de um exame, procedimento ou encaminhamento SEM citar qual (ex: "vou pedir um exame"), registre apenas que essa necessidade foi mencionada, sem citar nome, tipo ou categoria do exame.
        - Não utilize conhecimento médico geral para enriquecer, corrigir ou complementar informações ausentes na transcrição.
        - Não elabore hipóteses diagnósticas, raciocínio clínico próprio ou conclusões que vão além do que foi dito.
        - Utilize terminologia médica formal, em texto corrido, sem tópicos, símbolos (•) ou emojis, salvo instrução em contrário no modelo abaixo.
        - Evite o uso de latim, exceto em termos médicos consagrados.
        TEXT;
    }

    public function mergeContextChunks(array $contextChunks): string
    {
        $mergedContext = '';
        foreach ($contextChunks as $chunk) {
            $mergedContext .= $chunk['text'] . ' ';
        }
        return trim($mergedContext);    
    }

    public function generateInsightsAI(array $context): array
    {
        $promptTemplate = config("prompts.ai_insights");
        $insights = $this->llmResponseByTemplate($context, $promptTemplate, true, 'medium');
        return json_decode($insights, true);
    }

    public function refineDocument(array $data): string
    {
        $instructions = $this->buildRefinementInstructions(
            $data['refinements'] ?? [],
            $data['custom_instruction'] ?? null
        );

        $promptTemplate = config("prompts.anamnesis_dynamic_refine");

        $prompt = str_replace(
            ['{instructions}', '{context}'],
            [$instructions, $data['conversation']],
            $promptTemplate
        );

        $payload = [
            'model' => self::MODEL_NAME,
            'temperature' => 0.2,
            'top_p' => 0.9,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ],
            ],
        ];

        if (str_starts_with(self::MODEL_NAME, 'openai/gpt-oss')) {
            $payload['reasoning_effort'] = 'low';
        }

        $response = Groq::chat()->completions()->create($payload);

        return $response['choices'][0]['message']['content'];
    }

    private function buildRefinementInstructions(array $refinements, ?string $custom): string
    {
        $instructions = [];

        if (in_array('clarity', $refinements)) {
            $instructions[] = "- Improve clarity and sentence structure for better readability.";
        }

        if (in_array('technical', $refinements)) {
            $instructions[] = "- Use more formal and technical medical terminology.";
        }

        if (in_array('soap', $refinements)) {
            $instructions[] = "- Reorganize the document into SOAP format (Subjetivo, Objetivo, Avaliação, Plano).";
        }

        if (!empty($custom)) {
            $instructions[] = "- Additional instruction: " . $custom;
        }

        if (empty($instructions)) {
            $instructions[] = "- Improve the overall quality while maintaining structure.";
        }

        return implode("\n", $instructions);
    }
}