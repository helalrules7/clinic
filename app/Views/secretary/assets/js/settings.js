/**
 * Secretary settings — loads/saves via /api/secretary/settings
 */
(function () {
    'use strict';

    var API = '/api/secretary/settings';
    var prefs = {};

    function applyThemeUI(theme) {
        var isDark = theme === 'dark';
        document.documentElement.classList.toggle('dark', isDark);
        var logo = document.getElementById('clinicLogo');
        if (logo) {
            logo.src = isDark ? '/assets/images/Dark.png' : '/assets/images/Light.png';
        }
        var input = document.getElementById('themeToggleInput');
        if (input) input.checked = isDark;
        var secInput = document.getElementById('secCurrentModeInput');
        if (secInput) secInput.checked = isDark;
    }

    function setToggle(id, checked) {
        var el = document.getElementById(id);
        if (el) el.checked = !!checked;
    }

    async function loadPreferences() {
        try {
            var response = await fetch(API, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.status === 401 || response.status === 403) {
                window.location.href = '/login?expired=1';
                return;
            }

            var data = await response.json();
            if (!data.success || !data.settings) return;

            prefs = data.settings;

            setToggle('secCurrentModeInput', prefs.theme === 'dark');
            setToggle('secBackToTopDisplay', prefs.back_to_top_display !== false);

            var autoOn = prefs.theme_auto_schedule === true
                || prefs.theme_auto_schedule === '1'
                || prefs.theme_auto_schedule === 1;
            setToggle('secThemeAutoSchedule', autoOn);

            var times = document.getElementById('secThemeScheduleTimes');
            if (times) times.hidden = !autoOn;

            var darkFrom = document.getElementById('secThemeDarkFrom');
            var lightFrom = document.getElementById('secThemeLightFrom');
            if (darkFrom && prefs.theme_dark_from) darkFrom.value = String(prefs.theme_dark_from).substr(0, 5);
            if (lightFrom && prefs.theme_light_from) lightFrom.value = String(prefs.theme_light_from).substr(0, 5);

            if (prefs.theme_palette) {
                try { localStorage.setItem('appPalette', prefs.theme_palette); } catch (_) {}
                document.documentElement.setAttribute('data-palette', prefs.theme_palette);
            }
            if (prefs.theme) {
                try {
                    localStorage.setItem('appTheme', prefs.theme);
                    localStorage.setItem('theme', prefs.theme);
                } catch (_) {}
            }
            if (autoOn) {
                try {
                    localStorage.setItem('appThemeAutoSchedule', '1');
                    if (prefs.theme_dark_from) localStorage.setItem('appThemeDarkFrom', prefs.theme_dark_from);
                    if (prefs.theme_light_from) localStorage.setItem('appThemeLightFrom', prefs.theme_light_from);
                } catch (_) {}
            }
        } catch (err) {
            console.error('Error loading secretary settings:', err);
        }
    }

    async function putPreference(body) {
        try {
            var response = await fetch(API, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(body),
            });
            if (!response.ok) {
                console.error('Failed to save secretary settings');
            }
            return response.ok;
        } catch (err) {
            console.error('Error saving secretary settings:', err);
            return false;
        }
    }

    window.secUpdatePreference = async function (key, value) {
        prefs[key] = value;

        if (key === 'theme') {
            applyThemeUI(value);
            try {
                localStorage.setItem('appTheme', value);
                localStorage.setItem('theme', value);
            } catch (_) {}
        }

        if (key === 'back_to_top_display' && typeof window.secApplyBackToTopPreference === 'function') {
            window.secApplyBackToTopPreference(!!value);
        }

        await putPreference({ [key]: value });
    };

    window.secSaveAutoSchedule = async function () {
        var enabled = document.getElementById('secThemeAutoSchedule').checked;
        var darkFrom = (document.getElementById('secThemeDarkFrom') || {}).value || '19:00';
        var lightFrom = (document.getElementById('secThemeLightFrom') || {}).value || '07:00';
        var times = document.getElementById('secThemeScheduleTimes');
        if (times) times.hidden = !enabled;

        try {
            localStorage.setItem('appThemeAutoSchedule', enabled ? '1' : '0');
            localStorage.setItem('appThemeDarkFrom', darkFrom);
            localStorage.setItem('appThemeLightFrom', lightFrom);
        } catch (_) {}

        await fetch('/api/settings/theme-auto-schedule', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                enabled: enabled,
                dark_from: darkFrom,
                light_from: lightFrom,
            }),
        });

        if (typeof window.applyThemeSchedule === 'function') {
            window.applyThemeSchedule();
        }
    };

    document.addEventListener('DOMContentLoaded', loadPreferences);
})();
