/* Doctor↔Secretary chat widget (adaptive polling). Bilingual (ar secretary /
   en doctor), theme via CSS tokens. Trigger: the doctor's dock #dockChatBtn, or
   a floating FAB on layouts without a dock (secretary). See CHAT_FEATURE_PLAN.md.
   Phase 2: reply/quote · emoji reactions picker · @patient / #appointment chips
   (autocomplete + clickable deep-links) · read receipts ✓✓ · inline edit. */
(function () {
  'use strict';
  if (window.__chatWidgetInit) return;
  window.__chatWidgetInit = true;

  var isAr = (document.documentElement.getAttribute('lang') === 'ar') ||
             (document.documentElement.getAttribute('dir') === 'rtl');
  var IS_DOCTOR = document.documentElement.getAttribute('data-layout') === 'doctor';
  var ROLE_BASE = IS_DOCTOR ? '/doctor' : '/secretary';
  function t(en, ar) { return isAr ? ar : en; }
  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function initials(name) {
    var p = String(name || '').trim().split(/\s+/).filter(Boolean);
    if (!p.length) return '؟';
    return ((p[0][0] || '') + (p.length > 1 ? (p[1][0] || '') : '')).toUpperCase();
  }
  function timeShort(iso) {
    if (!iso) return '';
    var d = new Date((iso + '').replace(' ', 'T'));
    if (isNaN(d)) return '';
    var diff = Math.max(0, (Date.now() - d.getTime()) / 1000);
    if (diff < 60) return t('now', 'الآن');
    if (diff < 3600) return Math.round(diff / 60) + (isAr ? ' د' : 'm');
    if (diff < 86400) return Math.round(diff / 3600) + (isAr ? ' س' : 'h');
    return d.toLocaleDateString(isAr ? 'ar-EG' : undefined, { day: 'numeric', month: 'short' });
  }
  // 28-bar decorative waveform (mirrors the board's comment-media audio player)
  function audioWave() {
    var bars = '';
    for (var i = 0; i < 28; i++) {
      var h = 24 + Math.round(Math.abs(Math.sin(i * 1.7) * Math.cos(i * 0.6)) * 72);
      bars += '<i style="height:' + h + '%"></i>';
    }
    return bars;
  }
  function fmtClock(s) { s = Math.max(0, Math.floor(s || 0)); return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2); }
  // localized conversation-list preview of the latest activity (reaction /
  // image / voice / file / text), respecting direction (you ↔ them) + language
  function previewText(L) {
    if (!L) return '';
    var mine = !!L.mine;
    if (L.type === 'reaction') {
      var e = esc(L.emoji || '👍');
      if (mine) return e + ' ' + t('You reacted', 'تفاعلتَ');
      return e + ' ' + (L.on_mine ? t('reacted to your message', 'تفاعل مع رسالتك') : t('reacted', 'تفاعل'));
    }
    if (L.type === 'image') return mine ? t('📷 You sent a photo', '📷 أرسلتَ صورة') : t('📷 Sent you a photo', '📷 أرسل لك صورة');
    if (L.type === 'audio') return mine ? t('🎤 You sent a voice message', '🎤 أرسلتَ تسجيلاً صوتياً') : t('🎤 Sent you a voice message', '🎤 أرسل لك تسجيلاً صوتياً');
    if (L.type === 'file') return mine ? t('📎 You sent a file', '📎 أرسلتَ ملفاً') : t('📎 Sent you a file', '📎 أرسل لك ملفاً');
    if (L.body != null) return (mine ? t('You: ', 'أنت: ') : '') + esc(stripTokens(L.body));
    return '';
  }
  function api(url, opts) {
    opts = opts || {};
    opts.headers = Object.assign({ 'X-Requested-With': 'XMLHttpRequest' }, opts.headers || {});
    if (opts.body && typeof opts.body !== 'string' && !(opts.body instanceof FormData)) {
      opts.headers['Content-Type'] = 'application/json';
      opts.body = JSON.stringify(opts.body);
    }
    return fetch(url, opts)
      .then(function (r) { return r.json().catch(function () { return {}; }); })
      .catch(function () { return {}; }); // network failure → empty (callers treat as no-op)
  }

  // @[label](p:ID) patient · #[label](appt:ID) appointment · #[label](date:Y-M-D)
  // → clickable, role-aware chips. Non-token text is HTML-escaped.
  var TOKEN_RE = /([@#])\[([^\]]{1,80})\]\((p|appt|date):([^)]{1,40})\)/g;
  function chipHtml(type, id, label) {
    var safeId = /^[0-9]+$/.test(id);
    if (type === 'p' && safeId) {
      return '<a class="chat-chip chat-chip-patient" href="' + ROLE_BASE + '/patients/' + id +
        '" target="_blank" rel="noopener"><i class="bi bi-person"></i>' + esc(label) + '</a>';
    }
    if (type === 'appt' && safeId) {
      var href = IS_DOCTOR ? (ROLE_BASE + '/appointments/' + id) : (ROLE_BASE + '/bookings/' + id);
      return '<a class="chat-chip chat-chip-appt" href="' + href +
        '" target="_blank" rel="noopener"><i class="bi bi-calendar-check"></i>' + esc(label) + '</a>';
    }
    if (type === 'date') {
      return '<span class="chat-chip chat-chip-date"><i class="bi bi-calendar-event"></i>' + esc(label) + '</span>';
    }
    return '<span class="chat-chip">' + esc(label) + '</span>';
  }
  function renderBody(text) {
    if (text == null) return '';
    var out = '', last = 0, m;
    TOKEN_RE.lastIndex = 0;
    while ((m = TOKEN_RE.exec(text)) !== null) {
      out += esc(text.slice(last, m.index));
      out += chipHtml(m[3], m[4], m[2]);
      last = TOKEN_RE.lastIndex;
    }
    out += esc(text.slice(last));
    return out;
  }

  var S = { me: 0, convos: [], activeCid: null, view: 'list', cursor: 0, open: false,
            verTimer: null, threadTimer: null, lastConvRev: -1, pendingAtt: [],
            replyTo: null, editing: null, editText: '', readUpTo: 0, msgs: {}, order: [],
            lastUnread: null, lastReact: null, pins: [] };

  var REACTION_SET = ['👍', '❤️', '😂', '😮', '😢', '🙏'];

  // ---- DOM -------------------------------------------------------------
  var panel, body, head, reactPop, typeahead, badgeEls = [], glowEls = [];
  function setGlow(on) { glowEls.forEach(function (el) { if (el) el.classList.toggle('chat-glow', !!on); }); }
  function build() {
    panel = document.createElement('div');
    panel.className = 'chat-panel'; panel.id = 'chatPanel';
    panel.setAttribute('dir', isAr ? 'rtl' : 'ltr');
    panel.innerHTML =
      '<div class="chat-head" id="chatHead">' +
        '<button class="chat-icon-btn" id="chatBack" title="' + t('Back', 'رجوع') + '" style="display:none"><i class="bi ' + (isAr ? 'bi-arrow-right' : 'bi-arrow-left') + '"></i></button>' +
        '<div style="flex:1;min-width:0"><div class="chat-head-title" id="chatTitle">' + t('Chats', 'المحادثات') + '</div><div class="chat-head-sub" id="chatSub"></div></div>' +
        '<button class="chat-icon-btn" id="chatNew" title="' + t('New chat', 'محادثة جديدة') + '"><i class="bi bi-pencil-square"></i></button>' +
        '<button class="chat-icon-btn" id="chatSearch" title="' + t('Search', 'بحث') + '" style="display:none"><i class="bi bi-search"></i></button>' +
        '<button class="chat-icon-btn" id="chatMute" title="' + t('Mute', 'كتم') + '" style="display:none"><i class="bi bi-bell"></i></button>' +
        '<button class="chat-icon-btn" id="chatInfo" title="' + t('Group info', 'معلومات المجموعة') + '" style="display:none"><i class="bi bi-people"></i></button>' +
        '<button class="chat-icon-btn" id="chatClose" title="' + t('Close', 'إغلاق') + '"><i class="bi bi-x-lg"></i></button>' +
      '</div>' +
      '<div class="chat-pin-bar" id="chatPinBar" style="display:none"></div>' +
      '<div class="chat-body" id="chatBody"></div>' +
      '<div class="chat-typing" id="chatTyping"></div>' +
      '<div class="chat-reply-bar" id="chatReplyBar" style="display:none"></div>' +
      '<div class="chat-input-area" id="chatInputArea" style="display:none">' +
        '<div class="chat-att-preview" id="chatAttPreview" style="display:none"></div>' +
        '<div class="chat-input-row">' +
          '<input type="file" id="chatFile" hidden accept="image/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">' +
          '<button class="chat-attach" id="chatAttach" title="' + t('Attach', 'إرفاق') + '"><i class="bi bi-paperclip"></i></button>' +
          '<button class="chat-attach" id="chatCam" title="' + t('Camera', 'كاميرا') + '"><i class="bi bi-camera"></i></button>' +
          '<button class="chat-attach chat-mic" id="chatMic" title="' + t('Voice message', 'رسالة صوتية') + '"><i class="bi bi-mic"></i></button>' +
          '<textarea class="chat-input" id="chatInput" rows="1" placeholder="' + t('Message…', 'رسالة…') + '"></textarea>' +
          '<button class="chat-send" id="chatSend" disabled><i class="bi bi-send"></i></button>' +
        '</div>' +
      '</div>';
    document.body.appendChild(panel);
    body = panel.querySelector('#chatBody');
    head = panel.querySelector('#chatHead');

    // shared reaction popover + mention typeahead (positioned on demand)
    reactPop = document.createElement('div'); reactPop.className = 'chat-react-pop'; reactPop.style.display = 'none';
    reactPop.innerHTML = REACTION_SET.map(function (e) { return '<button data-emoji="' + e + '">' + e + '</button>'; }).join('');
    panel.appendChild(reactPop);
    reactPop.querySelectorAll('button').forEach(function (b) {
      b.onclick = function () { if (reactPop.dataset.mid) react(+reactPop.dataset.mid, b.dataset.emoji); hideReactPop(); };
    });
    typeahead = document.createElement('div'); typeahead.className = 'chat-typeahead'; typeahead.style.display = 'none';
    panel.appendChild(typeahead);

    panel.querySelector('#chatClose').onclick = close;
    panel.querySelector('#chatBack').onclick = showList;
    panel.querySelector('#chatNew').onclick = showRoster;
    panel.querySelector('#chatMute').onclick = toggleMuteCurrent;
    panel.querySelector('#chatInfo').onclick = function () { if (S.activeCid) showGroupInfo(S.activeCid); };
    panel.querySelector('#chatSearch').onclick = toggleSearch;
    var input = panel.querySelector('#chatInput'), send = panel.querySelector('#chatSend');
    input.addEventListener('input', function () {
      send.disabled = !input.value.trim() && !S.pendingAtt.length;
      input.style.height = 'auto'; input.style.height = Math.min(130, input.scrollHeight) + 'px';
      sendTyping('typing');
      checkTypeahead();
    });
    input.addEventListener('keydown', function (e) {
      if (typeahead.style.display !== 'none' && (e.key === 'Escape')) { e.preventDefault(); hideTA(); return; }
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); doSend(); }
    });
    input.addEventListener('blur', function () { setTimeout(hideTA, 180); });
    send.onclick = doSend;
    panel.querySelector('#chatAttach').onclick = function () { panel.querySelector('#chatFile').click(); };
    panel.querySelector('#chatFile').onchange = onPickFile;
    panel.querySelector('#chatMic').onclick = toggleRecording;
    panel.querySelector('#chatCam').onclick = openCamera;
    document.addEventListener('click', function (e) {
      if (!reactPop.contains(e.target) && !e.target.closest('[data-reactpop]')) hideReactPop();
    });
  }

  function setBadge(n) {
    badgeEls.forEach(function (b) {
      if (!b) return;
      if (n > 0) { b.textContent = n > 99 ? '99+' : n; b.hidden = false; b.style.display = ''; }
      else { b.hidden = true; b.style.display = 'none'; }
    });
  }

  // ---- views -----------------------------------------------------------
  function showList() {
    S.view = 'list'; S.activeCid = null; stopThreadPoll(); clearReply(); cancelEdit();
    panel.querySelector('#chatBack').style.display = 'none';
    panel.querySelector('#chatNew').style.display = '';
    panel.querySelector('#chatSearch').style.display = 'none';
    panel.querySelector('#chatMute').style.display = 'none';
    panel.querySelector('#chatInfo').style.display = 'none';
    panel.querySelector('#chatPinBar').style.display = 'none';
    panel.querySelector('#chatTitle').textContent = t('Chats', 'المحادثات');
    panel.querySelector('#chatSub').textContent = '';
    panel.querySelector('#chatInputArea').style.display = 'none';
    panel.querySelector('#chatTyping').textContent = '';
    renderList();
    loadConversations();
  }
  function renderList() {
    if (!S.convos.length) { body.innerHTML = '<div class="chat-empty">' + t('No conversations yet. Start one with the ✎ button.', 'لا محادثات بعد. ابدأ واحدة بزر ✎.') + '</div>'; return; }
    body.innerHTML = S.convos.map(function (c) {
      var av = c.avatar ? '<img class="chat-avatar" src="' + esc(c.avatar) + '">' : '<div class="chat-avatar">' + esc(initials(c.title)) + '</div>';
      var last = previewText(c.last);
      var un = c.unread > 0 ? '<span class="chat-conv-unread">' + (c.unread > 99 ? '99+' : c.unread) + '</span>' : '';
      return '<div class="chat-conv" data-cid="' + c.id + '">' + av +
        '<div class="chat-conv-main"><div class="chat-conv-name">' + esc(c.title) + (c.type === 'group' ? ' <i class="bi bi-people" style="font-size:.7rem"></i>' : '') + '</div><div class="chat-conv-last">' + last + '</div></div>' +
        '<div class="chat-conv-meta"><span class="chat-conv-time">' + timeShort(c.last_activity_at) + '</span>' + un + '</div></div>';
    }).join('');
    body.querySelectorAll('.chat-conv').forEach(function (el) {
      el.onclick = function () { openConversation(+el.dataset.cid); };
    });
  }
  // strip mention/appt tokens to their plain label for previews
  function stripTokens(text) {
    if (text == null) return '';
    TOKEN_RE.lastIndex = 0;
    return text.replace(TOKEN_RE, function (_, sig, label) { return sig + label; });
  }

  function showRoster() {
    S.view = 'roster'; clearReply(); cancelEdit();
    panel.querySelector('#chatBack').style.display = '';
    panel.querySelector('#chatNew').style.display = 'none';
    panel.querySelector('#chatSearch').style.display = 'none';
    panel.querySelector('#chatMute').style.display = 'none';
    panel.querySelector('#chatInfo').style.display = 'none';
    panel.querySelector('#chatPinBar').style.display = 'none';
    panel.querySelector('#chatTitle').textContent = t('New chat', 'محادثة جديدة');
    panel.querySelector('#chatSub').textContent = '';
    panel.querySelector('#chatInputArea').style.display = 'none';
    panel.querySelector('#chatBack').onclick = showList;
    body.innerHTML = '<div class="chat-empty">' + t('Loading…', 'جارٍ التحميل…') + '</div>';
    api('/api/chat/roster').then(function (d) {
      var us = (d && d.users) || [];
      if (!us.length) { body.innerHTML = '<div class="chat-empty">' + t('No one available to chat.', 'لا أحد متاح للمحادثة.') + '</div>'; return; }
      // only the doctor may create groups (per spec)
      var groupRow = IS_DOCTOR ? '<div class="chat-newgroup-row" id="chatNewGroupRow"><i class="bi bi-people-fill"></i> ' + t('New group', 'مجموعة جديدة') + '</div>' : '';
      body.innerHTML = groupRow + us.map(function (u) {
        var av = u.avatar ? '<img class="chat-avatar" src="' + esc(u.avatar) + '">' : '<div class="chat-avatar">' + esc(initials(u.name)) + '</div>';
        var role = u.role === 'doctor' ? t('Doctor', 'طبيب') : (u.role === 'secretary' ? t('Secretary', 'سكرتارية') : u.role);
        return '<div class="chat-roster-row" data-uid="' + u.id + '">' + av + '<div class="chat-conv-main"><div class="chat-conv-name">' + esc(u.name) + '</div><div class="chat-role-tag">' + esc(role) + '</div></div></div>';
      }).join('');
      if (IS_DOCTOR) { var gr = body.querySelector('#chatNewGroupRow'); if (gr) gr.onclick = function () { showGroupCompose(us); }; }
      body.querySelectorAll('.chat-roster-row').forEach(function (el) {
        el.onclick = function () {
          api('/api/chat/conversations', { method: 'POST', body: { user_id: +el.dataset.uid } }).then(function (r) {
            if (r && r.ok) { loadConversations(); openConversation(r.conversation_id); }
          });
        };
      });
    });
  }

  // group compose (doctor) — pick members + title -> create
  function showGroupCompose(users) {
    S.view = 'groupCompose'; clearReply(); cancelEdit();
    panel.querySelector('#chatTitle').textContent = t('New group', 'مجموعة جديدة');
    panel.querySelector('#chatBack').style.display = '';
    panel.querySelector('#chatBack').onclick = showRoster;
    panel.querySelector('#chatNew').style.display = 'none';
    panel.querySelector('#chatMute').style.display = 'none';
    panel.querySelector('#chatInfo').style.display = 'none';
    panel.querySelector('#chatInputArea').style.display = 'none';
    var rows = (users || []).map(function (u) {
      var av = u.avatar ? '<img class="chat-avatar" src="' + esc(u.avatar) + '">' : '<div class="chat-avatar">' + esc(initials(u.name)) + '</div>';
      return '<label class="chat-roster-row chat-gc-row"><input type="checkbox" class="chat-gc-check" value="' + u.id + '">' + av +
        '<div class="chat-conv-main"><div class="chat-conv-name">' + esc(u.name) + '</div></div></label>';
    }).join('');
    body.innerHTML = '<div class="chat-gc-titlebar"><input type="text" id="chatGcTitle" placeholder="' + t('Group name…', 'اسم المجموعة…') + '" maxlength="120"></div>' +
      '<div class="chat-gc-list">' + rows + '</div>' +
      '<div class="chat-gc-foot"><button class="chat-gc-create" id="chatGcCreate">' + t('Create group', 'إنشاء المجموعة') + '</button></div>';
    body.querySelector('#chatGcCreate').onclick = function () {
      var title = body.querySelector('#chatGcTitle').value.trim();
      var ids = [].slice.call(body.querySelectorAll('.chat-gc-check:checked')).map(function (c) { return +c.value; });
      if (!title) { body.querySelector('#chatGcTitle').focus(); return; }
      if (!ids.length) return;
      api('/api/chat/conversations', { method: 'POST', body: { type: 'group', title: title, member_ids: ids } }).then(function (r) {
        if (r && r.ok) loadConversations(function () { openConversation(r.conversation_id); });
      });
    };
  }

  // group info / management
  function showGroupInfo(cid) {
    var c = S.convos.find(function (x) { return x.id === cid; }); if (!c) return;
    S.view = 'groupInfo'; stopThreadPoll();
    panel.querySelector('#chatTitle').textContent = t('Group info', 'معلومات المجموعة');
    panel.querySelector('#chatSub').textContent = '';
    panel.querySelector('#chatBack').style.display = '';
    panel.querySelector('#chatBack').onclick = function () { openConversation(cid); };
    panel.querySelector('#chatNew').style.display = 'none';
    panel.querySelector('#chatSearch').style.display = 'none';
    panel.querySelector('#chatMute').style.display = 'none';
    panel.querySelector('#chatInfo').style.display = 'none';
    panel.querySelector('#chatPinBar').style.display = 'none';
    panel.querySelector('#chatInputArea').style.display = 'none';
    var admin = !!c.is_admin;
    var nameRow = admin
      ? '<div class="chat-gi-name"><input type="text" id="chatGiTitle" value="' + esc(c.title) + '" maxlength="120"><button id="chatGiRename">' + t('Rename', 'تسمية') + '</button></div>'
      : '<div class="chat-gi-name"><div class="chat-gi-title-static">' + esc(c.title) + '</div></div>';
    var memberRows = (c.members || []).map(function (m) {
      var av = m.avatar ? '<img class="chat-avatar" src="' + esc(m.avatar) + '">' : '<div class="chat-avatar">' + esc(initials(m.name)) + '</div>';
      var tag = m.group_role === 'admin' ? '<span class="chat-gi-admin">' + t('admin', 'مشرف') + '</span>' : '';
      var rm = (admin && m.id !== S.me) ? '<button class="chat-gi-remove" data-rm="' + m.id + '" title="' + t('Remove', 'إزالة') + '"><i class="bi bi-x-lg"></i></button>' : '';
      return '<div class="chat-roster-row">' + av + '<div class="chat-conv-main"><div class="chat-conv-name">' + esc(m.name) + ' ' + tag + '</div></div>' + rm + '</div>';
    }).join('');
    body.innerHTML = nameRow +
      (admin ? '<button class="chat-gi-action" id="chatGiAdd"><i class="bi bi-person-plus"></i> ' + t('Add member', 'إضافة عضو') + '</button>' : '') +
      '<div class="chat-gi-members-label">' + t('Members', 'الأعضاء') + ' (' + (c.members || []).length + ')</div>' +
      '<div class="chat-gi-members">' + memberRows + '</div>' +
      '<button class="chat-gi-action chat-gi-leave" id="chatGiLeave"><i class="bi bi-box-arrow-left"></i> ' + t('Leave group', 'مغادرة المجموعة') + '</button>';
    if (admin) {
      body.querySelector('#chatGiRename').onclick = function () {
        var title = body.querySelector('#chatGiTitle').value.trim(); if (!title) return;
        api('/api/chat/' + cid + '/group', { method: 'POST', body: { title: title } }).then(function (d) {
          if (d && d.ok) loadConversations(function () { showGroupInfo(cid); });
        });
      };
      body.querySelector('#chatGiAdd').onclick = function () { showAddMember(cid); };
      body.querySelectorAll('[data-rm]').forEach(function (b) {
        b.onclick = function () {
          api('/api/chat/' + cid + '/remove-member', { method: 'POST', body: { user_id: +b.dataset.rm } }).then(function (d) {
            if (d && d.ok) loadConversations(function () { showGroupInfo(cid); });
          });
        };
      });
    }
    body.querySelector('#chatGiLeave').onclick = function () {
      api('/api/chat/' + cid + '/leave', { method: 'POST' }).then(function (d) { if (d && d.ok) loadConversations(function () { showList(); }); });
    };
  }

  function showAddMember(cid) {
    var c = S.convos.find(function (x) { return x.id === cid; }); if (!c) return;
    var have = {}; (c.members || []).forEach(function (m) { have[m.id] = 1; });
    S.view = 'addMember';
    panel.querySelector('#chatTitle').textContent = t('Add member', 'إضافة عضو');
    panel.querySelector('#chatBack').onclick = function () { showGroupInfo(cid); };
    body.innerHTML = '<div class="chat-empty">' + t('Loading…', 'جارٍ التحميل…') + '</div>';
    api('/api/chat/roster').then(function (d) {
      var us = ((d && d.users) || []).filter(function (u) { return !have[u.id]; });
      if (!us.length) { body.innerHTML = '<div class="chat-empty">' + t('Everyone is already in.', 'الجميع مضاف بالفعل.') + '</div>'; return; }
      body.innerHTML = us.map(function (u) {
        var av = u.avatar ? '<img class="chat-avatar" src="' + esc(u.avatar) + '">' : '<div class="chat-avatar">' + esc(initials(u.name)) + '</div>';
        return '<div class="chat-roster-row" data-uid="' + u.id + '">' + av + '<div class="chat-conv-main"><div class="chat-conv-name">' + esc(u.name) + '</div></div></div>';
      }).join('');
      body.querySelectorAll('.chat-roster-row').forEach(function (el) {
        el.onclick = function () {
          api('/api/chat/' + cid + '/add-member', { method: 'POST', body: { user_id: +el.dataset.uid } }).then(function (r) {
            if (r && r.ok) loadConversations(function () { showGroupInfo(cid); });
          });
        };
      });
    });
  }

  function setMuteIcon(muted) {
    var b = panel.querySelector('#chatMute'); if (!b) return;
    b.querySelector('i').className = 'bi ' + (muted ? 'bi-bell-slash' : 'bi-bell');
    b.title = muted ? t('Unmute', 'إلغاء الكتم') : t('Mute', 'كتم');
  }
  function toggleMuteCurrent() {
    if (!S.activeCid) return;
    api('/api/chat/' + S.activeCid + '/mute', { method: 'PUT', body: {} }).then(function (d) {
      if (d && d.ok) { setMuteIcon(d.muted); var c = S.convos.find(function (x) { return x.id === S.activeCid; }); if (c) c.muted = d.muted; refreshBadge(); }
    });
  }

  function openConversation(cid) {
    var c = S.convos.find(function (x) { return x.id === cid; });
    S.view = 'thread'; S.activeCid = cid; S.cursor = 0; S.readUpTo = 0; clearReply(); cancelEdit();
    CUR_IS_GROUP = !!(c && c.type === 'group');
    panel.querySelector('#chatBack').style.display = '';
    panel.querySelector('#chatBack').onclick = showList;
    panel.querySelector('#chatNew').style.display = 'none';
    panel.querySelector('#chatSearch').style.display = '';
    panel.querySelector('#chatMute').style.display = '';
    setMuteIcon(c && c.muted);
    panel.querySelector('#chatInfo').style.display = CUR_IS_GROUP ? '' : 'none';
    panel.querySelector('#chatTitle').textContent = c ? c.title : t('Chat', 'محادثة');
    panel.querySelector('#chatSub').textContent = c && c.type === 'group' ? (c.members.length + ' ' + t('members', 'أعضاء')) : '';
    panel.querySelector('#chatInputArea').style.display = 'flex';
    S.msgs = {}; S.order = []; S.pins = []; renderPinBar();
    body.innerHTML = '<div class="chat-messages" id="chatMessages"></div>';
    api('/api/chat/' + cid + '/messages').then(function (d) {
      if (!d) return;
      if (d.ok === false) { showList(); return; }
      S.cursor = d.cursor || 0;
      S.readUpTo = d.read_up_to || 0;
      (d.messages || []).forEach(upsertMsg);
      renderThread(true);
      markRead();
      startThreadPoll();
    });
    loadPins();
  }

  function upsertMsg(m) {
    if (m.deleted) { if (S.msgs[m.id]) { delete S.msgs[m.id]; S.order = S.order.filter(function (i) { return i !== m.id; }); } return; }
    if (!S.msgs[m.id]) S.order.push(m.id);
    S.msgs[m.id] = m;
  }
  function renderThread(scroll) {
    var box = panel.querySelector('#chatMessages'); if (!box) return;
    S.order.sort(function (a, b) { return a - b; });
    box.innerHTML = S.order.map(function (id) { return msgHtml(S.msgs[id]); }).join('');
    // existing-reaction chips toggle; quick pickers; reply / edit / delete; quote jump
    box.querySelectorAll('[data-react]').forEach(function (b) { b.onclick = function () { react(+b.dataset.mid, b.dataset.react); }; });
    box.querySelectorAll('[data-reactpop]').forEach(function (b) { b.onclick = function (e) { e.stopPropagation(); openReactPop(+b.dataset.reactpop, b); }; });
    box.querySelectorAll('[data-reply]').forEach(function (b) { b.onclick = function () { startReply(+b.dataset.reply); }; });
    box.querySelectorAll('[data-pin]').forEach(function (b) { b.onclick = function () { togglePin(+b.dataset.pin); }; });
    box.querySelectorAll('[data-forward]').forEach(function (b) { b.onclick = function () { showForwardPicker(+b.dataset.forward); }; });
    box.querySelectorAll('[data-edit]').forEach(function (b) { b.onclick = function () { startEdit(+b.dataset.edit); }; });
    box.querySelectorAll('[data-del]').forEach(function (b) { b.onclick = function () { delMsg(+b.dataset.del); }; });
    box.querySelectorAll('[data-goto]').forEach(function (b) { b.onclick = function () { gotoMsg(+b.dataset.goto); }; });
    box.querySelectorAll('[data-editsave]').forEach(function (b) { b.onclick = function () { saveEdit(+b.dataset.editsave); }; });
    box.querySelectorAll('[data-editcancel]').forEach(function (b) { b.onclick = function () { cancelEdit(); renderThread(false); }; });
    box.querySelectorAll('.chat-msg-att img').forEach(function (im) { im.style.cursor = 'zoom-in'; im.onclick = function () { openImage(im.getAttribute('data-img') || im.src); }; });
    box.querySelectorAll('.chat-audio').forEach(wireAudio);
    // double-click (desktop) or double-tap (touch) on a bubble → quote-reply
    box.querySelectorAll('.chat-msg').forEach(function (el) {
      var mid = +String(el.id).replace('msg-', ''); if (!mid) return;
      var skip = function (e) { return e.target.closest && e.target.closest('a,button,audio,input,textarea,.chat-audio-track,.chat-react,.chat-msg-actions'); };
      el.addEventListener('dblclick', function (e) { if (skip(e)) return; startReply(mid); });
      var lastTap = 0;
      el.addEventListener('touchend', function (e) {
        if (skip(e)) return;
        var now = Date.now();
        if (now - lastTap < 320) { e.preventDefault(); startReply(mid); lastTap = 0; } else { lastTap = now; }
      });
    });
    if (S.editing) {
      var ed = box.querySelector('.chat-edit-input');
      if (ed) { ed.oninput = function () { S.editText = ed.value; }; ed.focus(); ed.setSelectionRange(ed.value.length, ed.value.length); }
    }
    if (scroll) box.scrollTop = box.scrollHeight + 9999;
    else { var nearBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 120; if (nearBottom) box.scrollTop = box.scrollHeight + 9999; }
  }
  function msgHtml(m) {
    var mine = m.sender_id === S.me;
    if (S.editing === m.id) {
      return '<div class="chat-msg ' + (mine ? 'mine' : 'theirs') + '" id="msg-' + m.id + '">' +
        '<div class="chat-edit"><textarea class="chat-edit-input" rows="1">' + esc(S.editText) + '</textarea>' +
        '<div class="chat-edit-actions"><button class="chat-edit-cancel" data-editcancel="1">' + t('Cancel', 'إلغاء') + '</button>' +
        '<button class="chat-edit-save" data-editsave="' + m.id + '">' + t('Save', 'حفظ') + '</button></div></div></div>';
    }
    var atts = (m.attachments || []).map(function (a) {
      if (a.kind === 'image') return '<div class="chat-msg-att"><img src="' + esc(a.url) + '" alt="" data-img="' + esc(a.url) + '"></div>';
      if (a.kind === 'audio') {
        var w = audioWave();
        return '<div class="chat-msg-att"><div class="chat-audio" style="--p:0">' +
          '<audio preload="metadata" src="' + esc(a.url) + '"></audio>' +
          '<button type="button" class="chat-audio-play" aria-label="Play"><i class="bi bi-play-fill"></i></button>' +
          '<div class="chat-audio-track" role="slider"><div class="chat-audio-wave">' + w + '</div><div class="chat-audio-wave chat-audio-wave--fill">' + w + '</div></div>' +
          '<span class="chat-audio-time">0:00</span></div></div>';
      }
      return '<div class="chat-msg-att"><a class="chat-file" href="' + esc(a.url) + '" target="_blank" rel="noopener"><i class="bi bi-file-earmark"></i>' + esc(a.name || 'file') + '</a></div>';
    }).join('');
    var reacts = (m.reactions || []).map(function (r) {
      var on = (r.users || []).indexOf(S.me) !== -1 ? ' on' : '';
      return '<span class="chat-react' + on + '" data-react="' + esc(r.emoji) + '" data-mid="' + m.id + '">' + esc(r.emoji) + ' ' + r.count + '</span>';
    }).join('');
    var actions = '<span class="chat-msg-actions">' +
      '<button data-reply="' + m.id + '" title="' + t('Reply', 'رد') + '"><i class="bi bi-reply"></i></button>' +
      '<button data-reactpop="' + m.id + '" title="' + t('React', 'تفاعل') + '"><i class="bi bi-emoji-smile"></i></button>' +
      '<button data-pin="' + m.id + '" title="' + t('Pin', 'تثبيت') + '"><i class="bi ' + (m.pinned ? 'bi-pin-angle-fill' : 'bi-pin-angle') + '"></i></button>' +
      '<button data-forward="' + m.id + '" title="' + t('Forward', 'توجيه') + '"><i class="bi bi-forward"></i></button>' +
      (mine && IS_DOCTOR ? '<button data-edit="' + m.id + '" title="' + t('Edit', 'تعديل') + '"><i class="bi bi-pencil"></i></button>' : '') +
      (mine && IS_DOCTOR ? '<button data-del="' + m.id + '" title="' + t('Delete', 'حذف') + '"><i class="bi bi-trash"></i></button>' : '') + '</span>';
    var sender = (!mine && CUR_IS_GROUP) ? '<div class="chat-msg-sender">' + esc(m.sender_name) + '</div>' : '';
    var quote = '';
    if (m.reply_preview) {
      var rp = m.reply_preview;
      var qtext = rp.deleted ? t('message removed', 'رسالة محذوفة') : esc(stripTokens(rp.snippet || ''));
      quote = '<div class="chat-quote" data-goto="' + (m.reply_to_id || 0) + '">' +
        '<span class="chat-quote-name">' + esc(rp.sender_name) + '</span>' +
        '<span class="chat-quote-text">' + qtext + '</span></div>';
    }
    var bodyHtml = m.body != null ? renderBody(m.body) : '';
    var tick = mine ? '<span class="chat-tick' + (m.id <= S.readUpTo ? ' read' : '') + '">' + (m.id <= S.readUpTo ? '✓✓' : '✓') + '</span>' : '';
    var pinMark = m.pinned ? '<i class="bi bi-pin-angle-fill chat-pin-mark" title="' + t('Pinned', 'مثبّتة') + '"></i>' : '';
    return '<div class="chat-msg ' + (mine ? 'mine' : 'theirs') + '" id="msg-' + m.id + '">' + sender +
      '<div class="chat-bubble">' + quote + bodyHtml + atts + '</div>' +
      '<div class="chat-msg-foot">' + (mine ? actions : '') + pinMark +
      '<span class="chat-msg-time">' + timeShort(m.created_at) + '</span>' + tick + (!mine ? actions : '') + '</div>' +
      (reacts ? '<div class="chat-msg-reacts">' + reacts + '</div>' : '') + '</div>';
  }
  var CUR_IS_GROUP = false;

  // open an image in the system lightbox modal (zoom/reset); fall back to a tab
  function openImage(url) {
    if (window.ImageViewerModal && window.ImageViewerModal.show) window.ImageViewerModal.show({ imageUrl: url, title: t('Image', 'صورة') });
    else window.open(url, '_blank');
  }
  // wire one board-style .chat-audio player (play/pause/seek + progress)
  function wireAudio(el) {
    if (!el || el.dataset.wired) return; el.dataset.wired = '1';
    var audio = el.querySelector('audio'), playBtn = el.querySelector('.chat-audio-play'),
        track = el.querySelector('.chat-audio-track'), timeEl = el.querySelector('.chat-audio-time');
    if (!audio || !playBtn) return;
    audio.addEventListener('error', function () { // unsupported codec (e.g. webm/opus on Safari) → download link
      el.innerHTML = '<a class="chat-file" href="' + esc(audio.src) + '" target="_blank" rel="noopener"><i class="bi bi-download"></i> ' + t('Download voice', 'تنزيل الصوت') + '</a>';
    });
    audio.addEventListener('loadedmetadata', function () { if (isFinite(audio.duration)) timeEl.textContent = fmtClock(audio.duration); });
    playBtn.onclick = function () { if (audio.paused) audio.play(); else audio.pause(); };
    audio.addEventListener('play', function () { el.classList.add('is-playing'); playBtn.querySelector('i').className = 'bi bi-pause-fill'; });
    audio.addEventListener('pause', function () { el.classList.remove('is-playing'); playBtn.querySelector('i').className = 'bi bi-play-fill'; });
    audio.addEventListener('timeupdate', function () { var d = audio.duration || 0; el.style.setProperty('--p', d ? (audio.currentTime / d) : 0); timeEl.textContent = fmtClock(audio.currentTime); });
    audio.addEventListener('ended', function () { el.classList.remove('is-playing'); playBtn.querySelector('i').className = 'bi bi-play-fill'; el.style.setProperty('--p', 0); timeEl.textContent = fmtClock(audio.duration); });
    if (track) track.onclick = function (e) { var r = track.getBoundingClientRect(); var ratio = (e.clientX - r.left) / r.width; if (audio.duration) audio.currentTime = Math.max(0, Math.min(audio.duration, ratio * audio.duration)); };
  }

  // ---- reply / quote ---------------------------------------------------
  function startReply(mid) {
    var m = S.msgs[mid]; if (!m) return;
    S.replyTo = { id: mid, name: m.sender_name || '', snippet: (m.body != null ? stripTokens(m.body) : '📎') };
    var bar = panel.querySelector('#chatReplyBar'); if (!bar) return;
    bar.innerHTML = '<div class="chat-reply-info"><span class="chat-reply-name"><i class="bi bi-reply"></i> ' + esc(S.replyTo.name) + '</span>' +
      '<span class="chat-reply-text">' + esc((S.replyTo.snippet || '').slice(0, 90)) + '</span></div>' +
      '<button class="chat-reply-x" title="' + t('Cancel', 'إلغاء') + '"><i class="bi bi-x-lg"></i></button>';
    bar.style.display = 'flex';
    bar.querySelector('.chat-reply-x').onclick = clearReply;
    var input = panel.querySelector('#chatInput'); if (input) input.focus();
  }
  function clearReply() {
    S.replyTo = null;
    if (panel) { var bar = panel.querySelector('#chatReplyBar'); if (bar) { bar.style.display = 'none'; bar.innerHTML = ''; } }
  }
  function gotoMsg(mid) {
    var el = panel.querySelector('#msg-' + mid);
    if (el) { el.scrollIntoView({ block: 'center', behavior: 'smooth' }); el.classList.add('chat-msg-flash'); setTimeout(function () { el.classList.remove('chat-msg-flash'); }, 1200); }
  }

  // ---- pin -------------------------------------------------------------
  function togglePin(mid) {
    api('/api/chat/messages/' + mid + '/pin', { method: 'POST' }).then(function (d) { if (d && d.ok) { pollThread(); loadPins(); } });
  }
  function loadPins() {
    if (!S.activeCid) return;
    var cid = S.activeCid;
    api('/api/chat/' + cid + '/pins').then(function (d) {
      if (!d || !d.ok || S.activeCid !== cid) return;
      S.pins = d.pins || [];
      renderPinBar();
    });
  }
  function renderPinBar() {
    var bar = panel.querySelector('#chatPinBar'); if (!bar) return;
    if (S.view !== 'thread' || !S.pins || !S.pins.length) { bar.style.display = 'none'; bar.innerHTML = ''; return; }
    var latest = S.pins[0];
    var snip = latest.body != null ? stripTokens(latest.body) : '📎';
    bar.style.display = 'flex';
    bar.innerHTML = '<i class="bi bi-pin-angle-fill"></i><span class="chat-pin-text">' + esc(snip.slice(0, 80)) + '</span>' +
      (S.pins.length > 1 ? '<span class="chat-pin-count">' + S.pins.length + '</span>' : '');
    bar.onclick = function () { gotoMsg(latest.id); };
  }

  // ---- in-conversation search -----------------------------------------
  function toggleSearch() {
    if (S.view === 'search') { openConversation(S.activeCid); return; }
    var cid = S.activeCid; if (!cid) return;
    S.view = 'search'; stopThreadPoll();
    panel.querySelector('#chatPinBar').style.display = 'none';
    panel.querySelector('#chatInputArea').style.display = 'none';
    body.innerHTML = '<div class="chat-search-bar"><input type="text" id="chatSearchInput" placeholder="' + t('Search in chat…', 'ابحث في المحادثة…') + '"><button id="chatSearchClose" title="' + t('Close', 'إغلاق') + '"><i class="bi bi-x-lg"></i></button></div>' +
      '<div class="chat-search-results" id="chatSearchResults"></div>';
    var inp = body.querySelector('#chatSearchInput'); inp.focus();
    body.querySelector('#chatSearchClose').onclick = function () { openConversation(cid); };
    var timer = 0;
    inp.addEventListener('input', function () {
      clearTimeout(timer); var q = inp.value.trim(); var rb = body.querySelector('#chatSearchResults');
      if (q.length < 2) { rb.innerHTML = ''; return; }
      timer = setTimeout(function () {
        api('/api/chat/' + cid + '/search?q=' + encodeURIComponent(q)).then(function (d) {
          if (!d || !d.ok || S.view !== 'search') return;
          var res = d.results || [];
          rb.innerHTML = res.length ? res.map(function (m) {
            return '<div class="chat-search-item" data-mid="' + m.id + '"><div class="chat-search-who">' + esc(m.sender_name) + ' · ' + timeShort(m.created_at) + '</div>' +
              '<div class="chat-search-snip">' + esc(stripTokens(m.body || '📎').slice(0, 110)) + '</div></div>';
          }).join('') : '<div class="chat-empty">' + t('No matches.', 'لا نتائج.') + '</div>';
          rb.querySelectorAll('.chat-search-item').forEach(function (el) {
            el.onclick = function () { var mid = +el.dataset.mid; openConversation(cid); setTimeout(function () { gotoMsg(mid); }, 650); };
          });
        });
      }, 250);
    });
  }

  // ---- forward ---------------------------------------------------------
  function showForwardPicker(mid) {
    var srcCid = S.activeCid; S.view = 'forward'; stopThreadPoll();
    panel.querySelector('#chatTitle').textContent = t('Forward to…', 'توجيه إلى…');
    panel.querySelector('#chatSub').textContent = '';
    panel.querySelector('#chatBack').style.display = '';
    panel.querySelector('#chatBack').onclick = function () { openConversation(srcCid); };
    panel.querySelector('#chatSearch').style.display = 'none';
    panel.querySelector('#chatMute').style.display = 'none';
    panel.querySelector('#chatInfo').style.display = 'none';
    panel.querySelector('#chatPinBar').style.display = 'none';
    panel.querySelector('#chatInputArea').style.display = 'none';
    var rows = (S.convos || []).map(function (c) {
      var av = c.avatar ? '<img class="chat-avatar" src="' + esc(c.avatar) + '">' : '<div class="chat-avatar">' + esc(initials(c.title)) + '</div>';
      var icon = c.type === 'group' ? ' <i class="bi bi-people" style="font-size:.7rem"></i>' : '';
      return '<div class="chat-roster-row" data-fwd="' + c.id + '">' + av + '<div class="chat-conv-main"><div class="chat-conv-name">' + esc(c.title) + icon + '</div></div></div>';
    }).join('');
    body.innerHTML = rows || '<div class="chat-empty">' + t('No conversations.', 'لا محادثات.') + '</div>';
    body.querySelectorAll('[data-fwd]').forEach(function (el) {
      el.onclick = function () {
        var tcid = +el.dataset.fwd;
        api('/api/chat/' + tcid + '/forward', { method: 'POST', body: { message_id: mid } }).then(function (r) {
          if (r && r.ok) loadConversations(function () { openConversation(tcid); });
        });
      };
    });
  }

  // ---- edit ------------------------------------------------------------
  function startEdit(mid) {
    var m = S.msgs[mid]; if (!m) return;
    S.editing = mid; S.editText = (m.body != null ? m.body : ''); hideReactPop();
    renderThread(false);
  }
  function cancelEdit() { S.editing = null; S.editText = ''; }
  function saveEdit(mid) {
    var txt = (S.editText || '').trim();
    if (!txt) { cancelEdit(); renderThread(false); return; }
    api('/api/chat/messages/' + mid, { method: 'PATCH', body: { body: txt } }).then(function (d) {
      cancelEdit();
      if (d && d.ok) pollThread(); else renderThread(false);
    });
  }

  // ---- reactions picker ------------------------------------------------
  function openReactPop(mid, anchor) {
    hideReactPop();
    reactPop.dataset.mid = mid;
    reactPop.style.display = 'flex';
    var pr = panel.getBoundingClientRect(), ar = anchor.getBoundingClientRect();
    var left = ar.left - pr.left + ar.width / 2 - reactPop.offsetWidth / 2;
    left = Math.max(8, Math.min(left, panel.clientWidth - reactPop.offsetWidth - 8));
    reactPop.style.left = left + 'px';
    // above the button, but flip below if it would clip the panel top, then
    // clamp so it never spills past the panel bottom (overflow:hidden clips both).
    var top = ar.top - pr.top - reactPop.offsetHeight - 6;
    if (top < 4) top = ar.bottom - pr.top + 6;
    if (top + reactPop.offsetHeight > panel.clientHeight - 4) top = Math.max(4, panel.clientHeight - reactPop.offsetHeight - 4);
    reactPop.style.top = top + 'px';
  }
  function hideReactPop() { if (reactPop) { reactPop.style.display = 'none'; delete reactPop.dataset.mid; } }

  // ---- @ / # autocomplete (race-safe: the token's trigger + offsets are
  //      captured per request, so a stale/late response can never render the
  //      wrong type or insert at the wrong place) -------------------------
  var TA = { trigger: '', timer: 0 };
  function checkTypeahead() {
    var ta = panel.querySelector('#chatInput'); if (!ta) return hideTA();
    var pos = ta.selectionStart, before = ta.value.slice(0, pos);
    var m = before.match(/(^|\s)([@#])([^\s@#\[\]()]{2,40})$/);
    if (!m) return hideTA();
    var trigger = m[2], q = m[3], start = pos - q.length - 1, end = pos;
    TA.trigger = trigger;
    var url = trigger === '@' ? '/api/patients/search?q=' : '/api/appointments/search?q=';
    clearTimeout(TA.timer);
    TA.timer = setTimeout(function () {
      api(url + encodeURIComponent(q)).then(function (d) {
        // drop a stale response: only render if the caret still sits on the
        // exact token (same trigger+text at the same offsets) we queried for.
        var cur = panel.querySelector('#chatInput');
        if (!cur || cur.selectionStart !== end || cur.value.slice(start, end) !== (trigger + q)) return;
        renderTA(trigger, (d && d.data) || [], start, end);
      });
    }, 250);
  }
  function renderTA(trigger, items, start, end) {
    if (!items.length) return hideTA();
    typeahead.innerHTML = items.slice(0, 8).map(function (it, i) {
      if (trigger === '@') {
        var name = (it.full_name || ((it.first_name || '') + ' ' + (it.last_name || ''))).trim() || ('#' + it.id);
        var sub = it.phone ? esc(it.phone) : '';
        return '<div class="chat-ta-item" data-i="' + i + '"><i class="bi bi-person"></i><span class="chat-ta-name">' + esc(name) + '</span><span class="chat-ta-sub">' + sub + '</span></div>';
      }
      var label = 'Appt #' + esc(String(it.id)) + (it.patient_name ? ' · ' + esc(it.patient_name) : '');
      var sub2 = (it.date ? esc(it.date) : '') + (it.start_time ? ' ' + esc((it.start_time + '').slice(0, 5)) : '');
      return '<div class="chat-ta-item" data-i="' + i + '"><i class="bi bi-calendar-check"></i><span class="chat-ta-name">' + label + '</span><span class="chat-ta-sub">' + sub2 + '</span></div>';
    }).join('');
    typeahead.style.display = 'block';
    var area = panel.querySelector('#chatInputArea'); if (!area) return hideTA();
    var pr = panel.getBoundingClientRect(), arr = area.getBoundingClientRect();
    typeahead.style.left = (arr.left - pr.left + 8) + 'px';
    typeahead.style.width = (arr.width - 16) + 'px';
    typeahead.style.top = (arr.top - pr.top - typeahead.offsetHeight - 6) + 'px';
    typeahead.querySelectorAll('.chat-ta-item').forEach(function (el) {
      el.onmousedown = function (e) { e.preventDefault(); pickTA(trigger, items[+el.dataset.i], start, end); };
    });
  }
  function pickTA(trigger, it, start, end) {
    var ta = panel.querySelector('#chatInput'); if (!ta || !it) return hideTA();
    var clean = function (s) { return String(s).replace(/[\[\]()]/g, ' ').trim(); }, token;
    if (trigger === '@') {
      var name = clean(it.full_name || ((it.first_name || '') + ' ' + (it.last_name || '')) || ('#' + it.id));
      token = '@[' + name + '](p:' + it.id + ')';
    } else {
      var label = clean('Appt #' + it.id + (it.date ? ' · ' + it.date : ''));
      token = '#[' + label + '](appt:' + it.id + ')';
    }
    var val = ta.value;
    ta.value = val.slice(0, start) + token + ' ' + val.slice(end);
    var np = start + token.length + 1;
    ta.focus(); ta.setSelectionRange(np, np);
    ta.style.height = 'auto'; ta.style.height = Math.min(130, ta.scrollHeight) + 'px';
    panel.querySelector('#chatSend').disabled = false;
    hideTA();
  }
  function hideTA() { if (typeahead) typeahead.style.display = 'none'; clearTimeout(TA.timer); }

  // ---- send / attach ---------------------------------------------------
  function doSend() {
    var input = panel.querySelector('#chatInput');
    var txt = input.value.trim();
    if (!txt && !S.pendingAtt.length) return;
    var attIds = S.pendingAtt.map(function (a) { return a.id; });
    var payload = { body: txt, attachment_ids: attIds };
    if (S.replyTo) payload.reply_to_id = S.replyTo.id;
    api('/api/chat/' + S.activeCid + '/messages', { method: 'POST', body: payload }).then(function (d) {
      if (d && d.ok && d.message) { upsertMsg(d.message); S.cursor = Math.max(S.cursor, d.cursor || 0); renderThread(true); }
    });
    input.value = ''; input.style.height = 'auto'; panel.querySelector('#chatSend').disabled = true;
    S.pendingAtt = []; renderAttPreview(); clearReply(); hideTA();
  }
  function onPickFile(e) {
    var f = e.target.files && e.target.files[0]; e.target.value = '';
    if (!f) return;
    sendTyping(f.type.indexOf('image') === 0 ? 'image' : (f.type.indexOf('audio') === 0 ? 'voice' : 'file'));
    uploadBlob(f, f.name);
  }
  function uploadBlob(blob, filename) {
    var fd = new FormData(); fd.append('file', blob, filename || 'file');
    panel.querySelector('#chatSub').textContent = t('Uploading…', 'جارٍ الرفع…');
    return api('/api/chat/attachments', { method: 'POST', body: fd }).then(function (d) {
      panel.querySelector('#chatSub').textContent = '';
      if (d && d.ok && d.attachment) { S.pendingAtt.push(d.attachment); panel.querySelector('#chatSend').disabled = false; renderAttPreview(); }
      else { panel.querySelector('#chatSub').textContent = t('Upload failed', 'فشل الرفع'); }
      return d;
    });
  }
  // pending-attachment preview strip (image thumbnails / audio + file chips)
  function renderAttPreview() {
    var box = panel.querySelector('#chatAttPreview'); if (!box) return;
    if (!S.pendingAtt.length) { box.style.display = 'none'; box.innerHTML = ''; return; }
    box.style.display = 'flex';
    box.innerHTML = S.pendingAtt.map(function (a, i) {
      var inner;
      if (a.kind === 'image') inner = '<img src="' + esc(a.url) + '" alt="">';
      else if (a.kind === 'audio') inner = '<span class="chat-att-ic"><i class="bi bi-mic-fill"></i></span><span class="chat-att-name">' + t('voice', 'صوت') + '</span>';
      else inner = '<span class="chat-att-ic"><i class="bi bi-file-earmark"></i></span><span class="chat-att-name">' + esc((a.name || 'file').slice(0, 14)) + '</span>';
      return '<div class="chat-att-thumb' + (a.kind === 'image' ? ' is-img' : '') + '" data-rm="' + i + '">' + inner +
        '<button class="chat-att-x" title="' + t('Remove', 'إزالة') + '"><i class="bi bi-x"></i></button></div>';
    }).join('');
    box.querySelectorAll('.chat-att-x').forEach(function (b) {
      b.onclick = function () {
        var i = +b.parentNode.dataset.rm; S.pendingAtt.splice(i, 1);
        if (!S.pendingAtt.length && !panel.querySelector('#chatInput').value.trim()) panel.querySelector('#chatSend').disabled = true;
        renderAttPreview();
      };
    });
  }

  // ---- voice recording (MediaRecorder) — the mic button BECOMES the control
  //      (board style): tap to record (red pulsing pill + timer), tap to stop →
  //      the clip lands as a pending attachment you can send or remove (×). ----
  var rec = { mr: null, chunks: [], stream: null, timer: 0, start: 0, starting: false };
  function micBtn() { return panel.querySelector('#chatMic'); }
  function setMicRecording(on, label) {
    var b = micBtn(); if (!b) return;
    if (on) { b.classList.add('recording'); b.innerHTML = '<i class="bi bi-stop-fill"></i><span class="chat-mic-time">' + (label || '0:00') + '</span>'; }
    else { b.classList.remove('recording'); b.innerHTML = '<i class="bi bi-mic"></i>'; }
  }
  function toggleRecording() {
    if (rec.mr && rec.mr.state === 'recording') { stopRecording(); return; }
    if (rec.starting) return; // guard against a double-tap before getUserMedia resolves
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || !window.MediaRecorder) {
      panel.querySelector('#chatSub').textContent = t('Voice not supported here', 'التسجيل غير مدعوم'); return;
    }
    rec.starting = true;
    navigator.mediaDevices.getUserMedia({ audio: true }).then(function (stream) {
      rec.starting = false; rec.stream = stream; rec.chunks = [];
      var mime = window.MediaRecorder.isTypeSupported && MediaRecorder.isTypeSupported('audio/webm') ? 'audio/webm' : '';
      rec.mr = mime ? new MediaRecorder(stream, { mimeType: mime }) : new MediaRecorder(stream);
      rec.mr.ondataavailable = function (e) { if (e.data && e.data.size) rec.chunks.push(e.data); };
      rec.mr.onstop = function () {
        clearInterval(rec.timer); stopRecStream(); setMicRecording(false);
        if (!rec.chunks.length) { rec.chunks = []; return; }
        var type = rec.mr.mimeType || 'audio/webm';
        var blob = new Blob(rec.chunks, { type: type }); rec.chunks = [];
        var ext = type.indexOf('ogg') >= 0 ? 'ogg' : (type.indexOf('mp4') >= 0 ? 'm4a' : (type.indexOf('mpeg') >= 0 ? 'mp3' : 'webm'));
        uploadBlob(blob, 'voice_' + Date.now() + '.' + ext);
      };
      rec.mr.start();
      rec.start = Date.now();
      setMicRecording(true, '0:00');
      sendTyping('voice');
      rec.timer = setInterval(function () {
        var s = Math.floor((Date.now() - rec.start) / 1000);
        setMicRecording(true, fmtClock(s));
        sendTyping('voice');
        if (s >= 120) stopRecording(); // 2-minute cap
      }, 250);
    }).catch(function () { rec.starting = false; panel.querySelector('#chatSub').textContent = t('Microphone denied', 'رُفض الميكروفون'); });
  }
  function stopRecording() { clearInterval(rec.timer); if (rec.mr && rec.mr.state === 'recording') { rec.mr.stop(); } else { stopRecStream(); setMicRecording(false); } }
  function stopRecStream() { if (rec.stream) { rec.stream.getTracks().forEach(function (tr) { tr.stop(); }); rec.stream = null; } }

  // ---- camera capture (live getUserMedia modal; mobile-native fallback) ----
  function openCamera() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      var fi = panel.querySelector('#chatFile'); fi.setAttribute('accept', 'image/*'); fi.setAttribute('capture', 'environment');
      fi.click(); setTimeout(function () { fi.removeAttribute('capture'); fi.setAttribute('accept', 'image/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt'); }, 1500);
      return;
    }
    var ov = document.createElement('div'); ov.className = 'chat-cam';
    ov.innerHTML = '<div class="chat-cam-box"><video autoplay playsinline muted></video>' +
      '<div class="chat-cam-actions"><button type="button" class="chat-cam-shot"><i class="bi bi-camera-fill"></i> ' + t('Capture', 'التقاط') + '</button>' +
      '<button type="button" class="chat-cam-cancel">' + t('Cancel', 'إلغاء') + '</button></div></div>';
    document.body.appendChild(ov);
    var video = ov.querySelector('video'), stream = null;
    function stop() { if (stream) stream.getTracks().forEach(function (tr) { tr.stop(); }); ov.remove(); }
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
      .then(function (s) { stream = s; video.srcObject = s; })
      .catch(function () { stop(); panel.querySelector('#chatSub').textContent = t('Camera unavailable', 'الكاميرا غير متاحة'); });
    ov.querySelector('.chat-cam-cancel').onclick = stop;
    ov.onclick = function (e) { if (e.target === ov) stop(); };
    ov.querySelector('.chat-cam-shot').onclick = function () {
      if (!video.videoWidth) return;
      var cv = document.createElement('canvas'); cv.width = video.videoWidth; cv.height = video.videoHeight;
      cv.getContext('2d').drawImage(video, 0, 0);
      cv.toBlob(function (blob) { if (blob) { sendTyping('image'); uploadBlob(blob, 'camera_' + Date.now() + '.jpg'); } stop(); }, 'image/jpeg', 0.9);
    };
  }
  function react(mid, emoji) {
    api('/api/chat/messages/' + mid + '/reactions', { method: 'POST', body: { emoji: emoji } }).then(function (d) {
      if (d && d.ok) pollThread();
    });
  }
  function delMsg(mid) {
    api('/api/chat/messages/' + mid, { method: 'DELETE' }).then(function (d) { if (d && d.ok) pollThread(); });
  }
  function markRead() {
    var ids = S.order; if (!ids.length) return;
    api('/api/chat/' + S.activeCid + '/read', { method: 'PUT', body: { up_to_message_id: ids[ids.length - 1] } }).then(refreshBadge);
  }
  var lastTyping = 0;
  function sendTyping(state) {
    if (!S.activeCid) return; var now = Date.now(); if (now - lastTyping < 2500) return; lastTyping = now;
    api('/api/chat/' + S.activeCid + '/typing', { method: 'POST', body: { state: state } });
  }

  // ---- polling ---------------------------------------------------------
  function pollThread() {
    if (!S.activeCid || document.hidden) return;
    api('/api/chat/' + S.activeCid + '/messages?after_rev=' + S.cursor).then(function (d) {
      if (!d) return;
      // conversation gone / no longer a participant (e.g. removed, or deleted) →
      // stop hammering it with 403s and drop back to the list.
      if (d.ok === false) { stopThreadPoll(); if (S.view === 'thread') showList(); return; }
      var hasNew = false, justSent = {};
      (d.messages || []).forEach(function (m) { upsertMsg(m); hasNew = true; if (!m.deleted) justSent[m.sender_id] = 1; });
      S.cursor = d.cursor || S.cursor;
      var readChanged = typeof d.read_up_to === 'number' && d.read_up_to !== S.readUpTo;
      if (readChanged) S.readUpTo = d.read_up_to;
      var tp = panel.querySelector('#chatTyping');
      // A "typing…/sending…" indicator must never linger AFTER the message lands:
      // drop any typer whose message just arrived in this same poll batch.
      var typers = (d.typing || []).filter(function (x) { return !justSent[x.user_id]; });
      if (tp) tp.innerHTML = typers.length ? typingLabel(typers[0]) : '';
      // New messages always re-render. A read-receipt-only tick re-renders too,
      // EXCEPT while the user is mid-edit (a full innerHTML rebuild would disrupt
      // the open editor — the tick syncs on the next message change). markRead
      // advances only on genuinely new messages, not on others' read receipts.
      if (hasNew || (readChanged && !S.editing)) renderThread(false);
      if (hasNew) markRead();
    });
  }
  function typingLabel(tinfo) {
    var who = esc(tinfo.name || '');
    var verb = tinfo.state === 'voice' ? t('is sending a voice message', 'يرسل رسالة صوتية')
      : tinfo.state === 'image' ? t('is sending an image', 'يرسل صورة')
      : tinfo.state === 'file' ? t('is sending a file', 'يرسل ملفاً')
      : t('is typing', 'يكتب');
    return who + ' ' + verb + '<span class="chat-typing-dots"><i></i><i></i><i></i></span>';
  }
  function startThreadPoll() { stopThreadPoll(); S.threadTimer = setInterval(pollThread, 4000); }
  function stopThreadPoll() { if (S.threadTimer) { clearInterval(S.threadTimer); S.threadTimer = null; } }

  function loadConversations(cb) {
    return api('/api/chat/conversations').then(function (d) {
      if (d && d.ok) { S.convos = d.conversations || []; if (S.view === 'list') renderList(); }
      if (typeof cb === 'function') cb();
    });
  }
  function refreshBadge() {
    api('/api/chat/version').then(function (d) {
      if (!d || !d.ok) return;
      S.me = d.me || S.me;
      var n = d.unread_total || 0;
      var increased = S.lastUnread != null && n > S.lastUnread;
      S.lastUnread = n;
      setBadge(n);
      // desktop notification when an unread arrives and the chat isn't being watched
      if (increased && (!S.open || document.hidden)) maybeDesktopNotify();
      // someone reacted to MY message → glow the chat button (reactions don't bump
      // unread_total). Glow only when the panel is closed; it clears on open().
      var rc = d.react_cursor || 0;
      if (S.lastReact != null && rc > S.lastReact && !S.open) setGlow(true);
      S.lastReact = rc;
      if (d.conversations_rev !== S.lastConvRev) {
        S.lastConvRev = d.conversations_rev;
        if (S.open && S.view === 'list') loadConversations();
        if (S.open && S.view === 'thread') {
          var c = S.convos.find(function (x) { return x.id === S.activeCid; });
          CUR_IS_GROUP = !!(c && c.type === 'group');
        }
      }
    });
  }
  // ---- desktop notifications ------------------------------------------
  function ensureChatNotifyPermission() {
    try { if ('Notification' in window && Notification.permission === 'default') Notification.requestPermission(); } catch (e) { /* */ }
  }
  function maybeDesktopNotify() {
    if (!('Notification' in window) || Notification.permission !== 'granted') return;
    api('/api/chat/conversations').then(function (d) {
      if (!d || !d.ok) return;
      S.convos = d.conversations || S.convos;
      var c = (S.convos || []).filter(function (x) { return x.unread > 0 && !x.muted; })[0];
      if (!c) return;
      try {
        var n = new Notification(c.title || t('New message', 'رسالة جديدة'), {
          body: (c.last && c.last.body != null) ? stripTokens(c.last.body).slice(0, 80) : '📎',
          tag: 'chat-' + c.id
        });
        n.onclick = function () { window.focus(); if (!S.open) open(); openConversation(c.id); n.close(); };
      } catch (e) { /* */ }
    });
  }
  function startVersionPoll() { if (S.verTimer) return; S.verTimer = setInterval(function () { if (!document.hidden) refreshBadge(); }, 12000); }

  // ---- open/close ------------------------------------------------------
  function open() {
    S.open = true; panel.classList.add('open');
    ensureChatNotifyPermission();
    setGlow(false);
    if (S.view === 'thread' && S.activeCid) { startThreadPoll(); pollThread(); }
    else showList();
  }
  function close() { S.open = false; panel.classList.remove('open'); stopThreadPoll(); hideReactPop(); hideTA(); }
  function toggle() { S.open ? close() : open(); }

  function boot() {
    build();
    var dockBtn = document.getElementById('dockChatBtn');
    if (dockBtn) {
      dockBtn.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); toggle(); });
      badgeEls.push(document.getElementById('chatUnreadBadge'));
      glowEls.push(dockBtn);
    } else {
      var isSecLayout = document.documentElement.getAttribute('data-layout') === 'secretary';
      var fab = document.createElement('button');
      fab.className = 'chat-fab' + (isSecLayout ? ' chat-fab--glass' : '');
      fab.id = 'chatFab';
      fab.setAttribute('aria-label', isSecLayout ? 'محادثة' : 'Chat');
      var iconHtml = (isSecLayout && window.__SEC_CHAT_FAB_ICON_HTML__)
        ? window.__SEC_CHAT_FAB_ICON_HTML__
        : '<i class="bi bi-chat-dots"></i>';
      fab.innerHTML = iconHtml + '<span class="chat-unread-badge" hidden>0</span>';
      fab.onclick = toggle;
      document.body.appendChild(fab);
      badgeEls.push(fab.querySelector('.chat-unread-badge'));
      glowEls.push(fab);
    }
    refreshBadge();
    startVersionPoll();
    document.addEventListener('visibilitychange', function () { if (!document.hidden && S.open) { refreshBadge(); pollThread(); } });
    // public hook — the notification center calls this to jump straight into a chat
    window.RoayaChat = {
      open: function () { if (!S.open) open(); },
      openConversation: function (cid) {
        cid = +cid; if (!cid) return;
        setGlow(false);
        if (!S.open) open();
        if (S.convos.find(function (x) { return x.id === cid; })) openConversation(cid);
        else loadConversations(function () { openConversation(cid); });
      }
    };
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
