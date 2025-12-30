import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Bell, Smartphone, Monitor, Trash2, PanelRight, Badge, RefreshCw, Volume2, Eye } from 'lucide-react';

export default function NotificationsDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.notifications.title')}
                subtitle={t('sections.notifications.subtitle')}
            />

            <Section title={t('sections.notifications.overview.title')} icon={<Bell />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.notifications.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.notifications.bell_icon.title')} icon={<Badge />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notifications.bell_icon.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notifications.bell_icon.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.notifications.bell_icon.features.badge.title')}:</strong> {t('sections.notifications.bell_icon.features.badge.description')}</li>
                            <li><strong>{t('sections.notifications.bell_icon.features.counter.title')}:</strong> {t('sections.notifications.bell_icon.features.counter.description')}</li>
                            <li><strong>{t('sections.notifications.bell_icon.features.visibility.title')}:</strong> {t('sections.notifications.bell_icon.features.visibility.description')}</li>
                            <li><strong>{t('sections.notifications.bell_icon.features.sound.title')}:</strong> {t('sections.notifications.bell_icon.features.sound.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notifications.bell_icon.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notifications.bell_icon.html')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notifications.bell_icon.css')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.notifications.bell_icon.js')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40 p-8 flex items-center justify-center">
                        <div className="relative inline-block">
                            <div className="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                                <button className="btn btn-outline-secondary notifications-toggle relative" style={{ position: 'relative', padding: '0.5rem 1rem' }}>
                                    <Bell size={32} />
                                    <span className="notifications-badge" style={{
                                        position: 'absolute',
                                        top: '-8px',
                                        right: '-8px',
                                        background: '#ef4444',
                                        color: 'white',
                                        borderRadius: '50%',
                                        width: '20px',
                                        height: '20px',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        fontSize: '0.75rem',
                                        fontWeight: 'bold',
                                        border: '2px solid white',
                                        minWidth: '20px',
                                        padding: '0 4px'
                                    }}>3</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notifications.panel.title')} icon={<PanelRight />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/ui-components/notifications/001-opt.png"
                            alt="Notifications Panel"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notifications.panel.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notifications.panel.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.notifications.panel.features.header.title')}:</strong> {t('sections.notifications.panel.features.header.description')}</li>
                            <li><strong>{t('sections.notifications.panel.features.body.title')}:</strong> {t('sections.notifications.panel.features.body.description')}</li>
                            <li><strong>{t('sections.notifications.panel.features.actions.title')}:</strong> {t('sections.notifications.panel.features.actions.description')}</li>
                            <li><strong>{t('sections.notifications.panel.features.items.title')}:</strong> {t('sections.notifications.panel.features.items.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notifications.panel.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notifications.panel.html')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notifications.panel.css')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.notifications.panel.js')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notifications.items.title')} icon={<Eye />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.notifications.items.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.notifications.items.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.notifications.items.features.types.title')}:</strong> {t('sections.notifications.items.features.types.description')}</li>
                            <li><strong>{t('sections.notifications.items.features.status.title')}:</strong> {t('sections.notifications.items.features.status.description')}</li>
                            <li><strong>{t('sections.notifications.items.features.icons.title')}:</strong> {t('sections.notifications.items.features.icons.description')}</li>
                            <li><strong>{t('sections.notifications.items.features.time.title')}:</strong> {t('sections.notifications.items.features.time.description')}</li>
                            <li><strong>{t('sections.notifications.items.features.delete.title')}:</strong> {t('sections.notifications.items.features.delete.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notifications.items.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notifications.items.api')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notifications.items.render')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.notifications.items.animation')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/ui-components/notifications/002-opt.png"
                            alt="Notification Items"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notifications.responsiveness.title')} icon={<Smartphone />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.notifications.responsiveness.description')}
                    </p>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Monitor className="text-blue-600 dark:text-blue-400" size={24} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.notifications.responsiveness.desktop.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.notifications.responsiveness.desktop.features.position')}</li>
                                <li>{t('sections.notifications.responsiveness.desktop.features.size')}</li>
                                <li>{t('sections.notifications.responsiveness.desktop.features.height')}</li>
                                <li>{t('sections.notifications.responsiveness.desktop.features.overlay')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Smartphone className="text-green-600 dark:text-green-400" size={24} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.notifications.responsiveness.mobile.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.notifications.responsiveness.mobile.features.fullscreen')}</li>
                                <li>{t('sections.notifications.responsiveness.mobile.features.height')}</li>
                                <li>{t('sections.notifications.responsiveness.mobile.features.swipe')}</li>
                                <li>{t('sections.notifications.responsiveness.mobile.features.touch')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notifications.responsiveness.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notifications.responsiveness.breakpoint')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notifications.responsiveness.calc')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.notifications.responsiveness.resize')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notifications.swipe_delete.title')} icon={<Trash2 />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.notifications.swipe_delete.description')}
                    </p>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Smartphone className="text-purple-600 dark:text-purple-400" size={24} />
                                <h4 className="font-semibold text-purple-600 dark:text-purple-400">{t('sections.notifications.swipe_delete.gesture.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.notifications.swipe_delete.gesture.features.direction')}</li>
                                <li>{t('sections.notifications.swipe_delete.gesture.features.distance')}</li>
                                <li>{t('sections.notifications.swipe_delete.gesture.features.detection')}</li>
                                <li>{t('sections.notifications.swipe_delete.gesture.features.animation')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Trash2 className="text-red-600 dark:text-red-400" size={24} />
                                <h4 className="font-semibold text-red-600 dark:text-red-400">{t('sections.notifications.swipe_delete.deletion.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.notifications.swipe_delete.deletion.features.api')}</li>
                                <li>{t('sections.notifications.swipe_delete.deletion.features.animation')}</li>
                                <li>{t('sections.notifications.swipe_delete.deletion.features.sync')}</li>
                                <li>{t('sections.notifications.swipe_delete.deletion.features.reload')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notifications.swipe_delete.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notifications.swipe_delete.events')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notifications.swipe_delete.function')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1">{t('sections.notifications.swipe_delete.threshold')}</code>
                        <code className="block text-sm text-cyan-600 dark:text-cyan-400 font-mono">{t('sections.notifications.swipe_delete.api')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.notifications.features.title')} icon={<RefreshCw />}>
                <div className="space-y-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <RefreshCw className="text-cyan-600 dark:text-cyan-400" size={20} />
                                <h4 className="font-semibold text-cyan-600 dark:text-cyan-400">{t('sections.notifications.features.polling.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.notifications.features.polling.description')}
                            </p>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Volume2 className="text-orange-600 dark:text-orange-400" size={20} />
                                <h4 className="font-semibold text-orange-600 dark:text-orange-400">{t('sections.notifications.features.sound.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.notifications.features.sound.description')}
                            </p>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Eye className="text-blue-600 dark:text-blue-400" size={20} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.notifications.features.read_unread.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.notifications.features.read_unread.description')}
                            </p>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Trash2 className="text-red-600 dark:text-red-400" size={20} />
                                <h4 className="font-semibold text-red-600 dark:text-red-400">{t('sections.notifications.features.bulk_actions.title')}</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.notifications.features.bulk_actions.description')}
                            </p>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.notifications.features.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.notifications.features.controller')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.notifications.features.endpoints')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.notifications.features.polling_interval')}</code>
                    </div>
                </div>
            </Section>
        </div>
    );
}

