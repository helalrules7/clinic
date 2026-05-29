import { X, ChevronRight, ChevronDown } from 'lucide-react';
import { NavLink, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import clsx from 'clsx';
import { AnimatePresence, motion } from 'framer-motion';
import { useEffect, useRef, useState, useCallback, useMemo } from 'react';
import { useNavigation } from '../hooks/useNavigation';

const STORAGE_KEY = 'docs_sidebar_expanded_sections';

// Load expanded sections from localStorage
const loadExpandedSections = (): Record<string, boolean> => {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        return stored ? JSON.parse(stored) : {};
    } catch {
        return {};
    }
};

// Save expanded sections to localStorage
const saveExpandedSections = (sections: Record<string, boolean>) => {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(sections));
    } catch {
        // Ignore storage errors
    }
};

export default function Sidebar({ isOpen, onClose }: { isOpen: boolean; onClose: () => void }) {
    const { t, i18n } = useTranslation();
    const location = useLocation();
    const navRef = useRef<HTMLElement>(null);
    const activeLinkRef = useRef<HTMLElement>(null);
    const isInitializedRef = useRef(false);

    // Initialize expanded sections from localStorage on first render only
    const [expandedSections, setExpandedSections] = useState<Record<string, boolean>>(() => {
        const stored = loadExpandedSections();
        isInitializedRef.current = true;
        return stored;
    });

    const links = useNavigation();

    // Memoize active section detection to avoid unnecessary recalculations
    const activeSectionLabel = useMemo(() => {
        for (const link of links) {
            if (link.items) {
                const isActive = link.items.some(item => item.to === location.pathname);
                if (isActive) {
                    return link.label;
                }
            }
        }
        return null;
    }, [links, location.pathname]);

    // Auto-expand section containing active route (only when route changes)
    // Accordion behavior: collapse all other sections when expanding active one
    useEffect(() => {
        if (!isInitializedRef.current || !activeSectionLabel) return;

        setExpandedSections(() => {
            // If the active section is already expanded and no others are expanded, do nothing
            // (Simpler check: just enforce the state)

            const newState: Record<string, boolean> = {};
            // Collapse all sections, expand only the newly active one
            links.forEach(link => {
                if (link.items) {
                    newState[link.label] = link.label === activeSectionLabel;
                }
            });

            saveExpandedSections(newState);
            return newState;
        });
    }, [activeSectionLabel, links]);

    // Accordion behavior: when toggling a section, collapse all others
    const toggleSection = useCallback((label: string) => {
        setExpandedSections(prev => {
            const isCurrentlyExpanded = Boolean(prev[label]);

            // If clicking on an already expanded section, collapse it (close all)
            if (isCurrentlyExpanded) {
                const newState: Record<string, boolean> = {};
                // Collapse all sections
                links.forEach(link => {
                    if (link.items) {
                        newState[link.label] = false;
                    }
                });
                saveExpandedSections(newState);
                return newState;
            }

            // If expanding a section, collapse all others first
            const newState: Record<string, boolean> = {};
            links.forEach(link => {
                if (link.items) {
                    newState[link.label] = link.label === label;
                }
            });

            saveExpandedSections(newState);
            return newState;
        });
    }, [links]);

    const SidebarContent = () => (
        <div className="h-full flex flex-col p-4 overflow-y-auto w-full">
            <div className="flex items-center justify-between mb-6 px-2 py-3 rounded-xl bg-gradient-to-br from-indigo-500/10 via-violet-500/5 to-transparent border border-indigo-500/15 dark:border-indigo-400/10">
                <div className="flex items-center gap-2.5 min-w-0">
                    <div className="flex-shrink-0 w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 shadow-lg shadow-indigo-500/30 flex items-center justify-center">
                        <svg viewBox="0 0 24 24" fill="none" className="w-5 h-5 text-white">
                            <path d="M12 2 L4 6 v6 c0 5 3.5 9 8 10 c4.5-1 8-5 8-10 V6 z" stroke="currentColor" strokeWidth="1.6" strokeLinejoin="round" />
                            <circle cx="12" cy="11" r="2.5" fill="currentColor" />
                        </svg>
                    </div>
                    <div className="min-w-0 flex-1">
                        <h1 className="text-sm font-bold tracking-tight bg-gradient-to-r from-indigo-600 via-violet-600 to-fuchsia-600 dark:from-indigo-300 dark:via-violet-300 dark:to-fuchsia-300 bg-clip-text text-transparent truncate leading-tight">
                            {t('common.title')}
                        </h1>
                        <div className="flex items-center gap-1 mt-0.5">
                            <span className="inline-flex items-center gap-1 px-1.5 rounded text-[10px] font-semibold bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/25">
                                <span className="w-1 h-1 rounded-full bg-emerald-500 animate-pulse" />
                                v10.1.0
                            </span>
                        </div>
                    </div>
                </div>
                <button onClick={onClose} className="md:hidden p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors flex-shrink-0">
                    <X size={20} />
                </button>
            </div>

            <nav ref={navRef} className="flex-1 space-y-1 overflow-y-auto px-2">
                {links.map((link, idx) => (
                    <div key={idx}>
                        {link.items ? (
                            <div className="mb-2">
                                <button
                                    onClick={(e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        toggleSection(link.label);
                                    }}
                                    className="w-full flex items-center justify-between px-3 py-2 text-gray-500 dark:text-gray-400 font-medium hover:text-gray-900 dark:hover:text-gray-200 transition-colors rounded-md hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                >
                                    <div className="flex items-center gap-3">
                                        <link.icon size={18} />
                                        <span>{link.label}</span>
                                    </div>
                                    <ChevronDown
                                        size={16}
                                        className={clsx(
                                            "transition-transform duration-200",
                                            expandedSections[link.label] ? "transform rotate-180" : ""
                                        )}
                                    />
                                </button>
                                <AnimatePresence>
                                    {expandedSections[link.label] && (
                                        <motion.div
                                            key={`section-${link.label}`}
                                            initial={{ height: 0, opacity: 0 }}
                                            animate={{ height: "auto", opacity: 1 }}
                                            exit={{ height: 0, opacity: 0 }}
                                            transition={{ duration: 0.2 }}
                                            className="overflow-hidden"
                                        >
                                            <div className="ml-4 space-y-1 border-l-2 border-gray-100 dark:border-gray-800 pl-3 my-1">
                                                {link.items.map((subItem) => (
                                                    <NavLink
                                                        key={subItem.to}
                                                        to={subItem.to!}
                                                        ref={(el) => {
                                                            if (el && location.pathname === subItem.to) {
                                                                activeLinkRef.current = el;
                                                            }
                                                        }}
                                                        onClick={() => window.innerWidth < 768 && onClose()}
                                                        className={({ isActive }) =>
                                                            clsx(
                                                                'block px-3 py-2 text-sm rounded-md transition-colors',
                                                                isActive
                                                                    ? 'text-indigo-700 dark:text-indigo-200 font-semibold bg-gradient-to-r from-indigo-500/15 via-violet-500/8 to-transparent border-s-2 border-indigo-500 shadow-sm shadow-indigo-500/10'
                                                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100/80 dark:hover:bg-white/5 hover:translate-x-0.5 transition-all'
                                                            )
                                                        }
                                                    >
                                                        {subItem.label}
                                                    </NavLink>
                                                ))}
                                            </div>
                                        </motion.div>
                                    )}
                                </AnimatePresence>
                            </div>
                        ) : (
                            <NavLink
                                to={link.to!}
                                ref={(el) => {
                                    if (el && location.pathname === link.to) {
                                        activeLinkRef.current = el;
                                    }
                                }}
                                onClick={() => window.innerWidth < 768 && onClose()}
                                className={({ isActive }) =>
                                    clsx(
                                        'flex items-center gap-3 px-3 py-2 rounded-md transition-colors mb-1',
                                        isActive
                                            ? 'text-indigo-700 dark:text-indigo-200 font-semibold bg-gradient-to-r from-indigo-500/15 via-violet-500/8 to-transparent border-s-2 border-indigo-500 shadow-sm shadow-indigo-500/10'
                                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100/80 dark:hover:bg-white/5 hover:translate-x-0.5 transition-all'
                                    )
                                }
                            >
                                {({ isActive }) => (
                                    <>
                                        <link.icon size={18} />
                                        <span>{link.label}</span>
                                        {isActive && <ChevronRight size={14} className="ml-auto opacity-50" />}
                                    </>
                                )}
                            </NavLink>
                        )}
                    </div>
                ))}
            </nav>

            <div className="mt-auto pt-4 border-t border-gray-200 dark:border-gray-800 px-2">
                <p className="text-xs text-center text-gray-400 mb-1">
                    v10.1.0 · © 2026 Roaya
                </p>
                <p className="text-xs text-center text-gray-400">
                    By <a href="https://ahmedhelal.dev" target="_blank" rel="noopener noreferrer" className="hover:text-primary-500 transition-colors">Ahmed Helal</a>
                </p>
            </div>
        </div>
    );

    // Scroll to active item when location or language changes
    useEffect(() => {
        // Wait for DOM to update and animations to complete
        const scrollTimeout = setTimeout(() => {
            if (activeLinkRef.current && navRef.current) {
                const linkRect = activeLinkRef.current.getBoundingClientRect();
                const navRect = navRef.current.getBoundingClientRect();

                if (!linkRect || !navRect) return;

                const scrollTop = navRef.current.scrollTop || 0;
                const linkTop = linkRect.top - navRect.top + scrollTop;
                const navHeight = navRef.current.clientHeight || 0;
                const linkHeight = linkRect.height;

                // Calculate position to center the active link
                const targetScroll = linkTop - (navHeight / 2) + (linkHeight / 2);

                navRef.current.scrollTo({
                    top: Math.max(0, targetScroll),
                    behavior: 'smooth'
                });
            }
        }, 350); // Slightly longer delay to ensure expansion animations complete

        return () => clearTimeout(scrollTimeout);
    }, [location.pathname, i18n.language, expandedSections]);

    return (
        <>
            {/* Mobile Backdrop */}
            <AnimatePresence>
                {isOpen && (
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        onClick={onClose}
                        className="fixed inset-0 bg-black/50 z-40 md:hidden backdrop-blur-sm"
                    />
                )}
            </AnimatePresence>

            {/* Sidebar Container */}
            <aside
                className={clsx(
                    'fixed inset-y-0 start-0 z-50 w-[300px] bg-white/95 dark:bg-dark-900/95 backdrop-blur-xl border-e border-gray-200 dark:border-gray-800 transition-transform duration-300 md:!translate-x-0 overflow-hidden shadow-xl md:shadow-none',
                    isOpen ? 'translate-x-0' : 'ltr:-translate-x-full rtl:translate-x-full'
                )}
            >
                <div className="h-full w-full">
                    <SidebarContent />
                </div>
            </aside>
        </>
    );
}
