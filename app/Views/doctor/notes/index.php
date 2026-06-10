<?php
/**
 * Doctor Notes Page
 * صفحة الملحوظات للأطباء
 */
?>
<link href="/app/Views/doctor/assets/css/notes.css?v=<?= file_exists(__DIR__ . '/assets/css/notes.css') ? filemtime(__DIR__ . '/assets/css/notes.css') : time() ?>" rel="stylesheet">

<div class="container-fluid <?= !empty($notesEmbedded) ? 'notes-page--embedded' : '' ?>">
    <div class="notes-toolbar">
        <div class="notes-toolbar-head">
            <span class="notes-toolbar-icon"><i class="bi bi-sticky-fill"></i></span>
            <div>
                <h5 class="mb-0">My Notes</h5>
                <small class="text-muted">Create and organize your personal notes</small>
            </div>
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

            <button class="btn btn-sm btn-outline-danger" onclick="showDeleteAllNotesConfirmation()" title="Delete All Notes">
                <i class="bi bi-trash me-1"></i>Delete All
            </button>
        </div>
    </div>
    
    <div class="notes-container" id="notesContainer">
        <button class="notes-container-reset" id="notesContainerReset" onclick="resetContainerSize()" title="Reset Container Size">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
        <div class="empty-state" id="emptyState" style="display: none;">
            <i class="bi bi-sticky"></i>
            <h4>No notes yet</h4>
            <p>Click "Add Note" to create your first note</p>
        </div>
        <div class="notes-container-resize" id="notesContainerResize" onmousedown="startContainerResize(event)" ontouchstart="startContainerResize(event)"></div>
    </div>
</div>

<script defer src="/app/Views/doctor/assets/js/notes-page.js?v=<?= file_exists(__DIR__ . '/../assets/js/notes-page.js') ? filemtime(__DIR__ . '/../assets/js/notes-page.js') : time() ?>"></script>

<style>
.dark .modal-content{
    background: var(--card) !important;
    }
    .modal-content{
    background: var(--card) !important;
    }
</style>