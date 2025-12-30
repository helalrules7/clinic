import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Activity, CloudSun, Zap, Calendar, CreditCard, TrendingUp, RefreshCw } from 'lucide-react';

export default function SecretaryDashboardDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.secretary_dashboard.title')}
                subtitle={t('sections.secretary_dashboard.subtitle')}
            />

            <Section title={t('sections.secretary_dashboard.overview.title')} icon={<Activity />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.secretary_dashboard.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.secretary_dashboard.stats.title')} icon={<TrendingUp />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_dashboard.stats.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_dashboard.stats.key_metrics')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_dashboard.stats.metrics.total_appointments')}:</strong> {t('sections.secretary_dashboard.stats.metrics.total_appointments_desc')}</li>
                            <li><strong>{t('sections.secretary_dashboard.stats.metrics.booked')}:</strong> {t('sections.secretary_dashboard.stats.metrics.booked_desc')}</li>
                            <li><strong>{t('sections.secretary_dashboard.stats.metrics.checked_in')}:</strong> {t('sections.secretary_dashboard.stats.metrics.checked_in_desc')}</li>
                            <li><strong>{t('sections.secretary_dashboard.stats.metrics.completed')}:</strong> {t('sections.secretary_dashboard.stats.metrics.completed_desc')}</li>
                            <li><strong>{t('sections.secretary_dashboard.stats.metrics.missed')}:</strong> {t('sections.secretary_dashboard.stats.metrics.missed_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_dashboard.stats.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.stats.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.stats.api_endpoint')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.stats.method')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/dashboard/sec/01-opt.png"
                            alt="Statistics Cards"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_dashboard.weather.title')} icon={<CloudSun />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_dashboard.weather.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_dashboard.weather.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.secretary_dashboard.weather.features.location')}</li>
                            <li>{t('sections.secretary_dashboard.weather.features.temperature')}</li>
                            <li>{t('sections.secretary_dashboard.weather.features.forecast')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_dashboard.weather.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.weather.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.weather.js_function')}</code>
                        </div>
                    </div>
                    <div className="order-2 lg:order-1 space-y-4">
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/dashboard/sec/03-opt.png"
                                alt="Weather Widget"
                                className="w-full h-auto hover:scale-105 transition-transform duration-500"
                            />
                        </div>
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/dashboard/sec/02-opt.png"
                                alt="Weather Forecast"
                                className="w-full h-auto hover:scale-105 transition-transform duration-500"
                            />
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_dashboard.quick_actions.title')} icon={<Zap />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/dashboard/sec/04-opt.png"
                            alt="Quick Actions"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_dashboard.quick_actions.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_dashboard.quick_actions.actions_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_dashboard.quick_actions.actions.bookings')}:</strong> {t('sections.secretary_dashboard.quick_actions.actions.bookings_desc')}</li>
                            <li><strong>{t('sections.secretary_dashboard.quick_actions.actions.patients')}:</strong> {t('sections.secretary_dashboard.quick_actions.actions.patients_desc')}</li>
                            <li><strong>{t('sections.secretary_dashboard.quick_actions.actions.payments')}:</strong> {t('sections.secretary_dashboard.quick_actions.actions.payments_desc')}</li>
                            <li><strong>{t('sections.secretary_dashboard.quick_actions.actions.expenses')}:</strong> {t('sections.secretary_dashboard.quick_actions.actions.expenses_desc')}</li>
                            <li><strong>{t('sections.secretary_dashboard.quick_actions.actions.profile')}:</strong> {t('sections.secretary_dashboard.quick_actions.actions.profile_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_dashboard.quick_actions.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.quick_actions.navigation')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.quick_actions.scroll')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.quick_actions.js_functions')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_dashboard.today_appointments.title')} icon={<Calendar />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/dashboard/sec/05-opt.png"
                            alt="Today's Appointments"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_dashboard.today_appointments.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_dashboard.today_appointments.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.secretary_dashboard.today_appointments.features.table')}</li>
                            <li>{t('sections.secretary_dashboard.today_appointments.features.patient_info')}</li>
                            <li>{t('sections.secretary_dashboard.today_appointments.features.status')}</li>
                            <li>{t('sections.secretary_dashboard.today_appointments.features.actions')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_dashboard.today_appointments.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.today_appointments.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.today_appointments.api_endpoint')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.today_appointments.js_function')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_dashboard.recent_payments.title')} icon={<CreditCard />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_dashboard.recent_payments.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_dashboard.recent_payments.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.secretary_dashboard.recent_payments.features.list')}</li>
                            <li>{t('sections.secretary_dashboard.recent_payments.features.patient_info')}</li>
                            <li>{t('sections.secretary_dashboard.recent_payments.features.amount')}</li>
                            <li>{t('sections.secretary_dashboard.recent_payments.features.timestamp')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_dashboard.recent_payments.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.recent_payments.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.recent_payments.api_endpoint')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.recent_payments.method')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/dashboard/sec/06-opt.png"
                            alt="Recent Payments"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_dashboard.auto_refresh.title')} icon={<RefreshCw />}>
                <div className="flex flex-col gap-6">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.secretary_dashboard.auto_refresh.description')}
                    </p>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <h4 className="font-semibold text-blue-600 dark:text-blue-400 mb-2">{t('sections.secretary_dashboard.auto_refresh.features.interval.title')}</h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.secretary_dashboard.auto_refresh.features.interval.description')}
                            </p>
                        </Card>
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <h4 className="font-semibold text-purple-600 dark:text-purple-400 mb-2">{t('sections.secretary_dashboard.auto_refresh.features.visibility.title')}</h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.secretary_dashboard.auto_refresh.features.visibility.description')}
                            </p>
                        </Card>
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <h4 className="font-semibold text-green-600 dark:text-green-400 mb-2">{t('sections.secretary_dashboard.auto_refresh.features.updates.title')}</h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.secretary_dashboard.auto_refresh.features.updates.description')}
                            </p>
                        </Card>
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <h4 className="font-semibold text-orange-600 dark:text-orange-400 mb-2">{t('sections.secretary_dashboard.auto_refresh.features.error_handling.title')}</h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.secretary_dashboard.auto_refresh.features.error_handling.description')}
                            </p>
                        </Card>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_dashboard.auto_refresh.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.auto_refresh.js_functions')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.auto_refresh.api_endpoint')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.secretary_dashboard.auto_refresh.refresh_interval')}</code>
                    </div>
                </div>
            </Section>
        </div>
    );
}

