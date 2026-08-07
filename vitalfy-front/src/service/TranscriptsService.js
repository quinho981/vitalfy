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
    storeAndGenerateDocument(formData) {
        try {
            return api.post(`/transcripts/generate-document`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
        } catch (error) {
            console.error(error);
        }
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
