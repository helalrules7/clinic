<?php
/**
 * Doctor Notes Page
 * صفحة الملحوظات للأطباء
 */
?>

<style>
:root {
    --bg: #f8fafc;
    --text: #0f172a;
    --card: #ffffff;
    --muted: #475569;
    --accent: #0ea5e9;
    --success: #10b981;
    --danger: #ef4444;
    --border: #e2e8f0;
}

.dark {
    --bg: #0b1220;
    --text: #f8fafc;
    --card: #1e293b;
    --muted: #cbd5e1;
    --accent: #38bdf8;
    --success: #4ade80;
    --danger: #fb7185;
    --border: #334155;
}

.notes-container {
    position: relative;
    width: 100%;
    min-height: calc(100vh - 200px);
    background: 
        linear-gradient(135deg, rgba(14, 165, 233, 0.05) 0%, rgba(16, 185, 129, 0.05) 100%),
        repeating-linear-gradient(
            45deg,
            transparent,
            transparent 10px,
            rgba(0, 0, 0, 0.02) 10px,
            rgba(0, 0, 0, 0.02) 20px
        ),
        var(--bg);
    border-radius: 12px;
    padding: 2rem;
    overflow: hidden;
    border: 1px solid var(--border);
    box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.03);
}

.dark .notes-container {
    background: 
        linear-gradient(135deg, rgba(56, 189, 248, 0.08) 0%, rgba(74, 222, 128, 0.08) 100%),
        repeating-linear-gradient(
            45deg,
            transparent,
            transparent 10px,
            rgba(255, 255, 255, 0.02) 10px,
            rgba(255, 255, 255, 0.02) 20px
        ),
        var(--bg);
    border: 1px solid var(--border);
    box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.2);
}

.notes-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1rem;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
}

.notes-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.color-picker-container {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.color-picker-label {
    color: var(--text);
    font-weight: 500;
    font-size: 0.9rem;
    margin-right: 0.5rem;
}

.color-options {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.color-option {
    width: 40px;
    height: 40px;
    border: 3px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.color-option:hover {
    transform: scale(1.15);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.color-option.active {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.3);
    transform: scale(1.1);
}

.color-option.white {
    background: #ffffff;
}

.color-option.red {
    background: #ef4444;
}

.color-option.black {
    background: #1e293b;
}

.color-option.dodgerblue {
    background: #1e90ff;
}

.color-option.warning {
    background: #fbbf24;
}

.color-option.success {
    background: #10b981;
}

.note-widget {
    position: absolute;
    min-width: 300px;
    min-height: 200px;
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    cursor: move;
    transition: box-shadow 0.3s ease, transform 0.2s ease;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* Glass effect for each color */
.note-widget.color-white {
    background: rgba(255, 255, 255, 0.85);
    border-color: rgba(255, 255, 255, 0.3);
}

.note-widget.color-red {
    background: rgba(239, 68, 68, 0.85);
    border-color: rgba(239, 68, 68, 0.4);
}

.note-widget.color-black {
    background: rgba(30, 41, 59, 0.85);
    border-color: rgba(30, 41, 59, 0.4);
}

.note-widget.color-dodgerblue {
    background: rgba(30, 144, 255, 0.85);
    border-color: rgba(30, 144, 255, 0.4);
}

.note-widget.color-warning {
    background: rgba(251, 191, 36, 0.85);
    border-color: rgba(251, 191, 36, 0.4);
}

.note-widget.color-success {
    background: rgba(16, 185, 129, 0.85);
    border-color: rgba(16, 185, 129, 0.4);
}

.note-widget:hover {
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.note-widget.dragging {
    opacity: 0.8;
    z-index: 10000 !important;
    transform: rotate(2deg);
}

.note-widget-header {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
    cursor: move;
}

.note-widget-title {
    font-weight: 600;
    font-size: 0.95rem;
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    transition: background 0.2s ease;
}

.note-widget-title:focus {
    background: rgba(255, 255, 255, 0.5);
}

.note-widget-actions {
    display: flex;
    gap: 0.5rem;
}

.note-widget-btn {
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(5px);
    color: var(--text);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 0.85rem;
}

.note-widget-btn:hover {
    background: rgba(255, 255, 255, 0.9);
    transform: scale(1.1);
}

.note-widget-btn.delete {
    color: var(--danger);
}

.note-widget-btn.delete:hover {
    background: rgba(239, 68, 68, 0.2);
}

.note-widget-body {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
    background-image: 
        repeating-linear-gradient(
            transparent,
            transparent 31px,
            rgba(0, 0, 0, 0.1) 31px,
            rgba(0, 0, 0, 0.1) 32px
        );
    background-size: 100% 32px;
    line-height: 32px;
}

.note-widget-content {
    width: 100%;
    height: 100%;
    border: none;
    background: transparent;
    outline: none;
    resize: none;
    font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 0.95rem;
    line-height: 32px;
    padding: 0;
    margin: 0;
    overflow-y: auto;
    word-wrap: break-word;
}

/* Ensure autocomplete elements don't interfere with contenteditable */
.note-widget-content a[data-type] {
    pointer-events: auto !important;
    user-select: none;
    cursor: pointer !important;
    text-decoration: none;
    display: inline-flex;
}

.note-widget-content a[data-type]:hover {
    opacity: 0.9;
}

.note-widget-content span[data-type] {
    pointer-events: auto;
    user-select: none;
    cursor: default;
    display: inline-flex;
    position: relative;
}

/* Prevent contenteditable from editing inside badges and links */
.note-widget-content a[data-type],
.note-widget-content span[data-type] {
    -webkit-user-modify: read-only;
    -moz-user-modify: read-only;
    user-modify: read-only;
}

/* Ensure links are fully clickable */
.note-widget-content a[data-type="patient"],
.note-widget-content a[data-type="appointment"] {
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Prevent contenteditable from capturing clicks on link content */
.note-widget-content a[data-type] * {
    pointer-events: none;
}

/* Support for notes created in dashboard.php (with dashboard- prefix) */
.note-widget-content .dashboard-note-content-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--accent);
    color: white;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    margin: 0 0.2rem;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.note-widget-content .dashboard-note-content-link:hover {
    background: var(--accent);
    opacity: 0.9;
    transform: translateY(-1px);
    color: white;
}

.note-widget-content .dashboard-note-content-link .patient-icon {
    font-size: 0.9rem;
}

.dark .note-widget-content .dashboard-note-content-link {
    background: var(--accent);
    color: white;
}

.dark .note-widget-content .dashboard-note-content-link:hover {
    background: var(--accent);
    color: white;
    opacity: 0.9;
}

.note-widget-content .dashboard-note-content-appointment-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: #6c757d;
    color: white;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    margin: 0 0.2rem;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.note-widget-content .dashboard-note-content-appointment-link:hover {
    background: #5a6268;
    transform: translateY(-1px);
    color: white;
}

.note-widget-content .dashboard-note-content-appointment-link .appointment-icon {
    font-size: 0.9rem;
}

.dark .note-widget-content .dashboard-note-content-appointment-link {
    background: #6c757d;
    color: white;
}

.dark .note-widget-content .dashboard-note-content-appointment-link:hover {
    background: #5a6268;
    color: white;
}

.note-widget-content .dashboard-note-content-drug-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    margin: 0 0.2rem;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.dark .note-widget-content .dashboard-note-content-drug-badge {
    background: rgba(16, 185, 129, 0.2);
    color: #4ade80;
    border-color: rgba(16, 185, 129, 0.4);
}

.note-widget-content .dashboard-note-content-drug-badge .drug-icon {
    font-size: 0.9rem;
}

.note-widget-content[contenteditable="true"]:empty:before {
    content: attr(data-placeholder);
    opacity: 0.5;
    pointer-events: none;
}

.note-widget-footer {
    padding: 0.5rem 1rem;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
    font-size: 0.75rem;
    opacity: 0.7;
}

.dark .note-widget.color-white {
    background: rgba(255, 255, 255, 0.75);
    border-color: rgba(255, 255, 255, 0.2);
}

.dark .note-widget.color-red {
    background: rgba(239, 68, 68, 0.75);
    border-color: rgba(239, 68, 68, 0.3);
}

.dark .note-widget.color-black {
    background: rgba(30, 41, 59, 0.90);
    border-color: rgba(30, 41, 59, 0.5);
}

.dark .note-widget.color-dodgerblue {
    background: rgba(30, 144, 255, 0.75);
    border-color: rgba(30, 144, 255, 0.3);
}

.dark .note-widget.color-warning {
    background: rgba(251, 191, 36, 0.75);
    border-color: rgba(251, 191, 36, 0.3);
}

.dark .note-widget.color-success {
    background: rgba(16, 185, 129, 0.75);
    border-color: rgba(16, 185, 129, 0.3);
}

.dark .note-widget-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(30, 41, 59, 0.5);
}

.dark .note-widget-body {
    background-image: 
        repeating-linear-gradient(
            transparent,
            transparent 31px,
            rgba(255, 255, 255, 0.1) 31px,
            rgba(255, 255, 255, 0.1) 32px
        );
}

.dark .note-widget-footer {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(30, 41, 59, 0.5);
}

.dark .note-widget-btn {
    background: rgba(30, 41, 59, 0.6);
    color: var(--text);
}

.dark .note-widget-btn:hover {
    background: rgba(30, 41, 59, 0.9);
}

/* Auto text color based on background */
.note-widget[data-bg-color] {
    /* Background color is set inline */
}

/* Utility function to determine text color based on background brightness */
.note-widget.light-text {
    color: #ffffff;
}

.note-widget.light-text .note-widget-title,
.note-widget.light-text .note-widget-content,
.note-widget.light-text .note-widget-footer {
    color: #ffffff;
}

.note-widget.light-text .note-widget-header,
.note-widget.light-text .note-widget-footer {
    border-color: rgba(255, 255, 255, 0.2);
}

.note-widget.dark-text {
    color: #0f172a;
}

.note-widget.dark-text .note-widget-title,
.note-widget.dark-text .note-widget-content,
.note-widget.dark-text .note-widget-footer {
    color: #0f172a;
}

.note-widget.dark-text .note-widget-header,
.note-widget.dark-text .note-widget-footer {
    border-color: rgba(0, 0, 0, 0.1);
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-primary {
    background-color: var(--accent);
    border-color: var(--accent);
}

.btn-primary:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    opacity: 0.9;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--muted);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h4 {
    color: var(--text);
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: var(--muted);
    margin-bottom: 1.5rem;
}

/* Resize handle */
.note-widget-resize {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 20px;
    height: 20px;
    cursor: nwse-resize;
    background: linear-gradient(-45deg, transparent 30%, rgba(0, 0, 0, 0.2) 30%, rgba(0, 0, 0, 0.2) 35%, transparent 35%, transparent 65%, rgba(0, 0, 0, 0.2) 65%, rgba(0, 0, 0, 0.2) 70%, transparent 70%);
}

.dark .note-widget-resize {
    background: linear-gradient(-45deg, transparent 30%, rgba(255, 255, 255, 0.2) 30%, rgba(255, 255, 255, 0.2) 35%, transparent 35%, transparent 65%, rgba(255, 255, 255, 0.2) 65%, rgba(255, 255, 255, 0.2) 70%, transparent 70%);
}

/* Color picker dropdown styles */
.note-color-picker-wrapper {
    position: relative;
}

.note-color-picker-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 0.5rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    min-width: 180px;
}

.color-option-dropdown {
    width: 32px;
    height: 32px;
    border: 2px solid var(--border);
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.color-option-dropdown:hover {
    transform: scale(1.15);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    border-color: var(--accent);
}

.color-option-dropdown.white {
    background: #ffffff;
}

.color-option-dropdown.red {
    background: #ef4444;
}

.color-option-dropdown.black {
    background: #1e293b;
}

.color-option-dropdown.dodgerblue {
    background: #1e90ff;
}

.color-option-dropdown.warning {
    background: #fbbf24;
}

.color-option-dropdown.success {
    background: #10b981;
}

.dark .note-color-picker-dropdown {
    background: var(--card);
    border-color: var(--border);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* Ensure delete modal is always on top */
#deleteNoteModal {
    z-index: 99999 !important;
}

#deleteNoteModal .modal-backdrop {
    z-index: 99998 !important;
}

#deleteNoteModal .modal-dialog {
    z-index: 100000 !important;
}

/* Autocomplete Portal Styles */
.note-autocomplete-portal {
    position: fixed !important;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    max-height: 300px;
    overflow-y: auto;
    z-index: 9999999 !important;
    min-width: 250px;
    max-width: 400px;
}

.dark .note-autocomplete-portal {
    background: var(--card);
    border-color: var(--border);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.note-autocomplete-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
    transition: background 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.note-autocomplete-item:last-child {
    border-bottom: none;
}

.note-autocomplete-item:hover,
.note-autocomplete-item.selected {
    background: var(--accent);
    color: white;
}

.note-autocomplete-item .item-icon {
    font-size: 1.2rem;
    opacity: 0.8;
}

.note-autocomplete-item .item-content {
    flex: 1;
}

.note-autocomplete-item .item-title {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.note-autocomplete-item .item-subtitle {
    font-size: 0.75rem;
    opacity: 0.8;
}

/* Patient link in note content - Badge Primary style */
.note-widget-content .note-content-link,
.note-content-link {
    display: inline-flex !important;
    align-items: center;
    gap: 0.4rem;
    background: var(--accent) !important;
    color: white !important;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    margin: 0 0.2rem;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.note-widget-content .note-content-link:hover,
.note-content-link:hover {
    background: var(--accent) !important;
    opacity: 0.9;
    transform: translateY(-1px);
    color: white !important;
}

.note-widget-content .note-content-link .patient-icon,
.note-content-link .patient-icon {
    font-size: 0.9rem;
}

.dark .note-widget-content .note-content-link,
.dark .note-content-link {
    background: var(--accent) !important;
    color: white !important;
}

.dark .note-widget-content .note-content-link:hover,
.dark .note-content-link:hover {
    background: var(--accent) !important;
    color: white !important;
    opacity: 0.9;
}

/* Appointment link in note content - Badge Secondary style */
.note-widget-content .note-content-appointment-link,
.note-content-appointment-link {
    display: inline-flex !important;
    align-items: center;
    gap: 0.4rem;
    background: #6c757d !important;
    color: white !important;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    margin: 0 0.2rem;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.note-widget-content .note-content-appointment-link:hover,
.note-content-appointment-link:hover {
    background: #5a6268 !important;
    transform: translateY(-1px);
    color: white !important;
}

.note-widget-content .note-content-appointment-link .appointment-icon,
.note-content-appointment-link .appointment-icon {
    font-size: 0.9rem;
}

.dark .note-widget-content .note-content-appointment-link,
.dark .note-content-appointment-link {
    background: #6c757d !important;
    color: white !important;
}

.dark .note-widget-content .note-content-appointment-link:hover,
.dark .note-content-appointment-link:hover {
    background: #5a6268 !important;
    color: white !important;
}

/* Drug badge in note content */
.note-widget-content .note-content-drug-badge,
.note-content-drug-badge {
    display: inline-flex !important;
    align-items: center;
    gap: 0.4rem;
    background: rgba(16, 185, 129, 0.15) !important;
    color: #10b981 !important;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    margin: 0 0.2rem;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.dark .note-widget-content .note-content-drug-badge,
.dark .note-content-drug-badge {
    background: rgba(16, 185, 129, 0.2) !important;
    color: #4ade80 !important;
    border-color: rgba(16, 185, 129, 0.4);
}

.note-widget-content .note-content-drug-badge .drug-icon,
.note-content-drug-badge .drug-icon {
    font-size: 0.9rem;
}
</style>

<div class="container-fluid">
    <div class="notes-toolbar">
        <div>
            <h5 class="mb-0" style="color: var(--text);">
                <i class="bi bi-sticky me-2"></i>
                My Notes
            </h5>
            <small class="text-muted" style="color: var(--text) !important;">Create and organize your personal notes</small>
        </div>
        <div class="notes-actions">
            <div class="color-picker-container">
                <label class="color-picker-label">Color:</label>
                <div class="color-options">
                    <div class="color-option white" data-color="white" data-bg="#ffffff" title="White"></div>
                    <div class="color-option red" data-color="red" data-bg="#ef4444" title="Red"></div>
                    <div class="color-option black" data-color="black" data-bg="#1e293b" title="Black"></div>
                    <div class="color-option dodgerblue" data-color="dodgerblue" data-bg="#1e90ff" title="Dodger Blue"></div>
                    <div class="color-option warning active" data-color="warning" data-bg="#fbbf24" title="Warning Yellow"></div>
                    <div class="color-option success" data-color="success" data-bg="#10b981" title="Success Green"></div>
                </div>
            </div>
            <button class="btn btn-primary" id="addNoteBtn">
                <i class="bi bi-plus-circle me-2"></i>
                Add Note
            </button>
        </div>
    </div>
    
    <div class="notes-container" id="notesContainer">
        <div class="empty-state" id="emptyState" style="display: none;">
            <i class="bi bi-sticky"></i>
            <h4>No notes yet</h4>
            <p>Click "Add Note" to create your first note</p>
        </div>
    </div>
</div>

<script>
// Color picker for new notes - default is warning yellow
let currentNoteColor = '#fbbf24';
let currentNoteColorClass = 'warning';

// Color options mapping
const colorMap = {
    'white': { bg: '#ffffff', class: 'white', text: 'dark-text' },
    'red': { bg: '#ef4444', class: 'red', text: 'light-text' },
    'black': { bg: '#1e293b', class: 'black', text: 'light-text' },
    'dodgerblue': { bg: '#1e90ff', class: 'dodgerblue', text: 'light-text' },
    'warning': { bg: '#fbbf24', class: 'warning', text: 'dark-text' },
    'success': { bg: '#10b981', class: 'success', text: 'light-text' }
};

// Color option click handlers
document.querySelectorAll('.color-option').forEach(option => {
    option.addEventListener('click', function() {
        // Remove active class from all options
        document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('active'));
        
        // Add active class to clicked option
        this.classList.add('active');
        
        // Update current color
        currentNoteColor = this.getAttribute('data-bg');
        currentNoteColorClass = this.getAttribute('data-color');
    });
});

// Get text color based on background brightness
function getTextColor(backgroundColor) {
    // Convert hex to RGB
    const hex = backgroundColor.replace('#', '');
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);
    
    // Calculate brightness
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    
    // Return light or dark text
    return brightness > 128 ? 'dark-text' : 'light-text';
}

// Get color class from background color
function getColorClass(backgroundColor) {
    for (const [key, value] of Object.entries(colorMap)) {
        if (value.bg.toLowerCase() === backgroundColor.toLowerCase()) {
            return value.class;
        }
    }
    // Default to warning if color not found
    return 'warning';
}

// Create note widget
function createNoteWidget(note) {
    const bgColor = note.background_color || '#fbbf24';
    const colorClass = getColorClass(bgColor);
    const textColorClass = colorMap[colorClass]?.text || 'dark-text';
    
    const widget = document.createElement('div');
    widget.className = `note-widget color-${colorClass} ${textColorClass}`;
    widget.id = `note-${note.id}`;
    widget.style.left = `${note.position_x || 0}px`;
    widget.style.top = `${note.position_y || 0}px`;
    widget.style.width = `${note.width || 300}px`;
    widget.style.height = `${note.height || 200}px`;
    widget.style.zIndex = note.z_index || 1;
    widget.setAttribute('data-bg-color', bgColor);
    widget.setAttribute('data-color-class', colorClass);
    widget.setAttribute('data-note-id', note.id);
    
    widget.innerHTML = `
        <div class="note-widget-header" onmousedown="startDrag(event, ${note.id})">
            <input type="text" class="note-widget-title" placeholder="Note title..." value="${note.title || ''}" 
                   data-note-id="${note.id}" onblur="updateNoteTitle(${note.id}, this.value)">
            <div class="note-widget-actions">
                <div class="note-color-picker-wrapper" style="position: relative;">
                    <button class="note-widget-btn" onclick="toggleColorPicker(${note.id}, event)" title="Change color">
                        <i class="bi bi-palette"></i>
                    </button>
                    <div class="note-color-picker-dropdown" id="colorPicker-${note.id}" style="display: none;">
                        <div class="color-option-dropdown white" data-color="white" data-bg="#ffffff" onclick="changeNoteColor(${note.id}, '#ffffff')" title="White"></div>
                        <div class="color-option-dropdown red" data-color="red" data-bg="#ef4444" onclick="changeNoteColor(${note.id}, '#ef4444')" title="Red"></div>
                        <div class="color-option-dropdown black" data-color="black" data-bg="#1e293b" onclick="changeNoteColor(${note.id}, '#1e293b')" title="Black"></div>
                        <div class="color-option-dropdown dodgerblue" data-color="dodgerblue" data-bg="#1e90ff" onclick="changeNoteColor(${note.id}, '#1e90ff')" title="Dodger Blue"></div>
                        <div class="color-option-dropdown warning" data-color="warning" data-bg="#fbbf24" onclick="changeNoteColor(${note.id}, '#fbbf24')" title="Warning Yellow"></div>
                        <div class="color-option-dropdown success" data-color="success" data-bg="#10b981" onclick="changeNoteColor(${note.id}, '#10b981')" title="Success Green"></div>
                    </div>
                </div>
                <button class="note-widget-btn" onclick="bringToFront(${note.id})" title="Bring to front">
                    <i class="bi bi-layers"></i>
                </button>
                <button class="note-widget-btn delete" onclick="deleteNote(${note.id})" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
        <div class="note-widget-body">
            <div class="note-widget-content" 
                 contenteditable="true"
                 data-placeholder="Write your note here... (Use @ for patients, # for appointments, $ for drugs)"
                 data-note-id="${note.id}" 
                 onblur="updateNoteContent(${note.id}, this.innerHTML)">${note.content || ''}</div>
        </div>
        <div class="note-widget-footer">
            <span>Created: ${new Date(note.created_at).toLocaleDateString()}</span>
            <span>Updated: ${new Date(note.updated_at).toLocaleDateString()}</span>
        </div>
        <div class="note-widget-resize" onmousedown="startResize(event, ${note.id})"></div>
    `;
    
    return widget;
}

// Load notes
async function loadNotes() {
    try {
        const response = await fetch('/api/notes', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            cache: 'no-cache'
        });
        
        // Check if response is ok
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error loading notes - HTTP:', response.status, errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Check content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Error loading notes - Invalid content type:', contentType, text);
            throw new Error('Response is not JSON');
        }
        
        const data = await response.json();
        
        if (data.success) {
            const container = document.getElementById('notesContainer');
            const emptyState = document.getElementById('emptyState');

            if (!container) {
                console.error('Notes container not found');
                return;
            }

            // Check if we have notes
            if (!data.notes || data.notes.length === 0) {
                // No notes - clear all widgets but keep emptyState
                const widgets = container.querySelectorAll('.note-widget');
                widgets.forEach(widget => widget.remove());
                
                // Show empty state
                if (emptyState) {
                    emptyState.style.display = 'block';
                }
                return;
            }

            // We have notes - hide empty state first
            if (emptyState) {
                emptyState.style.display = 'none';
            }
            
            // Clear existing widgets (but keep emptyState)
            const widgets = container.querySelectorAll('.note-widget');
            widgets.forEach(widget => widget.remove());
            
            // Add notes
            data.notes.forEach(note => {
                const widget = createNoteWidget(note);
                container.appendChild(widget);
                
                // Initialize autocomplete for this contenteditable
                const contentEditable = widget.querySelector('.note-widget-content[contenteditable="true"]');
                if (contentEditable) {
                    initAutocomplete(contentEditable);
                }
            });
        } else {
            console.error('Error loading notes:', data.message || 'Unknown error');
        }
    } catch (error) {
        console.error('Error loading notes:', error);
        // Don't show alert on dashboard load - it might be called from other pages
        if (window.location.pathname.includes('/doctor/notes')) {
            console.error('Failed to load notes:', error.message);
        }
    }
}

// Add new note
document.getElementById('addNoteBtn').addEventListener('click', async function() {
    const container = document.getElementById('notesContainer');
    const emptyState = document.getElementById('emptyState');
    if (emptyState) {
        emptyState.style.display = 'none';
    }
    
    // Get max z-index
    const existingNotes = container.querySelectorAll('.note-widget');
    let maxZIndex = 0;
    existingNotes.forEach(note => {
        const zIndex = parseInt(window.getComputedStyle(note).zIndex) || 0;
        if (zIndex > maxZIndex) maxZIndex = zIndex;
    });
    
    // Calculate position (center of visible area)
    const containerRect = container.getBoundingClientRect();
    const x = Math.max(0, (containerRect.width / 2) - 150);
    const y = Math.max(0, (containerRect.height / 2) - 100);
    
    try {
        const response = await fetch('/api/notes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                title: '',
                content: '',
                background_color: currentNoteColor,
                position_x: x,
                position_y: y,
                width: 300,
                height: 200,
                z_index: maxZIndex + 1
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Reload all notes to get the new one with full data
            loadNotes();
        }
    } catch (error) {
        console.error('Error creating note:', error);
        alert('Failed to create note. Please try again.');
    }
});

// Drag functionality
let isDragging = false;
let currentDragNote = null;
let dragOffset = { x: 0, y: 0 };

function startDrag(event, noteId) {
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA' || event.target.tagName === 'BUTTON') {
        return;
    }
    
    isDragging = true;
    currentDragNote = noteId;
    const widget = document.getElementById(`note-${noteId}`);
    const rect = widget.getBoundingClientRect();
    const containerRect = document.getElementById('notesContainer').getBoundingClientRect();
    
    dragOffset.x = event.clientX - rect.left;
    dragOffset.y = event.clientY - rect.top;
    
    widget.classList.add('dragging');
    bringToFront(noteId);
    
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDrag);
    
    event.preventDefault();
}

function onDrag(event) {
    if (!isDragging || !currentDragNote) return;
    
    const widget = document.getElementById(`note-${currentDragNote}`);
    const container = document.getElementById('notesContainer');
    const containerRect = container.getBoundingClientRect();
    
    let x = event.clientX - containerRect.left - dragOffset.x;
    let y = event.clientY - containerRect.top - dragOffset.y;
    
    // Constrain to container bounds
    x = Math.max(0, Math.min(x, containerRect.width - widget.offsetWidth));
    y = Math.max(0, Math.min(y, containerRect.height - widget.offsetHeight));
    
    widget.style.left = `${x}px`;
    widget.style.top = `${y}px`;
}

function stopDrag() {
    if (isDragging && currentDragNote) {
        const widget = document.getElementById(`note-${currentDragNote}`);
        widget.classList.remove('dragging');
        
        // Save position
        updateNotePosition(
            currentDragNote,
            parseInt(widget.style.left),
            parseInt(widget.style.top)
        );
        
        isDragging = false;
        currentDragNote = null;
    }
    
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
}

// Resize functionality
let isResizing = false;
let currentResizeNote = null;
let resizeStart = { x: 0, y: 0, width: 0, height: 0 };

function startResize(event, noteId) {
    isResizing = true;
    currentResizeNote = noteId;
    const widget = document.getElementById(`note-${noteId}`);
    const rect = widget.getBoundingClientRect();
    
    resizeStart.x = event.clientX;
    resizeStart.y = event.clientY;
    resizeStart.width = rect.width;
    resizeStart.height = rect.height;
    
    bringToFront(noteId);
    
    document.addEventListener('mousemove', onResize);
    document.addEventListener('mouseup', stopResize);
    
    event.preventDefault();
    event.stopPropagation();
}

function onResize(event) {
    if (!isResizing || !currentResizeNote) return;
    
    const widget = document.getElementById(`note-${currentResizeNote}`);
    const container = document.getElementById('notesContainer');
    const containerRect = container.getBoundingClientRect();
    const widgetRect = widget.getBoundingClientRect();
    
    const deltaX = event.clientX - resizeStart.x;
    const deltaY = event.clientY - resizeStart.y;
    
    let newWidth = resizeStart.width + deltaX;
    let newHeight = resizeStart.height + deltaY;
    
    // Constrain to container and min size
    newWidth = Math.max(300, Math.min(newWidth, containerRect.width - widgetRect.left));
    newHeight = Math.max(200, Math.min(newHeight, containerRect.height - widgetRect.top));
    
    widget.style.width = `${newWidth}px`;
    widget.style.height = `${newHeight}px`;
}

function stopResize() {
    if (isResizing && currentResizeNote) {
        const widget = document.getElementById(`note-${currentResizeNote}`);
        
        // Save size
        updateNoteSize(
            currentResizeNote,
            parseInt(widget.style.width),
            parseInt(widget.style.height)
        );
        
        isResizing = false;
        currentResizeNote = null;
    }
    
    document.removeEventListener('mousemove', onResize);
    document.removeEventListener('mouseup', stopResize);
}

// Bring to front
function bringToFront(noteId) {
    const widget = document.getElementById(`note-${noteId}`);
    const container = document.getElementById('notesContainer');
    const allNotes = container.querySelectorAll('.note-widget');
    
    let maxZIndex = 0;
    allNotes.forEach(note => {
        const zIndex = parseInt(window.getComputedStyle(note).zIndex) || 0;
        if (zIndex > maxZIndex) maxZIndex = zIndex;
    });
    
    widget.style.zIndex = maxZIndex + 1;
    updateNoteZIndex(noteId, maxZIndex + 1);
}

// Update functions
async function updateNoteTitle(noteId, title) {
    await updateNote(noteId, { title });
}

async function updateNoteContent(noteId, content) {
    // content is already HTML from contenteditable innerHTML
    await updateNote(noteId, { content: content });
}

async function updateNotePosition(noteId, x, y) {
    await updateNote(noteId, { position_x: x, position_y: y });
}

async function updateNoteSize(noteId, width, height) {
    await updateNote(noteId, { width, height });
}

async function updateNoteZIndex(noteId, zIndex) {
    await updateNote(noteId, { z_index: zIndex });
}

async function updateNote(noteId, data) {
    try {
        const response = await fetch(`/api/notes/${noteId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Network error' }));
            throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'Failed to update note');
        }
    } catch (error) {
        console.error('Error updating note:', error);
        // Don't show alert for every update - it's too frequent
        // Only log to console
    }
}

// Delete note
async function deleteNote(noteId) {
    // Show confirmation modal
    showDeleteConfirmModal(noteId);
}

// Show delete confirmation modal
function showDeleteConfirmModal(noteId) {
    const modal = document.getElementById('deleteNoteModal');
    if (!modal) {
        // Create modal if it doesn't exist
        const modalHtml = `
            <div class="modal fade" id="deleteNoteModal" tabindex="-1" aria-labelledby="deleteNoteModalLabel" aria-hidden="true" style="z-index: 99999;">
                <div class="modal-dialog modal-dialog-centered" style="z-index: 100000;">
                    <div class="modal-content" style="background: var(--card); border: 1px solid var(--border); z-index: 100001;">
                        <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                            <h5 class="modal-title" id="deleteNoteModalLabel" style="color: var(--text);">
                                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                                Delete Note
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="color: var(--text);">
                            <p>Are you sure you want to delete this note? This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer" style="border-top: 1px solid var(--border);">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                                <i class="bi bi-trash me-2"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
    
    const modalInstance = new bootstrap.Modal(document.getElementById('deleteNoteModal'));
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    
    // Remove previous event listeners
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    // Add new event listener
    newConfirmBtn.addEventListener('click', async function() {
        await performDelete(noteId);
        modalInstance.hide();
    });
    
    modalInstance.show();
}

// Perform the actual delete
async function performDelete(noteId) {
    try {
        const response = await fetch(`/api/notes/${noteId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        });
        
        // Check if response is ok
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Network error' }));
            throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            const widget = document.getElementById(`note-${noteId}`);
            if (widget) {
                widget.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                widget.style.opacity = '0';
                widget.style.transform = 'scale(0.8)';
                
                setTimeout(() => {
                    widget.remove();
                    
                    // Check if empty - verify after removal
                    const container = document.getElementById('notesContainer');
                    const emptyState = document.getElementById('emptyState');
                    
                    if (container && emptyState) {
                        // Double check: count remaining widgets
                        const remainingWidgets = container.querySelectorAll('.note-widget');
                        if (remainingWidgets.length === 0) {
                            emptyState.style.display = 'block';
                        } else {
                            emptyState.style.display = 'none';
                        }
                    }
                }, 300);
            } else {
                // Widget not found in DOM, but delete was successful
                // Reload notes to sync state
                loadNotes();
            }
        } else {
            throw new Error(data.message || 'Failed to delete note');
        }
    } catch (error) {
        console.error('Error deleting note:', error);
        alert('Failed to delete note: ' + error.message);
    }
}

// Toggle color picker dropdown
function toggleColorPicker(noteId, event) {
    event.stopPropagation();
    
    // Close all other color pickers
    document.querySelectorAll('.note-color-picker-dropdown').forEach(picker => {
        if (picker.id !== `colorPicker-${noteId}`) {
            picker.style.display = 'none';
        }
    });
    
    // Toggle current picker
    const picker = document.getElementById(`colorPicker-${noteId}`);
    if (picker) {
        if (picker.style.display === 'none' || !picker.style.display) {
            picker.style.display = 'flex';
            // Close on outside click
            setTimeout(() => {
                document.addEventListener('click', function closePicker(e) {
                    if (!picker.contains(e.target) && !e.target.closest(`#colorPicker-${noteId}`)) {
                        picker.style.display = 'none';
                        document.removeEventListener('click', closePicker);
                    }
                });
            }, 10);
        } else {
            picker.style.display = 'none';
        }
    }
}

// Change note color
function changeNoteColor(noteId, color) {
    // Close color picker
    const picker = document.getElementById(`colorPicker-${noteId}`);
    if (picker) {
        picker.style.display = 'none';
    }
    
    const widget = document.getElementById(`note-${noteId}`);
    if (!widget) return;
    
    const colorClass = getColorClass(color);
    const textColorClass = colorMap[colorClass]?.text || 'dark-text';
    
    // Remove all color classes
    widget.classList.remove('color-white', 'color-red', 'color-black', 'color-dodgerblue', 'color-warning', 'color-success');
    widget.classList.remove('light-text', 'dark-text');
    
    // Add new color classes
    widget.classList.add(`color-${colorClass}`);
    widget.classList.add(textColorClass);
    
    widget.setAttribute('data-bg-color', color);
    widget.setAttribute('data-color-class', colorClass);
    
    // Save to database immediately
    updateNote(noteId, { background_color: color });
}

// Add color picker to each note (optional - can be added to header)
// For now, we'll add it via a context menu or button

// Autocomplete functionality for notes
let autocompletePortal = null;
let currentAutocompleteType = null; // 'patient', 'appointment', 'drug'
let currentAutocompleteQuery = '';
let currentAutocompleteItems = [];
let selectedAutocompleteIndex = -1;
let autocompleteTextarea = null;
let autocompleteCursorPosition = 0;
let autocompleteDebounceTimer = null;

// Debounce function
function debounce(func, wait) {
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

// Escape HTML function
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize autocomplete for a contenteditable div
function initAutocomplete(contentEditable) {
    if (!contentEditable) return;
    
    contentEditable.addEventListener('input', handleContentEditableInput);
    contentEditable.addEventListener('keydown', handleContentEditableKeydown);
    contentEditable.addEventListener('blur', function() {
        // Delay hiding to allow click on autocomplete item
        setTimeout(() => {
            hideAutocomplete();
        }, 200);
    });
    
    // Handle clicks on links - allow navigation
    contentEditable.addEventListener('click', function(event) {
        const target = event.target;
        const link = target.closest('a[data-type]');
        
        if (link) {
            // Allow link navigation - open in new tab
            event.stopPropagation();
            event.preventDefault();
            window.open(link.href, '_blank');
            return false;
        }
    }, true); // Use capture phase to catch event early
    
    // Handle mousedown on badges to position cursor
    contentEditable.addEventListener('mousedown', function(event) {
        const target = event.target;
        const link = target.closest('a[data-type]');
        const badge = target.closest('span[data-type]');
        
        if (link) {
            // For links, don't prevent default - let click handler work
            event.stopPropagation();
            return true;
        }
        
        if (badge) {
            // For badges (drugs), position cursor before or after badge
            event.preventDefault();
            event.stopPropagation();
            const range = document.createRange();
            const selection = window.getSelection();
            const badgeRect = badge.getBoundingClientRect();
            const clickX = event.clientX;
            
            if (clickX < badgeRect.left + badgeRect.width / 2) {
                range.setStartBefore(badge);
            } else {
                range.setStartAfter(badge);
            }
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
            contentEditable.focus();
        }
    }, true); // Use capture phase
}

// Handle contenteditable input with debounce
function handleContentEditableInput(event) {
    const contentEditable = event.target;
    
    // Check if user is deleting content from autocomplete elements
    const selection = window.getSelection();
    if (selection.rangeCount > 0) {
        const range = selection.getRangeAt(0);
        const startContainer = range.startContainer;
        
        // Check if cursor is inside or at the edge of an autocomplete element
        let autocompleteElement = null;
        if (startContainer.nodeType === Node.TEXT_NODE) {
            autocompleteElement = startContainer.parentElement;
        } else if (startContainer.nodeType === Node.ELEMENT_NODE) {
            autocompleteElement = startContainer;
        }
        
        // Check if it's an autocomplete element (patient, appointment, or drug)
        while (autocompleteElement && autocompleteElement !== contentEditable) {
            const dataType = autocompleteElement.getAttribute('data-type');
            if (dataType === 'patient' || dataType === 'appointment' || dataType === 'drug') {
                // Check if user is actually deleting (not just clicking)
                const inputType = event.inputType;
                if (inputType === 'deleteContentBackward' || inputType === 'deleteContentForward' || 
                    inputType === 'deleteByDrag' || inputType === 'deleteByCut' ||
                    (!inputType && event.data === null)) {
                    // User is editing/deleting from an autocomplete element
                    // Remove the entire element
                    const parent = autocompleteElement.parentNode;
                    if (parent) {
                        // Create a text node with space to maintain cursor position
                        const space = document.createTextNode(' ');
                        parent.replaceChild(space, autocompleteElement);
                        
                        // Set cursor after space
                        const newRange = document.createRange();
                        newRange.setStartAfter(space);
                        newRange.collapse(true);
                        selection.removeAllRanges();
                        selection.addRange(newRange);
                        
                        // Ensure focus
                        contentEditable.focus();
                        
                        // Update note content
                        const noteId = contentEditable.getAttribute('data-note-id');
                        if (noteId) {
                            updateNoteContent(parseInt(noteId), contentEditable.innerHTML);
                        }
                    }
                    return; // Don't process autocomplete after deletion
                }
                // If not deleting, allow normal interaction
                break;
            }
            autocompleteElement = autocompleteElement.parentElement;
        }
    }
    
    // Clear previous debounce timer
    if (autocompleteDebounceTimer) {
        clearTimeout(autocompleteDebounceTimer);
    }
    
    // Debounce the actual processing
    autocompleteDebounceTimer = setTimeout(() => {
        processAutocompleteInput(event);
    }, 300);
}

// Process autocomplete input (called after debounce)
function processAutocompleteInput(event) {
    const contentEditable = event.target;
    const selection = window.getSelection();
    
    if (!selection.rangeCount) {
        hideAutocomplete();
        return;
    }
    
    // Get fresh range (may have changed after debounce)
    const range = selection.getRangeAt(0).cloneRange();
    const textNode = range.startContainer;
    
    // Get all text content before cursor position
    let textBeforeCursor = '';
    
    // Create a range from start of contentEditable to cursor
    const fullRange = document.createRange();
    fullRange.selectNodeContents(contentEditable);
    fullRange.setEnd(range.startContainer, range.startOffset);
    textBeforeCursor = fullRange.toString();
    
    // Check for @ (patient), # (appointment), or $ (drug)
    const match = textBeforeCursor.match(/(@|#|\$)([^\s@#$]*)$/);
    
    if (match) {
        const trigger = match[1];
        const query = match[2];
        
        // Minimum query length: 2 characters for patients and drugs
        // For appointments: if numeric (ID search), allow 1 char; if text (patient name), require 2 chars
        let minLength = 2;
        if (trigger === '#') {
            // For appointments: if query is numeric (ID search), allow 1 char; otherwise require 2 chars
            minLength = /^\d+$/.test(query) ? 1 : 2;
        }
        
        if (query.length >= minLength && query !== currentAutocompleteQuery) {
            currentAutocompleteType = trigger === '@' ? 'patient' : (trigger === '#' ? 'appointment' : 'drug');
            currentAutocompleteQuery = query;
            autocompleteTextarea = contentEditable;
            
            // Get fresh cursor position
            const rect = range.getBoundingClientRect();
            autocompleteCursorPosition = {
                range: range,
                textBefore: textBeforeCursor,
                match: match
            };
            
            showAutocomplete(contentEditable, rect, query);
        } else if (query.length < minLength) {
            hideAutocomplete();
        }
        // If query hasn't changed, don't reload
    } else {
        hideAutocomplete();
    }
}

// Handle keyboard navigation in autocomplete
function handleContentEditableKeydown(event) {
    const contentEditable = event.target;
    const selection = window.getSelection();
    
    // Check if cursor is inside an autocomplete element (badge/link)
    if (selection.rangeCount > 0) {
        const range = selection.getRangeAt(0);
        let node = range.startContainer;
        
        // Check if we're inside a badge or link
        while (node && node !== contentEditable) {
            if (node.nodeType === Node.ELEMENT_NODE) {
                const dataType = node.getAttribute('data-type');
                if (dataType === 'patient' || dataType === 'appointment' || dataType === 'drug') {
                    // User is trying to type inside an autocomplete element
                    // Move cursor outside the element
                    if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar' || event.keyCode === 13 || event.keyCode === 32) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        // Create a new text node after the element
                        const textContent = (event.key === 'Enter' || event.keyCode === 13) ? '\n' : ' ';
                        const newTextNode = document.createTextNode(textContent);
                        const parent = node.parentNode;
                        
                        if (parent) {
                            parent.insertBefore(newTextNode, node.nextSibling);
                            
                            // Set cursor after the new text node
                            const newRange = document.createRange();
                            newRange.setStartAfter(newTextNode);
                            newRange.collapse(true);
                            selection.removeAllRanges();
                            selection.addRange(newRange);
                            contentEditable.focus();
                            
                            // Update note content
                            const noteId = contentEditable.getAttribute('data-note-id');
                            if (noteId) {
                                updateNoteContent(parseInt(noteId), contentEditable.innerHTML);
                            }
                        }
                        return;
                    }
                    break;
                }
            }
            node = node.parentNode;
        }
    }
    
    // Handle autocomplete portal navigation
    if (!autocompletePortal || autocompletePortal.style.display === 'none') {
        return;
    }
    
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        selectedAutocompleteIndex = Math.min(selectedAutocompleteIndex + 1, currentAutocompleteItems.length - 1);
        updateAutocompleteSelection();
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        selectedAutocompleteIndex = Math.max(selectedAutocompleteIndex - 1, -1);
        updateAutocompleteSelection();
    } else if (event.key === 'Enter' || event.key === 'Tab') {
        event.preventDefault();
        if (selectedAutocompleteIndex >= 0 && currentAutocompleteItems[selectedAutocompleteIndex]) {
            selectAutocompleteItem(currentAutocompleteItems[selectedAutocompleteIndex]);
        }
    } else if (event.key === 'Escape') {
        hideAutocomplete();
    }
}

// Show autocomplete portal
async function showAutocomplete(contentEditable, cursorRect, query) {
    if (!autocompletePortal) {
        autocompletePortal = document.createElement('div');
        autocompletePortal.className = 'note-autocomplete-portal';
        autocompletePortal.id = 'noteAutocompletePortal';
        document.body.appendChild(autocompletePortal);
    }
    
    // Position the portal near the cursor - use fixed positioning for highest z-index
    const x = cursorRect.left + window.scrollX;
    const y = cursorRect.bottom + window.scrollY + 5;
    
    autocompletePortal.style.position = 'fixed';
    autocompletePortal.style.left = `${x}px`;
    autocompletePortal.style.top = `${y}px`;
    autocompletePortal.style.display = 'block';
    autocompletePortal.style.zIndex = '9999999';
    
    // Load autocomplete items
    await loadAutocompleteItems(query);
}

// Load autocomplete items based on type
async function loadAutocompleteItems(query) {
    try {
        // Verify query hasn't changed (user may have continued typing)
        if (query !== currentAutocompleteQuery) {
            return; // Query changed, ignore this response
        }
        
        let url = '';
        if (currentAutocompleteType === 'patient') {
            url = `/api/patients/search?q=${encodeURIComponent(query)}`;
        } else if (currentAutocompleteType === 'appointment') {
            url = `/api/appointments/search?q=${encodeURIComponent(query)}&limit=10`;
        } else if (currentAutocompleteType === 'drug') {
            url = `/api/searchDrugsAutocomplete?q=${encodeURIComponent(query)}&limit=10`;
        }
        
        if (!url) return;
        
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            if (response.status !== 400 && response.status !== 404) {
                console.error('Error loading autocomplete:', response.status);
            }
            return;
        }
        
        const data = await response.json();
        
        // Double-check query hasn't changed
        if (query !== currentAutocompleteQuery) {
            return; // Query changed during fetch, ignore this response
        }
        
        let items = [];
        
        if (currentAutocompleteType === 'patient' && data.ok && data.data) {
            items = data.data.map(patient => ({
                type: 'patient',
                id: patient.id,
                title: `${patient.first_name} ${patient.last_name}`,
                subtitle: patient.phone || '',
                data: patient
            }));
        } else if (currentAutocompleteType === 'appointment' && data.ok && data.data) {
            items = data.data.map(apt => {
                const date = new Date(apt.date);
                const dateStr = date.toLocaleDateString('en-GB');
                const timeStr = apt.start_time ? apt.start_time.substring(0, 5) : '';
                const patientName = escapeHtml(apt.patient_name || 'Unknown');
                const status = escapeHtml(apt.status || '');
                return {
                    type: 'appointment',
                    id: apt.id,
                    title: `#${apt.id} - ${patientName}`,
                    subtitle: `${dateStr} ${timeStr} - ${status}`,
                    data: apt
                };
            });
        } else if (currentAutocompleteType === 'drug' && data.drugs) {
            items = data.drugs.map(drug => ({
                type: 'drug',
                id: drug.ID,
                title: drug.drug_name,
                subtitle: drug.active_ingredient || drug.Company || '',
                data: drug
            }));
        }
        
        // Final check: query still matches
        if (query === currentAutocompleteQuery) {
            currentAutocompleteItems = items;
            selectedAutocompleteIndex = -1;
            renderAutocompleteItems(items);
        }
    } catch (error) {
        console.error('Error loading autocomplete items:', error);
    }
}

// Render autocomplete items
function renderAutocompleteItems(items) {
    if (!autocompletePortal) return;
    
    if (items.length === 0) {
        autocompletePortal.innerHTML = '<div class="note-autocomplete-item"><div class="item-content">No results found</div></div>';
        return;
    }
    
    let html = '';
    items.forEach((item, index) => {
        const icon = item.type === 'patient' ? 'bi-person' : (item.type === 'appointment' ? 'bi-calendar-event' : 'bi-capsule');
        html += `
            <div class="note-autocomplete-item ${index === selectedAutocompleteIndex ? 'selected' : ''}" 
                 data-index="${index}"
                 onclick="selectAutocompleteItem(${JSON.stringify(item).replace(/"/g, '&quot;')})">
                <i class="bi ${icon} item-icon"></i>
                <div class="item-content">
                    <div class="item-title">${escapeHtml(item.title)}</div>
                    ${item.subtitle ? `<div class="item-subtitle">${escapeHtml(item.subtitle)}</div>` : ''}
                </div>
            </div>
        `;
    });
    
    autocompletePortal.innerHTML = html;
}

// Update autocomplete selection
function updateAutocompleteSelection() {
    if (!autocompletePortal) return;
    
    const items = autocompletePortal.querySelectorAll('.note-autocomplete-item');
    items.forEach((item, index) => {
        if (index === selectedAutocompleteIndex) {
            item.classList.add('selected');
            item.scrollIntoView({ block: 'nearest' });
        } else {
            item.classList.remove('selected');
        }
    });
}

// Select autocomplete item
function selectAutocompleteItem(item) {
    if (!autocompleteTextarea || !item || !autocompleteCursorPosition) return;
    
    const contentEditable = autocompleteTextarea;
    const range = autocompleteCursorPosition.range;
    const match = autocompleteCursorPosition.match;
    
    if (match && range) {
        // Delete the trigger and query text
        range.setStart(range.startContainer, range.startOffset - match[0].length);
        range.deleteContents();
        
        // Create replacement element
        let replacement = null;
        if (item.type === 'patient') {
            replacement = document.createElement('a');
            replacement.href = `/doctor/patients/${item.id}`;
            replacement.className = 'note-content-link';
            replacement.target = '_blank';
            replacement.setAttribute('data-type', 'patient');
            replacement.setAttribute('data-id', item.id);
            replacement.innerHTML = `<i class="bi bi-person patient-icon"></i>${escapeHtml(item.title)}`;
        } else if (item.type === 'appointment') {
            replacement = document.createElement('a');
            replacement.href = `/doctor/appointments/${item.id}`;
            replacement.className = 'note-content-appointment-link';
            replacement.target = '_blank';
            replacement.setAttribute('data-type', 'appointment');
            replacement.setAttribute('data-id', item.id);
            replacement.innerHTML = `<i class="bi bi-calendar-event appointment-icon"></i>#${item.id}`;
        } else if (item.type === 'drug') {
            replacement = document.createElement('span');
            replacement.className = 'note-content-drug-badge';
            replacement.setAttribute('data-type', 'drug');
            replacement.setAttribute('data-id', item.id);
            replacement.innerHTML = `<i class="bi bi-capsule drug-icon"></i>${escapeHtml(item.title)}`;
        }
        
        if (replacement) {
            // Insert replacement
            range.insertNode(replacement);
            
            // Add space after replacement to allow typing
            const spaceAfter = document.createTextNode(' ');
            range.setStartAfter(replacement);
            range.insertNode(spaceAfter);
            
            // Set cursor after space - create fresh range
            const newRange = document.createRange();
            newRange.setStartAfter(spaceAfter);
            newRange.collapse(true);
            
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(newRange);
            
            // Force focus and ensure typing works - especially for drug badges
            setTimeout(() => {
                contentEditable.focus();
                
                // Create a new range after the space node
                const finalRange = document.createRange();
                const finalSelection = window.getSelection();
                
                // Ensure we have a text node after the replacement for typing
                let textNodeAfter = spaceAfter;
                
                // If spaceAfter is not a direct child or not found, create one
                if (!spaceAfter.parentNode || spaceAfter.parentNode !== contentEditable) {
                    // Find the replacement's parent
                    const parent = replacement.parentNode;
                    if (parent) {
                        // Check if there's already a text node after replacement
                        let nextSibling = replacement.nextSibling;
                        if (nextSibling && nextSibling.nodeType === Node.TEXT_NODE) {
                            textNodeAfter = nextSibling;
                        } else {
                            // Create a new text node after replacement
                            textNodeAfter = document.createTextNode(' ');
                            parent.insertBefore(textNodeAfter, replacement.nextSibling);
                        }
                    }
                }
                
                // Set cursor after the text node
                try {
                    finalRange.setStartAfter(textNodeAfter);
                    finalRange.collapse(true);
                } catch (e) {
                    // Fallback: create a text node at the end
                    const endTextNode = document.createTextNode(' ');
                    contentEditable.appendChild(endTextNode);
                    finalRange.setStartAfter(endTextNode);
                    finalRange.collapse(true);
                }
                
                finalSelection.removeAllRanges();
                finalSelection.addRange(finalRange);
                
                // Ensure contenteditable is ready for input
                contentEditable.focus();
                
                // Additional check: ensure cursor is not inside replacement
                setTimeout(() => {
                    const checkRange = finalSelection.getRangeAt(0);
                    let checkNode = checkRange.startContainer;
                    while (checkNode && checkNode !== contentEditable) {
                        if (checkNode === replacement) {
                            // Cursor is inside replacement, move it out
                            const parent = replacement.parentNode;
                            if (parent) {
                                const newTextNode = document.createTextNode(' ');
                                parent.insertBefore(newTextNode, replacement.nextSibling);
                                const newRange = document.createRange();
                                newRange.setStartAfter(newTextNode);
                                newRange.collapse(true);
                                finalSelection.removeAllRanges();
                                finalSelection.addRange(newRange);
                                contentEditable.focus();
                            }
                            break;
                        }
                        checkNode = checkNode.parentNode;
                    }
                }, 50);
            }, 200);
            
            // Update note content
            const noteId = contentEditable.getAttribute('data-note-id');
            if (noteId) {
                updateNoteContent(parseInt(noteId), contentEditable.innerHTML);
            }
        }
    }
    
    hideAutocomplete();
    contentEditable.focus();
}

// Hide autocomplete
function hideAutocomplete() {
    if (autocompletePortal) {
        autocompletePortal.style.display = 'none';
    }
    currentAutocompleteType = null;
    currentAutocompleteQuery = '';
    currentAutocompleteItems = [];
    selectedAutocompleteIndex = -1;
    autocompleteTextarea = null;
}

// Initialize autocomplete for all note content editables
function initAllAutocompletes() {
    document.querySelectorAll('.note-widget-content[contenteditable="true"]').forEach(contentEditable => {
        initAutocomplete(contentEditable);
    });
}

// Load notes on page load - only if we're on the notes page
if (window.location.pathname.includes('/doctor/notes')) {
    // Wait for DOM to be fully loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            loadNotes();
            // Initialize autocomplete after notes are loaded
            setTimeout(initAllAutocompletes, 500);
        });
    } else {
        loadNotes();
        setTimeout(initAllAutocompletes, 500);
    }
}

// Re-initialize autocomplete when new notes are added
const originalLoadNotes = loadNotes;
loadNotes = async function() {
    await originalLoadNotes();
    setTimeout(initAllAutocompletes, 100);
};
</script>

