import Hero from '../components/ui/Hero';
import Section from '../components/ui/Section';
import Card from '../components/ui/Card';
import { Server, Layers, Code } from 'lucide-react';
import { useTranslation } from 'react-i18next';

export default function Architecture() {
    const { t } = useTranslation();

    return (
        <div className="animate-fade-in">
            <Hero
                title={t('sections.architecture.hero.title')}
                subtitle={t('sections.architecture.hero.subtitle')}
                badge={t('sections.architecture.hero.badge')}
            />

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <Card title={t('sections.architecture.cards.mvc.title')} icon={Layers}>
                    {t('sections.architecture.cards.mvc.desc')}
                </Card>
                <Card title={t('sections.architecture.cards.php.title')} icon={Code}>
                    {t('sections.architecture.cards.php.desc')}
                </Card>
                <Card title={t('sections.architecture.cards.mysql.title')} icon={Server}>
                    {t('sections.architecture.cards.mysql.desc')}
                </Card>
            </div>

            <Section title={t('sections.architecture.structure.title')} id="structure" className="mb-16">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div className="bg-gray-900 rounded-xl p-6 font-mono text-sm text-gray-300 overflow-x-auto shadow-inner">
                        <div className="text-primary-400 font-bold mb-2">/app</div>
                        <div className="pl-4">
                            <div className="text-yellow-400">/Controllers</div>
                            <div className="pl-4 border-l border-gray-700 ml-1">
                                <div>AdminController.php</div>
                                <div>AlertController.php</div>
                                <div>ApiController.php</div>
                                <div>AuthController.php</div>
                                <div>DoctorController.php</div>
                                <div>ForumController.php</div>
                                <div>GeneralController.php</div>
                                <div>GlassesController.php</div>
                                <div>MediaController.php</div>
                                <div>MedicationsController.php</div>
                                <div>NotesController.php</div>
                                <div>NotificationController.php</div>
                                <div>PrintController.php</div>
                                <div>SecretaryController.php</div>
                            </div>

                            <div className="text-yellow-400 mt-2">/Views</div>
                            <div className="pl-4 border-l border-gray-700 ml-1">
                                <div>/admin</div>
                                <div>/auth</div>
                                <div>/doctor</div>
                                <div className="pl-4 border-l border-gray-700 ml-1">
                                    <div>/assets</div>
                                    <div className="pl-4 border-l border-gray-700 ml-1">
                                        <div>/css (20+ stylesheets)</div>
                                        <div>/js (18+ scripts)</div>
                                        <div>/svg</div>
                                    </div>
                                    <div>/alerts</div>
                                    <div>/forum</div>
                                    <div>/notes</div>
                                </div>
                                <div>/errors</div>
                                <div>/general</div>
                                <div>/glasses</div>
                                <div>/layouts</div>
                                <div>/media</div>
                                <div>/medications</div>
                                <div>/print</div>
                                <div>/secretary</div>
                                <div className="pl-4 border-l border-gray-700 ml-1">
                                    <div>/assets (css, js)</div>
                                </div>
                                <div>/whats-new</div>
                            </div>

                            <div className="text-yellow-400 mt-2">/Models</div>
                            <div className="pl-4 border-l border-gray-700 ml-1">
                                <div>AlertModel.php</div>
                                <div>...</div>
                            </div>

                            <div className="text-yellow-400 mt-2">/Services</div>
                            <div className="pl-4 border-l border-gray-700 ml-1">
                                <div className="text-gray-400">Core Services:</div>
                                <div>ClinicalDataParserService.php</div>
                                <div>PushNotificationService.php</div>
                                <div className="text-gray-400 mt-1">Ophthalmology Calculators:</div>
                                <div>IOLCalculatorService.php</div>
                                <div>IOPTrendAnalyzerService.php</div>
                                <div>VisualAcuityProgressService.php</div>
                                <div>OSDICalculatorService.php</div>
                                <div>TargetIOPCalculatorService.php</div>
                                <div>RefractionConsistencyService.php</div>
                                <div className="text-gray-400 mt-1">Specialized Services:</div>
                                <div>PediatricIOLUndercorrectionService.php</div>
                                <div>CornealAstigmatismService.php</div>
                                <div>PachymetryAdjustedIOPCalculatorService.php</div>
                                <div>DiabeticRetinopathyRiskEstimatorService.php</div>
                                <div>MacularThicknessTrendAnalyzerService.php</div>
                                <div>CataractSurgeryReadinessService.php</div>
                                <div>PostOperativeOutcomeAnalyzerService.php</div>
                                <div className="text-gray-400 mt-1">Calculator Implementations:</div>
                                <div>SRKTCalculator.php</div>
                                <div>HofferQCalculator.php</div>
                                <div>Holladay1Calculator.php</div>
                                <div className="text-gray-400 mt-1">Interfaces:</div>
                                <div>CalculatorInterface.php</div>
                                <div>AnalyzerInterface.php</div>
                                <div>IOLCalculatorInterface.php</div>
                                <div>IOPTrendAnalyzerInterface.php</div>
                                <div>SurgicalToolInterface.php</div>
                            </div>

                            <div className="text-yellow-400 mt-2">/Lib</div>
                            <div className="pl-4 border-l border-gray-700 ml-1">
                                <div>Auth.php</div>
                                <div>Helpers.php</div>
                                <div>Router.php</div>
                                <div>UrlHelper.php</div>
                                <div>Validator.php</div>
                                <div>View.php</div>
                            </div>

                            <div className="text-yellow-400 mt-2">/Config</div>
                            <div className="pl-4 border-l border-gray-700 ml-1">
                                <div>Database.php</div>
                                <div>Auth.php</div>
                                <div>Constants.php</div>
                            </div>

                            <div className="text-yellow-400 mt-2">/Scripts</div>
                            <div className="pl-4 border-l border-gray-700 ml-1">
                                <div>send_push_notifications.php</div>
                                <div>test_push_notifications.php</div>
                            </div>

                            <div className="text-yellow-400 mt-2">/storage</div>
                            <div className="pl-4 border-l border-gray-700 ml-1">
                                <div>/cache</div>
                            </div>

                            <div className="text-yellow-400 mt-2">/TEST</div>
                        </div>

                        <div className="text-primary-400 font-bold mt-4">/public</div>
                        <div className="pl-4 border-l border-gray-700 ml-2">
                            <div>index.php</div>
                            <div>/assets</div>
                        </div>

                        <div className="text-primary-400 font-bold mt-4">/vendor</div>
                        <div className="pl-4 border-l border-gray-700 ml-2">
                            <div>Composer dependencies</div>
                        </div>
                    </div>

                    <div className="space-y-4">
                        <h3 className="text-xl font-bold text-gray-900 dark:text-gray-100">{t('sections.architecture.structure.key_dirs')}</h3>
                        <div className="space-y-4">
                            <div className="p-4 rounded-lg bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700">
                                <div className="font-bold text-primary-600 dark:text-primary-400 mb-1">{t('sections.architecture.structure.controllers.title')}</div>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    {t('sections.architecture.structure.controllers.desc')}
                                </p>
                            </div>
                            <div className="p-4 rounded-lg bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700">
                                <div className="font-bold text-primary-600 dark:text-primary-400 mb-1">{t('sections.architecture.structure.services.title')}</div>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    {t('sections.architecture.structure.services.desc')}
                                </p>
                            </div>
                            <div className="p-4 rounded-lg bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700">
                                <div className="font-bold text-primary-600 dark:text-primary-400 mb-1">{t('sections.architecture.structure.views.title')}</div>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    {t('sections.architecture.structure.views.desc')}
                                </p>
                            </div>
                            <div className="p-4 rounded-lg bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700">
                                <div className="font-bold text-primary-600 dark:text-primary-400 mb-1">{t('sections.architecture.structure.models.title')}</div>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    {t('sections.architecture.structure.models.desc')}
                                </p>
                            </div>
                            <div className="p-4 rounded-lg bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700">
                                <div className="font-bold text-primary-600 dark:text-primary-400 mb-1">{t('sections.architecture.structure.public.title')}</div>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    {t('sections.architecture.structure.public.desc')}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.architecture.lifecycle.title')} id="lifecycle" className="mb-16">
                <Card className="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-dark-800 dark:to-dark-900">
                    <ol className="relative border-l-2 border-primary-500 ml-3 space-y-6 py-2">
                        <li className="mb-2 ml-6">
                            <span className="absolute flex items-center justify-center w-8 h-8 bg-primary-200 dark:bg-primary-900 rounded-full -left-4 ring-4 ring-white dark:ring-dark-900">
                                1
                            </span>
                            <h3 className="font-bold text-gray-900 dark:text-gray-100">{t('sections.architecture.lifecycle.steps.entry.title')}</h3>
                            <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.architecture.lifecycle.steps.entry.desc')}</p>
                        </li>
                        <li className="mb-2 ml-6">
                            <span className="absolute flex items-center justify-center w-8 h-8 bg-primary-200 dark:bg-primary-900 rounded-full -left-4 ring-4 ring-white dark:ring-dark-900">
                                2
                            </span>
                            <h3 className="font-bold text-gray-900 dark:text-gray-100">{t('sections.architecture.lifecycle.steps.routing.title')}</h3>
                            <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.architecture.lifecycle.steps.routing.desc')}</p>
                        </li>
                        <li className="mb-2 ml-6">
                            <span className="absolute flex items-center justify-center w-8 h-8 bg-primary-200 dark:bg-primary-900 rounded-full -left-4 ring-4 ring-white dark:ring-dark-900">
                                3
                            </span>
                            <h3 className="font-bold text-gray-900 dark:text-gray-100">{t('sections.architecture.lifecycle.steps.controller.title')}</h3>
                            <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.architecture.lifecycle.steps.controller.desc')}</p>
                        </li>
                        <li className="ml-6">
                            <span className="absolute flex items-center justify-center w-8 h-8 bg-primary-200 dark:bg-primary-900 rounded-full -left-4 ring-4 ring-white dark:ring-dark-900">
                                4
                            </span>
                            <h3 className="font-bold text-gray-900 dark:text-gray-100">{t('sections.architecture.lifecycle.steps.response.title')}</h3>
                            <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.architecture.lifecycle.steps.response.desc')}</p>
                        </li>
                    </ol>
                </Card>
            </Section>
        </div>
    );
}
