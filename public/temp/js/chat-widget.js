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
  var titleLabel = document.getElementById('chat-panel-title-label');
  var channelLabel = document.getElementById('chat-channel-label');

  var groupsUrl = widget.getAttribute('data-chat-groups-url');
  var messagesUrl = widget.getAttribute('data-chat-messages-url');
  var sendUrl = widget.getAttribute('data-chat-send-url');
  var deleteUrlBase = widget.getAttribute('data-chat-delete-url');
  var blockUrlBase = widget.getAttribute('data-chat-block-url');
  var userPreviewBase = widget.getAttribute('data-chat-user-preview-base');
  var groupJoinBase = widget.getAttribute('data-chat-group-join-base');

  var currentChannel = 'global';
  var currentGroupId = 0;
  var activeGroupId = 0;
  var lastMessageId = 0;
  var isSending = false;
  var pollTimer = null;
  var pollInterval = 3000;
  var isFullscreen = false;
  var isOpen = false;
  var groupsData = [];
  var currentGroupData = null;

  // =====================================================================
  //  CHANNEL SWITCHING (global / group)
  // =====================================================================
  var channelTabs = document.querySelectorAll('[data-chat-channel]');
  var groupShell = document.getElementById('chat-group-shell');
  var groupList = document.getElementById('chat-group-list');
  var groupMeta = document.getElementById('chat-group-meta');
  var groupCreateBtn = document.getElementById('chat-group-create-btn');
  var groupJoinBtn = document.getElementById('chat-group-join-btn');
  var groupLeaveBtn = document.getElementById('chat-group-leave-btn');
  var groupRequestsBtn = document.getElementById('chat-group-requests-btn');
  var groupPendingCount = document.getElementById('chat-group-pending-count');
  var groupSettingsBtn = document.getElementById('chat-group-settings-btn');
  var currentGroupNameEl = document.getElementById('chat-current-group-name');
  var currentGroupDescEl = document.getElementById('chat-current-group-description');
  var groupPrivacyBadge = document.getElementById('chat-group-privacy-badge');

  var subpanel = document.getElementById('chat-group-subpanel');
  var subpanelTitle = document.getElementById('chat-group-subpanel-title');
  var subpanelBody = document.getElementById('chat-group-subpanel-body');
  var subpanelBack = document.getElementById('chat-group-subpanel-back');

  // =====================================================================
  //  CREATE GROUP MODAL
  // =====================================================================
  var createModal = document.getElementById('prime-group-create-modal');
  var createName = document.getElementById('prime-group-create-name');
  var createDesc = document.getElementById('prime-group-create-desc');
  var createCancel = document.querySelector('[data-group-create-cancel]');
  var createOk = document.querySelector('[data-group-create-ok]');
  var createPrivacyBtns = document.querySelectorAll('.prime-group-create__toggle-btn');
  var selectedPrivacy = 'closed';

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
    panel.classList.toggle('chat-panel--fullscreen', isFullscreen);
    fullscreenBtn.innerHTML = isFullscreen
      ? '<i class="fa-solid fa-compress"></i>'
      : '<i class="fa-solid fa-expand"></i>';
    fullscreenBtn.setAttribute('aria-label', isFullscreen ? text('chat_fullscreen_exit', 'Exit fullscreen') : text('chat_fullscreen_enter', 'Fullscreen'));
  });

  // =====================================================================
  //  CHANNEL SWITCHING
  // =====================================================================
  channelTabs.forEach(function(tab) {
    tab.addEventListener('click', function() {
      var channel = this.getAttribute('data-chat-channel');
      if (channel === currentChannel) return;
      currentChannel = channel;
      channelTabs.forEach(function(t) { t.classList.remove('chat-panel-tab--active'); });
      this.classList.add('chat-panel-tab--active');
      groupShell.hidden = (channel !== 'group');
      if (channel === 'group') {
        currentGroupId = activeGroupId;
        loadGroupsList();
        updateGroupMeta();
        titleLabel.textContent = text('group_chat', 'Group Chat');
        if (currentGroupId > 0) {
          loadChatMessages();
        } else {
          messagesEl.innerHTML = '<div class="chat-msg-centered">' + escHtml(text('group_select_prompt', 'Select a group')) + '</div>';
        }
      } else {
        currentGroupId = 0;
        activeGroupId = 0;
        titleLabel.textContent = text('global_chat', 'Global Chat');
        loadChatMessages();
      }
    });
  });

  // =====================================================================
  //  LOAD & RENDER MESSAGES
  // =====================================================================
  function loadChatMessages(afterId) {
    var url = messagesUrl;
    var params = [];
    if (currentGroupId > 0) params.push('group_id=' + currentGroupId);
    if (afterId > 0) params.push('after=' + afterId);
    if (params.length) url += '?' + params.join('&');

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
      + '<span class="chat-msg-name" data-user-id="' + m.user_id + '"' + (nameStyle ? ' style="' + nameStyle + '"' : '') + '>' + escHtml(m.user_name) + '</span>'
      + donorBadgeHtml
      + '<span class="chat-msg-time">' + (m.date ? m.date + ' ' : '') + escHtml(m.time || '') + '</span>'
      + '</div>'
      + '<div class="chat-msg-text">' + escHtml(m.body) + '</div>'
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
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    if (isSending) return;
    var body = input.value.trim();
    if (!body) return;

    isSending = true;
    sendBtn.disabled = true;
    sendBtn.setAttribute('aria-busy', 'true');

    var payload = { body: body };
    if (currentGroupId > 0) payload.chat_group_id = currentGroupId;

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
      input.value = input.value + this.getAttribute('data-chat-sticker');
      input.focus();
    });
  });

  // =====================================================================
  //  POLLING
  // =====================================================================
  function startPolling() {
    stopPolling();
    pollTimer = setInterval(function() {
      if (isOpen) loadChatMessages(lastMessageId);
    }, pollInterval);
  }

  function stopPolling() {
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
  }

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
  //  GROUPS
  // =====================================================================
  function loadGroupsList() {
    if (!groupsUrl) return;
    apiFetch(groupsUrl).then(function(data) {
      if (data && data.groups) {
        groupsData = data.groups;
        renderGroupsList();
      }
    }).catch(function() {
      chatLog('Failed to load groups');
    });
  }

  function renderGroupsList() {
    if (!groupList) return;
    if (!groupsData || !groupsData.length) {
      groupList.innerHTML = '<div class="chat-group-empty">' + escHtml(text('group_list_empty', 'No groups available')) + '</div>';
      return;
    }

    var html = '';
    groupsData.forEach(function(g) {
      var isActive = (g.id === activeGroupId);
      var statusLabel = '';
      var statusClass = '';

      if (g.is_owner) {
        statusLabel = text('group_status_owner', 'Owner');
        statusClass = 'chat-group-status--owner';
      } else if (g.is_member) {
        statusLabel = text('group_status_member', 'Member');
        statusClass = 'chat-group-status--member';
      } else if (g.request_status === 'pending') {
        statusLabel = text('group_status_pending', 'Pending');
        statusClass = 'chat-group-status--pending';
      } else if (g.request_status === 'accepted') {
        statusLabel = text('group_status_member', 'Member');
        statusClass = 'chat-group-status--member';
      } else if (g.request_status === 'rejected') {
        statusLabel = text('group_status_join', 'Rejected');
        statusClass = 'chat-group-status--rejected';
      } else {
        statusLabel = text('group_status_join', 'Join');
        statusClass = 'chat-group-status--join';
      }

      if (g.privacy === 'closed') {
        statusLabel += ' <i class="fa-solid fa-lock" style="font-size:10px;"></i>';
      } else {
        statusLabel += ' <i class="fa-solid fa-unlock" style="font-size:10px;"></i>';
      }

      html += '<div class="chat-group-item' + (isActive ? ' is-active' : '') + '" data-group-id="' + g.id + '">'
        + '<div class="chat-group-item-avatar">'
        + (g.image ? '<img src="' + escAttr(g.image) + '" alt="" />' : '<i class="fa-solid fa-users"></i>')
        + '</div>'
        + '<div class="chat-group-item-body">'
        + '<div class="chat-group-item-name">' + escHtml(g.name) + '</div>'
        + '<div class="chat-group-item-meta">'
        + '<span class="chat-group-item-count"><i class="fa-solid fa-user"></i> ' + g.member_count + '</span>'
        + '</div>'
        + '</div>'
        + '<div class="chat-group-item-status ' + statusClass + '">' + statusLabel + '</div>'
        + (g.pending_requests_count > 0 ? '<div class="chat-group-item-badge">' + g.pending_requests_count + '</div>' : '')
        + '</div>';
    });

    groupList.innerHTML = html;

    // Click on group item
    groupList.querySelectorAll('.chat-group-item').forEach(function(item) {
      item.addEventListener('click', function() {
        var gid = parseInt(this.getAttribute('data-group-id'), 10);
        var group = groupsData.find(function(g) { return g.id === gid; });
        if (!group) return;

        activeGroupId = gid;
        currentGroupId = gid;
        currentGroupData = group;

        // Re-render with active state
        groupList.querySelectorAll('.chat-group-item').forEach(function(el) {
          el.classList.remove('is-active');
        });
        this.classList.add('is-active');

        updateGroupMeta();
        loadChatMessages();
      });
    });
  }

  function updateGroupMeta() {
    if (!currentGroupData) {
      groupMeta.hidden = true;
      return;
    }
    groupMeta.hidden = false;
    if (currentGroupNameEl) currentGroupNameEl.textContent = currentGroupData.name;
    if (currentGroupDescEl) {
      currentGroupDescEl.textContent = currentGroupData.description || '';
      currentGroupDescEl.hidden = !currentGroupData.description;
    }
    if (groupPrivacyBadge) {
      groupPrivacyBadge.className = 'chat-group-privacy-dot';
      groupPrivacyBadge.classList.add(currentGroupData.privacy === 'open' ? 'chat-group-privacy--open' : 'chat-group-privacy--closed');
      groupPrivacyBadge.textContent = currentGroupData.privacy === 'open' ? text('group_open', 'Open') : text('group_closed', 'Closed');
    }

    // Controls
    var canManage = currentGroupData.can_manage;
    var isOwner = currentGroupData.is_owner;
    var isMember = currentGroupData.is_member;

    if (groupJoinBtn) {
      var showJoin = (!isOwner && !isMember && currentGroupData.request_status !== 'pending' && currentGroupData.request_status !== 'accepted');
      groupJoinBtn.hidden = !showJoin;
      groupJoinBtn.textContent = currentGroupData.request_status === 'rejected' ? text('group_status_join', 'Join') : text('join_group', 'Join');
    }
    if (groupLeaveBtn) {
      groupLeaveBtn.hidden = !(isMember && !isOwner);
    }
    if (groupRequestsBtn) {
      groupRequestsBtn.hidden = !canManage;
      if (groupPendingCount) groupPendingCount.textContent = currentGroupData.pending_requests_count || 0;
    }
    if (groupSettingsBtn) {
      groupSettingsBtn.hidden = !currentGroupData.can_edit;
    }
  }

  // =====================================================================
  //  GROUP ACTIONS
  // =====================================================================

  // Join group
  if (groupJoinBtn) {
    groupJoinBtn.addEventListener('click', function() {
      if (!currentGroupData || !currentGroupData.id) return;
      var gid = currentGroupData.id;
      apiFetch(groupJoinBase + '/' + gid + '/join', { method: 'POST' }).then(function(data) {
        if (data && data.ok) {
          if (data.joined) {
            showChatToast('Joined group!', 'success');
          } else if (data.pending) {
            showChatToast(text('group_join_sent', 'Request sent'), 'success');
          }
          loadGroupsList();
        } else {
          showChatToast(data && data.error ? data.error : text('group_join_failed', 'Failed to join'), 'error');
        }
      }).catch(function() {
        showChatToast(text('chat_network_error', 'Network error'), 'error');
      });
    });
  }

  // Leave group
  if (groupLeaveBtn) {
    groupLeaveBtn.addEventListener('click', function() {
      if (!currentGroupData || !currentGroupData.id) return;
      var gid = currentGroupData.id;

      if (!confirm(text('group_leave_confirm', 'Leave this group?'))) return;

      apiFetch(groupJoinBase + '/' + gid + '/leave', { method: 'POST' }).then(function(data) {
        if (data && data.ok) {
          showChatToast('Left group', 'success');
          activeGroupId = 0;
          currentGroupId = 0;
          currentGroupData = null;
          groupMeta.hidden = true;
          loadGroupsList();
          messagesEl.innerHTML = '<div class="chat-msg-centered">' + escHtml(text('group_select_prompt', 'Select a group')) + '</div>';
        } else {
          showChatToast(data && data.error ? data.error : 'Failed to leave', 'error');
        }
      }).catch(function() {
        showChatToast(text('chat_network_error', 'Network error'), 'error');
      });
    });
  }

  // Requests button
  if (groupRequestsBtn) {
    groupRequestsBtn.addEventListener('click', function() {
      if (!currentGroupData || !currentGroupData.id) return;
      openRequestsSubpanel(currentGroupData.id);
    });
  }

  // Settings button
  if (groupSettingsBtn) {
    groupSettingsBtn.addEventListener('click', function() {
      if (!currentGroupData || !currentGroupData.id) return;
      openSettingsSubpanel(currentGroupData);
    });
  }

  // =====================================================================
  //  SUBPANEL (Requests / Settings / Members)
  // =====================================================================
  function openSubpanel(title) {
    subpanel.hidden = false;
    if (subpanelTitle) subpanelTitle.textContent = title;
    if (subpanelBody) subpanelBody.innerHTML = '<div class="chat-loading">' + escHtml(text('loading', 'Loading...')) + '</div>';
  }

  function closeSubpanel() {
    subpanel.hidden = true;
    if (subpanelBody) subpanelBody.innerHTML = '';
  }

  if (subpanelBack) {
    subpanelBack.addEventListener('click', closeSubpanel);
  }

  function openRequestsSubpanel(groupId) {
    openSubpanel(text('group_requests', 'Join Requests'));

    apiFetch(groupJoinBase + '/' + groupId + '/requests').then(function(data) {
      if (!data || !data.requests) {
        subpanelBody.innerHTML = '<div class="chat-empty">' + escHtml(text('group_requests_failed', 'Failed')) + '</div>';
        return;
      }
      var requests = data.requests;
      if (!requests.length) {
        subpanelBody.innerHTML = '<div class="chat-empty">' + escHtml(text('group_requests_empty', 'No pending requests')) + '</div>';
        return;
      }
      var html = '';
      requests.forEach(function(r) {
        html += '<div class="chat-request-item" data-request-id="' + r.id + '">'
          + '<div class="chat-request-user">'
          + (r.user_avatar ? '<img src="' + escAttr(r.user_avatar) + '" class="chat-request-avatar" />' : '<span class="chat-request-avatar chat-request-avatar--init">' + escHtml((r.user_name || '?')[0]) + '</span>')
          + '<span>' + escHtml(r.user_name) + '</span>'
          + '</div>'
          + '<div class="chat-request-actions">'
          + '<button type="button" class="chat-panel-btn chat-request-accept" data-request-id="' + r.id + '" data-user-id="' + r.user_id + '"><i class="fa-solid fa-check"></i></button>'
          + '<button type="button" class="chat-panel-btn chat-request-reject" data-request-id="' + r.id + '" data-user-id="' + r.user_id + '"><i class="fa-solid fa-xmark"></i></button>'
          + '</div>'
          + '</div>';
      });
      subpanelBody.innerHTML = html;

      // Accept
      subpanelBody.querySelectorAll('.chat-request-accept').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var reqId = parseInt(this.getAttribute('data-request-id'), 10);
          apiFetch(groupJoinBase + '/' + groupId + '/requests/' + reqId + '/accept', { method: 'POST' }).then(function(data) {
            if (data && data.ok) {
              showChatToast('Accepted', 'success');
              loadGroupsList();
              openRequestsSubpanel(groupId);
            } else {
              showChatToast(data && data.error ? data.error : 'Failed', 'error');
            }
          }).catch(function() {
            showChatToast(text('chat_network_error', 'Network error'), 'error');
          });
        });
      });

      // Reject
      subpanelBody.querySelectorAll('.chat-request-reject').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var reqId = parseInt(this.getAttribute('data-request-id'), 10);
          apiFetch(groupJoinBase + '/' + groupId + '/requests/' + reqId + '/reject', { method: 'POST' }).then(function(data) {
            if (data && data.ok) {
              showChatToast('Rejected', 'info');
              openRequestsSubpanel(groupId);
            } else {
              showChatToast(data && data.error ? data.error : 'Failed', 'error');
            }
          }).catch(function() {
            showChatToast(text('chat_network_error', 'Network error'), 'error');
          });
        });
      });
    }).catch(function() {
      subpanelBody.innerHTML = '<div class="chat-empty">' + escHtml(text('chat_network_error', 'Network error')) + '</div>';
    });
  }

  function openSettingsSubpanel(group) {
    openSubpanel(text('group_settings', 'Group Settings'));

    var html = '<div class="chat-settings-form">'
      + '<div class="chat-settings-field">'
      + '<label>' + escHtml(text('group_name', 'Name')) + '</label>'
      + '<input type="text" id="chat-settings-name" class="chat-input" value="' + escAttr(group.name) + '" maxlength="120" />'
      + '</div>'
      + '<div class="chat-settings-field">'
      + '<label>' + escHtml(text('group_description', 'Description')) + '</label>'
      + '<textarea id="chat-settings-desc" class="chat-input" maxlength="500" style="resize:none;">' + escHtml(group.description || '') + '</textarea>'
      + '</div>'
      + '<div class="chat-settings-field">'
      + '<label>' + escHtml(text('group_privacy', 'Privacy')) + '</label>'
      + '<select id="chat-settings-privacy" class="chat-input">'
      + '<option value="closed"' + (group.privacy === 'closed' ? ' selected' : '') + '>' + escHtml(text('group_closed', 'Closed')) + '</option>'
      + '<option value="open"' + (group.privacy === 'open' ? ' selected' : '') + '>' + escHtml(text('group_open', 'Open')) + '</option>'
      + '</select>'
      + '</div>'
      + '<div class="chat-settings-actions">'
      + '<button type="button" class="chat-panel-btn chat-settings-save"><i class="fa-solid fa-floppy-disk"></i> ' + escHtml(text('group_save', 'Save')) + '</button>'
      + '<button type="button" class="chat-panel-btn chat-settings-members"><i class="fa-solid fa-users"></i> ' + escHtml(text('group_members', 'Members')) + '</button>'
      + '<button type="button" class="chat-panel-btn chat-settings-delete" style="color:var(--danger);"><i class="fa-solid fa-trash"></i> ' + escHtml(text('group_delete', 'Delete')) + '</button>'
      + '</div>'
      + '</div>';

    subpanelBody.innerHTML = html;

    // Save
    subpanelBody.querySelector('.chat-settings-save').addEventListener('click', function() {
      var name = document.getElementById('chat-settings-name').value.trim();
      var desc = document.getElementById('chat-settings-desc').value.trim();
      var privacy = document.getElementById('chat-settings-privacy').value;

      if (!name || name.length < 2) { showChatToast('Name must be at least 2 chars', 'error'); return; }

      apiFetch(groupJoinBase + '/' + group.id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name, description: desc, privacy: privacy })
      }).then(function(data) {
        if (data && data.ok) {
          showChatToast(text('group_save', 'Saved'), 'success');
          loadGroupsList();
        } else {
          showChatToast('Failed to save', 'error');
        }
      }).catch(function() {
        showChatToast(text('chat_network_error', 'Network error'), 'error');
      });
    });

    // Members
    subpanelBody.querySelector('.chat-settings-members').addEventListener('click', function() {
      openMembersSubpanel(group);
    });

    // Delete
    subpanelBody.querySelector('.chat-settings-delete').addEventListener('click', function() {
      if (!confirm('Delete this group permanently?')) return;
      apiFetch(groupJoinBase + '/' + group.id, { method: 'DELETE' }).then(function(data) {
        if (data && data.ok) {
          showChatToast('Group deleted', 'success');
          activeGroupId = 0;
          currentGroupId = 0;
          currentGroupData = null;
          groupMeta.hidden = true;
          closeSubpanel();
          loadGroupsList();
          messagesEl.innerHTML = '<div class="chat-msg-centered">' + escHtml(text('group_select_prompt', 'Select a group')) + '</div>';
        } else {
          showChatToast('Failed to delete', 'error');
        }
      }).catch(function() {
        showChatToast(text('chat_network_error', 'Network error'), 'error');
      });
    });
  }

  function openMembersSubpanel(group) {
    openSubpanel(text('group_members', 'Members'));

    apiFetch(groupJoinBase + '/' + group.id + '/members').then(function(data) {
      if (!data || !data.members) {
        subpanelBody.innerHTML = '<div class="chat-empty">Failed to load members</div>';
        return;
      }
      var members = data.members;
      if (!members.length) {
        subpanelBody.innerHTML = '<div class="chat-empty">No members</div>';
        return;
      }
      var html = '';
      members.forEach(function(m) {
        var roleLabel = m.role === 'admin' ? text('group_role_admin', 'Admin') : text('group_role_member', 'Member');
        var isOwner = m.is_owner;
        var canPromote = !isOwner && m.role === 'member' && group.can_edit;
        var canDemote = !isOwner && m.role === 'admin' && group.can_edit;
        var canKick = !isOwner && group.can_edit;

        html += '<div class="chat-member-item" data-member-id="' + m.id + '">'
          + '<div class="chat-member-avatar">'
          + (m.user_avatar ? '<img src="' + escAttr(m.user_avatar) + '" />' : '<i class="fa-solid fa-user"></i>')
          + '</div>'
          + '<div class="chat-member-body">'
          + '<div class="chat-member-name">' + escHtml(m.user_name) + (isOwner ? ' <small>(owner)</small>' : '') + '</div>'
          + '<div class="chat-member-role">' + roleLabel + '</div>'
          + '</div>'
          + '<div class="chat-member-actions">'
          + (canPromote ? '<button type="button" class="chat-panel-btn chat-member-promote" data-member-id="' + m.id + '" title="Make admin"><i class="fa-solid fa-chevron-up"></i></button>' : '')
          + (canDemote ? '<button type="button" class="chat-panel-btn chat-member-demote" data-member-id="' + m.id + '" title="Make member"><i class="fa-solid fa-chevron-down"></i></button>' : '')
          + (canKick ? '<button type="button" class="chat-panel-btn chat-member-kick" data-member-id="' + m.id + '" title="Remove"><i class="fa-solid fa-xmark"></i></button>' : '')
          + '</div>'
          + '</div>';
      });
      subpanelBody.innerHTML = html;

      // Promote
      subpanelBody.querySelectorAll('.chat-member-promote').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var memberId = parseInt(this.getAttribute('data-member-id'), 10);
          apiFetch(groupJoinBase + '/' + group.id + '/members/' + memberId, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ role: 'admin' })
          }).then(function(data) {
            if (data && data.ok) { showChatToast('Promoted to admin', 'success'); openMembersSubpanel(group); }
            else { showChatToast('Failed', 'error'); }
          });
        });
      });

      // Demote
      subpanelBody.querySelectorAll('.chat-member-demote').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var memberId = parseInt(this.getAttribute('data-member-id'), 10);
          apiFetch(groupJoinBase + '/' + group.id + '/members/' + memberId, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ role: 'member' })
          }).then(function(data) {
            if (data && data.ok) { showChatToast('Demoted to member', 'info'); openMembersSubpanel(group); }
            else { showChatToast('Failed', 'error'); }
          });
        });
      });

      // Kick
      subpanelBody.querySelectorAll('.chat-member-kick').forEach(function(btn) {
        btn.addEventListener('click', function() {
          if (!confirm('Remove this member?')) return;
          var memberId = parseInt(this.getAttribute('data-member-id'), 10);
          apiFetch(groupJoinBase + '/' + group.id + '/members/' + memberId, { method: 'DELETE' }).then(function(data) {
            if (data && data.ok) { showChatToast('Removed', 'info'); openMembersSubpanel(group); }
            else { showChatToast('Failed', 'error'); }
          });
        });
      });
    });
  }

  // =====================================================================
  //  CREATE GROUP MODAL
  // =====================================================================
  if (groupCreateBtn) {
    groupCreateBtn.addEventListener('click', function() {
      if (createModal) {
        createModal.setAttribute('aria-hidden', 'false');
        createModal.classList.add('is-active');
        if (createName) { createName.value = ''; createName.focus(); }
        if (createDesc) createDesc.value = '';
        selectedPrivacy = 'closed';
        createPrivacyBtns.forEach(function(btn) {
          btn.classList.toggle('prime-group-create__toggle-btn--active', btn.getAttribute('data-privacy') === 'closed');
        });
      }
    });
  }

  if (createCancel) {
    createCancel.addEventListener('click', function() {
      if (createModal) {
        createModal.classList.remove('is-active');
        createModal.setAttribute('aria-hidden', 'true');
      }
    });
  }

  createPrivacyBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      selectedPrivacy = this.getAttribute('data-privacy');
      createPrivacyBtns.forEach(function(b) { b.classList.remove('prime-group-create__toggle-btn--active'); });
      this.classList.add('prime-group-create__toggle-btn--active');
    });
  });

  if (createOk) {
    createOk.addEventListener('click', function() {
      var name = createName ? createName.value.trim() : '';
      var desc = createDesc ? createDesc.value.trim() : '';

      if (!name || name.length < 2) {
        showChatToast('Group name must be at least 2 characters', 'error');
        return;
      }

      this.disabled = true;
      this.textContent = 'Creating...';

      apiFetch(groupsUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name, description: desc, privacy: selectedPrivacy })
      }).then(function(data) {
        if (data && data.ok) {
          showChatToast('Group created!', 'success');
          if (createModal) {
            createModal.classList.remove('is-active');
            createModal.setAttribute('aria-hidden', 'true');
          }
          if (currentChannel === 'group') {
            loadGroupsList();
          } else {
            // Switch to group tab
            document.querySelector('[data-chat-channel="group"]').click();
          }
        } else {
          showChatToast(data && data.error ? data.error : 'Failed to create', 'error');
        }
      }).catch(function() {
        showChatToast(text('chat_network_error', 'Network error'), 'error');
      }).finally(function() {
        if (createOk) { createOk.disabled = false; createOk.textContent = text('group_create', 'Create'); }
      });
    });
  }

  // Close modal on backdrop click
  if (createModal) {
    createModal.addEventListener('click', function(e) {
      if (e.target === this || e.target.classList.contains('prime-group-create__backdrop')) {
        this.classList.remove('is-active');
        this.setAttribute('aria-hidden', 'true');
      }
    });
  }

  // =====================================================================
  //  EXPOSE initGlobalChat
  // =====================================================================
  window.initGlobalChat = function() {
    chatLog('initGlobalChat called');

    // Initial state
    if (currentChannel === 'group') {
      loadGroupsList();
    }

    // If there's a group from initial load
    if (activeGroupId > 0) {
      currentGroupId = activeGroupId;
      loadChatMessages();
    }
  };

  // Auto-initialize
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGlobalChat);
  } else {
    initGlobalChat();
  }

})();
