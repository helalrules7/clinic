import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Users, UserPlus, Search, Filter, Table, Edit, Phone, Keyboard, ArrowUpDown, Calendar, Eye, Trash2, Database } from 'lucide-react';

export default function PatientsDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.patients.title')}
                subtitle={t('sections.patients.subtitle')}
            />

            <Section title={t('sections.patients.overview.title')} icon={<Users />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.patients.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.patients.stats_cards.title')} icon={<Database />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patients.stats_cards.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patients.stats_cards.cards_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.patients.stats_cards.cards.total.title')}:</strong> {t('sections.patients.stats_cards.cards.total.description')}</li>
                            <li><strong>{t('sections.patients.stats_cards.cards.visits.title')}:</strong> {t('sections.patients.stats_cards.cards.visits.description')}</li>
                            <li><strong>{t('sections.patients.stats_cards.cards.recent.title')}:</strong> {t('sections.patients.stats_cards.cards.recent.description')}</li>
                            <li><strong>{t('sections.patients.stats_cards.cards.new.title')}:</strong> {t('sections.patients.stats_cards.cards.new.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.patients.stats_cards.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.patients.stats_cards.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.patients.stats_cards.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.patients.stats_cards.update')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/patients/01-opt.png"
                            alt="Statistics Cards"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patients.keyboard_shortcuts.title')} icon={<Keyboard />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.patients.keyboard_shortcuts.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <UserPlus className="text-green-600 dark:text-green-400" size={24} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.patients.keyboard_shortcuts.add_patient.title')}</h4>
                            </div>
                            <div className="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <div className="flex items-center gap-2">
                                    <kbd className="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs">N</kbd>
                                    <span>or</span>
                                    <kbd className="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs" lang="ar">ى</kbd>
                                    <span>or</span>
                                    <kbd className="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs">Ctrl+N</kbd>
                                </div>
                                <p className="text-xs text-gray-500 dark:text-gray-500 mt-2">
                                    {t('sections.patients.keyboard_shortcuts.add_patient.description')}
                                </p>
                            </div>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Search className="text-blue-600 dark:text-blue-400" size={24} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.patients.keyboard_shortcuts.search.title')}</h4>
                            </div>
                            <div className="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <div className="flex items-center gap-2">
                                    <kbd className="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs">F</kbd>
                                    <span>or</span>
                                    <kbd className="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs" lang="ar">ب</kbd>
                                </div>
                                <p className="text-xs text-gray-500 dark:text-gray-500 mt-2">
                                    {t('sections.patients.keyboard_shortcuts.search.description')}
                                </p>
                            </div>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Search className="text-purple-600 dark:text-purple-400" size={24} />
                                <h4 className="font-semibold text-purple-600 dark:text-purple-400">{t('sections.patients.keyboard_shortcuts.quick_search.title')}</h4>
                            </div>
                            <div className="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <div className="flex items-center gap-2">
                                    <kbd className="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs">Ctrl+F</kbd>
                                    <span>or</span>
                                    <kbd className="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs">Cmd+F</kbd>
                                </div>
                                <p className="text-xs text-gray-500 dark:text-gray-500 mt-2">
                                    {t('sections.patients.keyboard_shortcuts.quick_search.description')}
                                </p>
                            </div>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Keyboard className="text-red-600 dark:text-red-400" size={24} />
                                <h4 className="font-semibold text-red-600 dark:text-red-400">{t('sections.patients.keyboard_shortcuts.close.title')}</h4>
                            </div>
                            <div className="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <div className="flex items-center gap-2">
                                    <kbd className="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs">Esc</kbd>
                                </div>
                                <p className="text-xs text-gray-500 dark:text-gray-500 mt-2">
                                    {t('sections.patients.keyboard_shortcuts.close.description')}
                                </p>
                            </div>
                        </Card>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patients.add_patient.title')} icon={<UserPlus />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/patients/02-opt.png"
                            alt="Add Patient Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patients.add_patient.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patients.add_patient.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.patients.add_patient.features.basic.title')}:</strong> {t('sections.patients.add_patient.features.basic.description')}</li>
                            <li><strong>{t('sections.patients.add_patient.features.contact.title')}:</strong> {t('sections.patients.add_patient.features.contact.description')}</li>
                            <li><strong>{t('sections.patients.add_patient.features.age.title')}:</strong> {t('sections.patients.add_patient.features.age.description')}</li>
                            <li><strong>{t('sections.patients.add_patient.features.validation.title')}:</strong> {t('sections.patients.add_patient.features.validation.description')}</li>
                            <li><strong>{t('sections.patients.add_patient.features.shortcut.title')}:</strong> {t('sections.patients.add_patient.features.shortcut.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.patients.add_patient.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.patients.add_patient.api')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.patients.add_patient.validation')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.patients.add_patient.auto_focus')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patients.search_modal.title')} icon={<Search />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patients.search_modal.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patients.search_modal.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.patients.search_modal.features.real_time.title')}:</strong> {t('sections.patients.search_modal.features.real_time.description')}</li>
                            <li><strong>{t('sections.patients.search_modal.features.debounce.title')}:</strong> {t('sections.patients.search_modal.features.debounce.description')}</li>
                            <li><strong>{t('sections.patients.search_modal.features.highlight.title')}:</strong> {t('sections.patients.search_modal.features.highlight.description')}</li>
                            <li><strong>{t('sections.patients.search_modal.features.actions.title')}:</strong> {t('sections.patients.search_modal.features.actions.description')}</li>
                            <li><strong>{t('sections.patients.search_modal.features.shortcut.title')}:</strong> {t('sections.patients.search_modal.features.shortcut.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.patients.search_modal.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.patients.search_modal.api')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.patients.search_modal.cancel')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.patients.search_modal.display')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/patients/03-opt.png"
                            alt="Search Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patients.doctor_filter.title')} icon={<Filter />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/patients/04-opt.png"
                            alt="Doctor Filter"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patients.doctor_filter.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patients.doctor_filter.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.patients.doctor_filter.features.all.title')}:</strong> {t('sections.patients.doctor_filter.features.all.description')}</li>
                            <li><strong>{t('sections.patients.doctor_filter.features.individual.title')}:</strong> {t('sections.patients.doctor_filter.features.individual.description')}</li>
                            <li><strong>{t('sections.patients.doctor_filter.features.avatar.title')}:</strong> {t('sections.patients.doctor_filter.features.avatar.description')}</li>
                            <li><strong>{t('sections.patients.doctor_filter.features.combine.title')}:</strong> {t('sections.patients.doctor_filter.features.combine.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.patients.doctor_filter.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.patients.doctor_filter.function')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.patients.doctor_filter.state')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.patients.doctor_filter.apply')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patients.patients_table.title')} icon={<Table />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patients.patients_table.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patients.patients_table.columns_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.patients.patients_table.columns.patient_info.title')}:</strong> {t('sections.patients.patients_table.columns.patient_info.description')}</li>
                            <li><strong>{t('sections.patients.patients_table.columns.contact.title')}:</strong> {t('sections.patients.patients_table.columns.contact.description')}</li>
                            <li><strong>{t('sections.patients.patients_table.columns.gender.title')}:</strong> {t('sections.patients.patients_table.columns.gender.description')}</li>
                            <li><strong>{t('sections.patients.patients_table.columns.age.title')}:</strong> {t('sections.patients.patients_table.columns.age.description')}</li>
                            <li><strong>{t('sections.patients.patients_table.columns.doctors.title')}:</strong> {t('sections.patients.patients_table.columns.doctors.description')}</li>
                            <li><strong>{t('sections.patients.patients_table.columns.last_visit.title')}:</strong> {t('sections.patients.patients_table.columns.last_visit.description')}</li>
                            <li><strong>{t('sections.patients.patients_table.columns.total_visits.title')}:</strong> {t('sections.patients.patients_table.columns.total_visits.description')}</li>
                            <li><strong>{t('sections.patients.patients_table.columns.actions.title')}:</strong> {t('sections.patients.patients_table.columns.actions.description')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2 mt-4">{t('sections.patients.patients_table.sorting_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.patients.patients_table.sorting.ascending.title')}:</strong> {t('sections.patients.patients_table.sorting.ascending.description')}</li>
                            <li><strong>{t('sections.patients.patients_table.sorting.descending.title')}:</strong> {t('sections.patients.patients_table.sorting.descending.description')}</li>
                            <li><strong>{t('sections.patients.patients_table.sorting.clear.title')}:</strong> {t('sections.patients.patients_table.sorting.clear.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.patients.patients_table.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.patients.patients_table.function')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.patients.patients_table.api')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.patients.patients_table.render')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/patients/05-opt.png"
                            alt="Patients Table"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patients.quick_actions.title')} icon={<Eye />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.patients.quick_actions.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Eye className="text-yellow-600 dark:text-yellow-400" size={20} />
                                <h4 className="font-semibold text-yellow-600 dark:text-yellow-400">{t('sections.patients.quick_actions.view.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.patients.quick_actions.view.description')}
                            </p>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Edit className="text-blue-600 dark:text-blue-400" size={20} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.patients.quick_actions.edit.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.patients.quick_actions.edit.description')}
                            </p>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Calendar className="text-green-600 dark:text-green-400" size={20} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.patients.quick_actions.book.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.patients.quick_actions.book.description')}
                            </p>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Trash2 className="text-red-600 dark:text-red-400" size={20} />
                                <h4 className="font-semibold text-red-600 dark:text-red-400">{t('sections.patients.quick_actions.delete.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.patients.quick_actions.delete.description')}
                            </p>
                        </Card>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patients.edit_modal.title')} icon={<Edit />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/patients/06-opt.png"
                            alt="Edit Patient Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patients.edit_modal.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patients.edit_modal.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.patients.edit_modal.features.dynamic.title')}:</strong> {t('sections.patients.edit_modal.features.dynamic.description')}</li>
                            <li><strong>{t('sections.patients.edit_modal.features.sections.title')}:</strong> {t('sections.patients.edit_modal.features.sections.description')}</li>
                            <li><strong>{t('sections.patients.edit_modal.features.reset.title')}:</strong> {t('sections.patients.edit_modal.features.reset.description')}</li>
                            <li><strong>{t('sections.patients.edit_modal.features.validation.title')}:</strong> {t('sections.patients.edit_modal.features.validation.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.patients.edit_modal.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.patients.edit_modal.function')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.patients.edit_modal.api')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.patients.edit_modal.method')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patients.filter_popovers.title')} icon={<Filter />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.patients.filter_popovers.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/doctors_pages/patients/08-opt.png"
                                alt="Gender Filter"
                                className="w-full h-auto hover:scale-105 transition-transform duration-500"
                            />
                        </div>
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/doctors_pages/patients/07-opt.png"
                                alt="Age Filter"
                                className="w-full h-auto hover:scale-105 transition-transform duration-500"
                            />
                        </div>
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/doctors_pages/patients/09-opt.png"
                                alt="Last Visit Filter"
                                className="w-full h-auto hover:scale-105 transition-transform duration-500"
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patients.filter_popovers.gender.title')}</h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                {t('sections.patients.filter_popovers.gender.description')}
                            </p>
                            <ul className="list-disc list-inside space-y-1 text-xs text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.patients.filter_popovers.gender.options.male')}</li>
                                <li>{t('sections.patients.filter_popovers.gender.options.female')}</li>
                                <li>{t('sections.patients.filter_popovers.gender.options.clear')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patients.filter_popovers.age.title')}</h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                {t('sections.patients.filter_popovers.age.description')}
                            </p>
                            <ul className="list-disc list-inside space-y-1 text-xs text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.patients.filter_popovers.age.options.min')}</li>
                                <li>{t('sections.patients.filter_popovers.age.options.max')}</li>
                                <li>{t('sections.patients.filter_popovers.age.options.range')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patients.filter_popovers.last_visit.title')}</h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                {t('sections.patients.filter_popovers.last_visit.description')}
                            </p>
                            <ul className="list-disc list-inside space-y-1 text-xs text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.patients.filter_popovers.last_visit.options.from')}</li>
                                <li>{t('sections.patients.filter_popovers.last_visit.options.to')}</li>
                                <li>{t('sections.patients.filter_popovers.last_visit.options.range')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.patients.filter_popovers.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.patients.filter_popovers.functions')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.patients.filter_popovers.state')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.patients.filter_popovers.combine')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patients.phone_tooltip.title')} icon={<Phone />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.patients.phone_tooltip.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.patients.phone_tooltip.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.patients.phone_tooltip.features.hover.title')}:</strong> {t('sections.patients.phone_tooltip.features.hover.description')}</li>
                            <li><strong>{t('sections.patients.phone_tooltip.features.call.title')}:</strong> {t('sections.patients.phone_tooltip.features.call.description')}</li>
                            <li><strong>{t('sections.patients.phone_tooltip.features.whatsapp.title')}:</strong> {t('sections.patients.phone_tooltip.features.whatsapp.description')}</li>
                            <li><strong>{t('sections.patients.phone_tooltip.features.primary.title')}:</strong> {t('sections.patients.phone_tooltip.features.primary.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.patients.phone_tooltip.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.patients.phone_tooltip.function')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.patients.phone_tooltip.event')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.patients.phone_tooltip.format')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/patients/10-opt.png"
                            alt="Phone Tooltip"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patients.quick_search.title')} icon={<Search />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.patients.quick_search.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Search className="text-blue-600 dark:text-blue-400" size={24} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.patients.quick_search.features.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.patients.quick_search.features.local')}</li>
                                <li>{t('sections.patients.quick_search.features.debounce')}</li>
                                <li>{t('sections.patients.quick_search.features.fields')}</li>
                                <li>{t('sections.patients.quick_search.features.minimum')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <ArrowUpDown className="text-green-600 dark:text-green-400" size={24} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.patients.quick_search.pagination.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.patients.quick_search.pagination.items')}</li>
                                <li>{t('sections.patients.quick_search.pagination.navigation')}</li>
                                <li>{t('sections.patients.quick_search.pagination.keyboard')}</li>
                                <li>{t('sections.patients.quick_search.pagination.info')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.patients.quick_search.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.patients.quick_search.function')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.patients.quick_search.state')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.patients.quick_search.render')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.patients.api_endpoints.title')} icon={<Database />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.patients.api_endpoints.description')}
                    </p>
                    
                    <div className="space-y-4">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-2">
                                <Database className="text-green-600 dark:text-green-400" size={20} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">GET /api/patients</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.patients.api_endpoints.get_all.description')}
                            </p>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded">
                                {t('sections.patients.api_endpoints.get_all.response')}
                            </code>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-2">
                                <Search className="text-blue-600 dark:text-blue-400" size={20} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">GET /api/patients/search</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.patients.api_endpoints.search.description')}
                            </p>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded mb-2">
                                {t('sections.patients.api_endpoints.search.params')}
                            </code>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded">
                                {t('sections.patients.api_endpoints.search.response')}
                            </code>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-2">
                                <UserPlus className="text-green-600 dark:text-green-400" size={20} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">POST /api/patients</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.patients.api_endpoints.create.description')}
                            </p>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded mb-2">
                                {t('sections.patients.api_endpoints.create.body')}
                            </code>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded">
                                {t('sections.patients.api_endpoints.create.response')}
                            </code>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-2">
                                <Edit className="text-yellow-600 dark:text-yellow-400" size={20} />
                                <h4 className="font-semibold text-yellow-600 dark:text-yellow-400">PUT /doctor/patients/{'{id}'}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.patients.api_endpoints.update.description')}
                            </p>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded">
                                {t('sections.patients.api_endpoints.update.response')}
                            </code>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-2">
                                <Trash2 className="text-red-600 dark:text-red-400" size={20} />
                                <h4 className="font-semibold text-red-600 dark:text-red-400">DELETE /api/patients/{'{id}'}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.patients.api_endpoints.delete.description')}
                            </p>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded">
                                {t('sections.patients.api_endpoints.delete.response')}
                            </code>
                        </Card>
                    </div>
                </div>
            </Section>
        </div>
    );
}

