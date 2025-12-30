import Hero from '../../components/ui/Hero';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Activity, Pill, Glasses, CalendarCheck, FileText, DollarSign, BarChart3, Users, Bell, StickyNote, Eye, Settings, Calendar, TrendingUp, AlertCircle, UserPlus } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { motion } from 'framer-motion';

export default function DoctorDocs() {
    const { t } = useTranslation();

    return (
        <div className="animate-fade-in">
            <Hero
                title={t('sections.doctor.hero.title')}
                subtitle={t('sections.doctor.hero.subtitle')}
                badge={t('sections.doctor.hero.badge')}
            />

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <Card title={t('sections.doctor.cards.diagnosis.title')} icon={Activity}>
                    {t('sections.doctor.cards.diagnosis.desc')}
                </Card>
                <Card title={t('sections.doctor.cards.prescriptions.title')} icon={Pill}>
                    {t('sections.doctor.cards.prescriptions.desc')}
                </Card>
                <Card title={t('sections.doctor.cards.refraction.title')} icon={Glasses}>
                    {t('sections.doctor.cards.refraction.desc')}
                </Card>
                <Card title={t('sections.doctor.cards.appointments.title')} icon={CalendarCheck}>
                    {t('sections.doctor.cards.appointments.desc')}
                </Card>
            </div>

            <Section title={t('sections.doctor.features.title')} id="features" className="mb-16">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">
                    <Card title={t('sections.doctor.features.patients.title')} icon={Users}>
                        {t('sections.doctor.features.patients.desc')}
                    </Card>
                    <Card title={t('sections.doctor.features.calendar.title')} icon={CalendarCheck}>
                        {t('sections.doctor.features.calendar.desc')}
                    </Card>
                    <Card title={t('sections.doctor.features.forum.title')} icon={FileText}>
                        {t('sections.doctor.features.forum.desc')}
                    </Card>
                    <Card title={t('sections.doctor.features.drugs.title')} icon={Pill}>
                        {t('sections.doctor.features.drugs.desc')}
                    </Card>
                    <Card title={t('sections.doctor.features.finance.title')} icon={DollarSign}>
                        {t('sections.doctor.features.finance.desc')}
                    </Card>
                    <Card title={t('sections.doctor.features.reports.title')} icon={BarChart3}>
                        {t('sections.doctor.features.reports.desc')}
                    </Card>
                    <Card title={t('sections.doctor.features.medications.title')} icon={Pill}>
                        {t('sections.doctor.features.medications.desc')}
                    </Card>
                    <Card title={t('sections.doctor.features.glasses.title')} icon={Glasses}>
                        {t('sections.doctor.features.glasses.desc')}
                    </Card>
                    <Card title={t('sections.doctor.features.media.title')} icon={Eye}>
                        {t('sections.doctor.features.media.desc')}
                    </Card>
                    <Card title={t('sections.doctor.features.alerts.title')} icon={Bell}>
                        {t('sections.doctor.features.alerts.desc')}
                    </Card>
                    <Card title={t('sections.doctor.features.notes.title')} icon={StickyNote}>
                        {t('sections.doctor.features.notes.desc')}
                    </Card>
                    <Card title={t('sections.doctor.features.profile.title')} icon={Settings}>
                        {t('sections.doctor.features.profile.desc')}
                    </Card>
                </div>
            </Section>

            <Section title={t('sections.doctor.exam.title')} id="exam" className="mb-16">
                <p className="text-gray-600 dark:text-gray-300 mb-8">
                    {t('sections.doctor.exam.content')}
                </p>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                    <Card className="bg-primary-50 dark:bg-primary-900/10 border-primary-100 dark:border-primary-900/30">
                        <h3 className="font-bold text-lg mb-2">{t('sections.doctor.exam.features_title')}</h3>
                        <ul className="space-y-2 text-sm">
                            <li className="flex items-center gap-2">✅ {t('sections.doctor.exam.features.templates')}</li>
                            <li className="flex items-center gap-2">✅ {t('sections.doctor.exam.features.drawing')}</li>
                            <li className="flex items-center gap-2">✅ {t('sections.doctor.exam.features.imaging')}</li>
                        </ul>
                    </Card>

                    {/* Mockup: Doctor Dashboard */}
                    <motion.div 
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.5 }}
                        className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden"
                    >
                        {/* Statistics Cards Row */}
                        <div className="p-4 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
                            <div className="grid grid-cols-3 gap-3 mb-4">
                                {/* Total Appointments Card */}
                                <motion.div
                                    initial={{ scale: 0.9, opacity: 0 }}
                                    animate={{ scale: 1, opacity: 1 }}
                                    transition={{ delay: 0.1, duration: 0.3 }}
                                    className="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-3 text-white shadow-lg relative overflow-hidden"
                                >
                                    <div className="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -mr-10 -mt-10"></div>
                                    <div className="relative z-10">
                                        <div className="text-xs opacity-90 mb-1">Total Today</div>
                                        <div className="text-2xl font-bold">24</div>
                                        <Calendar className="absolute bottom-2 right-2 w-8 h-8 opacity-20" />
                                    </div>
                                </motion.div>

                                {/* Completed Card */}
                                <motion.div
                                    initial={{ scale: 0.9, opacity: 0 }}
                                    animate={{ scale: 1, opacity: 1 }}
                                    transition={{ delay: 0.2, duration: 0.3 }}
                                    className="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-3 text-white shadow-lg relative overflow-hidden"
                                >
                                    <div className="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -mr-10 -mt-10"></div>
                                    <div className="relative z-10">
                                        <div className="text-xs opacity-90 mb-1">Completed</div>
                                        <div className="text-2xl font-bold">18</div>
                                        <Activity className="absolute bottom-2 right-2 w-8 h-8 opacity-20" />
                                    </div>
                                </motion.div>

                                {/* Missed Card */}
                                <motion.div
                                    initial={{ scale: 0.9, opacity: 0 }}
                                    animate={{ scale: 1, opacity: 1 }}
                                    transition={{ delay: 0.3, duration: 0.3 }}
                                    className="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-3 text-white shadow-lg relative overflow-hidden"
                                >
                                    <div className="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -mr-10 -mt-10"></div>
                                    <div className="relative z-10">
                                        <div className="text-xs opacity-90 mb-1">Missed</div>
                                        <div className="text-2xl font-bold">3</div>
                                        <AlertCircle className="absolute bottom-2 right-2 w-8 h-8 opacity-20" />
                                    </div>
                                </motion.div>
                            </div>

                            {/* Quick Actions */}
                            <div className="bg-white dark:bg-gray-800 rounded-xl p-3 shadow-md">
                                <div className="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2 flex items-center gap-1">
                                    <Activity className="w-3 h-3" />
                                    Quick Actions
                                </div>
                                <div className="flex gap-2">
                                    <motion.div
                                        whileHover={{ scale: 1.05 }}
                                        whileTap={{ scale: 0.95 }}
                                        className="flex-1 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-lg p-2 cursor-pointer border border-blue-200 dark:border-blue-700"
                                    >
                                        <div className="flex items-center gap-2">
                                            <div className="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                                <Users className="w-4 h-4 text-white" />
                                            </div>
                                            <div className="flex-1">
                                                <div className="text-xs font-semibold text-gray-700 dark:text-gray-300">Patients</div>
                                                <div className="text-[10px] text-gray-500 dark:text-gray-400">View & Add</div>
                                            </div>
                                        </div>
                                    </motion.div>
                                    <motion.div
                                        whileHover={{ scale: 1.05 }}
                                        whileTap={{ scale: 0.95 }}
                                        className="flex-1 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 rounded-lg p-2 cursor-pointer border border-green-200 dark:border-green-700"
                                    >
                                        <div className="flex items-center gap-2">
                                            <div className="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                                <Calendar className="w-4 h-4 text-white" />
                                            </div>
                                            <div className="flex-1">
                                                <div className="text-xs font-semibold text-gray-700 dark:text-gray-300">Calendar</div>
                                                <div className="text-[10px] text-gray-500 dark:text-gray-400">Schedule</div>
                                            </div>
                                        </div>
                                    </motion.div>
                                </div>
                            </div>
                        </div>

                        {/* Upcoming Appointments Section */}
                        <div className="p-4 border-t border-gray-200 dark:border-gray-800">
                            <div className="flex items-center justify-between mb-3">
                                <div className="flex items-center gap-2">
                                    <CalendarCheck className="w-4 h-4 text-blue-500" />
                                    <span className="text-sm font-semibold text-gray-700 dark:text-gray-300">Upcoming</span>
                                </div>
                                <span className="text-xs text-gray-500 dark:text-gray-400">View All →</span>
                            </div>
                            <div className="space-y-2">
                                {[1, 2].map((_, idx) => (
                                    <motion.div
                                        key={idx}
                                        initial={{ x: -20, opacity: 0 }}
                                        animate={{ x: 0, opacity: 1 }}
                                        transition={{ delay: 0.4 + idx * 0.1 }}
                                        className="flex items-center gap-3 p-2 bg-gray-50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                    >
                                        <div className="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                            <UserPlus className="w-5 h-5 text-white" />
                                        </div>
                                        <div className="flex-1">
                                            <div className="text-xs font-semibold text-gray-700 dark:text-gray-300">Patient Name</div>
                                            <div className="text-[10px] text-gray-500 dark:text-gray-400">10:30 AM - Consultation</div>
                                        </div>
                                        <div className="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    </motion.div>
                                ))}
                            </div>
                        </div>

                        {/* Chart Section */}
                        <div className="p-4 border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                            <div className="flex items-center gap-2 mb-3">
                                <TrendingUp className="w-4 h-4 text-purple-500" />
                                <span className="text-sm font-semibold text-gray-700 dark:text-gray-300">Appointments Trend</span>
                            </div>
                            <div className="h-24 bg-white dark:bg-gray-800 rounded-lg p-2 relative overflow-hidden">
                                {/* Mock Chart Bars */}
                                <div className="absolute inset-0 flex items-end justify-around px-2 pb-2">
                                    {[65, 80, 45, 90, 70, 85, 60].map((height, idx) => (
                                        <motion.div
                                            key={idx}
                                            initial={{ height: 0 }}
                                            animate={{ height: `${height}%` }}
                                            transition={{ delay: 0.6 + idx * 0.05, duration: 0.5, ease: "easeOut" }}
                                            className="w-6 bg-gradient-to-t from-purple-500 to-purple-400 rounded-t"
                                        />
                                    ))}
                                </div>
                            </div>
                    </div>
                    </motion.div>
                </div>
            </Section>

            <Section title={t('sections.doctor.meds.title')} id="meds">
                <p className="text-gray-600 dark:text-gray-300">
                    {t('sections.doctor.meds.content')}
                </p>
            </Section>
        </div>
    );
}
