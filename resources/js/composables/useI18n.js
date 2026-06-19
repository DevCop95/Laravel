import { ref, computed } from 'vue';
import es from '@/locales/es.json';
import en from '@/locales/en.json';

const locales = { es, en };

const currentLocale = ref(localStorage.getItem('locale') || 'es');

const translations = computed(() => locales[currentLocale.value] || locales.es);

const setLocale = (locale) => {
    if (locales[locale]) {
        currentLocale.value = locale;
        localStorage.setItem('locale', locale);
        document.documentElement.lang = locale;
    }
};

const t = (key) => {
    const keys = key.split('.');
    let result = translations.value;
    for (const k of keys) {
        result = result?.[k];
    }
    return result ?? key;
};

const availableLocales = [
    { code: 'es', label: 'Espanol' },
    { code: 'en', label: 'English' },
];

export function useI18n() {
    return {
        locale: currentLocale,
        t,
        setLocale,
        availableLocales,
    };
}
