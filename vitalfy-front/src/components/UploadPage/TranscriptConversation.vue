<template>
    <div class="transcription-preview card w-full md:w-1/2 flex flex-col gap-y-3">
        <div class="flex items-center gap-x-3">
            <div class="w-9 h-9 rounded-lg bg-teal-50 flex items-center justify-center flex-shrink-0 dark:bg-teal-950">
                <FileAudio2 :size="17" class="text-teal-600 dark:text-teal-400" />
            </div>
            <div>
                <p class="text-sm font-semibold text-surface-800 dark:text-surface-200">Transcrição da consulta</p>
                <p class="text-xs text-surface-400 mt-0.5">Texto transcrito automaticamente pela IA</p>
            </div>
            <button
                v-tooltip.top="{
                    value: `<span class='text-sm'>Para obter transcrições mais precisas e organizadas, grave apenas as informações relevantes da anamnese. Pause ou finalize a gravação ao encerrar essa etapa da consulta.</span>`,
                    escape: false,
                    showDelay: 300
                }"
                class="flex items-center justify-center rounded-full border-none text-surface-400 hover:text-surface-600 dark:text-surface-500 dark:hover:text-surface-300 transition-colors"
            >
                <HelpCircle :size="15" />
            </button>
        </div>
        <div class="p-3 h-full w-full rounded-lg border border-surface-200 dark:border-surface-700 dark:bg-surface-800 flex flex-col gap-y-2 xl:min-h-[350px] xl:max-h-[470px] overflow-y-auto">
            <div 
                v-if="transcriptions.length === 0 && !isTranscribing && !loadingTranscribeAndGenerate" 
                class="flex items-center justify-center h-full"
            >
                <p class="text-sm text-surface-400 text-center">Clique em <strong>Transcrever</strong> para visualizar o texto da consulta aqui,<br> ou em <strong>Transcrever e gerar documento</strong> para criar o documento completo automaticamente.</p>
            </div>

            <div v-if="isTranscribing" class="flex flex-col gap-4 px-2 py-4 animate-[fadeUp_0.4s_ease]">
                <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/50 rounded-lg">
                    <div class="flex items-center gap-[3px] h-9 shrink-0">
                        <span
                            v-for="i in 10" :key="i"
                            class="block w-1 rounded-full bg-blue-400 dark:bg-blue-400 animate-[wave_1s_ease-in-out_infinite]"
                            :style="{ animationDelay: waveDelay(i) }"
                        ></span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">Analisando áudio da consulta</p>
                        <p class="text-xs text-blue-500 dark:text-blue-400">Identificando falantes e transcrevendo...</p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 px-1">
                    <div v-for="(item, i) in skeletonLines" :key="i" class="flex flex-col gap-2">
                        <Skeleton :width="item.tag" height="15px" class="rounded-lg" />
                        <Skeleton :width="item.line" height="30px" class="rounded-lg mb-3" />
                    </div>
                </div>
            </div>
            
            <div v-if="loadingTranscribeAndGenerate" class="flex flex-col gap-4 px-2 py-4 animate-[fadeUp_0.4s_ease]">
                <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/50 rounded-lg">
                    <div class="flex flex-col gap-0.5">
                        <p class="text-[12.5px] font-semibold text-blue-800 dark:text-blue-300">
                            Preparando o resultado da consulta
                        </p>
                        <p class="text-[11.5px] text-blue-500 dark:text-blue-400 mb-1">
                            {{ currentStep.label }}
                        </p>
                        <p class="text-[11px] text-slate-400">
                            Isso pode levar alguns segundos, dependendo da duração do áudio.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-2 px-2">
                    <div
                        v-for="(step, index) in steps"
                        :key="step.key"
                        class="flex items-center gap-2 text-sm"
                    >
                        <div
                            class="w-5 h-5 flex items-center justify-center rounded-full text-[11px] font-semibold transition-all duration-300"
                            :class="{
                                'bg-blue-600 text-white scale-110 shadow-sm': index === currentStepIndex,
                                'bg-blue-500 text-white': index < currentStepIndex,
                                'bg-surface-300 dark:bg-surface-600 text-surface-600 dark:text-surface-300 opacity-70': index > currentStepIndex
                            }"
                        >
                            <span v-if="index < currentStepIndex" class="text-[12px]">✓</span>
                            <span v-else>{{ index + 1 }}</span>
                        </div>

                        <span
                            class="mb-2"
                            :class="{
                                'text-blue-700 dark:text-blue-300 font-medium': index === currentStepIndex,
                                'text-surface-500 opacity-70': index > currentStepIndex
                            }"
                        >
                            {{ step.label }}
                        </span>
                    </div>
                </div>
            </div>

            <div v-for="(transcription, index) in transcriptions" :key="index" class="transcription-item">
                <div class="flex items-center gap-2 mb-3">
                    <FileAudio2 :size="16" class="text-blue-500" />
                    <span class="font-semibold text-sm text-surface-700 dark:text-surface-300">{{ transcription.fileName }}</span>
                    <span class="text-xs text-surface-500 dark:text-surface-400">{{ transcription.timestamp }}</span>
                </div>

                <div v-for="(utterance, uttIndex) in transcription.utterances" :key="uttIndex" class="mb-2">
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
        </div>
        <div ref="bottomAnchor" v-show="transcriptions.length != 0 && !isTranscribing" class="flex justify-end gap-x-2">
            <button
                @click="emit('clear')"
                class="flex items-center px-4 py-2 border rounded-lg hover:bg-gray-100 transition dark:hover:bg-neutral-700 dark:border-neutral-600 dark:text-gray-300"
            >
                <Trash :size="15" class="mr-1" /> Limpar
            </button>
            <Button
                @click="emit('finish')"
                :disabled="loadingFinish"
                class="!bg-gradient-to-br !from-blue-500 !to-blue-700 !border-none !text-white !rounded-lg font-semibold hover:!from-blue-600 hover:!to-blue-800"
            >
                <Loader2 v-if="loadingFinish" :size="16" class="animate-spin mr-2" />
                <FileChartColumn v-else :size="16" class="mr-2" />
                Finalizar e gerar insights
            </Button>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, nextTick, computed } from 'vue';
import { FileAudio2, FileChartColumn, Loader2, HelpCircle, Trash } from 'lucide-vue-next';
import Skeleton from 'primevue/skeleton';
 
const emit = defineEmits(['clear', 'finish']);
 
const props = defineProps({
    transcriptions: {
        type: Array,
        default: () => []
    },
    isTranscribing: {
        type: Boolean,
        default: false
    },
    dialogClear: {
        type: Boolean,
        default: false
    },
    loadingFinish: {
        type: Boolean,
        default: false
    },
    loadingTranscribeAndGenerate: {
        type: Boolean,
        default: false
    }
})

const bottomAnchor = ref(null)
 
const delays = [0, 0.08, 0.16, 0.24, 0.32, 0.32, 0.24, 0.16, 0.08, 0]
const waveDelay = (i) => `${delays[i - 1]}s`
 
const randWidth = (min, max) => `${Math.floor(Math.random() * (max - min + 1)) + min}%`
 
const generateSkeletonLines = () =>
    Array.from({ length: 5 }, () => ({
        tag:  randWidth(4, 6),
        line: randWidth(15, 45),
    }))
 
const skeletonLines = ref(generateSkeletonLines())
 
watch(() => props.isTranscribing, (val) => {
    if (val) skeletonLines.value = generateSkeletonLines()
})

watch(
    () => props.transcriptions,
    () => {
        scrollToBottom()
    },
    { deep: true }
)

const scrollToBottom = async () => {
    await nextTick()

    setTimeout(() => {
        bottomAnchor.value?.scrollIntoView({
            behavior: 'smooth',
            block: 'end'
        })
    }, 100)
}

const steps = [
    { key: 'sending', label: 'Recebendo o áudio...' },
    { key: 'transcribing', label: 'Transcrevendo a consulta...' },
    { key: 'generating', label: 'Organizando as informações clínicas...' }
]

const currentStepIndex = ref(0)

const currentStep = computed(() => steps[currentStepIndex.value])

let stepInterval = null

watch(() => props.loadingTranscribeAndGenerate, (val) => {
    if (val) {
        currentStepIndex.value = 0

        stepInterval = setInterval(() => {
            if (currentStepIndex.value < steps.length - 1) {
                currentStepIndex.value++
            }
        }, 2000)
    } else {
        clearInterval(stepInterval)
    }
})
</script>

<style>
@keyframes wave {
    0%, 100% { height: 6px;  opacity: 0.35; }
    50%       { height: 30px; opacity: 1;    }
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0);   }
}
</style>