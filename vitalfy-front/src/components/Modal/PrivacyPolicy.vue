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
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 bg-emerald-50 dark:bg-green-500/10">
                    <ShieldCheck :size="18" class="text-emerald-600 dark:text-emerald-400" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-surface-800 dark:text-surface-200 leading-tight">
                        {{ $t("privacyPolicy") }}
                    </p>
                    <p class="text-xs text-surface-400 mt-0.5">
                        Como tratamos e protegemos os seus dados pessoais
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
                           bg-emerald-600 text-white hover:bg-emerald-700
                           dark:bg-emerald-700 dark:hover:bg-emerald-800"
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
import { ShieldCheck } from 'lucide-vue-next';
import privacy from '@/assets/privacy.md?raw';

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

const htmlContent = marked(privacy);

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
