<template>
    <Dialog 
        :visible="visible" 
        :modal="true" 
        :closable="false" 
        :dismissableMask="false"
        :style="{ width: '450px' }"
        :pt="{
            mask: {
                style: 'backdrop-filter: blur(4px)'
            }
        }"
    >
        <template #header>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900">
                    <i class="pi pi-check text-blue-600 dark:text-blue-400"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white m-0">
                        Bem-vindo ao Plano Pro!
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 m-0">
                        Sua assinatura foi ativada com sucesso
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div class="bg-gradient-to-r from-blue-50 to-blue-50 dark:from-blue-900/20 dark:to-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-3">
                    🎉 Parabéns! Você agora tem acesso a:
                </h4>
                <ul class="space-y-2 text-sm text-blue-700 dark:text-blue-300">
                    <li class="flex items-start gap-2">
                        <i class="pi pi-check-circle mt-0.5 text-blue-600 dark:text-blue-400"></i>
                        <span>Geração de documentos ilimitada</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="pi pi-check-circle mt-0.5 text-blue-600 dark:text-blue-400"></i>
                        <span>Acesso a todos os templates premium</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="pi pi-check-circle mt-0.5 text-blue-600 dark:text-blue-400"></i>
                        <span>Análises avançadas de IA</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="pi pi-check-circle mt-0.5 text-blue-600 dark:text-blue-400"></i>
                        <span>Suporte prioritário 24/7</span>
                    </li>
                </ul>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Seus dados foram atualizados e você já pode começar a usar todos os recursos!
                </p>
            </div>
        </div>

        <template #footer>
            <div class="flex gap-3 justify-end">
                <Button 
                    label="Ver Configurações" 
                    severity="secondary" 
                    @click="goToSettings"
                    :loading="loading"
                />
                <Button 
                    label="Começar a Usar" 
                    @click="closeModal"
                    :loading="loading"
                />
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '@/stores/userStore';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:visible']);
const router = useRouter();
const userStore = useUserStore();
const loading = ref(false);

const closeModal = () => {
    router.replace({ query: {} })
    emit('update:visible', false);
};

const goToSettings = () => {
    loading.value = true;
    router.push('/settings/user');
    closeModal();
};
</script>
