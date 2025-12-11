export class Router {
    constructor() {
        this.routes = new Map();
        this.currentRoute = null;
        this.initRoutes();
    }

    initRoutes() {
        // Overview routes
        this.routes.set('#/overview', () => import('./pages/overview.js'));
        this.routes.set('#/overview/features', () => import('./pages/overview.js'));
        this.routes.set('#/overview/requirements', () => import('./pages/overview.js'));
        this.routes.set('#/overview/quick-start', () => import('./pages/overview.js'));

        // Roles routes
        this.routes.set('#/roles/doctor', () => import('./pages/roles/doctor.js'));
        this.routes.set('#/roles/secretary', () => import('./pages/roles/secretary.js'));
        this.routes.set('#/roles/admin', () => import('./pages/roles/admin.js'));

        // Features routes
        this.routes.set('#/features/patients', () => import('./pages/features/patients.js'));
        this.routes.set('#/features/appointments', () => import('./pages/features/appointments.js'));
        this.routes.set('#/features/prescriptions', () => import('./pages/features/prescriptions.js'));
        this.routes.set('#/features/payments', () => import('./pages/features/payments.js'));
        this.routes.set('#/features/reports', () => import('./pages/features/reports.js'));
        this.routes.set('#/features/alerts', () => import('./pages/features/alerts.js'));
        this.routes.set('#/features/forum', () => import('./pages/features/forum.js'));
        this.routes.set('#/features/media', () => import('./pages/features/media.js'));
        this.routes.set('#/features/notifications', () => import('./pages/features/notifications.js'));

        // API routes
        this.routes.set('#/api/overview', () => import('./pages/api/overview.js'));
        this.routes.set('#/api/authentication', () => import('./pages/api/authentication.js'));
        this.routes.set('#/api/endpoints', () => import('./pages/api/endpoints.js'));
        this.routes.set('#/api/examples', () => import('./pages/api/examples.js'));

        // Default route
        this.routes.set('#/', () => import('./pages/overview.js'));
    }

    init() {
        // Handle initial route
        this.handleRoute();

        // Listen for hash changes
        window.addEventListener('hashchange', () => {
            this.handleRoute();
        });

        // Update active nav links
        this.updateActiveNav();
    }

    async handleRoute() {
        const hash = window.location.hash || '#/';
        const route = this.routes.get(hash) || this.routes.get('#/');

        try {
            // Show loading
            this.showLoading();

            // Load route module
            const module = await route();
            const page = module.default || module;

            // Render page
            await this.renderPage(page);

            // Update active nav
            this.updateActiveNav();

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (error) {
            console.error('Route error:', error);
            this.renderError();
        }
    }

    async renderPage(page) {
        const contentWrapper = document.querySelector('.content-wrapper');
        if (!contentWrapper) return;

        // Get content from page
        const content = typeof page === 'function' ? await page() : page;

        // Add fade animation
        contentWrapper.style.opacity = '0';
        contentWrapper.style.transform = 'translateY(20px)';

        setTimeout(() => {
            contentWrapper.innerHTML = content;
            contentWrapper.style.opacity = '1';
            contentWrapper.style.transform = 'translateY(0)';
            contentWrapper.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            
            // Re-initialize scroll reveal for new content
            this.setupScrollReveal();
        }, 100);
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

    showLoading() {
        const contentWrapper = document.querySelector('.content-wrapper');
        if (contentWrapper) {
            contentWrapper.innerHTML = `
                <div class="loading-content">
                    <div class="crypto-loader">
                        <div class="loader-ring"></div>
                        <div class="loader-ring"></div>
                        <div class="loader-ring"></div>
                    </div>
                </div>
            `;
        }
    }

    renderError() {
        const contentWrapper = document.querySelector('.content-wrapper');
        if (contentWrapper) {
            contentWrapper.innerHTML = `
                <div class="error-page">
                    <h1>404</h1>
                    <p>الصفحة غير موجودة</p>
                    <a href="#/" class="btn btn-primary">العودة للصفحة الرئيسية</a>
                </div>
            `;
        }
    }

    updateActiveNav() {
        const hash = window.location.hash || '#/';
        const navLinks = document.querySelectorAll('.nav-link');

        navLinks.forEach(link => {
            const linkHash = link.getAttribute('href');
            if (linkHash === hash || hash.startsWith(linkHash + '/')) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }
}
