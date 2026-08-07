<template>
    <Dialog
        v-model:visible="isVisible"
        modal
        :draggable="false"
        :closable="false"
        :style="{ width: '28rem' }"
    >
        <div class="p-3">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-50 dark:bg-red-900">
                        <i class="pi pi-exclamation-triangle text-red-600 text-lg dark:text-red-400"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-surface-300">
                        Trocar modo de consulta
                    </h2>
                </div>

                <button
                    @click="close"
                    class="text-gray-400 hover:text-gray-600 transition hover:text-gray-300"
                >
                    ✕
                </button>
            </div>

            <p class="text-[13px] text-gray-600 leading-relaxed dark:text-surface-400">
                Você já possui uma gravação em andamento.
            </p>

            <p class="text-[13px] text-gray-600 mt-3 leading-relaxed dark:text-surface-400">
                Ao mudar para <strong>Envio de arquivo</strong>, todo o progresso
                da gravação será perdido permanentemente.
            </p>

            <p class="text-[13px] text-gray-600 mt-3 dark:text-surface-400">
                Deseja continuar?
            </p>

            <p class="text-xs text-gray-400 mt-3">
                Esta ação é irreversível.
            </p>

            <div class="flex justify-end gap-3 mt-6">
                <button
                    @click="close"
                    class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition dark:hover:bg-gray-700"
                >
                    Cancelar
                </button>

                <button
                    @click="confirm"
                    :disabled="props.loading"
                    class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50 flex items-center gap-2"
                >
                    <i v-if="!props.loading" class="pi pi-exclamation-triangle text-sm"></i>
                    <i v-else class="pi pi-spin pi-spinner text-sm"></i>

                    {{ props.loading ? 'Trocando...' : 'Trocar e descartar gravação' }}
                </button>
            </div>
        </div>
    </Dialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import { TriangleAlert } from 'lucide-vue-next';

const isVisible = computed(() => props.active);

const emit = defineEmits(['close', 'confirm']);

const props = defineProps({
    active: {
        type: Boolean,
        required: true
    },
    loading: {
        type: Boolean,
        default: false
    }
}); 

const close = () => {
    emit('close', false);
};

const confirm = () => {
    emit('confirm');
};
</script>

<style scoped>
</style>