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

/* Posts/Replies */
.forum-posts-container {
    margin-top: 2rem;
}

.forum-post {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.forum-post:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.forum-post.reply {
    margin-left: 3rem;
    border-left: 3px solid var(--accent);
}

.forum-post.reply.reply-2 {
    margin-left: 6rem;
}

.forum-post.reply.reply-3 {
    margin-left: 9rem;
}

.forum-post-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
}

.forum-post-author-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.forum-post-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border);
}

.forum-post-avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--muted);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    border: 2px solid var(--border);
}

.forum-post-author {
    font-weight: 600;
    color: var(--text);
    margin-bottom: 0.25rem;
}

.forum-post-time {
    font-size: 0.875rem;
    color: var(--muted);
}

.forum-post-content {
    color: var(--text);
    line-height: 1.8;
    margin-bottom: 1rem;
}

.forum-post-images {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
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
    gap: 1rem;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
    flex-wrap: wrap;
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
    border: 1px solid var(--border);
    padding: 0.5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
}

.forum-post-like:hover {
    background: rgba(16, 185, 129, 0.1);
    border-color: var(--success);
}

.forum-post-dislike:hover {
    background: rgba(239, 68, 68, 0.1);
    border-color: var(--danger);
}

.forum-post-like.active {
    background: rgba(16, 185, 129, 0.2);
    border-color: var(--success);
    color: var(--success);
}

.forum-post-dislike.active {
    background: rgba(239, 68, 68, 0.2);
    border-color: var(--danger);
    color: var(--danger);
}

.forum-post-reply-btn {
    background: var(--accent);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.875rem;
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
.forum-autocomplete-portal {
    position: fixed;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    z-index: 10001;
    max-height: 300px;
    overflow-y: auto;
    min-width: 300px;
    display: none;
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
            <div class="forum-image-upload">
                <input type="file" id="replyImageInput" accept="image/*" multiple onchange="handleImageUpload(event)">
                <label for="replyImageInput" class="forum-image-upload-label">
                    <i class="bi bi-image"></i> Add Images
                </label>
            </div>
            <button class="forum-reply-submit" onclick="submitReply()">Post Reply</button>
        </div>
    </div>
</div>

<!-- Autocomplete Portal -->
<div id="forumAutocompletePortal" class="forum-autocomplete-portal"></div>

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
        return `<span class="forum-tag ${tag.tag_type}">${getTagIcon(tag.tag_type)} ${escapeHtml(tagName)}</span>`;
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
        } else {
            showError('Failed to load posts');
        }
    } catch (error) {
        console.error('Error loading posts:', error);
        showError('Error loading posts');
    }
}

function renderPosts() {
    const container = document.getElementById('postsContainer');
    
    if (forumPosts.length === 0) {
        container.innerHTML = `
            <div class="forum-empty">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💬</div>
                <p>No replies yet. Be the first to reply!</p>
                <button class="forum-reply-submit" onclick="showReplyForm()" style="margin-top: 1rem;">Post First Reply</button>
            </div>
        `;
        return;
    }
    
    let html = '';
    forumPosts.forEach(post => {
        html += renderPost(post, 0);
    });
    
    container.innerHTML = html;
}

function renderPost(post, depth) {
    const timeAgo = getTimeAgo(post.created_at);
    const imagesHtml = post.images && post.images.length > 0 ? post.images.map(img => 
        `<img src="${escapeHtml(img.image_path)}" alt="${escapeHtml(img.original_filename)}" class="forum-post-image" onclick="openImageModal('${escapeHtml(img.image_path)}', '${escapeHtml(img.original_filename)}')">`
    ).join('') : '';
    
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
    
    let html = `
        <div class="forum-post ${depthClass} ${depthClass2} ${depthClass3}" data-post-id="${post.id}">
            <div class="forum-post-header">
                <div class="forum-post-author-info">
                    ${post.creator_image ? (() => {
                        const imagePath = post.creator_image.startsWith('/public/') ? post.creator_image : '/public' + post.creator_image;
                        return `<img src="${escapeHtml(imagePath)}" alt="${escapeHtml(post.creator_name || 'Unknown')}" class="forum-post-avatar">`;
                    })() : '<div class="forum-post-avatar-placeholder"><i class="bi bi-person"></i></div>'}
                    <div>
                        <div class="forum-post-author">${escapeHtml(post.creator_name || 'Unknown')}</div>
                        <div class="forum-post-time">${timeAgo}</div>
                    </div>
                </div>
                ${canEdit || canDelete ? `
                    <div class="forum-post-actions-header" onclick="event.stopPropagation();">
                        ${canEdit ? `<button class="forum-post-action-btn edit-btn" onclick="editPost(${post.id})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>` : ''}
                        ${canDelete ? `<button class="forum-post-action-btn delete-btn" onclick="deletePostConfirm(${post.id})" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>` : ''}
                    </div>
                ` : ''}
            </div>
            <div class="forum-post-content">${post.content}</div>
            ${imagesHtml ? `<div class="forum-post-images">${imagesHtml}</div>` : ''}
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
    `;
    
    if (post.children && post.children.length > 0) {
        post.children.forEach(child => {
            html += renderPost(child, depth + 1);
        });
    }
    
    return html;
}

function showReplyForm(parentId = null) {
    currentReplyParentId = parentId;
    document.getElementById('replyForm').classList.remove('hidden');
    document.getElementById('replyContent').focus();
}

function hideReplyForm() {
    currentReplyParentId = null;
    document.getElementById('replyForm').classList.add('hidden');
    document.getElementById('replyContent').innerHTML = '';
    uploadedImages = [];
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
                content: content
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
            
            hideReplyForm();
            loadPosts();
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
    
    autocompletePortal.style.display = 'block';
    autocompletePortal.style.left = rect.left + 'px';
    autocompletePortal.style.top = (rect.bottom + 5) + 'px';
    
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
            const newRange = document.createRange();
            newRange.setStartAfter(replacement);
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
    document.getElementById('editTopicContent').innerHTML = topic.content;
    document.getElementById('editTopicForm').setAttribute('data-topic-id', topic.id);
    modalElement.classList.add('show');
}

function hideEditTopicModal() {
    const modal = document.getElementById('editTopicModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

async function updateTopic(topicId) {
    const title = document.getElementById('editTopicTitle').value.trim();
    const contentDiv = document.getElementById('editTopicContent');
    const content = contentDiv.innerHTML.trim();
    
    if (!title || !content) {
        showError('Title and content are required');
        return;
    }
    
    try {
        const tags = extractTagsFromContent(contentDiv);
        
        const response = await fetch(`/api/forum/topics/${topicId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                title: title,
                content: content,
                tags: tags
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

