import { ref, computed, onUnmounted } from 'vue';
import { AnamneseService } from '@/service/AnamneseService';
import { useHelpers } from '@/utils/helper';

// ~60s no total (20 x 3s), mesma ordem de grandeza do timeout de 10s que o
// SSE usava, com folga para variação de latência do LLM. Ver FE-R2-03 em
// ai-vitalfy/action-plans/frontend/R2.md.
const POLL_INTERVAL_MS = 3000;
const MAX_ATTEMPTS = 20;

/**
 * Substitui o EventSource por polling autenticado em
 * GET /documents/{document}/insights (BE-R2-05): usa a instância do axios,
 * herda o interceptor global de 401/403, e não depende da janela de 60s do
 * cache que o SSE tinha.
 */
export function useDocumentInsights() {
    const { capitalizeArray } = useHelpers();

    const medicalAnalysis = ref({
        red_flags: [],
        case_severity: [],
        brief_description: [],
        possible_diagnoses: [],
        suggested_cid_codes: [],
        suggested_exams: [],
        suggested_conducts: [],
        missing_clinical_information: [],
    });
    const insightsAttempted = ref(false);
    const insightsFailed = ref(false);

    const hasMedicalInsights = computed(() =>
        Object.values(medicalAnalysis.value).some((arr) => arr.length > 0)
    );

    let timer = null;
    let attempts = 0;
    let polledDocumentId = null;
    let visibilityListenerAdded = false;

    const applyInsights = (insights) => {
        const {
            red_flags, case_severity, brief_description, possible_diagnoses,
            suggested_cid_codes, suggested_exams, suggested_conducts, missing_clinical_information,
        } = insights;

        medicalAnalysis.value = {
            red_flags: capitalizeArray(red_flags) || [],
            case_severity: capitalizeArray(case_severity) || [],
            brief_description: capitalizeArray(brief_description) || [],
            possible_diagnoses: capitalizeArray(possible_diagnoses) || [],
            suggested_cid_codes: capitalizeArray(suggested_cid_codes) || [],
            suggested_exams: capitalizeArray(suggested_exams) || [],
            suggested_conducts: capitalizeArray(suggested_conducts) || [],
            missing_clinical_information: capitalizeArray(missing_clinical_information) || [],
        };
    };

    const clearTimer = () => {
        if (timer) {
            clearTimeout(timer);
            timer = null;
        }
    };

    const tick = async () => {
        attempts++;

        try {
            const response = await AnamneseService.getInsights(polledDocumentId);

            if (response.status === 200) {
                if (response.data?.failed) {
                    insightsFailed.value = true;
                    stopPolling();
                    return;
                }

                applyInsights(response.data);
                stopPolling();
                return;
            }
            // 204: ainda processando, continua o polling.
        } catch {
            // 401/403 já tratados pelo interceptor global de '@/services/axios'
            // (redireciona); qualquer outro erro conta como tentativa perdida,
            // igual ao comportamento do SSE antes de FE-R2-03.
        }

        if (attempts >= MAX_ATTEMPTS) {
            insightsFailed.value = true;
            stopPolling();
            return;
        }

        timer = setTimeout(tick, POLL_INTERVAL_MS);
    };

    const handleVisibilityChange = () => {
        if (document.visibilityState === 'hidden') {
            clearTimer();
        } else if (polledDocumentId && !timer) {
            tick();
        }
    };

    const startPolling = (documentId) => {
        stopPolling();
        polledDocumentId = documentId;
        attempts = 0;
        insightsAttempted.value = true;
        insightsFailed.value = false;

        if (!visibilityListenerAdded) {
            document.addEventListener('visibilitychange', handleVisibilityChange);
            visibilityListenerAdded = true;
        }

        tick();
    };

    const stopPolling = () => {
        clearTimer();
        polledDocumentId = null;

        if (visibilityListenerAdded) {
            document.removeEventListener('visibilitychange', handleVisibilityChange);
            visibilityListenerAdded = false;
        }
    };

    onUnmounted(stopPolling);

    return {
        medicalAnalysis,
        hasMedicalInsights,
        insightsAttempted,
        insightsFailed,
        applyInsights,
        startPolling,
        stopPolling,
    };
}
