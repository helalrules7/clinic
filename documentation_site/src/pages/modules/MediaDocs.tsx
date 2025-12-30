import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Image, Search, Grid3x3, Loader, Eye, User, FileText } from 'lucide-react';

export default function MediaDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.media.title')}
                subtitle={t('sections.media.subtitle')}
            />

            <Section title={t('sections.media.overview.title')} icon={<Image />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.media.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.media.page_structure.title')} icon={<Grid3x3 />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.media.page_structure.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.media.page_structure.components_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.media.page_structure.components.patient_filter')}</strong> - {t('sections.media.page_structure.components.patient_filter_desc')}</li>
                            <li><strong>{t('sections.media.page_structure.components.gallery_grid')}</strong> - {t('sections.media.page_structure.components.gallery_grid_desc')}</li>
                            <li><strong>{t('sections.media.page_structure.components.load_more')}</strong> - {t('sections.media.page_structure.components.load_more_desc')}</li>
                            <li><strong>{t('sections.media.page_structure.components.empty_state')}</strong> - {t('sections.media.page_structure.components.empty_state_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.media.page_structure.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.media.page_structure.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.media.page_structure.route')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.media.page_structure.js_file')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/media/01-opt.png"
                            alt={t('sections.media.page_structure.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.media.patient_search.title')} icon={<Search />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.media.patient_search.description')}
                    </p>
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">{t('sections.media.patient_search.features_title')}</h3>
                    <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                        <li>{t('sections.media.patient_search.features.autocomplete')}</li>
                        <li>{t('sections.media.patient_search.features.keyboard_navigation')}</li>
                        <li>{t('sections.media.patient_search.features.patient_info')}</li>
                        <li>{t('sections.media.patient_search.features.filter_badge')}</li>
                        <li>{t('sections.media.patient_search.features.clear_filter')}</li>
                    </ul>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.media.patient_search.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.media.patient_search.api_endpoint')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.media.patient_search.js_functions')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.media.media_cards.title')} icon={<Grid3x3 />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.media.media_cards.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.media.media_cards.card_structure_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.media.media_cards.card_structure.thumbnail')}</strong> - {t('sections.media.media_cards.card_structure.thumbnail_desc')}</li>
                            <li><strong>{t('sections.media.media_cards.card_structure.overlay')}</strong> - {t('sections.media.media_cards.card_structure.overlay_desc')}</li>
                            <li><strong>{t('sections.media.media_cards.card_structure.badge')}</strong> - {t('sections.media.media_cards.card_structure.badge_desc')}</li>
                            <li><strong>{t('sections.media.media_cards.card_structure.click_action')}</strong> - {t('sections.media.media_cards.card_structure.click_action_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.media.media_cards.data_displayed_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.media.media_cards.data_displayed.patient_name')}</li>
                            <li>{t('sections.media.media_cards.data_displayed.image_count')}</li>
                            <li>{t('sections.media.media_cards.data_displayed.first_image')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.media.media_cards.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.media.media_cards.js_function')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/media/01-opt.png"
                            alt={t('sections.media.media_cards.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.media.pagination.title')} icon={<Loader />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.media.pagination.description')}
                    </p>
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">{t('sections.media.pagination.features_title')}</h3>
                    <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                        <li>{t('sections.media.pagination.features.lazy_loading')}</li>
                        <li>{t('sections.media.pagination.features.load_more_button')}</li>
                        <li>{t('sections.media.pagination.features.loading_indicator')}</li>
                        <li>{t('sections.media.pagination.features.per_page')}</li>
                    </ul>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.media.pagination.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.media.pagination.api_endpoint')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.media.pagination.js_functions')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.media.pagination.pagination_response')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.media.image_modal.title')} icon={<Eye />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.media.image_modal.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.media.image_modal.modal_structure_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.media.image_modal.modal_structure.header')}</strong> - {t('sections.media.image_modal.modal_structure.header_desc')}</li>
                            <li><strong>{t('sections.media.image_modal.modal_structure.body')}</strong> - {t('sections.media.image_modal.modal_structure.body_desc')}</li>
                            <li><strong>{t('sections.media.image_modal.modal_structure.footer')}</strong> - {t('sections.media.image_modal.modal_structure.footer_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.media.image_modal.carousel_features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.media.image_modal.carousel_features.bootstrap_carousel')}</li>
                            <li>{t('sections.media.image_modal.carousel_features.navigation_arrows')}</li>
                            <li>{t('sections.media.image_modal.carousel_features.image_counter')}</li>
                            <li>{t('sections.media.image_modal.carousel_features.image_name')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.media.image_modal.footer_actions_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.media.image_modal.footer_actions.view_patient')}</li>
                            <li>{t('sections.media.image_modal.footer_actions.view_appointment')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.media.image_modal.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.media.image_modal.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.media.image_modal.js_functions')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/media/02-opt.png"
                            alt={t('sections.media.image_modal.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.media.api_endpoints.title')} icon={<FileText />}>
                <div className="space-y-4">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.media.api_endpoints.description')}
                    </p>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <Grid3x3 className="text-blue-600 dark:text-blue-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.media.api_endpoints.list.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.media.api_endpoints.list.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.media.api_endpoints.list.endpoint')}</code>
                            </div>
                        </Card>
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <User className="text-teal-600 dark:text-teal-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.media.api_endpoints.patient.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.media.api_endpoints.patient.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.media.api_endpoints.patient.endpoint')}</code>
                            </div>
                        </Card>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.media.api_endpoints.routes_title')}</h4>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.media.api_endpoints.page_route')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.media.api_endpoints.api_routes')}</code>
                    </div>
                </div>
            </Section>
        </div>
    );
}

