<template>
    <Dialog
        :visible="visible"
        @update:visible="(val) => emit('update:visible', val)"
        modal
        :style="{ width: '680px', maxWidth: '95vw' }"
        :pt="{
            header: { class: 'pb-3 border-b border-surface-100 dark:border-surface-700' },
            content: { class: '!p-0' },
        }"
    >
        <template #header>
            <div class="flex items-center gap-x-3">
                <div v-if="template" :class="`w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 ${template.category.color || 'bg-slate-100 text-slate-600'}`">
                    <component :is="getIcon(template.category.icon)" :size="18" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-surface-800 leading-tight dark:text-surface-300">{{ template?.name }}</p>
                    <p class="text-xs text-surface-400 mt-0.5">Estrutura da consulta</p>
                </div>
            </div>
        </template>

        <div v-if="!details" class="flex flex-col items-center justify-center py-14 gap-y-2 text-surface-400 px-6">
            <FileText :size="28" class="text-surface-300" />
            <p class="text-sm">Este template não possui estrutura detalhada disponível.</p>
        </div>

        <div 
            v-else 
            class="flex flex-col"
        >
            <div class="px-6 py-4 border-b border-surface-100 dark:border-surface-700">
                <p class="text-xs font-medium text-surface-400 uppercase tracking-wide mb-2">Como a IA organiza a consulta</p>
                <p class="text-sm text-surface-600 leading-relaxed dark:!text-surface-300 ">{{ details.description }}</p>
            </div>

            <div class="px-6 py-4 border-b border-surface-100 dark:border-surface-700">
                <p class="text-xs font-medium text-surface-400 uppercase tracking-wide mb-3">Estrutura gerada pela IA</p>
                <div class="flex flex-col gap-y-2">
                    <div
                        v-for="(item, idx) in details.structure"
                        :key="item"
                        class="flex items-start gap-x-3"
                    >
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-surface-100 text-surface-500 text-xs flex items-center justify-center font-medium mt-0.5 dark:bg-surface-600 dark:text-surface-200">
                            {{ idx + 1 }}
                        </span>
                        <span class="text-sm text-surface-700 leading-snug pt-0.5 dark:!text-surface-300 ">{{ item }}</span>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-b border-surface-100 dark:border-surface-700">
                <p class="text-xs font-medium text-surface-400 uppercase tracking-wide mb-3">Exemplo de resultado</p>
                <div
                    class="bg-surface-50 border border-surface-100 rounded-lg px-4 py-3 text-sm text-surface-600 leading-relaxed prose prose-sm max-w-none 
                        dark:bg-surface-800 dark:text-surface-300 dark:border-surface-700"
                    v-html="details.example"
                />
            </div>

            <div class="px-6 py-4">
                <Button
                    label="Iniciar consulta com este template"
                    class="w-full"
                    @click="$emit('start', template)"
                />
            </div>
        </div>
    </Dialog>
</template>

<script setup>
import { computed } from "vue"
import { templateDetailsMap } from "@/service/TemplateDetails"
import { Stethoscope, Slice, TestTubeDiagonal, Baby, Brain, Activity, FileText } from 'lucide-vue-next'

const iconMap = {
    Stethoscope, Slice, TestTubeDiagonal, Baby, Brain, Activity, FileText
}

const props = defineProps({
    visible: Boolean,
    template: Object
})

const emit = defineEmits(["update:visible", "start"])

const details = computed(() => templateDetailsMap[props.template?.name] || null)

const getIcon = (iconName) => {
    return iconMap[iconName] || FileText
}
</script>