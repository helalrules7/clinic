/**
 * V10Highlights — landing-page section showing the six biggest v10 wins
 * with inline TSX mockups (no screenshots needed). Designed for the new
 * Home page after the v8 → v10 update. Lives outside Home.tsx to keep
 * that file maintainable.
 */
import { motion } from 'framer-motion';
import { Layout, KanbanSquare, Palette, Settings as SettingsIcon, Sparkles, PenLine } from 'lucide-react';
import { useTranslation } from 'react-i18next';

/* ----------------------------- shared bits ----------------------------- */

interface MockupCardProps {
    title: string;
    description: string;
    Icon: React.ElementType;
    accent: string; // tailwind gradient suffix e.g. "from-indigo-500 to-violet-600"
    children: React.ReactNode; // the mockup itself
    delay?: number;
}

function MockupCard({ title, description, Icon, accent, children, delay = 0 }: MockupCardProps) {
    return (
        <motion.div
            initial={{ opacity: 0, y: 24 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: '-50px' }}
            transition={{ duration: 0.5, delay, ease: [0.22, 0.61, 0.36, 1] }}
            whileHover={{ y: -4 }}
            className="group relative rounded-2xl border border-gray-200/80 dark:border-white/10 bg-white/70 dark:bg-white/5 backdrop-blur-md p-5 overflow-hidden shadow-sm hover:shadow-xl transition-shadow"
        >
            {/* glow blob top-right */}
            <div
                className={`pointer-events-none absolute -top-12 -right-12 w-40 h-40 rounded-full bg-gradient-to-br ${accent} opacity-20 dark:opacity-30 blur-2xl group-hover:opacity-40 transition-opacity`}
            />
            {/* bottom accent line */}
            <div className={`absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r ${accent} opacity-80`} />

            <div className="relative">
                <div className="flex items-center gap-3 mb-3">
                    <div className={`p-2 rounded-lg bg-gradient-to-br ${accent} text-white shadow-sm`}>
                        <Icon size={18} />
                    </div>
                    <h3 className="text-base font-semibold text-gray-900 dark:text-white">{title}</h3>
                </div>
                <p className="text-sm text-gray-600 dark:text-gray-400 mb-4 leading-relaxed">{description}</p>
                <div className="relative">{children}</div>
            </div>
        </motion.div>
    );
}

/* ------------------- 1) Mini Sidebar mockup ------------------- */

function MiniSidebarMockup() {
    return (
        <div className="rounded-lg bg-slate-900 border border-white/5 overflow-hidden h-44 flex shadow-inner">
            {/* mini rail */}
            <div className="w-12 bg-slate-950 flex flex-col items-center py-2 gap-1.5 border-r border-white/5">
                {[true, false, false, false, false].map((active, i) => (
                    <div
                        key={i}
                        className={`w-8 h-8 rounded-md flex items-center justify-center ${active
                            ? 'bg-gradient-to-br from-indigo-500 to-violet-600 shadow-md shadow-indigo-500/40'
                            : 'bg-slate-800/70'
                            }`}
                    >
                        <div className={`w-3.5 h-3.5 rounded ${active ? 'bg-white/90' : 'bg-slate-500'}`} />
                    </div>
                ))}
                <div className="mt-auto w-8 h-8 rounded-full bg-slate-800 ring-2 ring-emerald-500/50" />
            </div>

            {/* peek-expanded panel sliding in */}
            <motion.div
                initial={{ opacity: 0, x: -16 }}
                whileInView={{ opacity: 1, x: 0 }}
                viewport={{ once: false, margin: '-20px' }}
                transition={{ delay: 0.25, duration: 0.4 }}
                className="flex-1 bg-gradient-to-r from-slate-900 to-slate-900/80 p-3 relative"
            >
                <div className="text-[9px] uppercase tracking-wider text-indigo-300 font-semibold mb-2">Medical Storage</div>
                <div className="space-y-1.5">
                    <div className="flex items-center gap-2 text-[10px] text-white/90 px-2 py-1.5 rounded bg-indigo-500/15 border-l-2 border-indigo-400">
                        <div className="w-2 h-2 rounded-sm bg-indigo-400" />
                        <span>Prescriptions</span>
                    </div>
                    <div className="flex items-center gap-2 text-[10px] text-slate-400 px-2 py-1.5 rounded">
                        <div className="w-2 h-2 rounded-sm bg-slate-600" />
                        <span>Glasses</span>
                    </div>
                    <div className="flex items-center gap-2 text-[10px] text-slate-400 px-2 py-1.5 rounded">
                        <div className="w-2 h-2 rounded-sm bg-slate-600" />
                        <span>Patient Media</span>
                    </div>
                </div>
                {/* shadow drop on the right edge to suggest overlay */}
                <div className="absolute inset-y-0 -right-3 w-3 bg-gradient-to-r from-black/40 to-transparent" />
            </motion.div>

            {/* dimmed content peeking behind */}
            <div className="hidden md:block flex-1 bg-slate-800/40 p-3">
                <div className="h-1.5 w-3/4 bg-slate-700 rounded mb-2" />
                <div className="h-1.5 w-1/2 bg-slate-700 rounded mb-2" />
                <div className="h-1.5 w-2/3 bg-slate-700 rounded" />
            </div>
        </div>
    );
}

/* ------------------- 2) Patient Boards mockup ------------------- */

function PatientBoardsMockup() {
    const cols = [
        { label: 'Awaiting', color: 'bg-blue-500', tint: 'bg-blue-500/10', text: 'text-blue-300', n: 2 },
        { label: 'Pre-op', color: 'bg-amber-500', tint: 'bg-amber-500/10', text: 'text-amber-300', n: 1 },
        { label: 'Post-op', color: 'bg-emerald-500', tint: 'bg-emerald-500/10', text: 'text-emerald-300', n: 2 },
    ];
    return (
        <div className="rounded-lg bg-slate-900 border border-white/5 p-2 h-44 overflow-hidden">
            <div className="grid grid-cols-3 gap-1.5 h-full">
                {cols.map((c, ci) => (
                    <motion.div
                        key={c.label}
                        initial={{ opacity: 0, y: 12 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{ delay: 0.1 + ci * 0.08 }}
                        className={`relative rounded-md ${c.tint} p-1.5 border-t-2 ${c.color.replace('bg-', 'border-')} flex flex-col`}
                    >
                        <div className={`flex items-center justify-between mb-1.5 ${c.text}`}>
                            <span className="text-[8px] font-semibold uppercase tracking-wider">{c.label}</span>
                            <span className="text-[8px] font-mono opacity-70">{c.n}</span>
                        </div>
                        <div className="space-y-1 flex-1">
                            {Array.from({ length: c.n }).map((_, i) => (
                                <div
                                    key={i}
                                    className="bg-slate-800/90 rounded p-1.5 flex items-center gap-1.5 border border-white/5"
                                >
                                    <div className={`w-3 h-3 rounded-full ${c.color} text-[7px] text-white flex items-center justify-center font-bold flex-shrink-0`}>
                                        {String.fromCharCode(65 + ci + i)}
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <div className="h-1 bg-slate-700 rounded w-full mb-0.5" />
                                        <div className="h-1 bg-slate-700 rounded w-2/3" />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </motion.div>
                ))}
            </div>
        </div>
    );
}

/* ------------------- 3) Glass / Indigo Design System mockup ------------------- */

function DesignSystemMockup() {
    return (
        <div className="rounded-lg h-44 overflow-hidden relative bg-gradient-to-br from-indigo-950 via-slate-900 to-violet-950 p-3">
            {/* big card center */}
            <motion.div
                initial={{ opacity: 0, scale: 0.95 }}
                whileInView={{ opacity: 1, scale: 1 }}
                viewport={{ once: true }}
                transition={{ duration: 0.5 }}
                className="rounded-xl bg-white/80 dark:bg-white/[0.08] backdrop-blur-xl border border-white/30 dark:border-white/10 p-3 shadow-xl shadow-indigo-500/20 h-full flex flex-col"
            >
                <div className="flex items-center gap-2 mb-2">
                    <div className="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center">
                        <Palette size={14} className="text-white" />
                    </div>
                    <div>
                        <div className="text-[10px] text-slate-200/80 font-semibold">Glass Card</div>
                        <div className="text-[8px] text-slate-300/60">var(--card)</div>
                    </div>
                </div>
                <div className="flex-1 space-y-1.5">
                    <div className="h-1.5 bg-white/20 rounded w-full" />
                    <div className="h-1.5 bg-white/20 rounded w-4/5" />
                    <div className="h-1.5 bg-white/20 rounded w-3/5" />
                </div>
                {/* token chips */}
                <div className="flex gap-1 mt-2">
                    <span className="text-[8px] px-1.5 py-0.5 rounded-full bg-indigo-500/30 text-indigo-100">--accent</span>
                    <span className="text-[8px] px-1.5 py-0.5 rounded-full bg-violet-500/30 text-violet-100">--r-xl</span>
                    <span className="text-[8px] px-1.5 py-0.5 rounded-full bg-emerald-500/30 text-emerald-100">--glass</span>
                </div>
            </motion.div>

            {/* floating accent particles */}
            <motion.div
                animate={{ y: [0, -8, 0], opacity: [0.4, 0.7, 0.4] }}
                transition={{ duration: 3, repeat: Infinity, ease: 'easeInOut' }}
                className="absolute top-3 right-3 w-8 h-8 rounded-full bg-indigo-500/40 blur-md"
            />
            <motion.div
                animate={{ y: [0, 6, 0], opacity: [0.3, 0.6, 0.3] }}
                transition={{ duration: 4, repeat: Infinity, ease: 'easeInOut', delay: 0.5 }}
                className="absolute bottom-3 left-3 w-10 h-10 rounded-full bg-violet-500/40 blur-md"
            />
        </div>
    );
}

/* ------------------- 4) Auto Complete settings mockup ------------------- */

function SettingsMockup() {
    return (
        <div className="rounded-lg bg-slate-900 border border-white/5 p-3 h-44 overflow-hidden">
            <div className="text-[9px] uppercase tracking-wider text-indigo-300 font-semibold mb-2 flex items-center gap-1.5">
                <SettingsIcon size={10} /> Auto Complete
            </div>
            <div className="space-y-2">
                {[
                    { label: 'Consultation suggestions', on: true, color: 'from-indigo-500 to-violet-600' },
                    { label: 'ICD-10 codes', on: true, color: 'from-blue-500 to-cyan-600' },
                    { label: 'Medication typeahead', on: false, color: 'from-amber-500 to-orange-600' },
                ].map((row, i) => (
                    <motion.div
                        key={row.label}
                        initial={{ opacity: 0, x: -8 }}
                        whileInView={{ opacity: 1, x: 0 }}
                        viewport={{ once: true }}
                        transition={{ delay: 0.1 + i * 0.08 }}
                        className="flex items-center justify-between px-2 py-1.5 rounded-md bg-slate-800/80 border border-white/5"
                    >
                        <span className="text-[10px] text-slate-200">{row.label}</span>
                        <div
                            className={`relative w-7 h-3.5 rounded-full transition-colors ${row.on ? `bg-gradient-to-r ${row.color}` : 'bg-slate-700'
                                }`}
                        >
                            <motion.div
                                animate={{ x: row.on ? 14 : 0 }}
                                transition={{ type: 'spring', stiffness: 500, damping: 30 }}
                                className="absolute top-0.5 left-0.5 w-2.5 h-2.5 bg-white rounded-full shadow"
                            />
                        </div>
                    </motion.div>
                ))}
            </div>
            {/* tiny typeahead preview */}
            <div className="mt-3 px-2 py-1.5 rounded bg-slate-800/60 border border-white/5">
                <div className="text-[9px] text-slate-400">Knee osteo<motion.span animate={{ opacity: [1, 0] }} transition={{ duration: 0.7, repeat: Infinity }}>|</motion.span></div>
                <div className="mt-1 text-[9px] text-indigo-300">↳ Knee osteoarthritis · Knee osteonecrosis</div>
            </div>
        </div>
    );
}

/* ------------------- 5) AI Tools mockup ------------------- */

function AiToolsMockup() {
    return (
        <div className="rounded-lg bg-slate-900 border border-white/5 p-3 h-44 overflow-hidden flex flex-col">
            <div className="flex items-center gap-1.5 mb-2 text-[10px] font-semibold text-emerald-300">
                <Sparkles size={10} /> Consultation AI
            </div>
            {/* chat bubbles */}
            <div className="space-y-1.5 flex-1 overflow-hidden">
                <motion.div
                    initial={{ opacity: 0, y: 4 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ delay: 0.2 }}
                    className="text-[9px] bg-slate-800 rounded-lg rounded-tl-none p-2 max-w-[85%] text-slate-200"
                >
                    Summarize prior visits for this patient.
                </motion.div>
                <motion.div
                    initial={{ opacity: 0, y: 4 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ delay: 0.5 }}
                    className="text-[9px] bg-gradient-to-r from-indigo-600 to-violet-600 rounded-lg rounded-tr-none p-2 max-w-[85%] ml-auto text-white"
                >
                    3 visits in last 90 days. Trend: ↗ improving IOP.
                </motion.div>
            </div>
            {/* ICD-10 chips */}
            <div className="mt-2 pt-2 border-t border-white/5">
                <div className="text-[8px] text-slate-400 mb-1 uppercase tracking-wider">ICD-10 Suggestions</div>
                <div className="flex gap-1 flex-wrap">
                    {['M17.11', 'M17.0', 'H40.10'].map((c, i) => (
                        <motion.span
                            key={c}
                            initial={{ opacity: 0, scale: 0.8 }}
                            whileInView={{ opacity: 1, scale: 1 }}
                            viewport={{ once: true }}
                            transition={{ delay: 0.7 + i * 0.1 }}
                            className="text-[8px] font-mono px-1.5 py-0.5 rounded bg-emerald-500/15 text-emerald-300 border border-emerald-500/30"
                        >
                            {c}
                        </motion.span>
                    ))}
                </div>
            </div>
        </div>
    );
}

/* ------------------- 6) Drawing Tools mockup ------------------- */

function DrawingToolsMockup() {
    return (
        <div className="rounded-lg bg-slate-900 border border-white/5 h-44 overflow-hidden flex flex-col">
            {/* toolbar */}
            <div className="flex items-center gap-1 px-2 py-1.5 border-b border-white/5 bg-slate-950/60">
                {[
                    { c: 'bg-white', ring: 'ring-1 ring-white/30' },
                    { c: 'bg-rose-500' },
                    { c: 'bg-blue-500' },
                    { c: 'bg-emerald-500' },
                    { c: 'bg-amber-500' },
                ].map((p, i) => (
                    <div key={i} className={`w-3.5 h-3.5 rounded-full ${p.c} ${p.ring || ''}`} />
                ))}
                <div className="w-px h-3 bg-white/10 mx-1" />
                <div className="flex gap-1">
                    {[PenLine, Sparkles].map((I, i) => (
                        <div
                            key={i}
                            className={`w-5 h-5 rounded flex items-center justify-center ${i === 0 ? 'bg-indigo-500/30 ring-1 ring-indigo-500/50' : 'bg-slate-800'}`}
                        >
                            <I size={10} className={i === 0 ? 'text-indigo-200' : 'text-slate-400'} />
                        </div>
                    ))}
                </div>
                <span className="ml-auto text-[8px] text-slate-400 font-mono">Layer 1 · Pen</span>
            </div>
            {/* canvas */}
            <div className="flex-1 relative bg-gradient-to-br from-slate-900 to-slate-950 overflow-hidden">
                <svg viewBox="0 0 200 100" className="absolute inset-0 w-full h-full">
                    {/* schematic eye / orbit shape */}
                    <motion.ellipse
                        cx="100" cy="50" rx="60" ry="32"
                        fill="none" stroke="#94a3b8" strokeWidth="1.5" strokeDasharray="4 3"
                        initial={{ pathLength: 0 }}
                        whileInView={{ pathLength: 1 }}
                        viewport={{ once: true }}
                        transition={{ duration: 1.2, ease: 'easeInOut' }}
                    />
                    <motion.circle
                        cx="100" cy="50" r="14"
                        fill="none" stroke="#818cf8" strokeWidth="2"
                        initial={{ pathLength: 0 }}
                        whileInView={{ pathLength: 1 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.8, delay: 0.6 }}
                    />
                    <motion.path
                        d="M70 30 Q 100 10 130 30"
                        fill="none" stroke="#f472b6" strokeWidth="2" strokeLinecap="round"
                        initial={{ pathLength: 0 }}
                        whileInView={{ pathLength: 1 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.8, delay: 1.2 }}
                    />
                </svg>
                {/* tag */}
                <div className="absolute bottom-2 left-2 text-[8px] px-1.5 py-0.5 rounded bg-slate-800/80 text-slate-300 backdrop-blur-sm border border-white/10">
                    OD · pre-op sketch
                </div>
            </div>
        </div>
    );
}

/* ----------------------------- main ----------------------------- */

export default function V10Highlights() {
    const { t } = useTranslation();
    const mockups = [
        { key: 'mini_sidebar', Icon: Layout, accent: 'from-indigo-500 to-violet-600', body: <MiniSidebarMockup /> },
        { key: 'patient_boards', Icon: KanbanSquare, accent: 'from-sky-500 to-blue-600', body: <PatientBoardsMockup /> },
        { key: 'design_system', Icon: Palette, accent: 'from-violet-500 to-fuchsia-600', body: <DesignSystemMockup /> },
        { key: 'settings', Icon: SettingsIcon, accent: 'from-amber-500 to-orange-600', body: <SettingsMockup /> },
        { key: 'ai_tools', Icon: Sparkles, accent: 'from-emerald-500 to-teal-600', body: <AiToolsMockup /> },
        { key: 'drawing_tools', Icon: PenLine, accent: 'from-rose-500 to-pink-600', body: <DrawingToolsMockup /> },
    ];

    return (
        <section className="relative mb-16">
            {/* header */}
            <div className="mb-8">
                <motion.div
                    initial={{ opacity: 0, y: 12 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    className="inline-flex items-center gap-2 px-3 py-1 mb-3 rounded-full text-xs font-semibold tracking-wider uppercase
                               bg-gradient-to-r from-indigo-500/15 to-violet-500/15 text-indigo-700 dark:text-indigo-300
                               border border-indigo-500/30"
                >
                    <Sparkles size={12} />
                    {t('sections.home.v10_highlights.badge')}
                </motion.div>
                <motion.h2
                    initial={{ opacity: 0, y: 12 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ delay: 0.05 }}
                    className="text-3xl md:text-4xl font-bold tracking-tight text-gray-900 dark:text-white mb-2"
                >
                    {t('sections.home.v10_highlights.title')}
                </motion.h2>
                <motion.p
                    initial={{ opacity: 0, y: 12 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ delay: 0.1 }}
                    className="text-base md:text-lg text-gray-600 dark:text-gray-400 max-w-2xl"
                >
                    {t('sections.home.v10_highlights.subtitle')}
                </motion.p>
            </div>

            {/* 6-up grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                {mockups.map((m, idx) => (
                    <MockupCard
                        key={m.key}
                        title={t(`sections.home.v10_highlights.items.${m.key}.title`)}
                        description={t(`sections.home.v10_highlights.items.${m.key}.description`)}
                        Icon={m.Icon}
                        accent={m.accent}
                        delay={idx * 0.06}
                    >
                        {m.body}
                    </MockupCard>
                ))}
            </div>
        </section>
    );
}
