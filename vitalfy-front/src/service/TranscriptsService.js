import api from '@/services/axios';

export const TranscriptsService = {
    async index(page = 1, perPage = 10) {
        try {
            const response = await api.get(`/user/transcripts?page=${page}&perPage=${perPage}`);
            return {
                transcripts: response.data.data,
                total: response.data.total,
                perPage: response.data.per_page
            };
        } catch (error) {
            console.error(error);
        }
    },
    store(formData) {
        try {
            return api.post(`/transcripts`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
        } catch (error) {
            console.error(error);
        }
    },
    // FE-R1-01 (ai-vitalfy/action-plans/frontend/R1.md): sem timeout
    // explícito o axios espera indefinidamente (default 0) — quem corta é a
    // cadeia de proxy, não o cliente, e o erro chega como falha de rede sem
    // `error.response`. 190s: ~10s acima do teto de 180s alinhado nos três
    // nginx por BE-R1-01, folga só para a própria requisição HTTP.
    //
    // Sem try/catch aqui de propósito: a promise rejeitada precisa chegar ao
    // .catch()/try-catch de quem chama (upload.vue). Um try/catch em volta de
    // um `return` sem `await` nunca captura nada — só dava a impressão de
    // tratamento (ver nota histórica em ai-vitalfy/action-plans/frontend/R1.md#fe-r1-01).
    storeAndGenerateDocument(formData) {
        return api.post(`/transcripts/generate-document`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            timeout: 190000,
        });
    },
    // BE-R1-07: status do processamento assíncrono, para polling.
    async getTranscriptStatus(id) {
        const response = await api.get(`/transcripts/${id}/status`);
        return response.data;
    },
    async show(id) {
        try {
            const response = await api.get(`/transcripts/${id}`);
            return response.data;
        } catch (error) {
            console.error(error);
        }
    },
    delete(id) {
        try {
            return api.delete(`/transcripts/${id}`);
        } catch (error) {
            console.error(error);
        }
    },
    update(id, data) {
        try {
            return api.put(`/transcripts/${id}`, data);
        } catch (error) {
            console.error(error);
        }
    },
    async getConversations(id) {
        try {
            const response = await api.get(`/transcripts/${id}/conversations`);
            return response.data;
        } catch (error) {
            console.error(error);
        }
    },
    async filterTranscripts(user = null, date = null, type = null) {
        try {
            const response = await api.get(`/transcripts/user/filter`, {
                params: { user, date, type }
            });
            return response.data;
        } catch (error) {
            console.error(error);
        }
    },
    async regenerateInsights(documentId) {
        try {
            const response = await api.post(`/documents/${documentId}/regenerate-insights`, {});
            return response.data;
        } catch (error) {
            console.error(error);
            throw error;
        }
    },
}
