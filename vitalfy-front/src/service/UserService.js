import api from '@/services/axios';

export const UserService = {
    async update(data) {
        try {
            const response = await api.put('/user', data);
            return response.data;
        } catch (error) {
            throw error;
        }
    },
    async changePassword(data) {
        try {
            const response = await api.post('/change-password', data);
            return response.data;
        } catch (error) {
            throw error;
        }
    }
}