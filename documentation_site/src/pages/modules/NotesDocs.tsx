import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { StickyNote, Plus, Trash2, Palette, Search, Bell, LayoutDashboard, MousePointerClick } from 'lucide-react';

export default function NotesDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.notes.title')}
                subtitle={t('sections.notes.subtitle')}
            />

            <Section title={t('sections.notes.overview.title')} icon={<StickyNote />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.notes.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.notes.notes_page.title')} icon={<StickyNote />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notes.notes_page.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.notes_page.components_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.notes.notes_page.components.toolbar')}</strong> - {t('sections.notes.notes_page.components.toolbar_desc')}</li>
                            <li><strong>{t('sections.notes.notes_page.components.color_picker')}</strong> - {t('sections.notes.notes_page.components.color_picker_desc')}</li>
                            <li><strong>{t('sections.notes.notes_page.components.add_button')}</strong> - {t('sections.notes.notes_page.components.add_button_desc')}</li>
                            <li><strong>{t('sections.notes.notes_page.components.delete_all')}</strong> - {t('sections.notes.notes_page.components.delete_all_desc')}</li>
                            <li><strong>{t('sections.notes.notes_page.components.container')}</strong> - {t('sections.notes.notes_page.components.container_desc')}</li>
                            <li><strong>{t('sections.notes.notes_page.components.reset_button')}</strong> - {t('sections.notes.notes_page.components.reset_button_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.notes_page.container_resize_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.notes.notes_page.container_resize.resize_handle')}</li>
                            <li>{t('sections.notes.notes_page.container_resize.persistent')}</li>
                            <li>{t('sections.notes.notes_page.container_resize.reset_function')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notes.notes_page.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.notes.notes_page.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.notes.notes_page.route')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.notes.notes_page.view_file')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/notes/01-opt.png"
                            alt={t('sections.notes.notes_page.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notes.drug_autocomplete.title')} icon={<Search />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notes.drug_autocomplete.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.drug_autocomplete.how_to_use_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.notes.drug_autocomplete.how_to_use.trigger')}</li>
                            <li>{t('sections.notes.drug_autocomplete.how_to_use.minimum_chars')}</li>
                            <li>{t('sections.notes.drug_autocomplete.how_to_use.autocomplete_dropdown')}</li>
                            <li>{t('sections.notes.drug_autocomplete.how_to_use.drug_badge')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.drug_autocomplete.drug_badge_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.notes.drug_autocomplete.drug_badge.styled')}</li>
                            <li>{t('sections.notes.drug_autocomplete.drug_badge.clickable')}</li>
                            <li>{t('sections.notes.drug_autocomplete.drug_badge.popover')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notes.drug_autocomplete.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.notes.drug_autocomplete.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.notes.drug_autocomplete.js_function')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/notes/02-opt.png"
                            alt={t('sections.notes.drug_autocomplete.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notes.patient_autocomplete.title')} icon={<Search />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notes.patient_autocomplete.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.patient_autocomplete.how_to_use_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.notes.patient_autocomplete.how_to_use.trigger')}</li>
                            <li>{t('sections.notes.patient_autocomplete.how_to_use.minimum_chars')}</li>
                            <li>{t('sections.notes.patient_autocomplete.how_to_use.autocomplete_dropdown')}</li>
                            <li>{t('sections.notes.patient_autocomplete.how_to_use.patient_link')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.patient_autocomplete.patient_link_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.notes.patient_autocomplete.patient_link.styled')}</li>
                            <li>{t('sections.notes.patient_autocomplete.patient_link.clickable')}</li>
                            <li>{t('sections.notes.patient_autocomplete.patient_link.redirect')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notes.patient_autocomplete.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.notes.patient_autocomplete.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.notes.patient_autocomplete.js_function')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/notes/03-opt.png"
                            alt={t('sections.notes.patient_autocomplete.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notes.appointment_autocomplete.title')} icon={<Search />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notes.appointment_autocomplete.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.appointment_autocomplete.how_to_use_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.notes.appointment_autocomplete.how_to_use.trigger')}</li>
                            <li>{t('sections.notes.appointment_autocomplete.how_to_use.search_methods')}</li>
                            <li>{t('sections.notes.appointment_autocomplete.how_to_use.autocomplete_dropdown')}</li>
                            <li>{t('sections.notes.appointment_autocomplete.how_to_use.appointment_link')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.appointment_autocomplete.search_methods_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li><strong>{t('sections.notes.appointment_autocomplete.search_methods.id')}</strong> - {t('sections.notes.appointment_autocomplete.search_methods.id_desc')}</li>
                            <li><strong>{t('sections.notes.appointment_autocomplete.search_methods.date')}</strong> - {t('sections.notes.appointment_autocomplete.search_methods.date_desc')}</li>
                            <li><strong>{t('sections.notes.appointment_autocomplete.search_methods.patient_name')}</strong> - {t('sections.notes.appointment_autocomplete.search_methods.patient_name_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notes.appointment_autocomplete.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.notes.appointment_autocomplete.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.notes.appointment_autocomplete.js_function')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/notes/04-opt.png"
                            alt={t('sections.notes.appointment_autocomplete.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notes.clickable_items.title')} icon={<MousePointerClick />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notes.clickable_items.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.clickable_items.item_types_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.notes.clickable_items.item_types.patient_links')}</strong> - {t('sections.notes.clickable_items.item_types.patient_links_desc')}</li>
                            <li><strong>{t('sections.notes.clickable_items.item_types.appointment_links')}</strong> - {t('sections.notes.clickable_items.item_types.appointment_links_desc')}</li>
                            <li><strong>{t('sections.notes.clickable_items.item_types.drug_badges')}</strong> - {t('sections.notes.clickable_items.item_types.drug_badges_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.clickable_items.styling_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.notes.clickable_items.styling.visual_distinction')}</li>
                            <li>{t('sections.notes.clickable_items.styling.icons')}</li>
                            <li>{t('sections.notes.clickable_items.styling.hover_effects')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notes.clickable_items.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.notes.clickable_items.css_classes')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.notes.clickable_items.js_handlers')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/notes/05-opt.png"
                            alt={t('sections.notes.clickable_items.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notes.drug_popover.title')} icon={<Search />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notes.drug_popover.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.drug_popover.popover_content_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.notes.drug_popover.popover_content.drug_name')}</strong> - {t('sections.notes.drug_popover.popover_content.drug_name_desc')}</li>
                            <li><strong>{t('sections.notes.drug_popover.popover_content.active_ingredient')}</strong> - {t('sections.notes.drug_popover.popover_content.active_ingredient_desc')}</li>
                            <li><strong>{t('sections.notes.drug_popover.popover_content.company')}</strong> - {t('sections.notes.drug_popover.popover_content.company_desc')}</li>
                            <li><strong>{t('sections.notes.drug_popover.popover_content.price')}</strong> - {t('sections.notes.drug_popover.popover_content.price_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.drug_popover.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.notes.drug_popover.features.click_to_show')}</li>
                            <li>{t('sections.notes.drug_popover.features.positioned_near')}</li>
                            <li>{t('sections.notes.drug_popover.features.close_on_click')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notes.drug_popover.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.notes.drug_popover.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono break-words whitespace-pre-wrap">{t('sections.notes.drug_popover.js_function')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/notes/06-opt.png"
                            alt={t('sections.notes.drug_popover.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notes.note_customization.title')} icon={<Palette />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notes.note_customization.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.note_customization.color_options_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.notes.note_customization.color_options.white')}</strong> - {t('sections.notes.note_customization.color_options.white_desc')}</li>
                            <li><strong>{t('sections.notes.note_customization.color_options.red')}</strong> - {t('sections.notes.note_customization.color_options.red_desc')}</li>
                            <li><strong>{t('sections.notes.note_customization.color_options.black')}</strong> - {t('sections.notes.note_customization.color_options.black_desc')}</li>
                            <li><strong>{t('sections.notes.note_customization.color_options.dodgerblue')}</strong> - {t('sections.notes.note_customization.color_options.dodgerblue_desc')}</li>
                            <li><strong>{t('sections.notes.note_customization.color_options.warning')}</strong> - {t('sections.notes.note_customization.color_options.warning_desc')}</li>
                            <li><strong>{t('sections.notes.note_customization.color_options.success')}</strong> - {t('sections.notes.note_customization.color_options.success_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.note_customization.text_color_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.notes.note_customization.text_color.automatic')}</li>
                            <li>{t('sections.notes.note_customization.text_color.brightness')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notes.note_customization.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.notes.note_customization.js_function')}</code>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/notes/07-opt.png"
                            alt={t('sections.notes.note_customization.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notes.create_alert.title')} icon={<Bell />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notes.create_alert.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.create_alert.how_it_works_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.notes.create_alert.how_it_works.alert_button')}</li>
                            <li>{t('sections.notes.create_alert.how_it_works.date_time_picker')}</li>
                            <li>{t('sections.notes.create_alert.how_it_works.html_format')}</li>
                            <li>{t('sections.notes.create_alert.how_it_works.patient_detection')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.create_alert.patient_detection_title')}</h3>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notes.create_alert.patient_detection_description')}
                        </p>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notes.create_alert.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.notes.create_alert.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.notes.create_alert.js_function')}</code>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                {t('sections.notes.create_alert.learn_more')} <Link to="/doctors-pages/alerts" className="text-blue-600 dark:text-blue-400 hover:underline font-semibold">{t('sections.notes.create_alert.alerts_link')}</Link>
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/notes/08-opt.png"
                            alt={t('sections.notes.create_alert.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notes.dashboard_integration.title')} icon={<LayoutDashboard />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notes.dashboard_integration.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.dashboard_integration.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li>{t('sections.notes.dashboard_integration.features.automatic_sync')}</li>
                            <li>{t('sections.notes.dashboard_integration.features.drag_drop')}</li>
                            <li>{t('sections.notes.dashboard_integration.features.resize')}</li>
                            <li>{t('sections.notes.dashboard_integration.features.color_preservation')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notes.dashboard_integration.note_widget_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.notes.dashboard_integration.note_widget.same_functionality')}</li>
                            <li>{t('sections.notes.dashboard_integration.note_widget.position_persistence')}</li>
                            <li>{t('sections.notes.dashboard_integration.note_widget.size_persistence')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto mt-4">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notes.dashboard_integration.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.notes.dashboard_integration.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.notes.dashboard_integration.js_function')}</code>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                {t('sections.notes.dashboard_integration.learn_more')} <Link to="/dashboards/doctor" className="text-blue-600 dark:text-blue-400 hover:underline font-semibold">{t('sections.notes.dashboard_integration.dashboard_link')}</Link>
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center justify-center">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/notes/09-opt.png"
                            alt={t('sections.notes.dashboard_integration.image_alt')}
                            className="rounded-lg shadow-lg max-w-full h-auto"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notes.api_endpoints.title')} icon={<StickyNote />}>
                <div className="space-y-4">
                    <p className="text-gray-700 dark:text-gray-300">
                        {t('sections.notes.api_endpoints.description')}
                    </p>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <StickyNote className="text-blue-600 dark:text-blue-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.notes.api_endpoints.list.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.notes.api_endpoints.list.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.notes.api_endpoints.list.endpoint')}</code>
                            </div>
                        </Card>
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <Plus className="text-teal-600 dark:text-teal-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.notes.api_endpoints.create.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.notes.api_endpoints.create.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.notes.api_endpoints.create.endpoint')}</code>
                            </div>
                        </Card>
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <Palette className="text-amber-600 dark:text-amber-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.notes.api_endpoints.update.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.notes.api_endpoints.update.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.notes.api_endpoints.update.endpoint')}</code>
                            </div>
                        </Card>
                        <Card>
                            <div className="flex items-center gap-3 mb-3">
                                <Trash2 className="text-red-600 dark:text-red-400" size={20} />
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    {t('sections.notes.api_endpoints.delete.title')}
                                </h3>
                            </div>
                            <p className="text-gray-700 dark:text-gray-300 text-sm mb-2">
                                {t('sections.notes.api_endpoints.delete.description')}
                            </p>
                            <div className="bg-gray-100 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                                <code className="block text-xs text-green-600 dark:text-green-400 font-mono break-words whitespace-pre-wrap">{t('sections.notes.api_endpoints.delete.endpoint')}</code>
                            </div>
                        </Card>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notes.api_endpoints.routes_title')}</h4>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.notes.api_endpoints.page_route')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.notes.api_endpoints.api_routes')}</code>
                    </div>
                </div>
            </Section>
        </div>
    );
}

