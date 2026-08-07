<template>
    <div class="flex flex-col gap-5 max-w-lg">
        <div>
            <label class="mb-1">Senha atual</label>
            <Password v-model="form.current_password" toggleMask fluid :invalid="errorCurrentPassword" :feedback="false" />
        </div>

        <div>
            <label class="mb-1">Nova senha</label>
            <Password v-model="form.new_password" toggleMask fluid :invalid="errorNewPassword" promptLabel="Escolha a senha" weakLabel="Muito fraca" mediumLabel="Média" strongLabel="Forte" />
        </div>

        <div>
            <label class="mb-1">Confirmar nova senha</label>
            <Password v-model="form.new_password_confirmation" toggleMask fluid :invalid="errorNewPassword" :feedback="false"/>
        </div>

        <div class="flex justify-end">
            <Button label="Atualizar senha" @click="updatePassword" :loading="loadingPassword" :disabled="loadingPassword" />
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { UserService } from '@/service/UserService'

const emit = defineEmits(['actionResponse']);

const form = reactive({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
});
const loadingPassword = ref(false)
const errorCurrentPassword = ref(false)
const errorNewPassword = ref(false)

const updatePassword = async () => {
    loadingPassword.value = true
    errorCurrentPassword.value = false
    errorNewPassword.value = false

    if (form.new_password !== form.new_password_confirmation) {
        emit('actionResponse', {'success': false, 'message': 'Erro! As senhas não coincidem.'});
        loadingPassword.value = false
        errorCurrentPassword.value = true
        return;
    }

    try {
        const response = await UserService.changePassword(form);
        
        emit('actionResponse', {'success': true, 'message': 'Senha alterada com sucesso!'});
        clearInputs()
        return response
    } catch (error) {
        errorCurrentPassword.value = true
        errorNewPassword.value = true
        emit('actionResponse', {'success': false, 'message': 'Erro ao trocar senhar. Tente novamente!'});
    } finally {
        loadingPassword.value = false
    }
}

const clearInputs = () => {
    form.current_password = ''
    form.new_password = ''
    form.new_password_confirmation = ''
    errorNewPassword.value = false
}
</script>

<style scoped>
</style>