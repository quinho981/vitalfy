import api from '@/services/axios';

export const TemplatesService = {
    async getTemplatesWithDocumentsCount(page = 1, search = null, category = null, perPage = 10) {
        const response = await api.get(`/templates/with-documents-count`, {
            params: { page, search, category, per_page: perPage }
        });
        return response.data;
    },
    async getCountCategories() {
        const response = await api.get(`/templates/count-categories`);
        return response.data;
    },
}
