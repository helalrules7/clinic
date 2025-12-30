import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Calendar, Clock, Filter, Plus, Trash2, ClipboardList, RefreshCw, Navigation, Zap, Database } from 'lucide-react';

export default function CalendarDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.calendar.title')}
                subtitle={t('sections.calendar.subtitle')}
            />

            <Section title={t('sections.calendar.overview.title')} icon={<Calendar />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.calendar.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.calendar.components.title')} icon={<Navigation />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.calendar.components.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.calendar.components.controls_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.calendar.components.controls.auto_refresh.title')}:</strong> {t('sections.calendar.components.controls.auto_refresh.description')}</li>
                            <li><strong>{t('sections.calendar.components.controls.go_to_date.title')}:</strong> {t('sections.calendar.components.controls.go_to_date.description')}</li>
                            <li><strong>{t('sections.calendar.components.controls.add_appointment.title')}:</strong> {t('sections.calendar.components.controls.add_appointment.description')}</li>
                            <li><strong>{t('sections.calendar.components.controls.navigation.title')}:</strong> {t('sections.calendar.components.controls.navigation.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.calendar.components.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.calendar.components.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.calendar.components.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.calendar.components.js')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/calendar/001-opt.png"
                            alt="Calendar Components and Controls"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.calendar.ui_components.title')} icon={<Calendar />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/calendar/002-opt.png"
                            alt="Calendar UI Components"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.calendar.ui_components.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.calendar.ui_components.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.calendar.ui_components.features.time_slots.title')}:</strong> {t('sections.calendar.ui_components.features.time_slots.description')}</li>
                            <li><strong>{t('sections.calendar.ui_components.features.appointments.title')}:</strong> {t('sections.calendar.ui_components.features.appointments.description')}</li>
                            <li><strong>{t('sections.calendar.ui_components.features.available.title')}:</strong> {t('sections.calendar.ui_components.features.available.description')}</li>
                            <li><strong>{t('sections.calendar.ui_components.features.progress.title')}:</strong> {t('sections.calendar.ui_components.features.progress.description')}</li>
                            <li><strong>{t('sections.calendar.ui_components.features.quick_actions.title')}:</strong> {t('sections.calendar.ui_components.features.quick_actions.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.calendar.ui_components.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.calendar.ui_components.render')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.calendar.ui_components.tooltips')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.calendar.ui_components.progress')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.calendar.medical_history.title')} icon={<ClipboardList />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.calendar.medical_history.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.calendar.medical_history.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.calendar.medical_history.features.quick_access.title')}:</strong> {t('sections.calendar.medical_history.features.quick_access.description')}</li>
                            <li><strong>{t('sections.calendar.medical_history.features.popover.title')}:</strong> {t('sections.calendar.medical_history.features.popover.description')}</li>
                            <li><strong>{t('sections.calendar.medical_history.features.content.title')}:</strong> {t('sections.calendar.medical_history.features.content.description')}</li>
                            <li><strong>{t('sections.calendar.medical_history.features.positioning.title')}:</strong> {t('sections.calendar.medical_history.features.positioning.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.calendar.medical_history.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.calendar.medical_history.function')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.calendar.medical_history.api')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.calendar.medical_history.rendering')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/calendar/003-opt.png"
                            alt="Medical History Quick Action"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.calendar.add_appointment.title')} icon={<Plus />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/calendar/004-opt.png"
                            alt="Add Appointment Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.calendar.add_appointment.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.calendar.add_appointment.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.calendar.add_appointment.features.patient_search.title')}:</strong> {t('sections.calendar.add_appointment.features.patient_search.description')}</li>
                            <li><strong>{t('sections.calendar.add_appointment.features.new_patient.title')}:</strong> {t('sections.calendar.add_appointment.features.new_patient.description')}</li>
                            <li><strong>{t('sections.calendar.add_appointment.features.auto_select.title')}:</strong> {t('sections.calendar.add_appointment.features.auto_select.description')}</li>
                            <li><strong>{t('sections.calendar.add_appointment.features.time_slots.title')}:</strong> {t('sections.calendar.add_appointment.features.time_slots.description')}</li>
                            <li><strong>{t('sections.calendar.add_appointment.features.visit_type.title')}:</strong> {t('sections.calendar.add_appointment.features.visit_type.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.calendar.add_appointment.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.calendar.add_appointment.function')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.calendar.add_appointment.api')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.calendar.add_appointment.validation')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.calendar.delete_appointment.title')} icon={<Trash2 />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.calendar.delete_appointment.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.calendar.delete_appointment.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.calendar.delete_appointment.features.warning.title')}:</strong> {t('sections.calendar.delete_appointment.features.warning.description')}</li>
                            <li><strong>{t('sections.calendar.delete_appointment.features.details.title')}:</strong> {t('sections.calendar.delete_appointment.features.details.description')}</li>
                            <li><strong>{t('sections.calendar.delete_appointment.features.consequences.title')}:</strong> {t('sections.calendar.delete_appointment.features.consequences.description')}</li>
                            <li><strong>{t('sections.calendar.delete_appointment.features.confirmation.title')}:</strong> {t('sections.calendar.delete_appointment.features.confirmation.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.calendar.delete_appointment.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.calendar.delete_appointment.function')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.calendar.delete_appointment.api')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.calendar.delete_appointment.cascade')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/calendar/005-opt.png"
                            alt="Delete Appointment Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.calendar.filters.title')} icon={<Filter />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.calendar.filters.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Clock className="text-blue-600 dark:text-blue-400" size={24} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.calendar.filters.time_ranges.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.calendar.filters.time_ranges.features.afternoon')}</li>
                                <li>{t('sections.calendar.filters.time_ranges.features.evening')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Filter className="text-green-600 dark:text-green-400" size={24} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.calendar.filters.availability.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.calendar.filters.availability.features.available')}</li>
                                <li>{t('sections.calendar.filters.availability.features.unavailable')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.calendar.filters.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.calendar.filters.function')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.calendar.filters.mobile')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.calendar.filters.state')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.calendar.auto_refresh.title')} icon={<RefreshCw />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.calendar.auto_refresh.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Zap className="text-yellow-600 dark:text-yellow-400" size={24} />
                                <h4 className="font-semibold text-yellow-600 dark:text-yellow-400">{t('sections.calendar.auto_refresh.features.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.calendar.auto_refresh.features.interval')}</li>
                                <li>{t('sections.calendar.auto_refresh.features.pause')}</li>
                                <li>{t('sections.calendar.auto_refresh.features.storage')}</li>
                                <li>{t('sections.calendar.auto_refresh.features.indicator')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Database className="text-blue-600 dark:text-blue-400" size={24} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.calendar.auto_refresh.storage.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.calendar.auto_refresh.storage.localstorage')}</li>
                                <li>{t('sections.calendar.auto_refresh.storage.default')}</li>
                                <li>{t('sections.calendar.auto_refresh.storage.persistent')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.calendar.auto_refresh.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.calendar.auto_refresh.function')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.calendar.auto_refresh.api')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.calendar.auto_refresh.check')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.calendar.api_endpoints.title')} icon={<Database />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.calendar.api_endpoints.description')}
                    </p>
                    
                    <div className="space-y-4">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-2">
                                <Database className="text-green-600 dark:text-green-400" size={20} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">GET /api/calendar</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.calendar.api_endpoints.get_calendar.description')}
                            </p>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded mb-2">
                                {t('sections.calendar.api_endpoints.get_calendar.params')}
                            </code>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded">
                                {t('sections.calendar.api_endpoints.get_calendar.response')}
                            </code>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-2">
                                <Plus className="text-blue-600 dark:text-blue-400" size={20} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">POST /api/appointments</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.calendar.api_endpoints.create_appointment.description')}
                            </p>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded mb-2">
                                {t('sections.calendar.api_endpoints.create_appointment.body')}
                            </code>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded">
                                {t('sections.calendar.api_endpoints.create_appointment.response')}
                            </code>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-2">
                                <Trash2 className="text-red-600 dark:text-red-400" size={20} />
                                <h4 className="font-semibold text-red-600 dark:text-red-400">DELETE /api/appointments/{'{id}'}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.calendar.api_endpoints.delete_appointment.description')}
                            </p>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded">
                                {t('sections.calendar.api_endpoints.delete_appointment.response')}
                            </code>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-2">
                                <ClipboardList className="text-purple-600 dark:text-purple-400" size={20} />
                                <h4 className="font-semibold text-purple-600 dark:text-purple-400">GET /api/patients/{'{id}'}/medical-history</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.calendar.api_endpoints.medical_history.description')}
                            </p>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded">
                                {t('sections.calendar.api_endpoints.medical_history.response')}
                            </code>
                        </Card>
                    </div>
                </div>
            </Section>
        </div>
    );
}

