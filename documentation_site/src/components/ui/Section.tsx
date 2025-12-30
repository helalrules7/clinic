import type { ReactNode } from 'react';
import { motion } from 'framer-motion';

interface SectionProps {
    title: string;
    children: ReactNode;
    icon?: ReactNode;
    className?: string;
    id?: string;
}

export default function Section({ title, children, icon, className = '', id }: SectionProps) {
    return (
        <motion.section
            id={id}
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className={`bg-white/80 dark:bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-gray-200 dark:border-white/10 shadow-sm dark:shadow-none ${className}`}
        >
            <div className="flex items-center gap-3 mb-6">
                {icon && (
                    <div className="p-2 rounded-lg bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400">
                        {icon}
                    </div>
                )}
                <h2 className="text-2xl font-bold text-gray-900 dark:text-white">{title}</h2>
            </div>
            {children}
        </motion.section>
    );
}
