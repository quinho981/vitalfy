<template>
    <div>
        <div v-if="loadingPage" class="flex justify-center p-10">
            <Loader2 :size="24" class="animate-spin mr-1" />
            <span class="ml-2">Carregando...</span>
        </div>
        <div v-else class="flex flex-col gap-5">
            <div class="border rounded-lg p-4 flex justify-between items-center dark:border-neutral-700">
                <div>
                    <p class="font-semibold text-lg">{{ subscription.plan?.name || 'Free' }}</p>
                    <p class="text-slate-500 dark:text-slate-400">Plano atual</p>
                    <div class="flex items-center gap-1">
                        <Calendar :size="12" /> <p class="text-sm">Adquirido em: {{ formatDate(subscription.subscription?.created_at) }}</p>
                    </div>
                </div>
                <Tag 
                    :value="subscription.has_subscription ? 'Ativo' : 'Inativo'" 
                    :severity="subscription.has_subscription ? 'success' : 'secondary'" 
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Loader2, Calendar } from 'lucide-vue-next';
import { SubscriptionService } from '@/service/SubscriptionService';
import { useUserStore } from '@/stores/userStore';
import { useHelpers } from '@/utils/helper';

const { formatDate } = useHelpers();
const userStore = useUserStore();

const subscription = ref({
    has_subscription: false,
    plan: { name: 'Free' },
    subscription: null
});
const loading = ref(false);
const loadingPage = ref(false);
const cancelling = ref(false);

const loadSubscription = async () => {
    loadingPage.value = true;
    try {
        const data = await SubscriptionService.getSubscription();
        subscription.value = data;
    } catch (error) {
        console.error('Error loading subscription:', error);
    } finally {
        loadingPage.value = false;
    }
};

const handleSubscribe = async (plan) => {
    loading.value = true;
    try {
        const response = await SubscriptionService.createCheckout(plan);
    
        // TODO: utilizar websocket para marcar o checkout
        localStorage.setItem('pending_checkout_session', response.session_id);
        localStorage.setItem('pending_checkout_session_at', String(Date.now()));
        window.location.href = response.url;
    } catch (error) {
        console.error('Error creating checkout:', error);
    } finally {
        loading.value = false;
    }
};

const handleCancelSubscription = async () => {
    if (!confirm('Tem certeza que deseja cancelar sua assinatura?')) {
        return;
    }
    
    cancelling.value = true;
    try {
        await SubscriptionService.cancelSubscription();
        await loadSubscription();
        await userStore.getUserInfo();
    } catch (error) {
        console.error('Error cancelling subscription:', error);
    } finally {
        cancelling.value = false;
    }
};

onMounted(() => {
    loadSubscription();
});
</script>

<style scoped>
</style>