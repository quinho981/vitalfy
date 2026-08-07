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
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-50 dark:bg-yellow-900">
                        <i class="pi pi-eraser text-yellow-600 text-lg dark:text-yellow-400"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                        Limpar transcrição
                    </h2>
                </div>
                <button
                    @click="close"
                    class="text-gray-400 hover:text-gray-600 transition"
                >
                    ✕
                </button>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed dark:text-gray-300">
                Esta ação limpará a transcrição da sessão atual. Você poderá acessá-la novamente pelo histórico.
            </p>
            <div class="flex justify-end gap-3 mt-6">

                <button
                    @click="close"
                    class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition dark:hover:bg-neutral-700 dark:border-neutral-600 dark:text-gray-300"
                >
                    Cancelar
                </button>

                <button
                    @click="confirm"
                    :disabled="props.loading"
                    class="px-5 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition disabled:opacity-50 flex items-center gap-2"
                >
                    <i v-if="!props.loading" class="pi pi-eraser text-sm"></i>
                    <i v-else class="pi pi-spin pi-spinner text-sm"></i>

                    {{ props.loading ? 'Limpando...' : 'Limpar dados' }}
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