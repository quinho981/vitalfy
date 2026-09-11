<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BE-R23-03 (ai-vitalfy/action-plans/backend/R23.md): fatos pertencem a
 * transcricao, nao ao documento -- derivam de conversation e sao funcao de
 * (transcricao, template) desde SH-R23-01 decisao 1. Sem indice por ora.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transcripts', function (Blueprint $table) {
            $table->jsonb('clinical_facts')->nullable()->after('conversation');
        });
    }

    public function down(): void
    {
        Schema::table('transcripts', function (Blueprint $table) {
            $table->dropColumn('clinical_facts');
        });
    }
};
