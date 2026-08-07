import api from '@/services/axios';

export const SubscriptionService = {
    async getSubscription() {
        try {
            const response = await api.get('/subscription');
            return response.data;
        } catch (error) {
            console.error('Error fetching subscription:', error);
            throw error;
        }
    },

    async verifyCheckout(sessionId) {
        try {
            const response = await api.get('/subscription/verify-checkout', {
                params: { session_id: sessionId },
            });
            return response.data;
        } catch (error) {
            console.error('Error verifying checkout:', error);
            throw error;
        }
    },

    async createCheckout(plan) {
        try {
            const response = await api.post('/subscription/checkout', { plan });
            return response.data;
        } catch (error) {
            console.error('Error creating checkout:', error);
            throw error;
        }
    },

    async cancelSubscription() {
        try {
            const response = await api.post('/subscription/cancel', {});
            return response.data;
        } catch (error) {
            console.error('Error cancelling subscription:', error);
            throw error;
        }
    },
};
