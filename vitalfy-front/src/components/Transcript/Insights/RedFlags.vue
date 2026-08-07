<template>
    <div>
        <template v-if="red_flags.length">
            <span 
                v-for="red_flag in red_flags" 
                :key="red_flag"
                class="bg-red-200 p-2 rounded-full mr-2 inline-block mb-1 dark:bg-red-900 dark:text-white"
            >
                {{ red_flag }}
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
    red_flags: {
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