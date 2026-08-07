<template>
    <div class="flex flex-wrap gap-4">
        <template v-if="filteredTemplates.length === 0">
            <div class="w-full card flex flex-col items-center justify-center py-16 gap-y-2 text-surface-400">
                <i class="pi pi-search text-3xl" />
                <p class="text-sm">Nenhum template encontrado</p>
            </div>
        </template>
        <div
            v-else
            v-for="template in filteredTemplates"
            :key="template.id"
            class="card flex flex-col flex-grow gap-y-4 w-full sm:basis-[48%] lg:basis-[32%] hover:shadow-lg transition-shadow duration-300"
        >
            <div class="flex justify-between items-start">
                <div :class="`p-3 rounded-lg w-fit ${template.category.color || 'bg-slate-100 text-slate-600'}`">
                    <component :is="getIcon(template.category.icon)" :size="32" />
                </div>
                <div>
                    <Star
                        :size="18"
                        @click.stop="makeFavorite(template.id)"
                        v-tooltip="isFavorite(template) ? 'Remover do favorito' : 'Marcar como favorito'"
                        :class="[
                            'cursor-pointer transition-all',
                            isFavorite(template)
                                ? 'text-yellow-400 fill-yellow-400'
                                : 'text-surface-400 fill-transparent hover:text-yellow-400 hover:fill-yellow-400'
                        ]"
                    />
                </div>
            </div>
            <h3 class="text-[16.5px] font-bold">{{ template.name }}</h3>
            <p class="line-clamp-2 text-sm text-surface-500 dark:text-surface-400">{{ template.description || `Template para ${template.name}` }}</p>
            <div class="flex items-center gap-x-2">
                <Tag severity="secondary" :value="`${template.total} uso${template.total !== 1 ? 's' : ''}`" rounded />
                <div
                    :class="`py-2 px-3 rounded-full font-semibold flex text-xs items-center justify-center ${template.category.color || 'bg-slate-100 text-slate-600'}`"
                >
                    {{template.category.name}}
                </div>
            </div>
            <div class="flex gap-x-3 mt-auto">
                <Button 
                    label="Ver estrutura" 
                    class="w-full !bg-white border !border-surface-200 !text-surface-700 hover:!bg-surface-100 
                        dark:!bg-surface-800 dark:!border-surface-600 dark:!text-surface-300 dark:hover:!bg-surface-700" 
                    @click="openTemplate(template)" 
                />
                <Button label="Iniciar consulta" class="w-full" @click="startTemplate(template)" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Stethoscope, Slice, TestTubeDiagonal, Baby, Brain, Activity, FileText, Star  } from 'lucide-vue-next'
const iconMap = { Stethoscope, Slice, TestTubeDiagonal, Baby, Brain, Activity, FileText }

const favoriteId = ref(null)

const props = defineProps({
    filteredTemplates: {
        type: Array,
        required: true
    }
})
const emit = defineEmits(['open', 'start', 'favorite'])

const openTemplate = (template) => {
    emit('open', template)
}

const startTemplate = (template) => {
    emit('start', template)
}

const getIcon = (iconName) => {
    return iconMap[iconName] || FileText
}

const makeFavorite = (id) => {
    favoriteId.value = id
    localStorage.setItem("favorite", id)
}

const isFavorite = (template) => {
    return String(template.id) === String(favoriteId.value)
}

onMounted(() => {
    favoriteId.value = localStorage.getItem("favorite")
})
</script>

<style scoped>
</style>