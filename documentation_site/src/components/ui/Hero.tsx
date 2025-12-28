import { motion } from 'framer-motion';

interface HeroProps {
    title: string;
    subtitle: string;
    badge?: string;
}

export default function Hero({ title, subtitle, badge }: HeroProps) {
    return (
        <div className="relative mb-16 py-12 px-6 rounded-3xl overflow-hidden bg-gradient-to-br from-primary-900 via-dark-900 to-purple-900 text-white shadow-2xl">
            <div className="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100"></div>
            <div className="absolute top-0 right-0 w-64 h-64 bg-primary-500/20 blur-[100px] rounded-full translate-x-1/2 -translate-y-1/2"></div>

            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="relative z-10 max-w-3xl"
            >
                {badge && (
                    <span className="inline-block px-3 py-1 mb-4 text-xs font-medium tracking-wider uppercase bg-white/10 backdrop-blur-sm rounded-full border border-white/20">
                        {badge}
                    </span>
                )}
                <h1 className="text-4xl md:text-5xl font-bold mb-4 tracking-tight leading-tight">
                    {title}
                </h1>
                <p className="text-lg md:text-xl text-gray-300 max-w-2xl leading-relaxed">
                    {subtitle}
                </p>
            </motion.div>
        </div>
    );
}
