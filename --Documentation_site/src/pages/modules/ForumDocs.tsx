import Hero from '../../components/ui/Hero';
import { useTranslation } from 'react-i18next';
import Section from '../../components/ui/Section';
import Card from '../../components/ui/Card';
import { MessageSquare, Search, Plus, Pin, Edit, ThumbsUp, Reply, CheckCircle, Tag, Paperclip, Image as ImageIcon, Database } from 'lucide-react';

export default function ForumDocs() {
    const { t } = useTranslation();

    return (
        <div className="space-y-8 animate-fade-in">
            <Hero
                title={t('sections.forum.title')}
                subtitle={t('sections.forum.subtitle')}
            />

            <Section title={t('sections.forum.overview.title')} icon={<MessageSquare />}>
                <p className="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                    {t('sections.forum.overview.content')}
                </p>
            </Section>

            <Section title={t('sections.forum.categories_tags_search.title')} icon={<Search />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.forum.categories_tags_search.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.forum.categories_tags_search.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.forum.categories_tags_search.features.stats.title')}:</strong> {t('sections.forum.categories_tags_search.features.stats.description')}</li>
                            <li><strong>{t('sections.forum.categories_tags_search.features.top_tags.title')}:</strong> {t('sections.forum.categories_tags_search.features.top_tags.description')}</li>
                            <li><strong>{t('sections.forum.categories_tags_search.features.search.title')}:</strong> {t('sections.forum.categories_tags_search.features.search.description')}</li>
                            <li><strong>{t('sections.forum.categories_tags_search.features.filter.title')}:</strong> {t('sections.forum.categories_tags_search.features.filter.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.forum.categories_tags_search.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.forum.categories_tags_search.api')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.forum.categories_tags_search.functions')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.forum.categories_tags_search.autocomplete')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/forum/01-opt.png"
                            alt="Categories, Tags, and Search"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.forum.new_discussion.title')} icon={<Plus />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/forum/02-opt.png"
                            alt="New Discussion Modal"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.forum.new_discussion.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.forum.new_discussion.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.forum.new_discussion.features.title_field.title')}:</strong> {t('sections.forum.new_discussion.features.title_field.description')}</li>
                            <li><strong>{t('sections.forum.new_discussion.features.category.title')}:</strong> {t('sections.forum.new_discussion.features.category.description')}</li>
                            <li><strong>{t('sections.forum.new_discussion.features.meta_tags.title')}:</strong> {t('sections.forum.new_discussion.features.meta_tags.description')}</li>
                            <li><strong>{t('sections.forum.new_discussion.features.content.title')}:</strong> {t('sections.forum.new_discussion.features.content.description')}</li>
                            <li><strong>{t('sections.forum.new_discussion.features.attachments.title')}:</strong> {t('sections.forum.new_discussion.features.attachments.description')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2 mt-4">{t('sections.forum.new_discussion.meta_shortcuts_title')}</h3>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <div className="flex items-center gap-2 mb-2">
                                    <Tag className="text-blue-600 dark:text-blue-400" size={20} />
                                    <kbd className="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs">@</kbd>
                                </div>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    {t('sections.forum.new_discussion.meta_shortcuts.patient')}
                                </p>
                            </Card>
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <div className="flex items-center gap-2 mb-2">
                                    <Tag className="text-green-600 dark:text-green-400" size={20} />
                                    <kbd className="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs">#</kbd>
                                </div>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    {t('sections.forum.new_discussion.meta_shortcuts.appointment')}
                                </p>
                            </Card>
                            <Card className="bg-gray-100 dark:bg-slate-800/30">
                                <div className="flex items-center gap-2 mb-2">
                                    <Tag className="text-purple-600 dark:text-purple-400" size={20} />
                                    <kbd className="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs">$</kbd>
                                </div>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    {t('sections.forum.new_discussion.meta_shortcuts.drug')}
                                </p>
                            </Card>
                        </div>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.forum.new_discussion.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.forum.new_discussion.api')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.forum.new_discussion.function')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.forum.new_discussion.upload')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.forum.topic_page.title')} icon={<MessageSquare />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.forum.topic_page.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.forum.topic_page.controls_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.forum.topic_page.controls.edit.title')}:</strong> {t('sections.forum.topic_page.controls.edit.description')}</li>
                            <li><strong>{t('sections.forum.topic_page.controls.pin.title')}:</strong> {t('sections.forum.topic_page.controls.pin.description')}</li>
                            <li><strong>{t('sections.forum.topic_page.controls.delete.title')}:</strong> {t('sections.forum.topic_page.controls.delete.description')}</li>
                        </ul>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2 mt-4">{t('sections.forum.topic_page.replies_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-4">
                            <li><strong>{t('sections.forum.topic_page.replies.hierarchy.title')}:</strong> {t('sections.forum.topic_page.replies.hierarchy.description')}</li>
                            <li><strong>{t('sections.forum.topic_page.replies.images.title')}:</strong> {t('sections.forum.topic_page.replies.images.description')}</li>
                            <li><strong>{t('sections.forum.topic_page.replies.attachments.title')}:</strong> {t('sections.forum.topic_page.replies.attachments.description')}</li>
                            <li><strong>{t('sections.forum.topic_page.replies.likes.title')}:</strong> {t('sections.forum.topic_page.replies.likes.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.forum.topic_page.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.forum.topic_page.functions')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.forum.topic_page.api')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.forum.topic_page.polling')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/forum/03-opt.png"
                            alt="Topic Page"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.forum.pinned_topics.title')} icon={<Pin />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/forum/04-opt.png"
                            alt="Pinned Topics"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.forum.pinned_topics.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.forum.pinned_topics.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.forum.pinned_topics.features.priority.title')}:</strong> {t('sections.forum.pinned_topics.features.priority.description')}</li>
                            <li><strong>{t('sections.forum.pinned_topics.features.section.title')}:</strong> {t('sections.forum.pinned_topics.features.section.description')}</li>
                            <li><strong>{t('sections.forum.pinned_topics.features.toggle.title')}:</strong> {t('sections.forum.pinned_topics.features.toggle.description')}</li>
                            <li><strong>{t('sections.forum.pinned_topics.features.styling.title')}:</strong> {t('sections.forum.pinned_topics.features.styling.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.forum.pinned_topics.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.forum.pinned_topics.api')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.forum.pinned_topics.function')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.forum.pinned_topics.render')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.forum.post_controls.title')} icon={<ThumbsUp />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.forum.post_controls.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.forum.post_controls.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.forum.post_controls.features.views.title')}:</strong> {t('sections.forum.post_controls.features.views.description')}</li>
                            <li><strong>{t('sections.forum.post_controls.features.replies.title')}:</strong> {t('sections.forum.post_controls.features.replies.description')}</li>
                            <li><strong>{t('sections.forum.post_controls.features.likes.title')}:</strong> {t('sections.forum.post_controls.features.likes.description')}</li>
                            <li><strong>{t('sections.forum.post_controls.features.dislikes.title')}:</strong> {t('sections.forum.post_controls.features.dislikes.description')}</li>
                            <li><strong>{t('sections.forum.post_controls.features.reply_btn.title')}:</strong> {t('sections.forum.post_controls.features.reply_btn.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.forum.post_controls.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.forum.post_controls.functions')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.forum.post_controls.api')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.forum.post_controls.state')}</code>
                        </div>
                    </div>
                    <div className="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/forum/05-opt.png"
                            alt="Post Controls"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                </div>
            </Section>

            <Section title={t('sections.forum.resolved_topics.title')} icon={<CheckCircle />}>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="order-2 lg:order-1 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-lg bg-gray-50 dark:bg-black/40">
                        <img
                            src="/docs/opth/assets/images/doctors_pages/forum/06-opt.png"
                            alt="Resolved Topics"
                            className="w-full h-auto hover:scale-105 transition-transform duration-500"
                        />
                    </div>
                    <div className="order-1 lg:order-2">
                        <p className="text-gray-700 dark:text-gray-300 mb-4">
                            {t('sections.forum.resolved_topics.description')}
                        </p>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">{t('sections.forum.resolved_topics.features_title')}</h3>
                        <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2 mb-6">
                            <li><strong>{t('sections.forum.resolved_topics.features.mark.title')}:</strong> {t('sections.forum.resolved_topics.features.mark.description')}</li>
                            <li><strong>{t('sections.forum.resolved_topics.features.styling.title')}:</strong> {t('sections.forum.resolved_topics.features.styling.description')}</li>
                            <li><strong>{t('sections.forum.resolved_topics.features.filter.title')}:</strong> {t('sections.forum.resolved_topics.features.filter.description')}</li>
                            <li><strong>{t('sections.forum.resolved_topics.features.toggle.title')}:</strong> {t('sections.forum.resolved_topics.features.toggle.description')}</li>
                        </ul>
                        <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.forum.resolved_topics.technical')}</h4>
                            <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.forum.resolved_topics.api')}</code>
                            <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.forum.resolved_topics.function')}</code>
                            <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.forum.resolved_topics.render')}</code>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.forum.rich_text_editor.title')} icon={<Edit />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.forum.rich_text_editor.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Edit className="text-blue-600 dark:text-blue-400" size={24} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">{t('sections.forum.rich_text_editor.formatting.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.forum.rich_text_editor.formatting.bold')}</li>
                                <li>{t('sections.forum.rich_text_editor.formatting.italic')}</li>
                                <li>{t('sections.forum.rich_text_editor.formatting.underline')}</li>
                                <li>{t('sections.forum.rich_text_editor.formatting.alignment')}</li>
                                <li>{t('sections.forum.rich_text_editor.formatting.lists')}</li>
                                <li>{t('sections.forum.rich_text_editor.formatting.links')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <ImageIcon className="text-green-600 dark:text-green-400" size={24} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">{t('sections.forum.rich_text_editor.media.title')}</h4>
                            </div>
                            <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.forum.rich_text_editor.media.images')}</li>
                                <li>{t('sections.forum.rich_text_editor.media.attachments')}</li>
                                <li>{t('sections.forum.rich_text_editor.media.preview')}</li>
                                <li>{t('sections.forum.rich_text_editor.media.progress')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.forum.rich_text_editor.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.forum.rich_text_editor.commands')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.forum.rich_text_editor.contenteditable')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.forum.rich_text_editor.processing')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.forum.autocomplete.title')} icon={<Tag />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.forum.autocomplete.description')}
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Tag className="text-blue-600 dark:text-blue-400" size={20} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">@ Patient</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.forum.autocomplete.patient.description')}
                            </p>
                            <ul className="list-disc list-inside space-y-1 text-xs text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.forum.autocomplete.patient.min_length')}</li>
                                <li>{t('sections.forum.autocomplete.patient.display')}</li>
                                <li>{t('sections.forum.autocomplete.patient.clickable')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Tag className="text-green-600 dark:text-green-400" size={20} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400"># Appointment</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.forum.autocomplete.appointment.description')}
                            </p>
                            <ul className="list-disc list-inside space-y-1 text-xs text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.forum.autocomplete.appointment.min_length')}</li>
                                <li>{t('sections.forum.autocomplete.appointment.display')}</li>
                                <li>{t('sections.forum.autocomplete.appointment.clickable')}</li>
                            </ul>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-3">
                                <Tag className="text-purple-600 dark:text-purple-400" size={20} />
                                <h4 className="font-semibold text-purple-600 dark:text-purple-400">$ Drug</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.forum.autocomplete.drug.description')}
                            </p>
                            <ul className="list-disc list-inside space-y-1 text-xs text-gray-600 dark:text-gray-400 ml-2">
                                <li>{t('sections.forum.autocomplete.drug.min_length')}</li>
                                <li>{t('sections.forum.autocomplete.drug.display')}</li>
                                <li>{t('sections.forum.autocomplete.drug.popover')}</li>
                            </ul>
                        </Card>
                    </div>

                    <div className="bg-gray-100 dark:bg-slate-900/50 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                        <h4 className="text-sm font-uppercase text-gray-500 mb-2 font-bold tracking-wider">{t('sections.forum.autocomplete.technical')}</h4>
                        <code className="block text-sm text-green-600 dark:text-green-400 font-mono mb-1">{t('sections.forum.autocomplete.functions')}</code>
                        <code className="block text-sm text-blue-600 dark:text-blue-400 font-mono mb-1">{t('sections.forum.autocomplete.keyboard')}</code>
                        <code className="block text-sm text-purple-600 dark:text-purple-400 font-mono">{t('sections.forum.autocomplete.positioning')}</code>
                    </div>
                </div>
            </Section>

            <Section title={t('sections.forum.api_endpoints.title')} icon={<Database />}>
                <div className="space-y-6">
                    <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {t('sections.forum.api_endpoints.description')}
                    </p>
                    
                    <div className="space-y-4">
                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-2">
                                <Database className="text-green-600 dark:text-green-400" size={20} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">GET /api/forum/topics</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.forum.api_endpoints.get_topics.description')}
                            </p>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded">
                                {t('sections.forum.api_endpoints.get_topics.response')}
                            </code>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-2">
                                <Plus className="text-blue-600 dark:text-blue-400" size={20} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">POST /api/forum/topics</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.forum.api_endpoints.create_topic.description')}
                            </p>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded mb-2">
                                {t('sections.forum.api_endpoints.create_topic.body')}
                            </code>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded">
                                {t('sections.forum.api_endpoints.create_topic.response')}
                            </code>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-2">
                                <Reply className="text-yellow-600 dark:text-yellow-400" size={20} />
                                <h4 className="font-semibold text-yellow-600 dark:text-yellow-400">POST /api/forum/posts</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.forum.api_endpoints.create_post.description')}
                            </p>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded mb-2">
                                {t('sections.forum.api_endpoints.create_post.body')}
                            </code>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded">
                                {t('sections.forum.api_endpoints.create_post.response')}
                            </code>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-2">
                                <ThumbsUp className="text-green-600 dark:text-green-400" size={20} />
                                <h4 className="font-semibold text-green-600 dark:text-green-400">POST /api/forum/posts/{'{id}'}/like</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.forum.api_endpoints.like_post.description')}
                            </p>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded">
                                {t('sections.forum.api_endpoints.like_post.response')}
                            </code>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-2">
                                <Pin className="text-purple-600 dark:text-purple-400" size={20} />
                                <h4 className="font-semibold text-purple-600 dark:text-purple-400">POST /api/forum/topics/{'{id}'}/toggle-pin</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.forum.api_endpoints.toggle_pin.description')}
                            </p>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded">
                                {t('sections.forum.api_endpoints.toggle_pin.response')}
                            </code>
                        </Card>

                        <Card className="bg-gray-100 dark:bg-slate-800/30">
                            <div className="flex items-center gap-2 mb-2">
                                <Paperclip className="text-blue-600 dark:text-blue-400" size={20} />
                                <h4 className="font-semibold text-blue-600 dark:text-blue-400">POST /api/forum/attachments/upload</h4>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {t('sections.forum.api_endpoints.upload_attachment.description')}
                            </p>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded mb-2">
                                {t('sections.forum.api_endpoints.upload_attachment.body')}
                            </code>
                            <code className="block text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-200 dark:bg-slate-700 p-2 rounded">
                                {t('sections.forum.api_endpoints.upload_attachment.response')}
                            </code>
                        </Card>
                    </div>
                </div>
            </Section>
        </div>
    );
}

