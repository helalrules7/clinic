<!-- About System Page -->
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 bg-gradient" style="background: linear-gradient(135deg, var(--accent), var(--success));">
                <div class="card-body text-white text-center py-5 header-content">
                    <div class="mb-4">
                        <i class="bi bi-eye" style="font-size: 4rem; opacity: 0.9;"></i>
                    </div>
                    <h1 class="display-4 fw-bold mb-3">Roaya Clinic</h1>
                    <h2 class="h4 mb-4 opacity-90">Management System</h2>
                    <div class="badge bg-white text-dark px-4 py-2 fs-6 fw-semibold">
                        Version 1.2
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-info-circle text-primary me-2"></i>
                        System Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-4">
                                <h5 class="text-muted mb-2">
                                    <i class="bi bi-tag me-2"></i>Version
                                </h5>
                                <p class="h4 text-primary fw-bold">1.2</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-4">
                                <h5 class="text-muted mb-2">
                                    <i class="bi bi-calendar me-2"></i>Release Year
                                </h5>
                                <p class="h4 text-primary fw-bold"><?= $releaseDate ?></p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-item">
                                <h5 class="text-muted mb-3">
                                    <i class="bi bi-file-text me-2"></i>Description
                                </h5>
                                <p class="lead">
                                    Roaya Clinic Management System is a comprehensive healthcare management solution designed 
                                    to streamline clinic operations, enhance patient care, and improve administrative efficiency. 
                                    The system provides a modern, user-friendly interface for managing patients, appointments, 
                                    medical records, and more.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Features -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-star text-warning me-2"></i>
                        Key Features
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Patient Management</h5>
                                        <p class="text-muted mb-0">Comprehensive patient records, medical history, and contact information management with advanced search capabilities.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Appointment Scheduling</h5>
                                        <p class="text-muted mb-0">Efficient appointment booking system with calendar integration and real-time availability checking.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="bi bi-file-medical"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Medical Records</h5>
                                        <p class="text-muted mb-0">Digital medical records with consultation notes, prescriptions, lab tests, and treatment history.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="bi bi-credit-card"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Payment Management</h5>
                                        <p class="text-muted mb-0">Comprehensive billing and payment tracking system with invoice generation and financial reporting.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">User Management</h5>
                                        <p class="text-muted mb-0">Role-based access control with separate interfaces for doctors, secretaries, and administrators.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="bi bi-moon"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Dark Mode Support</h5>
                                        <p class="text-muted mb-0">Modern user interface with light and dark theme options for comfortable usage in any environment.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-purple text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #6f42c1 !important;">
                                        <i class="bi bi-download"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Patient Data Export</h5>
                                        <p class="text-muted mb-0">Export comprehensive patient data including medical history, notes, and files to Word documents with embedded images.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-teal text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #20c997 !important;">
                                        <i class="bi bi-eyeglasses"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Glasses Prescriptions</h5>
                                        <p class="text-muted mb-0">Complete glasses prescription management with distance/near vision settings, PD measurements, and print functionality.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-orange text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #fd7e14 !important;">
                                        <i class="bi bi-pencil-square"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Patient Editing</h5>
                                        <p class="text-muted mb-0">Full patient information editing with comprehensive forms, validation, and secure update functionality.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technology Stack -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
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
                    <div class="row text-center mt-4">
                        <div class="col-md-4 mb-3">
                            <div class="tech-item">
                                <div class="tech-icon text-white rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #6f42c1;">
                                    <i class="bi bi-file-earmark-word"></i>
                                </div>
                                <h6 class="fw-bold">PHPWord</h6>
                                <small class="text-muted">Document Export</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="tech-item">
                                <div class="tech-icon text-white rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #20c997;">
                                    <i class="bi bi-image"></i>
                                </div>
                                <h6 class="fw-bold">GD Library</h6>
                                <small class="text-muted">Image Processing</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="tech-item">
                                <div class="tech-icon text-white rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #fd7e14;">
                                    <i class="bi bi-gear"></i>
                                </div>
                                <h6 class="fw-bold">Composer</h6>
                                <small class="text-muted">Dependencies</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Developer Information -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
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
                            <div class="developer-info">
                                <h4 class="fw-bold text-primary mb-2"><?= $developer['name'] ?></h4>
                                <p class="text-muted mb-3"><?= $developer['title'] ?></p>
                                <p class="lead mb-4">
                                    Experienced full-stack developer specializing in modern web applications 
                                    and healthcare management systems. Passionate about creating intuitive, 
                                    efficient, and scalable solutions for healthcare providers.
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
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="developer-avatar bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 120px; height: 120px; font-size: 3rem; font-weight: bold;">
                                AH
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- What's New -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-lightning text-warning me-2"></i>
                        What's New in Version 1.2
                    </h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-purple text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #6f42c1 !important;">
                                    <i class="bi bi-download" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Patient Data Export to Word</h6>
                                    <p class="text-muted mb-0">Complete patient data export functionality with Word document generation, embedded images, and comprehensive medical records export using PHPWord library.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px;">
                                    <i class="bi bi-eyeglasses" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Glasses Prescriptions Management</h6>
                                    <p class="text-muted mb-0">Full glasses prescription system with distance/near vision settings, cylinder and axis values, PD measurements, and professional print functionality.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px;">
                                    <i class="bi bi-pencil-square" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Patient Information Editing</h6>
                                    <p class="text-muted mb-0">Comprehensive patient editing interface with form validation, responsive design, and secure update functionality for all patient data fields.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px;">
                                    <i class="bi bi-image" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Enhanced Image Processing</h6>
                                    <p class="text-muted mb-0">Advanced image embedding in Word exports with automatic JPEG conversion, white background processing, and optimized compression for document compatibility.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px;">
                                    <i class="bi bi-ui-checks" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Improved User Interface</h6>
                                    <p class="text-muted mb-0">Replaced dropdown menus with individual action buttons, improved button layouts, and enhanced responsive design for better user experience.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px;">
                                    <i class="bi bi-bug" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Bug Fixes & Optimizations</h6>
                                    <p class="text-muted mb-0">Fixed routing issues, corrected SQL queries, improved error handling, and enhanced authentication checks for better system stability.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="row mb-5">
        <div class="col-lg-6 mx-auto">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                    <h4 class="fw-bold text-success mb-2">System Status: Active</h4>
                    <p class="text-muted mb-4">All systems are operational and running smoothly</p>
                    <div class="row">
                        <div class="col-4">
                            <div class="status-item">
                                <div class="h5 fw-bold text-success">✓</div>
                                <small class="text-muted">Database</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="status-item">
                                <div class="h5 fw-bold text-success">✓</div>
                                <small class="text-muted">API</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="status-item">
                                <div class="h5 fw-bold text-success">✓</div>
                                <small class="text-muted">Security</small>
                            </div>
                        </div>
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
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.card-body {
    background-color: var(--card);
    color: var(--text);
}

.text-muted {
    color: var(--muted) !important;
}

.text-primary {
    color: var(--accent) !important;
}

.status-item {
    padding: 1rem;
}

/* Timeline styling */
.timeline-item {
    position: relative;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 40px;
    width: 2px;
    height: calc(100% - 30px);
    background: var(--border);
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-marker {
    position: relative;
    z-index: 1;
}

/* Header gradient styling */
.bg-gradient {
    background: linear-gradient(135deg, var(--accent), var(--success)) !important;
}

/* Header content with gradient background and dynamic colors */
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

/* Light mode - black text and icons */
.header-content {
    color: #000000 !important;
    text-shadow: 2px 2px 4px rgba(255, 255, 255, 0.3);
}

.header-content .bi-eye {
    color: #000000 !important;
    filter: drop-shadow(2px 2px 4px rgba(255, 255, 255, 0.3));
}

.header-content .display-4,
.header-content .h4 {
    color: #000000 !important;
    text-shadow: 2px 2px 4px rgba(255, 255, 255, 0.3);
}

/* Dark mode - white text and icons */
.dark .header-content {
    color: #ffffff !important;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    background: linear-gradient(135deg, 
        #1e40af 0%, 
        #0ea5e9 25%, 
        #059669 50%, 
        #10b981 75%, 
        #1e40af 100%) !important;
    background-size: 400% 400% !important;
}

.dark .header-content .bi-eye {
    color: #ffffff !important;
    filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.5));
}

.dark .header-content .display-4,
.dark .header-content .h4 {
    color: #ffffff !important;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

/* Gradient animation */
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

/* Badge styling for both modes */
.badge.bg-white.text-dark {
    background-color: rgba(255, 255, 255, 0.95) !important;
    color: #1a1a1a !important;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Dark mode adjustments */
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
</style>