<template>
    <div ref="recorderRef" class="recorder-container w-full bg-surface-50 dark:bg-surface-800 border-2 border-dashed border-surface-300 dark:border-surface-700 rounded-lg p-6 flex flex-col gap-0">
        <div 
            class="flex items-center justify-between"
            :class="!recording && !audioUrl ? 'mb-3' : 'mb-5'"
        >
            <div class="flex items-center gap-2">
                <span
                    class="w-2 h-2 rounded-full transition-colors duration-300"
                    :class="{
                        'bg-red-500 animate-pulse': recording && !isPaused,
                        'bg-yellow-500': isPaused,
                        'bg-emerald-500': audioUrl && !recording,
                        'bg-surface-300 dark:bg-surface-600': !recording && !audioUrl
                    }"
                />
                <span class="text-xs font-medium uppercase tracking-widest text-surface-400 dark:text-surface-500">
                    {{ stateLabel }}
                </span>
            </div>
            <span
                class="text-xs font-medium px-3 py-1 rounded-full border transition-colors duration-300"
                :class="{
                    'bg-red-50 text-red-700 border-red-200 dark:bg-red-950 dark:text-red-400 dark:border-red-800': recording && !isPaused,
                    'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-950 dark:text-yellow-400 dark:border-yellow-800': isPaused,
                    'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-400 dark:border-emerald-800': audioUrl && !recording,
                    'bg-surface-100 text-surface-400 border-surface-200 dark:bg-surface-700 dark:text-surface-500 dark:border-surface-600': !recording && !audioUrl
                }"
            >
                {{ badgeLabel }}
            </span>
        </div>

        <div
            class="timer-display flex items-baseline gap-1.5"
            :class="!recording && !audioUrl ? 'mb-0' : 'mb-5'"
        >
            <span class="font-robotomono text-5xl font-semibold tracking-tight text-surface-900 dark:text-surface-100 leading-none tabular-nums">
                {{ formatTime(timer) }}
            </span>
            <span class="font-mono text-sm text-surface-400 dark:text-surface-500">
                {{ timer >= 60 ? 'min' : 'seg' }}
            </span>
        </div>

        <div
            v-if="recording || audioUrl"
            class="audio-visualizer w-full h-14 rounded-lg bg-surface-100 dark:bg-surface-700 border border-surface-200 dark:border-surface-600 flex items-center justify-center gap-[3px] px-3 overflow-hidden mb-6"
        >
            <div
                v-for="(bar, i) in bars"
                :key="i"
                class="w-[3px] rounded-full transition-all duration-[120ms] ease-out"
                :class="{
                    'bg-blue-500 dark:bg-blue-400': recording && !isPaused,
                    'bg-yellow-400': isPaused,
                    'bg-surface-300 dark:bg-surface-600': !recording && !isPaused
                }"
                :style="{ height: Math.min(Math.max(bar * 1.1, 4), 48) + 'px' }"
            />
        </div>

        <div class="recording-controls flex items-center justify-center gap-3 mb-4">
            <button
                v-if="!recording && !audioUrl"
                class="w-14 h-14 rounded-full bg-blue-600 hover:bg-blue-700 active:scale-95 text-white flex items-center justify-center shadow-md shadow-blue-200 dark:shadow-blue-900 transition-all duration-200"
                v-tooltip.top="'Iniciar gravação'"
                @click="startRecording"
            >
                <Mic :size="22" />
            </button>

            <template v-else-if="recording">
                <button
                    v-if="!isPaused"
                    class="w-10 h-10 rounded-full bg-surface-100 hover:bg-surface-200 dark:bg-surface-700 dark:hover:bg-surface-600 border border-surface-200 dark:border-surface-600 text-surface-500 dark:text-surface-400 active:scale-95 flex items-center justify-center transition-all duration-200"
                    v-tooltip.top="'Pausar gravação'"
                    @click="pauseRecording"
                >
                    <Pause :size="18" />
                </button>
                <button
                    v-else
                    class="w-10 h-10 rounded-full bg-surface-100 hover:bg-surface-200 dark:bg-surface-700 dark:hover:bg-surface-600 border border-surface-200 dark:border-surface-600 text-surface-500 dark:text-surface-400 active:scale-95 flex items-center justify-center transition-all duration-200"
                    v-tooltip.top="'Retomar gravação'"
                    @click="resumeRecording"
                >
                    <Play :size="18" />
                </button>
                <button
                    class="w-12 h-12 rounded-full bg-blue-600 hover:bg-blue-700 active:scale-95 text-white flex items-center justify-center shadow-md shadow-blue-200 dark:shadow-blue-900 transition-all duration-200"
                    v-tooltip.top="'Concluir gravação'"
                    @click="stopRecording"
                >
                    <Square :size="16" />
                </button>
            </template>

            <button
                v-else-if="audioUrl"
                class="inline-flex items-center gap-2 h-9 px-4 rounded-lg bg-surface-100 hover:bg-surface-200 dark:bg-surface-700 dark:hover:bg-surface-600 border border-surface-200 dark:border-surface-600 text-surface-500 dark:text-surface-400 text-sm font-medium active:scale-95 transition-all duration-200"
                @click="resetRecorder"
            >
                <Mic :size="14" />
                Nova gravação
            </button>
        </div>

        <p v-if="!recording" class="text-center text-xs transition-colors duration-300" :class="audioUrl ? 'text-emerald-600 dark:text-emerald-400' : 'text-surface-400 dark:text-surface-500'">
            {{ audioUrl ? 'Gravação salva com sucesso' : 'Clique para iniciar a gravação de áudio' }}
        </p>

        <template v-if="audioUrl">
            <div class="w-full h-px bg-surface-200 dark:bg-surface-700 my-4" />
                <div class="w-full">
                    <div class="flex items-center justify-between mb-2.5">
                        <p class="text-[11px] font-medium uppercase tracking-widest text-surface-400 dark:text-surface-500">Gravação</p>
                        <a
                            :href="audioUrl"
                            :download="`gravacao-${Date.now()}.${recordingExtension}`"
                            class="inline-flex items-center gap-1.5 text-[11px] font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors duration-150"
                            v-tooltip.top="'Baixar gravação'"
                        >
                            <Download :size="13" />
                            Baixar
                        </a>
                    </div>
                    <div class="flex items-center gap-3 bg-surface-100 dark:bg-surface-700 border border-surface-200 dark:border-surface-600 rounded-lg px-3.5 py-3">
                        <audio ref="audioRef" :src="audioUrl" @timeupdate="onTimeUpdate" @loadedmetadata="onMetadata" @ended="isPlaying = false" />
                        <button
                            class="w-9 h-9 rounded-full bg-blue-600 hover:bg-blue-700 active:scale-95 text-white flex items-center justify-center flex-shrink-0 transition-all duration-200"
                            @click="togglePlay"
                        >
                            <component :is="isPlaying ? Pause : Play" :size="14" />
                        </button>
                        <div class="flex flex-col gap-1.5 flex-1 min-w-0">
                            <div
                                class="h-[3px] bg-surface-200 dark:bg-surface-600 rounded-full cursor-pointer relative"
                                @click="seek"
                            >
                            <div
                                class="h-full bg-blue-500 rounded-full pointer-events-none transition-[width] duration-100"
                                :style="{ width: playbackProgress + '%' }"
                            />
                        </div>
                        <div class="flex justify-between font-mono text-[11px] text-surface-400 dark:text-surface-500 tabular-nums">
                            <span>{{ formatTime(Math.round(currentTime)) }}</span>
                            <span>{{ formatTime(Math.round(duration)) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Mic, Pause, Play, Square, Download  } from 'lucide-vue-next';
import { useHelpers } from '@/utils/helper';
import { useShowToast } from '@/utils/useShowToast';
import { AUDIO_CONFIG } from '@/utils/constants';

const emit = defineEmits(['recorded', 'recording-started'])
const { formatTime } = useHelpers();
const { showAttention } = useShowToast();
const recording = ref(false);
const isPaused = ref(false);
const audioUrl = ref(null);
const recordingExtension = ref('webm');
const timer = ref(0);
const bars = ref(Array(28).fill(4));
const audioRef = ref(null);
const isPlaying = ref(false);
const currentTime = ref(0);
const duration = ref(0);

let interval = null;
let mediaRecorder;
let audioChunks = [];
let audioContext, analyser, dataArray, source, rafId;

const playbackProgress = computed(() => (duration.value ? (currentTime.value / duration.value) * 100 : 0));

const stateLabel = computed(() => {
    if (recording.value && !isPaused.value) return 'Gravando';
    if (isPaused.value) return 'Pausado';
    if (audioUrl.value) return 'Concluído';
    return 'Pronto';
});

const badgeLabel = computed(() => {
    if (recording.value && !isPaused.value) return '● REC';
    if (isPaused.value) return '⏸ Pausado';
    if (audioUrl.value) return '✓ Pronto';
    return 'Aguardando';
});

const RECORDER_MIME_CANDIDATES = ['audio/mp4', 'audio/webm;codecs=opus', 'audio/webm', 'audio/aac'];
const getSupportedMimeType = () => RECORDER_MIME_CANDIDATES.find((type) => MediaRecorder.isTypeSupported(type)) || '';

const startRecording = async () => {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        const mimeType = getSupportedMimeType();
        mediaRecorder = new MediaRecorder(stream, mimeType ? { mimeType } : undefined);
        let file = null;
        initConfigAudio(file);

        mediaRecorder.ondataavailable = (e) => {
            if (e.data.size > 0) audioChunks.push(e.data);
        };

        mediaRecorder.onstop = async () => {
            const actualMimeType = mediaRecorder.mimeType || 'audio/webm';
            const extension = actualMimeType.includes('mp4') ? 'm4a' : 'webm';
            const audioBlob = new Blob(audioChunks, { type: actualMimeType });
            const file = new File([audioBlob], `record-${Date.now()}.${extension}`, { type: actualMimeType });
            emit('recorded', file);
            recordingExtension.value = extension;
            audioUrl.value = URL.createObjectURL(audioBlob);
            cleanup();

            await emit('recording-started')
        };

        mediaRecorder.start();
        recording.value = true;
        isPaused.value = false;
        timer.value = 0;
        emit('recording-started');

        await emit('recording-started')

        interval = setInterval(() => {
            timer.value++;
            if (timer.value >= AUDIO_CONFIG.MAX_RECORDING_DURATION) {
                stopRecording();
                showAttention('Tempo limite atingido', `A gravação foi interrompida após atingir o limite de ${AUDIO_CONFIG.MAX_RECORDING_DURATION / 60} minutos.`, 5000);
            }
        }, 1000);

        audioContext = new AudioContext();
        source = audioContext.createMediaStreamSource(stream);
        analyser = audioContext.createAnalyser();
        analyser.fftSize = 64;
        source.connect(analyser);
        dataArray = new Uint8Array(analyser.frequencyBinCount);
        animateBars();
    } catch (err) {
        console.error('Erro ao acessar microfone:', err);
    }
};

const pauseRecording = () => {
    if (mediaRecorder?.state === 'recording') {
        mediaRecorder.pause();
        isPaused.value = true;
        cleanup()
    }
};

const resumeRecording = () => {
    if (mediaRecorder?.state === 'paused') {
        mediaRecorder.resume();
        isPaused.value = false;
        interval = setInterval(() => {
            timer.value++;
            if (timer.value >= AUDIO_CONFIG.MAX_RECORDING_DURATION) {
                stopRecording();
                showAttention('Tempo limite atingido', `A gravação foi interrompida após atingir o limite de ${AUDIO_CONFIG.MAX_RECORDING_DURATION / 60} minutos.`, 5000);
            }
        }, 1000);
        animateBars();
    }
};

const stopRecording = () => {
    if (mediaRecorder?.state !== 'inactive') {
        mediaRecorder.stop();
        recording.value = false;
        isPaused.value = false;
        cleanup();
        audioContext?.close();
    }
};

const resetRecorder = () => {
    audioUrl.value = null;
    timer.value = 0;
    bars.value = Array(28).fill(4);
    isPlaying.value = false;
    currentTime.value = 0;
    duration.value = 0;
    emit('recorded', null);
};

const cleanup = () => {
    clearInterval(interval);
    cancelAnimationFrame(rafId);
}

const initConfigAudio = (file) => {
    audioChunks = [];
    emit('recorded', file);
    audioUrl.value = null;
};

const animateBars = () => {
    analyser.getByteFrequencyData(dataArray);
    bars.value = bars.value.map((v, i) => {
        const target = dataArray[i % dataArray.length] / 2;
        return v + (Math.max(target, 4) - v) * 0.2;
    });
    rafId = requestAnimationFrame(animateBars);
};

const togglePlay = () => {
    if (!audioRef.value) return;
    isPlaying.value ? audioRef.value.pause() : audioRef.value.play();
    isPlaying.value = !isPlaying.value;
};

const onTimeUpdate = () => {
    currentTime.value = audioRef.value?.currentTime ?? 0;
};

const onMetadata = () => {
    duration.value = audioRef.value?.duration ?? 0;
};

const seek = (e) => {
    if (!audioRef.value || !duration.value) return;
    const rect = e.currentTarget.getBoundingClientRect();
    audioRef.value.currentTime = ((e.clientX - rect.left) / rect.width) * duration.value;
};
</script>