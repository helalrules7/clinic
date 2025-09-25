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
                        Version 4.0
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
                                <p class="h4 text-primary fw-bold">4.0</p>
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
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-indigo text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #6366f1 !important;">
                                        <i class="bi bi-speedometer2"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Admin Dashboard</h5>
                                        <p class="text-muted mb-0">Comprehensive admin dashboard with system statistics, user management, reports, and system health monitoring.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-rose text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #f43f5e !important;">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">User Management</h5>
                                        <p class="text-muted mb-0">Complete user management system with role-based access control, user creation, editing, and deletion functionality.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-emerald text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #10b981 !important;">
                                        <i class="bi bi-graph-up"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Advanced Reports</h5>
                                        <p class="text-muted mb-0">Comprehensive reporting system with user statistics, appointment analytics, financial reports, and data export capabilities.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-amber text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #f59e0b !important;">
                                        <i class="bi bi-gear-fill"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">System Settings</h5>
                                        <p class="text-muted mb-0">Complete system configuration with clinic information, notification settings, maintenance mode, and backup management.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-cyan text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #06b6d4 !important;">
                                        <i class="bi bi-funnel"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Doctor Filter</h5>
                                        <p class="text-muted mb-0">Advanced filtering system for patients by doctor with real-time search, dropdown selection, and dynamic results.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-emerald text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #10b981 !important;">
                                        <i class="bi bi-cash-stack"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Daily Financial Management</h5>
                                        <p class="text-muted mb-0">Complete daily financial tracking with opening balance, additional balance, withdrawals, expenses, and automated daily closure system.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-blue text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #3b82f6 !important;">
                                        <i class="bi bi-file-earmark-excel"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Advanced Excel Export</h5>
                                        <p class="text-muted mb-0">Professional Excel export with Arabic RTL support, conditional formatting, color-coded transactions, and comprehensive financial reporting.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-purple text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #8b5cf6 !important;">
                                        <i class="bi bi-person-badge"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Secretary Management System</h5>
                                        <p class="text-muted mb-0">Complete secretary interface with profile management, financial access, patient management, and role-based permissions.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-orange text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #f97316 !important;">
                                        <i class="bi bi-graph-up-arrow"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Real-time Financial Dashboard</h5>
                                        <p class="text-muted mb-0">Live financial dashboard with current balance tracking, transaction summaries, and comprehensive financial analytics.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-teal text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #14b8a6 !important;">
                                        <i class="bi bi-calendar-x"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Daily Closure System</h5>
                                        <p class="text-muted mb-0">Automated daily closure with financial summaries, balance verification, and comprehensive daily reports for complete financial control.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-coral text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #ff6b6b !important;">
                                        <i class="bi bi-capsule"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Smart Drug Management</h5>
                                        <p class="text-muted mb-0">Intelligent drug suggestions with usage analytics, autocomplete search, comprehensive drug database, and streamlined prescription workflow.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-emerald text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #10b981 !important;">
                                        <i class="bi bi-currency-dollar"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Visit Cost Management</h5>
                                        <p class="text-muted mb-0">Comprehensive visit cost management system with configurable new visit and repeated visit pricing, automated billing calculations, and financial tracking.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-violet text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #8b5cf6 !important;">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Advanced Clinic Settings</h5>
                                        <p class="text-muted mb-0">Complete clinic configuration with Arabic/English names, logo management, watermark settings, website integration, and dynamic print templates.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-rose text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #f43f5e !important;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Logo & Branding Management</h5>
                                        <p class="text-muted mb-0">Advanced logo upload system with preview functionality, multiple logo types (clinic, print, watermark), and secure file management with .htaccess protection.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-amber text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #f59e0b !important;">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Dynamic Print Templates</h5>
                                        <p class="text-muted mb-0">All print templates now use dynamic clinic information from settings, including Arabic names, logos, contact details, and customizable branding elements.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-item">
                                <div class="d-flex align-items-start">
                                    <div class="feature-icon bg-teal text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #20c997 !important;">
                                        <i class="bi bi-shield-lock"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Enhanced Security</h5>
                                        <p class="text-muted mb-0">Advanced security features with CSRF protection, secure file uploads, .htaccess file protection, and comprehensive input validation for all forms.</p>
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

    <!-- Version 4.0 New Features -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-star-fill me-2"></i>
                        Version 4.0 New Features
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 mb-4">
                            <div class="new-feature-card text-center p-4 border rounded">
                                <div class="new-feature-icon bg-emerald text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #10b981 !important;">
                                    <i class="bi bi-cash-stack" style="font-size: 1.5rem;"></i>
                                </div>
                                <h5 class="fw-bold mb-3">Daily Financial Management</h5>
                                <p class="text-muted mb-0">Complete daily financial tracking with opening balance, additional balance, withdrawals, expenses, and automated daily closure system for comprehensive financial control.</p>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <div class="new-feature-card text-center p-4 border rounded">
                                <div class="new-feature-icon bg-blue text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #3b82f6 !important;">
                                    <i class="bi bi-file-earmark-excel" style="font-size: 1.5rem;"></i>
                                </div>
                                <h5 class="fw-bold mb-3">Advanced Excel Export</h5>
                                <p class="text-muted mb-0">Professional Excel export with Arabic RTL support, conditional formatting, color-coded transactions, and comprehensive financial reporting using PhpSpreadsheet.</p>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <div class="new-feature-card text-center p-4 border rounded">
                                <div class="new-feature-icon bg-purple text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #8b5cf6 !important;">
                                    <i class="bi bi-person-badge" style="font-size: 1.5rem;"></i>
                                </div>
                                <h5 class="fw-bold mb-3">Secretary Management System</h5>
                                <p class="text-muted mb-0">Complete secretary interface with profile management, financial access, patient management, and role-based permissions for enhanced workflow efficiency.</p>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-lg-4 mb-4">
                            <div class="new-feature-card text-center p-4 border rounded">
                                <div class="new-feature-icon bg-orange text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #f97316 !important;">
                                    <i class="bi bi-graph-up-arrow" style="font-size: 1.5rem;"></i>
                                </div>
                                <h5 class="fw-bold mb-3">Real-time Financial Dashboard</h5>
                                <p class="text-muted mb-0">Live financial dashboard with current balance tracking, transaction summaries, and comprehensive financial analytics for better decision making.</p>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <div class="new-feature-card text-center p-4 border rounded">
                                <div class="new-feature-icon bg-teal text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #14b8a6 !important;">
                                    <i class="bi bi-calendar-x" style="font-size: 1.5rem;"></i>
                                </div>
                                <h5 class="fw-bold mb-3">Daily Closure System</h5>
                                <p class="text-muted mb-0">Automated daily closure with financial summaries, balance verification, and comprehensive daily reports for complete financial control and accountability.</p>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <div class="new-feature-card text-center p-4 border rounded">
                                <div class="new-feature-icon bg-rose text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #f43f5e !important;">
                                    <i class="bi bi-shield-lock" style="font-size: 1.5rem;"></i>
                                </div>
                                <h5 class="fw-bold mb-3">Enhanced Security & Validation</h5>
                                <p class="text-muted mb-0">Advanced security features with CSRF protection, comprehensive input validation, secure file management, and role-based access control for maximum data protection.</p>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-primary border-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-lightning-charge text-primary me-3" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <h6 class="alert-heading mb-1">Major Financial & Management Enhancement</h6>
                                        <p class="mb-0">Version 4.0 brings revolutionary improvements to clinic management with comprehensive daily financial tracking, advanced Excel export capabilities, complete secretary management system, and enhanced security features for a complete healthcare management solution.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Management Features -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-cash-stack me-2"></i>
                        Advanced Financial Management Features
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 mb-4">
                            <div class="financial-feature-card text-center p-4 border rounded">
                                <div class="financial-feature-icon bg-emerald text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #10b981 !important;">
                                    <i class="bi bi-cash-stack" style="font-size: 1.5rem;"></i>
                                </div>
                                <h5 class="fw-bold mb-3">Daily Balance Management</h5>
                                <p class="text-muted mb-0">Complete daily financial tracking with opening balance, additional balance, withdrawals, and automated balance calculations for comprehensive financial control.</p>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <div class="financial-feature-card text-center p-4 border rounded">
                                <div class="financial-feature-icon bg-blue text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #3b82f6 !important;">
                                    <i class="bi bi-file-earmark-excel" style="font-size: 1.5rem;"></i>
                                </div>
                                <h5 class="fw-bold mb-3">Professional Excel Export</h5>
                                <p class="text-muted mb-0">Advanced Excel export with Arabic RTL support, conditional formatting, color-coded transactions, and comprehensive financial reporting using PhpSpreadsheet.</p>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <div class="financial-feature-card text-center p-4 border rounded">
                                <div class="financial-feature-icon bg-orange text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #f97316 !important;">
                                    <i class="bi bi-graph-up-arrow" style="font-size: 1.5rem;"></i>
                                </div>
                                <h5 class="fw-bold mb-3">Real-time Financial Dashboard</h5>
                                <p class="text-muted mb-0">Live financial dashboard with current balance tracking, transaction summaries, and comprehensive financial analytics for better decision making.</p>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-success border-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-lightbulb text-success me-3" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <h6 class="alert-heading mb-1">Revolutionary Financial Control</h6>
                                        <p class="mb-0">Version 4.0 introduces comprehensive financial management with daily balance tracking, advanced Excel export capabilities, real-time dashboards, and automated daily closure system for complete financial control and transparency in healthcare management.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Smart Drug Features -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-capsule me-2"></i>
                        Smart Drug Management Features
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 mb-4">
                            <div class="drug-feature-card text-center p-4 border rounded">
                                <div class="drug-feature-icon bg-coral text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #ff6b6b !important;">
                                    <i class="bi bi-capsule" style="font-size: 1.5rem;"></i>
                                </div>
                                <h5 class="fw-bold mb-3">Smart Drug Suggestions</h5>
                                <p class="text-muted mb-0">Intelligent badges showing most used medications with usage counts, enabling one-click prescription filling for enhanced workflow efficiency.</p>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <div class="drug-feature-card text-center p-4 border rounded">
                                <div class="drug-feature-icon bg-mint text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #00d4aa !important;">
                                    <i class="bi bi-search" style="font-size: 1.5rem;"></i>
                                </div>
                                <h5 class="fw-bold mb-3">Advanced Autocomplete</h5>
                                <p class="text-muted mb-0">Real-time drug search with intelligent suggestions after 3 characters, comprehensive drug database integration for accurate medication selection.</p>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <div class="drug-feature-card text-center p-4 border rounded">
                                <div class="drug-feature-icon bg-gold text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #ffd700 !important;">
                                    <i class="bi bi-info-circle" style="font-size: 1.5rem;"></i>
                                </div>
                                <h5 class="fw-bold mb-3">Comprehensive Drug Info</h5>
                                <p class="text-muted mb-0">Complete drug information system with manufacturer details, active ingredients, pricing information, and detailed drug profiles.</p>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info border-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-lightbulb text-info me-3" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <h6 class="alert-heading mb-1">Enhanced Prescription Workflow</h6>
                                        <p class="mb-0">The new drug management system streamlines the prescription process with smart suggestions, autocomplete features, and comprehensive drug information, making medication prescribing faster, more accurate, and more efficient for healthcare providers.</p>
                                    </div>
                                </div>
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
                        What's New in Version 4.0
                    </h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-emerald text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #10b981 !important;">
                                    <i class="bi bi-cash-stack" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Daily Financial Management System</h6>
                                    <p class="text-muted mb-0">Complete daily financial tracking with opening balance, additional balance, withdrawals, expenses, and automated daily closure system for comprehensive financial control and accountability.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-blue text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #3b82f6 !important;">
                                    <i class="bi bi-file-earmark-excel" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Advanced Excel Export with PhpSpreadsheet</h6>
                                    <p class="text-muted mb-0">Professional Excel export with Arabic RTL support, conditional formatting, color-coded transactions, alternating row colors, and comprehensive financial reporting using PhpSpreadsheet library.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-purple text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #8b5cf6 !important;">
                                    <i class="bi bi-person-badge" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Complete Secretary Management System</h6>
                                    <p class="text-muted mb-0">Full secretary interface with profile management, financial access, patient management, role-based permissions, and comprehensive secretary dashboard for enhanced workflow efficiency.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-orange text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #f97316 !important;">
                                    <i class="bi bi-graph-up-arrow" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Real-time Financial Dashboard</h6>
                                    <p class="text-muted mb-0">Live financial dashboard with current balance tracking, transaction summaries, comprehensive financial analytics, and real-time updates for better decision making and financial control.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-teal text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #14b8a6 !important;">
                                    <i class="bi bi-calendar-x" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Automated Daily Closure System</h6>
                                    <p class="text-muted mb-0">Automated daily closure with financial summaries, balance verification, comprehensive daily reports, and complete financial control for enhanced accountability and transparency.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-rose text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #f43f5e !important;">
                                    <i class="bi bi-shield-lock" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Enhanced Security & Validation</h6>
                                    <p class="text-muted mb-0">Advanced security features with CSRF protection, comprehensive input validation, secure file management, role-based access control, and enhanced authentication for maximum data protection.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-violet text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #8b5cf6 !important;">
                                    <i class="bi bi-building" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Advanced Clinic Settings</h6>
                                    <p class="text-muted mb-0">Comprehensive clinic configuration with Arabic/English names, logo management, watermark settings, website integration, and dynamic print templates for complete branding control.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-rose text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #f43f5e !important;">
                                    <i class="bi bi-image" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Logo & Branding Management</h6>
                                    <p class="text-muted mb-0">Advanced logo upload system with preview functionality, multiple logo types (clinic, print, watermark), secure file management with .htaccess protection, and real-time preview capabilities.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-amber text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #f59e0b !important;">
                                    <i class="bi bi-file-earmark-pdf" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Dynamic Print Templates</h6>
                                    <p class="text-muted mb-0">All print templates now use dynamic clinic information from settings, including Arabic names, logos, contact details, and customizable branding elements for professional document generation.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-teal text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #20c997 !important;">
                                    <i class="bi bi-shield-lock" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Enhanced Security Features</h6>
                                    <p class="text-muted mb-0">Advanced security with CSRF protection, secure file uploads, .htaccess file protection, comprehensive input validation, and secure file management for maximum data protection.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-indigo text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #6366f1 !important;">
                                    <i class="bi bi-gear-fill" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Doctor Settings Access</h6>
                                    <p class="text-muted mb-0">Extended settings functionality to doctor role with complete access to clinic configuration, visit cost management, and system settings for enhanced workflow control.</p>
                                </div>
                            </div>
                        </div>
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
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-indigo text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #6366f1 !important;">
                                    <i class="bi bi-speedometer2" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Admin Dashboard System</h6>
                                    <p class="text-muted mb-0">Complete admin dashboard with system statistics, user management, reports generation, system health monitoring, and quick actions for administrators.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-rose text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #f43f5e !important;">
                                    <i class="bi bi-people-fill" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Advanced User Management</h6>
                                    <p class="text-muted mb-0">Comprehensive user management with role-based access control, user creation/editing/deletion, search functionality, and pagination for better performance.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-emerald text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #10b981 !important;">
                                    <i class="bi bi-graph-up" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Enhanced Reporting System</h6>
                                    <p class="text-muted mb-0">Advanced reporting with user statistics, appointment analytics, financial reports, data export capabilities, and interactive charts for better insights.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-amber text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #f59e0b !important;">
                                    <i class="bi bi-gear-fill" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">System Settings Panel</h6>
                                    <p class="text-muted mb-0">Complete system configuration interface with clinic information management, notification settings, maintenance mode, and backup frequency controls.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-cyan text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #06b6d4 !important;">
                                    <i class="bi bi-funnel" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Doctor Filter for Patients</h6>
                                    <p class="text-muted mb-0">Advanced filtering system allowing patients to be filtered by assigned doctor with real-time search, dropdown selection, and dynamic result updates.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-slate text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #64748b !important;">
                                    <i class="bi bi-palette" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Enhanced Dark/Light Mode</h6>
                                    <p class="text-muted mb-0">Improved theme system with consistent dark and light mode support across all admin pages, better color variables, and enhanced user experience.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-violet text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #8b5cf6 !important;">
                                    <i class="bi bi-fonts" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Font Awesome Integration</h6>
                                    <p class="text-muted mb-0">Complete Font Awesome 6.4.0 integration with consistent icons across all admin interfaces, improved visual consistency, and better user experience.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-coral text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #ff6b6b !important;">
                                    <i class="bi bi-capsule" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Smart Drug Suggestions</h6>
                                    <p class="text-muted mb-0">Intelligent drug suggestion system with clickable badges showing most used medications, usage counts, and one-click prescription filling for enhanced workflow efficiency.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-mint text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #00d4aa !important;">
                                    <i class="bi bi-search" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Advanced Drug Autocomplete</h6>
                                    <p class="text-muted mb-0">Real-time drug search with autocomplete functionality, intelligent suggestions after 3 characters, and comprehensive drug database integration for accurate medication selection.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-gold text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #ffd700 !important;">
                                    <i class="bi bi-info-circle" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Comprehensive Drug Search</h6>
                                    <p class="text-muted mb-0">Complete drug information system with manufacturer details, active ingredients, pricing information, and detailed drug profiles for informed medical decisions.</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-lavender text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px; background-color: #b19cd9 !important;">
                                    <i class="bi bi-lightning-charge" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Enhanced Prescription Workflow</h6>
                                    <p class="text-muted mb-0">Streamlined prescription management with smart suggestions, autocomplete features, and optimized UI for faster, more accurate medication prescribing.</p>
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

/* Version 3.0 New Features Styling */
.new-feature-card {
    transition: all 0.3s ease;
    background-color: var(--card);
    border-color: var(--border) !important;
}

.new-feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border-color: var(--accent) !important;
}

.dark .new-feature-card:hover {
    box-shadow: 0 10px 30px rgba(255, 255, 255, 0.05);
}

.new-feature-icon {
    transition: all 0.3s ease;
}

.new-feature-card:hover .new-feature-icon {
    transform: scale(1.1);
}

/* Financial Management Features Styling */
.financial-feature-card {
    transition: all 0.3s ease;
    background-color: var(--card);
    border-color: var(--border) !important;
}

.financial-feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border-color: var(--accent) !important;
}

.dark .financial-feature-card:hover {
    box-shadow: 0 10px 30px rgba(255, 255, 255, 0.05);
}

.financial-feature-icon {
    transition: all 0.3s ease;
}

.financial-feature-card:hover .financial-feature-icon {
    transform: scale(1.1);
}

/* Smart Drug Features Styling */
.drug-feature-card {
    transition: all 0.3s ease;
    background-color: var(--card);
    border-color: var(--border) !important;
}

.drug-feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border-color: var(--accent) !important;
}

.dark .drug-feature-card:hover {
    box-shadow: 0 10px 30px rgba(255, 255, 255, 0.05);
}

.drug-feature-icon {
    transition: all 0.3s ease;
}

.drug-feature-card:hover .drug-feature-icon {
    transform: scale(1.1);
}

/* Alert styling for new features */
.alert-primary {
    background-color: rgba(13, 110, 253, 0.1) !important;
    border-color: rgba(13, 110, 253, 0.2) !important;
    color: var(--text) !important;
}

.dark .alert-primary {
    background-color: rgba(13, 110, 253, 0.05) !important;
    border-color: rgba(13, 110, 253, 0.1) !important;
}

/* Alert styling for drug features */
.alert-info {
    background-color: rgba(13, 202, 240, 0.1) !important;
    border-color: rgba(13, 202, 240, 0.2) !important;
    color: var(--text) !important;
}

.dark .alert-info {
    background-color: rgba(13, 202, 240, 0.05) !important;
    border-color: rgba(13, 202, 240, 0.1) !important;
}
</style>