import Hero from '../../components/ui/Hero';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Calendar, UserPlus, Phone, DollarSign, FileText, Users, CalendarCheck, Wallet, Activity } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { motion } from 'framer-motion';

export default function SecretaryDocs() {
    const { t } = useTranslation();

    return (
        <div className="animate-fade-in">
            <Hero
                title={t('sections.secretary.hero.title')}
                subtitle={t('sections.secretary.hero.subtitle')}
                badge={t('sections.secretary.hero.badge')}
            />

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <Card title={t('sections.secretary.cards.appointments.title')} icon={Calendar}>
                    {t('sections.secretary.cards.appointments.desc')}
                </Card>
                <Card title={t('sections.secretary.cards.registration.title')} icon={UserPlus}>
                    {t('sections.secretary.cards.registration.desc')}
                </Card>
                <Card title={t('sections.secretary.cards.communication.title')} icon={Phone}>
                    {t('sections.secretary.cards.communication.desc')}
                </Card>
            </div>

            <Section title={t('sections.secretary.features.title')} id="features" className="mb-16">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                    <Card title={t('sections.secretary.features.dashboard.title')} icon={Calendar}>
                        {t('sections.secretary.features.dashboard.desc')}
                    </Card>
                    <Card title={t('sections.secretary.features.bookings.title')} icon={Calendar}>
                        {t('sections.secretary.features.bookings.desc')}
                    </Card>
                    <Card title={t('sections.secretary.features.patients.title')} icon={Users}>
                        {t('sections.secretary.features.patients.desc')}
                    </Card>
                    <Card title={t('sections.secretary.features.payments.title')} icon={DollarSign}>
                        {t('sections.secretary.features.payments.desc')}
                    </Card>
                    <Card title={t('sections.secretary.features.invoices.title')} icon={FileText}>
                        {t('sections.secretary.features.invoices.desc')}
                    </Card>
                    <Card title={t('sections.secretary.features.profile.title')} icon={UserPlus}>
                        {t('sections.secretary.features.profile.desc')}
                    </Card>
                </div>
            </Section>

            <Section title={t('sections.secretary.workflow.title')} id="workflow" className="mb-16">
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                <div className="space-y-8">
                    <div className="flex gap-4">
                        <div className="flex-none flex flex-col items-center">
                            <div className="w-8 h-8 rounded-full bg-primary-500 text-white flex items-center justify-center font-bold">1</div>
                            <div className="w-0.5 flex-1 bg-gray-200 dark:bg-gray-700 my-2"></div>
                        </div>
                        <div>
                            <h3 className="text-xl font-bold mb-2">{t('sections.secretary.workflow.steps.checkin.title')}</h3>
                            <p className="text-gray-600 dark:text-gray-300">
                                {t('sections.secretary.workflow.steps.checkin.desc')}
                            </p>
                        </div>
                    </div>

                    <div className="flex gap-4">
                        <div className="flex-none flex flex-col items-center">
                            <div className="w-8 h-8 rounded-full bg-primary-500 text-white flex items-center justify-center font-bold">2</div>
                            <div className="w-0.5 flex-1 bg-gray-200 dark:bg-gray-700 my-2"></div>
                        </div>
                        <div>
                            <h3 className="text-xl font-bold mb-2">{t('sections.secretary.workflow.steps.queue.title')}</h3>
                            <p className="text-gray-600 dark:text-gray-300">
                                {t('sections.secretary.workflow.steps.queue.desc')}
                            </p>
                        </div>
                    </div>

                    <div className="flex gap-4">
                        <div className="flex-none flex flex-col items-center">
                            <div className="w-8 h-8 rounded-full bg-primary-500 text-white flex items-center justify-center font-bold">3</div>
                        </div>
                        <div>
                            <h3 className="text-xl font-bold mb-2">{t('sections.secretary.workflow.steps.payment.title')}</h3>
                            <p className="text-gray-600 dark:text-gray-300">
                                {t('sections.secretary.workflow.steps.payment.desc')}
                            </p>
                        </div>
                    </div>
                    </div>

                    {/* Mockup: Secretary Dashboard - Arabic Only */}
                    <motion.div 
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.5 }}
                        className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden"
                        dir="rtl"
                    >
                        {/* Statistics Cards Row */}
                        <div className="p-4 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
                            <div className="grid grid-cols-3 gap-2 mb-4">
                                {/* Total Appointments Card */}
                                <motion.div
                                    initial={{ scale: 0.9, opacity: 0 }}
                                    animate={{ scale: 1, opacity: 1 }}
                                    transition={{ delay: 0.1, duration: 0.3 }}
                                    className="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-2 text-white shadow-lg relative overflow-hidden"
                                >
                                    <div className="absolute top-0 left-0 w-16 h-16 bg-white/10 rounded-full -ml-8 -mt-8"></div>
                                    <div className="relative z-10 text-center">
                                        <div className="text-[9px] opacity-90 mb-1">إجمالي المواعيد</div>
                                        <div className="text-lg font-bold">24</div>
                                        <Calendar className="absolute bottom-1 left-1 w-5 h-5 opacity-20" />
                                    </div>
                                </motion.div>

                                {/* Booked Card */}
                                <motion.div
                                    initial={{ scale: 0.9, opacity: 0 }}
                                    animate={{ scale: 1, opacity: 1 }}
                                    transition={{ delay: 0.2, duration: 0.3 }}
                                    className="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-2 text-white shadow-lg relative overflow-hidden"
                                >
                                    <div className="absolute top-0 left-0 w-16 h-16 bg-white/10 rounded-full -ml-8 -mt-8"></div>
                                    <div className="relative z-10 text-center">
                                        <div className="text-[9px] opacity-90 mb-1">في الانتظار</div>
                                        <div className="text-lg font-bold">8</div>
                                        <CalendarCheck className="absolute bottom-1 left-1 w-5 h-5 opacity-20" />
                                    </div>
                                </motion.div>

                                {/* Checked In Card */}
                                <motion.div
                                    initial={{ scale: 0.9, opacity: 0 }}
                                    animate={{ scale: 1, opacity: 1 }}
                                    transition={{ delay: 0.3, duration: 0.3 }}
                                    className="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-2 text-white shadow-lg relative overflow-hidden"
                                >
                                    <div className="absolute top-0 left-0 w-16 h-16 bg-white/10 rounded-full -ml-8 -mt-8"></div>
                                    <div className="relative z-10 text-center">
                                        <div className="text-[9px] opacity-90 mb-1">تم الحضور</div>
                                        <div className="text-lg font-bold">12</div>
                                        <UserPlus className="absolute bottom-1 left-1 w-5 h-5 opacity-20" />
                                    </div>
                                </motion.div>
                            </div>

                            {/* Quick Actions */}
                            <div className="bg-white dark:bg-gray-800 rounded-xl p-2 shadow-md">
                                <div className="text-[10px] font-semibold text-gray-600 dark:text-gray-400 mb-2 flex items-center gap-1">
                                    <Activity className="w-3 h-3" />
                                    الإجراءات السريعة
                                </div>
                                <div className="flex gap-2">
                                    <motion.div
                                        whileHover={{ scale: 1.05 }}
                                        whileTap={{ scale: 0.95 }}
                                        className="flex-1 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-lg p-2 cursor-pointer border border-blue-200 dark:border-blue-700"
                                    >
                                        <div className="flex items-center gap-2">
                                            <div className="w-7 h-7 bg-blue-500 rounded-lg flex items-center justify-center">
                                                <Calendar className="w-3.5 h-3.5 text-white" />
                                            </div>
                                            <div className="flex-1">
                                                <div className="text-[10px] font-semibold text-gray-700 dark:text-gray-300">الحجوزات</div>
                                                <div className="text-[9px] text-gray-500 dark:text-gray-400">عرض</div>
                                            </div>
                                        </div>
                                    </motion.div>
                                    <motion.div
                                        whileHover={{ scale: 1.05 }}
                                        whileTap={{ scale: 0.95 }}
                                        className="flex-1 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 rounded-lg p-2 cursor-pointer border border-green-200 dark:border-green-700"
                                    >
                                        <div className="flex items-center gap-2">
                                            <div className="w-7 h-7 bg-green-500 rounded-lg flex items-center justify-center">
                                                <Users className="w-3.5 h-3.5 text-white" />
                                            </div>
                                            <div className="flex-1">
                                                <div className="text-[10px] font-semibold text-gray-700 dark:text-gray-300">المرضى</div>
                                                <div className="text-[9px] text-gray-500 dark:text-gray-400">إضافة</div>
                                            </div>
                                        </div>
                                    </motion.div>
                                </div>
                            </div>
                        </div>

                        {/* Today's Appointments Section */}
                        <div className="p-4 border-t border-gray-200 dark:border-gray-800">
                            <div className="flex items-center justify-between mb-3">
                                <div className="flex items-center gap-2">
                                    <CalendarCheck className="w-4 h-4 text-blue-500" />
                                    <span className="text-sm font-semibold text-gray-700 dark:text-gray-300">مواعيد اليوم</span>
                                </div>
                                <span className="text-xs text-gray-500 dark:text-gray-400">عرض الكل ←</span>
                            </div>
                            <div className="space-y-2">
                                {[1, 2].map((_, idx) => (
                                    <motion.div
                                        key={idx}
                                        initial={{ x: 20, opacity: 0 }}
                                        animate={{ x: 0, opacity: 1 }}
                                        transition={{ delay: 0.4 + idx * 0.1 }}
                                        className="flex items-center gap-3 p-2 bg-gray-50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                    >
                                        <div className="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                            <UserPlus className="w-5 h-5 text-white" />
                                        </div>
                                        <div className="flex-1">
                                            <div className="text-xs font-semibold text-gray-700 dark:text-gray-300">اسم المريض</div>
                                            <div className="text-[10px] text-gray-500 dark:text-gray-400">10:30 صباحاً - استشارة</div>
                                        </div>
                                        <div className="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    </motion.div>
                                ))}
                            </div>
                        </div>

                        {/* Recent Payments */}
                        <div className="p-4 border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                            <div className="flex items-center gap-2 mb-3">
                                <Wallet className="w-4 h-4 text-green-500" />
                                <span className="text-sm font-semibold text-gray-700 dark:text-gray-300">المدفوعات الأخيرة</span>
                            </div>
                            <div className="space-y-2">
                                {[1, 2].map((_, idx) => (
                                    <motion.div
                                        key={idx}
                                        initial={{ x: 20, opacity: 0 }}
                                        animate={{ x: 0, opacity: 1 }}
                                        transition={{ delay: 0.6 + idx * 0.1 }}
                                        className="flex items-center justify-between p-2 bg-white dark:bg-gray-800 rounded-lg"
                                    >
                                        <div className="flex items-center gap-2">
                                            <div className="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                                <DollarSign className="w-4 h-4 text-white" />
                                            </div>
                                            <div>
                                                <div className="text-xs font-semibold text-gray-700 dark:text-gray-300">اسم المريض</div>
                                                <div className="text-[10px] text-gray-500 dark:text-gray-400">دفعة</div>
                                            </div>
                                        </div>
                                        <div className="text-left">
                                            <div className="text-xs font-bold text-green-600 dark:text-green-400">250 ج.م</div>
                                            <div className="text-[10px] text-gray-500 dark:text-gray-400">منذ 5 دقائق</div>
                                        </div>
                                    </motion.div>
                                ))}
                            </div>
                        </div>
                    </motion.div>
                </div>
            </Section>
        </div>
    );
}
