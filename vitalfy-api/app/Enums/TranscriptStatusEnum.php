<?php

namespace App\Enums;

/**
 * Reintroduzido por BE-R1-05 (ai-vitalfy/action-plans/backend/R1.md) depois
 * de a coluna `status` ter sido removida em
 * 2025_08_17_190915_remove_status_from_transcripts_table — ver D3 em
 * ai-vitalfy/DECISIONS.md. Vive em `transcripts`, não em `documents`,
 * porque o processamento assíncrono (BE-R1-06) começa antes de o Document
 * existir.
 */
enum TranscriptStatusEnum: string
{
    case Pending = 'pending';
    case Transcribing = 'transcribing';
    case Generating = 'generating';
    case Completed = 'completed';
    case Failed = 'failed';

    public function isTerminal(): bool
    {
        return $this === self::Completed || $this === self::Failed;
    }
}
