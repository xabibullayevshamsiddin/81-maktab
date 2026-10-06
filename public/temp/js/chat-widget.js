(function() {

  'use strict';

  if (!document.getElementById('chat-widget')) return;

  var widget = document.getElementById('chat-widget');
  var csrfToken = widget.getAttribute('data-csrf');
  var currentUserId = parseInt(widget.getAttribute('data-user-id'), 10);

  var chatTexts = parseJson(widget.getAttribute('data-chat-texts'), {});

  function parseJson(value, fallback) {
    if (!value) return fallback;
    try { return JSON.parse(value); } catch (e) { return fallback; }
  }

  function escHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, function(ch) {
      return ({ '&': '&', '<': '<', '>': '>', '"': '"', "'": '&#39;' })[ch] || ch;
    });
  }

  function escAttr(s) {
    return String(s ?? '').replace(/"/g, '"');
  }

  // =====================================================================
  //  CHAT PANEL STATE
  // =====================================================================
  var bubble = document.getElementById('chat-bubble');
  var panel = document.getElementById('chat-panel');
  var closeBtn = document.getElementById('chat-close-btn');
  var fullscreenBtn = document.getElementById('chat-fullscreen-btn');
  var messagesEl = document.getElementById('chat-messages');
  var form = document.getElementById('chat-form');
  var input = document.getElementById('chat-input');
  var sendBtn = document.getElementById('chat-send-btn');
  var disabledPanel = document.getElementById('chat-disabled-panel');
  var disabledText = document.getElementById('chat-disabled-panel-text');
  var panelMain = document.getElementById('chat-panel-main');
  var clearBtn = document.getElementById('chat-clear-btn');

  var messagesUrl = widget.getAttribute('data-chat-messages-url');
  var sendUrl = widget.getAttribute('data-chat-send-url');
  var deleteUrlBase = widget.getAttribute('data-chat-delete-url');
  var blockUrlBase = widget.getAttribute('data-chat-block-url');
  var userPreviewBase = widget.getAttribute('data-chat-user-preview-base');

  var lastMessageId = 0;
  var isSending = false;
  var pollTimer = null;
  var pollInterval = 3000;
  var isFullscreen = false;
  var isOpen = false;

  // =====================================================================
  //  HELPERS
  // =====================================================================
  function chatLog(msg) {
    if (window.console) console.log('[Chat]', msg);
  }

  function apiFetch(url, opts) {
    if (!opts) opts = {};
    if (!opts.headers) opts.headers = {};
    opts.headers['Accept'] = 'application/json';
    opts.headers['X-CSRF-TOKEN'] = csrfToken;
    opts.credentials = 'same-origin';
    return fetch(url, opts).then(function(r) { return r.json(); });
  }

  function text(key, fallback) {
    return chatTexts[key] || fallback || key;
  }

  // =====================================================================
  //  TOAST / NOTIFICATION
  // =====================================================================
  function showChatToast(msg, type) {
    if (!type) type = 'info';
    var container = document.getElementById('toast-container');
    if (!container) return;
    var el = document.createElement('div');
    el.className = 'toast toast--' + type;
    el.textContent = msg;
    el.style.cssText = 'background:var(--bg-card);color:var(--text);padding:12px 18px;border-radius:14px;margin-bottom:8px;box-shadow:0 8px 32px rgba(0,0,0,.12);border:1px solid var(--border);animation:toastIn .3s ease;';
    container.appendChild(el);
    setTimeout(function() { el.style.opacity = '0'; setTimeout(function() { el.remove(); }, 400); }, 3000);
  }

  // =====================================================================
  //  CHAT PANEL TOGGLE
  // =====================================================================
  function openChatPanel() {
    if (typeof window.primeCloseAiPanel === 'function') window.primeCloseAiPanel();
    isOpen = true;
    panel.removeAttribute('hidden');
    widget.classList.add('is-open');
    setTimeout(function() { panel.classList.add('is-open'); }, 10);
    bubble.style.display = 'none';
    if (window.playPrimeSuccess) window.playPrimeSuccess();
    loadChatMessages();
    startPolling();
  }

  function closeChatPanel() {
    isOpen = false;
    // Reset fullscreen if active
    if (isFullscreen) {
      isFullscreen = false;
      panel.classList.remove('is-fullscreen');
      document.body.classList.remove('chat-fullscreen-active');
      fullscreenBtn.innerHTML = '<i class="fa-solid fa-expand"></i>';
    }
    panel.classList.remove('is-open');
    widget.classList.remove('is-open');
    setTimeout(function() { panel.setAttribute('hidden', ''); }, 300);
    bubble.style.display = '';
    stopPolling();
  }

  window.primeCloseGlobalChatPanel = function() {
    if (isOpen) closeChatPanel();
  };

  bubble.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    openChatPanel();
  });

  closeBtn.addEventListener('click', function(e) {
    e.preventDefault();
    closeChatPanel();
  });

  document.addEventListener('click', function(e) {
    if (isOpen && !panel.contains(e.target) && !bubble.contains(e.target)) {
      closeChatPanel();
    }
  });

  // Fullscreen
  fullscreenBtn.addEventListener('click', function() {
    isFullscreen = !isFullscreen;
    panel.classList.toggle('is-fullscreen', isFullscreen);
    document.body.classList.toggle('chat-fullscreen-active', isFullscreen);
    fullscreenBtn.innerHTML = isFullscreen
      ? '<i class="fa-solid fa-compress"></i>'
      : '<i class="fa-solid fa-expand"></i>';
    fullscreenBtn.setAttribute('aria-label', isFullscreen ? text('chat_fullscreen_exit', 'Exit fullscreen') : text('chat_fullscreen_enter', 'Fullscreen'));
  });

  // =====================================================================
  //  LOAD & RENDER MESSAGES
  // =====================================================================
  function loadChatMessages(afterId) {
    var url = messagesUrl;
    if (afterId > 0) url += '?after=' + afterId;

    apiFetch(url).then(function(data) {
      if (!data) return;
      if (data.chat_disabled) {
        showDisabledPanel(data.disabled_message || text('chat_disabled_default', 'Chat ochirilgan'));
        return;
      }
      hideDisabledPanel();
      if (afterId > 0) {
        appendMessages(data.messages || []);
      } else {
        renderMessages(data.messages || []);
      }
      if (data.last_id) lastMessageId = data.last_id;
      updateClearBtn(data.can_clear_all, data.can_moderate);
    }).catch(function() {
      chatLog('Failed to load messages');
    });
  }

  function renderMessages(messages) {
    messagesEl.innerHTML = '';
    appendMessages(messages);
  }

  function appendMessages(messages) {
    if (!messages || !messages.length) return;
    var fragment = document.createDocumentFragment();
    messages.forEach(function(m) {
      var el = createMessageElement(m);
      if (el) fragment.appendChild(el);
    });
    messagesEl.appendChild(fragment);
    scrollToBottom();
  }

  function createMessageElement(m) {
    var div = document.createElement('div');
    div.className = 'chat-msg' + (m.is_mine ? ' is-mine' : '') + (m.is_super_admin ? ' is-super-admin' : '') + (m.is_admin ? ' is-admin' : '');
    div.dataset.msgId = m.id;

    var avatarHtml = '';
    if (m.avatar_url) {
      avatarHtml = '<img class="chat-msg-avatar" src="' + escAttr(m.avatar_url) + '" alt="" loading="lazy" />';
    } else {
      avatarHtml = '<span class="chat-msg-avatar chat-msg-avatar--init">' + escHtml(m.user_initial) + '</span>';
    }

    var actionsHtml = '';
    if (m.can_delete) {
      actionsHtml += '<button type="button" class="chat-msg-action chat-msg-delete" data-msg-id="' + m.id + '" aria-label="Delete"><i class="fa-solid fa-trash-can"></i></button>';
    }
    if (m.can_block) {
      actionsHtml += '<button type="button" class="chat-msg-action chat-msg-block" data-user-id="' + m.user_id + '" aria-label="Block"><i class="fa-solid fa-ban"></i></button>';
    }

    var donorThemeClass = m.donor_theme ? ' chat-msg--theme-' + String(m.donor_theme).replace(/[^a-z0-9_-]/gi, '') : '';
    if (donorThemeClass) div.className += donorThemeClass;

    var nameStyle = '';
    var fontClass = (m.name_font_family && /^(orbitron|caveat|press-start|pacifico|righteous|bungee|permanent-marker)$/.test(m.name_font_family)) ? ' font-' + m.name_font_family : '';
    if (m.name_font_family && window.loadDonorFont) window.loadDonorFont(m.name_font_family);
    if (m.donor_color && /^#[0-9a-f]{3,8}$/i.test(String(m.donor_color))) {
      nameStyle += 'color:' + escAttr(m.donor_color) + ';';
    }
    if (m.name_font_weight && /^(600|700|800)$/.test(String(m.name_font_weight))) {
      nameStyle += 'font-weight:' + escAttr(m.name_font_weight) + ';';
    }

    var donorBadgeHtml = m.donor_badge ? '<span class="chat-msg-donor-badge">' + m.donor_badge + '</span>' : '';

    div.innerHTML = '<div class="chat-msg-inner">'
      + '<div class="chat-msg-avatar-wrap" data-user-id="' + m.user_id + '">' + avatarHtml + '</div>'
      + '<div class="chat-msg-body">'
      + '<div class="chat-msg-meta">'
      + '<span class="chat-msg-name' + fontClass + '"' + ' data-user-id="' + m.user_id + '"' + (nameStyle ? ' style="' + nameStyle + '"' : '') + '>' + escHtml(m.user_name) + (m.status_emoji ? ' ' + escHtml(m.status_emoji) : '') + '</span>'
      + donorBadgeHtml
      + '<span class="chat-msg-time">' + (m.date ? m.date + ' ' : '') + escHtml(m.time || '') + '</span>'
      + '</div>'
      + (m.sticker_url
        ? '<div class="chat-msg-text chat-msg-sticker"><img src="' + escAttr(m.sticker_url) + '" alt="Stiker" class="chat-sticker-image" loading="lazy" decoding="async" /></div>'
        : m.sticker_code
          ? '<div class="chat-msg-text chat-msg-sticker chat-msg-sticker--fallback">' + escHtml(m.sticker_code) + '</div>'
          : '<div class="chat-msg-text">' + escHtml(m.body) + '</div>')
      + '</div>'
      + (actionsHtml ? '<div class="chat-msg-actions">' + actionsHtml + '</div>' : '')
      + '</div>';

    // Click on avatar/name -> user preview
    div.querySelectorAll('[data-user-id]').forEach(function(el) {
      el.addEventListener('click', function() {
        var uid = parseInt(this.getAttribute('data-user-id'), 10);
        if (uid > 0 && window.openUserPreview) window.openUserPreview(uid);
      });
    });

    // Delete message
    var deleteBtn = div.querySelector('.chat-msg-delete');
    if (deleteBtn) {
      deleteBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        var msgId = parseInt(this.getAttribute('data-msg-id'), 10);
        if (msgId > 0) deleteMessage(msgId);
      });
    }

    // Block user
    var blockBtn = div.querySelector('.chat-msg-block');
    if (blockBtn) {
      blockBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        var uid = parseInt(this.getAttribute('data-user-id'), 10);
        if (uid > 0) blockUser(uid);
      });
    }

    return div;
  }

  function deleteMessage(msgId) {
    apiFetch(deleteUrlBase + '/' + msgId, { method: 'DELETE' }).then(function(data) {
      if (data && data.ok) {
        var el = messagesEl.querySelector('[data-msg-id="' + msgId + '"]');
        if (el) el.remove();
      } else {
        showChatToast('Failed to delete', 'error');
      }
    }).catch(function() {
      showChatToast(text('chat_network_error', 'Network error'), 'error');
    });
  }

  function blockUser(userId) {
    apiFetch(blockUrlBase + '/' + userId, { method: 'POST' }).then(function(data) {
      if (data && data.ok) {
        showChatToast('User blocked', 'success');
      } else {
        showChatToast(data && data.error ? data.error : 'Failed to block', 'error');
      }
    }).catch(function() {
      showChatToast(text('chat_network_error', 'Network error'), 'error');
    });
  }

  // =====================================================================
  //  SEND MESSAGE
  // =====================================================================
  // Sticker yuborish (to'g'ridan-to'g'ri)
  function sendChatMessage(payload) {
    if (isSending) return;
    isSending = true;
    sendBtn.disabled = true;
    sendBtn.setAttribute('aria-busy', 'true');

    apiFetch(sendUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    }).then(function(data) {
      if (data && data.ok) {
        if (window.playPrimeChatTick) window.playPrimeChatTick();
        if (!data.duplicated) {
          loadChatMessages(lastMessageId);
        }
      } else {
        showChatToast(data && data.error ? data.error : 'Failed to send', 'error');
      }
    }).catch(function() {
      showChatToast(text('chat_network_error', 'Network error'), 'error');
    }).finally(function() {
      isSending = false;
      sendBtn.disabled = false;
      sendBtn.removeAttribute('aria-busy');
      input.focus();
    });
  }

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    if (isSending) return;
    var body = input.value.trim();
    if (!body) return;

    isSending = true;
    sendBtn.disabled = true;
    sendBtn.setAttribute('aria-busy', 'true');

    var payload = { body: body };

    apiFetch(sendUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    }).then(function(data) {
      if (data && data.ok) {
        input.value = '';
        if (window.playPrimeChatTick) window.playPrimeChatTick();
        if (!data.duplicated) {
          // Reload messages to get the new one
          loadChatMessages(lastMessageId);
        }
      } else {
        showChatToast(data && data.error ? data.error : 'Failed to send', 'error');
      }
    }).catch(function() {
      showChatToast(text('chat_network_error', 'Network error'), 'error');
    }).finally(function() {
      isSending = false;
      sendBtn.disabled = false;
      sendBtn.removeAttribute('aria-busy');
      input.focus();
    });
  });

  input.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      form.dispatchEvent(new Event('submit'));
    }
  });

  // Sticker buttons
  document.querySelectorAll('[data-chat-sticker]').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var stickerId = this.getAttribute('data-sticker-id');
      if (stickerId) {
        // Agar sticker_id mavjud — to'g'ridan-to'g'ri stiker sifatida yuborish
        sendChatMessage({ chat_sticker_id: parseInt(stickerId, 10) });
        return;
      }
      input.value = input.value + this.getAttribute('data-chat-sticker');
      input.focus();
    });
  });

  // =====================================================================
  //  POLLING — tab visibility + error backoff
  // =====================================================================
  var pollErrors = 0;
  var MAX_POLL_INTERVAL = 30000; // 30s max when tab is idle/has errors
  var BASE_POLL_INTERVAL = 3000; // 3s normal

  function schedulePoll() {
    if (pollTimer) clearTimeout(pollTimer);
    if (!isOpen || document.hidden) return;
    var delay = Math.min(BASE_POLL_INTERVAL * Math.pow(1.5, pollErrors), MAX_POLL_INTERVAL);
    pollTimer = setTimeout(function() {
      doPoll();
    }, delay);
  }

  function doPoll() {
    if (!isOpen || document.hidden) return;
    var afterId = lastMessageId;
    var url = messagesUrl;
    if (afterId > 0) url += '?after=' + afterId;
    apiFetch(url).then(function(data) {
      pollErrors = 0;
      if (!data) { schedulePoll(); return; }
      if (data.chat_disabled) {
        showDisabledPanel(data.disabled_message || text('chat_disabled_default', 'Chat ochirilgan'));
        schedulePoll(); return;
      }
      hideDisabledPanel();
      if (afterId > 0) { appendMessages(data.messages || []); }
      else { renderMessages(data.messages || []); }
      if (data.last_id) lastMessageId = data.last_id;
      updateClearBtn(data.can_clear_all, data.can_moderate);
      if (data.user_blocked !== undefined) {
        updateBlockedState(data.user_blocked, data.blocked_until_ts);
      }
      schedulePoll();
    }).catch(function() {
      pollErrors = Math.min(pollErrors + 1, 5);
      chatLog('Poll failed #' + pollErrors);
      schedulePoll();
    });
  }

  function startPolling() {
    stopPolling();
    pollErrors = 0;
    schedulePoll();
  }

  function stopPolling() {
    if (pollTimer) { clearTimeout(pollTimer); pollTimer = null; }
  }

  // Pause when tab hidden, resume on visibility restore
  document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
      stopPolling();
    } else if (isOpen) {
      pollErrors = 0;
      schedulePoll();
    }
  });

  function scrollToBottom() {
    setTimeout(function() {
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }, 50);
  }

  // =====================================================================
  //  DISABLED PANEL
  // =====================================================================
  function showDisabledPanel(msg) {
    if (disabledPanel && disabledText) {
      disabledPanel.hidden = false;
      disabledText.textContent = msg || text('chat_disabled_default', 'Chat ochirilgan');
    }
    if (panelMain) panelMain.hidden = true;
  }

  function hideDisabledPanel() {
    if (disabledPanel) disabledPanel.hidden = true;
    if (panelMain) panelMain.hidden = false;
  }

  function updateClearBtn(canClearAll, canModerate) {
    if (clearBtn) {
      clearBtn.hidden = !canClearAll;
    }
  }

  // Clear all messages
  if (clearBtn) {
    clearBtn.addEventListener('click', function() {
      if (!confirm(text('global_chat_clear_confirm', 'Delete all messages?'))) return;
      apiFetch(deleteUrlBase + '/clear', { method: 'DELETE' }).then(function(data) {
        if (data && data.ok) {
          renderMessages([]);
          showChatToast(text('global_chat_cleared', 'Chat cleared'), 'success');
        } else {
          showChatToast(text('global_chat_clear_failed', 'Failed to clear'), 'error');
        }
      }).catch(function() {
        showChatToast(text('chat_network_error', 'Network error'), 'error');
      });
    });
  }

  // =====================================================================
  //  EXPOSE initGlobalChat
  // =====================================================================
  window.initGlobalChat = function() {
    chatLog('initGlobalChat called');
  };

  // Auto-initialize
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGlobalChat);
  } else {
    initGlobalChat();
  }

})();
