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
                </div>

                <!-- Card -->
                <div class="bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-xl text-center">

                    <!-- Estado: verificado com sucesso -->
                    <template v-if="status === 'success'">
                        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 mx-auto mb-4">
                            <ShieldCheck :size="30" class="text-green-600 dark:text-green-400" />
                        </div>

                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            {{ $t('auth.emailVerification.verifiedTitle') }}
                        </h2>

                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-6">
                            {{ $t('auth.emailVerification.verifiedDescription') }}
                        </p>

                        <router-link
                            :to="{ name: 'login' }"
                            class="block w-full py-3 text-center text-sm font-semibold text-white bg-blue-500 rounded-lg hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition-colors"
                        >
                            {{ $t('auth.emailVerification.goToLogin') }}
                        </router-link>
                    </template>

                    <!-- Estado: já verificado -->
                    <template v-else-if="status === 'already_verified'">
                        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900/30 mx-auto mb-4">
                            <MailCheck :size="30" class="text-blue-600 dark:text-blue-400" />
                        </div>

                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            {{ $t('auth.emailVerification.alreadyVerifiedTitle') }}
                        </h2>

                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-6">
                            {{ $t('auth.emailVerification.alreadyVerifiedDescription') }}
                        </p>

                        <router-link
                            :to="{ name: 'login' }"
                            class="block w-full py-3 text-center text-sm font-semibold text-white bg-blue-500 rounded-lg hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition-colors"
                        >
                            {{ $t('auth.emailVerification.goToLogin') }}
                        </router-link>
                    </template>

                    <!-- Estado: link inválido ou expirado -->
                    <template v-else>
                        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 mx-auto mb-4">
                            <ShieldAlert :size="30" class="text-red-600 dark:text-red-400" />
                        </div>

                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            {{ $t('auth.emailVerification.invalidTitle') }}
                        </h2>

                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-6">
                            {{ $t('auth.emailVerification.invalidDescription') }}
                        </p>

                        <router-link
                            :to="{ name: 'login' }"
                            class="block w-full py-3 text-center text-sm font-semibold text-white bg-blue-500 rounded-lg hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition-colors"
                        >
                            {{ $t('auth.emailVerification.backToLogin') }}
                        </router-link>
                    </template>
                </div>

                <p class="text-sm text-gray-500 text-center mt-6 dark:text-gray-400">
                    <router-link
                        :to="{ name: 'login' }"
                        class="text-primary font-medium hover:underline cursor-pointer"
                    >
                        ← {{ $t('auth.emailVerification.backToLogin') }}
                    </router-link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { MailCheck, ShieldCheck, ShieldAlert } from 'lucide-vue-next';
import AsideImage from './components/AsideImage.vue';

const route = useRoute();

const status = computed(() => route.query.status ?? 'invalid');
</script>
