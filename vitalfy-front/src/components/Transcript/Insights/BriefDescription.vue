<template>
    <div>
        <template v-if="brief_description.length">
            <span
                v-for="description in brief_description"
                :key="description"
            >
                {{ description }}
            </span>
        </template>

        <template v-else>
            <div class="flex flex-wrap gap-2">
                <Skeleton
                    v-for="(skeleton, i) in skeletons"
                    :key="i"
                    :width="skeleton.width"
                    height="22px"
                    class="!rounded-xl mr-2"
                />
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const skeletons = ref([])

const props = defineProps({
    brief_description: {
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