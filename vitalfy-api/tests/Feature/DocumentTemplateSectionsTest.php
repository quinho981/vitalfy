<?php

namespace Tests\Feature;

use App\Models\DocumentTemplate;
use App\Models\User;
use Database\Seeders\DocumentTemplateCategorySeeder;
use Database\Seeders\DocumentTemplateSeeder;
use Database\Seeders\DocumentTemplateSectionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * BE-R23-09 (ai-vitalfy/action-plans/backend/R23.md): os 55 templates têm
 * sections válido (key única por template, render no enum, label não
 * vazio), e GET /templates não expõe a coluna — contrato interno de
 * montagem, nunca API pública (SH-R23-01, BE-R23-03).
 */
class DocumentTemplateSectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DocumentTemplateCategorySeeder::class);
        $this->seed(DocumentTemplateSeeder::class);
        $this->seed(DocumentTemplateSectionsSeeder::class);
    }

    public function test_os_55_templates_tem_sections_valido(): void
    {
        $templates = DocumentTemplate::query()->get(['id', 'name', 'sections']);

        $this->assertSame(55, $templates->count());

        foreach ($templates as $template) {
            $this->assertNotEmpty(
                $template->sections,
                "template {$template->id} ({$template->name}) sem sections populado"
            );

            $seenKeys = [];

            foreach ($template->sections as $section) {
                $this->assertArrayHasKey('key', $section);
                $this->assertArrayHasKey('label', $section);
                $this->assertArrayHasKey('render', $section);

                $this->assertNotContains(
                    $section['key'],
                    $seenKeys,
                    "template {$template->id}: key duplicada '{$section['key']}'"
                );
                $seenKeys[] = $section['key'];

                $this->assertContains(
                    $section['render'],
                    ['prose', 'list', 'cid'],
                    "template {$template->id}, seção {$section['key']}: render inválido '{$section['render']}'"
                );

                $this->assertNotSame('', trim($section['label']), "template {$template->id}: label vazio para key '{$section['key']}'");
            }
        }
    }

    public function test_get_templates_nao_expoe_sections(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/templates');

        $response->assertStatus(200);

        $bodies = $response->json();
        $this->assertNotEmpty($bodies);

        foreach ($bodies as $template) {
            $this->assertArrayNotHasKey('sections', $template);
        }
    }

    public function test_rodar_o_seeder_duas_vezes_nao_duplica_nem_altera_nada(): void
    {
        $before = DocumentTemplate::query()->orderBy('id')->pluck('sections', 'id')->toArray();

        $this->seed(DocumentTemplateSectionsSeeder::class);

        $after = DocumentTemplate::query()->orderBy('id')->pluck('sections', 'id')->toArray();

        $this->assertSame($before, $after);
        $this->assertSame(55, DocumentTemplate::count());
    }
}
