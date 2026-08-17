<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateCategory;
use App\Models\Subscription;
use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use App\Jobs\ProcessGenerateInsightsAI;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * R5 (ai-vitalfy/action-plans/backend/R5.md, BE-R5-04): conta não verificada
 * continua navegando o sistema inteiro — só as cinco rotas que geram custo
 * real (Deepgram/Groq) ficam bloqueadas. Ver decisão de escopo em
 * ai-vitalfy/action-plans/R5.md#estratégia.
 */
class EmailVerificationRequiredTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);
    }

    private function createTranscriptFor(User $owner): Transcript
    {
        $transcriptType = TranscriptType::create(['type' => 'Consulta']);

        return Transcript::create([
            'user_id' => $owner->id,
            'transcript_type_id' => $transcriptType->id,
            'patient' => 'Paciente Teste',
            'conversation' => ['speaker' => 'médico', 'text' => 'teste'],
        ]);
    }

    private function createDocumentFor(Transcript $transcript): Document
    {
        $category = DocumentTemplateCategory::create(['name' => 'Categoria Teste']);
        $template = DocumentTemplate::create([
            'name' => 'Modelo padrão',
            'category_id' => $category->id,
            'content' => 'Contexto: {context}',
        ]);

        return Document::create([
            'transcript_id' => $transcript->id,
            'document_template_id' => $template->id,
            'patient' => 'Paciente Teste',
            'result' => '<p>documento existente</p>',
        ]);
    }

    private function giveActiveSubscription(User $user): void
    {
        Subscription::create([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_' . uniqid(),
            'stripe_status' => 'active',
            'ends_at' => null,
        ]);
    }

    private function assertRequiresVerification($response): void
    {
        $response->assertStatus(409);
        $response->assertJson(['email_verification_required' => true]);
    }

    // --- as cinco rotas de custo bloqueiam quem não verificou ---

    public function test_post_transcripts_bloqueia_usuario_nao_verificado(): void
    {
        Sanctum::actingAs(User::factory()->unverified()->create());

        $this->assertRequiresVerification($this->postJson('/api/transcripts', []));
    }

    public function test_post_transcripts_generate_document_bloqueia_usuario_nao_verificado(): void
    {
        Sanctum::actingAs(User::factory()->unverified()->create());

        $this->assertRequiresVerification($this->postJson('/api/transcripts/generate-document', []));
    }

    public function test_post_documents_generate_bloqueia_usuario_nao_verificado(): void
    {
        Sanctum::actingAs(User::factory()->unverified()->create());

        $this->assertRequiresVerification($this->postJson('/api/documents/generate', []));
    }

    public function test_post_documents_refine_bloqueia_usuario_nao_verificado_mesmo_com_plano_pro(): void
    {
        $user = User::factory()->unverified()->create();
        $this->giveActiveSubscription($user);

        Sanctum::actingAs($user);

        $this->assertRequiresVerification($this->postJson('/api/documents/refine', ['content' => 'texto']));
    }

    public function test_regenerate_insights_bloqueia_usuario_nao_verificado(): void
    {
        $owner = User::factory()->unverified()->create();
        $document = $this->createDocumentFor($this->createTranscriptFor($owner));

        Sanctum::actingAs($owner);

        $this->assertRequiresVerification($this->postJson("/api/documents/{$document->id}/regenerate-insights"));
    }

    // --- navegação e edição do que já existe continuam abertas ---

    public function test_usuario_nao_verificado_continua_navegando_o_resto_do_sistema(): void
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/user')->assertStatus(200);
        $this->getJson('/api/user/transcripts')->assertStatus(200);
        $this->getJson('/api/templates')->assertStatus(200);
        $this->getJson('/api/transcript-types')->assertStatus(200);
        $this->getJson('/api/dashboard/summary')->assertStatus(200);
        $this->getJson('/api/subscription')->assertStatus(200);
    }

    // --- usuário verificado não é afetado por nenhuma das cinco rotas ---

    public function test_usuario_verificado_nao_e_bloqueado_em_transcripts(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/transcripts', []);

        // Sem áudio no payload a validação da FormRequest rejeita — o que
        // importa aqui é que NÃO é o 409 de verificação.
        $response->assertStatus(422);
    }

    public function test_usuario_verificado_passa_pelo_middleware_de_regenerate_insights(): void
    {
        Queue::fake();

        $owner = User::factory()->create();
        $document = $this->createDocumentFor($this->createTranscriptFor($owner));

        Sanctum::actingAs($owner);
        $response = $this->postJson("/api/documents/{$document->id}/regenerate-insights");

        $response->assertStatus(200);
        Queue::assertPushed(ProcessGenerateInsightsAI::class);
    }

    // --- GET /user expõe o estado de verificação ---

    public function test_get_user_expoe_email_verified_true_para_conta_verificada(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJsonPath('user.email_verified', true);
    }

    public function test_get_user_expoe_email_verified_false_para_conta_nao_verificada(): void
    {
        Sanctum::actingAs(User::factory()->unverified()->create());

        $response = $this->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJsonPath('user.email_verified', false);
    }
}
