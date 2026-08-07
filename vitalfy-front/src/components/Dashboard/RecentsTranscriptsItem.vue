<template>
    <div>
        <div>
            <div v-if="loading">
                <div
                    v-for="n in 4"
                    :key="n"
                    class="border-[1px] border-surface p-3 rounded-lg mt-4"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-x-3">
                            <Skeleton shape="circle" size="3rem" />

                            <div class="flex flex-col gap-2">
                                <Skeleton width="150px" height="23px" />
                                <Skeleton width="200px" height="19px" />
                            </div>
                        </div>

                        <div class="flex items-center gap-x-2">
                            <Skeleton width="80px" height="24px" />
                            <Skeleton shape="circle" size="2rem" />
                        </div>
                    </div>
                </div>
            </div>
            <div
                v-if="!transcripts.length > 0 && !loading"
                class="flex flex-col text-center p-14"
            >
                <p class="text-[14px]">Ainda não há atendimentos finalizados.</p>
                <p class="text-[14px]">Ao concluir um atendimento, o documento clínico e os insights serão gerados automaticamente e exibidos aqui.</p>
                <div>
                    <router-link
                        :to="{ name: 'upload' }"
                        class="p-button p-component !bg-gradient-to-br !from-blue-500 !to-blue-700 !border-none !text-white !text-[13px] !p-2 
                            inline-flex hover:!from-blue-600 hover:!to-blue-800 duration-300 mt-4 rounded-md"
                    >
                        <Mic :size="16" class="mr-1" />
                        Novo Atendimento
                    </router-link>
                </div>
            </div>
            <div v-else>
                <div
                    v-for="transcript in transcripts"
                    :key="transcript.id"
                    class="border-[1px] border-surface p-3 rounded-lg mt-4 hover:border-blue-400 duration-200"
                    :class="[
                        transcript.document ? '' : 'hover:border-yellow-400 bg-yellow-200/10 dark:bg-yellow-900/30 hover:bg-yellow-300/10 dark:hover:bg-yellow-800/30 dark:hover:border-yellow-600',
                    ]"
                >
                    <div class="flex items-center justify-between max-[650px]:items-start">
                        <div class="flex items-center gap-x-3 min-w-0 max-[650px]:items-start">
                            <div :class="`w-10 h-10 rounded-full flex items-center justify-center text-[11.5px] font-semibold flex-shrink-0 mt-0.5 ${getPatientAvatar(transcript.patient)}`">
                                {{ getInitials(transcript.patient) }}
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex">
                                    <h4 class="text-md font-semibold truncate w-[95%]">{{ transcript.patient }}</h4>
                                    <Tag
                                        v-if="!transcript.document"
                                        severity="warn"
                                        value="transcrição"
                                        rounded
                                        class="!text-xs !py-0 ml-2"
                                    />
                                </div>

                                <p class="text-sm flex items-center gap-x-2 text-surface-400 max-[650px]:flex-wrap max-[650px]:gap-y-1 max-[650px]:mt-1.5">
                                    <span class="flex items-center gap-x-1">
                                        <Calendar :size="12" class="text-surface-500" />
                                        {{ formatDate(transcript.created_at) }}
                                    </span>
                                    <span class="flex items-center gap-x-1">
                                        <Timer :size="12" class="text-orange-400" />
                                        {{ convertSecondsToMinutes(transcript.end_conversation_time) }}
                                    </span>
                                    <span class="max-[650px]:hidden" v-if="transcript.document">
                                        |
                                    </span>
                                    <span v-if="transcript.document" class="text-surface-500 dark:text-surface-300">
                                        {{ transcript.document?.document_template?.name || '-' }}
                                    </span>

                                    <div class="hidden max-[650px]:flex">
                                        <Tag :severity="transcript.transcript_type.type == 'Urgente' ? 'danger' : 'secondary'" :value="transcript.transcript_type.type" rounded class="!text-xs" />
                                    </div>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-x-2 max-[650px]:self-center">
                            <Tag :severity="transcript.transcript_type.type == 'Urgente' ? 'danger' : 'secondary'" :value="transcript.transcript_type.type" rounded class="!hidden !text-xs min-[651px]:!flex" />

                            <Button
                                v-if="transcript.document"
                                as="router-link"
                                :to="'/transcripts/' + transcript.id"
                                severity="secondary"
                                text
                                v-tooltip.top="'Visualizar'"
                            >
                                <Eye :size="20" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import { ref, watch, computed } from 'vue';
import { FileText, Eye, Download, Mic, Calendar, Timer } from 'lucide-vue-next';
import { useHelpers } from '@/utils/helper';

const { convertSecondsToMinutes, formatDate, getInitials, getPatientAvatar } = useHelpers();

const props = defineProps({
    transcripts: {
        type: Array,
        required: true
    },
    loading: {
        type: Boolean,
        required: true
    }
}); 
</script>

<style scoped>
</style>