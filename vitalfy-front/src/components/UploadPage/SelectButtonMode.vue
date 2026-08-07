<template>
    <div class="w-full bg-surface-100 dark:bg-surface-800 rounded-lg p-1 flex">
        <button
            @click="update('record')"
            class="flex-1 py-2 rounded-md text-sm font-medium transition-all duration-200"
            :class="isRecordMode
                ? 'bg-white dark:bg-surface-700 text-surface-800 dark:text-surface-200 shadow-sm'
                : 'text-surface-500 dark:text-surface-400 hover:text-surface-700 dark:hover:text-surface-300'"
        >
            Gravar áudio
        </button>

        <button
            @click="update('upload')"
            class="flex-1 py-2 rounded-md text-sm font-medium transition-all duration-200"
            :class="!isRecordMode
                ? 'bg-white dark:bg-surface-700 text-surface-800 dark:text-surface-200 shadow-sm'
                : 'text-surface-500 dark:text-surface-400 hover:text-surface-700 dark:hover:text-surface-300'"
        >
            Enviar áudio
        </button>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    modelValue: { type: String, default: 'record' }
})

const emit = defineEmits(['changeInputMode'])
const isRecordMode = computed(() => props.modelValue === 'record')
const update = (mode) => emit('changeInputMode', mode)
</script>