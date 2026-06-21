<?php
/**
 * Admin → Clinic Management.
 * JS-driven page over the existing /api/clinics JSON API. No CRUD SQL here.
 * Ophthalmology (single-practice) is EDIT-ONLY: no create / delete.
 *
 * Vars: $csrf_token, $specialty, $fullManage (bool)
 */
?>

<style>
:root {
    --sidebar-width: 280px;
}

.card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
    animation: fadeUp 0.35s ease both;
    color: var(--text);
}

.card-header {
    background: var(--bg);
    border-bottom: 1px solid var(--border);
    border-radius: 12px 12px 0 0;
    padding: 1rem 1.5rem;
    color: var(--text);
}

.form-control, .form-select {
    background: var(--card);
    border: 2px solid var(--border);
    color: var(--text);
    font-weight: 500;
    border-radius: 8px;
}

.form-control:focus, .form-select:focus {
    background: var(--card);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25);
    font-weight: 600;
}

.form-control::placeholder { color: var(--muted); font-weight: 400; }
.form-label { color: var(--text); font-weight: 600; margin-bottom: 0.5rem; }
.form-text  { color: var(--muted); }
.form-check-input:checked { background-color: var(--accent); border-color: var(--accent); }
.form-check-label { color: var(--text); }

.btn { border-radius: 8px; font-weight: 500; transition: all 0.2s ease; }
.btn:hover { transform: translateY(-1px); }
.btn-primary { background-color: var(--accent); border-color: var(--accent); }
.btn-primary:hover { background-color: var(--accent); border-color: var(--accent); opacity: 0.9; }
.btn-outline-secondary { color: var(--text); border-color: var(--border); }
.btn-outline-secondary:hover { background-color: var(--bg); border-color: var(--accent); color: var(--accent); }
.text-muted { color: var(--muted) !important; }

.clinic-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.25rem;
    height: 100%;
    transition: all 0.2s ease;
    color: var(--text);
}
.clinic-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}
.clinic-card .clinic-name { font-weight: 700; font-size: 1.05rem; margin: 0; }
.clinic-card .clinic-name-ar { color: var(--muted); font-size: 0.95rem; }
.clinic-card .clinic-meta { font-size: 0.875rem; color: var(--muted); }
.code-badge {
    display: inline-block;
    font-family: monospace;
    font-size: 0.75rem;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 0.1rem 0.45rem;
    color: var(--text);
}
.color-dot {
    display: inline-block;
    width: 12px; height: 12px;
    border-radius: 50%;
    border: 1px solid var(--border);
    vertical-align: middle;
}
.modal-content { background: var(--card); color: var(--text); border: 1px solid var(--border); }
.modal-header, .modal-footer { border-color: var(--border); }
</style>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="mb-0">
                <i class="fas fa-hospital me-2"></i>
                Clinic Branches
            </h5>
            <div>
                <?php if (!empty($fullManage)): ?>
                    <button type="button" class="btn btn-primary btn-sm" id="newClinicBtn">
                        <i class="fas fa-plus me-1"></i> New clinic
                    </button>
                <?php else: ?>
                    <span class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        Clinic creation/removal is managed by your provider.
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="clinicsAlert"></div>

    <!-- Loading -->
    <div id="clinicsLoading" class="text-center text-muted py-5">
        <div class="spinner-border" role="status"></div>
        <div class="mt-2">Loading clinics…</div>
    </div>

    <!-- Empty -->
    <div id="clinicsEmpty" class="text-center text-muted py-5" style="display:none;">
        <i class="fas fa-hospital fa-2x mb-2 d-block"></i>
        <div>No clinic branches found.</div>
    </div>

    <!-- Error -->
    <div id="clinicsError" class="alert alert-danger" style="display:none;" role="alert"></div>

    <!-- Grid -->
    <div id="clinicsGrid" class="row g-3" style="display:none;"></div>
</div>

<!-- Edit modal -->
<div class="modal fade" id="clinicEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Edit clinic</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="editModalError" class="alert alert-danger" style="display:none;"></div>
        <input type="hidden" id="edit_id">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="edit_name_en">Name (EN)</label>
            <input type="text" class="form-control" id="edit_name_en">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="edit_name_ar">Name (AR)</label>
            <input type="text" class="form-control" id="edit_name_ar" dir="rtl">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="edit_address_en">Address (EN)</label>
            <input type="text" class="form-control" id="edit_address_en">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="edit_address_ar">Address (AR)</label>
            <input type="text" class="form-control" id="edit_address_ar" dir="rtl">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="edit_phone">Phone</label>
            <input type="text" class="form-control" id="edit_phone">
          </div>
          <div id="editFullManageFields" class="col-md-6" style="display:none;">
            <label class="form-label" for="edit_sort_order">Sort order</label>
            <input type="number" class="form-control" id="edit_sort_order">
          </div>
          <div id="editIconColorFields" class="col-12 row g-3 m-0 p-0" style="display:none;">
            <div class="col-md-6">
              <label class="form-label" for="edit_icon">Icon</label>
              <input type="text" class="form-control" id="edit_icon" placeholder="fa-hospital">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="edit_color">Color</label>
              <input type="color" class="form-control form-control-color" id="edit_color" value="#38bdf8">
            </div>
          </div>
          <div class="col-12">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="edit_is_active">
              <label class="form-check-label" for="edit_is_active">Active</label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveClinicBtn">
          <i class="fas fa-save me-1"></i> Save
        </button>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($fullManage)): ?>
<!-- Create modal (ortho-style full manage only) -->
<div class="modal fade" id="clinicCreateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-plus me-2"></i>New clinic</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="createModalError" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="new_code">Code (slug)</label>
            <input type="text" class="form-control" id="new_code" placeholder="main_branch">
            <div class="form-text">Lowercase letters, numbers, <code>-</code> and <code>_</code> only.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="new_sort_order">Sort order</label>
            <input type="number" class="form-control" id="new_sort_order" value="0">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="new_name_en">Name (EN)</label>
            <input type="text" class="form-control" id="new_name_en">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="new_name_ar">Name (AR)</label>
            <input type="text" class="form-control" id="new_name_ar" dir="rtl">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="new_phone">Phone</label>
            <input type="text" class="form-control" id="new_phone">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="new_address">Address</label>
            <input type="text" class="form-control" id="new_address">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="new_icon">Icon</label>
            <input type="text" class="form-control" id="new_icon" placeholder="fa-hospital">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="new_color">Color</label>
            <input type="color" class="form-control form-control-color" id="new_color" value="#38bdf8">
          </div>
          <div class="col-12">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="new_is_active" checked>
              <label class="form-check-label" for="new_is_active">Active</label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="createClinicBtn">
          <i class="fas fa-plus me-1"></i> Create
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
window.__FULL_MANAGE__ = <?= !empty($fullManage) ? 'true' : 'false' ?>;
window.__CSRF_TOKEN__  = <?= json_encode($csrf_token) ?>;
// Run after the page's scripts (Bootstrap bundle loads at the bottom of layouts/main.php),
// so `new bootstrap.Modal(...)` below isn't evaluated before bootstrap exists.
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    var FULL = window.__FULL_MANAGE__;
    // roaya exposes the list at /api/clinics/all; ortho uses /api/clinics?all=1.
    var LIST_URL = FULL ? '/api/clinics?all=1' : '/api/clinics/all';

    var grid    = document.getElementById('clinicsGrid');
    var loading = document.getElementById('clinicsLoading');
    var empty   = document.getElementById('clinicsEmpty');
    var errBox  = document.getElementById('clinicsError');
    var alertBox = document.getElementById('clinicsAlert');
    var clinics = [];

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function flash(type, msg) {
        alertBox.innerHTML =
            '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            esc(msg) +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        setTimeout(function () { alertBox.innerHTML = ''; }, 5000);
    }

    function showOnly(el) {
        [loading, empty, errBox, grid].forEach(function (n) { n.style.display = 'none'; });
        if (el === grid) { el.style.display = 'flex'; }
        else { el.style.display = el === errBox ? 'block' : ''; }
    }

    function cardHtml(c) {
        var active = parseInt(c.is_active, 10) === 1;
        var badge = active
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';
        var dot = c.color
            ? '<span class="color-dot me-2" style="background:' + esc(c.color) + '"></span>'
            : '';
        var addr = c.address_en || c.address_ar || '';
        var html = '' +
            '<div class="col-12 col-md-6 col-xl-4">' +
              '<div class="clinic-card d-flex flex-column">' +
                '<div class="d-flex align-items-start justify-content-between mb-2">' +
                  '<div>' +
                    '<p class="clinic-name mb-0">' + dot + esc(c.name_en || c.name_ar || '—') + '</p>' +
                    (c.name_ar ? '<div class="clinic-name-ar" dir="rtl">' + esc(c.name_ar) + '</div>' : '') +
                  '</div>' +
                  badge +
                '</div>' +
                '<div class="clinic-meta mb-1">' +
                  (c.code ? '<span class="code-badge me-2">' + esc(c.code) + '</span>' : '') +
                  (c.phone ? '<span><i class="fas fa-phone me-1"></i>' + esc(c.phone) + '</span>' : '') +
                '</div>' +
                (addr ? '<div class="clinic-meta mb-2"><i class="fas fa-location-dot me-1"></i>' + esc(addr) + '</div>' : '') +
                '<div class="mt-auto pt-2 d-flex gap-2">' +
                  '<button class="btn btn-outline-secondary btn-sm" data-edit="' + esc(c.id) + '">' +
                    '<i class="fas fa-pen me-1"></i>Edit</button>' +
                  (FULL ? '<button class="btn btn-outline-danger btn-sm" data-del="' + esc(c.id) + '">' +
                    '<i class="fas fa-trash me-1"></i>Delete</button>' : '') +
                '</div>' +
              '</div>' +
            '</div>';
        return html;
    }

    function render() {
        if (!clinics.length) { showOnly(empty); return; }
        grid.innerHTML = clinics.map(cardHtml).join('');
        showOnly(grid);
    }

    function load() {
        showOnly(loading);
        fetch(LIST_URL, { credentials: 'same-origin' })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
            .then(function (res) {
                if (!res.ok || (res.j && res.j.error)) {
                    throw new Error((res.j && res.j.error) || 'Failed to load clinics');
                }
                clinics = (res.j && res.j.data) || [];
                render();
            })
            .catch(function (e) {
                errBox.textContent = e.message || 'Failed to load clinics';
                showOnly(errBox);
            });
    }

    function findClinic(id) {
        for (var i = 0; i < clinics.length; i++) {
            if (String(clinics[i].id) === String(id)) return clinics[i];
        }
        return null;
    }

    // ---- Edit ----
    var editModalEl = document.getElementById('clinicEditModal');
    var editModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;

    function openEdit(id) {
        var c = findClinic(id);
        if (!c) return;
        document.getElementById('editModalError').style.display = 'none';
        document.getElementById('edit_id').value = c.id;
        document.getElementById('edit_name_en').value = c.name_en || '';
        document.getElementById('edit_name_ar').value = c.name_ar || '';
        document.getElementById('edit_address_en').value = c.address_en || '';
        document.getElementById('edit_address_ar').value = c.address_ar || '';
        document.getElementById('edit_phone').value = c.phone || '';
        document.getElementById('edit_is_active').checked = parseInt(c.is_active, 10) === 1;
        if (FULL) {
            document.getElementById('editFullManageFields').style.display = '';
            document.getElementById('edit_sort_order').value = c.sort_order != null ? c.sort_order : 0;
            var ic = document.getElementById('editIconColorFields');
            ic.style.display = 'flex';
            document.getElementById('edit_icon').value = c.icon || '';
            if (c.color) document.getElementById('edit_color').value = c.color;
        }
        editModal.show();
    }

    function saveEdit() {
        var id = document.getElementById('edit_id').value;
        var body = {
            name_en: document.getElementById('edit_name_en').value.trim(),
            name_ar: document.getElementById('edit_name_ar').value.trim(),
            address_en: document.getElementById('edit_address_en').value.trim(),
            address_ar: document.getElementById('edit_address_ar').value.trim(),
            phone: document.getElementById('edit_phone').value.trim(),
            is_active: document.getElementById('edit_is_active').checked ? 1 : 0
        };
        if (FULL) {
            body.sort_order = parseInt(document.getElementById('edit_sort_order').value, 10) || 0;
            body.icon = document.getElementById('edit_icon').value.trim();
            body.color = document.getElementById('edit_color').value;
        }
        // roaya updates via POST /api/clinics/{id}; ortho via PUT.
        var method = FULL ? 'PUT' : 'POST';
        fetch('/api/clinics/' + encodeURIComponent(id), {
            method: method,
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
        .then(function (res) {
            if (res.j && res.j.ok) {
                editModal.hide();
                flash('success', 'Clinic updated.');
                load();
            } else {
                var em = document.getElementById('editModalError');
                em.textContent = (res.j && res.j.error) || 'Failed to save clinic.';
                em.style.display = 'block';
            }
        })
        .catch(function () {
            var em = document.getElementById('editModalError');
            em.textContent = 'Network error while saving.';
            em.style.display = 'block';
        });
    }

    // ---- Create / Delete (full manage only) ----
    var createModal = null;
    if (FULL) {
        var createModalEl = document.getElementById('clinicCreateModal');
        createModal = createModalEl ? new bootstrap.Modal(createModalEl) : null;
        var newBtn = document.getElementById('newClinicBtn');
        if (newBtn) newBtn.addEventListener('click', function () {
            document.getElementById('createModalError').style.display = 'none';
            createModal.show();
        });
        var createBtn = document.getElementById('createClinicBtn');
        if (createBtn) createBtn.addEventListener('click', createClinic);
    }

    function createClinic() {
        var em = document.getElementById('createModalError');
        var code = document.getElementById('new_code').value.trim();
        if (!/^[a-z0-9_-]+$/.test(code)) {
            em.textContent = 'Code must be a slug: lowercase letters, numbers, - and _ only.';
            em.style.display = 'block';
            return;
        }
        var body = {
            code: code,
            name_en: document.getElementById('new_name_en').value.trim(),
            name_ar: document.getElementById('new_name_ar').value.trim(),
            phone: document.getElementById('new_phone').value.trim(),
            address: document.getElementById('new_address').value.trim(),
            sort_order: parseInt(document.getElementById('new_sort_order').value, 10) || 0,
            icon: document.getElementById('new_icon').value.trim(),
            color: document.getElementById('new_color').value,
            is_active: document.getElementById('new_is_active').checked ? 1 : 0
        };
        fetch('/api/clinics', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
        .then(function (res) {
            if (res.j && res.j.ok) {
                createModal.hide();
                flash('success', 'Clinic created.');
                load();
            } else {
                em.textContent = (res.j && res.j.error) || 'Failed to create clinic.';
                em.style.display = 'block';
            }
        })
        .catch(function () {
            em.textContent = 'Network error while creating.';
            em.style.display = 'block';
        });
    }

    function deleteClinic(id) {
        var c = findClinic(id);
        var name = c ? (c.name_en || c.name_ar || ('#' + id)) : ('#' + id);
        if (!window.confirm('Delete clinic "' + name + '"? This cannot be undone.')) return;
        fetch('/api/clinics/' + encodeURIComponent(id), {
            method: 'DELETE',
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
        .then(function (res) {
            if (res.j && res.j.ok) {
                flash('success', 'Clinic deleted.');
                load();
            } else {
                flash('danger', (res.j && res.j.error) || 'Failed to delete clinic.');
            }
        })
        .catch(function () { flash('danger', 'Network error while deleting.'); });
    }

    // ---- Wiring ----
    grid.addEventListener('click', function (e) {
        var ed = e.target.closest('[data-edit]');
        if (ed) { openEdit(ed.getAttribute('data-edit')); return; }
        var dl = e.target.closest('[data-del]');
        if (dl) { deleteClinic(dl.getAttribute('data-del')); return; }
    });
    document.getElementById('saveClinicBtn').addEventListener('click', saveEdit);

    load();
});
</script>
