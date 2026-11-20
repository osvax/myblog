(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('siteSettingsForm');
        if (!form) return;

        // ---------- Остальная часть настроек ----------

        // Загружаем актуальные настройки из базы данных;
        // localStorage используется только как запасной источник значений
        var applySettings = function (settings) {
            Array.from(form.elements).forEach(function (field) {
                if (!field.id || !Object.prototype.hasOwnProperty.call(settings, field.id)) return;
                if (field.type === 'checkbox') {
                    field.checked = Boolean(settings[field.id]);
                } else if (field.type === 'datetime-local') {
                    // В базе время хранится как «YYYY-MM-DD HH:MM», input ждёт «T»-разделитель
                    var v = String(settings[field.id] == null ? '' : settings[field.id]);
                    if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/.test(v)) v = v.replace(' ', 'T');
                    field.value = v;
                } else {
                    field.value = settings[field.id];
                }
            });
        };

        // Собрать значения всех полей формы в объект key → value
        var collectFormValues = function () {
            var values = {};
            Array.from(form.elements).forEach(function (field) {
                if (!field.id) return;
                values[field.id] = field.type === 'checkbox' ? field.checked : field.value.trim();
            });
            return values;
        };

        // Сохранить настройки (PUT); без уведомлений — их показывает вызывающий код
        var saveSiteSettings = function () {
            var updated = collectFormValues();
            return fetch('../api/auth/csrf', { credentials: 'same-origin' })
                .then(function (r) { if (!r.ok) throw new Error('Не удалось получить CSRF-токен'); return r.json(); })
                .then(function (csrf) {
                    return fetch('../api/admin/settings', {
                        method: 'PUT', credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json; charset=utf-8', 'X-CSRF-Token': csrf.token },
                        body: JSON.stringify(updated)
                    });
                })
                .then(function (r) { return r.json().then(function (d) { if (!r.ok || !d.success) throw new Error(d.error); return d; }); })
                .then(function () {
                    try { window.SiteSettings.save(updated); } catch (e) {}
                    return updated;
                });
        };

        fetch('../api/admin/settings', { credentials: 'same-origin' })
            .then(function (response) {
                if (!response.ok) throw new Error('Не удалось загрузить настройки.');
                return response.json();
            })
            .then(function (result) {
                applySettings(Object.assign({}, window.SiteSettings.load(), (result && result.settings) || {}));
            })
            .catch(function (error) {
                console.error(error.message || 'Не удалось загрузить настройки из базы.');
                applySettings(window.SiteSettings.load());
            });

        // Загружаем настройки консультанта
        fetch('../api/consultant', {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json; charset=utf-8' },
            body: JSON.stringify({ action: 'getAdminSettings' })
        }).then(function (response) {
            if (!response.ok) throw new Error('Не удалось загрузить настройки консультанта.');
            return response.json();
        }).then(function (result) {
            var values = {
                consultantEnabled: result.settings.enabled, consultantWorkingHours: result.settings.workingHours,
                consultantButtonColor: result.settings.buttonColor, consultantButtonTextColor: result.settings.buttonTextColor,
                consultantButtonPosition: result.settings.buttonPosition, welcomeMessage: result.settings.welcomeMessage,
                censoredWords: result.settings.censoredWords, notificationEmail: result.settings.notificationEmail
            };
            Object.keys(values).forEach(function (id) {
                var field = document.getElementById(id);
                if (!field || values[id] === undefined || values[id] === null) return;
                if (field.type === 'checkbox') field.checked = Boolean(values[id]);
                else field.value = values[id];
            });
        }).catch(function (error) { console.error(error.message || 'Не удалось загрузить настройки консультанта.'); });

        function saveConsultantSettings() {
            var normalized = {
                enabled: document.getElementById('consultantEnabled') ? document.getElementById('consultantEnabled').checked : true,
                workingHours: document.getElementById('consultantWorkingHours') ? document.getElementById('consultantWorkingHours').value.trim() : '09:00-20:00',
                buttonColor: document.getElementById('consultantButtonColor') ? document.getElementById('consultantButtonColor').value : '#ff8c1a',
                buttonTextColor: document.getElementById('consultantButtonTextColor') ? document.getElementById('consultantButtonTextColor').value : '#ffffff',
                buttonPosition: document.getElementById('consultantButtonPosition') ? document.getElementById('consultantButtonPosition').value : 'right',
                welcomeMessage: document.getElementById('consultantWelcomeMessage') ? document.getElementById('consultantWelcomeMessage').value.trim() : 'Здравствуйте! Чем могу помочь?',
                censoredWords: document.getElementById('consultantCensoredWords') ? document.getElementById('consultantCensoredWords').value.trim() : '',
                notificationEmail: document.getElementById('consultantNotificationEmail') ? document.getElementById('consultantNotificationEmail').value.trim() : ''
            };
            return fetch('../api/auth/csrf', { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (csrf) {
                    return fetch('../api/consultant', {
                        method: 'POST', credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json; charset=utf-8', 'X-CSRF-Token': csrf.token },
                        body: JSON.stringify({ action: 'saveSettings', settings: normalized })
                    });
                })
                .then(function (r) { return r.json().then(function (d) { if (!r.ok || !d.success) throw new Error(d.message); return d; }); });
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            if (!form.reportValidity()) {
                // Видимая ошибка вместо молчаливого отказа
                window.AdminNotifications.show(false, 'Проверьте выделенные браузером поля — в них ошибки заполнения.');
                return;
            }

            saveSiteSettings()
                .then(function () {
                    var consultantPane = document.getElementById('consultant-pane');
                    var consultantIsActive = consultantPane && consultantPane.classList.contains('active');
                    var saveResult = consultantIsActive ? saveConsultantSettings() : Promise.resolve();
                    return saveResult.then(function () { window.AdminNotifications.show(true, 'Настройки сохранены'); });
                })
                .catch(function (error) {
                    window.AdminNotifications.show(false, error.message || 'Не удалось сохранить настройки.');
                    console.error('Не удалось сохранить настройки сайта.', error);
                });
        });

        // ---------- Тест отправки почты ----------
        // Сначала сохраняет текущие значения вкладки Email, потом проверяет канал.
        var testBtn = document.getElementById('testEmailBtn');
        if (testBtn) {
            testBtn.addEventListener('click', function () {
                var statusEl = document.getElementById('testEmailStatus');
                testBtn.disabled = true;
                if (statusEl) statusEl.textContent = 'Отправляем тестовое письмо…';
                saveSiteSettings()
                    .then(function () {
                        return fetch('../api/auth/csrf', { credentials: 'same-origin' })
                            .then(function (r) { return r.json(); })
                            .then(function (csrf) {
                                return fetch('../api/admin/settings/test-email', {
                                    method: 'POST', credentials: 'same-origin',
                                    headers: { 'X-CSRF-Token': csrf.token }
                                });
                            });
                    })
                    .then(function (r) { return r.json().then(function (d) { if (!r.ok) throw new Error(d.error || 'Ошибка тестовой отправки'); return d; }); })
                    .then(function (d) {
                        if (statusEl) statusEl.textContent = d.message || 'Готово';
                        window.AdminNotifications.show(true, d.message || 'Тестовое письмо отправлено');
                    })
                    .catch(function (error) {
                        if (statusEl) statusEl.textContent = 'Ошибка';
                        window.AdminNotifications.show(false, error.message || 'Не удалось отправить тест.');
                    })
                    .then(function () { testBtn.disabled = false; });
            });
        }

        // ---------- Журнал событий («Логи») ----------
        var logsTypeLabels = { auth: 'Входы', forms: 'Формы', security: 'Безопасность', errors: 'Ошибки' };

        function escapeHtml(value) {
            return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function renderLogs(items, total) {
            var tbody = document.querySelector('#logsTable tbody');
            if (!tbody) return;
            var totalEl = document.getElementById('logsTotal');
            if (totalEl) totalEl.textContent = '— всего записей: ' + (total || 0);
            if (!items || !items.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Журнал пуст</td></tr>';
                return;
            }
            tbody.innerHTML = items.map(function (row) {
                return '<tr>' +
                    '<td class="text-nowrap">' + escapeHtml(row.created_at) + '</td>' +
                    '<td>' + escapeHtml(logsTypeLabels[row.log_type] || row.log_type) + '</td>' +
                    '<td>' + escapeHtml(row.message) + '</td>' +
                    '<td>' + escapeHtml(row.user_id ? '#' + row.user_id : '—') + '</td>' +
                    '<td class="font-monospace">' + escapeHtml(row.ip) + '</td>' +
                    '</tr>';
            }).join('');
        }

        function loadLogs() {
            return fetch('../api/admin/settings/logs', { credentials: 'same-origin' })
                .then(function (r) { if (!r.ok) throw new Error('Не удалось загрузить журнал'); return r.json(); })
                .then(function (d) { renderLogs(d.items, d.total); })
                .catch(function (error) {
                    renderLogs([], 0);
                    console.error('Не удалось загрузить журнал:', error.message);
                });
        }

        loadLogs();

        var clearBtn = document.getElementById('clearLogsBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (!window.confirm('Очистить весь журнал событий? Действие необратимо.')) return;
                fetch('../api/auth/csrf', { credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (csrf) {
                        return fetch('../api/admin/settings/logs/clear', {
                            method: 'POST', credentials: 'same-origin',
                            headers: { 'X-CSRF-Token': csrf.token }
                        });
                    })
                    .then(function (r) { return r.json().then(function (d) { if (!r.ok || !d.success) throw new Error(d.error); return d; }); })
                    .then(function (d) {
                        window.AdminNotifications.show(true, 'Журнал очищен (удалено записей: ' + (d.deleted || 0) + ')');
                        loadLogs();
                    })
                    .catch(function (error) {
                        window.AdminNotifications.show(false, error.message || 'Не удалось очистить журнал.');
                    });
            });
        }
    });
}());
