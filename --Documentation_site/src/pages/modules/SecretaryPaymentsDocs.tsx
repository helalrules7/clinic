import Hero from '../../components/ui/Hero';
import Section from '../../components/ui/Section';
import { Wallet, Plus, FileText, Eye, Calculator, CreditCard, Minus } from 'lucide-react';
import { useTranslation } from 'react-i18next';

export default function SecretaryPaymentsDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.secretary_payments.hero.title')}
                subtitle={t('sections.secretary_payments.hero.subtitle')}
            />

            <Section title={t('sections.secretary_payments.overview.title')} icon={<Wallet />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_payments/01-opt.png"
                            alt="Secretary Payments Page"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_payments.overview.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_payments.overview.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_payments.overview.features.daily_balance')}:</strong> {t('sections.secretary_payments.overview.features.daily_balance_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.overview.features.payment_types')}:</strong> {t('sections.secretary_payments.overview.features.payment_types_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.overview.features.transactions_log')}:</strong> {t('sections.secretary_payments.overview.features.transactions_log_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.overview.features.payments_table')}:</strong> {t('sections.secretary_payments.overview.features.payments_table_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.overview.features.search')}:</strong> {t('sections.secretary_payments.overview.features.search_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.overview.features.keyboard_shortcuts')}:</strong> {t('sections.secretary_payments.overview.features.keyboard_shortcuts_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_payments.overview.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_payments.overview.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.secretary_payments.overview.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.secretary_payments.overview.view_file')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_payments.daily_balance.title')} icon={<Calculator />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_payments.daily_balance.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_payments.daily_balance.cards_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_payments.daily_balance.cards.opening')}:</strong> {t('sections.secretary_payments.daily_balance.cards.opening_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.daily_balance.cards.total_received')}:</strong> {t('sections.secretary_payments.daily_balance.cards.total_received_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.daily_balance.cards.total_expenses')}:</strong> {t('sections.secretary_payments.daily_balance.cards.total_expenses_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.daily_balance.cards.current')}:</strong> {t('sections.secretary_payments.daily_balance.cards.current_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_payments.daily_balance.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_payments.daily_balance.controller_method')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono">{t('sections.secretary_payments.daily_balance.js_function')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_payments/01-opt.png"
                            alt="Daily Balance Cards"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_payments.payment_types.title')} icon={<CreditCard />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_payments/01-opt.png"
                            alt="Payment Types Summary"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_payments.payment_types.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_payments.payment_types.types_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_payments.payment_types.types.new_booking')}:</strong> {t('sections.secretary_payments.payment_types.types.new_booking_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.payment_types.types.followup')}:</strong> {t('sections.secretary_payments.payment_types.types.followup_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.payment_types.types.consultation')}:</strong> {t('sections.secretary_payments.payment_types.types.consultation_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.payment_types.types.procedure')}:</strong> {t('sections.secretary_payments.payment_types.types.procedure_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_payments.payment_types.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_payments.payment_types.controller_method')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono">{t('sections.secretary_payments.payment_types.js_function')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_payments.transactions_log.title')} icon={<FileText />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_payments.transactions_log.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_payments.transactions_log.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_payments.transactions_log.features.pagination')}:</strong> {t('sections.secretary_payments.transactions_log.features.pagination_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.transactions_log.features.date_filter')}:</strong> {t('sections.secretary_payments.transactions_log.features.date_filter_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.transactions_log.features.type_filter')}:</strong> {t('sections.secretary_payments.transactions_log.features.type_filter_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.transactions_log.features.export')}:</strong> {t('sections.secretary_payments.transactions_log.features.export_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_payments.transactions_log.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_payments.transactions_log.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.secretary_payments.transactions_log.js_function')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.secretary_payments.transactions_log.export_function')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_payments/02-opt.png"
                            alt="Transactions Log"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_payments.payments_table.title')} icon={<FileText />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_payments/02-opt.png"
                            alt="Payments Table"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_payments.payments_table.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_payments.payments_table.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_payments.payments_table.features.quick_search')}:</strong> {t('sections.secretary_payments.payments_table.features.quick_search_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.payments_table.features.type_filter')}:</strong> {t('sections.secretary_payments.payments_table.features.type_filter_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.payments_table.features.method_filter')}:</strong> {t('sections.secretary_payments.payments_table.features.method_filter_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.payments_table.features.actions')}:</strong> {t('sections.secretary_payments.payments_table.features.actions_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_payments.payments_table.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_payments.payments_table.js_functions')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono">{t('sections.secretary_payments.payments_table.controller_methods')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_payments.add_balance.title')} icon={<Plus />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_payments/03-opt.png"
                            alt="Add Daily Balance Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_payments.add_balance.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_payments.add_balance.form_fields_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_payments.add_balance.form_fields.amount')}:</strong> {t('sections.secretary_payments.add_balance.form_fields.amount_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.add_balance.form_fields.type')}:</strong> {t('sections.secretary_payments.add_balance.form_fields.type_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.add_balance.form_fields.description')}:</strong> {t('sections.secretary_payments.add_balance.form_fields.description_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.add_balance.form_fields.date')}:</strong> {t('sections.secretary_payments.add_balance.form_fields.date_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_payments.add_balance.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_payments.add_balance.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.secretary_payments.add_balance.js_function')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.secretary_payments.add_balance.keyboard_shortcut')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_payments.add_expense.title')} icon={<Minus />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_payments/04-opt.png"
                            alt="Add Expense Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_payments.add_expense.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_payments.add_expense.form_fields_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_payments.add_expense.form_fields.amount')}:</strong> {t('sections.secretary_payments.add_expense.form_fields.amount_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.add_expense.form_fields.name')}:</strong> {t('sections.secretary_payments.add_expense.form_fields.name_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.add_expense.form_fields.category')}:</strong> {t('sections.secretary_payments.add_expense.form_fields.category_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.add_expense.form_fields.quick_types')}:</strong> {t('sections.secretary_payments.add_expense.form_fields.quick_types_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.add_expense.form_fields.notes')}:</strong> {t('sections.secretary_payments.add_expense.form_fields.notes_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_payments.add_expense.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_payments.add_expense.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.secretary_payments.add_expense.js_function')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.secretary_payments.add_expense.keyboard_shortcut')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_payments.payment_details.title')} icon={<Eye />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_payments.payment_details.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_payments.payment_details.sections_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_payments.payment_details.sections.payment_info')}:</strong> {t('sections.secretary_payments.payment_details.sections.payment_info_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.payment_details.sections.patient_info')}:</strong> {t('sections.secretary_payments.payment_details.sections.patient_info_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.payment_details.sections.appointment_info')}:</strong> {t('sections.secretary_payments.payment_details.sections.appointment_info_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.payment_details.sections.related_payments')}:</strong> {t('sections.secretary_payments.payment_details.sections.related_payments_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_payments.payment_details.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_payments.payment_details.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.secretary_payments.payment_details.controller_method')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1">{t('sections.secretary_payments.payment_details.view_file')}</code>
                            <code className="block text-sm text-yellow-600 dark:text-yellow-400 font-mono">{t('sections.secretary_payments.payment_details.print_route')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_payments/02-opt.png"
                            alt="Payment Details Page"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_payments.expense_details.title')} icon={<Eye />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/sec_payments/05-opt.png"
                            alt="Expense Details Page"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.secretary_payments.expense_details.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.secretary_payments.expense_details.sections_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.secretary_payments.expense_details.sections.expense_info')}:</strong> {t('sections.secretary_payments.expense_details.sections.expense_info_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.expense_details.sections.creator_info')}:</strong> {t('sections.secretary_payments.expense_details.sections.creator_info_desc')}</li>
                            <li><strong>{t('sections.secretary_payments.expense_details.sections.related_expenses')}:</strong> {t('sections.secretary_payments.expense_details.sections.related_expenses_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.secretary_payments.expense_details.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.secretary_payments.expense_details.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.secretary_payments.expense_details.controller_method')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.secretary_payments.expense_details.view_file')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.secretary_payments.api_endpoints.title')} icon={<FileText />}>
                <div className="space-y-4">
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/secretary/payments</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_payments.api_endpoints.get_payments')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/secretary/payments/{'{id}'}</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_payments.api_endpoints.get_payment_details')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/secretary/payments/{'{id}'}/receipt</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_payments.api_endpoints.print_receipt')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/secretary/expenses/{'{id}'}</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_payments.api_endpoints.get_expense_details')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 font-mono">POST</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/api/daily-balance</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_payments.api_endpoints.create_balance')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 font-mono">POST</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/api/expenses</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_payments.api_endpoints.create_expense')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/api/financial-transactions</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_payments.api_endpoints.get_transactions')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/api/financial-transactions/export</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_payments.api_endpoints.export_transactions')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/api/dashboard-summary</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.secretary_payments.api_endpoints.get_dashboard_summary')}</p>
                    </div>
                </div>
            </Section>
        </div>
    );
}

