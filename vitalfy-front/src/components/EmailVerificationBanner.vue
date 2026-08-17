<template>
    <div
        v-if="visible"
        class="flex items-center gap-x-3 px-4 py-2 text-sm bg-amber-50 text-amber-800 border-b border-amber-200
               dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20"
    >
        <MailWarning :size="16" class="flex-shrink-0" />

        <span class="flex-1">{{ $t('auth.emailVerification.bannerMessage') }}</span>

        <button
            @click="emailVerificationStore.resend()"
            :disabled="emailVerificationStore.sending"
            class="font-semibold underline underline-offset-2 hover:no-underline disabled:opacity-60 disabled:no-underline flex-shrink-0"
        >
            <span v-if="emailVerificationStore.sent">{{ $t('auth.emailVerification.resendSuccess') }}</span>
            <span v-else>{{ $t('auth.emailVerification.resendButton') }}</span>
        </button>

        <button
            @click="dismiss"
            class="flex-shrink-0 text-amber-500 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-200"
            :aria-label="$t('button.close')"
        >
            <X :size="16" />
        </button>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { MailWarning, X } from 'lucide-vue-next';
import { useUserStore } from '@/stores/userStore';
import { useEmailVerificationStore } from '@/stores/emailVerificationStore';

// R5 (ai-vitalfy/action-plans/frontend/R5.md, FE-R5-01): aviso ambiente, sem
// bloquear nada — quem interrompe a ação é o modal (FE-R5-02). Dispensável
// na sessão atual, mas volta a aparecer no próximo login (não é possível
// desligar de vez, senão o aviso perde a função).
const DISMISS_KEY = 'email_verification_banner_dismissed';

const userStore = useUserStore();
const emailVerificationStore = useEmailVerificationStore();

const dismissed = ref(sessionStorage.getItem(DISMISS_KEY) === 'true');

const visible = computed(() => userStore.emailVerified === false && !dismissed.value);

const dismiss = () => {
    dismissed.value = true;
    sessionStorage.setItem(DISMISS_KEY, 'true');
};
</script>
