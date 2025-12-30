import Hero from '../../components/ui/Hero';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Settings, Users, FileText, Database, HardDrive, Eye, User, CalendarCheck, DollarSign, Activity, Heart, Server } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { motion } from 'framer-motion';

export default function AdminDocs() {
    const { t } = useTranslation();

    return (
        <div className="animate-fade-in">
            <Hero
                title={t('sections.admin.hero.title')}
                subtitle={t('sections.admin.hero.subtitle')}
                badge={t('sections.admin.hero.badge')}
            />

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
                <Card title={t('sections.admin.cards.users.title')} icon={Users} className="mb-4">
                    {t('sections.admin.cards.users.desc')}
                </Card>
                <Card title={t('sections.admin.cards.settings.title')} icon={Settings} className="mb-4">
                    {t('sections.admin.cards.settings.desc')}
                </Card>
                <Card title={t('sections.admin.cards.backup.title')} icon={Database} className="mb-4">
                    {t('sections.admin.cards.backup.desc')}
                </Card>
                <Card title={t('sections.admin.cards.media.title')} icon={Eye} className="mb-4">
                    {t('sections.admin.cards.media.desc')}
                </Card>
            </div>

            <Section title={t('sections.admin.users.title')} id="users" className="mb-16">
                <p className="text-gray-600 dark:text-gray-300 mb-8">
                    {t('sections.admin.users.content')}
                </p>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                    <div className="space-y-4">
                        <div className="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                            <h4 className="font-semibold mb-2">{t('sections.admin.users.features.title')}</h4>
                            <ul className="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-300">
                                <li>{t('sections.admin.users.features.create')}</li>
                                <li>{t('sections.admin.users.features.update')}</li>
                                <li>{t('sections.admin.users.features.delete')}</li>
                                <li>{t('sections.admin.users.features.roles')}</li>
                                <li>{t('sections.admin.users.features.view_as')}</li>
                            </ul>
                        </div>
                    </div>

                    {/* Mockup: Admin Dashboard */}
                    <motion.div 
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.5 }}
                        className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden"
                    >
                        {/* System Statistics Cards */}
                        <div className="p-4 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
                            <div className="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-3 flex items-center gap-1">
                                <Activity className="w-3 h-3" />
                                System Statistics
                            </div>
                            <div className="grid grid-cols-2 gap-3 mb-4">
                                {/* Users Card */}
                                <motion.div
                                    initial={{ scale: 0.9, opacity: 0 }}
                                    animate={{ scale: 1, opacity: 1 }}
                                    transition={{ delay: 0.1, duration: 0.3 }}
                                    className="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-3 text-white shadow-lg relative overflow-hidden"
                                >
                                    <div className="absolute top-0 right-0 w-16 h-16 bg-white/10 rounded-full -mr-8 -mt-8"></div>
                                    <div className="relative z-10">
                                        <div className="text-[10px] opacity-90 mb-1">Total Users</div>
                                        <div className="text-xl font-bold">42</div>
                                        <div className="text-[9px] opacity-80 mt-1">12 Doctors</div>
                                        <Users className="absolute bottom-1 right-1 w-6 h-6 opacity-20" />
                                    </div>
                                </motion.div>

                                {/* Patients Card */}
                                <motion.div
                                    initial={{ scale: 0.9, opacity: 0 }}
                                    animate={{ scale: 1, opacity: 1 }}
                                    transition={{ delay: 0.2, duration: 0.3 }}
                                    className="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-3 text-white shadow-lg relative overflow-hidden"
                                >
                                    <div className="absolute top-0 right-0 w-16 h-16 bg-white/10 rounded-full -mr-8 -mt-8"></div>
                                    <div className="relative z-10">
                                        <div className="text-[10px] opacity-90 mb-1">Total Patients</div>
                                        <div className="text-xl font-bold">1,248</div>
                                        <div className="text-[9px] opacity-80 mt-1">1,156 Active</div>
                                        <User className="absolute bottom-1 right-1 w-6 h-6 opacity-20" />
                                    </div>
                                </motion.div>

                                {/* Appointments Card */}
                                <motion.div
                                    initial={{ scale: 0.9, opacity: 0 }}
                                    animate={{ scale: 1, opacity: 1 }}
                                    transition={{ delay: 0.3, duration: 0.3 }}
                                    className="bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl p-3 text-white shadow-lg relative overflow-hidden"
                                >
                                    <div className="absolute top-0 right-0 w-16 h-16 bg-white/10 rounded-full -mr-8 -mt-8"></div>
                                    <div className="relative z-10">
                                        <div className="text-[10px] opacity-90 mb-1">Appointments</div>
                                        <div className="text-xl font-bold">856</div>
                                        <div className="text-[9px] opacity-80 mt-1">30 days</div>
                                        <CalendarCheck className="absolute bottom-1 right-1 w-6 h-6 opacity-20" />
                                    </div>
                                </motion.div>

                                {/* Revenue Card */}
                                <motion.div
                                    initial={{ scale: 0.9, opacity: 0 }}
                                    animate={{ scale: 1, opacity: 1 }}
                                    transition={{ delay: 0.4, duration: 0.3 }}
                                    className="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-3 text-white shadow-lg relative overflow-hidden"
                                >
                                    <div className="absolute top-0 right-0 w-16 h-16 bg-white/10 rounded-full -mr-8 -mt-8"></div>
                                    <div className="relative z-10">
                                        <div className="text-[10px] opacity-90 mb-1">Revenue</div>
                                        <div className="text-xl font-bold">45.2K</div>
                                        <div className="text-[9px] opacity-80 mt-1">30 days</div>
                                        <DollarSign className="absolute bottom-1 right-1 w-6 h-6 opacity-20" />
                                    </div>
                                </motion.div>
                            </div>
                        </div>

                        {/* System Health Section */}
                        <div className="p-4 border-t border-gray-200 dark:border-gray-800">
                            <div className="flex items-center justify-between mb-3">
                                <div className="flex items-center gap-2">
                                    <Heart className="w-4 h-4 text-red-500" />
                                    <span className="text-sm font-semibold text-gray-700 dark:text-gray-300">System Health</span>
                                </div>
                            </div>
                            <div className="space-y-2">
                                {[
                                    { label: 'Database', status: 'Connected', color: 'green' },
                                    { label: 'Storage', status: '65%', color: 'green' },
                                    { label: 'PHP Version', status: '8.2', color: 'blue' }
                                ].map((item, idx) => (
                                    <motion.div
                                        key={idx}
                                        initial={{ x: -20, opacity: 0 }}
                                        animate={{ x: 0, opacity: 1 }}
                                        transition={{ delay: 0.5 + idx * 0.1 }}
                                        className="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800/50 rounded-lg"
                                    >
                                        <div className="flex items-center gap-2">
                                            <Server className="w-4 h-4 text-gray-500" />
                                            <span className="text-xs text-gray-700 dark:text-gray-300">{item.label}</span>
                                        </div>
                                        <span className={`text-xs px-2 py-1 rounded ${
                                            item.color === 'green' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
                                            item.color === 'blue' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' :
                                            'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                                        }`}>
                                            {item.status}
                                        </span>
                                    </motion.div>
                                ))}
                            </div>
                        </div>

                        {/* Recent Activities */}
                        <div className="p-4 border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                            <div className="flex items-center gap-2 mb-3">
                                <Activity className="w-4 h-4 text-purple-500" />
                                <span className="text-sm font-semibold text-gray-700 dark:text-gray-300">Recent Activities</span>
                            </div>
                            <div className="space-y-2">
                                {[1, 2].map((_, idx) => (
                                    <motion.div
                                        key={idx}
                                        initial={{ x: -20, opacity: 0 }}
                                        animate={{ x: 0, opacity: 1 }}
                                        transition={{ delay: 0.8 + idx * 0.1 }}
                                        className="flex items-start gap-2 p-2 bg-white dark:bg-gray-800 rounded-lg"
                                    >
                                        <div className="w-6 h-6 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                                            <Users className="w-3 h-3 text-white" />
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="text-xs font-semibold text-gray-700 dark:text-gray-300">User Action</div>
                                            <div className="text-[10px] text-gray-500 dark:text-gray-400">2 minutes ago</div>
                                        </div>
                                    </motion.div>
                                ))}
                            </div>
                        </div>
                    </motion.div>
                </div>
            </Section>

            <Section title={t('sections.admin.config.title')} id="config" className="mb-16">
                <p className="text-gray-600 dark:text-gray-300 mb-4">
                    {t('sections.admin.config.content')}
                </p>
                <ul className="list-disc list-inside space-y-2 text-gray-600 dark:text-gray-300">
                    <li>{t('sections.admin.config.items.clinic_info')}</li>
                    <li>{t('sections.admin.config.items.backup')}</li>
                    <li>{t('sections.admin.config.items.notifications')}</li>
                    <li>{t('sections.admin.config.items.system_settings')}</li>
                </ul>
            </Section>

            <Section title={t('sections.admin.backup.title')} id="backup" className="mb-16">
                <p className="text-gray-600 dark:text-gray-300 mb-4">
                    {t('sections.admin.backup.content')}
                </p>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Card title={t('sections.admin.backup.types.database.title')} icon={Database}>
                        {t('sections.admin.backup.types.database.desc')}
                    </Card>
                    <Card title={t('sections.admin.backup.types.full.title')} icon={HardDrive}>
                        {t('sections.admin.backup.types.full.desc')}
                    </Card>
                    <Card title={t('sections.admin.backup.types.website.title')} icon={FileText}>
                        {t('sections.admin.backup.types.website.desc')}
                    </Card>
                </div>
            </Section>

            <Section title={t('sections.admin.media.title')} id="media">
                <p className="text-gray-600 dark:text-gray-300 mb-4">
                    {t('sections.admin.media.content')}
                </p>
                <ul className="list-disc list-inside space-y-2 text-gray-600 dark:text-gray-300">
                    <li>{t('sections.admin.media.features.list')}</li>
                    <li>{t('sections.admin.media.features.delete')}</li>
                    <li>{t('sections.admin.media.features.backup')}</li>
                    <li>{t('sections.admin.media.features.restore')}</li>
                </ul>
            </Section>
        </div>
    );
}
