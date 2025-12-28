import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { User, Edit, Lock, Image, Sidebar, LayoutGrid, Filter, Badge } from 'lucide-react';

export default function ProfileDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.profile.title')}
                subtitle={t('sections.profile.subtitle')}
            />

            <Section title={t('sections.profile.overview.title')} icon={<User />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.profile.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.profile.profile_page.title')} icon={<User />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.profile.profile_page.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.profile.profile_page.components_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.profile.profile_page.components.header')}</strong> - {t('sections.profile.profile_page.components.header_desc')}</li>
                            <li><strong>{t('sections.profile.profile_page.components.profile_info')}</strong> - {t('sections.profile.profile_page.components.profile_info_desc')}</li>
                            <li><strong>{t('sections.profile.profile_page.components.change_password')}</strong> - {t('sections.profile.profile_page.components.change_password_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.profile.profile_page.profile_info_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.profile.profile_page.profile_info.full_name')}</strong> - {t('sections.profile.profile_page.profile_info.full_name_desc')}</li>
                            <li><strong>{t('sections.profile.profile_page.profile_info.email')}</strong> - {t('sections.profile.profile_page.profile_info.email_desc')}</li>
                            <li><strong>{t('sections.profile.profile_page.profile_info.phone')}</strong> - {t('sections.profile.profile_page.profile_info.phone_desc')}</li>
                            <li><strong>{t('sections.profile.profile_page.profile_info.role')}</strong> - {t('sections.profile.profile_page.profile_info.role_desc')}</li>
                            <li><strong>{t('sections.profile.profile_page.profile_info.display_name')}</strong> - {t('sections.profile.profile_page.profile_info.display_name_desc')}</li>
                            <li><strong>{t('sections.profile.profile_page.profile_info.specialty')}</strong> - {t('sections.profile.profile_page.profile_info.specialty_desc')}</li>
                            <li><strong>{t('sections.profile.profile_page.profile_info.last_login')}</strong> - {t('sections.profile.profile_page.profile_info.last_login_desc')}</li>
                            <li><strong>{t('sections.profile.profile_page.profile_info.account_status')}</strong> - {t('sections.profile.profile_page.profile_info.account_status_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.profile.profile_page.password_section_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li><strong>{t('sections.profile.profile_page.password_section.new_password')}</strong> - {t('sections.profile.profile_page.password_section.new_password_desc')}</li>
                            <li><strong>{t('sections.profile.profile_page.password_section.confirm_password')}</strong> - {t('sections.profile.profile_page.password_section.confirm_password_desc')}</li>
                            <li><strong>{t('sections.profile.profile_page.password_section.strength_indicator')}</strong> - {t('sections.profile.profile_page.password_section.strength_indicator_desc')}</li>
                            <li><strong>{t('sections.profile.profile_page.password_section.requirements')}</strong> - {t('sections.profile.profile_page.password_section.requirements_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.profile.profile_page.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.profile.profile_page.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.profile.profile_page.route')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.profile.profile_page.view_file')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/profile/01-opt.png"
                            alt={t('sections.profile.profile_page.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.profile.edit_profile_modal.title')} icon={<Edit />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.profile.edit_profile_modal.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.profile.edit_profile_modal.form_fields_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.profile.edit_profile_modal.form_fields.profile_image')}</strong> - {t('sections.profile.edit_profile_modal.form_fields.profile_image_desc')}</li>
                            <li><strong>{t('sections.profile.edit_profile_modal.form_fields.full_name')}</strong> - {t('sections.profile.edit_profile_modal.form_fields.full_name_desc')}</li>
                            <li><strong>{t('sections.profile.edit_profile_modal.form_fields.email')}</strong> - {t('sections.profile.edit_profile_modal.form_fields.email_desc')}</li>
                            <li><strong>{t('sections.profile.edit_profile_modal.form_fields.phone')}</strong> - {t('sections.profile.edit_profile_modal.form_fields.phone_desc')}</li>
                            <li><strong>{t('sections.profile.edit_profile_modal.form_fields.display_name')}</strong> - {t('sections.profile.edit_profile_modal.form_fields.display_name_desc')}</li>
                            <li><strong>{t('sections.profile.edit_profile_modal.form_fields.specialty')}</strong> - {t('sections.profile.edit_profile_modal.form_fields.specialty_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.profile.edit_profile_modal.image_upload_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.profile.edit_profile_modal.image_upload.click_to_upload')}</li>
                            <li>{t('sections.profile.edit_profile_modal.image_upload.preview')}</li>
                            <li>{t('sections.profile.edit_profile_modal.image_upload.file_validation')}</li>
                            <li>{t('sections.profile.edit_profile_modal.image_upload.old_image_deletion')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.profile.edit_profile_modal.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.profile.edit_profile_modal.js_file')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.profile.edit_profile_modal.api_endpoint')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/profile/02-opt.png"
                            alt={t('sections.profile.edit_profile_modal.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.profile.sidebar_integration.title')} icon={<Sidebar />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.profile.sidebar_integration.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.profile.sidebar_integration.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.profile.sidebar_integration.features.avatar_display')}</li>
                            <li>{t('sections.profile.sidebar_integration.features.hover_preview')}</li>
                            <li>{t('sections.profile.sidebar_integration.features.auto_update')}</li>
                            <li>{t('sections.profile.sidebar_integration.features.fallback')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.profile.sidebar_integration.hover_preview_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.profile.sidebar_integration.hover_preview.large_image')}</li>
                            <li>{t('sections.profile.sidebar_integration.hover_preview.positioned')}</li>
                            <li>{t('sections.profile.sidebar_integration.hover_preview.theme_support')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.profile.sidebar_integration.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.profile.sidebar_integration.css_classes')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.profile.sidebar_integration.js_function')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/profile/03-opt.png"
                            alt={t('sections.profile.sidebar_integration.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.profile.dock_integration.title')} icon={<LayoutGrid />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.profile.dock_integration.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.profile.dock_integration.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.profile.dock_integration.features.profile_item')}</li>
                            <li>{t('sections.profile.dock_integration.features.circular_avatar')}</li>
                            <li>{t('sections.profile.dock_integration.features.hover_effects')}</li>
                            <li>{t('sections.profile.dock_integration.features.auto_update')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.profile.dock_integration.styling_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.profile.dock_integration.styling.circular')}</li>
                            <li>{t('sections.profile.dock_integration.styling.border')}</li>
                            <li>{t('sections.profile.dock_integration.styling.scale')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.profile.dock_integration.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.profile.dock_integration.css_class')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.profile.dock_integration.layout_file')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/profile/04-opt.png"
                            alt={t('sections.profile.dock_integration.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.profile.patients_filter.title')} icon={<Filter />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.profile.patients_filter.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.profile.patients_filter.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.profile.patients_filter.features.filter_buttons')}</li>
                            <li>{t('sections.profile.patients_filter.features.avatar_display')}</li>
                            <li>{t('sections.profile.patients_filter.features.color_coding')}</li>
                            <li>{t('sections.profile.patients_filter.features.active_indicator')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.profile.patients_filter.filter_functionality_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.profile.patients_filter.filter_functionality.all_doctors')}</li>
                            <li>{t('sections.profile.patients_filter.filter_functionality.specific_doctor')}</li>
                            <li>{t('sections.profile.patients_filter.filter_functionality.current_filter')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.profile.patients_filter.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.profile.patients_filter.css_class')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.profile.patients_filter.view_file')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/profile/05-opt.png"
                            alt={t('sections.profile.patients_filter.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.profile.treating_doctor_badge.title')} icon={<Badge />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.profile.treating_doctor_badge.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.profile.treating_doctor_badge.locations_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.profile.treating_doctor_badge.locations.patient_profile')}</strong> - {t('sections.profile.treating_doctor_badge.locations.patient_profile_desc')}</li>
                            <li><strong>{t('sections.profile.treating_doctor_badge.locations.appointment_page')}</strong> - {t('sections.profile.treating_doctor_badge.locations.appointment_page_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.profile.treating_doctor_badge.badge_content_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.profile.treating_doctor_badge.badge_content.avatar')}</li>
                            <li>{t('sections.profile.treating_doctor_badge.badge_content.display_name')}</li>
                            <li>{t('sections.profile.treating_doctor_badge.badge_content.fallback')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.profile.treating_doctor_badge.styling_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.profile.treating_doctor_badge.styling.gradient')}</li>
                            <li>{t('sections.profile.treating_doctor_badge.styling.pulse_animation')}</li>
                            <li>{t('sections.profile.treating_doctor_badge.styling.icon_animation')}</li>
                            <li>{t('sections.profile.treating_doctor_badge.styling.responsive')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.profile.treating_doctor_badge.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.profile.treating_doctor_badge.css_class')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.profile.treating_doctor_badge.view_files')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/profile/06-opt.png"
                            alt={t('sections.profile.treating_doctor_badge.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.profile.password_validation.title')} icon={<Lock />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.profile.password_validation.description')}
                    </p>
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">{t('sections.profile.password_validation.requirements_title')}</h3>
                    <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                        <li><strong>{t('sections.profile.password_validation.requirements.length')}</strong> - {t('sections.profile.password_validation.requirements.length_desc')}</li>
                        <li><strong>{t('sections.profile.password_validation.requirements.uppercase')}</strong> - {t('sections.profile.password_validation.requirements.uppercase_desc')}</li>
                        <li><strong>{t('sections.profile.password_validation.requirements.lowercase')}</strong> - {t('sections.profile.password_validation.requirements.lowercase_desc')}</li>
                        <li><strong>{t('sections.profile.password_validation.requirements.number')}</strong> - {t('sections.profile.password_validation.requirements.number_desc')}</li>
                    </ul>
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">{t('sections.profile.password_validation.strength_levels_title')}</h3>
                    <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                        <li><strong>{t('sections.profile.password_validation.strength_levels.very_weak')}</strong> - {t('sections.profile.password_validation.strength_levels.very_weak_desc')}</li>
                        <li><strong>{t('sections.profile.password_validation.strength_levels.weak')}</strong> - {t('sections.profile.password_validation.strength_levels.weak_desc')}</li>
                        <li><strong>{t('sections.profile.password_validation.strength_levels.fair')}</strong> - {t('sections.profile.password_validation.strength_levels.fair_desc')}</li>
                        <li><strong>{t('sections.profile.password_validation.strength_levels.good')}</strong> - {t('sections.profile.password_validation.strength_levels.good_desc')}</li>
                        <li><strong>{t('sections.profile.password_validation.strength_levels.strong')}</strong> - {t('sections.profile.password_validation.strength_levels.strong_desc')}</li>
                    </ul>
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">{t('sections.profile.password_validation.live_validation_title')}</h3>
                    <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                        <li>{t('sections.profile.password_validation.live_validation.real_time')}</li>
                        <li>{t('sections.profile.password_validation.live_validation.visual_indicators')}</li>
                        <li>{t('sections.profile.password_validation.live_validation.match_validation')}</li>
                    </ul>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.profile.password_validation.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.profile.password_validation.js_functions')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.profile.api_endpoints.title')} icon={<User />}>
                <div className="space-y-4">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.profile.api_endpoints.description')}
                    </p>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <User className="text-blue-600 dark:text-blue-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.profile.api_endpoints.get.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.profile.api_endpoints.get.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.profile.api_endpoints.get.endpoint')}</code>
                            </div>
                        </Card>
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <Edit className="text-teal-600 dark:text-teal-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.profile.api_endpoints.update.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.profile.api_endpoints.update.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.profile.api_endpoints.update.endpoint')}</code>
                            </div>
                        </Card>
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <Lock className="text-amber-600 dark:text-amber-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.profile.api_endpoints.change_password.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.profile.api_endpoints.change_password.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.profile.api_endpoints.change_password.endpoint')}</code>
                            </div>
                        </Card>
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <Image className="text-purple-600 dark:text-purple-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.profile.api_endpoints.update_field.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.profile.api_endpoints.update_field.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.profile.api_endpoints.update_field.endpoint')}</code>
                            </div>
                        </Card>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.profile.api_endpoints.routes_title')}</h4>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.profile.api_endpoints.page_route')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.profile.api_endpoints.api_routes')}</code>
                    </div>
                </div>
            </Section>
        </div>
    );
}

