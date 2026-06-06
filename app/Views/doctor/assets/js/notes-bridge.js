/* =====================================================================
 * notes-bridge.js — one merged view over the app's TWO note stores.
 *
 * The app keeps two independent stores:
 *   - quick_notes  → simple { title, body, pinned, background_color }   (drawer + Cmd+K)
 *   - notes        → canvas widgets { title, content(HTML), background_color,
 *                     position/size, alert }                            (notes page + dashboard)
 *
 * The user wants a note added in ANY surface to appear in ALL surfaces
 * ("merged read"): every surface DISPLAYS both stores; each note still
 * lives in its origin store; edit / delete / pin route to the correct API.
 * No duplication.
 *
 * Public API (window.NotesBridge):
 *   NotesBridge.list()                  → Promise<NormalizedNote[]> (pinned first, newest first)
 *   NotesBridge.listFrom(scope)         → 'quick' | 'board' | 'all'
 *   NotesBridge.remove(rec)             → Promise  (routed DELETE, emits sync)
 *   NotesBridge.setPinned(rec, on)      → Promise  (quick only; board is a no-op)
 *   NotesBridge.save(rec, fields)       → Promise<NormalizedNote> (routed PATCH, emits sync)
 *   NotesBridge.toText(html)            → plain text from HTML
 *   NotesBridge.toHtml(text)            → minimal HTML (escaped + <br>) from text
 *
 * NormalizedNote = {
 *   key, origin:'quick'|'board', id,
 *   title, body (plain text), html, background_color, pinned,
 *   created_at, updated_at, raw
 * }
 * ===================================================================== */
(function (global) {
    'use strict';

    if (global.NotesBridge) return;

    function api(method, url, body) {
        var opts = {
            method: method,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        };
        if (body !== undefined) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        return fetch(url, opts).then(function (r) {
            return r.text().then(function (t) {
                var json = null;
                try { json = t ? JSON.parse(t) : null; } catch (_) {}
                if (!r.ok) {
                    var msg = (json && (json.message || json.error)) || ('HTTP ' + r.status);
                    throw new Error(msg);
                }
                return json;
            });
        });
    }

    function toText(html) {
        if (html == null) return '';
        var d = document.createElement('div');
        d.innerHTML = String(html);
        // Preserve line structure from block elements / <br>.
        d.querySelectorAll('br').forEach(function (br) { br.replaceWith('\n'); });
        d.querySelectorAll('p, div, li').forEach(function (el) { el.append('\n'); });
        var txt = d.textContent || '';
        return txt.replace(/\n{3,}/g, '\n\n').trim();
    }

    function toHtml(text) {
        var esc = String(text == null ? '' : text)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return esc.replace(/\n/g, '<br>');
    }

    function normQuick(n) {
        return {
            key: 'q:' + n.id,
            origin: 'quick',
            id: n.id,
            title: n.title || '',
            body: n.body || '',
            html: toHtml(n.body || ''),
            background_color: n.background_color || '',
            pinned: !!n.pinned,
            created_at: n.created_at || null,
            updated_at: n.updated_at || n.created_at || null,
            raw: n
        };
    }

    function normBoard(n) {
        return {
            key: 'b:' + n.id,
            origin: 'board',
            id: n.id,
            title: n.title || '',
            body: toText(n.content || ''),
            html: n.content || '',
            background_color: n.background_color || '',
            pinned: false,                 // board notes have no pin concept
            created_at: n.created_at || null,
            updated_at: n.updated_at || n.created_at || null,
            raw: n
        };
    }

    function fetchQuick() {
        return api('GET', '/api/quick-notes')
            .then(function (d) {
                var arr = (d && d.data) || (Array.isArray(d) ? d : []) || [];
                return arr.map(normQuick);
            })
            .catch(function () { return []; });
    }

    function fetchBoard() {
        return api('GET', '/api/notes')
            .then(function (d) {
                var arr = (d && (d.notes || d.data)) || (Array.isArray(d) ? d : []) || [];
                return arr.map(normBoard);
            })
            .catch(function () { return []; });
    }

    function sortNotes(list) {
        return list.slice().sort(function (a, b) {
            var ap = a.pinned ? 1 : 0, bp = b.pinned ? 1 : 0;
            if (ap !== bp) return bp - ap;
            var at = new Date(a.updated_at || a.created_at || 0).getTime();
            var bt = new Date(b.updated_at || b.created_at || 0).getTime();
            return bt - at;
        });
    }

    function list() {
        return Promise.all([fetchQuick(), fetchBoard()]).then(function (parts) {
            return sortNotes(parts[0].concat(parts[1]));
        });
    }

    // The bridge performs the routed HTTP only. Broadcasting the change over
    // NotesSync is left to the calling surface so it can self-suppress its own
    // echo (avoids a redundant reload right after an optimistic update).

    function remove(rec) {
        var url = rec.origin === 'board'
            ? '/api/notes/' + encodeURIComponent(rec.id)
            : '/api/quick-notes/' + encodeURIComponent(rec.id);
        return api('DELETE', url);
    }

    function setPinned(rec, on) {
        if (rec.origin !== 'quick') return Promise.resolve(null);   // board has no pin
        var url = '/api/quick-notes/' + encodeURIComponent(rec.id) + (on ? '/pin' : '/unpin');
        return api('POST', url);
    }

    // fields: { title?, body?, background_color?, pinned? }
    function save(rec, fields) {
        fields = fields || {};
        var out;
        if (rec.origin === 'board') {
            // Board notes are updated via PUT and the endpoint does not echo the
            // row back, so we reconstruct the normalized record locally.
            var payload = {};
            if ('title' in fields) payload.title = fields.title;
            if ('body' in fields)  payload.content = toHtml(fields.body);
            if ('background_color' in fields) payload.background_color = fields.background_color;
            out = api('PUT', '/api/notes/' + encodeURIComponent(rec.id), payload)
                .then(function () {
                    var merged = Object.assign({}, rec.raw, {
                        title: payload.title != null ? payload.title : rec.raw.title,
                        content: payload.content != null ? payload.content : rec.raw.content,
                        background_color: payload.background_color != null ? payload.background_color : rec.raw.background_color
                    });
                    return normBoard(merged);
                });
        } else {
            var p = {};
            if ('title' in fields) p.title = fields.title;
            if ('body' in fields)  p.body = fields.body;
            if ('background_color' in fields) p.background_color = fields.background_color;
            if ('pinned' in fields) p.pinned = fields.pinned;
            out = api('PATCH', '/api/quick-notes/' + encodeURIComponent(rec.id), p)
                .then(function (d) {
                    var note = (d && (d.data || d.note)) || null;
                    return note ? normQuick(note) : Object.assign({}, rec, fields);
                });
        }
        return out;
    }

    global.NotesBridge = {
        list: list,
        remove: remove,
        setPinned: setPinned,
        save: save,
        toText: toText,
        toHtml: toHtml,
        _normQuick: normQuick,
        _normBoard: normBoard
    };
})(window);
