(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var list = document.getElementById('messagesList');
        var errorBox = document.getElementById('messagesError');
        var messages = [];

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function (char) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
            });
        }

        function formatDate(value) {
            var date = new Date(String(value).replace(' ', 'T'));
            return Number.isNaN(date.getTime()) ? '' : date.toLocaleString('ru-RU');
        }

        // Разметка ответа: отдельная карточка с тумблером «Одобрен» и удалением
        function renderReply(reply) {
            var isStaff = Number(reply.is_staff) === 1;
            return '<div class="card mb-2" data-reply-card="' + reply.id + '"><div class="card-body p-3">' +
                '<div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">' +
                '<div class="d-flex align-items-center gap-2 flex-wrap">' +
                '<i class="bi ' + (isStaff ? 'bi-person-badge' : 'bi-person-circle') + ' text-orange"></i>' +
                '<strong class="fw-bold">' + escapeHtml(reply.name) + '</strong>' +
                (isStaff ? '<span class="badge bg-orange fw-bold">Админ</span>' : '<span class="badge bg-secondary fw-bold">Ответ</span>') +
                '<small class="fw-bold text-muted">' + escapeHtml(formatDate(reply.created_at)) + '</small>' +
                '</div>' +
                '<div class="d-flex align-items-center gap-2">' +
                '<label class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" data-message-approved="' +
                reply.id + '"' + (reply.is_approved ? ' checked' : '') + '><span class="form-check-label small fw-bold">Одобрен</span></label>' +
                '<button type="button" class="btn btn-outline-danger btn-sm" data-message-delete="' + reply.id +
                '" aria-label="Удалить ответ"><i class="bi bi-trash"></i></button>' +
                '</div></div>' +
                '<p class="fw-bold mt-2 mb-0 text-break">' + escapeHtml(reply.body) + '</p>' +
                '</div></div>';
        }

        // Разметка формы ответа автора сайта
        function renderReplyForm(message) {
            return '<div class="card border-0 shadow-sm p-3 mt-2 mb-2" data-reply-wrap="' + message.id + '" hidden>' +
                '<div class="row g-2">' +
                '<div class="col-md-5"><label class="form-label fw-bold" for="replyAuthorName-' + message.id + '">Имя автора</label>' +
                '<input type="text" class="form-control" id="replyAuthorName-' + message.id + '" value="Автор" maxlength="160"></div>' +
                '<div class="col-12"><label class="form-label fw-bold" for="replyAuthorBody-' + message.id + '">Текст ответа</label>' +
                '<textarea class="form-control" id="replyAuthorBody-' + message.id + '" rows="3" maxlength="10000" placeholder="Ответ пользователю..."></textarea></div>' +
                '<div class="col-12"><button type="button" class="btn btn-orange btn-sm fw-bold" data-reply-send="' + message.id + '">' +
                '<i class="bi bi-send"></i> Ответить</button></div>' +
                '</div></div>';
        }

        function render() {
            if (!messages.length) {
                list.innerHTML = '<div class="alert alert-light border">Обращений пока нет.</div>';
                return;
            }

            // Группируем ответы по родительскому комментарию
            var repliesByParent = {};
            messages.forEach(function (message) {
                if (message.parent_id) {
                    var key = String(message.parent_id);
                    if (!repliesByParent[key]) repliesByParent[key] = [];
                    repliesByParent[key].push(message);
                }
            });
            var roots = messages.filter(function (message) { return !message.parent_id; });

            list.innerHTML = roots.map(function (message) {
                var isComment = message.message_type === 'comment';
                var heading = isComment
                    ? 'Комментарий к «' + (message.article_slug || 'статье') + '»'
                    : (message.subject || 'Обратная связь');
                var dateText = formatDate(message.created_at);
                var replyList = repliesByParent[String(message.id)] || [];
                var replies = replyList.map(renderReply).join('');

                return '<article class="card admin-card mb-3" data-message="' + message.id + '"><div class="card-body">' +
                    '<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap"><div>' +
                    '<h2 class="h6 fw-bold mb-1">' + escapeHtml(message.name) + ' <span class="badge ' +
                    (message.is_read ? 'bg-secondary' : 'bg-orange') + '">' + (message.is_read ? 'Прочитано' : 'Новое') + '</span></h2>' +
                    '<small class="fw-bold text-muted"><i class="bi bi-envelope"></i> ' + escapeHtml(message.email) +
                    (message.phone ? ' · ' + escapeHtml(message.phone) : '') + ' · ' + escapeHtml(heading) +
                    ' · ' + escapeHtml(dateText) + '</small></div><div class="d-flex align-items-center gap-2">' +
                    '<label class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" data-message-approved="' +
                    message.id + '"' + (message.is_approved ? ' checked' : '') + '><span class="form-check-label small fw-bold">Одобрен</span></label>' +
                    '<button type="button" class="btn btn-danger btn-action" data-message-delete="' + message.id +
                    '" aria-label="Удалить обращение"><i class="bi bi-trash"></i></button></div></div>' +
                    '<p class="fw-bold mt-3 mb-1 text-break">' + escapeHtml(message.body) + '</p>' +
                    (isComment
                        ? '<button type="button" class="btn btn-outline-primary btn-sm" data-reply-toggle="' + message.id + '"><i class="bi bi-reply"></i> Ответить</button>' + renderReplyForm(message)
                        : '') +
                    (replyList.length
                        ? '<div class="mt-3"><h3 class="h6 fw-bold mb-2"><i class="bi bi-chat-left-text text-orange"></i> Ответы (' + replyList.length + ')</h3>' + replies + '</div>'
                        : '') +
                    '</div></article>';
            }).join('');
        }

        function updateMessage(message, approved) {
            return window.AdminApi.save('/api/admin/messages/' + encodeURIComponent(message.id), 'PATCH', {
                isRead: true,
                isApproved: approved
            }).then(function () {
                message.isRead = true;
                message.is_read = 1;
                message.isApproved = approved;
                message.is_approved = approved ? 1 : 0;
                render();
            });
        }

        function loadMessages() {
            return window.AdminApi.get('/api/admin/messages').then(function (data) {
                messages = data.messages || [];
                render();
            });
        }

        function sendReply(messageId) {
            var wrap = list.querySelector('[data-reply-wrap="' + messageId + '"]');
            if (!wrap) return;
            var nameField = document.getElementById('replyAuthorName-' + messageId);
            var bodyField = document.getElementById('replyAuthorBody-' + messageId);
            var name = nameField ? nameField.value.trim() : '';
            var body = bodyField ? bodyField.value.trim() : '';

            if (name.length < 2) {
                window.AdminNotifications.show('error', 'Укажите имя автора (от 2 символов)');
                return;
            }
            if (body === '') {
                window.AdminNotifications.show('error', 'Введите текст ответа');
                return;
            }

            window.AdminApi.save('/api/admin/messages/' + encodeURIComponent(messageId) + '/reply', 'POST', {
                name: name,
                body: body
            }).then(function () {
                window.AdminNotifications.show('success', 'Ответ опубликован');
                return loadMessages();
            }).catch(function (error) {
                window.AdminNotifications.show('error', error.message || 'Не удалось отправить ответ');
            });
        }

        list.addEventListener('change', function (event) {
            var input = event.target.closest('[data-message-approved]');
            if (!input) return;
            var message = messages.find(function (item) { return String(item.id) === input.dataset.messageApproved; });
            if (!message) return;
            updateMessage(message, input.checked).catch(function (error) {
                input.checked = !input.checked;
                window.AdminNotifications.show('error', error.message || 'Не удалось сохранить статус обращения');
            });
        });

        list.addEventListener('click', function (event) {
            var replyButton = event.target.closest('[data-reply-toggle]');
            if (replyButton) {
                var replyWrap = list.querySelector('[data-reply-wrap="' + replyButton.dataset.replyToggle + '"]');
                if (replyWrap) {
                    replyWrap.hidden = !replyWrap.hidden;
                    if (!replyWrap.hidden) {
                        var bodyField = document.getElementById('replyAuthorBody-' + replyButton.dataset.replyToggle);
                        if (bodyField) bodyField.focus();
                    }
                }
                return;
            }

            var sendButton = event.target.closest('[data-reply-send]');
            if (sendButton) {
                sendReply(sendButton.dataset.replySend);
                return;
            }

            var button = event.target.closest('[data-message-delete]');
            if (!button || !window.confirm('Удалить обращение?')) return;
            window.AdminApi.remove('/api/admin/messages/' + encodeURIComponent(button.dataset.messageDelete))
                .then(function () {
                    return loadMessages();
                }).then(function () {
                    window.AdminNotifications.show('success', 'Обращение удалено');
                }).catch(function (error) {
                    window.AdminNotifications.show('error', error.message || 'Не удалось удалить обращение');
                });
        });

        loadMessages().catch(function (error) {
            errorBox.textContent = error.message || 'Не удалось загрузить обращения';
            errorBox.hidden = false;
            list.textContent = '';
            window.AdminNotifications.show('error', errorBox.textContent);
        });
    });
}());
