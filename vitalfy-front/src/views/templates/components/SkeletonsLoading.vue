<template>
    <div>
        <div
            v-if="isListView"
            class="card flex flex-col gap-y-3"
        >
            <div v-for="i in 8" :key="i" class="flex items-center gap-x-4 py-3 border-b border-surface-100 last:border-0 dark:border-surface-600">
                <Skeleton shape="square" size="2.5rem" class="rounded-lg flex-shrink-0" />
                <div class="flex-1 flex flex-col gap-y-2">
                    <Skeleton width="30%" height="14px" />
                    <Skeleton width="65%" height="12px" />
                </div>
                <Skeleton width="60px" height="22px" class="rounded-full hidden sm:block" />
                <Skeleton width="80px" height="28px" class="rounded-md hidden md:block" />
                <Skeleton width="100px" height="28px" class="rounded-md hidden md:block" />
            </div>
        </div>
        <div 
            v-else
            class="flex flex-wrap gap-4"
        >
            <div
                v-for="i in 6"
                :key="i"
                class="card flex flex-col flex-grow gap-y-4 w-full sm:basis-[48%] lg:basis-[32%]"
            >
                <Skeleton width="3rem" height="3rem" borderRadius="0.5rem" />
                <Skeleton width="70%" height="1.2rem" />
                <div class="flex flex-col gap-2">
                    <Skeleton width="100%" height="0.8rem" />
                    <Skeleton width="90%" height="0.8rem" />
                </div>
                <div class="flex items-center gap-x-2">
                    <Skeleton width="4rem" height="1.5rem" borderRadius="999px" />
                    <Skeleton width="5rem" height="1.5rem" borderRadius="999px" />
                </div>
                <div class="flex gap-x-3 mt-auto">
                    <Skeleton width="100%" height="2.5rem" borderRadius="0.5rem" />
                    <Skeleton width="100%" height="2.5rem" borderRadius="0.5rem" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue"

const props = defineProps({
    viewMode: {
        type: String,
        required: true
    }
})

const isListView = computed(() => props.viewMode === 'list')
</script>

<style scoped>
</style>