import { Globe } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useEffect } from 'react';

export default function LanguageSwitcher() {
    const { i18n, t } = useTranslation('common');

    const toggleLanguage = () => {
        const newLang = i18n.language === 'en' ? 'ar' : 'en';
        i18n.changeLanguage(newLang);
        localStorage.setItem('i18nextLng', newLang);
    };

    useEffect(() => {
        // Set RTL/LTR on body
        document.documentElement.dir = i18n.language === 'ar' ? 'rtl' : 'ltr';
        document.documentElement.lang = i18n.language;
    }, [i18n.language]);

    return (
        <button
            onClick={toggleLanguage}
            className="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors"
            title={t('language')}
        >
            <Globe size={20} />
            <span className="text-sm font-medium uppercase">{i18n.language === 'en' ? 'AR' : 'EN'}</span>
        </button>
    );
}
