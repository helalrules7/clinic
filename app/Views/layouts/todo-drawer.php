<?php
require_once __DIR__ . '/v11-i18n.php';
/**
 * Todo Drawer — right-side multi-list to-do drawer.
 *
 * Included by main.php. Renders a fixed <aside> off-canvas drawer that slides in
 * from the right (or up from the bottom on mobile). All dynamic content (lists,
 * task rows) is rendered client-side by todo-drawer.js via the public API:
 *   window.openTodoDrawer()  /  window.closeTodoDrawer()
 */
?>
<div class="td-backdrop" id="todoDrawerBackdrop" hidden></div>

<aside class="todo-drawer" id="todoDrawer" role="complementary"
       aria-label="<?= v11e('todo.drawer_label', 'To-do drawer') ?>" aria-hidden="true" tabindex="-1">

    <span class="td-drag-handle" aria-hidden="true"></span>

    <header class="td-header">
        <div class="td-header-top">
            <h2 class="td-title">
                <i class="bi bi-check2-square" aria-hidden="true"></i>
                <span><?= v11e('todo.title', 'To-Do') ?></span>
            </h2>
            <div class="td-header-actions">
                <button type="button" class="td-header-btn" id="todoArchivedBtn"
                        aria-label="<?= v11e('todo.archived_lists', 'Archived lists') ?>" title="<?= v11e('todo.archived_lists', 'Archived lists') ?>">
                    <i class="bi bi-archive" aria-hidden="true"></i>
                </button>
                <button type="button" class="td-close" id="todoDrawerClose"
                        aria-label="<?= v11e('todo.close', 'Close to-do drawer') ?>">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="td-filters" role="tablist" aria-label="<?= v11e('todo.filter_tasks', 'Filter tasks') ?>">
            <button type="button" role="tab" class="td-filter is-active"
                    data-filter="open" aria-selected="true"><?= v11e('todo.filter.open', 'Open') ?></button>
            <button type="button" role="tab" class="td-filter"
                    data-filter="done" aria-selected="false"><?= v11e('todo.filter.done', 'Done') ?></button>
            <button type="button" role="tab" class="td-filter"
                    data-filter="all" aria-selected="false"><?= v11e('todo.filter.all', 'All') ?></button>
        </div>
    </header>

    <nav class="td-list-rail" id="todoListRail" aria-label="<?= v11e('todo.lists_nav', 'Task lists') ?>">
        <!-- list chips rendered by JS, trailing "+ New list" chip appended -->
        <div class="td-rail-skeleton" aria-hidden="true">
            <span class="td-rail-sk"></span>
            <span class="td-rail-sk"></span>
            <span class="td-rail-sk"></span>
        </div>
    </nav>

    <div class="td-body" id="todoListBody">

        <section class="td-progress-card" id="todoProgressCard"
                 style="--list-c: var(--palette-indigo);">
            <div class="td-progress-meta">
                <p class="td-progress-label"><?= v11e('todo.progress_label', "Today's progress") ?></p>
                <h3 class="td-progress-title" id="todoProgressTitle"><?= v11e('todo.progress.lets_go', "Let's go!") ?></h3>
                <p class="td-progress-sub" id="todoProgressSub">0 of 0 completed</p>
            </div>
            <div class="td-progress-badge" id="todoProgressBadge"
                 aria-label="<?= v11e('todo.progress_pct', 'Completion percentage') ?>">0%</div>
            <div class="td-progress-bar" role="progressbar"
                 aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <div class="td-progress-fill" id="todoProgressFill" style="width:0%"></div>
            </div>
        </section>

        <form class="td-quick-add" id="todoQuickAdd" autocomplete="off">
            <span class="td-quick-add-bullet" aria-hidden="true"></span>
            <input type="text" class="td-quick-add-input"
                   id="todoQuickAddInput"
                   placeholder="<?= v11e('todo.quick_add_ph', 'Add a quick task and press Enter…') ?>"
                   maxlength="240"
                   aria-label="<?= v11e('todo.quick_add', 'Quick add task') ?>">
            <button type="submit" class="td-quick-add-btn" aria-label="<?= v11e('todo.add_task_btn', 'Add task') ?>">
                <i class="bi bi-arrow-return-left" aria-hidden="true"></i>
            </button>
        </form>

        <div class="td-rows" id="todoRows" role="list">
            <!-- task rows injected here -->
        </div>

        <div class="td-empty" id="todoEmpty" hidden>
            <i class="bi bi-clipboard2-check" aria-hidden="true"></i>
            <p class="td-empty-title"><?= v11e('todo.empty_title', 'Nothing here yet') ?></p>
            <p class="td-empty-sub"><?= v11e('todo.empty_sub', 'Add your first task above to get started.') ?></p>
        </div>

        <div class="td-loading" id="todoLoading" hidden>
            <span class="td-spinner" aria-hidden="true"></span>
            <span><?= v11e('todo.loading', 'Loading…') ?></span>
        </div>
    </div>

    <button type="button" class="td-fab" id="todoFullAddFab"
            aria-label="<?= v11e('todo.add_detailed', 'Add detailed task') ?>" title="<?= v11e('todo.add_detailed', 'Add detailed task') ?>">
        <i class="bi bi-plus-lg" aria-hidden="true"></i>
    </button>

    <!-- ============================================================
         List context popover (rename / color / move / archive)
         Positioned dynamically by JS.
         ============================================================ -->
    <div class="td-popover" id="todoListPopover" role="menu" hidden>
        <button type="button" role="menuitem" data-act="rename">
            <i class="bi bi-pencil" aria-hidden="true"></i> <?= v11e('todo.rename', 'Rename') ?>
        </button>
        <button type="button" role="menuitem" data-act="color">
            <i class="bi bi-palette" aria-hidden="true"></i> <?= v11e('todo.color', 'Color') ?>
        </button>
        <button type="button" role="menuitem" data-act="up">
            <i class="bi bi-arrow-up" aria-hidden="true"></i> <?= v11e('todo.move_up', 'Move up') ?>
        </button>
        <button type="button" role="menuitem" data-act="down">
            <i class="bi bi-arrow-down" aria-hidden="true"></i> <?= v11e('todo.move_down', 'Move down') ?>
        </button>
        <div class="td-popover-sep" role="separator"></div>
        <button type="button" role="menuitem" data-act="archive" data-popover-archive>
            <i class="bi bi-archive" aria-hidden="true"></i> <?= v11e('todo.archive_list', 'Archive list') ?>
        </button>
        <button type="button" role="menuitem" data-act="delete" class="is-danger" data-popover-delete>
            <i class="bi bi-trash" aria-hidden="true"></i> <?= v11e('todo.delete_list', 'Delete list') ?>
        </button>
    </div>

    <!-- ============================================================
         Full add/edit modal — lives INSIDE the drawer so the drawer
         backdrop doesn't dim it.
         ============================================================ -->
    <div class="td-modal" id="todoFullModal" hidden role="dialog"
         aria-modal="true" aria-labelledby="todoFullModalTitle">
        <div class="td-modal-backdrop" data-close></div>
        <div class="td-modal-panel" role="document">
            <header class="td-modal-head">
                <h3 id="todoFullModalTitle"><?= v11e('todo.new_task', 'New task') ?></h3>
                <button type="button" class="td-modal-close" data-close
                        aria-label="<?= v11e('todo.modal_close', 'Close') ?>">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </header>
            <form id="todoFullForm" class="td-modal-body" autocomplete="off">
                <input type="hidden" name="id" value="">

                <div class="td-field">
                    <label for="todoFmList"><?= v11e('todo.field.list', 'List') ?></label>
                    <select id="todoFmList" name="list_id" required></select>
                </div>

                <div class="td-field">
                    <label for="todoFmTitle"><?= v11e('todo.field.title', 'Title') ?></label>
                    <input type="text" id="todoFmTitle" name="title"
                           required maxlength="240"
                           placeholder="<?= v11e('todo.field.title_ph', 'What needs to be done?') ?>">
                </div>

                <div class="td-field">
                    <label for="todoFmDesc"><?= v11e('todo.field.desc', 'Description') ?></label>
                    <textarea id="todoFmDesc" name="description" rows="3"
                              placeholder="<?= v11e('todo.field.desc_ph', 'Notes, links, context…') ?>"></textarea>
                </div>

                <div class="td-field-row">
                    <div class="td-field">
                        <label for="todoFmDueAt"><?= v11e('todo.field.due', 'Due') ?></label>
                        <input type="datetime-local" id="todoFmDueAt" name="due_at">
                    </div>
                    <div class="td-field">
                        <label for="todoFmRemind"><?= v11e('todo.field.remind', 'Remind') ?></label>
                        <!-- Field name + values must match the backend's
                             ALLOWED_REMIND list in TodoController (15 / 60 /
                             240 / 1440 minutes). Anything else triggers a
                             422 "Invalid remind window" on save. -->
                        <select id="todoFmRemind" name="remind_before_minutes">
                            <option value=""><?= v11e('todo.remind.none', 'No reminder') ?></option>
                            <option value="15"><?= v11e('todo.remind.15', '15 min before') ?></option>
                            <option value="60"><?= v11e('todo.remind.60', '1 hour before') ?></option>
                            <option value="240"><?= v11e('todo.remind.240', '4 hours before') ?></option>
                            <option value="1440"><?= v11e('todo.remind.1440', '1 day before') ?></option>
                        </select>
                    </div>
                </div>

                <div class="td-field-row">
                    <div class="td-field">
                        <label for="todoFmPatient"><?= v11e('todo.field.patient', 'Patient') ?></label>
                        <div class="td-typeahead">
                            <input type="text" id="todoFmPatient"
                                   placeholder="<?= v11e('todo.field.patient_ph', 'Search patient by name or phone…') ?>"
                                   autocomplete="off">
                            <input type="hidden" name="patient_id" id="todoFmPatientId" value="">
                            <div class="td-typeahead-results" id="todoFmPatientResults" hidden></div>
                        </div>
                    </div>
                    <div class="td-field">
                        <label for="todoFmPriority"><?= v11e('todo.field.priority', 'Priority') ?></label>
                        <!-- Values must match backend's ALLOWED_PRIORITY:
                             low / med / high. Submitting 'normal' or 'urgent'
                             gets rejected with a 422 "Invalid priority". -->
                        <select id="todoFmPriority" name="priority">
                            <option value="low"><?= v11e('todo.priority.low', 'Low') ?></option>
                            <option value="med" selected><?= v11e('todo.priority.med', 'Medium') ?></option>
                            <option value="high"><?= v11e('todo.priority.high', 'High') ?></option>
                        </select>
                    </div>
                </div>

                <footer class="td-modal-foot">
                    <button type="button" class="td-btn td-btn-ghost" data-close>
                        <?= v11e('todo.cancel', 'Cancel') ?>
                    </button>
                    <button type="submit" class="td-btn td-btn-primary">
                        <i class="bi bi-check2" aria-hidden="true"></i>
                        <span data-submit-label><?= v11e('todo.save_task', 'Save task') ?></span>
                    </button>
                </footer>
            </form>
        </div>
    </div>

    <!-- ============================================================
         List create / edit modal — replaces the old inline rail form
         AND the rename prompt() + color cycle. Same .td-modal pattern
         as the task modal so it sits above the drawer (not clipped)
         and presents as a centered card.
         ============================================================ -->
    <div class="td-modal" id="todoListModal" hidden role="dialog"
         aria-modal="true" aria-labelledby="todoListModalTitle">
        <div class="td-modal-backdrop" data-close></div>
        <div class="td-modal-panel" role="document">
            <header class="td-modal-head">
                <h3 id="todoListModalTitle"><?= v11e('todo.new_list', 'New list') ?></h3>
                <button type="button" class="td-modal-close" data-close
                        aria-label="<?= v11e('todo.modal_close', 'Close') ?>">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </header>
            <form id="todoListForm" class="td-modal-body" autocomplete="off">
                <input type="hidden" name="id" value="">
                <div class="td-field">
                    <label for="todoListName"><?= v11e('todo.list_name', 'List name') ?></label>
                    <input type="text" id="todoListName" name="name"
                           required maxlength="60"
                           placeholder="<?= v11e('todo.list_name_ph', 'e.g. Clinic follow-ups') ?>">
                </div>

                <div class="td-field">
                    <label><?= v11e('todo.list_color', 'Color') ?></label>
                    <div class="td-color-dots" role="radiogroup" aria-label="<?= v11e('todo.list_color', 'List color') ?>">
                        <button type="button" class="td-dot is-active" role="radio"
                                aria-checked="true" data-color="indigo"
                                style="--dot:var(--palette-indigo)"></button>
                        <button type="button" class="td-dot" role="radio"
                                aria-checked="false" data-color="emerald"
                                style="--dot:var(--palette-emerald)"></button>
                        <button type="button" class="td-dot" role="radio"
                                aria-checked="false" data-color="rose"
                                style="--dot:var(--palette-rose)"></button>
                        <button type="button" class="td-dot" role="radio"
                                aria-checked="false" data-color="amber"
                                style="--dot:var(--palette-amber)"></button>
                        <button type="button" class="td-dot" role="radio"
                                aria-checked="false" data-color="ocean"
                                style="--dot:var(--palette-ocean)"></button>
                        <button type="button" class="td-dot" role="radio"
                                aria-checked="false" data-color="slate"
                                style="--dot:var(--palette-slate)"></button>
                    </div>
                </div>

                <div class="td-field">
                    <label for="todoListIcon"><?= v11e('todo.list_icon', 'Icon') ?></label>
                    <select id="todoListIcon" name="icon" class="td-new-list-icon">
                        <option value="bi-list-task"><?= v11e('todo.icon.list', 'List') ?></option>
                        <option value="bi-briefcase"><?= v11e('todo.icon.work', 'Work') ?></option>
                        <option value="bi-house-heart"><?= v11e('todo.icon.personal', 'Personal') ?></option>
                        <option value="bi-heart-pulse"><?= v11e('todo.icon.clinic', 'Clinic') ?></option>
                        <option value="bi-cart"><?= v11e('todo.icon.shopping', 'Shopping') ?></option>
                        <option value="bi-book"><?= v11e('todo.icon.study', 'Study') ?></option>
                        <option value="bi-stars"><?= v11e('todo.icon.ideas', 'Ideas') ?></option>
                        <option value="bi-flag"><?= v11e('todo.icon.goals', 'Goals') ?></option>
                    </select>
                </div>

                <footer class="td-modal-foot">
                    <button type="button" class="td-btn td-btn-ghost" data-close>
                        <?= v11e('todo.cancel', 'Cancel') ?>
                    </button>
                    <button type="submit" class="td-btn td-btn-primary">
                        <i class="bi bi-check2" aria-hidden="true"></i>
                        <span data-list-submit-label><?= v11e('todo.create_list', 'Create list') ?></span>
                    </button>
                </footer>
            </form>
        </div>
    </div>

    <!-- ============================================================
         Archived lists modal — view + restore (or permanently delete)
         lists that were archived from the context popover.
         ============================================================ -->
    <div class="td-modal" id="todoArchivedModal" hidden role="dialog"
         aria-modal="true" aria-labelledby="todoArchivedTitle">
        <div class="td-modal-backdrop" data-close></div>
        <div class="td-modal-panel" role="document">
            <header class="td-modal-head">
                <h3 id="todoArchivedTitle">
                    <i class="bi bi-archive" aria-hidden="true"></i> <?= v11e('todo.archived_title', 'Archived lists') ?>
                </h3>
                <button type="button" class="td-modal-close" data-close
                        aria-label="<?= v11e('todo.modal_close', 'Close') ?>">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </header>
            <div class="td-modal-body">
                <div class="td-archived" id="todoArchivedBody" role="list">
                    <!-- archived rows injected by JS -->
                </div>
                <div class="td-archived-empty" id="todoArchivedEmpty" hidden>
                    <i class="bi bi-archive" aria-hidden="true"></i>
                    <p><?= v11e('todo.no_archived', 'No archived lists') ?></p>
                </div>
                <div class="td-archived-loading" id="todoArchivedLoading" hidden>
                    <span class="td-spinner" aria-hidden="true"></span>
                    <span><?= v11e('todo.loading', 'Loading…') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Archived list row template -->
    <template id="tpl-td-archived-row">
        <div class="td-archived-row" role="listitem" data-list-id="">
            <span class="td-archived-icon bi" aria-hidden="true"></span>
            <span class="td-archived-name"></span>
            <span class="td-archived-count"></span>
            <div class="td-archived-acts">
                <button type="button" class="td-btn td-btn-ghost td-btn-sm"
                        data-act="restore">
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                    <?= v11e('todo.restore', 'Restore') ?>
                </button>
                <button type="button" class="td-archived-del" data-act="delete"
                        aria-label="<?= v11e('todo.delete_perm', 'Delete permanently') ?>" title="<?= v11e('todo.delete_perm', 'Delete permanently') ?>">
                    <i class="bi bi-trash" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </template>

    <!-- ============================================================
         TEMPLATES (cloned by JS)
         ============================================================ -->

    <!-- List chip template (rendered into #todoListRail) -->
    <template id="tpl-td-list-chip">
        <button type="button" class="td-list-chip" role="tab"
                aria-selected="false" data-list-id="">
            <i class="td-list-icon bi" aria-hidden="true"></i>
            <span class="td-list-name"></span>
            <span class="td-list-count" aria-label="<?= v11e('todo.open_tasks', 'open tasks') ?>"></span>
            <span class="td-list-opts" role="button" tabindex="-1"
                  aria-label="<?= v11e('todo.list_options', 'List options') ?>" title="<?= v11e('todo.list_options', 'List options') ?>">
                <i class="bi bi-three-dots-vertical" aria-hidden="true"></i>
            </span>
        </button>
    </template>

    <!-- Task row template -->
    <template id="tpl-td-row">
        <div class="td-row" role="listitem" data-task-id="" data-status="open">
            <button type="button" class="td-check" aria-label="<?= v11e('todo.toggle_complete', 'Toggle complete') ?>">
                <i class="bi bi-check2" aria-hidden="true"></i>
            </button>
            <div class="td-row-body">
                <p class="td-row-title"></p>
                <div class="td-row-meta">
                    <span class="td-row-due" hidden>
                        <i class="bi bi-clock" aria-hidden="true"></i>
                        <span class="td-row-due-text"></span>
                    </span>
                    <span class="td-row-priority" hidden></span>
                    <span class="td-row-patient" hidden>
                        <i class="bi bi-person" aria-hidden="true"></i>
                        <span class="td-row-patient-name"></span>
                    </span>
                </div>
            </div>
            <div class="td-row-actions">
                <button type="button" class="td-row-act" data-act="snooze"
                        aria-label="<?= v11e('todo.snooze', 'Snooze') ?>" title="<?= v11e('todo.snooze', 'Snooze') ?>">
                    <i class="bi bi-bell-slash" aria-hidden="true"></i>
                </button>
                <button type="button" class="td-row-act" data-act="edit"
                        aria-label="<?= v11e('todo.edit', 'Edit') ?>" title="<?= v11e('todo.edit', 'Edit') ?>">
                    <i class="bi bi-pencil" aria-hidden="true"></i>
                </button>
                <button type="button" class="td-row-act" data-act="delete"
                        aria-label="<?= v11e('todo.delete', 'Delete') ?>" title="<?= v11e('todo.delete', 'Delete') ?>">
                    <i class="bi bi-trash" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </template>

    <!-- Snooze options template -->
    <template id="tpl-td-snooze">
        <div class="td-snooze-menu" role="menu">
            <button type="button" role="menuitem" data-snooze="15"><?= v11e('todo.snooze.15', '15 min') ?></button>
            <button type="button" role="menuitem" data-snooze="60"><?= v11e('todo.snooze.60', '1 hour') ?></button>
            <button type="button" role="menuitem" data-snooze="240"><?= v11e('todo.snooze.240', '4 hours') ?></button>
            <button type="button" role="menuitem" data-snooze="1440"><?= v11e('todo.snooze.1440', 'Tomorrow') ?></button>
            <button type="button" role="menuitem" data-snooze="10080"><?= v11e('todo.snooze.10080', 'Next week') ?></button>
        </div>
    </template>

</aside>
