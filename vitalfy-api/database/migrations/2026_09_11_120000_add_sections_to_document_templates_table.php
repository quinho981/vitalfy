<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BE-R23-03 (ai-vitalfy/action-plans/backend/R23.md): document_templates.sections
 * e a entrada do montador de BE-R23-07 -- dado curado, nao derivado em
 * runtime. content nao e alterado; sections passa a ser a coluna de
 * montagem, revisada a mao em SH-R23-01.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            $table->jsonb('sections')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            $table->dropColumn('sections');
        });
    }
};
