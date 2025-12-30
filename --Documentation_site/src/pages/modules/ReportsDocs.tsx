import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { FileBarChart, BarChart3, Table, Filter, Download, FileText, TrendingUp } from 'lucide-react';

export default function ReportsDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.reports.title')}
                subtitle={t('sections.reports.subtitle')}
            />

            <Section title={t('sections.reports.overview.title')} icon={<FileBarChart />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.reports.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.reports.page_controls.title')} icon={<Filter />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.reports.page_controls.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.reports.page_controls.quick_dates_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.reports.page_controls.quick_dates.this_month')}</strong></li>
                            <li><strong>{t('sections.reports.page_controls.quick_dates.all_time')}</strong></li>
                            <li><strong>{t('sections.reports.page_controls.quick_dates.last_quarter')}</strong></li>
                            <li><strong>{t('sections.reports.page_controls.quick_dates.last_6months')}</strong></li>
                            <li><strong>{t('sections.reports.page_controls.quick_dates.last_year')}</strong></li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.reports.page_controls.report_types_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.reports.page_controls.report_types.appointments')}</strong></li>
                            <li><strong>{t('sections.reports.page_controls.report_types.patients')}</strong></li>
                            <li><strong>{t('sections.reports.page_controls.report_types.revenue')}</strong></li>
                            <li><strong>{t('sections.reports.page_controls.report_types.medical_prescriptions')}</strong></li>
                            <li><strong>{t('sections.reports.page_controls.report_types.glasses_prescriptions')}</strong></li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.reports.page_controls.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.reports.page_controls.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.reports.page_controls.route')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.reports.page_controls.js_file')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/reports/01-opt.png"
                            alt={t('sections.reports.page_controls.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.reports.summary_stats.title')} icon={<TrendingUp />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.reports.summary_stats.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.reports.summary_stats.appointments_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.reports.summary_stats.appointments.total')}</li>
                            <li>{t('sections.reports.summary_stats.appointments.completed')}</li>
                            <li>{t('sections.reports.summary_stats.appointments.missed')}</li>
                            <li>{t('sections.reports.summary_stats.appointments.completion_ratio')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.reports.summary_stats.revenue_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.reports.summary_stats.revenue.total_revenue')}</li>
                            <li>{t('sections.reports.summary_stats.revenue.total_transactions')}</li>
                            <li>{t('sections.reports.summary_stats.revenue.avg_transaction')}</li>
                            <li>{t('sections.reports.summary_stats.revenue.total_discounts')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.reports.summary_stats.patients_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.reports.summary_stats.patients.total_new')}</li>
                            <li>{t('sections.reports.summary_stats.patients.male')}</li>
                            <li>{t('sections.reports.summary_stats.patients.female')}</li>
                            <li>{t('sections.reports.summary_stats.patients.male_percentage')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.reports.summary_stats.prescriptions_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.reports.summary_stats.prescriptions.total')}</li>
                            <li>{t('sections.reports.summary_stats.prescriptions.appointments_with')}</li>
                            <li>{t('sections.reports.summary_stats.prescriptions.patients')}</li>
                            <li>{t('sections.reports.summary_stats.prescriptions.avg_per_appointment')}</li>
                        </ul>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/reports/01-opt.png"
                            alt={t('sections.reports.summary_stats.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.reports.detailed_table.title')} icon={<Table />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.reports.detailed_table.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.reports.detailed_table.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.reports.detailed_table.features.pagination')}</li>
                            <li>{t('sections.reports.detailed_table.features.per_page')}</li>
                            <li>{t('sections.reports.detailed_table.features.columns')}</li>
                            <li>{t('sections.reports.detailed_table.features.date_formatting')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.reports.detailed_table.data_structure_title')}</h3>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mb-4">
                            <code className="block text-sm text-gray-800 dark:text-gray-200 font-mono break-words whitespace-pre-wrap">{t('sections.reports.detailed_table.data_structure')}</code>
                        </div>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.reports.detailed_table.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.reports.detailed_table.js_functions')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/reports/02-opt.png"
                            alt={t('sections.reports.detailed_table.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.reports.visual_analytics.title')} icon={<BarChart3 />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.reports.visual_analytics.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.reports.visual_analytics.chart_types_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.reports.visual_analytics.chart_types.line_charts')}</strong> - {t('sections.reports.visual_analytics.chart_types.line_description')}</li>
                            <li><strong>{t('sections.reports.visual_analytics.chart_types.pie_charts')}</strong> - {t('sections.reports.visual_analytics.chart_types.pie_description')}</li>
                            <li><strong>{t('sections.reports.visual_analytics.chart_types.doughnut_charts')}</strong> - {t('sections.reports.visual_analytics.chart_types.doughnut_description')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.reports.visual_analytics.appointments_charts_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.reports.visual_analytics.appointments_charts.trend')}</li>
                            <li>{t('sections.reports.visual_analytics.appointments_charts.status')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.reports.visual_analytics.revenue_charts_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.reports.visual_analytics.revenue_charts.trend')}</li>
                            <li>{t('sections.reports.visual_analytics.revenue_charts.vs_discounts')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.reports.visual_analytics.patients_charts_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.reports.visual_analytics.patients_charts.trend')}</li>
                            <li>{t('sections.reports.visual_analytics.patients_charts.gender')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.reports.visual_analytics.prescriptions_charts_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.reports.visual_analytics.prescriptions_charts.medical_trend')}</li>
                            <li>{t('sections.reports.visual_analytics.prescriptions_charts.top_medications')}</li>
                            <li>{t('sections.reports.visual_analytics.prescriptions_charts.glasses_trend')}</li>
                            <li>{t('sections.reports.visual_analytics.prescriptions_charts.lens_type')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.reports.visual_analytics.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.reports.visual_analytics.chart_library')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.reports.visual_analytics.theme_support')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.reports.visual_analytics.js_functions')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/reports/03-opt.png"
                            alt={t('sections.reports.visual_analytics.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.reports.export_functionality.title')} icon={<Download />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.reports.export_functionality.description')}
                    </p>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <FileText className="text-blue-600 dark:text-blue-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.reports.export_functionality.csv.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.reports.export_functionality.csv.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.reports.export_functionality.csv.endpoint')}</code>
                            </div>
                        </Card>
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <FileText className="text-red-600 dark:text-red-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.reports.export_functionality.pdf.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.reports.export_functionality.pdf.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.reports.export_functionality.pdf.js_function')}</code>
                            </div>
                        </Card>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.reports.export_functionality.pdf_features_title')}</h4>
                        <ul className="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-300 text-sm ml-2">
                            <li>{t('sections.reports.export_functionality.pdf_features.summary_stats')}</li>
                            <li>{t('sections.reports.export_functionality.pdf_features.charts')}</li>
                            <li>{t('sections.reports.export_functionality.pdf_features.detailed_table')}</li>
                            <li>{t('sections.reports.export_functionality.pdf_features.headers_footers')}</li>
                            <li>{t('sections.reports.export_functionality.pdf_features.page_numbers')}</li>
                        </ul>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.reports.api_endpoints.title')} icon={<FileBarChart />}>
                <div className="space-y-4">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.reports.api_endpoints.description')}
                    </p>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-3 font-bold tracking-wider">{t('sections.reports.api_endpoints.routes_title')}</h4>
                        <div className="space-y-2">
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.reports.api_endpoints.reports_page')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.reports.api_endpoints.export_csv')}</code>
                        </div>
                    </div>
                </div>
            </Section>
        </div>
    );
}

