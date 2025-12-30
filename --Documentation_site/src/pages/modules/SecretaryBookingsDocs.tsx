import Hero from '../../components/ui/Hero';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Calendar, Plus, Edit, CheckCircle, Trash2, Printer, Clock, FileText } from 'lucide-react';
import { useTranslation } from 'react-i18next';

export default function SecretaryBookingsDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.secretary_bookings.hero.title')}
                subtitle={t('sections.secretary_bookings.hero.subtitle')}
            />

            <Section title={t('sections.secretary_bookings.overview.title')} icon={<Calendar />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_cal/01-opt.png"
                            alt="Secretary Bookings Page"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_bookings.overview.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_bookings.overview.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_bookings.overview.features.statistics')}:</strong> {t('sections.secretary_bookings.overview.features.statistics_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.overview.features.calendar')}:</strong> {t('sections.secretary_bookings.overview.features.calendar_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.overview.features.auto_refresh')}:</strong> {t('sections.secretary_bookings.overview.features.auto_refresh_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.overview.features.keyboard_shortcuts')}:</strong> {t('sections.secretary_bookings.overview.features.keyboard_shortcuts_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.overview.features.status_tracking')}:</strong> {t('sections.secretary_bookings.overview.features.status_tracking_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.overview.features.payment_integration')}:</strong> {t('sections.secretary_bookings.overview.features.payment_integration_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_bookings.overview.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_bookings.overview.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.secretary_bookings.overview.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1">{t('sections.secretary_bookings.overview.view_file')}</code>
                            <code className="block text-sm text-yellow-600 dark:text-yellow-400 font-mono">{t('sections.secretary_bookings.overview.js_functions')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_bookings.statistics.title')} icon={<Calendar />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_bookings.statistics.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_bookings.statistics.cards_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_bookings.statistics.cards.total')}:</strong> {t('sections.secretary_bookings.statistics.cards.total_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.statistics.cards.pending')}:</strong> {t('sections.secretary_bookings.statistics.cards.pending_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.statistics.cards.checked_in')}:</strong> {t('sections.secretary_bookings.statistics.cards.checked_in_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.statistics.cards.completed')}:</strong> {t('sections.secretary_bookings.statistics.cards.completed_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_bookings.statistics.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_bookings.statistics.js_function')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono">{t('sections.secretary_bookings.statistics.update_function')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_cal/01-opt.png"
                            alt="Statistics Cards"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_bookings.add_booking.title')} icon={<Plus />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_cal/02-opt.png"
                            alt="Add Booking Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_bookings.add_booking.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_bookings.add_booking.form_fields_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_bookings.add_booking.form_fields.patient')}:</strong> {t('sections.secretary_bookings.add_booking.form_fields.patient_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.add_booking.form_fields.date')}:</strong> {t('sections.secretary_bookings.add_booking.form_fields.date_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.add_booking.form_fields.time')}:</strong> {t('sections.secretary_bookings.add_booking.form_fields.time_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.add_booking.form_fields.doctor')}:</strong> {t('sections.secretary_bookings.add_booking.form_fields.doctor_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.add_booking.form_fields.visit_type')}:</strong> {t('sections.secretary_bookings.add_booking.form_fields.visit_type_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.add_booking.form_fields.payment')}:</strong> {t('sections.secretary_bookings.add_booking.form_fields.payment_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_bookings.add_booking.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_bookings.add_booking.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.secretary_bookings.add_booking.controller_method')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1">{t('sections.secretary_bookings.add_booking.js_function')}</code>
                            <code className="block text-sm text-yellow-600 dark:text-yellow-400 font-mono">{t('sections.secretary_bookings.add_booking.validation')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_bookings.edit_booking.title')} icon={<Edit />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_bookings.edit_booking.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_bookings.edit_booking.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_bookings.edit_booking.features.patient_change')}:</strong> {t('sections.secretary_bookings.edit_booking.features.patient_change_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.edit_booking.features.reschedule')}:</strong> {t('sections.secretary_bookings.edit_booking.features.reschedule_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.edit_booking.features.additional_payment')}:</strong> {t('sections.secretary_bookings.edit_booking.features.additional_payment_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.edit_booking.features.notes')}:</strong> {t('sections.secretary_bookings.edit_booking.features.notes_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_bookings.edit_booking.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_bookings.edit_booking.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.secretary_bookings.edit_booking.controller_method')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.secretary_bookings.edit_booking.js_function')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_cal/03-opt.png"
                            alt="Edit Booking Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_bookings.confirm_attendance.title')} icon={<CheckCircle />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_cal/04-opt.png"
                            alt="Confirm Attendance Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_bookings.confirm_attendance.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_bookings.confirm_attendance.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_bookings.confirm_attendance.features.status_update')}:</strong> {t('sections.secretary_bookings.confirm_attendance.features.status_update_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.confirm_attendance.features.remaining_payment')}:</strong> {t('sections.secretary_bookings.confirm_attendance.features.remaining_payment_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.confirm_attendance.features.payment_validation')}:</strong> {t('sections.secretary_bookings.confirm_attendance.features.payment_validation_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.confirm_attendance.features.automatic_calculation')}:</strong> {t('sections.secretary_bookings.confirm_attendance.features.automatic_calculation_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_bookings.confirm_attendance.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_bookings.confirm_attendance.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.secretary_bookings.confirm_attendance.controller_method')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.secretary_bookings.confirm_attendance.js_function')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_bookings.delete_booking.title')} icon={<Trash2 />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_bookings.delete_booking.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_bookings.delete_booking.warning_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.secretary_bookings.delete_booking.warning.permanent')}</li>
                            <li>{t('sections.secretary_bookings.delete_booking.warning.payments')}</li>
                            <li>{t('sections.secretary_bookings.delete_booking.warning.confirmation')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_bookings.delete_booking.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_bookings.delete_booking.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.secretary_bookings.delete_booking.controller_method')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.secretary_bookings.delete_booking.js_function')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_cal/05-opt.png"
                            alt="Delete Booking Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_bookings.print_booking.title')} icon={<Printer />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_cal/06-opt.png"
                            alt="Print Booking Details"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_bookings.print_booking.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_bookings.print_booking.content_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_bookings.print_booking.content.booking_info')}:</strong> {t('sections.secretary_bookings.print_booking.content.booking_info_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.print_booking.content.patient_info')}:</strong> {t('sections.secretary_bookings.print_booking.content.patient_info_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.print_booking.content.doctor_info')}:</strong> {t('sections.secretary_bookings.print_booking.content.doctor_info_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.print_booking.content.payments')}:</strong> {t('sections.secretary_bookings.print_booking.content.payments_desc')}</li>
                            <li><strong>{t('sections.secretary_bookings.print_booking.content.related_bookings')}:</strong> {t('sections.secretary_bookings.print_booking.content.related_bookings_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_bookings.print_booking.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_bookings.print_booking.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.secretary_bookings.print_booking.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.secretary_bookings.print_booking.view_file')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_bookings.calendar_features.title')} icon={<Clock />}>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card title={t('sections.secretary_bookings.calendar_features.time_slots.title')} icon={Clock}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            {t('sections.secretary_bookings.calendar_features.time_slots.description')}
                        </p>
                        <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                            <li>{t('sections.secretary_bookings.calendar_features.time_slots.working_hours')}</li>
                            <li>{t('sections.secretary_bookings.calendar_features.time_slots.interval')}</li>
                            <li>{t('sections.secretary_bookings.calendar_features.time_slots.friday_holiday')}</li>
                        </ul>
                    </Card>
                    <Card title={t('sections.secretary_bookings.calendar_features.status_colors.title')} icon={Calendar}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            {t('sections.secretary_bookings.calendar_features.status_colors.description')}
                        </p>
                        <div className="space-y-2">
                            <div className="flex items-center gap-2">
                                <div className="w-4 h-4 rounded bg-blue-500"></div>
                                <span className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_bookings.calendar_features.status_colors.booked')}</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <div className="w-4 h-4 rounded bg-green-500"></div>
                                <span className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_bookings.calendar_features.status_colors.checked_in')}</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <div className="w-4 h-4 rounded bg-yellow-500"></div>
                                <span className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_bookings.calendar_features.status_colors.in_progress')}</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <div className="w-4 h-4 rounded bg-cyan-500"></div>
                                <span className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_bookings.calendar_features.status_colors.completed')}</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <div className="w-4 h-4 rounded bg-red-500"></div>
                                <span className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_bookings.calendar_features.status_colors.cancelled')}</span>
                            </div>
                        </div>
                    </Card>
                </div>
            </Section>

            <Section title={t('sections.secretary_bookings.api_endpoints.title')} icon={<FileText />}>
                <div className="space-y-4">
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/secretary/bookings/calendar?date={'{date}'}</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_bookings.api_endpoints.get_calendar')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 font-mono">POST</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/secretary/bookings</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_bookings.api_endpoints.create_booking')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-yellow-500/10 dark:bg-yellow-500/20 text-yellow-600 dark:text-yellow-400 font-mono">POST</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/secretary/bookings/{'{id}'}/update</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_bookings.api_endpoints.update_booking')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">POST</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/secretary/bookings/{'{id}'}/confirm</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_bookings.api_endpoints.confirm_attendance')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-red-500/10 dark:bg-red-500/20 text-red-600 dark:text-red-400 font-mono">DELETE</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/secretary/bookings/{'{id}'}</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_bookings.api_endpoints.delete_booking')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/secretary/bookings/{'{id}'}/details</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_bookings.api_endpoints.get_details')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/secretary/bookings/{'{id}'}/print</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_bookings.api_endpoints.print_booking')}</p>
                    </div>
                </div>
            </Section>
        </div>
    );
}

