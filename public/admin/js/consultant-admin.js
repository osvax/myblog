(function () {
    'use strict';

    var endpoint = '../api/consultant';
    var currentUserId = null;
    var usersCache = [];
    var activeUserView = 'chats';
    var readStateKey = 'myblog.admin.consultant.read.v1';
    var lastMarkedMessageByUser = {};
    var stateRequestInProgress = false;
    var typingStatusInProgress = false;
    var typingHeartbeat = null;
    var typingUserId = null;
    var typingActive = false;

    function markConversationRead(user) {
        if (!user || !user.id || !user.lastUserMessageId) return;
        try {
            var readState = JSON.parse(localStorage.getItem(readStateKey) || '{}');
            if (!readState || typeof readState !== 'object' || Array.isArray(readState)) {
                readState = {};
            }
            if (String(readState[user.id] || '') === String(user.lastUserMessageId)) return;
            readState[user.id] = String(user.lastUserMessageId);
            localStorage.setItem(readStateKey, JSON.stringify(readState));
            window.dispatchEvent(new CustomEvent('admin-consultant-read', { detail: { userId: user.id } }));
        } catch (error) {
            console.error('Не удалось отметить обращение как прочитанное.', error);
        }
    }

    function jsonRequest(action, payload) {
        return fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json; charset=utf-8'
            },
            body: JSON.stringify(Object.assign({ action: action }, payload || {}))
        }).then(function (response) {
            if (!response.ok) {
                return response.json().then(function (errorBody) {
                    throw new Error(errorBody.message || 'Не удалось выполнить запрос.');
                }).catch(function () {
                    throw new Error('Не удалось выполнить запрос.');
                });
            }
            return response.json();
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatDate(value) {
        if (!value) return 'только что';
        var date = new Date(value);
        if (Number.isNaN(date.getTime())) return value;
        return date.toLocaleString('ru-RU', {
            year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit'
        });
    }

    function isMessageAtOrBefore(messageId, receiptId) {
        if (messageId === null || messageId === undefined || receiptId === null || receiptId === undefined) return false;
        var message = String(messageId);
        var receipt = String(receiptId);
        return message.length < receipt.length || (message.length === receipt.length && message <= receipt);
    }

    function renderUserReadIndicator(user) {
        var localReadMessageId = null;
        try {
            var readState = JSON.parse(localStorage.getItem(readStateKey) || '{}');
            localReadMessageId = readState && readState[user.id];
        } catch (error) {
            console.error('Не удалось прочитать состояние прочитанных обращений.', error);
        }
        if (isMessageAtOrBefore(user.lastUserMessageId, user.readByAdminMessageId) ||
            String(localReadMessageId || '') === String(user.lastUserMessageId || '')) {
            return '<span class="consultant-user-read-dot" title="Прочитано" aria-label="Прочитано"></span>';
        }
        if (user.status === 'new') {
            return '<span class="consultant-unanswered-dot" title="Ожидает прочтения" aria-label="Ожидает прочтения"></span>';
        }
        return '';
    }

    function renderUsers(users) {
        var list = document.getElementById('consultantUsers');
        var deleteButton = document.getElementById('consultantDeleteAnswered');
        if (!list) return;
        var activeUsers = (users || []).filter(function (user) {
            return user.status !== 'closed';
        });
        var archivedUsers = (users || []).filter(function (user) {
            return user.status === 'closed';
        });
        var visibleUsers = activeUserView === 'archive' ? archivedUsers : activeUsers;
        var chatsCount = document.getElementById('consultantChatsCount');
        var archiveCount = document.getElementById('consultantArchiveCount');
        if (chatsCount) chatsCount.textContent = String(activeUsers.length);
        if (archiveCount) archiveCount.textContent = String(archivedUsers.length);
        document.querySelectorAll('.consultant-user-tab').forEach(function (tab) {
            var isActive = tab.dataset.view === activeUserView;
            tab.classList.toggle('active', isActive);
            tab.setAttribute('aria-selected', String(isActive));
        });

        var answeredCount = (users || []).filter(function (user) {
            return user.status === 'answered';
        }).length;
        if (deleteButton) {
            deleteButton.disabled = answeredCount === 0;
            deleteButton.title = answeredCount ? 'Удалить отвеченные чаты и всю их историю' : 'Нет отвеченных чатов';
        }

        if (!visibleUsers.length) {
            list.innerHTML = activeUserView === 'archive'
                ? '<div class="consultant-empty-card">Архив пуст.</div>'
                : '<div class="consultant-empty-card">Пока нет активных чатов.</div>';
            return;
        }

        list.innerHTML = visibleUsers.map(function (user) {
            var isCurrent = currentUserId && String(currentUserId) === String(user.id);
            return [
                '<button type="button" class="consultant-user-item ' + (isCurrent ? 'active' : '') + '" data-user-id="' + escapeHtml(user.id) + '">',
                '   <div class="consultant-user-top">',
                '       <strong>' + escapeHtml(user.name || 'Гость') + '</strong>',
                '       ' + renderUserReadIndicator(user),
                '       <span class="consultant-badge ' + escapeHtml(user.status) + '">' +
                    (user.status === 'closed' ? 'Закрыт' : user.status === 'answered' ? 'Отвечен' : 'Новый') + '</span>',
                '   </div>',
                '   <div class="consultant-user-preview">' + escapeHtml(user.phone || 'Телефон не указан') + '</div>',
                '   <div class="consultant-user-preview">' + escapeHtml(user.lastMessage || user.question || 'Нет текста') + '</div>',
                '   <small>' + escapeHtml(formatDate(user.updatedAt || user.createdAt)) + '</small>',
                '</button>'
            ].join('');
        }).join('');

        list.querySelectorAll('.consultant-user-item').forEach(function (button) {
            button.addEventListener('click', function () {
                if (typingHeartbeat) window.clearInterval(typingHeartbeat);
                typingHeartbeat = null;
                setTypingState(false);
                currentUserId = button.dataset.userId;
                loadState();
            });
        });
    }

    function renderMessages(messages, user) {
        var thread = document.getElementById('consultantThread');
        if (!thread) return;

        var rows = (messages || []).map(function (message) {
            var direction = message.role === 'admin' ? 'admin' : 'user';
            var viewedId = direction === 'admin' ? user.viewedByUserMessageId : null;
            var readId = direction === 'admin' ? user.readByUserMessageId : null;
            var receipt = '';
            if (isMessageAtOrBefore(message.id, readId)) {
                receipt = '<span class="consultant-message-checks read" title="Прочитано" aria-label="Прочитано">✓✓</span>';
            } else if (isMessageAtOrBefore(message.id, viewedId)) {
                receipt = '<span class="consultant-message-checks" title="Просмотрено" aria-label="Просмотрено">✓</span>';
            }
            return [
                '<div class="consultant-chat-row ' + direction + '">',
                '   <div class="consultant-chat-bubble">' + escapeHtml(message.text || '') + '</div>',
                '   <small class="consultant-message-meta">' + escapeHtml(formatDate(message.createdAt)) + receipt + '</small>',
                '</div>'
            ].join('');
        });
        if (!rows.length) rows.push('<div class="consultant-empty-card">Нет сообщений в чате.</div>');
        if (user && user.typingRole === 'user') rows.push(typingIndicator(user.name || 'Собеседник'));
        thread.innerHTML = rows.join('');

        thread.scrollTop = thread.scrollHeight;
    }

    function typingIndicator(name) {
        return '<div id="consultantTypingIndicator" class="consultant-typing-indicator"><span>' + escapeHtml(name) +
            ' печатает</span><i></i><i></i><i></i></div>';
    }

    function updateTypingIndicator(typingRole, user) {
        var thread = document.getElementById('consultantThread');
        if (!thread) return;
        var indicator = document.getElementById('consultantTypingIndicator');
        if (typingRole !== 'user') {
            if (indicator) indicator.remove();
            return;
        }
        if (!indicator) {
            thread.insertAdjacentHTML('beforeend', typingIndicator(user && user.name || 'Собеседник'));
            thread.scrollTop = thread.scrollHeight;
        }
    }

    function loadTypingStatus() {
        if (!currentUserId || typingStatusInProgress) return;
        typingStatusInProgress = true;
        var requestedUserId = currentUserId;
        jsonRequest('getTyping', { userId: requestedUserId, role: 'admin' }).then(function (response) {
            if (String(currentUserId) !== String(requestedUserId)) return;
            var user = usersCache.find(function (item) {
                return String(item.id) === String(requestedUserId);
            });
            updateTypingIndicator(response.typingRole, user);
        }).catch(function (error) {
            console.error('Не удалось получить состояние набора текста.', error);
        }).finally(function () {
            typingStatusInProgress = false;
        });
    }

    function loadState() {
        if (stateRequestInProgress) return;
        stateRequestInProgress = true;
        jsonRequest('getState').then(function (response) {
            var data = response && response.data ? response.data : { users: [], messages: {} };
            var users = data.users || [];
            usersCache = users;
            var visibleUsers = users.filter(function (user) {
                return activeUserView === 'archive' ? user.status === 'closed' : user.status !== 'closed';
            });
            if (currentUserId && !visibleUsers.some(function (user) {
                return String(user.id) === String(currentUserId);
            })) {
                currentUserId = null;
            }
            if (!currentUserId && visibleUsers.length) {
                currentUserId = visibleUsers[0].id;
            }

            renderUsers(users);

            var selectedUser = users.find(function (item) {
                return String(item.id) === String(currentUserId);
            }) || null;
            var threadTitle = document.getElementById('consultantThreadTitle');
            if (threadTitle) {
                threadTitle.textContent = selectedUser
                    ? (selectedUser.name || 'Гость') + (selectedUser.status === 'closed' ? ' · Закрыт' : '')
                    : 'Консультант';
            }
            var closeButton = document.getElementById('consultantCloseThread');
            if (closeButton) {
                closeButton.disabled = !selectedUser || selectedUser.status === 'closed';
                closeButton.title = selectedUser && selectedUser.status === 'closed'
                    ? 'Этот чат уже закрыт'
                    : 'Закрыть диалог, сохранив историю';
            }
            var replyInput = document.getElementById('consultantReplyInput');
            var submitButton = document.querySelector('#consultantReplyForm button[type="submit"]');
            var isClosed = Boolean(selectedUser && selectedUser.status === 'closed');
            if (replyInput) {
                replyInput.disabled = isClosed || !selectedUser;
                replyInput.placeholder = isClosed ? 'Чат закрыт' : 'Напишите ответ посетителю...';
            }
            if (submitButton) submitButton.disabled = isClosed || !selectedUser;

            var messages = selectedUser ? (data.messages && data.messages[selectedUser.id]) || [] : [];
            renderMessages(messages, selectedUser);
            if (selectedUser && document.visibilityState === 'visible') {
                sendMessageStatus(selectedUser, 'viewed');
            }
        }).catch(function (error) {
            var thread = document.getElementById('consultantThread');
            if (thread) {
                thread.innerHTML = '<div class="consultant-empty-card">' + escapeHtml(error.message || 'Не удалось загрузить чат.') + '</div>';
            }
        }).finally(function () {
            stateRequestInProgress = false;
        });
    }

    function sendMessageStatus(user, level) {
        if (!user || !user.id) return Promise.resolve();
        var messageId = user.lastUserMessageId;
        if (!messageId) return Promise.resolve();

        var stateKey = user.id + ':' + level;
        var messageKey = String(messageId);
        if (lastMarkedMessageByUser[stateKey] === messageKey) return Promise.resolve();
        lastMarkedMessageByUser[stateKey] = messageKey;

        return fetch('../api/auth/csrf', { credentials: 'same-origin' }).then(function (response) {
            if (!response.ok) throw new Error('Не удалось проверить права администратора.');
            return response.json();
        }).then(function (csrf) {
            if (!csrf.success || !csrf.token) throw new Error('Не удалось проверить права администратора.');
            return fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json; charset=utf-8',
                    'X-CSRF-Token': csrf.token
                },
                body: JSON.stringify({
                    action: 'markRead',
                    userId: user.id,
                    role: 'admin',
                    level: level
                })
            });
        }).then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Не удалось обновить статус сообщения.');
                }
                return data;
            });
        }).catch(function (error) {
            delete lastMarkedMessageByUser[stateKey];
            console.error('Не удалось обновить статус сообщения.', error);
            throw error;
        });
    }

    function markCurrentConversationRead() {
        var user = usersCache.find(function (item) {
            return String(item.id) === String(currentUserId);
        });
        if (!user || !user.lastUserMessageId) return;
        markConversationRead(user);
        sendMessageStatus(user, 'read').then(loadState).catch(function () {});
    }

    function setTypingState(isTyping, refresh) {
        if (isTyping) {
            if (!currentUserId) return;
            var userId = currentUserId;
            if (typingActive && String(typingUserId) === String(userId) && !refresh) return;
            if (typingActive && typingUserId && String(typingUserId) !== String(userId)) {
                sendTypingRequest(typingUserId, false);
            }
            typingUserId = userId;
            typingActive = true;
            sendTypingRequest(userId, true);
            return;
        }
        if (typingActive && typingUserId) sendTypingRequest(typingUserId, false);
        typingActive = false;
        typingUserId = null;
    }

    function sendTypingRequest(userId, isTyping) {
        fetch('../api/auth/csrf', { credentials: 'same-origin' }).then(function (response) {
            if (!response.ok) throw new Error('Не удалось проверить права администратора.');
            return response.json();
        }).then(function (csrf) {
            if (!csrf.success || !csrf.token) throw new Error('Не удалось проверить права администратора.');
            return fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json; charset=utf-8',
                    'X-CSRF-Token': csrf.token
                },
                body: JSON.stringify({ action: 'setTyping', userId: userId, role: 'admin', isTyping: isTyping })
            });
        }).then(function (response) {
            if (!response.ok) throw new Error('Не удалось обновить индикатор набора текста.');
        }).catch(function (error) {
            console.error('Не удалось обновить индикатор набора текста.', error);
        });
    }

    function attachForm() {
        var form = document.getElementById('consultantReplyForm');
        if (!form) return;

        var replyInput = document.getElementById('consultantReplyInput');
        if (replyInput) {
            replyInput.addEventListener('focus', markCurrentConversationRead);
            replyInput.addEventListener('click', markCurrentConversationRead);
            replyInput.addEventListener('input', function () {
                var isTyping = Boolean(replyInput.value.trim());
                setTypingState(isTyping);
                if (typingHeartbeat) window.clearInterval(typingHeartbeat);
                if (isTyping) {
                    typingHeartbeat = window.setInterval(function () {
                        if (!replyInput.value.trim()) {
                            window.clearInterval(typingHeartbeat);
                            typingHeartbeat = null;
                            setTypingState(false);
                            return;
                        }
                        setTypingState(true, true);
                    }, 2000);
                } else {
                    typingHeartbeat = null;
                }
            });
            replyInput.addEventListener('blur', function () {
                if (typingHeartbeat) window.clearInterval(typingHeartbeat);
                typingHeartbeat = null;
                setTypingState(false);
            });
            replyInput.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter' || event.shiftKey || event.isComposing) return;
                event.preventDefault();
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                }
            });
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var input = document.getElementById('consultantReplyInput');
            if (!input || !currentUserId) return;

            var value = (input.value || '').trim();
            if (!value) {
                input.focus();
                return;
            }

            fetch('../api/auth/csrf', { credentials: 'same-origin' }).then(function (response) {
                if (!response.ok) throw new Error('Не удалось проверить права администратора.');
                return response.json();
            }).then(function (csrf) {
                if (!csrf.success || !csrf.token) throw new Error('Не удалось проверить права администратора.');
                return fetch(endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json; charset=utf-8',
                        'X-CSRF-Token': csrf.token
                    },
                    body: JSON.stringify({
                        action: 'sendMessage',
                        userId: currentUserId,
                        role: 'admin',
                        message: value
                    })
                }).then(function (response) {
                    return response.json().then(function (data) {
                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'Не удалось отправить ответ.');
                        }
                        return data;
                    });
                });
            }).then(function () {
                if (typingHeartbeat) window.clearInterval(typingHeartbeat);
                typingHeartbeat = null;
                setTypingState(false);
                input.value = '';
                loadState();
            }).catch(function (error) {
                var thread = document.getElementById('consultantThread');
                if (thread) {
                    thread.insertAdjacentHTML('beforeend', '<div class="consultant-empty-card">' + escapeHtml(error.message || 'Не удалось отправить ответ.') + '</div>');
                }
            });
        });
    }

    function attachDeleteAnswered() {
        var button = document.getElementById('consultantDeleteAnswered');
        if (!button) return;

        button.addEventListener('click', function () {
            if (button.disabled) return;
            window.AdminConfirm.ask('Удалить все чаты, на которые уже ответил консультант, вместе с историей переписки? Это действие нельзя отменить.', { title: 'Удаление чатов', confirmLabel: 'Удалить' })
                .then(function (result) {
                    if (!result.ok) return;
                    button.disabled = true;
                    fetch('../api/auth/csrf', { credentials: 'same-origin' }).then(function (response) {
                        if (!response.ok) throw new Error('Не удалось проверить права администратора.');
                        return response.json();
                    }).then(function (csrf) {
                        if (!csrf.success || !csrf.token) throw new Error('Не удалось проверить права администратора.');
                        return fetch(endpoint, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json; charset=utf-8',
                                'X-CSRF-Token': csrf.token
                            },
                            body: JSON.stringify({ action: 'deleteAnswered' })
                        }).then(function (response) {
                            return response.json().then(function (data) {
                                if (!response.ok || !data.success) {
                                    throw new Error(data.message || 'Не удалось удалить отвеченные чаты.');
                                }
                                return data;
                            });
                        });
                    }).then(function (result) {
                        try {
                            var readState = JSON.parse(localStorage.getItem(readStateKey) || '{}');
                            usersCache.forEach(function (user) {
                                if (user.status === 'answered') delete readState[user.id];
                            });
                            localStorage.setItem(readStateKey, JSON.stringify(readState));
                        } catch (error) {
                            console.error('Не удалось обновить состояние прочитанных обращений.', error);
                        }
                        currentUserId = null;
                        if (window.AdminNotifications) {
                            window.AdminNotifications.show(true, 'Удалено чатов: ' + result.deletedCount);
                        }
                        loadState();
                    }).catch(function (error) {
                        button.disabled = false;
                        if (window.AdminNotifications) {
                            window.AdminNotifications.show(false, error.message || 'Не удалось удалить отвеченные чаты.');
                        } else {
                            console.error(error);
                        }
                    });
                });
        });
    }

    function attachCloseThread() {
        var button = document.getElementById('consultantCloseThread');
        if (!button) return;
        button.addEventListener('click', function () {
            if (!currentUserId || button.disabled) return;
            var userId = currentUserId;
            window.AdminConfirm.ask('Закрыть этот чат? История сохранится, но отправлять новые сообщения будет нельзя.', { title: 'Закрытие чата', confirmLabel: 'Закрыть' })
                .then(function (result) {
                    if (!result.ok) return;
                    button.disabled = true;
                    fetch('../api/auth/csrf', { credentials: 'same-origin' }).then(function (response) {
                        if (!response.ok) throw new Error('Не удалось проверить права администратора.');
                        return response.json();
                    }).then(function (csrf) {
                        if (!csrf.success || !csrf.token) throw new Error('Не удалось проверить права администратора.');
                        return fetch(endpoint, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json; charset=utf-8',
                                'X-CSRF-Token': csrf.token
                            },
                            body: JSON.stringify({ action: 'closeThread', userId: userId })
                        });
                    }).then(function (response) {
                        return response.json().then(function (data) {
                            if (!response.ok || !data.success) {
                                throw new Error(data.message || 'Не удалось закрыть чат.');
                            }
                            return data;
                        });
                    }).then(function () {
                        setTypingState(false);
                        var input = document.getElementById('consultantReplyInput');
                        if (input) input.value = '';
                        activeUserView = 'archive';
                        loadState();
                    }).catch(function (error) {
                        button.disabled = false;
                        if (window.AdminNotifications) {
                            window.AdminNotifications.show(false, error.message || 'Не удалось закрыть чат.');
                        } else {
                            console.error(error);
                        }
                    });
                });
        });
    }

    function attachUserTabs() {
        document.querySelectorAll('.consultant-user-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                var nextView = tab.dataset.view;
                if (nextView !== 'chats' && nextView !== 'archive') return;
                if (activeUserView === nextView) return;
                if (typingHeartbeat) window.clearInterval(typingHeartbeat);
                typingHeartbeat = null;
                setTypingState(false);
                activeUserView = nextView;
                currentUserId = null;
                loadState();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadState();
        attachForm();
        attachDeleteAnswered();
        attachCloseThread();
        attachUserTabs();
        window.setInterval(loadState, 5000);
        window.setInterval(loadTypingStatus, 1800);
        window.addEventListener('focus', loadState);
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) loadState();
        });
    });
}());
