<template>
    <div className="card mr-5">
        <div class="font-semibold text-xl mb-4">{{ $t("transcription.listPage.allTranscripts") }}</div>
        <DataTable 
            :value="transcripts" 
            tableStyle="min-width: 50rem" 
            :loading="loading"
            scrollable scrollHeight="480px"
        >
            <Column field="code" :header='$t("transcription.listPage.table.title")' class="w-[75%]">
                <template #body="{ data }">
                    <p 
                        @click="redirectTo(data)"
                        class="cursor-pointer"
                        v-if="editingId !== data.id"
                    >
                        {{data.title}}
                    </p>
                    <InputText 
                        v-else-if="editingId === data.id"
                        type="text" 
                        v-model="data.title" 
                        class="w-full text-base" 
                    />
                </template>
            </Column>
            <Column field="created_at" :header='$t("transcription.listPage.table.createdAt")' class="w-[12%]">
                <template #body="{ data }">
                    {{ new Date(data.created_at).toLocaleDateString() }}
                </template>
            </Column>
            <Column :header='$t("transcription.listPage.table.actions")' >
                <template #body="{ data }">
                    <div v-if="editingId !== data.id">
                        <Button 
                            icon="pi pi-trash" 
                            severity="secondary" 
                            @click="deleteConfirmation(data)"
                            class="mr-3 mb-2 xl:mb-0" 
                            v-tooltip.top="$t('button.exclude')"
                        />
                        <Button 
                            icon="pi pi-pencil" 
                            severity="primary" 
                            @click="toggleEdit(data)" 
                            v-tooltip.top="$t('button.rename')"
                        />
                    </div>
                    <div v-else-if="editingId === data.id">
                        <Button 
                            icon="pi pi-times" 
                            severity="secondary" 
                            outlined
                            @click="toggleEdit(data)" 
                            class="mr-3 mb-2 xl:mb-0" 
                            v-tooltip.top="$t('button.cancel')"
                        />
                        <Button 
                            icon="pi pi-check" 
                            severity="primary" 
                            outlined
                            @click="renameItem(data)" 
                            class="mr-3 mb-2 xl:mb-0" 
                            v-tooltip.top="$t('button.save')"
                        />
                    </div>
                </template>
            </Column>
            <template #empty>{{ $t("transcription.noTranscriptsFound") }}...</template>
        </DataTable>
        <Paginator 
            class="mt-5"
            :rows="10" 
            :totalRecords="total" 
            @page="onPage"
        ></Paginator>
        <DeleteConfirmation 
            :active="dialogConfirmation"
            :item="item"
            :loading="dialogLoading"
            @close="dialogConfirmation = false" 
            @confirm="deleteItem"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { TranscriptsService } from '@/service/TranscriptsService';
import { useShowToast } from '@/utils/useShowToast';
import { useI18n } from 'vue-i18n';
import { useRouter } from "vue-router";

const { t } = useI18n();
const { showSuccess, showError } = useShowToast();
const router = useRouter();

const transcripts = ref([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const item = ref({});
const dialogConfirmation = ref(false);
const dialogLoading = ref(false);
const editingId = ref(null);

const index = async () => {
    loading.value = true;

    try {
        const response = await TranscriptsService.index(page.value);

        transcripts.value = response.transcripts;
        total.value = response.total;
    } catch (error) {
        showError(t('notifications.titles.error'), t('notifications.messages.dataLoadingError'), 3000);
    } finally {
        loading.value = false;
    }
}

const onPage = (event) => {
    page.value = event.page + 1;

    index();
};

const toggleEdit = (item) => {
    editingId.value = editingId.value === item.id ? null : item.id;
};

// TODO: RETIRAR O TRY..CATCH
const deleteItem = async (item) => {
    dialogLoading.value = true;

    try {
        const response = await TranscriptsService.delete(item.id);

        if (response.status === 200) {
            transcripts.value = transcripts.value.filter(transcript => transcript.id !== item.id);
            total.value -= 1;
            dialogConfirmation.value = false;
            showSuccess(t('notifications.titles.success'), t('notifications.messages.itemExcludedSuccessfully'), 3000);
            await index();
        }

        return response;
    } catch (error) {
        showError(t('notifications.titles.error'), t('notifications.messages.itemExcludingError'), 3000);
    } finally {
        dialogLoading.value = false;
    }
};

const deleteConfirmation = (selectedItem) => {
    item.value = selectedItem;
    dialogConfirmation.value = !dialogConfirmation.value;
};

const renameItem = async (item) => {
    loading.value = true;

    try {
        const response = await TranscriptsService.update(item);

        if (response.status === 200) {
            transcripts.value = transcripts.value.map(transcript => 
                transcript.id === item.id ? { ...transcript, title: item.title } : transcript
            );
            editingId.value = null;
            showSuccess(t('notifications.titles.success'), t('notifications.messages.dataSavedSuccessfully'), 3000);
        }

        return response;
    } catch (error) {
        showError(t('notifications.titles.error'), t('notifications.messages.dataSavingError'), 3000);
    } finally {
        loading.value = false;
    }
};

const redirectTo = (data) => {
    router.push({
        name: 'transcriptsShow',
        params: { id: data.id }
    });
}

onMounted(() => {
    index();
});
</script>

<style scoped>
</style>