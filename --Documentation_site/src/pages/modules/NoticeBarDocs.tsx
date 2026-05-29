import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Menu, CloudSun, Clock, Calendar, Calculator, Eye, Droplet, TrendingUp, Target, CheckCircle, Activity, AlertTriangle, Stethoscope, BarChart3, Scissors } from 'lucide-react';

export default function NoticeBarDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.notice_bar.title')}
                subtitle={t('sections.notice_bar.subtitle')}
            />

            <Section title={t('sections.notice_bar.overview.title')} icon={<Menu />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notice_bar.overview.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notice_bar.overview.components_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.notice_bar.overview.components.weather')}</li>
                            <li>{t('sections.notice_bar.overview.components.datetime')}</li>
                            <li>{t('sections.notice_bar.overview.components.appointments')}</li>
                            <li>{t('sections.notice_bar.overview.components.tools')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notice_bar.overview.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.overview.css_class')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notice_bar.overview.js_file')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.notice_bar.overview.php_file')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/notice-bar/00-opt.png"
                            alt="Notice Bar Overview"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notice_bar.weather.title')} icon={<CloudSun />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <div className="space-y-4">
                            <img
                                src="/docs/opth/assets/images/doctors_pages/notice-bar/ezgif-48409ddeb5cd0a41.gif"
                                alt="Weather Widget Animation"
                                className="w-full h-auto"
                            />
                            <img
                                src="/docs/opth/assets/images/doctors_pages/notice-bar/01-opt.png"
                                alt="Weather Popover"
                                className="w-full h-auto"
                            />
                            <img
                                src="/docs/opth/assets/images/doctors_pages/notice-bar/02-opt.png"
                                alt="Weather Forecast"
                                className="w-full h-auto"
                            />
                        </div>
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notice_bar.weather.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notice_bar.weather.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.notice_bar.weather.features.temperature')}</li>
                            <li>{t('sections.notice_bar.weather.features.icon')}</li>
                            <li>{t('sections.notice_bar.weather.features.pollen')}</li>
                            <li>{t('sections.notice_bar.weather.features.dry_eye')}</li>
                            <li>{t('sections.notice_bar.weather.features.forecast')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notice_bar.weather.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.weather.api_current')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notice_bar.weather.api_forecast')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1">{t('sections.notice_bar.weather.js_functions')}</code>
                            <code className="block text-sm text-yellow-600 dark:text-yellow-400 font-mono">{t('sections.notice_bar.weather.storage')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notice_bar.datetime.title')} icon={<Clock />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notice_bar.datetime.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notice_bar.datetime.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.notice_bar.datetime.features.real_time')}</li>
                            <li>{t('sections.notice_bar.datetime.features.calendar')}</li>
                            <li>{t('sections.notice_bar.datetime.features.clock')}</li>
                            <li>{t('sections.notice_bar.datetime.features.interactive')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notice_bar.datetime.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.datetime.js_function')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notice_bar.datetime.update_interval')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.notice_bar.datetime.popover_id')}</code>
                        </div>
                    </div>
                    <div className="space-y-4">
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/doctors_pages/notice-bar/03.gif"
                                alt="Clock Widget Animation"
                                className="w-full h-auto"
                            />
                        </div>
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/doctors_pages/notice-bar/03-opt.png"
                                alt="Clock Calendar Popover"
                                className="w-full h-auto"
                            />
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notice_bar.appointments.title')} icon={<Calendar />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 space-y-4">
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/doctors_pages/notice-bar/04.gif"
                                alt="Appointments Scroll Animation"
                                className="w-full h-auto"
                            />
                        </div>
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/doctors_pages/notice-bar/04-opt.png"
                                alt="Appointments Popover"
                                className="w-full h-auto"
                            />
                        </div>
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notice_bar.appointments.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notice_bar.appointments.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.notice_bar.appointments.features.auto_scroll')}</li>
                            <li>{t('sections.notice_bar.appointments.features.real_time')}</li>
                            <li>{t('sections.notice_bar.appointments.features.next_day')}</li>
                            <li>{t('sections.notice_bar.appointments.features.popover')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notice_bar.appointments.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.appointments.js_function')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notice_bar.appointments.api_endpoint')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.notice_bar.appointments.animation')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notice_bar.ophthalmology_tools.title')} icon={<Calculator />}>
                <div className="mb-6">
                    <p className="text-gray-700 dark:text-gray-300 mb-4">
                        {t('sections.notice_bar.ophthalmology_tools.description')}
                    </p>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40 mb-6">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/notice-bar/calcs/00-opt-opt-opt-opt-opt-opt-opt-opt-opt-opt-opt-opt-opt.jpg"
                            alt="Ophthalmology Tools Overview"
                            className="w-full h-auto"
                        />
                    </div>
                </div>

                <div className="space-y-8">
                    {/* IOL Power Calculator */}
                    <Card className="bg-gray-100 dark:bg-slate-800/30">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            <div>
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400 mb-2 flex items-center gap-2">
                                    <Eye className="w-5 h-5" />
                                    {t('sections.notice_bar.ophthalmology_tools.iol.title')}
                                </h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {t('sections.notice_bar.ophthalmology_tools.iol.description')}
                                </p>
                                <div className="bg-gray-50 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 text-xs">
                                    <code className="block text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.iol.api')}</code>
                                    <code className="block text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.iol.service')}</code>
                                    <code className="block text-purple-600 dark:text-purple-400 font-mono">{t('sections.notice_bar.ophthalmology_tools.iol.formulas')}</code>
                                </div>
                            </div>
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg">
                                <img
                                    src="/docs/opth/assets/images/doctors_pages/notice-bar/calcs/01-opt.png"
                                    alt="IOL Power Calculator"
                                    className="w-full h-auto"
                                />
                            </div>
                        </div>
                    </Card>

                    {/* Pediatric IOL */}
                    <Card className="bg-gray-100 dark:bg-slate-800/30">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            <div>
                                <h4 className="font-semibold text-purple-600 dark:text-purple-400 mb-2 flex items-center gap-2">
                                    <Eye className="w-5 h-5" />
                                    {t('sections.notice_bar.ophthalmology_tools.pediatric_iol.title')}
                                </h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {t('sections.notice_bar.ophthalmology_tools.pediatric_iol.description')}
                                </p>
                                <div className="bg-gray-50 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 text-xs">
                                    <code className="block text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.pediatric_iol.api')}</code>
                                    <code className="block text-blue-600 dark:text-blue-400 font-mono">{t('sections.notice_bar.ophthalmology_tools.pediatric_iol.service')}</code>
                                </div>
                            </div>
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg">
                                <img
                                    src="/docs/opth/assets/images/doctors_pages/notice-bar/calcs/02-opt.png"
                                    alt="Pediatric IOL Calculator"
                                    className="w-full h-auto"
                                />
                            </div>
                        </div>
                    </Card>

                    {/* Corneal Astigmatism */}
                    <Card className="bg-gray-100 dark:bg-slate-800/30">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            <div>
                                <h4 className="font-semibold text-indigo-600 dark:text-indigo-400 mb-2 flex items-center gap-2">
                                    <Eye className="w-5 h-5" />
                                    {t('sections.notice_bar.ophthalmology_tools.corneal_astigmatism.title')}
                                </h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {t('sections.notice_bar.ophthalmology_tools.corneal_astigmatism.description')}
                                </p>
                                <div className="bg-gray-50 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 text-xs">
                                    <code className="block text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.corneal_astigmatism.api')}</code>
                                    <code className="block text-blue-600 dark:text-blue-400 font-mono">{t('sections.notice_bar.ophthalmology_tools.corneal_astigmatism.service')}</code>
                                </div>
                            </div>
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg">
                                <img
                                    src="/docs/opth/assets/images/doctors_pages/notice-bar/calcs/03-opt.png"
                                    alt="Corneal Astigmatism Calculator"
                                    className="w-full h-auto"
                                />
                            </div>
                        </div>
                    </Card>

                    {/* IOP Trend Analyzer */}
                    <Card className="bg-gray-100 dark:bg-slate-800/30">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            <div>
                                <h4 className="font-semibold text-red-600 dark:text-red-400 mb-2 flex items-center gap-2">
                                    <TrendingUp className="w-5 h-5" />
                                    {t('sections.notice_bar.ophthalmology_tools.iop_trend.title')}
                                </h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {t('sections.notice_bar.ophthalmology_tools.iop_trend.description')}
                                </p>
                                <div className="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg border border-yellow-200 dark:border-yellow-800 mb-4">
                                    <p className="text-xs text-yellow-800 dark:text-yellow-200">
                                        <strong>{t('sections.notice_bar.ophthalmology_tools.iop_trend.auto_detect')}</strong>
                                    </p>
                                </div>
                                <div className="bg-gray-50 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 text-xs">
                                    <code className="block text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.iop_trend.api')}</code>
                                    <code className="block text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.iop_trend.service')}</code>
                                    <code className="block text-purple-600 dark:text-purple-400 font-mono">{t('sections.notice_bar.ophthalmology_tools.iop_trend.interface')}</code>
                                </div>
                            </div>
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg">
                                <img
                                    src="/docs/opth/assets/images/doctors_pages/notice-bar/calcs/04-IOPTrendAnalyzer-opt.png"
                                    alt="IOP Trend Analyzer"
                                    className="w-full h-auto"
                                />
                            </div>
                        </div>
                    </Card>

                    {/* Target IOP */}
                    <Card className="bg-gray-100 dark:bg-slate-800/30">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            <div>
                                <h4 className="font-semibold text-orange-600 dark:text-orange-400 mb-2 flex items-center gap-2">
                                    <Target className="w-5 h-5" />
                                    {t('sections.notice_bar.ophthalmology_tools.target_iop.title')}
                                </h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {t('sections.notice_bar.ophthalmology_tools.target_iop.description')}
                                </p>
                                <div className="bg-gray-50 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 text-xs">
                                    <code className="block text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.target_iop.api')}</code>
                                    <code className="block text-blue-600 dark:text-blue-400 font-mono">{t('sections.notice_bar.ophthalmology_tools.target_iop.service')}</code>
                                </div>
                            </div>
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg">
                                <img
                                    src="/docs/opth/assets/images/doctors_pages/notice-bar/calcs/05-opt.png"
                                    alt="Target IOP Calculator"
                                    className="w-full h-auto"
                                />
                            </div>
                        </div>
                    </Card>

                    {/* Refraction Consistency */}
                    <Card className="bg-gray-100 dark:bg-slate-800/30">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            <div>
                                <h4 className="font-semibold text-teal-600 dark:text-teal-400 mb-2 flex items-center gap-2">
                                    <CheckCircle className="w-5 h-5" />
                                    {t('sections.notice_bar.ophthalmology_tools.refraction.title')}
                                </h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {t('sections.notice_bar.ophthalmology_tools.refraction.description')}
                                </p>
                                <div className="bg-gray-50 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 text-xs">
                                    <code className="block text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.refraction.api')}</code>
                                    <code className="block text-blue-600 dark:text-blue-400 font-mono">{t('sections.notice_bar.ophthalmology_tools.refraction.service')}</code>
                                </div>
                            </div>
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg">
                                <img
                                    src="/docs/opth/assets/images/doctors_pages/notice-bar/calcs/06-opt.png"
                                    alt="Refraction Consistency Checker"
                                    className="w-full h-auto"
                                />
                            </div>
                        </div>
                    </Card>

                    {/* Visual Acuity Progress */}
                    <Card className="bg-gray-100 dark:bg-slate-800/30">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            <div>
                                <h4 className="font-semibold text-cyan-600 dark:text-cyan-400 mb-2 flex items-center gap-2">
                                    <Activity className="w-5 h-5" />
                                    {t('sections.notice_bar.ophthalmology_tools.visual_acuity.title')}
                                </h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {t('sections.notice_bar.ophthalmology_tools.visual_acuity.description')}
                                </p>
                                <div className="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg border border-yellow-200 dark:border-yellow-800 mb-4">
                                    <p className="text-xs text-yellow-800 dark:text-yellow-200">
                                        <strong>{t('sections.notice_bar.ophthalmology_tools.visual_acuity.auto_detect')}</strong>
                                    </p>
                                </div>
                                <div className="bg-gray-50 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 text-xs">
                                    <code className="block text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.visual_acuity.api')}</code>
                                    <code className="block text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.visual_acuity.service')}</code>
                                    <code className="block text-purple-600 dark:text-purple-400 font-mono">{t('sections.notice_bar.ophthalmology_tools.visual_acuity.interface')}</code>
                                </div>
                            </div>
                            <div className="space-y-4">
                                <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg">
                                    <img
                                        src="/docs/opth/assets/images/doctors_pages/notice-bar/calcs/07-visualAcuty01-opt.png"
                                        alt="Visual Acuity Progress Calculator"
                                        className="w-full h-auto"
                                    />
                                </div>
                                <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg">
                                    <img
                                        src="/docs/opth/assets/images/doctors_pages/notice-bar/calcs/08-visual-opt.png"
                                        alt="Visual Acuity Progress Results"
                                        className="w-full h-auto"
                                    />
                                </div>
                                <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg">
                                    <img
                                        src="/docs/opth/assets/images/doctors_pages/notice-bar/calcs/09-opt.png"
                                        alt="Visual Acuity Summary"
                                        className="w-full h-auto"
                                    />
                                </div>
                            </div>
                        </div>
                    </Card>

                    {/* OSDI */}
                    <Card className="bg-gray-100 dark:bg-slate-800/30">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            <div>
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400 mb-2 flex items-center gap-2">
                                    <Droplet className="w-5 h-5" />
                                    {t('sections.notice_bar.ophthalmology_tools.osdi.title')}
                                </h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {t('sections.notice_bar.ophthalmology_tools.osdi.description')}
                                </p>
                                <div className="bg-gray-50 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 text-xs">
                                    <code className="block text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.osdi.api')}</code>
                                    <code className="block text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.osdi.service')}</code>
                                    <code className="block text-purple-600 dark:text-purple-400 font-mono">{t('sections.notice_bar.ophthalmology_tools.osdi.questions')}</code>
                                </div>
                            </div>
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg">
                                <img
                                    src="/docs/opth/assets/images/doctors_pages/notice-bar/calcs/10(12QuestionsDryEye)-opt.png"
                                    alt="OSDI Calculator"
                                    className="w-full h-auto"
                                />
                            </div>
                        </div>
                    </Card>

                    {/* Pachymetry-Adjusted IOP */}
                    <Card className="bg-gray-100 dark:bg-slate-800/30">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            <div>
                                <h4 className="font-semibold text-pink-600 dark:text-pink-400 mb-2 flex items-center gap-2">
                                    <TrendingUp className="w-5 h-5" />
                                    {t('sections.notice_bar.ophthalmology_tools.pachymetry.title')}
                                </h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {t('sections.notice_bar.ophthalmology_tools.pachymetry.description')}
                                </p>
                                <div className="bg-gray-50 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 text-xs">
                                    <code className="block text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.pachymetry.api')}</code>
                                    <code className="block text-blue-600 dark:text-blue-400 font-mono">{t('sections.notice_bar.ophthalmology_tools.pachymetry.service')}</code>
                                </div>
                            </div>
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg">
                                <img
                                    src="/docs/opth/assets/images/doctors_pages/notice-bar/calcs/11-opt.png"
                                    alt="Pachymetry-Adjusted IOP Calculator"
                                    className="w-full h-auto"
                                />
                            </div>
                        </div>
                    </Card>

                    {/* Diabetic Retinopathy */}
                    <Card className="bg-gray-100 dark:bg-slate-800/30">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            <div>
                                <h4 className="font-semibold text-red-600 dark:text-red-400 mb-2 flex items-center gap-2">
                                    <AlertTriangle className="w-5 h-5" />
                                    {t('sections.notice_bar.ophthalmology_tools.diabetic_retinopathy.title')}
                                </h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {t('sections.notice_bar.ophthalmology_tools.diabetic_retinopathy.description')}
                                </p>
                                <div className="bg-gray-50 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 text-xs">
                                    <code className="block text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.diabetic_retinopathy.api')}</code>
                                    <code className="block text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.diabetic_retinopathy.service')}</code>
                                    <code className="block text-purple-600 dark:text-purple-400 font-mono">{t('sections.notice_bar.ophthalmology_tools.diabetic_retinopathy.interface')}</code>
                                </div>
                            </div>
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg">
                                <img
                                    src="/docs/opth/assets/images/doctors_pages/notice-bar/calcs/12-opt.png"
                                    alt="Diabetic Retinopathy Risk Estimator"
                                    className="w-full h-auto"
                                />
                            </div>
                        </div>
                    </Card>

                    {/* Macular Thickness */}
                    <Card className="bg-gray-100 dark:bg-slate-800/30">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            <div>
                                <h4 className="font-semibold text-violet-600 dark:text-violet-400 mb-2 flex items-center gap-2">
                                    <BarChart3 className="w-5 h-5" />
                                    {t('sections.notice_bar.ophthalmology_tools.macular_thickness.title')}
                                </h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {t('sections.notice_bar.ophthalmology_tools.macular_thickness.description')}
                                </p>
                                <div className="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg border border-yellow-200 dark:border-yellow-800 mb-4">
                                    <p className="text-xs text-yellow-800 dark:text-yellow-200">
                                        <strong>{t('sections.notice_bar.ophthalmology_tools.macular_thickness.auto_detect')}</strong>
                                    </p>
                                </div>
                                <div className="bg-gray-50 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 text-xs">
                                    <code className="block text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.macular_thickness.api')}</code>
                                    <code className="block text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.macular_thickness.service')}</code>
                                    <code className="block text-purple-600 dark:text-purple-400 font-mono">{t('sections.notice_bar.ophthalmology_tools.macular_thickness.interface')}</code>
                                </div>
                            </div>
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg">
                                <img
                                    src="/docs/opth/assets/images/doctors_pages/notice-bar/calcs/13-opt.png"
                                    alt="Macular Thickness Trend Analyzer"
                                    className="w-full h-auto"
                                />
                            </div>
                        </div>
                    </Card>

                    {/* Cataract Surgery Readiness */}
                    <Card className="bg-gray-100 dark:bg-slate-800/30">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            <div>
                                <h4 className="font-semibold text-amber-600 dark:text-amber-400 mb-2 flex items-center gap-2">
                                    <Scissors className="w-5 h-5" />
                                    {t('sections.notice_bar.ophthalmology_tools.cataract.title')}
                                </h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {t('sections.notice_bar.ophthalmology_tools.cataract.description')}
                                </p>
                                <div className="bg-gray-50 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 text-xs">
                                    <code className="block text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.cataract.api')}</code>
                                    <code className="block text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.cataract.service')}</code>
                                    <code className="block text-purple-600 dark:text-purple-400 font-mono">{t('sections.notice_bar.ophthalmology_tools.cataract.interface')}</code>
                                </div>
                            </div>
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg">
                                <img
                                    src="/docs/opth/assets/images/doctors_pages/notice-bar/calcs/14-opt.png"
                                    alt="Cataract Surgery Readiness Score"
                                    className="w-full h-auto"
                                />
                            </div>
                        </div>
                    </Card>

                    {/* Post-Operative Outcome */}
                    <Card className="bg-gray-100 dark:bg-slate-800/30">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            <div>
                                <h4 className="font-semibold text-emerald-600 dark:text-emerald-400 mb-2 flex items-center gap-2">
                                    <Stethoscope className="w-5 h-5" />
                                    {t('sections.notice_bar.ophthalmology_tools.postop.title')}
                                </h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {t('sections.notice_bar.ophthalmology_tools.postop.description')}
                                </p>
                                <div className="bg-gray-50 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 text-xs">
                                    <code className="block text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.postop.api')}</code>
                                    <code className="block text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notice_bar.ophthalmology_tools.postop.service')}</code>
                                    <code className="block text-purple-600 dark:text-purple-400 font-mono">{t('sections.notice_bar.ophthalmology_tools.postop.interface')}</code>
                                </div>
                            </div>
                            <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg">
                                <img
                                    src="/docs/opth/assets/images/doctors_pages/notice-bar/calcs/15-opt.png"
                                    alt="Post-Operative Outcome Analyzer"
                                    className="w-full h-auto"
                                />
                            </div>
                        </div>
                    </Card>
                </div>
            </Section>
        </div>
    );
}

