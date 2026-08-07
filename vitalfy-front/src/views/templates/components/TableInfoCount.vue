<template>
    <div class="flex items-center justify-between">
        <span class="text-xs text-surface-400">
            {{
                filteredTemplates.length === allTemplates.length
                    ? formatLabel(allTemplates.length)
                    : `${filteredTemplates.length} de ${formatLabel(allTemplates.length)}`
            }}
        </span>
        <span v-if="searchQuery" class="text-xs text-surface-400">
            Resultados para "<strong>{{ searchQuery }}</strong>"
        </span>
    </div>
</template>

<script setup>
import { computed } from "vue"

const props = defineProps({
    filteredTemplates: {
        type: Array,
        required: true
    },
    allTemplates: {
        type: Array,
        required: true
    },
    searchQuery: {
        type: String,
        required: false,
        default: ''
    }
})

const formatLabel = (count) => {
    return `${count} ${count === 1 ? 'especialidade' : 'especialidades'}`
}
</script>

<style scoped>
</style>