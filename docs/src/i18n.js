export const i18n = {
    currentLang: 'ar',
    translations: {},

    async init(lang = 'ar') {
        this.currentLang = lang;
        await this.loadTranslations();
        this.applyTranslations();
        this.updateHTMLAttributes();
    },

    async loadTranslations() {
        // Load translations from data files
        try {
            const translations = await import('./data/translations.js');
            this.translations = translations.default || translations;
        } catch (error) {
            console.warn('Translations file not found, using defaults');
            this.translations = this.getDefaultTranslations();
        }
    },

    async setLanguage(lang) {
        this.currentLang = lang;
        this.applyTranslations();
        this.updateHTMLAttributes();
    },

    applyTranslations() {
        // Update all elements with data-i18n attribute
        document.querySelectorAll('[data-i18n]').forEach(element => {
            const key = element.getAttribute('data-i18n');
            const translation = this.t(key);
            if (translation) {
                if (element.tagName === 'INPUT' && element.type === 'text') {
                    element.placeholder = translation;
                } else {
                    element.textContent = translation;
                }
            }
        });
    },

    updateHTMLAttributes() {
        document.documentElement.lang = this.currentLang;
        document.documentElement.dir = this.currentLang === 'ar' ? 'rtl' : 'ltr';
    },

    t(key) {
        const keys = key.split('.');
        let value = this.translations[this.currentLang];
        
        for (const k of keys) {
            if (value && value[k]) {
                value = value[k];
            } else {
                return key; // Return key if translation not found
            }
        }
        
        return value;
    },

    getDefaultTranslations() {
        return {
            ar: {
                'loading': 'جاري التحميل...',
                'app-name': 'Roaya Clinic Docs',
                'nav-overview': 'نظرة عامة',
                'nav-introduction': 'مقدمة',
                'nav-features': 'الميزات',
                'nav-requirements': 'المتطلبات',
                'nav-quick-start': 'البدء السريع',
                'nav-roles': 'الأدوار',
                'nav-doctor': 'الطبيب',
                'nav-secretary': 'السكرتير',
                'nav-admin': 'المدير',
                'nav-patients': 'المرضى',
                'nav-appointments': 'المواعيد',
                'nav-prescriptions': 'الوصفات',
                'nav-payments': 'المدفوعات',
                'nav-reports': 'التقارير',
                'nav-alerts': 'التنبيهات',
                'nav-forum': 'المنتدى',
                'nav-media': 'الوسائط',
                'nav-notifications': 'الإشعارات',
                'nav-api': 'API',
                'nav-api-overview': 'نظرة عامة',
                'nav-api-auth': 'المصادقة',
                'nav-api-endpoints': 'Endpoints',
                'nav-api-examples': 'أمثلة'
            },
            en: {
                'loading': 'Loading...',
                'app-name': 'Roaya Clinic Docs',
                'nav-overview': 'Overview',
                'nav-introduction': 'Introduction',
                'nav-features': 'Features',
                'nav-requirements': 'Requirements',
                'nav-quick-start': 'Quick Start',
                'nav-roles': 'Roles',
                'nav-doctor': 'Doctor',
                'nav-secretary': 'Secretary',
                'nav-admin': 'Admin',
                'nav-patients': 'Patients',
                'nav-appointments': 'Appointments',
                'nav-prescriptions': 'Prescriptions',
                'nav-payments': 'Payments',
                'nav-reports': 'Reports',
                'nav-alerts': 'Alerts',
                'nav-forum': 'Forum',
                'nav-media': 'Media',
                'nav-notifications': 'Notifications',
                'nav-api': 'API',
                'nav-api-overview': 'Overview',
                'nav-api-auth': 'Authentication',
                'nav-api-endpoints': 'Endpoints',
                'nav-api-examples': 'Examples'
            }
        };
    }
};
