import Hero from '../components/ui/Hero';
import Section from '../components/ui/Section';
import { useTranslation } from 'react-i18next';

export default function ApiDocs() {
    const { t } = useTranslation();

    const Endpoint = ({ method, url, desc }: { method: string, url: string, desc: string }) => (
        <div className="flex flex-col md:flex-row gap-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-dark-800">
            <div className="flex-none w-24">
                <span className={`px-2 py-1 rounded text-xs font-bold uppercase ${method === 'GET' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' :
                    method === 'POST' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' :
                        method === 'PUT' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' :
                            method === 'DELETE' ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' :
                                'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300'
                    }`}>
                    {method}
                </span>
            </div>
            <div className="flex-1 font-mono text-sm text-gray-700 dark:text-gray-300">
                {url}
            </div>
            <div className="flex-1 text-sm text-gray-600 dark:text-gray-400">
                {desc}
            </div>
        </div>
    );

    return (
        <div className="animate-fade-in">
            <Hero
                title={t('sections.api.hero.title')}
                subtitle={t('sections.api.hero.subtitle')}
                badge={t('sections.api.hero.badge')}
            />

            <Section title={t('sections.api.auth.title')} id="auth" className="mb-16">
                <p className="text-gray-600 dark:text-gray-300 mb-4">
                    {t('sections.api.auth.content')}
                </p>
                <div className="bg-gray-900 p-4 rounded-lg overflow-x-auto">
                    <code className="text-sm text-green-400">
                        {t('sections.api.auth.header')}
                    </code>
                </div>
                <div className="space-y-3 mt-4">
                    <Endpoint method="GET" url="/api/auth/session-time" desc={t('sections.api.auth.endpoints.session_time')} />
                </div>
            </Section>

            <Section title={t('sections.api.patients.title')} id="patients" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/patients" desc={t('sections.api.patients.endpoints.list')} />
                    <Endpoint method="GET" url="/api/patients/search?q={query}" desc={t('sections.api.patients.endpoints.search')} />
                    <Endpoint method="GET" url="/api/patients/{id}" desc={t('sections.api.patients.endpoints.get')} />
                    <Endpoint method="POST" url="/api/patients" desc={t('sections.api.patients.endpoints.create')} />
                    <Endpoint method="PUT" url="/api/patients/{id}/emergency-contact" desc={t('sections.api.patients.endpoints.update_contact')} />
                    <Endpoint method="DELETE" url="/api/patients/{id}" desc={t('sections.api.patients.endpoints.delete')} />
                    <Endpoint method="GET" url="/api/patients/{id}/timeline" desc={t('sections.api.patients.endpoints.timeline')} />
                    <Endpoint method="GET" url="/api/patients/{id}/files" desc={t('sections.api.patients.endpoints.files')} />
                    <Endpoint method="GET" url="/api/patients/{id}/appointments" desc={t('sections.api.patients.endpoints.appointments')} />
                    <Endpoint method="GET" url="/api/patients/{id}/appointments/history" desc={t('sections.api.patients.endpoints.appointments_history')} />
                    <Endpoint method="GET" url="/api/patients/{id}/appointments/check-active" desc={t('sections.api.patients.endpoints.check_active')} />
                    <Endpoint method="GET" url="/api/patients/{id}/export" desc={t('sections.api.patients.endpoints.export')} />
                    <Endpoint method="HEAD" url="/api/patients/{id}/export" desc={t('sections.api.patients.endpoints.check_export')} />
                </div>
            </Section>

            <Section title={t('sections.api.appointments.title')} id="appointments" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/calendar" desc={t('sections.api.appointments.endpoints.calendar')} />
                    <Endpoint method="GET" url="/api/appointments/{id}" desc={t('sections.api.appointments.endpoints.get')} />
                    <Endpoint method="GET" url="/api/appointments/search?q={query}" desc={t('sections.api.appointments.endpoints.search')} />
                    <Endpoint method="POST" url="/api/appointments" desc={t('sections.api.appointments.endpoints.create')} />
                    <Endpoint method="PUT" url="/api/appointments/{id}" desc={t('sections.api.appointments.endpoints.update')} />
                    <Endpoint method="DELETE" url="/api/appointments/{id}" desc={t('sections.api.appointments.endpoints.delete')} />
                    <Endpoint method="POST" url="/api/appointments/{id}/reschedule" desc={t('sections.api.appointments.endpoints.reschedule')} />
                    <Endpoint method="GET" url="/api/upcoming-appointments" desc={t('sections.api.appointments.endpoints.upcoming')} />
                    <Endpoint method="GET" url="/api/missed-appointments" desc={t('sections.api.appointments.endpoints.missed')} />
                </div>
            </Section>

            <Section title={t('sections.api.forum.title')} id="forum" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/forum/topics" desc={t('sections.api.forum.endpoints.topics')} />
                    <Endpoint method="GET" url="/api/forum/topics/{id}" desc={t('sections.api.forum.endpoints.topic')} />
                    <Endpoint method="POST" url="/api/forum/topics" desc={t('sections.api.forum.endpoints.create_topic')} />
                    <Endpoint method="PUT" url="/api/forum/topics/{id}" desc={t('sections.api.forum.endpoints.update_topic')} />
                    <Endpoint method="DELETE" url="/api/forum/topics/{id}" desc={t('sections.api.forum.endpoints.delete_topic')} />
                    <Endpoint method="GET" url="/api/forum/topics/patient/{patientId}" desc={t('sections.api.forum.endpoints.patient_topics')} />
                    <Endpoint method="GET" url="/api/forum/topics/appointment/{appointmentId}" desc={t('sections.api.forum.endpoints.appointment_topics')} />
                    <Endpoint method="GET" url="/api/forum/posts/topic/{topicId}" desc={t('sections.api.forum.endpoints.posts')} />
                    <Endpoint method="GET" url="/api/forum/posts/{id}" desc={t('sections.api.forum.endpoints.get_post')} />
                    <Endpoint method="POST" url="/api/forum/posts" desc={t('sections.api.forum.endpoints.create_post')} />
                    <Endpoint method="PUT" url="/api/forum/posts/{id}" desc={t('sections.api.forum.endpoints.update_post')} />
                    <Endpoint method="DELETE" url="/api/forum/posts/{id}" desc={t('sections.api.forum.endpoints.delete_post')} />
                    <Endpoint method="POST" url="/api/forum/posts/{id}/like" desc={t('sections.api.forum.endpoints.like')} />
                    <Endpoint method="POST" url="/api/forum/posts/{id}/dislike" desc={t('sections.api.forum.endpoints.dislike')} />
                    <Endpoint method="DELETE" url="/api/forum/posts/{id}/like" desc={t('sections.api.forum.endpoints.remove_like')} />
                    <Endpoint method="POST" url="/api/forum/topics/{id}/like" desc={t('sections.api.forum.endpoints.like_topic')} />
                    <Endpoint method="POST" url="/api/forum/topics/{id}/dislike" desc={t('sections.api.forum.endpoints.dislike_topic')} />
                    <Endpoint method="POST" url="/api/forum/posts/{id}/images" desc={t('sections.api.forum.endpoints.upload_image')} />
                    <Endpoint method="DELETE" url="/api/forum/images/{id}" desc={t('sections.api.forum.endpoints.delete_image')} />
                    <Endpoint method="POST" url="/api/forum/topics/{id}/tags" desc={t('sections.api.forum.endpoints.add_tags')} />
                    <Endpoint method="DELETE" url="/api/forum/topics/{id}/tags/{tagId}" desc={t('sections.api.forum.endpoints.remove_tag')} />
                    <Endpoint method="POST" url="/api/forum/attachments/upload" desc={t('sections.api.forum.endpoints.upload_attachment')} />
                    <Endpoint method="GET" url="/api/forum/attachments/view/{id}" desc={t('sections.api.forum.endpoints.view_attachment')} />
                    <Endpoint method="DELETE" url="/api/forum/attachments/{id}" desc={t('sections.api.forum.endpoints.delete_attachment')} />
                    <Endpoint method="GET" url="/api/forum/stats/categories" desc={t('sections.api.forum.endpoints.category_stats')} />
                    <Endpoint method="GET" url="/api/forum/stats/top-meta" desc={t('sections.api.forum.endpoints.top_meta')} />
                    <Endpoint method="POST" url="/api/forum/topics/{id}/toggle-resolved" desc={t('sections.api.forum.endpoints.toggle_resolved')} />
                    <Endpoint method="POST" url="/api/forum/topics/{id}/toggle-pin" desc={t('sections.api.forum.endpoints.toggle_pin')} />
                </div>
            </Section>

            <Section title={t('sections.api.notifications.title')} id="notifications" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/notifications" desc={t('sections.api.notifications.endpoints.list')} />
                    <Endpoint method="GET" url="/api/notifications/unread-count" desc={t('sections.api.notifications.endpoints.unread')} />
                    <Endpoint method="PUT" url="/api/notifications/{id}/read" desc={t('sections.api.notifications.endpoints.mark_read')} />
                    <Endpoint method="PUT" url="/api/notifications/read-all" desc={t('sections.api.notifications.endpoints.mark_all_read')} />
                    <Endpoint method="DELETE" url="/api/notifications/{id}" desc={t('sections.api.notifications.endpoints.delete')} />
                    <Endpoint method="POST" url="/api/notifications/system" desc={t('sections.api.notifications.endpoints.create_system')} />
                </div>
            </Section>

            <Section title={t('sections.api.search.title')} id="search" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/search/comprehensive?q={query}" desc={t('sections.api.search.endpoints.comprehensive')} />
                    <Endpoint method="GET" url="/api/searchDrugs?q={query}" desc={t('sections.api.search.endpoints.drugs')} />
                    <Endpoint method="GET" url="/api/searchDrugsAutocomplete?q={query}" desc={t('sections.api.search.endpoints.drugs_autocomplete')} />
                </div>
            </Section>

            <Section title={t('sections.api.dashboard.title')} id="dashboard" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/dashboard-summary" desc={t('sections.api.dashboard.endpoints.summary')} />
                    <Endpoint method="GET" url="/api/dashboard-charts" desc={t('sections.api.dashboard.endpoints.charts')} />
                    <Endpoint method="GET" url="/api/recent-activity" desc={t('sections.api.dashboard.endpoints.activity')} />
                    <Endpoint method="GET" url="/api/secretary/dashboard" desc={t('sections.api.dashboard.endpoints.secretary')} />
                </div>
            </Section>

            <Section title={t('sections.api.attachments.title')} id="attachments" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="POST" url="/api/attachments/upload" desc={t('sections.api.attachments.endpoints.upload')} />
                    <Endpoint method="GET" url="/api/attachments/view/{id}" desc={t('sections.api.attachments.endpoints.view')} />
                    <Endpoint method="GET" url="/api/attachments/download/{id}" desc={t('sections.api.attachments.endpoints.download')} />
                    <Endpoint method="DELETE" url="/api/attachments/{id}" desc={t('sections.api.attachments.endpoints.delete')} />
                </div>
            </Section>

            <Section title={t('sections.api.doctor.title')} id="doctor" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/doctor/settings" desc={t('sections.api.doctor.endpoints.get_settings')} />
                    <Endpoint method="PUT" url="/api/doctor/settings" desc={t('sections.api.doctor.endpoints.update_settings')} />
                    <Endpoint method="GET" url="/api/organizer/month" desc={t('sections.api.doctor.endpoints.organizer')} />
                    <Endpoint method="GET" url="/api/ophthalmology-news" desc={t('sections.api.doctor.endpoints.news')} />
                </div>
            </Section>

            <Section title={t('sections.api.alerts.title')} id="alerts" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/alerts" desc={t('sections.api.alerts.endpoints.list')} />
                    <Endpoint method="GET" url="/api/alerts/today" desc={t('sections.api.alerts.endpoints.today')} />
                    <Endpoint method="GET" url="/api/alerts/active" desc={t('sections.api.alerts.endpoints.active')} />
                    <Endpoint method="GET" url="/api/alerts/{id}" desc={t('sections.api.alerts.endpoints.get')} />
                    <Endpoint method="GET" url="/api/alerts/patient/{patientId}" desc={t('sections.api.alerts.endpoints.patient')} />
                    <Endpoint method="POST" url="/api/alerts" desc={t('sections.api.alerts.endpoints.create')} />
                    <Endpoint method="PUT" url="/api/alerts/{id}" desc={t('sections.api.alerts.endpoints.update')} />
                    <Endpoint method="DELETE" url="/api/alerts/{id}" desc={t('sections.api.alerts.endpoints.delete')} />
                    <Endpoint method="POST" url="/api/alerts/dismiss" desc={t('sections.api.alerts.endpoints.dismiss')} />
                    <Endpoint method="POST" url="/api/alerts/{id}/toggle-status" desc={t('sections.api.alerts.endpoints.toggle_status')} />
                    <Endpoint method="POST" url="/api/alerts/disable-all" desc={t('sections.api.alerts.endpoints.disable_all')} />
                    <Endpoint method="DELETE" url="/api/alerts/delete-all" desc={t('sections.api.alerts.endpoints.delete_all')} />
                </div>
            </Section>

            <Section title={t('sections.api.notes.title')} id="notes" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/notes" desc={t('sections.api.notes.endpoints.list')} />
                    <Endpoint method="GET" url="/api/notes/{id}" desc={t('sections.api.notes.endpoints.get')} />
                    <Endpoint method="POST" url="/api/notes" desc={t('sections.api.notes.endpoints.create')} />
                    <Endpoint method="PUT" url="/api/notes/{id}" desc={t('sections.api.notes.endpoints.update')} />
                    <Endpoint method="DELETE" url="/api/notes/{id}" desc={t('sections.api.notes.endpoints.delete')} />
                    <Endpoint method="DELETE" url="/api/notes/delete-all" desc={t('sections.api.notes.endpoints.delete_all')} />
                </div>
            </Section>

            <Section title={t('sections.api.prescriptions.title')} id="prescriptions" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="POST" url="/api/prescriptions/meds" desc={t('sections.api.prescriptions.endpoints.create_medication')} />
                    <Endpoint method="PUT" url="/api/prescriptions/meds/{id}" desc={t('sections.api.prescriptions.endpoints.update_medication')} />
                    <Endpoint method="DELETE" url="/api/prescriptions/meds/{id}" desc={t('sections.api.prescriptions.endpoints.delete_medication')} />
                    <Endpoint method="POST" url="/api/prescriptions/glasses" desc={t('sections.api.prescriptions.endpoints.create_glasses')} />
                    <Endpoint method="PUT" url="/api/prescriptions/glasses/{id}" desc={t('sections.api.prescriptions.endpoints.update_glasses')} />
                    <Endpoint method="DELETE" url="/api/prescriptions/glasses/{id}" desc={t('sections.api.prescriptions.endpoints.delete_glasses')} />
                    <Endpoint method="GET" url="/api/prescriptions/glasses/{id}" desc={t('sections.api.prescriptions.endpoints.get_glasses')} />
                    <Endpoint method="GET" url="/api/glasses/prescriptions" desc={t('sections.api.prescriptions.endpoints.list_glasses')} />
                    <Endpoint method="GET" url="/api/medications/prescriptions" desc={t('sections.api.prescriptions.endpoints.list_medications')} />
                    <Endpoint method="GET" url="/api/prescriptions/suggestions?diagnosis={diagnosis}&complaint={complaint}" desc={t('sections.api.prescriptions.endpoints.suggestions')} />
                </div>
            </Section>

            <Section title={t('sections.api.lab_tests.title')} id="lab-tests" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/lab-tests/appointment/{id}" desc={t('sections.api.lab_tests.endpoints.get')} />
                    <Endpoint method="POST" url="/api/lab-tests" desc={t('sections.api.lab_tests.endpoints.create')} />
                    <Endpoint method="PUT" url="/api/lab-tests/{id}" desc={t('sections.api.lab_tests.endpoints.update')} />
                    <Endpoint method="DELETE" url="/api/lab-tests/{id}" desc={t('sections.api.lab_tests.endpoints.delete')} />
                </div>
            </Section>

            <Section title={t('sections.api.consultations.title')} id="consultations" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="POST" url="/api/consultations" desc={t('sections.api.consultations.endpoints.create')} />
                    <Endpoint method="DELETE" url="/api/consultation-notes/{id}" desc={t('sections.api.consultations.endpoints.delete_note')} />
                    <Endpoint method="GET" url="/api/consultation/common-complaints" desc={t('sections.api.consultations.endpoints.common_complaints')} />
                    <Endpoint method="GET" url="/api/consultation/suggestions?field={field}&query={query}" desc={t('sections.api.consultations.endpoints.suggestions')} />
                </div>
            </Section>

            <Section title={t('sections.api.ai.title')} id="ai" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="POST" url="/api/ai/chat" desc={t('sections.api.ai.endpoints.chat')} />
                    <Endpoint method="GET" url="/api/ai/chat/history" desc={t('sections.api.ai.endpoints.chat_history')} />
                    <Endpoint method="DELETE" url="/api/ai/chat/history" desc={t('sections.api.ai.endpoints.clear_history')} />
                </div>
            </Section>

            <Section title={t('sections.api.secretary.title')} id="secretary" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/secretary/dashboard" desc={t('sections.api.secretary.endpoints.dashboard')} />
                    <Endpoint method="GET" url="/api/secretary/patients" desc={t('sections.api.secretary.endpoints.patients')} />
                    <Endpoint method="GET" url="/secretary/bookings/calendar" desc={t('sections.api.secretary.endpoints.bookings_calendar')} />
                    <Endpoint method="POST" url="/secretary/bookings" desc={t('sections.api.secretary.endpoints.create_booking')} />
                    <Endpoint method="POST" url="/secretary/bookings/{id}/update" desc={t('sections.api.secretary.endpoints.update_booking')} />
                    <Endpoint method="DELETE" url="/secretary/bookings/{id}" desc={t('sections.api.secretary.endpoints.delete_booking')} />
                    <Endpoint method="POST" url="/secretary/bookings/{id}/confirm" desc={t('sections.api.secretary.endpoints.confirm_attendance')} />
                    <Endpoint method="GET" url="/secretary/bookings/{id}/details" desc={t('sections.api.secretary.endpoints.booking_details')} />
                </div>
            </Section>

            <Section title={t('sections.api.admin.title')} id="admin" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/admin/users" desc={t('sections.api.admin.endpoints.list_users')} />
                    <Endpoint method="POST" url="/admin/users" desc={t('sections.api.admin.endpoints.create_user')} />
                    <Endpoint method="PUT" url="/admin/users/{id}" desc={t('sections.api.admin.endpoints.update_user')} />
                    <Endpoint method="DELETE" url="/admin/users/{id}" desc={t('sections.api.admin.endpoints.delete_user')} />
                    <Endpoint method="GET" url="/admin/view-as" desc={t('sections.api.admin.endpoints.view_as')} />
                    <Endpoint method="GET" url="/admin/stop-view-as" desc={t('sections.api.admin.endpoints.stop_view_as')} />
                    <Endpoint method="POST" url="/api/admin/backup/database" desc={t('sections.api.admin.endpoints.backup_database')} />
                    <Endpoint method="POST" url="/api/admin/backup/full" desc={t('sections.api.admin.endpoints.backup_full')} />
                    <Endpoint method="POST" url="/api/admin/backup/website" desc={t('sections.api.admin.endpoints.backup_website')} />
                    <Endpoint method="GET" url="/api/admin/backup/list" desc={t('sections.api.admin.endpoints.backup_list')} />
                    <Endpoint method="POST" url="/api/admin/backup/restore" desc={t('sections.api.admin.endpoints.backup_restore')} />
                    <Endpoint method="GET" url="/api/admin/media/list" desc={t('sections.api.admin.endpoints.media_list')} />
                    <Endpoint method="POST" url="/api/admin/media/delete" desc={t('sections.api.admin.endpoints.media_delete')} />
                    <Endpoint method="POST" url="/api/admin/media/backup" desc={t('sections.api.admin.endpoints.media_backup')} />
                </div>
            </Section>

            <Section title={t('sections.api.financial.title')} id="financial" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="POST" url="/api/payments" desc={t('sections.api.financial.endpoints.create_payment')} />
                    <Endpoint method="GET" url="/api/payments/{id}" desc={t('sections.api.financial.endpoints.get_payment')} />
                    <Endpoint method="PUT" url="/api/payments/{id}" desc={t('sections.api.financial.endpoints.update_payment')} />
                    <Endpoint method="DELETE" url="/api/payments/{id}" desc={t('sections.api.financial.endpoints.delete_payment')} />
                    <Endpoint method="POST" url="/api/expenses" desc={t('sections.api.financial.endpoints.create_expense')} />
                    <Endpoint method="GET" url="/api/expenses/{id}" desc={t('sections.api.financial.endpoints.get_expense')} />
                    <Endpoint method="PUT" url="/api/expenses/{id}" desc={t('sections.api.financial.endpoints.update_expense')} />
                    <Endpoint method="DELETE" url="/api/expenses/{id}" desc={t('sections.api.financial.endpoints.delete_expense')} />
                    <Endpoint method="GET" url="/api/financial-transactions" desc={t('sections.api.financial.endpoints.transactions')} />
                    <Endpoint method="GET" url="/api/financial-transactions/export" desc={t('sections.api.financial.endpoints.export')} />
                    <Endpoint method="POST" url="/api/daily-balance" desc={t('sections.api.financial.endpoints.daily_balance')} />
                    <Endpoint method="POST" url="/api/daily-closure" desc={t('sections.api.financial.endpoints.daily_closure')} />
                    <Endpoint method="POST" url="/api/daily-closure/lock" desc={t('sections.api.financial.endpoints.lock_closure')} />
                </div>
            </Section>

            <Section title={t('sections.api.media.title')} id="media" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/media" desc={t('sections.api.media.endpoints.list')} />
                    <Endpoint method="GET" url="/api/media/patient" desc={t('sections.api.media.endpoints.patient_images')} />
                </div>
            </Section>

            <Section title={t('sections.api.weather.title')} id="weather" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/weather" desc={t('sections.api.weather.endpoints.current')} />
                    <Endpoint method="GET" url="/api/weather-forecast" desc={t('sections.api.weather.endpoints.forecast')} />
                    <Endpoint method="GET" url="/api/weather-ar" desc={t('sections.api.weather.endpoints.current_ar')} />
                    <Endpoint method="GET" url="/api/weather-forecast-ar" desc={t('sections.api.weather.endpoints.forecast_ar')} />
                </div>
            </Section>

            <Section title={t('sections.api.drugs.title')} id="drugs" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/searchDrugs?q={query}" desc={t('sections.api.drugs.endpoints.search')} />
                    <Endpoint method="GET" url="/api/getDrugDetails?id={id}" desc={t('sections.api.drugs.endpoints.details')} />
                    <Endpoint method="GET" url="/api/getFilterOptions" desc={t('sections.api.drugs.endpoints.filter_options')} />
                    <Endpoint method="GET" url="/api/getMostUsedDrugs" desc={t('sections.api.drugs.endpoints.most_used')} />
                    <Endpoint method="POST" url="/api/drugs/update-database" desc={t('sections.api.drugs.endpoints.update_database')} />
                </div>
            </Section>

            <Section title={t('sections.api.appointment_details.title')} id="appointment-details" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/appointments/{id}/attachments" desc={t('sections.api.appointment_details.endpoints.attachments')} />
                    <Endpoint method="GET" url="/api/appointments/{id}/medications" desc={t('sections.api.appointment_details.endpoints.medications')} />
                    <Endpoint method="GET" url="/api/appointments/{id}/glasses" desc={t('sections.api.appointment_details.endpoints.glasses')} />
                    <Endpoint method="GET" url="/api/appointments/{id}/followup" desc={t('sections.api.appointment_details.endpoints.followup')} />
                    <Endpoint method="GET" url="/api/appointments/{id}/original" desc={t('sections.api.appointment_details.endpoints.original')} />
                    <Endpoint method="POST" url="/api/appointments/{id}/reschedule-followup" desc={t('sections.api.appointment_details.endpoints.reschedule_followup')} />
                </div>
            </Section>

            <Section title={t('sections.api.ophthalmology_tools.title')} id="ophthalmology-tools" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="POST" url="/api/iol/calculate" desc={t('sections.api.ophthalmology_tools.endpoints.iol')} />
                    <Endpoint method="GET" url="/api/iop/analyze?patient_id={id}" desc={t('sections.api.ophthalmology_tools.endpoints.iop_trend')} />
                    <Endpoint method="POST" url="/api/pediatric-iol/calculate" desc={t('sections.api.ophthalmology_tools.endpoints.pediatric_iol')} />
                    <Endpoint method="GET" url="/api/pediatric-iol/calculate" desc={t('sections.api.ophthalmology_tools.endpoints.pediatric_iol_get')} />
                    <Endpoint method="POST" url="/api/astigmatism/calculate" desc={t('sections.api.ophthalmology_tools.endpoints.corneal_astigmatism')} />
                    <Endpoint method="GET" url="/api/astigmatism/calculate" desc={t('sections.api.ophthalmology_tools.endpoints.corneal_astigmatism_get')} />
                    <Endpoint method="POST" url="/api/target-iop/calculate" desc={t('sections.api.ophthalmology_tools.endpoints.target_iop')} />
                    <Endpoint method="GET" url="/api/target-iop/calculate" desc={t('sections.api.ophthalmology_tools.endpoints.target_iop_get')} />
                    <Endpoint method="POST" url="/api/refraction/consistency" desc={t('sections.api.ophthalmology_tools.endpoints.refraction_consistency')} />
                    <Endpoint method="GET" url="/api/refraction/consistency" desc={t('sections.api.ophthalmology_tools.endpoints.refraction_consistency_get')} />
                    <Endpoint method="POST" url="/api/visual-acuity/progress" desc={t('sections.api.ophthalmology_tools.endpoints.visual_acuity')} />
                    <Endpoint method="GET" url="/api/visual-acuity/progress" desc={t('sections.api.ophthalmology_tools.endpoints.visual_acuity_get')} />
                    <Endpoint method="POST" url="/api/osdi/calculate" desc={t('sections.api.ophthalmology_tools.endpoints.osdi')} />
                    <Endpoint method="GET" url="/api/osdi/calculate" desc={t('sections.api.ophthalmology_tools.endpoints.osdi_get')} />
                    <Endpoint method="GET" url="/api/patients/{patientId}/osdi/history" desc={t('sections.api.ophthalmology_tools.endpoints.osdi_history')} />
                    <Endpoint method="POST" url="/api/pachymetry-adjusted-iop/calculate" desc={t('sections.api.ophthalmology_tools.endpoints.pachymetry_iop')} />
                    <Endpoint method="GET" url="/api/pachymetry-adjusted-iop/calculate" desc={t('sections.api.ophthalmology_tools.endpoints.pachymetry_iop_get')} />
                    <Endpoint method="POST" url="/api/diabetic-retinopathy/risk-estimate" desc={t('sections.api.ophthalmology_tools.endpoints.diabetic_retinopathy')} />
                    <Endpoint method="GET" url="/api/diabetic-retinopathy/risk-estimate" desc={t('sections.api.ophthalmology_tools.endpoints.diabetic_retinopathy_get')} />
                    <Endpoint method="POST" url="/api/macular-thickness/trend" desc={t('sections.api.ophthalmology_tools.endpoints.macular_thickness')} />
                    <Endpoint method="GET" url="/api/macular-thickness/trend" desc={t('sections.api.ophthalmology_tools.endpoints.macular_thickness_get')} />
                    <Endpoint method="GET" url="/api/patients/{patientId}/macular-thickness/history" desc={t('sections.api.ophthalmology_tools.endpoints.macular_thickness_history')} />
                    <Endpoint method="POST" url="/api/cataract-surgery/readiness" desc={t('sections.api.ophthalmology_tools.endpoints.cataract_readiness')} />
                    <Endpoint method="GET" url="/api/cataract-surgery/readiness" desc={t('sections.api.ophthalmology_tools.endpoints.cataract_readiness_get')} />
                    <Endpoint method="POST" url="/api/cataract-surgery/postop-outcome" desc={t('sections.api.ophthalmology_tools.endpoints.postop_outcome')} />
                    <Endpoint method="GET" url="/api/cataract-surgery/postop-outcome" desc={t('sections.api.ophthalmology_tools.endpoints.postop_outcome_get')} />
                    <Endpoint method="GET" url="/api/cataract-surgery/audit" desc={t('sections.api.ophthalmology_tools.endpoints.surgical_audit')} />
                </div>
            </Section>

            <Section title={t('sections.api.clinical_dashboard.title')} id="clinical-dashboard" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/clinical-dashboard/snapshot?patient_id={id}" desc={t('sections.api.clinical_dashboard.endpoints.snapshot')} />
                </div>
            </Section>

            <Section title={t('sections.api.medical_history.title')} id="medical-history" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="GET" url="/api/patients/{id}/medical-history" desc={t('sections.api.medical_history.endpoints.list')} />
                    <Endpoint method="POST" url="/api/patients/{id}/medical-history" desc={t('sections.api.medical_history.endpoints.create')} />
                    <Endpoint method="GET" url="/api/patients/{id}/medical-history/{historyId}" desc={t('sections.api.medical_history.endpoints.get')} />
                    <Endpoint method="PUT" url="/api/patients/{id}/medical-history/{historyId}" desc={t('sections.api.medical_history.endpoints.update')} />
                    <Endpoint method="DELETE" url="/api/patients/{id}/medical-history/{historyId}" desc={t('sections.api.medical_history.endpoints.delete')} />
                </div>
            </Section>

            <Section title={t('sections.api.patient_files.title')} id="patient-files" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="POST" url="/api/patients/files/upload" desc={t('sections.api.patient_files.endpoints.upload')} />
                    <Endpoint method="GET" url="/api/patients/files/view/{id}" desc={t('sections.api.patient_files.endpoints.view')} />
                    <Endpoint method="GET" url="/api/patients/files/download/{id}" desc={t('sections.api.patient_files.endpoints.download')} />
                    <Endpoint method="DELETE" url="/api/patients/files/{id}" desc={t('sections.api.patient_files.endpoints.delete')} />
                </div>
            </Section>

            <Section title={t('sections.api.patient_notes.title')} id="patient-notes" className="mb-16">
                <div className="space-y-3">
                    <Endpoint method="POST" url="/api/patients/notes" desc={t('sections.api.patient_notes.endpoints.create')} />
                    <Endpoint method="PUT" url="/api/patients/notes/{id}" desc={t('sections.api.patient_notes.endpoints.update')} />
                    <Endpoint method="DELETE" url="/api/patients/notes/{id}" desc={t('sections.api.patient_notes.endpoints.delete')} />
                </div>
            </Section>
        </div>
    );
}
