import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/axios';
import Cookies from 'js-cookie'
import { secureStorage } from '@/utils/secureStorage'

export const useUserStore = defineStore('user', () => {
    const userId = ref(null)
    const userPhone = ref(null)

    const username  = ref(Cookies.get('username')   || null)
    const userEmail = ref(Cookies.get('user_email') || null)

    // plan e active: apenas em memória. Sempre vêm do servidor via getUserInfo().
    const plan = ref(null)
    const active = ref(null)

    // remaining: persiste com assinatura HMAC para detectar adulteração entre navegações
    const remaining = ref(null)
    const recordingTourCompleted = ref(localStorage.getItem('recording_tour_completed') === 'true')

    // R5 (ai-vitalfy/action-plans/R5.md): estado em memória, sempre vindo do
    // servidor via getUserInfo() — não persiste entre reloads por design, o
    // mesmo padrão de plan/active acima, para nunca confiar num valor velho.
    const emailVerified = ref(null)

    const getUserInfo = async () => {
        const rememberMe = Cookies.get('remember') === 'true'
        const cookieOpts = rememberMe ? { expires: 30 } : {}

        try {
            const response = await api.get('/user')

            userId.value               = response.data.user.id
            username.value             = response.data.user.name
            userEmail.value            = response.data.user.email
            userPhone.value            = response.data.user.phone
            plan.value                 = response.data.plan.name
            active.value               = response.data.plan.name !== 'Free'
            remaining.value            = response.data.remaining
            recordingTourCompleted.value = response.data.user.recording_tour_completed || false
            emailVerified.value        = response.data.user.email_verified

            Cookies.set('username',   username.value,  cookieOpts)
            Cookies.set('user_email', userEmail.value, cookieOpts)

            await secureStorage.set('remaining', remaining.value)

            localStorage.setItem('recording_tour_completed', recordingTourCompleted.value)

            return response.data
        } catch (error) {
            console.error(error)
        }
    }

    // Lê remaining do cache assinado — roda antes de getUserInfo() para
    // popular o valor imediatamente sem aguardar a API.
    const initRemainingFromCache = async () => {
        const cached = await secureStorage.get('remaining')
        if (cached !== null) {
            remaining.value = cached
        }
    }

    const reset = () => {
        userId.value               = null
        username.value             = null
        userEmail.value            = null
        userPhone.value            = null
        plan.value                 = null
        active.value               = null
        remaining.value            = null
        recordingTourCompleted.value = false
        emailVerified.value        = null

        Cookies.remove('username')
        Cookies.remove('user_email')
        secureStorage.remove('remaining')
        localStorage.removeItem('recording_tour_completed')
    }

    return {
        userId,
        username,
        userEmail,
        userPhone,
        plan,
        active,
        remaining,
        recordingTourCompleted,
        emailVerified,
        getUserInfo,
        initRemainingFromCache,
        reset,
    }
})
