import Hero from '../components/ui/Hero';
import Section from '../components/ui/Section';
import Card from '../components/ui/Card';
import { ClipboardList, Lightbulb, Search, MessageSquare, Code, Zap } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useEffect, useState, useRef } from 'react';
import '../styles/mockups.css';

export default function MedicalAIAssistant() {
    const { t } = useTranslation();
    const [chatWindowOpen, setChatWindowOpen] = useState(false);
    const [messagesVisible, setMessagesVisible] = useState<boolean[]>([]);
    const [autocompleteVisible, setAutocompleteVisible] = useState(false);
    const [complaintsModalVisible, setComplaintsModalVisible] = useState(false);
    const mouseCursorRef = useRef<HTMLDivElement>(null);
    const chatButtonRef = useRef<HTMLDivElement>(null);

    // AI Chat Mockup Animation
    useEffect(() => {
        const timer = setTimeout(() => {
            if (mouseCursorRef.current && chatButtonRef.current) {
                // Start cursor from top-left area
                mouseCursorRef.current.classList.add('show');
                mouseCursorRef.current.style.left = '20px';
                mouseCursorRef.current.style.top = '20px';
                
                // Move cursor towards chat button
                setTimeout(() => {
                    if (mouseCursorRef.current) {
                        mouseCursorRef.current.style.left = 'calc(100% - 45px)';
                        mouseCursorRef.current.style.top = 'calc(100% - 45px)';
                        
                        // Small pause, then click effect
                        setTimeout(() => {
                            if (mouseCursorRef.current) {
                                mouseCursorRef.current.style.top = 'calc(100% - 43px)';
                                
                                setTimeout(() => {
                                    if (mouseCursorRef.current) {
                                        mouseCursorRef.current.style.top = 'calc(100% - 45px)';
                                        
                                        // Show chat window after click
                                        setTimeout(() => {
                                            setChatWindowOpen(true);
                                            if (mouseCursorRef.current) {
                                                mouseCursorRef.current.style.display = 'none';
                                            }
                                            
                                            // Show messages sequentially
                                            setTimeout(() => {
                                                setMessagesVisible([true]);
                                                setTimeout(() => setMessagesVisible([true, true]), 800);
                                                setTimeout(() => setMessagesVisible([true, true, true]), 1600);
                                            }, 500);
                                        }, 300);
                                    }
                                }, 200);
                            }
                        }, 500);
                    }
                }, 1500);
            }
        }, 500);

        return () => clearTimeout(timer);
    }, []);

    // Common Complaints Mockup Animation
    useEffect(() => {
        const timer = setTimeout(() => {
            setAutocompleteVisible(true);
            setTimeout(() => {
                setComplaintsModalVisible(true);
            }, 1000);
        }, 500);

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
                <div className="whats-new-mockup-container">
                    <div className="whats-new-mockup-label">AI Chat Widget Demo</div>
                    <div className="ai-chat-mockup">
                        <div className="mockup-screen">
                            {/* Chat Button */}
                            <div ref={chatButtonRef} className="mockup-chat-button">
                                <span className="mockup-icon-wrapper">
                                    <i className="bi bi-heart-pulse mockup-icon-medical"></i>
                                    <i className="bi bi-robot mockup-icon-robot"></i>
                                </span>
                            </div>
                            
                            {/* Chat Window */}
                            <div className={`mockup-chat-window ${chatWindowOpen ? 'show' : ''}`}>
                                <div className="mockup-chat-header">
                                    <span className="mockup-icon-wrapper-header">
                                        <i className="bi bi-heart-pulse mockup-icon-medical"></i>
                                        <i className="bi bi-robot mockup-icon-robot"></i>
                                    </span>
                                    <span>Medical AI Assistant</span>
                                </div>
                                <div className="mockup-chat-messages">
                                    <div className={`mockup-message mockup-message-user ${messagesVisible[0] ? 'show' : ''}`} style={messagesVisible[0] ? { opacity: 1, transform: 'translateY(0)', transition: 'opacity 0.5s ease, transform 0.5s ease' } : {}}>
                                        <div className="mockup-message-content">Send Patient History</div>
                                    </div>
                                    <div className={`mockup-message mockup-message-assistant ${messagesVisible[1] ? 'show' : ''}`} style={messagesVisible[1] ? { opacity: 1, transform: 'translateY(0)', transition: 'opacity 0.5s ease, transform 0.5s ease' } : {}}>
                                        <div className="mockup-message-content">Analyzing patient history...</div>
                                    </div>
                                    <div className={`mockup-message mockup-message-assistant ${messagesVisible[2] ? 'show' : ''}`} style={messagesVisible[2] ? { opacity: 1, transform: 'translateY(0)', transition: 'opacity 0.5s ease, transform 0.5s ease' } : {}}>
                                        <div className="mockup-message-content">Based on the patient's medical history, I've identified:<br/><br/>• Chronic hypertension (5 years)<br/>• Type 2 diabetes (3 years)<br/>• Regular eye examinations show stable vision<br/><br/>Recommendations: Continue current medication regimen and schedule follow-up in 3 months.</div>
                                    </div>
                                </div>
                                <div className="mockup-chat-actions">
                                    <button className="mockup-action-btn">Send Patient History</button>
                                    <button className="mockup-action-btn">Summarize Consultation</button>
                                </div>
                            </div>
                            {/* Mouse Cursor */}
                            <div ref={mouseCursorRef} className="mockup-mouse-cursor"></div>
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
                <div className="whats-new-mockup-container">
                    <div className="whats-new-mockup-label">Intelligent Autocomplete Demo</div>
                    <div className="complaints-mockup">
                        <div className="mockup-screen">
                            <div className="mockup-form-field">
                                <label>Chief Complaint</label>
                                <div className="mockup-input-group">
                                    <input type="text" className="mockup-input" placeholder="Type complaint..." defaultValue="Headache" />
                                </div>
                            </div>
                            <div className={`mockup-autocomplete ${autocompleteVisible ? 'show' : ''}`}>
                                <div className="mockup-autocomplete-item">Headache - Migraine</div>
                                <div className="mockup-autocomplete-item">Headache - Tension</div>
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
                <div className="whats-new-mockup-container">
                    <div className="whats-new-mockup-label">Common Complaints System Demo</div>
                    <div className="complaints-mockup">
                        <div className="mockup-screen">
                            <div className="mockup-form-field">
                                <label>Chief Complaint</label>
                                <div className="mockup-input-group">
                                    <input type="text" className="mockup-input" placeholder="Type complaint..." defaultValue="Headache" />
                                    <button className="mockup-btn-primary">Most Common Cases</button>
                                </div>
                            </div>
                            <div className={`mockup-modal-overlay ${complaintsModalVisible ? 'show' : ''}`}>
                                <div className="mockup-modal">
                                    <div className="mockup-modal-header">
                                        <h5>Most Common Cases</h5>
                                        <button className="mockup-close-btn">&times;</button>
                                    </div>
                                    <div className="mockup-modal-body">
                                        <div className="mockup-complaint-item">Headache - Migraine</div>
                                        <div className="mockup-complaint-item">Eye pain - Conjunctivitis</div>
                                        <div className="mockup-complaint-item">Blurred vision - Refractive error</div>
                                        <div className="mockup-complaint-item">Dry eyes - Dry eye syndrome</div>
                                    </div>
                                </div>
                            </div>
                            <div className={`mockup-autocomplete ${autocompleteVisible ? 'show' : ''}`}>
                                <div className="mockup-autocomplete-item">Headache - Migraine</div>
                                <div className="mockup-autocomplete-item">Headache - Tension</div>
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
