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
                } else {
                    field.value = settings[field.id];
                }
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

            var updated = {};
            Array.from(form.elements).forEach(function (field) {
                if (!field.id) return;
                updated[field.id] = field.type === 'checkbox' ? field.checked : field.value.trim();
            });

            fetch('../api/auth/csrf', { credentials: 'same-origin' })
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
                    var consultantPane = document.getElementById('consultant-pane');
                    var consultantIsActive = consultantPane && consultantPane.classList.contains('active');
                    var saveResult = consultantIsActive ? saveConsultantSettings() : Promise.resolve();
                    saveResult.then(function () { window.AdminNotifications.show(true, 'Настройки сохранены'); })
                        .catch(function (error) { window.AdminNotifications.show(false, error.message || 'Не удалось сохранить настройки.'); });
                })
                .catch(function (error) {
                    window.AdminNotifications.show(false, error.message || 'Не удалось сохранить настройки.');
                    console.error('Не удалось сохранить настройки сайта.', error);
                });
        });
    });
}());
