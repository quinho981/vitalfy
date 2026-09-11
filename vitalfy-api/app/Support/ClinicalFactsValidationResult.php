<?php

namespace App\Support;

/**
 * BE-R23-05 (ai-vitalfy/action-plans/backend/R23.md). Diferente de
 * assertValidMedicalAnalysis() (DocumentService.php), este resultado não
 * lança — devolve um valor tipado e deixa a decisão (fallback ou não) para
 * BE-R23-06, porque documento ausente não é tolerável como insight ausente é.
 */
final readonly class ClinicalFactsValidationResult
{
    private function __construct(
        public bool $valid,
        public array $facts,
        public ?string $reason,
        public array $stats,
    ) {
    }

    public static function invalid(string $reason, array $stats): self
    {
        return new self(false, [], $reason, $stats);
    }

    public static function valid(array $facts, array $stats): self
    {
        return new self(true, $facts, null, $stats);
    }
}
