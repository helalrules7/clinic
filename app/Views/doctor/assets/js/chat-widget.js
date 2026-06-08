/* Doctor↔Secretary chat widget (adaptive polling). Bilingual (ar secretary /
   en doctor), theme via CSS tokens. Trigger: the doctor's dock #dockChatBtn, or
   a floating FAB on layouts without a dock (secretary). See CHAT_FEATURE_PLAN.md. */
(function () {
  'use strict';
  if (window.__chatWidgetInit) return;
  window.__chatWidgetInit = true;

  var isAr = (document.documentElement.getAttribute('lang') === 'ar') ||
             (document.documentElement.getAttribute('dir') === 'rtl');
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
  function api(url, opts) {
    opts = opts || {};
    opts.headers = Object.assign({ 'X-Requested-With': 'XMLHttpRequest' }, opts.headers || {});
    if (opts.body && typeof opts.body !== 'string' && !(opts.body instanceof FormData)) {
      opts.headers['Content-Type'] = 'application/json';
      opts.body = JSON.stringify(opts.body);
    }
    return fetch(url, opts).then(function (r) { return r.json().catch(function () { return {}; }); });
  }

  var S = { me: 0, convos: [], activeCid: null, view: 'list', cursor: 0, open: false, badge: null,
            verTimer: null, threadTimer: null, lastConvRev: -1, typingTimer: 0, pendingAtt: [] };

  // ---- DOM -------------------------------------------------------------
  var panel, body, head, badgeEls = [];
  function build() {
    panel = document.createElement('div');
    panel.className = 'chat-panel'; panel.id = 'chatPanel';
    panel.setAttribute('dir', isAr ? 'rtl' : 'ltr');
    panel.innerHTML =
      '<div class="chat-head" id="chatHead">' +
        '<button class="chat-icon-btn" id="chatBack" title="' + t('Back', 'رجوع') + '" style="display:none"><i class="bi ' + (isAr ? 'bi-arrow-right' : 'bi-arrow-left') + '"></i></button>' +
        '<div style="flex:1;min-width:0"><div class="chat-head-title" id="chatTitle">' + t('Chats', 'المحادثات') + '</div><div class="chat-head-sub" id="chatSub"></div></div>' +
        '<button class="chat-icon-btn" id="chatNew" title="' + t('New chat', 'محادثة جديدة') + '"><i class="bi bi-pencil-square"></i></button>' +
        '<button class="chat-icon-btn" id="chatClose" title="' + t('Close', 'إغلاق') + '"><i class="bi bi-x-lg"></i></button>' +
      '</div>' +
      '<div class="chat-body" id="chatBody"></div>' +
      '<div class="chat-typing" id="chatTyping"></div>' +
      '<div class="chat-input-area" id="chatInputArea" style="display:none">' +
        '<input type="file" id="chatFile" hidden accept="image/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">' +
        '<button class="chat-attach" id="chatAttach" title="' + t('Attach', 'إرفاق') + '"><i class="bi bi-paperclip"></i></button>' +
        '<textarea class="chat-input" id="chatInput" rows="1" placeholder="' + t('Message…', 'رسالة…') + '"></textarea>' +
        '<button class="chat-send" id="chatSend" disabled><i class="bi bi-send"></i></button>' +
      '</div>';
    document.body.appendChild(panel);
    body = panel.querySelector('#chatBody');
    head = panel.querySelector('#chatHead');

    panel.querySelector('#chatClose').onclick = close;
    panel.querySelector('#chatBack').onclick = showList;
    panel.querySelector('#chatNew').onclick = showRoster;
    var input = panel.querySelector('#chatInput'), send = panel.querySelector('#chatSend');
    input.addEventListener('input', function () {
      send.disabled = !input.value.trim() && !S.pendingAtt.length;
      input.style.height = 'auto'; input.style.height = Math.min(110, input.scrollHeight) + 'px';
      sendTyping('typing');
    });
    input.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); doSend(); } });
    send.onclick = doSend;
    panel.querySelector('#chatAttach').onclick = function () { panel.querySelector('#chatFile').click(); };
    panel.querySelector('#chatFile').onchange = onPickFile;
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
    S.view = 'list'; S.activeCid = null; stopThreadPoll();
    panel.querySelector('#chatBack').style.display = 'none';
    panel.querySelector('#chatNew').style.display = '';
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
      var last = c.last && c.last.body != null ? esc(c.last.body) : (c.last && c.last.at ? '📎' : '');
      var un = c.unread > 0 ? '<span class="chat-conv-unread">' + (c.unread > 99 ? '99+' : c.unread) + '</span>' : '';
      return '<div class="chat-conv" data-cid="' + c.id + '">' + av +
        '<div class="chat-conv-main"><div class="chat-conv-name">' + esc(c.title) + (c.type === 'group' ? ' <i class="bi bi-people" style="font-size:.7rem"></i>' : '') + '</div><div class="chat-conv-last">' + last + '</div></div>' +
        '<div class="chat-conv-meta"><span class="chat-conv-time">' + timeShort(c.last_activity_at) + '</span>' + un + '</div></div>';
    }).join('');
    body.querySelectorAll('.chat-conv').forEach(function (el) {
      el.onclick = function () { openConversation(+el.dataset.cid); };
    });
  }

  function showRoster() {
    S.view = 'roster';
    panel.querySelector('#chatBack').style.display = '';
    panel.querySelector('#chatNew').style.display = 'none';
    panel.querySelector('#chatTitle').textContent = t('New chat', 'محادثة جديدة');
    panel.querySelector('#chatSub').textContent = '';
    panel.querySelector('#chatInputArea').style.display = 'none';
    panel.querySelector('#chatBack').onclick = showList;
    body.innerHTML = '<div class="chat-empty">' + t('Loading…', 'جارٍ التحميل…') + '</div>';
    api('/api/chat/roster').then(function (d) {
      var us = (d && d.users) || [];
      if (!us.length) { body.innerHTML = '<div class="chat-empty">' + t('No one available to chat.', 'لا أحد متاح للمحادثة.') + '</div>'; return; }
      body.innerHTML = us.map(function (u) {
        var av = u.avatar ? '<img class="chat-avatar" src="' + esc(u.avatar) + '">' : '<div class="chat-avatar">' + esc(initials(u.name)) + '</div>';
        var role = u.role === 'doctor' ? t('Doctor', 'طبيب') : (u.role === 'secretary' ? t('Secretary', 'سكرتارية') : u.role);
        return '<div class="chat-roster-row" data-uid="' + u.id + '">' + av + '<div class="chat-conv-main"><div class="chat-conv-name">' + esc(u.name) + '</div><div class="chat-role-tag">' + esc(role) + '</div></div></div>';
      }).join('');
      body.querySelectorAll('.chat-roster-row').forEach(function (el) {
        el.onclick = function () {
          api('/api/chat/conversations', { method: 'POST', body: { user_id: +el.dataset.uid } }).then(function (r) {
            if (r && r.ok) openConversation(r.conversation_id);
          });
        };
      });
    });
  }

  function openConversation(cid) {
    var c = S.convos.find(function (x) { return x.id === cid; });
    S.view = 'thread'; S.activeCid = cid; S.cursor = 0;
    panel.querySelector('#chatBack').style.display = '';
    panel.querySelector('#chatBack').onclick = showList;
    panel.querySelector('#chatNew').style.display = 'none';
    panel.querySelector('#chatTitle').textContent = c ? c.title : t('Chat', 'محادثة');
    panel.querySelector('#chatSub').textContent = c && c.type === 'group' ? (c.members.length + ' ' + t('members', 'أعضاء')) : '';
    panel.querySelector('#chatInputArea').style.display = 'flex';
    S.msgs = {}; S.order = [];
    body.innerHTML = '<div class="chat-messages" id="chatMessages"></div>';
    api('/api/chat/' + cid + '/messages').then(function (d) {
      if (!d || !d.ok) return;
      S.cursor = d.cursor || 0;
      (d.messages || []).forEach(upsertMsg);
      renderThread(true);
      markRead();
      startThreadPoll();
    });
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
    box.querySelectorAll('[data-react]').forEach(function (b) { b.onclick = function () { react(+b.dataset.mid, b.dataset.react); }; });
    box.querySelectorAll('[data-del]').forEach(function (b) { b.onclick = function () { delMsg(+b.dataset.del); }; });
    box.querySelectorAll('.chat-msg-att img').forEach(function (im) { im.onclick = function () { window.open(im.src, '_blank'); }; });
    if (scroll) box.scrollTop = box.scrollHeight + 9999;
    else { var nearBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 120; if (nearBottom) box.scrollTop = box.scrollHeight + 9999; }
  }
  function msgHtml(m) {
    var mine = m.sender_id === S.me;
    var atts = (m.attachments || []).map(function (a) {
      if (a.kind === 'image') return '<div class="chat-msg-att"><img src="' + esc(a.url) + '" alt=""></div>';
      if (a.kind === 'audio') return '<div class="chat-msg-att"><audio controls preload="metadata" src="' + esc(a.url) + '"></audio></div>';
      return '<div class="chat-msg-att"><a class="chat-file" href="' + esc(a.url) + '" target="_blank"><i class="bi bi-file-earmark"></i>' + esc(a.name || 'file') + '</a></div>';
    }).join('');
    var reacts = (m.reactions || []).map(function (r) {
      var on = (r.users || []).indexOf(S.me) !== -1 ? ' on' : '';
      return '<span class="chat-react' + on + '" data-react="' + esc(r.emoji) + '" data-mid="' + m.id + '">' + esc(r.emoji) + ' ' + r.count + '</span>';
    }).join('');
    var actions = '<span class="chat-msg-actions">' +
      '<button data-react="👍" data-mid="' + m.id + '" title="like">👍</button>' +
      '<button data-react="❤️" data-mid="' + m.id + '" title="love">❤️</button>' +
      (mine && IS_DOCTOR ? '<button data-del="' + m.id + '" title="' + t('Delete', 'حذف') + '"><i class="bi bi-trash"></i></button>' : '') + '</span>';
    var sender = (!mine && CUR_IS_GROUP) ? '<div class="chat-msg-sender">' + esc(m.sender_name) + '</div>' : '';
    var bodyHtml = m.body != null ? esc(m.body) : '';
    return '<div class="chat-msg ' + (mine ? 'mine' : 'theirs') + '">' + sender +
      '<div class="chat-bubble">' + bodyHtml + atts + '</div>' +
      '<div style="display:flex;align-items:center;gap:4px">' + (mine ? actions : '') +
      '<span class="chat-msg-time">' + timeShort(m.created_at) + (m.edited ? ' · ' + t('edited', 'معدّلة') : '') + '</span>' + (!mine ? actions : '') + '</div>' +
      (reacts ? '<div class="chat-msg-reacts">' + reacts + '</div>' : '') + '</div>';
  }
  var CUR_IS_GROUP = false, IS_DOCTOR = document.documentElement.getAttribute('data-layout') === 'doctor';

  function doSend() {
    var input = panel.querySelector('#chatInput');
    var txt = input.value.trim();
    if (!txt && !S.pendingAtt.length) return;
    var attIds = S.pendingAtt.map(function (a) { return a.id; });
    api('/api/chat/' + S.activeCid + '/messages', { method: 'POST', body: { body: txt, attachment_ids: attIds } }).then(function (d) {
      if (d && d.ok && d.message) { upsertMsg(d.message); S.cursor = Math.max(S.cursor, d.cursor || 0); renderThread(true); }
    });
    input.value = ''; input.style.height = 'auto'; panel.querySelector('#chatSend').disabled = true;
    S.pendingAtt = []; updateAttHint();
  }
  function onPickFile(e) {
    var f = e.target.files && e.target.files[0]; e.target.value = '';
    if (!f) return;
    sendTyping(f.type.indexOf('image') === 0 ? 'image' : (f.type.indexOf('audio') === 0 ? 'voice' : 'file'));
    var fd = new FormData(); fd.append('file', f);
    panel.querySelector('#chatSub').textContent = t('Uploading…', 'جارٍ الرفع…');
    api('/api/chat/attachments', { method: 'POST', body: fd }).then(function (d) {
      panel.querySelector('#chatSub').textContent = '';
      if (d && d.ok && d.attachment) { S.pendingAtt.push(d.attachment); panel.querySelector('#chatSend').disabled = false; updateAttHint(); }
    });
  }
  function updateAttHint() {
    panel.querySelector('#chatSub').textContent = S.pendingAtt.length ? (S.pendingAtt.length + ' ' + t('attachment(s) ready', 'مرفق جاهز')) : '';
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
      if (!d || !d.ok) return;
      var changed = false;
      (d.messages || []).forEach(function (m) { upsertMsg(m); changed = true; });
      S.cursor = d.cursor || S.cursor;
      var tp = panel.querySelector('#chatTyping');
      if (tp) tp.textContent = (d.typing || []).length ? typingLabel(d.typing[0]) : '';
      if (changed) { renderThread(false); markRead(); }
    });
  }
  function typingLabel(tinfo) {
    var who = esc(tinfo.name || '');
    if (tinfo.state === 'voice') return who + ' ' + t('is sending a voice message…', 'يرسل رسالة صوتية…');
    if (tinfo.state === 'image') return who + ' ' + t('is sending an image…', 'يرسل صورة…');
    if (tinfo.state === 'file') return who + ' ' + t('is sending a file…', 'يرسل ملفاً…');
    return who + ' ' + t('is typing…', 'يكتب…');
  }
  function startThreadPoll() { stopThreadPoll(); S.threadTimer = setInterval(pollThread, 4000); }
  function stopThreadPoll() { if (S.threadTimer) { clearInterval(S.threadTimer); S.threadTimer = null; } }

  function loadConversations() {
    api('/api/chat/conversations').then(function (d) {
      if (d && d.ok) { S.convos = d.conversations || []; if (S.view === 'list') renderList(); }
    });
  }
  function refreshBadge() {
    api('/api/chat/version').then(function (d) {
      if (!d || !d.ok) return;
      S.me = d.me || S.me;
      setBadge(d.unread_total || 0);
      if (d.conversations_rev !== S.lastConvRev) {
        S.lastConvRev = d.conversations_rev;
        if (S.open && S.view === 'list') loadConversations();
        if (S.open && S.view === 'thread') {
          var c = S.convos.find(function (x) { return x.id === S.activeCid; });
          CUR_IS_GROUP = c && c.type === 'group';
        }
      }
    });
  }
  function startVersionPoll() { if (S.verTimer) return; S.verTimer = setInterval(function () { if (!document.hidden) refreshBadge(); }, 12000); }

  // ---- open/close ------------------------------------------------------
  function open() {
    S.open = true; panel.classList.add('open');
    if (S.view === 'thread' && S.activeCid) { startThreadPoll(); pollThread(); }
    else showList();
  }
  function close() { S.open = false; panel.classList.remove('open'); stopThreadPoll(); }
  function toggle() { S.open ? close() : open(); }

  function boot() {
    build();
    var dockBtn = document.getElementById('dockChatBtn');
    if (dockBtn) {
      dockBtn.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); toggle(); });
      badgeEls.push(document.getElementById('chatUnreadBadge'));
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
    }
    refreshBadge();
    startVersionPoll();
    document.addEventListener('visibilitychange', function () { if (!document.hidden && S.open) { refreshBadge(); pollThread(); } });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
