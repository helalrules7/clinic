/**
 * CommentMedia — shared rich-comment helpers (board_card / patient / appointment).
 *
 * One module powers every comment surface so the behaviour stays identical:
 *   - render a comment body with @-mention avatar badges + safe markdown links
 *   - render image / audio attachments
 *   - attach a composer toolbar to a <textarea>: upload image, capture from
 *     camera, record audio, and an @-mention autocomplete
 *
 * Exposes window.CommentMedia. No framework, no build step.
 *
 * Mention wire format (stored in the comment body): `@[Display Name](u:ID)`.
 * The server resolves the id authoritatively; the renderer turns the token
 * into a pill with the colleague's mini avatar + name.
 */
(function () {
    'use strict';
    if (window.CommentMedia) return;

    var UPLOAD_URL = '/api/comments/attachments';
    var USERS_URL  = '/api/users/search';

    // ---- small utils -----------------------------------------------------
    function escapeHtml(s) {
        return (s == null ? '' : String(s))
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
    function escapeAttr(s) { return (s == null ? '' : String(s)).replace(/"/g, '&quot;'); }
    function initials(name) {
        // drop a leading honorific so "Dr. Ahmed Abo AlKassem" → "AA", not "DA"
        var n = (name || '').replace(/^(dr|prof|mr|mrs|ms|miss)\.?\s+/i, '').trim() || (name || '?');
        return n.trim().split(/\s+/).slice(0, 2)
            .map(function (w) { return w[0] || ''; }).join('').toUpperCase() || '?';
    }
    // Mirrors board.js profileSrc(): stored as "/uploads/..", served from /public.
    function avatarSrc(img) {
        if (!img) return null;
        return img.indexOf('/public/') === 0 ? img : '/public' + img;
    }
    function fmtTime(s) {
        s = Math.max(0, Math.floor(s || 0));
        return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2);
    }
    // A stable decorative "waveform" (28 bars). Same heights both layers so the
    // played-fill (clip-path) lines up exactly with the base.
    function audioWave() {
        var bars = '';
        for (var i = 0; i < 28; i++) {
            var h = 24 + Math.round(Math.abs(Math.sin(i * 1.7) * Math.cos(i * 0.6)) * 72);
            bars += '<i style="height:' + h + '%"></i>';
        }
        return bars;
    }
    // Pick the most cross-browser-playable recording container the current
    // browser supports. mp4/AAC plays everywhere (incl. Safari); webm/opus
    // does not play in Safari. Returns '' if none can be probed (let the
    // browser default).
    function pickAudioMime() {
        if (!(window.MediaRecorder && MediaRecorder.isTypeSupported)) return '';
        var prefs = ['audio/mp4', 'audio/mpeg', 'audio/webm;codecs=opus', 'audio/webm', 'audio/ogg'];
        for (var i = 0; i < prefs.length; i++) {
            try { if (MediaRecorder.isTypeSupported(prefs[i])) return prefs[i]; } catch (e) {}
        }
        return '';
    }

    // ---- body rendering --------------------------------------------------
    function renderBody(body, mentions) {
        var byId = {};
        (mentions || []).forEach(function (m) { byId[String(m.user_id)] = m; });

        var html = escapeHtml(body || '');

        // structured mention token → avatar pill
        html = html.replace(/@\[([^\]\n]+)\]\(u:(\d+)\)/g, function (_, name, id) {
            var u = byId[id];
            var img = u ? avatarSrc(u.profile_image) : null;
            var av = img
                ? '<img class="cm-mention-av" src="' + escapeAttr(img) + '" alt="" ' +
                  'onerror="this.replaceWith(Object.assign(document.createElement(\'span\'),{className:\'cm-mention-av cm-mention-av--ini\',textContent:\'' + escapeAttr(initials(name)) + '\'}))">'
                : '<span class="cm-mention-av cm-mention-av--ini">' + escapeHtml(initials(name)) + '</span>';
            return '<span class="cm-mention" data-uid="' + escapeAttr(id) + '">' +
                   av + '<span class="cm-mention-name">' + name + '</span></span>';
        });

        // legacy bare @name (typed without the picker)
        html = html.replace(/(^|[^\w@>\]])@([A-Za-z0-9_.\-]{2,40})/g, function (_, pre, name) {
            return pre + '<span class="cm-mention cm-mention--plain">@' + name + '</span>';
        });

        // controlled markdown link [label](href) → internal / http(s) only
        html = html.replace(/\[([^\]\n]+)\]\(([^)\s]+)\)/g, function (m, label, href) {
            var raw = href.replace(/&amp;/g, '&');
            if (!/^(\/[\w\-./?=&#%]*|https?:\/\/[\w\-./?=&#%:]+)$/i.test(raw)) return m;
            return '<a href="' + href + '" class="cm-link">' + label + '</a>';
        });

        return html.replace(/\n/g, '<br>');
    }

    // ---- attachment rendering -------------------------------------------
    // Document → bootstrap-icon class + tint key, by extension.
    var DOC_ICONS = {
        pdf:  { icon: 'bi-file-earmark-pdf-fill',         tint: 'pdf'   },
        doc:  { icon: 'bi-file-earmark-word-fill',        tint: 'word'  },
        docx: { icon: 'bi-file-earmark-word-fill',        tint: 'word'  },
        xls:  { icon: 'bi-file-earmark-spreadsheet-fill', tint: 'excel' },
        xlsx: { icon: 'bi-file-earmark-spreadsheet-fill', tint: 'excel' },
        ppt:  { icon: 'bi-file-earmark-ppt-fill',         tint: 'ppt'   },
        pptx: { icon: 'bi-file-earmark-ppt-fill',         tint: 'ppt'   },
        txt:  { icon: 'bi-file-earmark-text-fill',        tint: 'txt'   },
    };
    function fileMeta(name) {
        var ext = (name || '').split('.').pop().toLowerCase();
        return DOC_ICONS[ext] || { icon: 'bi-file-earmark-fill', tint: 'generic', ext: ext };
    }
    function humanSize(bytes) {
        if (!bytes) return '';
        var u = ['B', 'KB', 'MB', 'GB']; var i = 0;
        while (bytes >= 1024 && i < u.length - 1) { bytes /= 1024; i++; }
        return (bytes < 10 && i > 0 ? bytes.toFixed(1) : Math.round(bytes)) + ' ' + u[i];
    }

    function renderAttachments(atts) {
        if (!atts || !atts.length) return '';
        var imgs = atts.filter(function (a) { return a.kind === 'image'; });
        var auds = atts.filter(function (a) { return a.kind === 'audio'; });
        var docs = atts.filter(function (a) { return a.kind === 'file'; });
        var html = '';

        if (imgs.length) {
            html += '<div class="cm-att-images">' + imgs.map(function (a) {
                // href kept for right-click / no-JS fallback; the delegated
                // handler opens it in an in-page lightbox instead of a new tab.
                return '<a class="cm-att-img" href="' + escapeAttr(a.url) + '">' +
                       '<img src="' + escapeAttr(a.url) + '" alt="' + escapeAttr(a.name || 'image') + '" loading="lazy"></a>';
            }).join('') + '</div>';
        }
        if (docs.length) {
            html += '<div class="cm-att-files">' + docs.map(function (a) {
                var m = fileMeta(a.name);
                var size = humanSize(a.size);
                return '<a class="cm-att-file cm-att-file--' + m.tint + '" ' +
                            'href="' + escapeAttr(a.url) + '" download="' + escapeAttr(a.name || '') + '" ' +
                            'target="_blank" rel="noopener">' +
                            '<span class="cm-att-file__icon"><i class="bi ' + m.icon + '"></i></span>' +
                            '<span class="cm-att-file__body">' +
                                '<span class="cm-att-file__name">' + escapeHtml(a.name || 'file') + '</span>' +
                                (size ? '<span class="cm-att-file__size">' + escapeHtml(size) + '</span>' : '') +
                            '</span>' +
                            '<span class="cm-att-file__dl" aria-hidden="true"><i class="bi bi-download"></i></span>' +
                       '</a>';
            }).join('') + '</div>';
        }
        auds.forEach(function (a) {
            var initT = a.duration_ms ? fmtTime(a.duration_ms / 1000) : '0:00';
            var bars = audioWave();
            html += '<div class="cm-audio" style="--p:0"' +
                        (a.duration_ms ? ' data-duration="' + (a.duration_ms / 1000) + '"' : '') + '>' +
                    '<audio preload="metadata" src="' + escapeAttr(a.url) + '"></audio>' +
                    '<button type="button" class="cm-audio-play" aria-label="Play"><i class="bi bi-play-fill"></i></button>' +
                    '<div class="cm-audio-track" role="slider" aria-label="Seek">' +
                        '<div class="cm-audio-wave">' + bars + '</div>' +
                        '<div class="cm-audio-wave cm-audio-wave--fill">' + bars + '</div>' +
                    '</div>' +
                    '<span class="cm-audio-time">' + escapeHtml(initT) + '</span>' +
                    '</div>';
        });
        return html ? '<div class="cm-attachments">' + html + '</div>' : '';
    }

    // ---- custom audio player (delegated, wired once) ---------------------
    function initAudioPlayers() {
        if (window.__cmAudioInit) return;
        window.__cmAudioInit = true;

        // play/pause + seek (these bubble)
        document.addEventListener('click', function (e) {
            var playBtn = e.target.closest && e.target.closest('.cm-audio-play');
            if (playBtn) {
                var box = playBtn.closest('.cm-audio'); var au = box.querySelector('audio');
                if (au.paused) {
                    document.querySelectorAll('.cm-audio audio').forEach(function (o) { if (o !== au) o.pause(); });
                    // play() returns a promise that REJECTS when the codec/
                    // container isn't supported (e.g. a Chrome-recorded
                    // webm/opus clip opened in Safari). Catch it so it's never
                    // an unhandled rejection, and offer a download instead.
                    var p = au.play();
                    if (p && typeof p.catch === 'function') {
                        p.catch(function () { audioFallback(box, au); });
                    }
                } else { au.pause(); }
                return;
            }
            var track = e.target.closest && e.target.closest('.cm-audio-track');
            if (track) {
                var box2 = track.closest('.cm-audio'); var au2 = box2.querySelector('audio');
                var rect = track.getBoundingClientRect();
                var ratio = Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width));
                var dur = (isFinite(au2.duration) && au2.duration > 0) ? au2.duration : (parseFloat(box2.dataset.duration) || 0);
                if (dur) { au2.currentTime = ratio * dur; box2.style.setProperty('--p', ratio); }
            }

            // image attachment → open in a lightbox modal (not a new tab)
            var imgLink = e.target.closest && e.target.closest('.cm-att-img');
            if (imgLink) { e.preventDefault(); openLightbox(imgLink); }
        });

        // media events don't bubble → listen in the capture phase
        function onMedia(e) {
            var au = e.target;
            if (!au || au.tagName !== 'AUDIO') return;
            var box = au.closest && au.closest('.cm-audio'); if (!box) return;
            var dur = (isFinite(au.duration) && au.duration > 0) ? au.duration : (parseFloat(box.dataset.duration) || 0);
            var cur = au.currentTime || 0;
            box.style.setProperty('--p', dur ? (cur / dur) : 0);
            var t = box.querySelector('.cm-audio-time');
            if (t) t.textContent = (au.paused && cur < 0.1) ? fmtTime(dur) : fmtTime(cur);
            var icon = box.querySelector('.cm-audio-play i');
            if (icon) icon.className = au.paused ? 'bi bi-play-fill' : 'bi bi-pause-fill';
            box.classList.toggle('is-playing', !au.paused);
        }
        ['timeupdate', 'play', 'pause', 'ended', 'loadedmetadata', 'durationchange'].forEach(function (ev) {
            document.addEventListener(ev, onMedia, true);
        });
        // A media decode/network error also surfaces here (capture phase) —
        // e.g. an unsupported codec — so swap to the download fallback.
        document.addEventListener('error', function (e) {
            var au = e.target;
            if (!au || au.tagName !== 'AUDIO') return;
            var box = au.closest && au.closest('.cm-audio'); if (box) audioFallback(box, au);
        }, true);
    }
    initAudioPlayers();

    // Replace an un-playable audio widget with a clear download affordance
    // (the browser can't decode this container, e.g. webm/opus in Safari).
    function audioFallback(box, au) {
        if (!box || box.classList.contains('cm-audio--err')) return;
        box.classList.add('cm-audio--err');
        var src = (au && au.getAttribute('src')) || '';
        box.innerHTML =
            '<span class="cm-audio-errmsg"><i class="bi bi-exclamation-triangle"></i> ' +
            'Can’t play here</span>' +
            '<a class="cm-audio-dl" href="' + escapeAttr(src) + '" download ' +
            'title="This audio format isn’t supported by this browser — download to play">' +
            '<i class="bi bi-download"></i> Download</a>';
    }

    // ---- image lightbox --------------------------------------------------
    function openLightbox(anchor) {
        var group = anchor.closest('.cm-att-images');
        var links = group
            ? Array.prototype.slice.call(group.querySelectorAll('.cm-att-img'))
            : [anchor];
        var idx = Math.max(0, links.indexOf(anchor));
        var multi = links.length > 1;

        var ov = document.createElement('div');
        ov.className = 'cm-lightbox';
        var zoomPanel = (window.ImageViewerModal && window.ImageViewerModal.zoomPanelHtml)
            ? window.ImageViewerModal.zoomPanelHtml() : '';
        ov.innerHTML =
            '<button type="button" class="cm-lb-close" aria-label="Close"><i class="bi bi-x-lg"></i></button>' +
            (multi ? '<button type="button" class="cm-lb-nav cm-lb-prev" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>' : '') +
            '<div class="iv-image-wrap">' +
            '<img class="cm-lb-img" src="" alt="">' +
            zoomPanel +
            '</div>' +
            (multi ? '<button type="button" class="cm-lb-nav cm-lb-next" aria-label="Next"><i class="bi bi-chevron-right"></i></button>' : '') +
            (multi ? '<div class="cm-lb-count"></div>' : '');
        document.body.appendChild(ov);
        document.body.classList.add('cm-lb-open');

        var imgEl = ov.querySelector('.cm-lb-img');
        var wrapEl = ov.querySelector('.iv-image-wrap');
        var countEl = ov.querySelector('.cm-lb-count');
        var zoomCtl = (window.ImageViewerModal && wrapEl && imgEl)
            ? window.ImageViewerModal.bindZoom(wrapEl, imgEl) : null;
        function show(i) {
            idx = (i + links.length) % links.length;
            imgEl.src = links[idx].getAttribute('href') || links[idx].querySelector('img').src;
            if (countEl) countEl.textContent = (idx + 1) + ' / ' + links.length;
            if (zoomCtl && zoomCtl.reset) zoomCtl.reset();
        }
        function close() {
            document.removeEventListener('keydown', onKey);
            document.body.classList.remove('cm-lb-open');
            ov.remove();
        }
        function onKey(ev) {
            if (ev.key === 'Escape') close();
            else if (multi && ev.key === 'ArrowLeft') show(idx - 1);
            else if (multi && ev.key === 'ArrowRight') show(idx + 1);
        }
        ov.addEventListener('click', function (ev) {
            if (ev.target === ov || ev.target.closest('.cm-lb-close')) { close(); return; }
            if (ev.target.closest('.cm-lb-prev')) { show(idx - 1); }
            if (ev.target.closest('.cm-lb-next')) { show(idx + 1); }
        });
        document.addEventListener('keydown', onKey);
        show(idx);
    }

    // ---- upload ----------------------------------------------------------
    function uploadFile(file, csrf, durationMs) {
        var fd = new FormData();
        fd.append('file', file, file.name || 'upload');
        if (durationMs) fd.append('duration_ms', String(durationMs));
        return fetch(UPLOAD_URL, {
            method: 'POST', credentials: 'same-origin',
            headers: { 'X-CSRF-Token': csrf || '' },
            body: fd
        }).then(function (r) { return r.json(); });
    }

    // ---- composer --------------------------------------------------------
    function attachComposer(opts) {
        opts = opts || {};
        var ta = opts.textarea;
        if (!ta) return null;
        var csrf = function () { return typeof opts.getCsrf === 'function' ? opts.getCsrf() : (opts.csrf || ''); };
        var onChange = opts.onChange || function () {};

        var pending = [];          // [{id, kind, url, name, ...}]
        var supportsMedia = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);

        // wrapper (relative) so the autocomplete + camera overlay anchor here
        var wrap = document.createElement('div');
        wrap.className = 'cm-composer';
        ta.parentNode.insertBefore(wrap, ta);

        // Rich content-editable editor replaces the plain textarea: it shows
        // @-mentions as styled chips while composing, auto-grows with content,
        // and is drag-resizable. The original textarea is kept hidden, used only
        // as the mount anchor + placeholder source.
        var editor = document.createElement('div');
        editor.className = 'cm-editor is-empty';
        editor.contentEditable = 'true';
        editor.setAttribute('role', 'textbox');
        editor.setAttribute('aria-multiline', 'true');
        editor.dataset.placeholder = ta.getAttribute('placeholder') || '';
        ta.style.display = 'none';
        ta.setAttribute('aria-hidden', 'true');
        wrap.appendChild(editor);
        wrap.appendChild(ta);

        // toolbar
        var bar = document.createElement('div');
        bar.className = 'cm-toolbar';
        bar.innerHTML =
            '<button type="button" class="cm-tool" data-act="image" title="Add a photo"><i class="bi bi-image"></i></button>' +
            '<button type="button" class="cm-tool" data-act="document" title="Attach a document (PDF, Word, Excel, PPT, TXT)"><i class="bi bi-paperclip"></i></button>' +
            (supportsMedia ? '<button type="button" class="cm-tool" data-act="camera" title="Take a photo"><i class="bi bi-camera"></i></button>' : '') +
            (supportsMedia && window.MediaRecorder ? '<button type="button" class="cm-tool" data-act="audio" title="Record audio"><i class="bi bi-mic"></i></button>' : '') +
            '<button type="button" class="cm-tool" data-act="mention" title="Mention a colleague"><i class="bi bi-at"></i></button>';
        wrap.appendChild(bar);

        // hidden file input — image picker
        var fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*';
        fileInput.multiple = true;
        fileInput.style.display = 'none';
        wrap.appendChild(fileInput);

        // hidden file input — document picker (separate so each button gets
        // the right native chooser hint and accept filter)
        var docInput = document.createElement('input');
        docInput.type = 'file';
        docInput.accept = [
            '.pdf', '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx', '.txt',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
        ].join(',');
        docInput.multiple = true;
        docInput.style.display = 'none';
        wrap.appendChild(docInput);

        // pending-attachment preview strip
        var strip = document.createElement('div');
        strip.className = 'cm-previews';
        wrap.appendChild(strip);

        // autocomplete dropdown — appended to <body> with fixed positioning so
        // an ancestor `overflow:hidden` (e.g. the board's .patient-card) can't
        // clip it.
        var menu = document.createElement('div');
        menu.className = 'cm-mention-menu';
        menu.hidden = true;
        document.body.appendChild(menu);

        function renderPreviews() {
            strip.innerHTML = '';
            pending.forEach(function (a, i) {
                var chip = document.createElement('div');
                chip.className = 'cm-prev cm-prev--' + a.kind;
                if (a.kind === 'image') {
                    chip.innerHTML = '<img src="' + escapeAttr(a.url) + '" alt="">';
                } else if (a.kind === 'audio') {
                    chip.innerHTML = '<i class="bi bi-mic-fill"></i><span>Audio</span>';
                } else if (a.kind === 'file') {
                    var m = fileMeta(a.name);
                    chip.classList.add('cm-prev--file', 'cm-prev--' + m.tint);
                    chip.innerHTML = '<i class="bi ' + m.icon + '"></i>' +
                                     '<span class="cm-prev-name">' + escapeHtml(a.name || 'file') + '</span>';
                } else {
                    chip.innerHTML = '<i class="bi bi-paperclip"></i>';
                }
                var x = document.createElement('button');
                x.type = 'button'; x.className = 'cm-prev-x'; x.innerHTML = '<i class="bi bi-x"></i>';
                x.addEventListener('click', function () { pending.splice(i, 1); renderPreviews(); onChange(); });
                chip.appendChild(x);
                strip.appendChild(chip);
            });
            strip.classList.toggle('is-empty', pending.length === 0);
        }

        function addUploaded(att) { pending.push(att); renderPreviews(); onChange(); }

        // Classify a File by MIME + extension. Returns 'image' | 'file' | null.
        var DOC_EXT_RE = /\.(pdf|docx?|xlsx?|pptx?|txt)$/i;
        function classifyFile(f) {
            if (/^image\//.test(f.type)) return 'image';
            if (/^(application\/(pdf|msword|vnd\.(ms-excel|ms-powerpoint|ms-office)|vnd\.openxmlformats-officedocument\.|CDFV2)|text\/plain)/.test(f.type)
                || DOC_EXT_RE.test(f.name || '')) return 'file';
            return null;
        }

        function handleFiles(files) {
            Array.prototype.forEach.call(files, function (f) {
                var kind = classifyFile(f);
                if (!kind) return;
                var holder = kind === 'image'
                    ? { id: 'tmp', kind: 'image', url: URL.createObjectURL(f), name: f.name, size: f.size, uploading: true }
                    : { id: 'tmp', kind: 'file',  url: '#', name: f.name, size: f.size, uploading: true };
                pending.push(holder); renderPreviews(); onChange();
                uploadFile(f, csrf()).then(function (j) {
                    var idx = pending.indexOf(holder);
                    if (!j || !j.ok) {
                        if (idx > -1) pending.splice(idx, 1); renderPreviews(); onChange();
                        toast((j && j.error) || 'Upload failed'); return;
                    }
                    if (idx > -1) pending[idx] = j.attachment;
                    renderPreviews(); onChange();
                }).catch(function () {
                    var idx = pending.indexOf(holder); if (idx > -1) pending.splice(idx, 1);
                    renderPreviews(); onChange(); toast('Upload failed');
                });
            });
        }

        function toast(msg) {
            if (typeof opts.onError === 'function') opts.onError(msg);
            else console.warn('[CommentMedia]', msg);
        }

        // ---- toolbar actions
        bar.addEventListener('click', function (e) {
            var btn = e.target.closest('.cm-tool'); if (!btn) return;
            var act = btn.dataset.act;
            if (act === 'image')    fileInput.click();
            if (act === 'document') docInput.click();
            if (act === 'camera')   openCamera();
            if (act === 'audio')    toggleAudio(btn);
            if (act === 'mention')  { insertAtCaret('@'); onInput(); onChange(); }
        });
        fileInput.addEventListener('change', function () { if (fileInput.files) handleFiles(fileInput.files); fileInput.value = ''; });
        docInput.addEventListener('change',  function () { if (docInput.files)  handleFiles(docInput.files);  docInput.value  = ''; });

        // ---- camera capture (getUserMedia → canvas → blob)
        function openCamera() {
            var ov = document.createElement('div');
            ov.className = 'cm-cam';
            ov.innerHTML =
                '<div class="cm-cam-box">' +
                '  <video autoplay playsinline></video>' +
                '  <div class="cm-cam-actions">' +
                '    <button type="button" class="cm-cam-shot"><i class="bi bi-camera-fill"></i> Capture</button>' +
                '    <button type="button" class="cm-cam-cancel">Cancel</button>' +
                '  </div>' +
                '</div>';
            document.body.appendChild(ov);
            var video = ov.querySelector('video');
            var stream = null;
            function stop() { if (stream) stream.getTracks().forEach(function (t) { t.stop(); }); ov.remove(); }
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
                .then(function (s) { stream = s; video.srcObject = s; })
                .catch(function () { toast('Camera unavailable'); stop(); });
            ov.querySelector('.cm-cam-cancel').addEventListener('click', stop);
            ov.addEventListener('click', function (e) { if (e.target === ov) stop(); });
            ov.querySelector('.cm-cam-shot').addEventListener('click', function () {
                if (!video.videoWidth) return;
                var cv = document.createElement('canvas');
                cv.width = video.videoWidth; cv.height = video.videoHeight;
                cv.getContext('2d').drawImage(video, 0, 0);
                cv.toBlob(function (blob) {
                    var f = new File([blob], 'camera_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                    handleFiles([f]); stop();
                }, 'image/jpeg', 0.9);
            });
        }

        // ---- audio recording (MediaRecorder)
        var rec = null, recChunks = [], recStart = 0, recTimer = null, recStream = null;
        function toggleAudio(btn) {
            if (rec && rec.state === 'recording') { rec.stop(); return; }
            navigator.mediaDevices.getUserMedia({ audio: true }).then(function (stream) {
                recStream = stream; recChunks = [];
                // Prefer a widely-playable container. Safari can only DECODE
                // mp4/AAC (not webm/opus), so record mp4 when the browser can
                // (Safari does) → those clips then play in every browser.
                // Chrome can't record mp4, so it falls back to webm/opus
                // (fine in Chrome/Firefox/Edge; Safari shows a download
                // fallback on play — see initAudioPlayers).
                var mime = pickAudioMime();
                rec = mime ? new MediaRecorder(stream, { mimeType: mime }) : new MediaRecorder(stream);
                rec.ondataavailable = function (e) { if (e.data && e.data.size) recChunks.push(e.data); };
                rec.onstop = function () {
                    clearInterval(recTimer); btn.classList.remove('is-recording'); btn.innerHTML = '<i class="bi bi-mic"></i>';
                    if (recStream) recStream.getTracks().forEach(function (t) { t.stop(); });
                    var dur = Date.now() - recStart;
                    var type = rec.mimeType || mime || 'audio/webm';
                    var blob = new Blob(recChunks, { type: type });
                    var ext = type.indexOf('mp4') > -1 ? 'm4a' : (type.indexOf('mpeg') > -1 ? 'mp3' : (type.indexOf('ogg') > -1 ? 'ogg' : 'webm'));
                    var f = new File([blob], 'voice_' + Date.now() + '.' + ext, { type: blob.type });
                    var holder = { id: 'tmp', kind: 'audio', url: URL.createObjectURL(blob), name: f.name, uploading: true };
                    pending.push(holder); renderPreviews(); onChange();
                    uploadFile(f, csrf(), dur).then(function (j) {
                        var idx = pending.indexOf(holder);
                        if (!j || !j.ok) { if (idx > -1) pending.splice(idx, 1); renderPreviews(); onChange(); toast('Upload failed'); return; }
                        if (idx > -1) pending[idx] = j.attachment;
                        renderPreviews(); onChange();
                    }).catch(function () { var idx = pending.indexOf(holder); if (idx > -1) pending.splice(idx, 1); renderPreviews(); onChange(); toast('Upload failed'); });
                };
                rec.start();
                recStart = Date.now();
                btn.classList.add('is-recording');
                recTimer = setInterval(function () {
                    var s = Math.floor((Date.now() - recStart) / 1000);
                    btn.innerHTML = '<i class="bi bi-stop-fill"></i> ' + Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2);
                    if (s >= 120) rec.stop(); // 2 min hard cap
                }, 250);
            }).catch(function () { toast('Microphone unavailable'); });
        }

        // ---- content-editable helpers (placeholder, serialize, chips) -----
        function refreshEmpty() {
            var empty = editor.textContent.trim() === '' && !editor.querySelector('.cm-chip, img');
            editor.classList.toggle('is-empty', empty);
        }
        function serialize(el) {
            var out = '';
            Array.prototype.forEach.call(el.childNodes, function (n) {
                if (n.nodeType === 3) { out += n.textContent; return; }
                if (n.nodeType !== 1) return;
                if (n.classList && n.classList.contains('cm-chip')) {
                    out += '@[' + (n.dataset.name || '') + '](u:' + (n.dataset.uid || '') + ')';
                } else if (n.tagName === 'BR') {
                    out += '\n';
                } else if (n.tagName === 'DIV' || n.tagName === 'P') {
                    if (out && !/\n$/.test(out)) out += '\n';
                    out += serialize(n);
                } else {
                    out += serialize(n);
                }
            });
            return out;
        }
        function getBodyText() {
            return serialize(editor).replace(/ /g, ' ').replace(/\n{3,}/g, '\n\n').trim();
        }
        function makeChip(id, name, img) {
            var chip = document.createElement('span');
            chip.className = 'cm-chip';
            chip.contentEditable = 'false';
            chip.dataset.uid = id;
            chip.dataset.name = name;
            var src = avatarSrc(img);
            chip.innerHTML = (src
                ? '<img class="cm-chip-av" src="' + escapeAttr(src) + '" alt="">'
                : '<span class="cm-chip-av cm-chip-av--ini">' + escapeHtml(initials(name)) + '</span>')
                + '<span class="cm-chip-name">' + escapeHtml(name) + '</span>';
            return chip;
        }

        // ---- @-mention autocomplete (over the content-editable editor) ----
        var mItems = [], mActive = -1, mDebounce = null, curCtx = null;
        function queryBeforeCaret() {
            var sel = window.getSelection();
            if (!sel || !sel.rangeCount) return null;
            var range = sel.getRangeAt(0);
            if (!range.collapsed) return null;
            var node = range.startContainer;
            if (node.nodeType !== 3 || !editor.contains(node)) return null;
            var m = node.textContent.slice(0, range.startOffset).match(/(?:^|\s)@([A-Za-z0-9_.\-]{0,40})$/);
            if (!m) return null;
            return { node: node, query: m[1], at: range.startOffset - m[1].length - 1, caret: range.startOffset };
        }
        function onInput() {
            refreshEmpty();
            var ctx = queryBeforeCaret();
            if (!ctx) { hideMenu(); return; }
            curCtx = ctx;
            clearTimeout(mDebounce);
            mDebounce = setTimeout(function () { searchUsers(ctx.query); }, 140);
        }
        function searchUsers(q) {
            fetch(USERS_URL + '?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (!j || !j.ok) { hideMenu(); return; }
                    mItems = j.data || []; mActive = mItems.length ? 0 : -1;
                    if (!mItems.length) { hideMenu(); return; }
                    menu.innerHTML = mItems.map(function (u, i) {
                        var img = avatarSrc(u.profile_image);
                        var av = img
                            ? '<img class="cm-mm-av" src="' + escapeAttr(img) + '" alt="">'
                            : '<span class="cm-mm-av cm-mm-av--ini">' + escapeHtml(initials(u.name)) + '</span>';
                        return '<div class="cm-mm-item' + (i === 0 ? ' is-active' : '') + '" data-i="' + i + '">' +
                               av + '<span class="cm-mm-name">' + escapeHtml(u.name) + '</span>' +
                               '<span class="cm-mm-role">' + escapeHtml(u.role || '') + '</span></div>';
                    }).join('');
                    positionMenu();
                    menu.hidden = false;
                }).catch(hideMenu);
        }
        function positionMenu() {
            var r = editor.getBoundingClientRect();
            var spaceBelow = window.innerHeight - r.bottom;
            menu.style.left = r.left + 'px';
            menu.style.width = r.width + 'px';
            if (spaceBelow < 200 && r.top > spaceBelow) {       // flip above
                menu.style.top = 'auto';
                menu.style.bottom = (window.innerHeight - r.top + 4) + 'px';
                menu.style.maxHeight = Math.min(260, r.top - 12) + 'px';
            } else {
                menu.style.bottom = 'auto';
                menu.style.top = (r.bottom + 4) + 'px';
                menu.style.maxHeight = Math.min(260, spaceBelow - 12) + 'px';
            }
        }
        function hideMenu() { menu.hidden = true; mItems = []; mActive = -1; curCtx = null; }
        function pick(i) {
            var u = mItems[i]; if (!u || !curCtx) { hideMenu(); return; }
            var node = curCtx.node;
            var before = node.textContent.slice(0, curCtx.at);
            var after  = node.textContent.slice(curCtx.caret);
            var parent = node.parentNode;
            var chip = makeChip(u.id, u.name, u.profile_image);
            var space = document.createTextNode(' ');
            var afterNode = document.createTextNode(after);
            parent.insertBefore(afterNode, node.nextSibling); // node | after
            parent.insertBefore(space, afterNode);            // node | space | after
            parent.insertBefore(chip, space);                 // node | chip | space | after
            node.textContent = before;
            var range = document.createRange();
            range.setStart(afterNode, 0); range.collapse(true);
            var sel = window.getSelection(); sel.removeAllRanges(); sel.addRange(range);
            hideMenu(); editor.focus(); refreshEmpty(); onChange();
        }
        function paintActive() {
            Array.prototype.forEach.call(menu.children, function (el, i) { el.classList.toggle('is-active', i === mActive); });
        }
        menu.addEventListener('mousedown', function (e) {
            var it = e.target.closest('.cm-mm-item'); if (!it) return;
            e.preventDefault(); pick(parseInt(it.dataset.i, 10));
        });
        editor.addEventListener('input', function () { onInput(); onChange(); });
        editor.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault(); if (typeof opts.onSubmit === 'function') opts.onSubmit(); return;
            }
            if (menu.hidden) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); mActive = Math.min(mActive + 1, mItems.length - 1); paintActive(); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); mActive = Math.max(mActive - 1, 0); paintActive(); }
            else if (e.key === 'Enter' || e.key === 'Tab') { if (mActive > -1) { e.preventDefault(); pick(mActive); } }
            else if (e.key === 'Escape') { hideMenu(); }
        });
        // paste as plain text so external rich HTML can't enter the editor
        editor.addEventListener('paste', function (e) {
            e.preventDefault();
            var text = ((e.clipboardData || window.clipboardData).getData('text/plain') || '');
            document.execCommand('insertText', false, text);
        });
        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target) && !menu.contains(e.target)) hideMenu();
        });
        // the menu is fixed-positioned off the editor; hide it on scroll rather
        // than chase a moving anchor.
        window.addEventListener('scroll', function () { if (!menu.hidden) hideMenu(); }, true);

        function insertAtCaret(text) {
            editor.focus();
            var sel = window.getSelection();
            if (!sel.rangeCount || !editor.contains(sel.getRangeAt(0).startContainer)) {
                editor.appendChild(document.createTextNode(text));
            } else {
                var range = sel.getRangeAt(0);
                range.deleteContents();
                var tn = document.createTextNode(text);
                range.insertNode(tn);
                range.setStartAfter(tn); range.collapse(true);
                sel.removeAllRanges(); sel.addRange(range);
            }
            refreshEmpty();
        }

        renderPreviews();
        refreshEmpty();

        return {
            getBody: function () { return getBodyText(); },
            getAttachmentIds: function () {
                return pending.filter(function (a) { return a.id && a.id !== 'tmp'; })
                              .map(function (a) { return a.id; });
            },
            isUploading: function () { return pending.some(function (a) { return a.uploading; }); },
            hasContent: function () { return getBodyText() !== '' || pending.length > 0; },
            reset: function () { editor.innerHTML = ''; pending = []; renderPreviews(); hideMenu(); refreshEmpty(); },
            focus: function () { editor.focus(); }
        };
    }

    window.CommentMedia = {
        escapeHtml: escapeHtml,
        escapeAttr: escapeAttr,
        initials: initials,
        avatarSrc: avatarSrc,
        renderBody: renderBody,
        renderAttachments: renderAttachments,
        uploadFile: uploadFile,
        attachComposer: attachComposer
    };
})();
