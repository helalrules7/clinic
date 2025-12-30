import Hero from '../components/ui/Hero';
import Section from '../components/ui/Section';
import Card from '../components/ui/Card';
import { Download, Server, ShieldCheck, Database } from 'lucide-react';
import { useTranslation } from 'react-i18next';

export default function Setup() {
    const { t } = useTranslation();

    const Step = ({ num, title, children }: { num: number, title: string, children: React.ReactNode }) => (
        <div className="flex gap-4">
            <div className="flex-none w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-lg">
                {num}
            </div>
            <div>
                <h3 className="text-xl font-bold mb-2 text-gray-900 dark:text-gray-100">{title}</h3>
                <div className="text-gray-600 dark:text-gray-300 space-y-2">
                    {children}
                </div>
            </div>
        </div>
    );

    return (
        <div className="animate-fade-in">
            <Hero
                title={t('sections.setup.hero.title')}
                subtitle={t('sections.setup.hero.subtitle')}
                badge={t('sections.setup.hero.badge')}
            />

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <Card title={t('sections.setup.cards.php.title')} icon={Server}>
                    {t('sections.setup.cards.php.desc')}
                </Card>
                <Card title={t('sections.setup.cards.mysql.title')} icon={Database}>
                    {t('sections.setup.cards.mysql.desc')}
                </Card>
                <Card title={t('sections.setup.cards.composer.title')} icon={Download}>
                    {t('sections.setup.cards.composer.desc')}
                </Card>
                <Card title={t('sections.setup.cards.ssl.title')} icon={ShieldCheck}>
                    {t('sections.setup.cards.ssl.desc')}
                </Card>
            </div>

            <Section title={t('sections.setup.steps.title')} id="steps" className="mb-16">
                <div className="space-y-12">
                    <Step num={1} title={t('sections.setup.steps.items.clone.title')}>
                        <p>{t('sections.setup.steps.items.clone.desc')}</p>
                        <div className="bg-gray-900 p-4 rounded-lg mt-2 overflow-x-auto">
                            <code className="text-sm font-mono text-gray-300">
                                git clone https://github.com/helalrules7/clinic.git<br />
                                cd clinic
                            </code>
                        </div>
                    </Step>

                    <Step num={2} title={t('sections.setup.steps.items.deps.title')}>
                        <p>{t('sections.setup.steps.items.deps.desc')}</p>
                        <div className="bg-gray-900 p-4 rounded-lg mt-2 overflow-x-auto">
                            <code className="text-sm font-mono text-gray-300">
                                composer install --optimize-autoloader --no-dev
                            </code>
                        </div>
                    </Step>

                    <Step num={3} title={t('sections.setup.steps.items.env.title')}>
                        <p>{t('sections.setup.steps.items.env.desc')}</p>
                        <div className="bg-gray-900 p-4 rounded-lg mt-2 overflow-x-auto">
                            <code className="text-sm font-mono text-gray-300">
                                cp env.example .env<br />
                                nano .env
                            </code>
                        </div>
                        <div className="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg mt-2">
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                <strong>{t('sections.setup.steps.items.env.config_title')}</strong>
                            </p>
                            <ul className="text-sm text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
                                <li>DB_HOST={t('sections.setup.steps.items.env.db_host')}</li>
                                <li>DB_NAME={t('sections.setup.steps.items.env.db_name')}</li>
                                <li>DB_USER={t('sections.setup.steps.items.env.db_user')}</li>
                                <li>DB_PASS={t('sections.setup.steps.items.env.db_pass')}</li>
                                <li>APP_ENV={t('sections.setup.steps.items.env.app_env')}</li>
                                <li>APP_DEBUG={t('sections.setup.steps.items.env.app_debug')}</li>
                            </ul>
                        </div>
                    </Step>

                    <Step num={4} title={t('sections.setup.steps.items.migration.title')}>
                        <p>{t('sections.setup.steps.items.migration.desc')}</p>
                    </Step>
                </div>
            </Section>
        </div>
    );
}
