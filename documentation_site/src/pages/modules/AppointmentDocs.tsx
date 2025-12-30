import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import Section from '../../components/ui/Section';
import { Calendar, CheckCircle, AlertCircle, FileText, Pill, FlaskConical, Eye, Camera, Paperclip, History, MessageSquare, Edit, Printer, MoreVertical, User, Link as LinkIcon } from 'lucide-react';

export default function AppointmentDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.appointment.title')}
                subtitle={t('sections.appointment.subtitle')}
            />

            <Section title={t('sections.appointment.overview.title')} icon={<Calendar />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.appointment.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.appointment.header.title')} icon={<Calendar />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/00-opt.png"
                            alt="Appointment Header"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.appointment.header.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.appointment.header.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.appointment.header.features.doctor_detection')}:</strong> {t('sections.appointment.header.features.doctor_detection_desc')}</li>
                            <li><strong>{t('sections.appointment.header.features.original_appointment')}:</strong> {t('sections.appointment.header.features.original_appointment_desc')}</li>
                            <li><strong>{t('sections.appointment.header.features.followup_appointment')}:</strong> {t('sections.appointment.header.features.followup_appointment_desc')}</li>
                            <li><strong>{t('sections.appointment.header.features.status_colors')}:</strong> {t('sections.appointment.header.features.status_colors_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.appointment.header.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.appointment.header.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.appointment.header.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.appointment.header.js_function')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.appointment.status.title')} icon={<AlertCircle />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.appointment.status.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.appointment.status.types_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong className="text-green-600 dark:text-green-400">{t('sections.appointment.status.types.completed')}:</strong> {t('sections.appointment.status.types.completed_desc')}</li>
                            <li><strong className="text-blue-600 dark:text-blue-400">{t('sections.appointment.status.types.scheduled')}:</strong> {t('sections.appointment.status.types.scheduled_desc')}</li>
                            <li><strong className="text-yellow-600 dark:text-yellow-400">{t('sections.appointment.status.types.pending')}:</strong> {t('sections.appointment.status.types.pending_desc')}</li>
                            <li><strong className="text-red-600 dark:text-red-400">{t('sections.appointment.status.types.cancelled')}:</strong> {t('sections.appointment.status.types.cancelled_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.appointment.status.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.appointment.status.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono">{t('sections.appointment.status.js_function')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/001-opt.png"
                            alt="Appointment Status Colors"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.appointment.mark_completed.title')} icon={<CheckCircle />}>
                <p className="text-gray-700 dark:text-gray-300 mb-4">
                    {t('sections.appointment.mark_completed.description')}
                </p>
                <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                    <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.appointment.mark_completed.technical')}</h4>
                    <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.appointment.mark_completed.api_endpoint')}</code>
                    <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono">{t('sections.appointment.mark_completed.js_function')}</code>
                </div>
            </Section>

            <Section title={t('sections.appointment.status_modal.title')} icon={<AlertCircle />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/01-opt.png"
                            alt="Status Change Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.appointment.status_modal.description')}
                        </p>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.appointment.status_modal.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.appointment.status_modal.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono">{t('sections.appointment.status_modal.js_function')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.appointment.tools.title')} icon={<MoreVertical />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/02-opt.png"
                            alt="Appointment Tools"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.appointment.tools.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.appointment.tools.actions_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.appointment.tools.actions.edit_consultation')}:</strong> {t('sections.appointment.tools.actions.edit_consultation_desc')}</li>
                            <li><strong>{t('sections.appointment.tools.actions.add_prescription')}:</strong> {t('sections.appointment.tools.actions.add_prescription_desc')}</li>
                            <li><strong>{t('sections.appointment.tools.actions.print_report')}:</strong> {t('sections.appointment.tools.actions.print_report_desc')}</li>
                            <li><strong>{t('sections.appointment.tools.actions.reschedule')}:</strong> {t('sections.appointment.tools.actions.reschedule_desc')}</li>
                            <li><strong>{t('sections.appointment.tools.actions.add_notes')}:</strong> {t('sections.appointment.tools.actions.add_notes_desc')}</li>
                        </ul>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.appointment.more_actions.title')} icon={<MoreVertical />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.appointment.more_actions.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.appointment.more_actions.actions_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.appointment.more_actions.actions.schedule_followup')}:</strong> {t('sections.appointment.more_actions.actions.schedule_followup_desc')}</li>
                            <li><strong>{t('sections.appointment.more_actions.actions.view_patient')}:</strong> {t('sections.appointment.more_actions.actions.view_patient_desc')}</li>
                            <li><strong>{t('sections.appointment.more_actions.actions.change_status')}:</strong> {t('sections.appointment.more_actions.actions.change_status_desc')}</li>
                        </ul>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/03-opt.png"
                            alt="More Actions Menu"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.appointment.patient_card.title')} icon={<User />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/04-opt.png"
                            alt="Patient Profile Card"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.appointment.patient_card.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.appointment.patient_card.info_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.appointment.patient_card.info.patient_name')}:</strong> {t('sections.appointment.patient_card.info.patient_name_desc')}</li>
                            <li><strong>{t('sections.appointment.patient_card.info.contact')}:</strong> {t('sections.appointment.patient_card.info.contact_desc')}</li>
                            <li><strong>{t('sections.appointment.patient_card.info.age')}:</strong> {t('sections.appointment.patient_card.info.age_desc')}</li>
                            <li><strong>{t('sections.appointment.patient_card.info.quick_actions')}:</strong> {t('sections.appointment.patient_card.info.quick_actions_desc')}</li>
                        </ul>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.appointment.medications.title')} icon={<Pill />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/05-opt.png"
                            alt="Medications Section"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.appointment.medications.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.appointment.medications.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.appointment.medications.features.add_edit')}:</strong> {t('sections.appointment.medications.features.add_edit_desc')}</li>
                            <li><strong>{t('sections.appointment.medications.features.print')}:</strong> {t('sections.appointment.medications.features.print_desc')}</li>
                            <li><strong>{t('sections.appointment.medications.features.delete')}:</strong> {t('sections.appointment.medications.features.delete_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.appointment.medications.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.appointment.medications.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.appointment.medications.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.appointment.medications.js_function')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.appointment.add_medication.title')} icon={<Pill />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.appointment.add_medication.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.appointment.add_medication.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.appointment.add_medication.features.most_used')}:</strong> {t('sections.appointment.add_medication.features.most_used_desc')}</li>
                            <li><strong>{t('sections.appointment.add_medication.features.autocomplete')}:</strong> {t('sections.appointment.add_medication.features.autocomplete_desc')}</li>
                            <li><strong>{t('sections.appointment.add_medication.features.price_tag')}:</strong> {t('sections.appointment.add_medication.features.price_tag_desc')}</li>
                            <li><strong>{t('sections.appointment.add_medication.features.usage_times')}:</strong> {t('sections.appointment.add_medication.features.usage_times_desc')}</li>
                        </ul>
                        <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                            <p className="text-sm text-blue-800 dark:text-blue-200">
                                <LinkIcon className="inline mr-2" size={16} />
                                {t('sections.appointment.add_medication.drugs_db_link')} <Link to="/doctors-pages/drugs" className="text-blue-600 dark:text-blue-400 hover:underline">{t('sections.appointment.add_medication.drugs_db_link_text')}</Link>
                            </p>
                            <p className="text-sm text-blue-800 dark:text-blue-200 mt-2">
                                <LinkIcon className="inline mr-2" size={16} />
                                {t('sections.appointment.add_medication.medications_gallery_link')} <Link to="/doctors-pages/medications" className="text-blue-600 dark:text-blue-400 hover:underline">{t('sections.appointment.add_medication.medications_gallery_link_text')}</Link>
                            </p>
                        </div>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.appointment.add_medication.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.appointment.add_medication.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.appointment.add_medication.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.appointment.add_medication.js_function')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/06-opt.png"
                            alt="Add Medication Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.appointment.print_medication.title')} icon={<Printer />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/07-opt.png"
                            alt="Print Medication Prescription"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.appointment.print_medication.description')}
                        </p>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.appointment.print_medication.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.appointment.print_medication.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono">{t('sections.appointment.print_medication.js_function')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.appointment.lab_tests.title')} icon={<FlaskConical />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.appointment.lab_tests.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.appointment.lab_tests.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.appointment.lab_tests.features.add_test')}:</strong> {t('sections.appointment.lab_tests.features.add_test_desc')}</li>
                            <li><strong>{t('sections.appointment.lab_tests.features.categories')}:</strong> {t('sections.appointment.lab_tests.features.categories_desc')}</li>
                            <li><strong>{t('sections.appointment.lab_tests.features.results')}:</strong> {t('sections.appointment.lab_tests.features.results_desc')}</li>
                            <li><strong>{t('sections.appointment.lab_tests.features.print')}:</strong> {t('sections.appointment.lab_tests.features.print_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.appointment.lab_tests.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.appointment.lab_tests.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.appointment.lab_tests.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.appointment.lab_tests.js_function')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/08-opt.png"
                            alt="Add Lab Test Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.appointment.glasses.title')} icon={<Eye />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/09-opt.png"
                            alt="Add Glasses Prescription Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.appointment.glasses.description')}
                        </p>
                        <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                            <p className="text-sm text-blue-800 dark:text-blue-200">
                                <LinkIcon className="inline mr-2" size={16} />
                                {t('sections.appointment.glasses.gallery_link')} <Link to="/doctors-pages/glasses" className="text-blue-600 dark:text-blue-400 hover:underline">{t('sections.appointment.glasses.gallery_link_text')}</Link>
                            </p>
                        </div>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.appointment.glasses.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.appointment.glasses.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.appointment.glasses.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.appointment.glasses.js_function')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.appointment.attachments.title')} icon={<Paperclip />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.appointment.attachments.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.appointment.attachments.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.appointment.attachments.features.upload')}:</strong> {t('sections.appointment.attachments.features.upload_desc')}</li>
                            <li><strong>{t('sections.appointment.attachments.features.camera')}:</strong> {t('sections.appointment.attachments.features.camera_desc')}</li>
                            <li><strong>{t('sections.appointment.attachments.features.view')}:</strong> {t('sections.appointment.attachments.features.view_desc')}</li>
                            <li><strong>{t('sections.appointment.attachments.features.delete')}:</strong> {t('sections.appointment.attachments.features.delete_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.appointment.attachments.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.appointment.attachments.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.appointment.attachments.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.appointment.attachments.js_function')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/10-opt.png"
                            alt="Add Attachment Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.appointment.camera.title')} icon={<Camera />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/11-opt.png"
                            alt="Camera Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.appointment.camera.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.appointment.camera.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.appointment.camera.features.capture')}:</strong> {t('sections.appointment.camera.features.capture_desc')}</li>
                            <li><strong>{t('sections.appointment.camera.features.retake')}:</strong> {t('sections.appointment.camera.features.retake_desc')}</li>
                            <li><strong>{t('sections.appointment.camera.features.save')}:</strong> {t('sections.appointment.camera.features.save_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.appointment.camera.technical')}</h4>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.appointment.camera.js_function')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.appointment.medical_history.title')} icon={<History />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.appointment.medical_history.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.appointment.medical_history.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.appointment.medical_history.features.carousel')}:</strong> {t('sections.appointment.medical_history.features.carousel_desc')}</li>
                            <li><strong>{t('sections.appointment.medical_history.features.navigation')}:</strong> {t('sections.appointment.medical_history.features.navigation_desc')}</li>
                            <li><strong>{t('sections.appointment.medical_history.features.summary')}:</strong> {t('sections.appointment.medical_history.features.summary_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.appointment.medical_history.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.appointment.medical_history.api_endpoint')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.appointment.medical_history.js_function')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/12-opt.png"
                            alt="Medical History Carousel"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.appointment.forum_topics.title')} icon={<MessageSquare />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/13-opt.png"
                            alt="Forum Topics"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.appointment.forum_topics.description')}
                        </p>
                        <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                            <p className="text-sm text-blue-800 dark:text-blue-200">
                                <LinkIcon className="inline mr-2" size={16} />
                                {t('sections.appointment.forum_topics.forum_link')} <Link to="/doctors-pages/forum" className="text-blue-600 dark:text-blue-400 hover:underline">{t('sections.appointment.forum_topics.forum_link_text')}</Link>
                            </p>
                        </div>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.appointment.forum_topics.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.appointment.forum_topics.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono">{t('sections.appointment.forum_topics.controller')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.appointment.consultation_notes.title')} icon={<FileText />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.appointment.consultation_notes.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.appointment.consultation_notes.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.appointment.consultation_notes.features.add')}:</strong> {t('sections.appointment.consultation_notes.features.add_desc')}</li>
                            <li><strong>{t('sections.appointment.consultation_notes.features.edit')}:</strong> {t('sections.appointment.consultation_notes.features.edit_desc')}</li>
                            <li><strong>{t('sections.appointment.consultation_notes.features.delete')}:</strong> {t('sections.appointment.consultation_notes.features.delete_desc')}</li>
                            <li><strong>{t('sections.appointment.consultation_notes.features.expand')}:</strong> {t('sections.appointment.consultation_notes.features.expand_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.appointment.consultation_notes.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.appointment.consultation_notes.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.appointment.consultation_notes.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.appointment.consultation_notes.js_function')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/14-opt.png"
                            alt="Consultation Notes"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.appointment.edit_consultation.title')} icon={<Edit />}>
                <p className="text-gray-700 dark:text-gray-300 mb-6">
                    {t('sections.appointment.edit_consultation.description')}
                </p>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/15-01-opt.png"
                            alt="Edit Consultation Page 1"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/15-02-opt.png"
                            alt="Edit Consultation Page 2"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/appointment/15-03-opt.png"
                            alt="Edit Consultation Page 3"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
                <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.appointment.edit_consultation.features_title')}</h3>
                <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                    <li><strong>{t('sections.appointment.edit_consultation.features.comprehensive_form')}:</strong> {t('sections.appointment.edit_consultation.features.comprehensive_form_desc')}</li>
                    <li><strong>{t('sections.appointment.edit_consultation.features.clinical_data')}:</strong> {t('sections.appointment.edit_consultation.features.clinical_data_desc')}</li>
                    <li><strong>{t('sections.appointment.edit_consultation.features.sticky_header')}:</strong> {t('sections.appointment.edit_consultation.features.sticky_header_desc')}</li>
                    <li><strong>{t('sections.appointment.edit_consultation.features.validation')}:</strong> {t('sections.appointment.edit_consultation.features.validation_desc')}</li>
                </ul>
                <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                    <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.appointment.edit_consultation.technical')}</h4>
                    <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.appointment.edit_consultation.route')}</code>
                    <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.appointment.edit_consultation.controller')}</code>
                    <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1">{t('sections.appointment.edit_consultation.js_file')}</code>
                    <code className="block text-sm text-yellow-600 dark:text-yellow-400 font-mono">{t('sections.appointment.edit_consultation.view_file')}</code>
                </div>
            </Section>
        </div>
    );
}

