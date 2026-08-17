<template>
    <section>
        <div class="mb-3 py-3">
            <h1 class="text-3xl font-bold">Novo atendimento</h1>
            <p class="my-1 text-lg text-surface-500">Grave ou envie o áudio da consulta para gerar automaticamente o documento clínico e os insights</p>
        </div>
        <UpgradeBanner
            v-if="showWarningBanner"
            variant="warning"
            :reset-date="getNextMonthResetDate()"
            @upgrade="showSignatureModal = true"
            @dismiss="bannerDismissed = true"
        />
        <UpgradeBanner
            v-else-if="showCriticalBanner"
            variant="critical"
            :reset-date="getNextMonthResetDate()"
            @upgrade="showSignatureModal = true"
        />
        <div class="flex gap-x-5 flex-wrap md:flex-nowrap">
            <div class="card w-full md:w-1/2 flex flex-col gap-y-4 mb-5 md:mb-0">
                <SelectButtonMode 
                    :modelValue="inputMode"
                    @changeInputMode="confirmInputModeChangeIfFileSelected"
                />

                <RecordCardTitle v-if="isRecordMode()" />
                <UploadCardTitle v-if="!isRecordMode()" />

                <div class="flex flex-col gap-y-3">
                    <div>
                        <div class="flex gap-x-1">
                            <label class="mb-1 text-sm font-medium text-surface-700 dark:text-surface-300" for="name">Nome do Paciente<span class="text-red-500">*</span></label>
                        </div>
                        <InputText id="name" v-model="form.patient" type="text" class="w-full" maxlength="254" placeholder="Digite o nome do paciente..." :class="{ 'p-invalid': errorMessagePatient }" />
                        <small v-if="errorMessagePatient" class="text-red-500">
                            {{ errorMessagePatient }}
                        </small>
                    </div>
                    <div class="flex gap-4 flex-wrap xl:flex-nowrap">
                        <div class="w-full">
                            <label class="mb-1 text-sm font-medium text-surface-700 dark:text-surface-300" for="template">Template<span class="text-red-500">*</span></label>
                            <Select 
                                id="template" 
                                v-model="form.template_id" 
                                :options="dropdownTemplates" 
                                filter
                                :loading="loadingTemplates"
                                :class="{ 'p-invalid': errorMessage }"
                                optionValue="id" 
                                optionLabel="name" 
                                placeholder="Selecione" 
                                class="w-full" 
                            />
                        </div>
                        <div class="w-full">
                            <label class="mb-1 text-sm font-medium text-surface-700 dark:text-surface-300" for="type">Tipo de atendimento<span class="text-red-500">*</span></label>
                            <Select 
                                id="type" 
                                v-model="form.type_id" 
                                :options="dropdownTypes"
                                :loading="loadingTypes"
                                :class="{ 'p-invalid': errorMessageType }"
                                optionValue="id" 
                                optionLabel="type" 
                                placeholder="Selecione" 
                                class="w-full" 
                            />
                        </div>
                    </div>

                    <FileUpload
                        ref="uploader"
                        name="demo[]"
                        :auto="false"
                        @select="onFileSelect"
                        :multiple="false"
                        accept="audio/*,.mp3,.wav,.m4a,.aac,.ogg,.flac"
                        class="vitalfy-uploader"
                        :showThumbnails="false"
                        v-if="!isRecordMode()"
                    >
                        <template #header><span class="hidden"></span></template>
                        <template #content="{ files }"> 
                            <div 
                                v-if="files.length > 0" 
                                class="space-y-3"
                            >
                                <div 
                                    v-for="(file) of files" 
                                    :key="file.name + file.type + file.size"
                                    class="group flex items-center justify-between gap-4 p-4 rounded-lg 
                                        bg-surface-50 border border-surface-200
                                        hover:border-blue-300 hover:bg-surface-100 transition-all duration-200 dark:bg-surface-800 dark:border-surface-700 dark:hover:border-blue-600 dark:hover:bg-surface-700"
                                >
                                    <div class="flex items-center gap-4 flex-1 min-w-0">
                                        <div class="flex items-center justify-center w-10 h-10 rounded-lg 
                                                    bg-blue-50 dark:bg-blue-950">
                                            <FileVolume size="18" class="text-blue-600 dark:text-blue-400"/>
                                        </div>

                                        <div class="flex flex-col min-w-0">
                                            <span class="font-semibold text-sm truncate text-surface-800 dark:text-surface-200">
                                                {{ file.name }}
                                            </span>
                                            <span class="text-xs text-surface-500 dark:text-surface-400">
                                                {{ formatSize(file.size) }}
                                            </span>
                                        </div>

                                        <span class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-600 font-medium dark:bg-blue-950 dark:text-blue-400">
                                            {{ file.type.split('/')[1] || 'audio' }}
                                        </span>
                                    </div>

                                    <button 
                                        @click="removeFile"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg
                                            hover:bg-red-50 text-red-500 dark:hover:bg-red-950 dark:text-red-400 transition-colors"
                                    >
                                        <i class="pi pi-times text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template #empty>
                            <div 
                                @click="openFileDialog"
                                class="group relative flex flex-col items-center justify-center text-center
                                    p-8 rounded-lg border-2 border-dashed border-surface-300
                                    bg-surface-50
                                    hover:border-blue-400 hover:bg-blue-50/50
                                    transition-all duration-200 cursor-pointer 
                                    dark:bg-surface-800 dark:border-surface-700 
                                    dark:hover:border-blue-500 dark:hover:bg-blue-950/30"
                            >
                                <div class="flex items-center justify-center w-12 h-12 rounded-lg
                                            bg-blue-50 text-blue-600 mb-4
                                            group-hover:scale-105 transition dark:bg-blue-950 dark:text-blue-400">
                                    <Upload size="20"/>
                                </div>

                                <p class="text-sm font-medium text-surface-700 dark:text-surface-300">
                                    Arraste seu áudio aqui
                                </p>
                                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">
                                    ou clique para selecionar um arquivo
                                </p>

                                <div class="mt-4 px-4 py-2 rounded-lg text-sm font-medium
                                            bg-blue-600 text-white hover:bg-blue-700
                                            transition-colors">
                                    Selecionar arquivo
                                </div>
                            </div>
                        </template>
                    </FileUpload>
                    
                    <AudioRecord 
                        v-if="isRecordMode()"
                        @recorded="handleRecordedFile" 
                        @recording-started="scrollAfterRecordingStart"
                    />
                    
                    <div class="flex justify-end gap-x-2">
                        <div class="transcribe-actions flex gap-2 max-[990px]:flex-1 max-[990px]:flex-col">
                            <Button
                                ref="submitBtn"
                                @click="transcribeAudio"
                                :disabled="!selectedFile || isTranscribing || loadingTranscribeAndGenerate || isAsyncProcessing"
                                v-tooltip.top="isLimitReached ? 'Você atingiu o limite mensal. Faça upgrade para continuar.' : null"
                                outlined
                                severity="secondary"
                                class="transcription-button !border-blue-500 !text-blue-500 !bg-white !rounded-lg font-semibold hover:!bg-blue-50 dark:!bg-surface-800 dark:hover:!bg-surface-700"
                            >
                                <Loader2 v-if="isTranscribing" :size="16" class="animate-spin mr-2" />
                                <MessagesSquare v-else :size="16" class="mr-1" />
                                {{ isTranscribing ? 'Transcrevendo...' : 'Transcrever' }}
                            </Button>
                            <Button
                                @click="transcribeAndGenerateDocument"
                                :disabled="!selectedFile || isTranscribing || loadingTranscribeAndGenerate || isAsyncProcessing"
                                v-tooltip.top="isLimitReached ? 'Você atingiu o limite mensal. Faça upgrade para continuar.' : null"
                                class="transcribe-and-generate-button !bg-gradient-to-br !from-blue-500 !to-blue-700 !border-none !text-white !rounded-lg font-semibold hover:!from-blue-600 hover:!to-blue-800"
                            >
                                <Loader2 v-if="loadingTranscribeAndGenerate" :size="16" class="animate-spin mr-2" />
                                <FilePlus v-else :size="16" class="mr-1" />
                                {{ loadingTranscribeAndGenerate ? 'Transcrevendo...' : 'Transcrever e gerar documento' }}
                            </Button>
                        </div>
                        <button
                            v-tooltip.top="{
                                value: `<span class='text-sm'><u>Transcrever</u>: exibe o texto da consulta nesta tela. Você poderá gerar o documento clínico em seguida.</span>\n
                                    <span class='text-sm'><u>Transcrever e gerar documento</u>: cria o documento automaticamente. A transcrição poderá ser vista nos detalhes do documento.</span>`,
                                escape: false,
                                showDelay: 300
                            }"
                            class="flex items-center justify-center rounded-full border-none text-surface-400 hover:text-surface-600 dark:text-surface-500 dark:hover:text-surface-300 transition-colors"
                        >
                            <HelpCircle :size="15" />
                        </button>
                    </div>
                </div>
            </div>
            <TranscriptConversation
                :transcriptions="transcriptions"
                :is-transcribing="isTranscribing"
                :dialog-clear="dialogClear"
                :loading-finish="loadingFinish"
                :is-async-processing="isAsyncProcessing"
                :processing-stage-label="asyncStatus"
                :processing-kind="processingKind"
                @clear="dialogClear = true"
                @finish="finishConversation"
            />
        </div>
        <ChangeInputMode 
            :active="dialogChangeInputMode" 
            :loading="dialogLoading" 
            @close="dialogChangeInputMode = false" 
            @confirm="confirmChangeInputMode"
        />
        <ClearTranscription 
            :active="dialogClear"
            :loading="dialogLoading"
            @close="dialogClear = false" 
            @confirm="confirmClearTranscription"
        />
        <Signature 
            v-model:visible="showSignatureModal"
            :loading="signatureLoading"
            @close="handleSignatureClose"
            @subscribe="handleSignatureSubscribe"
        />

        <TourGuide
            v-if="isRecordMode()"
            tour-type="recording"
            :show-tour-button="false"
            :auto-start="true"
            @tour-complete="onTourComplete"
        />
        <UpgradeReminderToast
            :visible="showUpgradeToast"
            :remaining="parseInt(userStore.remaining)"
            @close="showUpgradeToast = false"
            @upgrade="handleToastUpgrade"
        />
    </section>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { Upload, FileVolume, Loader2, FilePlus, MessagesSquare, HelpCircle } from 'lucide-vue-next';
import { AnamneseService } from '@/service/AnamneseService';
import { TranscriptsService } from '@/service/TranscriptsService';
import { SelectOptionsService } from '@/service/SelectOptionsService';
import { UserService } from '@/service/UserService'
import { useShowToast } from '@/utils/useShowToast';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute, onBeforeRouteLeave } from "vue-router";
import { useHelpers } from '@/utils/helper';
import { useUserStore } from '@/stores/userStore'
import { AUDIO_CONFIG } from '@/utils/constants'
import { useTranscriptProcessing, persistInFlightTranscript, readInFlightTranscript, clearInFlightTranscript } from '@/composables/useTranscriptProcessing'
import UpgradeReminderToast from '@/components/UploadPage/UpgradeReminderToast.vue'
import UpgradeBanner from '@/components/UploadPage/UpgradeBanner.vue'

const router = useRouter();
const route = useRoute();
const { t, te } = useI18n();
const { showSuccess, showError, showAttention } = useShowToast();
const { formatSize, getNextMonthResetDate } = useHelpers();
const userStore = useUserStore()

// FE-R1-03/04 (ai-vitalfy/action-plans/frontend/R1.md)
const { status: asyncStatus, failureReason: asyncFailureReason, recoverable: asyncRecoverable, timedOut: asyncTimedOut, startPolling, stopPolling } = useTranscriptProcessing()
const ASYNC_TERMINAL_STATUSES = ['completed', 'failed']

// `status` vira 'completed' assim que o poll enxerga o estado terminal —
// antes de handleAsyncCompleted()/handleTranscribeCompleted() terminarem o
// trabalho que ainda depende disso (buscar a conversa, atualizar `remaining`,
// redirecionar). Sem `finalizing`, `isAsyncProcessing` cai para `false` nesse
// meio-tempo e o card de progresso some um frame antes do conteúdo final
// estar pronto — a tela mostra a mensagem padrão de "clique para transcrever"
// (ou a conversa antiga, no fluxo que redireciona) por um instante. Os dois
// handlers ligam `finalizing` como primeira linha, na mesma volta síncrona em
// que `status` muda — sem `await` entre as duas, o Vue nunca chega a
// renderizar o estado intermediário.
const finalizing = ref(false)
const isAsyncProcessing = computed(() => (asyncStatus.value !== null && !ASYNC_TERMINAL_STATUSES.includes(asyncStatus.value)) || finalizing.value)

// FE-R20-04 (ai-vitalfy/action-plans/frontend/R20.md): qual dos três
// disparadores de processamento assíncrono está em curso agora — usado só
// para escolher o conjunto certo de etapas no card de progresso
// (TranscriptConversation.vue). Não confundir com o `kind` persistido por
// persistInFlightTranscript() (só 'transcribe-only'/'generate-document',
// usado para decidir inline vs. redirecionamento ao retomar após reload).
const processingKind = ref('transcribe-and-generate')

const inputMode = ref('record')
const chatTranscription = ref();
const uploader = ref(null)
const selectedFile = ref(null)
const isTranscribing = ref(false)
const loadingFinish = ref(false)
const dialogClear = ref(false)
const dialogLoading = ref(false)
const dialogChangeInputMode = ref(false)
const transcriptions = ref([])
const loadingTemplates = ref(false)
const loadingTypes = ref(false)
const loadingTranscribeAndGenerate = ref(false)
const endConversationTime = ref('')
const dropdownTemplates = ref([]);
const dropdownTypes = ref([]);
const errorMessage = ref(false);
const fileSize = ref('')
const form = ref({
    patient: '',
    template_id: null,
    type_id: null
})
const submitBtn = ref(null)
const pendingInputMode = ref(null);
const showSignatureModal = ref(false)
const signatureLoading = ref(false)
const showUpgradeToast = ref(false)
const bannerDismissed = ref(false)

const isLimitReached = computed(() => parseInt(userStore.remaining) <= 0 && userStore.plan === 'Free')
const showWarningBanner = computed(() => parseInt(userStore.remaining) === 1 && userStore.plan === 'Free' && !bannerDismissed.value)
const showCriticalBanner = computed(() => parseInt(userStore.remaining) <= 0 && userStore.plan === 'Free')

const scrollAfterRecordingStart = () => {
    nextTick(() => {
        submitBtn.value?.$el?.scrollIntoView({ behavior: 'smooth', block: 'end' })
    })
}

const openFileDialog = () => {
    if (transcriptions.value.length > 0) {
        showAttention('Atenção', 'Por favor, limpe a transcrição antes de enviar um novo arquivo.', 5000)
        return;
    }
    uploader.value?.choose();
}

const validateAudioFile = async (file) => {
    if (!file) {
        showError('Erro', 'Nenhum arquivo selecionado.', 3000)
        return false
    }

    const extension = file.name.split('.').pop()?.toLowerCase()

    if (!AUDIO_CONFIG.ALLOWED_EXTENSIONS.includes(extension)) {
        showError('Arquivo inválido', `Formatos permitidos: ${AUDIO_CONFIG.ALLOWED_EXTENSIONS.join(', ')}`, 5000)
        return false
    }

    if (file.size > AUDIO_CONFIG.MAX_FILE_SIZE) {
        showError('Arquivo muito grande', `O arquivo deve ter no máximo ${formatSize(AUDIO_CONFIG.MAX_FILE_SIZE)}.`, 5000)
        return false
    }

    try {
        const duration = await getAudioDuration(file)
        if (duration > AUDIO_CONFIG.MAX_RECORDING_DURATION) {
            showError('Arquivo muito longo', `O áudio deve ter no máximo ${AUDIO_CONFIG.MAX_RECORDING_DURATION / 60} minutos.`, 5000)
            return false
        }
    } catch (error) {
        console.error('Erro ao verificar duração do áudio:', error)
    }

    return true
}

const getAudioDuration = (file) => {
    return new Promise((resolve, reject) => {
        const audio = new Audio()
        const objectUrl = URL.createObjectURL(file)
        
        audio.addEventListener('loadedmetadata', () => {
            URL.revokeObjectURL(objectUrl)
            resolve(audio.duration)
        })
        
        audio.addEventListener('error', () => {
            URL.revokeObjectURL(objectUrl)
            reject(new Error('Falha ao carregar os metadados de áudio'))
        })
        
        audio.src = objectUrl
    })
}

const onFileSelect = async (event) => {
    const file = event.files[0]

    if (!(await validateAudioFile(file))) {
        uploader.value?.clear()
        return
    }

    selectedFile.value = file
}

const removeFile = () => {
    selectedFile.value = null;
    uploader.value?.clear();
};

const confirmClearTranscription = () => {
    dialogClear.value = false
    clearTranscriptionData()
};

const clearTranscriptionData = () => {
    removeFile();
    transcriptions.value = [];
    chatTranscription.value = null;
    fileSize.value = ''
    endConversationTime.value = ''
    transcriptId.value = '' 
}

const transcriptId = ref()
// FE-R19-02 (ai-vitalfy/action-plans/frontend/R19.md): com a flag ligada, o
// back responde 202 e a conversa ainda não existe — o front acompanha via
// startAsyncTranscribeTracking() e, ao concluir, renderiza a conversa
// inline (sem redirecionar, ao contrário do fluxo irmão de "gerar
// documento"). Com a flag desligada, 201 mantém o comportamento síncrono
// de sempre.
const transcribeAudio = async () => {
    if (!validateForm()) return;
    if (!hasSelectedFile()) return;
    if (isLimitReached.value) {
        showSignatureModal.value = true
        return
    }

    isTranscribing.value = true;
    enableUnloadWarning()

    const formData = new FormData();
    formData.append('audio', selectedFile.value);

    const { patient, template_id: template, type_id: type } = form.value;
    formData.append('patient', patient);
    formData.append('template', template);
    formData.append('type', type);

    const fileName = selectedFile.value.name;

    try {
        const response = await TranscriptsService.store(formData);
        disableUnloadWarning()

        // SH-R19-01: o front descobre o modo pelo status code da própria
        // resposta — nunca por variável de build (mesmo padrão de
        // transcribeAndGenerateDocument()).
        if (response.status === 202) {
            selectedFile.value = null;
            uploader.value?.clear();
            startAsyncTranscribeTracking(response.data.transcript_id, fileName);
            return;
        }

        const transcript = response.data.transcript
        transcriptId.value = transcript.id

        const processedTranscription = processDeepgramResultAndCreateChatDesign(transcript.conversation, fileName);

        chatTranscription.value = processedTranscription.utterances;
        transcriptions.value.unshift(processedTranscription);

        setUsageOnStorage(response.data.remaining)
        triggerUpgradeToastIfNeeded(response.data.remaining)

        selectedFile.value = null;
        uploader.value?.clear();

        showSuccess(t('notifications.titles.success'), t('notifications.messages.transcriptionGeneratedSuccessfully'), 3000);
    } catch (error) {
        disableUnloadWarning()
        handleTranscriptRequestError(error);
    } finally {
        isTranscribing.value = false;
    }
};

const setUsageOnStorage = (remaining) => {
    if (remaining == null) return;
    userStore.remaining = remaining
    localStorage.setItem('remaining', remaining)
}

const triggerUpgradeToastIfNeeded = (remaining) => {
    if (parseInt(remaining) <= 2 && parseInt(remaining) > 0 && userStore.plan === 'Free') {
        showUpgradeToast.value = true
    }
}

const handleToastUpgrade = () => {
    showUpgradeToast.value = false
    showSignatureModal.value = true
}

// FE-R1-02 (ai-vitalfy/action-plans/frontend/R1.md): cobre só a janela em
// que a requisição HTTP ainda está em voo. Se a resposta for 202
// (assíncrono), o trabalho já está seguro no servidor e a navegação é
// liberada — FE-R1-03 assume o acompanhamento a partir daí. Se for 200
// (síncrono), a resposta só chega depois de tudo pronto, então o aviso já
// não faz mais sentido nesse ponto.
let unloadWarningActive = false
const beforeUnloadHandler = (event) => {
    event.preventDefault()
    event.returnValue = ''
}
const enableUnloadWarning = () => {
    if (unloadWarningActive) return
    unloadWarningActive = true
    window.addEventListener('beforeunload', beforeUnloadHandler)
}
const disableUnloadWarning = () => {
    if (!unloadWarningActive) return
    unloadWarningActive = false
    window.removeEventListener('beforeunload', beforeUnloadHandler)
}
onBeforeRouteLeave(() => {
    if (unloadWarningActive) {
        return window.confirm(t('notifications.messages.leaveWhileProcessingConfirm'))
    }
})
onBeforeUnmount(disableUnloadWarning)

// FE-R20-01 (ai-vitalfy/action-plans/frontend/R20.md): a cota só é debitada
// quando o processamento assíncrono chega a `completed` — o corpo do 202
// nunca traz `remaining` atualizado (ver TranscriptService::enqueueGenerateDocument()),
// então é aqui, no estado terminal, que o valor precisa ser buscado de novo.
const handleAsyncCompleted = async (data) => {
    // Mantém o card de progresso na tela até o redirecionamento acontecer de
    // fato — sem isso, a conversa antiga (nunca limpa por este fluxo) volta a
    // aparecer por um instante assim que `status` chega a 'completed'. Não
    // precisa voltar a `false`: a navegação abaixo desmonta o componente.
    finalizing.value = true
    if (userStore.userId) clearInFlightTranscript(userStore.userId)
    await userStore.getUserInfo()
    triggerUpgradeToastIfNeeded(userStore.remaining)
    showSuccess(t('notifications.titles.success'), t('notifications.messages.documentGeneratedSuccessfully'), 3000);
    redirectTo(data.transcript_id);
}

const handleAsyncFailed = (data) => {
    if (userStore.userId) clearInFlightTranscript(userStore.userId)
    const message = data.failure_reason || t('notifications.messages.generateDocumentFailedDefault')
    showError(t('notifications.titles.error'), message, 10000);
}

const handleAsyncNotFound = () => {
    if (userStore.userId) clearInFlightTranscript(userStore.userId)
}

const handleAsyncTimeout = () => {
    showAttention(t('notifications.titles.warning'), t('notifications.messages.processingDelayedResumed'), 8000);
}

// FE-R19-03: usado pelos dois pontos de entrada de "Finalizar e gerar
// insights" (finishConversation() aqui embaixo) — ao concluir, redireciona
// para o documento, igual ao fluxo irmão de R1.
//
// FE-R20-04: `kind` diz ao card de progresso quais etapas mostrar —
// 'transcribe-and-generate' (default, pipeline completo de R1: pending →
// transcribing → generating) para transcribeAndGenerateDocument(), ou
// 'generate-only' (só a etapa de documento, a conversa já existe) para
// finishConversation().
const startAsyncTracking = (id, kind = 'transcribe-and-generate') => {
    if (userStore.userId) persistInFlightTranscript(userStore.userId, id, 'generate-document')
    processingKind.value = kind
    startPolling(id, {
        onCompleted: handleAsyncCompleted,
        onFailed: handleAsyncFailed,
        onNotFound: handleAsyncNotFound,
        onTimeout: handleAsyncTimeout,
    })
}

// FE-R19-02: ao contrário de startAsyncTracking() acima, aqui não há
// documento — ao concluir, busca a conversa e a renderiza inline, sem
// navegação. `fileName` é só para exibição (mesmo campo que o caminho
// síncrono já usa); depois de um reload não temos mais o nome do arquivo
// original (não faz parte da dica persistida), então a retomada passa ''.
const handleTranscribeCompleted = async (data, fileName) => {
    // Mesmo raciocínio de handleAsyncCompleted(): mantém o card de progresso
    // até a conversa estar de fato pronta para renderizar — sem isso,
    // `isAsyncProcessing` cai assim que `status` chega a 'completed' e, como
    // `transcriptions` ainda está vazio nesse instante, a mensagem padrão de
    // "clique para transcrever" pisca antes da conversa aparecer. Aqui
    // *precisa* voltar a `false` (no `finally`) — ao contrário do fluxo que
    // redireciona, este fica na mesma tela.
    finalizing.value = true
    if (userStore.userId) clearInFlightTranscript(userStore.userId)

    try {
        const result = await TranscriptsService.getConversations(data.transcript_id)
        transcriptId.value = data.transcript_id

        const processedTranscription = processDeepgramResultAndCreateChatDesign(result.conversation, fileName ?? '');
        chatTranscription.value = processedTranscription.utterances;
        transcriptions.value.unshift(processedTranscription);

        // FE-R20-01: a cota só é debitada quando o status chega a
        // `completed` — é aqui, não no 202, que `remaining` precisa ser
        // buscado de novo.
        await userStore.getUserInfo()
        triggerUpgradeToastIfNeeded(userStore.remaining)

        showSuccess(t('notifications.titles.success'), t('notifications.messages.transcriptionGeneratedSuccessfully'), 3000);
    } catch (error) {
        showError(t('notifications.titles.error'), t('notifications.messages.transcriptConversationLoadError'), 8000);
    } finally {
        finalizing.value = false
    }
}

const startAsyncTranscribeTracking = (id, fileName) => {
    if (userStore.userId) persistInFlightTranscript(userStore.userId, id, 'transcribe-only')
    processingKind.value = 'transcribe-only'
    startPolling(id, {
        onCompleted: (data) => handleTranscribeCompleted(data, fileName),
        onFailed: handleAsyncFailed,
        onNotFound: handleAsyncNotFound,
        onTimeout: handleAsyncTimeout,
    })
}

// SH-R19-01 (decisão 4): a dica de retomada agora carrega `kind` — escolhe
// aqui o onCompleted certo (renderizar inline vs. redirecionar) em vez de
// assumir sempre 'generate-document' como antes de R19.
const resumeAsyncTrackingIfNeeded = async () => {
    if (!userStore.userId) return
    const hint = readInFlightTranscript(userStore.userId)
    if (!hint) return

    try {
        const data = await TranscriptsService.getTranscriptStatus(hint.transcriptId)
        if (data.status === 'completed' || data.status === 'failed') {
            clearInFlightTranscript(userStore.userId)
            return
        }

        if (hint.kind === 'transcribe-only') {
            startAsyncTranscribeTracking(hint.transcriptId, '')
        } else {
            // FE-R20-04: a dica persistida só distingue 'transcribe-only' de
            // 'generate-document' (SH-R19-01) — não sabe dizer, depois de um
            // reload, se era transcribeAndGenerateDocument() (pipeline
            // completo) ou finishConversation() (só documento). Assume o
            // default 'transcribe-and-generate' (superset de etapas): se a
            // retomada era na verdade 'generate-only', o efeito colateral é
            // as etapas de recebimento/transcrição do áudio aparecerem
            // riscadas como "já concluídas" (nunca como etapa atual) do
            // primeiro poll até o fim, em vez de simplesmente não existir —
            // cosmético, não afeta o resultado; limitação conhecida, não
            // resolvida por este plano.
            startAsyncTracking(hint.transcriptId)
        }
    } catch (error) {
        if (error.response?.status === 404) {
            clearInFlightTranscript(userStore.userId)
        }
    }
}

// FE-R1-01/FE-R19-01: distingue os modos de falha característicos do R1
// (timeout/504/524) de cota, concorrência, arquivo inválido e erro
// genérico — antes, tudo virava "Erro ao transcrever o áudio.", inclusive
// quando o processamento continuava no servidor e o usuário só precisava
// esperar. Compartilhado entre transcribeAudio() ("Transcrever") e
// transcribeAndGenerateDocument() ("Transcrever e gerar documento") — os
// dois fluxos batem em /transcripts (com ou sem geração de documento) e
// tratam os mesmos códigos de status, então a lógica não é duplicada.
const handleTranscriptRequestError = (error) => {
    const status = error.response?.status

    if (status === 429) {
        showSignatureModal.value = true
        showAttention(t('notifications.titles.warning'), t('notifications.messages.transcriptionLimitReached'), 5000);
        return
    }

    if (status === 409) {
        // R5 (ai-vitalfy/action-plans/frontend/R5.md, FE-R5-02): este 409
        // também acontece quando o e-mail não está verificado — nesse caso
        // o interceptor global (services/axios.js) já abriu o modal de
        // verificação; mostrar também o toast de "processamento em
        // andamento" aqui seria uma mensagem errada por cima da certa.
        if (error.response?.data?.email_verification_required) {
            return
        }

        showAttention(t('notifications.titles.warning'), t('notifications.messages.concurrentProcessing'), 6000);
        return
    }

    if (status === 422 && error.response?.data?.errors?.audio) {
        showError(t('notifications.titles.error'), error.response.data.errors.audio[0] || t('notifications.messages.audioTooLarge'), 6000);
        return
    }

    const isTimeout = error.code === 'ECONNABORTED' || [502, 504, 524].includes(status)
    if (isTimeout) {
        showError(t('notifications.titles.error'), t('notifications.messages.processingDelayed'), 12000);
        return
    }

    showError(t('notifications.titles.error'), t('notifications.messages.generateDocumentGenericError'), 10000);
}

const transcribeAndGenerateDocument = async () => {
    if (!validateForm()) return;
    if (!hasSelectedFile()) return;
    if (isLimitReached.value) {
        showSignatureModal.value = true
        return
    }

    loadingTranscribeAndGenerate.value = true;
    enableUnloadWarning()

    const formData = new FormData();
    formData.append('audio', selectedFile.value);

    const { patient, template_id: template, type_id: type } = form.value;
    formData.append('patient', patient);
    formData.append('template', template);
    formData.append('type', type);

    try {
        const response = await TranscriptsService.storeAndGenerateDocument(formData);
        disableUnloadWarning()

        // SH-R1-02: o front descobre o modo pelo status code da própria
        // resposta — nunca por variável de build.
        if (response.status === 202) {
            loadingTranscribeAndGenerate.value = false
            selectedFile.value = null
            uploader.value?.clear()
            startAsyncTracking(response.data.transcript_id, 'transcribe-and-generate')
            return
        }

        const result = response.data;
        if (result) {
            setUsageOnStorage(response.data.remaining)

            loadingTranscribeAndGenerate.value = false
            showSuccess(t('notifications.titles.success'), t('notifications.messages.documentGeneratedSuccessfully'), 3000);

            redirectTo(result.document.transcript_id);
        }
    } catch (error) {
        disableUnloadWarning()
        loadingTranscribeAndGenerate.value = false
        handleTranscriptRequestError(error)
    }
};

// TODO: VER A NECESSIDADE DESSA FUNÇÃO E EXCLUIR SE DESNECESSÁRIO
const processDeepgramResultAndCreateChatDesign = (conversation, fileName) => {
    return {
        fileName: fileName,
        timestamp: new Date().toLocaleTimeString('pt-BR', { 
            hour: '2-digit', 
            minute: '2-digit' 
        }),
        utterances: conversation
    };
};

// FE-R19-03: com a flag ligada, a resposta é 202 e o acompanhamento
// (startAsyncTracking) redireciona ao concluir — mesmo destino de hoje,
// mesmo onCompleted que o fluxo irmão de R1 já usa. Com a flag desligada,
// 201 mantém o redirecionamento imediato de sempre.
const finishConversation = async () => {
    loadingFinish.value = true;
    enableUnloadWarning()

    try {
        const payload = buildPayload();
        const response = await AnamneseService.generator(payload);
        disableUnloadWarning()

        if (response.status === 202) {
            startAsyncTracking(response.data.transcript_id, 'generate-only');
            return;
        }

        redirectTo(response.data.transcript_id);
    } catch (error) {
        disableUnloadWarning()
        // FE-R19-01: AnamneseService.generator() agora relança de verdade —
        // uma falha do Groq chega aqui em vez de terminar em toast de
        // sucesso falso.
        // R5: e-mail não verificado já vira modal pelo interceptor global —
        // não duplicar com este toast genérico.
        if (!error.response?.data?.email_verification_required) {
            showError(t('notifications.titles.error'), t('notifications.messages.anamnesisGeneratingError'), 8000);
        }
    } finally {
        loadingFinish.value = false;
    }
};

const confirmInputModeChangeIfFileSelected = (newValue) => {
    if (!hasSelectedFile() && !hasTranscriptions()) {
        inputMode.value = newValue
        return
    }
    
    pendingInputMode.value = newValue;
    dialogChangeInputMode.value = true
}

const confirmChangeInputMode = () => {
    dialogChangeInputMode.value = false;
    inputMode.value = pendingInputMode.value;
    pendingInputMode.value = null;
    clearTranscriptionData();
}

const handleRecordedFile = (file) => {
    selectedFile.value = file;
};

const buildPayload = () => {
    const { patient, template_id: template, type_id: type } = form.value;
    return {
        conversation: chatTranscription.value,
        patient,
        template,
        transcript_id: transcriptId.value
    };
};

// TODO: USAR VALIDAÇÃO COM ZOD
const errorMessagePatient = ref(false)
const errorMessageType = ref(false)
const validateForm = () => {
    errorMessage.value = false
    errorMessagePatient.value = false
    errorMessageType.value = false

    let isValid = true

    if (!form.value.template_id) {
        errorMessage.value = true
        isValid = false
    }

    if (!form.value.patient) {
        errorMessagePatient.value = 'O nome do paciente é obrigatório.'
        isValid = false
    } else if (form.value.patient.length > 255) {
        errorMessagePatient.value = 'O nome do paciente deve ter no máximo 255 caracteres.'
        isValid = false
    }

    if (!form.value.type_id) {
        errorMessageType.value = true
        isValid = false
    }

    if (!isValid) {
        showError(
            t('notifications.titles.error'),
            "Por favor, verifique os campos do formulário.",
            4000
        )
    }

    return isValid
}

const redirectTo = (id) => {
    router.push({
        name: 'transcriptsShow',
        params: { id: id },
        query: { type: 'new' }
    });
}

const isRecordMode = () => inputMode.value === 'record';
const hasSelectedFile = () => selectedFile.value != null;
const hasTranscriptions = () => transcriptions.value.length > 0;

async function loadTemplates() {
    loadingTemplates.value = true
    try {
        dropdownTemplates.value = await SelectOptionsService.getTemplatesMinimal()
        form.value.template_id = Number(localStorage.getItem("favorite")) || null

        const templateFromUrl = route.query.template

        if (templateFromUrl) {
            form.value.template_id = Number(templateFromUrl)
        }
    } catch (error) {
        console.error(error)
    } finally {
        loadingTemplates.value = false
    }
}

async function loadTypes() {
    loadingTypes.value = true
    try {
        dropdownTypes.value = await SelectOptionsService.getTypesMinimal()
        form.value.type_id = Number(localStorage.getItem("favoriteType")) || null
    } catch (error) {
        console.error(error)
    } finally {
        loadingTypes.value = false
    }
}

const handleSignatureClose = () => {
    showSignatureModal.value = false
}

const handleSignatureSubscribe = async (plan) => {
    signatureLoading.value = true
    
    try {
        const { SubscriptionService } = await import('@/service/SubscriptionService')
        
        const response = await SubscriptionService.createCheckout(plan)
        
        window.location.href = response.url
    } catch (error) {
        showError(t('notifications.titles.error'), 'Erro ao iniciar assinatura. Tente novamente!', 3000)
    } finally {
        signatureLoading.value = false
    }
}

const onTourComplete = async () => {
    try {
        await UserService.update({ recording_tour_completed: true });

        userStore.recordingTourCompleted = true;
    } catch (error) {
        console.error('Error updating tour completion:', error);
    }
};

onMounted(() => {
    loadTemplates();
    loadTypes();
    resumeAsyncTrackingIfNeeded();
});
</script>

<style scoped>
@media (min-width: 990px) and (max-width: 1220px) {
    .layout-static:not(.layout-static-inactive) .transcribe-actions {
        flex-direction: column;
        flex: 1 1 0%;
    }
}
::v-deep(.p-fileupload-header) {
    padding: 0 !important;
    margin: 0 !important;
    height: 0 !important;
    border: none !important;
}
::v-deep(.p-fileupload-content) {
    padding: 0 !important;
    border: none !important;
}
::v-deep(.p-fileupload-advanced) {
    border: none !important;
}
</style>