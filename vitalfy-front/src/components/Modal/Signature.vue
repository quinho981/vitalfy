<template>
    <Dialog
        :visible="isVisible"
        @update:visible="close"
        :dismissable-mask="true"
        :breakpoints="{ '1024px': '75vw', '960px': '80vw', '640px': '92vw' }"
        :style="{ width: '52vw' }"
        modal
        class="!border-none !p-0 !rounded-2xl overflow-hidden"
        :pt="{ mask: { style: 'backdrop-filter: blur(3px)' }, content: { style: 'padding: 0' } }"
    >
        <template #container>
            <div class="flex flex-col md:flex-row overflow-hidden rounded-2xl">
                <div class="md:w-[50%] bg-[#1e3a5f] p-7 flex flex-col">
                    <div class="flex gap-1.5 mb-5 justify-center">
                        <button
                            v-for="opt in periods"
                            :key="opt.key"
                            @click="selected = opt.key"
                            :class="[
                                'px-3 py-1 rounded-lg text-sm font-medium border transition-all duration-150',
                                selected === opt.key 
                                ? 'bg-blue-600 border-blue-600 text-white' 
                                : 'bg-transparent border-blue-300/30 text-blue-300 hover:bg-surface-900/40 hover:text-white'
                            ]"
                        >
                            {{ opt.label }}
                        </button>
                    </div>

                    <div 
                        v-if="currentPeriod.key !== 'pro_monthly'"
                        class="flex items-center gap-1.5 bg-white/[0.15] rounded-lg px-2.5 py-1 w-fit mb-4"
                    >
                        <i class="pi pi-bolt text-blue-200 text-xs"></i>
                        <span class="text-blue-200 text-[12px] font-medium">
                            <span v-if="currentPeriod.key === 'pro_annual'">Mais escolhido ·</span> Economize 
                            <span>{{ currentPeriod.key === 'pro_annual' ? '38%' : '25%' }}</span>
                        </span>
                    </div>

                    <h2 class="text-white text-xl font-semibold mb-2 leading-snug">
                        Pare de perder tempo escrevendo prontuários
                    </h2>

                    <p class="text-slate-300 text-xs leading-relaxed mb-5">
                        A Vitalfy transcreve, organiza e estrutura suas consultas automaticamente — 
                        para você focar no paciente, não na digitação.
                    </p>

                    <div class="mb-2">
                        <div class="flex items-baseline gap-2">
                            <span class="text-white text-3xl font-semibold">
                                {{ currentPeriod.price }}
                            </span>
                            <span class="text-slate-300 text-xs">/mês</span>
                        </div>

                        <p class="text-emerald-400 text-[11px] mt-1">
                            Menos de R$ 10 por dia
                        </p>

                        <p 
                            v-if="currentPeriod.original" 
                            class="text-slate-400 text-[11px] line-through mt-1"
                        >
                            {{ currentPeriod.original }}
                        </p>
                    </div>

                    <div class="flex-1"></div>

                    <button
                        :disabled="loading"
                        @click="subscribe"
                        class="w-full mt-5 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] 
                               text-white text-sm font-semibold py-3 rounded-xl 
                               cursor-pointer transition-all duration-150 shadow-md hover:shadow-lg"
                               :class=" loading ? 'flex items-center justify-center gap-x-2' : ''"
                    >
                        <Loader2 v-if="loading" :size="17" class="animate-spin" />
                        {{ loading ? 'Carregando...' : 'Quero economizar tempo nas consultas' }} 
                    </button>

                    <p class="text-slate-300 text-[11px] text-center mt-3">
                        Mais de 500 médicos já usam a Vitalfy diariamente
                    </p>
                </div>

                <div class="flex-1 p-7 flex flex-col dark:bg-surface-800">
                    <div class="flex justify-between items-center mb-5">
                        <p class="text-sm font-medium text-gray-800 dark:text-slate-200 m-0">
                            O que você ganha com o plano Profissional
                        </p>

                        <button 
                            @click="close" 
                            class="w-7 h-7 flex items-center justify-center rounded-md 
                                   hover:bg-gray-100 dark:hover:bg-slate-800 
                                   transition-colors duration-150"
                        >
                            <i class="pi pi-times text-gray-400 text-xs"></i>
                        </button>
                    </div>

                    <ul class="flex flex-col m-0 p-0 list-none divide-y divide-gray-100 dark:divide-slate-700">
                        <li 
                            v-for="benefit in benefits" 
                            :key="benefit" 
                            class="flex items-start gap-2.5 py-2.5"
                        >
                            <div class="w-[18px] h-[18px] rounded-full bg-green-100 flex items-center justify-center mt-0.5">
                                <i class="pi pi-check text-green-600 text-[9px]"></i>
                            </div>

                            <span class="text-sm text-gray-700 dark:text-slate-300 leading-snug">
                                {{ $t(benefit) }}
                            </span>
                        </li>
                    </ul>

                    <div class="mt-auto pt-4 flex items-center gap-1.5">
                        <i class="pi pi-lock text-slate-400 text-xs"></i>
                        <p class="text-[11px] text-slate-400 m-0">
                            Pagamento seguro · Cancele quando quiser
                        </p>
                    </div>
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Loader2 } from 'lucide-vue-next';

const props = defineProps({ 
    active: { 
        type: Boolean, 
        default: false 
    },
    loading: {
        type: Boolean,
        default: false
    }
});
const emit = defineEmits(['close', 'subscribe']);

const close = () => emit('close', false);

const selected = ref('pro_annual');
const periods = [
    { key: 'pro_annual', label: 'Anual', price: 'R$ 124', original: 'R$ 1.497,00/ano' },
    { key: 'pro_semester', label: 'Semestral', price: 'R$ 149', original: 'R$ 897,00/semestre' },
    { key: 'pro_monthly', label: 'Mensal', price: 'R$ 199', original: '' }
];

const isVisible = computed(() => props.active);
const currentPeriod = computed(() => 
    periods.find((p) => p.key === selected.value)
);

const benefits = [
    'signature.modal.benefits.benefit1',
    "Gere documentos clínicos organizados em segundos",
    'signature.modal.benefits.benefit3',
    'signature.modal.benefits.benefit4',
    'signature.modal.benefits.benefit5',
    'signature.modal.benefits.benefit6',
    'signature.modal.benefits.benefit7'
];

const subscribe = () => {
    emit('subscribe', selected.value);
};
</script>