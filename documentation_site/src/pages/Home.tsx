import Hero from '../components/ui/Hero';
import Section from '../components/ui/Section';
import Card from '../components/ui/Card';
import { Link } from 'react-router-dom';
import { Activity, Shield, Users, Database, Menu, CloudSun, Clock, Calendar, Calculator, Sparkles } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useEffect, useState } from 'react';

export default function Home() {
    const { t } = useTranslation();
    const [currentTime, setCurrentTime] = useState(new Date());
    const [appointmentIndex, setAppointmentIndex] = useState(0);

    // Update time every second
    useEffect(() => {
        const timer = setInterval(() => {
            setCurrentTime(new Date());
        }, 1000);
        return () => clearInterval(timer);
    }, []);

    // Simulate appointment scrolling
    useEffect(() => {
        const appointments = [
            { name: 'Ahmed Mohamed', time: '10:30 AM' },
            { name: 'Fatima Ali', time: '11:15 AM' },
            { name: 'Omar Hassan', time: '2:00 PM' },
        ];
        const timer = setInterval(() => {
            setAppointmentIndex((prev) => (prev + 1) % appointments.length);
        }, 3000);
        return () => clearInterval(timer);
    }, []);

    const formatTime = (date: Date) => {
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
    };

    const formatDate = (date: Date) => {
        return date.toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric'
        });
    };

    const getClockHandRotation = (value: number, max: number) => {
        return (value / max) * 360;
    };

    const hours = currentTime.getHours() % 12;
    const minutes = currentTime.getMinutes();
    const seconds = currentTime.getSeconds();

    const appointments = [
        { name: 'Ahmed Mohamed', time: '10:30 AM' },
        { name: 'Fatima Ali', time: '11:15 AM' },
        { name: 'Omar Hassan', time: '2:00 PM' },
    ];

    // Video autoplay handler
    useEffect(() => {
        const video = document.getElementById('dashboard-video') as HTMLVideoElement;
        if (video) {
            // Try to play the video
            const playPromise = video.play();
            
            // Handle promise rejection (autoplay blocked)
            if (playPromise !== undefined) {
                playPromise
                    .then(() => {
                        // Autoplay started successfully
                        console.log('Video autoplay started');
                    })
                    .catch((error) => {
                        // Autoplay was prevented
                        console.log('Autoplay prevented, trying with muted:', error);
                        // Try again with muted
                        video.muted = true;
                        video.play().catch((err) => {
                            console.log('Autoplay failed even with muted:', err);
                        });
                    });
            }

            // Ensure video plays when it comes into view
            const observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            video.play().catch(() => {
                                // If play fails, try with muted
                                if (!video.muted) {
                                    video.muted = true;
                                    video.play().catch(() => {});
                                }
                            });
                        }
                    });
                },
                { threshold: 0.5 }
            );

            observer.observe(video);

            return () => {
                observer.disconnect();
            };
        }
    }, []);

    return (
        <div className="animate-fade-in">
            <Hero
                title={t('sections.home.hero.title')}
                subtitle={t('sections.home.hero.subtitle')}
                badge={t('sections.home.hero.badge')}
            />

            <Section title={t('sections.home.whats_new.title')} id="whats-new" icon={<Sparkles />} className="mb-16">
                <div className="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-6 mb-6">
                    <div className="flex items-center gap-3 mb-4">
                        <div className="p-2 bg-gradient-to-r from-purple-500 to-pink-600 rounded-lg text-white">
                            <Sparkles size={24} />
                        </div>
                        <div>
                            <h3 className="text-xl font-bold text-gray-900 dark:text-white">
                                {t('sections.home.whats_new.version')}
                            </h3>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.home.whats_new.subtitle')}
                            </p>
                        </div>
                    </div>
                    <p className="text-gray-700 dark:text-gray-300 mb-6">
                        {t('sections.home.whats_new.description')}
                    </p>
                </div>

                {/* Dashboard Video Section */}
                <div className="mb-8 rounded-xl overflow-hidden border-2 border-gray-300 dark:border-gray-700 shadow-2xl bg-gradient-to-r from-slate-50 to-gray-100 dark:from-slate-900 dark:to-gray-800">
                    <div className="relative w-full" style={{ paddingBottom: '56.25%' }}>
                        <video
                            id="dashboard-video"
                            className="absolute top-0 left-0 w-full h-full object-cover"
                            controls
                            autoPlay
                            loop
                            muted
                            playsInline
                            preload="auto"
                        >
                            <source src="/docs/opth/assets/videos/dashboard/05_output.mp4" type="video/mp4" />
                            {t('sections.home.dashboard_video.fallback')}
                        </video>
                    </div>
                </div>
                {t('sections.home.dashboard_video.description') && (
                    <p className="text-gray-700 dark:text-gray-300 mb-6 text-center">
                        {t('sections.home.dashboard_video.description')}
                    </p>
                )}

                
                {/* Version Updates Cards */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div className="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-5">
                        <h4 className="font-semibold text-indigo-900 dark:text-indigo-100 mb-3 flex items-center gap-2">
                            <Sparkles size={20} />
                            {t('sections.home.whats_new.v7_2_8.title')}
                        </h4>
                        <p className="text-sm text-indigo-800 dark:text-indigo-200 mb-3">
                            {t('sections.home.whats_new.v7_2_8.description')}
                        </p>
                        <ul className="space-y-2 text-sm text-indigo-700 dark:text-indigo-300">
                            <li className="flex items-start gap-2">
                                <span className="text-indigo-500 mt-1">•</span>
                                <span>{t('sections.home.whats_new.v7_2_8.updates.version_update')}</span>
                            </li>
                            <li className="flex items-start gap-2">
                                <span className="text-indigo-500 mt-1">•</span>
                                <span>{t('sections.home.whats_new.v7_2_8.updates.css_enhancements')}</span>
                            </li>
                            <li className="flex items-start gap-2">
                                <span className="text-indigo-500 mt-1">•</span>
                                <span>{t('sections.home.whats_new.v7_2_8.updates.theme_toggle')}</span>
                            </li>
                            <li className="flex items-start gap-2">
                                <span className="text-indigo-500 mt-1">•</span>
                                <span>{t('sections.home.whats_new.v7_2_8.updates.wave_animation')}</span>
                            </li>
                            <li className="flex items-start gap-2">
                                <span className="text-indigo-500 mt-1">•</span>
                                <span>{t('sections.home.whats_new.v7_2_8.updates.login_pages')}</span>
                            </li>
                        </ul>
                    </div>

                    <div className="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-5">
                        <h4 className="font-semibold text-purple-900 dark:text-purple-100 mb-3 flex items-center gap-2">
                            <Sparkles size={20} />
                            {t('sections.home.whats_new.v7_2_7.title')}
                        </h4>
                        <p className="text-sm text-purple-800 dark:text-purple-200 mb-3">
                            {t('sections.home.whats_new.v7_2_7.description')}
                        </p>
                        <ul className="space-y-2 text-sm text-purple-700 dark:text-purple-300">
                            <li className="flex items-start gap-2">
                                <span className="text-purple-500 mt-1">•</span>
                                <span>{t('sections.home.whats_new.v7_2_7.updates.notice_bar')}</span>
                            </li>
                            <li className="flex items-start gap-2">
                                <span className="text-purple-500 mt-1">•</span>
                                <span>{t('sections.home.whats_new.v7_2_7.updates.appointment_docs')}</span>
                            </li>
                            <li className="flex items-start gap-2">
                                <span className="text-purple-500 mt-1">•</span>
                                <span>{t('sections.home.whats_new.v7_2_7.updates.documentation_enhancements')}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {/* Notice Bar Mockup */}
                <div className="mb-8 rounded-xl overflow-hidden border-2 border-gray-300 dark:border-gray-700 shadow-2xl bg-gradient-to-r from-slate-50 to-gray-100 dark:from-slate-900 dark:to-gray-800">
                    <div className="notice-bar-mockup bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <div className="flex items-center justify-between px-4 py-3 gap-2 md:gap-4 flex-wrap md:flex-nowrap">
                            {/* Weather Widget */}
                            <div className="flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 flex-shrink-0">
                                <div className="relative">
                                    <CloudSun className="text-blue-500 dark:text-blue-400" size={20} />
                                    <div className="absolute -top-1 -right-1 w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
                                </div>
                                <div className="flex flex-col">
                                    <span className="text-sm font-semibold text-gray-900 dark:text-white">24°C</span>
                                    <span className="text-xs text-gray-600 dark:text-gray-400">Cairo</span>
                                </div>
                                <div className="flex items-center gap-1">
                                    <div className="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                                    <div className="w-1.5 h-1.5 bg-yellow-500 rounded-full"></div>
                                </div>
                            </div>

                            {/* Clock Widget */}
                            <div className="flex items-center gap-2 px-3 py-2 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 cursor-pointer hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors flex-shrink-0">
                                <div className="relative w-10 h-10">
                                    <svg className="w-10 h-10 transform -rotate-90" viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" strokeWidth="2" className="text-gray-300 dark:text-gray-600" />
                                        <g style={{ transform: `rotate(${getClockHandRotation(hours * 60 + minutes, 12 * 60)}deg)`, transformOrigin: '50% 50%' }}>
                                            <line
                                                x1="50" y1="50"
                                                x2="50" y2="25"
                                                stroke="currentColor"
                                                strokeWidth="3"
                                                strokeLinecap="round"
                                                className="text-gray-900 dark:text-white"
                                            />
                                        </g>
                                        <g style={{ transform: `rotate(${getClockHandRotation(minutes, 60)}deg)`, transformOrigin: '50% 50%' }}>
                                            <line
                                                x1="50" y1="50"
                                                x2="50" y2="15"
                                                stroke="currentColor"
                                                strokeWidth="2"
                                                strokeLinecap="round"
                                                className="text-gray-700 dark:text-gray-300"
                                            />
                                        </g>
                                        <g style={{ transform: `rotate(${getClockHandRotation(seconds, 60)}deg)`, transformOrigin: '50% 50%' }}>
                                            <line
                                                x1="50" y1="50"
                                                x2="50" y2="20"
                                                stroke="currentColor"
                                                strokeWidth="1"
                                                strokeLinecap="round"
                                                className="text-red-500"
                                            />
                                        </g>
                                        <circle cx="50" cy="50" r="3" fill="currentColor" className="text-gray-900 dark:text-white" />
                                    </svg>
                                </div>
                                <div className="flex flex-col">
                                    <span className="text-sm font-semibold text-gray-900 dark:text-white">{formatTime(currentTime)}</span>
                                    <span className="text-xs text-gray-600 dark:text-gray-400">{formatDate(currentTime)}</span>
                                </div>
                            </div>

                            {/* Appointments Widget */}
                            <div className="flex-1 flex items-center gap-2 px-3 py-2 rounded-lg bg-purple-50 dark:bg-purple-900/30 border border-purple-200 dark:border-purple-800 overflow-hidden min-w-0">
                                <Calendar className="text-purple-500 dark:text-purple-400 flex-shrink-0" size={18} />
                                <span className="text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap hidden sm:inline">Next:</span>
                                <div className="flex-1 overflow-hidden relative h-6">
                                    <div
                                        className="absolute inset-0 transition-transform duration-500 ease-in-out"
                                        style={{ transform: `translateY(-${appointmentIndex * 100}%)` }}
                                    >
                                        {appointments.map((apt, idx) => (
                                            <div key={idx} className="flex items-center justify-between h-6">
                                                <span className="text-sm font-semibold text-gray-900 dark:text-white truncate">{apt.name}</span>
                                                <span className="text-xs text-gray-600 dark:text-gray-400 ml-2 whitespace-nowrap">{apt.time}</span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>

                            {/* Tools Button */}
                            <div className="flex items-center gap-2 px-3 py-2 rounded-lg bg-orange-50 dark:bg-orange-900/30 border border-orange-200 dark:border-orange-800 cursor-pointer hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors flex-shrink-0">
                                <Calculator className="text-orange-500 dark:text-orange-400" size={18} />
                                <span className="text-sm font-semibold text-gray-900 dark:text-white hidden md:inline">Tools</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <Card
                        title={t('sections.home.whats_new.tool_bar.title')}
                        icon={Menu}
                        className="bg-white dark:bg-slate-800 border-2 border-purple-200 dark:border-purple-800"
                    >
                        <div className="space-y-3">
                            <p className="text-gray-700 dark:text-gray-300">
                                {t('sections.home.whats_new.tool_bar.description')}
                            </p>
                            <div className="grid grid-cols-1 gap-3 mt-4">
                                <div className="flex items-start gap-3 p-3 bg-gray-50 dark:bg-slate-900/50 rounded-lg">
                                    <CloudSun className="text-blue-500 dark:text-blue-400 flex-shrink-0 mt-0.5" size={20} />
                                    <div>
                                        <h4 className="font-semibold text-gray-900 dark:text-white mb-1">
                                            {t('sections.home.whats_new.tool_bar.weather.title')}
                                        </h4>
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            {t('sections.home.whats_new.tool_bar.weather.description')}
                                        </p>
                                    </div>
                                </div>
                                <div className="flex items-start gap-3 p-3 bg-gray-50 dark:bg-slate-900/50 rounded-lg">
                                    <Clock className="text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" size={20} />
                                    <div>
                                        <h4 className="font-semibold text-gray-900 dark:text-white mb-1">
                                            {t('sections.home.whats_new.tool_bar.datetime.title')}
                                        </h4>
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            {t('sections.home.whats_new.tool_bar.datetime.description')}
                                        </p>
                                    </div>
                                </div>
                                <div className="flex items-start gap-3 p-3 bg-gray-50 dark:bg-slate-900/50 rounded-lg">
                                    <Calendar className="text-purple-500 dark:text-purple-400 flex-shrink-0 mt-0.5" size={20} />
                                    <div>
                                        <h4 className="font-semibold text-gray-900 dark:text-white mb-1">
                                            {t('sections.home.whats_new.tool_bar.appointments.title')}
                                        </h4>
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            {t('sections.home.whats_new.tool_bar.appointments.description')}
                                        </p>
                                    </div>
                                </div>
                                <div className="flex items-start gap-3 p-3 bg-gray-50 dark:bg-slate-900/50 rounded-lg">
                                    <Calculator className="text-orange-500 dark:text-orange-400 flex-shrink-0 mt-0.5" size={20} />
                                    <div>
                                        <h4 className="font-semibold text-gray-900 dark:text-white mb-1">
                                            {t('sections.home.whats_new.tool_bar.tools.title')}
                                        </h4>
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            {t('sections.home.whats_new.tool_bar.tools.description')}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <div className="space-y-6">
                        <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-5">
                            <h4 className="font-semibold text-blue-900 dark:text-blue-100 mb-2 flex items-center gap-2">
                                <Activity size={20} />
                                {t('sections.home.whats_new.highlights.title')}
                            </h4>
                            <ul className="space-y-2 text-sm text-blue-800 dark:text-blue-200">
                                <li className="flex items-start gap-2">
                                    <span className="text-blue-500 mt-1">•</span>
                                    <span>{t('sections.home.whats_new.highlights.mobile')}</span>
                                </li>
                                <li className="flex items-start gap-2">
                                    <span className="text-blue-500 mt-1">•</span>
                                    <span>{t('sections.home.whats_new.highlights.auto_detect')}</span>
                                </li>
                                <li className="flex items-start gap-2">
                                    <span className="text-blue-500 mt-1">•</span>
                                    <span>{t('sections.home.whats_new.highlights.real_time')}</span>
                                </li>
                                <li className="flex items-start gap-2">
                                    <span className="text-blue-500 mt-1">•</span>
                                    <span>{t('sections.home.whats_new.highlights.clinical')}</span>
                                </li>
                            </ul>
                        </div>

                        <div className="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-800 rounded-lg p-5">
                            <h4 className="font-semibold text-green-900 dark:text-green-100 mb-2 flex items-center gap-2">
                                <Calculator size={20} />
                                {t('sections.home.whats_new.calculators.title')}
                            </h4>
                            <p className="text-sm text-green-800 dark:text-green-200 mb-3">
                                {t('sections.home.whats_new.calculators.description')}
                            </p>
                            <div className="flex flex-wrap gap-2">
                                {['IOL Power', 'IOP Trend', 'Visual Acuity', 'OSDI', 'Macular Thickness', 'Cataract Surgery'].map((calc) => (
                                    <span key={calc} className="px-3 py-1 bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200 rounded-full text-xs font-medium">
                                        {calc}
                                    </span>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>

                <div className="mt-6 text-center">
                    <Link
                        to="/ui-components/notice-bar"
                        className="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-600 text-white rounded-lg font-semibold hover:from-purple-600 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl"
                    >
                        <Menu size={20} />
                        {t('sections.home.whats_new.learn_more')}
                    </Link>
                </div>
            </Section>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
                <Card title={t('sections.home.cards.doctor_portal')} icon={Activity}>
                    {t('sections.home.cards.doctor_portal_desc')}
                </Card>
                <Card title={t('sections.home.cards.admin_control')} icon={Shield}>
                    {t('sections.home.cards.admin_control_desc')}
                </Card>
                <Card title={t('sections.home.cards.secretary_desk')} icon={Users}>
                    {t('sections.home.cards.secretary_desk_desc')}
                </Card>
                <Card title={t('sections.home.cards.secure_data')} icon={Database}>
                    {t('sections.home.cards.secure_data_desc')}
                </Card>
            </div>

            <Section title={t('sections.home.overview.title')} id="overview" className="mb-16">
                <p className="text-lg leading-relaxed text-gray-600 dark:text-gray-300">
                    {t('sections.home.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.home.features.title')} id="features" className="mb-16">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <ul className="space-y-3 text-gray-600 dark:text-gray-300">
                        <li className="flex items-center gap-2">
                            <div className="w-2 h-2 rounded-full bg-primary-500"></div>
                            <span>{t('sections.home.features.items.scheduling')}</span>
                        </li>
                        <li className="flex items-center gap-2">
                            <div className="w-2 h-2 rounded-full bg-primary-500"></div>
                            <span>{t('sections.home.features.items.emr')}</span>
                        </li>
                        <li className="flex items-center gap-2">
                            <div className="w-2 h-2 rounded-full bg-primary-500"></div>
                            <span>{t('sections.home.features.items.inventory')}</span>
                        </li>
                    </ul>
                    <ul className="space-y-3 text-gray-600 dark:text-gray-300">
                        <li className="flex items-center gap-2">
                            <div className="w-2 h-2 rounded-full bg-primary-500"></div>
                            <span>{t('sections.home.features.items.finance')}</span>
                        </li>
                        <li className="flex items-center gap-2">
                            <div className="w-2 h-2 rounded-full bg-primary-500"></div>
                            <span>{t('sections.home.features.items.multilang')}</span>
                        </li>
                        <li className="flex items-center gap-2">
                            <div className="w-2 h-2 rounded-full bg-primary-500"></div>
                            <span>{t('sections.home.features.items.push_notifications')}</span>
                        </li>
                    </ul>
                </div>
            </Section>
        </div>
    );
}
