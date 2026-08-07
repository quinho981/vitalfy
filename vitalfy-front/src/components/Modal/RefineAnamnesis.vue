<template>
    <Dialog
        v-model:visible="isVisible"
        modal
        :draggable="false"
        :closable="false"
        class="!rounded-2xl"
        :style="{ width: '55rem' }"
    >
        <div class="p-6">
            <div v-if="!isStepReviewAnamnese">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <BrainCircuit class="text-blue-600" />
                        <h2 class="text-xl font-semibold">Refinar Anamnese</h2>
                    </div>
                    <button @click="close" class="text-gray-400 hover:text-gray-600 transition dark:hover:text-gray-300">
                        ✕
                    </button>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                    Escolha como deseja aprimorar o registro clínico.
                </p>
                <p class="text-xs text-gray-400 mb-5">
                    A análise é baseada exclusivamente no conteúdo desta consulta.
                </p>
                <div class="flex flex-col gap-3">
                    <label class="flex flex-col gap-1 p-3 border rounded-xl cursor-pointer hover:bg-gray-50 transition dark:border-gray-700 dark:hover:bg-gray-700/50">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" value="clarity" v-model="selectedRefinements" />
                            <span class="font-medium">Melhorar clareza do texto</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Reorganiza frases para maior fluidez e compreensão.
                        </p>
                    </label>
                    <label class="flex flex-col gap-1 p-3 border rounded-xl cursor-pointer hover:bg-gray-50 transition dark:border-gray-700 dark:hover:bg-gray-700/50">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" value="technical" v-model="selectedRefinements" />
                            <span class="font-medium">Tornar linguagem mais técnica</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Adequa o texto para terminologia médica formal.
                        </p>
                    </label>
                    <label class="flex flex-col gap-1 p-3 border rounded-xl cursor-pointer hover:bg-gray-50 transition dark:border-gray-700 dark:hover:bg-gray-700/50">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" value="soap" v-model="selectedRefinements" />
                            <span class="font-medium">Organizar em formato SOAP</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Estrutura em Subjetivo, Objetivo, Avaliação e Plano.
                        </p>
                    </label>
                    <label class="flex flex-col gap-2 p-3 border rounded-xl cursor-pointer hover:bg-gray-50 transition dark:border-gray-700 dark:hover:bg-gray-700/50">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" value="custom" v-model="selectedRefinements" />
                            <span class="font-medium">Personalizar refinamento</span>
                        </div>
                        <div v-if="selectedRefinements.includes('custom')" class="mt-2">
                            <p class="text-xs text-gray-500 mb-2 dark:text-gray-400">
                                Descreva como deseja aprimorar o registro clínico.
                            </p>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <button
                                    v-for="suggestion in customSuggestions"
                                    :key="suggestion"
                                    @click.prevent="customInstruction = suggestion"
                                    class="text-xs px-3 py-1 border rounded-full hover:bg-blue-50 hover:border-blue-300 transition dark:border-gray-700 dark:hover:bg-gray-700/50"
                                >
                                    {{ suggestion }}
                                </button>
                            </div>
                            <textarea
                                v-model="customInstruction"
                                rows="3"
                                class="w-full border rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-700"
                                placeholder="Ex: Reforçar justificativa clínica da hipótese principal..."
                            ></textarea>
                        </div>
                    </label>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="close()"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition dark:hover:bg-gray-700/60"
                    >
                        Cancelar
                    </button>
                    <button 
                        @click="applyRefinement"
                        :disabled="loading"
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
                    >
                        <i v-if="!loading" class="pi pi-check text-sm mr-2"></i>
                        <i v-else class="pi pi-spin pi-spinner text-sm mr-2"></i>

                        {{ loading ? 'Refinando...' : 'Aplicar Refinamento' }}
                    </button>
                </div>
            </div>
            <div v-else>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl flex font-semibold">
                        <FileText class="mr-2 text-blue-600" /> Revisar anamnese refinada
                    </h3>
                    <button @click="close" class="text-gray-400 hover:text-gray-600 transition">
                        ✕
                    </button>
                </div>
                <Tiptap
                    v-model:content="refinedContent"
                    :allowRefine="false"
                />
                <div class="flex justify-end gap-3 mt-6">
                    <button
                        @click="rejectRefinement"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-100"
                    >
                        Voltar
                    </button>
                    <button
                        @click="acceptRefinement"
                        :disabled="loadingSave"
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                    >
                        <i v-if="!loadingSave" class="pi pi-check text-sm mr-2"></i>
                        <i v-else class="pi pi-spin pi-spinner text-sm mr-2"></i>

                        {{ loadingSave ? 'Salvando...' : 'Salvar Refinamento' }}
                    </button>
                </div>
            </div>
        </div>
    </Dialog>

    <Signature 
        v-model:visible="showSignatureModal"
        :loading="signatureLoading"
        @close="handleSignatureClose"
        @subscribe="handleSignatureSubscribe"
    />
</template>

<script setup>
import { ref, computed } from 'vue';
import { BrainCircuit, FileText } from "lucide-vue-next";
import { AnamneseService } from '@/service/AnamneseService';
import { useShowToast } from '@/utils/useShowToast';
import { useI18n } from 'vue-i18n';

import api from '@/services/axios';
import Cookies from 'js-cookie';

const { t } = useI18n();
const { showSuccess, showError } = useShowToast();

const isVisible = computed(() => props.showRefineModal);
const isStepReviewAnamnese = computed(() => step.value === 'review')

const emit = defineEmits(['close', 'apply-refined']);

const props = defineProps({
    showRefineModal: {
        type: Boolean,
        default: false,
        required: true
    },
    patientName: {
        type: String,
        required: true
    },
    content: {
        type: String,
        required: true
    },
    documentId: {
        type: String,
        required: true
    }
}); 

const selectedRefinements = ref([]);
const customInstruction = ref('');

const step = ref('options') 

const refinedContent = ref('')
const loading = ref(false)
const loadingSave = ref(false)
const showSignatureModal = ref(false)
const signatureLoading = ref(false)

const customSuggestions = [
    'Focar na hipótese diagnóstica principal',
    'Detalhar melhor o plano terapêutico',
    'Reforçar justificativa clínica',
    'Organizar melhor a queixa principal'
];

const close = () => {
    emit('close');
}

const applyRefinement = async () => {
    loading.value = true

    const payload = {
        refinements: selectedRefinements.value.filter(r => r !== 'custom'),
        custom_instruction: selectedRefinements.value.includes('custom')
            ? customInstruction.value
            : null,
        patient: props.patientName,
        conversation: props.content
    };

    try {
        const response = await AnamneseService.refine(payload)

        refinedContent.value = response.content
        step.value = 'review'
    } catch (error) {
        const REQUIRES_PRO = error.response?.data?.requires_pro

        if (REQUIRES_PRO) {
            showSignatureModal.value = true
        } else {
            showError(t('notifications.titles.error'), 'Problema ao refinar anamnese. Tente novamente!', 3000)
        }
    } finally {
        loading.value = false
    }
};

const acceptRefinement = async () => {
    loadingSave.value = true
    try {
        await AnamneseService.update(props.documentId, refinedContent.value);
        showSuccess(t('notifications.titles.success'), 'Anamnese refinada salva com sucesso!', 3000)
    } catch (error) {
        showError(t('notifications.titles.error'), 'Problema ao salvar a anamnese refinada. Tente novamente!', 3000)
    } finally {
        loadingSave.value = false
        emit('apply-refined', refinedContent.value)
        close()
    }

}

const rejectRefinement = () => {
    step.value = 'options'
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
        console.error('Error creating subscription:', error)
        showError(t('notifications.titles.error'), 'Erro ao iniciar assinatura. Tente novamente!', 3000)
    } finally {
        signatureLoading.value = false
    }
}

</script>

<style scoped>
</style>