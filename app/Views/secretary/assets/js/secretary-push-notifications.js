/* ============================================================
   Secretary — Enable Push Notifications toast (Arabic / RTL).
   Self-contained mirror of the doctor flow in layouts/main.js, but:
     • talks to /api/secretary/settings (GET/PUT)
     • all copy in Arabic
     • WEEKLY reminder cadence (re-surfaces at most once / 7 days)
     • never reminds a user who is already subscribed (push_notifications_enabled)
   The doctor side carries the same weekly + skip-if-subscribed gates.
   See v12_perf/PUSH_NOTIFICATIONS_secretary_weekly.md
   ============================================================ */
(function () {
    'use strict';
    if (window.__secPushInited) return;
    window.__secPushInited = true;

    // Re-surface the prompt at most once every 7 days (the "weekly reminder").
    var REMIND_WINDOW_MS = 7 * 24 * 60 * 60 * 1000;
    var VAPID_PUBLIC_KEY = 'BM81HP8k4re4ObeiBgk2BSdC3FDx5Ke8-XbtPF_RbsEF5M6SC0OyHcygclxzQbPeiY8re_q6Hco16kLvol-4ozg';
    var API = '/api/secretary/settings';

    function isPushSupported() {
        return ('serviceWorker' in navigator) && ('PushManager' in window) && ('Notification' in window);
    }

    function getBrowserIdentifier() {
        return navigator.userAgent + '|' + window.location.origin;
    }

    function urlBase64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - base64String.length % 4) % 4);
        var base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
        var rawData = window.atob(base64);
        var outputArray = new Uint8Array(rawData.length);
        for (var i = 0; i < rawData.length; ++i) outputArray[i] = rawData.charCodeAt(i);
        return outputArray;
    }

    async function registerServiceWorker() {
        try { return await navigator.serviceWorker.register('/sw.js'); }
        catch (e) { return null; }
    }

    async function requestNotificationPermission() {
        if (!('Notification' in window)) return 'denied';
        if (Notification.permission === 'granted') return 'granted';
        return await Notification.requestPermission();
    }

    // ---- settings I/O (secretary_settings via /api/secretary/settings) ----
    async function loadPushSettings() {
        var fallback = { enabled: false, subscription: null, declinedBrowsers: [], isDeclined: false, shouldRemind: true };
        try {
            var res = await fetch(API, { method: 'GET', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return fallback;
            var data = await res.json();
            if (!data.success || !data.settings) return fallback;
            var s = data.settings;

            var enabled = s.push_notifications_enabled === true || s.push_notifications_enabled === '1' || s.push_notifications_enabled === 1;

            var subscription = null;
            if (s.push_subscription) {
                subscription = typeof s.push_subscription === 'string' ? JSON.parse(s.push_subscription) : s.push_subscription;
                if (!Array.isArray(subscription) && subscription && subscription.endpoint) subscription = [subscription];
            }

            var declinedBrowsers = [];
            if (s.dont_ask_push_notifications_browsers) {
                declinedBrowsers = typeof s.dont_ask_push_notifications_browsers === 'string'
                    ? JSON.parse(s.dont_ask_push_notifications_browsers) : s.dont_ask_push_notifications_browsers;
                if (!Array.isArray(declinedBrowsers)) declinedBrowsers = [];
            }

            var remindLater = s.push_notification_remind_later ? (parseInt(s.push_notification_remind_later, 10) || null) : null;
            var now = Date.now();
            var shouldRemind = !remindLater || (now - remindLater) >= REMIND_WINDOW_MS;

            return {
                enabled: enabled,
                subscription: subscription,
                declinedBrowsers: declinedBrowsers,
                isDeclined: declinedBrowsers.includes(getBrowserIdentifier()),
                shouldRemind: shouldRemind
            };
        } catch (e) { return fallback; }
    }

    async function putSettings(body) {
        try {
            var res = await fetch(API, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(body)
            });
            if (!res.ok) return false;
            var data = await res.json();
            return !!data.success;
        } catch (e) { return false; }
    }

    function findSubscriptionByEndpoint(arr, endpoint) {
        if (!Array.isArray(arr)) return null;
        for (var i = 0; i < arr.length; i++) {
            var sub = arr[i];
            var ep = typeof sub === 'string' ? JSON.parse(sub).endpoint : sub.endpoint;
            if (ep === endpoint) return typeof sub === 'string' ? JSON.parse(sub) : sub;
        }
        return null;
    }

    async function savePushSubscription(subscription) {
        var settings = await loadPushSettings();
        var arr = [];
        if (settings.subscription) arr = Array.isArray(settings.subscription) ? settings.subscription.slice() : [settings.subscription];
        var obj = typeof subscription === 'string' ? JSON.parse(subscription) : subscription;
        var idx = arr.findIndex(function (sub) {
            var ep = typeof sub === 'string' ? JSON.parse(sub).endpoint : sub.endpoint;
            return ep === obj.endpoint;
        });
        if (idx >= 0) arr[idx] = obj; else arr.push(obj);
        return await putSettings({ push_notifications_enabled: true, push_subscription: arr });
    }

    async function subscribeToPush(registration) {
        try {
            var subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY)
            });
            var ok = await savePushSubscription(subscription);
            return ok ? subscription : null;
        } catch (e) { return null; }
    }

    // Record that the prompt was shown → schedules the next one +7 days.
    async function recordPromptShown() {
        return await putSettings({ push_notification_remind_later: Date.now() });
    }

    async function saveDontAskForThisBrowser() {
        var settings = await loadPushSettings();
        var declined = settings.declinedBrowsers || [];
        var id = getBrowserIdentifier();
        if (!declined.includes(id)) declined.push(id);
        return await putSettings({ dont_ask_push_notifications_browsers: declined });
    }

    async function checkBrowserSubscription(registration) {
        try {
            var current = await registration.pushManager.getSubscription();
            if (!current) return { needsSetup: true, isValid: false };
            var settings = await loadPushSettings();
            var saved = settings.subscription;
            if (settings.enabled && saved && findSubscriptionByEndpoint(Array.isArray(saved) ? saved : [saved], current.endpoint)) {
                return { needsSetup: false, isValid: true };
            }
            return { needsSetup: true, isValid: false };
        } catch (e) { return { needsSetup: true, isValid: false }; }
    }

    // ---- feedback toast (bottom-center, auto-hide) ----
    function feedback(type, title, msg) {
        var c = document.getElementById('secPushFeedbackContainer');
        if (!c) {
            c = document.createElement('div');
            c.id = 'secPushFeedbackContainer';
            document.body.appendChild(c);
        }
        var icon = type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger';
        var el = document.createElement('div');
        el.className = 'toast align-items-center border-0';
        el.setAttribute('role', 'alert');
        el.setAttribute('dir', 'rtl');
        el.innerHTML = '<div class="d-flex"><div class="toast-body d-flex align-items-center gap-2">'
            + '<i class="bi ' + icon + '"></i><div><strong>' + title + '</strong><div class="small">' + msg + '</div></div></div>'
            + '<button type="button" class="btn-close ms-auto me-2 m-auto" data-bs-dismiss="toast" aria-label="إغلاق"></button></div>';
        c.appendChild(el);
        try {
            var t = new bootstrap.Toast(el, { autohide: true, delay: 5000 });
            t.show();
            el.addEventListener('hidden.bs.toast', function () { el.remove(); });
        } catch (e) { setTimeout(function () { el.remove(); }, 5000); }
    }

    function hideToast(toastElement) {
        toastElement.classList.add('hiding');
        var t = bootstrap.Toast.getInstance(toastElement);
        if (t) setTimeout(function () { t.hide(); }, 300);
        else setTimeout(function () { toastElement.remove(); }, 300);
    }

    async function showSecPushToast() {
        var settings = await loadPushSettings();
        if (settings.isDeclined) return;     // "don't ask on this browser" — hard opt-out
        if (settings.enabled) return;        // already subscribed — never remind
        if (!settings.shouldRemind) return;  // within the 7-day window

        var container = document.getElementById('pushToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'pushToastContainer';
            container.className = 'toast-container push-toast-container';
            document.body.appendChild(container);
        }
        var toastId = 'sec-push-notification-toast';
        if (document.getElementById(toastId)) return;

        var html = ''
            + '<div id="' + toastId + '" class="toast align-items-center push-notification-toast border-0" role="alert" aria-live="assertive" aria-atomic="true" dir="rtl" data-bs-autohide="false">'
            + '  <div class="toast-header push-toast-header">'
            + '    <div class="push-toast-title">'
            + '      <i class="bi bi-bell-fill"></i>'
            + '      <strong>تفعيل إشعارات المتصفح</strong>'
            + '    </div>'
            + '    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="إغلاق"></button>'
            + '  </div>'
            + '  <div class="toast-body">'
            + '    <div>فعّل الإشعارات لتصلك التنبيهات والمواعيد والملاحظات وغيرها حتى عندما يكون المتصفح مغلقًا.<br>يمكنك إيقافها لاحقًا من إعدادات المتصفح.</div>'
            + '  </div>'
            + '  <div class="toast-footer push-toast-footer">'
            + '    <div class="d-flex align-items-center gap-2 w-100 flex-wrap">'
            + '      <button type="button" class="btn btn-sm btn-light" id="secEnablePushBtn"><i class="bi bi-check-circle ms-1"></i>تفعيل</button>'
            + '      <button type="button" class="btn btn-sm btn-outline-light" id="secRemindLaterBtn" style="font-size:0.75rem;white-space:nowrap;"><i class="bi bi-clock ms-1"></i>ذكّرني لاحقًا</button>'
            + '      <button type="button" class="btn btn-sm btn-outline-light" id="secDontAskBtn" style="font-size:0.75rem;white-space:nowrap;"><i class="bi bi-x-circle ms-1"></i>لا تسأل في هذا المتصفح</button>'
            + '    </div>'
            + '  </div>'
            + '</div>';

        container.insertAdjacentHTML('beforeend', html);
        var toastEl = document.getElementById(toastId);
        if (!toastEl) return;

        var enableBtn = toastEl.querySelector('#secEnablePushBtn');
        enableBtn && enableBtn.addEventListener('click', async function () {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm ms-1"></span>جارٍ التفعيل…';
            var permission = await requestNotificationPermission();
            if (permission === 'granted') {
                var reg = await navigator.serviceWorker.ready;
                var sub = await subscribeToPush(reg);
                if (sub) {
                    hideToast(toastEl);
                    feedback('success', 'تم تفعيل الإشعارات', 'ستصلك الإشعارات الآن حتى عندما يكون المتصفح مغلقًا.');
                } else {
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-check-circle ms-1"></i>تفعيل';
                    feedback('error', 'تعذّر التفعيل', 'حدث خطأ أثناء تفعيل الإشعارات، يرجى المحاولة مرة أخرى.');
                }
            } else {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-check-circle ms-1"></i>تفعيل';
                feedback('error', 'تم رفض الإذن', 'يرجى السماح بالإشعارات من إعدادات المتصفح.');
            }
        });

        var remindBtn = toastEl.querySelector('#secRemindLaterBtn');
        remindBtn && remindBtn.addEventListener('click', async function () {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm ms-1"></span>جارٍ الحفظ…';
            await recordPromptShown();
            hideToast(toastEl);
        });

        var dontAskBtn = toastEl.querySelector('#secDontAskBtn');
        dontAskBtn && dontAskBtn.addEventListener('click', async function () {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm ms-1"></span>جارٍ الحفظ…';
            await saveDontAskForThisBrowser();
            hideToast(toastEl);
        });

        var closeBtn = toastEl.querySelector('.btn-close');
        closeBtn && closeBtn.addEventListener('click', function () { hideToast(toastEl); });

        toastEl.addEventListener('hidden.bs.toast', function () { toastEl.remove(); });

        var toast = new bootstrap.Toast(toastEl, { autohide: false, delay: 0 });
        toast.show();

        // Record on show → the weekly cadence holds even if the user just closes it.
        recordPromptShown();
    }

    async function initSecPush() {
        if (!isPushSupported()) return;
        var settings = await loadPushSettings();
        if (settings.isDeclined) return;     // hard opt-out for this browser
        if (settings.enabled) return;        // already subscribed → no reminder
        if (!settings.shouldRemind) return;  // shown within the last 7 days

        var reg = await registerServiceWorker();
        if (!reg) return;

        var check = await checkBrowserSubscription(reg);
        if (!check.isValid) {
            setTimeout(showSecPushToast, 3000);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSecPush);
    } else {
        initSecPush();
    }
})();
