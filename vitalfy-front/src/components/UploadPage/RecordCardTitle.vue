<template>
    <div class="flex items-center gap-x-3 mb-3">
        <div class="w-9 h-9 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0 dark:bg-purple-950">
            <AudioLines :size="17" class="text-purple-600 dark:text-purple-400" />
        </div>
        <div>
            <p class="text-sm font-semibold text-surface-800 dark:text-surface-200">Gravar áudio</p>
            <p class="text-xs text-surface-400 mt-0.5">Controle a gravação da consulta (máx. 30min)</p>
        </div>
    </div>
</template>

<script setup>
import { AudioLines } from 'lucide-vue-next';
</script>

<style scoped>
</style>