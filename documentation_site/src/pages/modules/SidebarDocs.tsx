import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Sidebar as SidebarIcon, User, Menu, Smartphone, Monitor, ChevronDown, Eye, Settings } from 'lucide-react';

export default function SidebarDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.sidebar.title')}
                subtitle={t('sections.sidebar.subtitle')}
            />

            <Section title={t('sections.sidebar.overview.title')} icon={<SidebarIcon />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.sidebar.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.sidebar.structure.title')} icon={<Menu />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.sidebar.structure.description')}
                        </p>
                        <div className="space-y-4 mb-6">
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400 mb-2">{t('sections.sidebar.structure.components.header.title')}</h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {t('sections.sidebar.structure.components.header.description')}
                                </p>
                                <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                    <li>{t('sections.sidebar.structure.components.header.features.logo')}</li>
                                    <li>{t('sections.sidebar.structure.components.header.features.name')}</li>
                                    <li>{t('sections.sidebar.structure.components.header.features.theme')}</li>
                                </ul>
                            </Card>
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <h4 className="font-semibold text-green-600 dark:text-green-400 mb-2">{t('sections.sidebar.structure.components.user_info.title')}</h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {t('sections.sidebar.structure.components.user_info.description')}
                                </p>
                                <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                    <li>{t('sections.sidebar.structure.components.user_info.features.avatar')}</li>
                                    <li>{t('sections.sidebar.structure.components.user_info.features.name')}</li>
                                    <li>{t('sections.sidebar.structure.components.user_info.features.role')}</li>
                                    <li>{t('sections.sidebar.structure.components.user_info.features.preview')}</li>
                                </ul>
                            </Card>
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <h4 className="font-semibold text-purple-600 dark:text-purple-400 mb-2">{t('sections.sidebar.structure.components.navigation.title')}</h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {t('sections.sidebar.structure.components.navigation.description')}
                                </p>
                                <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                    <li>{t('sections.sidebar.structure.components.navigation.features.active')}</li>
                                    <li>{t('sections.sidebar.structure.components.navigation.features.submenu')}</li>
                                    <li>{t('sections.sidebar.structure.components.navigation.features.roles')}</li>
                                    <li>{t('sections.sidebar.structure.components.navigation.features.customizable')}</li>
                                </ul>
                            </Card>
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <h4 className="font-semibold text-orange-600 dark:text-orange-400 mb-2">{t('sections.sidebar.structure.components.footer.title')}</h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {t('sections.sidebar.structure.components.footer.description')}
                                </p>
                                <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                    <li>{t('sections.sidebar.structure.components.footer.features.version')}</li>
                                    <li>{t('sections.sidebar.structure.components.footer.features.copyright')}</li>
                                </ul>
                            </Card>
                        </div>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.sidebar.structure.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.sidebar.structure.file')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.sidebar.structure.id')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.sidebar.structure.classes')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/ui-components/sidebar-opt.png"
                            alt="Sidebar Structure"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.sidebar.user_avatar.title')} icon={<User />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/ui-components/sidebar-withuserimage-opt.png"
                            alt="User Avatar with Image"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.sidebar.user_avatar.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.sidebar.user_avatar.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.sidebar.user_avatar.features.image.title')}:</strong> {t('sections.sidebar.user_avatar.features.image.description')}</li>
                            <li><strong>{t('sections.sidebar.user_avatar.features.fallback.title')}:</strong> {t('sections.sidebar.user_avatar.features.fallback.description')}</li>
                            <li><strong>{t('sections.sidebar.user_avatar.features.preview.title')}:</strong> {t('sections.sidebar.user_avatar.features.preview.description')}</li>
                            <li><strong>{t('sections.sidebar.user_avatar.features.link.title')}:</strong> {t('sections.sidebar.user_avatar.features.link.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.sidebar.user_avatar.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.sidebar.user_avatar.html')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.sidebar.user_avatar.css')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.sidebar.user_avatar.js')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.sidebar.responsiveness.title')} icon={<Smartphone />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.sidebar.responsiveness.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Monitor className="text-blue-600 dark:text-blue-400" size={24} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.sidebar.responsiveness.desktop.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.sidebar.responsiveness.desktop.features.always_visible')}</li>
                                <li>{t('sections.sidebar.responsiveness.desktop.features.fixed')}</li>
                                <li>{t('sections.sidebar.responsiveness.desktop.features.full_height')}</li>
                                <li>{t('sections.sidebar.responsiveness.desktop.features.width')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Smartphone className="text-green-600 dark:text-green-400" size={24} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.sidebar.responsiveness.mobile.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.sidebar.responsiveness.mobile.features.hidden')}</li>
                                <li>{t('sections.sidebar.responsiveness.mobile.features.toggle')}</li>
                                <li>{t('sections.sidebar.responsiveness.mobile.features.overlay')}</li>
                                <li>{t('sections.sidebar.responsiveness.mobile.features.auto_close')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.sidebar.responsiveness.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.sidebar.responsiveness.breakpoint')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.sidebar.responsiveness.toggle')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1">{t('sections.sidebar.responsiveness.overlay')}</code>
                        <code className="block text-sm text-cyan-600 dark:text-cyan-400 font-mono">{t('sections.sidebar.responsiveness.resize')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.sidebar.features.title')} icon={<Settings />}>
                <div className="space-y-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Eye className="text-purple-600 dark:text-purple-400" size={20} />
                                <h4 className="font-semibold text-purple-600 dark:text-purple-400">{t('sections.sidebar.features.active_state.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.sidebar.features.active_state.description')}
                            </p>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <ChevronDown className="text-cyan-600 dark:text-cyan-400" size={20} />
                                <h4 className="font-semibold text-cyan-600 dark:text-cyan-400">{t('sections.sidebar.features.submenu.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.sidebar.features.submenu.description')}
                            </p>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <User className="text-orange-600 dark:text-orange-400" size={20} />
                                <h4 className="font-semibold text-orange-600 dark:text-orange-400">{t('sections.sidebar.features.role_based.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.sidebar.features.role_based.description')}
                            </p>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Settings className="text-red-600 dark:text-red-400" size={20} />
                                <h4 className="font-semibold text-red-600 dark:text-red-400">{t('sections.sidebar.features.customizable.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.sidebar.features.customizable.description')}
                            </p>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.sidebar.features.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.sidebar.features.api')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.sidebar.features.settings')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.sidebar.features.method')}</code>
                    </div>
                </div>
            </Section>
        </div>
    );
}

