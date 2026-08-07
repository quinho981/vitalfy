<template>
    <div>
        <template v-if="possible_diagnoses.length">
            <ol class="custom-marker-diagnosis list-decimal list-inside">
                <li v-for="diagnose in possible_diagnoses" :key="diagnose">
                    {{ diagnose }}
                </li>
            </ol>
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
    possible_diagnoses: {
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