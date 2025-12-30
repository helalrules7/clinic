import React from 'react';
import { LucideIcon } from 'lucide-react';

interface CardProps {
    title: string;
    icon?: LucideIcon | React.ReactNode;
    className?: string;
    children: React.ReactNode;
}

export default function Card({ title, icon: Icon, className = '', children }: CardProps) {
    return (
        <div className={`p-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-slate-800 shadow-sm hover:shadow-md transition-shadow ${className}`}>
            <div className="flex items-start gap-4 mb-4">
                {Icon && (
                    <div className="p-3 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg text-white flex-shrink-0">
                        {typeof Icon === 'function' ? <Icon size={24} /> : Icon}
                    </div>
                )}
                <h3 className="text-xl font-bold text-gray-900 dark:text-white flex-1">
                    {title}
                </h3>
            </div>
            <div className="text-gray-600 dark:text-gray-300 leading-relaxed">
                {children}
            </div>
        </div>
    );
}
