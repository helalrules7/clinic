import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { User, Pill, Calendar, FileText, Camera, Download, Eye, Bell, MessageSquare, Clock, Activity, Link as LinkIcon, AlertCircle } from 'lucide-react';
import { Link } from 'react-router-dom';

export default function PatientProfileDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <div className="border-b border-gray-200 dark:border-white/10 pb-6">
                <div className="flex items-center gap-3 mb-2">
                    <div className="p-2 rounded-lg bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400">
                        <User size={24} />
                    </div>
                    <h1 className="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400">
                        {t('sections.patient_profile.hero.title')}
                    </h1>
                </div>
                <p className="text-xl text-gray-600 dark:text-gray-400 max-w-3xl">
                    {t('sections.patient_profile.hero.subtitle')}
                </p>
            </div>

            <Section title={t('sections.patient_profile.overview.title')} icon={<User />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.patient_profile.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.patient_profile.profile_header.title')} icon={<User />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patient_profile.profile_header.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.profile_header.components_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.patient_profile.profile_header.components.patient_info')}</strong> - {t('sections.patient_profile.profile_header.components.patient_info_desc')}</li>
                            <li><strong>{t('sections.patient_profile.profile_header.components.contact_info')}</strong> - {t('sections.patient_profile.profile_header.components.contact_info_desc')}</li>
                            <li><strong>{t('sections.patient_profile.profile_header.components.actions')}</strong> - {t('sections.patient_profile.profile_header.components.actions_desc')}</li>
                            <li><strong>{t('sections.patient_profile.profile_header.components.treating_doctor')}</strong> - {t('sections.patient_profile.profile_header.components.treating_doctor_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.profile_header.actions_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li><strong>{t('sections.patient_profile.profile_header.actions.book_appointment')}</strong> - {t('sections.patient_profile.profile_header.actions.book_appointment_desc')}</li>
                            <li><strong>{t('sections.patient_profile.profile_header.actions.print_summary')}</strong> - {t('sections.patient_profile.profile_header.actions.print_summary_desc')}</li>
                            <li><strong>{t('sections.patient_profile.profile_header.actions.export_data')}</strong> - {t('sections.patient_profile.profile_header.actions.export_data_desc')}</li>
                            <li><strong>{t('sections.patient_profile.profile_header.actions.edit_patient')}</strong> - {t('sections.patient_profile.profile_header.actions.edit_patient_desc')}</li>
                            <li><strong>{t('sections.patient_profile.profile_header.actions.set_alert')}</strong> - {t('sections.patient_profile.profile_header.actions.set_alert_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.patient_profile.profile_header.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.patient_profile.profile_header.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.patient_profile.profile_header.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.patient_profile.profile_header.view_file')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/patient_profile/doctor/01-opt.png"
                            alt={t('sections.patient_profile.profile_header.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patient_profile.prescriptions_history.title')} icon={<Pill />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/patient_profile/doctor/02-opt.png"
                            alt={t('sections.patient_profile.prescriptions_history.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patient_profile.prescriptions_history.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.prescriptions_history.tabs_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.patient_profile.prescriptions_history.tabs.medications')}</strong> - {t('sections.patient_profile.prescriptions_history.tabs.medications_desc')}</li>
                            <li><strong>{t('sections.patient_profile.prescriptions_history.tabs.glasses')}</strong> - {t('sections.patient_profile.prescriptions_history.tabs.glasses_desc')}</li>
                            <li><strong>{t('sections.patient_profile.prescriptions_history.tabs.all')}</strong> - {t('sections.patient_profile.prescriptions_history.tabs.all_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.prescriptions_history.timeline_features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.patient_profile.prescriptions_history.timeline_features.expand_collapse')}</strong> - {t('sections.patient_profile.prescriptions_history.timeline_features.expand_collapse_desc')}</li>
                            <li><strong>{t('sections.patient_profile.prescriptions_history.timeline_features.expand_all')}</strong> - {t('sections.patient_profile.prescriptions_history.timeline_features.expand_all_desc')}</li>
                            <li><strong>{t('sections.patient_profile.prescriptions_history.timeline_features.latest_badge')}</strong> - {t('sections.patient_profile.prescriptions_history.timeline_features.latest_badge_desc')}</li>
                            <li><strong>{t('sections.patient_profile.prescriptions_history.timeline_features.view_details')}</strong> - {t('sections.patient_profile.prescriptions_history.timeline_features.view_details_desc')}</li>
                            <li><strong>{t('sections.patient_profile.prescriptions_history.timeline_features.print')}</strong> - {t('sections.patient_profile.prescriptions_history.timeline_features.print_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.patient_profile.prescriptions_history.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.patient_profile.prescriptions_history.js_functions')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.patient_profile.prescriptions_history.api_endpoints')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patient_profile.prescription_modals.title')} icon={<Pill />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start mb-8">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patient_profile.prescription_modals.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.prescription_modals.medication_modal_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.patient_profile.prescription_modals.medication_modal.drug_list')}</li>
                            <li>{t('sections.patient_profile.prescription_modals.medication_modal.appointment_info')}</li>
                            <li>{t('sections.patient_profile.prescription_modals.medication_modal.print_option')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.prescription_modals.glasses_modal_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.patient_profile.prescription_modals.glasses_modal.eye_measurements')}</li>
                            <li>{t('sections.patient_profile.prescription_modals.glasses_modal.lens_type')}</li>
                            <li>{t('sections.patient_profile.prescription_modals.glasses_modal.comments')}</li>
                        </ul>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/patient_profile/doctor/03-opt.png"
                            alt={t('sections.patient_profile.prescription_modals.medication_image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
                <div className="flex items-center justify-center">
                    <img
                        src="/docs/opth/assets/images/patient_profile/doctor/04-opt.png"
                        alt={t('sections.patient_profile.prescription_modals.glasses_image_alt')}
                        className="rounded-lg shadow-lg max-w-full h-auto"
                    />
                </div>
            </Section>

            <Section title={t('sections.patient_profile.appointment_history.title')} icon={<Calendar />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patient_profile.appointment_history.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.appointment_history.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.patient_profile.appointment_history.features.timeline_view')}</strong> - {t('sections.patient_profile.appointment_history.features.timeline_view_desc')}</li>
                            <li><strong>{t('sections.patient_profile.appointment_history.features.expand_collapse')}</strong> - {t('sections.patient_profile.appointment_history.features.expand_collapse_desc')}</li>
                            <li><strong>{t('sections.patient_profile.appointment_history.features.expand_all')}</strong> - {t('sections.patient_profile.appointment_history.features.expand_all_desc')}</li>
                            <li><strong>{t('sections.patient_profile.appointment_history.features.status_badges')}</strong> - {t('sections.patient_profile.appointment_history.features.status_badges_desc')}</li>
                            <li><strong>{t('sections.patient_profile.appointment_history.features.prescriptions')}</strong> - {t('sections.patient_profile.appointment_history.features.prescriptions_desc')}</li>
                            <li><strong>{t('sections.patient_profile.appointment_history.features.followup_indicator')}</strong> - {t('sections.patient_profile.appointment_history.features.followup_indicator_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.patient_profile.appointment_history.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.patient_profile.appointment_history.js_functions')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.patient_profile.appointment_history.api_endpoints')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/patient_profile/doctor/05-opt.png"
                            alt={t('sections.patient_profile.appointment_history.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patient_profile.medical_history.title')} icon={<FileText />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start mb-8">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patient_profile.medical_history.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.medical_history.timeline_view_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.patient_profile.medical_history.timeline_view.entry_types')}</strong> - {t('sections.patient_profile.medical_history.timeline_view.entry_types_desc')}</li>
                            <li><strong>{t('sections.patient_profile.medical_history.timeline_view.expand_collapse')}</strong> - {t('sections.patient_profile.medical_history.timeline_view.expand_collapse_desc')}</li>
                            <li><strong>{t('sections.patient_profile.medical_history.timeline_view.actions')}</strong> - {t('sections.patient_profile.medical_history.timeline_view.actions_desc')}</li>
                        </ul>
                        <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 flex items-start gap-3 mb-4">
                            <AlertCircle className="text-blue-600 dark:text-blue-400 flex-shrink-0 mt-1" size={20} />
                            <p className="text-blue-800 dark:text-blue-200 text-sm">
                                {t('sections.patient_profile.medical_history.auto_analysis_notice')}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/patient_profile/doctor/06-opt.png"
                            alt={t('sections.patient_profile.medical_history.timeline_image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start mb-8">
                    <div className="order-2 lg:order-1 flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/patient_profile/doctor/07-opt.png"
                            alt={t('sections.patient_profile.medical_history.details_image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.medical_history.details_view_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.patient_profile.medical_history.details_view.view_modes')}</strong> - {t('sections.patient_profile.medical_history.details_view.view_modes_desc')}</li>
                            <li><strong>{t('sections.patient_profile.medical_history.details_view.switch_view')}</strong> - {t('sections.patient_profile.medical_history.details_view.switch_view_desc')}</li>
                            <li><strong>{t('sections.patient_profile.medical_history.details_view.full_details')}</strong> - {t('sections.patient_profile.medical_history.details_view.full_details_desc')}</li>
                        </ul>
                    </div>
                </div>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.medical_history.add_entry_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.patient_profile.medical_history.add_entry.form_fields')}</strong> - {t('sections.patient_profile.medical_history.add_entry.form_fields_desc')}</li>
                            <li><strong>{t('sections.patient_profile.medical_history.add_entry.categories')}</strong> - {t('sections.patient_profile.medical_history.add_entry.categories_desc')}</li>
                            <li><strong>{t('sections.patient_profile.medical_history.add_entry.status')}</strong> - {t('sections.patient_profile.medical_history.add_entry.status_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.patient_profile.medical_history.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.patient_profile.medical_history.js_functions')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.patient_profile.medical_history.api_endpoints')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/patient_profile/doctor/08-opt.png"
                            alt={t('sections.patient_profile.medical_history.add_entry_image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patient_profile.patient_alerts.title')} icon={<Bell />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patient_profile.patient_alerts.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.patient_alerts.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.patient_profile.patient_alerts.features.filtered_list')}</strong> - {t('sections.patient_profile.patient_alerts.features.filtered_list_desc')}</li>
                            <li><strong>{t('sections.patient_profile.patient_alerts.features.quick_create')}</strong> - {t('sections.patient_profile.patient_alerts.features.quick_create_desc')}</li>
                            <li><strong>{t('sections.patient_profile.patient_alerts.features.auto_select')}</strong> - {t('sections.patient_profile.patient_alerts.features.auto_select_desc')}</li>
                            <li><strong>{t('sections.patient_profile.patient_alerts.features.actions')}</strong> - {t('sections.patient_profile.patient_alerts.features.actions_desc')}</li>
                        </ul>
                        <div className="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 flex items-start gap-3 mb-4">
                            <LinkIcon className="text-amber-600 dark:text-amber-400 flex-shrink-0 mt-1" size={20} />
                            <p className="text-amber-800 dark:text-amber-200 text-sm">
                                {t('sections.patient_profile.patient_alerts.more_info')}{' '}
                                <Link to="/doctors-pages/alerts" className="text-amber-600 dark:text-amber-400 hover:underline font-semibold">
                                    {t('sections.patient_profile.patient_alerts.alerts_docs_link')}
                                </Link>
                            </p>
                        </div>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.patient_profile.patient_alerts.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.patient_profile.patient_alerts.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.patient_profile.patient_alerts.js_function')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/patient_profile/doctor/09-opt.png"
                            alt={t('sections.patient_profile.patient_alerts.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patient_profile.recent_appointments.title')} icon={<Calendar />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patient_profile.recent_appointments.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.recent_appointments.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li><strong>{t('sections.patient_profile.recent_appointments.features.limited_list')}</strong> - {t('sections.patient_profile.recent_appointments.features.limited_list_desc')}</li>
                            <li><strong>{t('sections.patient_profile.recent_appointments.features.quick_access')}</strong> - {t('sections.patient_profile.recent_appointments.features.quick_access_desc')}</li>
                            <li><strong>{t('sections.patient_profile.recent_appointments.features.status_info')}</strong> - {t('sections.patient_profile.recent_appointments.features.status_info_desc')}</li>
                        </ul>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/patient_profile/doctor/10-opt.png"
                            alt={t('sections.patient_profile.recent_appointments.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patient_profile.patient_files.title')} icon={<FileText />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start mb-8">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patient_profile.patient_files.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.patient_files.file_types_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.patient_profile.patient_files.file_types.images')}</strong> - {t('sections.patient_profile.patient_files.file_types.images_desc')}</li>
                            <li><strong>{t('sections.patient_profile.patient_files.file_types.documents')}</strong> - {t('sections.patient_profile.patient_files.file_types.documents_desc')}</li>
                            <li><strong>{t('sections.patient_profile.patient_files.file_types.upload')}</strong> - {t('sections.patient_profile.patient_files.file_types.upload_desc')}</li>
                        </ul>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/patient_profile/doctor/11-opt.png"
                            alt={t('sections.patient_profile.patient_files.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/patient_profile/doctor/12-opt.png"
                            alt={t('sections.patient_profile.patient_files.camera_image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.patient_files.camera_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.patient_profile.patient_files.camera.features.capture')}</strong> - {t('sections.patient_profile.patient_files.camera.features.capture_desc')}</li>
                            <li><strong>{t('sections.patient_profile.patient_files.camera.features.auto_upload')}</strong> - {t('sections.patient_profile.patient_files.camera.features.auto_upload_desc')}</li>
                            <li><strong>{t('sections.patient_profile.patient_files.camera.features.ajax')}</strong> - {t('sections.patient_profile.patient_files.camera.features.ajax_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.patient_profile.patient_files.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.patient_profile.patient_files.js_functions')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.patient_profile.patient_files.api_endpoints')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patient_profile.view_files.title')} icon={<Eye />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start mb-8">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patient_profile.view_files.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.view_files.modal_features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.patient_profile.view_files.modal_features.image_preview')}</strong> - {t('sections.patient_profile.view_files.modal_features.image_preview_desc')}</li>
                            <li><strong>{t('sections.patient_profile.view_files.modal_features.document_view')}</strong> - {t('sections.patient_profile.view_files.modal_features.document_view_desc')}</li>
                            <li><strong>{t('sections.patient_profile.view_files.modal_features.download')}</strong> - {t('sections.patient_profile.view_files.modal_features.download_desc')}</li>
                            <li><strong>{t('sections.patient_profile.view_files.modal_features.file_info')}</strong> - {t('sections.patient_profile.view_files.modal_features.file_info_desc')}</li>
                        </ul>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/patient_profile/doctor/13-opt.png"
                            alt={t('sections.patient_profile.view_files.files_image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
                <div className="flex items-center justify-center">
                    <img
                        src="/docs/opth/assets/images/patient_profile/doctor/14-opt.png"
                        alt={t('sections.patient_profile.view_files.modal_image_alt')}
                        className="rounded-lg shadow-lg max-w-full h-auto"
                    />
                </div>
            </Section>

            <Section title={t('sections.patient_profile.forum_topics.title')} icon={<MessageSquare />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patient_profile.forum_topics.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.forum_topics.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.patient_profile.forum_topics.features.filtered_topics')}</strong> - {t('sections.patient_profile.forum_topics.features.filtered_topics_desc')}</li>
                            <li><strong>{t('sections.patient_profile.forum_topics.features.patient_mention')}</strong> - {t('sections.patient_profile.forum_topics.features.patient_mention_desc')}</li>
                            <li><strong>{t('sections.patient_profile.forum_topics.features.quick_access')}</strong> - {t('sections.patient_profile.forum_topics.features.quick_access_desc')}</li>
                        </ul>
                        <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 flex items-start gap-3 mb-4">
                            <LinkIcon className="text-blue-600 dark:text-blue-400 flex-shrink-0 mt-1" size={20} />
                            <p className="text-blue-800 dark:text-blue-200 text-sm">
                                {t('sections.patient_profile.forum_topics.more_info')}{' '}
                                <Link to="/doctors-pages/forum" className="text-blue-600 dark:text-blue-400 hover:underline font-semibold">
                                    {t('sections.patient_profile.forum_topics.forum_docs_link')}
                                </Link>
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/patient_profile/doctor/15-opt.png"
                            alt={t('sections.patient_profile.forum_topics.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patient_profile.full_timeline.title')} icon={<Clock />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patient_profile.full_timeline.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patient_profile.full_timeline.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.patient_profile.full_timeline.features.comprehensive')}</strong> - {t('sections.patient_profile.full_timeline.features.comprehensive_desc')}</li>
                            <li><strong>{t('sections.patient_profile.full_timeline.features.event_types')}</strong> - {t('sections.patient_profile.full_timeline.features.event_types_desc')}</li>
                            <li><strong>{t('sections.patient_profile.full_timeline.features.chronological')}</strong> - {t('sections.patient_profile.full_timeline.features.chronological_desc')}</li>
                            <li><strong>{t('sections.patient_profile.full_timeline.features.details')}</strong> - {t('sections.patient_profile.full_timeline.features.details_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.patient_profile.full_timeline.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.patient_profile.full_timeline.controller_method')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.patient_profile.full_timeline.data_source')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/patient_profile/doctor/16-opt.png"
                            alt={t('sections.patient_profile.full_timeline.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patient_profile.api_endpoints.title')} icon={<Activity />}>
                <div className="space-y-4">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.patient_profile.api_endpoints.description')}
                    </p>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <User className="text-blue-600 dark:text-blue-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.patient_profile.api_endpoints.get_patient.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.patient_profile.api_endpoints.get_patient.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.patient_profile.api_endpoints.get_patient.endpoint')}</code>
                            </div>
                        </Card>
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <FileText className="text-teal-600 dark:text-teal-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.patient_profile.api_endpoints.upload_file.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.patient_profile.api_endpoints.upload_file.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.patient_profile.api_endpoints.upload_file.endpoint')}</code>
                            </div>
                        </Card>
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <Camera className="text-purple-600 dark:text-purple-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.patient_profile.api_endpoints.capture_image.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.patient_profile.api_endpoints.capture_image.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.patient_profile.api_endpoints.capture_image.endpoint')}</code>
                            </div>
                        </Card>
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <Download className="text-amber-600 dark:text-amber-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.patient_profile.api_endpoints.export_data.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.patient_profile.api_endpoints.export_data.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.patient_profile.api_endpoints.export_data.endpoint')}</code>
                            </div>
                        </Card>
                    </div>
                </div>
            </Section>
        </div>
    );
}
