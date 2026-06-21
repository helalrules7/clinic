/*
 * Consultation timer — floating glassmorphism count-up widget for the doctor's
 * appointment page. Self-contained (injects its own styles), reads the seed globals
 * (APPT_ID / APPT_STATUS / APPT_TIMER_SECONDS / APPT_TIMER_STATUS) and the doctor
 * settings (enable / sound / alert minutes), and persists active time to
 * POST /api/appointments/{id}/timer.
 *
 * Pause/resume: the client owns active-time accounting. It pauses when the tab is
 * hidden / the window is blurred / the page is navigated away (beacon), and resumes
 * when the doctor is back on the screen. Stored seconds are monotonic server-side.
 */
(function () {
  'use strict';
  if (typeof window === 'undefined') return;
  var APPT_ID = window.APPT_ID;
  if (!APPT_ID) return;

  var STATUS = window.APPT_STATUS || '';
  var COMPLETED = /^(completed|closed|cancelled|canceled|no[ _-]?show)$/i.test(STATUS);

  var settings = { enabled: false, sound: false, alertMinutes: 0 };

  var elapsed = parseInt(window.APPT_TIMER_SECONDS, 10) || 0;
  var seededStatus = window.APPT_TIMER_STATUS || null; // running | paused | done | null
  var running = false;      // counting toward elapsed
  var manualPause = false;  // doctor tapped Pause
  var hiddenPause = false;  // tab hidden / window blurred
  var tickHandle = null;
  var saveHandle = null;
  var alarmFired = false;
  var lastSaved = elapsed;
  var els = {};

  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function fmt(s) {
    s = Math.max(0, Math.floor(s));
    var h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60), x = s % 60;
    return (h > 0 ? pad(h) + ':' : '') + pad(m) + ':' + pad(x);
  }

  /* ------------------------------------------------------------------ styles */
  function injectStyles() {
    if (document.getElementById('ct-styles')) return;
    var css = '' +
      /* Raised above the bottom-right control cluster (back-to-top / chat-fab / ai-chat / dock, all ~bottom 2rem). */
      '.ct-root{position:fixed;right:18px;bottom:150px;z-index:1045;font-family:inherit;}' +
      '@media(max-width:640px){.ct-root{right:12px;bottom:120px;}}' +
      '.ct-card{min-width:208px;padding:14px 16px;border-radius:18px;' +
        'background:var(--glass-bg-strong,rgba(255,255,255,.85));' +
        '-webkit-backdrop-filter:blur(22px) saturate(180%);backdrop-filter:blur(22px) saturate(180%);' +
        'border:1px solid var(--glass-border,rgba(226,232,240,.7));' +
        'box-shadow:var(--glass-shadow-lg,0 16px 48px rgba(15,23,42,.18));color:var(--ds-text,#0f172a);}' +
      '.ct-head{display:flex;align-items:center;gap:8px;margin-bottom:6px;}' +
      '.ct-dot{width:9px;height:9px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.18);}' +
      '.ct-dot.ct-paused{background:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,.18);animation:none;}' +
      '.ct-dot:not(.ct-paused){animation:ct-pulse 1.6s ease-in-out infinite;}' +
      '@keyframes ct-pulse{0%,100%{opacity:1;}50%{opacity:.35;}}' +
      '.ct-label{font-size:12px;font-weight:700;letter-spacing:.02em;color:var(--ds-text-muted,#64748b);text-transform:uppercase;flex:1;}' +
      '.ct-min{border:0;background:transparent;color:var(--ds-text-muted,#94a3b8);font-size:18px;line-height:1;cursor:pointer;padding:0 2px;}' +
      '.ct-time{font-size:30px;font-weight:800;font-variant-numeric:tabular-nums;letter-spacing:.01em;color:var(--ds-primary,#4F46E5);transition:color .25s;}' +
      '.ct-time.ct-warn{color:#d97706;}' +
      '.ct-time.ct-danger{color:#dc2626;animation:ct-flash 1s steps(2,start) infinite;}' +
      '@keyframes ct-flash{50%{opacity:.45;}}' +
      '.ct-actions{display:flex;gap:8px;margin-top:10px;}' +
      '.ct-btn{flex:1;border:0;border-radius:11px;padding:8px 10px;font-size:13px;font-weight:700;cursor:pointer;' +
        'display:inline-flex;align-items:center;justify-content:center;gap:5px;transition:filter .15s,background .15s;}' +
      '.ct-btn:hover{filter:brightness(.96);}' +
      '.ct-pause{background:var(--ds-primary-soft,rgba(79,70,229,.1));color:var(--ds-primary,#4F46E5);}' +
      '.ct-complete{background:#16a34a;color:#fff;}' +
      '.ct-complete.ct-confirm{background:#dc2626;}' +
      '.ct-prompt{font-size:13px;color:var(--ds-text,#334155);margin:2px 0 10px;line-height:1.4;}' +
      '.ct-pill{border:0;border-radius:999px;padding:10px 16px;font-size:13px;font-weight:700;cursor:pointer;' +
        'background:var(--ds-primary,#4F46E5);color:#fff;box-shadow:var(--glass-shadow-lg,0 12px 32px rgba(79,70,229,.35));' +
        'display:inline-flex;align-items:center;gap:7px;}' +
      '.ct-chip{border-radius:999px;padding:9px 15px;font-size:13px;font-weight:700;color:var(--ds-primary,#4F46E5);' +
        'background:var(--glass-bg-strong,rgba(255,255,255,.85));-webkit-backdrop-filter:blur(18px);backdrop-filter:blur(18px);' +
        'border:1px solid var(--glass-border,rgba(226,232,240,.7));box-shadow:var(--glass-shadow,0 4px 24px rgba(15,23,42,.08));' +
        'display:inline-flex;align-items:center;gap:7px;}' +
      'html.dark .ct-card,html.dark .ct-chip{color:#e2e8f0;}';
    var st = document.createElement('style');
    st.id = 'ct-styles';
    st.textContent = css;
    document.head.appendChild(st);
  }

  /* -------------------------------------------------------------- persistence */
  function save(status, useBeacon) {
    var body = JSON.stringify({ seconds: Math.floor(elapsed), status: status });
    var url = '/api/appointments/' + APPT_ID + '/timer';
    if (useBeacon && navigator.sendBeacon) {
      try { navigator.sendBeacon(url, new Blob([body], { type: 'application/json' })); } catch (e) {}
      return Promise.resolve();
    }
    lastSaved = elapsed;
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: body,
      keepalive: true,
    }).catch(function () {});
  }

  /* -------------------------------------------------------------------- alarm */
  var audioCtx = null;
  function beep() {
    try {
      var AC = window.AudioContext || window.webkitAudioContext;
      if (!AC) return;
      audioCtx = audioCtx || new AC();
      if (audioCtx.state === 'suspended') audioCtx.resume();
      var t0 = audioCtx.currentTime;
      [0, 0.45, 0.9].forEach(function (off) {
        var o = audioCtx.createOscillator(), g = audioCtx.createGain();
        o.type = 'sine'; o.frequency.value = 880;
        g.gain.setValueAtTime(0.0001, t0 + off);
        g.gain.exponentialRampToValueAtTime(0.4, t0 + off + 0.03);
        g.gain.exponentialRampToValueAtTime(0.0001, t0 + off + 0.34);
        o.connect(g); g.connect(audioCtx.destination);
        o.start(t0 + off); o.stop(t0 + off + 0.36);
      });
    } catch (e) {}
  }

  function applyAlertState() {
    if (!els.time) return;
    els.time.classList.remove('ct-warn', 'ct-danger');
    var alertSec = settings.alertMinutes * 60;
    if (alertSec <= 0) return;
    if (elapsed >= alertSec) {
      els.time.classList.add('ct-danger');
      if (!alarmFired) { alarmFired = true; if (settings.sound) beep(); }
    } else if (elapsed >= alertSec - 120) {
      els.time.classList.add('ct-warn');
    }
  }

  /* --------------------------------------------------------------------- tick */
  function tick() {
    if (!running || manualPause || hiddenPause) return;
    elapsed += 1;
    if (els.time) els.time.textContent = fmt(elapsed);
    applyAlertState();
  }
  function ensureLoops() {
    if (!tickHandle) tickHandle = setInterval(tick, 1000);
    if (!saveHandle) saveHandle = setInterval(function () {
      if (running && !manualPause && !hiddenPause && elapsed !== lastSaved) save('running', false);
    }, 15000);
  }

  /* ------------------------------------------------------------------ widgets */
  function clearRoot() { if (els.root) els.root.innerHTML = ''; }

  function renderReadOnlyChip() {
    if (elapsed <= 0) return;
    injectStyles();
    var root = document.createElement('div');
    root.className = 'ct-root';
    root.innerHTML = '<div class="ct-chip"><span>⏱</span><span>Consultation: ' + fmt(elapsed) + '</span></div>';
    document.body.appendChild(root);
  }

  function renderPill() {
    clearRoot();
    var pill = document.createElement('button');
    pill.className = 'ct-pill';
    if (running) {
      // Minimized while running → show the LIVE timer (tap to expand), not "Start timer".
      pill.style.display = 'inline-flex';
      pill.style.alignItems = 'center';
      pill.style.gap = '8px';
      pill.innerHTML = '<span class="ct-dot' + (manualPause ? ' ct-paused' : '') + '"></span>' +
        '<span class="ct-pill-time" style="font-size:15px">' + fmt(elapsed) + '</span>';
      els.time = pill.querySelector('.ct-pill-time');
      els.dot = pill.querySelector('.ct-dot');
      pill.addEventListener('click', renderRunning);
      applyAlertState();
    } else {
      pill.innerHTML = '<span>▶</span><span>Start timer</span>';
      pill.addEventListener('click', startFresh);
    }
    els.root.appendChild(pill);
  }

  function renderPrompt() {
    clearRoot();
    var card = document.createElement('div');
    card.className = 'ct-card';
    card.innerHTML =
      '<div class="ct-head"><span class="ct-dot ct-paused"></span><span class="ct-label">Consultation</span></div>' +
      '<div class="ct-prompt">Start a timer for this consultation?</div>' +
      '<div class="ct-actions">' +
        '<button class="ct-btn ct-pause" data-act="no">Not now</button>' +
        '<button class="ct-btn ct-complete" data-act="yes" style="background:var(--ds-primary,#4F46E5)">Start</button>' +
      '</div>';
    card.querySelector('[data-act="yes"]').addEventListener('click', startFresh);
    card.querySelector('[data-act="no"]').addEventListener('click', renderPill);
    els.root.appendChild(card);
  }

  function renderRunning() {
    clearRoot();
    var card = document.createElement('div');
    card.className = 'ct-card';
    card.innerHTML =
      '<div class="ct-head"><span class="ct-dot"></span><span class="ct-label">Consultation</span>' +
        '<button class="ct-min" title="Hide">–</button></div>' +
      '<div class="ct-time">' + fmt(elapsed) + '</div>' +
      '<div class="ct-actions">' +
        '<button class="ct-btn ct-pause"></button>' +
        '<button class="ct-btn ct-complete">✓ Complete</button>' +
      '</div>';
    els.time = card.querySelector('.ct-time');
    els.dot = card.querySelector('.ct-dot');
    els.pause = card.querySelector('.ct-pause');
    els.complete = card.querySelector('.ct-complete');
    els.pause.addEventListener('click', togglePause);
    els.complete.addEventListener('click', onComplete);
    card.querySelector('.ct-min').addEventListener('click', function () { renderPill(); });
    els.root.appendChild(card);
    updatePauseUI();
    applyAlertState();
  }

  function updatePauseUI() {
    if (els.pause) els.pause.innerHTML = manualPause ? '▶ Resume' : '⏸ Pause';
    if (els.dot) els.dot.classList.toggle('ct-paused', manualPause);
  }

  /* ------------------------------------------------------------------ actions */
  function startFresh() {
    running = true; manualPause = false;
    renderRunning();
    ensureLoops();
    save('running', false);
  }
  function resumeExisting() {
    running = true; manualPause = false;
    renderRunning();
    ensureLoops();
    save('running', false);
  }
  function togglePause() {
    manualPause = !manualPause;
    updatePauseUI();
    save(manualPause ? 'paused' : 'running', false);
  }
  function onComplete() {
    // Two-step confirm in place of a browser dialog.
    if (!els.complete.classList.contains('ct-confirm')) {
      els.complete.classList.add('ct-confirm');
      els.complete.textContent = 'Confirm?';
      setTimeout(function () {
        if (els.complete) { els.complete.classList.remove('ct-confirm'); els.complete.innerHTML = '✓ Complete'; }
      }, 3000);
      return;
    }
    running = false;
    if (tickHandle) { clearInterval(tickHandle); tickHandle = null; }
    if (saveHandle) { clearInterval(saveHandle); saveHandle = null; }
    els.complete.textContent = 'Saving…';
    // Save the final time, then complete via the SAME endpoint the page's Complete
    // button uses (PUT /api/appointments/{id}) so the outcome is identical.
    save('done', false)
      .then(function () {
        return fetch('/api/appointments/' + APPT_ID, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          body: JSON.stringify({ status: 'Completed' }),
        });
      })
      .then(function () { window.location.reload(); })
      .catch(function () { window.location.reload(); });
  }

  /* ----------------------------------------------------- visibility / unload */
  function wireLifecycle() {
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) { hiddenPause = true; if (running && !manualPause) save('paused', false); }
      else { hiddenPause = false; if (running && !manualPause) save('running', false); }
    });
    window.addEventListener('blur', function () { hiddenPause = true; });
    window.addEventListener('focus', function () { hiddenPause = false; });
    window.addEventListener('beforeunload', function () {
      if (running) save('paused', true); // leaving the screen pauses
    });
  }

  /* -------------------------------------------------------------------- boot */
  function boot() {
    if (COMPLETED) { renderReadOnlyChip(); return; }
    if (!settings.enabled) return;
    injectStyles();
    els.root = document.createElement('div');
    els.root.className = 'ct-root';
    document.body.appendChild(els.root);
    wireLifecycle();
    if (seededStatus === 'running' || seededStatus === 'paused') {
      resumeExisting();           // a timer already exists → resume silently
    } else {
      renderPrompt();             // first open → ask
    }
  }

  function init() {
    fetch('/api/doctor/settings', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) {
        var s = (d && d.settings) || {};
        settings.enabled = s.consultation_timer_enabled === true || s.consultation_timer_enabled === 1 || s.consultation_timer_enabled === '1';
        settings.sound = s.consultation_timer_sound === true || s.consultation_timer_sound === 1 || s.consultation_timer_sound === '1';
        settings.alertMinutes = parseInt(s.consultation_timer_alert_minutes, 10) || 0;
      })
      .catch(function () {})
      .then(boot);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
