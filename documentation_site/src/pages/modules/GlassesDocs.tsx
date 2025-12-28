import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Eye, Search, Grid3x3, Loader, FileText, User, Printer } from 'lucide-react';

export default function GlassesDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.glasses.title')}
                subtitle={t('sections.glasses.subtitle')}
            />

            <Section title={t('sections.glasses.overview.title')} icon={<Eye />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.glasses.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.glasses.page_structure.title')} icon={<Grid3x3 />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.glasses.page_structure.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.glasses.page_structure.components_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.glasses.page_structure.components.patient_filter')}</strong> - {t('sections.glasses.page_structure.components.patient_filter_desc')}</li>
                            <li><strong>{t('sections.glasses.page_structure.components.gallery_grid')}</strong> - {t('sections.glasses.page_structure.components.gallery_grid_desc')}</li>
                            <li><strong>{t('sections.glasses.page_structure.components.load_more')}</strong> - {t('sections.glasses.page_structure.components.load_more_desc')}</li>
                            <li><strong>{t('sections.glasses.page_structure.components.empty_state')}</strong> - {t('sections.glasses.page_structure.components.empty_state_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.glasses.page_structure.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.glasses.page_structure.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.glasses.page_structure.route')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.glasses.page_structure.js_file')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/glasses/01-opt.png"
                            alt={t('sections.glasses.page_structure.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.glasses.patient_search.title')} icon={<Search />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.glasses.patient_search.description')}
                    </p>
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">{t('sections.glasses.patient_search.features_title')}</h3>
                    <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                        <li>{t('sections.glasses.patient_search.features.autocomplete')}</li>
                        <li>{t('sections.glasses.patient_search.features.keyboard_navigation')}</li>
                        <li>{t('sections.glasses.patient_search.features.patient_info')}</li>
                        <li>{t('sections.glasses.patient_search.features.filter_badge')}</li>
                        <li>{t('sections.glasses.patient_search.features.clear_filter')}</li>
                    </ul>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.glasses.patient_search.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.glasses.patient_search.api_endpoint')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.glasses.patient_search.js_functions')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.glasses.prescription_cards.title')} icon={<Grid3x3 />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.glasses.prescription_cards.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.glasses.prescription_cards.card_structure_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.glasses.prescription_cards.card_structure.thumbnail')}</strong> - {t('sections.glasses.prescription_cards.card_structure.thumbnail_desc')}</li>
                            <li><strong>{t('sections.glasses.prescription_cards.card_structure.overlay')}</strong> - {t('sections.glasses.prescription_cards.card_structure.overlay_desc')}</li>
                            <li><strong>{t('sections.glasses.prescription_cards.card_structure.badge')}</strong> - {t('sections.glasses.prescription_cards.card_structure.badge_desc')}</li>
                            <li><strong>{t('sections.glasses.prescription_cards.card_structure.click_action')}</strong> - {t('sections.glasses.prescription_cards.card_structure.click_action_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.glasses.prescription_cards.data_displayed_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.glasses.prescription_cards.data_displayed.patient_name')}</li>
                            <li>{t('sections.glasses.prescription_cards.data_displayed.prescription_count')}</li>
                            <li>{t('sections.glasses.prescription_cards.data_displayed.last_prescription')}</li>
                            <li>{t('sections.glasses.prescription_cards.data_displayed.lens_type')}</li>
                            <li>{t('sections.glasses.prescription_cards.data_displayed.eye_measurements')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.glasses.prescription_cards.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.glasses.prescription_cards.js_function')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/glasses/01-opt.png"
                            alt={t('sections.glasses.prescription_cards.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.glasses.pagination.title')} icon={<Loader />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.glasses.pagination.description')}
                    </p>
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">{t('sections.glasses.pagination.features_title')}</h3>
                    <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                        <li>{t('sections.glasses.pagination.features.lazy_loading')}</li>
                        <li>{t('sections.glasses.pagination.features.load_more_button')}</li>
                        <li>{t('sections.glasses.pagination.features.loading_indicator')}</li>
                        <li>{t('sections.glasses.pagination.features.per_page')}</li>
                    </ul>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.glasses.pagination.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.glasses.pagination.api_endpoint')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.glasses.pagination.js_functions')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.glasses.pagination.pagination_response')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.glasses.preview_modal.title')} icon={<Eye />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.glasses.preview_modal.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.glasses.preview_modal.modal_structure_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.glasses.preview_modal.modal_structure.header')}</strong> - {t('sections.glasses.preview_modal.modal_structure.header_desc')}</li>
                            <li><strong>{t('sections.glasses.preview_modal.modal_structure.body')}</strong> - {t('sections.glasses.preview_modal.modal_structure.body_desc')}</li>
                            <li><strong>{t('sections.glasses.preview_modal.modal_structure.footer')}</strong> - {t('sections.glasses.preview_modal.modal_structure.footer_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.glasses.preview_modal.prescription_display_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.glasses.preview_modal.prescription_display.prescription_header')}</li>
                            <li>{t('sections.glasses.preview_modal.prescription_display.eye_measurements')}</li>
                            <li>{t('sections.glasses.preview_modal.prescription_display.right_eye')}</li>
                            <li>{t('sections.glasses.preview_modal.prescription_display.left_eye')}</li>
                            <li>{t('sections.glasses.preview_modal.prescription_display.pd_box')}</li>
                            <li>{t('sections.glasses.preview_modal.prescription_display.comments')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.glasses.preview_modal.measurements_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.glasses.preview_modal.measurements.sphere')}</li>
                            <li>{t('sections.glasses.preview_modal.measurements.cylinder')}</li>
                            <li>{t('sections.glasses.preview_modal.measurements.axis')}</li>
                            <li>{t('sections.glasses.preview_modal.measurements.near_sphere')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.glasses.preview_modal.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.glasses.preview_modal.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.glasses.preview_modal.js_functions')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/glasses/02-opt.png"
                            alt={t('sections.glasses.preview_modal.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.glasses.print_functionality.title')} icon={<Printer />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.glasses.print_functionality.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.glasses.print_functionality.print_button_title')}</h3>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.glasses.print_functionality.print_button_description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.glasses.print_functionality.prescription_content_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.glasses.print_functionality.prescription_content.header')}</strong> - {t('sections.glasses.print_functionality.prescription_content.header_desc')}</li>
                            <li><strong>{t('sections.glasses.print_functionality.prescription_content.patient_info')}</strong> - {t('sections.glasses.print_functionality.prescription_content.patient_info_desc')}</li>
                            <li><strong>{t('sections.glasses.print_functionality.prescription_content.eye_measurements')}</strong> - {t('sections.glasses.print_functionality.prescription_content.eye_measurements_desc')}</li>
                            <li><strong>{t('sections.glasses.print_functionality.prescription_content.lens_type')}</strong> - {t('sections.glasses.print_functionality.prescription_content.lens_type_desc')}</li>
                            <li><strong>{t('sections.glasses.print_functionality.prescription_content.pd_section')}</strong> - {t('sections.glasses.print_functionality.prescription_content.pd_section_desc')}</li>
                            <li><strong>{t('sections.glasses.print_functionality.prescription_content.comments')}</strong> - {t('sections.glasses.print_functionality.prescription_content.comments_desc')}</li>
                            <li><strong>{t('sections.glasses.print_functionality.prescription_content.validity')}</strong> - {t('sections.glasses.print_functionality.prescription_content.validity_desc')}</li>
                            <li><strong>{t('sections.glasses.print_functionality.prescription_content.footer')}</strong> - {t('sections.glasses.print_functionality.prescription_content.footer_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.glasses.print_functionality.print_features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.glasses.print_functionality.print_features.auto_print')}</li>
                            <li>{t('sections.glasses.print_functionality.print_features.a4_format')}</li>
                            <li>{t('sections.glasses.print_functionality.print_features.watermark')}</li>
                            <li>{t('sections.glasses.print_functionality.print_features.bilingual')}</li>
                            <li>{t('sections.glasses.print_functionality.print_features.professional_layout')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.glasses.print_functionality.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.glasses.print_functionality.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.glasses.print_functionality.route')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.glasses.print_functionality.view_file')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/glasses/03-opt.png"
                            alt={t('sections.glasses.print_functionality.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.glasses.api_endpoints.title')} icon={<FileText />}>
                <div className="space-y-4">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.glasses.api_endpoints.description')}
                    </p>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <Grid3x3 className="text-blue-600 dark:text-blue-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.glasses.api_endpoints.list.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.glasses.api_endpoints.list.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.glasses.api_endpoints.list.endpoint')}</code>
                            </div>
                        </Card>
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <User className="text-indigo-600 dark:text-indigo-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.glasses.api_endpoints.patient.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.glasses.api_endpoints.patient.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.glasses.api_endpoints.patient.endpoint')}</code>
                            </div>
                        </Card>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.glasses.api_endpoints.routes_title')}</h4>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.glasses.api_endpoints.page_route')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.glasses.api_endpoints.api_routes')}</code>
                    </div>
                </div>
            </Section>
        </div>
    );
}

