<script setup>
import { watch, onUnmounted } from 'vue'

const props = defineProps({
    visible: { type: Boolean, default: false },
    remaining: { type: Number, default: 0 }
})

const emit = defineEmits(['close', 'upgrade'])

let dismissTimer = null

const clearTimer = () => clearTimeout(dismissTimer)

const close = () => {
    clearTimer()
    emit('close')
}

const upgrade = () => {
    clearTimer()
    emit('upgrade')
}

watch(() => props.visible, (val) => {
    if (val) {
        clearTimer()
        dismissTimer = setTimeout(() => emit('close'), 10000)
    } else {
        clearTimer()
    }
})

onUnmounted(clearTimer)
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
    >
        <div
            v-if="visible"
            class="fixed bottom-6 right-6 z-[9999] w-80 rounded-xl border border-blue-500/40 bg-slate-900 dark:bg-slate-800 shadow-2xl p-4"
        >
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-blue-500/15 flex items-center justify-center">
                    <i class="pi pi-bolt text-base text-blue-400"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white leading-tight mb-1">
                        {{ remaining === 1 ? 'Última transcrição disponível' : 'Transcrições quase esgotadas' }}
                    </p>
                    <p class="text-xs text-slate-400 leading-snug">
                        {{ remaining === 1
                            ? 'Resta apenas 1 transcrição este mês. Faça upgrade para nunca parar no meio de uma consulta.'
                            : 'Você tem 2 transcrições restantes este mês. Nunca pare no meio de uma consulta.' }}
                    </p>
                    <button
                        @click="upgrade"
                        class="mt-2.5 w-full rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold py-2 transition-colors"
                    >
                        Fazer upgrade
                    </button>
                </div>
                <button
                    @click="close"
                    class="flex-shrink-0 text-slate-500 hover:text-slate-300 transition-colors ml-1"
                >
                    <i class="pi pi-times text-xs"></i>
                </button>
            </div>
        </div>
    </Transition>
</template>
