<template>
    <section>
        <div class="mb-3 py-3">
            <h1 class="text-3xl font-bold">Configurações</h1>
            <p class="my-1 text-lg text-surface-500">Gerencie suas informações e preferências</p>
        </div>

        <div class="card settings-tabs mb-5">
            <Tabs v-model:value="activeTab" scrollable>
                <TabList>
                    <Tab value="profile">Meu perfil</Tab>
                    <Tab value="preferences">Preferências</Tab>
                    <Tab value="plan" v-if="userStore.plan !== 'Free'">Plano</Tab>
                    <Tab value="security">Segurança</Tab>
                </TabList>
            </Tabs>
        </div>

        <div class="card p-6">
            <div v-if="activeTab === 'profile'">
                <ProfileTab
                    :loadingTemplates="loadingTemplates"
                    :specialties="specialties"
                    @actionResponse="handleResponse"
                />
            </div>

            <div v-if="activeTab === 'preferences'">
                <PreferencesTab />
            </div>

            <div v-if="activeTab === 'plan'">
                <PlanTab />
            </div>

            <div v-if="activeTab === 'security'">
                <SecurityTab 
                    @actionResponse="handleResponse"
                />
            </div>
        </div>
        <Toast />
    </section>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { ProfileTab, PreferencesTab, PlanTab, SecurityTab } from './components'
import { useShowToast } from '@/utils/useShowToast';
import { useI18n } from 'vue-i18n';
import { SelectOptionsService } from '@/service/SelectOptionsService'
import { useUserStore } from '@/stores/userStore';

const { showSuccess, showError } = useShowToast();
const userStore = useUserStore();
const { t } = useI18n();

const activeTab = ref('profile');
const loadingTemplates = ref(false)
const specialties = ref([])

const handleResponse = (value) => {
    return value.success
        ? showSuccess(t('notifications.titles.success'), value.message, 4000)
        : showError(t('notifications.titles.error'), value.message, 4000)
}

const loadTemplates = () => {
    loadingTemplates.value = true
    
    SelectOptionsService.getTemplatesMinimal()
        .then((response) => {
            specialties.value = response
        })
        .finally(() => {
            loadingTemplates.value = false;
        });
}

onMounted(() => {
    loadTemplates();
});
</script>

<style scoped>
.settings-tabs :deep(.p-tablist-tab-list) {
    min-width: max-content;
}
.settings-tabs :deep(.p-tablist-viewport) {
    -webkit-overflow-scrolling: touch;
}
@media (max-width: 575px) {
    .settings-tabs {
        padding: 0.5rem;
    }

    .settings-tabs :deep(.p-tab) {
        padding: 1rem 0.75rem;
    }
}
</style>