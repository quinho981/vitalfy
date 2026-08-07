import { authStore } from '@/stores/authStore'

export default function authGuard(to, from, next) {
    const auth = authStore();
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);

    if (requiresAuth && !auth.isAuthenticated) {
        return next('/auth/login');
    }

    next();
}