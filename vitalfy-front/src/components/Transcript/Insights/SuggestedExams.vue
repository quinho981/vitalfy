<template>
    <div>
        <template v-if="suggested_exams.length">
            <span 
                v-for="exam in suggested_exams" 
                :key="exam"
                class="bg-gray-200 p-2 rounded-full mr-2 inline-block mb-1 dark:bg-neutral-700 dark:text-white"
            >
                {{ exam }}
            </span>
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
    suggested_exams: {
        type: Array,
        default: []
    },
});

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