<template>
    <div class="min-h-screen flex flex-col md:flex-row bg-gray-50 dark:bg-zinc-900">
        <FloatingConfigurator />
        <AsideImage />

        <div class="w-full md:w-1/2 flex items-center justify-center px-6 py-10">
            <div class="w-full max-w-md">

                <div class="text-center mb-8">
                    <div class="flex items-center justify-center">
                        <img src="/vitalfy-logo.png" alt="Vitalfy Logo" class="w-14 h-12" />
                        <h1 class="text-5xl font-semibold text-gray-800 tracking-tight dark:text-gray-200">
                            italfy
                        </h1>
                    </div>

                    <h2 class="text-2xl font-semibold text-gray-700 mt-4 dark:text-gray-300">
                        {{ $t("auth.resetPassword.title") }}
                    </h2>

                    <p class="text-sm text-gray-500 mt-2 dark:text-gray-400">
                        {{ $t("auth.resetPassword.description") }}
                    </p>
                </div>

                <div class="bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-xl">
                    <div v-if="!token || !emailParam" class="text-center">
                        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 mx-auto mb-4">
                            <ShieldAlert :size="30" class="text-red-500 dark:text-red-400" />
                        </div>

                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            {{ $t("auth.resetPassword.invalidToken") }}
                        </p>

                        <router-link
                            :to="{ name: 'forgotPassword' }"
                            class="block w-full py-3 text-center text-sm font-semibold text-white bg-blue-500 rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            {{ $t("auth.resetPassword.requestNewLink") }}
                        </router-link>
                    </div>

                    <div v-else-if="success" class="text-center">
                        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 mx-auto mb-4">
                            <ShieldCheck :size="30" class="text-green-600 dark:text-green-400" />
                        </div>

                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            {{ $t("notifications.messages.passwordResetSuccess") }}
                        </h3>

                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                            {{ $t("auth.forgotPassword.backToLogin") }}...
                        </p>

                        <router-link
                            :to="{ name: 'login' }"
                            class="block w-full py-3 text-center text-sm font-semibold text-white bg-blue-500 rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            {{ $t("auth.forgotPassword.backToLogin") }}
                        </router-link>
                    </div>

                    <form v-else @submit.prevent="submit" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                                {{ $t("auth.resetPassword.newPassword") }}
                            </label>

                            <Password
                                v-model="form.password"
                                :placeholder='$t("auth.form.label.enterPassword")'
                                :toggleMask="true"
                                fluid
                                :feedback="true"
                                :class="['w-full !rounded-lg', errors.password ? 'p-invalid' : '']"
                                inputClass="!px-4 !py-3 !rounded-lg !border-gray-200 dark:!border-zinc-600"
                                promptLabel="Escolha a senha"
                                weakLabel="Muito fraca"
                                mediumLabel="Média"
                                strongLabel="Forte"
                            />

                            <small class="p-error block mt-1" v-if="errors.password">
                                {{ errors.password }}
                            </small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                                {{ $t("auth.resetPassword.confirmNewPassword") }}
                            </label>

                            <Password
                                v-model="form.password_confirmation"
                                :placeholder='$t("auth.form.label.confirmPassword")'
                                :toggleMask="true"
                                fluid
                                :feedback="false"
                                :class="['w-full !rounded-lg', errors.password_confirmation ? 'p-invalid' : '']"
                                inputClass="!px-4 !py-3 !rounded-lg !border-gray-200 dark:!border-zinc-600"
                            />

                            <small class="p-error block mt-1" v-if="errors.password_confirmation">
                                {{ errors.password_confirmation }}
                            </small>
                        </div>

                        <Button
                            :label='$t("auth.form.button.resetPassword")'
                            :loading="loading"
                            type="submit"
                            class="w-full !py-3 !text-base !font-semibold !rounded-lg shadow-md"
                        />
                    </form>
                </div>

                <p v-if="!success" class="text-sm text-gray-500 text-center mt-6 dark:text-gray-400">
                    <router-link
                        :to="{ name: 'login' }"
                        class="text-primary font-medium hover:underline cursor-pointer"
                    >
                        ← {{ $t("auth.forgotPassword.backToLogin") }}
                    </router-link>
                </p>

            </div>
        </div>

        <Toast />
    </div>
</template>

<script setup>
import { defineAsyncComponent, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { authStore } from '@/stores/authStore';
import { resetPasswordSchema } from '@/validations/authSchema';
import { useShowToast } from '@/utils/useShowToast';
import { useI18n } from 'vue-i18n';
import AsideImage from './components/AsideImage.vue';
import { ShieldCheck, ShieldAlert } from 'lucide-vue-next';

const Toast = defineAsyncComponent(() => import('primevue/toast'));

const { t } = useI18n();
const route  = useRoute();
const router = useRouter();
const auth   = authStore();
const { showError } = useShowToast();

const token      = route.query.token ?? '';
const emailParam = route.query.email ?? '';

const form = ref({
    password: '',
    password_confirmation: '',
});
const errors  = ref({});
const loading = ref(false);
const success = ref(false);

const submit = async () => {
    errors.value = {};

    try {
        resetPasswordSchema.parse(form.value);
    } catch (e) {
        e.errors.forEach((err) => {
            if (err.path[0]) errors.value[err.path[0]] = err.message;
        });
        return;
    }

    loading.value = true;

    try {
        await auth.resetPassword({
            token,
            email: emailParam,
            password: form.value.password,
            password_confirmation: form.value.password_confirmation,
        });

        success.value = true;

        setTimeout(() => router.push({ name: 'login' }), 3000);
    } catch (err) {
        const message = err?.response?.data?.message ?? t('notifications.messages.passwordResetError');

        if (message.toLowerCase().includes('token') || message.toLowerCase().includes('inválido')) {
            showError(t('notifications.titles.error'), t('auth.resetPassword.invalidToken'), 5000);
        } else {
            showError(t('notifications.titles.error'), message, 4000);
        }
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.p-error {
    color: #dc3545;
    font-size: 0.875rem;
}
</style>
