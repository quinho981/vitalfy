<template>
    <section>
        <div class="mb-3 py-3">
            <h1 class="text-3xl font-bold">Templates</h1>
            <p class="my-1 text-lg text-surface-500">Modelos pré-configurados para diferentes especialidades</p>
        </div>

        <div class="flex flex-col gap-y-4">
            <div class="card flex flex-col gap-y-3 sm:flex-row sm:items-center sm:gap-x-3">
                <IconField class="flex-1">
                    <InputIcon class="pi pi-search" />
                    <InputText
                        v-model="searchQuery"
                        placeholder="Pesquisar por especialidade..."
                        class="w-full"
                    />
                </IconField>

                <div class="flex items-center gap-x-2">
                    <span class="text-sm text-surface-500 hidden sm:inline">Exibir como:</span>
                    <SelectButton
                        v-model="viewMode"
                        :options="viewOptions"
                        option-label="label"
                        option-value="value"
                        :allow-empty="false"
                    >
                        <template #option="{ option }">
                            <i :class="option.icon" class="text-sm" />
                        </template>
                    </SelectButton>
                </div>
            </div>

            <div class="flex items-center gap-x-2 gap-y-2 flex-wrap">
                <FiltersByCategory
                    :categories="categories"
                    :selected-category="selectedCategory"
                    v-model:selectedCategory="selectedCategory"
                />
            </div>

            <template v-if="loadingTemplates">
                <SkeletonsLoading 
                    :viewMode="viewMode"
                />
            </template>
            <template v-else>
                <TableInfoCount
                    :filtered-templates="allTemplates"
                    :all-templates="allTemplates"
                    :search-query="appliedSearch"
                />

                <div 
                    v-if="viewMode === 'list'" 
                    class="card !p-0 overflow-hidden"
                >
                    <ListMode 
                        :search-query="appliedSearch"
                        :selected-category="selectedCategory"
                        :current-page="currentPage"
                        :top-templates="topTemplates"
                        :rest-templates="restTemplates"
                        :filtered-templates="allTemplates"
                        :show-top="shouldShowTop"
                        @open="openTemplate"
                        @start="redirectTo"
                    />
                </div>
                <div v-else>
                    <CardMode
                        :filtered-templates="allTemplates"
                        @open="openTemplate"
                        @start="redirectTo"
                    />
                </div>

                <div 
                    v-if="totalPages > 1"
                    class="flex items-center justify-between max-[535px]:justify-center mt-2"
                >
                    <span class="text-xs text-surface-400 max-[535px]:hidden">
                        Página {{ currentPage }} de {{ totalPages }}
                    </span>
                    <Paginator
                        :rows="perPage"
                        :total-records="totalRecords"
                        :first="(currentPage - 1) * perPage"
                        :rows-per-page-options="[10, 30, 50]"
                        template="PrevPageLink PageLinks NextPageLink RowsPerPageDropdown"
                        @page="onPageChange"
                    />
                </div>
            </template>
        </div>
        <TemplateDetails
            v-model:visible="showModal"
            :template="selectedTemplate"
            @start="redirectTo"
        />
    </section>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue"
import { TableInfoCount, FiltersByCategory, SkeletonsLoading, ListMode, CardMode } from './components'
import { TemplatesService } from '@/service/TemplateService'
import { useRouter } from "vue-router";

const router = useRouter();

const searchQuery = ref("")
const showModal = ref(false)
const selectedTemplate = ref(null)
const allTemplates = ref([])
const loadingTemplates = ref(true)
const viewMode = ref(localStorage.getItem("viewMode") || "list")
const selectedCategory = ref("all")
const appliedSearch = ref("")

watch(viewMode, (newValue) => {
    localStorage.setItem("viewMode", newValue)
})

const viewOptions = [
    { label: "Lista", value: "list", icon: "pi pi-list" },
    { label: "Cards", value: "grid", icon: "pi pi-th-large" },
]

const totalAllRecords = ref(0)
const categoriesCount = ref([])
const getCategoriesCount = () => {
    TemplatesService.getCountCategories()
        .then((response) => {
            categoriesCount.value = response.categories
            totalAllRecords.value = response.total
        })
}
const categories = computed(() => {
    return [
        { value: "all", label: "Todos", count: totalAllRecords.value },
        ...categoriesCount.value.map(cat => ({
            value: cat.name,
            label: cat.name,
            count: cat.total
        }))
    ]
})

// TODO: PASSAR A LÓGICA DE TOP E REST PARA O BACKEND
const shouldShowTop = computed(() => {
    return (
        currentPage.value === 1 &&
        !appliedSearch.value &&
        selectedCategory.value === 'all' &&
        topTemplates.value.length > 0
    )
})

const TOP_COUNT = 3
const topIds = computed(() => new Set(topTemplates.value.map(t => t.id)))

const restTemplates = computed(() => {
    if (!shouldShowTop.value) {
        return allTemplates.value
    }

    return allTemplates.value.filter(t => !topIds.value.has(t.id))
})

watch(selectedCategory, () => {
    getTemplatesUsage(1)
})

let debounceTimer = null;
watch(searchQuery, () => {
    clearTimeout(debounceTimer)

    debounceTimer = setTimeout(() => {
        appliedSearch.value = searchQuery.value.trim()
        getTemplatesUsage(1)
    }, 600)
})

const openTemplate = (template) => {
    selectedTemplate.value = template
    showModal.value = true
}

const redirectTo = (template) => {
    router.push({
        name: 'upload',
        query: { template: template.id }
    });
}

const currentPage = ref(1)
const totalPages = ref(1)
const totalRecords = ref(0)
const perPage = ref(10)
const topTemplates = ref([])
const getTemplatesUsage = (page = 1) => {
    loadingTemplates.value = true

    TemplatesService.getTemplatesWithDocumentsCount(page, appliedSearch.value, selectedCategory.value, perPage.value)
        .then((response) => {
            topTemplates.value = response.top || []
            allTemplates.value = response.data

            currentPage.value = response.meta.current_page
            totalPages.value = response.meta.last_page
            totalRecords.value = response.meta.total
            perPage.value = response.meta.per_page
        })
        .finally(() => {
            loadingTemplates.value = false
        })
}

const onPageChange = (event) => {
    const page = event.page + 1

    perPage.value = event.rows

    getTemplatesUsage(page)
}

onMounted(() => {
    getTemplatesUsage()
    getCategoriesCount()
})
</script>