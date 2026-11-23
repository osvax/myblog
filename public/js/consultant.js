(function () {
    'use strict';

    var endpoint = '/api/consultant';
    var sessionKey = 'myblog.consultant.session.v1';
    var latestAdminMessageKey = null;
    var hasLoadedThread = false;
    var replyBorderTimer = null;
    var notificationAudio = null;
    var notificationAudioUnlocked = false;
    var typingHeartbeat = null;
    var typingSession = null;
    var typingActive = false;
    var currentThreadClosed = false;
    var lastMarkedMessageByLevel = {};
    var defaultSettings = {
        enabled: true,
        buttonColor: '#ff8c1a',
        buttonTextColor: '#ffffff',
        buttonPosition: 'right',
        workingHours: '09:00-20:00',
        censoredWords: 'дурак, идиот, тупица',
        welcomeMessage: 'Здравствуйте! Чем могу помочь?'
    };
    var welcomeMessage = defaultSettings.welcomeMessage;

    function jsonRequest(action, payload) {
        var init = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json; charset=utf-8'
            },
            body: JSON.stringify(Object.assign({ action: action }, payload || {}))
        };

        return fetch(endpoint, init).then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok) {
                    var error = new Error(data.message || 'Ошибка запроса к консультанту.');
                    error.status = response.status;
                    throw error;
                }
                return data;
            });
        });
    }

    function getCurrentTimeMinutes(value) {
        if (!value || typeof value !== 'string') return 0;
        var match = value.match(/(\d{1,2}):(\d{2})/g);
        if (!match || !match[0]) return 0;
        var parts = match[0].split(':');
        return Number(parts[0]) * 60 + Number(parts[1]);
    }

    function isWithinWorkingHours(value) {
        var range = (value || '09:00-20:00').trim();
        var match = range.match(/^(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})$/);
        if (!match) return true;

        var now = new Date();
        var currentMinutes = now.getHours() * 60 + now.getMinutes();
        var startMinutes = getCurrentTimeMinutes(match[1]);
        var endMinutes = getCurrentTimeMinutes(match[2]);

        if (startMinutes <= endMinutes) {
            return currentMinutes >= startMinutes && currentMinutes <= endMinutes;
        }

        return currentMinutes >= startMinutes || currentMinutes <= endMinutes;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function createChatWidget() {
        var root = document.createElement('div');
        root.className = 'consultant-widget';
        root.id = 'consultantWidget';
        root.innerHTML = [
            '<button type="button" class="consultant-fab" id="consultantToggle" aria-label="Открыть чат консультанта">',
            '   <span class="consultant-fab-icon"><i class="bi bi-headset"></i></span>',
            '   <span class="consultant-fab-label">Консультант</span>',
            '</button>',
            '<div class="consultant-panel" id="consultantPanel" aria-live="polite">',
            '   <div class="consultant-panel-header">',
            '       <div>',
            '           <strong>Онлайн-консультант</strong>',
            '           <small id="consultantStatus">Проверяем доступность…</small>',
            '       </div>',
            '       <button type="button" class="consultant-close" id="consultantClose" aria-label="Закрыть чат">×</button>',
            '   </div>',
            '   <div class="consultant-message-list" id="consultantMessages"></div>',
            '   <form id="consultantForm" class="consultant-form">',
            '       <div class="consultant-field-group">',
            '           <label for="consultantName">Ваше имя</label>',
            '           <input id="consultantName" type="text" maxlength="100" autocomplete="name" placeholder="Например, Ирина" required />',
            '       </div>',
            '       <div class="consultant-field-group" id="consultantPhoneGroup">',
            '           <label for="consultantPhone">Телефон для ответа</label>',
            '           <input id="consultantPhone" type="tel" maxlength="40" autocomplete="tel" placeholder="+7 900 000-00-00" required />',
            '       </div>',
            '       <div class="consultant-field-group">',
            '           <label for="consultantQuestion">Вопрос</label>',
            '           <textarea id="consultantQuestion" rows="3" maxlength="2000" placeholder="Опишите задачу" required></textarea>',
            '       </div>',
            '       <button type="submit" class="consultant-submit">Отправить</button>',
            '   </form>',
            '</div>'
        ].join('');

        document.body.appendChild(root);
        return root;
    }

    function applyWidgetSettings(settings) {
        var widget = document.getElementById('consultantWidget');
        var fab = document.getElementById('consultantToggle');
        var panel = document.getElementById('consultantPanel');
        if (!widget || !fab || !panel) return;
        widget.hidden = settings.enabled === false;

        var position = settings.buttonPosition === 'left' ? 'left' : 'right';
        widget.dataset.position = position;
        fab.style.backgroundColor = settings.buttonColor || defaultSettings.buttonColor;
        fab.style.color = settings.buttonTextColor || defaultSettings.buttonTextColor;

        var workingHours = settings.workingHours || defaultSettings.workingHours;
        var isOpen = isWithinWorkingHours(workingHours);
        var status = document.getElementById('consultantStatus');
        if (status) {
            status.textContent = isOpen ? 'Мы онлайн' : 'Сейчас офлайн';
        }

        if (panel) {
            panel.style.display = 'none';
        }

        fab.setAttribute('title', isOpen ? 'Открыть чат консультанта' : 'Консультант доступен в рабочее время');
    }

    function formatMessageDate(value) {
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

    function renderMessages(messages, readStatus, typingRole, isClosed) {
        var container = document.getElementById('consultantMessages');
        if (!container) return;

        readStatus = readStatus || {};
        var rows = (messages || []).map(function (message) {
            var adminClass = message.role === 'admin' ? 'admin' : 'user';
            var recipientStatus = message.role === 'user' ? readStatus.admin : null;
            recipientStatus = recipientStatus || {};
            var receipt = '';
            if (isMessageAtOrBefore(message.id, recipientStatus.read)) {
                receipt = '<span class="consultant-message-checks read" title="Прочитано" aria-label="Прочитано">✓✓</span>';
            } else if (isMessageAtOrBefore(message.id, recipientStatus.viewed)) {
                receipt = '<span class="consultant-message-checks" title="Просмотрено" aria-label="Просмотрено">✓</span>';
            }
            return '<div class="consultant-message ' + adminClass + '">' +
                '<div class="consultant-bubble">' + escapeHtml(message.text) + '</div>' +
                '<small class="consultant-message-meta">' + escapeHtml(formatMessageDate(message.createdAt)) + receipt + '</small>' +
                '</div>';
        });

        if (!rows.length) {
            rows.push('<div class="consultant-empty">Чат пока пуст. Задайте свой вопрос и мы ответим в рабочее время.</div>');
        }
        if (isClosed) {
            rows.push('<div class="consultant-empty consultant-closed-notice">Этот чат закрыт. Отправка новых сообщений недоступна.</div>');
        } else if (typingRole === 'admin') {
            rows.push(typingIndicator('Консультант'));
        }
        container.innerHTML = rows.join('');

        container.scrollTop = container.scrollHeight;
    }

    function typingIndicator(name) {
        return '<div class="consultant-typing-indicator"><span>' + escapeHtml(name) +
            ' печатает</span><i></i><i></i><i></i></div>';
    }

    function setPanelVisibility(visible) {
        var panel = document.getElementById('consultantPanel');
        var toggle = document.getElementById('consultantToggle');
        if (!panel) return;
        panel.style.display = visible ? 'flex' : 'none';
        if (visible) {
            markAdminMessageRead(getSavedSession(), latestAdminMessageKey);
            stopReplyBorderAlert();
            loadThread(getSavedSession());
        }
    }

    function getAdminMessageReadStorageKey(session) {
        return session && session.userId
            ? sessionKey + '.lastAdminMessageRead.' + session.userId
            : null;
    }

    function getLastReadAdminMessageKey(session) {
        var storageKey = getAdminMessageReadStorageKey(session);
        if (!storageKey) return null;
        try {
            return localStorage.getItem(storageKey);
        } catch (error) {
            console.error('Не удалось прочитать отметку о просмотре чата.', error);
            return null;
        }
    }

    function markAdminMessageRead(session, messageKey) {
        var storageKey = getAdminMessageReadStorageKey(session);
        if (!storageKey || !messageKey) return;
        try {
            localStorage.setItem(storageKey, messageKey);
        } catch (error) {
            console.error('Не удалось сохранить отметку о просмотре чата.', error);
        }
    }

    function sendMessageStatus(session, level) {
        if (!session || !session.userId || !latestAdminMessageKey) return;
        var stateKey = level + ':' + session.userId;
        if (lastMarkedMessageByLevel[stateKey] === latestAdminMessageKey) return;
        lastMarkedMessageByLevel[stateKey] = latestAdminMessageKey;
        jsonRequest('markRead', Object.assign({}, session, { role: 'user', level: level }))
            .catch(function (error) {
                delete lastMarkedMessageByLevel[stateKey];
                console.error('Не удалось обновить статус сообщения.', error);
            });
    }

    function sendTypingRequest(session, isTyping) {
        jsonRequest('setTyping', Object.assign({}, session, { role: 'user', isTyping: isTyping }))
            .catch(function (error) {
                console.error('Не удалось обновить индикатор набора текста.', error);
            });
    }

    function sendTypingState(session, isTyping, refresh) {
        if (isTyping) {
            if (!session || !session.userId) return;
            if (typingActive && String(typingSession.userId) === String(session.userId) && !refresh) return;
            if (typingActive && typingSession) sendTypingRequest(typingSession, false);
            typingSession = session;
            typingActive = true;
            sendTypingRequest(session, true);
            return;
        }
        if (typingActive && typingSession) sendTypingRequest(typingSession, false);
        typingActive = false;
        typingSession = null;
    }

    function stopReplyBorderAlert() {
        var toggle = document.getElementById('consultantToggle');
        if (replyBorderTimer !== null) {
            window.clearInterval(replyBorderTimer);
            replyBorderTimer = null;
        }
        if (toggle) {
            toggle.classList.remove('has-consultant-reply', 'consultant-fab-alert-red');
        }
    }

    function startReplyBorderAlert() {
        var toggle = document.getElementById('consultantToggle');
        if (!toggle) return;
        toggle.classList.add('has-consultant-reply');
        if (replyBorderTimer !== null) return;

        var red = false;
        replyBorderTimer = window.setInterval(function () {
            var currentToggle = document.getElementById('consultantToggle');
            if (!currentToggle) {
                stopReplyBorderAlert();
                return;
            }
            red = !red;
            currentToggle.classList.toggle('consultant-fab-alert-red', red);
        }, 450);
    }

    function unlockNotificationAudio() {
        if (notificationAudioUnlocked) return;
        notificationAudioUnlocked = true;
        var AudioContextConstructor = window.AudioContext || window.webkitAudioContext;
        if (!AudioContextConstructor) return;
        try {
            notificationAudio = new AudioContextConstructor();
            if (notificationAudio.state === 'suspended') {
                notificationAudio.resume().catch(function (error) {
                    console.warn('Не удалось включить звук уведомлений чата.', error);
                });
            }
        } catch (error) {
            console.warn('Не удалось включить звук уведомлений чата.', error);
        }
    }

    function playMessageNotification() {
        if (!notificationAudio) return;

        function play() {
            var startAt = notificationAudio.currentTime + 0.02;
            [880, 1175].forEach(function (frequency, index) {
                var toneStart = startAt + index * 0.16;
                var oscillator = notificationAudio.createOscillator();
                var gain = notificationAudio.createGain();
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(frequency, toneStart);
                gain.gain.setValueAtTime(0.0001, toneStart);
                gain.gain.exponentialRampToValueAtTime(0.14, toneStart + 0.025);
                gain.gain.exponentialRampToValueAtTime(0.0001, toneStart + 0.3);
                oscillator.connect(gain);
                gain.connect(notificationAudio.destination);
                oscillator.start(toneStart);
                oscillator.stop(toneStart + 0.31);
            });
        }

        if (notificationAudio.state === 'suspended') {
            notificationAudio.resume().then(play).catch(function (error) {
                console.warn('Не удалось воспроизвести сигнал нового сообщения.', error);
            });
        } else {
            play();
        }
    }

    function showWelcomeMessage(messageText) {
        var container = document.getElementById('consultantMessages');
        if (!container) return;
        container.innerHTML = '<div class="consultant-empty">' + escapeHtml(messageText || 'Здравствуйте! Чем могу помочь?') + '</div>';
    }

    function getSavedSession() {
        try {
            var stored = localStorage.getItem(sessionKey);
            return stored ? JSON.parse(stored) : null;
        } catch (error) {
            console.error('Не удалось прочитать данные чата.', error);
            return null;
        }
    }

    function saveSession(session) {
        try {
            localStorage.setItem(sessionKey, JSON.stringify(session));
        } catch (error) {
            console.error('Не удалось сохранить данные чата в браузере.', error);
        }
    }

    function setReplyMode() {
        var nameInput = document.getElementById('consultantName');
        var nameGroup = nameInput ? nameInput.closest('.consultant-field-group') : null;
        var phoneGroup = document.getElementById('consultantPhoneGroup');
        var phoneInput = document.getElementById('consultantPhone');
        var label = document.querySelector('label[for="consultantQuestion"]');
        var input = document.getElementById('consultantQuestion');
        var submit = document.querySelector('#consultantForm .consultant-submit');
        if (nameGroup) nameGroup.hidden = true;
        if (phoneGroup) phoneGroup.hidden = true;
        if (nameInput) {
            nameInput.required = false;
            nameInput.disabled = true;
        }
        if (phoneInput) {
            phoneInput.required = false;
            phoneInput.disabled = true;
        }
        if (label) label.textContent = 'Сообщение';
        if (input) {
            input.placeholder = currentThreadClosed ? 'Чат закрыт' : 'Напишите сообщение...';
            input.disabled = currentThreadClosed;
        }
        if (submit) submit.disabled = currentThreadClosed;
        if (submit) submit.textContent = 'Отправить';
    }

    function setInitialFormMode() {
        var nameInput = document.getElementById('consultantName');
        var nameGroup = nameInput ? nameInput.closest('.consultant-field-group') : null;
        var phoneGroup = document.getElementById('consultantPhoneGroup');
        var phoneInput = document.getElementById('consultantPhone');
        var label = document.querySelector('label[for="consultantQuestion"]');
        var question = document.getElementById('consultantQuestion');

        if (nameGroup) nameGroup.hidden = false;
        if (phoneGroup) phoneGroup.hidden = false;
        if (nameInput) {
            nameInput.required = true;
            nameInput.disabled = false;
            nameInput.value = '';
        }
        if (phoneInput) {
            phoneInput.required = true;
            phoneInput.disabled = false;
            phoneInput.value = '';
        }
        if (label) label.textContent = 'Вопрос';
        if (question) {
            question.value = '';
            question.placeholder = 'Опишите задачу';
        }
        stopReplyBorderAlert();
        latestAdminMessageKey = null;
        hasLoadedThread = false;
        showWelcomeMessage(welcomeMessage);
    }

    function clearDeletedSession(session) {
        var currentSession = getSavedSession();
        if (!session || !currentSession || String(currentSession.userId) !== String(session.userId)) return;
        try {
            localStorage.removeItem(sessionKey);
        } catch (error) {
            console.error('Не удалось очистить удалённый чат.', error);
        }
        setInitialFormMode();
    }

    function clearClosedSession(session) {
        var currentSession = getSavedSession();
        if (!session || !currentSession || String(currentSession.userId) !== String(session.userId)) return;
        try {
            localStorage.removeItem(sessionKey);
        } catch (error) {
            console.error('Не удалось завершить закрытый чат.', error);
        }
        currentThreadClosed = false;
        setInitialFormMode();
    }

    function loadThread(session) {
        if (!session) return Promise.resolve();
        return jsonRequest('getThread', session).then(function (response) {
            var messages = response.messages || [];
            if (response.status === 'closed') {
                clearClosedSession(session);
                return;
            }
            currentThreadClosed = false;
            var latestAdminMessage = messages.filter(function (message) {
                return message.role === 'admin';
            }).slice(-1)[0];
            var nextAdminMessageKey = latestAdminMessage
                ? String(latestAdminMessage.id || latestAdminMessage.createdAt || latestAdminMessage.text)
                : null;
            var panel = document.getElementById('consultantPanel');
            var isPanelVisible = panel && panel.style.display !== 'none';

            if (hasLoadedThread && nextAdminMessageKey && nextAdminMessageKey !== latestAdminMessageKey) {
                playMessageNotification();
            }
            latestAdminMessageKey = nextAdminMessageKey;

            if (isPanelVisible) {
                markAdminMessageRead(session, nextAdminMessageKey);
                sendMessageStatus(session, 'viewed');
                stopReplyBorderAlert();
            } else if (nextAdminMessageKey &&
                nextAdminMessageKey !== getLastReadAdminMessageKey(session)) {
                startReplyBorderAlert();
            } else {
                stopReplyBorderAlert();
            }

            hasLoadedThread = true;

            renderMessages(messages, response.readStatus, response.typingRole, currentThreadClosed);
            setReplyMode();
        }).catch(function (error) {
            if (error.status === 404) {
                clearDeletedSession(session);
                return;
            }
            renderMessages([{ role: 'admin', text: error.message || 'Не удалось загрузить историю чата.' }]);
        });
    }

    function attachEvents(settings) {
        var toggle = document.getElementById('consultantToggle');
        var close = document.getElementById('consultantClose');
        var form = document.getElementById('consultantForm');
        if (!toggle || !close || !form) return;

        document.addEventListener('pointerdown', unlockNotificationAudio, { once: true });
        document.addEventListener('keydown', unlockNotificationAudio, { once: true });

        toggle.addEventListener('click', function () {
            var panel = document.getElementById('consultantPanel');
            if (!panel) return;
            var isVisible = panel.style.display !== 'none';
            setPanelVisibility(!isVisible);
        });

        close.addEventListener('click', function () {
            setPanelVisibility(false);
        });

        var questionInput = document.getElementById('consultantQuestion');
        if (questionInput) {
            questionInput.addEventListener('focus', function () {
                sendMessageStatus(getSavedSession(), 'read');
            });
            questionInput.addEventListener('click', function () {
                sendMessageStatus(getSavedSession(), 'read');
            });
            questionInput.addEventListener('input', function () {
                var session = getSavedSession();
                var isTyping = Boolean(session && questionInput.value.trim());
                sendTypingState(session, isTyping);
                if (typingHeartbeat) window.clearInterval(typingHeartbeat);
                if (isTyping) {
                    typingHeartbeat = window.setInterval(function () {
                        if (!questionInput.value.trim()) {
                            window.clearInterval(typingHeartbeat);
                            typingHeartbeat = null;
                            sendTypingState(session, false);
                            return;
                        }
                        sendTypingState(session, true, true);
                    }, 2000);
                } else {
                    typingHeartbeat = null;
                }
            });
            questionInput.addEventListener('blur', function () {
                if (typingHeartbeat) window.clearInterval(typingHeartbeat);
                typingHeartbeat = null;
                sendTypingState(getSavedSession(), false);
            });
            questionInput.addEventListener('keydown', function (event) {
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

            var username = document.getElementById('consultantName');
            var phone = document.getElementById('consultantPhone');
            var question = document.getElementById('consultantQuestion');
            var nameValue = username ? username.value.trim() : '';
            var phoneValue = phone ? phone.value.trim() : '';
            var questionValue = question ? question.value.trim() : '';

            if (!questionValue) {
                if (question) question.focus();
                return;
            }
            if (currentThreadClosed) return;
            if (typingHeartbeat) window.clearInterval(typingHeartbeat);
            typingHeartbeat = null;
            sendTypingState(getSavedSession(), false);

            var isOpen = isWithinWorkingHours(settings.workingHours || defaultSettings.workingHours);
            if (!isOpen) {
                renderMessages([{ role: 'admin', text: 'Консультант сейчас не принимает сообщения. Попробуйте написать в рабочее время: ' + (settings.workingHours || '09:00-20:00') + '.' }]);
                return;
            }

            var session = getSavedSession();
            var request = session
                ? jsonRequest('sendMessage', {
                    userId: session.userId,
                    token: session.token,
                    role: 'user',
                    message: questionValue
                })
                : jsonRequest('createThread', {
                    name: nameValue,
                    phone: phoneValue,
                    question: questionValue,
                    source: location.pathname
                });

            request.then(function (response) {
                if (!response || !response.success) {
                    throw new Error('Не удалось отправить сообщение.');
                }
                if (!session) {
                    session = { userId: response.userId, token: response.token };
                    saveSession(session);
                    setReplyMode();
                }
                if (question) question.value = '';
                return loadThread(session);
            }).catch(function (error) {
                renderMessages([{ role: 'admin', text: error.message || 'Не удалось отправить вопрос.' }]);
            });
        });

        var savedSession = getSavedSession();
        if (savedSession) {
            setReplyMode();
            loadThread(savedSession);
        }
        window.setInterval(function () {
            loadThread(getSavedSession());
        }, 2000);
    }

    function loadConsultant() {
        if (document.body.classList.contains('auth-page')) return;
        createChatWidget();

        jsonRequest('getSettings').then(function (response) {
            var settings = (response && response.settings) || defaultSettings;
            welcomeMessage = settings.welcomeMessage || defaultSettings.welcomeMessage;
            applyWidgetSettings(settings);
            attachEvents(settings);
            showWelcomeMessage(welcomeMessage);
        }).catch(function (error) {
            welcomeMessage = defaultSettings.welcomeMessage;
            applyWidgetSettings(defaultSettings);
            attachEvents(defaultSettings);
            showWelcomeMessage(error.message || welcomeMessage);
        });
    }

    document.addEventListener('DOMContentLoaded', loadConsultant);
}());
