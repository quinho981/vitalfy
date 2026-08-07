<template>
    <div>
        <section>
            <div class="flex items-center flex-wrap justify-between mb-3 py-3">
                <div>
                    <h1 class="text-3xl font-bold">Detalhes do documento clínico</h1>
                    <p class="my-1 text-lg ">Documento gerado automaticamente com base na consulta</p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="copyText"
                        class="!text-[14px] !font-semibold !py-2 px-3 flex items-center gap-2 border border-slate-200 rounded-lg bg-white hover:bg-gray-100 duration-300 
                            dark:border-neutral-700 dark:bg-neutral-900 dark:hover:bg-gray-700"
                    >
                        <Copy :size="17" />
                        Copiar
                    </button>
                    <button
                        @click="shareDocument"
                        class="!text-[14px] !font-semibold !py-2 px-3 flex items-center gap-2 border border-slate-200 rounded-lg bg-white hover:bg-gray-100 duration-300
                            dark:border-neutral-700 dark:bg-neutral-900 dark:hover:bg-gray-700"
                    >
                        <Share2 :size="17" />
                        Compartilhar
                    </button>
                    <button
                        @click="exportDocument"
                        class="!text-[14px] !font-semibold !py-2 px-3 flex items-center gap-2 border border-slate-200 rounded-lg bg-white hover:bg-gray-100 duration-300
                            dark:border-neutral-700 dark:bg-neutral-900 dark:hover:bg-gray-700"
                        :disabled="loadingExport"
                    >
                        <Loader2 v-if="loadingExport" :size="17" class="animate-spin" />
                        <Download v-else :size="17" />
                        {{ loadingExport ? 'Carregando' : 'Exportar' }} 
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 md:col-span-6 xl:col-span-3 hover:shadow-md transition-shadow duration-300 rounded-lg">
                    <div class="card flex items-center gap-3 !p-6">
                        <div>
                            <User class="text-blue-500" />
                        </div>
                        <div class="min-w-0">
                            <p>Paciente</p>
                            <p v-if="!loadingTranscript" class="font-bold text-lg truncate">{{ patient }}</p>
                            <Skeleton v-else width="11rem" height="1.4rem" class="mt-2"></Skeleton>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-3 hover:shadow-md transition-shadow duration-300 rounded-lg">
                    <div class="card flex items-center gap-3 !p-6">
                        <div>
                            <Calendar class="text-yellow-500" />
                        </div>
                        <div>
                            <p>Data</p>
                            <p v-if="!loadingTranscript" class="font-bold text-lg"> {{ createdAt }} </p>
                            <Skeleton v-else width="11rem" height="1.4rem" class="mt-2"></Skeleton>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-3 hover:shadow-md transition-shadow duration-300 rounded-lg">
                    <div class="card flex items-center gap-3 !p-6">
                        <div>
                            <Clock class="text-green-500" />
                        </div>
                        <div>
                            <p>Duração</p>
                            <p v-if="!loadingTranscript" class="font-bold text-lg">{{ duration }}</p>
                            <Skeleton v-else width="11rem" height="1.4rem" class="mt-2"></Skeleton>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-3 hover:shadow-md transition-shadow duration-300 rounded-lg">
                    <div class="card flex items-center gap-3 !p-6">
                        <div>
                            <LayoutTemplate class="text-orange-500" />
                        </div>
                        <div>
                            <p>Template</p>
                            <p v-if="!loadingTranscript" class="font-bold text-lg">
                                {{ documentTemplate }}
                            </p>
                            <Skeleton v-else width="11rem" height="1.4rem" class="mt-2"></Skeleton>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-4 mt-5">
                <div class="col-span-12 xl:col-span-8 rounded-lg">
                    <div class="card flex">
                        <div class="flex flex-col w-full">
                            <div class="flex gap-4 items-center w-full">
                                <Tabs v-model:value="activeTab" scrollable class="document-tabs w-full ml-2 mr-2">
                                    <TabList>
                                        <Tab 
                                            value="0" 
                                            @click="getConversations" 
                                            class="dark:!text-white dark:hover:!text-gray-300"
                                            :class="activeTab === '0' ? 'dark:!text-blue-400 dark:hover:!text-blue-400' : ''"
                                        >
                                            Contexto da consulta
                                        </Tab>
                                        <Tab 
                                            value="1" 
                                            class="dark:!text-white dark:hover:!text-gray-300"
                                            :class="activeTab === '1' ? 'dark:!text-blue-400 dark:hover:!text-blue-400' : ''"
                                        >
                                            {{ documentTemplate }}
                                        </Tab>
                                    </TabList>
                                </Tabs>
                            </div>
                            <div v-if="!loadingConversations">
                                <div v-show="activeTab === '0'" class="border border-slate-200 rounded-lg p-4 min-h-[21rem] max-h-[39rem] overflow-y-auto dark:border-gray-700">
                                    <div v-for="(conversation, uttIndex) in conversations" :key="uttIndex" class="mb-2">
                                        <div class="rounded-lg p-2">
                                            <div
                                                v-if="conversation.speaker == 0"
                                                class="flex mb-1"
                                            >
                                                <div>
                                                    <div class="flex">
                                                        <span class="text-xs text-surface-500 dark:text-surface-400">{{ conversation.start }}s — Falante 1</span>
                                                    </div>
                                                    <p class="text-surface-800 p-2 rounded-lg bg-surface-100 dark:bg-surface-700 dark:text-surface-200">{{ conversation.text }}</p>
                                                </div>
                                            </div>
                                            <div 
                                                v-else 
                                                class="flex justify-end mb-1"
                                            >
                                                <div>
                                                    <div class="flex">
                                                        <span class="text-xs text-surface-500 dark:text-surface-400">{{ conversation.start }}s — Falante 2</span>
                                                    </div>
                                                    <p class="text-surface-800 p-2 rounded-lg bg-blue-100 dark:bg-blue-900 dark:text-surface-200">{{ conversation.text }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div 
                                v-else 
                                v-show="activeTab === '0'" 
                                class="border border-slate-200 rounded-lg p-4 min-h-[21rem] max-h-[24rem] overflow-y-auto"
                            >
                                <SkeletonLoadingConversations />
                            </div>
                            <div v-show="activeTab === '1'">
                                <div v-if="loadingTranscript" class="flex items-center justify-center h-64">
                                    <Loader2 :size="24" class="animate-spin mr-1" />
                                    <span class="ml-2">Carregando editor...</span>
                                </div>
                                <div v-else>
                                    <Tiptap
                                        :key="tiptapKey"
                                        :content="documentContent"
                                        :isSaving="isSaving"
                                        :isPro="userStore.plan !== 'Free'"
                                        @open-refine-modal="handleRefineModalOpen"
                                        @save="handleSaveDocument"
                                    />
                                    <div
                                        v-if="documentFeedback == null"
                                        class="flex items-center justify-center gap-3 mt-6 max-[420px]:flex-col max-[420px]:gap-2"
                                    >
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Este documento foi útil?</p>
                                        <div class="flex items-center gap-3">
                                            <button
                                                @click="handleFeedback('positive')"
                                                :disabled="submittingFeedback"
                                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm font-medium transition-all duration-200"
                                                :class="documentFeedback === 'positive'
                                                    ? 'bg-green-100 border-green-400 text-green-700 dark:bg-green-900/40 dark:border-green-600 dark:text-green-400'
                                                    : 'border-slate-200 hover:bg-gray-100 dark:border-neutral-700 dark:hover:bg-neutral-800'"
                                            >
                                                <ThumbsUp :size="14" />
                                                Útil
                                            </button>
                                            <button
                                                @click="handleFeedback('negative')"
                                                :disabled="submittingFeedback"
                                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm font-medium transition-all duration-200"
                                                :class="documentFeedback === 'negative'
                                                    ? 'bg-red-100 border-red-400 text-red-700 dark:bg-red-900/40 dark:border-red-600 dark:text-red-400'
                                                    : 'border-slate-200 hover:bg-gray-100 dark:border-neutral-700 dark:hover:bg-neutral-800'"
                                            >
                                                <ThumbsDown :size="14" />
                                                Melhorar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 xl:col-span-4 rounded-lg">
                    <div class="card flex flex-col gap-5">
                        <div class="flex flex-col items-top">
                            <div class="flex gap-2 justify-between items-center">
                                <div class="flex gap-2">
                                    <BrainCircuit />
                                    <p class="font-semibold text-xl mb-1">Insights Vitalfy</p>
                                </div>
                                <button
                                    v-if="!hasMedicalInsights && !loadingTranscript && (sseFailed || !sseAttempted)"
                                    @click="regenerateInsights"
                                    :disabled="regeneratingInsights"
                                    class="!text-[12px] !font-semibold !py-1 px-2 flex items-center gap-1 border rounded-full bg-surface-100 
                                    hover:bg-surface-200 duration-200 dark:border-surface-600 dark:bg-surface-800 dark:hover:bg-surface-700 group"
                                >
                                    <Loader2 v-if="regeneratingInsights" :size="14" class="animate-spin" />
                                    <RefreshCw v-else :size="14" class="transition-transform duration-300 group-hover:rotate-180" />
                                    {{ regeneratingInsights ? 'Gerando...' : 'Recarregar insights' }}
                                </button>
                            </div>
                            <p class="text-sm text-gray-400">Análise clínica assistida por IA</p>
                        </div>
                        <div class="flex flex-col">
                            <div v-if="medicalAnalysis.red_flags.length">
                                <h4 class="text-lg font-semibold mb-2">Sinais de alertar</h4>
                                <div class="flex flex-wrap gap-2">
                                    <RedFlags :red_flags="medicalAnalysis.red_flags" />
                                </div>
                                <hr class="my-4" />
                            </div>
                            <div v-if="medicalAnalysis.case_severity.length">
                                <h4 class="text-lg font-semibold mb-2">Gravidade estimada</h4>
                                <div class="flex flex-wrap gap-2">
                                    <CaseSeverity :case_severity="medicalAnalysis.case_severity" />
                                </div>
                                <hr class="my-4" />
                            </div>
                            <div v-if="medicalAnalysis.brief_description.length">
                                <h4 class="text-lg font-semibold mb-2">Resumo clínico</h4>
                                <div class="flex flex-wrap gap-2">
                                    <BriefDescription :brief_description="medicalAnalysis.brief_description" />
                                </div>
                                <hr class="my-4" />
                            </div>
                            <div v-if="medicalAnalysis.possible_diagnoses.length">
                                <h4 class="text-lg font-semibold mb-2">Possíveis diagnósticos</h4>
                                <div class="flex flex-wrap gap-2">
                                    <PossibleDiagnoses :possible_diagnoses="medicalAnalysis.possible_diagnoses" />
                                </div>
                                <hr class="my-4" />
                            </div>
                            <div v-if="medicalAnalysis.suggested_cid_codes.length">
                                <h4 class="text-lg font-semibold mb-2">CIDs sugeridos</h4>
                                <div class="flex flex-wrap gap-2">
                                    <ListItems :items="medicalAnalysis.suggested_cid_codes" />
                                </div>
                                <hr class="my-4" />
                            </div>
                            <div v-if="medicalAnalysis.suggested_exams.length">
                                <h4 class="text-lg font-semibold mb-2">Exames sugeridos</h4>
                                <div class="flex flex-wrap gap-2">
                                    <SuggestedExams :suggested_exams="medicalAnalysis.suggested_exams" />
                                </div>
                                <hr class="my-4" />
                            </div>
                            <div v-if="medicalAnalysis.suggested_conducts.length">
                                <h4 class="text-lg font-semibold mb-2">Conduta sugerida</h4>
                                <div class="flex flex-wrap gap-2">
                                    <ListItems :items="medicalAnalysis.suggested_conducts" />
                                </div>
                                <hr class="my-4" />
                            </div>
                            <div v-if="medicalAnalysis.missing_clinical_information.length">
                                <h4 class="text-lg font-semibold mb-2">Informações possivelmente faltantes</h4>
                                <div class="flex flex-wrap gap-2">
                                    <ListItems :items="medicalAnalysis.missing_clinical_information" />
                                </div>
                            </div>
                        </div>
                        <div v-if="loadingTranscript || !hasMedicalInsights">
                            <SkeletonLoadingInsights />
                        </div>
                        <p class="text-xs text-gray-400 mx-auto mt-2">Esta análise é assistiva e não substitui avaliação médica.</p>
                    </div>
                </div>
            </div>
        </section>
        <RefineAnamnesis
            :showRefineModal="showRefineModal"
            :patientName="patient"
            :content="documentContent"
            :documentId="documentId"
            @close="showRefineModal = false"
            @apply-refined="updateContent"
        />
        <Signature
            :active="showUpgradeModal"
            :loading="upgradeLoading"
            @close="showUpgradeModal = false"
            @subscribe="handleUpgradeSubscribe"
        />
    </div>
</template>

<script setup>
import { defineAsyncComponent, ref, watch, onMounted, onBeforeUnmount, computed } from 'vue';
import { User, Calendar, Clock, Share2, Download, BrainCircuit, LayoutTemplate, Loader2, Copy, RefreshCw, ThumbsUp, ThumbsDown } from 'lucide-vue-next';
import { TranscriptsService } from '@/service/TranscriptsService';
import { AnamneseService } from '@/service/AnamneseService';
import { useRoute, useRouter } from "vue-router";
import { useShowToast } from '@/utils/useShowToast';
import { useHelpers } from '@/utils/helper';
import { useI18n } from 'vue-i18n';
import { useUserStore } from '@/stores/userStore';

const Tiptap = defineAsyncComponent(() => import('@/components/Tiptap.vue'));
const RefineAnamnesis = defineAsyncComponent(() => import('@/components/Modal/RefineAnamnesis.vue'));
const Signature = defineAsyncComponent(() => import('@/components/Modal/Signature.vue'));

const { t } = useI18n();
const { showSuccess, showError } = useShowToast();
const { formatPtBrCurto, convertSecondsToMinutes, capitalizeArray } = useHelpers();
const userStore = useUserStore();

const route = useRoute();
const router = useRouter();
const type = route.query.type;
const value = ref('');
const patient = ref('');
const createdAt = ref('');
const documentContent = ref('');
const duration = ref('');
const documentTemplate = ref('');
const activeTab = ref('1');
const conversations = ref('');
const loadingTranscript = ref(false);
const loadingConversations = ref(false);
const documentId = ref();
const showRefineModal = ref(false);
const showUpgradeModal = ref(false);
const upgradeLoading = ref(false);
const tiptapKey = ref(0); // key responsável pela renderização do tiptap, necessário para atualizar o conteúdo após refinamento
const isSaving = ref(false);
const medicalAnalysis = ref({
    red_flags: [],
    case_severity: [],
    brief_description: [],
    possible_diagnoses: [],
    suggested_cid_codes: [],
    suggested_exams: [],
    suggested_conducts: [],
    missing_clinical_information: []
})
const sseFailed = ref(false);
const sseAttempted = ref(false);
const regeneratingInsights = ref(false);
const documentFeedback = ref(null);
const submittingFeedback = ref(false);

let eventSource = null;

const hasMedicalInsights = computed(() => {
    return Object.values(medicalAnalysis.value).some(arr => arr.length > 0)
})

const showTranscript = async (id) => {
    loadingTranscript.value = true;
    sseFailed.value = false;
    sseAttempted.value = false;
    try {
        const response = await TranscriptsService.show(id);
        documentContent.value = ''

        patient.value = response.patient;
        documentTemplate.value = response.document.document_template.name;
        createdAt.value = formatPtBrCurto(response.created_at);
        duration.value = convertSecondsToMinutes(response.end_conversation_time)
        documentContent.value = response.document.result;
        documentId.value = response.document.id;
        documentFeedback.value = response.document.feedback ?? null;
        if(response.document?.ai_insights) {
            const ai = response.document.ai_insights

            medicalAnalysis.value = {
                red_flags: capitalizeArray(ai.red_flags) || [],
                case_severity: capitalizeArray(ai.case_severity) || [],
                brief_description: capitalizeArray(ai.brief_description) || [],
                possible_diagnoses: capitalizeArray(ai.possible_diagnoses) || [],
                suggested_cid_codes: capitalizeArray(ai.suggested_cid_codes) || [],
                suggested_exams: capitalizeArray(ai.suggested_exams) || [],
                suggested_conducts: capitalizeArray(ai.suggested_conducts) || [],
                missing_clinical_information: capitalizeArray(ai.missing_clinical_information) || []
            }
        }
    } catch (error) {
        showError(t('notifications.titles.error'), t('notifications.messages.dataLoadingError'), 3000)  
    } finally {
        loadingTranscript.value = false;
    }
}

const getConversations = async () => {
    if (conversations.value !== '') return;

    loadingConversations.value = true;
    try {
        const response = await TranscriptsService.getConversations(route.params.id);
        conversations.value = response.conversation;
    } catch (error) {
        showError(t('notifications.titles.error'), t('notifications.messages.dataLoadingError'), 3000)  
    } finally {
        loadingConversations.value = false;
    }
}

const copyText = () => {
    const parser = new DOMParser();
    const doc = parser.parseFromString(documentContent.value, "text/html");

    const plainText = doc.body.innerText;

    navigator.clipboard.writeText(plainText);
    showSuccess(t('notifications.titles.success'), t('notifications.messages.textCopiedSuccessfully'), 3000)
};

const loadingExport = ref(false);
const exportDocument = async () => {
    loadingExport.value = true;

    try {
        const blob = await AnamneseService.generatePdf(documentId.value);

        const url = window.URL.createObjectURL(blob);

        // 👉 abre o PDF em nova aba
        window.open(url, '_blank');

        // limpa memória depois de um tempo
        setTimeout(() => window.URL.revokeObjectURL(url), 5000);

        showSuccess(t('notifications.titles.success'), 'Documento aberto com sucesso!', 3000);
    } catch (error) {
        showError(t('notifications.titles.error'), 'Erro ao abrir PDF', 3000);
    } finally {
        loadingExport.value = false;
    }
}

const shareDocument = async () => {
    const shareData = {
        title: `Atendimento de ${patient.value}`,
        text: `Confira os detalhes deste atendimento: ${documentTemplate.value}`,
    };

    try {
        if (navigator.share) {
            await navigator.share(shareData);
            return;
        }

        const clipboardContent = `${shareData.title}\n${shareData.text}`;
        await navigator.clipboard.writeText(clipboardContent);
    } catch (error) {
        showError(t('notifications.titles.error'), 'Não foi possível compartilhar o documento', 3000);
    }
};
const startSSE = () => {
    sseAttempted.value = true;
    sseFailed.value = false;
    eventSource = new EventSource(`${import.meta.env.VITE_BASE_URL}/stream/insights-ai/${documentId.value}`);

    eventSource.onmessage = handleInsightMessage;
    eventSource.onerror = handleSSEError;

    router.replace({ path: route.path });
}

const handleInsightMessage = (event) => {
    let insights = JSON.parse(event.data);

    const { red_flags, case_severity, brief_description, possible_diagnoses,
        suggested_cid_codes, suggested_exams, suggested_conducts, missing_clinical_information} = insights

    medicalAnalysis.value = {
        red_flags: capitalizeArray(red_flags) || [],
        case_severity: capitalizeArray(case_severity) || [],
        brief_description: capitalizeArray(brief_description) || [],
        possible_diagnoses: capitalizeArray(possible_diagnoses) || [],
        suggested_cid_codes: capitalizeArray(suggested_cid_codes) || [],
        suggested_exams: capitalizeArray(suggested_exams) || [],
        suggested_conducts: capitalizeArray(suggested_conducts) || [],
        missing_clinical_information: capitalizeArray(missing_clinical_information) || []
    }

    eventSource.close();
}

const updateContent = (content) => {
    documentContent.value = content
    tiptapKey.value++;
}

const handleRefineModalOpen = () => {
    if (userStore.plan === 'Free') {
        showUpgradeModal.value = true
    } else {
        showRefineModal.value = true
    }
}

const handleUpgradeSubscribe = async (plan) => {
    upgradeLoading.value = true
    try {
        const { SubscriptionService } = await import('@/service/SubscriptionService')
        const response = await SubscriptionService.createCheckout(plan)
        window.location.href = response.url
    } catch (error) {
        showError(t('notifications.titles.error'), 'Erro ao iniciar assinatura. Tente novamente!', 3000)
    } finally {
        upgradeLoading.value = false
    }
}

const handleSSEError = (error) => {
    sseFailed.value = true;
    eventSource.close();
    showError('Erro', 'Aconteceu um problema ao carregar insights. Tente novamente!', 3000);
}

const regenerateInsights = async () => {
    regeneratingInsights.value = true;
    try {
        await TranscriptsService.regenerateInsights(documentId.value);
        showSuccess(t('notifications.titles.success'), 'Geração de insights iniciada!', 3000);
        startSSE();
    } catch (error) {
        showError(t('notifications.titles.error'), 'Erro ao regerar insights', 3000);
    } finally {
        regeneratingInsights.value = false;
    }
}

const handleFeedback = async (value) => {
    const newFeedback = documentFeedback.value === value ? null : value;
    submittingFeedback.value = true;
    try {
        await AnamneseService.update(documentId.value, { feedback: newFeedback });
        showSuccess(t('notifications.titles.success'), 'Feedback registrado com sucesso!', 4000);
        documentFeedback.value = newFeedback;
    } catch (error) {
        showError(t('notifications.titles.error'), 'Erro ao registrar feedback. Tente novamente!', 4000);
    } finally {
        submittingFeedback.value = false;
    }
}

const handleSaveDocument = async (content) => {
    isSaving.value = true;
    try {
        await AnamneseService.update(documentId.value, { result: content});
        documentContent.value = content;
        tiptapKey.value++;
        showSuccess(t('notifications.titles.success'), 'Documento salvo com sucesso!', 4000);
    } catch (error) {
        showError(t('notifications.titles.error'), 'Erro ao salvar documento. Tente novamente!', 4000);
    } finally {
        isSaving.value = false;
    }
}

watch(
    () => route.params.id,
    (newId) => {
        showTranscript(newId)
    }
)

onMounted(async () => {
    const id = route.params.id;
    await showTranscript(id);

    if(type === 'new' && documentId.value) {
        startSSE();
    }
});

onBeforeUnmount(() => {
    if (eventSource) eventSource.close();
});
</script>

<style scoped>
.custom-marker-topic li::marker {
  @apply text-primary;
}
.custom-marker-diagnosis li::marker {
  color: rgb(45, 187, 45);
}
.document-tabs :deep(.p-tablist-tab-list) {
    min-width: max-content;
}
.document-tabs :deep(.p-tablist-viewport) {
    -webkit-overflow-scrolling: touch;
}
</style>
