let appInstance = null;
let servicesLoadPromise = null;

export const setPrimeVueApp = (app) => {
    appInstance = app;
};

export const ensurePrimeVueFeedbackServices = async () => {
    if (!appInstance) {
        throw new Error('PrimeVue app instance is not available.');
    }

    if (!servicesLoadPromise) {
        servicesLoadPromise = Promise.all([
            import('primevue/toastservice'),
            import('primevue/confirmationservice')
        ]).then(([toastServiceModule, confirmationServiceModule]) => {
            appInstance.use(toastServiceModule.default);
            appInstance.use(confirmationServiceModule.default);
        });
    }

    return servicesLoadPromise;
};
