<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { Loader2, CheckCircle2, Crown } from 'lucide-vue-next';
import { useUserStore } from '@/stores/userStore';
import { SubscriptionService } from '@/service/SubscriptionService.js';

const { t } = useI18n();
const userStore = useUserStore();
const router = useRouter();

const loadingPlan = ref(null);

const periods = [
    {
        key: 'pro_annual',
        label: 'Anual',
        price: 'R$ 124',
        billing: 'R$ 1.497,00 cobrado anualmente',
        badge: 'Mais popular',
        highlight: true,
        savings: '-38%',
    },
    {
        key: 'pro_semester',
        label: 'Semestral',
        price: 'R$ 149',
        billing: 'R$ 897,00 cobrado semestralmente',
        badge: null,
        highlight: false,
        savings: '-25%',
    },
    {
        key: 'pro_monthly',
        label: 'Mensal',
        price: 'R$ 199',
        billing: 'Cobrado mensalmente',
        badge: null,
        highlight: false,
        savings: null,
    },
];

const benefits = [
    t('signature.modal.benefits.benefit1'),
    'Gere documentos clínicos organizados em segundos',
    t('signature.modal.benefits.benefit3'),
    t('signature.modal.benefits.benefit4'),
    t('signature.modal.benefits.benefit5'),
    t('signature.modal.benefits.benefit6'),
    t('signature.modal.benefits.benefit7'),
];

const subscribe = async (planKey) => {
    loadingPlan.value = planKey;
    try {
        const data = await SubscriptionService.createCheckout(planKey);
        localStorage.setItem('pending_checkout_session', data.session_id);
        localStorage.setItem('pending_checkout_session_at', String(Date.now()));
        window.location.href = data.url;
    } catch (e) {
        console.error(e);
    } finally {
        loadingPlan.value = null;
    }
};
</script>

<template>
    <div class="min-h-screen px-4 py-12 dark:bg-surface-900">
        <div class="max-w-4xl mx-auto">

            <!-- Header -->
            <div class="text-center mb-10">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">
                    Escolha o melhor plano para você
                </h1>
                <p class="text-gray-500 dark:text-slate-400 text-base max-w-lg mx-auto leading-relaxed">
                    Economize tempo, atenda mais e documente menos com a Vitalfy Pro.
                </p>
            </div>

            <!-- Already Pro state -->
            <div
                v-if="userStore.plan !== 'Free'"
                class="max-w-md mx-auto bg-gradient-to-br from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 border border-yellow-200 dark:border-yellow-700/40 rounded-2xl p-8 text-center"
            >
                <div class="w-14 h-14 rounded-full bg-yellow-100 dark:bg-yellow-800/40 flex items-center justify-center mx-auto mb-4">
                    <Crown :size="28" class="text-yellow-500" />
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                    Você já é Pro!
                </h2>
                <p class="text-gray-500 dark:text-slate-400 text-sm mb-6">
                    Seu plano profissional está ativo. Gerencie sua assinatura nas configurações.
                </p>
                <Button
                    label="Gerenciar assinatura"
                    icon="pi pi-cog"
                    @click="router.push({ name: 'settingsUser' })"
                    severity="warning"
                    class="w-full"
                />
            </div>

            <!-- Pricing cards (Free users) -->
            <template v-else>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-10">
                    <div
                        v-for="period in periods"
                        :key="period.key"
                        :class="[
                            'relative flex flex-col rounded-2xl border p-6 transition-shadow',
                            period.highlight
                                ? 'border-blue-500 shadow-lg shadow-blue-100 dark:shadow-blue-900/30 bg-white dark:bg-surface-800'
                                : 'border-gray-200 dark:border-surface-700 bg-white dark:bg-surface-800 hover:shadow-md'
                        ]"
                    >
                        <!-- Most popular badge -->
                        <div
                            v-if="period.badge"
                            class="absolute -top-3 left-1/2 -translate-x-1/2 bg-blue-600 text-white text-xs font-semibold px-3 py-1 rounded-full whitespace-nowrap"
                        >
                            {{ period.badge }}
                        </div>

                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-semibold text-gray-700 dark:text-slate-300">
                                {{ period.label }}
                            </span>
                            <span
                                v-if="period.savings"
                                class="text-xs font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400 px-2 py-0.5 rounded-full"
                            >
                                {{ period.savings }}
                            </span>
                        </div>

                        <div class="mb-1">
                            <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ period.price }}</span>
                            <span class="text-gray-400 text-sm">/mês</span>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-slate-500 mb-6">{{ period.billing }}</p>

                        <div class="flex-1"></div>

                        <button
                            :disabled="loadingPlan !== null"
                            @click="subscribe(period.key)"
                            :class="[
                                'w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-semibold transition-all duration-150 cursor-pointer',
                                period.highlight
                                    ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm'
                                    : 'bg-gray-100 hover:bg-gray-200 dark:bg-surface-700 dark:hover:bg-surface-600 text-gray-800 dark:text-white',
                                loadingPlan !== null ? 'opacity-60 cursor-not-allowed' : ''
                            ]"
                        >
                            <Loader2
                                v-if="loadingPlan === period.key"
                                :size="15"
                                class="animate-spin"
                            />
                            {{ loadingPlan === period.key ? 'Aguarde...' : 'Assinar agora' }}
                        </button>
                    </div>
                </div>

                <!-- Benefits -->
                <div class="bg-gray-50 dark:bg-surface-800 border border-gray-100 dark:border-surface-700 rounded-2xl p-7">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-slate-300 mb-5">
                        O que você ganha com o plano Pro:
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div
                            v-for="benefit in benefits"
                            :key="benefit"
                            class="flex items-start gap-2.5"
                        >
                            <CheckCircle2 :size="17" class="text-emerald-500 flex-shrink-0 mt-0.5" />
                            <span class="text-sm text-gray-700 dark:text-slate-300 leading-snug">
                                {{ benefit }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Footer note -->
                <p class="text-center text-xs text-gray-400 dark:text-slate-500 mt-6">
                    <i class="pi pi-lock mr-1"></i>
                    Pagamento seguro via Stripe · Cancele quando quiser · Mais de 500 médicos já usam a Vitalfy
                </p>
            </template>
        </div>
    </div>
</template>
