<?php
/**
 * Doctor Forum Topic Page
 * صفحة الموضوع في المنتدى
 */
$topicId = $topicId ?? null;
?>
<link href="/app/Views/doctor/assets/css/forum/topic.css?v=<?= file_exists(__DIR__ . '/assets/css/forum/topic.css') ? filemtime(__DIR__ . '/assets/css/forum/topic.css') : time() ?>" rel="stylesheet">
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
    window.TOPIC_CONFIG = {
        topicId: <?= isset($topicId) ? (int)$topicId : 'null' ?>,
        userId: <?= isset($user['id']) ? (int)$user['id'] : 'null' ?>,
        isAdmin: <?= isset($user['role']) && $user['role'] === 'admin' ? 'true' : 'false' ?>,
    };
</script>

<script src="/app/Views/doctor/assets/js/topic.js?v=<?= file_exists(__DIR__ . '/assets/js/topic.js') ? filemtime(__DIR__ . '/assets/js/topic.js') : time() ?>"></script>
<style>
    .modal-backdrop.show{
        display: none !important;
    }
    body > div.modal-backdrop.fade.show{
        display: none !important;
    }
    .dark .modal-content{
    background: rgba(11, 18, 32, 0.8) !important;
    }
    .modal-content{
    background: rgba(248, 250, 252, 0.8) !important;
    }
</style>