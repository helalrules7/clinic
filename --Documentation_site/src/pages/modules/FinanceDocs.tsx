import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { DollarSign, Wallet, TrendingUp, Search, Calendar, Lock, FileSpreadsheet, Filter, Plus, Minus } from 'lucide-react';

export default function FinanceDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.finance.title')}
                subtitle={t('sections.finance.subtitle')}
            />

            <Section title={t('sections.finance.overview.title')} icon={<DollarSign />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.finance.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.finance.page_content.title')} icon={<Wallet />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.finance.page_content.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.finance.page_content.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.finance.page_content.features.balance_cards.title')}:</strong> {t('sections.finance.page_content.features.balance_cards.description')}</li>
                            <li><strong>{t('sections.finance.page_content.features.payment_types.title')}:</strong> {t('sections.finance.page_content.features.payment_types.description')}</li>
                            <li><strong>{t('sections.finance.page_content.features.transactions.title')}:</strong> {t('sections.finance.page_content.features.transactions.description')}</li>
                            <li><strong>{t('sections.finance.page_content.features.payments_table.title')}:</strong> {t('sections.finance.page_content.features.payments_table.description')}</li>
                            <li><strong>{t('sections.finance.page_content.features.export.title')}:</strong> {t('sections.finance.page_content.features.export.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.finance.page_content.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.page_content.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.page_content.route')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.page_content.js_file')}</code>
                            <code className="block text-sm text-cyan-600 dark:text-cyan-400 font-mono break-words whitespace-pre-wrap">{t('sections.finance.page_content.keyboard_shortcuts')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/finance/01-opt.png"
                            alt="Financial Management Page"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.finance.daily_balance.title')} icon={<Plus />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/finance/02-opt.png"
                            alt="Add Daily Balance Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.finance.daily_balance.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.finance.daily_balance.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.finance.daily_balance.features.types.title')}:</strong> {t('sections.finance.daily_balance.features.types.description')}</li>
                            <li><strong>{t('sections.finance.daily_balance.features.amount.title')}:</strong> {t('sections.finance.daily_balance.features.amount.description')}</li>
                            <li><strong>{t('sections.finance.daily_balance.features.description.title')}:</strong> {t('sections.finance.daily_balance.features.description.description')}</li>
                            <li><strong>{t('sections.finance.daily_balance.features.date.title')}:</strong> {t('sections.finance.daily_balance.features.date.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.finance.daily_balance.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.daily_balance.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.daily_balance.js_function')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.finance.daily_balance.shortcut')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.finance.expenses.title')} icon={<Minus />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.finance.expenses.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.finance.expenses.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.finance.expenses.features.quick_types.title')}:</strong> {t('sections.finance.expenses.features.quick_types.description')}</li>
                            <li><strong>{t('sections.finance.expenses.features.categories.title')}:</strong> {t('sections.finance.expenses.features.categories.description')}</li>
                            <li><strong>{t('sections.finance.expenses.features.notes.title')}:</strong> {t('sections.finance.expenses.features.notes.description')}</li>
                            <li><strong>{t('sections.finance.expenses.features.crud.title')}:</strong> {t('sections.finance.expenses.features.crud.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.finance.expenses.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.expenses.api_create')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.expenses.api_update')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.expenses.api_delete')}</code>
                            <code className="block text-sm text-cyan-600 dark:text-cyan-400 font-mono break-words whitespace-pre-wrap">{t('sections.finance.expenses.shortcut')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/finance/03-opt.png"
                            alt="Add Expense Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.finance.search.title')} icon={<Search />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/finance/04-opt.png"
                            alt="Search Transactions Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.finance.search.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.finance.search.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.finance.search.features.date_range.title')}:</strong> {t('sections.finance.search.features.date_range.description')}</li>
                            <li><strong>{t('sections.finance.search.features.type_filter.title')}:</strong> {t('sections.finance.search.features.type_filter.description')}</li>
                            <li><strong>{t('sections.finance.search.features.amount_range.title')}:</strong> {t('sections.finance.search.features.amount_range.description')}</li>
                            <li><strong>{t('sections.finance.search.features.keyword.title')}:</strong> {t('sections.finance.search.features.keyword.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.finance.search.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.search.js_function')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.search.shortcut')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.finance.daily_closure.title')} icon={<Calendar />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.finance.daily_closure.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.finance.daily_closure.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.finance.daily_closure.features.summary_cards.title')}:</strong> {t('sections.finance.daily_closure.features.summary_cards.description')}</li>
                            <li><strong>{t('sections.finance.daily_closure.features.balance_section.title')}:</strong> {t('sections.finance.daily_closure.features.balance_section.description')}</li>
                            <li><strong>{t('sections.finance.daily_closure.features.payments_table.title')}:</strong> {t('sections.finance.daily_closure.features.payments_table.description')}</li>
                            <li><strong>{t('sections.finance.daily_closure.features.withdrawals.title')}:</strong> {t('sections.finance.daily_closure.features.withdrawals.description')}</li>
                            <li><strong>{t('sections.finance.daily_closure.features.expenses_table.title')}:</strong> {t('sections.finance.daily_closure.features.expenses_table.description')}</li>
                            <li><strong>{t('sections.finance.daily_closure.features.net_amount.title')}:</strong> {t('sections.finance.daily_closure.features.net_amount.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.finance.daily_closure.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.daily_closure.controller')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.daily_closure.route')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.finance.daily_closure.api_endpoint')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/finance/05-opt.png"
                            alt="Daily Closure Page"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.finance.close_day.title')} icon={<Lock />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/finance/06-opt.png"
                            alt="Close Day Confirmation Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.finance.close_day.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.finance.close_day.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.finance.close_day.features.summary.title')}:</strong> {t('sections.finance.close_day.features.summary.description')}</li>
                            <li><strong>{t('sections.finance.close_day.features.notes.title')}:</strong> {t('sections.finance.close_day.features.notes.description')}</li>
                            <li><strong>{t('sections.finance.close_day.features.lock.title')}:</strong> {t('sections.finance.close_day.features.lock.description')}</li>
                            <li><strong>{t('sections.finance.close_day.features.warning.title')}:</strong> {t('sections.finance.close_day.features.warning.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.finance.close_day.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.close_day.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.close_day.js_functions')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono break-words whitespace-pre-wrap">{t('sections.finance.close_day.lock_api')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.finance.transactions.title')} icon={<TrendingUp />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.finance.transactions.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Filter className="text-blue-600 dark:text-blue-400" size={24} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.finance.transactions.features.filtering.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.finance.transactions.features.filtering.date')}</li>
                                <li>{t('sections.finance.transactions.features.filtering.type')}</li>
                                <li>{t('sections.finance.transactions.features.filtering.real_time')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <FileSpreadsheet className="text-green-600 dark:text-green-400" size={24} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.finance.transactions.features.export.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.finance.transactions.features.export.excel')}</li>
                                <li>{t('sections.finance.transactions.features.export.csv')}</li>
                                <li>{t('sections.finance.transactions.features.export.formatted')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.finance.transactions.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.transactions.api_list')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.transactions.api_export')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.transactions.js_functions')}</code>
                        <code className="block text-sm text-cyan-600 dark:text-cyan-400 font-mono break-words whitespace-pre-wrap">{t('sections.finance.transactions.pagination')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.finance.payment_management.title')} icon={<Wallet />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.finance.payment_management.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Wallet className="text-green-600 dark:text-green-400" size={24} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.finance.payment_management.features.types.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.finance.payment_management.features.types.booking')}</li>
                                <li>{t('sections.finance.payment_management.features.types.followup')}</li>
                                <li>{t('sections.finance.payment_management.features.types.consultation')}</li>
                                <li>{t('sections.finance.payment_management.features.types.procedure')}</li>
                                <li>{t('sections.finance.payment_management.features.types.other')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <DollarSign className="text-blue-600 dark:text-blue-400" size={24} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.finance.payment_management.features.methods.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.finance.payment_management.features.methods.cash')}</li>
                                <li>{t('sections.finance.payment_management.features.methods.card')}</li>
                                <li>{t('sections.finance.payment_management.features.methods.transfer')}</li>
                                <li>{t('sections.finance.payment_management.features.methods.wallet')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.finance.payment_management.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.payment_management.api_create')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.payment_management.api_update')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1 break-words whitespace-pre-wrap">{t('sections.finance.payment_management.api_delete')}</code>
                        <code className="block text-sm text-cyan-600 dark:text-cyan-400 font-mono break-words whitespace-pre-wrap">{t('sections.finance.payment_management.js_functions')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.finance.api_endpoints.title')} icon={<FileSpreadsheet />}>
                <div className="space-y-4">
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-3 font-bold tracking-wider">{t('sections.finance.api_endpoints.payments.title')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.finance.api_endpoints.payments.create')}</code>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.finance.api_endpoints.payments.get')}</code>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.finance.api_endpoints.payments.update')}</code>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.finance.api_endpoints.payments.delete')}</code>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-2">{t('sections.finance.api_endpoints.payments.description')}</p>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-3 font-bold tracking-wider">{t('sections.finance.api_endpoints.expenses.title')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.finance.api_endpoints.expenses.create')}</code>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.finance.api_endpoints.expenses.get')}</code>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.finance.api_endpoints.expenses.update')}</code>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.finance.api_endpoints.expenses.delete')}</code>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-2">{t('sections.finance.api_endpoints.expenses.description')}</p>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-3 font-bold tracking-wider">{t('sections.finance.api_endpoints.balance.title')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.finance.api_endpoints.balance.create')}</code>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-2">{t('sections.finance.api_endpoints.balance.description')}</p>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-3 font-bold tracking-wider">{t('sections.finance.api_endpoints.closure.title')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.finance.api_endpoints.closure.create')}</code>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.finance.api_endpoints.closure.lock')}</code>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-2">{t('sections.finance.api_endpoints.closure.description')}</p>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5 overflow-x-auto">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-3 font-bold tracking-wider">{t('sections.finance.api_endpoints.transactions.title')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.finance.api_endpoints.transactions.list')}</code>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-2 break-words whitespace-pre-wrap">{t('sections.finance.api_endpoints.transactions.export')}</code>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-2">{t('sections.finance.api_endpoints.transactions.description')}</p>
                    </div>
                </div>
            </Section>
        </div>
    );
}

