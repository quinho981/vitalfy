<template>
    <section>
        <span class="text-xs text-surface-400 font-medium uppercase tracking-wide">Filtrar: </span>
        <button
            v-for="cat in categories"
            :key="cat.value"
            @click="toggleCategory(cat.value)"
            :class="[
                'px-3 py-1 rounded-full text-xs border transition-colors duration-150 mr-1 max-[819px]:my-1',
                selectedCategory === cat.value
                    ? 'bg-primary-50 border-primary-300 text-primary-700 font-semibold dark:bg-surface-600 dark:border-surface-600 dark:text-surface-200'
                    : 'bg-white border-surface-200 text-surface-500 hover:border-surface-400 dark:bg-surface-800 dark:border-surface-700 dark:text-surface-300 dark:hover:border-surface-500'
            ]"
        >
            {{ cat.label }} <span class="opacity-60">({{ cat.count }})</span>
        </button>
    </section>
</template>

<script setup>
const props = defineProps({
    categories: {
        type: Array,
        required: true
    },
    selectedCategory: {
        type: String,
        required: true
    }
})

const toggleCategory = (category) => {
    const newCategory = props.selectedCategory === category ? 'all' : category
    emit('update:selectedCategory', newCategory)
}

const emit = defineEmits(['update:selectedCategory'])
</script>

<style scoped>
</style>