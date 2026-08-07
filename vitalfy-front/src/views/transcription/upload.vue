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
                                :disabled="!selectedFile || isTranscribing || loadingTranscribeAndGenerate"
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
                                :disabled="!selectedFile || isTranscribing || loadingTranscribeAndGenerate"
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
                :loading-transcribe-and-generate="loadingTranscribeAndGenerate"
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
import { ref, computed, onMounted, nextTick } from 'vue'
import { Upload, FileVolume, Loader2, FilePlus, MessagesSquare, HelpCircle } from 'lucide-vue-next';
import { AnamneseService } from '@/service/AnamneseService';
import { TranscriptsService } from '@/service/TranscriptsService';
import { SelectOptionsService } from '@/service/SelectOptionsService';
import { UserService } from '@/service/UserService'
import { useShowToast } from '@/utils/useShowToast';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from "vue-router";
import { useHelpers } from '@/utils/helper';
import { useUserStore } from '@/stores/userStore'
import { AUDIO_CONFIG } from '@/utils/constants'
import UpgradeReminderToast from '@/components/UploadPage/UpgradeReminderToast.vue'
import UpgradeBanner from '@/components/UploadPage/UpgradeBanner.vue'

const router = useRouter();
const route = useRoute();
const { t } = useI18n();
const { showSuccess, showError, showAttention } = useShowToast();
const { formatSize, getNextMonthResetDate } = useHelpers();
const userStore = useUserStore()

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
const transcribeAudio = async () => {
    if (!validateForm()) return;
    if (!hasSelectedFile()) return;
    if (isLimitReached.value) {
        showSignatureModal.value = true
        return
    }

    isTranscribing.value = true;

    const formData = new FormData();
    formData.append('audio', selectedFile.value);

    const { patient, template_id: template, type_id: type } = form.value;
    formData.append('patient', patient);
    formData.append('template', template);
    formData.append('type', type);

    TranscriptsService.store(formData)
        .then(response => {
            const transcript = response.data.transcript
            transcriptId.value = transcript.id

            const processedTranscription = processDeepgramResultAndCreateChatDesign(transcript.conversation, selectedFile.value.name);
            
            chatTranscription.value = processedTranscription.utterances;
            transcriptions.value.unshift(processedTranscription);

            setUsageOnStorage(response.data.remaining)
            triggerUpgradeToastIfNeeded(response.data.remaining)

            selectedFile.value = null;
            uploader.value?.clear();

            showSuccess('Sucesso', 'Transcrição gerada com sucesso!', 3000);
        })
        .catch(error => {
            if (error.response?.status === 429) {
                showSignatureModal.value = true
                showAttention('Atenção', 'Limite de transcrições atingido. Faça upgrade para continuar.', 5000);
            } else {
                showError('Erro', 'Erro ao transcrever o áudio.', 3000);
            }
        })
        .finally(() => {
            isTranscribing.value = false;
        });
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

const transcribeAndGenerateDocument = async () => {
    if (!validateForm()) return;
    if (!hasSelectedFile()) return;
    if (isLimitReached.value) {
        showSignatureModal.value = true
        return
    }

    loadingTranscribeAndGenerate.value = true;

    const formData = new FormData();
    formData.append('audio', selectedFile.value);

    const { patient, template_id: template, type_id: type } = form.value;
    formData.append('patient', patient);
    formData.append('template', template);
    formData.append('type', type);

    try {
        const response = await TranscriptsService.storeAndGenerateDocument(formData);
        const result = response.data;

        if (result) {
            setUsageOnStorage(response.data.remaining)

            loadingTranscribeAndGenerate.value = false
            showSuccess('Sucesso', 'Documento gerado com sucesso!', 3000);
            
            redirectTo(result.document.transcript_id);
        }
    } catch (error) {
        loadingTranscribeAndGenerate.value = false
        if (error.response?.status === 429) {
            showSignatureModal.value = true
            showAttention('Atenção', 'Limite de transcrições atingido. Faça upgrade para continuar.', 5000);
        } else {
            showError('Erro', 'Erro ao transcrever o áudio.', 10000);
        }
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

const finishConversation = async () => {
    loadingFinish.value = true;

    try {
        const payload = buildPayload();
        const response = await AnamneseService.generator(payload);

        redirectTo(response.transcript_id);
    } catch (error) {
        showError(t('notifications.titles.error'), t('notifications.messages.anamnesisGeneratingError'), 3000);
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