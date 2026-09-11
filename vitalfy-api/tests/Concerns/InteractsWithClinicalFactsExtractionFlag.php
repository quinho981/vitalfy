<?php

namespace Tests\Concerns;

/**
 * SH-R23-02 (ai-vitalfy/action-plans/shared/R23.md): mesmo padrão de
 * InteractsWithAsyncPipelineFlag, para FEATURE_CLINICAL_FACTS_EXTRACTION.
 */
trait InteractsWithClinicalFactsExtractionFlag
{
    private ?string $originalClinicalFactsFlagValue = null;

    private bool $clinicalFactsFlagOverridden = false;

    protected function setClinicalFactsExtraction(bool $enabled): void
    {
        if (! $this->clinicalFactsFlagOverridden) {
            $current = getenv('FEATURE_CLINICAL_FACTS_EXTRACTION');
            $this->originalClinicalFactsFlagValue = $current === false ? null : $current;
            $this->clinicalFactsFlagOverridden = true;
        }

        $this->putClinicalFactsFlagEnv($enabled ? 'true' : 'false');
    }

    protected function restoreClinicalFactsExtraction(): void
    {
        if (! $this->clinicalFactsFlagOverridden) {
            return;
        }

        if ($this->originalClinicalFactsFlagValue === null) {
            putenv('FEATURE_CLINICAL_FACTS_EXTRACTION');
            unset($_ENV['FEATURE_CLINICAL_FACTS_EXTRACTION'], $_SERVER['FEATURE_CLINICAL_FACTS_EXTRACTION']);
        } else {
            $this->putClinicalFactsFlagEnv($this->originalClinicalFactsFlagValue);
        }

        $this->clinicalFactsFlagOverridden = false;
    }

    private function putClinicalFactsFlagEnv(string $value): void
    {
        putenv("FEATURE_CLINICAL_FACTS_EXTRACTION={$value}");
        $_ENV['FEATURE_CLINICAL_FACTS_EXTRACTION'] = $value;
        $_SERVER['FEATURE_CLINICAL_FACTS_EXTRACTION'] = $value;
    }
}
