import React from 'react';

interface SectionProps {
    title: string;
    subtitle?: string;
    id?: string;
    icon?: React.ReactNode;
    className?: string;
    children: React.ReactNode;
}

export default function Section({ title, subtitle, id, icon, className = '', children }: SectionProps) {
    return (
        <section id={id} className={`max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 ${className}`}>
            <div className="mb-8">
                <div className="flex items-center gap-3 mb-2">
                    {icon && (
                        <div className="p-2 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg text-white flex-shrink-0">
                            {icon}
                        </div>
                    )}
                    <h2 className="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">
                        {title}
                    </h2>
                </div>
                {subtitle && (
                    <p className="text-lg text-gray-600 dark:text-gray-400 mt-2 ml-14">
                        {subtitle}
                    </p>
                )}
            </div>
            <div className="prose prose-lg dark:prose-invert max-w-none">
                {children}
            </div>
        </section>
    );
}
