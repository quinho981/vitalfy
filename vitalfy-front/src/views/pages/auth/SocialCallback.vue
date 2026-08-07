<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-zinc-900">
        <div class="text-center">
            <div v-if="!failed" class="flex flex-col items-center gap-3 text-surface-500 dark:text-surface-400">
                <Loader2 :size="32" class="animate-spin text-blue-500" />
                <p class="text-sm">{{ $t("auth.socialCallback.loading") }}</p>
            </div>

            <div v-else class="flex flex-col items-center gap-4 max-w-sm px-6">
                <div class="w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <ShieldAlert :size="28" class="text-red-500 dark:text-red-400" />
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $t("auth.socialCallback.error") }}
                </p>
                <router-link
                    :to="{ name: 'login' }"
                    class="px-5 py-2 text-sm font-semibold text-white bg-blue-500 rounded-lg hover:bg-blue-600 transition-colors"
                >
                    {{ $t("auth.forgotPassword.backToLogin") }}
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useUserStore } from '@/stores/userStore';
import { Loader2, ShieldAlert } from 'lucide-vue-next';

const route     = useRoute();
const router    = useRouter();
const userStore = useUserStore();

const failed = ref(false);

onMounted(async () => {
    const error = route.query.error;

    if (error) {
        failed.value = true;
        return;
    }

    try {
        await userStore.getUserInfo();
        router.replace({ name: 'dashboard' });
    } catch {
        failed.value = true;
    }
});
</script>
