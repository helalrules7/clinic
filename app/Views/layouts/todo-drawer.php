<?php
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
       aria-label="To-do drawer" aria-hidden="true" tabindex="-1">

    <span class="td-drag-handle" aria-hidden="true"></span>

    <header class="td-header">
        <div class="td-header-top">
            <h2 class="td-title">
                <i class="bi bi-check2-square" aria-hidden="true"></i>
                <span>To-Do</span>
            </h2>
            <button type="button" class="td-close" id="todoDrawerClose"
                    aria-label="Close to-do drawer">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <div class="td-filters" role="tablist" aria-label="Filter tasks">
            <button type="button" role="tab" class="td-filter is-active"
                    data-filter="open" aria-selected="true">Open</button>
            <button type="button" role="tab" class="td-filter"
                    data-filter="done" aria-selected="false">Done</button>
            <button type="button" role="tab" class="td-filter"
                    data-filter="all" aria-selected="false">All</button>
        </div>
    </header>

    <nav class="td-list-rail" id="todoListRail" aria-label="Task lists">
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
                <p class="td-progress-label">Today's progress</p>
                <h3 class="td-progress-title" id="todoProgressTitle">Let's go!</h3>
                <p class="td-progress-sub" id="todoProgressSub">0 of 0 completed</p>
            </div>
            <div class="td-progress-badge" id="todoProgressBadge"
                 aria-label="Completion percentage">0%</div>
            <div class="td-progress-bar" role="progressbar"
                 aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <div class="td-progress-fill" id="todoProgressFill" style="width:0%"></div>
            </div>
        </section>

        <form class="td-quick-add" id="todoQuickAdd" autocomplete="off">
            <span class="td-quick-add-bullet" aria-hidden="true"></span>
            <input type="text" class="td-quick-add-input"
                   id="todoQuickAddInput"
                   placeholder="Add a quick task and press Enter…"
                   maxlength="240"
                   aria-label="Quick add task">
            <button type="submit" class="td-quick-add-btn" aria-label="Add task">
                <i class="bi bi-arrow-return-left" aria-hidden="true"></i>
            </button>
        </form>

        <div class="td-rows" id="todoRows" role="list">
            <!-- task rows injected here -->
        </div>

        <div class="td-empty" id="todoEmpty" hidden>
            <i class="bi bi-clipboard2-check" aria-hidden="true"></i>
            <p class="td-empty-title">Nothing here yet</p>
            <p class="td-empty-sub">Add your first task above to get started.</p>
        </div>

        <div class="td-loading" id="todoLoading" hidden>
            <span class="td-spinner" aria-hidden="true"></span>
            <span>Loading…</span>
        </div>
    </div>

    <button type="button" class="td-fab" id="todoFullAddFab"
            aria-label="Add detailed task" title="Add detailed task">
        <i class="bi bi-plus-lg" aria-hidden="true"></i>
    </button>

    <!-- ============================================================
         List context popover (rename / color / move / archive)
         Positioned dynamically by JS.
         ============================================================ -->
    <div class="td-popover" id="todoListPopover" role="menu" hidden>
        <button type="button" role="menuitem" data-act="rename">
            <i class="bi bi-pencil" aria-hidden="true"></i> Rename
        </button>
        <button type="button" role="menuitem" data-act="color">
            <i class="bi bi-palette" aria-hidden="true"></i> Color
        </button>
        <button type="button" role="menuitem" data-act="up">
            <i class="bi bi-arrow-up" aria-hidden="true"></i> Move up
        </button>
        <button type="button" role="menuitem" data-act="down">
            <i class="bi bi-arrow-down" aria-hidden="true"></i> Move down
        </button>
        <div class="td-popover-sep" role="separator"></div>
        <button type="button" role="menuitem" data-act="archive" data-popover-archive>
            <i class="bi bi-archive" aria-hidden="true"></i> Archive list
        </button>
        <button type="button" role="menuitem" data-act="delete" class="is-danger" data-popover-delete>
            <i class="bi bi-trash" aria-hidden="true"></i> Delete list
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
                <h3 id="todoFullModalTitle">New task</h3>
                <button type="button" class="td-modal-close" data-close
                        aria-label="Close">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </header>
            <form id="todoFullForm" class="td-modal-body" autocomplete="off">
                <input type="hidden" name="id" value="">

                <div class="td-field">
                    <label for="todoFmList">List</label>
                    <select id="todoFmList" name="list_id" required></select>
                </div>

                <div class="td-field">
                    <label for="todoFmTitle">Title</label>
                    <input type="text" id="todoFmTitle" name="title"
                           required maxlength="240"
                           placeholder="What needs to be done?">
                </div>

                <div class="td-field">
                    <label for="todoFmDesc">Description</label>
                    <textarea id="todoFmDesc" name="description" rows="3"
                              placeholder="Notes, links, context…"></textarea>
                </div>

                <div class="td-field-row">
                    <div class="td-field">
                        <label for="todoFmDueAt">Due</label>
                        <input type="datetime-local" id="todoFmDueAt" name="due_at">
                    </div>
                    <div class="td-field">
                        <label for="todoFmRemind">Remind</label>
                        <!-- Field name + values must match the backend's
                             ALLOWED_REMIND list in TodoController (15 / 60 /
                             240 / 1440 minutes). Anything else triggers a
                             422 "Invalid remind window" on save. -->
                        <select id="todoFmRemind" name="remind_before_minutes">
                            <option value="">No reminder</option>
                            <option value="15">15 min before</option>
                            <option value="60">1 hour before</option>
                            <option value="240">4 hours before</option>
                            <option value="1440">1 day before</option>
                        </select>
                    </div>
                </div>

                <div class="td-field-row">
                    <div class="td-field">
                        <label for="todoFmPatient">Patient</label>
                        <div class="td-typeahead">
                            <input type="text" id="todoFmPatient"
                                   placeholder="Search patient by name or phone…"
                                   autocomplete="off">
                            <input type="hidden" name="patient_id" id="todoFmPatientId" value="">
                            <div class="td-typeahead-results" id="todoFmPatientResults" hidden></div>
                        </div>
                    </div>
                    <div class="td-field">
                        <label for="todoFmPriority">Priority</label>
                        <!-- Values must match backend's ALLOWED_PRIORITY:
                             low / med / high. Submitting 'normal' or 'urgent'
                             gets rejected with a 422 "Invalid priority". -->
                        <select id="todoFmPriority" name="priority">
                            <option value="low">Low</option>
                            <option value="med" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>

                <footer class="td-modal-foot">
                    <button type="button" class="td-btn td-btn-ghost" data-close>
                        Cancel
                    </button>
                    <button type="submit" class="td-btn td-btn-primary">
                        <i class="bi bi-check2" aria-hidden="true"></i>
                        <span data-submit-label>Save task</span>
                    </button>
                </footer>
            </form>
        </div>
    </div>

    <!-- ============================================================
         TEMPLATES (cloned by JS)
         ============================================================ -->

    <!-- List chip template (rendered into #todoListRail) -->
    <template id="tpl-td-list-chip">
        <button type="button" class="td-list-chip" role="tab"
                aria-selected="false" data-list-id="">
            <i class="td-list-icon bi" aria-hidden="true"></i>
            <span class="td-list-name"></span>
            <span class="td-list-count" aria-label="open tasks"></span>
        </button>
    </template>

    <!-- Task row template -->
    <template id="tpl-td-row">
        <div class="td-row" role="listitem" data-task-id="" data-status="open">
            <button type="button" class="td-check" aria-label="Toggle complete">
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
                        aria-label="Snooze" title="Snooze">
                    <i class="bi bi-bell-slash" aria-hidden="true"></i>
                </button>
                <button type="button" class="td-row-act" data-act="edit"
                        aria-label="Edit" title="Edit">
                    <i class="bi bi-pencil" aria-hidden="true"></i>
                </button>
                <button type="button" class="td-row-act" data-act="delete"
                        aria-label="Delete" title="Delete">
                    <i class="bi bi-trash" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </template>

    <!-- Inline new-list form (cloned into rail when "+ New list" is clicked) -->
    <template id="tpl-td-new-list">
        <form class="td-new-list" autocomplete="off">
            <input type="text" name="name" class="td-new-list-name"
                   placeholder="List name" maxlength="60" required>
            <div class="td-color-dots" role="radiogroup" aria-label="List color">
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
            <select name="icon" class="td-new-list-icon" aria-label="Icon">
                <option value="bi-list-task">List</option>
                <option value="bi-briefcase">Work</option>
                <option value="bi-house-heart">Personal</option>
                <option value="bi-heart-pulse">Clinic</option>
                <option value="bi-cart">Shopping</option>
                <option value="bi-book">Study</option>
                <option value="bi-stars">Ideas</option>
                <option value="bi-flag">Goals</option>
            </select>
            <div class="td-new-list-actions">
                <button type="button" class="td-btn td-btn-ghost" data-cancel>Cancel</button>
                <button type="submit" class="td-btn td-btn-primary">Create</button>
            </div>
        </form>
    </template>

    <!-- Snooze options template -->
    <template id="tpl-td-snooze">
        <div class="td-snooze-menu" role="menu">
            <button type="button" role="menuitem" data-snooze="15">15 min</button>
            <button type="button" role="menuitem" data-snooze="60">1 hour</button>
            <button type="button" role="menuitem" data-snooze="240">4 hours</button>
            <button type="button" role="menuitem" data-snooze="1440">Tomorrow</button>
            <button type="button" role="menuitem" data-snooze="10080">Next week</button>
        </div>
    </template>

</aside>
