<?php

use App\Models\Document;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/**
 * BE-R23-02 (ai-vitalfy/action-plans/backend/R23.md): exporta pares
 * documents.result + transcripts.conversation para classificação manual
 * (sustentada / inferida / inventada), insumo da linha de base de
 * factualidade que SH-R23-01 (decisão 5) usa para confirmar o limiar de
 * descarte de BE-R23-05.
 *
 * Roda contra o banco real, fora da suíte de testes — os documentos mais
 * recentes com transcript.conversation preenchida, sem filtro de usuário.
 * Cada arquivo é uma amostra isolada; o nome do arquivo é o id do documento,
 * nunca o nome do paciente.
 */
Artisan::command('r23:export-documents-sample {--limit=20}', function () {
    $limit = (int) $this->option('limit');

    $documents = Document::query()
        ->whereNotNull('result')
        ->whereHas('transcript', fn ($query) => $query->whereNotNull('conversation'))
        ->with(['transcript:id,conversation', 'documentTemplate:id,name'])
        ->latest('created_at')
        ->limit($limit)
        ->get();

    if ($documents->isEmpty()) {
        $this->warn('Nenhum documento com transcrição associada encontrado.');
        return;
    }

    $dir = 'r23-samples/' . now()->format('Y-m-d_His');
    Storage::disk('local')->makeDirectory($dir);

    foreach ($documents as $document) {
        Storage::disk('local')->put(
            "{$dir}/{$document->id}.json",
            json_encode([
                'document_id' => $document->id,
                'template_name' => $document->documentTemplate?->name,
                'document_result' => $document->result,
                'transcript_conversation' => $document->transcript?->conversation,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    $this->info(sprintf(
        '%d documentos exportados para storage/app/%s',
        $documents->count(),
        $dir
    ));
})->purpose('Exportar pares documento+transcrição para classificação manual de factualidade (BE-R23-02)');
