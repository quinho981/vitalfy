<script setup>
defineProps({
    message: { type: String, default: '' },
    loading: { type: Boolean, default: false }
})

const emit = defineEmits(['dismiss'])
</script>

<template>
    <div class="flex items-center gap-3 rounded-xl px-4 py-3 mb-4 border-l-4 bg-green-50 dark:bg-green-950/40 border-green-500">
        <i v-if="!loading" class="pi pi-check-circle text-base flex-shrink-0 text-green-500"></i>
        <Skeleton v-else shape="circle" size="1.1rem"/>

        <p v-if="!loading" class="flex-1 text-sm font-medium text-green-800 dark:text-green-200">
            {{ message }}
        </p>
        <Skeleton v-else height="14px" width="40rem" />

        <button
            v-if="!loading"
            @click="emit('dismiss')"
            class="flex-shrink-0 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 transition-colors"
            aria-label="Fechar"
        >
            <i class="pi pi-times text-xs" />
        </button>
    </div>
</template>
