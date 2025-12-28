import { Home, Server, Layers, Settings, FileCode, LayoutDashboard, Palette, User, GitBranch, Calendar, Users, Wallet, Stethoscope, Shield } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useMemo } from 'react';

export interface NavItem {
    to?: string;
    label: string;
    icon?: any;
    items?: NavItem[];
}

export function useNavigation() {
    const { t } = useTranslation();

    const links: NavItem[] = useMemo(() => [
        { to: '/', icon: Home, label: t('nav.overview') },
        { to: '/architecture', icon: Server, label: t('nav.architecture') },
        {
            label: t('nav.modules'),
            icon: Layers,
            items: [
                { to: '/modules/admin', label: t('nav.admin') },
                { to: '/modules/doctor', label: t('nav.doctor') },
                { to: '/modules/secretary', label: t('nav.secretary') },
            ]
        },
        {
            label: t('nav.dashboards'),
            icon: LayoutDashboard,
            items: [
                { to: '/dashboards/doctor', label: t('nav.doctor_dashboard') },
                { to: '/dashboards/secretary', label: t('nav.secretary_dashboard') },
                { to: '/dashboards/admin', label: t('nav.admin_dashboard') },
            ]
        },
        {
            label: t('nav.calendar_section'),
            icon: Calendar,
            items: [
                { to: '/doctors-pages/calendar', label: t('nav.doctor_calendar') },
                { to: '/doctors-pages/appointment', label: t('nav.appointment') },
                { to: '/doctors-pages/secretary-bookings', label: t('nav.secretary_bookings') },
            ]
        },
        {
            label: t('nav.patients_section'),
            icon: Users,
            items: [
                { to: '/doctors-pages/patients', label: t('nav.doctors_view') },
                { to: '/doctors-pages/patient-profile', label: t('nav.patient_profile') },
                { to: '/doctors-pages/secretary-patients', label: t('nav.secretary_patients') },
            ]
        },
        {
            label: t('nav.finance_section'),
            icon: Wallet,
            items: [
                { to: '/doctors-pages/finance', label: t('nav.doctors_view') },
                { to: '/doctors-pages/secretary-payments', label: t('nav.secretary_payments') },
            ]
        },
        {
            label: t('nav.doctors_pages'),
            icon: Stethoscope,
            items: [
                { to: '/doctors-pages/forum', label: t('nav.forum') },
                { to: '/doctors-pages/drugs', label: t('nav.drugs') },
                { to: '/doctors-pages/reports', label: t('nav.reports') },
                { to: '/doctors-pages/medications', label: t('nav.medications') },
                { to: '/doctors-pages/glasses', label: t('nav.glasses') },
                { to: '/doctors-pages/media', label: t('nav.media') },
                { to: '/doctors-pages/alerts', label: t('nav.alerts') },
                { to: '/doctors-pages/notes', label: t('nav.notes') },
                { to: '/doctors-pages/settings', label: t('nav.settings') },
            ]
        },
        {
            label: t('nav.ui_components'),
            icon: Palette,
            items: [
                { to: '/ui-components/notice-bar', label: t('nav.notice_bar') },
                { to: '/ui-components/sidebar', label: t('nav.sidebar') },
                { to: '/ui-components/notifications', label: t('nav.notifications') },
                { to: '/ui-components/theme-switch', label: t('nav.theme_switch') },
                { to: '/ui-components/dock', label: t('nav.dock') },
                { to: '/ui-components/search', label: t('nav.search') },
            ]
        },
        { to: '/doctors-pages/profile', icon: User, label: t('nav.profile') },
        { to: '/admin-module', icon: Shield, label: t('nav.admin_module_link') },
        { to: '/api', icon: FileCode, label: t('nav.api') },
        { to: '/setup', icon: Settings, label: t('nav.setup') },
        { to: '/changelog', icon: GitBranch, label: t('nav.changelog') },
    ], [t]);

    return links;
}
