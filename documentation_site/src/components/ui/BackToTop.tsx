import { useState, useEffect, useRef } from 'react';
import { ArrowUp } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import clsx from 'clsx';

export default function BackToTop() {
    const [isVisible, setIsVisible] = useState(false);
    const { } = useTranslation();
    const rafRef = useRef<number | null>(null);
    const isVisibleRef = useRef(false);

    useEffect(() => {
        const toggleVisibility = () => {
            // Cancel any pending animation frame
            if (rafRef.current !== null) {
                cancelAnimationFrame(rafRef.current);
            }

            // Use requestAnimationFrame for synchronized updates
            rafRef.current = requestAnimationFrame(() => {
                const currentScrollY = window.pageYOffset;
                const shouldBeVisible = currentScrollY > 300;
                
                // Only update state if it actually changed
                if (isVisibleRef.current !== shouldBeVisible) {
                    isVisibleRef.current = shouldBeVisible;
                    setIsVisible(shouldBeVisible);
                }
            });
        };

        window.addEventListener('scroll', toggleVisibility, { passive: true });

        return () => {
            window.removeEventListener('scroll', toggleVisibility);
            if (rafRef.current !== null) {
                cancelAnimationFrame(rafRef.current);
            }
        };
    }, []);

    const scrollToTop = () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth',
        });
    };

    if (!isVisible) {
        return null;
    }

    return (
        <button
            onClick={scrollToTop}
            className={clsx(
                "fixed bottom-8 z-50 p-3 rounded-full shadow-lg backdrop-blur-md group",
                "bg-white/80 dark:bg-dark-800/80 border border-gray-200 dark:border-gray-700",
                "text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-500/50 dark:hover:border-primary-500/50",
                // LTR: right-8, RTL: left-8 (reversed as user requested: right for english (ltr), left for arabic (rtl))
                // Tailwind RTL support: 'ltr:right-8 rtl:left-8'
                "ltr:right-8 rtl:left-8"
            )}
            aria-label="Back to top"
        >
            <ArrowUp className="w-5 h-5 group-hover:-translate-y-1" />
        </button>
    );
}
