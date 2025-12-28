import { BrowserRouter, Route, Routes } from 'react-router-dom';
import Layout from './components/Layout';
import Home from './pages/Home';
import Architecture from './pages/Architecture';
import ApiDocs from './pages/ApiDocs';
import Setup from './pages/Setup';
import ChangeLog from './pages/ChangeLog';
import DoctorDashboardDocs from './pages/modules/DoctorDashboardDocs';
import SecretaryDashboardDocs from './pages/modules/SecretaryDashboardDocs';
import AdminDashboardDocs from './pages/modules/AdminDashboardDocs';
import AdminDocs from './pages/modules/AdminDocs';
import DoctorDocs from './pages/modules/DoctorDocs';
import SecretaryDocs from './pages/modules/SecretaryDocs';
import SidebarDocs from './pages/modules/SidebarDocs';
import NotificationsDocs from './pages/modules/NotificationsDocs';
import ThemeSwitchDocs from './pages/modules/ThemeSwitchDocs';
import DockDocs from './pages/modules/DockDocs';
import SearchDocs from './pages/modules/SearchDocs';
import NoticeBarDocs from './pages/modules/NoticeBarDocs';
import CalendarDocs from './pages/modules/CalendarDocs';
import AppointmentDocs from './pages/modules/AppointmentDocs';
import SecretaryBookingsDocs from './pages/modules/SecretaryBookingsDocs';
import SecretaryPatientsDocs from './pages/modules/SecretaryPatientsDocs';
import SecretaryPaymentsDocs from './pages/modules/SecretaryPaymentsDocs';
import PatientsDocs from './pages/modules/PatientsDocs';
import ForumDocs from './pages/modules/ForumDocs';
import DrugsDocs from './pages/modules/DrugsDocs';
import FinanceDocs from './pages/modules/FinanceDocs';
import ReportsDocs from './pages/modules/ReportsDocs';
import MedicationsDocs from './pages/modules/MedicationsDocs';
import GlassesDocs from './pages/modules/GlassesDocs';
import MediaDocs from './pages/modules/MediaDocs';
import AlertsDocs from './pages/modules/AlertsDocs';
import NotesDocs from './pages/modules/NotesDocs';
import ProfileDocs from './pages/modules/ProfileDocs';
import PatientProfileDocs from './pages/modules/PatientProfileDocs';
import SettingsDocs from './pages/modules/SettingsDocs';
import AdminModuleDocs from './pages/modules/AdminModuleDocs';

function App() {
    return (
        <BrowserRouter basename="/docs/opth">
            <Routes>
                <Route path="/" element={<Layout />}>
                    <Route index element={<Home />} />
                    <Route path="architecture" element={<Architecture />} />
                    <Route path="api" element={<ApiDocs />} />
                    <Route path="setup" element={<Setup />} />
                    <Route path="changelog" element={<ChangeLog />} />

                    <Route path="dashboards/doctor" element={<DoctorDashboardDocs />} />
                    <Route path="dashboards/secretary" element={<SecretaryDashboardDocs />} />
                    <Route path="dashboards/admin" element={<AdminDashboardDocs />} />

                    <Route path="modules/admin" element={<AdminDocs />} />
                    <Route path="modules/doctor" element={<DoctorDocs />} />
                    <Route path="modules/secretary" element={<SecretaryDocs />} />

                    <Route path="ui-components/sidebar" element={<SidebarDocs />} />
                    <Route path="ui-components/notifications" element={<NotificationsDocs />} />
                    <Route path="ui-components/theme-switch" element={<ThemeSwitchDocs />} />
                    <Route path="ui-components/dock" element={<DockDocs />} />
                    <Route path="ui-components/search" element={<SearchDocs />} />
                    <Route path="ui-components/notice-bar" element={<NoticeBarDocs />} />

                    <Route path="doctors-pages/calendar" element={<CalendarDocs />} />
                    <Route path="doctors-pages/appointment" element={<AppointmentDocs />} />
                    <Route path="doctors-pages/secretary-bookings" element={<SecretaryBookingsDocs />} />
                    <Route path="doctors-pages/secretary-patients" element={<SecretaryPatientsDocs />} />
                    <Route path="doctors-pages/secretary-payments" element={<SecretaryPaymentsDocs />} />
                    <Route path="doctors-pages/patients" element={<PatientsDocs />} />
                    <Route path="doctors-pages/forum" element={<ForumDocs />} />
                    <Route path="doctors-pages/drugs" element={<DrugsDocs />} />
                    <Route path="doctors-pages/finance" element={<FinanceDocs />} />
                    <Route path="doctors-pages/reports" element={<ReportsDocs />} />
                    <Route path="doctors-pages/medications" element={<MedicationsDocs />} />
                    <Route path="doctors-pages/glasses" element={<GlassesDocs />} />
                    <Route path="doctors-pages/media" element={<MediaDocs />} />
                    <Route path="doctors-pages/alerts" element={<AlertsDocs />} />
                    <Route path="doctors-pages/notes" element={<NotesDocs />} />
                    <Route path="doctors-pages/profile" element={<ProfileDocs />} />
                    <Route path="doctors-pages/patient-profile" element={<PatientProfileDocs />} />
                    <Route path="doctors-pages/settings" element={<SettingsDocs />} />
                    <Route path="admin-module" element={<AdminModuleDocs />} />
                </Route>
            </Routes>
        </BrowserRouter>
    );
}

export default App;
