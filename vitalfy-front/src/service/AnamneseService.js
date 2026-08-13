import api from '@/services/axios';

// TODO: Mudar para documentService
export const AnamneseService = {
    // FE-R19-01 (ai-vitalfy/action-plans/frontend/R19.md): sem try/catch de
    // propósito — um try/catch aqui só engolia o erro (`console.error` sem
    // relançar), fazendo a promise nunca rejeitar do ponto de vista de quem
    // chama (`finishConversation()` em upload.vue,
    // `generateClinicalDocument()` em GenerateDocument.vue). Uma falha real
    // do Groq terminava em toast de sucesso e redirecionamento sem nenhum
    // documento criado. Devolve a resposta completa do axios (não só
    // `.data`) para permitir distinguir status code no futuro (201 síncrono
    // vs. 202 assíncrono), mesmo padrão de `TranscriptsService.storeAndGenerateDocument()`.
    async generator(payload) {
        return api.post('/documents/generate', payload);
    },
    async refine(payload) {
        try {
            const response = await api.post('/documents/refine', payload);
            return response.data;
        } catch (error) {
            throw error;
        }
    },
    async generatePdf(documentId) {
        try {
            const response = await api.get(`/documents/${documentId}/pdf`, {
                responseType: 'blob'
            });
            return response.data;
        } catch (error) {
            console.error(error);
        }
    },
    async update(documentId, content) {
        try {
            const response = await api.put(`/documents/${documentId}`, content);
            return response.data;
        } catch (error) {
            throw error;
        }
    },
    async getInsights(documentId) {
        return api.get(`/documents/${documentId}/insights`);
    },
}