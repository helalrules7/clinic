<?php
/**
 * Doctor Forum Main Page
 * صفحة المنتدى الرئيسية للأطباء
 */
?>

<link href="/app/Views/doctor/assets/css/forum/index.css?v=<?= file_exists(__DIR__ . '/assets/css/forum/index.css') ? filemtime(__DIR__ . '/assets/css/forum/index.css') : time() ?>" rel="stylesheet">
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
                <section class="field menu" style="min-width: 100%;">
                    <div class="control">
                        <select id="topicCategory" class="forum-form-input d-none" required>
                            <option value="All" selected>All</option>
                            <option value="General Discussion">General Discussion</option>
                            <option value="Clinical Case">Clinical Case</option>
                            <option value="Procedure Feedback">Procedure Feedback</option>
                            <option value="Protocol Update">Protocol Update</option>
                            <option value="Drug Interaction">Drug Interaction</option>
                            <option value="Prescription Inquiry">Prescription Inquiry</option>
                            <option value="Lab/Imaging Interpretation">Lab/Imaging Interpretation</option>
                        </select>
                        <button type="button" class="custom-select-toggle" aria-expanded="false">All</button>
                        <menu>
                            <li data-option="All" tabindex="0" role="button" class="selected"><i class="bi-tags fs-5"></i><h3>All</h3></li>
                            <li data-option="General Discussion" tabindex="0" role="button"><i class="bi-tags fs-5"></i><h3>General Discussion</h3></li>
                            <li data-option="Clinical Case" tabindex="0" role="button"><i class="bi-tags fs-5"></i><h3>Clinical Case</h3></li>
                            <li data-option="Procedure Feedback" tabindex="0" role="button"><i class="bi-tags fs-5"></i><h3>Procedure Feedback</h3></li>
                            <li data-option="Protocol Update" tabindex="0" role="button"><i class="bi-tags fs-5"></i><h3>Protocol Update</h3></li>
                            <li data-option="Drug Interaction" tabindex="0" role="button"><i class="bi-tags fs-5"></i><h3>Drug Interaction</h3></li>
                            <li data-option="Prescription Inquiry" tabindex="0" role="button"><i class="bi-tags fs-5"></i><h3>Prescription Inquiry</h3></li>
                            <li data-option="Lab/Imaging Interpretation" tabindex="0" role="button"><i class="bi-tags fs-5"></i><h3>Lab/Imaging Interpretation</h3></li>
                        </menu>
                    </div>
                </section>
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
    window.FORUM_CONFIG = {
        patientId: <?= isset($patient['id']) ? (int)$patient['id'] : 'null' ?>,
        userId: <?= isset($user['id']) ? (int)$user['id'] : 'null' ?>,
        isAdmin: <?= isset($user['role']) && $user['role'] === 'admin' ? 'true' : 'false' ?>,
        appointmentId: <?= isset($appointment['id']) ? (int)$appointment['id'] : 'null' ?>,
    };
</script>
<script src="/app/Views/doctor/assets/js/forum_index.js?v=<?= file_exists(__DIR__ . '/assets/js/forum_index.js') ? filemtime(__DIR__ . '/assets/js/forum_index.js') : time() ?>"></script>
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