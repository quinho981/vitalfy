<template>
    <div class="min-h-screen flex flex-col md:flex-row bg-gray-50 dark:bg-zinc-900">
        <FloatingConfigurator />
        <AsideImage />

        <div class="w-full md:w-1/2 flex items-center justify-center px-6 py-10">
            <div class="w-full max-w-md">

                <!-- Logo -->
                <div class="text-center mb-8">
                    <div class="flex items-center justify-center">
                        <img src="/vitalfy-logo.png" alt="Vitalfy Logo" class="w-14 h-12" />
                        <h1 class="text-5xl font-semibold text-gray-800 tracking-tight dark:text-gray-200">
                            italfy
                        </h1>
                    </div>

                    <h2 class="text-2xl font-semibold text-gray-700 mt-4 dark:text-gray-300">
                        {{ $t("auth.forgotPassword.title") }}
                    </h2>

                    <p class="text-sm text-gray-500 mt-2 dark:text-gray-400">
                        {{ $t("auth.forgotPassword.description") }}
                    </p>
                </div>

                <!-- Card -->
                <div class="bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-xl">

                    <!-- Estado: e-mail enviado -->
                    <div v-if="sent" class="text-center">
                        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 mx-auto mb-4">
                            <MailCheck :size="30" class="text-green-600 dark:text-green-400" />
                        </div>

                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            {{ $t("auth.forgotPassword.emailSentTitle") }}
                        </h3>

                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-6">
                            {{ $t("auth.forgotPassword.emailSentDescription") }}
                        </p>

                        <div class="space-y-3">
                            <router-link
                                :to="{ name: 'login' }"
                                class="block w-full py-3 text-center text-sm font-semibold text-white bg-blue-500 rounded-lg hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition-colors"
                            >
                                {{ $t("auth.forgotPassword.backToLogin") }}
                            </router-link>

                            <button
                                @click="reset"
                                class="block w-full py-3 text-center text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
                            >
                                {{ $t("auth.forgotPassword.tryAnotherEmail") }}
                            </button>
                        </div>
                    </div>

                    <!-- Estado: formulário -->
                    <form v-else @submit.prevent="submit" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                                {{ $t("form.label.email") }}
                            </label>

                            <InputText
                                v-model="email"
                                type="email"
                                :placeholder='$t("auth.form.label.enterEmail")'
                                class="w-full !px-4 !py-3 !rounded-lg !border-gray-200 dark:!border-zinc-600"
                                :class="{ 'p-invalid': error }"
                            />

                            <small class="p-error block mt-1" v-if="error">{{ error }}</small>
                        </div>

                        <Button
                            :label='$t("auth.form.button.sendResetLink")'
                            :loading="loading"
                            type="submit"
                            class="w-full !py-3 !text-base !font-semibold !rounded-lg shadow-md"
                        />
                    </form>
                </div>

                <p class="text-sm text-gray-500 text-center mt-6 dark:text-gray-400">
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
import { authStore } from '@/stores/authStore';
import { forgotPasswordSchema } from '@/validations/authSchema';
import { useShowToast } from '@/utils/useShowToast';
import { useI18n } from 'vue-i18n';
import AsideImage from './components/AsideImage.vue';
import { MailCheck } from 'lucide-vue-next';

const Toast = defineAsyncComponent(() => import('primevue/toast'));

const { t } = useI18n();
const auth = authStore();
const { showError } = useShowToast();

const email   = ref('');
const error   = ref('');
const loading = ref(false);
const sent    = ref(false);

const submit = async () => {
    error.value = '';

    try {
        forgotPasswordSchema.parse({ email: email.value });
    } catch (e) {
        error.value = e.errors[0]?.message ?? t('form.errors.email');
        return;
    }

    loading.value = true;

    try {
        await auth.forgotPassword(email.value);
        sent.value = true;
    } catch {
        showError(
            t('notifications.titles.error'),
            t('notifications.messages.passwordResetError'),
            4000
        );
    } finally {
        loading.value = false;
    }
};

const reset = () => {
    sent.value   = false;
    email.value  = '';
    error.value  = '';
};
</script>

<style scoped>
.p-error {
    color: #dc3545;
    font-size: 0.875rem;
}
</style>
