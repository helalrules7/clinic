import Hero from '../../components/ui/Hero';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Users, Plus, Eye, Search, Calendar, CreditCard, FileText } from 'lucide-react';
import { useTranslation } from 'react-i18next';

export default function SecretaryPatientsDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.secretary_patients.hero.title')}
                subtitle={t('sections.secretary_patients.hero.subtitle')}
            />

            <Section title={t('sections.secretary_patients.overview.title')} icon={<Users />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_patients/01-opt.png"
                            alt="Secretary Patients Page"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_patients.overview.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_patients.overview.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_patients.overview.features.statistics')}:</strong> {t('sections.secretary_patients.overview.features.statistics_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.overview.features.search')}:</strong> {t('sections.secretary_patients.overview.features.search_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.overview.features.filters')}:</strong> {t('sections.secretary_patients.overview.features.filters_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.overview.features.quick_actions')}:</strong> {t('sections.secretary_patients.overview.features.quick_actions_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.overview.features.auto_refresh')}:</strong> {t('sections.secretary_patients.overview.features.auto_refresh_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.overview.features.keyboard_shortcuts')}:</strong> {t('sections.secretary_patients.overview.features.keyboard_shortcuts_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_patients.overview.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_patients.overview.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.secretary_patients.overview.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1">{t('sections.secretary_patients.overview.view_file')}</code>
                            <code className="block text-sm text-yellow-600 dark:text-yellow-400 font-mono">{t('sections.secretary_patients.overview.api_endpoint')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_patients.statistics.title')} icon={<Users />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_patients.statistics.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_patients.statistics.cards_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_patients.statistics.cards.total')}:</strong> {t('sections.secretary_patients.statistics.cards.total_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.statistics.cards.active')}:</strong> {t('sections.secretary_patients.statistics.cards.active_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.statistics.cards.recent')}:</strong> {t('sections.secretary_patients.statistics.cards.recent_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.statistics.cards.total_paid')}:</strong> {t('sections.secretary_patients.statistics.cards.total_paid_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_patients.statistics.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_patients.statistics.controller_method')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono">{t('sections.secretary_patients.statistics.js_function')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_patients/01-opt.png"
                            alt="Statistics Cards"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_patients.search_filters.title')} icon={<Search />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_patients/01-opt.png"
                            alt="Search and Filters"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_patients.search_filters.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_patients.search_filters.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_patients.search_filters.features.quick_search')}:</strong> {t('sections.secretary_patients.search_filters.features.quick_search_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.search_filters.features.advanced_search')}:</strong> {t('sections.secretary_patients.search_filters.features.advanced_search_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.search_filters.features.gender_filter')}:</strong> {t('sections.secretary_patients.search_filters.features.gender_filter_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.search_filters.features.age_filter')}:</strong> {t('sections.secretary_patients.search_filters.features.age_filter_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.search_filters.features.last_visit_filter')}:</strong> {t('sections.secretary_patients.search_filters.features.last_visit_filter_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_patients.search_filters.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_patients.search_filters.js_functions')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.secretary_patients.search_filters.api_endpoint')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.secretary_patients.search_filters.controller_method')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_patients.add_patient.title')} icon={<Plus />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_patients/02-opt.png"
                            alt="Add Patient Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_patients.add_patient.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_patients.add_patient.form_fields_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_patients.add_patient.form_fields.basic_info')}:</strong> {t('sections.secretary_patients.add_patient.form_fields.basic_info_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.add_patient.form_fields.contact_info')}:</strong> {t('sections.secretary_patients.add_patient.form_fields.contact_info_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.add_patient.form_fields.age_dob')}:</strong> {t('sections.secretary_patients.add_patient.form_fields.age_dob_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.add_patient.form_fields.validation')}:</strong> {t('sections.secretary_patients.add_patient.form_fields.validation_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_patients.add_patient.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_patients.add_patient.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.secretary_patients.add_patient.controller_method')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1">{t('sections.secretary_patients.add_patient.js_function')}</code>
                            <code className="block text-sm text-yellow-600 dark:text-yellow-400 font-mono">{t('sections.secretary_patients.add_patient.validation_rules')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_patients.patient_details.title')} icon={<Eye />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_patients.patient_details.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_patients.patient_details.sections_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_patients.patient_details.sections.personal_info')}:</strong> {t('sections.secretary_patients.patient_details.sections.personal_info_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.patient_details.sections.contact_info')}:</strong> {t('sections.secretary_patients.patient_details.sections.contact_info_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.patient_details.sections.appointments')}:</strong> {t('sections.secretary_patients.patient_details.sections.appointments_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.patient_details.sections.payments')}:</strong> {t('sections.secretary_patients.patient_details.sections.payments_desc')}</li>
                            <li><strong>{t('sections.secretary_patients.patient_details.sections.quick_actions')}:</strong> {t('sections.secretary_patients.patient_details.sections.quick_actions_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_patients.patient_details.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_patients.patient_details.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.secretary_patients.patient_details.controller_method')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1">{t('sections.secretary_patients.patient_details.view_file')}</code>
                            <code className="block text-sm text-yellow-600 dark:text-yellow-400 font-mono">{t('sections.secretary_patients.patient_details.data_methods')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_patients/03-opt.png"
                            alt="Patient Details Page"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_patients.table_features.title')} icon={<Users />}>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card title={t('sections.secretary_patients.table_features.columns.title')} icon={Users}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            {t('sections.secretary_patients.table_features.columns.description')}
                        </p>
                        <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                            <li>{t('sections.secretary_patients.table_features.columns.name')}</li>
                            <li>{t('sections.secretary_patients.table_features.columns.phone')}</li>
                            <li>{t('sections.secretary_patients.table_features.columns.age')}</li>
                            <li>{t('sections.secretary_patients.table_features.columns.gender')}</li>
                            <li>{t('sections.secretary_patients.table_features.columns.last_visit')}</li>
                            <li>{t('sections.secretary_patients.table_features.columns.total_appointments')}</li>
                            <li>{t('sections.secretary_patients.table_features.columns.actions')}</li>
                        </ul>
                    </Card>
                    <Card title={t('sections.secretary_patients.table_features.actions.title')} icon={Calendar}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            {t('sections.secretary_patients.table_features.actions.description')}
                        </p>
                        <div className="space-y-2">
                            <div className="flex items-center gap-2">
                                <Eye size={16} className="text-blue-500" />
                                <span className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_patients.table_features.actions.view')}</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <Calendar size={16} className="text-green-500" />
                                <span className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_patients.table_features.actions.book')}</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <CreditCard size={16} className="text-yellow-500" />
                                <span className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_patients.table_features.actions.payments')}</span>
                            </div>
                        </div>
                    </Card>
                </div>
            </Section>

            <Section title={t('sections.secretary_patients.api_endpoints.title')} icon={<FileText />}>
                <div className="space-y-4">
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/secretary/patients</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_patients.api_endpoints.get_patients')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/api/secretary/patients</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_patients.api_endpoints.get_patients_data')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/secretary/patients/{'{id}'}</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_patients.api_endpoints.get_patient_details')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/secretary/patients/new</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_patients.api_endpoints.new_patient_page')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 font-mono">POST</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/secretary/patients</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_patients.api_endpoints.create_patient')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/api/patients/search?q={'{query}'}</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_patients.api_endpoints.search_patients')}</p>
                    </div>
                </div>
            </Section>
        </div>
    );
}

