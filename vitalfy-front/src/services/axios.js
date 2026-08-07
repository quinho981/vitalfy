import axios from 'axios';
import Cookies from 'js-cookie';
import router from '@/router';

const api = axios.create({
    baseURL: import.meta.env.VITE_BASE_URL,
    withCredentials: true, // envia cookies HttpOnly automaticamente em toda requisição
    headers: {
        'Content-Type': 'application/json',
    },
});

// Nenhuma injeção manual de token — o middleware TokenFromCookie no backend
// lê o cookie HttpOnly api_token e o converte em Bearer header antes do Sanctum.
api.interceptors.request.use(
    (config) => config,
    (error) => Promise.reject(error)
);

api.interceptors.response.use(
    (response) => response,
    (error) => {
        const STATUS = error?.response?.status;
        const REQUIRES_PRO = error.response?.data?.requires_pro;

        if (STATUS === 401) {
            Cookies.remove('logged_in');
            Cookies.remove('client_nonce');
            router.push({ name: 'login' });
        }

        if (STATUS === 403 && !REQUIRES_PRO) {
            router.push({ name: 'error' });
        }

        return Promise.reject(error);
    }
);

export default api;
