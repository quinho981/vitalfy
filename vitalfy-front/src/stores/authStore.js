import { defineStore } from 'pinia'
import { computed } from 'vue'
import api from '@/services/axios';
import Cookies from 'js-cookie'
import { useUserStore } from '@/stores/userStore'

export const authStore = defineStore('auth', () => {
    // O token JWT vive num cookie HttpOnly controlado pelo servidor.
    // O frontend não consegue lê-lo — usa logged_in como indicador de sessão ativa.
    const isAuthenticated = computed(() => !!Cookies.get('logged_in'))

    const register = async (payload) => {
        try {
            const response = await api.post('/register', payload)
            return response.data
        } catch (error) {
            return Promise.reject(error)
        }
    }

    const login = async (payload) => {
        try {
            const response = await api.post('/login', payload)
            // O servidor seta api_token (HttpOnly), logged_in e client_nonce via Set-Cookie.
            // Não há token no body — nenhum gerenciamento de cookie aqui.
            return response.data
        } catch (error) {
            return Promise.reject(error)
        }
    }

    const logout = async () => {
        const userStore = useUserStore()

        try {
            const response = await api.post('/logout', null)

            userStore.reset()

            // O servidor limpa api_token e logged_in via Set-Cookie.
            // Apenas removemos client_nonce localmente (não é HttpOnly).
            Cookies.remove('client_nonce')
            Cookies.remove('logged_in')

            return response.data
        } catch (error) {
            return Promise.reject(error)
        }
    }

    const forgotPassword = async (email) => {
        const response = await api.post('/forgot-password', { email })
        return response.data
    }

    const resetPassword = async (payload) => {
        const response = await api.post('/reset-password', payload)
        return response.data
    }

    return {
        isAuthenticated,
        register,
        login,
        logout,
        forgotPassword,
        resetPassword,
    }
})
