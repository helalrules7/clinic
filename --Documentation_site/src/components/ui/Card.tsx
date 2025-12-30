import { motion } from 'framer-motion';
import clsx from 'clsx';
import type { ReactNode } from 'react';

interface CardProps {
    children: ReactNode;
    className?: string;
    title?: string;
    icon?: React.ElementType;
}

export default function Card({ children, className, title, icon: Icon }: CardProps) {
    return (
        <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className={clsx('glass-panel p-6 rounded-xl', className)}
        >
            {title && (
                <div className="flex items-center gap-3 mb-4">
                    {Icon && <div className="p-2 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400"><Icon size={20} /></div>}
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100">{title}</h3>
                </div>
            )}
            <div className="text-gray-600 dark:text-gray-300">
                {children}
            </div>
        </motion.div>
    );
}
