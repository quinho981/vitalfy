import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/axios'

const COOLDOWN_MS = 60_000
const COOLDOWN_STORAGE_KEY = 'email_verification_resend_available_at'

export const useEmailVerificationStore = defineStore('emailVerification', () => {
    const isModalOpen = ref(false)
    const sending = ref(false)
    const sent = ref(false)
    const cooldownRemaining = ref(0)

    let cooldownTimer = null

    const computeRemaining = (availableAt) => Math.max(0, Math.ceil((availableAt - Date.now()) / 1000))

    const stopCooldownTimer = () => {
        if (cooldownTimer) {
            clearInterval(cooldownTimer)
            cooldownTimer = null
        }
    }

    const startCooldown = (availableAt) => {
        sessionStorage.setItem(COOLDOWN_STORAGE_KEY, String(availableAt))
        cooldownRemaining.value = computeRemaining(availableAt)

        stopCooldownTimer()
        cooldownTimer = setInterval(() => {
            cooldownRemaining.value = computeRemaining(availableAt)
            if (cooldownRemaining.value <= 0) {
                stopCooldownTimer()
            }
        }, 1000)
    }

    // Retoma o cronômetro se a página foi recarregada em meio ao período de
    // espera — sem isto, um F5 zeraria o bloqueio client-side.
    const storedAvailableAt = Number(sessionStorage.getItem(COOLDOWN_STORAGE_KEY))
    if (storedAvailableAt && computeRemaining(storedAvailableAt) > 0) {
        startCooldown(storedAvailableAt)
    }

    const openModal = () => {
        isModalOpen.value = true
    }

    const closeModal = () => {
        isModalOpen.value = false
    }

    const resend = async () => {
        if (sending.value || cooldownRemaining.value > 0) {
            return
        }

        sending.value = true
        sent.value = false

        try {
            await api.post('/email/resend-verification')
            sent.value = true
            startCooldown(Date.now() + COOLDOWN_MS)
        } catch (error) {
            const retryAfterSeconds = Number(error?.response?.headers?.['retry-after'])
            if (error?.response?.status === 429) {
                startCooldown(Date.now() + (retryAfterSeconds > 0 ? retryAfterSeconds * 1000 : COOLDOWN_MS))
            }
            throw error
        } finally {
            sending.value = false
        }
    }

    return {
        isModalOpen,
        sending,
        sent,
        cooldownRemaining,
        openModal,
        closeModal,
        resend,
    }
})
