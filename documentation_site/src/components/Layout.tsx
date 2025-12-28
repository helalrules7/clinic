import { useState, useEffect } from 'react';
import { Outlet, useLocation } from 'react-router-dom';
import Sidebar from './Sidebar';
import Navbar from './Navbar';
import ImageModal from './ImageModal';
import Footer from './Footer';
import BackToTop from './ui/BackToTop';
import { useTranslation } from 'react-i18next';
import clsx from 'clsx';

export default function Layout() {
    const [isSidebarOpen, setIsSidebarOpen] = useState(false);
    const { i18n, t } = useTranslation();
    const location = useLocation();

    useEffect(() => {
        const dir = i18n.language === 'ar' ? 'rtl' : 'ltr';
        document.documentElement.dir = dir;
        document.documentElement.lang = i18n.language;
    }, [i18n.language]);

    useEffect(() => {
        const getPageTitle = (path: string) => {
            if (path === '/') return t('nav.overview');
            if (path === '/architecture') return t('nav.architecture');
            if (path === '/api') return t('nav.api');
            if (path === '/setup') return t('nav.setup');
            if (path.startsWith('/dashboards/doctor')) return t('sections.doctor_dashboard.title');
            if (path.startsWith('/dashboards/secretary')) return t('nav.secretary_dashboard');
            if (path.startsWith('/dashboards/admin')) return t('nav.admin_dashboard');
            if (path.startsWith('/modules/admin')) return t('nav.admin_module_link');
            if (path.startsWith('/modules/doctor')) return t('nav.doctor_module');
            if (path.startsWith('/modules/secretary')) return t('nav.secretary_module');
            if (path.startsWith('/ui-components/sidebar')) return t('nav.sidebar');
            if (path.startsWith('/ui-components/notifications')) return t('nav.notifications');
            if (path.startsWith('/ui-components/theme-switch')) return t('nav.theme_switch');
            if (path.startsWith('/ui-components/dock')) return t('nav.dock');
            if (path.startsWith('/ui-components/search')) return t('nav.search');
            if (path.startsWith('/doctors-pages/calendar')) return t('nav.doctor_calendar');
            if (path.startsWith('/doctors-pages/patients')) return t('nav.doctors_view');
            if (path.startsWith('/doctors-pages/forum')) return t('nav.forum');
            if (path.startsWith('/doctors-pages/drugs')) return t('nav.drugs');
            if (path.startsWith('/doctors-pages/finance')) return t('nav.doctors_view');
            if (path.startsWith('/doctors-pages/reports')) return t('nav.reports');
            if (path.startsWith('/doctors-pages/medications')) return t('nav.medications');
            if (path.startsWith('/doctors-pages/glasses')) return t('nav.glasses');
            if (path.startsWith('/doctors-pages/media')) return t('nav.media');
            if (path.startsWith('/doctors-pages/alerts')) return t('nav.alerts');
            if (path.startsWith('/doctors-pages/notes')) return t('nav.notes');
            if (path.startsWith('/doctors-pages/profile')) return t('nav.profile');
            if (path.startsWith('/doctors-pages/patient-profile')) return t('nav.patient_profile_section');
            if (path.startsWith('/doctors-pages/settings')) return t('nav.settings');
            if (path.startsWith('/changelog')) return t('nav.changelog');
            return '';
        };

        const pageTitle = getPageTitle(location.pathname);

        if (pageTitle) {
            document.title = `${t('common.title')} | ${pageTitle}`;
        } else {
            document.title = t('common.title');
        }
    }, [location.pathname, t, i18n.language]);

    // Scroll to top when route changes or language changes
    useEffect(() => {
        // Use a small delay to ensure DOM has updated, especially for language changes
        const scrollTimeout = setTimeout(() => {
            // Use requestAnimationFrame for smooth scroll
            requestAnimationFrame(() => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }, 50); // Small delay to ensure content has re-rendered

        return () => clearTimeout(scrollTimeout);
    }, [location.pathname, i18n.language]);

    // Prevent body scroll when sidebar is open on mobile
    useEffect(() => {
        if (isSidebarOpen) {
            document.body.classList.add('sidebar-open');
        } else {
            document.body.classList.remove('sidebar-open');
        }
        return () => {
            document.body.classList.remove('sidebar-open');
        };
    }, [isSidebarOpen]);

    // Image Zoom Logic
    const [zoomedImage, setZoomedImage] = useState<string | null>(null);

    useEffect(() => {
        // Global click listener for zoom
        const handleImageClick = (e: MouseEvent) => {
            const target = e.target as HTMLElement;
            if (target.tagName === 'IMG' && !target.closest('.no-zoom') && !target.closest('button') && !target.closest('a')) {
                const img = target as HTMLImageElement;
                if (img.src) {
                    e.preventDefault();
                    setZoomedImage(img.src);
                }
            }
        };

        // Wrap images with hover effect
        const wrapImages = () => {
            const images = document.querySelectorAll('main img');
            images.forEach((img) => {
                if (
                    img.closest('.image-hover-wrapper') ||
                    img.closest('button') ||
                    img.closest('a') ||
                    img.classList.contains('no-zoom')
                ) return;

                // Create wrapper
                const wrapper = document.createElement('div');
                // w-full flex justify-center to make the container full width and center the content
                wrapper.className = 'image-hover-wrapper relative group flex justify-center w-full cursor-zoom-in rounded-lg overflow-hidden';

                // Add scale effect to image instead of wrapper (Removed per previous request)
                // Enforce w-auto to prevent stretching, remove w-full if present
                img.classList.remove('w-full');
                img.classList.add('w-auto', 'transition-transform', 'duration-300');

                // Create overlay
                const overlay = document.createElement('div');
                overlay.className = 'absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center pointer-events-none';

                // Zoom Icon (Maximize)
                overlay.innerHTML = `
                    <div class="bg-black/50 p-3 rounded-full backdrop-blur-sm transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/>
                        </svg>
                    </div>
                `;

                // Wrap image
                img.parentNode?.insertBefore(wrapper, img);
                wrapper.appendChild(img);
                wrapper.appendChild(overlay);
            });
        };

        document.addEventListener('click', handleImageClick);
        // Run wrapping on mount and location change
        const timeoutId = setTimeout(wrapImages, 100); // Small delay to ensure content render

        return () => {
            document.removeEventListener('click', handleImageClick);
            clearTimeout(timeoutId);
        };
    }, [location.pathname, t]); // Re-run when route or language changes

    return (
        <div className={clsx(
            "min-h-screen bg-gray-50 dark:bg-dark-900 flex",
            isSidebarOpen && "md:overflow-hidden"
        )}>
            <Sidebar isOpen={isSidebarOpen} onClose={() => setIsSidebarOpen(false)} />

            <div className={clsx(
                "flex-1 flex flex-col min-w-0 md:ps-[300px] transition-all duration-300",
                isSidebarOpen && "md:overflow-hidden"
            )}>
                <Navbar onMenuClick={() => setIsSidebarOpen(true)} />

                <main className="flex-1 p-4 md:p-8 max-w-7xl mx-auto w-full animate-fade-in flex flex-col">
                    <div className="flex-1">
                        <Outlet />
                    </div>
                    <Footer />
                </main>
            </div>

            <ImageModal src={zoomedImage} onClose={() => setZoomedImage(null)} />
            <BackToTop />
        </div>
    );
}
