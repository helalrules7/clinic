import { Router } from './router.js';
import { i18n } from './i18n.js';
import { Search } from './search.js';
import { Sidebar } from './components/Sidebar.js';

// Initialize app
class App {
    constructor() {
        this.router = new Router();
        this.search = new Search();
        this.sidebar = new Sidebar();
        this.currentLang = localStorage.getItem('lang') || 'ar';
    }

    async init() {
        // Initialize i18n
        await i18n.init(this.currentLang);
        
        // Initialize router
        this.router.init();
        
        // Initialize search
        this.search.init();
        
        // Initialize sidebar
        this.sidebar.init();
        
        // Setup language toggle
        this.setupLanguageToggle();
        
        // Setup keyboard shortcuts
        this.setupKeyboardShortcuts();
        
        // Hide loading screen
        this.hideLoadingScreen();
        
        // Setup scroll reveal
        this.setupScrollReveal();
    }

    setupLanguageToggle() {
        const langToggle = document.getElementById('langToggle');
        if (!langToggle) return;

        langToggle.addEventListener('click', () => {
            const newLang = this.currentLang === 'ar' ? 'en' : 'ar';
            this.switchLanguage(newLang);
        });

        // Update toggle text
        this.updateLanguageToggle();
    }

    async switchLanguage(lang) {
        this.currentLang = lang;
        localStorage.setItem('lang', lang);
        
        // Update HTML attributes
        document.documentElement.lang = lang;
        document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
        
        // Update i18n
        await i18n.setLanguage(lang);
        
        // Update toggle text
        this.updateLanguageToggle();
        
        // Re-render current page
        this.router.handleRoute();
    }

    updateLanguageToggle() {
        const langToggle = document.getElementById('langToggle');
        if (langToggle) {
            langToggle.querySelector('span').textContent = this.currentLang === 'ar' ? 'EN' : 'AR';
        }
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+K or Cmd+K for search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.search.open();
            }
            
            // Escape to close search
            if (e.key === 'Escape') {
                this.search.close();
            }
        });
    }

    hideLoadingScreen() {
        const loadingScreen = document.getElementById('loading-screen');
        const app = document.getElementById('app');
        
        if (!loadingScreen || !app) return;
        
        setTimeout(() => {
            loadingScreen.classList.add('hidden');
            app.style.display = 'flex';
            
            setTimeout(() => {
                loadingScreen.style.display = 'none';
            }, 500);
        }, 800);
    }

    setupScrollReveal() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, {
            threshold: 0.1
        });

        document.querySelectorAll('.scroll-reveal').forEach(el => {
            observer.observe(el);
        });
    }
}

// Start app when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        const app = new App();
        app.init();
    });
} else {
    const app = new App();
    app.init();
}
