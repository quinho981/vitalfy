<template>
    <Dialog
        :visible="emailVerificationStore.isModalOpen"
        @update:visible="(value) => !value && emailVerificationStore.closeModal()"
        modal
        :draggable="false"
        :closable="false"
        :style="{ width: '29rem' }"
    >
        <div class="p-3">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-amber-50 dark:bg-amber-900">
                        <i class="pi pi-envelope text-amber-600 text-lg dark:text-amber-400"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                        {{ $t('auth.emailVerification.modalTitle') }}
                    </h2>
                </div>

                <button
                    @click="emailVerificationStore.closeModal()"
                    class="text-gray-400 hover:text-gray-600 transition dark:hover:text-white dark:text-gray-200"
                >
                    ✕
                </button>
            </div>

            <p class="text-[13px] text-gray-600 leading-relaxed dark:text-gray-300">
                {{ $t('auth.emailVerification.modalDescription') }}
            </p>

            <div class="flex justify-end gap-3 mt-6">
                <button
                    @click="emailVerificationStore.closeModal()"
                    class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition dark:hover:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                >
                    {{ $t('button.close') }}
                </button>

                <button
                    @click="emailVerificationStore.resend()"
                    :disabled="emailVerificationStore.sending || emailVerificationStore.cooldownRemaining > 0"
                    class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 flex items-center gap-2"
                >
                    <i v-if="emailVerificationStore.sending" class="pi pi-spin pi-spinner text-sm"></i>
                    {{ resendLabel }}
                </button>
            </div>
        </div>
    </Dialog>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useEmailVerificationStore } from '@/stores/emailVerificationStore';

// R5 (ai-vitalfy/action-plans/frontend/R5.md, FE-R5-02): aberto pelo
// interceptor do axios (services/axios.js) quando uma das cinco rotas de
// custo responde 409 com email_verification_required. Fechar sem verificar
// não bloqueia nada — o usuário volta a navegar normalmente.
const { t } = useI18n();
const emailVerificationStore = useEmailVerificationStore();

const resendLabel = computed(() => {
    if (emailVerificationStore.cooldownRemaining > 0) {
        const key = emailVerificationStore.sent ? 'resendSuccessCooldown' : 'resendCooldown';
        return t(`auth.emailVerification.${key}`, { seconds: emailVerificationStore.cooldownRemaining });
    }

    return t('auth.emailVerification.resendButton');
});
</script>
