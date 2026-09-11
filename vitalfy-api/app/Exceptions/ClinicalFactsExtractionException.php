<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * BE-R23-04 (ai-vitalfy/action-plans/backend/R23.md): erro de rede, timeout
 * ou JSON indecodificável na extração factual. Deliberadamente não
 * InvalidArgumentException -- no pipeline (ProcessGenerateDocumentPipeline)
 * esse tipo já significa "falha definitiva, não retentar" e sequestraria o
 * comportamento. Esta é capturada por BE-R23-06 e vira fallback, não falha
 * de job.
 */
class ClinicalFactsExtractionException extends RuntimeException
{
}
