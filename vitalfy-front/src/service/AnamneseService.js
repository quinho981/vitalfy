import api from '@/services/axios';

// TODO: Mudar para documentService
export const AnamneseService = {
    async generator(payload) {
        try {
            const response = await api.post('/documents/generate', payload);
            return response.data;
        } catch (error) {
            console.error(error);
        }
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
}