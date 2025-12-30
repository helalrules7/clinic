import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Pill, Search, Filter, Database, Info, Clock, CheckCircle } from 'lucide-react';

export default function DrugsDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.drugs.title')}
                subtitle={t('sections.drugs.subtitle')}
            />

            <Section title={t('sections.drugs.overview.title')} icon={<Pill />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.drugs.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.drugs.search.title')} icon={<Search />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.drugs.search.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.drugs.search.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li>{t('sections.drugs.search.features.real_time')}</li>
                            <li>{t('sections.drugs.search.features.autocomplete')}</li>
                            <li>{t('sections.drugs.search.features.multiple_fields')}</li>
                            <li>{t('sections.drugs.search.features.debounce')}</li>
                            <li>{t('sections.drugs.search.features.shortcuts')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.drugs.search.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.drugs.search.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.drugs.search.route')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.drugs.search.api_endpoint')}</code>
                            <code className="block text-sm text-cyan-600 dark:text-cyan-400 font-mono break-words whitespace-pre-wrap">{t('sections.drugs.search.js_class')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/drugs/01-opt.png"
                            alt="Drug Search and Autocomplete"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.drugs.autocomplete.title')} icon={<Search />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.drugs.autocomplete.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Search className="text-green-600 dark:text-green-400" size={24} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.drugs.autocomplete.features.portal.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.drugs.autocomplete.features.portal.dynamic')}</li>
                                <li>{t('sections.drugs.autocomplete.features.portal.positioned')}</li>
                                <li>{t('sections.drugs.autocomplete.features.portal.max_items')}</li>
                                <li>{t('sections.drugs.autocomplete.features.portal.clickable')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Clock className="text-blue-600 dark:text-blue-400" size={24} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.drugs.autocomplete.features.timing.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.drugs.autocomplete.features.timing.debounce')}</li>
                                <li>{t('sections.drugs.autocomplete.features.timing.min_chars')}</li>
                                <li>{t('sections.drugs.autocomplete.features.timing.auto_hide')}</li>
                                <li>{t('sections.drugs.autocomplete.features.timing.scroll')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.drugs.autocomplete.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.drugs.autocomplete.js_methods')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.drugs.autocomplete.api_endpoint')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.drugs.autocomplete.positioning')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.drugs.results.title')} icon={<CheckCircle />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 space-y-4">
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/doctors_pages/drugs/02-opt.png"
                                alt="Search Results"
                                className="w-full h-auto hover:scale-105 transition-transform duration-500"
                            />
                        </div>
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/doctors_pages/drugs/03-opt.png"
                                alt="Drug Details Modal"
                                className="w-full h-auto hover:scale-105 transition-transform duration-500"
                            />
                        </div>
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.drugs.results.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.drugs.results.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.drugs.results.features.cards.title')}:</strong> {t('sections.drugs.results.features.cards.description')}</li>
                            <li><strong>{t('sections.drugs.results.features.pagination.title')}:</strong> {t('sections.drugs.results.features.pagination.description')}</li>
                            <li><strong>{t('sections.drugs.results.features.modal.title')}:</strong> {t('sections.drugs.results.features.modal.description')}</li>
                            <li><strong>{t('sections.drugs.results.features.url_params.title')}:</strong> {t('sections.drugs.results.features.url_params.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.drugs.results.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.drugs.results.api_search')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.drugs.results.api_details')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.drugs.results.js_methods')}</code>
                            <code className="block text-sm text-cyan-600 dark:text-cyan-400 font-mono break-words whitespace-pre-wrap">{t('sections.drugs.results.modal_display')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.drugs.filters.title')} icon={<Filter />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.drugs.filters.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Filter className="text-purple-600 dark:text-purple-400" size={24} />
                                <h4 className="font-semibold text-purple-600 dark:text-purple-400">{t('sections.drugs.filters.types.category.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.drugs.filters.types.category.description')}
                            </p>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Filter className="text-blue-600 dark:text-blue-400" size={24} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.drugs.filters.types.company.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.drugs.filters.types.company.description')}
                            </p>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Filter className="text-green-600 dark:text-green-400" size={24} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.drugs.filters.types.route.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.drugs.filters.types.route.description')}
                            </p>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.drugs.filters.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.drugs.filters.api_endpoint')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.drugs.filters.js_methods')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.drugs.filters.apply_logic')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.drugs.database_update.title')} icon={<Database />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.drugs.database_update.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.drugs.database_update.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.drugs.database_update.features.download.title')}:</strong> {t('sections.drugs.database_update.features.download.description')}</li>
                            <li><strong>{t('sections.drugs.database_update.features.extract.title')}:</strong> {t('sections.drugs.database_update.features.extract.description')}</li>
                            <li><strong>{t('sections.drugs.database_update.features.update.title')}:</strong> {t('sections.drugs.database_update.features.update.description')}</li>
                            <li><strong>{t('sections.drugs.database_update.features.progress.title')}:</strong> {t('sections.drugs.database_update.features.progress.description')}</li>
                            <li><strong>{t('sections.drugs.database_update.features.statistics.title')}:</strong> {t('sections.drugs.database_update.features.statistics.description')}</li>
                            <li><strong>{t('sections.drugs.database_update.features.cronjob.title')}:</strong> {t('sections.drugs.database_update.features.cronjob.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.drugs.database_update.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.drugs.database_update.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.drugs.database_update.controller_method')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.drugs.database_update.js_functions')}</code>
                            <code className="block text-sm text-cyan-600 dark:text-cyan-400 font-mono break-words whitespace-pre-wrap">{t('sections.drugs.database_update.cronjob_schedule')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/drugs/04-opt.png"
                            alt="Database Update Process"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.drugs.api_endpoints.title')} icon={<Info />}>
                <div className="space-y-4">
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-3 font-bold tracking-wider">{t('sections.drugs.api_endpoints.search.title')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.drugs.api_endpoints.search.endpoint')}</code>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">{t('sections.drugs.api_endpoints.search.description')}</p>
                        <p className="text-sm text-gray-600 dark:text-gray-400"><strong>{t('sections.drugs.api_endpoints.search.params')}</strong></p>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-3 font-bold tracking-wider">{t('sections.drugs.api_endpoints.details.title')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.drugs.api_endpoints.details.endpoint')}</code>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.drugs.api_endpoints.details.description')}</p>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-3 font-bold tracking-wider">{t('sections.drugs.api_endpoints.filters.title')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.drugs.api_endpoints.filters.endpoint')}</code>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.drugs.api_endpoints.filters.description')}</p>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-3 font-bold tracking-wider">{t('sections.drugs.api_endpoints.update.title')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.drugs.api_endpoints.update.endpoint')}</code>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.drugs.api_endpoints.update.description')}</p>
                    </div>
                </div>
            </Section>
        </div>
    );
}

