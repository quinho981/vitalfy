<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reintroduz o estado de processamento removido em
 * 2025_08_17_190915_remove_status_from_transcripts_table — não reverte
 * aquela migração, cria uma nova coluna. Ver BE-R1-05 em
 * ai-vitalfy/action-plans/backend/R1.md e D3 em ai-vitalfy/DECISIONS.md.
 *
 * Default 'completed' faz o backfill correto: toda transcrição existente
 * já passou pelo pipeline síncrono até o fim (ou não teria sido salva).
 *
 * `conversation` vira nullable porque BE-R1-06 cria a linha em `pending`
 * antes de o Deepgram rodar — o valor só existe a partir de `transcribing`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transcripts', function (Blueprint $table) {
            $table->string('status')->default('completed')->after('file_size');
            $table->text('failure_reason')->nullable()->after('status');
            $table->string('audio_storage_path')->nullable()->after('failure_reason');
            $table->text('conversation')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('transcripts', function (Blueprint $table) {
            $table->dropColumn(['status', 'failure_reason', 'audio_storage_path']);
            $table->text('conversation')->nullable(false)->change();
        });
    }
};
