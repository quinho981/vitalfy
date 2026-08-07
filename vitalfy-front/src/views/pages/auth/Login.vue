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
                        {{ $t("welcome") }}
                    </h2>

                    <p class="text-sm text-gray-500 mt-2 dark:text-gray-400">
                        {{ $t("auth.login.description") }}
                    </p>
                </div>

                <div class="bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-xl">
                    <Message
                        v-if="errorMessage"
                        class="mb-4"
                        severity="error"
                    >
                        {{ $t("notifications.messages.wrongUserOrPassword") }}
                    </Message>

                    <Message
                        v-if="socialError"
                        class="mb-4"
                        severity="error"
                    >
                        {{ $t("auth.socialCallback.error") }}
                    </Message>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                                {{ $t("form.label.email") }}
                            </label>

                            <InputText 
                                id="email" 
                                type="email" 
                                :placeholder='$t("auth.form.label.enterEmail")' 
                                class="w-full !px-4 !py-3 !rounded-lg !border-gray-200 dark:!border-zinc-600"
                                v-model="form.email" 
                            />
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                                {{ $t("auth.form.label.password") }}
                            </label>

                            <Password 
                                id="password" 
                                v-model="form.password" 
                                :placeholder='$t("auth.form.label.enterPassword")' 
                                :toggleMask="true" 
                                class="w-full !rounded-lg"
                                inputClass="!px-4 !py-3 !rounded-lg !border-gray-200 dark:!border-zinc-600"
                                fluid 
                                :feedback="false"
                            />
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <label class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                <Checkbox
                                    v-model="form.remember"
                                    id="rememberme"
                                    binary
                                />
                                {{ $t("auth.form.label.rememberMe") }}
                            </label>

                            <router-link
                                :to="{ name: 'forgotPassword' }"
                                class="text-primary hover:underline font-medium"
                            >
                                {{ $t("auth.form.label.forgotPassword") }}?
                            </router-link>
                        </div>

                        <Button
                            :label='$t("auth.form.button.signIn")'
                            class="w-full !py-3 !text-base !font-semibold !rounded-lg shadow-md"
                            :loading="loading"
                            type="submit"
                        />
                    </form>

                    <div class="flex items-center gap-3 my-5">
                        <hr class="flex-1 border-gray-200 dark:border-zinc-600" />
                        <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                            {{ $t("auth.form.label.orContinueWith") }}
                        </span>
                        <hr class="flex-1 border-gray-200 dark:border-zinc-600" />
                    </div>

                    <a
                        :href="googleAuthUrl"
                        class="flex items-center justify-center gap-3 w-full py-3 px-4 rounded-lg border border-gray-200 dark:border-zinc-600 bg-white dark:bg-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-600 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="w-5 h-5 flex-shrink-0">
                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                            <path fill="none" d="M0 0h48v48H0z"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ $t("auth.form.label.continueWithGoogle") }}
                        </span>
                    </a>
                </div>

                <p class="text-sm text-gray-500 text-center mt-6 dark:text-gray-400">
                    {{ $t("auth.form.label.haveAnAccount") }} 
                    <router-link 
                        to="register" 
                        class="text-primary font-medium hover:underline cursor-pointer"
                    >
                        {{ $t("auth.form.button.signUp") }}
                    </router-link>
                </p>
            </div>
        </div>
    </div>
</template>
<script setup>
import { ref } from "vue";
import AsideImage from './components/AsideImage.vue'
import { useRoute, useRouter } from 'vue-router';
import { authStore } from '@/stores/authStore'
import { useUserStore } from '@/stores/userStore'

const auth = authStore();
const userStore = useUserStore();

const route  = useRoute();
const router = useRouter();
const loading     = ref(false);
const errorMessage  = ref(false);
const socialError   = ref(route.query.error === 'social_auth_failed');
const googleAuthUrl = `${import.meta.env.VITE_BASE_URL.replace(/\/api\/?$/, '')}/api/auth/google`;

const form = ref({
    email: '',
    password: '',
    remember: false
})

const submit = async () => {
    if (!validateForm()) {
        loading.value = false;
        return;
    }

    errorMessage.value = false;
    loading.value = true;

    try {
        await auth.login(form.value);
        await userStore.getUserInfo();
        router.push({ name: 'dashboard' });
    } catch (error) {
        errorMessage.value = true;
        loading.value = false;
    }
};


const validateForm = () => {
    if (!form.value.email && !form.value.password) {
        errorMessage.value = true;
        return false;
    }
    return true;
};
</script>

<style scoped>
.p-invalid {
    border-color: #dc3545 !important;
}

.p-error {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
</style>
