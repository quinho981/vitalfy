import { ref, onUnmounted } from 'vue';
import { TranscriptsService } from '@/service/TranscriptsService';

// Contrato congelado em ai-vitalfy/action-plans/R1.md (SH-R1-01): intervalo
// de 3s, até 300 tentativas (15min) — mesma ordem de grandeza do $timeout do
// job (ProcessGenerateDocumentPipeline, 900s). Passado isso, é mais provável
// que algo tenha travado do que que o áudio de 30min ainda esteja
// processando; o front para de sondar sozinho, sem travar o usuário num
// spinner eterno.
const POLL_INTERVAL_MS = 3000;
const MAX_ATTEMPTS = 300;

const STORAGE_PREFIX = 'vitalfy:inflight-transcript:';

/**
 * FE-R1-03/FE-R1-04 (ai-vitalfy/action-plans/frontend/R1.md): acompanha o
 * status de um processamento assíncrono (POST .../generate-document → 202)
 * e mantém uma dica de retomada em localStorage para sobreviver a reload —
 * o estado real de verdade é sempre o servidor (BE-R1-05); a dica local só
 * evita perguntar "processamento de qual transcrição?" ao usuário.
 *
 * Não usa secureStorage (ver D2 em ai-vitalfy/DECISIONS.md): a dica é só um
 * ponteiro para consultar o servidor, que já autoriza por posse — não há
 * nada de sensível para assinar aqui.
 */
export function useTranscriptProcessing() {
    const status = ref(null); // 'pending' | 'transcribing' | 'generating' | 'completed' | 'failed'
    const failureReason = ref(null);
    const recoverable = ref(null);
    const timedOut = ref(false);

    let timer = null;
    let attempts = 0;
    let polledTranscriptId = null;
    let visibilityListenerAdded = false;
    let callbacks = {};

    const clearTimer = () => {
        if (timer) {
            clearTimeout(timer);
            timer = null;
        }
    };

    const tick = async () => {
        attempts++;

        try {
            const data = await TranscriptsService.getTranscriptStatus(polledTranscriptId);
            status.value = data.status;
            failureReason.value = data.failure_reason;
            recoverable.value = data.recoverable;

            if (data.status === 'completed') {
                stopPolling();
                callbacks.onCompleted?.(data);
                return;
            }

            if (data.status === 'failed') {
                stopPolling();
                callbacks.onFailed?.(data);
                return;
            }
        } catch (error) {
            // 401 já tratado pelo interceptor global de '@/services/axios'.
            // 404 (transcrição excluída nesse meio-tempo, ou id não existe
            // mais): trata como falha terminal em vez de insistir para
            // sempre — sem isso o polling nunca para.
            if (error.response?.status === 404) {
                stopPolling();
                callbacks.onNotFound?.();
                return;
            }
            // Qualquer outro erro conta como tentativa perdida e tenta de novo.
        }

        if (attempts >= MAX_ATTEMPTS) {
            timedOut.value = true;
            stopPolling();
            callbacks.onTimeout?.();
            return;
        }

        timer = setTimeout(tick, POLL_INTERVAL_MS);
    };

    const handleVisibilityChange = () => {
        if (document.visibilityState === 'hidden') {
            clearTimer();
        } else if (polledTranscriptId && !timer) {
            tick();
        }
    };

    const startPolling = (transcriptId, handlers = {}) => {
        stopPolling();
        polledTranscriptId = transcriptId;
        attempts = 0;
        callbacks = handlers;
        status.value = 'pending';
        failureReason.value = null;
        recoverable.value = null;
        timedOut.value = false;

        if (!visibilityListenerAdded) {
            document.addEventListener('visibilitychange', handleVisibilityChange);
            visibilityListenerAdded = true;
        }

        tick();
    };

    const stopPolling = () => {
        clearTimer();
        polledTranscriptId = null;

        if (visibilityListenerAdded) {
            document.removeEventListener('visibilitychange', handleVisibilityChange);
            visibilityListenerAdded = false;
        }
    };

    const isPolling = () => polledTranscriptId !== null;

    onUnmounted(stopPolling);

    return {
        status,
        failureReason,
        recoverable,
        timedOut,
        startPolling,
        stopPolling,
        isPolling,
    };
}

/**
 * FE-R1-04: dica de retomada, escopada por usuário — dois usuários no mesmo
 * navegador (ou o mesmo usuário logando de novo) não devem herdar o
 * ponteiro um do outro.
 */
export function persistInFlightTranscript(userId, transcriptId) {
    if (!userId) return;
    localStorage.setItem(`${STORAGE_PREFIX}${userId}`, transcriptId);
}

export function readInFlightTranscript(userId) {
    if (!userId) return null;
    return localStorage.getItem(`${STORAGE_PREFIX}${userId}`);
}

export function clearInFlightTranscript(userId) {
    if (!userId) return;
    localStorage.removeItem(`${STORAGE_PREFIX}${userId}`);
}
