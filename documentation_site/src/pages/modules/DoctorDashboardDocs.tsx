import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Activity, CloudSun, Zap, Newspaper, Calendar, Clock, StickyNote, BarChart3, Bell, AlertTriangle, Move, Settings, ClipboardList } from 'lucide-react';

export default function DoctorDashboardDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.doctor_dashboard.title')}
                subtitle={t('sections.doctor_dashboard.subtitle')}
            />

            <Section title={t('sections.doctor_dashboard.overview.title')} icon={<Activity />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.doctor_dashboard.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.doctor_dashboard.stats.title')} icon={<Activity />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.doctor_dashboard.stats.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.doctor_dashboard.stats.key_metrics')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.doctor_dashboard.stats.metrics.total_appointments')}:</strong> {t('sections.doctor_dashboard.stats.metrics.total_appointments_desc')}</li>
                            <li><strong>{t('sections.doctor_dashboard.stats.metrics.completed_appointments')}:</strong> {t('sections.doctor_dashboard.stats.metrics.completed_appointments_desc')}</li>
                            <li><strong>{t('sections.doctor_dashboard.stats.metrics.missed_appointments')}:</strong> {t('sections.doctor_dashboard.stats.metrics.missed_appointments_desc')}</li>
                            <li><strong>{t('sections.doctor_dashboard.stats.metrics.new_patients')}:</strong> {t('sections.doctor_dashboard.stats.metrics.new_patients_desc')}</li>
                            <li><strong>{t('sections.doctor_dashboard.stats.metrics.prescriptions')}:</strong> {t('sections.doctor_dashboard.stats.metrics.prescriptions_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.doctor_dashboard.stats.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.doctor_dashboard.stats.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono">{t('sections.doctor_dashboard.stats.dataset')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/dashboard/stats-cards-opt.png"
                            alt="Statistics Cards"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.doctor_dashboard.weather.title')} icon={<CloudSun />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <div className="carousel relative group">
                            <div className="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide py-2">
                                <img
                                    src="/docs/opth/assets/images/dashboard/weather-widget-opt.png"
                                    alt="Weather Widget"
                                    className="w-full flex-shrink-0 snap-center rounded-lg mr-4"
                                />
                                <img
                                    src="/docs/opth/assets/images/dashboard/weather-forecast-opt.png"
                                    alt="Weather Forecast"
                                    className="w-full flex-shrink-0 snap-center rounded-lg"
                                />
                            </div>
                        </div>
                        <p className="text-xs text-gray-500 text-center mt-2">{t('sections.doctor_dashboard.weather.swipe_hint')}</p>
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.doctor_dashboard.weather.description')}
                        </p>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <Card title={t('sections.doctor_dashboard.weather.pollen.title')} className="bg-gray-100 dark:bg-slate-800/50">
                                <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.doctor_dashboard.weather.pollen.desc')}</p>
                            </Card>
                            <Card title={t('sections.doctor_dashboard.weather.dry_eye.title')} className="bg-gray-100 dark:bg-slate-800/50">
                                <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.doctor_dashboard.weather.dry_eye.desc')}</p>
                            </Card>
                        </div>

                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.doctor_dashboard.weather.api_endpoints')}</h4>
                            <div className="space-y-2">
                                <div className="flex items-center gap-2">
                                    <span className="px-2 py-0.5 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                                    <code className="text-sm text-gray-700 dark:text-gray-300">/api/weather</code>
                                </div>
                                <div className="flex items-center gap-2">
                                    <span className="px-2 py-0.5 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                                    <code className="text-sm text-gray-700 dark:text-gray-300">/api/weather-forecast</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.doctor_dashboard.news.title')} icon={<Newspaper />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.doctor_dashboard.news.description')}
                        </p>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.doctor_dashboard.news.implementation')}</h4>
                            <code className="block text-sm text-yellow-600 dark:text-yellow-400 font-mono mb-1">{t('sections.doctor_dashboard.news.id')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono">{t('sections.doctor_dashboard.news.source')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50  dark:bg-black/40 p-2">
                        <img
                            src="/docs/opth/assets/images/dashboard/news-widget-opt.png"
                            alt="News Widget"
                            className="w-full h-auto rounded-md"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.doctor_dashboard.quick_actions.title')} icon={<Zap />}>
                <div className="flex flex-col gap-6">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.doctor_dashboard.quick_actions.description')}
                    </p>

                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/dashboard/quick-actions-opt.png"
                            alt="Quick Actions Dock"
                            className="w-full h-auto"
                        />
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div className="p-4 rounded-lg bg-gray-100 dark:bg-slate-800/30 border border-gray-200 dark:border-white/5">
                            <h4 className="font-semibold text-blue-600 dark:text-blue-400 mb-2">{t('sections.doctor_dashboard.quick_actions.nav_title')}</h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.doctor_dashboard.quick_actions.nav_desc')}</p>
                        </div>
                        <div className="p-4 rounded-lg bg-gray-100 dark:bg-slate-800/30 border border-gray-200 dark:border-white/5">
                            <h4 className="font-semibold text-purple-600 dark:text-purple-400 mb-2">{t('sections.doctor_dashboard.quick_actions.sub_title')}</h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.doctor_dashboard.quick_actions.sub_desc')}</p>
                        </div>
                        <div className="p-4 rounded-lg bg-gray-100 dark:bg-slate-800/30 border border-gray-200 dark:border-white/5">
                            <h4 className="font-semibold text-green-600 dark:text-green-400 mb-2">{t('sections.doctor_dashboard.quick_actions.handlers_title')}</h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.doctor_dashboard.quick_actions.handlers_desc')}</p>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.doctor_dashboard.upcoming_appointments.title')} icon={<Calendar />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.doctor_dashboard.upcoming_appointments.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.doctor_dashboard.upcoming_appointments.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.doctor_dashboard.upcoming_appointments.features.real_time')}</li>
                            <li>{t('sections.doctor_dashboard.upcoming_appointments.features.progress_bar')}</li>
                            <li>{t('sections.doctor_dashboard.upcoming_appointments.features.pagination')}</li>
                            <li>{t('sections.doctor_dashboard.upcoming_appointments.features.quick_actions')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.doctor_dashboard.upcoming_appointments.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.doctor_dashboard.upcoming_appointments.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.doctor_dashboard.upcoming_appointments.js_function')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.doctor_dashboard.upcoming_appointments.controller_method')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/dashboard/upcoming-appointments-opt.png"
                            alt="Upcoming Appointments"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.doctor_dashboard.recent_activities.title')} icon={<Clock />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/dashboard/recent-activities-opt.png"
                            alt="Recent Activities"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.doctor_dashboard.recent_activities.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.doctor_dashboard.recent_activities.event_types_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.doctor_dashboard.recent_activities.event_types.appointment_booked')}</li>
                            <li>{t('sections.doctor_dashboard.recent_activities.event_types.status_changed')}</li>
                            <li>{t('sections.doctor_dashboard.recent_activities.event_types.file_uploaded')}</li>
                            <li>{t('sections.doctor_dashboard.recent_activities.event_types.prescription_created')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.doctor_dashboard.recent_activities.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.doctor_dashboard.recent_activities.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.doctor_dashboard.recent_activities.js_function')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.doctor_dashboard.recent_activities.modal')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.doctor_dashboard.notes_board.title')} icon={<StickyNote />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.doctor_dashboard.notes_board.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.doctor_dashboard.notes_board.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.doctor_dashboard.notes_board.features.drag_drop')}</li>
                            <li>{t('sections.doctor_dashboard.notes_board.features.resize')}</li>
                            <li>{t('sections.doctor_dashboard.notes_board.features.color_customization')}</li>
                            <li>{t('sections.doctor_dashboard.notes_board.features.autocomplete')}</li>
                            <li>{t('sections.doctor_dashboard.notes_board.features.alerts')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.doctor_dashboard.notes_board.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.doctor_dashboard.notes_board.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.doctor_dashboard.notes_board.js_functions')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.doctor_dashboard.notes_board.autocomplete_triggers')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/dashboard/notes-board-opt.png"
                            alt="Notes Board"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.doctor_dashboard.visual_analytics.title')} icon={<BarChart3 />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/dashboard/visual-analytics-opt.png"
                            alt="Visual Analytics"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.doctor_dashboard.visual_analytics.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.doctor_dashboard.visual_analytics.charts_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.doctor_dashboard.visual_analytics.charts.appointments_trend')}</li>
                            <li>{t('sections.doctor_dashboard.visual_analytics.charts.new_patients_trend')}</li>
                            <li>{t('sections.doctor_dashboard.visual_analytics.charts.time_range')}</li>
                            <li>{t('sections.doctor_dashboard.visual_analytics.charts.theme_support')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.doctor_dashboard.visual_analytics.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.doctor_dashboard.visual_analytics.library')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.doctor_dashboard.visual_analytics.api_endpoint')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.doctor_dashboard.visual_analytics.js_function')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.doctor_dashboard.today_alerts.title')} icon={<Bell />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.doctor_dashboard.today_alerts.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.doctor_dashboard.today_alerts.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.doctor_dashboard.today_alerts.features.patient_linked')}</li>
                            <li>{t('sections.doctor_dashboard.today_alerts.features.time_based')}</li>
                            <li>{t('sections.doctor_dashboard.today_alerts.features.quick_access')}</li>
                            <li>{t('sections.doctor_dashboard.today_alerts.features.management')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.doctor_dashboard.today_alerts.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.doctor_dashboard.today_alerts.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.doctor_dashboard.today_alerts.js_function')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.doctor_dashboard.today_alerts.model')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/dashboard/today-alerts-opt.png"
                            alt="Today's Alerts"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.doctor_dashboard.missed_appointments.title')} icon={<AlertTriangle />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/dashboard/missed-appointments-opt.png"
                            alt="Missed Appointments"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.doctor_dashboard.missed_appointments.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.doctor_dashboard.missed_appointments.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.doctor_dashboard.missed_appointments.features.pagination')}</li>
                            <li>{t('sections.doctor_dashboard.missed_appointments.features.status_tags')}</li>
                            <li>{t('sections.doctor_dashboard.missed_appointments.features.quick_actions')}</li>
                            <li>{t('sections.doctor_dashboard.missed_appointments.features.filtering')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.doctor_dashboard.missed_appointments.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.doctor_dashboard.missed_appointments.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.doctor_dashboard.missed_appointments.js_function')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.doctor_dashboard.missed_appointments.query_logic')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.doctor_dashboard.widget_rearrangement.title')} icon={<Move />}>
                <div className="flex flex-col gap-6">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.doctor_dashboard.widget_rearrangement.description')}
                    </p>

                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                        <div>
                            <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">{t('sections.doctor_dashboard.widget_rearrangement.methods_title')}</h3>

                            <div className="space-y-4 mb-6">
                                <Card className="bg-gray-100 dark:bg-slate-800/30">
                                    <h4 className="font-semibold text-blue-600 dark:text-blue-400 mb-2">{t('sections.doctor_dashboard.widget_rearrangement.drag_drop.title')}</h4>
                                    <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        {t('sections.doctor_dashboard.widget_rearrangement.drag_drop.description')}
                                    </p>
                                    <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                        <li>{t('sections.doctor_dashboard.widget_rearrangement.drag_drop.steps.grab')}</li>
                                        <li>{t('sections.doctor_dashboard.widget_rearrangement.drag_drop.steps.drag')}</li>
                                        <li>{t('sections.doctor_dashboard.widget_rearrangement.drag_drop.steps.drop')}</li>
                                    </ul>
                                </Card>

                                <Card className="bg-gray-100 dark:bg-slate-800/30">
                                    <h4 className="font-semibold text-purple-600 dark:text-purple-400 mb-2">{t('sections.doctor_dashboard.widget_rearrangement.buttons.title')}</h4>
                                    <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        {t('sections.doctor_dashboard.widget_rearrangement.buttons.description')}
                                    </p>
                                    <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                        <li>{t('sections.doctor_dashboard.widget_rearrangement.buttons.steps.up')}</li>
                                        <li>{t('sections.doctor_dashboard.widget_rearrangement.buttons.steps.down')}</li>
                                        <li>{t('sections.doctor_dashboard.widget_rearrangement.buttons.steps.auto_hide')}</li>
                                    </ul>
                                </Card>
                            </div>

                            <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                                <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.doctor_dashboard.widget_rearrangement.technical')}</h4>
                                <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.doctor_dashboard.widget_rearrangement.js_functions')}</code>
                                <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.doctor_dashboard.widget_rearrangement.api_endpoint')}</code>
                                <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.doctor_dashboard.widget_rearrangement.storage')}</code>
                            </div>
                        </div>

                        <div className="space-y-4">
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                                <img
                                    src="/docs/opth/assets/images/dashboard/dashboard_rearrange_widgets-opt.png"
                                    alt="Dashboard Widget Rearrangement"
                                    className="w-full h-auto hover:scale-105 transition-transform duration-500"
                                />
                            </div>

                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                                <video
                                    src="/docs/opth/assets/videos/dashboard/moving_widgets.mp4"
                                    className="w-full h-auto"
                                    controls
                                    autoPlay
                                    muted
                                    loop
                                    playsInline
                                >
                                    {t('sections.doctor_dashboard.widget_rearrangement.video_fallback')}
                                </video>
                            </div>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.doctor_dashboard.mobile_settings.title')} icon={<Settings />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.doctor_dashboard.mobile_settings.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.doctor_dashboard.mobile_settings.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.doctor_dashboard.mobile_settings.features.toggle')}</li>
                            <li>{t('sections.doctor_dashboard.mobile_settings.features.mobile_only')}</li>
                            <li>{t('sections.doctor_dashboard.mobile_settings.features.persistent')}</li>
                            <li>{t('sections.doctor_dashboard.mobile_settings.features.automatic')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.doctor_dashboard.mobile_settings.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.doctor_dashboard.mobile_settings.setting_key')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.doctor_dashboard.mobile_settings.js_function')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.doctor_dashboard.mobile_settings.settings_page')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40 p-4">
                        <div className="bg-white dark:bg-gray-800 rounded-lg p-6">
                            <h4 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">{t('sections.doctor_dashboard.mobile_settings.settings_preview_title')}</h4>
                            <div className="space-y-3">
                                <div className="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div>
                                        <p className="font-medium text-gray-900 dark:text-white">{t('sections.doctor_dashboard.mobile_settings.setting_label')}</p>
                                        <p className="text-sm text-gray-500 dark:text-gray-400">{t('sections.doctor_dashboard.mobile_settings.setting_description')}</p>
                                    </div>
                                    <div className="relative inline-block w-12 h-6">
                                        <input type="checkbox" className="toggle-switch" defaultChecked />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.doctor_dashboard.unified_clinical_dashboard.title')} icon={<ClipboardList />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/dashboard/UnifiedClinicalDashboard-opt.png"
                            alt="Unified Clinical Dashboard"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.doctor_dashboard.unified_clinical_dashboard.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.doctor_dashboard.unified_clinical_dashboard.components_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.doctor_dashboard.unified_clinical_dashboard.components.patient_notice')}:</strong> {t('sections.doctor_dashboard.unified_clinical_dashboard.components.patient_notice_desc')}</li>
                            <li><strong>{t('sections.doctor_dashboard.unified_clinical_dashboard.components.iop_status')}:</strong> {t('sections.doctor_dashboard.unified_clinical_dashboard.components.iop_status_desc')}</li>
                            <li><strong>{t('sections.doctor_dashboard.unified_clinical_dashboard.components.visual_acuity')}:</strong> {t('sections.doctor_dashboard.unified_clinical_dashboard.components.visual_acuity_desc')}</li>
                            <li><strong>{t('sections.doctor_dashboard.unified_clinical_dashboard.components.cataract')}:</strong> {t('sections.doctor_dashboard.unified_clinical_dashboard.components.cataract_desc')}</li>
                            <li><strong>{t('sections.doctor_dashboard.unified_clinical_dashboard.components.dry_eye')}:</strong> {t('sections.doctor_dashboard.unified_clinical_dashboard.components.dry_eye_desc')}</li>
                            <li><strong>{t('sections.doctor_dashboard.unified_clinical_dashboard.components.alerts')}:</strong> {t('sections.doctor_dashboard.unified_clinical_dashboard.components.alerts_desc')}</li>
                            <li><strong>{t('sections.doctor_dashboard.unified_clinical_dashboard.components.mini_trends')}:</strong> {t('sections.doctor_dashboard.unified_clinical_dashboard.components.mini_trends_desc')}</li>
                            <li><strong>{t('sections.doctor_dashboard.unified_clinical_dashboard.components.summary')}:</strong> {t('sections.doctor_dashboard.unified_clinical_dashboard.components.summary_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.doctor_dashboard.unified_clinical_dashboard.integration_title')}</h3>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.doctor_dashboard.unified_clinical_dashboard.integration_description')}
                        </p>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.doctor_dashboard.unified_clinical_dashboard.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.doctor_dashboard.unified_clinical_dashboard.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.doctor_dashboard.unified_clinical_dashboard.service')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1">{t('sections.doctor_dashboard.unified_clinical_dashboard.js_function')}</code>
                            <code className="block text-sm text-yellow-600 dark:text-yellow-400 font-mono mb-1">{t('sections.doctor_dashboard.unified_clinical_dashboard.storage')}</code>
                            <code className="block text-sm text-pink-600 dark:text-pink-400 font-mono">{t('sections.doctor_dashboard.unified_clinical_dashboard.routing')}</code>
                        </div>
                    </div>
                </div>
            </Section>
        </div>
    );
}
