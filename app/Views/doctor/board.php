<link
    href="/app/Views/doctor/assets/css/board.css?v=<?= file_exists(__DIR__ . '/assets/css/board.css') ? filemtime(__DIR__ . '/assets/css/board.css') : time() ?>"
    rel="stylesheet">
<link
    href="/app/Views/doctor/assets/css/comment-media.css?v=<?= file_exists(__DIR__ . '/assets/css/comment-media.css') ? filemtime(__DIR__ . '/assets/css/comment-media.css') : time() ?>"
    rel="stylesheet">

<div class="board-page <?= !empty($boardEmbedded) ? 'board-page--embedded' : '' ?>" id="boardPage" data-size="md">

    <!-- ============================ HEADER ============================ -->
    <header class="board-head">
        <!-- Overview header -->
        <div class="board-head-row" id="boardOverviewHead">
            <div class="board-head-titles">
                <h1 class="board-head-title">
                    <i class="bi bi-columns-gap"></i>
                    Boards
                </h1>
                <p class="board-head-sub">
                    Organize patient follow-up across stages of care
                    <span class="board-head-count" id="boardsCount" hidden></span>
                </p>
            </div>
            <div class="board-head-actions">
                <div class="board-search">
                    <i class="bi bi-search"></i>
                    <input type="search" id="boardsSearch" class="board-search-input"
                           placeholder="Search boards…" autocomplete="off"
                           aria-label="Search boards">
                </div>
                <div class="board-size" role="group" aria-label="Card size">
                    <button type="button" class="board-size-btn" data-size="sm" title="Compact" aria-label="Compact cards"><i class="bi bi-dash-square"></i></button>
                    <button type="button" class="board-size-btn" data-size="md" title="Default" aria-label="Default cards"><i class="bi bi-square"></i></button>
                    <button type="button" class="board-size-btn" data-size="lg" title="Large" aria-label="Large cards"><i class="bi bi-plus-square"></i></button>
                </div>
                <button type="button" class="board-btn board-btn-primary" id="boardCreateBtn">
                    <i class="bi bi-plus-lg"></i>
                    <span>New board</span>
                </button>
            </div>
        </div>

        <!-- Detail header -->
        <div class="board-head-row" id="boardDetailHead" hidden>
            <div class="board-head-titles">
                <nav class="board-breadcrumb" aria-label="Breadcrumb">
                    <button type="button" class="board-crumb-back" id="boardBackBtn"
                            aria-label="Back to all boards">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                    <button type="button" class="board-crumb-link" id="boardCrumbRoot">Boards</button>
                    <i class="bi bi-chevron-right board-crumb-sep"></i>
                    <span class="board-crumb-current" id="boardCrumbName">—</span>
                </nav>
                <h1 class="board-head-title">
                    <span class="board-detail-swatch" id="boardDetailSwatch"></span>
                    <span id="boardDetailName">—</span>
                    <span class="board-pill" id="boardDetailCount">0</span>
                </h1>
                <p class="board-head-sub" id="boardDetailDesc"></p>
            </div>
            <div class="board-head-actions">
                <div class="board-search">
                    <i class="bi bi-search"></i>
                    <input type="search" id="patientsSearch" class="board-search-input"
                           placeholder="Search patients by name or phone…" autocomplete="off"
                           aria-label="Search patients">
                </div>
                <div class="board-sort">
                    <label for="patientsSort" class="visually-hidden">Sort</label>
                    <i class="bi bi-sort-down"></i>
                    <select id="patientsSort" class="board-sort-select" aria-label="Sort patients">
                        <option value="moved">Recently updated</option>
                        <option value="recent">Last visit</option>
                        <option value="visits">Visit count</option>
                        <option value="name">Name</option>
                    </select>
                </div>
                <div class="board-size" role="group" aria-label="Card size">
                    <button type="button" class="board-size-btn" data-size="sm" title="Compact" aria-label="Compact cards"><i class="bi bi-dash-square"></i></button>
                    <button type="button" class="board-size-btn" data-size="md" title="Default" aria-label="Default cards"><i class="bi bi-square"></i></button>
                    <button type="button" class="board-size-btn" data-size="lg" title="Large" aria-label="Large cards"><i class="bi bi-plus-square"></i></button>
                </div>
                <button type="button" class="board-btn board-btn-ghost" id="boardEditBtn" title="Edit board">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="board-btn board-btn-primary" id="patientAddBtn">
                    <i class="bi bi-person-plus"></i>
                    <span>Add patient</span>
                </button>
            </div>
        </div>
    </header>

    <!-- ============================ OVERVIEW ============================ -->
    <section id="boardOverview">
        <div class="board-grid" id="boardGrid" role="list"></div>

        <!-- Skeleton -->
        <div class="board-grid" id="boardsSkeleton">
            <?php for ($i = 0; $i < 6; $i++): ?>
            <div class="board-skel-card">
                <div class="skel skel-line skel-w60"></div>
                <div class="skel skel-line skel-w90"></div>
                <div class="skel skel-line skel-w40"></div>
                <div class="board-skel-foot">
                    <div class="skel skel-pill"></div>
                </div>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Empty -->
        <div class="board-state" id="boardsEmpty" hidden>
            <?php include __DIR__ . '/assets/board-illustration.php'; ?>
            <h3 class="board-state-title">No boards yet</h3>
            <p class="board-state-text">Create your first board to organize patient follow-up by stage of treatment.</p>
            <button type="button" class="board-btn board-btn-primary" id="boardsEmptyCreateBtn">
                <i class="bi bi-plus-lg"></i> Create board
            </button>
        </div>

        <!-- No search results -->
        <div class="board-state" id="boardsNoResults" hidden>
            <i class="bi bi-search board-state-icon"></i>
            <h3 class="board-state-title">No results</h3>
            <p class="board-state-text">No board matches your search.</p>
        </div>
    </section>

    <!-- ============================ DETAIL ============================ -->
    <section id="boardDetail" hidden>
        <div class="patient-grid" id="patientGrid" role="list"></div>

        <!-- Skeleton -->
        <div class="patient-grid" id="patientsSkeleton" hidden>
            <?php for ($i = 0; $i < 6; $i++): ?>
            <div class="patient-skel-card">
                <div class="patient-skel-head">
                    <div class="skel skel-avatar"></div>
                    <div class="patient-skel-lines">
                        <div class="skel skel-line skel-w70"></div>
                        <div class="skel skel-line skel-w40"></div>
                    </div>
                </div>
                <div class="skel skel-line skel-w90"></div>
                <div class="skel skel-line skel-w60"></div>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Empty board -->
        <div class="board-state" id="patientsEmpty" hidden>
            <?php include __DIR__ . '/assets/board-illustration.php'; ?>
            <h3 class="board-state-title">This board is empty</h3>
            <p class="board-state-text">Add patients to this board to start following them through this stage.</p>
            <button type="button" class="board-btn board-btn-primary" id="patientsEmptyAddBtn">
                <i class="bi bi-person-plus"></i> Add patient
            </button>
        </div>

        <!-- No search results -->
        <div class="board-state" id="patientsNoResults" hidden>
            <i class="bi bi-search board-state-icon"></i>
            <h3 class="board-state-title">No results</h3>
            <p class="board-state-text">No patient in this board matches your search.</p>
        </div>
    </section>
</div>

<!-- ============================ MODALS ============================ -->

<!-- Board create / edit -->
<div class="modal fade" id="boardEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="boardEditForm" autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title" id="boardEditTitle">New board</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="boardEditId" value="">
                    <div class="mb-3">
                        <label class="form-label" for="boardEditName">Board name <span class="req">*</span></label>
                        <input type="text" class="form-control" id="boardEditName" maxlength="80" required
                               placeholder="e.g. Post-op follow-up">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="boardEditDesc">Description</label>
                        <textarea class="form-control" id="boardEditDesc" maxlength="255" rows="2"
                                  placeholder="A short description of this stage (optional)"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Board color</label>
                        <div class="board-color-row" id="boardColorRow" role="radiogroup" aria-label="Board color"></div>
                        <input type="hidden" id="boardEditColor" value="#0ea5e9">
                    </div>
                    <div class="mb-1">
                        <div class="board-icon-row-head">
                            <label class="form-label mb-0">Board icon</label>
                            <span class="board-edit-preview-wrap">
                                <span class="board-edit-preview-label">Preview</span>
                                <span class="board-icon-chip" id="boardEditPreview" aria-hidden="true"><i class="bi bi-kanban"></i></span>
                            </span>
                        </div>
                        <div class="board-icon-row" id="boardIconRow" role="radiogroup" aria-label="Board icon"></div>
                        <input type="hidden" id="boardEditIcon" value="bi-kanban">
                    </div>
                    <div class="board-form-error" id="boardEditError" hidden></div>
                </div>
                <div class="modal-footer board-edit-footer">
                    <button type="button" class="board-btn board-btn-ghost board-edit-delete" id="boardEditDelete" hidden>
                        <i class="bi bi-trash"></i> Delete
                    </button>
                    <div class="board-edit-footer-end">
                        <button type="button" class="board-btn board-btn-ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="board-btn board-btn-primary" id="boardEditSave">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Board delete confirm -->
<div class="modal fade" id="boardDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-1"></i> Delete board
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Are you sure you want to delete the board “<strong id="boardDeleteName">—</strong>”?</p>
                <p class="board-muted small mb-0">
                    Patients in it will be moved to the default board. This action cannot be undone.
                </p>
                <div class="board-form-error" id="boardDeleteError" hidden></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="board-btn board-btn-ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="board-btn board-btn-danger" id="boardDeleteConfirm">Delete permanently</button>
            </div>
        </div>
    </div>
</div>

<!-- Add patient to board -->
<div class="modal fade" id="addPatientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-1"></i> Add patient to board</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="board-search board-search--block">
                    <i class="bi bi-search"></i>
                    <input type="search" class="board-search-input" id="addPatientSearch"
                           placeholder="Type a patient name or phone number…" autocomplete="off"
                           aria-label="Search for a patient to add">
                </div>
                <div class="add-patient-results" id="addPatientResults">
                    <p class="board-muted small text-center py-3 mb-0">Start typing to search for patients (at least 2 characters).</p>
                </div>
                <div class="board-form-error" id="addPatientError" hidden></div>
            </div>
        </div>
    </div>
</div>

<!-- Patient quick edit -->
<div class="modal fade" id="patientEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="patientEditForm" autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-1"></i> Edit patient details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="patientEditId" value="">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label" for="patientEditFirst">First name <span class="req">*</span></label>
                            <input type="text" class="form-control" id="patientEditFirst" maxlength="50" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="patientEditLast">Last name <span class="req">*</span></label>
                            <input type="text" class="form-control" id="patientEditLast" maxlength="50" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="patientEditPhone">Phone <span class="req">*</span></label>
                            <input type="text" class="form-control" id="patientEditPhone" maxlength="20" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="patientEditAltPhone">Alternate phone</label>
                            <input type="text" class="form-control" id="patientEditAltPhone" maxlength="20">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="patientEditGender">Gender</label>
                            <select class="form-select" id="patientEditGender">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="patientEditDob">Date of birth</label>
                            <input type="date" class="form-control" id="patientEditDob">
                        </div>
                    </div>
                    <div class="board-form-error" id="patientEditError" hidden></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="board-btn board-btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="board-btn board-btn-primary" id="patientEditSave">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remove patient from board confirm -->
<div class="modal fade" id="patientRemoveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-person-dash me-1"></i> Remove from board
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Remove “<strong id="patientRemoveName">—</strong>” from the “<strong id="patientRemoveBoard">—</strong>” board?</p>
                <p class="board-muted small mb-0">The patient will not be deleted from the system, only removed from this board.</p>
                <div class="board-form-error" id="patientRemoveError" hidden></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="board-btn board-btn-ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="board-btn board-btn-danger" id="patientRemoveConfirm">Remove</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.BOARD_CONFIG = {
        csrfToken: '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>',
        currentUser: <?= json_encode([
            'id'   => $user['id'] ?? null,
            'role' => $user['role'] ?? null,
            'name' => $user['name'] ?? null,
        ]) ?>
    };
</script>
<script defer
    src="/app/Views/doctor/assets/js/comment-media.js?v=<?= file_exists(__DIR__ . '/assets/js/comment-media.js') ? filemtime(__DIR__ . '/assets/js/comment-media.js') : time() ?>"></script>
<script defer
    src="/app/Views/doctor/assets/js/board.js?v=<?= file_exists(__DIR__ . '/assets/js/board.js') ? filemtime(__DIR__ . '/assets/js/board.js') : time() ?>"></script>
