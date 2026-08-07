<template>
    <Dialog
        :visible="visible"
        @update:visible="(val) => emit('update:visible', val)"
        modal
        :style="{ width: '730px', maxWidth: '95vw' }"
        :pt="{
            header: { class: 'pb-3 border-b border-surface-100 dark:border-surface-700' },
            content: { class: '!p-0' },
        }"
    >
        <template #header>
            <div class="flex items-center gap-x-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 bg-blue-100 dark:bg-blue-950">
                    <ScrollText 
                        :size="18" 
                        class="text-blue-500"
                    />
                </div>
                <div>
                    <p class="text-sm font-semibold text-surface-800 leading-tight dark:text-surface-300">Você está visualizando a transcrição de Florisvaldo</p>
                    <p class="text-xs text-surface-400 mt-0.5">Revise e gere o documento clínico quando estiver pronto</p>
                </div>
            </div>
        </template>

        <div
            v-if="loadingConversations"
            class="flex flex-col items-center justify-center py-14 gap-y-2 text-surface-400 px-6"
        >
            <Loader2 :size="24" class="animate-spin mr-1" />
            <span class="ml-2">Carregando conversa...</span>
        </div>
        
        <div 
            v-else 
            class="flex flex-col text-surface-400 p-6"
        >
            <div class="flex flex-col mb-3">
                <label class="text-surface-600 dark:text-surface-400 mb-1" for="template">Template<span class="text-red-500">*</span></label>
                <Select 
                    id="template" 
                    v-model="template_id" 
                    :options="dropdownTemplates" 
                    filter
                    :loading="loadingTemplates"
                    :class="{ 'p-invalid': errorMessage }"
                    optionValue="id" 
                    optionLabel="name" 
                    placeholder="Selecione" 
                />
            </div>
            <div class=" border-2 border-surface-200 p-2 rounded-lg mb-4 max-h-[600px] overflow-y-auto dark:border-surface-700">
                <div v-for="(utterance, index) in conversations" :key="index" >
                    <div class="rounded-lg p-2">
                        <div
                            v-if="utterance.speaker == 0"
                            class="flex mb-1"
                        >
                            <div>
                                <div class="flex">
                                    <span class="text-xs text-surface-500 dark:text-surface-400">{{ utterance.start }}s — Falante 1</span>
                                </div>
                                <p class="text-surface-800 p-2 rounded-lg bg-surface-100 dark:bg-surface-700 dark:text-surface-200">{{ utterance.text }}</p>
                            </div>
                        </div>
                        <div 
                            v-else 
                            class="flex justify-end mb-1"
                        >
                            <div>
                                <div class="flex">
                                    <span class="text-xs text-surface-500 dark:text-surface-400">{{ utterance.start }}s — Falante 2</span>
                                </div>
                                <p class="text-surface-800 p-2 rounded-lg bg-blue-100 dark:bg-blue-900 dark:text-surface-200">{{ utterance.text }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-x-4">
                <button
                    @click="emit('update:visible', false)"
                    class="px-4 py-2 border text-black rounded-lg hover:bg-gray-100 transition dark:hover:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                >
                    Cancelar
                </button>
                <Button
                    text
                    size="small"
                    class="!bg-blue-500 !text-white !font-semibold hover:!bg-blue-600 transition-colors dark:!bg-blue-700 dark:hover:!bg-blue-800 dark:!text-surface-200"
                    :disabled="loadingFinish"
                    @click="generateClinicalDocument"
                >
                    <Loader2 v-if="loadingFinish" :size="17" class="animate-spin mr-1" />
                    <FileText v-else :size="16" class="text-white dark:text-surface-200" /> 
                     {{ loadingFinish ? 'Gerando...' : 'Gerar documento clínico' }}
                </Button>
            </div>
        </div>
    </Dialog>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { ScrollText, FileText, Loader2 } from 'lucide-vue-next'
import { TranscriptsService } from '@/service/TranscriptsService';
import { AnamneseService } from '@/service/AnamneseService';
import { SelectOptionsService } from '@/service/SelectOptionsService';
import { useShowToast } from '@/utils/useShowToast';
import { useRouter } from "vue-router";

const { showSuccess, showError } = useShowToast();
const router = useRouter();

const props = defineProps({
    visible: Boolean,
    template: Object,
})

const emit = defineEmits(["update:visible"])

const conversations = ref(null)
const loadingConversations = ref(true)
const loadingFinish = ref(false)
const dropdownTemplates = ref([])
const loadingTemplates = ref(false)
const errorMessage = ref(false)
const template_id = ref(null)

const getConversationDetails = async () => {
    loadingConversations.value = true
    try {
        const response = await TranscriptsService.getConversations(props.template.id)
        conversations.value = response.conversation
    } catch (error) {
        showError('Erro', 'Erro ao carregar detalhes da conversa. Por favor, tente novamente.', 4000)
        emit('update:visible', false)
    } finally {
        loadingConversations.value = false
    }
}

const generateClinicalDocument = async () => {
    loadingFinish.value = true;
    let transcript_id = props.template.id

    if(template_id.value === null) {
        errorMessage.value = true
        loadingFinish.value = false;
        showError('Atenção!', "Por favor, informe o template no formulário.", 4000)
        return
    } else {
        errorMessage.value = false
    }
    
    let payload = {
        transcript_id: transcript_id,
        conversation: conversations.value,
        patient: props.template.patient,
        template: template_id.value
    }

    try {
        const response = await AnamneseService.generator(payload);

        showSuccess('Sucesso', 'Documento clínico gerado com sucesso', 4000)
        redirectTo(transcript_id)
    } catch (error) {
        showError('Erro', 'Erro ao gerar documento clínico. Por favor, tente novamente.', 4000)
    } finally {
        loadingFinish.value = false;
        emit('update:visible', false)
    }
}

const loadTemplates = async () => {
    loadingTemplates.value = true
    try {
        dropdownTemplates.value = await SelectOptionsService.getTemplatesMinimal()
    } catch (error) {
        console.error(error)
    } finally {
        loadingTemplates.value = false
    }
}

const redirectTo = (id) => {
    router.push({
        name: 'transcriptsShow',
        params: { id: id },
        query: { type: 'new' }
    });
}

onMounted(async () => {
    await loadTemplates()
    getConversationDetails()
})
</script>