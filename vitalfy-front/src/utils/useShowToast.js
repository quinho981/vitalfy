import { useToast } from 'primevue/usetoast';

export const useShowToast = () => {
    const toast = useToast();

    const showSuccess = (summary, detail, life) => {
        toast.add({ severity: 'success', summary, detail, life });
    };

    const showError = (summary, detail, life) => {
        toast.add({ severity: 'error', summary, detail, life });
    };

    const showAttention = (summary, detail, life) => {
        toast.add({ severity: 'warn', summary, detail, life });
    };

    return {
        showSuccess,
        showError,
        showAttention
    };
};
