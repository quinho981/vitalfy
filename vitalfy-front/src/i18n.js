import { createI18n } from 'vue-i18n';
import en from './locales/en-US.json';
import pt from './locales/pt-BR.json';

const messages = { en, pt };

const i18n = createI18n({
    legacy: false,
    locale: 'pt',
    fallbackLocale: 'pt',
    messages
});

export default i18n;
