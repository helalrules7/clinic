import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Activity, History, Heart, Eye, TrendingUp, Database, HardDrive, Code, Shield } from 'lucide-react';

export default function AdminDashboardDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.admin_dashboard.title')}
                subtitle={t('sections.admin_dashboard.subtitle')}
            />

            <Section title={t('sections.admin_dashboard.overview.title')} icon={<Activity />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.admin_dashboard.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.admin_dashboard.stats.title')} icon={<TrendingUp />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.admin_dashboard.stats.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.admin_dashboard.stats.cards_title')}</h3>
                        <div className="space-y-4 mb-6">
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400 mb-2">{t('sections.admin_dashboard.stats.cards.users.title')}</h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {t('sections.admin_dashboard.stats.cards.users.description')}
                                </p>
                                <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                    <li>{t('sections.admin_dashboard.stats.cards.users.metrics.total')}</li>
                                    <li>{t('sections.admin_dashboard.stats.cards.users.metrics.active')}</li>
                                    <li>{t('sections.admin_dashboard.stats.cards.users.metrics.doctors')}</li>
                                    <li>{t('sections.admin_dashboard.stats.cards.users.metrics.secretaries')}</li>
                                </ul>
                            </Card>
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <h4 className="font-semibold text-green-600 dark:text-green-400 mb-2">{t('sections.admin_dashboard.stats.cards.patients.title')}</h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {t('sections.admin_dashboard.stats.cards.patients.description')}
                                </p>
                            </Card>
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <h4 className="font-semibold text-cyan-600 dark:text-cyan-400 mb-2">{t('sections.admin_dashboard.stats.cards.appointments.title')}</h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {t('sections.admin_dashboard.stats.cards.appointments.description')}
                                </p>
                                <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                    <li>{t('sections.admin_dashboard.stats.cards.appointments.metrics.total')}</li>
                                    <li>{t('sections.admin_dashboard.stats.cards.appointments.metrics.completed')}</li>
                                    <li>{t('sections.admin_dashboard.stats.cards.appointments.metrics.cancelled')}</li>
                                </ul>
                            </Card>
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <h4 className="font-semibold text-yellow-600 dark:text-yellow-400 mb-2">{t('sections.admin_dashboard.stats.cards.financial.title')}</h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {t('sections.admin_dashboard.stats.cards.financial.description')}
                                </p>
                                <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                    <li>{t('sections.admin_dashboard.stats.cards.financial.metrics.revenue')}</li>
                                    <li>{t('sections.admin_dashboard.stats.cards.financial.metrics.transactions')}</li>
                                    <li>{t('sections.admin_dashboard.stats.cards.financial.metrics.discounts')}</li>
                                </ul>
                            </Card>
                        </div>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.admin_dashboard.stats.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.admin_dashboard.stats.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.admin_dashboard.stats.method')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.admin_dashboard.stats.time_range')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/dashboard/admin/001-opt.png"
                            alt="System Statistics"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.admin_dashboard.recent_activities.title')} icon={<History />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/dashboard/admin/002-opt.png"
                            alt="Recent Activities"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.admin_dashboard.recent_activities.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.admin_dashboard.recent_activities.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.admin_dashboard.recent_activities.features.timeline')}</li>
                            <li>{t('sections.admin_dashboard.recent_activities.features.user_info')}</li>
                            <li>{t('sections.admin_dashboard.recent_activities.features.action_types')}</li>
                            <li>{t('sections.admin_dashboard.recent_activities.features.timestamp')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.admin_dashboard.recent_activities.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.admin_dashboard.recent_activities.query')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.admin_dashboard.recent_activities.limit')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.admin_dashboard.recent_activities.data_source')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.admin_dashboard.system_health.title')} icon={<Heart />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.admin_dashboard.system_health.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.admin_dashboard.system_health.monitors_title')}</h3>
                        <div className="space-y-4 mb-6">
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <div className="flex items-center gap-2 mb-2">
                                    <Database className="text-blue-600 dark:text-blue-400" size={20} />
                                    <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.admin_dashboard.system_health.monitors.database.title')}</h4>
                                </div>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    {t('sections.admin_dashboard.system_health.monitors.database.description')}
                                </p>
                            </Card>
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <div className="flex items-center gap-2 mb-2">
                                    <HardDrive className="text-green-600 dark:text-green-400" size={20} />
                                    <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.admin_dashboard.system_health.monitors.storage.title')}</h4>
                                </div>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    {t('sections.admin_dashboard.system_health.monitors.storage.description')}
                                </p>
                            </Card>
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <div className="flex items-center gap-2 mb-2">
                                    <Code className="text-purple-600 dark:text-purple-400" size={20} />
                                    <h4 className="font-semibold text-purple-600 dark:text-purple-400">{t('sections.admin_dashboard.system_health.monitors.php.title')}</h4>
                                </div>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    {t('sections.admin_dashboard.system_health.monitors.php.description')}
                                </p>
                            </Card>
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <div className="flex items-center gap-2 mb-2">
                                    <Shield className="text-orange-600 dark:text-orange-400" size={20} />
                                    <h4 className="font-semibold text-orange-600 dark:text-orange-400">{t('sections.admin_dashboard.system_health.monitors.extensions.title')}</h4>
                                </div>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    {t('sections.admin_dashboard.system_health.monitors.extensions.description')}
                                </p>
                            </Card>
                        </div>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.admin_dashboard.system_health.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.admin_dashboard.system_health.method')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.admin_dashboard.system_health.checks')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.admin_dashboard.system_health.storage_calc')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/dashboard/admin/003-opt.png"
                            alt="System Health"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.admin_dashboard.view_as.title')} icon={<Eye />}>
                <div className="space-y-6">
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                        <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/dashboard/admin/004-opt.png"
                                alt="View As Controls"
                                className="w-full h-auto hover:scale-105 transition-transform duration-500"
                            />
                        </div>
                        <div className="order-1 lg:order-2">
                            <p className="text-gray-700 dark:text-gray-300 mb-4">
                                {t('sections.admin_dashboard.view_as.description')}
                            </p>
                            <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.admin_dashboard.view_as.features_title')}</h3>
                            <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                                <li>{t('sections.admin_dashboard.view_as.features.preview')}</li>
                                <li>{t('sections.admin_dashboard.view_as.features.roles')}</li>
                                <li>{t('sections.admin_dashboard.view_as.features.switch')}</li>
                                <li>{t('sections.admin_dashboard.view_as.features.return')}</li>
                            </ul>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                                <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.admin_dashboard.view_as.technical')}</h4>
                                <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.admin_dashboard.view_as.routes')}</code>
                                <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.admin_dashboard.view_as.methods')}</code>
                                <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.admin_dashboard.view_as.auth')}</code>
                            </div>
                        </div>
                    </div>

                    <div className="mt-8">
                        <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">{t('sections.admin_dashboard.view_as.doctor_mode.title')}</h3>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.admin_dashboard.view_as.doctor_mode.description')}
                        </p>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                                <img
                                    src="/docs/opth/assets/images/dashboard/admin/005-v2-opt.png"
                                    alt="View As Doctor - View 1"
                                    className="w-full h-auto hover:scale-105 transition-transform duration-500"
                                />
                            </div>
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                                <img
                                    src="/docs/opth/assets/images/dashboard/admin/006-opt.png"
                                    alt="View As Doctor - View 2"
                                    className="w-full h-auto hover:scale-105 transition-transform duration-500"
                                />
                            </div>
                        </div>
                    </div>

                    <div className="mt-8">
                        <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">{t('sections.admin_dashboard.view_as.secretary_mode.title')}</h3>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.admin_dashboard.view_as.secretary_mode.description')}
                        </p>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                                <img
                                    src="/docs/opth/assets/images/dashboard/admin/009-opt.png"
                                    alt="View As Secretary - View 1"
                                    className="w-full h-auto hover:scale-105 transition-transform duration-500"
                                />
                            </div>
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                                <img
                                    src="/docs/opth/assets/images/dashboard/admin/008-opt.png"
                                    alt="View As Secretary - View 2"
                                    className="w-full h-auto hover:scale-105 transition-transform duration-500"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </Section>
        </div>
    );
}

