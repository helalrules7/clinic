/**
 * Cross-clinic booking guard — show modal in-place, never navigate away.
 * Loaded on every secretary page (secretary_main.php).
 */
(function () {
    'use strict';

    function getModalEl() {
        return document.getElementById('bookingScopeDeniedModal');
    }

    window.showBookingScopeDeniedModal = function (bookingId) {
        var modalEl = getModalEl();
        if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            return;
        }

        var idWrap = document.getElementById('bookingScopeDeniedIdWrap');
        var idSpan = document.getElementById('bookingScopeDeniedId');
        if (idWrap && idSpan) {
            if (bookingId) {
                idSpan.textContent = '#' + bookingId;
                idWrap.classList.remove('d-none');
            } else {
                idWrap.classList.add('d-none');
            }
        }

        bootstrap.Modal.getOrCreateInstance(modalEl, {
            backdrop: 'static',
            keyboard: false
        }).show();
    };

    window.navigateToSecretaryBooking = function (appointmentId) {
        var id = parseInt(appointmentId, 10);
        if (!id) return;

        fetch('/secretary/bookings/' + id + '/details', {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(function (res) {
                return res.json().then(function (body) {
                    return { res: res, body: body };
                });
            })
            .then(function (payload) {
                if (payload.res.status === 403) {
                    window.showBookingScopeDeniedModal(id);
                    return;
                }
                if (!payload.res.ok || !payload.body.ok) {
                    return;
                }
                window.location.href = '/secretary/bookings/' + id;
            })
            .catch(function () {
                window.location.href = '/secretary/bookings/' + id;
            });
    };

    function dismissScopeModal() {
        var modalEl = getModalEl();
        if (!modalEl || typeof bootstrap === 'undefined') return;
        var inst = bootstrap.Modal.getInstance(modalEl);
        if (inst) inst.hide();
    }

    function initDismissHandlers() {
        document.getElementById('bookingScopeDeniedCloseBtn')
            ?.addEventListener('click', dismissScopeModal);
        document.getElementById('bookingScopeDeniedBackBtn')
            ?.addEventListener('click', dismissScopeModal);
    }

    function handleCrossClinicQuery() {
        var params = new URLSearchParams(window.location.search);
        if (params.get('crossClinic') !== '1') return;

        window.showBookingScopeDeniedModal(null);

        params.delete('crossClinic');
        var qs = params.toString();
        var clean = window.location.pathname + (qs ? '?' + qs : '');
        window.history.replaceState({}, '', clean);
    }

    function handleServerCrossClinicFlag() {
        if (!window.__SHOW_CROSS_CLINIC_MODAL) return;
        window.__SHOW_CROSS_CLINIC_MODAL = false;
        window.showBookingScopeDeniedModal(null);
    }

    function boot() {
        initDismissHandlers();
        handleCrossClinicQuery();
        handleServerCrossClinicFlag();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
