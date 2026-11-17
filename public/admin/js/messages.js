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

        function render() {
            if (!messages.length) {
                list.innerHTML = '<div class="alert alert-light border">Обращений пока нет.</div>';
                return;
            }
            list.innerHTML = messages.map(function (message) {
                var heading = message.message_type === 'comment'
                    ? 'Комментарий к «' + (message.article_slug || 'статье') + '»'
                    : (message.subject || 'Обратная связь');
                var date = new Date(String(message.created_at).replace(' ', 'T'));
                var dateText = Number.isNaN(date.getTime()) ? '' : date.toLocaleString('ru-RU');
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
                    '<p class="fw-bold mt-3 mb-0 text-break">' + escapeHtml(message.body) + '</p></div></article>';
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
            var button = event.target.closest('[data-message-delete]');
            if (!button || !window.confirm('Удалить обращение?')) return;
            window.AdminApi.remove('/api/admin/messages/' + encodeURIComponent(button.dataset.messageDelete))
                .then(function () {
                    messages = messages.filter(function (item) { return String(item.id) !== button.dataset.messageDelete; });
                    render();
                    window.AdminNotifications.show('success', 'Обращение удалено');
                }).catch(function (error) {
                    window.AdminNotifications.show('error', error.message || 'Не удалось удалить обращение');
                });
        });

        window.AdminApi.get('/api/admin/messages').then(function (data) {
            messages = data.messages;
            render();
        }).catch(function (error) {
            errorBox.textContent = error.message || 'Не удалось загрузить обращения';
            errorBox.hidden = false;
            list.textContent = '';
            window.AdminNotifications.show('error', errorBox.textContent);
        });
    });
}());
