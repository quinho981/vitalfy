import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import i18n from './i18n';

import { createPinia } from 'pinia'

import Aura from '@primevue/themes/aura';
import { definePreset } from '@primevue/themes';
import PrimeVue from 'primevue/config';
import { ensurePrimeVueFeedbackServices, setPrimeVueApp } from '@/plugins/primevueServices';

import '@/assets/styles.scss';
import '@/assets/tailwind.css';

const pinia = createPinia()
const app = createApp(App);
setPrimeVueApp(app);

// Documentation to alter project's primary color  https://primevue.org/theming/styled/#noir
const MyPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: '{blue.50}',
            100: '{blue.100}',
            200: '{blue.200}',
            300: '{blue.300}',
            400: '{blue.400}',
            500: '{blue.500}',
            600: '{blue.600}',
            700: '{blue.700}',
            800: '{blue.800}',
            900: '{blue.900}',
            950: '{blue.950}'
        },
        
    }
});

app.use(router);
app.use(PrimeVue, {
    theme: {
        preset: MyPreset,
        options: {
            darkModeSelector: '.app-dark'
        }
    }
});
app.use(pinia);
app.use(i18n);

const bootstrap = async () => {
    await ensurePrimeVueFeedbackServices();
    app.mount('#app');
};

bootstrap();
