import React from 'react';
import { useTranslation } from 'react-i18next';
import Hero from '../components/ui/Hero';
import Section from '../components/ui/Section';
import { GitBranch, Sparkles, Zap, Shield, TrendingUp, Package, Layers, Code, Search, Bell } from 'lucide-react';

export default function ChangeLog() {
    const { t } = useTranslation();

    const VersionSection = ({ version, date, badgeColor, icon, children }: { version: string, date?: string, badgeColor: string, icon: React.ReactNode, children: React.ReactNode }) => (
        <div className="mb-6 pb-6 border-b border-gray-200 dark:border-white/10 last:border-b-0">
            <div className="flex items-center gap-3 mb-3">
                <div className={`p-2 rounded-lg ${badgeColor} text-white flex-shrink-0`}>
                    {icon}
                </div>
                <div className="flex-1">
                    <h3 className="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span className={`px-3 py-1 rounded-lg text-sm font-semibold ${badgeColor}`}>
                            {version}
                        </span>
                        {date && <span className="text-sm font-normal text-gray-500 dark:text-gray-400">({date})</span>}
                    </h3>
                </div>
            </div>
            <div className="ml-14 mt-2">
                <div className="bg-gray-50 dark:bg-slate-900/50 border border-gray-200 dark:border-white/10 rounded-lg p-4 font-mono text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap break-words">
                    {children}
                </div>
            </div>
        </div>
    );

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.changelog.hero.title')}
                subtitle={t('sections.changelog.hero.subtitle')}
                badge={t('sections.changelog.hero.badge')}
            />

            <div className="bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-500 dark:border-amber-400 p-4 mb-6 rounded-r-lg">
                <div className="flex items-start gap-3">
                    <div className="flex-shrink-0 mt-0.5">
                        <svg className="w-5 h-5 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h4 className="text-sm font-semibold text-amber-800 dark:text-amber-200 mb-1">
                            {t('sections.changelog.notice.title')}
                        </h4>
                        <p className="text-sm text-amber-700 dark:text-amber-300">
                            {t('sections.changelog.notice.content')}
                        </p>
                    </div>
                </div>
            </div>

            <Section title={t('sections.changelog.overview.title')} icon={<GitBranch />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.changelog.overview.content')}
                </p>
            </Section>

            {/* Version 8.0.0 */}
            <VersionSection 
                version="v8.0.0" 
                date="2025-12-25"
                badgeColor="bg-gradient-to-r from-indigo-500 to-purple-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v8_0_0.content')}
            </VersionSection>

            {/* Version 7.3.2 */}
            <VersionSection 
                version="v7.3.2" 
                date="2025-12-24"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_3_2.content')}
            </VersionSection>

            {/* Version 7.3.1 */}
            <VersionSection 
                version="v7.3.1" 
                date="2025-12-23"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_3_1.content')}
            </VersionSection>

            {/* Version 7.3.0 */}
            <VersionSection 
                version="v7.3.0" 
                date="2025-12-23"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_3_0.content')}
            </VersionSection>

            {/* Version 7.2.9 */}
            <VersionSection 
                version="v7.2.9" 
                date="2025-12-23"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_2_9.content')}
            </VersionSection>

            {/* Version 7.2.8 */}
            <VersionSection 
                version="v7.2.8" 
                date="2025-12-22"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_2_8.content')}
            </VersionSection>

            {/* Version 7.2.7 */}
            <VersionSection 
                version="v7.2.7" 
                date="2025-12-20"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_2_7.content')}
            </VersionSection>

            {/* Version 7.2.6 */}
            <VersionSection 
                version="v7.2.6" 
                date="2025-12-20"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_2_6.content')}
            </VersionSection>

            {/* Version 7.2.5 */}
            <VersionSection 
                version="v7.2.5" 
                date="2025-12-19"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_2_5.content')}
            </VersionSection>

            {/* Version 7.2.4 */}
            <VersionSection 
                version="v7.2.4" 
                date="2025-12-19"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_2_4.content')}
            </VersionSection>

            {/* Version 7.2.3 */}
            <VersionSection 
                version="v7.2.3" 
                date="2025-12-19"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_2_3.content')}
            </VersionSection>

            {/* Version 7.2.2 */}
            <VersionSection 
                version="v7.2.2" 
                date="2025-12-19"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_2_2.content')}
            </VersionSection>

            {/* Version 7.2.1 */}
            <VersionSection 
                version="v7.2.1" 
                date="2025-12-19"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_2_1.content')}
            </VersionSection>

            {/* Version 7.2.0 */}
            <VersionSection 
                version="v7.2.0" 
                date="2025-12-19"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_2_0.content')}
            </VersionSection>

            {/* Version 7.1.5 */}
            <VersionSection 
                version="v7.1.5" 
                date="2025-12-18"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_1_5.content')}
            </VersionSection>

            {/* Version 7.1.4 */}
            <VersionSection 
                version="v7.1.4" 
                date="2025-12-18"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_1_4.content')}
            </VersionSection>

            {/* Version 7.1.3 */}
            <VersionSection 
                version="v7.1.3" 
                date="2025-12-17"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_1_3.content')}
            </VersionSection>

            {/* Version 7.1.2 */}
            <VersionSection 
                version="v7.1.2" 
                date="2025-12-17"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_1_2.content')}
            </VersionSection>

            {/* Version 7.1.1 */}
            <VersionSection 
                version="v7.1.1" 
                date="2025-12-17"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_1_1.content')}
            </VersionSection>

            {/* Version 7.1.0 */}
            <VersionSection 
                version="v7.1.0" 
                date="2025-12-17"
                badgeColor="bg-gradient-to-r from-purple-500 to-pink-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v7_1_0.content')}
            </VersionSection>

            {/* Version 7.0.9 */}
            <VersionSection 
                version="v7.0.9" 
                date="2025-12-15"
                badgeColor="bg-gradient-to-r from-blue-500 to-indigo-600"
                icon={<Search size={20} />}
            >
                {t('sections.changelog.v7_0_9.content')}
            </VersionSection>

            {/* Version 7.0.8 */}
            <VersionSection 
                version="v7.0.8" 
                date="2025-12-15"
                badgeColor="bg-gradient-to-r from-blue-500 to-indigo-600"
                icon={<Search size={20} />}
            >
                {t('sections.changelog.v7_0_8.content')}
            </VersionSection>

            {/* Version 7.0.7 */}
            <VersionSection 
                version="v7.0.7" 
                date="2025-12-14"
                badgeColor="bg-gradient-to-r from-blue-500 to-indigo-600"
                icon={<Search size={20} />}
            >
                {t('sections.changelog.v7_0_7.content')}
            </VersionSection>

            {/* Version 7.0.6 */}
            <VersionSection 
                version="v7.0.6" 
                date="2025-12-13"
                badgeColor="bg-gradient-to-r from-blue-500 to-indigo-600"
                icon={<Search size={20} />}
            >
                {t('sections.changelog.v7_0_6.content')}
            </VersionSection>

            {/* Version 7.0.5 */}
            <VersionSection 
                version="v7.0.5" 
                date="2025-12-13"
                badgeColor="bg-gradient-to-r from-blue-500 to-indigo-600"
                icon={<Search size={20} />}
            >
                {t('sections.changelog.v7_0_5.content')}
            </VersionSection>

            {/* Version 7.0.4 */}
            <VersionSection 
                version="v7.0.4" 
                date="2025-12-11"
                badgeColor="bg-gradient-to-r from-blue-500 to-indigo-600"
                icon={<Shield size={20} />}
            >
                {t('sections.changelog.v7_0_4.content')}
            </VersionSection>

            {/* Version 7.0.3 */}
            <VersionSection 
                version="v7.0.3" 
                date="2025-12-11"
                badgeColor="bg-gradient-to-r from-blue-500 to-indigo-600"
                icon={<Shield size={20} />}
            >
                {t('sections.changelog.v7_0_3.content')}
            </VersionSection>

            {/* Version 7.0.2 */}
            <VersionSection 
                version="v7.0.2" 
                date="2025-12-11"
                badgeColor="bg-gradient-to-r from-blue-500 to-indigo-600"
                icon={<Shield size={20} />}
            >
                {t('sections.changelog.v7_0_2.content')}
            </VersionSection>

            {/* Version 7.0.1 */}
            <VersionSection 
                version="v7.0.1" 
                date="2025-12-11"
                badgeColor="bg-gradient-to-r from-blue-500 to-indigo-600"
                icon={<Code size={20} />}
            >
                {t('sections.changelog.v7_0_1.content')}
            </VersionSection>

            {/* Version 7.0 */}
            <VersionSection 
                version="v7.0" 
                date="2025-11-20"
                badgeColor="bg-gradient-to-r from-indigo-500 to-purple-600"
                icon={<Layers size={20} />}
            >
                {t('sections.changelog.v7_0.content')}
            </VersionSection>

            {/* Version 6.2 */}
            <VersionSection 
                version="v6.2" 
                date="2025-11-16"
                badgeColor="bg-gradient-to-r from-amber-500 to-orange-600"
                icon={<Bell size={20} />}
            >
                {t('sections.changelog.v6_2.content')}
            </VersionSection>

            {/* Version 6.1 */}
            <VersionSection 
                version="v6.1" 
                date="2025-11-16"
                badgeColor="bg-gradient-to-r from-amber-500 to-orange-600"
                icon={<Sparkles size={20} />}
            >
                {t('sections.changelog.v6_1.content')}
            </VersionSection>

            {/* Version 6.0 */}
            <VersionSection 
                version="v6.0" 
                date="2025-11-13"
                badgeColor="bg-gradient-to-r from-blue-500 to-indigo-600"
                icon={<Layers size={20} />}
            >
                {t('sections.changelog.v6_0.content')}
            </VersionSection>

            {/* Version 5.1 */}
            <VersionSection 
                version="v5.1" 
                badgeColor="bg-gradient-to-r from-cyan-500 to-blue-600"
                icon={<Package size={20} />}
            >
                {t('sections.changelog.v5_1.content')}
            </VersionSection>

            {/* Version 5.0 */}
            <VersionSection 
                version="v5.0" 
                badgeColor="bg-gradient-to-r from-blue-500 to-indigo-600"
                icon={<TrendingUp size={20} />}
            >
                {t('sections.changelog.v5_0.content')}
            </VersionSection>

            {/* Version 4.0 */}
            <VersionSection 
                version="v4.0" 
                badgeColor="bg-gradient-to-r from-green-500 to-emerald-600"
                icon={<Shield size={20} />}
            >
                {t('sections.changelog.v4_0.content')}
            </VersionSection>

            {/* Version 3.0 */}
            <VersionSection 
                version="v3.0" 
                badgeColor="bg-gradient-to-r from-yellow-500 to-amber-600"
                icon={<Zap size={20} />}
            >
                {t('sections.changelog.v3_0.content')}
            </VersionSection>

            {/* Version 2.0 */}
            <VersionSection 
                version="v2.0" 
                badgeColor="bg-gradient-to-r from-red-500 to-rose-600"
                icon={<TrendingUp size={20} />}
            >
                {t('sections.changelog.v2_0.content')}
            </VersionSection>

            {/* Version 1.0 */}
            <VersionSection 
                version="v1.0" 
                badgeColor="bg-gradient-to-r from-gray-500 to-slate-600"
                icon={<Layers size={20} />}
            >
                {t('sections.changelog.v1_0.content')}
            </VersionSection>

            <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 mt-8">
                <div className="flex items-start gap-3">
                    <GitBranch className="text-blue-600 dark:text-blue-400 flex-shrink-0 mt-1" size={24} />
                    <div>
                        <h4 className="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                            {t('sections.changelog.github.title')}
                        </h4>
                        <p className="text-blue-800 dark:text-blue-200">
                            {t('sections.changelog.github.description')}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
