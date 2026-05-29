import { motion } from 'framer-motion';

interface HeroProps {
    title: string;
    subtitle: string;
    badge?: string;
}

export default function Hero({ title, subtitle, badge }: HeroProps) {
    return (
        <div
            className="
                relative mb-16 py-16 px-6 md:px-10 rounded-3xl overflow-hidden shadow-2xl
                bg-gradient-to-br from-indigo-50 via-violet-50 to-fuchsia-100
                dark:from-indigo-950 dark:via-slate-950 dark:to-violet-950
                ring-1 ring-indigo-200/50 dark:ring-white/10
            "
        >
            {/* grain texture overlay (visible mostly in dark) */}
            <div className="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 mix-blend-overlay pointer-events-none" />

            {/* animated mesh-style blurred orbs — softer in light, vivid in dark */}
            <motion.div
                animate={{ x: [0, 30, 0], y: [0, -20, 0], opacity: [0.5, 0.7, 0.5] }}
                transition={{ duration: 16, repeat: Infinity, ease: 'easeInOut' }}
                className="absolute top-0 right-0 w-96 h-96 bg-indigo-300/40 dark:bg-indigo-500/30 blur-[120px] rounded-full translate-x-1/3 -translate-y-1/3"
            />
            <motion.div
                animate={{ x: [0, -20, 0], y: [0, 30, 0], opacity: [0.4, 0.6, 0.4] }}
                transition={{ duration: 18, repeat: Infinity, ease: 'easeInOut', delay: 2 }}
                className="absolute bottom-0 left-0 w-80 h-80 bg-violet-300/35 dark:bg-violet-500/30 blur-[100px] rounded-full -translate-x-1/3 translate-y-1/3"
            />
            <motion.div
                animate={{ scale: [1, 1.1, 1], opacity: [0.3, 0.5, 0.3] }}
                transition={{ duration: 12, repeat: Infinity, ease: 'easeInOut' }}
                className="absolute top-1/2 right-1/4 w-64 h-64 bg-fuchsia-300/30 dark:bg-fuchsia-500/20 blur-[90px] rounded-full"
            />

            {/* subtle grid overlay — adapts opacity per theme */}
            <div
                className="absolute inset-0 opacity-[0.05] dark:opacity-[0.07] pointer-events-none"
                style={{
                    backgroundImage:
                        'linear-gradient(currentColor 1px, transparent 1px), linear-gradient(90deg, currentColor 1px, transparent 1px)',
                    backgroundSize: '40px 40px',
                    color: 'rgb(99 102 241)', // indigo-500-ish
                }}
            />

            <motion.div
                initial={{ opacity: 0, y: 24 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.7, ease: [0.22, 0.61, 0.36, 1] }}
                className="relative z-10 max-w-3xl"
            >
                {badge && (
                    <motion.span
                        initial={{ opacity: 0, scale: 0.9 }}
                        animate={{ opacity: 1, scale: 1 }}
                        transition={{ delay: 0.15 }}
                        className="
                            inline-flex items-center gap-2 px-3.5 py-1.5 mb-5 text-xs font-semibold tracking-wider uppercase rounded-full
                            bg-white/70 text-indigo-700 border border-indigo-300/60 shadow-md shadow-indigo-300/30
                            dark:bg-white/10 dark:text-white dark:border-white/20 dark:shadow-indigo-500/20
                            backdrop-blur-md
                        "
                    >
                        <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-emerald-400/60 shadow-md animate-pulse" />
                        {badge}
                    </motion.span>
                )}
                <h1
                    className="
                        text-4xl md:text-6xl font-bold mb-5 tracking-tight leading-[1.05] bg-clip-text text-transparent
                        bg-gradient-to-br from-indigo-700 via-violet-700 to-fuchsia-700
                        dark:from-white dark:via-indigo-100 dark:to-violet-200
                    "
                >
                    {title}
                </h1>
                <p className="text-lg md:text-xl text-slate-700 dark:text-slate-300/90 max-w-2xl leading-relaxed">
                    {subtitle}
                </p>
            </motion.div>
        </div>
    );
}
