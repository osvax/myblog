// ============================================================
// Онлайн-консультант (виджет чата)
//
// ESM-порт legacy public/js/consultant.js — тот же API:
// один POST /api/consultant с полем action:
// getSettings, createThread, getThread, sendMessage, markRead, setTyping.
// Включён в app-бандл (resources/js/app.js) — грузится на всех
// frontend-страницах, как legacy-скрипт в dev-ветке.
// ============================================================

const endpoint = '/api/consultant';
const sessionKey = 'myblog.consultant.session.v1';
let latestAdminMessageKey = null;
let hasLoadedThread = false;
let replyBorderTimer = null;
let notificationAudio = null;
let notificationAudioUnlocked = false;
let typingHeartbeat = null;
let typingSession = null;
let typingActive = false;
let currentThreadClosed = false;
const lastMarkedMessageByLevel = {};
const defaultSettings = {
    enabled: true,
    buttonColor: '#ff8c1a',
    buttonTextColor: '#ffffff',
    buttonPosition: 'right',
    workingHours: '09:00-20:00',
    censoredWords: 'дурак, идиот, тупица',
    welcomeMessage: 'Здравствуйте! Чем могу помочь?'
};
let welcomeMessage = defaultSettings.welcomeMessage;

function jsonRequest(action, payload) {
    const init = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json; charset=utf-8'
        },
        body: JSON.stringify(Object.assign({ action: action }, payload || {}))
    };

    return fetch(endpoint, init).then(function (response) {
        return response.json().then(function (data) {
            if (!response.ok) {
                const error = new Error(data.message || 'Ошибка запроса к консультанту.');
                error.status = response.status;
                throw error;
            }
            return data;
        });
    });
}

function getCurrentTimeMinutes(value) {
    if (!value || typeof value !== 'string') return 0;
    const match = value.match(/(\d{1,2}):(\d{2})/g);
    if (!match || !match[0]) return 0;
    const parts = match[0].split(':');
    return Number(parts[0]) * 60 + Number(parts[1]);
}

function isWithinWorkingHours(value) {
    const range = (value || '09:00-20:00').trim();
    const match = range.match(/^(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})$/);
    if (!match) return true;

    const now = new Date();
    const currentMinutes = now.getHours() * 60 + now.getMinutes();
    const startMinutes = getCurrentTimeMinutes(match[1]);
    const endMinutes = getCurrentTimeMinutes(match[2]);

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
    const root = document.createElement('div');
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
    const widget = document.getElementById('consultantWidget');
    const fab = document.getElementById('consultantToggle');
    const panel = document.getElementById('consultantPanel');
    if (!widget || !fab || !panel) return;
    widget.hidden = settings.enabled === false;

    const position = settings.buttonPosition === 'left' ? 'left' : 'right';
    widget.dataset.position = position;
    fab.style.backgroundColor = settings.buttonColor || defaultSettings.buttonColor;
    fab.style.color = settings.buttonTextColor || defaultSettings.buttonTextColor;

    const workingHours = settings.workingHours || defaultSettings.workingHours;
    const isOpen = isWithinWorkingHours(workingHours);
    const status = document.getElementById('consultantStatus');
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
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleString('ru-RU', {
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit'
    });
}

function isMessageAtOrBefore(messageId, receiptId) {
    if (messageId === null || messageId === undefined || receiptId === null || receiptId === undefined) return false;
    const message = String(messageId);
    const receipt = String(receiptId);
    return message.length < receipt.length || (message.length === receipt.length && message <= receipt);
}

function renderMessages(messages, readStatus, typingRole, isClosed) {
    const container = document.getElementById('consultantMessages');
    if (!container) return;

    readStatus = readStatus || {};
    const rows = (messages || []).map(function (message) {
        const adminClass = message.role === 'admin' ? 'admin' : 'user';
        let recipientStatus = message.role === 'user' ? readStatus.admin : null;
        recipientStatus = recipientStatus || {};
        let receipt = '';
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
    const panel = document.getElementById('consultantPanel');
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
    const storageKey = getAdminMessageReadStorageKey(session);
    if (!storageKey) return null;
    try {
        return localStorage.getItem(storageKey);
    } catch (error) {
        console.error('Не удалось прочитать отметку о просмотре чата.', error);
        return null;
    }
}

function markAdminMessageRead(session, messageKey) {
    const storageKey = getAdminMessageReadStorageKey(session);
    if (!storageKey || !messageKey) return;
    try {
        localStorage.setItem(storageKey, messageKey);
    } catch (error) {
        console.error('Не удалось сохранить отметку о просмотре чата.', error);
    }
}

function sendMessageStatus(session, level) {
    if (!session || !session.userId || !latestAdminMessageKey) return;
    const stateKey = level + ':' + session.userId;
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
    const toggle = document.getElementById('consultantToggle');
    if (replyBorderTimer !== null) {
        window.clearInterval(replyBorderTimer);
        replyBorderTimer = null;
    }
    if (toggle) {
        toggle.classList.remove('has-consultant-reply', 'consultant-fab-alert-red');
    }
}

function startReplyBorderAlert() {
    const toggle = document.getElementById('consultantToggle');
    if (!toggle) return;
    toggle.classList.add('has-consultant-reply');
    if (replyBorderTimer !== null) return;

    let red = false;
    replyBorderTimer = window.setInterval(function () {
        const currentToggle = document.getElementById('consultantToggle');
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
    const AudioContextConstructor = window.AudioContext || window.webkitAudioContext;
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
        const startAt = notificationAudio.currentTime + 0.02;
        [880, 1175].forEach(function (frequency, index) {
            const toneStart = startAt + index * 0.16;
            const oscillator = notificationAudio.createOscillator();
            const gain = notificationAudio.createGain();
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
    const container = document.getElementById('consultantMessages');
    if (!container) return;
    container.innerHTML = '<div class="consultant-empty">' + escapeHtml(messageText || 'Здравствуйте! Чем могу помочь?') + '</div>';
}

function getSavedSession() {
    try {
        const stored = localStorage.getItem(sessionKey);
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
    const nameInput = document.getElementById('consultantName');
    const nameGroup = nameInput ? nameInput.closest('.consultant-field-group') : null;
    const phoneGroup = document.getElementById('consultantPhoneGroup');
    const phoneInput = document.getElementById('consultantPhone');
    const label = document.querySelector('label[for="consultantQuestion"]');
    const input = document.getElementById('consultantQuestion');
    const submit = document.querySelector('#consultantForm .consultant-submit');
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
    const nameInput = document.getElementById('consultantName');
    const nameGroup = nameInput ? nameInput.closest('.consultant-field-group') : null;
    const phoneGroup = document.getElementById('consultantPhoneGroup');
    const phoneInput = document.getElementById('consultantPhone');
    const label = document.querySelector('label[for="consultantQuestion"]');
    const question = document.getElementById('consultantQuestion');

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
    const currentSession = getSavedSession();
    if (!session || !currentSession || String(currentSession.userId) !== String(session.userId)) return;
    try {
        localStorage.removeItem(sessionKey);
    } catch (error) {
        console.error('Не удалось очистить удалённый чат.', error);
    }
    setInitialFormMode();
}

function clearClosedSession(session) {
    const currentSession = getSavedSession();
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
        const messages = response.messages || [];
        if (response.status === 'closed') {
            clearClosedSession(session);
            return;
        }
        currentThreadClosed = false;
        const latestAdminMessage = messages.filter(function (message) {
            return message.role === 'admin';
        }).slice(-1)[0];
        const nextAdminMessageKey = latestAdminMessage
            ? String(latestAdminMessage.id || latestAdminMessage.createdAt || latestAdminMessage.text)
            : null;
        const panel = document.getElementById('consultantPanel');
        const isPanelVisible = panel && panel.style.display !== 'none';

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
    const toggle = document.getElementById('consultantToggle');
    const close = document.getElementById('consultantClose');
    const form = document.getElementById('consultantForm');
    if (!toggle || !close || !form) return;

    document.addEventListener('pointerdown', unlockNotificationAudio, { once: true });
    document.addEventListener('keydown', unlockNotificationAudio, { once: true });

    toggle.addEventListener('click', function () {
        const panel = document.getElementById('consultantPanel');
        if (!panel) return;
        const isVisible = panel.style.display !== 'none';
        setPanelVisibility(!isVisible);
    });

    close.addEventListener('click', function () {
        setPanelVisibility(false);
    });

    const questionInput = document.getElementById('consultantQuestion');
    if (questionInput) {
        questionInput.addEventListener('focus', function () {
            sendMessageStatus(getSavedSession(), 'read');
        });
        questionInput.addEventListener('click', function () {
            sendMessageStatus(getSavedSession(), 'read');
        });
        questionInput.addEventListener('input', function () {
            const session = getSavedSession();
            const isTyping = Boolean(session && questionInput.value.trim());
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

        const username = document.getElementById('consultantName');
        const phone = document.getElementById('consultantPhone');
        const question = document.getElementById('consultantQuestion');
        const nameValue = username ? username.value.trim() : '';
        const phoneValue = phone ? phone.value.trim() : '';
        const questionValue = question ? question.value.trim() : '';

        if (!questionValue) {
            if (question) question.focus();
            return;
        }
        if (currentThreadClosed) return;
        if (typingHeartbeat) window.clearInterval(typingHeartbeat);
        typingHeartbeat = null;
        sendTypingState(getSavedSession(), false);

        const isOpen = isWithinWorkingHours(settings.workingHours || defaultSettings.workingHours);
        if (!isOpen) {
            renderMessages([{ role: 'admin', text: 'Консультант сейчас не принимает сообщения. Попробуйте написать в рабочее время: ' + (settings.workingHours || '09:00-20:00') + '.' }]);
            return;
        }

        const session = getSavedSession();
        const request = session
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
                const newSession = { userId: response.userId, token: response.token };
                saveSession(newSession);
                setReplyMode();
                return loadThread(newSession);
            }
            if (question) question.value = '';
            return loadThread(session);
        }).catch(function (error) {
            renderMessages([{ role: 'admin', text: error.message || 'Не удалось отправить вопрос.' }]);
        });
    });

    const savedSession = getSavedSession();
    if (savedSession) {
        setReplyMode();
        loadThread(savedSession);
    }
    window.setInterval(function () {
        loadThread(getSavedSession());
    }, 2000);
}

function loadConsultant() {
    createChatWidget();

    jsonRequest('getSettings').then(function (response) {
        const settings = (response && response.settings) || defaultSettings;
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
