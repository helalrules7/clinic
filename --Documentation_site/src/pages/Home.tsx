import Hero from '../components/ui/Hero';
import Section from '../components/ui/Section';
import Card from '../components/ui/Card';
import V10Highlights from '../components/V10Highlights';
import { Activity, Shield, Users, Database } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import '../styles/mockups.css';

export default function Home() {
    const { t } = useTranslation();

    return (
        <div className="animate-fade-in">
            <Hero
                title={t('sections.home.hero.title')}
                subtitle={t('sections.home.hero.subtitle')}
                badge={t('sections.home.hero.badge')}
            />

            {/* v10 highlights — 6 mockup cards introducing the major v10 features.
                Replaces the legacy v8 "What's New" Home content; that's now in /changelog. */}
            <V10Highlights />

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
                            <div className="w-2 h-2 rounded-full bg-indigo-500"></div>
                            <span>{t('sections.home.features.items.scheduling')}</span>
                        </li>
                        <li className="flex items-center gap-2">
                            <div className="w-2 h-2 rounded-full bg-indigo-500"></div>
                            <span>{t('sections.home.features.items.emr')}</span>
                        </li>
                        <li className="flex items-center gap-2">
                            <div className="w-2 h-2 rounded-full bg-indigo-500"></div>
                            <span>{t('sections.home.features.items.inventory')}</span>
                        </li>
                    </ul>
                    <ul className="space-y-3 text-gray-600 dark:text-gray-300">
                        <li className="flex items-center gap-2">
                            <div className="w-2 h-2 rounded-full bg-indigo-500"></div>
                            <span>{t('sections.home.features.items.finance')}</span>
                        </li>
                        <li className="flex items-center gap-2">
                            <div className="w-2 h-2 rounded-full bg-indigo-500"></div>
                            <span>{t('sections.home.features.items.multilang')}</span>
                        </li>
                        <li className="flex items-center gap-2">
                            <div className="w-2 h-2 rounded-full bg-indigo-500"></div>
                            <span>{t('sections.home.features.items.push_notifications')}</span>
                        </li>
                    </ul>
                </div>
            </Section>
        </div>
    );
}
