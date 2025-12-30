import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Pill, Search, Grid3x3, Loader, Eye, User, FileText, Printer } from 'lucide-react';

export default function MedicationsDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.medications.title')}
                subtitle={t('sections.medications.subtitle')}
            />

            <Section title={t('sections.medications.overview.title')} icon={<Pill />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.medications.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.medications.page_structure.title')} icon={<Grid3x3 />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.medications.page_structure.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.medications.page_structure.components_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.medications.page_structure.components.patient_filter')}</strong> - {t('sections.medications.page_structure.components.patient_filter_desc')}</li>
                            <li><strong>{t('sections.medications.page_structure.components.gallery_grid')}</strong> - {t('sections.medications.page_structure.components.gallery_grid_desc')}</li>
                            <li><strong>{t('sections.medications.page_structure.components.load_more')}</strong> - {t('sections.medications.page_structure.components.load_more_desc')}</li>
                            <li><strong>{t('sections.medications.page_structure.components.empty_state')}</strong> - {t('sections.medications.page_structure.components.empty_state_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.medications.page_structure.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.medications.page_structure.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.medications.page_structure.route')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.medications.page_structure.js_file')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/medication/01-opt.png"
                            alt={t('sections.medications.page_structure.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.medications.patient_search.title')} icon={<Search />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.medications.patient_search.description')}
                    </p>
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">{t('sections.medications.patient_search.features_title')}</h3>
                    <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                        <li>{t('sections.medications.patient_search.features.autocomplete')}</li>
                        <li>{t('sections.medications.patient_search.features.keyboard_navigation')}</li>
                        <li>{t('sections.medications.patient_search.features.patient_info')}</li>
                        <li>{t('sections.medications.patient_search.features.filter_badge')}</li>
                        <li>{t('sections.medications.patient_search.features.clear_filter')}</li>
                    </ul>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.medications.patient_search.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.medications.patient_search.api_endpoint')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.medications.patient_search.js_functions')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.medications.prescription_cards.title')} icon={<Grid3x3 />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.medications.prescription_cards.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.medications.prescription_cards.card_structure_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.medications.prescription_cards.card_structure.thumbnail')}</strong> - {t('sections.medications.prescription_cards.card_structure.thumbnail_desc')}</li>
                            <li><strong>{t('sections.medications.prescription_cards.card_structure.overlay')}</strong> - {t('sections.medications.prescription_cards.card_structure.overlay_desc')}</li>
                            <li><strong>{t('sections.medications.prescription_cards.card_structure.badge')}</strong> - {t('sections.medications.prescription_cards.card_structure.badge_desc')}</li>
                            <li><strong>{t('sections.medications.prescription_cards.card_structure.click_action')}</strong> - {t('sections.medications.prescription_cards.card_structure.click_action_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.medications.prescription_cards.data_displayed_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.medications.prescription_cards.data_displayed.patient_name')}</li>
                            <li>{t('sections.medications.prescription_cards.data_displayed.appointment_count')}</li>
                            <li>{t('sections.medications.prescription_cards.data_displayed.last_prescription')}</li>
                            <li>{t('sections.medications.prescription_cards.data_displayed.latest_drug')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.medications.prescription_cards.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.medications.prescription_cards.js_function')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/medication/01-opt.png"
                            alt={t('sections.medications.prescription_cards.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.medications.pagination.title')} icon={<Loader />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.medications.pagination.description')}
                    </p>
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">{t('sections.medications.pagination.features_title')}</h3>
                    <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                        <li>{t('sections.medications.pagination.features.lazy_loading')}</li>
                        <li>{t('sections.medications.pagination.features.load_more_button')}</li>
                        <li>{t('sections.medications.pagination.features.loading_indicator')}</li>
                        <li>{t('sections.medications.pagination.features.per_page')}</li>
                    </ul>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.medications.pagination.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.medications.pagination.api_endpoint')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.medications.pagination.js_functions')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.medications.pagination.pagination_response')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.medications.preview_modal.title')} icon={<Eye />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.medications.preview_modal.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.medications.preview_modal.modal_structure_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.medications.preview_modal.modal_structure.header')}</strong> - {t('sections.medications.preview_modal.modal_structure.header_desc')}</li>
                            <li><strong>{t('sections.medications.preview_modal.modal_structure.body')}</strong> - {t('sections.medications.preview_modal.modal_structure.body_desc')}</li>
                            <li><strong>{t('sections.medications.preview_modal.modal_structure.footer')}</strong> - {t('sections.medications.preview_modal.modal_structure.footer_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.medications.preview_modal.appointment_groups_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.medications.preview_modal.appointment_groups.accordion')}</li>
                            <li>{t('sections.medications.preview_modal.appointment_groups.expanded')}</li>
                            <li>{t('sections.medications.preview_modal.appointment_groups.appointment_info')}</li>
                            <li>{t('sections.medications.preview_modal.appointment_groups.actions')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.medications.preview_modal.prescription_items_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.medications.preview_modal.prescription_items.drug_name')}</li>
                            <li>{t('sections.medications.preview_modal.prescription_items.details')}</li>
                            <li>{t('sections.medications.preview_modal.prescription_items.notes')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.medications.preview_modal.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.medications.preview_modal.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.medications.preview_modal.js_functions')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/medication/02-opt.png"
                            alt={t('sections.medications.preview_modal.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.medications.print_functionality.title')} icon={<Printer />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.medications.print_functionality.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.medications.print_functionality.print_button_title')}</h3>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.medications.print_functionality.print_button_description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.medications.print_functionality.prescription_content_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.medications.print_functionality.prescription_content.header')}</strong> - {t('sections.medications.print_functionality.prescription_content.header_desc')}</li>
                            <li><strong>{t('sections.medications.print_functionality.prescription_content.patient_info')}</strong> - {t('sections.medications.print_functionality.prescription_content.patient_info_desc')}</li>
                            <li><strong>{t('sections.medications.print_functionality.prescription_content.medications_list')}</strong> - {t('sections.medications.print_functionality.prescription_content.medications_list_desc')}</li>
                            <li><strong>{t('sections.medications.print_functionality.prescription_content.medication_item')}</strong> - {t('sections.medications.print_functionality.prescription_content.medication_item_desc')}</li>
                            <li><strong>{t('sections.medications.print_functionality.prescription_content.notes')}</strong> - {t('sections.medications.print_functionality.prescription_content.notes_desc')}</li>
                            <li><strong>{t('sections.medications.print_functionality.prescription_content.footer')}</strong> - {t('sections.medications.print_functionality.prescription_content.footer_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.medications.print_functionality.print_features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.medications.print_functionality.print_features.auto_print')}</li>
                            <li>{t('sections.medications.print_functionality.print_features.a4_format')}</li>
                            <li>{t('sections.medications.print_functionality.print_features.watermark')}</li>
                            <li>{t('sections.medications.print_functionality.print_features.bilingual')}</li>
                            <li>{t('sections.medications.print_functionality.print_features.professional_layout')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.medications.print_functionality.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.medications.print_functionality.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.medications.print_functionality.route')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.medications.print_functionality.view_file')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/medication/03-opt.png"
                            alt={t('sections.medications.print_functionality.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.medications.api_endpoints.title')} icon={<FileText />}>
                <div className="space-y-4">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.medications.api_endpoints.description')}
                    </p>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <Grid3x3 className="text-blue-600 dark:text-blue-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.medications.api_endpoints.list.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.medications.api_endpoints.list.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.medications.api_endpoints.list.endpoint')}</code>
                            </div>
                        </Card>
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <User className="text-purple-600 dark:text-purple-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.medications.api_endpoints.patient.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.medications.api_endpoints.patient.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.medications.api_endpoints.patient.endpoint')}</code>
                            </div>
                        </Card>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.medications.api_endpoints.routes_title')}</h4>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.medications.api_endpoints.page_route')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.medications.api_endpoints.api_routes')}</code>
                    </div>
                </div>
            </Section>
        </div>
    );
}

