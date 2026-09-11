<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BE-R23-03 (ai-vitalfy/action-plans/backend/R23.md): marca quando
 * BE-R23-06 caiu no fallback (extracao/validacao/montagem falhou e o
 * documento saiu pelo caminho de hoje). Mesma forma de insights_failed_at
 * (BE-R8-01).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->timestamp('facts_fallback_at')->nullable()->after('insights_failed_at');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('facts_fallback_at');
        });
    }
};
