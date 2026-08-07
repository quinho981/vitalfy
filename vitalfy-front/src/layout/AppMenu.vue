<script setup>
import { ref, computed } from 'vue';

import AppMenuItem from './AppMenuItem.vue';
import { useLayout } from '@/layout/composables/layout';
import { useUserStore } from '@/stores/userStore'
import { useRouter } from 'vue-router';
import { CircleQuestionMark, Plus } from 'lucide-vue-next';
import { SubscriptionService } from '@/service/SubscriptionService';
import { useHelpers } from '@/utils/helper';
import { FREE_TRANSCRIPT_LIMIT } from '@/utils/constants';

const { getNextMonthResetDate } = useHelpers();
const { resetMenu } = useLayout();
const userStore = useUserStore();
const router = useRouter();

const FREE_PLAN = 'Free';

const usedCount = computed(() => {
    const remaining = parseInt(userStore.remaining)
    return isNaN(remaining) ? 0 : Math.max(0, FREE_TRANSCRIPT_LIMIT - remaining)
})
const progressPercent = computed(() => Math.min((usedCount.value / FREE_TRANSCRIPT_LIMIT) * 100, 100))
const progressBarColor = computed(() => {
    const remaining = parseInt(userStore.remaining)
    if (isNaN(remaining) || remaining > 3) return 'bg-blue-400'
    if (remaining > 1) return 'bg-amber-400'
    return 'bg-red-500'
})
const modalHelpAndSupport = ref(false);
const modalSignatureActive = ref(false);
const isSubscribing = ref(false);

const model = ref([
    {
        label: 'Principal',
        items: [
            { label: 'Transcrição', icon: 'pi pi-fw pi-microphone', to: '/upload', visible: true },
            { label: 'Histórico', icon: 'pi pi-fw pi-history', to: '/transcripts/history', visible: true },
            { label: 'Templates', icon: 'pi pi-fw pi-file', to: '/templates', visible: true },
        ]
    },
    {
        label: 'Análises',
        items: [
            { label: 'Dashboard', icon: 'pi pi-fw pi-chart-bar', to: '/', visible: true },
            { label: 'Configurações', icon: 'pi pi-fw pi-cog', to: '/settings/user', visible: true },
        ]
    },
    
]);

const planColor = computed(() => {
    return userStore.plan === FREE_PLAN ? 'bg-blue-500' : 'bg-yellow-500';
});

const planColorHexdecimal = computed(() => {
    return userStore.plan === FREE_PLAN ? '#3b82f6' : '#eab308';
});

const redirectTo = (to) => {
    window.open(to, "_blank");
}

const closeSignatureModal = () => {
    modalSignatureActive.value = false;
}

const truncate = (text, maxLength) => {
    if (!text) return '';
    return (text.length >= maxLength) ? text.slice(0, maxLength) + '...' : text;
}

const redirectToTranscript = () => {
    if (router.currentRoute.value.name !== 'upload') {
        router.push({ name: 'upload' });
    }

    resetMenu();
}

const handleSubscribe = async (plan) => {
    isSubscribing.value = true;
    try {
        const data = await SubscriptionService.createCheckout(plan);
        localStorage.setItem('pending_checkout_session', data.session_id);
        localStorage.setItem('pending_checkout_session_at', String(Date.now()));
        window.location.href = data.url;
    } catch (e) {
        console.error(e);
    } finally {
        isSubscribing.value = false;
    }
}
</script>

<template>
    <div class="layout-sidebar">
        <div class="layout-menu">
            <Button 
                class="w-full !bg-blue-600 mb-2 mt-1 !rounded-lg !border-none dark:!text-white hover:!bg-blue-700"
                @click="redirectToTranscript"
            >
                <Plus :size="14  " /> Novo Atendimento
            </Button>
            <template v-for="(item, i) in model" :key="item">
                <app-menu-item
                    v-if="!item.separator" 
                    :item="item" 
                    :index="i"
                    @refresh-sidebar="index"
                ></app-menu-item>
                <li v-if="item.separator" class="menu-separator"></li>
            </template>
        </div>
        <div class="layout-sidebar-footer border-t dark:border-gray-700">
            <div class="flex flex-col">
                <a 
                    href="#" class="flex items-center gap-x-2 p-[8.4px] mt-2 hover:bg-surface-100 dark:hover:bg-[#27272a] rounded-lg transition-all duration-300 !w-full text-[14px] font-semibold" 
                    @click.prevent="modalHelpAndSupport = !modalHelpAndSupport"
                >
                    <CircleQuestionMark :size="18" />
                    <p>{{ $t("sidebar.helpAndSupport") }}</p>
                </a>

                <div
                    v-if="userStore.plan === 'Free'" 
                    class="pt-2"
                >
                    <button
                        class="w-full bg-transparent hover:bg-blue-50 dark:hover:bg-blue-950/40 border border-blue-400 dark:border-blue-500/50 active:scale-95 rounded-xl px-3.5 py-3 text-left cursor-pointer transition-all duration-150 outline-none"
                        @click="modalSignatureActive = !modalSignatureActive"
                    >
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 bg-blue-100 dark:bg-blue-400/15">
                                <i class="pi pi-bolt text-sm text-blue-600 dark:text-blue-400"></i>
                            </div>

                            <div>
                                <p class="text-xs font-medium leading-tight m-0 text-blue-800 dark:text-blue-300">
                                    {{ $t("button.signature.unlockProPlan") }}
                                </p>
                                <p
                                    v-if="userStore.remaining > 0"
                                    class="text-[11px] leading-tight mt-0.5 m-0 text-slate-500 dark:text-slate-400">
                                    {{ (userStore.remaining > 1) ? 'Restam apenas' : 'Resta apenas' }}
                                    <span class="font-bold text-[11.5px] text-blue-600 dark:text-blue-400">{{ userStore.remaining }}</span>
                                    {{ (userStore.remaining > 1) ? 'transcrições' : 'transcrição' }}
                                </p>
                                <p v-else class="text-[11px] leading-tight mt-0.5 m-0 text-slate-500 dark:text-slate-400">Seu limite renova em {{ getNextMonthResetDate() }}</p>
                            </div>
                        </div>
                        <div class="mt-2 w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                            <div
                                class="h-1.5 rounded-full transition-all duration-300"
                                :class="progressBarColor"
                                :style="{ width: progressPercent + '%' }"
                            ></div>
                        </div>
                        <p class="text-[10px] mt-1 m-0 text-slate-400 dark:text-slate-500">
                            {{ usedCount }} de {{ FREE_TRANSCRIPT_LIMIT }} usadas este mês
                        </p>
                    </button>
                </div>

                <div class="flex items-center pt-3 pb-3 relative">
                    <div class="relative flex flex-col items-center">
                        <Avatar
                            :label="userStore.username?.charAt(0) ?? '?'"
                            :class="{'mr-[8px]': userStore.plan !== 'Free'}"
                            class="mr-3 flex-shrink-0 uppercase !bg-gradient-to-br !from-blue-500 !to-blue-700 pb-1" 
                            size="small" 
                            :style="{ color: '#ffffff', border: `3px solid ${planColorHexdecimal}`, height: '2.4rem', width: '2.4rem' }" 
                            shape="circle">
                        </Avatar> 
                        <span 
                            class="absolute top-[26px] text-white text-xs font-semibold px-2 py-0.5 rounded-full"
                            :class="[
                                planColor,
                                userStore.plan === 'Free' ? 'right-[8px]' : 'right-[10px]'
                            ]"
                        >
                            {{ userStore.plan === 'Free' ? 'Free' : 'Pro' }}
                        </span>
                    </div>
                    <div class="flex flex-col w-full min-w-0">
                        <span class="block text-base text-left font-bold truncate">
                            {{ userStore.username }}
                        </span>
                        <span class="block text-left text-sm font-medium text-gray-500 dark:text-gray-300 truncate">
                            {{ userStore.userEmail }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!--- TODO: Transformar em componente -->
        <Dialog 
            :header='$t("sidebar.buttons.helpAndSupport")' 
            :visible="modalHelpAndSupport" 
            :breakpoints="{ '960px': '75vw' }" 
            :style="{ width: '27vw' }" 
            modal
            @update:visible="modalHelpAndSupport = $event"
            :dismissable-mask="true"
            :pt="{
                mask: {
                    style: 'backdrop-filter: blur(2px)'
                }
            }"
        >
            <div class="flex flex-col gap-y-2">
                <Button 
                    class="w-full"
                    :label='$t("sidebar.buttons.feedback")' 
                    icon="pi pi-fw pi-comments " 
                    severity="secondary"
                    @click='redirectTo("https://wa.me/5571999999999?text=Gostaria%20de%20passar%20um%20feedback")'
                />
                <Button 
                    class="w-full"
                    :label='$t("sidebar.buttons.talkToSupport")'
                    icon="pi pi-fw pi-phone " 
                    severity="secondary"
                    @click='redirectTo("https://wa.me/5571999999999?text=Quero%20de%20solicitar%20suporte")'
                />
            </div>
            <div class="flex flex-col sm:flex-row lg:flex-col xl:flex-row xl:justify-between justify-between my-1 font-semibold">
                <a 
                    href="https://wa.me/5571999999999" 
                    target="_blank"
                    class="flex justify-center sm:justify-start lg:justify-center xl:justify-start"    
                >
                    <Button label="+55 (71) 9 9999-9999" class="p-button-link" />
                </a>
                <a 
                    href="mailto:suportevital@vitalfy.com.br"
                    target="_blank"
                    class="flex justify-center sm:justify-start lg:justify-center xl:justify-start"
                >
                    <Button label="suportevital@vitalfy.com.br" class="p-button-link" />
                </a>
            </div>
        </Dialog>

        <Signature 
            :active="modalSignatureActive"
            :loading="isSubscribing"
            @subscribe="handleSubscribe"
            @close="closeSignatureModal" 
        />
    </div>
</template>

<style lang="scss" scoped>
</style>