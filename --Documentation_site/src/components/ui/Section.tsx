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
            viewport={{ once: true, margin: '-50px' }}
            transition={{ duration: 0.5, ease: [0.22, 0.61, 0.36, 1] }}
            className={`
                relative bg-white/75 dark:bg-white/[0.04] backdrop-blur-md
                rounded-2xl p-8
                border border-gray-200/70 dark:border-white/10
                shadow-sm dark:shadow-none
                overflow-hidden
                ${className}
            `}
        >
            {/* accent line top */}
            <div className="pointer-events-none absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-indigo-400/60 to-transparent" />

            <div className="flex items-center gap-3 mb-6">
                {icon && (
                    <div className="
                        p-2.5 rounded-xl
                        bg-gradient-to-br from-indigo-500/15 to-violet-500/10
                        text-indigo-600 dark:text-indigo-300
                        ring-1 ring-indigo-500/20 dark:ring-indigo-400/20
                        shadow-sm shadow-indigo-500/10
                    ">
                        {icon}
                    </div>
                )}
                <h2 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{title}</h2>
            </div>
            {children}
        </motion.section>
    );
}
