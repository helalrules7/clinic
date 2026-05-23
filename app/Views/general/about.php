<!-- About System Page -->
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient" style="background: linear-gradient(135deg, var(--accent), var(--success));">
                <div class="card-body text-white text-center py-4 header-content">
                    <div class="mb-3">
                        <i class="bi bi-eye" style="font-size: 3.5rem; opacity: 0.9;"></i>
                    </div>
                    <h1 class="display-5 fw-bold mb-2">HClinic / Roaya Clinic</h1>
                    <h2 class="h5 mb-3 opacity-90">Management System</h2>
                    <div class="badge bg-white text-dark px-4 py-2 fs-6 fw-semibold">
                        Version 6.1
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
    <!-- System Information -->
            <div class="row mb-4">
                <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-info-circle text-primary me-2"></i>
                        System Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                                <div class="col-md-6 mb-3">
                                <h5 class="text-muted mb-2">
                                    <i class="bi bi-tag me-2"></i>Version
                                </h5>
                                    <p class="h4 text-primary fw-bold mb-0">6.1</p>
                            </div>
                                <div class="col-md-6 mb-3">
                                <h5 class="text-muted mb-2">
                                    <i class="bi bi-calendar me-2"></i>Release Year
                                </h5>
                                    <p class="h4 text-primary fw-bold mb-0"><?= $releaseDate ?></p>
                        </div>
                        <div class="col-12">
                                    <h5 class="text-muted mb-2">
                                    <i class="bi bi-file-text me-2"></i>Description
                                </h5>
                                    <p class="mb-0">
                                    HClinic / Roaya Clinic Management System is a comprehensive healthcare management solution designed 
                                    to streamline clinic operations, enhance patient care, and improve administrative efficiency. 
                                </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

            <!-- Previous Versions Features -->
            <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                                <i class="bi bi-clock-history text-secondary me-2"></i>
                                Previous Versions Features
                    </h3>
                </div>
                <div class="card-body">
                            <!-- Version 6.0 -->
                            <div class="version-section mb-4">
                                <h4 class="version-title mb-3">
                                    <span class="badge bg-primary me-2">v6.0</span>
                                    Media Gallery & Prescriptions Management
                                </h4>
                                <ul class="version-features">
                                    <li>Comprehensive media management system organizing all patient images and attachments</li>
                                    <li>Group images by patient with thumbnail preview and image count</li>
                                    <li>Full-screen carousel viewer for media gallery</li>
                                    <li>Patient name filtering with autocomplete</li>
                                    <li>Load more pagination system</li>
                                    <li>Direct links to appointments and visits</li>
                                    <li>Dedicated glasses prescriptions gallery grouped by patient</li>
                                    <li>Medication prescriptions organized by patient and appointment</li>
                                    <li>Complete Ajax implementation for all operations without page reloads</li>
                                    <li>Real-time UI updates with progress indicators</li>
                                    <li>Significant performance improvements and optimizations</li>
                                    <li>Faster page load times and optimized database queries</li>
                                    <li>Efficient pagination and reduced server requests</li>
                                    <li>Better caching strategies and improved response times</li>
                                </ul>
                                    </div>

                            <!-- Version 5.1 -->
                            <div class="version-section mb-4">
                                <h4 class="version-title mb-3">
                                    <span class="badge bg-info me-2">v5.1</span>
                                    Automatic Drug Database Update System
                                </h4>
                                <ul class="version-features">
                                    <li>Complete automated drug database update with SQLite support</li>
                                    <li>Real-time database synchronization with progress tracking</li>
                                    <li>SQLite command-line integration with error handling</li>
                                    <li>Enhanced error handling and logging system</li>
                                </ul>
                                    </div>

                            <!-- Version 5.0 -->
                            <div class="version-section mb-4">
                                <h4 class="version-title mb-3">
                                    <span class="badge bg-primary me-2">v5.0</span>
                                    Dashboard & Financial Management
                                </h4>
                                <ul class="version-features">
                                    <li>Daily financial management with opening balance and daily closure</li>
                                    <li>Advanced Excel export with Arabic RTL support</li>
                                    <li>Secretary management system with role-based permissions</li>
                                    <li>Real-time financial dashboard with analytics</li>
                                    <li>Appointment history timeline with attachments</li>
                                    <li>Visual analytics dashboard with Chart.js</li>
                                    <li>Missed appointments tracking and management</li>
                                    <li>Advanced pagination and quantity control</li>
                                </ul>
                                </div>

                            <!-- Version 4.0 -->
                            <div class="version-section mb-4">
                                <h4 class="version-title mb-3">
                                    <span class="badge bg-success me-2">v4.0</span>
                                    Smart Drug Management & Security
                                </h4>
                                <ul class="version-features">
                                    <li>Smart drug suggestions with usage analytics</li>
                                    <li>Advanced drug autocomplete search</li>
                                    <li>Comprehensive drug information system</li>
                                    <li>Enhanced security with CSRF protection</li>
                                    <li>Advanced clinic settings with branding</li>
                                    <li>Logo and branding management system</li>
                                    <li>Dynamic print templates</li>
                                    <li>Visit cost management system</li>
                                </ul>
                            </div>

                            <!-- Version 3.0 -->
                            <div class="version-section mb-4">
                                <h4 class="version-title mb-3">
                                    <span class="badge bg-warning me-2">v3.0</span>
                                    Patient Management & Export
                                </h4>
                                <ul class="version-features">
                                    <li>Patient data export to Word with embedded images</li>
                                    <li>Glasses prescriptions management</li>
                                    <li>Patient information editing interface</li>
                                    <li>Enhanced image processing for exports</li>
                                    <li>Improved user interface design</li>
                                    <li>Doctor filter for patients</li>
                                </ul>
                        </div>

                            <!-- Version 2.0 -->
                            <div class="version-section mb-4">
                                <h4 class="version-title mb-3">
                                    <span class="badge bg-danger me-2">v2.0</span>
                                    Admin Dashboard & Reports
                                </h4>
                                <ul class="version-features">
                                    <li>Complete admin dashboard system</li>
                                    <li>Advanced user management with roles</li>
                                    <li>Enhanced reporting system with charts</li>
                                    <li>System settings panel</li>
                                    <li>Enhanced dark/light mode support</li>
                                    <li>Font Awesome integration</li>
                                </ul>
                                    </div>

                            <!-- Version 1.0 -->
                            <div class="version-section mb-4">
                                <h4 class="version-title mb-3">
                                    <span class="badge bg-secondary me-2">v1.0</span>
                                    Core Features
                                </h4>
                                <ul class="version-features">
                                    <li>Patient management system</li>
                                    <li>Appointment scheduling</li>
                                    <li>Medical records management</li>
                                    <li>Payment management</li>
                                    <li>User management with role-based access</li>
                                    <li>Dark mode support</li>
                                </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technology Stack -->
            <div class="row mb-4">
                <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-gear text-secondary me-2"></i>
                        Technology Stack
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="tech-item">
                                <div class="tech-icon bg-primary text-white rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-code-slash"></i>
                                </div>
                                <h6 class="fw-bold">PHP 8+</h6>
                                <small class="text-muted">Backend</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="tech-item">
                                <div class="tech-icon bg-success text-white rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-database"></i>
                                </div>
                                <h6 class="fw-bold">MySQL</h6>
                                <small class="text-muted">Database</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="tech-item">
                                <div class="tech-icon bg-info text-white rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-bootstrap"></i>
                                </div>
                                <h6 class="fw-bold">Bootstrap 5</h6>
                                <small class="text-muted">Frontend</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="tech-item">
                                <div class="tech-icon bg-warning text-white rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-lightning"></i>
                                </div>
                                <h6 class="fw-bold">Vanilla JS</h6>
                                <small class="text-muted">Interactive</small>
                            </div>
                        </div>
                    </div>
                                </div>
            </div>
        </div>
    </div>

            <!-- Developer Information -->
            <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-person-badge me-2"></i>
                        Developer Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                                <h4 class="fw-bold text-primary mb-2"><?= $developer['name'] ?></h4>
                                <p class="text-muted mb-3"><?= $developer['title'] ?></p>
                                    <p class="mb-4">
                                    Experienced full-stack developer specializing in modern web applications 
                                        and healthcare management systems.
                                </p>
                                <div class="d-flex gap-3">
                                    <a href="<?= $developer['website'] ?>" target="_blank" class="btn btn-primary">
                                        <i class="bi bi-globe me-2"></i>
                                        Visit Website
                                    </a>
                                    <a href="mailto:contact@ahmedhelal.dev" class="btn btn-outline-primary">
                                        <i class="bi bi-envelope me-2"></i>
                                        Contact
                                    </a>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                                    <div class="developer-avatar bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: bold;">
                                AH
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Sidebar - Version 6.1 Features -->
        <div class="col-lg-4">
            <div class="card border-primary sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-star-fill me-2"></i>
                        Version 6.1 Features
                    </h3>
                </div>
                <div class="card-body">
                    <div class="v6-feature-item mb-3">
                        <div class="d-flex align-items-start mb-2">
                            <div class="v6-feature-icon bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; flex-shrink: 0;">
                                <i class="bi bi-bell"></i>
                                </div>
                                <div>
                                <h6 class="fw-bold mb-1">Patient Alert System</h6>
                                <p class="text-muted small mb-0">Comprehensive alert and notification system with real-time toast notifications</p>
                                </div>
                            </div>
                        </div>

                    <div class="v6-feature-item mb-3">
                        <div class="d-flex align-items-start mb-2">
                            <div class="v6-feature-icon bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; flex-shrink: 0;">
                                <i class="bi bi-person-badge"></i>
                                </div>
                                <div>
                                <h6 class="fw-bold mb-1">Enhanced Patient Page UI/UX</h6>
                                <p class="text-muted small mb-0">Improved timeline markers, reorganized actions, and better visual hierarchy</p>
            </div>
        </div>
    </div>

                    <div class="v6-feature-item mb-3">
                        <div class="d-flex align-items-start mb-2">
                            <div class="v6-feature-icon bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; flex-shrink: 0;">
                                <i class="bi bi-window"></i>
                </div>
                                    <div>
                                <h6 class="fw-bold mb-1">Glass Fixed Header System</h6>
                                <p class="text-muted small mb-0">Beautiful glass-effect header with backdrop blur and scroll effects</p>
            </div>
        </div>
    </div>

                    <div class="mt-4 pt-3 border-top">
                        <a href="/whats-new" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-arrow-right-circle me-2"></i>
                            What's New?
                        </a>
                        <a href="/whats-new/full-features" class="btn btn-outline-primary w-100">
                            <i class="bi bi-stars me-2"></i>
                            Full Features
                        </a>
                            </div>
                        </div>
            </div>
        </div>
    </div>
</div>

<style>
/* About page specific styles */
.feature-item:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease;
}

.tech-item:hover .tech-icon {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}

.developer-avatar {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.developer-avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.card-header {
    background-color: transparent !important;
    border-color: var(--border);
    color: var(--text);
}

.card-body {
    background-color: transparent !important;
    color: var(--text);
}

.text-muted {
    color: var(--muted) !important;
}

.text-primary {
    color: var(--accent) !important;
}

/* Version sections styling */
.version-section {
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border);
}

.version-section:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.version-title {
    font-size: 1.25rem;
    color: var(--text);
    display: flex;
    align-items: center;
}

.version-features {
    list-style: none;
    padding: 0;
    margin: 0;
}

.version-features li {
    padding: 0.5rem 0;
    padding-left: 1.5rem;
    position: relative;
    color: var(--muted);
}

.version-features li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: var(--success);
    font-weight: bold;
}

/* V6 Sidebar features */
.v6-feature-item {
    transition: all 0.2s ease;
    padding: 0.5rem;
    border-radius: 8px;
}

.v6-feature-item:hover {
    background-color: var(--bg);
    transform: translateX(5px);
}

.v6-feature-icon {
    transition: transform 0.2s ease;
}

.v6-feature-item:hover .v6-feature-icon {
    transform: scale(1.1);
}

/* Header gradient styling */
.bg-gradient {
    background: linear-gradient(135deg, var(--accent), var(--success)) !important;
}

.header-content {
    background: linear-gradient(135deg, 
        var(--accent) 0%, 
        #22d3ee 25%, 
        var(--success) 50%, 
        #34d399 75%, 
        var(--accent) 100%) !important;
    background-size: 400% 400% !important;
    animation: gradientShift 8s ease-in-out infinite !important;
    position: relative !important;
    overflow: hidden !important;
}

.header-content {
    color: #000000 !important;
    text-shadow: 2px 2px 4px rgba(255, 255, 255, 0.3);
}

.header-content .bi-eye {
    color: #000000 !important;
    filter: drop-shadow(2px 2px 4px rgba(255, 255, 255, 0.3));
}

.header-content .display-5,
.header-content .h5 {
    color: #000000 !important;
    text-shadow: 2px 2px 4px rgba(255, 255, 255, 0.3);
}

.dark .header-content {
    color: #ffffff !important;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    background: linear-gradient(135deg, 
        #1e40af 0%, 
        #4F46E5 25%, 
        #059669 50%, 
        #10b981 75%, 
        #1e40af 100%) !important;
    background-size: 400% 400% !important;
}

.dark .header-content .bi-eye {
    color: #ffffff !important;
    filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.5));
}

.dark .header-content .display-5,
.dark .header-content .h5 {
    color: #ffffff !important;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

@keyframes gradientShift {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

.badge.bg-white.text-dark {
    background-color: rgba(255, 255, 255, 0.95) !important;
    color: #1a1a1a !important;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.dark .card:hover {
    box-shadow: 0 8px 25px rgba(255, 255, 255, 0.05);
}

.dark .developer-avatar:hover {
    box-shadow: 0 8px 25px rgba(255, 255, 255, 0.1);
}

.dark .badge.bg-white.text-dark {
    background-color: rgba(30, 41, 59, 0.9) !important;
    color: var(--text) !important;
    border: 1px solid var(--border) !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.dark .version-features li::before {
    color: #10b981;
}

/* Responsive */
@media (max-width: 991px) {
    .sticky-top {
        position: relative !important;
        top: 0 !important;
    }
}
</style>
