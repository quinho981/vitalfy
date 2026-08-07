<template>
    <div class="tour-guide-container">
        <button
            v-if="showTourButton"
            @click="startTour"
            class="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-200 dark:shadow-blue-900 transition-all duration-300 flex items-center justify-center group"
            v-tooltip.left="'Iniciar tour guiado'"
        >
            <HelpCircle :size="24" />
            <span class="absolute right-full mr-3 px-3 py-1 bg-surface-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                Tour guiado
            </span>
        </button>
    </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import Shepherd from 'shepherd.js';
import 'shepherd.js/dist/css/shepherd.css';
import { HelpCircle } from 'lucide-vue-next';
import { useUserStore } from '@/stores/userStore'

const userStore = useUserStore()

const props = defineProps({
    tourType: {
        type: String,
        default: 'recording'
    },
    showTourButton: {
        type: Boolean,
        default: true
    },
    autoStart: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['tour-complete', 'tour-cancel']);

let tour = null;

const tourSteps = {
    recording: [
        {
            id: 'recording-start',
            target: '.recorder-container',
            title: 'Bem-vindo à Vitalfy',
            text: 'A Vitalfy transforma o áudio da sua consulta em transcrições, documentos clínicos e insights automaticamente. Vamos mostrar como funciona.',
            attachTo: {
                element: '.recorder-container',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Próximo',
                    action: 'next'
                }
            ]
        },
        {
            id: 'recording-timer',
            target: '.timer-display',
            title: 'Acompanhe a gravação',
            text: 'O tempo da consulta é exibido em tempo real durante toda a gravação.',
            attachTo: {
                element: '.timer-display',
                on: 'top'
            },
            buttons: [
                {
                    text: 'Anterior',
                    action: 'back'
                },
                {
                    text: 'Próximo',
                    action: 'next'
                }
            ]
        },
        {
            id: 'recording-controls',
            target: '.recording-controls',
            title: 'Grave a consulta',
            text: 'Clique no microfone para iniciar a gravação. Você pode pausar, continuar e finalizar quando quiser.',
            attachTo: {
                element: '.recording-controls',
                on: 'top'
            },
            buttons: [
                {
                    text: 'Anterior',
                    action: 'back'
                },
                {
                    text: 'Próximo',
                    action: 'next'
                }
            ]
        },
        {
            id: 'transcription-button',
            target: '.transcription-button',
            title: 'Transcrição primeiro',
            text: 'Use esta opção para gerar a transcrição da consulta. O texto aparecerá ao lado para revisão antes de gerar qualquer documento.',
            attachTo: {
                element: '.transcription-button',
                on: 'top'
            },
            buttons: [
                {
                    text: 'Anterior',
                    action: 'back'
                },
                {
                    text: 'Próximo',
                    action: 'next'
                }
            ]
        },
        {
            id: 'transcription-preview',
            target: '.transcription-preview',
            title: 'Revise a transcrição',
            text: 'A transcrição será exibida aqui. Você pode revisar o conteúdo antes de transformar a consulta em documento clínico.',
            attachTo: {
                element: '.transcription-preview',
                on: 'left'
            },
            buttons: [
                {
                    text: 'Anterior',
                    action: 'back'
                },
                {
                    text: 'Próximo',
                    action: 'next'
                }
            ]
        },
        {
            id: 'transcribe-and-generate-button',
            target: '.transcribe-and-generate-button',
            title: 'Transcrever e gerar documento',
            text: 'Esta opção transcreve o áudio e já gera automaticamente o documento clínico completo com apoio da IA. A transcrição poderá ser acessada depois na página de detalhes do documento clínico.',
            attachTo: {
                element: '.transcribe-and-generate-button',
                on: 'top'
            },
            buttons: [
                {
                    text: 'Anterior',
                    action: 'back'
                },
                {
                    text: 'Concluir',
                    action: 'complete'
                }
            ]
        }
    ],
};

const startTour = () => {
    if (tour) {
        tour.complete();
    }

    const steps = tourSteps[props.tourType] || tourSteps.recording;

    tour = new Shepherd.Tour({
        defaultStepOptions: {
            cancelIcon: {
                enabled: true
            },
            classes: 'shepherd-theme-custom',
            scrollTo: {
                behavior: 'smooth',
                block: 'center'
            },
            arrow: true,
            highlightClass: 'shepherd-highlight'
        },
        useModalOverlay: true
    });

    steps.forEach(step => {
        tour.addStep({
            ...step,
            buttons: step.buttons.map(btn => ({
                ...btn,
                classes: btn.text === 'Concluir' ? 'shepherd-button-primary' : 'shepherd-button-secondary',
                action: () => {
                    if (btn.action === 'complete') {
                        tour.complete();
                    } else if (btn.action === 'next') {
                        tour.next();
                    } else if (btn.action === 'back') {
                        tour.back();
                    }
                }
            }))
        });
    });

    tour.on('complete', () => {
        emit('tour-complete');
        localStorage.setItem(`${props.tourType}_tour_completed`, 'true');
    });

    tour.on('cancel', () => {
        emit('tour-cancel');
    });

    tour.start();
};

const checkTourCompleted = () => {
    return userStore.recordingTourCompleted;
};

onMounted(() => {
    if (props.autoStart && !checkTourCompleted()) {
        
        setTimeout(() => {
            startTour();
        }, 1000);
    }
});

onUnmounted(() => {
    if (tour) {
        tour.complete();
    }
});
</script>

<style scoped>
/* DARK THEME */
:global([class*="app-dark"] .shepherd-theme-custom) {
    box-shadow:
        0 10px 30px rgba(0, 0, 0, 0.45),
        0 4px 12px rgba(0, 0, 0, 0.25);
}
:global([class*="app-dark"] .shepherd-theme-custom .shepherd-content) {
    background: var(--surface-card, #0f172a);
    border: 1px solid var(--surface-border, #1e293b);
}
:global([class*="app-dark"] .shepherd-theme-custom .shepherd-header) {
    background: var(--surface-hover, #111827);
}
:global([class*="app-dark"] .shepherd-theme-custom .shepherd-cancel-icon) {
    color: white !important;
}
:global([class*="app-dark"] .shepherd-theme-custom .shepherd-title) {
    color: white;
}
:global([class*="app-dark"] .shepherd-theme-custom .shepherd-text) {
    background: var(--surface-card, #0f172a);
    color: white;
}
:global([class*="app-dark"] .shepherd-theme-custom .shepherd-footer) {
    background: var(--surface-card, #0f172a);
    border-top: 1px solid var(--surface-border, #1f2937);
}
:global([class*="app-dark"] .shepherd-button-primary) {
    background: #3b82f6;
    color: white;
    margin-top: 10px;
}
:global([class*="app-dark"] .shepherd-button-primary:hover) {
    background: color-mix(in srgb, var(--primary-color, #3b82f6) 85%, white);
}
:global([class*="app-dark"] .shepherd-button-secondary) {
    background: var(--surface-border, #151c26);
    color: var(--text-color, #e5e7eb);
    margin-top: 10px;
}
:global([class*="app-dark"] .shepherd-button-secondary:hover) {
    background: color-mix(in srgb, var(--surface-border, #151c26) 75%, white) !important;
    color: #e5e7eb !important;
}
:global([class*="app-dark"] .shepherd-theme-custom .shepherd-cancel-icon) {
    color: var(--primary-contrast-color, rgba(255, 255, 255, 0.9));
}
:global([class*="app-dark"] .shepherd-highlight) {
    box-shadow: 0 0 0 4px color-mix(in srgb, var(--primary-color, #3b82f6) 45%, transparent);
}
:global([class*="app-dark"] .shepherd-modal-mask-container) {
    background: var(--maskbg, rgba(0, 0, 0, 0.7));
    backdrop-filter: blur(6px);
}
</style>