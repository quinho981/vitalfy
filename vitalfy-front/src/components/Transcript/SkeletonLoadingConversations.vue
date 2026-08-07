<template>
    <div>
        <div
            v-for="(_, i) in skeletonMessages"
            :key="i"
            class="mb-6"
        >
        <Skeleton width="2.5rem" height="0.8rem" class="mb-2" />

        <Skeleton
            :width="randomWidth(10, 26)"
            height="1.4rem"
            class="rounded-lg"
        />
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const skeletonMessages = ref(Array.from({ length: 5 }))

const randomWidth = (min = 5, max = 30) => {
    return `${Math.floor(Math.random() * (max - min + 1) + min)}rem`
}
</script>

<style>
</style>