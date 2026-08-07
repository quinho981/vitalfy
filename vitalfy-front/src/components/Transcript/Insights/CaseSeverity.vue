<template>
    <div>
        <template v-if="case_severity.length">
            <ul class="custom-marker-topic list-disc list-inside">
                <span v-for="severity in case_severity" :key="severity">
                    <span
                        class="inline-block w-3.5 h-3.5 rounded-full mr-1"
                        :class="getSeverityColor(severity)"
                    ></span>
                    {{ translateSeverity(severity) }}
                </span>
            </ul>
        </template>

        <template v-else>
            <Skeleton
                v-for="(skeleton, i) in skeletons"
                :key="i"
                :width="skeleton.width"
                height="22px"
                class="!rounded-xl mb-2"
            />
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const skeletons = ref([])

const props = defineProps({
    case_severity: {
        type: Array,
        default: []
    },
});

const severityMap = {
    vermelho: {
        color: 'bg-red-500',
        label: 'Emergência'
    },
    laranja: {
        color: 'bg-orange-500',
        label: 'Muito urgente'
    },
    amarelo: {
        color: 'bg-yellow-500',
        label: 'Urgente'
    },
    verde: {
        color: 'bg-green-500',
        label: 'Pouco urgente'
    },
    azul: {
        color: 'bg-blue-500',
        label: 'Não urgente'
    }
}

const getSeverityKey = (severity) => {
    const value = severity?.toLowerCase() ?? ''
    return Object.keys(severityMap).find(key => value.includes(key))
}

const getSeverityColor = (severity) => {
    const key = getSeverityKey(severity)
    return severityMap[key]?.color ?? 'bg-gray-400'
}

const translateSeverity = (severity) => {
    const key = getSeverityKey(severity)
    return severityMap[key]?.label ?? 'Desconhecido'
}

onMounted(() => {
    const count = Math.floor(Math.random() * 4) + 1
    skeletons.value = Array.from({ length: count }, () => {
        const size = Math.floor(Math.random() * (7 - 3 + 1)) + 3
        return { width: `${size}rem` }
    })
})
</script>

<style>
</style>