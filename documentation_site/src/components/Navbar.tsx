import { Menu, Search, X } from 'lucide-react';
import ThemeToggle from './ThemeToggle';
import LanguageSwitcher from './LanguageSwitcher';
import { useTranslation } from 'react-i18next';
import { useState, useEffect, useRef } from 'react';
import { searchDocs, type SearchResult } from '../lib/search';
import { useNavigate } from 'react-router-dom';

export default function Navbar({ onMenuClick }: { onMenuClick: () => void }) {
    const { t } = useTranslation('common');
    const navigate = useNavigate();
    const [query, setQuery] = useState('');
    const [results, setResults] = useState<SearchResult[]>([]);
    const [showResults, setShowResults] = useState(false);
    const searchRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (query.trim()) {
            setResults(searchDocs(query));
            setShowResults(true);
        } else {
            setShowResults(false);
        }
    }, [query]);

    // Click outside to close
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (searchRef.current && !searchRef.current.contains(event.target as Node)) {
                setShowResults(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleSelect = (path: string) => {
        navigate(path);
        setQuery('');
        setShowResults(false);
    };

    return (
        <header className="sticky top-0 z-30 h-16 glass-panel border-b-0 border-x-0 border-t-0 rounded-none px-4 flex items-center justify-between">
            <div className="flex items-center gap-4 flex-1">
                <button onClick={onMenuClick} className="md:hidden p-2 text-gray-600 dark:text-gray-400">
                    <Menu size={20} />
                </button>

                {/* Search Bar - Desktop & Mobile Friendly */}
                <div ref={searchRef} className="relative w-full max-w-md mx-auto md:mx-0">
                    <div className="flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-dark-800 rounded-lg w-full border border-transparent focus-within:border-primary-500 transition-colors">
                        <Search size={16} className="text-gray-500" />
                        <input
                            type="text"
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            onFocus={() => query && setShowResults(true)}
                            placeholder={t('search')}
                            className="bg-transparent border-none outline-none text-sm w-full dark:text-gray-200 placeholder-gray-500"
                        />
                        {query && (
                            <button onClick={() => setQuery('')} className="text-gray-400 hover:text-gray-600">
                                <X size={14} />
                            </button>
                        )}
                    </div>

                    {/* Search Results Dropdown */}
                    {showResults && (
                        <div className="absolute top-full mt-2 left-0 right-0 bg-white dark:bg-dark-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 max-h-96 overflow-y-auto animate-fade-in">
                            {results.length > 0 ? (
                                <div className="py-2">
                                    {results.map((result, idx) => (
                                        <button
                                            key={idx}
                                            onClick={() => handleSelect(result.path)}
                                            className="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-dark-700 transition-colors border-b border-gray-100 dark:border-gray-800 last:border-0"
                                        >
                                            <div className="flex items-center justify-between mb-1">
                                                <span className="font-medium text-gray-900 dark:text-gray-100">{result.title}</span>
                                                <span className="text-xs px-2 py-0.5 rounded bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400">
                                                    {result.category}
                                                </span>
                                            </div>
                                            <p className="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                {result.content}
                                            </p>
                                        </button>
                                    ))}
                                </div>
                            ) : (
                                <div className="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No results found.
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>

            <div className="flex items-center gap-2">
                <LanguageSwitcher />
                <ThemeToggle />
            </div>
        </header>
    );
}
