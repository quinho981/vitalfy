<template>
    <div
        v-if="visible"
        class="flex items-center gap-3 rounded-xl px-4 py-3 mb-4 border-l-4 bg-amber-50 border-amber-500
               dark:bg-amber-950/40"
    >
        <MailWarning :size="18" class="flex-shrink-0 text-amber-500" />

        <p class="flex-1 text-sm font-medium text-amber-800 dark:text-amber-200">
            {{ $t('auth.emailVerification.bannerMessage') }}
        </p>

        <button
            @click="emailVerificationStore.resend()"
            :disabled="emailVerificationStore.sending || emailVerificationStore.cooldownRemaining > 0"
            class="text-sm font-semibold underline underline-offset-2 hover:no-underline disabled:opacity-60 disabled:no-underline flex-shrink-0
                   text-amber-800 dark:text-amber-200"
        >
            {{ resendLabel }}
        </button>

        <button
            @click="dismiss"
            class="flex-shrink-0 text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-200 transition-colors"
            :aria-label="$t('button.close')"
        >
            <i class="pi pi-times text-xs" />
        </button>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { MailWarning } from 'lucide-vue-next';
import { useUserStore } from '@/stores/userStore';
import { useEmailVerificationStore } from '@/stores/emailVerificationStore';

const DISMISS_KEY = 'email_verification_banner_dismissed';

const { t } = useI18n();
const userStore = useUserStore();
const emailVerificationStore = useEmailVerificationStore();

const dismissed = ref(sessionStorage.getItem(DISMISS_KEY) === 'true');

const visible = computed(() => userStore.emailVerified === false && !dismissed.value);

const resendLabel = computed(() => {
    if (emailVerificationStore.cooldownRemaining > 0) {
        const key = emailVerificationStore.sent ? 'resendSuccessCooldown' : 'resendCooldown';
        return t(`auth.emailVerification.${key}`, { seconds: emailVerificationStore.cooldownRemaining });
    }

    return t('auth.emailVerification.resendButton');
});

const dismiss = () => {
    dismissed.value = true;
    sessionStorage.setItem(DISMISS_KEY, 'true');
};
</script>
