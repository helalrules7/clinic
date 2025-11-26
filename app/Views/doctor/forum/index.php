<?php
/**
 * Doctor Forum Main Page
 * صفحة المنتدى الرئيسية للأطباء
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

.forum-container {
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

.dark .forum-container {
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

.forum-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding: 0.25rem;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
}

.forum-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.btn-new-topic {
    background: var(--accent);
    color: white;
    border: none;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-new-topic:hover {
    background: var(--accent);
    opacity: 0.9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
}

.forum-topics-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.forum-topic-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.forum-topic-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    border-color: var(--accent);
}

.forum-topic-card.pinned {
    border-left: 4px solid var(--accent);
    background: linear-gradient(90deg, rgba(14, 165, 233, 0.05) 0%, var(--card) 5%);
}

.forum-topic-card.resolved {
    border-left: 4px solid var(--success);
    background: linear-gradient(90deg, rgba(16, 185, 129, 0.05) 0%, var(--card) 5%);
}

.dark .forum-topic-card.pinned {
    background: linear-gradient(90deg, rgba(56, 189, 248, 0.1) 0%, var(--card) 5%);
}

.forum-topic-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.forum-topic-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text);
    margin: 0;
    flex: 1;
}

.forum-topic-meta {
    display: flex;
    gap: 1rem;
    align-items: center;
    font-size: 0.875rem;
    color: var(--muted);
    margin-top: 0.5rem;
    flex-wrap: wrap;
}

.forum-topic-author {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.forum-author-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border);
}

.forum-author-avatar-placeholder {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--muted);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    border: 2px solid var(--border);
}

.forum-topic-content {
    color: var(--muted);
    margin-bottom: 1rem;
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.forum-topic-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
}

.forum-topic-tags {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.forum-tag {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.forum-tag.custom {
    background: rgba(239, 68, 68, 0.1) !important;
    color: var(--danger) !important;
    border: 1px solid rgba(239, 68, 68, 0.2) !important;
    text-decoration: none !important;
}

.forum-tag.custom span {
    text-decoration: none !important;
}

.forum-tag.patient {
    background: rgba(14, 165, 233, 0.1);
    color: var(--accent);
    border: 1px solid rgba(14, 165, 233, 0.2);
    text-decoration: none !important;
}

.forum-tag.patient span {
    text-decoration: none !important;
}

.forum-tag.appointment {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
    border: 1px solid rgba(16, 185, 129, 0.2);
    text-decoration: none !important;
}

.forum-tag.appointment span {
    text-decoration: none !important;
}

.forum-tag.drug {
    background: rgba(251, 191, 36, 0.1);
    color: #f59e0b;
    border: 1px solid rgba(251, 191, 36, 0.2);
    text-decoration: none !important;
    cursor: pointer;
    transition: all 0.2s ease;
}

.forum-tag.drug:hover {
    background: rgba(251, 191, 36, 0.2);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(251, 191, 36, 0.2);
}

/* Drug Popover - Glass Effect */
.forum-drug-popover {
    position: fixed;
    z-index: 10000000;
    background: rgba(248, 250, 252, 0.85);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(226, 232, 240, 0.5);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    min-width: 300px;
    max-width: 500px;
    max-height: 80vh;
    overflow-y: auto;
    animation: fadeInPopover 0.2s ease;
}

.dark .forum-drug-popover {
    background: rgba(11, 18, 32, 0.85);
    border-color: rgba(51, 65, 85, 0.5);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
}

.forum-drug-popover-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
}

.forum-drug-popover-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text);
    margin: 0;
}

.forum-drug-popover-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--muted);
    cursor: pointer;
    padding: 0;
    line-height: 1;
    transition: color 0.2s ease;
}

.forum-drug-popover-close:hover {
    color: var(--text);
}

.forum-drug-popover-body {
    color: var(--text);
}

.forum-drug-popover-item {
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--border);
}

.forum-drug-popover-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.forum-drug-popover-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.forum-drug-popover-value {
    font-size: 0.875rem;
    color: var(--text);
    word-break: break-word;
}

@keyframes fadeInPopover {
    from {
        opacity: 0;
        transform: translateY(-10px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.forum-drug-popover-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
    z-index: 9999999;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.forum-tag.drug span {
    text-decoration: none !important;
}

/* Patient and Appointment Badges */
.forum-patient-badge,
.forum-appointment-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
}

.forum-patient-badge {
    background: rgba(14, 165, 233, 0.15);
    color: var(--accent);
    border: 1px solid rgba(14, 165, 233, 0.3);
}

.forum-patient-badge:hover {
    background: rgba(14, 165, 233, 0.25);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(14, 165, 233, 0.2);
}

.forum-appointment-badge {
    background: rgba(16, 185, 129, 0.15);
    color: var(--success);
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.forum-appointment-badge:hover {
    background: rgba(16, 185, 129, 0.25);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
}

/* Topic Actions */
.forum-topic-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.forum-action-btn {
    width: 32px;
    height: 32px;
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
    font-size: 0.9rem;
}

.forum-action-btn:hover {
    background: rgba(255, 255, 255, 0.9);
    transform: scale(1.1);
}

.forum-action-btn.edit-btn {
    color: var(--accent);
}

.forum-action-btn.edit-btn:hover {
    background: rgba(14, 165, 233, 0.2);
}

.forum-action-btn.pin-btn {
    color: var(--accent);
}

.forum-action-btn.pin-btn:hover {
    background: rgba(14, 165, 233, 0.2);
}

.forum-action-btn.pin-btn.pinned {
    color: var(--accent);
    background: rgba(14, 165, 233, 0.15);
}

.forum-action-btn.pin-btn.pinned:hover {
    background: rgba(14, 165, 233, 0.25);
}

.forum-action-btn.delete-btn {
    color: var(--danger);
}

.forum-action-btn.delete-btn:hover {
    background: rgba(239, 68, 68, 0.2);
}

.dark .forum-action-btn {
    background: rgba(30, 41, 59, 0.6);
    color: var(--text);
}

.dark .forum-action-btn:hover {
    background: rgba(30, 41, 59, 0.9);
}

.forum-topic-stats {
    display: flex;
    gap: 1.5rem;
    align-items: center;
    font-size: 0.875rem;
    color: var(--muted);
}

.forum-empty {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--muted);
}

.forum-empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* Modal for new topic - Glass Effect */
.forum-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.forum-modal.show {
    display: flex;
}

.forum-modal-content {
    /* Glass effect - similar to sidebar */
    background: rgba(248, 250, 252, 0.7) !important;
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border: 1px solid rgba(226, 232, 240, 0.4) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.15);
    border-radius: 12px;
    padding: 2rem;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    color: var(--text) !important;
}

.dark .forum-modal-content {
    background: rgba(11, 18, 32, 0.40) !important;
    border: 1px solid rgba(51, 65, 85, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3);
}

.forum-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.4);
    background: transparent !important;
}

.dark .forum-modal-header {
    border-bottom-color: rgba(51, 65, 85, 0.3);
}

.forum-modal-body {
    background: transparent !important;
    color: var(--text) !important;
    padding: 1rem 0;
}

.forum-modal-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text);
    margin: 0;
}

.forum-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--muted);
    cursor: pointer;
    padding: 0.5rem;
    line-height: 1;
}

.forum-form-group {
    margin-bottom: 1rem;
}

.forum-form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text);
}

.forum-form-input,
.forum-form-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--card);
    color: var(--text);
    font-size: 1rem;
    font-family: inherit;
}

.forum-form-textarea {
    min-height: 200px;
    resize: vertical;
}

/* Rich Text Editor Toolbar */
.forum-editor-toolbar {
    display: flex;
    gap: 0.5rem;
    padding: 0.5rem;
    background: var(--card);
    border: 1px solid var(--border);
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    flex-wrap: wrap;
}

.forum-editor-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 4px;
    background: transparent;
    color: var(--text);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 0.9rem;
}

.forum-editor-btn:hover {
    background: rgba(14, 165, 233, 0.1);
    color: var(--accent);
}

.forum-editor-separator {
    width: 1px;
    height: 24px;
    background: var(--border);
    margin: 0 0.25rem;
}

.forum-form-textarea[contenteditable="true"] {
    min-height: 200px;
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-top: none;
    border-radius: 0 0 8px 8px;
    background: var(--card);
    color: var(--text);
    outline: none;
}

.forum-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-cancel {
    background: var(--muted);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
}

.btn-submit {
    background: var(--accent);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

/* Tag styling similar to notes */
.forum-content-link,
.forum-content-appointment-link,
.forum-content-drug-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    text-decoration: none;
    margin: 0 0.125rem;
    vertical-align: baseline;
}

.forum-content-link {
    color: var(--accent);
    background: rgba(14, 165, 233, 0.1);
    border: 1px solid rgba(14, 165, 233, 0.2);
}

.forum-content-appointment-link {
    color: var(--success);
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.forum-content-drug-badge {
    color: #f59e0b;
    background: rgba(251, 191, 36, 0.1);
    border: 1px solid rgba(251, 191, 36, 0.2);
}

/* Autocomplete - Meta Tags (Absolute Positioning like Drugs) */
.forum-meta-autocomplete-portal {
    position: absolute;
    z-index: 99999;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    max-height: 300px;
    overflow-y: auto;
    min-width: 250px;
    max-width: 400px;
    display: none;
}

/* Autocomplete - Content Textarea (Fixed Positioning like Notes) */
.forum-content-autocomplete-portal {
    position: fixed !important;
    z-index: 99999 !important;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    max-height: 300px;
    overflow-y: auto;
    min-width: 250px;
    max-width: 400px;
    display: none;
}

/* Search Autocomplete Portal - Full width like drugs.php */
#forumSearchAutocomplete {
    position: absolute;
    z-index: 99999;
    background: var(--card);
    border: 1px solid var(--border);
    border-top: none;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    max-height: 300px;
    overflow-y: auto;
    display: none;
}

.dark #forumSearchAutocomplete {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .forum-meta-autocomplete-portal,
.dark .forum-content-autocomplete-portal {
    background: var(--card);
    border-color: var(--border);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.forum-autocomplete-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.forum-autocomplete-item:last-child {
    border-bottom: none;
}

.forum-autocomplete-item:hover,
.forum-autocomplete-item.selected {
    background: rgba(14, 165, 233, 0.1);
}

.forum-autocomplete-item .item-icon {
    font-size: 1.25rem;
}

.forum-autocomplete-item .item-content {
    flex: 1;
}

.forum-autocomplete-item .item-title {
    font-weight: 500;
    color: var(--text);
    margin-bottom: 0.25rem;
}

.forum-autocomplete-item .item-subtitle {
    font-size: 0.875rem;
    color: var(--muted);
}

@media (max-width: 768px) {
    .forum-container {
        padding: 1rem;
    }
    
    .forum-toolbar {
        flex-direction: row;
        gap: 0.5rem;
        align-items: center;
        padding: 0.5rem;
        flex-wrap: nowrap;
    }
    
    .forum-toolbar h3 {
        font-size: 1rem;
        margin: 0 !important;
        flex: 1;
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .forum-actions {
        flex-shrink: 0;
        gap: 0.5rem;
    }
    
    .btn-new-topic {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        white-space: nowrap;
        gap: 0.375rem;
    }
    
    .btn-new-topic i {
        font-size: 1rem;
        flex-shrink: 0;
    }
    
    .btn-new-topic .btn-text {
        display: inline;
    }
    
    @media (max-width: 480px) {
        .btn-new-topic .btn-text {
            display: none;
        }
        
        .btn-new-topic {
            padding: 0.5rem;
            min-width: 40px;
            justify-content: center;
        }
    }
    
    .forum-topic-card {
        padding: 1rem;
    }
    
    .forum-modal-content {
        width: 95%;
        padding: 1.5rem;
    }
}
</style>

<div class="forum-container">
    <!-- Category Statistics Cards -->
    <div id="categoryStatsCards" class="forum-category-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
        <!-- Will be populated by JavaScript -->
    </div>

        <!-- Top Meta Tags Badges -->
 <div id="topMetaTags" class="forum-top-meta-badges" style="margin-bottom: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap; padding: 1rem; background: var(--card); border: 1px solid var(--border); border-radius: 8px;">
        <!-- Will be populated by JavaScript -->
    </div>

    <!-- Search Bar -->
    <div class="forum-search-bar" style="margin-bottom: 1rem; position: relative;">
        <div style="position: relative;">
            <input type="text" id="forumSearchInput" class="forum-form-input" placeholder="Search topics..." style="padding-left: 2.5rem;" autocomplete="off">
            <i class="bi bi-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--muted);"></i>
        </div>
        <div id="forumSearchAutocomplete" class="forum-meta-autocomplete-portal" style="display: none;"></div>
    </div>

    <div class="forum-toolbar">
        <h3 style="margin: 0; color: var(--text); margin-left: 1rem !important; margin-right: 1rem !important; flex: 1; min-width: 0;">Discussions</h3>
        <div class="forum-actions" style="flex-shrink: 0;">
            <button class="btn-new-topic" onclick="showNewTopicModal()">
                <i class="bi bi-plus-circle"></i>
                <span class="btn-text">New Discussion</span>
            </button>
        </div>
    </div>

    <!-- Pinned Topics Section -->
    <div id="pinnedTopicsSection" class="forum-pinned-section" style="margin-bottom: 1rem;">
        <h3 style="color: var(--text); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="bi bi-pin-angle-fill" style="color: var(--accent);"></i>
            Pinned Topics
        </h3>
        <div id="pinnedTopicsList" class="forum-topics-list">
            <!-- Will be populated by JavaScript -->
        </div>
    </div>

    <!-- Regular Topics Section -->
    <div id="regularTopicsSection">
        <h3 style="color: var(--text); margin-bottom: 1rem;">All Topics</h3>
        <div id="forumTopicsList" class="forum-topics-list">
            <div class="forum-empty">
                <div class="forum-empty-icon">💬</div>
                <p>Loading topics...</p>
            </div>
        </div>
    </div>
</div>

<!-- New Topic Modal -->
<div id="newTopicModal" class="forum-modal">
    <div class="forum-modal-content">
        <div class="forum-modal-header">
            <h3 class="forum-modal-title">Create New Discussion</h3>
            <button class="forum-modal-close" onclick="hideNewTopicModal()">&times;</button>
        </div>
        <form id="newTopicForm">
            <div class="forum-form-group">
                <label class="forum-form-label" for="topicTitle">Title</label>
                <input type="text" id="topicTitle" class="forum-form-input" required placeholder="Enter discussion title">
            </div>
            <div class="forum-form-group">
                <label class="forum-form-label" for="topicCategory">Category</label>
                <select id="topicCategory" class="forum-form-input" required>
                    <option value="All">All</option>
                    <option value="General Discussion">General Discussion</option>
                    <option value="Clinical Case">Clinical Case</option>
                    <option value="Procedure Feedback">Procedure Feedback</option>
                    <option value="Protocol Update">Protocol Update</option>
                    <option value="Drug Interaction">Drug Interaction</option>
                    <option value="Prescription Inquiry">Prescription Inquiry</option>
                    <option value="Lab/Imaging Interpretation">Lab/Imaging Interpretation</option>
                </select>
            </div>
            <div class="forum-form-group">
                <label class="forum-form-label" for="topicMeta">Meta Tags <small style="color: var(--muted);">(Use @ for patients, # for appointments, $ for drugs, or type custom tags and then press ; or Enter to add)</small></label>
                <div id="topicMetaContainer" class="forum-meta-container" style="min-height: 60px; padding: 0.5rem; border: 1px solid var(--border); border-radius: 8px; background: var(--card); display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: flex-start;">
                    <div id="topicMetaInput" contenteditable="true" style="flex: 1; min-width: 200px; outline: none; padding: 0.25rem;" placeholder="Type @, #, $ or custom tag..."></div>
                </div>
                <div id="topicMetaAutocomplete" class="forum-meta-autocomplete-portal"></div>
            </div>
            <div class="forum-form-group">
                <label class="forum-form-label" for="topicContent">Content</label>
                <!-- Rich Text Editor Toolbar -->
                <div class="forum-editor-toolbar">
                    <button type="button" class="forum-editor-btn" onclick="document.execCommand('bold', false, null)" title="Bold">
                        <i class="bi bi-type-bold"></i>
                    </button>
                    <button type="button" class="forum-editor-btn" onclick="document.execCommand('italic', false, null)" title="Italic">
                        <i class="bi bi-type-italic"></i>
                    </button>
                    <button type="button" class="forum-editor-btn" onclick="document.execCommand('underline', false, null)" title="Underline">
                        <i class="bi bi-type-underline"></i>
                    </button>
                    <div class="forum-editor-separator"></div>
                    <button type="button" class="forum-editor-btn" onclick="document.execCommand('justifyLeft', false, null)" title="Align Left">
                        <i class="bi bi-text-left"></i>
                    </button>
                    <button type="button" class="forum-editor-btn" onclick="document.execCommand('justifyCenter', false, null)" title="Align Center">
                        <i class="bi bi-text-center"></i>
                    </button>
                    <button type="button" class="forum-editor-btn" onclick="document.execCommand('justifyRight', false, null)" title="Align Right">
                        <i class="bi bi-text-right"></i>
                    </button>
                    <div class="forum-editor-separator"></div>
                    <button type="button" class="forum-editor-btn" onclick="document.execCommand('insertUnorderedList', false, null)" title="Bullet List">
                        <i class="bi bi-list-ul"></i>
                    </button>
                    <button type="button" class="forum-editor-btn" onclick="document.execCommand('insertOrderedList', false, null)" title="Numbered List">
                        <i class="bi bi-list-ol"></i>
                    </button>
                    <div class="forum-editor-separator"></div>
                    <button type="button" class="forum-editor-btn" onclick="insertLink()" title="Insert Link">
                        <i class="bi bi-link"></i>
                    </button>
                </div>
                <div id="topicContent" class="forum-form-textarea" contenteditable="true" placeholder="Type your message here. Use @ for patients, # for appointments, $ for drugs..."></div>
            </div>
            <div class="forum-form-group">
                <label class="forum-form-label">Attachments</label>
                <input type="file" id="topicAttachments" multiple accept="image/*,.pdf,.doc,.docx" style="display: none;">
                <label for="topicAttachments" class="btn-cancel" style="display: inline-block; cursor: pointer; margin-bottom: 0.5rem;">
                    <i class="bi bi-paperclip"></i> Add Files
                </label>
                <div id="topicAttachmentsPreview" style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;"></div>
            </div>
            <div class="forum-form-actions">
                <button type="button" class="btn-cancel" onclick="hideNewTopicModal()">Cancel</button>
                <button type="submit" class="btn-submit">Create Discussion</button>
            </div>
        </form>
    </div>
</div>

<!-- Autocomplete Portal -->
<div id="forumAutocompletePortal" class="forum-content-autocomplete-portal"></div>

<script>
// Forum state
let forumTopics = [];
let pinnedTopics = [];
let forumPollingInterval = null;
let currentAutocompleteType = null;
let currentAutocompleteQuery = '';
let currentAutocompleteItems = [];
let selectedAutocompleteIndex = -1;
let autocompleteTextarea = null;
let autocompleteCursorPosition = null;
let autocompleteDebounceTimer = null;
let autocompletePortal = null;
let currentSearchQuery = '';
let currentCategoryFilter = null;
let currentMetaFilter = null;
let searchAutocompleteItems = [];
let selectedSearchIndex = -1;
let topicAttachments = [];
let topicUploadedAttachmentIds = []; // Store uploaded attachment IDs for deletion on cancel

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    autocompletePortal = document.getElementById('forumAutocompletePortal');
    
    // Check if we need to auto-open create modal
    const urlParams = new URLSearchParams(window.location.search);
    const patientId = urlParams.get('patient_id');
    const appointmentId = urlParams.get('appointment_id');
    const shouldCreate = urlParams.get('create') === 'true';
    
    // Store for later use
    window.autoTagPatientId = patientId;
    window.autoTagAppointmentId = appointmentId;
    
    // Load initial data
    loadCategoryStats();
    loadTopMetaTags();
    loadTopics(patientId, appointmentId);
    initAutocomplete(document.getElementById('topicContent'));
    initMetaAutocomplete();
    startForumPolling();
    
    // Search functionality with autocomplete
    const searchInput = document.getElementById('forumSearchInput');
    if (searchInput) {
        let searchTimeout;
        let searchAutocompleteTimeout;
        
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            // Clear previous timeout
            clearTimeout(searchTimeout);
            clearTimeout(searchAutocompleteTimeout);
            
            if (query.length >= 2) {
                // Show autocomplete
                searchAutocompleteTimeout = setTimeout(() => {
                    searchTopicsAutocomplete(query);
                }, 200);
            } else {
                // Hide autocomplete if query is too short
                hideSearchAutocomplete();
                // Still load topics if there's a query
                if (query.length > 0) {
                    searchTimeout = setTimeout(() => {
                        currentSearchQuery = query;
                        loadTopics(patientId, appointmentId);
                    }, 300);
                } else {
                    currentSearchQuery = '';
                    loadTopics(patientId, appointmentId);
                }
            }
        });
        
        searchInput.addEventListener('keydown', function(e) {
            const autocomplete = document.getElementById('forumSearchAutocomplete');
            if (!autocomplete || autocomplete.style.display === 'none') return;
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedSearchIndex = Math.min(selectedSearchIndex + 1, searchAutocompleteItems.length - 1);
                renderSearchAutocomplete();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedSearchIndex = Math.max(selectedSearchIndex - 1, -1);
                renderSearchAutocomplete();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedSearchIndex >= 0 && searchAutocompleteItems[selectedSearchIndex]) {
                    const item = searchAutocompleteItems[selectedSearchIndex];
                    window.location.href = `/doctor/forum/topic/${item.id}`;
                } else {
                    // Just search normally
                    currentSearchQuery = this.value.trim();
                    hideSearchAutocomplete();
                    loadTopics(patientId, appointmentId);
                }
            } else if (e.key === 'Escape') {
                hideSearchAutocomplete();
            }
        });
        
        // Hide autocomplete when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !document.getElementById('forumSearchAutocomplete').contains(e.target)) {
                hideSearchAutocomplete();
            }
        });
    }
    
    // Search topics autocomplete
    async function searchTopicsAutocomplete(query) {
        try {
            const response = await fetch(`/api/forum/topics?search=${encodeURIComponent(query)}&limit=10`);
            if (!response.ok) return;
            
            const data = await response.json();
            if (data.success && data.topics) {
                searchAutocompleteItems = data.topics.map(topic => ({
                    id: topic.id,
                    title: topic.title,
                    content: getFirstLine(topic.content),
                    category: topic.category,
                    creator_name: topic.creator_name || 'Unknown',
                    creator_image: topic.creator_image || null,
                    created_at: topic.created_at || null
                }));
                selectedSearchIndex = -1;
                renderSearchAutocomplete();
            }
        } catch (error) {
            console.error('Error searching topics:', error);
        }
    }
    
    // Render search autocomplete
    function renderSearchAutocomplete() {
        const autocomplete = document.getElementById('forumSearchAutocomplete');
        if (!autocomplete) return;
        
        const searchInput = document.getElementById('forumSearchInput');
        if (!searchInput) return;
        
        if (searchAutocompleteItems.length === 0) {
            autocomplete.style.display = 'none';
            return;
        }
        
        // Position autocomplete below search input - fixed position approach
        // Get the search bar container (parent container)
        const searchBarContainer = searchInput.closest('.forum-search-bar');
        const inputRect = searchInput.getBoundingClientRect();
        const containerRect = searchBarContainer ? searchBarContainer.getBoundingClientRect() : null;
        
        autocomplete.style.display = 'block';
        autocomplete.style.position = 'absolute';
        autocomplete.style.width = inputRect.width + 'px';
        autocomplete.style.left = '0px'; // Always 0 relative to search bar container
        // Calculate top position: distance from container top to input bottom + 60px
        if (containerRect) {
            autocomplete.style.top = (inputRect.bottom - containerRect.top + 10) + 'px';
        } else {
            autocomplete.style.top = (window.scrollY + inputRect.bottom + 10) + 'px';
        }
        autocomplete.style.zIndex = '9999999';
        
        // Register listeners to reposition on scroll/resize
        if (!window._forumSearchPortalUpdater) {
            window._forumSearchPortalUpdater = () => {
                const inputRect = searchInput.getBoundingClientRect();
                const containerRect = searchBarContainer ? searchBarContainer.getBoundingClientRect() : null;
                if (autocomplete && autocomplete.style.display !== 'none') {
                    autocomplete.style.width = '100%'; // 100% width to match input
                    autocomplete.style.left = '0px'; // Always 0
                    if (containerRect) {
                        autocomplete.style.top = (inputRect.bottom - containerRect.top + 10) + 'px';
                    } else {
                        autocomplete.style.top = (window.scrollY + inputRect.bottom + 10) + 'px';
                    }
                }
            };
            window.addEventListener('scroll', window._forumSearchPortalUpdater, true);
            window.addEventListener('resize', window._forumSearchPortalUpdater);
        }
        
        // Build HTML for suggestions - same style as drugs.php
        let html = '';
        searchAutocompleteItems.forEach((item, index) => {
            html += `
                <div class="forum-autocomplete-item ${index === selectedSearchIndex ? 'selected' : ''}" 
                     onclick="window.location.href='/doctor/forum/topic/${item.id}'"
                     style="padding: 0.75rem; cursor: pointer; border-bottom: 1px solid var(--border); display: flex; flex-direction: column; gap: 0.25rem; transition: background-color 0.15s ease-in-out;">
                    <div style="font-weight: 500; color: var(--text);">${escapeHtml(item.title)}</div>
                    <div style="font-size: 0.875rem; color: var(--muted);">${escapeHtml(item.content)}</div>
                    ${item.category ? `<div style="font-size: 0.75rem; color: var(--accent);">${escapeHtml(item.category)}</div>` : ''}
                </div>
            `;
        });
        autocomplete.innerHTML = html;
        
        // Add hover effect
        autocomplete.querySelectorAll('.forum-autocomplete-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'var(--bg)';
            });
            item.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    }
    
    // Hide search autocomplete
    function hideSearchAutocomplete() {
        const autocomplete = document.getElementById('forumSearchAutocomplete');
        if (autocomplete) {
            autocomplete.style.display = 'none';
            autocomplete.innerHTML = '';
        }
        searchAutocompleteItems = [];
        selectedSearchIndex = -1;
        
        // Cleanup listeners (same as drugs.php)
        if (window._forumSearchPortalUpdater) {
            window.removeEventListener('scroll', window._forumSearchPortalUpdater, true);
            window.removeEventListener('resize', window._forumSearchPortalUpdater);
            window._forumSearchPortalUpdater = null;
        }
    }
    
    // File attachments preview
    const attachmentsInput = document.getElementById('topicAttachments');
    if (attachmentsInput) {
        attachmentsInput.addEventListener('change', function(e) {
            handleTopicAttachments(e);
        });
    }
    
    if (shouldCreate) {
        setTimeout(() => {
            showNewTopicModal();
        }, 500);
    }
    
    // Handle form submission
    const newTopicForm = document.getElementById('newTopicForm');
    if (newTopicForm) {
        newTopicForm.addEventListener('submit', function(e) {
            e.preventDefault();
            createTopic();
        });
    }
});

// Load category statistics
async function loadCategoryStats() {
    try {
        const response = await fetch('/api/forum/stats/categories');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Expected JSON but got:', text.substring(0, 200));
            throw new Error('Response is not JSON');
        }
        const data = await response.json();
        
        if (data.success && data.stats) {
            renderCategoryStats(data.stats);
        }
    } catch (error) {
        console.error('Error loading category stats:', error);
    }
}

// Render category statistics cards
function renderCategoryStats(stats) {
    const container = document.getElementById('categoryStatsCards');
    if (!container) return;
    
    const categoryIcons = {
        'All': 'bi-chat-dots',
        'General Discussion': 'bi-chat-dots',
        'Clinical Case': 'bi-clipboard-pulse',
        'Procedure Feedback': 'bi-heart-pulse',
        'Protocol Update': 'bi-file-earmark-text',
        'Drug Interaction': 'bi-capsule',
        'Prescription Inquiry': 'bi-prescription',
        'Lab/Imaging Interpretation': 'bi-file-image'
    };
    
    // Calculate total count
    const totalCount = stats.reduce((sum, stat) => sum + stat.count, 0);
    
    // Always show "All" card first
    let html = `
        <div class="forum-stat-card" onclick="filterByCategory('All')" style="cursor: pointer; padding: 1rem; background: var(--card); border: 1px solid var(--border); border-radius: 8px; transition: all 0.2s ease; ${currentCategoryFilter === null || currentCategoryFilter === 'All' ? 'border-color: var(--accent); border-width: 2px;' : ''}">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 48px; height: 48px; border-radius: 8px; background: rgba(14, 165, 233, 0.1); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--accent);">
                    <i class="bi bi-chat-dots"></i>
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: var(--text); margin-bottom: 0.25rem;">All</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent);">${totalCount}</div>
                </div>
            </div>
        </div>
    `;
    
    // Add other category cards
    stats.forEach(stat => {
        const icon = categoryIcons[stat.category] || 'bi-chat-dots';
        html += `
            <div class="forum-stat-card" onclick="filterByCategory('${escapeHtml(stat.category)}')" style="cursor: pointer; padding: 1rem; background: var(--card); border: 1px solid var(--border); border-radius: 8px; transition: all 0.2s ease; ${currentCategoryFilter === stat.category ? 'border-color: var(--accent); border-width: 2px;' : ''}">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 48px; height: 48px; border-radius: 8px; background: rgba(14, 165, 233, 0.1); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--accent);">
                        <i class="bi ${icon}"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: var(--text); margin-bottom: 0.25rem;">${escapeHtml(stat.category)}</div>
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent);">${stat.count}</div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Filter by category
function filterByCategory(category) {
    if (category === 'All') {
        // Remove all filters
        currentCategoryFilter = null;
        currentMetaFilter = null;
        currentSearchQuery = '';
        // Clear search input
        const searchInput = document.getElementById('forumSearchInput');
        if (searchInput) {
            searchInput.value = '';
        }
    } else {
        currentCategoryFilter = category;
        currentMetaFilter = null;
    }
    loadTopics();
    // Re-render stats to update active state
    loadCategoryStats().then(() => {
        // Stats updated
    });
}

// Load top meta tags
async function loadTopMetaTags() {
    try {
        const response = await fetch('/api/forum/stats/top-meta?limit=5');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Expected JSON but got:', text.substring(0, 200));
            throw new Error('Response is not JSON');
        }
        const data = await response.json();
        
        if (data.success && data.tags) {
            renderTopMetaTags(data.tags);
        }
    } catch (error) {
        console.error('Error loading top meta tags:', error);
    }
}

// Render top meta tags badges
function renderTopMetaTags(tags) {
    const container = document.getElementById('topMetaTags');
    if (!container) return;
    
    if (tags.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'flex';
    container.style.alignItems = 'center';
    container.style.gap = '0.5rem';
    container.style.flexWrap = 'wrap';
    
    let html = '<span style="font-weight: 600; color: var(--text); margin-right: 0.5rem;">Top Tags:</span>';
    tags.forEach(tag => {
        const icon = tag.tag_type === 'patient' ? 'bi-person' : tag.tag_type === 'appointment' ? 'bi-calendar-event' : tag.tag_type === 'custom' ? 'bi-tag' : 'bi-capsule';
        const tagId = tag.tag_type === 'custom' ? 'null' : tag.tag_id;
        const isActive = currentMetaFilter && currentMetaFilter.type === tag.tag_type && 
                        (tag.tag_type === 'custom' ? currentMetaFilter.name === tag.tag_name : currentMetaFilter.id == tagId);
        html += `
            <span class="forum-tag ${tag.tag_type}" onclick="filterByMeta('${tag.tag_type}', ${tagId}, '${escapeHtml(tag.tag_name).replace(/'/g, "\\'")}')" 
                  style="cursor: pointer; text-decoration: none !important; ${isActive ? 'border-width: 2px; border-color: var(--accent);' : ''}">
                <i class="bi ${icon}"></i> <span style="text-decoration: none !important;">${escapeHtml(tag.tag_name)}</span> (${tag.count})
            </span>
        `;
    });
    
    // Add Clear Filter button if any filter is active
    if (currentMetaFilter || currentCategoryFilter) {
        html += `
            <button onclick="clearAllFilters()" style="margin-left: auto; padding: 0.375rem 0.75rem; background: var(--danger); color: white; border: none; border-radius: 20px; font-size: 0.75rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 0.25rem;">
                <i class="bi bi-x-circle"></i> Clear Filter
            </button>
        `;
    }
    
    container.innerHTML = html;
}

// Filter by meta
function filterByMeta(tagType, tagId, tagName = null) {
    if (tagType === 'custom' && tagName) {
        currentMetaFilter = { type: tagType, name: tagName };
    } else {
        currentMetaFilter = { type: tagType, id: tagId === 'null' ? null : parseInt(tagId) };
    }
    currentCategoryFilter = null;
    currentSearchQuery = '';
    // Clear search input
    const searchInput = document.getElementById('forumSearchInput');
    if (searchInput) {
        searchInput.value = '';
    }
    loadTopics();
    // Re-render stats and tags to update active state
    loadCategoryStats().then(() => {
        loadTopMetaTags();
    });
}

// Clear all filters
function clearAllFilters() {
    currentCategoryFilter = null;
    currentMetaFilter = null;
    currentSearchQuery = '';
    // Clear search input
    const searchInput = document.getElementById('forumSearchInput');
    if (searchInput) {
        searchInput.value = '';
    }
    loadTopics();
    // Re-render stats and tags to update active state
    loadCategoryStats().then(() => {
        loadTopMetaTags();
    });
}

// Load topics
async function loadTopics(patientId = null, appointmentId = null) {
    try {
        let url = '/api/forum/topics?';
        const params = new URLSearchParams();
        
        if (patientId) {
            params.append('patient_id', patientId);
        }
        if (appointmentId) {
            params.append('appointment_id', appointmentId);
        }
        if (currentSearchQuery) {
            params.append('search', currentSearchQuery);
        }
        if (currentCategoryFilter && currentCategoryFilter !== 'All') {
            params.append('category', currentCategoryFilter);
        }
        if (currentMetaFilter) {
            if (currentMetaFilter.type === 'custom' && currentMetaFilter.name) {
                params.append('meta_type', 'custom');
                params.append('meta_name', currentMetaFilter.name);
            } else if (currentMetaFilter.type && currentMetaFilter.id) {
                params.append('meta_type', currentMetaFilter.type);
                params.append('meta_id', currentMetaFilter.id);
            }
        }
        
        url += params.toString();
        
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Expected JSON but got:', text.substring(0, 200));
            throw new Error('Response is not JSON');
        }
        const data = await response.json();
        
        if (data.success) {
            forumTopics = data.topics.filter(t => !t.is_pinned);
            pinnedTopics = data.topics.filter(t => t.is_pinned);
            renderTopics();
        } else {
            showError('Failed to load topics');
        }
    } catch (error) {
        console.error('Error loading topics:', error);
        showError('Error loading topics');
    }
}

// Render topics
function renderTopics() {
    // Render pinned topics
    renderPinnedTopics();
    
    // Render regular topics
    const container = document.getElementById('forumTopicsList');
    if (!container) return;
    
    if (forumTopics.length === 0 && pinnedTopics.length === 0) {
        container.innerHTML = `
            <div class="forum-empty">
                <div class="forum-empty-icon">💬</div>
                <p>No topics yet. Be the first to start a discussion!</p>
            </div>
        `;
        return;
    }
    
    if (forumTopics.length === 0) {
        container.innerHTML = `
            <div class="forum-empty">
                <div class="forum-empty-icon">💬</div>
                <p>No more topics to display.</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    forumTopics.forEach(topic => {
        const tagsHtml = topic.tags ? topic.tags.map(tag => {
            const tagName = tag.tag_name || (tag.tag_type === 'appointment' ? `#${tag.tag_id}` : '');
            const tagClass = tag.tag_type === 'custom' ? 'forum-tag custom' : `forum-tag ${tag.tag_type}`;
            return `<span class="${tagClass}" style="text-decoration: none !important;">${getTagIcon(tag.tag_type)} <span style="text-decoration: none !important;">${escapeHtml(tagName)}</span></span>`;
        }).join('') : '';
        
        // Add patient badge if topic is related to a patient
        let patientBadge = '';
        if (topic.patient_id && topic.patient_first_name && topic.patient_last_name) {
            const patientName = `${topic.patient_first_name} ${topic.patient_last_name}`;
            patientBadge = `<a href="/doctor/patients/${topic.patient_id}" class="forum-patient-badge" onclick="event.stopPropagation();" target="_blank">
                <i class="bi bi-person"></i> ${escapeHtml(patientName)}
            </a>`;
        }
        
        // Add appointment badge if topic is related to an appointment
        let appointmentBadge = '';
        if (topic.appointment_id) {
            appointmentBadge = `<a href="/doctor/appointments/${topic.appointment_id}" class="forum-appointment-badge" onclick="event.stopPropagation();" target="_blank">
                <i class="bi bi-calendar-event"></i> Appointment #${topic.appointment_id}
            </a>`;
        }
        
        const timeAgo = getTimeAgo(topic.created_at);
        const lastReply = topic.last_reply_at ? getTimeAgo(topic.last_reply_at) : null;
        
        // Check if current user can edit/delete (author or admin)
        const currentUserId = <?= json_encode($user['id'] ?? null) ?>;
        const isAuthor = topic.created_by === currentUserId;
        const isAdmin = <?= json_encode(($user['role'] ?? '') === 'admin') ?>;
        const canEdit = isAuthor || isAdmin;
        const canDelete = isAuthor || isAdmin;
        const canPin = true; // All doctors can pin/unpin
        
        // Get first line of content for preview
        const contentPreview = getFirstLine(topic.content);
        const fullContent = topic.content;
        const showReadMore = fullContent.length > contentPreview.length;
        
        // Category badge
        const categoryBadge = topic.category ? `<span class="forum-tag" style="background: rgba(14, 165, 233, 0.1); color: var(--accent); border: 1px solid rgba(14, 165, 233, 0.2);">${escapeHtml(topic.category)}</span>` : '';
        
        // Resolved badge (only if not General Discussion)
        const resolvedBadge = topic.is_resolved && topic.category !== 'General Discussion' 
            ? `<span class="badge" style="background: var(--success); color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                <i class="bi bi-check-circle-fill"></i> Resolved
            </span>` : '';
        
        // Make tags clickable
        const clickableTagsHtml = topic.tags ? topic.tags.map(tag => {
            const tagName = tag.tag_name || (tag.tag_type === 'appointment' ? `#${tag.tag_id}` : '');
            let tagLink = '#';
            if (tag.tag_type === 'patient') {
                tagLink = `/doctor/patients/${tag.tag_id}`;
            } else if (tag.tag_type === 'appointment') {
                tagLink = `/doctor/appointments/${tag.tag_id}`;
            } else if (tag.tag_type === 'drug') {
                // Drug tags open popover instead of link
                const tagName = tag.tag_name || '';
                const drugId = tag.tag_id || null;
                if (drugId) {
                    return `<span class="forum-tag ${tag.tag_type}" onclick="event.stopPropagation(); showDrugPopover('${escapeHtml(tagName)}', ${drugId}, event)" style="cursor: pointer;">${getTagIcon(tag.tag_type)} ${escapeHtml(tagName)}</span>`;
                }
                return `<span class="forum-tag ${tag.tag_type}">${getTagIcon(tag.tag_type)} ${escapeHtml(tagName)}</span>`;
            }
            return `<a href="${tagLink}" class="forum-tag ${tag.tag_type}" onclick="event.stopPropagation();">${getTagIcon(tag.tag_type)} ${escapeHtml(tagName)}</a>`;
        }).join('') : '';
        
        html += `
            <div class="forum-topic-card ${topic.is_pinned ? 'pinned' : ''} ${topic.is_resolved ? 'resolved' : ''}" onclick="window.location.href='/doctor/forum/topic/${topic.id}'" style="cursor: pointer;">
                <div class="forum-topic-header" onclick="event.stopPropagation();">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; flex-wrap: wrap;">
                            ${categoryBadge}
                            ${resolvedBadge}
                        </div>
                        <h3 class="forum-topic-title" onclick="event.stopPropagation(); window.location.href='/doctor/forum/topic/${topic.id}'" style="cursor: pointer;">
                            ${topic.is_pinned ? '<i class="bi bi-pin-angle-fill" style="color: var(--accent); margin-right: 0.5rem;"></i>' : ''}
                            ${escapeHtml(topic.title)}
                        </h3>
                        <div class="forum-topic-meta">
                            <div class="forum-topic-author">
                                ${topic.creator_image ? (() => {
                                    const imagePath = topic.creator_image.startsWith('/public/') ? topic.creator_image : '/public' + topic.creator_image;
                                    return `<img src="${escapeHtml(imagePath)}" alt="${escapeHtml(topic.creator_name || 'Unknown')}" class="forum-author-avatar">`;
                                })() : '<div class="forum-author-avatar-placeholder"><i class="bi bi-person"></i></div>'}
                                <span>By ${escapeHtml(topic.creator_name || 'Unknown')}</span>
                            </div>
                            <span>•</span>
                            <span>${timeAgo}</span>
                            ${lastReply ? `<span>•</span><span>Last reply ${lastReply}</span>` : ''}
                        </div>
                        ${patientBadge || appointmentBadge ? `
                            <div class="forum-topic-badges" style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                ${patientBadge}
                                ${appointmentBadge}
                            </div>
                        ` : ''}
                    </div>
                    ${canEdit || canDelete || canPin ? `
                        <div class="forum-topic-actions" onclick="event.stopPropagation();">
                            ${canPin ? `<button class="forum-action-btn pin-btn ${topic.is_pinned ? 'pinned' : ''}" onclick="togglePin(${topic.id})" title="${topic.is_pinned ? 'Unpin' : 'Pin'}">
                                <i class="bi ${topic.is_pinned ? 'bi-pin-angle-fill' : 'bi-pin'}"></i>
                            </button>` : ''}
                            ${canEdit ? `<button class="forum-action-btn edit-btn" onclick="editTopic(${topic.id})" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>` : ''}
                            ${canDelete ? `<button class="forum-action-btn delete-btn" onclick="deleteTopicConfirm(${topic.id})" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>` : ''}
                        </div>
                    ` : ''}
                </div>
                <div class="forum-topic-content" onclick="event.stopPropagation();">
                    <div style="color: var(--text); line-height: 1.6;">${contentPreview}</div>
                    ${showReadMore ? `<button class="btn-read-more" onclick="event.stopPropagation(); window.location.href='/doctor/forum/topic/${topic.id}'" style="margin-top: 0.5rem; background: transparent; border: none; color: var(--accent); cursor: pointer; font-weight: 600; padding: 0.25rem 0;">Read More <i class="bi bi-arrow-right"></i></button>` : ''}
                </div>
                <div class="forum-topic-footer">
                    <div class="forum-topic-tags">${clickableTagsHtml}</div>
                    <div class="forum-topic-stats" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <span><i class="bi bi-eye"></i> ${topic.views_count || 0}</span>
                        <span><i class="bi bi-chat"></i> ${topic.replies_count || 0}</span>
                        ${topic.attachments_count > 0 ? `<span style="display: flex; align-items: center; gap: 0.25rem; color: var(--accent);"><i class="bi bi-paperclip"></i> ${topic.attachments_count}</span>` : ''}
                        <button class="forum-topic-like ${topic.user_like === 'like' ? 'active' : ''}" onclick="event.stopPropagation(); toggleTopicLike(${topic.id}, true)" style="background: ${topic.user_like === 'like' ? 'rgba(14, 165, 233, 0.2)' : 'transparent'}; border: 1px solid ${topic.user_like === 'like' ? 'var(--accent)' : 'var(--border)'}; color: ${topic.user_like === 'like' ? 'var(--accent)' : 'var(--text)'}; padding: 0.25rem 0.5rem; border-radius: 5px; font-size: 0.875rem; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; gap: 0.25rem;">
                            <i class="bi bi-hand-thumbs-up"></i>
                            <span>${topic.likes_count || 0}</span>
                        </button>
                        <button class="forum-topic-dislike ${topic.user_like === 'dislike' ? 'active' : ''}" onclick="event.stopPropagation(); toggleTopicLike(${topic.id}, false)" style="background: ${topic.user_like === 'dislike' ? 'rgba(239, 68, 68, 0.2)' : 'transparent'}; border: 1px solid ${topic.user_like === 'dislike' ? 'var(--danger)' : 'var(--border)'}; color: ${topic.user_like === 'dislike' ? 'var(--danger)' : 'var(--text)'}; padding: 0.25rem 0.5rem; border-radius: 5px; font-size: 0.875rem; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; gap: 0.25rem;">
                            <i class="bi bi-hand-thumbs-down"></i>
                            <span>${topic.dislikes_count || 0}</span>
                        </button>
                        ${topic.category !== 'General Discussion' && isAuthor ? `
                            <button class="btn-resolved" onclick="event.stopPropagation(); toggleResolved(${topic.id})" style="background: ${topic.is_resolved ? 'var(--success)' : 'transparent'}; border: 1px solid var(--success); color: ${topic.is_resolved ? 'white' : 'var(--success)'}; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">
                                <i class="bi ${topic.is_resolved ? 'bi-check-circle-fill' : 'bi-circle'}"></i> ${topic.is_resolved ? 'Resolved' : 'Mark Resolved'}
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Render pinned topics
function renderPinnedTopics() {
    const container = document.getElementById('pinnedTopicsList');
    const section = document.getElementById('pinnedTopicsSection');
    if (!container || !section) return;
    
    if (pinnedTopics.length === 0) {
        section.style.display = 'none';
        return;
    }
    
    section.style.display = 'block';
    
    let html = '';
    pinnedTopics.forEach(topic => {
        const tagsHtml = topic.tags ? topic.tags.map(tag => {
            const tagName = tag.tag_name || (tag.tag_type === 'appointment' ? `#${tag.tag_id}` : '');
            let tagLink = '#';
            if (tag.tag_type === 'patient') {
                tagLink = `/doctor/patients/${tag.tag_id}`;
            } else if (tag.tag_type === 'appointment') {
                tagLink = `/doctor/appointments/${tag.tag_id}`;
            } else if (tag.tag_type === 'drug') {
                // Drug tags open popover instead of link
                const drugId = tag.tag_id || null;
                if (drugId) {
                    return `<span class="forum-tag ${tag.tag_type}" onclick="event.stopPropagation(); showDrugPopover('${escapeHtml(tagName)}', ${drugId}, event)" style="cursor: pointer;">${getTagIcon(tag.tag_type)} ${escapeHtml(tagName)}</span>`;
                }
                return `<span class="forum-tag ${tag.tag_type}">${getTagIcon(tag.tag_type)} ${escapeHtml(tagName)}</span>`;
            }
            return tagLink !== '#' ? `<a href="${tagLink}" class="forum-tag ${tag.tag_type}" onclick="event.stopPropagation();">${getTagIcon(tag.tag_type)} ${escapeHtml(tagName)}</a>` : `<span class="forum-tag ${tag.tag_type}">${getTagIcon(tag.tag_type)} ${escapeHtml(tagName)}</span>`;
        }).join('') : '';
        
        const patientBadge = topic.patient_id && topic.patient_first_name && topic.patient_last_name
            ? `<a href="/doctor/patients/${topic.patient_id}" class="forum-patient-badge" onclick="event.stopPropagation();"><i class="bi bi-person"></i> ${escapeHtml(topic.patient_first_name + ' ' + topic.patient_last_name)}</a>` : '';
        const appointmentBadge = topic.appointment_id
            ? `<a href="/doctor/appointments/${topic.appointment_id}" class="forum-appointment-badge" onclick="event.stopPropagation();"><i class="bi bi-calendar-event"></i> #${topic.appointment_id}</a>` : '';
        
        const timeAgo = getTimeAgo(topic.created_at);
        const contentPreview = getFirstLine(topic.content);
        const categoryBadge = topic.category ? `<span class="forum-tag" style="background: rgba(14, 165, 233, 0.1); color: var(--accent);">${escapeHtml(topic.category)}</span>` : '';
        const resolvedBadge = topic.is_resolved && topic.category !== 'General Discussion'
            ? `<span class="badge" style="background: var(--success); color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem;"><i class="bi bi-check-circle-fill"></i> Resolved</span>` : '';
        
        const currentUserId = <?= json_encode($user['id'] ?? null) ?>;
        const isAuthor = topic.created_by === currentUserId;
        const isAdmin = <?= json_encode(($user['role'] ?? '') === 'admin') ?>;
        const canEdit = isAuthor || isAdmin;
        const canDelete = isAuthor || isAdmin;
        const canPin = true; // All doctors can pin/unpin
        
        html += `
            <div class="forum-topic-card pinned" onclick="window.location.href='/doctor/forum/topic/${topic.id}'" style="cursor: pointer;">
                <div class="forum-topic-header" onclick="event.stopPropagation();">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                            ${categoryBadge}
                            ${resolvedBadge}
                        </div>
                        <h3 class="forum-topic-title" onclick="event.stopPropagation(); window.location.href='/doctor/forum/topic/${topic.id}'" style="cursor: pointer;">
                            <i class="bi bi-pin-angle-fill" style="color: var(--accent); margin-right: 0.5rem;"></i>
                            ${escapeHtml(topic.title)}
                        </h3>
                        <div class="forum-topic-meta">
                            <div class="forum-topic-author">
                                ${topic.creator_image ? (() => {
                                    const imagePath = topic.creator_image.startsWith('/public/') ? topic.creator_image : '/public' + topic.creator_image;
                                    return `<img src="${escapeHtml(imagePath)}" alt="${escapeHtml(topic.creator_name || 'Unknown')}" class="forum-author-avatar">`;
                                })() : '<div class="forum-author-avatar-placeholder"><i class="bi bi-person"></i></div>'}
                                <span>By ${escapeHtml(topic.creator_name || 'Unknown')}</span>
                            </div>
                            <span>•</span>
                            <span>${timeAgo}</span>
                        </div>
                        ${patientBadge || appointmentBadge ? `
                            <div class="forum-topic-badges" style="margin-top: 0.5rem; display: flex; gap: 0.5rem;">
                                ${patientBadge}
                                ${appointmentBadge}
                            </div>
                        ` : ''}
                    </div>
                    ${canEdit || canDelete || canPin ? `
                        <div class="forum-topic-actions" onclick="event.stopPropagation();">
                            ${canPin ? `<button class="forum-action-btn pin-btn pinned" onclick="togglePin(${topic.id})" title="Unpin">
                                <i class="bi bi-pin-angle-fill"></i>
                            </button>` : ''}
                            ${canEdit ? `<button class="forum-action-btn edit-btn" onclick="editTopic(${topic.id})"><i class="bi bi-pencil"></i></button>` : ''}
                            ${canDelete ? `<button class="forum-action-btn delete-btn" onclick="deleteTopicConfirm(${topic.id})"><i class="bi bi-trash"></i></button>` : ''}
                        </div>
                    ` : ''}
                </div>
                <div class="forum-topic-content" onclick="event.stopPropagation();">
                    <div>${contentPreview}</div>
                    <button class="btn-read-more" onclick="event.stopPropagation(); window.location.href='/doctor/forum/topic/${topic.id}'" style="margin-top: 0.5rem; background: transparent; border: none; color: var(--accent); cursor: pointer; font-weight: 600;">Read More <i class="bi bi-arrow-right"></i></button>
                </div>
                <div class="forum-topic-footer">
                    <div class="forum-topic-tags">${tagsHtml}</div>
                    <div class="forum-topic-stats" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <span><i class="bi bi-eye"></i> ${topic.views_count || 0}</span>
                        <span><i class="bi bi-chat"></i> ${topic.replies_count || 0}</span>
                        ${topic.attachments_count > 0 ? `<span style="display: flex; align-items: center; gap: 0.25rem; color: var(--accent);"><i class="bi bi-paperclip"></i> ${topic.attachments_count}</span>` : ''}
                        <button class="forum-topic-like ${topic.user_like === 'like' ? 'active' : ''}" onclick="event.stopPropagation(); toggleTopicLike(${topic.id}, true)" style="background: ${topic.user_like === 'like' ? 'rgba(14, 165, 233, 0.2)' : 'transparent'}; border: 1px solid ${topic.user_like === 'like' ? 'var(--accent)' : 'var(--border)'}; color: ${topic.user_like === 'like' ? 'var(--accent)' : 'var(--text)'}; padding: 0.25rem 0.5rem; border-radius: 5px; font-size: 0.875rem; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; gap: 0.25rem;">
                            <i class="bi bi-hand-thumbs-up"></i>
                            <span>${topic.likes_count || 0}</span>
                        </button>
                        <button class="forum-topic-dislike ${topic.user_like === 'dislike' ? 'active' : ''}" onclick="event.stopPropagation(); toggleTopicLike(${topic.id}, false)" style="background: ${topic.user_like === 'dislike' ? 'rgba(239, 68, 68, 0.2)' : 'transparent'}; border: 1px solid ${topic.user_like === 'dislike' ? 'var(--danger)' : 'var(--border)'}; color: ${topic.user_like === 'dislike' ? 'var(--danger)' : 'var(--text)'}; padding: 0.25rem 0.5rem; border-radius: 5px; font-size: 0.875rem; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; gap: 0.25rem;">
                            <i class="bi bi-hand-thumbs-down"></i>
                            <span>${topic.dislikes_count || 0}</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Get first line of content
function getFirstLine(content) {
    if (!content) return '';
    // Remove HTML tags for preview
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = content;
    const text = tempDiv.textContent || tempDiv.innerText || '';
    const firstLine = text.split('\n')[0].trim();
    return firstLine.length > 150 ? firstLine.substring(0, 150) + '...' : firstLine;
}

// Toggle resolved status
async function toggleResolved(topicId) {
    try {
        const response = await fetch(`/api/forum/topics/${topicId}/toggle-resolved`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        if (data.success) {
            loadTopics();
            showToast('Resolved status updated', 'success');
        } else {
            showError(data.message || 'Failed to update resolved status');
        }
    } catch (error) {
        console.error('Error toggling resolved:', error);
        showError('Error updating resolved status');
    }
}

// Toggle pin status
async function togglePin(topicId) {
    try {
        const response = await fetch(`/api/forum/topics/${topicId}/toggle-pin`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        if (data.success) {
            loadTopics();
            showToast('Pin status updated', 'success');
        } else {
            showError(data.message || 'Failed to update pin status');
        }
    } catch (error) {
        console.error('Error toggling pin:', error);
        showError('Error updating pin status');
    }
}

// Toggle topic like/dislike
async function toggleTopicLike(topicId, isLike) {
    try {
        const endpoint = isLike ? 'like' : 'dislike';
        const response = await fetch(`/api/forum/topics/${topicId}/${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update UI - find the topic card and update buttons
            const topicCards = document.querySelectorAll('.forum-topic-card');
            topicCards.forEach(card => {
                if (card.getAttribute('onclick') && card.getAttribute('onclick').includes(`topic/${topicId}`)) {
                    const likeBtn = card.querySelector('.forum-topic-like');
                    const dislikeBtn = card.querySelector('.forum-topic-dislike');
                    
                    if (likeBtn) {
                        likeBtn.querySelector('span').textContent = data.likes_count || 0;
                        if (data.user_like === 'like') {
                            likeBtn.classList.add('active');
                            likeBtn.style.background = 'rgba(14, 165, 233, 0.2)';
                            likeBtn.style.borderColor = 'var(--accent)';
                            likeBtn.style.color = 'var(--accent)';
                        } else {
                            likeBtn.classList.remove('active');
                            likeBtn.style.background = 'transparent';
                            likeBtn.style.borderColor = 'var(--border)';
                            likeBtn.style.color = 'var(--text)';
                        }
                    }
                    
                    if (dislikeBtn) {
                        dislikeBtn.querySelector('span').textContent = data.dislikes_count || 0;
                        if (data.user_like === 'dislike') {
                            dislikeBtn.classList.add('active');
                            dislikeBtn.style.background = 'rgba(239, 68, 68, 0.2)';
                            dislikeBtn.style.borderColor = 'var(--danger)';
                            dislikeBtn.style.color = 'var(--danger)';
                        } else {
                            dislikeBtn.classList.remove('active');
                            dislikeBtn.style.background = 'transparent';
                            dislikeBtn.style.borderColor = 'var(--border)';
                            dislikeBtn.style.color = 'var(--text)';
                        }
                    }
                }
            });
            
            // Also update pinned topics if exists
            const pinnedCards = document.querySelectorAll('#pinnedTopicsList .forum-topic-card');
            pinnedCards.forEach(card => {
                if (card.getAttribute('onclick') && card.getAttribute('onclick').includes(`topic/${topicId}`)) {
                    const likeBtn = card.querySelector('.forum-topic-like');
                    const dislikeBtn = card.querySelector('.forum-topic-dislike');
                    
                    if (likeBtn) {
                        likeBtn.querySelector('span').textContent = data.likes_count || 0;
                        if (data.user_like === 'like') {
                            likeBtn.classList.add('active');
                            likeBtn.style.background = 'rgba(14, 165, 233, 0.2)';
                            likeBtn.style.borderColor = 'var(--accent)';
                            likeBtn.style.color = 'var(--accent)';
                        } else {
                            likeBtn.classList.remove('active');
                            likeBtn.style.background = 'transparent';
                            likeBtn.style.borderColor = 'var(--border)';
                            likeBtn.style.color = 'var(--text)';
                        }
                    }
                    
                    if (dislikeBtn) {
                        dislikeBtn.querySelector('span').textContent = data.dislikes_count || 0;
                        if (data.user_like === 'dislike') {
                            dislikeBtn.classList.add('active');
                            dislikeBtn.style.background = 'rgba(239, 68, 68, 0.2)';
                            dislikeBtn.style.borderColor = 'var(--danger)';
                            dislikeBtn.style.color = 'var(--danger)';
                        } else {
                            dislikeBtn.classList.remove('active');
                            dislikeBtn.style.background = 'transparent';
                            dislikeBtn.style.borderColor = 'var(--border)';
                            dislikeBtn.style.color = 'var(--text)';
                        }
                    }
                }
            });
        } else {
            showError(data.message || 'Failed to update like');
        }
    } catch (error) {
        console.error('Error toggling topic like:', error);
        showError('Error updating like');
    }
}

// Handle topic attachments
// Handle topic attachments with immediate upload
async function handleTopicAttachments(event) {
    const files = Array.from(event.target.files);
    const preview = document.getElementById('topicAttachmentsPreview');
    if (!preview) return;
    
    // Disable buttons during upload
    const submitBtn = document.querySelector('#newTopicForm button[type="submit"]');
    const cancelBtn = document.querySelector('#newTopicModal .btn-cancel');
    if (submitBtn) submitBtn.disabled = true;
    if (cancelBtn) cancelBtn.disabled = true;
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const fileIndex = topicAttachments.length;
        topicAttachments.push(file);
        
        // Create preview item with progress bar
        const previewItem = document.createElement('div');
        previewItem.id = `topic-attachment-${fileIndex}`;
        previewItem.style.cssText = 'position: relative; margin: 0.5rem; width: 100px;';
        
        const isImage = file.type.startsWith('image/');
        
        if (isImage) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewItem.innerHTML = `
                    <img src="${e.target.result}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);">
                    <div class="upload-progress" style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); color: white; padding: 2px; font-size: 0.7rem; text-align: center; border-radius: 0 0 8px 8px;">
                        <div class="progress-bar" style="width: 0%; background: var(--accent); height: 2px; margin-top: 2px;"></div>
                        <span class="progress-text">Uploading...</span>
                    </div>
                    <button type="button" onclick="removeTopicAttachment(${fileIndex})" style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 0.75rem; display: none;">×</button>
                `;
                preview.appendChild(previewItem);
                uploadFileToTopic(file, fileIndex, previewItem);
            };
            reader.readAsDataURL(file);
        } else {
            previewItem.innerHTML = `
                <div style="width: 100px; height: 100px; background: var(--card); border: 1px solid var(--border); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 0.5rem;">
                    <i class="bi bi-file-earmark" style="font-size: 2rem; color: var(--muted);"></i>
                    <small style="font-size: 0.7rem; color: var(--muted); text-align: center; word-break: break-all;">${escapeHtml(file.name)}</small>
                </div>
                <div class="upload-progress" style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); color: white; padding: 2px; font-size: 0.7rem; text-align: center; border-radius: 0 0 8px 8px;">
                    <div class="progress-bar" style="width: 0%; background: var(--accent); height: 2px; margin-top: 2px;"></div>
                    <span class="progress-text">Uploading...</span>
                </div>
                <button type="button" onclick="removeTopicAttachment(${fileIndex})" style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 0.75rem; display: none;">×</button>
            `;
            preview.appendChild(previewItem);
            uploadFileToTopic(file, fileIndex, previewItem);
        }
    }
}

// Upload file to topic with progress
async function uploadFileToTopic(file, fileIndex, previewItem) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', 'topic');
    // topic_id will be null for now, will be linked after topic creation
    
    const progressBar = previewItem.querySelector('.progress-bar');
    const progressText = previewItem.querySelector('.progress-text');
    
    try {
        const xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                if (progressBar) progressBar.style.width = percentComplete + '%';
                if (progressText) progressText.textContent = Math.round(percentComplete) + '%';
            }
        });
        
        await new Promise((resolve, reject) => {
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        topicUploadedAttachmentIds.push({
                            id: data.attachment_id,
                            fileIndex: fileIndex
                        });
                        
                        if (progressBar) progressBar.style.width = '100%';
                        if (progressBar) progressBar.style.background = 'var(--success)';
                        if (progressText) progressText.textContent = 'Uploaded';
                        
                        const removeBtn = previewItem.querySelector('button');
                        if (removeBtn) removeBtn.style.display = 'block';
                        
                        setTimeout(() => {
                            const progressDiv = previewItem.querySelector('.upload-progress');
                            if (progressDiv) progressDiv.style.display = 'none';
                        }, 1000);
                        
                        checkTopicUploadsComplete();
                        resolve(data.attachment_id);
                    } else {
                        reject(new Error(data.message || 'Upload failed'));
                    }
                } else {
                    reject(new Error('Upload failed'));
                }
            };
            
            xhr.onerror = function() {
                if (progressText) progressText.textContent = 'Error';
                if (progressBar) progressBar.style.background = 'var(--danger)';
                checkTopicUploadsComplete();
                reject(new Error('Upload failed'));
            };
            
            xhr.open('POST', '/api/forum/attachments/upload');
            xhr.send(formData);
        });
    } catch (error) {
        console.error('Error uploading attachment:', error);
        if (progressText) progressText.textContent = 'Error';
        if (progressBar) progressBar.style.background = 'var(--danger)';
        checkTopicUploadsComplete();
    }
}

// Check if all topic uploads are complete
function checkTopicUploadsComplete() {
    const preview = document.getElementById('topicAttachmentsPreview');
    if (!preview) return;
    
    const allProgressBars = preview.querySelectorAll('.upload-progress');
    const allComplete = Array.from(allProgressBars).every(progress => {
        return progress.style.display === 'none' || progress.querySelector('.progress-text').textContent === 'Uploaded' || progress.querySelector('.progress-text').textContent.includes('Error');
    });
    
    if (allComplete) {
        const submitBtn = document.querySelector('#newTopicForm button[type="submit"]');
        const cancelBtn = document.querySelector('#newTopicModal .btn-cancel');
        if (submitBtn) submitBtn.disabled = false;
        if (cancelBtn) cancelBtn.disabled = false;
    }
}

// Remove topic attachment
async function removeTopicAttachment(index) {
    // Prevent deletion if arrays are already cleared (e.g., after successful topic creation)
    if (!topicAttachments || topicAttachments.length === 0) {
        return;
    }
    
    // Check if this attachment was uploaded
    const uploadedAttachment = topicUploadedAttachmentIds.find(a => a.fileIndex === index);
    
    if (uploadedAttachment) {
        // Delete from server
        try {
            const response = await fetch(`/api/forum/attachments/${uploadedAttachment.id}`, {
                method: 'DELETE'
            });
            if (response.ok) {
                const data = await response.json();
                if (!data.success) {
                    console.error('Error deleting attachment:', data.message);
                }
            }
            topicUploadedAttachmentIds = topicUploadedAttachmentIds.filter(a => a.id !== uploadedAttachment.id);
        } catch (error) {
            console.error('Error deleting attachment:', error);
            // Don't show error to user if it's just a network issue
        }
    }
    
    // Remove from local array
    topicAttachments.splice(index, 1);
    
    // Remove preview item
    const previewItem = document.getElementById(`topic-attachment-${index}`);
    if (previewItem) {
        previewItem.remove();
    }
    
    // Re-render preview with correct indices
    const preview = document.getElementById('topicAttachmentsPreview');
    if (preview && topicAttachments.length > 0) {
        preview.innerHTML = '';
        topicAttachments.forEach((file, idx) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const isImage = file.type.startsWith('image/');
                const previewItem = document.createElement('div');
                previewItem.id = `topic-attachment-${idx}`;
                previewItem.style.cssText = 'position: relative; margin: 0.5rem; width: 100px;';
                
                if (isImage) {
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);">
                        <button type="button" onclick="removeTopicAttachment(${idx})" style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 0.75rem;">×</button>
                    `;
                } else {
                    previewItem.innerHTML = `
                        <div style="width: 100px; height: 100px; background: var(--card); border: 1px solid var(--border); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 0.5rem;">
                            <i class="bi bi-file-earmark" style="font-size: 2rem; color: var(--muted);"></i>
                            <small style="font-size: 0.7rem; color: var(--muted); text-align: center; word-break: break-all;">${escapeHtml(file.name)}</small>
                        </div>
                        <button type="button" onclick="removeTopicAttachment(${idx})" style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 0.75rem;">×</button>
                    `;
                }
                preview.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        });
        
        // Update fileIndex in topicUploadedAttachmentIds to match new indices
        topicUploadedAttachmentIds.forEach(uploaded => {
            if (uploaded.fileIndex > index) {
                uploaded.fileIndex = uploaded.fileIndex - 1;
            }
        });
    }
}

// Create new topic
async function createTopic() {
    const title = document.getElementById('topicTitle').value.trim();
    const category = document.getElementById('topicCategory')?.value || 'General Discussion';
    const contentDiv = document.getElementById('topicContent');
    const content = extractContentWithTags(contentDiv);
    const metaContainer = document.getElementById('topicMetaContainer');
    const metaTags = extractMetaTagsFromContainer(metaContainer);
    
    if (!title || !content) {
        showError('Title and content are required');
        return;
    }
    
    try {
        const tags = extractTagsFromContent(contentDiv);
        
        // Add meta tags
        metaTags.forEach(meta => {
            if (meta.type === 'custom' && meta.name) {
                tags.push({ type: 'custom', name: meta.name });
            } else if (meta.type && meta.id) {
                tags.push({ type: meta.type, id: meta.id });
            }
        });
        
        // Add auto-tags if available
        if (window.autoTagPatientId) {
            tags.push({ type: 'patient', id: parseInt(window.autoTagPatientId) });
        }
        if (window.autoTagAppointmentId) {
            tags.push({ type: 'appointment', id: parseInt(window.autoTagAppointmentId) });
        }
        
        const urlParams = new URLSearchParams(window.location.search);
        const patientId = urlParams.get('patient_id');
        const appointmentId = urlParams.get('appointment_id');
        
        // Create topic first
        const response = await fetch('/api/forum/topics', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                title: title,
                content: content,
                category: category,
                patient_id: patientId ? parseInt(patientId) : null,
                appointment_id: appointmentId ? parseInt(appointmentId) : null,
                tags: tags,
                attachment_ids: topicUploadedAttachmentIds.map(u => u.id)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const topicId = data.topic_id;
            
            // Attachments are already uploaded, just need to link them via attachment_ids
            // This is handled in the createTopic request
            
            hideNewTopicModal();
            document.getElementById('newTopicForm').reset();
            contentDiv.innerHTML = '';
            topicAttachments = [];
            topicUploadedAttachmentIds = []; // Clear uploaded attachment IDs
            const attachmentsPreview = document.getElementById('topicAttachmentsPreview');
            if (attachmentsPreview) {
                attachmentsPreview.innerHTML = '';
            }
            window.autoTagPatientId = null;
            window.autoTagAppointmentId = null;
            
            // Redirect to topic page
            window.location.href = `/doctor/forum/topic/${topicId}`;
        } else {
            showError(data.message || 'Failed to create topic');
        }
    } catch (error) {
        console.error('Error creating topic:', error);
        showError('Error creating topic');
    }
}

// Extract tags from content
function extractTagsFromContent(contentDiv) {
    const tags = [];
    const links = contentDiv.querySelectorAll('[data-type]');
    
    links.forEach(link => {
        const type = link.getAttribute('data-type');
        const id = link.getAttribute('data-id');
        if (type && id) {
            tags.push({
                type: type,
                id: parseInt(id)
            });
        }
    });
    
    return tags;
}

// Extract content with tags
function extractContentWithTags(contentDiv) {
    return contentDiv.innerHTML;
}

// Extract meta tags from meta container
function extractMetaTagsFromContainer(container) {
    if (!container) return [];
    const tags = [];
    const badges = container.querySelectorAll('[data-meta-type]');
    badges.forEach(badge => {
        const type = badge.getAttribute('data-meta-type');
        const id = badge.getAttribute('data-meta-id');
        const name = badge.getAttribute('data-meta-name');
        
        if (type === 'custom' && name) {
            // Custom tags have name but no id
            tags.push({ type: 'custom', name: name });
        } else if (type && id) {
            // Regular tags (patient, appointment, drug) have id
            tags.push({ type: type, id: parseInt(id) });
        }
    });
    return tags;
}

// Initialize meta autocomplete
function initMetaAutocomplete() {
    const metaInput = document.getElementById('topicMetaInput');
    if (!metaInput) return;
    
    metaInput.addEventListener('input', function() {
        handleMetaInput(this);
    });
    
    metaInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            handleMetaTagCreation(this, e.key === 'Enter');
        }
    });
}

// Handle meta input
function handleMetaInput(input) {
    const selection = window.getSelection();
    if (!selection.rangeCount) {
        hideMetaAutocomplete();
        return;
    }
    
    const range = selection.getRangeAt(0).cloneRange();
    const fullRange = document.createRange();
    fullRange.selectNodeContents(input);
    fullRange.setEnd(range.startContainer, range.startOffset);
    const textBeforeCursor = fullRange.toString();
    
    const match = textBeforeCursor.match(/(@|#|\$|~)([^\s@#$~]*)$/);
    
    if (match) {
        const trigger = match[1];
        const query = match[2];
        
        if (trigger === '~') {
            // Custom tag - no autocomplete needed
            return;
        }
        
        const minLength = trigger === '#' && /^\d+$/.test(query) ? 1 : 2;
        if (query.length >= minLength) {
            const type = trigger === '@' ? 'patient' : (trigger === '#' ? 'appointment' : 'drug');
            showMetaAutocomplete(input, type, query, range);
        } else {
            hideMetaAutocomplete();
        }
    } else {
        hideMetaAutocomplete();
    }
}

// Show meta autocomplete
async function showMetaAutocomplete(input, type, query, range) {
    let autocompletePortal = document.getElementById('topicMetaAutocomplete');
    if (!autocompletePortal) return;
    
    // Move to body to ensure positioning works correctly
    if (autocompletePortal.parentNode !== document.body) {
        document.body.appendChild(autocompletePortal);
    }
    
    // Position below the input field directly
    if (!input) return;
    
    const inputRect = input.getBoundingClientRect();
    // Use absolute positioning (scrollY + rect.bottom)
    const x = inputRect.left + window.scrollX;
    const y = inputRect.bottom + window.scrollY;
    
    console.log('Meta Autocomplete Position (Absolute):', {
        inputRect: inputRect,
        calculatedX: x,
        calculatedY: y
    });

    // Remove any inline styles that might interfere
    autocompletePortal.style.removeProperty('top');
    autocompletePortal.style.removeProperty('left');
    autocompletePortal.style.removeProperty('bottom');
    autocompletePortal.style.removeProperty('right');
    
    autocompletePortal.style.display = 'block';
    autocompletePortal.style.position = 'absolute';
    autocompletePortal.style.left = `${x}px`;
    autocompletePortal.style.top = `${y}px`;
    // Ensure width matches or is reasonable
    autocompletePortal.style.minWidth = `${Math.max(inputRect.width, 250)}px`;
    autocompletePortal.style.zIndex = '9999999';
    
    try {
        let url = '';
        if (type === 'patient') {
            url = `/api/patients/search?q=${encodeURIComponent(query)}`;
        } else if (type === 'appointment') {
            url = `/api/appointments/search?q=${encodeURIComponent(query)}&limit=10`;
        } else if (type === 'drug') {
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
                console.error('Error loading meta autocomplete:', response.status);
            }
            return;
        }
        
        const data = await response.json();
        
        let items = [];
        if (type === 'patient' && data.ok && data.data) {
            items = data.data.map(p => ({
                type: 'patient',
                id: p.id,
                title: `${p.first_name} ${p.last_name}`,
                subtitle: p.phone || ''
            }));
        } else if (type === 'appointment' && data.ok && data.data) {
            items = data.data.map(a => ({
                type: 'appointment',
                id: a.id,
                title: `#${a.id}`,
                subtitle: `${a.patient_name || ''} - ${a.date || ''}`
            }));
        } else if (type === 'drug' && data.drugs) {
            items = data.drugs.map(d => ({
                type: 'drug',
                id: d.ID,
                title: d.drug_name,
                subtitle: d.active_ingredient || d.Company || ''
            }));
        }
        
        renderMetaAutocomplete(items, type);
    } catch (error) {
        console.error('Error loading meta autocomplete:', error);
    }
}

// Render meta autocomplete
function renderMetaAutocomplete(items, type) {
    const portal = document.getElementById('topicMetaAutocomplete');
    if (!portal) return;
    
    if (items.length === 0) {
        portal.innerHTML = '<div class="forum-autocomplete-list" style="background: var(--card); border: 1px solid var(--border); border-radius: 8px; padding: 0.5rem;">No results</div>';
        return;
    }
    
    let html = '<div class="forum-autocomplete-list" style="background: var(--card); border: 1px solid var(--border); border-radius: 8px; max-height: 200px; overflow-y: auto;">';
    items.forEach((item, index) => {
        const icon = type === 'patient' ? 'bi-person' : type === 'appointment' ? 'bi-calendar-event' : 'bi-capsule';
        html += `
            <div class="forum-autocomplete-item" onclick="selectMetaItem(${index}, '${type}')" style="padding: 0.75rem; cursor: pointer; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 0.75rem;">
                <i class="bi ${icon}" style="font-size: 1.25rem; color: var(--accent);"></i>
                <div>
                    <div style="font-weight: 500; color: var(--text);">${escapeHtml(item.title)}</div>
                    ${item.subtitle ? `<div style="font-size: 0.875rem; color: var(--muted);">${escapeHtml(item.subtitle)}</div>` : ''}
                </div>
            </div>
        `;
    });
    html += '</div>';
    portal.innerHTML = html;
    
    // Store items for selection
    window.metaAutocompleteItems = items;
}

// Hide meta autocomplete
function hideMetaAutocomplete() {
    const portal = document.getElementById('topicMetaAutocomplete');
    if (portal) {
        portal.style.display = 'none';
        portal.innerHTML = '';
    }
}

// Select meta item
function selectMetaItem(index, type) {
    const items = window.metaAutocompleteItems || [];
    if (!items[index]) return;
    
    const item = items[index];
    addMetaTagBadge(item.type, item.id, item.title);
    
    const input = document.getElementById('topicMetaInput');
    if (input) {
        input.textContent = '';
        input.focus();
    }
    
    hideMetaAutocomplete();
}

// Add meta tag badge
function addMetaTagBadge(type, id, name) {
    const container = document.getElementById('topicMetaContainer');
    if (!container) return;
    
    const badge = document.createElement('span');
    badge.className = `forum-tag ${type}`;
    badge.setAttribute('data-meta-type', type);
    badge.setAttribute('data-meta-id', id);
    badge.style.cssText = 'display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 500; margin: 0.25rem;';
    
    const icon = type === 'patient' ? 'bi-person' : type === 'appointment' ? 'bi-calendar-event' : type === 'drug' ? 'bi-capsule' : 'bi-tag';
    badge.innerHTML = `
        <i class="bi ${icon}"></i>
        <span>${escapeHtml(name)}</span>
        <button type="button" onclick="removeMetaTagBadge(this)" style="background: none; border: none; color: inherit; cursor: pointer; margin-left: 0.25rem; padding: 0;">×</button>
    `;
    
    const input = document.getElementById('topicMetaInput');
    if (input && container.contains(input)) {
        container.insertBefore(badge, input);
    } else {
        container.appendChild(badge);
    }
}

// Add meta tag badge to edit modal
function addMetaTagBadgeToEdit(type, id, name) {
    const container = document.getElementById('editTopicMetaContainer');
    if (!container) return;
    
    const badge = document.createElement('span');
    badge.className = `forum-tag ${type}`;
    badge.setAttribute('data-meta-type', type);
    badge.setAttribute('data-meta-id', id);
    badge.style.cssText = 'display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 500; margin: 0.25rem;';
    
    const icon = type === 'patient' ? 'bi-person' : type === 'appointment' ? 'bi-calendar-event' : type === 'drug' ? 'bi-capsule' : 'bi-tag';
    badge.innerHTML = `
        <i class="bi ${icon}"></i>
        <span>${escapeHtml(name)}</span>
        <button type="button" onclick="removeMetaTagBadge(this)" style="background: none; border: none; color: inherit; cursor: pointer; margin-left: 0.25rem; padding: 0;">×</button>
    `;
    
    const input = document.getElementById('editTopicMetaInput');
    if (input && container.contains(input)) {
        container.insertBefore(badge, input);
    } else {
        container.appendChild(badge);
    }
}

// Remove meta tag badge
function removeMetaTagBadge(button) {
    const badge = button.closest('[data-meta-type]');
    if (badge) {
        badge.remove();
    }
}

// Handle meta tag creation (for custom tags)
function handleMetaTagCreation(input, isTrigger) {
    let text = input.textContent.trim();
    if (!text) return;
    
    // Clean up text if it ends with ;
    if (text.endsWith(';')) {
        text = text.slice(0, -1).trim();
    }
    
    if (!text) return;
    
    if (isTrigger) {
        // Create custom tag
        addCustomMetaTagBadge(text);
        input.textContent = '';
    }
}

// Add custom meta tag badge
function addCustomMetaTagBadge(name) {
    const container = document.getElementById('topicMetaContainer');
    if (!container) return;
    
    const badge = document.createElement('span');
    badge.className = 'forum-tag';
    badge.setAttribute('data-meta-type', 'custom');
    badge.setAttribute('data-meta-name', name);
    badge.style.cssText = 'display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 500; margin: 0.25rem; background: rgba(239, 68, 68, 0.1) !important; color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); text-decoration: none !important;';
    badge.innerHTML = `
        <i class="bi bi-tag"></i>
        <span style="text-decoration: none !important;">${escapeHtml(name)}</span>
        <button type="button" onclick="removeMetaTagBadge(this)" style="background: none; border: none; color: inherit; cursor: pointer; margin-left: 0.25rem; padding: 0; text-decoration: none !important;">×</button>
    `;
    
    const input = document.getElementById('topicMetaInput');
    if (input && container.contains(input)) {
        container.insertBefore(badge, input);
    } else {
        container.appendChild(badge);
    }
}

// Handle edit meta input
function handleEditMetaInput(input) {
    const text = input.textContent || '';
    const selection = window.getSelection();
    if (!selection.rangeCount) {
        hideEditMetaAutocomplete();
        return;
    }
    const range = selection.getRangeAt(0).cloneRange();
    const fullRange = document.createRange();
    fullRange.selectNodeContents(input);
    fullRange.setEnd(range.startContainer, range.startOffset);
    const textBeforeCursor = fullRange.toString();

    const match = textBeforeCursor.match(/(@|#|\$|~)([^\s@#$~]*)$/);

    if (match) {
        const trigger = match[1];
        const query = match[2];

        if (trigger === '~') {
            return;
        }

        const minLength = trigger === '#' && /^\d+$/.test(query) ? 1 : 2;
        if (query.length >= minLength) {
            const type = trigger === '@' ? 'patient' : (trigger === '#' ? 'appointment' : 'drug');
            showEditMetaAutocomplete(input, type, query, range);
        } else {
            hideEditMetaAutocomplete();
        }
    } else {
        hideEditMetaAutocomplete();
    }
}

// Show edit meta autocomplete
async function showEditMetaAutocomplete(input, type, query, range) {
    let autocompletePortal = document.getElementById('editTopicMetaAutocomplete');
    if (!autocompletePortal) return;

    // Move to body to ensure positioning works correctly
    if (autocompletePortal.parentNode !== document.body) {
        document.body.appendChild(autocompletePortal);
    }

    // Position below the input field directly
    if (!input) return;
    
    const inputRect = input.getBoundingClientRect();
    // Use absolute positioning (scrollY + rect.bottom)
    const x = inputRect.left + window.scrollX;
    const y = inputRect.bottom + window.scrollY;

    console.log('Edit Meta Autocomplete Position (Absolute):', {
        inputRect: inputRect,
        calculatedX: x,
        calculatedY: y
    });

    // Remove any inline styles that might interfere
    autocompletePortal.style.removeProperty('top');
    autocompletePortal.style.removeProperty('left');
    autocompletePortal.style.removeProperty('bottom');
    autocompletePortal.style.removeProperty('right');

    autocompletePortal.style.display = 'block';
    autocompletePortal.style.position = 'absolute';
    autocompletePortal.style.left = `${x}px`;
    autocompletePortal.style.top = `${y}px`;
    // Ensure width matches or is reasonable
    autocompletePortal.style.minWidth = `${Math.max(inputRect.width, 250)}px`;
    autocompletePortal.style.zIndex = '9999999';

    try {
        let url = '';
        if (type === 'patient') {
            url = `/api/patients/search?q=${encodeURIComponent(query)}`;
        } else if (type === 'appointment') {
            url = `/api/appointments/search?q=${encodeURIComponent(query)}&limit=10`;
        } else if (type === 'drug') {
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
                console.error('Error loading edit meta autocomplete:', response.status);
            }
            return;
        }

        const data = await response.json();

        let items = [];
        if (type === 'patient' && data.ok && data.data) {
            items = data.data.map(p => ({
                type: 'patient',
                id: p.id,
                title: `${p.first_name} ${p.last_name}`,
                subtitle: p.phone || ''
            }));
        } else if (type === 'appointment' && data.ok && data.data) {
            items = data.data.map(a => ({
                type: 'appointment',
                id: a.id,
                title: `#${a.id}`,
                subtitle: `${a.patient_name || ''} - ${a.date || ''}`
            }));
        } else if (type === 'drug' && data.drugs) {
            items = data.drugs.map(drug => ({
                type: 'drug',
                id: drug.ID,
                title: drug.drug_name,
                subtitle: drug.active_ingredient || drug.Company || ''
            }));
        }

        window.editMetaAutocompleteItems = items;
        renderEditMetaAutocomplete(items, type);
    } catch (error) {
        console.error('Error loading edit meta autocomplete:', error);
        hideEditMetaAutocomplete();
    }
}

// Render edit meta autocomplete
function renderEditMetaAutocomplete(items, type) {
    const portal = document.getElementById('editTopicMetaAutocomplete');
    if (!portal) return;

    if (items.length === 0) {
        portal.innerHTML = '<div class="forum-autocomplete-list" style="background: var(--card); border: 1px solid var(--border); border-radius: 8px; padding: 0.5rem;">No results</div>';
        return;
    }

    let html = '<div class="forum-autocomplete-list" style="background: var(--card); border: 1px solid var(--border); border-radius: 8px; max-height: 200px; overflow-y: auto;">';
    items.forEach((item, index) => {
        const icon = type === 'patient' ? 'bi-person' : type === 'appointment' ? 'bi-calendar-event' : 'bi-capsule';
        html += `
            <div class="forum-autocomplete-item" onclick="selectEditMetaItem(${index}, '${type}')" style="padding: 0.75rem; cursor: pointer; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 0.75rem;">
                <i class="bi ${icon}" style="font-size: 1.25rem; color: var(--accent);"></i>
                <div>
                    <div style="font-weight: 500; color: var(--text);">${escapeHtml(item.title)}</div>
                    ${item.subtitle ? `<div style="font-size: 0.875rem; color: var(--muted);">${escapeHtml(item.subtitle)}</div>` : ''}
                </div>
            </div>
        `;
    });
    html += '</div>';
    portal.innerHTML = html;
}

// Hide edit meta autocomplete
function hideEditMetaAutocomplete() {
    const portal = document.getElementById('editTopicMetaAutocomplete');
    if (portal) {
        portal.style.display = 'none';
        portal.innerHTML = '';
    }
}

// Select edit meta item
function selectEditMetaItem(index, type) {
    const items = window.editMetaAutocompleteItems || [];
    if (!items[index]) return;

    const item = items[index];
    addMetaTagBadgeToEdit(item.type, item.id, item.title);

    const input = document.getElementById('editTopicMetaInput');
    if (input) {
        input.textContent = '';
        input.focus();
    }

    hideEditMetaAutocomplete();
}

// Handle edit meta tag creation
function handleEditMetaTagCreation(input, isTrigger) {
    let text = input.textContent.trim();
    if (!text) return;

    // Clean up text if it ends with ;
    if (text.endsWith(';')) {
        text = text.slice(0, -1).trim();
    }
    
    if (!text) return;

    if (isTrigger) {
        addCustomMetaTagBadgeToEdit(text);
        input.textContent = '';
    }
}

// Add custom meta tag badge to edit
function addCustomMetaTagBadgeToEdit(name) {
    const container = document.getElementById('editTopicMetaContainer');
    if (!container) return;

    const badge = document.createElement('span');
    badge.className = 'forum-tag';
    badge.setAttribute('data-meta-type', 'custom');
    badge.setAttribute('data-meta-name', name);
    badge.style.cssText = 'display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 500; margin: 0.25rem; background: rgba(239, 68, 68, 0.1) !important; color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); text-decoration: none !important;';
    badge.innerHTML = `
        <i class="bi bi-tag"></i>
        <span style="text-decoration: none !important;">${escapeHtml(name)}</span>
        <button type="button" onclick="removeMetaTagBadge(this)" style="background: none; border: none; color: inherit; cursor: pointer; margin-left: 0.25rem; padding: 0; text-decoration: none !important;">×</button>
    `;

    const input = document.getElementById('editTopicMetaInput');
    if (input && container.contains(input)) {
        container.insertBefore(badge, input);
    } else {
        container.appendChild(badge);
    }
}

// Handle edit topic attachments
function handleEditTopicAttachments(event) {
    const files = Array.from(event.target.files);
    const preview = document.getElementById('editTopicAttachmentsPreview');
    if (!preview) return;

    // Add new files to editTopicAttachments array, marking them as new
    files.forEach(file => {
        editTopicAttachments.push({ file: file, existing: false });
    });

    renderEditTopicAttachmentsPreview();
}

// Remove edit topic attachment
function removeEditTopicAttachment(idOrIndex) {
    if (typeof idOrIndex === 'string') { // Existing attachment by ID
        editTopicAttachments = editTopicAttachments.filter(attach => attach.id !== idOrIndex);
    } else { // New attachment by index
        editTopicAttachments.splice(idOrIndex, 1);
    }
    renderEditTopicAttachmentsPreview();
}

// Render edit topic attachments preview
function renderEditTopicAttachmentsPreview() {
    const preview = document.getElementById('editTopicAttachmentsPreview');
    if (!preview) return;
    preview.innerHTML = '';

    editTopicAttachments.forEach((attach, index) => {
        const file = attach.file || { name: attach.original_filename, type: attach.mime_type };
        const isImage = file.type && file.type.startsWith('image/');
        const previewItem = document.createElement('div');
        previewItem.style.cssText = 'position: relative; margin: 0.5rem;';

        const src = attach.existing ? attach.file_path : (file instanceof File ? URL.createObjectURL(file) : '');

        if (isImage) {
            previewItem.innerHTML = `
                <img src="${escapeHtml(src)}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);">
                <button type="button" onclick="removeEditTopicAttachment(${attach.existing ? `'${attach.id}'` : index})" style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 0.75rem;">×</button>
            `;
        } else {
            previewItem.innerHTML = `
                <div style="width: 100px; height: 100px; background: var(--card); border: 1px solid var(--border); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 0.5rem;">
                    <i class="bi bi-file-earmark" style="font-size: 2rem; color: var(--muted);"></i>
                    <small style="font-size: 0.7rem; color: var(--muted); text-align: center; word-break: break-all;">${escapeHtml(file.name)}</small>
                </div>
                <button type="button" onclick="removeEditTopicAttachment(${attach.existing ? `'${attach.id}'` : index})" style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 0.75rem;">×</button>
            `;
        }
        preview.appendChild(previewItem);
    });
}

// Show/Hide modal
function showNewTopicModal() {
    document.getElementById('newTopicModal').classList.add('show');
}

async function hideNewTopicModal() {
    // Delete uploaded attachments if any
    if (topicUploadedAttachmentIds.length > 0) {
        for (const uploaded of topicUploadedAttachmentIds) {
            try {
                await fetch(`/api/forum/attachments/${uploaded.id}`, {
                    method: 'DELETE'
                });
            } catch (error) {
                console.error('Error deleting attachment:', error);
            }
        }
        topicUploadedAttachmentIds = [];
    }
    
    document.getElementById('newTopicModal').classList.remove('show');
    topicAttachments = [];
    const attachmentsPreview = document.getElementById('topicAttachmentsPreview');
    if (attachmentsPreview) {
        attachmentsPreview.innerHTML = '';
    }
}

// Close modal on outside click
document.getElementById('newTopicModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideNewTopicModal();
    }
});

// Polling system
function startForumPolling() {
    if (forumPollingInterval) return;
    
    forumPollingInterval = setInterval(() => {
        loadTopics();
    }, 10000); // Poll every 10 seconds
}

function stopForumPolling() {
    if (forumPollingInterval) {
        clearInterval(forumPollingInterval);
        forumPollingInterval = null;
    }
}

// Autocomplete system (similar to notes)
function initAutocomplete(contentEditable) {
    if (!contentEditable) return;
    
    contentEditable.addEventListener('input', handleContentEditableInput);
    contentEditable.addEventListener('keydown', handleContentEditableKeydown);
}

function handleContentEditableInput(event) {
    const contentEditable = event.target;
    const selection = window.getSelection();
    
    if (!selection.rangeCount) {
        hideAutocomplete();
        return;
    }
    
    const range = selection.getRangeAt(0).cloneRange();
    const fullRange = document.createRange();
    fullRange.selectNodeContents(contentEditable);
    fullRange.setEnd(range.startContainer, range.startOffset);
    const textBeforeCursor = fullRange.toString();
    
    const match = textBeforeCursor.match(/(@|#|\$)([^\s@#$]*)$/);
    
    if (match) {
        const trigger = match[1];
        const query = match[2];
        const minLength = trigger === '#' && /^\d+$/.test(query) ? 1 : 2;
        
        if (query.length >= minLength && query !== currentAutocompleteQuery) {
            currentAutocompleteType = trigger === '@' ? 'patient' : (trigger === '#' ? 'appointment' : 'drug');
            currentAutocompleteQuery = query;
            autocompleteTextarea = contentEditable;
            autocompleteCursorPosition = { range: range, match: match };
            
            const rect = range.getBoundingClientRect();
            showAutocomplete(contentEditable, rect, query);
        } else if (query.length < minLength) {
            hideAutocomplete();
        }
    } else {
        hideAutocomplete();
    }
}

async function showAutocomplete(contentEditable, rect, query) {
    if (!autocompletePortal) return;
    
    // Position right beside the cursor - use getBoundingClientRect for accurate positioning
    const x = rect.left + window.scrollX;
    const y = rect.bottom + window.scrollY + 5;
    
    autocompletePortal.style.display = 'block';
    autocompletePortal.style.position = 'fixed';
    autocompletePortal.style.left = `${x}px`;
    autocompletePortal.style.top = `${y}px`;
    autocompletePortal.style.zIndex = '9999999';
    
    try {
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
        
        // Verify query hasn't changed (user may have continued typing)
        if (query !== currentAutocompleteQuery) {
            return; // Query changed, ignore this response
        }
        
        let items = [];
        if (currentAutocompleteType === 'patient' && data.ok && data.data) {
            items = data.data.map(p => ({
                type: 'patient',
                id: p.id,
                title: `${p.first_name} ${p.last_name}`,
                subtitle: p.phone || ''
            }));
        } else if (currentAutocompleteType === 'appointment' && data.ok && data.data) {
            items = data.data.map(a => ({
                type: 'appointment',
                id: a.id,
                title: `#${a.id}`,
                subtitle: `${a.patient_name || ''} - ${a.date || ''}`
            }));
        } else if (currentAutocompleteType === 'drug' && data.drugs) {
            items = data.drugs.map(drug => ({
                type: 'drug',
                id: drug.ID,
                title: drug.drug_name,
                subtitle: drug.active_ingredient || drug.Company || ''
            }));
        }
        
        currentAutocompleteItems = items;
        selectedAutocompleteIndex = -1;
        renderAutocompleteItems();
    } catch (error) {
        console.error('Error loading autocomplete:', error);
        hideAutocomplete();
    }
}

function renderAutocompleteItems() {
    if (!autocompletePortal) return;
    
    if (currentAutocompleteItems.length === 0) {
        autocompletePortal.innerHTML = '<div class="forum-autocomplete-item">No results found</div>';
        return;
    }
    
    let html = '';
    currentAutocompleteItems.forEach((item, index) => {
        const icon = item.type === 'patient' ? '👤' : (item.type === 'appointment' ? '📅' : '💊');
        html += `
            <div class="forum-autocomplete-item ${index === selectedAutocompleteIndex ? 'selected' : ''}" 
                 data-index="${index}" 
                 onclick="selectAutocompleteItem(${index})">
                <span class="item-icon">${icon}</span>
                <div class="item-content">
                    <div class="item-title">${escapeHtml(item.title)}</div>
                    ${item.subtitle ? `<div class="item-subtitle">${escapeHtml(item.subtitle)}</div>` : ''}
                </div>
            </div>
        `;
    });
    
    autocompletePortal.innerHTML = html;
}

function handleContentEditableKeydown(event) {
    if (!autocompletePortal || autocompletePortal.style.display === 'none') return;
    
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        selectedAutocompleteIndex = Math.min(selectedAutocompleteIndex + 1, currentAutocompleteItems.length - 1);
        renderAutocompleteItems();
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        selectedAutocompleteIndex = Math.max(selectedAutocompleteIndex - 1, -1);
        renderAutocompleteItems();
    } else if (event.key === 'Enter') {
        event.preventDefault();
        if (selectedAutocompleteIndex >= 0) {
            selectAutocompleteItem(selectedAutocompleteIndex);
        } else {
            // If Enter is pressed without selection, ensure we're not inside a special tag
            // This helps break out of styling if cursor is trapped
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                let node = range.startContainer;
                if (node.nodeType === Node.TEXT_NODE) {
                    node = node.parentNode;
                }
                if (node.tagName === 'A' || node.tagName === 'SPAN') {
                    // If inside a link/span, insert a space after it
                    const spaceNode = document.createTextNode('\u00A0');
                    if (node.nextSibling) {
                        node.parentNode.insertBefore(spaceNode, node.nextSibling);
                    } else {
                        node.parentNode.appendChild(spaceNode);
                    }
                    range.setStartAfter(spaceNode);
                    range.collapse(true);
                    selection.removeAllRanges();
                    selection.addRange(range);
                    // Don't prevent default here to allow new line if needed, 
                    // but we already did preventing default at start of block.
                    // Since we want new line behavior for normal enter:
                    document.execCommand('insertLineBreak');
                } else {
                    document.execCommand('insertLineBreak');
                }
            }
        }
    } else if (event.key === 'Escape') {
        hideAutocomplete();
    }
}

function selectAutocompleteItem(index) {
    if (!autocompleteTextarea || !autocompleteCursorPosition) return;
    
    const item = currentAutocompleteItems[index];
    if (!item) return;
    
    const contentEditable = autocompleteTextarea;
    const range = autocompleteCursorPosition.range;
    const match = autocompleteCursorPosition.match;
    
    if (match && range) {
        range.setStart(range.startContainer, range.startOffset - match[0].length);
        range.deleteContents();
        
        let replacement = null;
        if (item.type === 'patient') {
            replacement = document.createElement('a');
            replacement.href = `/doctor/patients/${item.id}`;
            replacement.className = 'forum-content-link';
            replacement.target = '_blank';
            replacement.setAttribute('data-type', 'patient');
            replacement.setAttribute('data-id', item.id);
            replacement.innerHTML = `<i class="bi bi-person patient-icon"></i>${escapeHtml(item.title)}`;
        } else if (item.type === 'appointment') {
            replacement = document.createElement('a');
            replacement.href = `/doctor/appointments/${item.id}`;
            replacement.className = 'forum-content-appointment-link';
            replacement.target = '_blank';
            replacement.setAttribute('data-type', 'appointment');
            replacement.setAttribute('data-id', item.id);
            replacement.innerHTML = `<i class="bi bi-calendar-event appointment-icon"></i>#${item.id}`;
        } else if (item.type === 'drug') {
            replacement = document.createElement('span');
            replacement.className = 'forum-content-drug-badge';
            replacement.setAttribute('data-type', 'drug');
            replacement.setAttribute('data-id', item.id);
            replacement.innerHTML = `<i class="bi bi-capsule drug-icon"></i>${escapeHtml(item.title)}`;
        }
        
        if (replacement) {
            range.insertNode(replacement);
            
            // Insert a space after the replacement to break the style
            const spaceNode = document.createTextNode('\u00A0'); // Non-breaking space
            if (replacement.nextSibling) {
                replacement.parentNode.insertBefore(spaceNode, replacement.nextSibling);
            } else {
                replacement.parentNode.appendChild(spaceNode);
            }
            
            const newRange = document.createRange();
            newRange.setStartAfter(spaceNode);
            newRange.collapse(true);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(newRange);
        }
    }
    
    hideAutocomplete();
    contentEditable.focus();
}

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

function getTagIcon(type) {
    if (type === 'patient') return '👤';
    if (type === 'appointment') return '📅';
    if (type === 'drug') return '💊';
    if (type === 'custom') return '🏷️';
    return '';
}

function getTimeAgo(dateString) {
    const now = new Date();
    const date = new Date(dateString);
    const diff = Math.floor((now - date) / 1000);
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    return date.toLocaleDateString();
}

// Show toast notification
function showToast(message, type = 'info') {
    const globalShowToast = window.showToast;
    if (typeof globalShowToast === 'function' && globalShowToast !== showToast) {
        globalShowToast(message, type);
        return;
    }
    // Fallback toast implementation
    const toast = document.createElement('div');
    toast.style.cssText = `position: fixed; top: 2rem; right: 2rem; padding: 1rem 1.5rem; background: var(--card); border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10000; color: var(--text);`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Show error
function showError(message) {
    showToast(message, 'error');
}

// Escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    if (window.showToast) {
        showToast(message, 'error');
    } else {
        alert(message);
    }
}

function showToast(message, type = 'info') {
    // Check if there's a global showToast function that's different from this one
    const globalShowToast = window.showToast;
    if (typeof globalShowToast === 'function' && globalShowToast !== showToast) {
        globalShowToast(message, type);
        return;
    }
    
    // Fallback toast
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 10000; min-width: 300px;';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            <div class="flex-grow-1">${escapeHtml(message)}</div>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}

// Delete topic with confirmation
async function deleteTopicConfirm(topicId) {
    const modal = document.getElementById('deleteTopicModal');
    if (!modal) {
        const modalHtml = `
            <div class="forum-modal" id="deleteTopicModal">
                <div class="forum-modal-content">
                    <div class="forum-modal-header">
                        <h3 class="forum-modal-title">
                            <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                            Delete Topic
                        </h3>
                        <button class="forum-modal-close" onclick="hideDeleteTopicModal()">&times;</button>
                    </div>
                    <div class="forum-modal-body">
                        <p>Are you sure you want to delete this topic? This action cannot be undone.</p>
                    </div>
                    <div class="forum-form-actions">
                        <button type="button" class="btn-cancel" onclick="hideDeleteTopicModal()">Cancel</button>
                        <button type="button" class="btn-submit" style="background: var(--danger);" id="confirmDeleteTopicBtn">Delete</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
    
    const modalElement = document.getElementById('deleteTopicModal');
    modalElement.classList.add('show');
    
    const confirmBtn = document.getElementById('confirmDeleteTopicBtn');
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    newConfirmBtn.addEventListener('click', async function() {
        await deleteTopic(topicId);
        hideDeleteTopicModal();
    });
}

function hideDeleteTopicModal() {
    const modal = document.getElementById('deleteTopicModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

async function deleteTopic(topicId) {
    try {
        const response = await fetch(`/api/forum/topics/${topicId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Topic deleted successfully', 'success');
            loadTopics();
        } else {
            showError(data.message || 'Failed to delete topic');
        }
    } catch (error) {
        console.error('Error deleting topic:', error);
        showError('Error deleting topic');
    }
}

// Edit topic
async function editTopic(topicId) {
    try {
        const response = await fetch(`/api/forum/topics/${topicId}`);
        const data = await response.json();
        
        if (data.success) {
            const topic = data.topic;
            showEditTopicModal(topic);
        } else {
            showError('Failed to load topic');
        }
    } catch (error) {
        console.error('Error loading topic:', error);
        showError('Error loading topic');
    }
}

function showEditTopicModal(topic) {
    const modal = document.getElementById('editTopicModal');
    if (!modal) {
        const modalHtml = `
            <div class="forum-modal" id="editTopicModal">
                <div class="forum-modal-content">
                    <div class="forum-modal-header">
                        <h3 class="forum-modal-title">Edit Topic</h3>
                        <button class="forum-modal-close" onclick="hideEditTopicModal()">&times;</button>
                    </div>
                    <form id="editTopicForm">
                        <div class="forum-form-group">
                            <label class="forum-form-label" for="editTopicTitle">Title</label>
                            <input type="text" id="editTopicTitle" class="forum-form-input" required placeholder="Enter topic title">
                        </div>
                        <div class="forum-form-group">
                            <label class="forum-form-label" for="editTopicCategory">Category</label>
                            <select id="editTopicCategory" class="forum-form-input" required>
                                <option value="General Discussion">General Discussion</option>
                                <option value="Clinical Case">Clinical Case</option>
                                <option value="Procedure Feedback">Procedure Feedback</option>
                                <option value="Protocol Update">Protocol Update</option>
                                <option value="Drug Interaction">Drug Interaction</option>
                                <option value="Prescription Inquiry">Prescription Inquiry</option>
                                <option value="Lab/Imaging Interpretation">Lab/Imaging Interpretation</option>
                            </select>
                        </div>
                        <div class="forum-form-group">
                            <label class="forum-form-label" for="editTopicMeta">Meta Tags <small style="color: var(--muted);">(Use @ for patients, # for appointments, $ for drugs, or type custom tags)</small></label>
                            <div id="editTopicMetaContainer" class="forum-meta-container" style="min-height: 60px; padding: 0.5rem; border: 1px solid var(--border); border-radius: 8px; background: var(--card); display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: flex-start;">
                                <div id="editTopicMetaInput" contenteditable="true" style="flex: 1; min-width: 200px; outline: none; padding: 0.25rem;" placeholder="Type @, #, $ or custom tag..."></div>
                            </div>
                            <div id="editTopicMetaAutocomplete" class="forum-meta-autocomplete-portal"></div>
                        </div>
                        <div class="forum-form-group">
                            <label class="forum-form-label" for="editTopicContent">Content</label>
                            <!-- Rich Text Editor Toolbar -->
                            <div class="forum-editor-toolbar">
                                <button type="button" class="forum-editor-btn" onclick="document.execCommand('bold', false, null)" title="Bold">
                                    <i class="bi bi-type-bold"></i>
                                </button>
                                <button type="button" class="forum-editor-btn" onclick="document.execCommand('italic', false, null)" title="Italic">
                                    <i class="bi bi-type-italic"></i>
                                </button>
                                <button type="button" class="forum-editor-btn" onclick="document.execCommand('underline', false, null)" title="Underline">
                                    <i class="bi bi-type-underline"></i>
                                </button>
                                <div class="forum-editor-separator"></div>
                                <button type="button" class="forum-editor-btn" onclick="document.execCommand('justifyLeft', false, null)" title="Align Left">
                                    <i class="bi bi-text-left"></i>
                                </button>
                                <button type="button" class="forum-editor-btn" onclick="document.execCommand('justifyCenter', false, null)" title="Align Center">
                                    <i class="bi bi-text-center"></i>
                                </button>
                                <button type="button" class="forum-editor-btn" onclick="document.execCommand('justifyRight', false, null)" title="Align Right">
                                    <i class="bi bi-text-right"></i>
                                </button>
                                <div class="forum-editor-separator"></div>
                                <button type="button" class="forum-editor-btn" onclick="document.execCommand('insertUnorderedList', false, null)" title="Bullet List">
                                    <i class="bi bi-list-ul"></i>
                                </button>
                                <button type="button" class="forum-editor-btn" onclick="document.execCommand('insertOrderedList', false, null)" title="Numbered List">
                                    <i class="bi bi-list-ol"></i>
                                </button>
                                <div class="forum-editor-separator"></div>
                                <button type="button" class="forum-editor-btn" onclick="insertLink()" title="Insert Link">
                                    <i class="bi bi-link"></i>
                                </button>
                            </div>
                            <div id="editTopicContent" class="forum-form-textarea" contenteditable="true" placeholder="Type your message here. Use @ for patients, # for appointments, $ for drugs..."></div>
                        </div>
                        <div class="forum-form-group">
                            <label class="forum-form-label">Attachments</label>
                            <input type="file" id="editTopicAttachments" multiple accept="image/*,.pdf,.doc,.docx" style="display: none;">
                            <label for="editTopicAttachments" class="btn-cancel" style="display: inline-block; cursor: pointer; margin-bottom: 0.5rem;">
                                <i class="bi bi-paperclip"></i> Add Files
                            </label>
                            <div id="editTopicAttachmentsPreview" style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;"></div>
                        </div>
                        <div class="forum-form-actions">
                            <button type="button" class="btn-cancel" onclick="hideEditTopicModal()">Cancel</button>
                            <button type="submit" class="btn-submit">Update Topic</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Initialize autocomplete for edit modal
        const editContent = document.getElementById('editTopicContent');
        if (editContent) {
            initAutocomplete(editContent);
        }
        
        // Handle form submission
        document.getElementById('editTopicForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const topicId = document.getElementById('editTopicForm').getAttribute('data-topic-id');
            updateTopic(topicId);
        });
    }
    
    const modalElement = document.getElementById('editTopicModal');
    document.getElementById('editTopicTitle').value = topic.title;
    document.getElementById('editTopicCategory').value = topic.category || 'General Discussion';
    document.getElementById('editTopicContent').innerHTML = topic.content;
    document.getElementById('editTopicForm').setAttribute('data-topic-id', topic.id);
    
    // Load existing tags
    const metaContainer = document.getElementById('editTopicMetaContainer');
    const metaInput = document.getElementById('editTopicMetaInput');
    if (metaContainer && topic.tags) {
        // Clear existing badges except input
        const existingBadges = metaContainer.querySelectorAll('[data-meta-type]');
        existingBadges.forEach(badge => badge.remove());
        
        // Add existing tags as badges
        topic.tags.forEach(tag => {
            if (tag.tag_type === 'custom') {
                // Custom tags: use tag_name directly, no id
                if (tag.tag_name) {
                    addCustomMetaTagBadgeToEdit(tag.tag_name);
                }
            } else {
                const tagName = tag.tag_name || (tag.tag_type === 'appointment' ? `#${tag.tag_id}` : (tag.tag_type === 'drug' ? tag.tag_name : ''));
                if (tagName) {
                    addMetaTagBadgeToEdit(tag.tag_type, tag.tag_id, tagName);
                }
            }
        });
    }
    
    // Initialize meta autocomplete for edit modal
    if (metaInput) {
        // Remove existing listeners to avoid duplicates
        const newInput = metaInput.cloneNode(true);
        metaInput.parentNode.replaceChild(newInput, metaInput);
        
        // Monitor input for semicolon to create custom tags (similar to email to/cc/bcc)
        newInput.addEventListener('input', function(e) {
            const text = this.textContent;
            
            // Check if text ends with semicolon (custom tag creation)
            if (text.endsWith(';')) {
                handleEditMetaTagCreation(this, true);
                return; // Don't process autocomplete if we just created a tag
            }
            
            // Otherwise, handle autocomplete
            handleEditMetaInput(this);
        });
        
        // Also check on keyup for immediate response
        newInput.addEventListener('keyup', function(e) {
            const text = this.textContent;
            
            // Check for semicolon or Enter
            if (e.key === ';' || e.key === 'Enter' || text.endsWith(';')) {
                const cleanText = text.replace(/;+$/, '').trim();
                if (cleanText.length > 0) {
                    handleEditMetaTagCreation(this, true);
                }
            }
        });
        
        newInput.addEventListener('keydown', function(e) {
            // Handle Backspace to remove last tag if input is empty
            if (e.key === 'Backspace') {
                const text = this.textContent.trim();
                if (text === '') {
                    const container = document.getElementById('editTopicMetaContainer');
                    if (container) {
                        const badges = container.querySelectorAll('[data-meta-type="custom"]');
                        if (badges.length > 0) {
                            e.preventDefault();
                            badges[badges.length - 1].remove();
                        }
                    }
                }
            }
        });
    }
    
    // Load existing attachments
    editTopicAttachments = [];
    const attachmentsPreview = document.getElementById('editTopicAttachmentsPreview');
    if (attachmentsPreview && topic.attachments) {
        attachmentsPreview.innerHTML = '';
        topic.attachments.forEach(attach => {
            const previewItem = document.createElement('div');
            previewItem.style.cssText = 'position: relative; margin: 0.5rem;';
            const isImage = attach.mime_type && attach.mime_type.startsWith('image/');
            if (isImage) {
                previewItem.innerHTML = `
                    <img src="${escapeHtml(attach.file_path)}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);">
                    <button type="button" onclick="removeEditTopicAttachment('${attach.id}')" style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 0.75rem;">×</button>
                `;
            } else {
                previewItem.innerHTML = `
                    <div style="width: 100px; height: 100px; background: var(--card); border: 1px solid var(--border); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 0.5rem;">
                        <i class="bi bi-file-earmark" style="font-size: 2rem; color: var(--muted);"></i>
                        <small style="font-size: 0.7rem; color: var(--muted); text-align: center; word-break: break-all;">${escapeHtml(attach.original_filename)}</small>
                    </div>
                    <button type="button" onclick="removeEditTopicAttachment('${attach.id}')" style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 0.75rem;">×</button>
                `;
            }
            attachmentsPreview.appendChild(previewItem);
            editTopicAttachments.push({ id: attach.id, existing: true });
        });
    }
    
    // Handle file attachments
    const editAttachmentsInput = document.getElementById('editTopicAttachments');
    if (editAttachmentsInput) {
        editAttachmentsInput.onchange = function(e) {
            handleEditTopicAttachments(e);
        };
    }
    
    modalElement.classList.add('show');
}

function hideEditTopicModal() {
    const modal = document.getElementById('editTopicModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

let editTopicAttachments = [];

async function updateTopic(topicId) {
    const title = document.getElementById('editTopicTitle').value.trim();
    const category = document.getElementById('editTopicCategory')?.value || 'General Discussion';
    const contentDiv = document.getElementById('editTopicContent');
    const content = extractContentWithTags(contentDiv);
    const metaContainer = document.getElementById('editTopicMetaContainer');
    const metaTags = extractMetaTagsFromContainer(metaContainer);
    
    if (!title || !content) {
        showError('Title and content are required');
        return;
    }
    
    try {
        const tags = extractTagsFromContent(contentDiv);
        
        // Add meta tags
        metaTags.forEach(meta => {
            if (meta.type === 'custom' && meta.name) {
                tags.push({ type: 'custom', name: meta.name });
            } else if (meta.type && meta.id) {
                tags.push({ type: meta.type, id: meta.id });
            }
        });
        
        // Upload new attachments first if any
        let attachmentIds = [];
        const newAttachments = editTopicAttachments.filter(a => !a.existing);
        if (newAttachments.length > 0) {
            for (const attach of newAttachments) {
                if (attach.file) {
                    const formData = new FormData();
                    formData.append('file', attach.file);
                    formData.append('type', 'topic');
                    formData.append('topic_id', topicId);
                    
                    const uploadResponse = await fetch('/api/forum/attachments/upload', {
                        method: 'POST',
                        body: formData
                    });
                    const uploadData = await uploadResponse.json();
                    if (uploadData.success) {
                        attachmentIds.push(uploadData.attachment_id);
                    }
                }
            }
        }
        
        // Add existing attachment IDs
        const existingAttachments = editTopicAttachments.filter(a => a.existing && a.id);
        existingAttachments.forEach(a => attachmentIds.push(a.id));
        
        const response = await fetch(`/api/forum/topics/${topicId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                title: title,
                content: content,
                category: category,
                tags: tags,
                attachment_ids: attachmentIds
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            hideEditTopicModal();
            showToast('Topic updated successfully', 'success');
            loadTopics();
        } else {
            showError(data.message || 'Failed to update topic');
        }
    } catch (error) {
        console.error('Error updating topic:', error);
        showError('Error updating topic');
    }
}

// Close modals on outside click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('forum-modal')) {
        if (e.target.id === 'deleteTopicModal') {
            hideDeleteTopicModal();
        } else if (e.target.id === 'editTopicModal') {
            hideEditTopicModal();
        }
    }
});

// Insert link function for rich text editor
function insertLink() {
    const url = prompt('Enter URL:');
    if (url) {
        document.execCommand('createLink', false, url);
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    stopForumPolling();
});

// Drug Popover Functions
let currentDrugPopover = null;
let currentDrugPopoverOverlay = null;

async function showDrugPopover(drugName, drugId, event) {
    event.preventDefault();
    event.stopPropagation();
    
    // Close existing popover if any
    closeDrugPopover();
    
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'forum-drug-popover-overlay';
    overlay.onclick = closeDrugPopover;
    document.body.appendChild(overlay);
    currentDrugPopoverOverlay = overlay;
    
    // Create popover
    const popover = document.createElement('div');
    popover.className = 'forum-drug-popover';
    popover.innerHTML = `
        <div class="forum-drug-popover-header">
            <h3 class="forum-drug-popover-title">Loading...</h3>
            <button class="forum-drug-popover-close" onclick="closeDrugPopover()">&times;</button>
        </div>
        <div class="forum-drug-popover-body">
            <div style="text-align: center; padding: 2rem;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    `;
    
    // Position popover above the clicked element (drug tag)
    const rect = event.target.getBoundingClientRect();
    const popoverX = rect.left + window.scrollX;
    // Position above the tag - calculate height of popover (approximately 300px) and position it above
    const popoverY = rect.top + window.scrollY - 10; // 10px gap above tag
    
    // Calculate if popover would go off screen, adjust if needed
    const popoverHeight = 300; // Approximate height
    const finalY = popoverY - popoverHeight < window.scrollY 
        ? rect.bottom + window.scrollY + 10  // If too high, show below instead
        : popoverY;
    
    popover.style.left = `${Math.min(popoverX, window.innerWidth - 520)}px`;
    popover.style.top = `${finalY}px`;
    popover.style.transform = finalY === popoverY ? 'translateY(-100%)' : 'none';
    
    document.body.appendChild(popover);
    currentDrugPopover = popover;
    
    try {
        // Fetch drug details
        const response = await fetch(`/api/getDrugDetails?id=${drugId}`);
        const data = await response.json();
        
        if (data.drug) {
            const drug = data.drug;
            popover.innerHTML = `
                <div class="forum-drug-popover-header">
                    <h3 class="forum-drug-popover-title">${escapeHtml(drug.drug_name || drugName)}</h3>
                    <button class="forum-drug-popover-close" onclick="closeDrugPopover()">&times;</button>
                </div>
                <div class="forum-drug-popover-body">
                    ${drug.Company ? `
                        <div class="forum-drug-popover-item">
                            <div class="forum-drug-popover-label">Company</div>
                            <div class="forum-drug-popover-value">${escapeHtml(drug.Company)}</div>
                        </div>
                    ` : ''}
                    ${drug.category ? `
                        <div class="forum-drug-popover-item">
                            <div class="forum-drug-popover-label">Category</div>
                            <div class="forum-drug-popover-value">${escapeHtml(drug.category)}</div>
                        </div>
                    ` : ''}
                    ${drug.price ? `
                        <div class="forum-drug-popover-item">
                            <div class="forum-drug-popover-label">Price</div>
                            <div class="forum-drug-popover-value">EGP ${escapeHtml(drug.price)}</div>
                        </div>
                    ` : ''}
                    ${drug.administration_route ? `
                        <div class="forum-drug-popover-item">
                            <div class="forum-drug-popover-label">Route</div>
                            <div class="forum-drug-popover-value">${escapeHtml(drug.administration_route)}</div>
                        </div>
                    ` : ''}
                    ${drug.SRDE ? `
                        <div class="forum-drug-popover-item">
                            <div class="forum-drug-popover-label">Additional Information</div>
                            <div class="forum-drug-popover-value">${escapeHtml(drug.SRDE)}</div>
                        </div>
                    ` : ''}
                </div>
            `;
        } else {
            popover.innerHTML = `
                <div class="forum-drug-popover-header">
                    <h3 class="forum-drug-popover-title">${escapeHtml(drugName)}</h3>
                    <button class="forum-drug-popover-close" onclick="closeDrugPopover()">&times;</button>
                </div>
                <div class="forum-drug-popover-body">
                    <div class="forum-drug-popover-item">
                        <div class="forum-drug-popover-value" style="color: var(--muted);">Drug information not available</div>
                    </div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error fetching drug details:', error);
        popover.innerHTML = `
            <div class="forum-drug-popover-header">
                <h3 class="forum-drug-popover-title">${escapeHtml(drugName)}</h3>
                <button class="forum-drug-popover-close" onclick="closeDrugPopover()">&times;</button>
            </div>
            <div class="forum-drug-popover-body">
                <div class="forum-drug-popover-item">
                    <div class="forum-drug-popover-value" style="color: var(--danger);">Error loading drug information</div>
                </div>
            </div>
        `;
    }
}

function closeDrugPopover() {
    if (currentDrugPopover) {
        currentDrugPopover.remove();
        currentDrugPopover = null;
    }
    if (currentDrugPopoverOverlay) {
        currentDrugPopoverOverlay.remove();
        currentDrugPopoverOverlay = null;
    }
}

// Close popover on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && currentDrugPopover) {
        closeDrugPopover();
    }
});
</script>

