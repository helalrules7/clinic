import React from 'react';

interface HeroProps {
    title: string;
    subtitle: string;
    badge?: string;
}

export default function Hero({ title, subtitle, badge }: HeroProps) {
    return (
        <div className="relative overflow-hidden bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 dark:from-indigo-900/20 dark:via-purple-900/20 dark:to-pink-900/20 border-b border-gray-200 dark:border-gray-800">
            <div className="absolute inset-0 bg-grid-pattern opacity-5"></div>
            <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-24">
                <div className="text-center">
                    {badge && (
                        <span className="inline-block px-4 py-2 mb-4 text-sm font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900/30 rounded-full border border-indigo-200 dark:border-indigo-800">
                            {badge}
                        </span>
                    )}
                    <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-4 tracking-tight">
                        {title}
                    </h1>
                    <p className="text-xl sm:text-2xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed">
                        {subtitle}
                    </p>
                </div>
            </div>
        </div>
    );
}
