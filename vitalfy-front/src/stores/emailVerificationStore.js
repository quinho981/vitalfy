import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/axios'

// R5 (ai-vitalfy/action-plans/R5.md): estado compartilhado pelo banner
// (FE-R5-01, sempre visível para conta não verificada) e pelo modal
// (FE-R5-02, aberto pelo interceptor do axios quando uma das cinco rotas de
// custo responde 409 com email_verification_required).
export const useEmailVerificationStore = defineStore('emailVerification', () => {
    const isModalOpen = ref(false)
    const sending = ref(false)
    const sent = ref(false)

    const openModal = () => {
        isModalOpen.value = true
    }

    const closeModal = () => {
        isModalOpen.value = false
    }

    const resend = async () => {
        sending.value = true
        sent.value = false

        try {
            await api.post('/email/resend-verification')
            sent.value = true
        } finally {
            sending.value = false
        }
    }

    return {
        isModalOpen,
        sending,
        sent,
        openModal,
        closeModal,
        resend,
    }
})
