<template>
    <section>
        <div class="mb-3 py-3">
            <h1 class="text-3xl font-bold">Histórico de atendimentos</h1>
            <p class="my-1 text-lg text-surface-500">Visualize e gerencie seus atendimentos e documentos clínicos</p>
        </div>

        <div class="card mb-4">
            <div class="flex flex-wrap gap-3">
                <IconField class="flex-1 min-w-[200px]">
                    <InputIcon class="pi pi-search" />
                    <InputText v-model="username" placeholder="Buscar por paciente ou conteúdo..." class="w-full" />
                </IconField>
                <div class="relative w-full md:w-auto">
                    <DatePicker
                        v-model="date"
                        :maxDate="today"
                        showIcon
                        fluid
                        :manualInput="false"
                        placeholder="Data de atendimento"
                        class="w-full"
                    />
                    <i
                        v-if="date"
                        class="pi pi-times absolute right-12 top-1/2 -translate-y-1/2 cursor-pointer text-surface-400 hover:text-red-500 transition"
                        @click="clearDate"
                    />
                </div>
                <Select
                    v-model="selectedType"
                    :options="dropdownTypes"
                    :loading="loadingTypes"
                    optionValue="id"
                    optionLabel="type"
                    placeholder="Tipo de atendimento"
                    class="w-full md:w-auto"
                    showClear
                />
            </div>
        </div>

        <div v-if="!loading || transcripts.length > 0" class="flex items-center justify-between mb-3">
            <span class="text-xs text-surface-400">
                {{ transcripts.length > 0 ? `${transcripts.length} atendimento${transcripts.length !== 1 ? 's' : ''} encontrado${transcripts.length !== 1 ? 's' : ''}` : '' }}
            </span>
            <span v-if="username" class="text-xs text-surface-400">
                Resultados para "<strong>{{ username }}</strong>"
            </span>
        </div>

        <div v-if="loading && transcripts.length === 0" class="card !p-0 overflow-hidden">
            <div v-for="i in 8" :key="i" class="flex items-center gap-x-4 px-4 py-3 border-b border-surface-100 last:border-0 dark:border-surface-600">
                <Skeleton shape="circle" size="2.25rem" class="flex-shrink-0" />
                <div class="flex-1 flex flex-col gap-y-2">
                    <Skeleton width="35%" height="13px" />
                    <Skeleton width="55%" height="11px" />
                    <Skeleton width="25%" height="11px" />
                </div>
                <div class="hidden sm:flex items-end gap-x-2">
                    <Skeleton width="80px" height="20px" class="rounded-full" />
                    <Skeleton width="60px" height="20px" class="rounded-full" />
                </div>
                <div class="hidden md:flex gap-x-2">
                    <Skeleton width="28px" height="28px" class="rounded-md" />
                    <Skeleton width="28px" height="28px" class="rounded-md" />
                    <Skeleton width="28px" height="28px" class="rounded-md" />
                </div>
            </div>
        </div>

        <div v-else-if="!loading && transcripts.length === 0" class="card flex flex-col items-center text-center py-16 gap-y-3">
            <div class="w-12 h-12 rounded-full bg-surface-100 flex items-center justify-center mb-1 dark:bg-surface-700/50">
                <FileAudio :size="22" class="text-surface-400 dark:text-surface-500" />
            </div>
            <p class="text-[13px] font-medium text-surface-700 dark:text-surface-300">Nenhum atendimento encontrado</p>
            <p class="text-xs text-surface-400 max-w-xs dark:text-surface-400">
                {{ username || date || selectedType
                    ? 'Tente ajustar os filtros para encontrar o que procura.'
                    : 'Inicie um novo atendimento para gerar automaticamente o documento clínico e os insights.' }}
            </p>
            <router-link
                v-if="!username && !date && !selectedType"
                :to="{ name: 'upload' }"
                class="mt-2 inline-flex items-center gap-x-2 px-4 py-2 rounded-lg bg-primary-600 text-white text-sm hover:bg-primary-700 transition-colors"
            >
                <Mic :size="15" />
                Novo Atendimento
            </router-link>
        </div>

        <div v-else class="card !p-0 overflow-hidden">
            <div
                v-for="(item, idx) in transcripts"
                :key="item.id"
                :class="[
                    'flex items-start gap-x-3 px-4 py-3 hover:bg-surface-50 transition-colors duration-150 group dark:hover:bg-surface-700/30',
                    idx < transcripts.length - 1 ? 'border-b border-surface-100 dark:border-surface-600' : '',
                    item.documentNotGenerated ? ' border-l-4 border-l-yellow-400 dark:border-l-yellow-500 bg-yellow-200/10 dark:bg-yellow-900/30 hover:bg-yellow-300/10 dark:hover:bg-yellow-800/30' : ''
                ]"
            >
                <div :class="`w-9 h-9 rounded-full flex items-center justify-center text-[11.5px] font-semibold flex-shrink-0 mt-0.5 ${getPatientAvatar(item.patient)}`">
                    {{ getInitials(item.patient) }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-x-3">
                        <div class="min-w-0">
                            <p class="text-[13.5px] font-semibold text-surface-800 leading-tight truncate dark:text-surface-200">
                                {{ item.patient }}
                                <Tag
                                    v-if="item.documentNotGenerated"
                                    severity="warn" 
                                    value="Só transcrição" 
                                    rounded 
                                    class="!text-xs !py-0 ml-2" 
                                />
                            </p>
                            <p class="text-[12.4px] text-surface-500 mt-0.5 line-clamp-1 dark:text-surface-400">{{ item.description }}</p>
                        </div>

                        <div class="hidden sm:flex items-center gap-x-2 flex-shrink-0 mt-0.5">
                            <div v-if="item.documentNotGenerated">
                                <Button
                                    text
                                    size="small"
                                    @click="openGenerateModal(item)"
                                    class="!bg-blue-500 !text-white !font-semibold hover:!bg-blue-600 transition-colors dark:!bg-blue-700 dark:hover:!bg-blue-800 dark:!text-surface-200"
                                >
                                    <FileText :size="16" class="text-white dark:text-surface-200" /> Gerar documento clínico
                                </Button>
                            </div>
                            <div v-if="!item.documentNotGenerated" :class="`py-1 px-2 rounded-full font-semibold flex text-xs items-center justify-center ${item.color}`">
                                {{item.template}}
                            </div>
                            <Tag  v-if="!item.documentNotGenerated" :severity="item.type == 'Urgente' ? 'danger' : 'secondary'" :value="item.type" rounded class="!text-xs !py-0.5" />
                        </div>
                    </div>

                    <div class="flex items-center flex-wrap gap-x-4 gap-y-0.5 mt-1.5">
                        <span class="flex items-center gap-x-1 text-[12px] text-surface-400">
                            <Calendar :size="12" class="text-surface-500" />
                            {{ formatDate(item.created_at) }}
                        </span>
                        <span class="flex items-center gap-x-1 text-[12px] text-surface-400">
                            <Timer :size="12" class="text-orange-400" />
                            {{ item.time }}
                        </span>
                        <span v-if="item.size != null" class="flex items-center gap-x-1 text-[12px] text-surface-400">
                            <FileAudio :size="12" />
                            {{ formatSize(item.size) }}
                        </span>
                        <!-- Tags mobile -->
                        <div class="flex flex-col gap-y-1 sm:hidden gap-x-1.5 mt-0.5">
                            <div :class="`py-1 px-2 rounded-full font-semibold flex text-xs items-center justify-center ${item.color}`">
                                {{item.template}}
                            </div>
                            <Tag severity="secondary" :value="item.type" rounded class="!text-xs !py-0" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-0.5 max-[495px]:flex-col flex-shrink-0 opacity-60 group-hover:opacity-100 transition-opacity">
                    <Button
                        v-if="!item.documentNotGenerated"
                        text
                        rounded
                        size="small"
                        @click="goToDetail(item)"
                        v-tooltip.top="'Visualizar'"
                    >
                        <Eye :size="20" class="text-surface-700 dark:text-surface-300" />
                    </Button>
                    <Button
                        text
                        rounded
                        size="small"
                        @click="openEditDialog(item)"
                        v-tooltip.top="'Renomear'"
                    >
                        <Pencil :size="20" class="text-surface-700 dark:text-surface-300" />
                    </Button>
                    <Button
                        text
                        rounded
                        size="small"
                        severity="danger"
                        @click="deleteConfirmation(item)"
                        v-tooltip.top="'Excluir'"
                    >
                        <Trash :size="20" />
                    </Button>
                </div>
            </div>

            <div v-if="loading && transcripts.length > 0" class="flex items-center justify-center gap-x-2 py-4 border-t border-surface-100">
                <Loader2 :size="16" class="animate-spin text-surface-400" />
                <span class="text-xs text-surface-400">Carregando mais atendimentos...</span>
            </div>
        </div>

        <div v-if="hasReachedEnd && transcripts.length > 0" class="text-center py-4">
            <span class="text-[11.5px] text-surface-400">Todos os atendimentos foram carregados</span>
        </div>

        <div ref="loadMoreTrigger" class="h-4" />

        <GenerateDocument 
            v-if="showGenerate"
            v-model:visible="showGenerate"
            :template="selectedTranscriptToGenerated"
        />
        <EditTranscriptionName
            :active="dialogEditName"
            :item="selectedItem"
            :loading="loadingRename"
            @close="dialogEditName = false"
            @confirm="renameTranscript"
        />
        <DeleteConfirmation
            :active="dialogConfirmation"
            :item="selectedItem"
            :loading="dialogLoading"
            @close="dialogConfirmation = false"
            @confirm="deleteTranscript"
        />
    </section>
</template>

<script setup>
import { ref, nextTick, onMounted, onUnmounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { TranscriptsService } from '@/service/TranscriptsService';
import { useShowToast } from '@/utils/useShowToast';
import { Eye, Trash, Mic, Pencil, Calendar, Timer, FileAudio, Loader2, FileText } from 'lucide-vue-next';
import { useHelpers } from '@/utils/helper';
import { useI18n } from 'vue-i18n';
import api from '@/services/axios';

const { t } = useI18n();
const { showSuccess, showError } = useShowToast();
const { formatDate, formatSize, convertSecondsToMinutes, getInitials, getPatientAvatar } = useHelpers();
const router = useRouter();
const today = new Date();

const transcripts = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const hasReachedEnd = ref(false);
const loadMoreTrigger = ref(null);
const observer = ref(null);
const selectedItem = ref({});
const dialogConfirmation = ref(false);
const dialogEditName = ref(false);
const dialogLoading = ref(false);
const loadingRename = ref(false);
const username = ref(null);
const date = ref(null);
const loadingTypes = ref(false);
const selectedType = ref(null);
const dropdownTypes = ref([]);
const showGenerate = ref(false)

const mapperTranscript = (list) =>
    list.map((t) => ({
        ...t,
        size: t.file_size,
        template: t.document?.document_template?.name || '-',
        time: convertSecondsToMinutes(t.end_conversation_time),
        type: t.transcript_type.type,
        color: t.document?.document_template?.category?.color || 'bg-slate-100 text-slate-600',
        description: truncateText(t.description) || 'Documento clínico ainda não gerado',
        documentNotGenerated: !t.document
    }))

function truncateText(text, maxLength = 100) {
    if (!text) return ''
    return text.length > maxLength ? text.slice(0, maxLength) + '...' : text
}

const clearDate = () => { date.value = null }

const fetchTranscripts = async (page = 1, reset = false) => {
    if (loading.value || (hasReachedEnd.value && !reset)) return
    loading.value = true
    try {
        const response = await TranscriptsService.index(page)
        const newTranscripts = mapperTranscript(response.transcripts)
        if (reset) {
            transcripts.value = newTranscripts
            currentPage.value = 1
            hasReachedEnd.value = false
        } else {
            transcripts.value = [...transcripts.value, ...newTranscripts]
        }
        if (newTranscripts.length === 0 || newTranscripts.length < 10) hasReachedEnd.value = true
        currentPage.value = page
    } catch {
        showError('Erro', 'Erro ao carregar os atendimentos', 3000)
    } finally {
        loading.value = false
    }
}

const loadMore = async () => {
    if (!hasReachedEnd.value && !loading.value) await fetchTranscripts(currentPage.value + 1)
}

const setupIntersectionObserver = () => {
    if (!loadMoreTrigger.value) return
    observer.value = new IntersectionObserver(
        (entries) => {
            if (entries[0].isIntersecting && !loading.value && !hasReachedEnd.value) loadMore()
        },
        { rootMargin: '100px' }
    )
    observer.value.observe(loadMoreTrigger.value)
}

const goToDetail = (item) => router.push({ name: 'transcriptsShow', params: { id: item.id } })

const selectedTranscriptToGenerated = ref(null)
const openGenerateModal = (template) => {
    selectedTranscriptToGenerated.value = template
    showGenerate.value = true
}

const openEditDialog = (item) => {
    selectedItem.value = item
    dialogEditName.value = !dialogEditName.value
}

const renameTranscript = async (namePatient) => {
    loadingRename.value = true
    try {
        const response = await TranscriptsService.update(selectedItem.value.id, { ...selectedItem.value, patient: namePatient })
        if (response.status === 200) {
            transcripts.value = transcripts.value.map((tr) =>
                tr.id === selectedItem.value.id ? { ...tr, patient: namePatient } : tr
            )
            showSuccess(t('notifications.titles.success'), t('notifications.messages.editSuccessfully'), 3000)
        }
    } catch {
        showError(t('notifications.titles.error'), t('notifications.messages.editError'), 3000)
    } finally {
        loadingRename.value = false
        dialogEditName.value = false
    }
}

const deleteConfirmation = (item) => {
    selectedItem.value = item
    dialogConfirmation.value = !dialogConfirmation.value
}

const deleteTranscript = async (item) => {
    dialogLoading.value = true
    try {
        await TranscriptsService.delete(item.id)
        transcripts.value = transcripts.value.filter((t) => t.id !== item.id)
        dialogConfirmation.value = false
        showSuccess('Sucesso', 'Atendimento excluído', 3000)
    } catch {
        showError('Erro', 'Erro ao excluir atendimento', 3000)
    } finally {
        dialogLoading.value = false
    }
}

const getTypes = async () => {
    loadingTypes.value = true
    try {
        const response = await api.get(`/transcript-types`)
        dropdownTypes.value = response.data
    } catch (error) {
        console.error(error)
    } finally {
        loadingTypes.value = false
    }
}

const filterTranscripts = async (username, date, selectedType) => {
    transcripts.value = []
    loading.value = true
    try {
        const filtered = await TranscriptsService.filterTranscripts(username, date, selectedType)
        transcripts.value = mapperTranscript(filtered)
    } finally {
        loading.value = false
    }
}

let debounceTimer = null
watch([username, date, selectedType],
    ([username, date, selectedType]) => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => filterTranscripts(username, date, selectedType), 500)
}, { immediate: false })

onMounted(async () => {
    await fetchTranscripts(1, true)
    await nextTick()
    setupIntersectionObserver()
    getTypes()
})

onUnmounted(() => { if (observer.value) observer.value.disconnect() })
</script>