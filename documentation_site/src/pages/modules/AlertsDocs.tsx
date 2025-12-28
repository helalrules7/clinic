import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Bell, Plus, Edit, Trash2, Clock, User, Smartphone, Calendar } from 'lucide-react';

export default function AlertsDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.alerts.title')}
                subtitle={t('sections.alerts.subtitle')}
            />

            <Section title={t('sections.alerts.overview.title')} icon={<Bell />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.alerts.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.alerts.alerts_page.title')} icon={<Bell />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.alerts.alerts_page.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.alerts.alerts_page.components_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.alerts.alerts_page.components.header')}</strong> - {t('sections.alerts.alerts_page.components.header_desc')}</li>
                            <li><strong>{t('sections.alerts.alerts_page.components.create_button')}</strong> - {t('sections.alerts.alerts_page.components.create_button_desc')}</li>
                            <li><strong>{t('sections.alerts.alerts_page.components.table')}</strong> - {t('sections.alerts.alerts_page.components.table_desc')}</li>
                            <li><strong>{t('sections.alerts.alerts_page.components.pagination')}</strong> - {t('sections.alerts.alerts_page.components.pagination_desc')}</li>
                            <li><strong>{t('sections.alerts.alerts_page.components.bulk_actions')}</strong> - {t('sections.alerts.alerts_page.components.bulk_actions_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.alerts.alerts_page.table_columns_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li><strong>{t('sections.alerts.alerts_page.table_columns.message')}</strong> - {t('sections.alerts.alerts_page.table_columns.message_desc')}</li>
                            <li><strong>{t('sections.alerts.alerts_page.table_columns.date_time')}</strong> - {t('sections.alerts.alerts_page.table_columns.date_time_desc')}</li>
                            <li><strong>{t('sections.alerts.alerts_page.table_columns.patient')}</strong> - {t('sections.alerts.alerts_page.table_columns.patient_desc')}</li>
                            <li><strong>{t('sections.alerts.alerts_page.table_columns.repeat')}</strong> - {t('sections.alerts.alerts_page.table_columns.repeat_desc')}</li>
                            <li><strong>{t('sections.alerts.alerts_page.table_columns.status')}</strong> - {t('sections.alerts.alerts_page.table_columns.status_desc')}</li>
                            <li><strong>{t('sections.alerts.alerts_page.table_columns.actions')}</strong> - {t('sections.alerts.alerts_page.table_columns.actions_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.alerts.alerts_page.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.alerts.alerts_page.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.alerts.alerts_page.route')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.alerts.alerts_page.view_file')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/alerts/01-opt.png"
                            alt={t('sections.alerts.alerts_page.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.alerts.create_modal.title')} icon={<Plus />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.alerts.create_modal.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.alerts.create_modal.form_fields_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.alerts.create_modal.form_fields.patient_search')}</strong> - {t('sections.alerts.create_modal.form_fields.patient_search_desc')}</li>
                            <li><strong>{t('sections.alerts.create_modal.form_fields.message')}</strong> - {t('sections.alerts.create_modal.form_fields.message_desc')}</li>
                            <li><strong>{t('sections.alerts.create_modal.form_fields.date')}</strong> - {t('sections.alerts.create_modal.form_fields.date_desc')}</li>
                            <li><strong>{t('sections.alerts.create_modal.form_fields.time')}</strong> - {t('sections.alerts.create_modal.form_fields.time_desc')}</li>
                            <li><strong>{t('sections.alerts.create_modal.form_fields.repeat_count')}</strong> - {t('sections.alerts.create_modal.form_fields.repeat_count_desc')}</li>
                            <li><strong>{t('sections.alerts.create_modal.form_fields.repeat_interval')}</strong> - {t('sections.alerts.create_modal.form_fields.repeat_interval_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.alerts.create_modal.patient_search_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.alerts.create_modal.patient_search.autocomplete')}</li>
                            <li>{t('sections.alerts.create_modal.patient_search.debounce')}</li>
                            <li>{t('sections.alerts.create_modal.patient_search.patient_info')}</li>
                            <li>{t('sections.alerts.create_modal.patient_search.change_patient')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.alerts.create_modal.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.alerts.create_modal.js_file')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.alerts.create_modal.api_endpoint')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/alerts/02-opt.png"
                            alt={t('sections.alerts.create_modal.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.alerts.time_picker.title')} icon={<Clock />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.alerts.time_picker.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.alerts.time_picker.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.alerts.time_picker.features.material_ui')}</li>
                            <li>{t('sections.alerts.time_picker.features.theme_sync')}</li>
                            <li>{t('sections.alerts.time_picker.features.format')}</li>
                            <li>{t('sections.alerts.time_picker.features.conversion')}</li>
                            <li>{t('sections.alerts.time_picker.features.desktop_mode')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.alerts.time_picker.functionality_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.alerts.time_picker.functionality.clock_interface')}</li>
                            <li>{t('sections.alerts.time_picker.functionality.real_time_update')}</li>
                            <li>{t('sections.alerts.time_picker.functionality.hidden_input')}</li>
                            <li>{t('sections.alerts.time_picker.functionality.auto_init')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.alerts.time_picker.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.alerts.time_picker.library')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.alerts.time_picker.js_functions')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/alerts/03-opt.png"
                            alt={t('sections.alerts.time_picker.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.alerts.alert_actions.title')} icon={<Edit />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.alerts.alert_actions.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.alerts.alert_actions.actions_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.alerts.alert_actions.actions.view_patient')}</strong> - {t('sections.alerts.alert_actions.actions.view_patient_desc')}</li>
                            <li><strong>{t('sections.alerts.alert_actions.actions.toggle_status')}</strong> - {t('sections.alerts.alert_actions.actions.toggle_status_desc')}</li>
                            <li><strong>{t('sections.alerts.alert_actions.actions.edit')}</strong> - {t('sections.alerts.alert_actions.actions.edit_desc')}</li>
                            <li><strong>{t('sections.alerts.alert_actions.actions.delete')}</strong> - {t('sections.alerts.alert_actions.actions.delete_desc')}</li>
                            <li><strong>{t('sections.alerts.alert_actions.actions.quick_dismiss')}</strong> - {t('sections.alerts.alert_actions.actions.quick_dismiss_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.alerts.alert_actions.status_badges_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li><strong>{t('sections.alerts.alert_actions.status_badges.active')}</strong> - {t('sections.alerts.alert_actions.status_badges.active_desc')}</li>
                            <li><strong>{t('sections.alerts.alert_actions.status_badges.past_due')}</strong> - {t('sections.alerts.alert_actions.status_badges.past_due_desc')}</li>
                            <li><strong>{t('sections.alerts.alert_actions.status_badges.inactive')}</strong> - {t('sections.alerts.alert_actions.status_badges.inactive_desc')}</li>
                            <li><strong>{t('sections.alerts.alert_actions.status_badges.dismissed')}</strong> - {t('sections.alerts.alert_actions.status_badges.dismissed_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.alerts.alert_actions.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.alerts.alert_actions.api_endpoints')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/alerts/04-opt.png"
                            alt={t('sections.alerts.alert_actions.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.alerts.alert_toast.title')} icon={<Bell />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.alerts.alert_toast.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.alerts.alert_toast.toast_features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.alerts.alert_toast.toast_features.real_time')}</strong> - {t('sections.alerts.alert_toast.toast_features.real_time_desc')}</li>
                            <li><strong>{t('sections.alerts.alert_toast.toast_features.notification_sound')}</strong> - {t('sections.alerts.alert_toast.toast_features.notification_sound_desc')}</li>
                            <li><strong>{t('sections.alerts.alert_toast.toast_features.content')}</strong> - {t('sections.alerts.alert_toast.toast_features.content_desc')}</li>
                            <li><strong>{t('sections.alerts.alert_toast.toast_features.actions')}</strong> - {t('sections.alerts.alert_toast.toast_features.actions_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.alerts.alert_toast.toast_buttons_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li><strong>{t('sections.alerts.alert_toast.toast_buttons.view_patient')}</strong> - {t('sections.alerts.alert_toast.toast_buttons.view_patient_desc')}</li>
                            <li><strong>{t('sections.alerts.alert_toast.toast_buttons.snooze')}</strong> - {t('sections.alerts.alert_toast.toast_buttons.snooze_desc')}</li>
                            <li><strong>{t('sections.alerts.alert_toast.toast_buttons.close')}</strong> - {t('sections.alerts.alert_toast.toast_buttons.close_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.alerts.alert_toast.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.alerts.alert_toast.polling_interval')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.alerts.alert_toast.js_functions')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/alerts/06-opt.png"
                            alt={t('sections.alerts.alert_toast.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.alerts.push_notifications.title')} icon={<Smartphone />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.alerts.push_notifications.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.alerts.push_notifications.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.alerts.push_notifications.features.browser_notifications')}</li>
                            <li>{t('sections.alerts.push_notifications.features.background')}</li>
                            <li>{t('sections.alerts.push_notifications.features.patient_redirect')}</li>
                            <li>{t('sections.alerts.push_notifications.features.multiple_browsers')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.alerts.push_notifications.notification_content_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.alerts.push_notifications.notification_content.title')}</li>
                            <li>{t('sections.alerts.push_notifications.notification_content.body')}</li>
                            <li>{t('sections.alerts.push_notifications.notification_content.icon')}</li>
                            <li>{t('sections.alerts.push_notifications.notification_content.actions')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.alerts.push_notifications.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.alerts.push_notifications.service_worker')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.alerts.push_notifications.vapid')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/alerts/05-opt.png"
                            alt={t('sections.alerts.push_notifications.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.alerts.patient_profile.title')} icon={<User />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.alerts.patient_profile.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.alerts.patient_profile.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.alerts.patient_profile.features.quick_create')}</li>
                            <li>{t('sections.alerts.patient_profile.features.auto_select')}</li>
                            <li>{t('sections.alerts.patient_profile.features.patient_context')}</li>
                            <li>{t('sections.alerts.patient_profile.features.view_alerts')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.alerts.patient_profile.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.alerts.patient_profile.js_function')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/alerts/07-opt.png"
                            alt={t('sections.alerts.patient_profile.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.alerts.automatic_alerts.title')} icon={<Calendar />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.alerts.automatic_alerts.description')}
                    </p>
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">{t('sections.alerts.automatic_alerts.how_it_works_title')}</h3>
                    <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                        <li>{t('sections.alerts.automatic_alerts.how_it_works.appointment_created')}</li>
                        <li>{t('sections.alerts.automatic_alerts.how_it_works.alert_timing')}</li>
                        <li>{t('sections.alerts.automatic_alerts.how_it_works.alert_message')}</li>
                        <li>{t('sections.alerts.automatic_alerts.how_it_works.followup_appointments')}</li>
                    </ul>
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">{t('sections.alerts.automatic_alerts.settings_title')}</h3>
                    <p className="text-gray-700 dark:text-gray-300 mb-4">
                        {t('sections.alerts.automatic_alerts.settings_description')}
                    </p>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.alerts.automatic_alerts.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.alerts.automatic_alerts.setting_key')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.alerts.automatic_alerts.controller_logic')}</code>
                    </div>
                    <div className="flex items-center justify-center mt-6">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/alerts/08-opt.png"
                            alt={t('sections.alerts.automatic_alerts.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.alerts.api_endpoints.title')} icon={<Bell />}>
                <div className="space-y-4">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.alerts.api_endpoints.description')}
                    </p>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <Bell className="text-blue-600 dark:text-blue-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.alerts.api_endpoints.list.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.alerts.api_endpoints.list.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.alerts.api_endpoints.list.endpoint')}</code>
                            </div>
                        </Card>
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <Plus className="text-teal-600 dark:text-teal-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.alerts.api_endpoints.create.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.alerts.api_endpoints.create.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.alerts.api_endpoints.create.endpoint')}</code>
                            </div>
                        </Card>
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <Edit className="text-amber-600 dark:text-amber-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.alerts.api_endpoints.update.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.alerts.api_endpoints.update.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.alerts.api_endpoints.update.endpoint')}</code>
                            </div>
                        </Card>
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <Trash2 className="text-red-600 dark:text-red-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.alerts.api_endpoints.delete.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.alerts.api_endpoints.delete.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.alerts.api_endpoints.delete.endpoint')}</code>
                            </div>
                        </Card>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.alerts.api_endpoints.routes_title')}</h4>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.alerts.api_endpoints.page_route')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.alerts.api_endpoints.api_routes')}</code>
                    </div>
                </div>
            </Section>
        </div>
    );
}

