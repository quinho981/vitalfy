<script setup>
defineProps({
    resetDate: { type: String, default: '' },
    variant: { type: String, default: 'warning' }
})

const emit = defineEmits(['upgrade', 'dismiss'])
</script>

<template>
    <div
        class="flex items-center gap-3 rounded-xl px-4 py-3 mb-4 border-l-4"
        :class="variant === 'critical'
            ? 'bg-red-50 dark:bg-red-950/40 border-red-500'
            : 'bg-amber-50 dark:bg-amber-950/40 border-amber-400'"
    >
        <i
            class="text-base flex-shrink-0"
            :class="variant === 'critical'
                ? 'pi pi-ban text-red-500'
                : 'pi pi-exclamation-triangle text-amber-500'"
        ></i>
        <p
            class="flex-1 text-sm font-medium"
            :class="variant === 'critical'
                ? 'text-red-800 dark:text-red-200'
                : 'text-amber-800 dark:text-amber-200'"
        >
            <template v-if="variant === 'critical'">
                Você atingiu o limite do plano gratuito.
                <span v-if="resetDate" class="font-normal"> Próxima renovação em <strong>{{ resetDate }}</strong>.</span>
            </template>
            <template v-else>
                Você tem apenas <strong>1 transcrição</strong> disponível este mês. Faça upgrade para continuar sem interrupções.
            </template>
        </p>
        <div class="flex items-center gap-2 flex-shrink-0">
            <button
                @click="emit('upgrade')"
                class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors"
                :class="variant === 'critical'
                    ? 'bg-red-600 hover:bg-red-500 text-white'
                    : 'bg-amber-500 hover:bg-amber-400 text-white'"
            >
                Fazer upgrade
            </button>
            <button
                v-if="variant === 'warning'"
                @click="emit('dismiss')"
                class="text-xs font-medium text-amber-700 dark:text-amber-300 hover:text-amber-900 dark:hover:text-amber-100 transition-colors"
            >
                Continuar assim mesmo
            </button>
        </div>
    </div>
</template>
