/* global window, document, bootstrap, CALENDAR_CONFIG, showNotification, loadCalendar */
/*
 * Smart action — "Book nearest slot by phone".
 * ---------------------------------------------------------------------------
 * Lives on the calendar page (owns doctorId via CALENDAR_CONFIG, the clinic
 * <select>, and the booking APIs). The command palette detects a phone number
 * and hands off here via ActionRegistry → window.openBookByPhone(phone).
 *
 * Flow: search the patient by phone (reuse if found, else collect the minimum
 * new-patient fields) → find the nearest free slot from /api/calendar → show an
 * ALWAYS-ON confirm step (date/time/clinic editable) → on confirm, create the
 * patient if new (POST /api/patients) then the appointment (POST /api/appointments,
 * visit_type=New, source=Phone). Nothing is written until the user confirms.
 */
(function () {
    'use strict';

    const MAX_DAYS = 45;           // how far ahead we hunt for a free slot
    let modalEl = null;
    let bsModal = null;
    let state = null;              // { phone, patient, clinics, date, slots, nearest }

    // ----- helpers ----------------------------------------------------------
    function doctorId() {
        return (window.CALENDAR_CONFIG && window.CALENDAR_CONFIG.doctorId) || null;
    }
    function serverToday() {
        return (window.CALENDAR_CONFIG && window.CALENDAR_CONFIG.serverDate) ||
            new Date().toISOString().slice(0, 10);
    }
    function serverNowMinutes() {
        // Minutes-since-midnight of the server "now" (used to skip past slots today).
        const dt = window.CALENDAR_CONFIG && window.CALENDAR_CONFIG.serverDateTime;
        if (dt) {
            const m = /(\d{1,2}):(\d{2})/.exec(dt.slice(11));
            if (m) return (+m[1]) * 60 + (+m[2]);
        }
        const d = new Date();
        return d.getHours() * 60 + d.getMinutes();
    }
    function toMin(hhmm) {
        const m = /^(\d{1,2}):(\d{2})/.exec(String(hhmm || ''));
        return m ? (+m[1]) * 60 + (+m[2]) : 9999;
    }
    function addDays(ymd, n) {
        const [y, m, d] = ymd.split('-').map(Number);
        const dt = new Date(Date.UTC(y, m - 1, d));
        dt.setUTCDate(dt.getUTCDate() + n);
        return dt.toISOString().slice(0, 10);
    }
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
        });
    }
    function fmtTime(hhmm) {
        const mins = toMin(hhmm);
        if (mins >= 9999) return hhmm;
        let h = Math.floor(mins / 60), mm = mins % 60;
        const ap = h >= 12 ? 'PM' : 'AM';
        h = h % 12; if (h === 0) h = 12;
        return h + ':' + String(mm).padStart(2, '0') + ' ' + ap;
    }
    function fmtDate(ymd) {
        const [y, m, d] = ymd.split('-').map(Number);
        const dt = new Date(Date.UTC(y, m - 1, d));
        return dt.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
    }
    function api(url, opts) {
        return fetch(url, Object.assign({ headers: { 'X-Requested-With': 'XMLHttpRequest' } }, opts || {}))
            .then(function (r) { return r.json().catch(function () { return {}; }); });
    }

    // ----- data ops ---------------------------------------------------------
    function searchPatientByPhone(phone) {
        return api('/api/patients/search?q=' + encodeURIComponent(phone))
            .then(function (res) {
                const list = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
                return list && list.length ? list[0] : null;
            })
            .catch(function () { return null; });
    }
    function slotsForDate(date) {
        return api('/api/calendar?doctor_id=' + encodeURIComponent(doctorId()) + '&date=' + encodeURIComponent(date))
            .then(function (res) {
                const d = (res && res.data) ? res.data : res;
                const slots = (d && d.available_slots) ? d.available_slots.slice() : [];
                slots.sort(function (a, b) { return toMin(a) - toMin(b); });
                return slots;
            })
            .catch(function () { return []; });
    }
    // Walk forward from today to find the first date+time that's free.
    function findNearestSlot() {
        const today = serverToday();
        const nowMin = serverNowMinutes();
        let i = 0;
        function step() {
            if (i >= MAX_DAYS) return Promise.resolve(null);
            const date = addDays(today, i);
            return slotsForDate(date).then(function (slots) {
                const usable = (i === 0) ? slots.filter(function (s) { return toMin(s) > nowMin; }) : slots;
                if (usable.length) return { date: date, time: usable[0], slots: slots };
                i++;
                return step();
            });
        }
        return step();
    }
    function cloneClinicOptions() {
        const src = document.getElementById('appointmentClinic');
        const out = [];
        if (src) {
            Array.prototype.forEach.call(src.options, function (o) {
                if (o.value) out.push({ value: o.value, label: o.textContent });
            });
        }
        return out;
    }

    // ----- UI ---------------------------------------------------------------
    function ensureModal() {
        if (modalEl) return;
        modalEl = document.createElement('div');
        modalEl.className = 'modal fade';
        modalEl.id = 'bookByPhoneModal';
        modalEl.tabIndex = -1;
        modalEl.setAttribute('aria-hidden', 'true');
        modalEl.innerHTML =
            '<div class="modal-dialog modal-dialog-centered">' +
              '<div class="modal-content">' +
                '<div class="modal-header">' +
                  '<h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Book nearest slot</h5>' +
                  '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                '</div>' +
                '<div class="modal-body" id="bbpBody"></div>' +
                '<div class="modal-footer">' +
                  '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>' +
                  '<button type="button" class="btn btn-success" id="bbpConfirm" disabled>Confirm booking</button>' +
                '</div>' +
              '</div>' +
            '</div>';
        document.body.appendChild(modalEl);
        if (window.bootstrap && bootstrap.Modal) bsModal = new bootstrap.Modal(modalEl);
        modalEl.querySelector('#bbpConfirm').addEventListener('click', confirmBooking);
        modalEl.addEventListener('change', function (e) {
            if (e.target && e.target.id === 'bbpDate') onDateChange(e.target.value);
        });
    }
    function body() { return document.getElementById('bbpBody'); }
    function confirmBtn() { return document.getElementById('bbpConfirm'); }
    function setBody(html) { const b = body(); if (b) b.innerHTML = html; }

    function renderLoading() {
        setBody('<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div>' +
            '<p class="mt-3 mb-0 text-muted">Finding the soonest free slot…</p></div>');
        confirmBtn().disabled = true;
    }
    function renderNoSlot() {
        setBody('<div class="alert alert-warning mb-0">No free slot found in the next ' + MAX_DAYS +
            ' days. Try the full calendar instead.</div>');
        confirmBtn().disabled = true;
    }
    function patientBlock() {
        if (state.patient) {
            const name = esc(state.patient.full_name ||
                ((state.patient.first_name || '') + ' ' + (state.patient.last_name || '')).trim());
            return '<div class="alert alert-info py-2 mb-3">' +
                '<i class="bi bi-person-check me-1"></i><strong>' + name + '</strong> ' +
                '<span class="text-muted">· existing patient · ' + esc(state.phone) + '</span></div>';
        }
        // New patient — collect the minimum required fields.
        return '<div class="mb-3">' +
            '<div class="small text-muted mb-2"><i class="bi bi-person-plus me-1"></i>New patient · ' + esc(state.phone) + '</div>' +
            '<div class="row g-2">' +
              '<div class="col"><input type="text" class="form-control" id="bbpFirst" placeholder="First name" maxlength="50"></div>' +
              '<div class="col"><input type="text" class="form-control" id="bbpLast" placeholder="Last name" maxlength="50"></div>' +
            '</div>' +
            '<div class="mt-2 d-flex gap-3">' +
              '<label class="form-check-label"><input type="radio" name="bbpGender" value="Male" class="form-check-input me-1">Male</label>' +
              '<label class="form-check-label"><input type="radio" name="bbpGender" value="Female" class="form-check-input me-1">Female</label>' +
            '</div></div>';
    }
    function clinicBlock() {
        const opts = state.clinics.map(function (c) {
            return '<option value="' + esc(c.value) + '">' + esc(c.label) + '</option>';
        }).join('');
        return '<div class="mb-3"><label class="form-label small text-muted">Clinic</label>' +
            '<select class="form-select" id="bbpClinic">' + opts + '</select></div>';
    }
    function slotBlock() {
        const timeOpts = state.slots.map(function (s) {
            return '<option value="' + esc(s) + '"' + (s === state.time ? ' selected' : '') + '>' + esc(fmtTime(s)) + '</option>';
        }).join('');
        return '<div class="row g-2">' +
            '<div class="col-6"><label class="form-label small text-muted">Date</label>' +
              '<input type="date" class="form-control" id="bbpDate" min="' + esc(serverToday()) + '" value="' + esc(state.date) + '"></div>' +
            '<div class="col-6"><label class="form-label small text-muted">Time</label>' +
              '<select class="form-select" id="bbpTime">' + timeOpts + '</select></div>' +
            '</div>' +
            '<p class="small text-success mt-2 mb-0"><i class="bi bi-stars me-1"></i>Nearest free slot preselected — ' +
              esc(fmtDate(state.date)) + ' at ' + esc(fmtTime(state.time)) + '</p>';
    }
    function renderReady() {
        if (!state.clinics.length) {
            setBody('<div class="alert alert-danger mb-0">No active clinic available to book into.</div>');
            confirmBtn().disabled = true;
            return;
        }
        setBody(patientBlock() + clinicBlock() + slotBlock());
        confirmBtn().disabled = false;
    }
    function onDateChange(date) {
        if (!date) return;
        const timeSel = document.getElementById('bbpTime');
        if (timeSel) timeSel.innerHTML = '<option>Loading…</option>';
        slotsForDate(date).then(function (slots) {
            const nowMin = serverNowMinutes();
            const usable = (date === serverToday()) ? slots.filter(function (s) { return toMin(s) > nowMin; }) : slots;
            state.date = date;
            state.slots = usable;
            state.time = usable.length ? usable[0] : null;
            if (!usable.length) {
                if (timeSel) timeSel.innerHTML = '<option value="">No free slots</option>';
                confirmBtn().disabled = true;
            } else {
                if (timeSel) {
                    timeSel.innerHTML = usable.map(function (s) {
                        return '<option value="' + esc(s) + '">' + esc(fmtTime(s)) + '</option>';
                    }).join('');
                }
                confirmBtn().disabled = false;
            }
        });
    }

    // ----- confirm ----------------------------------------------------------
    function confirmBooking() {
        const btn = confirmBtn();
        const clinicId = (document.getElementById('bbpClinic') || {}).value;
        const date = (document.getElementById('bbpDate') || {}).value || state.date;
        const time = (document.getElementById('bbpTime') || {}).value || state.time;
        if (!clinicId) { showNotice('Please choose a clinic.', 'warning'); return; }
        if (!date || !time) { showNotice('Please choose a date and time.', 'warning'); return; }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Booking…';

        ensurePatient(clinicId).then(function (patientId) {
            if (!patientId) throw new Error('Could not resolve the patient.');
            return api('/api/appointments', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({
                    patient_id: patientId,
                    doctor_id: doctorId(),
                    clinic_id: clinicId,
                    date: date,
                    start_time: time,
                    visit_type: 'New',
                    source: 'Phone'
                })
            });
        }).then(function (res) {
            if (res && res.ok) {
                if (bsModal) bsModal.hide();
                if (typeof showNotification === 'function') {
                    showNotification('Booked ' + fmtDate(date) + ' at ' + fmtTime(time) + '.', 'success');
                }
                if (typeof loadCalendar === 'function') loadCalendar();
            } else {
                throw new Error((res && (res.message || res.error)) || 'Booking failed.');
            }
        }).catch(function (err) {
            showNotice(err.message || 'Booking failed.', 'danger');
            btn.disabled = false;
            btn.textContent = 'Confirm booking';
        });
    }
    // Returns a patient id — the existing one, or a freshly created record.
    function ensurePatient(clinicId) {
        if (state.patient && state.patient.id) return Promise.resolve(state.patient.id);
        const first = (document.getElementById('bbpFirst') || {}).value || '';
        const last = (document.getElementById('bbpLast') || {}).value || '';
        const genderEl = document.querySelector('input[name="bbpGender"]:checked');
        const gender = genderEl ? genderEl.value : '';
        if (!first.trim() || !last.trim()) { showNotice('Enter the new patient’s first and last name.', 'warning'); return Promise.resolve(null); }
        if (!gender) { showNotice('Choose the patient’s gender.', 'warning'); return Promise.resolve(null); }

        const fd = new FormData();
        fd.append('first_name', first.trim());
        fd.append('last_name', last.trim());
        fd.append('phone', state.phone);
        fd.append('gender', gender);
        fd.append('clinic_id', clinicId);
        return api('/api/patients', { method: 'POST', body: fd }).then(function (res) {
            if (res && res.ok && res.data && res.data.id) return res.data.id;
            throw new Error((res && (res.error || res.message)) || 'Could not create the patient.');
        });
    }
    function showNotice(msg, type) {
        const b = body();
        if (!b) return;
        let note = b.querySelector('.bbp-note');
        if (!note) {
            note = document.createElement('div');
            note.className = 'bbp-note mt-3';
            b.appendChild(note);
        }
        note.innerHTML = '<div class="alert alert-' + (type || 'info') + ' py-2 mb-0">' + esc(msg) + '</div>';
    }

    // ----- entry ------------------------------------------------------------
    window.openBookByPhone = function openBookByPhone(phone) {
        phone = String(phone || '').trim();
        if (!phone) { return; }
        if (!doctorId()) {
            if (typeof showNotification === 'function') showNotification('Open the calendar to book by phone.', 'warning');
            return;
        }
        ensureModal();
        state = { phone: phone, patient: null, clinics: cloneClinicOptions(), date: null, slots: [], time: null };
        if (bsModal) bsModal.show();
        renderLoading();

        Promise.all([searchPatientByPhone(phone), findNearestSlot()]).then(function (out) {
            state.patient = out[0];
            const nearest = out[1];
            if (!nearest) { renderNoSlot(); return; }
            state.date = nearest.date;
            state.slots = nearest.slots.filter(function (s) {
                return nearest.date === serverToday() ? toMin(s) > serverNowMinutes() : true;
            });
            if (!state.slots.length) state.slots = nearest.slots;
            state.time = nearest.time;
            renderReady();
        }).catch(function () {
            setBody('<div class="alert alert-danger mb-0">Something went wrong preparing the booking.</div>');
        });
    };
})();
