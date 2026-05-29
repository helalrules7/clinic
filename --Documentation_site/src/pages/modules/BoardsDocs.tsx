import Hero from '../../components/ui/Hero';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { useTranslation } from 'react-i18next';
import { motion } from 'framer-motion';
import {
    KanbanSquare, Move, Columns3, MessageSquare, Paperclip, AtSign,
    Filter, Palette, Zap, ShieldCheck, Sparkles, ListChecks
} from 'lucide-react';

/* ---------- inline rich mockup: full board with 4 columns + comments side panel ---------- */

function BoardMockup() {
    const cols = [
        { label: 'New Consultation', color: '#0ea5e9', tint: 'bg-sky-500/10', text: 'text-sky-300', cards: ['Ahmed M.', 'Sara T.'] },
        { label: 'Awaiting Imaging', color: '#f59e0b', tint: 'bg-amber-500/10', text: 'text-amber-300', cards: ['Omar H.'] },
        { label: 'Surgical · Pre-op', color: '#a855f7', tint: 'bg-violet-500/10', text: 'text-violet-300', cards: ['Fatima A.', 'Yousef S.', 'Layla Q.'] },
        { label: 'Long-term Follow-up', color: '#64748b', tint: 'bg-slate-500/10', text: 'text-slate-300', cards: ['Hassan B.'] },
    ];
    return (
        <div className="rounded-2xl bg-slate-950 border border-white/10 p-4 shadow-xl overflow-hidden">
            {/* board header */}
            <div className="flex items-center justify-between mb-4">
                <div className="flex items-center gap-2">
                    <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center">
                        <KanbanSquare size={16} className="text-white" />
                    </div>
                    <div>
                        <div className="text-sm font-semibold text-white">Patients Board</div>
                        <div className="text-[10px] text-slate-400">Ophthalmology · 7 active cards</div>
                    </div>
                </div>
                <div className="flex items-center gap-1.5">
                    <button className="text-[10px] px-2 py-1 rounded bg-white/10 text-slate-300 border border-white/10">+ Column</button>
                    <button className="text-[10px] px-2 py-1 rounded bg-gradient-to-r from-indigo-500 to-violet-600 text-white font-semibold">+ Patient</button>
                </div>
            </div>
            {/* columns */}
            <div className="grid grid-cols-4 gap-2.5">
                {cols.map((c, ci) => (
                    <motion.div
                        key={c.label}
                        initial={{ opacity: 0, y: 12 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{ delay: 0.1 + ci * 0.08 }}
                        className={`rounded-lg ${c.tint} p-2 flex flex-col`}
                        style={{ borderTop: `3px solid ${c.color}` }}
                    >
                        <div className={`flex items-center justify-between mb-2 ${c.text}`}>
                            <span className="text-[10px] font-semibold uppercase tracking-wider truncate">{c.label}</span>
                            <span className="text-[9px] font-mono opacity-70 px-1 rounded bg-white/5">{c.cards.length}</span>
                        </div>
                        <div className="space-y-1.5 min-h-[80px]">
                            {c.cards.map((name, i) => (
                                <motion.div
                                    key={i}
                                    whileHover={{ y: -2 }}
                                    className="bg-slate-800/95 rounded p-1.5 flex items-center gap-1.5 border border-white/5 hover:border-white/15 cursor-pointer"
                                >
                                    <div
                                        className="w-4 h-4 rounded-full text-[8px] text-white flex items-center justify-center font-bold flex-shrink-0"
                                        style={{ background: c.color }}
                                    >
                                        {name[0]}
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <div className="text-[9px] text-slate-200 truncate">{name}</div>
                                        <div className="flex items-center gap-1 text-[7px] text-slate-500">
                                            <MessageSquare size={6} /> {Math.floor(Math.random() * 5) + 1}
                                            <Paperclip size={6} className="ml-1" /> {Math.floor(Math.random() * 3)}
                                        </div>
                                    </div>
                                </motion.div>
                            ))}
                        </div>
                    </motion.div>
                ))}
            </div>
        </div>
    );
}

/* ---------- card detail mockup (with comments + mentions + attachments) ---------- */

function CardDetailMockup() {
    return (
        <div className="rounded-2xl bg-slate-950 border border-white/10 p-4 shadow-xl">
            <div className="flex items-start gap-3 mb-3 pb-3 border-b border-white/10">
                <div className="w-10 h-10 rounded-full bg-violet-500 flex items-center justify-center font-bold text-white text-sm flex-shrink-0">F</div>
                <div className="flex-1">
                    <div className="text-sm font-semibold text-white">Fatima A. · Surgical · Pre-op</div>
                    <div className="text-[10px] text-slate-400 mt-0.5">Moved 2 days ago · IOL planning · Re-check on Mon</div>
                </div>
                <div className="flex gap-1">
                    <span className="text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">priority</span>
                    <span className="text-[9px] px-1.5 py-0.5 rounded bg-amber-500/15 text-amber-300 border border-amber-500/30">followup</span>
                </div>
            </div>
            {/* comments thread */}
            <div className="space-y-2.5">
                <div className="flex gap-2">
                    <div className="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center text-[10px] text-white font-bold flex-shrink-0">M</div>
                    <div className="flex-1 bg-slate-900 rounded-lg p-2 border border-white/5">
                        <div className="flex items-center gap-2 text-[10px] text-slate-400 mb-1">
                            <span className="font-semibold text-slate-200">Dr. Mohamed</span>
                            <span>·</span>
                            <span>2h ago</span>
                        </div>
                        <div className="text-[11px] text-slate-300">Pre-op clearance from <span className="text-indigo-300 bg-indigo-500/10 px-1 rounded">@dr_osama</span> needed. Attaching latest OCT.</div>
                        <div className="mt-1.5 inline-flex items-center gap-1 text-[10px] text-slate-400 px-1.5 py-0.5 rounded bg-slate-800 border border-white/5">
                            <Paperclip size={9} /> oct-2026-05-28.png
                        </div>
                    </div>
                </div>
                <div className="flex gap-2">
                    <div className="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-[10px] text-white font-bold flex-shrink-0">O</div>
                    <div className="flex-1 bg-slate-900 rounded-lg p-2 border border-white/5">
                        <div className="flex items-center gap-2 text-[10px] text-slate-400 mb-1">
                            <span className="font-semibold text-slate-200">Dr. Osama</span>
                            <span>·</span>
                            <span>45m ago</span>
                        </div>
                        <div className="text-[11px] text-slate-300">Looks good. Cleared for IOL surgery Tuesday. Move to <span className="bg-violet-500/15 text-violet-300 px-1 rounded">Surgical · Pre-op</span>.</div>
                    </div>
                </div>
                {/* reply box */}
                <div className="flex gap-2 mt-3">
                    <div className="w-6 h-6 rounded-full bg-slate-700 flex items-center justify-center text-[10px] text-slate-400 flex-shrink-0">+</div>
                    <div className="flex-1 bg-slate-900 rounded-lg p-2 border border-indigo-500/30 ring-1 ring-indigo-500/20">
                        <div className="text-[11px] text-slate-500 italic flex items-center gap-1.5">
                            Type a reply · use <AtSign size={10} className="text-indigo-400" /> to mention someone
                            <motion.span animate={{ opacity: [1, 0] }} transition={{ duration: 0.7, repeat: Infinity }} className="text-indigo-400">|</motion.span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

/* ---------- snapshot widget mockup ---------- */

function SnapshotMockup() {
    const data = [
        { label: 'New Consultation', color: '#0ea5e9', n: 12, pct: 38 },
        { label: 'Awaiting Imaging', color: '#f59e0b', n: 5, pct: 16 },
        { label: 'Pre-op', color: '#a855f7', n: 8, pct: 25 },
        { label: 'Post-op', color: '#22c55e', n: 4, pct: 12 },
        { label: 'Long-term', color: '#64748b', n: 3, pct: 9 },
    ];
    return (
        <div className="rounded-xl bg-slate-950 border border-white/10 p-4 shadow-xl">
            <div className="flex items-center gap-2 mb-3 pb-2 border-b border-white/10">
                <KanbanSquare size={14} className="text-indigo-400" />
                <span className="text-xs font-semibold text-white">Board Snapshot</span>
                <span className="ml-auto text-[10px] text-slate-500">/api/board/snapshot</span>
            </div>
            <div className="space-y-2">
                {data.map((d, i) => (
                    <motion.div
                        key={i}
                        initial={{ opacity: 0, width: 0 }}
                        whileInView={{ opacity: 1, width: '100%' }}
                        viewport={{ once: true }}
                        transition={{ delay: i * 0.08 }}
                        className="flex items-center gap-2"
                    >
                        <div className="w-2 h-2 rounded-full flex-shrink-0" style={{ background: d.color }} />
                        <div className="flex-1 min-w-0">
                            <div className="flex justify-between text-[10px] mb-0.5">
                                <span className="text-slate-300 truncate">{d.label}</span>
                                <span className="text-slate-400 font-mono">{d.n}</span>
                            </div>
                            <div className="h-1 bg-slate-800 rounded-full overflow-hidden">
                                <motion.div
                                    initial={{ width: 0 }}
                                    whileInView={{ width: `${d.pct}%` }}
                                    viewport={{ once: true }}
                                    transition={{ delay: 0.2 + i * 0.08, duration: 0.6 }}
                                    className="h-full rounded-full"
                                    style={{ background: d.color }}
                                />
                            </div>
                        </div>
                    </motion.div>
                ))}
            </div>
        </div>
    );
}

/* ---------- main page ---------- */

export default function BoardsDocs() {
    const { t } = useTranslation();
    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.boards.hero.title')}
                subtitle={t('sections.boards.hero.subtitle')}
                badge={t('sections.boards.hero.badge')}
            />

            <Section title={t('sections.boards.overview.title')} id="overview" icon={<KanbanSquare />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.boards.overview.content')}
                </p>
                <div className="mt-6">
                    <BoardMockup />
                </div>
            </Section>

            <Section title={t('sections.boards.key_features.title')} id="features" icon={<Sparkles />}>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {[
                        { Icon: Columns3, k: 'columns' },
                        { Icon: Move, k: 'drag_drop' },
                        { Icon: Palette, k: 'colors' },
                        { Icon: MessageSquare, k: 'comments' },
                        { Icon: AtSign, k: 'mentions' },
                        { Icon: Paperclip, k: 'attachments' },
                        { Icon: Filter, k: 'filters' },
                        { Icon: Zap, k: 'autoplace' },
                        { Icon: ShieldCheck, k: 'permissions' },
                    ].map(({ Icon, k }) => (
                        <Card key={k} title={t(`sections.boards.key_features.items.${k}.title`)} icon={Icon}>
                            {t(`sections.boards.key_features.items.${k}.description`)}
                        </Card>
                    ))}
                </div>
            </Section>

            <Section title={t('sections.boards.card_detail.title')} id="card-detail" icon={<MessageSquare />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                            {t('sections.boards.card_detail.content')}
                        </p>
                        <ul className="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            {(['threaded', 'mentions', 'attachments', 'audio', 'lightbox'] as const).map(k => (
                                <li key={k} className="flex items-start gap-2">
                                    <span className="mt-1 w-1.5 h-1.5 rounded-full bg-indigo-500 flex-shrink-0" />
                                    <span>{t(`sections.boards.card_detail.bullets.${k}`)}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                    <CardDetailMockup />
                </div>
            </Section>

            <Section title={t('sections.boards.snapshot.title')} id="snapshot" icon={<ListChecks />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                    <SnapshotMockup />
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                            {t('sections.boards.snapshot.content')}
                        </p>
                        <div className="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800/60 text-xs font-mono text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-white/10">
                            <span className="text-emerald-600 dark:text-emerald-400 font-bold">GET</span>
                            <span>/api/board/snapshot</span>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.boards.api.title')} id="api" icon={<Zap />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                    {t('sections.boards.api.content')}
                </p>
                <div className="mt-3">
                    <a
                        href="/docs/opth/api#board"
                        className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-500 to-violet-600 text-white text-sm font-semibold shadow-md shadow-indigo-500/30 hover:shadow-lg hover:shadow-indigo-500/40 transition-shadow"
                    >
                        {t('sections.boards.api.cta')}
                    </a>
                </div>
            </Section>
        </div>
    );
}
