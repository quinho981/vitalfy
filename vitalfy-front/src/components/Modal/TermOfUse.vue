<template>
    <Dialog
        :visible="isVisible"
        @update:visible="updateVisibility"
        modal
        :draggable="false"
        :style="{ width: '820px', maxWidth: '95vw' }"
        :pt="{
            header: { class: 'pb-3 border-b border-surface-100 dark:border-surface-700' },
            content: { class: '!p-0' },
            footer: { class: '!pt-3 border-t border-surface-100 dark:border-surface-700' },
        }"
    >
        <template #header>
            <div class="flex items-center gap-x-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 bg-blue-100 dark:bg-blue-500/10">
                    <ScrollText :size="18" class="text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-surface-800 dark:text-surface-200 leading-tight">
                        {{ $t("termOfUse") }}
                    </p>
                    <p class="text-xs text-surface-400 mt-0.5">
                        Leia com atenção antes de utilizar a plataforma
                    </p>
                </div>
            </div>
        </template>

        <div
            v-html="htmlContent"
            class="terms-scroll max-h-[68vh] overflow-y-auto px-6 py-4
                   text-[13px] leading-relaxed
                   text-surface-700 dark:text-surface-300"
        />

        <template #footer>
            <div class="flex justify-end">
                <button
                    @click="close"
                    class="px-5 py-2 rounded-lg text-sm font-semibold transition-colors
                           bg-blue-500 text-white hover:bg-blue-600
                           dark:bg-blue-600 dark:hover:bg-blue-700"
                >
                    {{ $t("button.close") }}
                </button>
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { computed } from "vue";
import { marked } from "marked";
import { ScrollText } from 'lucide-vue-next';
import terms from '@/assets/terms.md?raw';

const emit = defineEmits(['close']);

const props = defineProps({
    active: {
        type: Boolean,
        default: false
    }
});

const isVisible = computed(() => props.active);

const updateVisibility = (value) => {
    if (!value) close();
};

const htmlContent = marked(terms);

const close = () => emit('close', false);
</script>

<style scoped>
/* Markdown typography — não pode ser feito via Tailwind em v-html */
:deep(h1) { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
:deep(h2) { font-size: 13px; font-weight: 700; margin-top: 16px; margin-bottom: 4px; }
:deep(span) { font-weight: 700; }
:deep(ul)  { margin-left: 20px; }
:deep(li)  { list-style-type: disc; margin-bottom: 4px; }
:deep(p)   { margin-bottom: 6px; }
:deep(hr)  { margin: 8px 0; border-color: #e5e7eb; }

/* Scrollbar */
.terms-scroll::-webkit-scrollbar         { width: 6px; }
.terms-scroll::-webkit-scrollbar-track   { background: transparent; }
.terms-scroll::-webkit-scrollbar-thumb   { background-color: #d1d5db; border-radius: 3px; }

:global([class*="app-dark"]) .terms-scroll::-webkit-scrollbar-thumb { background-color: #4b5563; }
:global([class*="app-dark"]) :deep(hr)   { border-color: #374151; }
</style>
