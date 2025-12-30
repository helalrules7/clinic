import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Dock, Monitor, Smartphone, EyeOff, Minimize2, Maximize2, Settings, Database, Zap, Archive } from 'lucide-react';

export default function DockDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.dock.title')}
                subtitle={t('sections.dock.subtitle')}
            />

            <Section title={t('sections.dock.overview.title')} icon={<Dock />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.dock.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.dock.desktop.title')} icon={<Monitor />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.dock.desktop.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.dock.desktop.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.dock.desktop.features.position.title')}:</strong> {t('sections.dock.desktop.features.position.description')}</li>
                            <li><strong>{t('sections.dock.desktop.features.items.title')}:</strong> {t('sections.dock.desktop.features.items.description')}</li>
                            <li><strong>{t('sections.dock.desktop.features.hover.title')}:</strong> {t('sections.dock.desktop.features.hover.description')}</li>
                            <li><strong>{t('sections.dock.desktop.features.stack.title')}:</strong> {t('sections.dock.desktop.features.stack.description')}</li>
                            <li><strong>{t('sections.dock.desktop.features.profile.title')}:</strong> {t('sections.dock.desktop.features.profile.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.dock.desktop.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.dock.desktop.html')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.dock.desktop.css')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.dock.desktop.js')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/ui-components/thedock/001-opt.png"
                            alt="Desktop Dock"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.dock.features.title')} icon={<Minimize2 />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/ui-components/thedock/002-opt.png"
                            alt="Dock Features"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.dock.features.description')}
                        </p>
                        
                        <div className="space-y-4 mb-6">
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <div className="flex items-center gap-2 mb-2">
                                    <Minimize2 className="text-blue-600 dark:text-blue-400" size={20} />
                                    <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.dock.features.minimize.title')}</h4>
                                </div>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {t('sections.dock.features.minimize.description')}
                                </p>
                                <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                    <li>{t('sections.dock.features.minimize.features.icon')}</li>
                                    <li>{t('sections.dock.features.minimize.features.size')}</li>
                                    <li>{t('sections.dock.features.minimize.features.autohide')}</li>
                                </ul>
                            </Card>

                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <div className="flex items-center gap-2 mb-2">
                                    <EyeOff className="text-green-600 dark:text-green-400" size={20} />
                                    <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.dock.features.autohide.title')}</h4>
                                </div>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {t('sections.dock.features.autohide.description')}
                                </p>
                                <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                    <li>{t('sections.dock.features.autohide.features.hover')}</li>
                                    <li>{t('sections.dock.features.autohide.features.hidden')}</li>
                                    <li>{t('sections.dock.features.autohide.features.visible')}</li>
                                </ul>
                            </Card>

                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <div className="flex items-center gap-2 mb-2">
                                    <Maximize2 className="text-purple-600 dark:text-purple-400" size={20} />
                                    <h4 className="font-semibold text-purple-600 dark:text-purple-400">{t('sections.dock.features.maximize.title')}</h4>
                                </div>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {t('sections.dock.features.maximize.description')}
                                </p>
                                <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                    <li>{t('sections.dock.features.maximize.features.restore')}</li>
                                    <li>{t('sections.dock.features.maximize.features.full')}</li>
                                    <li>{t('sections.dock.features.maximize.features.autohide')}</li>
                                </ul>
                            </Card>
                        </div>

                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.dock.features.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.dock.features.classes')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.dock.features.functions')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.dock.features.api')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.dock.mobile.title')} icon={<Smartphone />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.dock.mobile.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Smartphone className="text-indigo-600 dark:text-indigo-400" size={24} />
                                <h4 className="font-semibold text-indigo-600 dark:text-indigo-400">{t('sections.dock.mobile.features.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.dock.mobile.features.minimized')}</li>
                                <li>{t('sections.dock.mobile.features.radial')}</li>
                                <li>{t('sections.dock.mobile.features.position')}</li>
                                <li>{t('sections.dock.mobile.features.scroll')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Dock className="text-purple-600 dark:text-purple-400" size={24} />
                                <h4 className="font-semibold text-purple-600 dark:text-purple-400">{t('sections.dock.mobile.interaction.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.dock.mobile.interaction.features.tap')}</li>
                                <li>{t('sections.dock.mobile.interaction.features.close')}</li>
                                <li>{t('sections.dock.mobile.interaction.features.auto')}</li>
                                <li>{t('sections.dock.mobile.interaction.features.items')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40 flex items-center justify-center">
                        <video
                            src="/docs/opth/assets/images/ui-components/thedock/mobile_dock.mp4"
                            controls
                            muted
                            loop
                            playsInline
                            autoPlay
                            className="w-full h-auto md:max-w-md mx-auto"
                        >
                            {t('sections.dock.mobile.video_fallback')}
                        </video>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.dock.mobile.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.dock.mobile.classes')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.dock.mobile.function')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.dock.mobile.position')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.dock.storage.title')} icon={<Database />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.dock.storage.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Zap className="text-yellow-600 dark:text-yellow-400" size={24} />
                                <h4 className="font-semibold text-yellow-600 dark:text-yellow-400">{t('sections.dock.storage.localstorage.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.dock.storage.localstorage.features.fast')}</li>
                                <li>{t('sections.dock.storage.localstorage.features.session')}</li>
                                <li>{t('sections.dock.storage.localstorage.features.sync')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Database className="text-blue-600 dark:text-blue-400" size={24} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.dock.storage.database.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.dock.storage.database.features.table')}</li>
                                <li>{t('sections.dock.storage.database.features.keys')}</li>
                                <li>{t('sections.dock.storage.database.features.persistent')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.dock.storage.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.dock.storage.api')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.dock.storage.load')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.dock.storage.save')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.dock.settings_page.title')} icon={<Settings />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/ui-components/thedock/003-opt.png"
                            alt="Dock Settings"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.dock.settings_page.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.dock.settings_page.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.dock.settings_page.features.desktop.title')}:</strong> {t('sections.dock.settings_page.features.desktop.description')}</li>
                            <li><strong>{t('sections.dock.settings_page.features.mobile.title')}:</strong> {t('sections.dock.settings_page.features.mobile.description')}</li>
                            <li><strong>{t('sections.dock.settings_page.features.minimize.title')}:</strong> {t('sections.dock.settings_page.features.minimize.description')}</li>
                            <li><strong>{t('sections.dock.settings_page.features.autohide.title')}:</strong> {t('sections.dock.settings_page.features.autohide.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.dock.settings_page.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.dock.settings_page.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.dock.settings_page.api')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.dock.settings_page.controller')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.dock.stack_menu.title')} icon={<Archive />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.dock.stack_menu.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Archive className="text-orange-600 dark:text-orange-400" size={24} />
                                <h4 className="font-semibold text-orange-600 dark:text-orange-400">{t('sections.dock.stack_menu.features.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.dock.stack_menu.features.genie')}</li>
                                <li>{t('sections.dock.stack_menu.features.items')}</li>
                                <li>{t('sections.dock.stack_menu.features.close')}</li>
                                <li>{t('sections.dock.stack_menu.features.animation')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Dock className="text-cyan-600 dark:text-cyan-400" size={24} />
                                <h4 className="font-semibold text-cyan-600 dark:text-cyan-400">{t('sections.dock.stack_menu.current.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.dock.stack_menu.current.features.medical')}</li>
                                <li>{t('sections.dock.stack_menu.current.features.prescriptions')}</li>
                                <li>{t('sections.dock.stack_menu.current.features.glasses')}</li>
                                <li>{t('sections.dock.stack_menu.current.features.media')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.dock.stack_menu.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.dock.stack_menu.html')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.dock.stack_menu.css')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.dock.stack_menu.js')}</code>
                    </div>
                </div>
            </Section>
        </div>
    );
}

