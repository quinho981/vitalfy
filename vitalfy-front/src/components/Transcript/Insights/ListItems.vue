<template>
    <div>
        <template v-if="items.length">
            <ul class="custom-marker-diagnosis list-disc list-inside">
                <li v-for="item in items" :key="item">
                    {{ item }}
                </li>
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
    items: {
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