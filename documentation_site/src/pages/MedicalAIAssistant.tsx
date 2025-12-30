import Hero from '../components/ui/Hero';
import Section from '../components/ui/Section';
import Card from '../components/ui/Card';
import { Bot, HeartPulse, ClipboardList, Lightbulb, Search, MessageSquare, Code, Zap } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useEffect, useState } from 'react';

export default function MedicalAIAssistant() {
    const { t } = useTranslation();
    const [chatOpen, setChatOpen] = useState(false);

    // Simulate chat widget animation
    useEffect(() => {
        const timer = setTimeout(() => setChatOpen(true), 1000);
        return () => clearTimeout(timer);
    }, []);

    return (
        <div className="animate-fade-in">
            <Hero
                title={t('sections.ai_assistant.hero.title')}
                subtitle={t('sections.ai_assistant.hero.subtitle')}
                badge={t('sections.ai_assistant.hero.badge')}
            />

            {/* Overview Section */}
            <Section title={t('sections.ai_assistant.overview.title')} id="overview" className="mb-16">
                <p className="text-lg leading-relaxed text-gray-600 dark:text-gray-300 mb-6">
                    {t('sections.ai_assistant.overview.description')}
                </p>
                
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <Card
                        title={t('sections.ai_assistant.overview.features.patient_history.title')}
                        icon={MessageSquare}
                        className="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 border-2 border-indigo-200 dark:border-indigo-800"
                    >
                        {t('sections.ai_assistant.overview.features.patient_history.description')}
                    </Card>
                    <Card
                        title={t('sections.ai_assistant.overview.features.consultation_summary.title')}
                        icon={ClipboardList}
                        className="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-2 border-purple-200 dark:border-purple-800"
                    >
                        {t('sections.ai_assistant.overview.features.consultation_summary.description')}
                    </Card>
                    <Card
                        title={t('sections.ai_assistant.overview.features.context_aware.title')}
                        icon={Zap}
                        className="bg-gradient-to-br from-pink-50 to-red-50 dark:from-pink-900/20 dark:to-red-900/20 border-2 border-pink-200 dark:border-pink-800"
                    >
                        {t('sections.ai_assistant.overview.features.context_aware.description')}
                    </Card>
                </div>
            </Section>

            {/* AI Chat Widget Section */}
            <Section title={t('sections.ai_assistant.chat_widget.title')} id="chat-widget" className="mb-16">
                <div className="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl p-6 mb-6">
                    <p className="text-gray-700 dark:text-gray-300 mb-4">
                        {t('sections.ai_assistant.chat_widget.description')}
                    </p>
                </div>

                {/* Chat Widget Mockup */}
                <div className="mb-8 rounded-xl overflow-hidden border-2 border-gray-300 dark:border-gray-700 shadow-2xl bg-gradient-to-r from-slate-50 to-gray-100 dark:from-slate-900 dark:to-gray-800">
                    <div className="p-6 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <div className="relative bg-white dark:bg-slate-800 rounded-lg p-8 shadow-lg" style={{ minHeight: '500px' }}>
                            {/* Mockup Screen */}
                            <div className="relative h-full">
                                {/* Chat Button */}
                                <div className={`absolute bottom-6 right-6 w-14 h-14 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-lg cursor-pointer transition-all duration-300 ${chatOpen ? 'scale-110' : ''}`}>
                                    <div className="relative">
                                        <HeartPulse size={16} className="absolute -top-2 -left-2 text-red-500 animate-pulse" />
                                        <Bot size={24} />
                                    </div>
                                </div>
                                
                                {/* Chat Window */}
                                <div className={`absolute bottom-24 right-0 w-80 h-96 bg-white dark:bg-slate-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 flex flex-col transition-all duration-300 ${chatOpen ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'}`}>
                                    <div className="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gradient-to-r from-indigo-500 to-purple-600">
                                        <div className="flex items-center gap-2">
                                            <div className="relative">
                                                <HeartPulse size={12} className="absolute -top-1 -left-1 text-red-500" />
                                                <Bot size={16} className="text-white" />
                                            </div>
                                            <span className="text-sm font-semibold text-white">Medical AI Assistant</span>
                                        </div>
                                        <button className="text-white hover:text-gray-200">
                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <div className="px-3 py-2 bg-yellow-50 dark:bg-yellow-900/20 border-b border-yellow-200 dark:border-yellow-800">
                                        <p className="text-xs text-yellow-800 dark:text-yellow-200 flex items-start gap-2">
                                            <span>⚠️</span>
                                            <span>{t('sections.ai_assistant.chat_widget.disclaimer')}</span>
                                        </p>
                                    </div>
                                    
                                    <div className="flex-1 p-3 overflow-y-auto space-y-3">
                                        <div className="flex justify-end">
                                            <div className="bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg max-w-[80%]">
                                                Send Patient History
                                            </div>
                                        </div>
                                        <div className="flex justify-start">
                                            <div className="bg-gray-100 dark:bg-slate-700 text-gray-900 dark:text-white text-sm px-4 py-2 rounded-lg max-w-[80%]">
                                                Analyzing patient history...
                                            </div>
                                        </div>
                                        <div className="flex justify-start">
                                            <div className="bg-gray-100 dark:bg-slate-700 text-gray-900 dark:text-white text-sm px-4 py-2 rounded-lg max-w-[80%]">
                                                Based on the patient's medical history, I've identified:<br/><br/>
                                                • Chronic hypertension (5 years)<br/>
                                                • Type 2 diabetes (3 years)<br/>
                                                • Regular eye examinations show stable vision<br/><br/>
                                                Recommendations: Continue current medication regimen and schedule follow-up in 3 months.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div className="px-3 py-2 border-t border-gray-200 dark:border-gray-700 space-y-2">
                                        <div className="flex gap-2">
                                            <button className="flex-1 text-xs px-3 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg text-center hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
                                                <MessageSquare size={14} className="inline mr-1" />
                                                Send Patient History
                                            </button>
                                            <button className="flex-1 text-xs px-3 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg text-center hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
                                                <ClipboardList size={14} className="inline mr-1" />
                                                Summarize Consultation
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Technical Details */}
                <div className="space-y-6">
                    <Card
                        title={t('sections.ai_assistant.chat_widget.quick_actions.title')}
                        icon={Zap}
                        className="bg-white dark:bg-slate-800 border-2 border-indigo-200 dark:border-indigo-800"
                    >
                        <div className="space-y-4">
                            <div>
                                <h4 className="font-semibold text-gray-900 dark:text-white mb-2">
                                    {t('sections.ai_assistant.chat_widget.quick_actions.patient_history.title')}
                                </h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {t('sections.ai_assistant.chat_widget.quick_actions.patient_history.description')}
                                </p>
                                <ul className="text-sm text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
                                    <li>{t('sections.ai_assistant.chat_widget.quick_actions.patient_history.items.medical_conditions')}</li>
                                    <li>{t('sections.ai_assistant.chat_widget.quick_actions.patient_history.items.medication_history')}</li>
                                    <li>{t('sections.ai_assistant.chat_widget.quick_actions.patient_history.items.consultation_notes')}</li>
                                    <li>{t('sections.ai_assistant.chat_widget.quick_actions.patient_history.items.lab_tests')}</li>
                                    <li>{t('sections.ai_assistant.chat_widget.quick_actions.patient_history.items.glasses_prescriptions')}</li>
                                    <li>{t('sections.ai_assistant.chat_widget.quick_actions.patient_history.items.patterns')}</li>
                                    <li>{t('sections.ai_assistant.chat_widget.quick_actions.patient_history.items.recommendations')}</li>
                                </ul>
                            </div>
                            <div>
                                <h4 className="font-semibold text-gray-900 dark:text-white mb-2">
                                    {t('sections.ai_assistant.chat_widget.quick_actions.consultation_summary.title')}
                                </h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {t('sections.ai_assistant.chat_widget.quick_actions.consultation_summary.description')}
                                </p>
                                <ul className="text-sm text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
                                    <li>{t('sections.ai_assistant.chat_widget.quick_actions.consultation_summary.items.chief_complaint')}</li>
                                    <li>{t('sections.ai_assistant.chat_widget.quick_actions.consultation_summary.items.clinical_findings')}</li>
                                    <li>{t('sections.ai_assistant.chat_widget.quick_actions.consultation_summary.items.diagnosis')}</li>
                                    <li>{t('sections.ai_assistant.chat_widget.quick_actions.consultation_summary.items.treatment_plan')}</li>
                                    <li>{t('sections.ai_assistant.chat_widget.quick_actions.consultation_summary.items.lab_tests')}</li>
                                    <li>{t('sections.ai_assistant.chat_widget.quick_actions.consultation_summary.items.follow_up')}</li>
                                    <li>{t('sections.ai_assistant.chat_widget.quick_actions.consultation_summary.items.history_context')}</li>
                                </ul>
                            </div>
                        </div>
                    </Card>

                    <Card
                        title={t('sections.ai_assistant.chat_widget.technical.title')}
                        icon={Code}
                        className="bg-white dark:bg-slate-800 border-2 border-indigo-200 dark:border-indigo-800"
                    >
                        <div className="space-y-4">
                            <div>
                                <h4 className="font-semibold text-gray-900 dark:text-white mb-2">API Endpoint</h4>
                                <code className="block bg-gray-100 dark:bg-slate-900 p-3 rounded text-sm mb-2">
                                    POST /api/ai/chat
                                </code>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    {t('sections.ai_assistant.chat_widget.technical.api_description')}
                                </p>
                            </div>
                            <div>
                                <h4 className="font-semibold text-gray-900 dark:text-white mb-2">Request Parameters</h4>
                                <div className="bg-gray-100 dark:bg-slate-900 p-3 rounded text-sm font-mono">
                                    <div className="space-y-1">
                                        <div><span className="text-blue-600 dark:text-blue-400">patientId</span>: <span className="text-gray-600 dark:text-gray-400">number (required)</span></div>
                                        <div><span className="text-blue-600 dark:text-blue-400">appointmentId</span>: <span className="text-gray-600 dark:text-gray-400">number (optional)</span></div>
                                        <div><span className="text-blue-600 dark:text-blue-400">message</span>: <span className="text-gray-600 dark:text-gray-400">string (required)</span></div>
                                        <div><span className="text-blue-600 dark:text-blue-400">contextType</span>: <span className="text-gray-600 dark:text-gray-400">'patient_history' | 'consultation_summary' | 'general'</span></div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h4 className="font-semibold text-gray-900 dark:text-white mb-2">Context Building</h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {t('sections.ai_assistant.chat_widget.technical.context_building')}
                                </p>
                                <ul className="text-sm text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
                                    <li>{t('sections.ai_assistant.chat_widget.technical.context_items.patient_data')}</li>
                                    <li>{t('sections.ai_assistant.chat_widget.technical.context_items.appointments')}</li>
                                    <li>{t('sections.ai_assistant.chat_widget.technical.context_items.prescriptions')}</li>
                                    <li>{t('sections.ai_assistant.chat_widget.technical.context_items.lab_tests')}</li>
                                    <li>{t('sections.ai_assistant.chat_widget.technical.context_items.chat_history')}</li>
                                </ul>
                            </div>
                        </div>
                    </Card>
                </div>
            </Section>

            {/* Autocomplete Section */}
            <Section title={t('sections.ai_assistant.autocomplete.title')} id="autocomplete" className="mb-16">
                <div className="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-6 mb-6">
                    <p className="text-gray-700 dark:text-gray-300 mb-4">
                        {t('sections.ai_assistant.autocomplete.description')}
                    </p>
                </div>

                {/* Autocomplete Mockup */}
                <div className="mb-8 rounded-xl overflow-hidden border-2 border-gray-300 dark:border-gray-700 shadow-2xl bg-gradient-to-r from-slate-50 to-gray-100 dark:from-slate-900 dark:to-gray-800">
                    <div className="p-6 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <div className="bg-white dark:bg-slate-800 rounded-lg p-6 shadow-lg">
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Chief Complaint</label>
                                    <div className="relative">
                                        <input 
                                            type="text" 
                                            className="w-full px-4 py-2 border-2 border-purple-300 dark:border-purple-700 rounded-lg bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-200 dark:focus:ring-purple-800"
                                            placeholder="Type complaint..." 
                                            defaultValue="Headache"
                                        />
                                        <div className="absolute z-10 w-full mt-1 bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
                                            <div className="p-2 hover:bg-purple-50 dark:hover:bg-purple-900/30 cursor-pointer border-b border-gray-100 dark:border-gray-700">
                                                <div className="flex justify-between items-center">
                                                    <span className="text-sm text-gray-900 dark:text-white">Headache - Migraine</span>
                                                    <span className="text-xs text-gray-500 dark:text-gray-400">Used 45 times</span>
                                                </div>
                                            </div>
                                            <div className="p-2 hover:bg-purple-50 dark:hover:bg-purple-900/30 cursor-pointer">
                                                <div className="flex justify-between items-center">
                                                    <span className="text-sm text-gray-900 dark:text-white">Headache - Tension</span>
                                                    <span className="text-xs text-gray-500 dark:text-gray-400">Used 32 times</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <Card
                    title={t('sections.ai_assistant.autocomplete.features.title')}
                    icon={Search}
                    className="bg-white dark:bg-slate-800 border-2 border-purple-200 dark:border-purple-800"
                >
                    <div className="space-y-4">
                        <div>
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">
                                {t('sections.ai_assistant.autocomplete.features.debounce.title')}
                            </h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.ai_assistant.autocomplete.features.debounce.description')}
                            </p>
                        </div>
                        <div>
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">
                                {t('sections.ai_assistant.autocomplete.features.keyboard_nav.title')}
                            </h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.ai_assistant.autocomplete.features.keyboard_nav.description')}
                            </p>
                        </div>
                        <div>
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">
                                {t('sections.ai_assistant.autocomplete.features.fields.title')}
                            </h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.ai_assistant.autocomplete.features.fields.description')}
                            </p>
                            <ul className="text-sm text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
                                <li>Chief Complaint</li>
                                <li>Diagnosis</li>
                                <li>Plan</li>
                            </ul>
                        </div>
                    </div>
                </Card>

                <Card
                    title={t('sections.ai_assistant.autocomplete.technical.title')}
                    icon={Code}
                    className="bg-white dark:bg-slate-800 border-2 border-purple-200 dark:border-purple-800 mt-6"
                >
                    <div className="space-y-4">
                        <div>
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">API Endpoint</h4>
                            <code className="block bg-gray-100 dark:bg-slate-900 p-3 rounded text-sm mb-2">
                                GET /api/consultation/suggestions?field={'{field}'}&query={'{query}'}
                            </code>
                        </div>
                        <div>
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">Implementation</h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.ai_assistant.autocomplete.technical.implementation')}
                            </p>
                        </div>
                    </div>
                </Card>
            </Section>

            {/* Common Complaints Section */}
            <Section title={t('sections.ai_assistant.common_complaints.title')} id="common-complaints" className="mb-16">
                <div className="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-800 rounded-xl p-6 mb-6">
                    <p className="text-gray-700 dark:text-gray-300 mb-4">
                        {t('sections.ai_assistant.common_complaints.description')}
                    </p>
                </div>

                {/* Common Complaints Mockup */}
                <div className="mb-8 rounded-xl overflow-hidden border-2 border-gray-300 dark:border-gray-700 shadow-2xl bg-gradient-to-r from-slate-50 to-gray-100 dark:from-slate-900 dark:to-gray-800">
                    <div className="p-6 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <div className="bg-white dark:bg-slate-800 rounded-lg p-6 shadow-lg">
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Chief Complaint</label>
                                    <div className="flex gap-2">
                                        <input 
                                            type="text" 
                                            className="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-700 text-gray-900 dark:text-white"
                                            placeholder="Type complaint..." 
                                            defaultValue="Headache"
                                        />
                                        <button className="px-6 py-2 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 transition-colors">
                                            Most Common Cases
                                        </button>
                                    </div>
                                </div>
                                
                                {/* Modal Overlay */}
                                <div className="relative bg-black/50 rounded-lg p-4">
                                    <div className="bg-white dark:bg-slate-800 rounded-lg shadow-xl">
                                        <div className="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                            <h5 className="font-semibold text-gray-900 dark:text-white">Most Common Cases</h5>
                                            <button className="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">×</button>
                                        </div>
                                        <div className="p-4 space-y-2 max-h-64 overflow-y-auto">
                                            <div className="p-3 bg-gray-50 dark:bg-slate-700 rounded-lg cursor-pointer hover:bg-green-50 dark:hover:bg-green-900/30 transition-colors">
                                                <div className="flex justify-between items-start">
                                                    <div>
                                                        <div className="flex items-center gap-2 mb-1">
                                                            <span className="badge bg-green-500">1</span>
                                                            <span className="font-semibold text-gray-900 dark:text-white">Headache - Migraine</span>
                                                        </div>
                                                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-1"><strong>Diagnosis:</strong> Migraine</p>
                                                        <p className="text-sm text-gray-600 dark:text-gray-400"><strong>Plan:</strong> Prescribe pain relief medication</p>
                                                    </div>
                                                    <span className="text-xs text-gray-500 dark:text-gray-400">Used 45 times</span>
                                                </div>
                                            </div>
                                            <div className="p-3 bg-gray-50 dark:bg-slate-700 rounded-lg cursor-pointer hover:bg-green-50 dark:hover:bg-green-900/30 transition-colors">
                                                <div className="flex justify-between items-start">
                                                    <div>
                                                        <div className="flex items-center gap-2 mb-1">
                                                            <span className="badge bg-green-500">2</span>
                                                            <span className="font-semibold text-gray-900 dark:text-white">Eye pain - Conjunctivitis</span>
                                                        </div>
                                                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-1"><strong>Diagnosis:</strong> Conjunctivitis</p>
                                                        <p className="text-sm text-gray-600 dark:text-gray-400"><strong>Plan:</strong> Antibiotic eye drops</p>
                                                    </div>
                                                    <span className="text-xs text-gray-500 dark:text-gray-400">Used 38 times</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <Card
                    title={t('sections.ai_assistant.common_complaints.features.title')}
                    icon={ClipboardList}
                    className="bg-white dark:bg-slate-800 border-2 border-green-200 dark:border-green-800"
                >
                    <div className="space-y-4">
                        <div>
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">
                                {t('sections.ai_assistant.common_complaints.features.extraction.title')}
                            </h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.ai_assistant.common_complaints.features.extraction.description')}
                            </p>
                        </div>
                        <div>
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">
                                {t('sections.ai_assistant.common_complaints.features.auto_update.title')}
                            </h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.ai_assistant.common_complaints.features.auto_update.description')}
                            </p>
                        </div>
                        <div>
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">
                                {t('sections.ai_assistant.common_complaints.features.quick_fill.title')}
                            </h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.ai_assistant.common_complaints.features.quick_fill.description')}
                            </p>
                        </div>
                    </div>
                </Card>

                <Card
                    title={t('sections.ai_assistant.common_complaints.technical.title')}
                    icon={Code}
                    className="bg-white dark:bg-slate-800 border-2 border-green-200 dark:border-green-800 mt-6"
                >
                    <div className="space-y-4">
                        <div>
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">API Endpoint</h4>
                            <code className="block bg-gray-100 dark:bg-slate-900 p-3 rounded text-sm mb-2">
                                GET /api/consultation/common-complaints
                            </code>
                        </div>
                        <div>
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">Data Storage</h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.ai_assistant.common_complaints.technical.storage')}
                            </p>
                        </div>
                        <div>
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">Cron Job</h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.ai_assistant.common_complaints.technical.cron')}
                            </p>
                            <code className="block bg-gray-100 dark:bg-slate-900 p-3 rounded text-sm mt-2">
                                0 22 * * * php /path/to/update_common_complaints.php
                            </code>
                        </div>
                    </div>
                </Card>
            </Section>

            {/* Prescription Suggestions Section */}
            <Section title={t('sections.ai_assistant.prescription_suggestions.title')} id="prescription-suggestions" className="mb-16">
                <div className="bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 border border-orange-200 dark:border-orange-800 rounded-xl p-6 mb-6">
                    <p className="text-gray-700 dark:text-gray-300 mb-4">
                        {t('sections.ai_assistant.prescription_suggestions.description')}
                    </p>
                </div>

                {/* Prescription Suggestions Mockup */}
                <div className="mb-8 rounded-xl overflow-hidden border-2 border-gray-300 dark:border-gray-700 shadow-2xl bg-gradient-to-r from-slate-50 to-gray-100 dark:from-slate-900 dark:to-gray-800">
                    <div className="p-6 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <div className="bg-white dark:bg-slate-800 rounded-lg shadow-xl">
                            <div className="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-orange-500 to-red-600">
                                <h5 className="font-semibold text-white flex items-center gap-2">
                                    <Lightbulb size={20} />
                                    Prescription Suggestions
                                </h5>
                                <button className="text-white hover:text-gray-200">×</button>
                            </div>
                            <div className="p-4">
                                <div className="mb-4">
                                    <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                        <strong>Diagnosis:</strong> <span className="text-gray-900 dark:text-white">Migraine</span>
                                    </p>
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        <strong>Complaint:</strong> <span className="text-gray-900 dark:text-white">Headache</span>
                                    </p>
                                </div>
                                <div className="space-y-2 max-h-64 overflow-y-auto">
                                    <div className="p-3 bg-gray-50 dark:bg-slate-700 rounded-lg border-2 border-orange-300 dark:border-orange-700">
                                        <div className="flex items-start gap-3">
                                            <input type="checkbox" className="mt-1" checked />
                                            <div className="flex-1">
                                                <h6 className="font-semibold text-gray-900 dark:text-white mb-1 flex items-center gap-2">
                                                    💊 Paracetamol 500mg
                                                </h6>
                                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">Take 2 tablets every 6 hours</p>
                                                <small className="text-gray-500 dark:text-gray-400">Route: Oral</small>
                                            </div>
                                            <span className="badge bg-orange-500">Used 28 times</span>
                                        </div>
                                    </div>
                                    <div className="p-3 bg-gray-50 dark:bg-slate-700 rounded-lg">
                                        <div className="flex items-start gap-3">
                                            <input type="checkbox" className="mt-1" />
                                            <div className="flex-1">
                                                <h6 className="font-semibold text-gray-900 dark:text-white mb-1 flex items-center gap-2">
                                                    💊 Ibuprofen 400mg
                                                </h6>
                                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">Take 1 tablet every 8 hours</p>
                                                <small className="text-gray-500 dark:text-gray-400">Route: Oral</small>
                                            </div>
                                            <span className="badge bg-orange-500">Used 15 times</span>
                                        </div>
                                    </div>
                                </div>
                                <div className="mt-4 flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div className="flex gap-2">
                                        <button className="px-3 py-1 text-sm bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-200 dark:hover:bg-slate-600">
                                            Select All
                                        </button>
                                        <button className="px-3 py-1 text-sm bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-200 dark:hover:bg-slate-600">
                                            Deselect All
                                        </button>
                                    </div>
                                    <button className="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                                        Add Selected (1)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <Card
                    title={t('sections.ai_assistant.prescription_suggestions.features.title')}
                    icon={Lightbulb}
                    className="bg-white dark:bg-slate-800 border-2 border-orange-200 dark:border-orange-800"
                >
                    <div className="space-y-4">
                        <div>
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">
                                {t('sections.ai_assistant.prescription_suggestions.features.based_on.title')}
                            </h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.ai_assistant.prescription_suggestions.features.based_on.description')}
                            </p>
                        </div>
                        <div>
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">
                                {t('sections.ai_assistant.prescription_suggestions.features.usage_stats.title')}
                            </h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.ai_assistant.prescription_suggestions.features.usage_stats.description')}
                            </p>
                        </div>
                        <div>
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">
                                {t('sections.ai_assistant.prescription_suggestions.features.bulk_add.title')}
                            </h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.ai_assistant.prescription_suggestions.features.bulk_add.description')}
                            </p>
                        </div>
                    </div>
                </Card>

                <Card
                    title={t('sections.ai_assistant.prescription_suggestions.technical.title')}
                    icon={Code}
                    className="bg-white dark:bg-slate-800 border-2 border-orange-200 dark:border-orange-800 mt-6"
                >
                    <div className="space-y-4">
                        <div>
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">API Endpoint</h4>
                            <code className="block bg-gray-100 dark:bg-slate-900 p-3 rounded text-sm mb-2">
                                GET /api/prescriptions/suggestions?diagnosis={'{diagnosis}'}&complaint={'{complaint}'}
                            </code>
                        </div>
                        <div>
                            <h4 className="font-semibold text-gray-900 dark:text-white mb-2">Algorithm</h4>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {t('sections.ai_assistant.prescription_suggestions.technical.algorithm')}
                            </p>
                        </div>
                    </div>
                </Card>
            </Section>
        </div>
    );
}
