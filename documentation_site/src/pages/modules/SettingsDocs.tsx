import Hero from '../../components/ui/Hero';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { BellOff, ArrowUp, Layout, Moon, Smartphone, List, DollarSign, Building2, Image as ImageIcon, RefreshCw } from 'lucide-react';
import { useTranslation } from 'react-i18next';

export default function SettingsDocs() {
    const { t } = useTranslation();

    return (
        <div className="animate-fade-in">
            <Hero
                title={t('sections.settings.hero.title')}
                subtitle={t('sections.settings.hero.subtitle')}
                badge={t('sections.settings.hero.badge')}
            />

            <Section title={t('sections.settings.overview.title')} id="overview">
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.settings.overview.content')}
                </p>
            </Section>

            {/* Personal Preferences */}
            <Section title={t('sections.settings.personal_preferences.title')} id="personal-preferences">
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.settings.personal_preferences.content')}
                </p>

                <div className="mb-6">
                    <img 
                        src="/docs/opth/assets/images/doctors_pages/settings/01-opt.png" 
                        alt="Personal Preferences" 
                        className="w-full rounded-lg shadow-lg border border-gray-200 dark:border-gray-700"
                    />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <Card title={t('sections.settings.personal_preferences.items.alerts.title')} icon={BellOff}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {t('sections.settings.personal_preferences.items.alerts.desc')}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-gray-500">
                            {t('sections.settings.personal_preferences.items.alerts.note')}
                        </p>
                    </Card>

                    <Card title={t('sections.settings.personal_preferences.items.notifications.title')} icon={BellOff}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {t('sections.settings.personal_preferences.items.notifications.desc')}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-gray-500">
                            {t('sections.settings.personal_preferences.items.notifications.note')}
                        </p>
                    </Card>

                    <Card title={t('sections.settings.personal_preferences.items.back_to_top.title')} icon={ArrowUp}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {t('sections.settings.personal_preferences.items.back_to_top.desc')}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-gray-500">
                            {t('sections.settings.personal_preferences.items.back_to_top.note')}
                        </p>
                    </Card>

                    <Card title={t('sections.settings.personal_preferences.items.dock.title')} icon={Layout}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {t('sections.settings.personal_preferences.items.dock.desc')}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-gray-500">
                            {t('sections.settings.personal_preferences.items.dock.note')}
                        </p>
                    </Card>

                    <Card title={t('sections.settings.personal_preferences.items.dock_autohide.title')} icon={Layout}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {t('sections.settings.personal_preferences.items.dock_autohide.desc')}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-gray-500">
                            {t('sections.settings.personal_preferences.items.dock_autohide.note')}
                        </p>
                    </Card>

                    <Card title={t('sections.settings.personal_preferences.items.theme.title')} icon={Moon}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {t('sections.settings.personal_preferences.items.theme.desc')}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-gray-500">
                            {t('sections.settings.personal_preferences.items.theme.note')}
                        </p>
                    </Card>

                    <Card title={t('sections.settings.personal_preferences.items.push_notifications.title')} icon={Smartphone}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {t('sections.settings.personal_preferences.items.push_notifications.desc')}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-gray-500">
                            {t('sections.settings.personal_preferences.items.push_notifications.note')}
                        </p>
                    </Card>

                    <Card title={t('sections.settings.personal_preferences.items.sidebar_items.title')} icon={List}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {t('sections.settings.personal_preferences.items.sidebar_items.desc')}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-gray-500">
                            {t('sections.settings.personal_preferences.items.sidebar_items.note')}
                        </p>
                    </Card>
                </div>
            </Section>

            {/* General Settings */}
            <Section title={t('sections.settings.general_settings.title')} id="general-settings">
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.settings.general_settings.content')}
                </p>

                <div className="mb-6">
                    <img 
                        src="/docs/opth/assets/images/doctors_pages/settings/02-opt.png" 
                        alt="General Settings" 
                        className="w-full rounded-lg shadow-lg border border-gray-200 dark:border-gray-700"
                    />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <Card title={t('sections.settings.general_settings.items.clinic_info.title')} icon={Building2}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {t('sections.settings.general_settings.items.clinic_info.desc')}
                        </p>
                        <ul className="text-xs text-gray-500 dark:text-gray-500 space-y-1 list-disc list-inside">
                            <li>{t('sections.settings.general_settings.items.clinic_info.fields.name')}</li>
                            <li>{t('sections.settings.general_settings.items.clinic_info.fields.name_arabic')}</li>
                            <li>{t('sections.settings.general_settings.items.clinic_info.fields.email')}</li>
                            <li>{t('sections.settings.general_settings.items.clinic_info.fields.phone')}</li>
                            <li>{t('sections.settings.general_settings.items.clinic_info.fields.address')}</li>
                            <li>{t('sections.settings.general_settings.items.clinic_info.fields.website')}</li>
                        </ul>
                    </Card>

                    <Card title={t('sections.settings.general_settings.items.logos.title')} icon={ImageIcon}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {t('sections.settings.general_settings.items.logos.desc')}
                        </p>
                        <ul className="text-xs text-gray-500 dark:text-gray-500 space-y-1 list-disc list-inside">
                            <li>{t('sections.settings.general_settings.items.logos.fields.main')}</li>
                            <li>{t('sections.settings.general_settings.items.logos.fields.print')}</li>
                            <li>{t('sections.settings.general_settings.items.logos.fields.watermark')}</li>
                        </ul>
                        <p className="text-xs text-gray-500 dark:text-gray-500 mt-2">
                            {t('sections.settings.general_settings.items.logos.note')}
                        </p>
                    </Card>
                </div>
            </Section>

            {/* Visit Costs */}
            <Section title={t('sections.settings.visit_costs.title')} id="visit-costs">
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.settings.visit_costs.content')}
                </p>

                <div className="mb-6">
                    <img 
                        src="/docs/opth/assets/images/doctors_pages/settings/03-opt.png" 
                        alt="Visit Costs Settings" 
                        className="w-full rounded-lg shadow-lg border border-gray-200 dark:border-gray-700"
                    />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <Card title={t('sections.settings.visit_costs.items.new_visit.title')} icon={DollarSign}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {t('sections.settings.visit_costs.items.new_visit.desc')}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-gray-500">
                            {t('sections.settings.visit_costs.items.new_visit.note')}
                        </p>
                    </Card>

                    <Card title={t('sections.settings.visit_costs.items.followup.title')} icon={DollarSign}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {t('sections.settings.visit_costs.items.followup.desc')}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-gray-500">
                            {t('sections.settings.visit_costs.items.followup.note')}
                        </p>
                    </Card>

                    <Card title={t('sections.settings.visit_costs.items.consultation.title')} icon={DollarSign}>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {t('sections.settings.visit_costs.items.consultation.desc')}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-gray-500">
                            {t('sections.settings.visit_costs.items.consultation.note')}
                        </p>
                    </Card>
                </div>
            </Section>

            {/* Drugs Database Update */}
            <Section title={t('sections.settings.drugs_database.title')} id="drugs-database">
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.settings.drugs_database.content')}
                </p>

                <div className="mb-6">
                    <img 
                        src="/docs/opth/assets/images/doctors_pages/settings/04-opt.png" 
                        alt="Drugs Database Update" 
                        className="w-full rounded-lg shadow-lg border border-gray-200 dark:border-gray-700"
                    />
                </div>

                <Card title={t('sections.settings.drugs_database.update_process.title')} icon={RefreshCw}>
                    <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        {t('sections.settings.drugs_database.update_process.content')}
                    </p>
                    <ol className="text-sm text-gray-600 dark:text-gray-400 space-y-2 list-decimal list-inside">
                        <li>{t('sections.settings.drugs_database.update_process.steps.download')}</li>
                        <li>{t('sections.settings.drugs_database.update_process.steps.extract')}</li>
                        <li>{t('sections.settings.drugs_database.update_process.steps.update')}</li>
                        <li>{t('sections.settings.drugs_database.update_process.steps.complete')}</li>
                    </ol>
                </Card>

                <div className="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Card className="bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800">
                        <h4 className="font-semibold text-blue-900 dark:text-blue-100 mb-2">
                            {t('sections.settings.drugs_database.statistics.total')}
                        </h4>
                        <p className="text-sm text-blue-700 dark:text-blue-300">
                            {t('sections.settings.drugs_database.statistics.total_desc')}
                        </p>
                    </Card>

                    <Card className="bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
                        <h4 className="font-semibold text-green-900 dark:text-green-100 mb-2">
                            {t('sections.settings.drugs_database.statistics.inserted')}
                        </h4>
                        <p className="text-sm text-green-700 dark:text-green-300">
                            {t('sections.settings.drugs_database.statistics.inserted_desc')}
                        </p>
                    </Card>

                    <Card className="bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800">
                        <h4 className="font-semibold text-purple-900 dark:text-purple-100 mb-2">
                            {t('sections.settings.drugs_database.statistics.updated')}
                        </h4>
                        <p className="text-sm text-purple-700 dark:text-purple-300">
                            {t('sections.settings.drugs_database.statistics.updated_desc')}
                        </p>
                    </Card>
                </div>
            </Section>

            {/* API Endpoints */}
            <Section title={t('sections.settings.api_endpoints.title')} id="api-endpoints">
                <div className="space-y-4">
                    <div className="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div className="flex items-center gap-3 mb-2">
                            <span className="px-2 py-1 rounded text-xs font-bold uppercase bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                                GET
                            </span>
                            <code className="text-sm font-mono text-gray-700 dark:text-gray-300">
                                /api/doctor/settings
                            </code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            {t('sections.settings.api_endpoints.get_settings')}
                        </p>
                    </div>

                    <div className="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div className="flex items-center gap-3 mb-2">
                            <span className="px-2 py-1 rounded text-xs font-bold uppercase bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300">
                                PUT
                            </span>
                            <code className="text-sm font-mono text-gray-700 dark:text-gray-300">
                                /api/doctor/settings
                            </code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            {t('sections.settings.api_endpoints.update_settings')}
                        </p>
                    </div>

                    <div className="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div className="flex items-center gap-3 mb-2">
                            <span className="px-2 py-1 rounded text-xs font-bold uppercase bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                                POST
                            </span>
                            <code className="text-sm font-mono text-gray-700 dark:text-gray-300">
                                /api/drugs/update-database
                            </code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            {t('sections.settings.api_endpoints.update_database')}
                        </p>
                    </div>
                </div>
            </Section>
        </div>
    );
}

