import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Search, Database, Filter, Calendar, User, Pill, FileText, Zap, Smartphone, Monitor } from 'lucide-react';

export default function SearchDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.search.title')}
                subtitle={t('sections.search.subtitle')}
            />

            <Section title={t('sections.search.overview.title')} icon={<Search />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.search.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.search.entities.title')} icon={<Database />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.search.entities.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <User className="text-blue-600 dark:text-blue-400" size={24} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.search.entities.patients.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.search.entities.patients.description')}
                            </p>
                            <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.search.entities.patients.fields.first_name')}</li>
                                <li>{t('sections.search.entities.patients.fields.last_name')}</li>
                                <li>{t('sections.search.entities.patients.fields.phone')}</li>
                                <li>{t('sections.search.entities.patients.fields.national_id')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Calendar className="text-green-600 dark:text-green-400" size={24} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.search.entities.appointments.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.search.entities.appointments.description')}
                            </p>
                            <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.search.entities.appointments.fields.id')}</li>
                                <li>{t('sections.search.entities.appointments.fields.patient_name')}</li>
                                <li>{t('sections.search.entities.appointments.fields.date')}</li>
                                <li>{t('sections.search.entities.appointments.fields.time')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Pill className="text-purple-600 dark:text-purple-400" size={24} />
                                <h4 className="font-semibold text-purple-600 dark:text-purple-400">{t('sections.search.entities.drugs.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.search.entities.drugs.description')}
                            </p>
                            <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.search.entities.drugs.fields.name')}</li>
                                <li>{t('sections.search.entities.drugs.fields.ingredient')}</li>
                                <li>{t('sections.search.entities.drugs.fields.company')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <FileText className="text-orange-600 dark:text-orange-400" size={24} />
                                <h4 className="font-semibold text-orange-600 dark:text-orange-400">{t('sections.search.entities.prescriptions.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.search.entities.prescriptions.description')}
                            </p>
                            <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.search.entities.prescriptions.fields.medication')}</li>
                                <li>{t('sections.search.entities.prescriptions.fields.glasses')}</li>
                                <li>{t('sections.search.entities.prescriptions.fields.notes')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <FileText className="text-cyan-600 dark:text-cyan-400" size={24} />
                                <h4 className="font-semibold text-cyan-600 dark:text-cyan-400">{t('sections.search.entities.media.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.search.entities.media.description')}
                            </p>
                            <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.search.entities.media.fields.file_name')}</li>
                                <li>{t('sections.search.entities.media.fields.description')}</li>
                                <li>{t('sections.search.entities.media.fields.patient')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <FileText className="text-pink-600 dark:text-pink-400" size={24} />
                                <h4 className="font-semibold text-pink-600 dark:text-pink-400">{t('sections.search.entities.consultation.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.search.entities.consultation.description')}
                            </p>
                            <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.search.entities.consultation.fields.complaint')}</li>
                                <li>{t('sections.search.entities.consultation.fields.diagnosis')}</li>
                                <li>{t('sections.search.entities.consultation.fields.slit_lamp')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.search.entities.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.search.entities.api')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.search.entities.limit')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.search.entities.controller')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.search.smart_filter.title')} icon={<Filter />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.search.smart_filter.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                        <div>
                            <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">{t('sections.search.smart_filter.patient.title')}</h3>
                            <p className="text-gray-700 dark:text-gray-300 mb-4">
                                {t('sections.search.smart_filter.patient.description')}
                            </p>
                            
                            <div className="space-y-3 mb-6">
                                <Card className="bg-gray-100 dark:bg-slate-800/30">
                                    <div className="flex items-center gap-2 mb-2">
                                        <User className="text-blue-600 dark:text-blue-400" size={20} />
                                        <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.search.smart_filter.patient.by_name.title')}</h4>
                                    </div>
                                    <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.search.smart_filter.patient.by_name.example')}</code>
                                    <p className="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                        {t('sections.search.smart_filter.patient.by_name.description')}
                                    </p>
                                </Card>

                                <Card className="bg-gray-100 dark:bg-slate-800/30">
                                    <div className="flex items-center gap-2 mb-2">
                                        <Database className="text-purple-600 dark:text-purple-400" size={20} />
                                        <h4 className="font-semibold text-purple-600 dark:text-purple-400">{t('sections.search.smart_filter.patient.by_id.title')}</h4>
                                    </div>
                                    <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.search.smart_filter.patient.by_id.example')}</code>
                                    <p className="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                        {t('sections.search.smart_filter.patient.by_id.description')}
                                    </p>
                                </Card>
                            </div>

                            <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                                <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.search.smart_filter.patient.technical')}</h4>
                                <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.search.smart_filter.patient.format')}</code>
                                <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.search.smart_filter.patient.detection')}</code>
                                <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.search.smart_filter.patient.filtering')}</code>
                            </div>
                        </div>
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/ui-components/search/001-opt.png"
                                alt="Smart Filter by Patient"
                                className="w-full h-auto hover:scale-105 transition-transform duration-500"
                            />
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.search.date_filter.title')} icon={<Calendar />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/ui-components/search/002-opt.png"
                            alt="Smart Filter by Date"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.search.date_filter.description')}
                        </p>
                        
                        <div className="space-y-3 mb-6">
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <div className="flex items-center gap-2 mb-2">
                                    <Calendar className="text-green-600 dark:text-green-400" size={20} />
                                    <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.search.date_filter.full_date.title')}</h4>
                                </div>
                                <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.search.date_filter.full_date.example')}</code>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                    {t('sections.search.date_filter.full_date.description')}
                                </p>
                            </Card>

                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <div className="flex items-center gap-2 mb-2">
                                    <Calendar className="text-blue-600 dark:text-blue-400" size={20} />
                                    <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.search.date_filter.month.title')}</h4>
                                </div>
                                <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.search.date_filter.month.example')}</code>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                    {t('sections.search.date_filter.month.description')}
                                </p>
                            </Card>

                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <div className="flex items-center gap-2 mb-2">
                                    <Calendar className="text-purple-600 dark:text-purple-400" size={20} />
                                    <h4 className="font-semibold text-purple-600 dark:text-purple-400">{t('sections.search.date_filter.year.title')}</h4>
                                </div>
                                <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.search.date_filter.year.example')}</code>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                    {t('sections.search.date_filter.year.description')}
                                </p>
                            </Card>
                        </div>

                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.search.date_filter.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.search.date_filter.format')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.search.date_filter.parsing')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.search.date_filter.combined')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.search.interface.title')} icon={<Monitor />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.search.interface.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Zap className="text-yellow-600 dark:text-yellow-400" size={24} />
                                <h4 className="font-semibold text-yellow-600 dark:text-yellow-400">{t('sections.search.interface.expand.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.search.interface.expand.features.click')}</li>
                                <li>{t('sections.search.interface.expand.features.focus')}</li>
                                <li>{t('sections.search.interface.expand.features.animation')}</li>
                                <li>{t('sections.search.interface.expand.features.backdrop')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Smartphone className="text-blue-600 dark:text-blue-400" size={24} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.search.interface.mobile.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.search.interface.mobile.features.toggle')}</li>
                                <li>{t('sections.search.interface.mobile.features.fullscreen')}</li>
                                <li>{t('sections.search.interface.mobile.features.results')}</li>
                                <li>{t('sections.search.interface.mobile.features.close')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/ui-components/search/000-opt.png"
                            alt="Search Interface"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.search.interface.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.search.interface.html')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.search.interface.js')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.search.interface.css')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.search.results.title')} icon={<Search />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.search.results.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Zap className="text-green-600 dark:text-green-400" size={24} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.search.results.display.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.search.results.display.features.icons')}</li>
                                <li>{t('sections.search.results.display.features.title')}</li>
                                <li>{t('sections.search.results.display.features.subtitle')}</li>
                                <li>{t('sections.search.results.display.features.type')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Database className="text-blue-600 dark:text-blue-400" size={24} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.search.results.interaction.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.search.results.interaction.features.click')}</li>
                                <li>{t('sections.search.results.interaction.features.keyboard')}</li>
                                <li>{t('sections.search.results.interaction.features.hover')}</li>
                                <li>{t('sections.search.results.interaction.features.empty')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.search.results.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.search.results.api')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.search.results.rendering')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.search.results.debounce')}</code>
                    </div>
                </div>
            </Section>
        </div>
    );
}

