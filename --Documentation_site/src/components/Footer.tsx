import { useLocation, Link } from 'react-router-dom';
import { useNavigation, type NavItem } from '../hooks/useNavigation';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useMemo } from 'react';

export default function Footer() {
    const { t } = useTranslation();
    const location = useLocation();
    const navigation = useNavigation();

    const flatLinks = useMemo(() => {
        const flattened: { to: string; label: string; parentLabel?: string }[] = [];
        const traverse = (items: NavItem[], parent?: string) => {
            items.forEach(item => {
                if (item.to) {
                    flattened.push({
                        to: item.to,
                        label: item.label,
                        parentLabel: parent
                    });
                }
                if (item.items) {
                    // If it's a top-level section with items, use its label as parent
                    // If we're already deeper, we might want to keep the top parent or chain them
                    // For now, let's use the immediate parent which seems to be the request "main item title / subitem title"
                    traverse(item.items, item.label);
                }
            });
        };
        traverse(navigation);
        return flattened;
    }, [navigation]);

    const currentIndex = flatLinks.findIndex(link => link.to === location.pathname);
    const prev = currentIndex > 0 ? flatLinks[currentIndex - 1] : null;
    const next = currentIndex < flatLinks.length - 1 ? flatLinks[currentIndex + 1] : null;

    if (currentIndex === -1) return null;

    const renderLabel = (item: { label: string; parentLabel?: string }) => {
        return (
            <span className="font-semibold text-gray-900 dark:text-gray-100 group-hover:text-primary-600 dark:group-hover:text-primary-400">
                {item.parentLabel && (
                    <span className="text-gray-500 dark:text-gray-400 font-normal">
                        {item.parentLabel} /{' '}
                    </span>
                )}
                {item.label}
            </span>
        );
    };

    return (
        <footer className="mt-16 pt-8 border-t border-gray-200 dark:border-gray-800">
            <div className="flex flex-col md:flex-row justify-between gap-4">
                {prev ? (
                    <Link
                        to={prev.to}
                        className="flex-1 flex flex-col p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:border-primary-500 dark:hover:border-primary-500 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all group min-w-[200px]"
                    >
                        <span className="text-xs text-gray-500 dark:text-gray-400 mb-1 flex items-center gap-1 group-hover:text-primary-500 transition-colors">
                            <ChevronLeft size={14} />
                            {t('common.previous')}
                        </span>
                        {renderLabel(prev)}
                    </Link>
                ) : <div className="flex-1" />}

                {next ? (
                    <Link
                        to={next.to}
                        className="flex-1 flex flex-col items-end p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:border-primary-500 dark:hover:border-primary-500 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all group min-w-[200px]"
                    >
                        <span className="text-xs text-gray-500 dark:text-gray-400 mb-1 flex items-center gap-1 group-hover:text-primary-500 transition-colors">
                            {t('common.next')}
                            <ChevronRight size={14} />
                        </span>
                        {renderLabel(next)}
                    </Link>
                ) : <div className="flex-1" />}
            </div>
        </footer>
    );
}
