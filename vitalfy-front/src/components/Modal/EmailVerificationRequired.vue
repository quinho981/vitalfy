<template>
    <Dialog
        :visible="emailVerificationStore.isModalOpen"
        @update:visible="(value) => !value && emailVerificationStore.closeModal()"
        modal
        :draggable="false"
        :style="{ width: '420px', maxWidth: '95vw' }"
        :pt="{
            header: { class: 'pb-3 border-b border-surface-100 dark:border-surface-700' },
            footer: { class: '!pt-3 border-t border-surface-100 dark:border-surface-700' },
        }"
    >
        <template #header>
            <div class="flex items-center gap-x-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 bg-amber-50 dark:bg-amber-500/10">
                    <MailWarning :size="18" class="text-amber-600 dark:text-amber-400" />
                </div>
                <p class="text-sm font-semibold text-surface-800 dark:text-surface-200 leading-tight">
                    {{ $t('auth.emailVerification.modalTitle') }}
                </p>
            </div>
        </template>

        <p class="text-sm text-surface-600 dark:text-surface-300 leading-relaxed py-2">
            {{ $t('auth.emailVerification.modalDescription') }}
        </p>

        <template #footer>
            <div class="flex justify-end gap-x-2">
                <button
                    @click="emailVerificationStore.closeModal()"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                           text-surface-600 hover:bg-surface-100
                           dark:text-surface-300 dark:hover:bg-surface-700"
                >
                    {{ $t('button.close') }}
                </button>
                <button
                    @click="emailVerificationStore.resend()"
                    :disabled="emailVerificationStore.sending"
                    class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors disabled:opacity-60
                           bg-blue-500 text-white hover:bg-blue-600
                           dark:bg-blue-600 dark:hover:bg-blue-700"
                >
                    <span v-if="emailVerificationStore.sent">{{ $t('auth.emailVerification.resendSuccess') }}</span>
                    <span v-else>{{ $t('auth.emailVerification.resendButton') }}</span>
                </button>
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { MailWarning } from 'lucide-vue-next';
import { useEmailVerificationStore } from '@/stores/emailVerificationStore';

// R5 (ai-vitalfy/action-plans/frontend/R5.md, FE-R5-02): aberto pelo
// interceptor do axios (services/axios.js) quando uma das cinco rotas de
// custo responde 409 com email_verification_required. Fechar sem verificar
// não bloqueia nada — o usuário volta a navegar normalmente.
const emailVerificationStore = useEmailVerificationStore();
</script>
