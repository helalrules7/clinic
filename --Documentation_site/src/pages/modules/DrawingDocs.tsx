import Hero from '../../components/ui/Hero';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { useTranslation } from 'react-i18next';
import { motion } from 'framer-motion';
import { PenLine, Eraser, Palette, Undo2, Redo2, Layers, Save, Image as ImageIcon, FileText, Stethoscope, Sparkles } from 'lucide-react';

/* ---------- canvas + toolbar mockup ---------- */

function DrawCanvasMockup() {
    const colors = ['bg-white', 'bg-rose-500', 'bg-blue-500', 'bg-emerald-500', 'bg-amber-500', 'bg-violet-500'];
    return (
        <div className="rounded-2xl bg-slate-950 border border-white/10 shadow-xl overflow-hidden">
            {/* toolbar */}
            <div className="flex items-center gap-2 px-3 py-2 border-b border-white/10 bg-slate-950/80">
                <div className="flex items-center gap-1">
                    {colors.map((c, i) => (
                        <button
                            key={i}
                            className={`w-5 h-5 rounded-full ${c} ${i === 0 ? 'ring-2 ring-indigo-400/70' : ''}`}
                        />
                    ))}
                </div>
                <div className="w-px h-4 bg-white/10 mx-1" />
                <div className="flex gap-1">
                    {[
                        { I: PenLine, on: true },
                        { I: Eraser, on: false },
                        { I: Undo2, on: false },
                        { I: Redo2, on: false },
                        { I: Layers, on: false },
                        { I: Save, on: false },
                    ].map(({ I, on }, i) => (
                        <button
                            key={i}
                            className={`w-7 h-7 rounded-md flex items-center justify-center ${on ? 'bg-indigo-500/30 ring-1 ring-indigo-400/60' : 'bg-slate-800/70 hover:bg-slate-800'}`}
                        >
                            <I size={13} className={on ? 'text-indigo-200' : 'text-slate-400'} />
                        </button>
                    ))}
                </div>
                <span className="ml-auto text-[10px] text-slate-400 font-mono">Layer 1 · Pen · 2px</span>
            </div>

            {/* canvas */}
            <div className="relative bg-gradient-to-br from-slate-900 to-slate-950 aspect-[3/2]">
                {/* schematic eye sketch with animated strokes */}
                <svg viewBox="0 0 320 200" className="absolute inset-0 w-full h-full">
                    {/* sclera outline */}
                    <motion.ellipse
                        cx="160" cy="100" rx="90" ry="46"
                        fill="none" stroke="#cbd5e1" strokeWidth="1.5" strokeDasharray="5 4"
                        initial={{ pathLength: 0 }}
                        whileInView={{ pathLength: 1 }}
                        viewport={{ once: true }}
                        transition={{ duration: 1.2, ease: 'easeInOut' }}
                    />
                    {/* iris */}
                    <motion.circle
                        cx="160" cy="100" r="22"
                        fill="rgba(129,140,248,0.1)" stroke="#818cf8" strokeWidth="2.5"
                        initial={{ pathLength: 0, scale: 0 }}
                        whileInView={{ pathLength: 1, scale: 1 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.8, delay: 0.6 }}
                    />
                    {/* pupil */}
                    <motion.circle
                        cx="160" cy="100" r="6"
                        fill="#1e293b"
                        initial={{ scale: 0 }}
                        whileInView={{ scale: 1 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.4, delay: 1.0 }}
                    />
                    {/* annotation strokes */}
                    <motion.path
                        d="M 75 60 Q 130 30 200 50"
                        fill="none" stroke="#f472b6" strokeWidth="2.5" strokeLinecap="round"
                        initial={{ pathLength: 0 }}
                        whileInView={{ pathLength: 1 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.8, delay: 1.2 }}
                    />
                    <motion.path
                        d="M 220 130 L 260 160"
                        fill="none" stroke="#f472b6" strokeWidth="2.5" strokeLinecap="round"
                        initial={{ pathLength: 0 }}
                        whileInView={{ pathLength: 1 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.5, delay: 1.6 }}
                    />
                    {/* small labels */}
                    <motion.text
                        x="265" y="170" fontSize="9" fill="#f472b6" fontFamily="ui-monospace, monospace"
                        initial={{ opacity: 0 }}
                        whileInView={{ opacity: 1 }}
                        viewport={{ once: true }}
                        transition={{ delay: 1.8 }}
                    >
                        nasal
                    </motion.text>
                </svg>
                {/* corner status badge */}
                <div className="absolute bottom-2 left-2 text-[9px] px-2 py-0.5 rounded bg-slate-800/80 text-slate-300 backdrop-blur-sm border border-white/10">
                    OD · pre-op sketch · saved
                </div>
                <motion.div
                    animate={{ opacity: [0.4, 1, 0.4] }}
                    transition={{ duration: 2, repeat: Infinity }}
                    className="absolute top-2 right-2 flex items-center gap-1 text-[9px] px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-300 border border-emerald-500/30"
                >
                    <span className="w-1 h-1 rounded-full bg-emerald-500" />
                    Recording stroke
                </motion.div>
            </div>
        </div>
    );
}

/* ---------- workflow steps mockup ---------- */

function WorkflowMockup() {
    const steps = [
        { Icon: Stethoscope, label: 'Open appointment' },
        { Icon: PenLine, label: 'Open Draw Canvas' },
        { Icon: Palette, label: 'Annotate findings' },
        { Icon: Save, label: 'Auto-saved per visit' },
        { Icon: FileText, label: 'Included in summary' },
    ];
    return (
        <div className="rounded-2xl bg-gradient-to-br from-indigo-950 via-slate-950 to-violet-950 border border-white/10 p-5 shadow-xl">
            <div className="grid grid-cols-1 md:grid-cols-5 gap-3 md:gap-1.5">
                {steps.map(({ Icon, label }, i) => (
                    <motion.div
                        key={label}
                        initial={{ opacity: 0, y: 12 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{ delay: i * 0.08 }}
                        className="relative flex md:flex-col items-center gap-2 md:gap-1.5"
                    >
                        <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 shadow-md shadow-indigo-500/30 flex items-center justify-center flex-shrink-0">
                            <Icon size={18} className="text-white" />
                        </div>
                        <div className="md:text-center text-[11px] text-slate-200 leading-tight">{label}</div>
                        {/* connecting arrow on desktop */}
                        {i < steps.length - 1 && (
                            <div className="hidden md:block absolute top-5 -right-2 w-4 h-px bg-gradient-to-r from-indigo-400/50 to-transparent" />
                        )}
                    </motion.div>
                ))}
            </div>
        </div>
    );
}

/* ---------- main page ---------- */

export default function DrawingDocs() {
    const { t } = useTranslation();
    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.drawing.hero.title')}
                subtitle={t('sections.drawing.hero.subtitle')}
                badge={t('sections.drawing.hero.badge')}
            />

            <Section title={t('sections.drawing.overview.title')} id="overview" icon={<PenLine />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.drawing.overview.content')}
                </p>
                <div className="mt-6">
                    <DrawCanvasMockup />
                </div>
            </Section>

            <Section title={t('sections.drawing.tools.title')} id="tools" icon={<Sparkles />}>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {[
                        { Icon: PenLine, k: 'pen' },
                        { Icon: Eraser, k: 'eraser' },
                        { Icon: Palette, k: 'colors' },
                        { Icon: Undo2, k: 'undo' },
                        { Icon: Layers, k: 'layers' },
                        { Icon: Save, k: 'autosave' },
                    ].map(({ Icon, k }) => (
                        <Card key={k} title={t(`sections.drawing.tools.items.${k}.title`)} icon={Icon}>
                            {t(`sections.drawing.tools.items.${k}.description`)}
                        </Card>
                    ))}
                </div>
            </Section>

            <Section title={t('sections.drawing.workflow.title')} id="workflow" icon={<Stethoscope />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.drawing.workflow.content')}
                </p>
                <WorkflowMockup />
            </Section>

            <Section title={t('sections.drawing.outputs.title')} id="outputs" icon={<ImageIcon />}>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card title={t('sections.drawing.outputs.png.title')} icon={ImageIcon}>
                        {t('sections.drawing.outputs.png.description')}
                    </Card>
                    <Card title={t('sections.drawing.outputs.summary.title')} icon={FileText}>
                        {t('sections.drawing.outputs.summary.description')}
                    </Card>
                </div>
            </Section>
        </div>
    );
}
