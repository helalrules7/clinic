/* =====================================================================
 * notes-sync.js — tiny cross-surface live-sync bus for notes.
 *
 * The app has two note stores (quick_notes: drawer + Cmd+K modal; and the
 * rich `notes`: dashboard widget + notes page). Each surface keeps its own
 * local copy, so a note added in one place used to require a page refresh to
 * show up elsewhere. This bus lets any surface announce a change and every
 * other surface react immediately — no refresh.
 *
 * Emit after any create / update / delete / pin:
 *   window.NotesSync.emit('quick'|'board', { action, id, note });
 *
 * Listen (a surface refreshing itself):
 *   window.NotesSync.on(function (detail) { ... reload ... });
 *
 * For backwards-compat it also (re)dispatches the legacy DOM events
 * 'quicknote:changed' and 'notes:changed' that some modules already listen to.
 * A guard token prevents a surface from reacting to its own emit.
 * ===================================================================== */
(function (global) {
    'use strict';

    if (global.NotesSync) return;

    var listeners = [];
    var seq = 0;

    function emit(scope, detail) {
        detail = detail || {};
        detail.scope = scope || 'all';     // 'quick' | 'board' | 'all'
        detail.token = ++seq;              // lets emitters ignore their own echo
        detail.ts = Date.now();

        listeners.forEach(function (fn) {
            try { fn(detail); } catch (e) { if (global.console) console.error('[NotesSync]', e); }
        });

        // Legacy DOM events so existing code keeps working.
        try { document.dispatchEvent(new CustomEvent('notes:changed', { detail: detail })); } catch (_) {}
        try { document.dispatchEvent(new CustomEvent('quicknote:changed', { detail: detail })); } catch (_) {}
    }

    function on(fn) {
        if (typeof fn === 'function') listeners.push(fn);
        return function off() {
            var i = listeners.indexOf(fn);
            if (i !== -1) listeners.splice(i, 1);
        };
    }

    global.NotesSync = { emit: emit, on: on };
})(window);
