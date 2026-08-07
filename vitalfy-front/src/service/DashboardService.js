import api from '@/services/axios';

export const DashboardService = {
    async summary(period = 'today') {
        try {
            const response = await api.get('/dashboard/summary', { params: { period } });
            return response.data;
        } catch (error) {
            console.error(error);
        }
    },
    async charts() {
        try {
            const response = await api.get('/dashboard/charts');
            return response.data;
        } catch (error) {
            console.error(error);
        }
    },
    async latestRecentTranscripts() {
        try {
            const response = await api.get('/dashboard/last-transcripts');
            return response.data;
        } catch (error) {
            console.error(error);
        }
    }
}