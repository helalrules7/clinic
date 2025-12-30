import Hero from '../../components/ui/Hero';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { Shield, Users, Database, Image, Bell, Plus, Edit, FileCode } from 'lucide-react';
import { useTranslation } from 'react-i18next';

export default function AdminModuleDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.admin_module.hero.title')}
                subtitle={t('sections.admin_module.hero.subtitle')}
                badge={t('sections.admin_module.hero.badge')}
            />

            <Section title={t('sections.admin_module.overview.title')} icon={<Shield />}>
                <p className="text-gray-700 dark:text-gray-300 mb-4">
                    {t('sections.admin_module.overview.description')}
                </p>
                <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.admin_module.overview.features_title')}</h3>
                <div className="grid md:grid-cols-2 gap-4">
                    <Card title={t('sections.admin_module.overview.features.users')} icon={Users}>
                        <p>{t('sections.admin_module.overview.features.users_desc')}</p>
                    </Card>
                    <Card title={t('sections.admin_module.overview.features.backup')} icon={Database}>
                        <p>{t('sections.admin_module.overview.features.backup_desc')}</p>
                    </Card>
                    <Card title={t('sections.admin_module.overview.features.media')} icon={Image}>
                        <p>{t('sections.admin_module.overview.features.media_desc')}</p>
                    </Card>
                    <Card title={t('sections.admin_module.overview.features.notifications')} icon={Bell}>
                        <p>{t('sections.admin_module.overview.features.notifications_desc')}</p>
                    </Card>
                </div>
            </Section>

            <Section title={t('sections.admin_module.users_management.title')} icon={<Users />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/admin/01-opt.png"
                            alt="Users Management Page"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.admin_module.users_management.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.admin_module.users_management.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.admin_module.users_management.features.search')}:</strong> {t('sections.admin_module.users_management.features.search_desc')}</li>
                            <li><strong>{t('sections.admin_module.users_management.features.filter')}:</strong> {t('sections.admin_module.users_management.features.filter_desc')}</li>
                            <li><strong>{t('sections.admin_module.users_management.features.table')}:</strong> {t('sections.admin_module.users_management.features.table_desc')}</li>
                            <li><strong>{t('sections.admin_module.users_management.features.pagination')}:</strong> {t('sections.admin_module.users_management.features.pagination_desc')}</li>
                            <li><strong>{t('sections.admin_module.users_management.features.actions')}:</strong> {t('sections.admin_module.users_management.features.actions_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.admin_module.users_management.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.admin_module.users_management.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.admin_module.users_management.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.admin_module.users_management.view_file')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.admin_module.add_user.title')} icon={<Plus />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/admin/00-opt.png"
                            alt="Add New User Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.admin_module.add_user.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.admin_module.add_user.form_fields_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.admin_module.add_user.form_fields.name')}:</strong> {t('sections.admin_module.add_user.form_fields.name_desc')}</li>
                            <li><strong>{t('sections.admin_module.add_user.form_fields.username')}:</strong> {t('sections.admin_module.add_user.form_fields.username_desc')}</li>
                            <li><strong>{t('sections.admin_module.add_user.form_fields.email')}:</strong> {t('sections.admin_module.add_user.form_fields.email_desc')}</li>
                            <li><strong>{t('sections.admin_module.add_user.form_fields.role')}:</strong> {t('sections.admin_module.add_user.form_fields.role_desc')}</li>
                            <li><strong>{t('sections.admin_module.add_user.form_fields.specialization')}:</strong> {t('sections.admin_module.add_user.form_fields.specialization_desc')}</li>
                            <li><strong>{t('sections.admin_module.add_user.form_fields.license')}:</strong> {t('sections.admin_module.add_user.form_fields.license_desc')}</li>
                            <li><strong>{t('sections.admin_module.add_user.form_fields.password')}:</strong> {t('sections.admin_module.add_user.form_fields.password_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.admin_module.add_user.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.admin_module.add_user.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.admin_module.add_user.controller_method')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.admin_module.add_user.validation')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.admin_module.edit_user.title')} icon={<Edit />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/admin/02-opt.png"
                            alt="Edit User Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.admin_module.edit_user.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.admin_module.edit_user.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.admin_module.edit_user.features.update_info')}:</strong> {t('sections.admin_module.edit_user.features.update_info_desc')}</li>
                            <li><strong>{t('sections.admin_module.edit_user.features.change_role')}:</strong> {t('sections.admin_module.edit_user.features.change_role_desc')}</li>
                            <li><strong>{t('sections.admin_module.edit_user.features.toggle_status')}:</strong> {t('sections.admin_module.edit_user.features.toggle_status_desc')}</li>
                            <li><strong>{t('sections.admin_module.edit_user.features.validation')}:</strong> {t('sections.admin_module.edit_user.features.validation_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.admin_module.edit_user.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.admin_module.edit_user.api_endpoint')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.admin_module.edit_user.controller_method')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.admin_module.edit_user.js_function')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.admin_module.backup_restore.title')} icon={<Database />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.admin_module.backup_restore.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.admin_module.backup_restore.backup_types_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.admin_module.backup_restore.backup_types.database')}:</strong> {t('sections.admin_module.backup_restore.backup_types.database_desc')}</li>
                            <li><strong>{t('sections.admin_module.backup_restore.backup_types.full')}:</strong> {t('sections.admin_module.backup_restore.backup_types.full_desc')}</li>
                            <li><strong>{t('sections.admin_module.backup_restore.backup_types.website')}:</strong> {t('sections.admin_module.backup_restore.backup_types.website_desc')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2 mt-4">{t('sections.admin_module.backup_restore.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.admin_module.backup_restore.features.statistics')}:</strong> {t('sections.admin_module.backup_restore.features.statistics_desc')}</li>
                            <li><strong>{t('sections.admin_module.backup_restore.features.backup_list')}:</strong> {t('sections.admin_module.backup_restore.features.backup_list_desc')}</li>
                            <li><strong>{t('sections.admin_module.backup_restore.features.restore')}:</strong> {t('sections.admin_module.backup_restore.features.restore_desc')}</li>
                            <li><strong>{t('sections.admin_module.backup_restore.features.download')}:</strong> {t('sections.admin_module.backup_restore.features.download_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.admin_module.backup_restore.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.admin_module.backup_restore.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.admin_module.backup_restore.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.admin_module.backup_restore.view_file')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/admin/03-opt.png"
                            alt="Database Backup & Restore Page"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.admin_module.media_management.title')} icon={<Image />}>
                <div className="space-y-6">
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/admin/04-opt.png"
                                alt="Media Management Page"
                                className="w-full h-auto hover:scale-105 transition-transform duration-500"
                            />
                        </div>
                        <div>
                            <p className="text-gray-700 dark:text-gray-300 mb-4">
                                {t('sections.admin_module.media_management.description')}
                            </p>
                            <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.admin_module.media_management.features_title')}</h3>
                            <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                                <li><strong>{t('sections.admin_module.media_management.features.statistics')}:</strong> {t('sections.admin_module.media_management.features.statistics_desc')}</li>
                                <li><strong>{t('sections.admin_module.media_management.features.view_modes')}:</strong> {t('sections.admin_module.media_management.features.view_modes_desc')}</li>
                                <li><strong>{t('sections.admin_module.media_management.features.sort_filter')}:</strong> {t('sections.admin_module.media_management.features.sort_filter_desc')}</li>
                                <li><strong>{t('sections.admin_module.media_management.features.bulk_actions')}:</strong> {t('sections.admin_module.media_management.features.bulk_actions_desc')}</li>
                                <li><strong>{t('sections.admin_module.media_management.features.context_menu')}:</strong> {t('sections.admin_module.media_management.features.context_menu_desc')}</li>
                            </ul>
                        </div>
                    </div>
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/admin/04-01-opt.png"
                                alt="Media Management Thumbnail View"
                                className="w-full h-auto hover:scale-105 transition-transform duration-500"
                            />
                        </div>
                        <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                            <img
                                src="/docs/opth/assets/images/admin/04-02-opt.png"
                                alt="Media Management Folder View"
                                className="w-full h-auto hover:scale-105 transition-transform duration-500"
                            />
                        </div>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.admin_module.media_management.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.admin_module.media_management.route')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.admin_module.media_management.controller')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1">{t('sections.admin_module.media_management.view_file')}</code>
                        <code className="block text-sm text-yellow-600 dark:text-yellow-400 font-mono">{t('sections.admin_module.media_management.js_functions')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.admin_module.notifications.title')} icon={<Bell />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/admin/05-opt.png"
                            alt="System Notifications Page"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.admin_module.notifications.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.admin_module.notifications.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.admin_module.notifications.features.notification_types')}:</strong> {t('sections.admin_module.notifications.features.notification_types_desc')}</li>
                            <li><strong>{t('sections.admin_module.notifications.features.recipients')}:</strong> {t('sections.admin_module.notifications.features.recipients_desc')}</li>
                            <li><strong>{t('sections.admin_module.notifications.features.preview')}:</strong> {t('sections.admin_module.notifications.features.preview_desc')}</li>
                            <li><strong>{t('sections.admin_module.notifications.features.user_selection')}:</strong> {t('sections.admin_module.notifications.features.user_selection_desc')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.admin_module.notifications.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.admin_module.notifications.route')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.admin_module.notifications.controller')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono mb-1">{t('sections.admin_module.notifications.view_file')}</code>
                            <code className="block text-sm text-yellow-600 dark:text-yellow-400 font-mono">{t('sections.admin_module.notifications.api_endpoint')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.admin_module.api_endpoints.title')} icon={<FileCode />}>
                <div className="space-y-4">
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/admin/users</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.admin_module.api_endpoints.get_users')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 font-mono">POST</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/admin/users</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.admin_module.api_endpoints.create_user')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 font-mono">POST</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/admin/users/update/{'{id}'}</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.admin_module.api_endpoints.update_user')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-red-500/10 dark:bg-red-500/20 text-red-600 dark:text-red-400 font-mono">POST</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/admin/users/delete/{'{id}'}</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.admin_module.api_endpoints.delete_user')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/admin/backup</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.admin_module.api_endpoints.get_backup_page')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 font-mono">POST</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/api/admin/backup/database</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.admin_module.api_endpoints.create_db_backup')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 font-mono">POST</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/api/admin/backup/full</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.admin_module.api_endpoints.create_full_backup')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/api/admin/backup/list</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.admin_module.api_endpoints.list_backups')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 font-mono">POST</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/api/admin/backup/restore</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.admin_module.api_endpoints.restore_backup')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/admin/media</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.admin_module.api_endpoints.get_media_page')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/api/admin/media/list</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.admin_module.api_endpoints.list_media')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-red-500/10 dark:bg-red-500/20 text-red-600 dark:text-red-400 font-mono">POST</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/api/admin/media/delete</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.admin_module.api_endpoints.delete_media')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 font-mono">GET</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/admin/notifications</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.admin_module.api_endpoints.get_notifications_page')}</p>
                    </div>
                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-1 rounded text-xs bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 font-mono">POST</span>
                            <code className="text-sm text-gray-700 dark:text-gray-300 font-mono">/api/notifications/system</code>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">{t('sections.admin_module.api_endpoints.send_notification')}</p>
                    </div>
                </div>
            </Section>
        </div>
    );
}

