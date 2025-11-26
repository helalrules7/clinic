<?php
/**
 * Doctor Forum Topic Page
 * صفحة الموضوع في المنتدى
 */
$topicId = $topicId ?? null;
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

.forum-topic-container {
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

.dark .forum-topic-container {
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

.forum-topic-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border);
}

.forum-topic-back {
    background: var(--muted);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.forum-topic-main {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.forum-topic-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 1rem;
}

.forum-topic-meta {
    display: flex;
    gap: 1rem;
    align-items: center;
    font-size: 0.875rem;
    color: var(--muted);
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
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

.forum-action-btn.delete-btn {
    color: var(--danger);
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

.forum-topic-content {
    color: var(--text);
    line-height: 1.8;
    margin-bottom: 1.5rem;
}

.forum-topic-tags {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 1rem;
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

.forum-tag.patient {
    background: rgba(14, 165, 233, 0.1);
    color: var(--accent);
    border: 1px solid rgba(14, 165, 233, 0.2);
}

.forum-tag.appointment {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.forum-tag.drug {
    background: rgba(251, 191, 36, 0.1);
    color: #f59e0b;
    border: 1px solid rgba(251, 191, 36, 0.2);
}

/* Posts/Replies - Redesigned to match reference code */
.forum-posts-container {
    margin-top: 2rem;
}

/* Comment row - main container */
.forum-post-row {
    display: flex;
    align-items: flex-start;
    margin-bottom: 15px;
    position: relative;
}

/* Reply styling with margin-left */
.forum-post-row.reply {
    margin-left: 50px;
}

.forum-post-row.reply.reply-2 {
    margin-left: 100px;
}

.forum-post-row.reply.reply-3 {
    margin-left: 150px;
}

/* Reply arrow - SVG icon */
.forum-reply-arrow {
    color: #555;
    font-size: 20px;
    margin-right: 10px;
    margin-top: 15px;
    display: none;
    flex-shrink: 0;
    width: 24px;
    height: 24px;
}

.dark .forum-reply-arrow {
    color: #9ca3af;
}

.forum-post-row.reply .forum-reply-arrow {
    display: block;
}

.forum-reply-arrow svg {
    width: 100%;
    height: 100%;
}

/* Avatar */
.forum-post-avatar-wrapper {
    flex-shrink: 0;
    margin-right: 15px;
}

.forum-post-avatar {
    width: 50px;
    height: 50px;
    border-radius: 6px;
    object-fit: cover;
    border: 1px solid #ddd;
    padding: 2px;
    background: #fff;
}

.dark .forum-post-avatar {
    border-color: #374151;
    background: #1f2937;
}

.forum-post-avatar-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 6px;
    background: #e5e7eb;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    border: 1px solid #ddd;
}

.dark .forum-post-avatar-placeholder {
    background: #374151;
    color: #9ca3af;
    border-color: #4b5563;
}

/* Comment content box */
.forum-post {
    flex: 1;
    background-color: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 4px;
    padding: 10px 15px;
    position: relative;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.dark .forum-post {
    background-color: #1f2937;
    border-color: #374151;
    box-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

/* Comment header - name and time */
.forum-post-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 5px;
}

.dark .forum-post-header {
    border-bottom-color: #374151;
}

.forum-post-author {
    font-weight: bold;
    color: #333;
    font-size: 14px;
}

.dark .forum-post-author {
    color: #f9fafb;
}

.forum-post-author::after {
    content: ' said:';
    font-weight: 400;
}

.forum-post-time {
    font-size: 12px;
    color: #888;
}

.dark .forum-post-time {
    color: #9ca3af;
}

.forum-post-content {
    color: #555;
    font-size: 14px;
    line-height: 1.5;
    margin-top: 5px;
}

.dark .forum-post-content {
    color: #d1d5db;
}

/* Mentions in content */
.forum-post-content .mention {
    font-weight: bold;
    color: #333;
}

.dark .forum-post-content .mention {
    color: #f9fafb;
}

.forum-post-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.forum-post-content img:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.forum-content-image-wrapper {
    margin: 1rem 0;
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    background: var(--card);
}

.forum-content-image-title {
    padding: 0.5rem 0.75rem;
    background: var(--bg);
    border-bottom: 1px solid var(--border);
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--muted);
    text-align: center;
}

.forum-content-image-container {
    padding: 0.5rem;
    text-align: center;
}

.forum-content-image-container img {
    max-width: 100%;
    max-height: 500px;
    height: auto;
    border-radius: 4px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.forum-content-image-container img:hover {
    transform: scale(1.05);
}

.forum-post-images {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.5rem;
}

.forum-post-image {
    max-width: 200px;
    max-height: 200px;
    border-radius: 8px;
    cursor: pointer;
    object-fit: cover;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.forum-post-image:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.forum-image-modal-overlay {
    animation: fadeIn 0.2s ease;
}

.forum-image-modal-content {
    animation: slideUp 0.3s ease;
}

.forum-image-modal-btn {
    transition: all 0.2s ease;
    font-weight: 500;
}

.forum-image-modal-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.forum-post-actions {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    padding-top: 0.75rem;
    margin-top: 0.5rem;
    border-top: 1px solid #f0f0f0;
    flex-wrap: wrap;
}

.dark .forum-post-actions {
    border-top-color: #374151;
}

.forum-post-actions-header {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.forum-post-action-btn {
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

.forum-post-action-btn:hover {
    background: rgba(255, 255, 255, 0.9);
    transform: scale(1.1);
}

.forum-post-action-btn.edit-btn {
    color: var(--accent);
}

.forum-post-action-btn.edit-btn:hover {
    background: rgba(14, 165, 233, 0.2);
}

.forum-post-action-btn.delete-btn {
    color: var(--danger);
}

.forum-post-action-btn.delete-btn:hover {
    background: rgba(239, 68, 68, 0.2);
}

.dark .forum-post-action-btn {
    background: rgba(30, 41, 59, 0.6);
    color: var(--text);
}

.dark .forum-post-action-btn:hover {
    background: rgba(30, 41, 59, 0.9);
}

.forum-post-like,
.forum-post-dislike {
    background: none;
    border: 1px solid #e5e7eb;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.dark .forum-post-like,
.dark .forum-post-dislike {
    border-color: #374151;
}

.forum-post-like:hover {
    background: rgba(16, 185, 129, 0.08);
    border-color: #10b981;
}

.dark .forum-post-like:hover {
    background: rgba(16, 185, 129, 0.15);
}

.forum-post-dislike:hover {
    background: rgba(239, 68, 68, 0.08);
    border-color: #ef4444;
}

.dark .forum-post-dislike:hover {
    background: rgba(239, 68, 68, 0.15);
}

.forum-post-like.active {
    background: rgba(16, 185, 129, 0.15);
    border-color: #10b981;
    color: #10b981;
}

.dark .forum-post-like.active {
    background: rgba(16, 185, 129, 0.25);
}

.forum-post-dislike.active {
    background: rgba(239, 68, 68, 0.15);
    border-color: #ef4444;
    color: #ef4444;
}

.dark .forum-post-dislike.active {
    background: rgba(239, 68, 68, 0.25);
}

.forum-post-reply-btn {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.forum-post-reply-btn:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.dark .forum-post-reply-btn {
    background: #2563eb;
}

.dark .forum-post-reply-btn:hover {
    background: #1d4ed8;
}

/* Reply Form */
.forum-reply-form {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-top: 2rem;
}

.forum-reply-form.hidden {
    display: none;
}

.forum-reply-form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.forum-reply-form-title {
    font-weight: 600;
    color: var(--text);
}

.forum-reply-form-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--muted);
    cursor: pointer;
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

.forum-reply-content {
    width: 100%;
    min-height: 150px;
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-top: none;
    border-radius: 0 0 8px 8px;
    background: var(--card);
    color: var(--text);
    font-family: inherit;
    resize: vertical;
}

.forum-reply-content[contenteditable="true"] {
    outline: none;
}

.forum-reply-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
}

.forum-image-upload {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.forum-image-upload input {
    display: none;
}

.forum-image-upload-label {
    background: var(--muted);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.875rem;
}

.forum-reply-submit {
    background: var(--accent);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

.forum-empty {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--muted);
}

/* Tag styling */
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

/* Modal - Glass Effect */
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
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.4);
    background: transparent !important;
}

.dark .forum-modal-header {
    border-bottom-color: rgba(51, 65, 85, 0.3);
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

.forum-modal-body {
    background: transparent !important;
    color: var(--text) !important;
    padding: 1rem 0;
}

.forum-form-group {
    margin-bottom: 1.5rem;
}

.forum-form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text);
}

.forum-form-input {
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
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--card);
    color: var(--text);
    font-size: 1rem;
    font-family: inherit;
    min-height: 200px;
    resize: vertical;
}

.forum-form-textarea[contenteditable="true"] {
    min-height: 200px;
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-radius: 8px;
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

/* Autocomplete */
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

@media (max-width: 768px) {
    .forum-topic-container {
        padding: 1rem;
    }
    
    .forum-post.reply {
        margin-left: 1rem;
    }
    
    .forum-post.reply.reply-2 {
        margin-left: 2rem;
    }
    
    .forum-post.reply.reply-3 {
        margin-left: 3rem;
    }
}
</style>

<div class="forum-topic-container">
    <div class="forum-topic-header">
        <a href="/doctor/forum" class="forum-topic-back">
            <i class="bi bi-arrow-left"></i>
            Back to Forum
        </a>
    </div>

    <div id="topicContent">
        <div class="forum-empty">Loading topic...</div>
    </div>

    <div id="postsContainer" class="forum-posts-container">
        <div class="forum-empty">Loading posts...</div>
    </div>

    <div id="replyForm" class="forum-reply-form">
        <div class="forum-reply-form-header">
            <h3 class="forum-reply-form-title">Post a Reply</h3>
            <button class="forum-reply-form-close" onclick="hideReplyForm()">&times;</button>
        </div>
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
        <div id="replyContent" class="forum-reply-content" contenteditable="true" placeholder="Type your reply here. Use @ for patients, # for appointments, $ for drugs..."></div>
        <div class="forum-reply-actions">
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <div class="forum-image-upload">
                    <input type="file" id="replyImageInput" accept="image/*" multiple onchange="handleImageUpload(event)">
                    <label for="replyImageInput" class="forum-image-upload-label">
                        <i class="bi bi-image"></i> Add Images
                    </label>
                </div>
                <div class="forum-image-upload">
                    <input type="file" id="replyAttachmentsInput" accept="image/*,.pdf,.doc,.docx" multiple onchange="handleReplyAttachments(event)" style="display: none;">
                    <label for="replyAttachmentsInput" class="forum-image-upload-label" style="background: var(--muted);">
                        <i class="bi bi-paperclip"></i> Add Files
                    </label>
                </div>
            </div>
            <div id="replyAttachmentsPreview" style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;"></div>
            <button class="forum-reply-submit" onclick="submitReply()">Post Reply</button>
        </div>
    </div>
</div>

<!-- Autocomplete Portal -->
<div id="forumAutocompletePortal" class="forum-content-autocomplete-portal"></div>

<script>
const topicId = <?= json_encode($topicId) ?>;
let currentTopic = null;
let forumPosts = [];
let currentReplyParentId = null;
let currentPostId = null;
let forumPollingInterval = null;
let uploadedImages = [];

// Autocomplete state
let currentAutocompleteType = null;
let currentAutocompleteQuery = '';
let currentAutocompleteItems = [];
let selectedAutocompleteIndex = -1;
let autocompleteTextarea = null;
let autocompleteCursorPosition = null;
let autocompletePortal = null;

document.addEventListener('DOMContentLoaded', function() {
    autocompletePortal = document.getElementById('forumAutocompletePortal');
    loadTopic();
    loadPosts();
    initAutocomplete(document.getElementById('replyContent'));
    startForumPolling();
});

async function loadTopic() {
    try {
        const response = await fetch(`/api/forum/topics/${topicId}`);
        const data = await response.json();
        
        if (data.success) {
            currentTopic = data.topic;
            renderTopic();
        } else {
            showError('Failed to load topic');
        }
    } catch (error) {
        console.error('Error loading topic:', error);
        showError('Error loading topic');
    }
}

function renderTopic() {
    if (!currentTopic) return;
    
    const tagsHtml = currentTopic.tags ? currentTopic.tags.map(tag => {
        const tagName = tag.tag_name || (tag.tag_type === 'appointment' ? `#${tag.tag_id}` : '');
        const tagClass = tag.tag_type === 'custom' ? 'forum-tag custom' : `forum-tag ${tag.tag_type}`;
        return `<span class="${tagClass}">${getTagIcon(tag.tag_type)} ${escapeHtml(tagName)}</span>`;
    }).join('') : '';
    
    const timeAgo = getTimeAgo(currentTopic.created_at);
    
    // Add patient and appointment badges if available
    let patientBadge = '';
    if (currentTopic.patient_id && currentTopic.patient_first_name && currentTopic.patient_last_name) {
        const patientName = `${currentTopic.patient_first_name} ${currentTopic.patient_last_name}`;
        patientBadge = `<a href="/doctor/patients/${currentTopic.patient_id}" class="forum-patient-badge" target="_blank">
            <i class="bi bi-person"></i> ${escapeHtml(patientName)}
        </a>`;
    }
    
    let appointmentBadge = '';
    if (currentTopic.appointment_id) {
        appointmentBadge = `<a href="/doctor/appointments/${currentTopic.appointment_id}" class="forum-appointment-badge" target="_blank">
            <i class="bi bi-calendar-event"></i> Appointment #${currentTopic.appointment_id}
        </a>`;
    }
    
    // Check if current user can edit/delete (author or admin)
    const currentUserId = <?= json_encode($user['id'] ?? null) ?>;
    const isAuthor = currentTopic.created_by === currentUserId;
    const isAdmin = <?= json_encode(($user['role'] ?? '') === 'admin') ?>;
    const canEdit = isAuthor || isAdmin;
    const canDelete = isAuthor || isAdmin;
    const canPin = true; // All doctors can pin/unpin
    
    document.getElementById('topicContent').innerHTML = `
        <div class="forum-topic-main">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <h1 class="forum-topic-title" style="flex: 1; margin: 0;">${escapeHtml(currentTopic.title)}</h1>
                ${canEdit || canDelete || canPin ? `
                    <div style="display: flex; gap: 0.5rem;">
                        ${canPin ? `<button class="forum-action-btn pin-btn ${currentTopic.is_pinned ? 'pinned' : ''}" onclick="togglePin(${currentTopic.id})" title="${currentTopic.is_pinned ? 'Unpin' : 'Pin'}">
                            <i class="bi ${currentTopic.is_pinned ? 'bi-pin-angle-fill' : 'bi-pin'}"></i>
                        </button>` : ''}
                        ${canEdit ? `<button class="forum-action-btn edit-btn" onclick="editTopic(${currentTopic.id})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>` : ''}
                        ${canDelete ? `<button class="forum-action-btn delete-btn" onclick="deleteTopicConfirm(${currentTopic.id})" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>` : ''}
                    </div>
                ` : ''}
            </div>
            <div class="forum-topic-meta">
                <div class="forum-topic-author">
                    ${currentTopic.creator_image ? (() => {
                        const imagePath = currentTopic.creator_image.startsWith('/public/') ? currentTopic.creator_image : '/public' + currentTopic.creator_image;
                        return `<img src="${escapeHtml(imagePath)}" alt="${escapeHtml(currentTopic.creator_name || 'Unknown')}" class="forum-author-avatar">`;
                    })() : '<div class="forum-author-avatar-placeholder"><i class="bi bi-person"></i></div>'}
                    <span>By ${escapeHtml(currentTopic.creator_name || 'Unknown')}</span>
                </div>
                <span>•</span>
                <span>${timeAgo}</span>
                <span>•</span>
                <span><i class="bi bi-eye"></i> ${currentTopic.views_count || 0} views</span>
                <span>•</span>
                <span><i class="bi bi-chat"></i> ${currentTopic.replies_count || 0} replies</span>
            </div>
            ${patientBadge || appointmentBadge ? `
                <div class="forum-topic-badges" style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    ${patientBadge}
                    ${appointmentBadge}
                </div>
            ` : ''}
            <div class="forum-topic-content">${currentTopic.content}</div>
            ${tagsHtml ? `<div class="forum-topic-tags">${tagsHtml}</div>` : ''}
            ${currentTopic.attachments && currentTopic.attachments.length > 0 ? `
                <div class="forum-topic-attachments" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; color: var(--muted); font-size: 0.875rem; font-weight: 600;">
                        <i class="bi bi-paperclip"></i>
                        <span>Attachments (${currentTopic.attachments.length})</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        ${currentTopic.attachments.map(attach => `
                            <a href="/api/forum/attachments/view/${attach.id}" 
                               class="forum-attachment-item" 
                               style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: var(--card); border: 1px solid var(--border); border-radius: 8px; text-decoration: none; color: var(--text); transition: all 0.2s ease;"
                               download="${escapeHtml(attach.original_filename)}"
                               target="_blank">
                                <i class="bi ${getAttachmentIcon(attach.mime_type)}" style="font-size: 1.5rem; color: var(--accent);"></i>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 500; margin-bottom: 0.25rem; word-break: break-word;">${escapeHtml(attach.original_filename)}</div>
                                    <div style="font-size: 0.75rem; color: var(--muted);">${formatFileSize(attach.file_size)}</div>
                                </div>
                                <i class="bi bi-download" style="color: var(--accent); font-size: 1.25rem;"></i>
                            </a>
                        `).join('')}
                    </div>
                </div>
            ` : ''}
        </div>
    `;
}

async function loadPosts() {
    try {
        const response = await fetch(`/api/forum/posts/topic/${topicId}`);
        const data = await response.json();
        
        if (data.success) {
            forumPosts = data.posts;
            renderPosts();
            return true;
        } else {
            showError('Failed to load posts');
            return false;
        }
    } catch (error) {
        console.error('Error loading posts:', error);
        showError('Error loading posts');
        return false;
    }
}

function renderPosts() {
    const container = document.getElementById('postsContainer');
    
    if (!forumPosts || forumPosts.length === 0) {
        container.innerHTML = `
            <div class="forum-empty">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💬</div>
                <p>No replies yet. Be the first to reply!</p>
                <button class="forum-reply-submit" onclick="showReplyForm()" style="margin-top: 1rem;">Post First Reply</button>
            </div>
        `;
        return;
    }
    
    // Debug: Log posts structure
    console.log('Forum renderPosts: Total top-level posts:', forumPosts.length);
    forumPosts.forEach((post, index) => {
        const childrenCount = post.children && Array.isArray(post.children) ? post.children.length : 0;
        console.log(`Forum renderPosts: Post ${index + 1} (ID: ${post.id}) has ${childrenCount} children`);
    });
    
    let html = '';
    // forumPosts should already be a tree structure from buildPostTree
    // Only top-level posts are in the array, children are nested
    forumPosts.forEach(post => {
        html += renderPost(post, 0);
    });
    
    container.innerHTML = html;
}

function renderPost(post, depth) {
    const timeAgo = getTimeAgo(post.created_at);
    
    // Process content to extract and format images
    let processedContent = post.content || '';
    const imagePathsInContent = new Set();
    
    // Extract and replace images in content HTML string
    // Match <img> tags with various attributes
    const imgRegex = /<img[^>]+src=["']([^"']+)["'][^>]*(?:alt=["']([^"']*)["'])?[^>]*>/gi;
    processedContent = processedContent.replace(imgRegex, (match, src, alt) => {
        if (src) {
            imagePathsInContent.add(src);
            const imageName = alt || 'Image';
            // Replace img tag with wrapped version
            return `
                <div class="forum-content-image-wrapper">
                    <div class="forum-content-image-title">${escapeHtml(imageName)}</div>
                    <div class="forum-content-image-container">
                        <img src="${escapeHtml(src)}" alt="${escapeHtml(imageName)}" onclick="openImageModal('${escapeHtml(src)}', '${escapeHtml(imageName)}')" style="cursor: pointer;">
                    </div>
                </div>
            `;
        }
        return match;
    });
    
    // Only show images from post.images that are NOT in content
    // Normalize paths for comparison (remove leading /storage or /public if present)
    const normalizePath = (path) => {
        if (!path) return '';
        return path.replace(/^\/?(storage|public)\//, '').replace(/^\//, '');
    };
    
    const imagesHtml = post.images && post.images.length > 0 ? post.images
        .filter(img => {
            const normalizedImagePath = normalizePath(img.image_path);
            // Check if this image path is in content
            for (const contentPath of imagePathsInContent) {
                const normalizedContentPath = normalizePath(contentPath);
                if (normalizedImagePath === normalizedContentPath || 
                    contentPath.includes(normalizedImagePath) || 
                    normalizedImagePath.includes(normalizedContentPath)) {
                    return false; // Image is in content, don't show in forum-post-images
                }
            }
            return true; // Image not in content, show it
        })
        .map(img => 
            `<img src="${escapeHtml(img.image_path)}" alt="${escapeHtml(img.original_filename)}" class="forum-post-image" onclick="openImageModal('${escapeHtml(img.image_path)}', '${escapeHtml(img.original_filename)}')">`
        ).join('') : '';
    
    // Attachments HTML
    const attachmentsHtml = post.attachments && post.attachments.length > 0 ? post.attachments.map(attach => {
        const icon = getAttachmentIcon(attach.mime_type);
        return `
            <a href="/api/forum/attachments/view/${attach.id}" 
               class="forum-attachment-item" 
               style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; background: var(--card); border: 1px solid var(--border); border-radius: 8px; text-decoration: none; color: var(--text); transition: all 0.2s ease; margin-bottom: 0.5rem;"
               download="${escapeHtml(attach.original_filename)}"
               target="_blank">
                <i class="bi ${icon}" style="font-size: 1.25rem; color: var(--accent);"></i>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 500; word-break: break-word;">${escapeHtml(attach.original_filename)}</div>
                    <div style="font-size: 0.75rem; color: var(--muted);">${formatFileSize(attach.file_size)}</div>
                </div>
                <i class="bi bi-download" style="color: var(--accent);"></i>
            </a>
        `;
    }).join('') : '';
    
    const userLike = post.user_like || null;
    const likeClass = userLike === 'like' ? 'active' : '';
    const dislikeClass = userLike === 'dislike' ? 'active' : '';
    
    const depthClass = depth > 0 ? 'reply' : '';
    const depthClass2 = depth > 1 ? 'reply-2' : '';
    const depthClass3 = depth > 2 ? 'reply-3' : '';
    
    // Check if current user can edit/delete (author or admin)
    const currentUserId = <?= json_encode($user['id'] ?? null) ?>;
    const isAuthor = post.created_by === currentUserId;
    const isAdmin = <?= json_encode(($user['role'] ?? '') === 'admin') ?>;
    const canEdit = isAuthor || isAdmin;
    const canDelete = isAuthor || isAdmin;
    
    // Build HTML structure matching the reference code
    const isReply = depth > 0;
    const rowClass = isReply ? `reply ${depthClass2} ${depthClass3}` : '';
    
    let html = `
        <div class="forum-post-row ${rowClass}" data-post-id="${post.id}" data-parent-id="${post.parent_post_id || ''}">
            ${isReply ? `
                <div class="forum-reply-arrow">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 10 4 15 9 20"></polyline>
                        <path d="M20 4v7a4 4 0 0 1-4 4H4"></path>
                    </svg>
                </div>
            ` : ''}
            <div class="forum-post-avatar-wrapper">
                ${post.creator_image ? (() => {
                    const imagePath = post.creator_image.startsWith('/public/') ? post.creator_image : '/public' + post.creator_image;
                    return `<img src="${escapeHtml(imagePath)}" alt="${escapeHtml(post.creator_name || 'Unknown')}" class="forum-post-avatar">`;
                })() : '<div class="forum-post-avatar-placeholder"><i class="bi bi-person"></i></div>'}
            </div>
            <div class="forum-post">
                <div class="forum-post-header">
                    <span class="forum-post-author">${escapeHtml(post.creator_name || 'Unknown')}${isAuthor ? ' <span style="color: #888; font-weight: normal;">(you)</span>' : ''}</span>
                    <span class="forum-post-time">${timeAgo}</span>
                    ${canEdit || canDelete ? `
                        <div class="forum-post-actions-header" onclick="event.stopPropagation();" style="position: absolute; top: 10px; right: 15px; display: flex; gap: 0.5rem;">
                            ${canEdit ? `<button class="forum-post-action-btn edit-btn" onclick="editPost(${post.id})" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>` : ''}
                            ${canDelete ? `<button class="forum-post-action-btn delete-btn" onclick="deletePostConfirm(${post.id})" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>` : ''}
                        </div>
                    ` : ''}
                </div>
                <div class="forum-post-content">${processedContent}</div>
                ${imagesHtml ? `<div class="forum-post-images" style="margin-top: 0.5rem; margin-bottom: 0.5rem;">${imagesHtml}</div>` : ''}
                ${attachmentsHtml ? `<div class="forum-post-attachments" style="margin-top: 0.5rem; margin-bottom: 0.5rem;">${attachmentsHtml}</div>` : ''}
                <div class="forum-post-actions">
                    <button class="forum-post-like ${likeClass}" onclick="toggleLike(${post.id}, true)">
                        <i class="bi bi-hand-thumbs-up"></i>
                        <span>${post.likes_count || 0}</span>
                    </button>
                    <button class="forum-post-dislike ${dislikeClass}" onclick="toggleLike(${post.id}, false)">
                        <i class="bi bi-hand-thumbs-down"></i>
                        <span>${post.dislikes_count || 0}</span>
                    </button>
                    <button class="forum-post-reply-btn" onclick="showReplyForm(${post.id})">
                        <i class="bi bi-reply"></i> Reply
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Render children recursively - ensure all replies are rendered
    if (post.children && Array.isArray(post.children) && post.children.length > 0) {
        // Sort children by created_at to maintain order
        const sortedChildren = [...post.children].sort((a, b) => {
            return new Date(a.created_at) - new Date(b.created_at);
        });
        sortedChildren.forEach(child => {
            html += renderPost(child, depth + 1);
        });
    }
    
    return html;
}

function showReplyForm(parentId = null) {
    currentReplyParentId = parentId;
    const replyForm = document.getElementById('replyForm');
    replyForm.classList.remove('hidden');
    
    // Show parent post info if replying to a specific post
    let parentInfoHtml = '';
    if (parentId) {
        const findPost = (posts, id) => {
            for (const p of posts) {
                if (p.id === id) return p;
                if (p.children) {
                    const found = findPost(p.children, id);
                    if (found) return found;
                }
            }
            return null;
        };
        const parentPost = findPost(forumPosts, parentId);
        if (parentPost) {
            parentInfoHtml = `
                <div style="padding: 0.75rem; background: rgba(14, 165, 233, 0.1); border-left: 3px solid var(--accent); border-radius: 4px; margin-bottom: 1rem; font-size: 0.875rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <i class="bi bi-reply" style="color: var(--accent);"></i>
                        <strong style="color: var(--text);">Replying to ${escapeHtml(parentPost.creator_name || 'Unknown')}</strong>
                    </div>
                    <div style="color: var(--muted); max-height: 100px; overflow: hidden; text-overflow: ellipsis;">
                        ${parentPost.content.substring(0, 200)}${parentPost.content.length > 200 ? '...' : ''}
                    </div>
                </div>
            `;
        }
    }
    
    // Insert parent info before reply content
    const replyContent = document.getElementById('replyContent');
    const existingParentInfo = replyForm.querySelector('.reply-parent-info');
    if (existingParentInfo) {
        existingParentInfo.remove();
    }
    if (parentInfoHtml) {
        const parentInfoDiv = document.createElement('div');
        parentInfoDiv.className = 'reply-parent-info';
        parentInfoDiv.innerHTML = parentInfoHtml;
        // Insert before the toolbar (which is before replyContent)
        const toolbar = replyForm.querySelector('.forum-editor-toolbar');
        if (toolbar && toolbar.parentNode) {
            toolbar.parentNode.insertBefore(parentInfoDiv, toolbar);
        } else if (replyContent && replyContent.parentNode) {
            replyContent.parentNode.insertBefore(parentInfoDiv, replyContent);
        } else {
            replyForm.insertBefore(parentInfoDiv, replyForm.firstChild);
        }
    }
    
    document.getElementById('replyContent').focus();
}

async function hideReplyForm() {
    // Delete uploaded attachments if any
    if (replyUploadedAttachmentIds.length > 0) {
        for (const uploaded of replyUploadedAttachmentIds) {
            try {
                await fetch(`/api/forum/attachments/${uploaded.id}`, {
                    method: 'DELETE'
                });
            } catch (error) {
                console.error('Error deleting attachment:', error);
            }
        }
        replyUploadedAttachmentIds = [];
    }
    
    currentReplyParentId = null;
    document.getElementById('replyForm').classList.add('hidden');
    document.getElementById('replyContent').innerHTML = '';
    uploadedImages = [];
    replyAttachments = [];
    const preview = document.getElementById('replyAttachmentsPreview');
    if (preview) {
        preview.innerHTML = '';
    }
}

let replyAttachments = [];
let replyUploadedAttachmentIds = []; // Store uploaded attachment IDs for deletion on cancel

// Handle reply attachments with immediate upload
async function handleReplyAttachments(event) {
    const files = Array.from(event.target.files);
    const preview = document.getElementById('replyAttachmentsPreview');
    if (!preview) return;
    
    // Disable buttons during upload
    const submitBtn = document.querySelector('.forum-reply-submit');
    const closeBtn = document.querySelector('.forum-reply-form-close');
    if (submitBtn) submitBtn.disabled = true;
    if (closeBtn) closeBtn.disabled = true;
    
    let uploadPromises = [];
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const fileIndex = replyAttachments.length;
        replyAttachments.push(file);
        
        // Create preview item with progress bar
        const previewItem = document.createElement('div');
        previewItem.id = `reply-attachment-${fileIndex}`;
        previewItem.style.cssText = 'position: relative; margin: 0.5rem; width: 100px;';
        
        const isImage = file.type.startsWith('image/');
        let previewHtml = '';
        
        if (isImage) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewHtml = `
                    <img src="${e.target.result}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);">
                    <div class="upload-progress" style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); color: white; padding: 2px; font-size: 0.7rem; text-align: center; border-radius: 0 0 8px 8px;">
                        <div class="progress-bar" style="width: 0%; background: var(--accent); height: 2px; margin-top: 2px;"></div>
                        <span class="progress-text">Uploading...</span>
                    </div>
                    <button type="button" onclick="removeReplyAttachment(${fileIndex})" style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 0.75rem; display: none;">×</button>
                `;
                previewItem.innerHTML = previewHtml;
                preview.appendChild(previewItem);
                
                // Start upload
                uploadFileToReply(file, fileIndex, previewItem);
            };
            reader.readAsDataURL(file);
        } else {
            previewHtml = `
                <div style="width: 100px; height: 100px; background: var(--card); border: 1px solid var(--border); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 0.5rem;">
                    <i class="bi bi-file-earmark" style="font-size: 2rem; color: var(--muted);"></i>
                    <small style="font-size: 0.7rem; color: var(--muted); text-align: center; word-break: break-all;">${escapeHtml(file.name)}</small>
                </div>
                <div class="upload-progress" style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); color: white; padding: 2px; font-size: 0.7rem; text-align: center; border-radius: 0 0 8px 8px;">
                    <div class="progress-bar" style="width: 0%; background: var(--accent); height: 2px; margin-top: 2px;"></div>
                    <span class="progress-text">Uploading...</span>
                </div>
                <button type="button" onclick="removeReplyAttachment(${fileIndex})" style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 0.75rem; display: none;">×</button>
            `;
            previewItem.innerHTML = previewHtml;
            preview.appendChild(previewItem);
            
            // Start upload
            uploadFileToReply(file, fileIndex, previewItem);
        }
    }
}

// Upload file to reply with progress
async function uploadFileToReply(file, fileIndex, previewItem) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', 'post');
    // post_id will be null for now, will be linked after post creation
    
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
        
        const uploadPromise = new Promise((resolve, reject) => {
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        // Store attachment ID for potential deletion
                        replyUploadedAttachmentIds.push({
                            id: data.attachment_id,
                            fileIndex: fileIndex
                        });
                        
                        // Update preview to show success
                        if (progressBar) progressBar.style.width = '100%';
                        if (progressBar) progressBar.style.background = 'var(--success)';
                        if (progressText) progressText.textContent = 'Uploaded';
                        
                        // Show remove button
                        const removeBtn = previewItem.querySelector('button');
                        if (removeBtn) removeBtn.style.display = 'block';
                        
                        // Hide progress after a moment
                        setTimeout(() => {
                            const progressDiv = previewItem.querySelector('.upload-progress');
                            if (progressDiv) progressDiv.style.display = 'none';
                        }, 1000);
                        
                        resolve(data.attachment_id);
                    } else {
                        reject(new Error(data.message || 'Upload failed'));
                    }
                } else {
                    reject(new Error('Upload failed'));
                }
                
                // Re-enable buttons when all uploads complete
                checkReplyUploadsComplete();
            };
            
            xhr.onerror = function() {
                if (progressText) progressText.textContent = 'Error';
                if (progressBar) progressBar.style.background = 'var(--danger)';
                reject(new Error('Upload failed'));
                checkReplyUploadsComplete();
            };
        });
        
        xhr.open('POST', '/api/forum/attachments/upload');
        xhr.send(formData);
        
        await uploadPromise;
    } catch (error) {
        console.error('Error uploading attachment:', error);
        if (progressText) progressText.textContent = 'Error';
        if (progressBar) progressBar.style.background = 'var(--danger)';
        checkReplyUploadsComplete();
    }
}

// Check if all reply uploads are complete
function checkReplyUploadsComplete() {
    const preview = document.getElementById('replyAttachmentsPreview');
    if (!preview) return;
    
    const allProgressBars = preview.querySelectorAll('.upload-progress');
    const allComplete = Array.from(allProgressBars).every(progress => {
        return progress.style.display === 'none' || progress.querySelector('.progress-text').textContent === 'Uploaded' || progress.querySelector('.progress-text').textContent.includes('Error');
    });
    
    if (allComplete) {
        const submitBtn = document.querySelector('.forum-reply-submit');
        const closeBtn = document.querySelector('.forum-reply-form-close');
        if (submitBtn) submitBtn.disabled = false;
        if (closeBtn) closeBtn.disabled = false;
    }
}

// Remove reply attachment
async function removeReplyAttachment(index) {
    // Prevent deletion if arrays are already cleared (e.g., after successful post)
    if (!replyAttachments || replyAttachments.length === 0) {
        return;
    }
    
    // Check if this attachment was uploaded
    const uploadedAttachment = replyUploadedAttachmentIds.find(a => a.fileIndex === index);
    
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
            // Remove from array
            replyUploadedAttachmentIds = replyUploadedAttachmentIds.filter(a => a.id !== uploadedAttachment.id);
        } catch (error) {
            console.error('Error deleting attachment:', error);
            // Don't show error to user if it's just a network issue
        }
    }
    
    // Remove from local array
    replyAttachments.splice(index, 1);
    
    // Remove preview item
    const previewItem = document.getElementById(`reply-attachment-${index}`);
    if (previewItem) {
        previewItem.remove();
    }
    
    // Re-render preview with correct indices - but we need to map fileIndex correctly
    const preview = document.getElementById('replyAttachmentsPreview');
    if (preview && replyAttachments.length > 0) {
        preview.innerHTML = '';
        replyAttachments.forEach((file, idx) => {
            // Find the original fileIndex for this file
            // We need to track which fileIndex corresponds to which position
            // Since we're removing by index, we need to recalculate fileIndex
            const reader = new FileReader();
            reader.onload = function(e) {
                const isImage = file.type.startsWith('image/');
                const previewItem = document.createElement('div');
                // Use idx as the new fileIndex after removal
                previewItem.id = `reply-attachment-${idx}`;
                previewItem.style.cssText = 'position: relative; margin: 0.5rem; width: 100px;';
                
                if (isImage) {
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);">
                        <button type="button" onclick="removeReplyAttachment(${idx})" style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 0.75rem;">×</button>
                    `;
                } else {
                    previewItem.innerHTML = `
                        <div style="width: 100px; height: 100px; background: var(--card); border: 1px solid var(--border); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 0.5rem;">
                            <i class="bi bi-file-earmark" style="font-size: 2rem; color: var(--muted);"></i>
                            <small style="font-size: 0.7rem; color: var(--muted); text-align: center; word-break: break-all;">${escapeHtml(file.name)}</small>
                        </div>
                        <button type="button" onclick="removeReplyAttachment(${idx})" style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 0.75rem;">×</button>
                    `;
                }
                preview.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        });
        
        // Update fileIndex in replyUploadedAttachmentIds to match new indices
        replyUploadedAttachmentIds.forEach(uploaded => {
            if (uploaded.fileIndex > index) {
                uploaded.fileIndex = uploaded.fileIndex - 1;
            }
        });
    }
}

async function submitReply() {
    const content = document.getElementById('replyContent').innerHTML.trim();
    
    if (!content) {
        showError('Please enter a reply');
        return;
    }
    
    try {
        const response = await fetch('/api/forum/posts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                topic_id: topicId,
                parent_post_id: currentReplyParentId,
                content: content,
                attachment_ids: replyUploadedAttachmentIds.map(u => u.id)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentPostId = data.post_id;
            
            // Upload images if any
            if (uploadedImages.length > 0) {
                for (const imageFile of uploadedImages) {
                    await uploadImageToPost(data.post_id, imageFile);
                }
            }
            
            // Attachments are already uploaded, just need to link them via attachment_ids
            // This is handled in the createPost request
            
            // Clear form but keep it visible for next reply
            currentReplyParentId = null;
            document.getElementById('replyContent').innerHTML = '';
            uploadedImages = [];
            
            // Clear attachments arrays BEFORE clearing preview to prevent any onclick handlers from firing
            const attachmentIdsToClear = [...replyUploadedAttachmentIds]; // Copy array
            replyUploadedAttachmentIds = []; // Clear uploaded attachment IDs first
            replyAttachments = [];
            
            // Clear preview after clearing arrays
            const preview = document.getElementById('replyAttachmentsPreview');
            if (preview) {
                preview.innerHTML = '';
            }
            
            // Remove parent info if exists
            const replyForm = document.getElementById('replyForm');
            const existingParentInfo = replyForm.querySelector('.reply-parent-info');
            if (existingParentInfo) {
                existingParentInfo.remove();
            }
            
            // Reload posts
            await loadPosts();
            
            // Keep form visible
            replyForm.classList.remove('hidden');
            
            showToast('Reply posted successfully', 'success');
        } else {
            showError(data.message || 'Failed to post reply');
        }
    } catch (error) {
        console.error('Error posting reply:', error);
        showError('Error posting reply');
    }
}

async function toggleLike(postId, isLike) {
    try {
        const endpoint = isLike ? 'like' : 'dislike';
        const response = await fetch(`/api/forum/posts/${postId}/${endpoint}`, {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update UI
            const postElement = document.querySelector(`[data-post-id="${postId}"]`);
            if (postElement) {
                const likeBtn = postElement.querySelector('.forum-post-like');
                const dislikeBtn = postElement.querySelector('.forum-post-dislike');
                
                if (likeBtn) {
                    likeBtn.querySelector('span').textContent = data.likes_count;
                    likeBtn.classList.toggle('active', data.user_like === 'like');
                }
                
                if (dislikeBtn) {
                    dislikeBtn.querySelector('span').textContent = data.dislikes_count;
                    dislikeBtn.classList.toggle('active', data.user_like === 'dislike');
                }
            }
        }
    } catch (error) {
        console.error('Error toggling like:', error);
    }
}

function handleImageUpload(event) {
    const files = Array.from(event.target.files);
    uploadedImages = uploadedImages.concat(files);
    
    // Show preview
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'forum-post-image';
            img.style.maxWidth = '100px';
            img.style.marginRight = '0.5rem';
            document.getElementById('replyContent').appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}

async function uploadImageToPost(postId, file) {
    const formData = new FormData();
    formData.append('image', file);
    
    try {
        const response = await fetch(`/api/forum/posts/${postId}/images`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        return data.success;
    } catch (error) {
        console.error('Error uploading image:', error);
        return false;
    }
}

function startForumPolling() {
    if (forumPollingInterval) return;
    
    forumPollingInterval = setInterval(() => {
        loadPosts();
    }, 10000);
}

function stopForumPolling() {
    if (forumPollingInterval) {
        clearInterval(forumPollingInterval);
        forumPollingInterval = null;
    }
}

// Autocomplete functions (same as index.php)
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
    
    // Position right beside the cursor - use fixed positioning for accurate placement
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
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                let node = range.startContainer;
                if (node.nodeType === Node.TEXT_NODE) {
                    node = node.parentNode;
                }
                if (node.tagName === 'A' || node.tagName === 'SPAN') {
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

// Delete post with confirmation
async function deletePostConfirm(postId) {
    const modal = document.getElementById('deletePostModal');
    if (!modal) {
        const modalHtml = `
            <div class="forum-modal" id="deletePostModal">
                <div class="forum-modal-content">
                    <div class="forum-modal-header">
                        <h3 class="forum-modal-title">
                            <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                            Delete Reply
                        </h3>
                        <button class="forum-modal-close" onclick="hideDeletePostModal()">&times;</button>
                    </div>
                    <div class="forum-modal-body">
                        <p>Are you sure you want to delete this reply? This action cannot be undone.</p>
                    </div>
                    <div class="forum-form-actions">
                        <button type="button" class="btn-cancel" onclick="hideDeletePostModal()">Cancel</button>
                        <button type="button" class="btn-submit" style="background: var(--danger);" id="confirmDeletePostBtn">Delete</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
    
    const modalElement = document.getElementById('deletePostModal');
    modalElement.classList.add('show');
    
    const confirmBtn = document.getElementById('confirmDeletePostBtn');
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    newConfirmBtn.addEventListener('click', async function() {
        await deletePost(postId);
        hideDeletePostModal();
    });
}

function hideDeletePostModal() {
    const modal = document.getElementById('deletePostModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

async function deletePost(postId) {
    try {
        const response = await fetch(`/api/forum/posts/${postId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Reply deleted successfully', 'success');
            loadPosts();
        } else {
            showError(data.message || 'Failed to delete reply');
        }
    } catch (error) {
        console.error('Error deleting post:', error);
        showError('Error deleting reply');
    }
}

// Edit post
async function editPost(postId) {
    try {
        const response = await fetch(`/api/forum/posts/${postId}`);
        const data = await response.json();
        
        if (data.success) {
            const post = data.post;
            showEditPostModal(post);
        } else {
            showError('Failed to load reply');
        }
    } catch (error) {
        console.error('Error loading post:', error);
        showError('Error loading reply');
    }
}

function showEditPostModal(post) {
    const modal = document.getElementById('editPostModal');
    if (!modal) {
        const modalHtml = `
            <div class="forum-modal" id="editPostModal">
                <div class="forum-modal-content">
                    <div class="forum-modal-header">
                        <h3 class="forum-modal-title">Edit Reply</h3>
                        <button class="forum-modal-close" onclick="hideEditPostModal()">&times;</button>
                    </div>
                    <form id="editPostForm">
                        <div class="forum-form-group">
                            <label class="forum-form-label" for="editPostContent">Content</label>
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
                            <div id="editPostContent" class="forum-form-textarea" contenteditable="true" placeholder="Type your reply here..."></div>
                        </div>
                        <div class="forum-form-actions">
                            <button type="button" class="btn-cancel" onclick="hideEditPostModal()">Cancel</button>
                            <button type="submit" class="btn-submit">Update Reply</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Initialize autocomplete for edit modal
        const editContent = document.getElementById('editPostContent');
        if (editContent) {
            initAutocomplete(editContent);
        }
        
        // Handle form submission
        document.getElementById('editPostForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const postId = document.getElementById('editPostForm').getAttribute('data-post-id');
            updatePost(postId);
        });
    }
    
    const modalElement = document.getElementById('editPostModal');
    document.getElementById('editPostContent').innerHTML = post.content;
    document.getElementById('editPostForm').setAttribute('data-post-id', post.id);
    modalElement.classList.add('show');
}

function hideEditPostModal() {
    const modal = document.getElementById('editPostModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

async function updatePost(postId) {
    const contentDiv = document.getElementById('editPostContent');
    const content = contentDiv.innerHTML.trim();
    
    if (!content) {
        showError('Content is required');
        return;
    }
    
    try {
        const response = await fetch(`/api/forum/posts/${postId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                content: content
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            hideEditPostModal();
            showToast('Reply updated successfully', 'success');
            loadPosts();
        } else {
            showError(data.message || 'Failed to update reply');
        }
    } catch (error) {
        console.error('Error updating post:', error);
        showError('Error updating reply');
    }
}

// Close modals on outside click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('forum-modal')) {
        if (e.target.id === 'deletePostModal') {
            hideDeletePostModal();
        } else if (e.target.id === 'editPostModal') {
            hideEditPostModal();
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
            window.location.href = '/doctor/forum';
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

    console.log('Topic Edit Meta Autocomplete Position (Absolute):', {
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

// Add meta tag badge to edit
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
    // Use danger color (red) for custom tags background
    badge.style.cssText = 'display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 500; margin: 0.25rem; background: rgba(233, 14, 186, 0.1); color: var(--accent); border: 1px solid rgba(200, 14, 233, 0.2);';
    badge.innerHTML = `
        <i class="bi bi-tag"></i>
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

// Handle edit topic attachments with immediate upload
async function handleEditTopicAttachments(event) {
    const files = Array.from(event.target.files);
    const preview = document.getElementById('editTopicAttachmentsPreview');
    if (!preview) return;

    // Disable buttons during upload
    const submitBtn = document.querySelector('#editTopicForm button[type="submit"]');
    const cancelBtn = document.querySelector('#editTopicModal .btn-cancel');
    if (submitBtn) submitBtn.disabled = true;
    if (cancelBtn) cancelBtn.disabled = true;

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const fileIndex = editTopicAttachments.filter(a => !a.existing).length;
        
        // Create preview item with progress bar
        const previewItem = document.createElement('div');
        previewItem.id = `edit-topic-attachment-new-${fileIndex}`;
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
                    <button type="button" onclick="removeEditTopicAttachment('new-${fileIndex}')" style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 0.75rem; display: none;">×</button>
                `;
                preview.appendChild(previewItem);
                uploadFileToEditTopic(file, fileIndex, previewItem);
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
                <button type="button" onclick="removeEditTopicAttachment('new-${fileIndex}')" style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 0.75rem; display: none;">×</button>
            `;
            preview.appendChild(previewItem);
            uploadFileToEditTopic(file, fileIndex, previewItem);
        }
    }
}

// Upload file to edit topic with progress
async function uploadFileToEditTopic(file, fileIndex, previewItem) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', 'topic');
    // topic_id will be linked after topic update
    
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
                        editTopicUploadedAttachmentIds.push({
                            id: data.attachment_id,
                            fileIndex: fileIndex
                        });
                        
                        // Add to editTopicAttachments array
                        editTopicAttachments.push({
                            id: data.attachment_id,
                            file: file,
                            existing: false,
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
                        
                        checkEditTopicUploadsComplete();
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
                checkEditTopicUploadsComplete();
                reject(new Error('Upload failed'));
            };
            
            xhr.open('POST', '/api/forum/attachments/upload');
            xhr.send(formData);
        });
    } catch (error) {
        console.error('Error uploading attachment:', error);
        if (progressText) progressText.textContent = 'Error';
        if (progressBar) progressBar.style.background = 'var(--danger)';
        checkEditTopicUploadsComplete();
    }
}

// Check if all edit topic uploads are complete
function checkEditTopicUploadsComplete() {
    const preview = document.getElementById('editTopicAttachmentsPreview');
    if (!preview) return;
    
    const allProgressBars = preview.querySelectorAll('.upload-progress');
    const allComplete = Array.from(allProgressBars).every(progress => {
        return progress.style.display === 'none' || progress.querySelector('.progress-text').textContent === 'Uploaded' || progress.querySelector('.progress-text').textContent.includes('Error');
    });
    
    if (allComplete) {
        const submitBtn = document.querySelector('#editTopicForm button[type="submit"]');
        const cancelBtn = document.querySelector('#editTopicModal .btn-cancel');
        if (submitBtn) submitBtn.disabled = false;
        if (cancelBtn) cancelBtn.disabled = false;
    }
}

// Remove edit topic attachment
async function removeEditTopicAttachment(idOrIndex) {
    if (typeof idOrIndex === 'string' && idOrIndex.startsWith('new-')) {
        // Newly uploaded attachment - delete from server
        const fileIndex = parseInt(idOrIndex.replace('new-', ''));
        if (editTopicUploadedAttachmentIds && editTopicUploadedAttachmentIds.length > 0) {
            const uploadedAttachment = editTopicUploadedAttachmentIds.find(a => a.fileIndex === fileIndex);
            
            if (uploadedAttachment) {
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
                    editTopicUploadedAttachmentIds = editTopicUploadedAttachmentIds.filter(a => a.id !== uploadedAttachment.id);
                } catch (error) {
                    console.error('Error deleting attachment:', error);
                }
            }
        }
        
        // Remove from array
        editTopicAttachments = editTopicAttachments.filter(attach => !(attach.fileIndex === fileIndex && !attach.existing));
        
        // Remove preview item
        const previewItem = document.getElementById(`edit-topic-attachment-${idOrIndex}`);
        if (previewItem) {
            previewItem.remove();
        }
    } else if (typeof idOrIndex === 'string') {
        // Existing attachment by ID
        editTopicAttachments = editTopicAttachments.filter(attach => attach.id !== idOrIndex);
        renderEditTopicAttachmentsPreview();
    } else {
        // New attachment by index (legacy)
        const attach = editTopicAttachments[idOrIndex];
        if (attach && !attach.existing && attach.id) {
            // Delete from server if it was uploaded
            try {
                await fetch(`/api/forum/attachments/${attach.id}`, {
                    method: 'DELETE'
                });
            } catch (error) {
                console.error('Error deleting attachment:', error);
            }
        }
        editTopicAttachments.splice(idOrIndex, 1);
        renderEditTopicAttachmentsPreview();
    }
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
                            <label class="forum-form-label" for="editTopicMeta">Meta Tags <small style="color: var(--muted);">(Use @ for patients, # for appointments, $ for drugs, or type custom tags and then press ; or Enter to add)</small></label>
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
    editTopicUploadedAttachmentIds = []; // Reset uploaded attachment IDs
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
            editTopicAttachments.push({ id: attach.id, existing: true, file_path: attach.file_path, original_filename: attach.original_filename, mime_type: attach.mime_type });
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

async function hideEditTopicModal() {
    // Delete newly uploaded attachments if any
    if (editTopicUploadedAttachmentIds && editTopicUploadedAttachmentIds.length > 0) {
        for (const uploaded of editTopicUploadedAttachmentIds) {
            try {
                await fetch(`/api/forum/attachments/${uploaded.id}`, {
                    method: 'DELETE'
                });
            } catch (error) {
                console.error('Error deleting attachment:', error);
            }
        }
        editTopicUploadedAttachmentIds = [];
    }
    
    const modal = document.getElementById('editTopicModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

let editTopicAttachments = [];
let editTopicUploadedAttachmentIds = []; // Store uploaded attachment IDs for deletion on cancel

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
        
        // Attachments are already uploaded, just collect IDs
        let attachmentIds = [];
        
        // Add newly uploaded attachment IDs
        if (editTopicUploadedAttachmentIds && editTopicUploadedAttachmentIds.length > 0) {
            editTopicUploadedAttachmentIds.forEach(uploaded => {
                attachmentIds.push(uploaded.id);
            });
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
            loadTopic();
            loadPosts();
        } else {
            showError(data.message || 'Failed to update topic');
        }
    } catch (error) {
        console.error('Error updating topic:', error);
        showError('Error updating topic');
    }
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

// Close modals on outside click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('forum-modal')) {
        if (e.target.id === 'deleteTopicModal') {
            hideDeleteTopicModal();
        } else if (e.target.id === 'editTopicModal') {
            hideEditTopicModal();
        } else if (e.target.id === 'deletePostModal') {
            hideDeletePostModal();
        } else if (e.target.id === 'editPostModal') {
            hideEditPostModal();
        }
    }
});

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getAttachmentIcon(mimeType) {
    if (!mimeType) return 'bi-file-earmark';
    if (mimeType.startsWith('image/')) return 'bi-file-image';
    if (mimeType.startsWith('video/')) return 'bi-file-play';
    if (mimeType.startsWith('audio/')) return 'bi-file-music';
    if (mimeType.includes('pdf')) return 'bi-file-pdf';
    if (mimeType.includes('word') || mimeType.includes('document')) return 'bi-file-word';
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'bi-file-excel';
    if (mimeType.includes('zip') || mimeType.includes('archive')) return 'bi-file-zip';
    return 'bi-file-earmark';
}

function formatFileSize(bytes) {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
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

function openImageModal(imagePath, originalFilename) {
    // Create modal overlay with glass effect
    const modal = document.createElement('div');
    modal.id = 'forumImageModal';
    modal.className = 'forum-image-modal-overlay';
    modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);';
    
    // Create modal content with glass effect
    const modalContent = document.createElement('div');
    modalContent.className = 'forum-image-modal-content';
    modalContent.style.cssText = 'position: relative; max-width: 90%; max-height: 90%; background: rgba(248, 250, 252, 0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(226, 232, 240, 0.5); border-radius: 16px; padding: 1.5rem; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);';
    
    if (document.body.classList.contains('dark')) {
        modalContent.style.background = 'rgba(11, 18, 32, 0.85)';
        modalContent.style.borderColor = 'rgba(51, 65, 85, 0.5)';
    }
    
    modalContent.innerHTML = `
        <div style="position: relative;">
            <img src="${escapeHtml(imagePath)}" style="max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 8px; display: block;">
            <div style="display: flex; gap: 0.5rem; margin-top: 1rem; justify-content: center;">
                <button onclick="downloadImage('${escapeHtml(imagePath)}', '${escapeHtml(originalFilename)}')" class="forum-image-modal-btn" style="background: rgba(14, 165, 233, 0.2); border: 1px solid rgba(14, 165, 233, 0.4); color: var(--accent); padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-download"></i> Download
                </button>
                <button onclick="closeImageModal()" class="forum-image-modal-btn" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: var(--danger); padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer;">
                    <i class="bi bi-x-lg"></i> Close
                </button>
            </div>
        </div>
    `;
    
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    
    // Close on overlay click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeImageModal();
        }
    });
    
    // Close on Escape key
    const escapeHandler = function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
            document.removeEventListener('keydown', escapeHandler);
        }
    };
    document.addEventListener('keydown', escapeHandler);
}

function closeImageModal() {
    const modal = document.getElementById('forumImageModal');
    if (modal) {
        modal.remove();
    }
}

function downloadImage(imagePath, originalFilename) {
    const link = document.createElement('a');
    link.href = imagePath;
    link.download = originalFilename || 'image.jpg';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
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
            loadTopic(); // Reload topic to reflect pin status
            showToast('Pin status updated', 'success');
        } else {
            showError(data.message || 'Failed to update pin status');
        }
    } catch (error) {
        console.error('Error toggling pin:', error);
        showError('Error updating pin status');
    }
}

window.addEventListener('beforeunload', function() {
    stopForumPolling();
});
</script>

