import { createRouter, createWebHistory } from 'vue-router';
import authGuard from './middleware/authGuard';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: () => import('@/layout/AppLayout.vue'),
            children: [
                {
                    path: '/templates',
                    name: 'templates',
                    component: () => import('@/views/templates/index.vue'),
                    meta: { requiresAuth: true }
                },
                {
                    path: '/upload',
                    name: 'upload',
                    component: () => import('@/views/transcription/upload.vue'),
                    meta: { requiresAuth: true }
                },
                {
                    path: '/',
                    name: 'dashboard',
                    component: () => import('@/views/Dashboard.vue'),
                    meta: { requiresAuth: true }
                },
                {
                    path: '/pages/empty',
                    name: 'empty',
                    component: () => import('@/views/pages/Empty.vue'),
                    meta: { requiresAuth: true }
                },
                {
                    path: '/transcripts/list',
                    name: 'transcriptsList',
                    component: () => import('@/views/transcripts/List.vue'),
                    meta: { requiresAuth: true }
                },
                {
                    path: '/transcripts/history',
                    name: 'transcriptsHistory',
                    component: () => import('@/views/transcripts/History.vue'),
                    meta: { requiresAuth: true }
                },
                {
                    path: '/transcripts/:id',
                    name: 'transcriptsShow',
                    component: () => import('@/views/transcripts/[id].vue'),
                    meta: { requiresAuth: true }
                },
                {
                    path: '/settings/user',
                    name: 'settingsUser',
                    component: () => import('@/views/settings/index.vue'),
                    meta: { requiresAuth: true }
                },
                {
                    path: '/subscription',
                    name: 'subscription',
                    component: () => import('@/views/subscription/SubscriptionView.vue'),
                    meta: { requiresAuth: true }
                },
            ]
        },
        {
            // todo: remover
            path: '/teste',
            name: 'teste',
            component: () => import('@/views/pages/auth/teste.vue')
        },
        {
            path: '/pages/notfound',
            name: 'notfound',
            component: () => import('@/views/pages/NotFound.vue'),
            meta: { requiresAuth: true }
        },
        {
            path: '/auth',
            children: [
                {
                    path: '/auth/login',
                    name: 'login',
                    component: () => import('@/views/pages/auth/Login.vue')
                },
                {
                    path: '/auth/register',
                    name: 'register',
                    component: () => import('@/views/pages/auth/Register.vue')
                },
                {
                    path: '/auth/forgot-password',
                    name: 'forgotPassword',
                    component: () => import('@/views/pages/auth/ForgotPassword.vue')
                },
                {
                    path: '/auth/reset-password',
                    name: 'resetPassword',
                    component: () => import('@/views/pages/auth/ResetPassword.vue')
                },
                {
                    path: '/auth/social-callback',
                    name: 'socialCallback',
                    component: () => import('@/views/pages/auth/SocialCallback.vue')
                },
                {
                    path: '/auth/email-verified',
                    name: 'emailVerified',
                    component: () => import('@/views/pages/auth/EmailVerified.vue')
                },
            ]
        },
        {
            path: '/auth/access',
            name: 'accessDenied',
            component: () => import('@/views/pages/auth/Access.vue'),
            meta: { requiresAuth: true }
        },
        {
            path: '/auth/error',
            name: 'error',
            component: () => import('@/views/pages/auth/Error.vue'),
            meta: { requiresAuth: true }
        }
    ]
});

router.beforeEach(authGuard);

export default router;
