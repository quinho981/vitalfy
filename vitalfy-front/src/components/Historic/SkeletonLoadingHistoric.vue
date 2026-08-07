<template>
    <div>
        <div v-for="(_, i) in skeletonMessages" :key="i">
            <div class="mb-4">
                <div class="grid grid-cols-1 gap-4">
                    <div class="flex justify-between flex-wrap card p-3">
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <div class="flex gap-2 items-center w-full">
                                    <p class="text-xl font-semibold"><Skeleton height="1.6rem" width="15rem" class="mb-1"></Skeleton></p>
                                </div>
                            </div>
                            <div class="flex justify-between mb-1 text-slate-600 dark:text-slate-200">
                                <div class="flex gap-8">
                                    <div class="flex items-center gap-1"><Skeleton height="1.5rem" width="7rem" class="mb-1"></Skeleton></div>
                                    <div class="flex items-center gap-1"><Skeleton height="1.5rem" width="5rem" class="mb-1"></Skeleton></div>
                                </div>
                            </div>
                            <p class="text-slate-600 dark:text-slate-200"><Skeleton height="1.6rem" width="35rem"></Skeleton></p>
                        </div>
                        <div class="flex flex-col justify-between mt-3 sm:mt-0 sm:items-center">
                            <div class="flex gap-2">
                                <Skeleton height="1.5rem" width="5rem"></Skeleton>
                                <Skeleton height="1.5rem" width="5rem"></Skeleton>
                            </div>
                            <div class="flex gap-x-2">
                                <Skeleton height="1.5rem" width="2rem" class="mr-2"></Skeleton>
                                <Skeleton height="1.5rem" width="2rem" class="mr-2"></Skeleton>
                                <Skeleton height="1.5rem" width="2rem" ></Skeleton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const count = Math.floor(Math.random() * 4) + 1;
const skeletonMessages = ref(Array.from({ length: count }));
</script>

<style>
</style>