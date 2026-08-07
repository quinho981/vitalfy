<template>
    <div class="flex items-center gap-x-3 mb-3">
        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 dark:bg-blue-950">
            <Upload :size="17" class="text-blue-600 dark:text-blue-400" />
        </div>
        <div>
            <p class="text-sm font-semibold text-surface-800 dark:text-surface-200">Enviar áudio</p>
            <p class="text-xs text-surface-400 mt-0.5">Formatos suportados: MP3, WAV, M4A, AAC, OGG, FLAC (máx. 100MB/30min)</p>
        </div>
    </div>
</template>

<script setup>
import { Upload } from 'lucide-vue-next';
</script>

<style scoped>
</style>