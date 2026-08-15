<?php

namespace Tests\Concerns;

/**
 * BE-R19-05 (ai-vitalfy/action-plans/shared/R19.md, SH-R19-01):
 * FeatureFlags::asyncTranscriptPipeline() lê env() diretamente, de
 * propósito — é o que permite trocar o comportamento em runtime sem deploy
 * (ver App\Support\FeatureFlags). Testes que precisam de um valor específico
 * da flag, independente do que estiver no .env do ambiente de teste, usam
 * este trait para setar/restaurar a variável de ambiente ao redor do teste,
 * sem vazar o valor para outros testes do mesmo processo phpunit.
 */
trait InteractsWithAsyncPipelineFlag
{
    private ?string $originalAsyncPipelineFlagValue = null;

    private bool $asyncPipelineFlagOverridden = false;

    protected function setAsyncTranscriptPipeline(bool $enabled): void
    {
        if (! $this->asyncPipelineFlagOverridden) {
            $current = getenv('FEATURE_ASYNC_TRANSCRIPT_PIPELINE');
            $this->originalAsyncPipelineFlagValue = $current === false ? null : $current;
            $this->asyncPipelineFlagOverridden = true;
        }

        $this->putAsyncPipelineFlagEnv($enabled ? 'true' : 'false');
    }

    protected function restoreAsyncTranscriptPipeline(): void
    {
        if (! $this->asyncPipelineFlagOverridden) {
            return;
        }

        if ($this->originalAsyncPipelineFlagValue === null) {
            putenv('FEATURE_ASYNC_TRANSCRIPT_PIPELINE');
            unset($_ENV['FEATURE_ASYNC_TRANSCRIPT_PIPELINE'], $_SERVER['FEATURE_ASYNC_TRANSCRIPT_PIPELINE']);
        } else {
            $this->putAsyncPipelineFlagEnv($this->originalAsyncPipelineFlagValue);
        }

        $this->asyncPipelineFlagOverridden = false;
    }

    private function putAsyncPipelineFlagEnv(string $value): void
    {
        putenv("FEATURE_ASYNC_TRANSCRIPT_PIPELINE={$value}");
        $_ENV['FEATURE_ASYNC_TRANSCRIPT_PIPELINE'] = $value;
        $_SERVER['FEATURE_ASYNC_TRANSCRIPT_PIPELINE'] = $value;
    }
}
