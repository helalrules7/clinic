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
            viewport={{ once: true, margin: '-50px' }}
            whileHover={{ y: -3 }}
            transition={{ duration: 0.4, ease: [0.22, 0.61, 0.36, 1] }}
            className={clsx(
                'group relative p-6 rounded-2xl',
                'bg-white/70 dark:bg-white/[0.04] backdrop-blur-md',
                'border border-gray-200/70 dark:border-white/10',
                'shadow-sm hover:shadow-xl hover:shadow-indigo-500/10 dark:hover:shadow-indigo-500/5',
                'hover:border-indigo-300/60 dark:hover:border-indigo-400/30',
                'transition-shadow duration-300 overflow-hidden',
                className
            )}
        >
            {/* hover glow strip on top */}
            <div className="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-indigo-400/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
            {/* subtle corner glow on hover */}
            <div className="pointer-events-none absolute -top-12 -right-12 w-32 h-32 rounded-full bg-indigo-500/0 group-hover:bg-indigo-500/15 blur-2xl transition-colors duration-500" />

            {title && (
                <div className="relative flex items-center gap-3 mb-4">
                    {Icon && (
                        <div className="p-2 rounded-lg bg-gradient-to-br from-indigo-500/15 to-violet-500/10 text-indigo-600 dark:text-indigo-300 ring-1 ring-indigo-500/20 dark:ring-indigo-400/20">
                            <Icon size={20} />
                        </div>
                    )}
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100 tracking-tight">{title}</h3>
                </div>
            )}
            <div className="relative text-gray-600 dark:text-gray-300">
                {children}
            </div>
        </motion.div>
    );
}
