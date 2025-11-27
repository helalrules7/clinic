<!-- ============================================
   Calendar Page Styles   Overrides the default styles(from layouts/style.css)
   ============================================ -->
<style>

    /* CSS Variables for Dark Mode */
:root {
    --bg: #f8fafc;
    --text: #0f172a;
    --card: #ffffff;
    --muted: #475569;
    --accent: #0ea5e9;
    --success: #10b981;
    --danger: #ef4444;
    --border: #e2e8f0;
    --shadow: rgba(0, 0, 0, 0.1);
    --success-rgb: 16, 185, 129;
}

.dark {
    --bg: #0b1220;
    --text: #f8fafc;
    --card: #1e293b;
    --muted: #94a3b8;
    --accent: #38bdf8;
    --success: #4ade80;
    --danger: #fb7185;
    --border: #334155;
    --shadow: rgba(0, 0, 0, 0.3);
    --success-rgb: 74, 222, 128;
}

/* Calendar Grid Styles */
.calendar-grid {
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    background: var(--card);
}

.calendar-header {
    display: grid;
    grid-template-columns: 120px 1fr;
    background: var(--bg);
    border-bottom: 2px solid var(--border);
    font-weight: 600;
}

.calendar-header > div {
    padding: 1rem;
    border-right: 1px solid var(--border);
    color: var(--text);
}

/* Dark Mode Calendar Styles */
.dark .calendar-header {
    background: var(--bg);
    color: var(--text);
}

.dark .calendar-grid {
    background: var(--card);
    border-color: var(--border);
}

.calendar-row {
    display: grid;
    grid-template-columns: 120px 1fr;
    border-bottom: 1px solid var(--border);
    min-height: 80px;
}

.calendar-row:last-child {
    border-bottom: none;
}

.time-slot {
    padding: 1rem;
    border-right: 1px solid var(--border);
    background: var(--bg);
    display: flex;
    align-items: center;
    font-weight: 500;
    color: var(--text);
}

.appointment-slot {
    padding: 0.5rem;
    display: flex;
    align-items: center;
}
    
.appointment-card {
    width: 100%;
    padding: 0.75rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid var(--border) !important;
    background: var(--card) !important;
    color: var(--text);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Highlighted Appointment Styles */
.appointment-card.highlighted-appointment {
    border: 3px solid var(--accent);
    box-shadow: 0 0 20px rgba(14, 165, 233, 0.5), 0 0 40px rgba(14, 165, 233, 0.3);
    animation: pulseHighlight 2s ease-in-out infinite;
    position: relative;
    z-index: 10;
}

#prevDayBtn, #nextDayBtn, #todayBtn{
        margin-right: 1px !important;
        margin-left: 10px !important;
        border-right-color:rgb(56, 189, 248) !important;
        border-left-color:rgb(56, 189, 248) !important;
    }

.appointment-card.highlighted-appointment::before {
    content: '';
    position: absolute;
    top: -3px;
    left: -3px;
    right: -3px;
    bottom: -3px;
    border-radius: 8px;
    background: linear-gradient(45deg, var(--accent), var(--success), var(--accent));
    background-size: 200% 200%;
    animation: gradientShift 3s ease infinite;
    z-index: -1;
    opacity: 0.6;
}

@keyframes pulseHighlight {
    0%, 100% {
        box-shadow: 0 0 20px rgba(14, 165, 233, 0.5), 0 0 40px rgba(14, 165, 233, 0.3);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 30px rgba(14, 165, 233, 0.7), 0 0 60px rgba(14, 165, 233, 0.5);
        transform: scale(1.02);
    }
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

/* Dark Mode Highlighted Appointment */
.dark .appointment-card.highlighted-appointment {
    border-color: var(--accent);
    box-shadow: 0 0 20px rgba(56, 189, 248, 0.6), 0 0 40px rgba(56, 189, 248, 0.4);
}

.dark .appointment-card.highlighted-appointment::before {
    background: linear-gradient(45deg, var(--accent), var(--success), var(--accent));
    opacity: 0.4;
}

/* Dark Mode Appointment Cards */
.dark .appointment-card {
    background: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.dark .appointment-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    transform: translateY(-2px);
}

.appointment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-color: var(--accent) !important;
}

.appointment-card.booked {
    border-left: 4px solid var(--accent);
}

.appointment-card.checkedin {
    border-left: 4px solid #17a2b8;
}

.appointment-card.inprogress {
    border-left: 4px solid #ffc107;
}

.appointment-card.completed {
    border-left: 4px solid var(--success);
}

.appointment-card.cancelled {
    border-left: 4px solid var(--danger);
    opacity: 0.7;
}

.appointment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
    padding: 0.5rem;
    border-radius: 6px;
    background: linear-gradient(135deg, var(--accent) 0%, var(--success) 100%);
    margin: -0.5rem -0.5rem 0.5rem -0.5rem;
    padding: 0.75rem 0.5rem;
}

.appointment-header:hover {
    background: linear-gradient(135deg, var(--success) 0%, var(--accent) 100%);
}

.appointment-header .appointment-info {
    flex: 1;
    cursor: pointer;
}

.appointment-header .appointment-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.appointment-header .appointment-info .info-line {
    font-size: 0.85em;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
}

.appointment-header .appointment-info .info-line .label {
    font-weight: 600;
    min-width: 55px;
    margin-right: 4px;
    color: rgba(255, 255, 255, 0.85);
}

.patient-name {
    font-weight: 600;
    color: var(--text);
}

/* Dark Mode Text Colors */
.dark .patient-name {
    color: var(--text);
}

.dark .appointment-header {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.2) 0%, rgba(74, 222, 128, 0.2) 100%);
    border: 1px solid var(--border);
}

.dark .appointment-header .appointment-info .info-line .label {
    color: var(--muted);
}

/* Light Mode Appointment Header - Enhanced text colors for gradient background */
.appointment-header .appointment-info .info-line {
    color: rgba(255, 255, 255, 0.95);
}

.appointment-header .appointment-info .info-line span:not(.label) {
    color: rgba(255, 255, 255, 0.95);
    font-weight: 500;
}

.appointment-header .appointment-info .patient-name {
    color: rgba(255, 255, 255, 0.95);
    font-weight: 600;
}

.dark .appointment-notes {
    color: var(--muted);
}

.appointment-details {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.appointment-notes {
    font-size: 0.875rem;
    color: var(--muted);
    line-height: 1.4;
}

.available-slot {
    color: var(--success);
    text-align: center;
    width: 100%;
    cursor: pointer;
    padding: 0.75rem;
    border-radius: 6px;
    border: 2px dashed var(--success);
    background: rgba(var(--success-rgb), 0.05);
    transition: all 0.2s ease;
    font-weight: 500;
}

.available-slot:hover {
    background: rgba(var(--success-rgb), 0.15);
    border-color: var(--accent);
    color: var(--accent);
    transform: translateY(-1px);
}

/* Dark Mode Available Slots */
.dark .available-slot {
    color: var(--success);
    border-color: var(--success);
    background: rgba(var(--success-rgb), 0.1);
}

.dark .available-slot:hover {
    background: rgba(var(--success-rgb), 0.2);
    border-color: var(--accent);
    color: var(--accent);
}

.unavailable-slot {
    background: linear-gradient(135deg, #dc3545, #b02a37);
    color: white;
    padding: 10px 12px;
    border-radius: 6px;
    text-align: center;
    font-size: 0.85em;
    line-height: 1.4;
    width: 100%;
    font-weight: 500;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.unavailable-slot.outside-hours {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: #f8f9fa;
}

.unavailable-slot.reserved-slot {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    padding: 8px 10px;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.unavailable-slot.reserved-slot .slot-details {
    flex: 1;
    text-align: left;
    line-height: 1.3;
}

.unavailable-slot.reserved-slot .info-line {
    font-size: 0.8em;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
}

.unavailable-slot.reserved-slot .info-line .label {
    font-weight: 600;
    min-width: 60px;
    margin-right: 4px;
    opacity: 0.9;
}

.unavailable-slot.debug-slot {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: #212529;
    padding: 8px 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.unavailable-slot.debug-slot .debug-info {
    flex: 1;
    text-align: left;
    font-size: 0.75em;
    line-height: 1.3;
}

.unavailable-slot.debug-slot .debug-title {
    font-weight: 600;
    margin-bottom: 3px;
    color: #212529;
}

.unavailable-slot.debug-slot .debug-details {
    font-family: 'Courier New', monospace;
    background: rgba(0, 0, 0, 0.1);
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.7em;
    word-break: break-all;
    max-height: 60px;
    overflow-y: auto;
}

.unavailable-slot.official-holiday {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 12px 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.unavailable-slot.official-holiday .holiday-info {
    flex: 1;
    text-align: left;
}

.unavailable-slot.official-holiday .holiday-title {
    font-weight: 600;
    font-size: 0.9em;
    margin-bottom: 2px;
    color: white;
}

.unavailable-slot.official-holiday .holiday-subtitle {
    font-size: 0.75em;
    opacity: 0.9;
    color: rgba(255, 255, 255, 0.9);
}

.unavailable-slot.official-holiday i {
    font-size: 1.1em;
    opacity: 1;
    color: white;
}

.unavailable-slot i {
    font-size: 0.85em;
    opacity: 0.9;
    flex-shrink: 0;
}

.refresh-indicator {
    animation: pulseOnce 0.6s ease;
}

@keyframes pulseOnce {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.status-indicator {
    transition: all 0.3s ease;
}

.modal-content {
    /* Glass effect - similar to sidebar */
    background: rgba(248, 250, 252, 0.35) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(226, 232, 240, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.08);
    border-radius: 12px;
    color: var(--text) !important;
    /* Enable dragging */
    cursor: move;
}

.modal-dialog {
    /* Enable dragging for modal dialog */
    cursor: default;
    transition: transform 0.2s ease;
    margin: 1.75rem auto;
}

.modal-header {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.dark .modal-content {
    background: rgba(11, 18, 32, 0.40) !important;
    border: 1px solid rgba(51, 65, 85, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3);
}

.modal-header {
    background: transparent !important;
    border-bottom: 1px solid rgba(226, 232, 240, 0.3) !important;
    border-radius: 12px 12px 0 0;
    color: var(--text) !important;
}

.dark .modal-header {
    border-bottom-color: rgba(51, 65, 85, 0.3) !important;
}

/* Close button white in dark mode */
.dark .modal-header .btn-close {
    filter: invert(1) brightness(2);
    opacity: 0.9;
}

.dark .modal-header .btn-close:hover {
    opacity: 1;
    filter: invert(1) brightness(2.5);
}

.modal-body {
    background: transparent !important;
    color: var(--text) !important;
}

.modal-footer {
    background: transparent !important;
    border-top: 1px solid rgba(226, 232, 240, 0.3) !important;
}

.dark .modal-footer {
    border-top-color: rgba(51, 65, 85, 0.3) !important;
}

.btn-group .btn {
    border-radius: 6px;
}

.btn-group .btn:not(:last-child) {
    border-right: 1px solid var(--border);
}

@media (max-width: 768px) {
    .calendar-header,
    .calendar-row {
        grid-template-columns: 100px 1fr;
    }
    
    .time-slot {
        padding: 0.75rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .appointment-slot {
        padding: 0.25rem;
    }
    
    .appointment-card {
        padding: 0.5rem;
    }
    
    .appointment-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}

/* Search Results Styles */
.search-results {
    position: relative;
    z-index: 1000;
}

.search-result-item {
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-top: none;
    background: var(--card);
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.search-result-item:first-child {
    border-top: 1px solid var(--border);
    border-radius: 8px 8px 0 0;
}

.search-result-item:last-child {
    border-radius: 0 0 8px 8px;
}

.search-result-item:only-child {
    border-radius: 8px;
}

.search-result-item:hover {
    background: var(--bg);
}

/* Dark Mode Search Results */
.dark .search-result-item {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .search-result-item:hover {
    background: var(--bg);
}

.dark .patient-details {
    color: var(--muted);
}

.patient-name {
    font-weight: 600;
    color: var(--text);
    margin-bottom: 0.25rem;
}

.patient-details {
    font-size: 0.875rem;
    color: var(--muted);
}

/* Modal improvements */
.modal-content {
    border-radius: 12px;
}

.form-label {
    font-weight: 600;
    color: var(--text);
}

/* Form Controls Dark Mode */
.form-control {
    background: var(--card);
    border: 2px solid var(--border);
    color: var(--text);
}

.form-control:focus {
    background: var(--card);
    border-color: var(--accent);
    box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25);
    color: var(--text);
}

.form-select {
    background: var(--card);
    border: 2px solid var(--border);
    color: var(--text);
}

.form-select:focus {
    background: var(--card);
    border-color: var(--accent);
    box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25);
    color: var(--text);
}

.dark .form-control {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .form-control:focus {
    background: var(--card);
    border-color: var(--accent);
    color: var(--text);
}

.dark .form-select {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .form-select:focus {
    background: var(--card);
    border-color: var(--accent);
    color: var(--text);
}

.dark .form-label {
    color: var(--text);
}

.btn-group .btn {
    border-radius: 6px !important;
}

/* Notification styles */
.alert {
    box-shadow: 0 4px 12px var(--shadow);
    border-radius: 8px;
    background: var(--card);
    border: 1px solid var(--border);
    color: var(--text);
}

/* Dark Mode Alert Styles */
.dark .alert {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
    box-shadow: 0 4px 12px var(--shadow);
}

.dark .alert-info {
    background: rgba(56, 189, 248, 0.1);
    border-color: var(--accent);
    color: var(--text);
}

/* Selected patient info */
.selected-patient-info {
    margin-top: 0.5rem;
    border-radius: 8px;
    border: 1px solid #b3d9ff;
    background: rgba(13, 110, 253, 0.1);
}

/* Readonly field styling */
input[readonly] {
    background-color: var(--bg) !important;
    cursor: not-allowed !important;
    opacity: 0.8;
    color: var(--text) !important;
}

/* Dark Mode Readonly Fields */
.dark input[readonly] {
    background-color: var(--bg) !important;
    color: var(--text) !important;
    border-color: var(--border) !important;
}

/* Preselected fields styling */
.preselected-field {
    background-color: rgba(var(--success-rgb), 0.1) !important;
    border-color: var(--success) !important;
    font-weight: 600;
}

.preselected-field:focus {
    box-shadow: 0 0 0 0.2rem rgba(var(--success-rgb), 0.25) !important;
}

/* Custom Tooltip Styling */
.tooltip {
    font-size: 0.875rem;
    max-width: 350px;
}

.tooltip-inner {
    background-color: #2c3e50 !important;
    color: #ffffff !important;
    border-radius: 8px !important;
    padding: 12px 16px !important;
    text-align: left;
    direction: ltr !important;
    box-shadow: 0 4px 12px var(--shadow) !important;
}

/* Dark Mode Tooltips */
.dark .tooltip-inner {
    background-color: var(--card) !important;
    color: var(--text) !important;
    border: 1px solid var(--border) !important;
    box-shadow: 0 4px 12px var(--shadow) !important;
}

/* ============================================
   Appointment Tooltip Styles - Enhanced
   ============================================ */
.appointment-tooltip {
    font-family: 'Cairo', sans-serif !important;
    max-width: 350px;
    padding: 0.5rem;
}

.appointment-tooltip .tooltip-header {
    font-weight: 600;
    font-size: 1rem;
    color: var(--text) !important;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border) !important;
}

.appointment-tooltip .tooltip-body {
    font-size: 0.875rem;
    line-height: 1.6 !important;
}

.appointment-tooltip .tooltip-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
    padding: 0.25rem 0;
}

.appointment-tooltip .tooltip-row:last-child {
    margin-bottom: 0;
}

.appointment-tooltip .tooltip-label {
    font-weight: 600;
    color: var(--muted) !important;
    min-width: 80px;
    margin-right: 8px;
    flex-shrink: 0;
}

.appointment-tooltip .tooltip-value {
    color: var(--text) !important;
    text-align: right;
    flex: 1;
    word-break: break-word;
}

.appointment-tooltip .tooltip-footer {
    border-top: 1px solid var(--border) !important;
    padding-top: 0.5rem;
    margin-top: 0.75rem !important;
    text-align: center !important;
}

.appointment-tooltip .tooltip-footer small {
    color: var(--muted) !important;
    font-size: 0.75rem;
    font-style: italic;
}

/* Dark Mode Tooltip */
.dark .appointment-tooltip .tooltip-header {
    color: var(--text) !important;
    border-bottom-color: var(--border) !important;
}

.dark .appointment-tooltip .tooltip-label {
    color: var(--muted) !important;
}

.dark .appointment-tooltip .tooltip-value {
    color: var(--text) !important;
}

.dark .appointment-tooltip .tooltip-footer {
    border-top-color: var(--border) !important;
}

.dark .appointment-tooltip .tooltip-footer small {
    color: var(--muted) !important;
}

/* Bootstrap Tooltip Container - Enhanced styling */
.tooltip .tooltip-inner {
    background-color: var(--card) !important;
    color: var(--text) !important;
    border: 1px solid var(--border) !important;
    border-radius: 8px !important;
    padding: 0.75rem !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    max-width: 400px !important;
}

.dark .tooltip .tooltip-inner {
    background-color: var(--card) !important;
    color: var(--text) !important;
    border-color: var(--border) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
}

.tooltip .tooltip-arrow::before {
    border-top-color: var(--card) !important;
    border-bottom-color: var(--card) !important;
    border-left-color: var(--card) !important;
    border-right-color: var(--card) !important;
}

.dark .tooltip .tooltip-arrow::before {
    border-top-color: var(--card) !important;
    border-bottom-color: var(--card) !important;
    border-left-color: var(--card) !important;
    border-right-color: var(--card) !important;
}

/* Dark Mode Buttons */
.dark .btn-outline-primary {
    color: var(--accent) !important;
    border-color: var(--accent) !important;
}

.dark .btn-outline-primary:hover {
    background-color: var(--accent) !important;
    border-color: var(--accent) !important;
    color: #0b1220 !important;
}

.dark .btn-success {
    background-color: var(--success) !important;
    border-color: var(--success) !important;
    color: #0b1220 !important;
}

.dark .btn-success:hover {
    background-color: #059669;
    border-color: #059669;
}

.dark .btn-secondary {
    background-color: #64748b;
    border-color: #64748b;
    color: white;
}

.dark .btn-secondary:hover {
    background-color: #475569;
    border-color: #475569;
}

.dark .btn-danger {
    background-color: var(--danger);
    border-color: var(--danger);
    color: white;
}

.dark .btn-warning {
    background-color: #f59e0b;
    border-color: #f59e0b;
    color: #0b1220;
}

/* Dark Mode Cards */
.dark .card {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .card-header {
    background-color: transparent !important;
    border-bottom-color: var(--border);
    color: var(--text);
}

.dark .card-body {
    background-color: transparent !important;
    color: var(--text);
}

/* Status badge styling */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-weight: 500;
}

.badge i {
    font-size: 0.875rem;
}

/* Specific status badge colors */
.badge.bg-success {
    background-color: #198754 !important;
    color: white;
}

.badge.bg-primary {
    background-color: #0d6efd !important;
    color: white;
}

.badge.bg-info {
    background-color: #0dcaf0 !important;
    color: #000;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #000;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
    color: white;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
    color: white;
}

/* Dark Mode Badge Styles */
.dark .badge {
    color: white;
}

.dark .badge.bg-success {
    background-color: var(--success) !important;
}

.dark .badge.bg-primary {
    background-color: var(--accent) !important;
}

.dark .badge.bg-info {
    background-color: #0ea5e9 !important;
}

.dark .badge.bg-warning {
    background-color: #f59e0b !important;
    color: #0b1220 !important;
}

.dark .badge.bg-danger {
    background-color: var(--danger) !important;
}

/* Dark Mode Text Colors */
.dark h4, .dark h5, .dark h6 {
    color: var(--text);
}

.dark .text-muted {
    color: var(--muted) !important;
}

.dark small {
    color: var(--muted);
}

/* Add Patient Modal Styling */
#addPatientModal .modal-content {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

#addPatientModal .modal-header {
    background-color: var(--bg);
    border-bottom-color: var(--border);
    color: var(--text);
}

#addPatientModal .modal-footer {
    background-color: var(--card);
    border-top-color: var(--border);
}

#addPatientModal .form-label {
    color: var(--text);
    font-weight: 500;
}

#addPatientModal .form-control,
#addPatientModal .form-select {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

#addPatientModal .form-control:focus,
#addPatientModal .form-select:focus {
    background-color: var(--card);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

#addPatientModal .form-text {
    color: var(--muted);
    font-size: 0.875rem;
}

#addPatientModal .text-primary {
    color: var(--accent) !important;
}

#addPatientModal .text-danger {
    color: #dc3545 !important;
}

#addPatientModal .invalid-feedback {
    color: #dc3545;
    font-size: 0.875rem;
}

#addPatientModal .form-control.is-invalid,
#addPatientModal .form-select.is-invalid {
    border-color: #dc3545;
}

#addPatientModal .alert {
    border-radius: 8px;
    margin-bottom: 1rem;
}

#addPatientModal .alert-success {
    background-color: rgba(40, 167, 69, 0.1);
    border-color: #28a745;
    color: #155724;
}

#addPatientModal .alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    border-color: #dc3545;
    color: #721c24;
}

/* Keyboard shortcut hint styling */
.keyboard-hint {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 0.75rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 5px;
}

.keyboard-hint kbd {
    background-color: var(--bg-alt);
    border: 1px solid var(--border);
    color: var(--text);
    font-size: 0.65rem;
    padding: 1px 4px;
}

/* Form validation styling */
.was-validated .form-control:valid {
    border-color: #28a745;
}

.was-validated .form-control:invalid {
    border-color: #dc3545;
}

.was-validated .form-select:valid {
    border-color: #28a745;
}

.was-validated .form-select:invalid {
    border-color: #dc3545;
}

/* Spinner styling */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.1em;
}

/* View Patient Profile button styling */
/* ============================================
   Appointment Card Buttons - Enhanced with proper borders
   ============================================ */
.view-patient-btn {
    padding: 0.375rem 0.75rem !important;
    font-size: 0.875rem !important;
    border-radius: 6px !important;
    border: 1px solid white !important;
    transition: all 0.2s ease !important;
    opacity: 0.9;
    color:white !important;
    background-color: transparent !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-decoration: none !important;
}

.view-patient-btn:hover {
    opacity: 1 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 8px rgba(255, 255, 255, 0.3) !important;
    background-color:rgb(255, 255, 255) !important;
    border-color:rgb(255, 255, 255) !important;
    color: black !important;
}

.view-patient-btn:hover i {
    color: black !important;
}

.appointment-card:hover .view-patient-btn {
    opacity: 1;
}

/* Dark mode view patient button */
.dark .view-patient-btn {
    color: #0ea5e9 !important;
    border-color: #0ea5e9 !important;
    background-color: transparent !important;
}

.dark .view-patient-btn:hover {
    background-color: #0ea5e9 !important;
    border-color: #0ea5e9 !important;
    color: white !important;
}

.dark .view-patient-btn:hover i {
    color: white !important;
}

/* Delete appointment button styling - Enhanced with proper borders */
.delete-appointment-btn {
    padding: 0.375rem 0.75rem !important;
    font-size: 0.875rem !important;
    border-radius: 6px !important;
    border: 1px solid var(--danger) !important;
    transition: all 0.2s ease !important;
    opacity: 0.9;
    background-color: transparent !important;
    color: var(--danger) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.delete-appointment-btn:hover {
    opacity: 1 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3) !important;
    background-color: var(--danger) !important;
    border-color: var(--danger) !important;
    color: white !important;
}

.delete-appointment-btn:hover i {
    color: white !important;
}

.appointment-card:hover .delete-appointment-btn {
    opacity: 1;
}

/* Dark mode delete button */
.dark .delete-appointment-btn {
    background-color: transparent !important;
    border-color: var(--danger) !important;
    color: var(--danger) !important;
}

.dark .delete-appointment-btn:hover {
    background-color: var(--danger) !important;
    border-color: var(--danger) !important;
    color: white !important;
}

.dark .delete-appointment-btn:hover i {
    color: white !important;
}

/* Delete Appointment Modal Styling */
#deleteAppointmentModal .modal-content {
    background-color: var(--card);
    color: var(--text);
}

#deleteAppointmentModal .modal-header {
    background-color: #dc3545 !important;
    border-bottom-color: #dc3545;
}

#deleteAppointmentModal .modal-footer {
    background-color: var(--card);
    border-top-color: var(--border);
}

#deleteAppointmentModal .alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    border-color: #dc3545;
    color: #721c24;
}

[data-bs-theme="dark"] #deleteAppointmentModal .alert-danger {
    background-color: rgba(220, 53, 69, 0.15);
    color: #f5c6cb;
}

#deleteAppointmentModal .list-group-item {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

#deleteAppointmentModal .card {
    background-color: var(--card);
    border-color: #ffc107;
}

#deleteAppointmentModal .card-body {
    background-color: var(--bg);
}

/* Date Picker Tooltip with Glass Effect */
.date-picker-tooltip {
    min-width: 250px;
    padding: 0;
}

.date-picker-tooltip .form-label {
    color: var(--text);
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
    display: block;
}

.date-picker-tooltip .form-control {
    background: rgba(255, 255, 255, 0.9);
    border: 2px solid var(--border);
    border-radius: 8px;
    padding: 0.5rem;
    color: var(--text);
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.date-picker-tooltip .form-control:focus {
    background: rgba(255, 255, 255, 0.95);
    border-color: var(--accent);
    box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
    outline: none;
}

.date-picker-tooltip .btn {
    margin-top: 0.75rem;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.date-picker-tooltip .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Dark Mode Date Picker Tooltip */
.dark .date-picker-tooltip .form-control {
    background: rgba(30, 41, 59, 0.9);
    border-color: var(--border);
    color: var(--text);
}

.dark .date-picker-tooltip .form-control:focus {
    background: rgba(30, 41, 59, 0.95);
    border-color: var(--accent);
}

/* Custom Popover Styling for Date Picker */
.popover:not(.mobile-filter-popover) {
    background: rgba(11, 18, 32, 0.40) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(51, 65, 85, 0.3) !important;
    border-radius: 12px !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3) !important;
    color: var(--text) !important;
    max-width: 300px;
}

.dark .popover:not(.mobile-filter-popover) {
    background: rgba(11, 18, 32, 0.40) !important;
    border: 1px solid rgba(51, 65, 85, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3) !important;
}

.popover .popover-body {
    padding: 1rem;
    color: var(--text);
    background: rgba(11, 18, 32, 0.40) !important;
    border: 1px solid rgba(51, 65, 85, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3) !important;
}

.popover .popover-arrow::before {
    border-bottom-color: rgba(248, 250, 252, 0.3) !important;
}

.dark .popover .popover-arrow::before {
    border-bottom-color: rgba(30, 41, 59, 0.3) !important;
}

.popover .popover-arrow::after {
    border-bottom-color: rgba(248, 250, 252, 0.4) !important;
}

.dark .popover .popover-arrow::after {
    border-bottom-color: rgba(30, 41, 59, 0.4) !important;
}

/* Filter Buttons Styles */
/* ============================================
   Filter Time Buttons - Enhanced with proper borders
   ============================================ */
.filter-time-btn {
    border: 1px solid var(--border) !important;
    border-radius: 6px !important;
    transition: all 0.2s ease !important;
    font-weight: 500;
    padding: 0.375rem 0.75rem !important;
}

.filter-time-btn.btn-outline-info {
    border-color: #0ea5e9 !important;
    color: #0ea5e9 !important;
    background-color: transparent !important;
}

.filter-time-btn.btn-outline-info:hover {
    background-color: #0ea5e9 !important;
    border-color: #0ea5e9 !important;
    color: white !important;
}

.filter-time-btn.btn-outline-success {
    border-color: var(--success) !important;
    color: var(--success) !important;
    background-color: transparent !important;
}

.filter-time-btn.btn-outline-success:hover {
    background-color: var(--success) !important;
    border-color: var(--success) !important;
    color: white !important;
}

.filter-time-btn.btn-outline-primary {
    border-color: var(--accent) !important;
    color: var(--accent) !important;
    background-color: transparent !important;
}

.filter-time-btn.btn-outline-primary:hover {
    background-color: var(--accent) !important;
    border-color: var(--accent) !important;
    color: white !important;
}

.filter-time-btn.btn-outline-warning {
    border-color: #fbbf24 !important;
    color: #fbbf24 !important;
    background-color: transparent !important;
}

.filter-time-btn.btn-outline-warning:hover {
    background-color: #fbbf24 !important;
    border-color: #fbbf24 !important;
    color: white !important;
}

.filter-time-btn.btn-secondary {
    border-color: var(--muted) !important;
    color: var(--muted) !important;
    background-color: transparent !important;
}

.filter-time-btn.btn-secondary:hover {
    background-color: var(--muted) !important;
    border-color: var(--muted) !important;
    color: white !important;
}

.filter-time-btn:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
}

.filter-time-btn.active {
    font-weight: 600 !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15) !important;
}

.filter-time-btn.active.btn-outline-primary {
    background-color: var(--accent) !important;
    border-color: var(--accent) !important;
    color: white !important;
}

.filter-time-btn.active.btn-outline-info {
    background-color: #0ea5e9 !important;
    border-color: #0ea5e9 !important;
    color: white !important;
}

.filter-time-btn.active.btn-outline-success {
    background-color: var(--success) !important;
    border-color: var(--success) !important;
    color: white !important;
}

.filter-time-btn.active.btn-outline-secondary {
    background-color: var(--muted) !important;
    border-color: var(--muted) !important;
    color: white !important;
}

.filter-time-btn.active.btn-outline-warning {
    background-color: #fbbf24 !important;
    border-color: #fbbf24 !important;
    color: white !important;
}

.filter-time-btn.active.btn-secondary {
    background-color: #6b7280 !important;
    border-color: #6b7280 !important;
    color: white !important;
}

/* Dark Mode Filter Buttons */
.dark .filter-time-btn.btn-outline-info {
    border-color: #0ea5e9 !important;
    color: #0ea5e9 !important;
}

.dark .filter-time-btn.btn-outline-info:hover,
.dark .filter-time-btn.active.btn-outline-info {
    background-color: #0ea5e9 !important;
    border-color: #0ea5e9 !important;
    color: white !important;
}

.dark .filter-time-btn.btn-outline-success {
    border-color: var(--success) !important;
    color: var(--success) !important;
}

.dark .filter-time-btn.btn-outline-success:hover,
.dark .filter-time-btn.active.btn-outline-success {
    background-color: var(--success) !important;
    border-color: var(--success) !important;
    color: white !important;
}

.dark .filter-time-btn.btn-outline-primary {
    border-color: var(--accent) !important;
    color: var(--accent) !important;
}

.dark .filter-time-btn.btn-outline-primary:hover,
.dark .filter-time-btn.active.btn-outline-primary {
    background-color: var(--accent) !important;
    border-color: var(--accent) !important;
    color: white !important;
}

.dark .filter-time-btn.btn-outline-warning {
    border-color: #fbbf24 !important;
    color: #fbbf24 !important;
}

.dark .filter-time-btn.btn-outline-warning:hover,
.dark .filter-time-btn.active.btn-outline-warning {
    background-color: #fbbf24 !important;
    border-color: #fbbf24 !important;
    color: white !important;
}

.dark .filter-time-btn.btn-secondary {
    border-color: var(--muted) !important;
    color: var(--muted) !important;
}

.dark .filter-time-btn.btn-secondary:hover,
.dark .filter-time-btn.active.btn-secondary {
    background-color: var(--muted) !important;
    border-color: var(--muted) !important;
    color: white !important;
}

/* ============================================
   Navigation Buttons - Enhanced with proper borders
   ============================================ */
#todayBtn, #prevDayBtn, #nextDayBtn {
    border: 1px solid var(--accent) !important;
    border-radius: 6px !important;
    color: var(--accent) !important;
    background-color: transparent !important;
    transition: all 0.2s ease !important;
    padding: 0.375rem 0.75rem !important;
}

#todayBtn:hover, #prevDayBtn:hover, #nextDayBtn:hover {
    background-color: var(--accent) !important;
    color: white !important;
    border-color: var(--accent) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 4px rgba(14, 165, 233, 0.2) !important;
}

.dark #todayBtn, .dark #prevDayBtn, .dark #nextDayBtn {
    border-color: var(--accent) !important;
    color: var(--accent) !important;
}

.dark #todayBtn:hover, .dark #prevDayBtn:hover, .dark #nextDayBtn:hover {
    background-color: var(--accent) !important;
    color: white !important;
    border-color: var(--accent) !important;
}

/* Mobile Filter Popover Glass Effect */
.popover.mobile-filter-popover-glass {
    background: rgba(248, 250, 252, 0.35) !important;
    backdrop-filter: blur(10px) !important;
    -webkit-backdrop-filter: blur(10px) !important;
    border: 1px solid rgba(226, 232, 240, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.08) !important;
    border-radius: 12px !important;
    color: var(--text) !important;
}

.popover.mobile-filter-popover-glass .popover-body {
    background: transparent !important;
    padding: 1rem;
}

.popover.mobile-filter-popover-glass .mobile-filter-popover h6 {
    color: var(--text);
    font-weight: 600;
    margin-bottom: 1rem;
}

.popover.mobile-filter-popover-glass .mobile-filter-popover .btn {
    margin-bottom: 0.5rem;
}

.popover.mobile-filter-popover-glass .mobile-filter-popover .btn:last-child {
    margin-bottom: 0;
}

/* Dark Mode Mobile Filter Popover */
.dark .popover.mobile-filter-popover-glass {
    background: rgba(11, 18, 32, 0.6) !important;
    border: 1px solid rgba(51, 65, 85, 0.3) !important;
    color: var(--text) !important;
}

.dark .popover.mobile-filter-popover-glass .popover-body {
    background: transparent !important;
}

/* Ensure popover appears above other elements on mobile */
@media (max-width: 767.98px) {
    .popover {
        z-index: 1060 !important;
        max-width: calc(100vw - 2rem);
        margin: 0 1rem;
    }
    
    .popover .popover-body {
        padding: 1rem;
    }
}
</style>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="d-flex align-items-center">
            <h4 class="mb-0 me-3">Calendar</h4>
            <div class="d-flex align-items-center ms-3">
                <label class="form-label mb-0 me-2" for="calendarAutoRefresh">
                    <small class="text-muted">Auto-refresh</small>
                </label>
                <div class="toggle-switch-wrapper">
                    <input type="checkbox" class="toggle-switch" id="calendarAutoRefresh" 
                           onchange="toggleCalendarAutoRefresh(this.checked)">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 text-end">
        <div class="d-flex gap-2 justify-content-end flex-wrap">
            <button type="button" class="btn btn-info" id="goToDateBtn" 
                    data-bs-toggle="popover" 
                    data-bs-placement="bottom" 
                    data-bs-html="true"
                    data-bs-content="<div class='date-picker-tooltip'><label class='form-label mb-2'>Select Date:</label><input type='date' id='tooltipDatePicker' class='form-control'><button class='btn btn-sm btn-outline-info w-100 mt-2' onclick='goToSelectedDate()'>Go to Date</button></div>"
                    data-bs-trigger="click">
                <i class="bi bi-calendar-event me-1"></i>
                Go to Date
            </button>
            <button type="button" class="btn btn-success" id="addAppointmentBtn">
                <i class="bi bi-plus-circle me-2"></i>
                Add Appointment
            </button>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary" id="todayBtn">Today</button>
                <button type="button" class="btn btn-outline-primary" id="prevDayBtn">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-outline-primary" id="nextDayBtn">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <!-- Desktop Filters -->
        <div class="d-none d-md-flex gap-2 flex-wrap align-items-center">
            <span class="text-muted me-2"><i class="bi bi-funnel me-1"></i>Filter Times:</span>
            <button type="button" class="btn btn-sm btn-outline-info filter-time-btn" data-filter="2pm-6pm" id="filter2pm6pm">
                <i class="bi bi-clock me-1"></i>
                2:00 PM - 6:00 PM
            </button>
            <button type="button" class="btn btn-sm btn-outline-success filter-time-btn" data-filter="6pm-1045pm" id="filter6pm1045pm">
                <i class="bi bi-clock me-1"></i>
                6:00 PM - 10:45 PM
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary filter-time-btn" data-filter="available" id="filterAvailable">
                <i class="bi bi-check-circle me-1"></i>
                Available Only
            </button>
            <button type="button" class="btn btn-sm btn-outline-warning filter-time-btn" data-filter="unavailable" id="filterUnavailable">
                <i class="bi bi-x-circle me-1"></i>
                Appointments Only
            </button>
            <button type="button" class="btn btn-sm btn-secondary filter-time-btn" data-filter="none" id="filterNone">
                <i class="bi bi-x-lg me-1"></i>
                Clear Filters
            </button>
        </div>
        
        <!-- Mobile Filter Button -->
        <div class="d-md-none">
            <button type="button" class="btn btn-sm btn-outline-primary w-100" id="mobileFilterBtn" 
                    data-bs-toggle="popover" 
                    data-bs-placement="bottom" 
                    data-bs-html="true"
                    data-bs-trigger="click"
                    data-bs-content="">
                <i class="bi bi-funnel me-2"></i>
                Filter Times
            </button>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0" id="currentDateDisplay">
                    <?= date('l, F j, Y') ?>
                </h5>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success me-2" id="statusIndicator">
                        <i class="bi bi-circle-fill me-1"></i>
                        Live
                    </span>
                    <small class="text-muted" id="lastUpdate">
                        Last updated: <?= date('H:i:s') ?>
                    </small>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="calendarContainer">
                    <!-- Calendar will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Appointment Details Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="appointmentModalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="startVisitBtn" style="display: none;">
                    <i class="bi bi-play-circle me-2"></i>
                    Start Visit
                </button>
                <button type="button" class="btn btn-success" id="completeVisitBtn" style="display: none;">
                    <i class="bi bi-check-circle me-2"></i>
                    Complete
                </button>
                <button type="button" class="btn btn-warning" id="rescheduleBtn" style="display: none;">
                    <i class="bi bi-calendar-event me-2"></i>
                    Reschedule
                </button>
                <button type="button" class="btn btn-danger" id="cancelBtn" style="display: none;">
                    <i class="bi bi-x-circle me-2"></i>
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Appointment Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="cancelForm">
                    <div class="mb-3">
                        <label for="cancellationReason" class="form-label">Cancellation Reason *</label>
                        <textarea class="form-control" id="cancellationReason" name="cancellation_reason" 
                                  rows="3" required placeholder="Please provide a reason for cancellation..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmCancelBtn">
                    <i class="bi bi-x-circle me-2"></i>
                    Confirm Cancellation
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Appointment Modal -->
<div class="modal fade" id="deleteAppointmentModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Delete Appointment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-flex align-items-start" role="alert">
                    <i class="bi bi-shield-exclamation fs-3 me-3"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Warning!</h6>
                        <p class="mb-0">You are about to delete this appointment permanently. This action <strong>cannot be undone</strong>.</p>
                    </div>
                </div>
                
                <div class="appointment-delete-info mb-4">
                    <h6 class="text-danger mb-3">
                        <i class="bi bi-calendar-event me-2"></i>
                        Appointment Details:
                    </h6>
                    <div class="card border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="bi bi-person-circle text-primary" style="font-size: 2rem;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1" id="deleteAppointmentPatientName"></h6>
                                    <small class="text-muted">Time: <span id="deleteAppointmentTime"></span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="deletion-consequences">
                    <h6 class="text-danger mb-3">
                        <i class="bi bi-list-check me-2"></i>
                        The following data will be deleted permanently:
                    </h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-calendar-event text-danger me-2"></i>
                            <span>Appointment details</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-file-medical text-danger me-2"></i>
                            <span>Consultation notes and prescriptions</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-receipt text-danger me-2"></i>
                            <span>Associated payments</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-file-earmark text-danger me-2"></i>
                            <span>Lab tests and results</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteAppointmentBtn">
                    <i class="bi bi-trash me-1"></i>
                    <span class="btn-text">Delete Appointment</span>
                    <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Appointment Modal -->
<div class="modal fade" id="addAppointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAppointmentForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="patientSearch" class="form-label">
                                    Patient * 
                                    <span id="preselectedLabel" class="badge bg-info ms-2" style="display: none;">Pre-selected</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="patientSearch" 
                                           placeholder="Search patient by name or phone..." required>
                                    <button type="button" class="btn btn-outline-primary" id="newPatientBtn">
                                        <i class="bi bi-person-plus"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="selectedPatientId" name="patient_id">
                                <div id="patientSearchResults" class="search-results"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="appointmentDate" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="appointmentDate" name="date" 
                                       min="<?= date('Y-m-d') ?>" required>
                                <div class="form-text text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Cannot select a date before today (Local timezone: Egypt)
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="appointmentTime" class="form-label">Time *</label>
                                <select class="form-select" id="appointmentTime" name="start_time" required>
                                    <option value="">Select time slot...</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="visitType" class="form-label">Visit Type *</label>
                                <select class="form-select" id="visitType" name="visit_type" required>
                                    <option value="">Select visit type...</option>
                                    <option value="New">New Patient</option>
                                    <option value="FollowUp">Follow Up</option>
                                    <option value="Procedure">Procedure</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="appointmentSource" class="form-label">Source</label>
                                <select class="form-select" id="appointmentSource" name="source">
                                    <option value="Walk-in">Walk-in</option>
                                    <option value="Phone">Phone</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="appointmentNotes" class="form-label">Notes</label>
                                <textarea class="form-control" id="appointmentNotes" name="notes" 
                                          rows="3" placeholder="Any additional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="saveAppointmentBtn">
                        <i class="bi bi-check-circle me-2"></i>
                        Save Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2"></i>
                    Add New Patient
                </h5>
                <div class="keyboard-hint">
                    <span>Press</span>
                    <kbd>Esc</kbd>
                    <span>to close</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPatientForm">
                <div class="modal-body">
                    <!-- Success/Error Messages -->
                    <div id="addPatientMessage" class="alert d-none" role="alert"></div>
                    
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-person me-1"></i>
                                Basic Information
                            </h6>
                            
                            <div class="mb-3">
                                <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="age" class="form-label">Age (Years)</label>
                                <input type="number" class="form-control" id="age" name="age" min="0" max="150" placeholder="Enter age in years">
                                <div class="form-text">Alternative: Enter age to automatically calculate date of birth</div>
                            </div>

                            <div class="mb-3">
                                <label for="dob" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" name="dob">
                                <div class="form-text">Patient's date of birth (if empty, today's date will be used)</div>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-telephone me-1"></i>
                                Contact Information
                            </h6>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required maxlength="20">
                                <div class="invalid-feedback"></div>
                                <div class="form-text">Primary contact number</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" maxlength="500"></textarea>
                                <div class="form-text">Home address (optional)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="Male" selected>Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <div class="invalid-feedback"></div>
                                <div class="form-text text-danger"><strong>Required:</strong> Change the gender if needed</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="addPatientSubmit" title="Save patient - Press 'Ctrl+S'">
                        <i class="bi bi-person-plus me-1"></i>
                        <span class="btn-text">Add Patient</span>
                        <small class="ms-2 text-white-50">
                            <kbd style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); font-size: 0.7rem;">Ctrl+S</kbd>
                        </small>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Get server time for Egypt timezone
<?php
date_default_timezone_set('Africa/Cairo');
$serverDate = date('Y-m-d');
$serverDateTime = date('Y-m-d H:i:s');
$serverTimestamp = time();
?>

const SERVER_DATE = '<?= $serverDate ?>';
const SERVER_DATETIME = '<?= $serverDateTime ?>';
const SERVER_TIMESTAMP = <?= $serverTimestamp ?>;

// Initialize currentDate - will be set based on URL parameter or today
let currentDate = new Date();
const today = new Date();
currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 12, 0, 0);

let selectedAppointment = null;
let refreshInterval;
let preselectedPatient = <?= $preselectedPatient ? json_encode($preselectedPatient) : 'null' ?>;
let highlightedAppointmentId = null; // Store appointment ID from URL to highlight
let currentTimeFilter = null; // Current time filter: '2pm-6pm', '6pm-1045pm', 'available', 'unavailable', or null
let calendarData = null; // Store calendar data for filtering

// Function to initialize date from URL parameter
function initializeDateFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    const dateParam = urlParams.get('date');
    const appointmentIdParam = urlParams.get('appointment_id');
    
    // Store appointment ID if provided
    if (appointmentIdParam) {
        highlightedAppointmentId = parseInt(appointmentIdParam);
    }
    
    if (dateParam && /^\d{4}-\d{2}-\d{2}$/.test(dateParam)) {
        // Use date from URL parameter
        const [year, month, day] = dateParam.split('-').map(Number);
        currentDate = new Date(year, month - 1, day, 12, 0, 0);
    } else {
        // Default to today at noon to avoid timezone issues
        const today = new Date();
        currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 12, 0, 0);
    }
}

// Initialize calendar
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date from URL FIRST before loading calendar
    initializeDateFromURL();
    loadCalendar();
    
    // Initialize auto-refresh toggle from localStorage
    const autoRefreshEnabled = getAutoRefreshState();
    const toggleSwitch = document.getElementById('calendarAutoRefresh');
    if (toggleSwitch) {
        toggleSwitch.checked = autoRefreshEnabled;
    }
    
    // Start auto-refresh if enabled
    if (autoRefreshEnabled) {
        startAutoRefresh();
    }
    
    setupEventListeners();
});

function setupEventListeners() {
    // Navigation buttons
    document.getElementById('todayBtn').addEventListener('click', () => {
        const today = new Date();
        currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 12, 0, 0);
        loadCalendar();
    });
    
    document.getElementById('prevDayBtn').addEventListener('click', () => {
        currentDate = new Date(currentDate.getTime() - 24 * 60 * 60 * 1000);
        loadCalendar();
    });
    
    document.getElementById('nextDayBtn').addEventListener('click', () => {
        currentDate = new Date(currentDate.getTime() + 24 * 60 * 60 * 1000);
        loadCalendar();
    });
    
    // Add appointment button
    document.getElementById('addAppointmentBtn').addEventListener('click', () => {
        // Use current date being viewed in calendar
        openAddAppointmentModal(null, currentDate.toISOString().split('T')[0]);
    });
    
    // Patient search
    document.getElementById('patientSearch').addEventListener('input', debounce(searchPatients, 300));
    
    // Date change - load available time slots
    document.getElementById('appointmentDate').addEventListener('change', (e) => {
        const selectedDate = e.target.value;
        
        // Validate selected date
        const validation = validateDateSelection(selectedDate);
        if (!validation.valid) {
            showErrorMessage(validation.message);
            // Reset to server date
            e.target.value = SERVER_DATE;
            return;
        }
        
        // Keep any preselected time when date changes
        const currentSelectedTime = document.getElementById('appointmentTime').value;
        loadAvailableTimeSlots(currentSelectedTime || null);
    });
    
    // Add appointment form submission
    document.getElementById('addAppointmentForm').addEventListener('submit', handleAddAppointment);
    
    // New patient button
    document.getElementById('newPatientBtn').addEventListener('click', () => {
        // Close current modal first
        bootstrap.Modal.getInstance(document.getElementById('addAppointmentModal')).hide();
        
        // Open add patient modal
        setTimeout(() => {
            const addPatientModal = new bootstrap.Modal(document.getElementById('addPatientModal'));
            addPatientModal.show();
        }, 300);
    });
    
    // Time filter buttons
    document.querySelectorAll('.filter-time-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            applyTimeFilter(filter);
        });
    });
    
    // Mobile filter popover
    const mobileFilterBtn = document.getElementById('mobileFilterBtn');
    if (mobileFilterBtn) {
        // Create filter buttons HTML for popover
        const filterButtonsHTML = `
            <div class="mobile-filter-popover">
                <div class="mb-3">
                    <h6 class="mb-3"><i class="bi bi-funnel me-2"></i>Filter Times</h6>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-sm btn-outline-info filter-time-btn w-100" data-filter="2pm-6pm">
                            <i class="bi bi-clock me-1"></i>
                            2:00 PM - 6:00 PM
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success filter-time-btn w-100" data-filter="6pm-1045pm">
                            <i class="bi bi-clock me-1"></i>
                            6:00 PM - 10:45 PM
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary filter-time-btn w-100" data-filter="available">
                            <i class="bi bi-check-circle me-1"></i>
                            Available Only
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning filter-time-btn w-100" data-filter="unavailable">
                            <i class="bi bi-x-circle me-1"></i>
                            Appointments Only
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary filter-time-btn w-100" data-filter="none">
                            <i class="bi bi-x-lg me-1"></i>
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Initialize popover
        const popoverInstance = new bootstrap.Popover(mobileFilterBtn, {
            html: true,
            content: filterButtonsHTML,
            placement: 'bottom',
            trigger: 'click',
            container: 'body',
            sanitize: false
        });
        
        // Add event listener for when popover is shown
        mobileFilterBtn.addEventListener('shown.bs.popover', function() {
            // Attach event listeners to filter buttons inside popover
            const popoverElement = document.querySelector('.popover');
            if (popoverElement) {
                // Add glass effect class to popover
                popoverElement.classList.add('mobile-filter-popover-glass');
                
                // Attach event listeners to filter buttons
                popoverElement.querySelectorAll('.filter-time-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const filter = this.getAttribute('data-filter');
                        applyTimeFilter(filter);
                        // Close popover after selection
                        setTimeout(() => {
                            popoverInstance.hide();
                        }, 300);
                    });
                });
            }
        });
        
        // Remove glass effect class when popover is hidden
        mobileFilterBtn.addEventListener('hidden.bs.popover', function() {
            const popoverElement = document.querySelector('.popover');
            if (popoverElement) {
                popoverElement.classList.remove('mobile-filter-popover-glass');
            }
        });
        
        // Update filter button states when filter changes
        mobileFilterBtn.addEventListener('hidden.bs.popover', function() {
            // Update button states when popover is closed
            updateFilterButtonStates();
        });
    }
}

function updateFilterButtonStates() {
    // Update all filter buttons (both desktop and mobile) to reflect current filter
    document.querySelectorAll('.filter-time-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-filter') === currentTimeFilter) {
            btn.classList.add('active');
        }
    });
}

function loadCalendar() {
    const dateStr = currentDate.toISOString().split('T')[0];
    const doctorId = <?= $doctorId ?>;
    
    
    // Any doctor can load calendar data
    fetch(`/api/calendar?doctor_id=${doctorId}&date=${dateStr}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                calendarData = data.data; // Store calendar data for filtering
                renderCalendar(data.data);
                updateDateDisplay();
                updateLastUpdate();
                // Initialize tooltips after calendar is loaded
                initializeTooltips();
                // Scroll to highlighted appointment if exists
                if (highlightedAppointmentId) {
                    setTimeout(() => {
                        scrollToHighlightedAppointment();
                    }, 300);
                }
            } else {
                console.error('Error loading calendar:', data.error);
            }
        })
        .catch(error => {
console.error('Error loading calendar:', error);
        });
}

function renderCalendar(data) {
    const container = document.getElementById('calendarContainer');
    const timeSlots = generateTimeSlots();
    
    // Check if it's Friday (official holiday) - use the date from server data
    const dateStr = data.date || currentDate.toISOString().split('T')[0];
    const currentDateObj = new Date(dateStr + 'T12:00:00'); // Use noon to avoid timezone issues
    const isFriday = currentDateObj.getDay() === 5; // 5 = Friday (0=Sunday, 1=Monday, ..., 6=Saturday)
    
    
    let html = '<div class="calendar-grid">';
    
    // Header row
    html += '<div class="calendar-header">';
    html += '<div class="time-column">Time</div>';
    html += '<div class="appointment-column">Appointments</div>';
    html += '</div>';
    
    // If it's Friday, show official holiday for all slots
    if (isFriday || data.is_friday) {
        const dayName = currentDateObj.toLocaleDateString('en-US', {weekday: 'long'});
        timeSlots.forEach(time => {
            html += '<div class="calendar-row">';
            html += `<div class="time-slot">${formatTime(time)}</div>`;
            html += '<div class="appointment-slot">';
            html += `<div class="unavailable-slot official-holiday" title="Official Holiday - ${dayName}">
                       <i class="bi bi-calendar-x me-2"></i>
                       <div class="holiday-info">
                           <div class="holiday-title">Official Holiday</div>
                           <div class="holiday-subtitle">${dayName}</div>
                       </div>
                     </div>`;
            html += '</div>';
            html += '</div>';
        });
    } else {
        // Normal day processing (any doctor can see all appointments)
        timeSlots.forEach(time => {
            // Apply time filter
            if (!shouldDisplayTimeSlot(time, data)) {
                return; // Skip this time slot if it doesn't match the filter
            }
            
            const appointment = data.appointments.find(apt => apt.start_time === time);
            const isAvailable = data.available_slots.includes(time);
            const unavailableSlot = data.unavailable_slots ? data.unavailable_slots.find(slot => slot.time === time) : null;
            
            
            html += '<div class="calendar-row">';
            html += `<div class="time-slot">${formatTime(time)}</div>`;
            html += '<div class="appointment-slot">';
            
            if (appointment) {
                html += renderAppointmentSlot(appointment);
            } else if (isAvailable) {
                html += `<div class="available-slot" onclick="quickAddAppointment('${time}')" 
                              title="Click to schedule appointment at ${formatTime(time)}">
                            <i class="bi bi-plus-circle me-2"></i>Available - ${formatTime(time)}
                         </div>`;
            } else {
                // Show unavailable information (only outside working hours now)
                if (unavailableSlot && unavailableSlot.reason === 'Outside working hours') {
                    html += `<div class="unavailable-slot outside-hours" title="Outside working hours">
                               <i class="bi bi-clock me-2"></i>Outside working hours
                             </div>`;
                } else {
                    // This should not happen - let's debug why
                    const debugInfo = unavailableSlot && unavailableSlot.debug_info ? unavailableSlot.debug_info : `No slot data - Time: ${time}`;
                    const reason = unavailableSlot && unavailableSlot.reason ? unavailableSlot.reason : 'No data available';
                    
                    html += `<div class="unavailable-slot debug-slot" title="Debug: ${reason}">
                               <i class="bi bi-bug me-2"></i>
                               <div class="debug-info">
                                   <div class="debug-title">🔍 Debug Info:</div>
                                   <div class="debug-details">${debugInfo}</div>
                               </div>
                             </div>`;
                }
            }
            
            html += '</div>';
            html += '</div>';
        });
    }
    
    html += '</div>';
    container.innerHTML = html;
}

function renderAppointmentSlot(appointment) {
    const statusClass = getStatusBadgeClass(appointment.status);
    const visitTypeClass = getVisitTypeBadgeClass(appointment.visit_type);
    
    // Check if this is the highlighted appointment
    const isHighlighted = highlightedAppointmentId && appointment.id === highlightedAppointmentId;
    const highlightClass = isHighlighted ? 'highlighted-appointment' : '';
    
    // Create detailed tooltip content (any doctor can see appointment details)
    const tooltipContent = `
        <div class="appointment-tooltip" style="font-family: 'Cairo', sans-serif; !important;">
            <div class="tooltip-header">
                <strong>Appointment Details</strong>
            </div>
            <div class="tooltip-body">
                <div class="tooltip-row">
                    <span class="tooltip-label">Patient:</span>
                    <span class="tooltip-value">${appointment.patient_name}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Doctor:</span>
                    <span class="tooltip-value">${appointment.doctor_display_name || 'N/A'}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Phone:</span>
                    <span class="tooltip-value">${appointment.phone || 'N/A'}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Age:</span>
                    <span class="tooltip-value">${calculateAge(appointment.dob) || 'N/A'}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Visit Type:</span>
                    <span class="tooltip-value">${appointment.visit_type}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Time:</span>
                    <span class="tooltip-value">${formatTime(appointment.start_time)} - ${formatTime(appointment.end_time)}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Status:</span>
                    <span class="tooltip-value">${appointment.status}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Source:</span>
                    <span class="tooltip-value">${appointment.source || 'Unavailable'}</span>
                </div>
                ${appointment.notes ? `
                <div class="tooltip-row">
                    <span class="tooltip-label">Notes:</span>
                    <span class="tooltip-value">${appointment.notes}</span>
                </div>
                ` : ''}
            </div>
            <div class="tooltip-footer">
                <small>Click to navigate to appointment page</small>
            </div>
        </div>
    `.replace(/\n\s+/g, ' ').trim();
    
    return `
        <div class="appointment-card ${appointment.status.toLowerCase()} ${highlightClass}" 
             data-appointment-id="${appointment.id}"
             data-bs-toggle="tooltip" 
             data-bs-placement="right" 
             data-bs-html="true"
             data-bs-title="${tooltipContent.replace(/"/g, '&quot;')}">
            <div class="appointment-header">
                <div class="appointment-info" onclick="navigateToAppointment(${appointment.id})">
                    <div class="info-line"><span class="label">Patient:</span> ${appointment.patient_name}</div>
                    <div class="info-line"><span class="label">Doctor:</span> ${appointment.doctor_display_name || 'N/A'}</div>
                    <div class="info-line"><span class="label">Type:</span> ${appointment.visit_type}</div>
                    <div class="info-line"><span class="label">Time:</span> ${formatTime(appointment.start_time)} - ${formatTime(appointment.end_time)}</div>
                </div>
                <div class="appointment-actions">
                    <a href="/doctor/patients/${appointment.patient_id}" 
                       class="btn btn-sm btn-outline-info view-patient-btn"
                       onclick="event.stopPropagation();"
                       data-bs-toggle="tooltip" 
                       data-bs-placement="top" 
                       data-bs-title="View Patient Profile">
                        <i class="bi bi-person-circle"></i>
                    </a>
                    ${appointment.status === 'Rescheduled' ? `
                    <a href="/doctor/appointments/${appointment.id}" 
                       class="badge bg-warning text-dark d-flex align-items-center gap-1 ms-1" style="text-decoration: none; font-weight: bold;"
                       onclick="event.stopPropagation();"
                       data-bs-toggle="tooltip" 
                       data-bs-placement="top" 
                       data-bs-title="This appointment was rescheduled">
                        <i class="bi bi-arrow-clockwise"></i>
                        Rescheduled
                    </a>
                    ` : ''}
                    ${appointment.has_followup ? `
                    <a href="/doctor/appointments/${appointment.followup_id}" 
                       class="badge bg-success d-flex align-items-center gap-1 ms-1" style="text-decoration: none; font-weight: bold;"
                       onclick="event.stopPropagation();"
                       data-bs-toggle="tooltip" 
                       data-bs-placement="top" 
                       data-bs-title="Follow-up appointment scheduled - Click to view">
                        <i class="bi bi-calendar-check"></i>
                        Follow-up
                    </a>
                    ` : ''}
                    ${appointment.is_followup && appointment.original_appointment_id ? `
                    <a href="/doctor/appointments/${appointment.original_appointment_id}" 
                       class="badge bg-info d-flex align-items-center gap-1 ms-1" style="text-decoration: none; font-weight: bold;"
                       onclick="event.stopPropagation();"
                       data-bs-toggle="tooltip" 
                       data-bs-placement="top" 
                       data-bs-title="Original appointment - Click to view">
                        <i class="bi bi-calendar-event"></i>
                        Original
                    </a>
                    ` : ''}
                    <span class="badge ${statusClass} d-flex align-items-center gap-1">
                        <i class="bi ${getStatusIcon(appointment.status)}"></i>
                        ${getStatusDisplayText(appointment.status)}
                    </span>
                    <button class="btn btn-sm btn-outline-danger delete-appointment-btn" 
                            onclick="event.stopPropagation(); deleteAppointment(${appointment.id}, '${appointment.patient_name}', '${formatTime(appointment.start_time)}')"
                            data-bs-toggle="tooltip" 
                            data-bs-placement="top" 
                            data-bs-title="Delete this appointment">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="appointment-notes" onclick="navigateToAppointment(${appointment.id})">
                ${appointment.notes ? appointment.notes.substring(0, 50) + '...' : 'No notes'}
            </div>
        </div>
    `;
}

function scrollToHighlightedAppointment() {
    if (!highlightedAppointmentId) return;
    
    const appointmentCard = document.querySelector(`[data-appointment-id="${highlightedAppointmentId}"]`);
    if (appointmentCard) {
        // Scroll to the appointment card with smooth behavior
        appointmentCard.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center',
            inline: 'nearest'
        });
        
        // Add a pulse animation to draw attention
        appointmentCard.style.animation = 'pulseHighlight 2s ease-in-out 3';
        
        // Remove animation after it completes
        setTimeout(() => {
            appointmentCard.style.animation = '';
        }, 6000);
    }
}

// Go to selected date function
function goToSelectedDate() {
    const datePicker = document.getElementById('tooltipDatePicker');
    if (!datePicker || !datePicker.value) {
        return;
    }
    
    const selectedDate = datePicker.value;
    
    // Hide popover
    const goToDateBtn = document.getElementById('goToDateBtn');
    if (goToDateBtn) {
        const popover = bootstrap.Popover.getInstance(goToDateBtn);
        if (popover) {
            popover.hide();
        }
    }
    
    // Navigate to the selected date
    window.location.href = `/doctor/calendar?date=${selectedDate}`;
}

function generateTimeSlots() {
    // Generate time slots for any doctor to use
    const slots = [];
    const start = new Date();
    start.setHours(14, 0, 0, 0); // 2:00 PM
    
    const end = new Date();
    end.setHours(23, 0, 0, 0); // 11:00 PM
    
    const current = new Date(start);
    
    while (current < end) {
        slots.push(current.toTimeString().substring(0, 5));
        current.setMinutes(current.getMinutes() + 15);
    }
    
    return slots;
}

// Time filter functions
function timeInRange(time, startTime, endTime) {
    // Convert time string (HH:MM) to minutes for comparison
    const timeToMinutes = (t) => {
        const [hours, minutes] = t.split(':').map(Number);
        return hours * 60 + minutes;
    };
    
    const timeMins = timeToMinutes(time);
    const startMins = timeToMinutes(startTime);
    const endMins = timeToMinutes(endTime);
    
    return timeMins >= startMins && timeMins <= endMins;
}

function shouldDisplayTimeSlot(time, data) {
    // If no filter is active, show all slots
    if (!currentTimeFilter || currentTimeFilter === 'none') {
        return true;
    }
    
    const appointment = data.appointments.find(apt => apt.start_time === time);
    const isAvailable = data.available_slots.includes(time);
    
    // Filter by time range
    if (currentTimeFilter === '2pm-6pm') {
        return timeInRange(time, '14:00', '18:00');
    }
    
    if (currentTimeFilter === '6pm-1045pm') {
        return timeInRange(time, '18:00', '22:45');
    }
    
    // Filter by availability
    if (currentTimeFilter === 'available') {
        return isAvailable && !appointment;
    }
    
    if (currentTimeFilter === 'unavailable') {
        return !isAvailable || !!appointment;
    }
    
    return true;
}

function applyTimeFilter(filter) {
    // Update current filter
    currentTimeFilter = filter === 'none' ? null : filter;
    
    // Update button styles (both desktop and mobile)
    updateFilterButtonStates();
    
    // Re-render calendar with filter applied
    if (calendarData) {
        renderCalendar(calendarData);
        updateDateDisplay();
        // Initialize tooltips after calendar is re-rendered
        setTimeout(() => {
            initializeTooltips();
        }, 100);
    }
}

function navigateToAppointment(appointmentId) {
    // Navigate to appointment page (any doctor can access any appointment)
    window.location.href = `/doctor/appointments/${appointmentId}`;
}

// Global variables for delete appointment
let currentAppointmentToDelete = null;

function deleteAppointment(appointmentId, patientName, appointmentTime) {
    // Store appointment data
    currentAppointmentToDelete = {
        id: appointmentId,
        patientName: patientName,
        time: appointmentTime
    };
    
    // Update modal content
    document.getElementById('deleteAppointmentPatientName').textContent = patientName;
    document.getElementById('deleteAppointmentTime').textContent = appointmentTime;
    
    // Show modal
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteAppointmentModal'));
    deleteModal.show();
}

// Handle confirm delete button click
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirmDeleteAppointmentBtn').addEventListener('click', function() {
        if (currentAppointmentToDelete) {
            confirmDeleteAppointment();
        }
    });
});

function confirmDeleteAppointment() {
    if (!currentAppointmentToDelete) {
        showNotification('No appointment selected for deletion.', 'danger');
        return;
    }
    
    const { id, patientName, time } = currentAppointmentToDelete;
    
    // Show loading state
    setDeleteAppointmentButtonLoading(true);
    
    // Send delete request
    fetch(`/api/appointments/${id}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        setDeleteAppointmentButtonLoading(false);
        
        if (data.ok) {
            // Success
            showNotification('Appointment deleted successfully!', 'success');
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('deleteAppointmentModal')).hide();
            
            // Clear current appointment
            currentAppointmentToDelete = null;
            
            // Refresh calendar
            loadCalendar();
        } else {
            // Error from server
            const errorMsg = data.error || data.message || 'Failed to delete appointment. Please try again.';
            showNotification(errorMsg, 'danger');
        }
    })
    .catch(error => {
        setDeleteAppointmentButtonLoading(false);
        console.error('Error deleting appointment:', error);
        showNotification('An error occurred while deleting the appointment. Please try again.', 'danger');
    });
}

function setDeleteAppointmentButtonLoading(loading) {
    const btn = document.getElementById('confirmDeleteAppointmentBtn');
    const btnText = btn.querySelector('.btn-text');
    const spinner = btn.querySelector('.spinner-border');
    
    if (loading) {
        btn.disabled = true;
        btnText.textContent = 'Deleting...';
        spinner.classList.remove('d-none');
    } else {
        btn.disabled = false;
        btnText.textContent = 'Delete Appointment';
        spinner.classList.add('d-none');
    }
}

function calculateAge(dob) {
    // Calculate age for any doctor to see
    if (!dob) return null;
    
    try {
        const birthDate = new Date(dob);
        const today = new Date();
        
        if (isNaN(birthDate.getTime())) return null;
        
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age > 0 ? `${age} years` : null;
    } catch (error) {
        console.error('Error calculating age:', error);
        return null;
    }
}

function initializeTooltips() {
    // Dispose existing tooltips first (any doctor can use tooltips)
    const existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    existingTooltips.forEach(element => {
        const tooltip = bootstrap.Tooltip.getInstance(element);
        if (tooltip) {
            tooltip.dispose();
        }
    });
    
    // Initialize new tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            html: true,
            trigger: 'hover focus',
            delay: { show: 300, hide: 100 },
            container: 'body'
        });
    });
    
    // Initialize popover for "Go to Date" button
    const goToDateBtn = document.getElementById('goToDateBtn');
    if (goToDateBtn) {
        // Dispose existing popover if any
        const existingPopover = bootstrap.Popover.getInstance(goToDateBtn);
        if (existingPopover) {
            existingPopover.dispose();
        }
        
        // Create new popover
        const popover = new bootstrap.Popover(goToDateBtn, {
            html: true,
            trigger: 'click',
            placement: 'bottom',
            container: 'body',
            sanitize: false
        });
        
        // Set current date when popover is shown
        goToDateBtn.addEventListener('shown.bs.popover', function() {
            setTimeout(() => {
                const datePicker = document.getElementById('tooltipDatePicker');
                if (datePicker) {
                    const currentDateStr = currentDate.toISOString().split('T')[0];
                    datePicker.value = currentDateStr;
                    
                    // Focus on date picker
                    datePicker.focus();
                    
                    // Add Enter key support
                    datePicker.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            goToSelectedDate();
                        }
                    });
                }
            }, 100);
        });
        
        // Close popover when clicking outside
        document.addEventListener('click', function(e) {
            const popoverInstance = bootstrap.Popover.getInstance(goToDateBtn);
            if (popoverInstance && popoverInstance._isShown()) {
                const popoverElement = document.querySelector('.popover');
                if (popoverElement && !goToDateBtn.contains(e.target) && !popoverElement.contains(e.target)) {
                    popoverInstance.hide();
                }
            }
        });
    }
}

function showAppointmentDetails(appointmentId) {
    // Any doctor can view any appointment details
    fetch(`/api/appointments/${appointmentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                selectedAppointment = data.data;
                populateAppointmentModal(data.data);
                showActionButtons(data.data.status);
                new bootstrap.Modal(document.getElementById('appointmentModal')).show();
            }
        })
        .catch(error => {
            console.error('Error loading appointment:', error);
        });
}

function populateAppointmentModal(appointment) {
    // Any doctor can populate appointment modal
    const modalBody = document.getElementById('appointmentModalBody');
    
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Patient Information</h6>
                <p><strong>Name:</strong> ${appointment.patient_name}</p>
                <p><strong>Phone:</strong> ${appointment.patient_phone}</p>
                <p><strong>Age:</strong> ${appointment.patient_age || 'N/A'}</p>
                <p><strong>Gender:</strong> ${appointment.patient_gender || 'N/A'}</p>
            </div>
            <div class="col-md-6">
                <h6>Appointment Details</h6>
                <p><strong>Doctor:</strong> ${appointment.doctor_display_name || 'N/A'}</p>
                <p><strong>Date:</strong> ${formatDate(appointment.date)}</p>
                <p><strong>Time:</strong> ${formatTime(appointment.start_time)} - ${formatTime(appointment.end_time)}</p>
                <p><strong>Type:</strong> ${appointment.visit_type}</p>
                <p><strong>Source:</strong> ${appointment.source}</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Notes</h6>
                <p>${appointment.notes || 'No notes available'}</p>
            </div>
        </div>
    `;
}

function showActionButtons(status) {
    // Hide all buttons first (any doctor can perform actions)
    document.querySelectorAll('#appointmentModal .modal-footer button:not(.btn-secondary)').forEach(btn => {
        btn.style.display = 'none';
    });
    
    // Show relevant buttons based on status (any doctor can perform actions)
    switch (status) {
        case 'Booked':
            document.getElementById('startVisitBtn').style.display = 'inline-block';
            document.getElementById('rescheduleBtn').style.display = 'inline-block';
            document.getElementById('cancelBtn').style.display = 'inline-block';
            break;
        case 'CheckedIn':
            document.getElementById('startVisitBtn').style.display = 'inline-block';
            document.getElementById('rescheduleBtn').style.display = 'inline-block';
            break;
        case 'InProgress':
            document.getElementById('completeVisitBtn').style.display = 'inline-block';
            break;
    }
}

// Get auto-refresh state from localStorage (default: true/ON)
function getAutoRefreshState() {
    const saved = localStorage.getItem('calendarAutoRefresh');
    return saved === null ? true : saved === 'true'; // Default is ON
}

// Save auto-refresh state to localStorage
function saveAutoRefreshState(enabled) {
    localStorage.setItem('calendarAutoRefresh', enabled ? 'true' : 'false');
}

// Toggle auto-refresh on/off
function toggleCalendarAutoRefresh(enabled) {
    saveAutoRefreshState(enabled);
    
    if (enabled) {
        // Start auto-refresh if not already running
        if (!refreshInterval) {
            startAutoRefresh();
        }
        console.log('[Calendar Auto-Refresh] Enabled');
    } else {
        // Stop auto-refresh
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
        console.log('[Calendar Auto-Refresh] Disabled');
    }
}

// Auto-refresh calendar data every 60 seconds using AJAX (pause when modals are open or user is interacting)
function startAutoRefresh() {
    // Clear any existing interval
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    
    refreshInterval = setInterval(() => {
        const searchModal = document.getElementById('searchModal');
        const addAppointmentModal = document.getElementById('addAppointmentModal');
        const addPatientModal = document.getElementById('addPatientModal');
        const appointmentModal = document.getElementById('appointmentModal');
        const cancelModal = document.getElementById('cancelModal');
        const deleteAppointmentModal = document.getElementById('deleteAppointmentModal');
        
        // Don't refresh if any modal is open
        const isModalOpen = searchModal?.classList.contains('show') ||
                           addAppointmentModal?.classList.contains('show') ||
                           addPatientModal?.classList.contains('show') ||
                           appointmentModal?.classList.contains('show') ||
                           cancelModal?.classList.contains('show') ||
                           deleteAppointmentModal?.classList.contains('show') ||
                           document.querySelector('.modal.show') !== null;
        
        if (!isModalOpen) {
            refreshCalendarData();
        }
    }, 60000); // 60 seconds
}

// Function to refresh calendar data via AJAX
function refreshCalendarData() {
    console.log('[Calendar Auto-Refresh] Starting refresh...');
    const dateStr = currentDate.toISOString().split('T')[0];
    const doctorId = <?= $doctorId ?>;
    
    console.log('[Calendar Auto-Refresh] Request params:', {
        date: dateStr,
        doctorId: doctorId
    });
    
    // Show subtle loading indicator
    const calendarContainer = document.getElementById('calendarContainer');
    if (calendarContainer) {
        calendarContainer.parentElement.classList.add('table-loading');
    }
    
    fetch(`/api/calendar?doctor_id=${doctorId}&date=${dateStr}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        console.log('[Calendar Auto-Refresh] API Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('[Calendar Auto-Refresh] API Response data:', {
            ok: data.ok,
            hasData: !!data.data,
            appointmentsCount: data.data?.appointments?.length || 0,
            availableSlotsCount: data.data?.available_slots?.length || 0,
            isFriday: data.data?.is_friday || false
        });
        
        if (data.ok) {
            calendarData = data.data; // Store calendar data for filtering
            renderCalendar(data.data);
            updateDateDisplay();
            updateLastUpdate();
            updateStatusIndicator();
            // Initialize tooltips after calendar is refreshed
            setTimeout(() => {
                initializeTooltips();
            }, 100);
            console.log('[Calendar Auto-Refresh] Calendar refreshed successfully');
        } else {
            console.error('[Calendar Auto-Refresh] Error in response:', data.error);
        }
    })
    .catch(error => {
        console.error('[Calendar Auto-Refresh] Fetch error:', error);
        // Silently fail - don't show error to user for background refresh
    })
    .finally(() => {
        // Remove loading indicator
        const calendarContainer = document.getElementById('calendarContainer');
        if (calendarContainer) {
            calendarContainer.parentElement.classList.remove('table-loading');
        }
        console.log('[Calendar Auto-Refresh] Refresh completed');
    });
}

function updateStatusIndicator() {
    // Update status indicator for any doctor
    const indicator = document.getElementById('statusIndicator');
    indicator.innerHTML = '<i class="bi bi-circle-fill me-1"></i> Live';
    indicator.className = 'badge bg-success me-2';
    
    // Add pulse animation
    indicator.style.animation = 'pulseOnce 0.6s ease';
    setTimeout(() => {
        indicator.style.animation = '';
    }, 600);
}

function updateDateDisplay() {
    // Update date display for any doctor
    const display = document.getElementById('currentDateDisplay');
    // Use the date string from currentDate to avoid timezone issues
    const dateStr = currentDate.toISOString().split('T')[0];
    const displayDate = new Date(dateStr + 'T12:00:00');
    display.textContent = displayDate.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function updateLastUpdate() {
    // Update last update time for any doctor
    const lastUpdate = document.getElementById('lastUpdate');
    lastUpdate.textContent = `Last updated: ${new Date().toLocaleTimeString()}`;
}

function getStatusBadgeClass(status) {
    // Get status badge class for any doctor to see
    const classes = {
        'Booked': 'bg-primary',
        'CheckedIn': 'bg-success',
        'InProgress': 'bg-warning',
        'Completed': 'bg-info',
        'Cancelled': 'bg-danger',
        'NoShow': 'bg-secondary',
        'Rescheduled': 'bg-info'
    };
    return classes[status] || 'bg-secondary';
}

function getStatusDisplayText(status) {
    const statusTexts = {
        'Booked': 'Booked',
        'CheckedIn': 'Checked In',
        'InProgress': 'In Progress',
        'Completed': 'Completed',
        'Cancelled': 'Cancelled',
        'NoShow': 'No Show',
        'Rescheduled': 'Rescheduled'
    };
    return statusTexts[status] || status;
}

function getStatusIcon(status) {
    const icons = {
        'Booked': 'bi-calendar-check',
        'CheckedIn': 'bi-check-circle-fill',
        'InProgress': 'bi-hourglass-split',
        'Completed': 'bi-check2-all',
        'Cancelled': 'bi-x-circle-fill',
        'NoShow': 'bi-clock-fill',
        'Rescheduled': 'bi-arrow-clockwise'
    };
    return icons[status] || 'bi-question-circle';
}

function getVisitTypeBadgeClass(type) {
    // Get visit type badge class for any doctor to see
    const classes = {
        'New': 'badge bg-primary',
        'FollowUp': 'badge bg-success',
        'Procedure': 'badge bg-warning'
    };
    return classes[type] || 'badge bg-secondary';
}

function formatTime(time) {
    // Format time for any doctor to see
    if (!time) return '';
    return new Date(`2000-01-01T${time}`).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

function formatDate(date) {
    // Format date for any doctor to see
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Add appointment functions
function openAddAppointmentModal(preselectedTime = null, preselectedDate = null) {
    // Set date - use preselected date or current date (any doctor can add appointments)
    const dateToUse = preselectedDate || currentDate.toISOString().split('T')[0];
    
    // Validate date before opening modal
    const validation = validateDateSelection(dateToUse);
    if (!validation.valid) {
        showErrorMessage(validation.message);
        return;
    }
    
    document.getElementById('appointmentDate').value = dateToUse;
    
    // Clear form
    document.getElementById('addAppointmentForm').reset();
    document.getElementById('selectedPatientId').value = '';
    document.getElementById('patientSearchResults').innerHTML = '';
    
    // Re-set the date after form reset
    document.getElementById('appointmentDate').value = dateToUse;
    
    // Handle preselected patient
    const patientSearchField = document.getElementById('patientSearch');
    const newPatientBtn = document.getElementById('newPatientBtn');
    const preselectedLabel = document.getElementById('preselectedLabel');
    
    if (preselectedPatient) {
        // Fill patient info
        document.getElementById('selectedPatientId').value = preselectedPatient.id;
        patientSearchField.value = preselectedPatient.full_name;
        
        // Make patient field readonly
        patientSearchField.readOnly = true;
        patientSearchField.style.backgroundColor = 'var(--bg)';
        patientSearchField.style.cursor = 'not-allowed';
        
        // Hide new patient button
        newPatientBtn.style.display = 'none';
        
        // Show preselected label
        preselectedLabel.style.display = 'inline-block';
        
        // Show patient info
        document.getElementById('patientSearchResults').innerHTML = `
            <div class="selected-patient-info alert alert-info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>Selected Patient:</strong> ${preselectedPatient.full_name}<br>
                        <small>Phone: ${preselectedPatient.phone} • Age: ${preselectedPatient.age || 'N/A'}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearPreselectedPatient()">
                        Change Patient
                    </button>
                </div>
            </div>
        `;
    } else {
        // Enable patient search
        patientSearchField.readOnly = false;
        patientSearchField.style.backgroundColor = '';
        patientSearchField.style.cursor = '';
        newPatientBtn.style.display = 'block';
        preselectedLabel.style.display = 'none';
    }
    
    // Load available time slots for selected date
    loadAvailableTimeSlots(preselectedTime);
    
    // Add styling to preselected date field if it's different from today
    const dateField = document.getElementById('appointmentDate');
    const today = new Date().toISOString().split('T')[0];
    if (dateToUse !== today) {
        dateField.classList.add('preselected-field');
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addAppointmentModal'));
    
    // Clean up styling when modal is hidden
    document.getElementById('addAppointmentModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('appointmentDate').classList.remove('preselected-field');
        document.getElementById('appointmentTime').classList.remove('preselected-field');
    });
    
    modal.show();
}

function quickAddAppointment(time) {
    const selectedDate = currentDate.toISOString().split('T')[0];
    
    // Check if selected date is in the past
    if (isDateInPast(selectedDate)) {
        showErrorMessage('Cannot add appointment on a past date. Please select today or a future date.');
        return;
    }
    
    // Set the current date being viewed and the selected time (any doctor can add appointments)
    openAddAppointmentModal(time, selectedDate);
}

// Function to check if date is in the past based on server time
function isDateInPast(dateString) {
    return dateString < SERVER_DATE;
}

// Function to validate date selection
function validateDateSelection(dateString) {
    if (isDateInPast(dateString)) {
        return {
            valid: false,
            message: 'Cannot select a date before today. Current date (Egypt timezone): ' + formatDateArabic(SERVER_DATE)
        };
    }
    return { valid: true };
}

// Format date in English (any doctor can see formatted dates)
function formatDateArabic(dateString) {
    const date = new Date(dateString + 'T00:00:00');
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        weekday: 'long'
    };
    return date.toLocaleDateString('en-US', options);
}

function searchPatients() {
    // Don't search if patient is preselected (any doctor can search patients)
    if (preselectedPatient) {
        return;
    }
    
    const query = document.getElementById('patientSearch').value.trim();
    if (query.length < 2) {
        document.getElementById('patientSearchResults').innerHTML = '';
        return;
    }
    
    fetch(`/api/patients/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                displayPatientSearchResults(data.data);
            }
        })
        .catch(error => {
            console.error('Error searching patients:', error);
        });
}

function displayPatientSearchResults(patients) {
    // Display patient search results for any doctor
    const resultsContainer = document.getElementById('patientSearchResults');
    
    if (patients.length === 0) {
        resultsContainer.innerHTML = '<div class="search-result-item text-muted">No patients found</div>';
        return;
    }
    
    let html = '';
    patients.forEach(patient => {
        html += `
            <div class="search-result-item" onclick="selectPatient(${patient.id}, '${patient.first_name} ${patient.last_name}')">
                <div class="patient-name">${patient.first_name} ${patient.last_name}</div>
                <div class="patient-details">${patient.phone} • Age: ${patient.age || 'N/A'}</div>
            </div>
        `;
    });
    
    resultsContainer.innerHTML = html;
}

function selectPatient(patientId, patientName) {
    // Any doctor can select any patient
    document.getElementById('selectedPatientId').value = patientId;
    document.getElementById('patientSearch').value = patientName;
    document.getElementById('patientSearchResults').innerHTML = '';
}

function loadAvailableTimeSlots(preselectedTime = null) {
    const date = document.getElementById('appointmentDate').value;
    if (!date) return;
    
    const doctorId = <?= $doctorId ?>;
    
    // Any doctor can load available time slots
    fetch(`/api/calendar?doctor_id=${doctorId}&date=${date}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                populateTimeSlots(data.data.available_slots, preselectedTime);
                
                // Ensure preselected time is selected after population
                if (preselectedTime) {
                    setTimeout(() => {
                        const timeField = document.getElementById('appointmentTime');
                        if (timeField.value !== preselectedTime) {
                            timeField.value = preselectedTime;
                        }
                        if (timeField.value === preselectedTime) {
                            timeField.classList.add('preselected-field');
                        }
                    }, 100);
                }
            }
        })
        .catch(error => {
            console.error('Error loading time slots:', error);
        });
}

function populateTimeSlots(availableSlots, preselectedTime = null) {
    const timeSelect = document.getElementById('appointmentTime');
    timeSelect.innerHTML = '<option value="">Select time slot...</option>';
    
    // Add all available slots (any doctor can see all available slots)
    availableSlots.forEach(time => {
        const option = document.createElement('option');
        option.value = time;
        option.textContent = formatTime(time);
        timeSelect.appendChild(option);
    });
    
    // If there's a preselected time that's not in available slots, add it
    if (preselectedTime && !availableSlots.includes(preselectedTime)) {
        const option = document.createElement('option');
        option.value = preselectedTime;
        option.textContent = formatTime(preselectedTime) + ' (Selected)';
        option.style.fontWeight = 'bold';
        option.style.color = '#28a745';
        option.style.backgroundColor = '#f8f9fa';
        timeSelect.appendChild(option);
    }
    
    // Sort all options by time (except the first "Select..." option)
    const options = Array.from(timeSelect.options).slice(1); // Skip first "Select..." option
    options.sort((a, b) => a.value.localeCompare(b.value));
    
    // Clear and re-add sorted options
    timeSelect.innerHTML = '<option value="">Select time slot...</option>';
    options.forEach(option => timeSelect.appendChild(option));
    
    // If preselected time exists, select it immediately
    if (preselectedTime) {
        setTimeout(() => {
            timeSelect.value = preselectedTime;
            if (timeSelect.value === preselectedTime) {
                timeSelect.classList.add('preselected-field');
            }
        }, 50);
    }
}

function handleAddAppointment(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const appointmentData = Object.fromEntries(formData);
    
    
    // Validation (any doctor can add appointments)
    if (!appointmentData.patient_id) {
        showErrorMessage('Please select a patient');
        return;
    }
    
    if (!appointmentData.date) {
        showErrorMessage('Please select a date');
        return;
    }
    
    // Final validation: Check if date is in the past
    const validation = validateDateSelection(appointmentData.date);
    if (!validation.valid) {
        showErrorMessage(validation.message);
        return;
    }
    
    if (!appointmentData.start_time) {
        showErrorMessage('Please select an appointment time');
        return;
    }
    
    if (!appointmentData.visit_type) {
        showErrorMessage('Please select a visit type');
        return;
    }
    
    // Add doctor_id (any doctor can book appointments)
    appointmentData.doctor_id = <?= $doctorId ?>;
    
    
    // Save appointment
    fetch('/api/appointments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(appointmentData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('addAppointmentModal')).hide();
            
            // Refresh calendar
            loadCalendar();
            
            // Show success message
            showNotification('Appointment added successfully!', 'success');
        } else {
            const errorMessage = data.message || data.error || 'Unknown error occurred';
            console.error('API Error:', errorMessage);
            alert('Error: ' + errorMessage);
        }
    })
    .catch(error => {
        console.error('Error saving appointment:', error);
        alert('Error saving appointment: ' + error.message);
    });
}

function showNotification(message, type = 'info') {
    // Create notification element (any doctor can see notifications)
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Enhanced error message function (any doctor can see error messages)
function showErrorMessage(message) {
    showNotification(message, 'danger');
}

// Enhanced success message function (any doctor can see success messages)
function showSuccessMessage(message) {
    showNotification(message, 'success');
}

function clearPreselectedPatient() {
    // Clear preselected patient (any doctor can change patient selection)
    preselectedPatient = null;
    
    // Clear form fields
    document.getElementById('selectedPatientId').value = '';
    document.getElementById('patientSearch').value = '';
    document.getElementById('patientSearchResults').innerHTML = '';
    
    // Enable patient search
    const patientSearchField = document.getElementById('patientSearch');
    const newPatientBtn = document.getElementById('newPatientBtn');
    const preselectedLabel = document.getElementById('preselectedLabel');
    
    patientSearchField.readOnly = false;
    patientSearchField.style.backgroundColor = '';
    patientSearchField.style.cursor = '';
    patientSearchField.placeholder = 'Search patient by name or phone...';
    
    newPatientBtn.style.display = 'block';
    preselectedLabel.style.display = 'none';
    
    // Focus on search field
    patientSearchField.focus();
}

function debounce(func, wait) {
    // Debounce function for any doctor to use
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Add Patient functionality
function initializeAddPatientModal() {
    const addPatientForm = document.getElementById('addPatientForm');
    const addPatientModal = document.getElementById('addPatientModal');
    const addPatientSubmit = document.getElementById('addPatientSubmit');
    const addPatientMessage = document.getElementById('addPatientMessage');
    
    // Reset form when modal opens
    addPatientModal.addEventListener('show.bs.modal', function() {
        addPatientForm.reset();
        addPatientForm.classList.remove('was-validated');
        hideMessage();
        resetSubmitButton();
        
        // Focus on first name field
        setTimeout(() => {
            document.getElementById('firstName').focus();
        }, 300);
    });
    
    // Handle form submission
    addPatientForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!addPatientForm.checkValidity()) {
            addPatientForm.classList.add('was-validated');
            showMessage('Please fill in all required fields correctly.', 'error');
            return;
        }
        
        // Additional validation
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const gender = document.getElementById('gender').value;
        
        if (!firstName || !lastName || !phone) {
            showMessage('First name, last name, and phone number are required.', 'error');
            return;
        }
        
        if (!gender) {
            showMessage('Please select the patient\'s gender.', 'error');
            document.getElementById('gender').focus();
            return;
        }
        
        // Validate phone number format
        const cleanPhone = phone.replace(/[\s\-\(\)]/g, '');
        const phoneRegex = /^(\+\d{1,3})?\d{7,15}$/;
        if (!phoneRegex.test(cleanPhone)) {
            showMessage('Please enter a valid phone number (7-15 digits, optionally with country code).', 'error');
            return;
        }
        
        // Submit form
        submitPatientForm();
    });
    
    function submitPatientForm() {
        const formData = new FormData(addPatientForm);
        
        // Show loading state
        setSubmitButtonLoading(true);
        hideMessage();
        
        // Send AJAX request
        fetch('/api/patients', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            setSubmitButtonLoading(false);
            
            if (data.ok) {
                // Success
                showMessage('Patient added successfully!', 'success');
                
                // Save form data before resetting
                const formData = new FormData(addPatientForm);
                const savedFormData = {
                    first_name: formData.get('first_name'),
                    last_name: formData.get('last_name'),
                    phone: formData.get('phone'),
                    gender: formData.get('gender'),
                    dob: formData.get('dob'),
                    age: formData.get('age')
                };
                
                // Reset form
                addPatientForm.reset();
                addPatientForm.classList.remove('was-validated');
                
                // Close modal after delay and return to appointment modal
                setTimeout(() => {
                    bootstrap.Modal.getInstance(addPatientModal).hide();
                    
                    // Return to appointment modal with new patient selected
                    setTimeout(() => {
                        const appointmentModal = new bootstrap.Modal(document.getElementById('addAppointmentModal'));
                        appointmentModal.show();
                        
                        // Auto-select the new patient - handle different response formats
                        const patientData = data.data || data.patient || data;
                        
                        if (patientData && (patientData.id || patientData.patient_id)) {
                            // Use saved form data to create patient info
                            const patientInfo = {
                                id: patientData.id || patientData.patient_id,
                                first_name: savedFormData.first_name,
                                last_name: savedFormData.last_name,
                                phone: savedFormData.phone,
                                gender: savedFormData.gender,
                                dob: savedFormData.dob,
                                age: savedFormData.age
                            };
                            
                            selectNewPatient(patientInfo);
                            
                            // Set visit type to "New" automatically
                            document.getElementById('visitType').value = 'New';
                            
                            // Also refresh the patient search to make sure the new patient appears in search results
                            setTimeout(() => {
                                const searchQuery = document.getElementById('patientSearch').value;
                                if (searchQuery) {
                                    searchPatients(searchQuery);
                                }
                            }, 1000);
                        } else {
                            console.error('No valid patient data found in response:', data);
                            showNotification('Patient added but could not auto-select. Please search for the patient manually.', 'warning');
                        }
                    }, 300);
                }, 1500);
                
            } else {
                // Error from server
                const errorMsg = data.error || data.message || 'Failed to add patient. Please try again.';
                showMessage(errorMsg, 'error');
                
                // Show validation errors if available
                if (data.details) {
                    showValidationErrors(data.details);
                }
            }
        })
        .catch(error => {
            setSubmitButtonLoading(false);
            console.error('Error adding patient:', error);
            showMessage('An error occurred while adding the patient. Please try again.', 'error');
        });
    }
    
    function selectNewPatient(patientData) {
        
        // Handle different response formats
        const firstName = patientData.first_name || patientData.firstName || '';
        const lastName = patientData.last_name || patientData.lastName || '';
        const fullName = `${firstName} ${lastName}`.trim();
        const patientId = patientData.id || patientData.patient_id;
        const phone = patientData.phone || patientData.phone_number || '';
        const age = patientData.age || calculateAgeFromDOB(patientData.dob) || 'N/A';
        
        
        // Fill patient search field
        document.getElementById('patientSearch').value = fullName;
        document.getElementById('selectedPatientId').value = patientId;
        
        // Show patient info
        document.getElementById('patientSearchResults').innerHTML = `
            <div class="selected-patient-info alert alert-success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>New Patient Added:</strong> ${fullName}<br>
                        <small>Phone: ${phone} • Age: ${age}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearPreselectedPatient()">
                        Change Patient
                    </button>
                </div>
            </div>
        `;
    }
    
    function calculateAgeFromDOB(dob) {
        if (!dob) return null;
        try {
            const today = new Date();
            const birthDate = new Date(dob);
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            return age > 0 ? age : null;
        } catch (error) {
            console.error('Error calculating age:', error);
            return null;
        }
    }
    
    
    function showMessage(message, type) {
        addPatientMessage.className = `alert alert-${type === 'error' ? 'danger' : type}`;
        addPatientMessage.textContent = message;
        addPatientMessage.classList.remove('d-none');
    }
    
    function hideMessage() {
        addPatientMessage.classList.add('d-none');
    }
    
    function setSubmitButtonLoading(loading) {
        const btnText = addPatientSubmit.querySelector('.btn-text');
        const spinner = addPatientSubmit.querySelector('.spinner-border');
        
        if (loading) {
            addPatientSubmit.disabled = true;
            btnText.textContent = 'Adding...';
            spinner.classList.remove('d-none');
        } else {
            addPatientSubmit.disabled = false;
            btnText.textContent = 'Add Patient';
            spinner.classList.add('d-none');
        }
    }
    
    function resetSubmitButton() {
        setSubmitButtonLoading(false);
    }
    
    function showValidationErrors(errors) {
        // Clear previous validation errors
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
        });
        
        // Show new validation errors
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = input.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.textContent = errors[field];
                }
            }
        });
    }
    
    // Clear validation errors on input
    addPatientForm.addEventListener('input', function(e) {
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
            const feedback = e.target.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = '';
            }
        }
    });
    
    // Age and Date of Birth conversion
    const dobInput = document.getElementById('dob');
    const ageInput = document.getElementById('age');
    
    // Convert age to date of birth
    ageInput.addEventListener('input', function() {
        const age = parseInt(this.value);
        if (age && age > 0 && age <= 150) {
            const today = new Date();
            const birthYear = today.getFullYear() - age;
            const birthDate = new Date(birthYear, today.getMonth(), today.getDate());
            dobInput.value = birthDate.toISOString().split('T')[0];
            
            // Clear age field after conversion
            setTimeout(() => {
                this.value = '';
            }, 1000);
        }
    });
    
    // Convert date of birth to age
    dobInput.addEventListener('change', function() {
        if (this.value) {
            const birthDate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            if (age >= 0 && age <= 150) {
                ageInput.placeholder = `Calculated age: ${age} years`;
                setTimeout(() => {
                    ageInput.placeholder = 'Enter age in years';
                }, 3000);
            }
        }
    });
}

// Initialize add patient modal when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeAddPatientModal();
    // Initialize draggable modals
    initializeDraggableModals();
});

// Make modals draggable
function initializeDraggableModals() {
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        const modalDialog = modal.querySelector('.modal-dialog');
        if (!modalDialog) return;
        
        let isDragging = false;
        let currentX;
        let currentY;
        let initialX;
        let initialY;
        let xOffset = 0;
        let yOffset = 0;
        
        // Make modal header the drag handle
        const modalHeader = modal.querySelector('.modal-header');
        if (!modalHeader) return;
        
        modalHeader.style.cursor = 'move';
        
        modalHeader.addEventListener('mousedown', dragStart);
        document.addEventListener('mousemove', drag);
        document.addEventListener('mouseup', dragEnd);
        
        function dragStart(e) {
            // Don't drag if clicking on buttons or inputs
            if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT' || e.target.closest('button') || e.target.closest('input') || e.target.closest('.btn-close')) {
                return;
            }
            
            // Get current transform values
            const transform = modalDialog.style.transform;
            if (transform) {
                const match = transform.match(/translate\(([^,]+)px,\s*([^)]+)px\)/);
                if (match) {
                    xOffset = parseFloat(match[1]) || 0;
                    yOffset = parseFloat(match[2]) || 0;
                }
            }
            
            initialX = e.clientX - xOffset;
            initialY = e.clientY - yOffset;
            
            if (e.target === modalHeader || modalHeader.contains(e.target)) {
                isDragging = true;
                modalDialog.style.transition = 'none';
            }
        }
        
        function drag(e) {
            if (isDragging) {
                e.preventDefault();
                currentX = e.clientX - initialX;
                currentY = e.clientY - initialY;
                
                xOffset = currentX;
                yOffset = currentY;
                
                setTranslate(currentX, currentY, modalDialog);
            }
        }
        
        function dragEnd(e) {
            initialX = currentX;
            initialY = currentY;
            isDragging = false;
            modalDialog.style.transition = '';
        }
        
        function setTranslate(xPos, yPos, el) {
            // Get viewport dimensions
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            
            // Get modal dimensions
            const modalRect = el.getBoundingClientRect();
            const modalWidth = modalRect.width;
            const modalHeight = modalRect.height;
            
            // Get the original position (center of viewport)
            const originalLeft = (viewportWidth - modalWidth) / 2;
            const originalTop = 50; // Keep at least 50px from top
            
            // Calculate boundaries relative to original position
            // Allow movement within viewport bounds
            const minX = -(originalLeft - 20); // Allow 20px from left edge
            const maxX = viewportWidth - modalWidth - originalLeft + 20; // Allow 20px from right edge
            const minY = -(originalTop - 20); // Allow 20px from top
            const maxY = viewportHeight - modalHeight - originalTop - 20; // Allow 20px from bottom
            
            // Constrain movement
            const constrainedX = Math.max(minX, Math.min(maxX, xPos));
            const constrainedY = Math.max(minY, Math.min(maxY, yPos));
            
            el.style.transform = `translate(${constrainedX}px, ${constrainedY}px)`;
        }
        
        // Reset position when modal is hidden
        modal.addEventListener('hidden.bs.modal', function() {
            xOffset = 0;
            yOffset = 0;
            modalDialog.style.transform = '';
        });
    });
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>