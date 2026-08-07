<template>
    <div class="flex flex-col gap-5 max-w-2xl">
        <div>
            <label class="mb-1">Nome</label> <span class="text-red-500">*</span>
            <InputText v-model="form.name" class="w-full" :invalid="errors.name.length > 0" />
            <small v-if="errors.name" class="text-red-500">{{ errors.name }}</small>
        </div>

        <div>
            <label class="mb-1">Email</label> <span class="text-red-500">*</span>
            <InputText v-model="form.email" class="w-full" :invalid=" errors.email.length > 0" />
            <small v-if="errors.email" class="text-red-500">{{ errors.email }}</small>
        </div>

        <div>
            <label class="mb-1">Telefone</label>
            <InputMask
                id="phone"
                v-model="form.phone"
                mask="(99) 99999-9999"
                placeholder="(99) 99999-9999"
                class="w-full"
                :invalid="errors.phone.length > 0"
            />
            <small v-if="errors.phone" class="text-red-500">{{ errors.phone }}</small>
        </div>

        <div>
            <label class="mb-1">Especialidade padrão</label>
            <Select v-model="form.specialty" :options="props.specialties" :loading="props.loadingTemplates" filter optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>

        <div class="flex justify-end">
            <Button @click="updateUser" :loading="loadingUpdate" :disabled="loadingUpdate" label="Salvar alterações" />
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useUserStore } from '@/stores/userStore'
import { UserService } from '@/service/UserService'
import Cookies from 'js-cookie'

const userStore = useUserStore();

const emit = defineEmits(['actionResponse']);

const form = reactive({
    name: '',
    email: '',
    phone: '',
    specialty: null
})
const errors = reactive({
    name: '',
    email: '',
    phone: ''
})
const loadingUpdate = ref(false)

const props = defineProps({
    specialties: {
        type: Array,
        required: true
    },
    loadingTemplates: {
        type: Boolean,
        required: false
    }
}); 

const onlyDigits = (value = '') => value.replace(/\D/g, '')

const formatPhone = (value = '') => {
    const digits = onlyDigits(value)

    if (digits.length === 11) {
        return digits.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3')
    }

    if (digits.length === 10) {
        return digits.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3')
    }

    return value || ''
}

const loadUserData = () => {
    form.email = userStore.userEmail
    form.name = userStore.username
    form.phone = formatPhone(userStore.userPhone || '')
    form.specialty = Number(localStorage.getItem("favorite")) || null
}

const updateUser = () => {
    if (!validateForm()) return

    loadingUpdate.value = true

    const payload = {
        ...form,
        phone: onlyDigits(form.phone)
    }

    UserService.update(payload)
        .then((response) => {
            userStore.username = form.name
            userStore.userEmail = form.email
            userStore.userPhone = payload.phone || null
            Cookies.set('username', form.name)
            Cookies.set('user_email', form.email)

            if(form.specialty) {
                localStorage.setItem("favorite", form.specialty)
            }
            
            emit('actionResponse', {'success': true, 'message': 'Usuário atualizado com sucesso!'});
        })
        .catch((error) => {
            emit('actionResponse', {'success': false, 'message': 'Erro ao atualizar usuário. Tente novamente!'});
        })
        .finally(() => {
            loadingUpdate.value = false
        });
}

const validateForm = () => {
    let isValid = true

    Object.keys(errors).forEach(key => errors[key] = '')

    if (!form.name.trim()) {
        errors.name = 'Nome é obrigatório'
        isValid = false
    }

    if (!form.email.trim()) {
        errors.email = 'Email é obrigatório'
        isValid = false
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errors.email = 'Email inválido'
        isValid = false
    }

    const phoneDigits = onlyDigits(form.phone)
    if (phoneDigits && !/^\d{10,11}$/.test(phoneDigits)) {
        errors.phone = 'Telefone inválido'
        isValid = false
    }

    return isValid
}

onMounted(() => {
    loadUserData();
});
</script>

<style scoped>

</style>