<script setup>
import { ref } from 'vue';
import { useLayout } from '@/layout/composables/layout';
import { authStore } from '@/stores/authStore'
import { useRouter } from 'vue-router';
import { PanelLeft  } from 'lucide-vue-next';

const { onMenuToggle, toggleDarkMode, isDarkTheme } = useLayout();

const auth = authStore();
const router = useRouter();
const overlayPanel = ref(null)
const loading = ref(false);

const toggleMenu = (event) => {
    overlayPanel.value.toggle(event)
}

const logout = async () => {
    loading.value = true;

    try {
        await auth.logout();
    } catch (error) {
        console.error('Logout failed:', error);
    } finally {
        loading.value = false;
        router.push({ name: 'login' });
    }
}
</script>

<template>
    <div class="layout-topbar !border-b-[1px] dark:border-gray-700">
        <div class="layout-topbar-logo-container">
            <router-link to="/" class="layout-topbar-logo !gap-0 flex items-center">
                <img src="/vitalfy-logo.png" alt="Vitalfy Logo" class="w-10 h-8" />
                <span class="font-semibold text-3xl text-slate-800 dark:text-slate-200">italfy</span>
            </router-link>
            <button class="layout-menu-button layout-topbar-action ml-[8.2rem]" @click="onMenuToggle">
                <PanelLeft :size="18" />
            </button>
        </div>

        <div class="layout-topbar-actions">
            <div class="layout-config-menu">
                <button type="button" class="layout-topbar-action" @click="toggleDarkMode">
                    <i :class="['pi', { 'pi-moon': isDarkTheme, 'pi-sun': !isDarkTheme }]"></i>
                </button>
            </div>
            <div class="layout-topbar-menu hidden lg:block">
                <div class="layout-topbar-menu-content">
                    <button type="button" class="layout-topbar-action" @click="toggleMenu">
                        <i class="pi pi-user"></i>
                        <span>Profile</span>
                    </button>
                    <OverlayPanel ref="overlayPanel">
                        <div class="flex flex-col gap-2 min-w-[150px]">
                            <Button 
                                @click="logout" 
                                :loading="loading" 
                                icon="pi pi-sign-out" 
                                :label='$t("button.leave")'
                                severity="danger" outlined
                            ></Button>
                        </div>
                    </OverlayPanel>
                </div>
            </div>
        </div>
    </div>
</template>
