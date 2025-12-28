import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Palette, Sun, Database, HardDrive, Settings, Zap, RefreshCw, Smartphone } from 'lucide-react';

export default function ThemeSwitchDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.theme_switch.title')}
                subtitle={t('sections.theme_switch.subtitle')}
            />

            <Section title={t('sections.theme_switch.overview.title')} icon={<Palette />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.theme_switch.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.theme_switch.component.title')} icon={<Sun />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.theme_switch.component.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.theme_switch.component.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.theme_switch.component.features.animation.title')}:</strong> {t('sections.theme_switch.component.features.animation.description')}</li>
                            <li><strong>{t('sections.theme_switch.component.features.sun.title')}:</strong> {t('sections.theme_switch.component.features.sun.description')}</li>
                            <li><strong>{t('sections.theme_switch.component.features.moon.title')}:</strong> {t('sections.theme_switch.component.features.moon.description')}</li>
                            <li><strong>{t('sections.theme_switch.component.features.stars.title')}:</strong> {t('sections.theme_switch.component.features.stars.description')}</li>
                            <li><strong>{t('sections.theme_switch.component.features.clouds.title')}:</strong> {t('sections.theme_switch.component.features.clouds.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.theme_switch.component.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.theme_switch.component.html')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.theme_switch.component.css')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.theme_switch.component.js')}</code>
                        </div>
                    </div>
                    <div className="space-y-4">
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/ui-components/themeswitch/001-opt.png"
                                alt="Theme Switch Light Mode"
                                className="w-full h-auto hover:scale-105 transition-transform duration-500"
                            />
                        </div>
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/ui-components/themeswitch/002-opt.png"
                                alt="Theme Switch Dark Mode"
                                className="w-full h-auto hover:scale-105 transition-transform duration-500"
                            />
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.theme_switch.storage.title')} icon={<Database />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.theme_switch.storage.description')}
                    </p>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Zap className="text-yellow-600 dark:text-yellow-400" size={24} />
                                <h4 className="font-semibold text-yellow-600 dark:text-yellow-400">{t('sections.theme_switch.storage.localstorage.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.theme_switch.storage.localstorage.features.immediate')}</li>
                                <li>{t('sections.theme_switch.storage.localstorage.features.keys')}</li>
                                <li>{t('sections.theme_switch.storage.localstorage.features.priority')}</li>
                                <li>{t('sections.theme_switch.storage.localstorage.features.sync')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Database className="text-blue-600 dark:text-blue-400" size={24} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.theme_switch.storage.database.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.theme_switch.storage.database.features.table')}</li>
                                <li>{t('sections.theme_switch.storage.database.features.key')}</li>
                                <li>{t('sections.theme_switch.storage.database.features.persistent')}</li>
                                <li>{t('sections.theme_switch.storage.database.features.sync')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.theme_switch.storage.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.theme_switch.storage.localstorage_code')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.theme_switch.storage.database_code')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1">{t('sections.theme_switch.storage.api')}</code>
                            <code className="block text-sm text-cyan-600 dark:text-cyan-400 font-mono break-words whitespace-pre-wrap">{t('sections.theme_switch.storage.priority')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.theme_switch.login_page.title')} icon={<RefreshCw />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.theme_switch.login_page.description')}
                    </p>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Sun className="text-orange-600 dark:text-orange-400" size={24} />
                                <h4 className="font-semibold text-orange-600 dark:text-orange-400">{t('sections.theme_switch.login_page.features.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.theme_switch.login_page.features.button')}</li>
                                <li>{t('sections.theme_switch.login_page.features.localstorage')}</li>
                                <li>{t('sections.theme_switch.login_page.features.system')}</li>
                                <li>{t('sections.theme_switch.login_page.features.sync')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <RefreshCw className="text-purple-600 dark:text-purple-400" size={24} />
                                <h4 className="font-semibold text-purple-600 dark:text-purple-400">{t('sections.theme_switch.login_page.sync.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.theme_switch.login_page.sync.features.load')}</li>
                                <li>{t('sections.theme_switch.login_page.sync.features.compare')}</li>
                                <li>{t('sections.theme_switch.login_page.sync.features.update')}</li>
                                <li>{t('sections.theme_switch.login_page.sync.features.save')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.theme_switch.login_page.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.theme_switch.login_page.file')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.theme_switch.login_page.function')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.theme_switch.login_page.storage')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.theme_switch.settings_page.title')} icon={<Settings />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/ui-components/themeswitch/003-opt.png"
                            alt="Theme Switch in Settings Page"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.theme_switch.settings_page.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.theme_switch.settings_page.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.theme_switch.settings_page.features.location.title')}:</strong> {t('sections.theme_switch.settings_page.features.location.description')}</li>
                            <li><strong>{t('sections.theme_switch.settings_page.features.control.title')}:</strong> {t('sections.theme_switch.settings_page.features.control.description')}</li>
                            <li><strong>{t('sections.theme_switch.settings_page.features.save.title')}:</strong> {t('sections.theme_switch.settings_page.features.save.description')}</li>
                            <li><strong>{t('sections.theme_switch.settings_page.features.preview.title')}:</strong> {t('sections.theme_switch.settings_page.features.preview.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.theme_switch.settings_page.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.theme_switch.settings_page.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.theme_switch.settings_page.api')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.theme_switch.settings_page.controller')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.theme_switch.initialization.title')} icon={<Zap />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.theme_switch.initialization.description')}
                    </p>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.theme_switch.initialization.process')}</h4>
                        <ol className="list-decimal list-inside space-y-2 text-sm text-gray-700 dark:text-gray-300 ml-2">
                            <li>{t('sections.theme_switch.initialization.steps.inline')}</li>
                            <li>{t('sections.theme_switch.initialization.steps.localstorage')}</li>
                            <li>{t('sections.theme_switch.initialization.steps.database')}</li>
                            <li>{t('sections.theme_switch.initialization.steps.sync')}</li>
                            <li>{t('sections.theme_switch.initialization.steps.apply')}</li>
                            <li>{t('sections.theme_switch.initialization.steps.mark')}</li>
                        </ol>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.theme_switch.initialization.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.theme_switch.initialization.script')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.theme_switch.initialization.class')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.theme_switch.initialization.default')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.theme_switch.features.title')} icon={<Palette />}>
                <div className="space-y-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Zap className="text-yellow-600 dark:text-yellow-400" size={20} />
                                <h4 className="font-semibold text-yellow-600 dark:text-yellow-400">{t('sections.theme_switch.features.instant.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.theme_switch.features.instant.description')}
                            </p>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <RefreshCw className="text-blue-600 dark:text-blue-400" size={20} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.theme_switch.features.sync.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.theme_switch.features.sync.description')}
                            </p>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <HardDrive className="text-green-600 dark:text-green-400" size={20} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.theme_switch.features.persistent.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.theme_switch.features.persistent.description')}
                            </p>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Smartphone className="text-purple-600 dark:text-purple-400" size={20} />
                                <h4 className="font-semibold text-purple-600 dark:text-purple-400">{t('sections.theme_switch.features.responsive.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.theme_switch.features.responsive.description')}
                            </p>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.theme_switch.features.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.theme_switch.features.logo')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.theme_switch.features.favicon')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.theme_switch.features.css')}</code>
                    </div>
                </div>
            </Section>
        </div>
    );
}

