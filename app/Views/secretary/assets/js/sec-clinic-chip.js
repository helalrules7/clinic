// Secretary — clinic identity chip sync (booking + add-patient modals)
(function (global) {
    'use strict';

    var CLINIC_VISUALS = {
        riyadh: { icon: 'bi-buildings-fill', color: '#0d6efd' },
        kfs: { icon: 'bi-hospital-fill', color: '#10b981' }
    };

    function bindClinicIconSync(selectId, iconId) {
        var sel = document.getElementById(selectId);
        var iconEl = document.getElementById(iconId);
        if (!sel || !iconEl) return;

        var sync = function () {
            var opt = sel.selectedOptions[0];
            var code = (opt && opt.dataset && opt.dataset.clinicCode) ? opt.dataset.clinicCode : '';
            var visual = (global.ClinicsLoader && typeof global.ClinicsLoader.getVisual === 'function')
                ? global.ClinicsLoader.getVisual(code)
                : (CLINIC_VISUALS[code] || { icon: 'bi-building', color: '#6c757d' });

            var group = iconEl.closest('.clinic-input-group');
            if (group) {
                group.style.setProperty('--clinic-color', visual.color);
            }
            var iconI = iconEl.querySelector('i');
            if (iconI) {
                iconI.className = 'bi ' + visual.icon;
            }
        };

        sel.addEventListener('change', sync);
        sync();
    }

    global.bindClinicIconSync = bindClinicIconSync;
})(typeof window !== 'undefined' ? window : this);
