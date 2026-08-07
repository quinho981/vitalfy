<template>
    <div>
        <template v-if="filteredTemplates.length === 0">
            <div class="flex flex-col items-center justify-center py-16 gap-y-2 text-surface-400">
                <i class="pi pi-search text-3xl" />
                <p class="text-sm">Nenhum template encontrado</p>
            </div>
        </template>

        <template v-else>
            <!-- Seção: Mais usados -->
            <div v-if="showTop && topTemplates.length">
                <div class="px-4 py-2 bg-surface-50 border-b border-surface-100 dark:bg-surface-800 dark:border-surface-700">
                    <span class="text-xs font-semibold text-surface-400 uppercase tracking-wider">Mais usados</span>
                </div>
                <div
                    v-for="template in topTemplates"
                    :key="'top-' + template.id"
                    class="flex items-center gap-x-3 px-4 py-3 border-b border-surface-100 hover:bg-surface-50 transition-colors duration-150 cursor-pointer border-l-2 border-l-primary-400
                        dark:border-b-surface-600 dark:hover:bg-surface-700/50"
                >
                    <TemplateListItem
                        :template="template"
                        :favoriteId="favoriteId"
                        @favorite="setFavorite"
                        @open="openTemplate"
                        @start="startTemplate"
                    />
                </div>
            </div>

            <!-- Seção: Demais -->
            <div v-if="showTop && restTemplates.length">
                <div class="px-4 py-2 bg-surface-50 border-b border-surface-100 dark:bg-surface-800 dark:border-surface-700">
                    <span class="text-xs font-semibold text-surface-400 uppercase tracking-wider">Todas as especialidades — A a Z</span>
                </div>
            </div>

            <div
                v-for="(template, idx) in restTemplates"
                :key="template.id"
                :class="[
                    'flex items-center gap-x-3 px-4 py-3 hover:bg-surface-50 transition-colors duration-150 cursor-pointer dark:border-b-surface-600 dark:hover:bg-surface-700/50',
                    idx < restTemplates.length - 1 ? 'border-b border-surface-100' : ''
                ]"
            >
                <TemplateListItem
                    :template="template"
                    :favoriteId="favoriteId"
                    @favorite="setFavorite"
                    @open="openTemplate"
                    @start="startTemplate"
                />
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue"
import TemplateListItem from './TemplateListItem.vue'

const favoriteId = ref(null)

const props = defineProps({
    searchQuery: {
        type: String,
        required: true
    },
    selectedCategory: {
        type: String,
        required: true
    },
    currentPage: {
        type: Number,
        required: true
    },
    topTemplates: {
        type: Array,
        required: true
    },
    restTemplates: {
        type: Array,
        required: true
    },
    filteredTemplates: {
        type: Array,
        required: true
    },
    showTop: {
        type: Boolean,
        required: true
    }
})

const emit = defineEmits(['open', 'start'])

const openTemplate = (template) => {
    emit('open', template)
}

const startTemplate = (template) => {
    emit('start', template)
}

const setFavorite = (id) => {
    favoriteId.value = id
    localStorage.setItem("favorite", id)
}

onMounted(() => {
    favoriteId.value = localStorage.getItem("favorite")
})
</script>

<style scoped>

</style>