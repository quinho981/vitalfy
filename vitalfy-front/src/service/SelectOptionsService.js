import api from '@/services/axios';

export const SelectOptionsService = {
    async getTemplates() {
        const response = await api.get(`/templates`);
        return response.data;
    },
    async getTemplatesMinimal() {
        const response = await api.get(`/templates/minimal`);
        return response.data;
    },
    async getTypes() {
        const response = await api.get(`/transcript-types`);
        return response.data;
    },
    async getTypesMinimal() {
        const response = await api.get(`/transcript-types/minimal`);
        return response.data;
    }
};