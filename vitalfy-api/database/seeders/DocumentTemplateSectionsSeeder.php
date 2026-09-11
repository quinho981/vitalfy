<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * BE-R23-03 (ai-vitalfy/action-plans/backend/R23.md): grava
 * document_templates.sections a partir do arquivo curado em
 * SH-R23-01 -- database/data/document_template_sections_final.json.
 *
 * O arquivo e o resultado revisado (ver
 * ai-vitalfy/action-plans/shared/R23.md#sh-r23-01, secao "Execução da
 * curadoria"), nao o rascunho de derive_sections.py. Este seeder so
 * ATUALIZA templates que ja existem (DocumentTemplateSeeder roda antes) --
 * nao cria linha nova, porque uma linha nova sem category_id/content
 * violaria as colunas obrigatorias da tabela.
 */
class DocumentTemplateSectionsSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('data/document_template_sections_final.json');

        $templates = json_decode(file_get_contents($filePath), true);

        $updated = 0;
        $missing = 0;

        foreach ($templates as $template) {
            $affected = DocumentTemplate::where('id', $template['template_id'])
                ->update(['sections' => $template['sections']]);

            if ($affected === 0) {
                $missing++;
                Log::warning('document_template_sections_seeder.template_not_found', [
                    'template_id' => $template['template_id'],
                ]);
                continue;
            }

            $updated++;
        }

        $this->command?->info("document_templates.sections atualizado: {$updated}, não encontrados: {$missing}");
    }
}
