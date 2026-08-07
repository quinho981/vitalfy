<script setup>
import { ref, onMounted } from 'vue';
import { useUserStore } from '@/stores/userStore'
import { authStore as useAuthStore } from '@/stores/authStore'
import SubscriptionSuccessModal from '@/components/Modal/SubscriptionSuccessModal.vue'

const showSubscriptionSuccessModal = ref(false)
const userStore = useUserStore()
const authStore = useAuthStore()

const CHECKOUT_SESSION_TTL_MS = 60 * 60 * 1000 // 1 hora

onMounted(() => {
    if (!authStore.isAuthenticated) return

    // Lê remaining do cache assinado — síncrono, sem bloquear
    userStore.initRemainingFromCache()

    // Refresha do servidor em background — não bloqueia o render inicial
    userStore.getUserInfo().then(() => {
        const pendingCheckout = localStorage.getItem('pending_checkout_session')
        if (!pendingCheckout) return

        const savedAt = localStorage.getItem('pending_checkout_session_at')
        const expired = savedAt && (Date.now() - Number(savedAt)) > CHECKOUT_SESSION_TTL_MS
        if (expired) {
            localStorage.removeItem('pending_checkout_session')
            localStorage.removeItem('pending_checkout_session_at')
            return
        }

        if (userStore.plan !== 'Free') {
            showSubscriptionSuccessModal.value = true
        }

        localStorage.removeItem('pending_checkout_session')
        localStorage.removeItem('pending_checkout_session_at')
    })
})
</script>

<template>
<div>
    <router-view />

    <SubscriptionSuccessModal
        v-model:visible="showSubscriptionSuccessModal"
    />
</div>
</template>

<style scoped></style>
