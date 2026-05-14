// Shared clinics dropdown loader.
// Populates clinic <select> + matching custom-select <menu> items, then re-inits the custom-select UI.

(function () {
    if (window.ClinicsLoader) return;

    let clinicsCache = null;
    let inflight = null;

    // Visual identity per clinic (code -> {icon, color}).
    // Falls back to a neutral building for unknown codes.
    const VISUALS = {
        riyadh: { icon: 'bi-buildings-fill', color: '#0d6efd' },
        kfs:    { icon: 'bi-hospital-fill',  color: '#10b981' },
    };
    function getVisual(code) {
        return VISUALS[code] || { icon: 'bi-building', color: '#6c757d' };
    }

    async function fetchClinics() {
        if (clinicsCache) return clinicsCache;

        // Server-side bootstrap (injected by layouts/main.php and
        // layouts/secretary_main.php). When present we skip the network
        // round-trip entirely — the first modal open is fully synchronous.
        if (Array.isArray(window.CLINICS_BOOTSTRAP) && window.CLINICS_BOOTSTRAP.length) {
            clinicsCache = window.CLINICS_BOOTSTRAP;
            return clinicsCache;
        }

        if (inflight) return inflight;

        inflight = fetch('/api/clinics', { credentials: 'same-origin' })
            .then(r => r.json())
            .then(json => {
                if (!json || !json.ok || !Array.isArray(json.data)) {
                    throw new Error(json.error || 'Failed to load clinics');
                }
                clinicsCache = json.data;
                return clinicsCache;
            })
            .finally(() => { inflight = null; });

        return inflight;
    }

    function getName(clinic, lang) {
        return lang === 'ar' ? (clinic.name_ar || clinic.name_en) : (clinic.name_en || clinic.name_ar);
    }

    /**
     * Populate a clinic dropdown synchronously when the server-side bootstrap
     * is present (default path now that layouts/main.php + secretary_main.php
     * inline the list). Falls back to async fetch only when the bootstrap is
     * unavailable — that keeps the helper resilient on legacy pages without
     * adding a microtask delay on the common path.
     */
    function populate(selectId, options) {
        const opts = Object.assign({
            lang: 'en',
            placeholder: null,           // text for the empty option
            preselectId: null,           // numeric id to preselect
            preserveCurrent: true        // keep current value if it matches a clinic id
        }, options || {});

        const select = document.getElementById(selectId);
        if (!select) return;

        // Reset any prior "locked single clinic" state from a previous open
        select.disabled = false;
        select.removeAttribute('readonly');
        const fieldEl = select.closest('.field.menu');
        if (fieldEl) fieldEl.classList.remove('locked');

        // Fast path: bootstrap available → render synchronously.
        const cached = clinicsCache
            || (Array.isArray(window.CLINICS_BOOTSTRAP) && window.CLINICS_BOOTSTRAP.length ? window.CLINICS_BOOTSTRAP : null);
        if (cached) {
            clinicsCache = cached;
            renderInto(select, cached, opts);
            return;
        }

        // Slow path: no bootstrap — fall back to async fetch.
        fetchClinics()
            .then(clinics => renderInto(select, clinics, opts))
            .catch(err => console.error('ClinicsLoader:', err));
    }

    function renderInto(select, clinics, opts) {
        const placeholder = opts.placeholder || (opts.lang === 'ar' ? 'اختر العيادة...' : 'Select clinic...');
        const previousValue = opts.preserveCurrent ? select.value : '';

        const field = select.closest('.field.menu');
        const menu = field ? field.querySelector('menu') : null;
        const toggleH3 = field ? field.querySelector('.custom-select-toggle h3') : null;

        // Rebuild <option> list
        select.innerHTML = '';
        const emptyOpt = document.createElement('option');
        emptyOpt.value = '';
        emptyOpt.textContent = placeholder;
        select.appendChild(emptyOpt);

        clinics.forEach(c => {
            const o = document.createElement('option');
            o.value = c.id;
            o.textContent = getName(c, opts.lang);
            select.appendChild(o);
        });

        // Rebuild <menu><li> list (for the custom UI)
        if (menu) {
            menu.innerHTML = '';
            const placeholderLi = document.createElement('li');
            placeholderLi.dataset.option = '';
            placeholderLi.tabIndex = 0;
            placeholderLi.setAttribute('role', 'button');
            placeholderLi.classList.add('selected');
            placeholderLi.innerHTML = `<h3>${placeholder}</h3>`;
            menu.appendChild(placeholderLi);

            clinics.forEach(c => {
                const li = document.createElement('li');
                li.dataset.option = String(c.id);
                li.tabIndex = 0;
                li.setAttribute('role', 'button');
                const v = getVisual(c.code);
                li.innerHTML = `<i class="bi ${v.icon} fs-5" style="color:${v.color}"></i> <h3>${getName(c, opts.lang)}</h3>`;
                menu.appendChild(li);
            });
        }

        // Reset and reinitialize the custom-select wiring
        if (field) {
            field.removeAttribute('data-initialized');
            field.classList.remove('open');
        }

        // When only one clinic is visible (e.g. a clinic-scoped secretary),
        // auto-select it and lock the dropdown so it can't be changed.
        const singleClinicLock = clinics.length === 1;

        const chosen = singleClinicLock
            ? String(clinics[0].id)
            : ((opts.preselectId && clinics.some(c => String(c.id) === String(opts.preselectId)))
                ? String(opts.preselectId)
                : (previousValue && clinics.some(c => String(c.id) === String(previousValue)) ? previousValue : ''));

        select.value = chosen;

        if (toggleH3) {
            const sel = clinics.find(c => String(c.id) === chosen);
            toggleH3.textContent = sel ? getName(sel, opts.lang) : placeholder;
            const toggleIcon = field ? field.querySelector('.custom-select-toggle i.bi') : null;
            if (toggleIcon) {
                const v = sel ? getVisual(sel.code) : { icon: 'bi-building', color: '' };
                toggleIcon.className = `bi ${v.icon} fs-5`;
                toggleIcon.style.color = v.color || '';
            }
        }

        // Mark the matching <li> as selected
        if (menu) {
            menu.querySelectorAll('li').forEach(li => li.classList.remove('selected'));
            const matchLi = menu.querySelector(`li[data-option="${chosen}"]`) || menu.querySelector('li[data-option=""]');
            if (matchLi) matchLi.classList.add('selected');
        }

        if (typeof window.initCustomSelects === 'function') {
            window.initCustomSelects();
        }

        // Apply the lock AFTER initCustomSelects so the custom UI is already wired.
        if (singleClinicLock) {
            select.disabled = true;
            if (field) {
                field.classList.add('locked');
                const toggleBtn = field.querySelector('.custom-select-toggle');
                if (toggleBtn) {
                    toggleBtn.disabled = true;
                    toggleBtn.setAttribute('aria-disabled', 'true');
                    toggleBtn.style.pointerEvents = 'none';
                    toggleBtn.style.opacity = '0.85';
                    toggleBtn.style.cursor = 'not-allowed';
                }
            }
        }
    }

    window.ClinicsLoader = { fetchClinics, populate, getVisual };

    // Pre-warm the cache so the very first time a modal opens, the dropdown
    // is populated synchronously from cache instead of after the fetch returns
    // (which previously made the calendar / patient modals need to be opened
    // twice for the clinic list to appear).
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => { fetchClinics().catch(() => {}); });
    } else {
        fetchClinics().catch(() => {});
    }
})();
