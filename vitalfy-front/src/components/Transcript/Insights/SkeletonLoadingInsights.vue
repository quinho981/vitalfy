<template>
    <div>
        <div class="flex flex-col">
            <!-- Sinais de alerta -->
            <div class="flex">
                <Skeleton
                    v-for="(skeleton, i) in redFlagsSkeletons"
                    :key="i"
                    :width="skeleton.width"
                    height="22px"
                    class="!rounded-xl mb-2 flex mr-2"
                />
            </div>
        </div>
        <hr class="my-4" />
        <div>
            <!-- Gravidade estimada -->
            <Skeleton
                width="10rem"
                height="22px"
                class="!rounded-xl mb-2"
            />
        </div>
        <hr class="my-4" />
        <div>
            <!-- Resumo clínico -->
            <Skeleton
                width="25rem"
                height="22px"
                class="!rounded-xl mb-2"
            />
        </div>
        <hr class="my-4" />
        <div>
            <!-- Possíveis diagnósticos -->
            <Skeleton
                v-for="(skeleton, i) in diagnosesSkeletons"
                :key="i"
                :width="skeleton.width"
                height="22px"
                class="!rounded-xl mb-2"
            />
        </div>
        <hr class="my-4" />
        <div>
            <!-- CIDs sugeridos -->
            <Skeleton
                v-for="(skeleton, i) in cidsSkeletons"
                :key="i"
                :width="skeleton.width"
                height="22px"
                class="!rounded-xl mb-2"
            />
        </div>
        <hr class="my-4" />
        <div>
            <!-- Exames sugeridos -->
            <Skeleton
                v-for="(skeleton, i) in examsSkeletons"
                :key="i"
                :width="skeleton.width"
                height="22px"
                class="!rounded-xl mb-2"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const redFlagsSkeletons = ref([])
const diagnosesSkeletons = ref([])
const cidsSkeletons = ref([])
const examsSkeletons = ref([])

const generateSkeletons = () => {
    const count = Math.floor(Math.random() * 4) + 1

    return Array.from({ length: count }, () => {
        const size = Math.floor(Math.random() * (12 - 6 + 1)) + 6
        return { width: `${size}rem` }
    })
}

onMounted(() => {
    redFlagsSkeletons.value = generateSkeletons()
    diagnosesSkeletons.value = generateSkeletons()
    cidsSkeletons.value = generateSkeletons()
    examsSkeletons.value = generateSkeletons()
})
</script>

<style>
</style>