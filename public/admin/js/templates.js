(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var fileSelect = document.getElementById('templateFile');
        var themeSelect = document.getElementById('editorTheme');
        var saveButton = document.getElementById('saveTemplate');
        var reloadButton = document.getElementById('reloadTemplate');
        var previewButton = document.getElementById('togglePreview');
        var previewContainer = document.getElementById('previewContainer');
        var previewFrame = document.getElementById('templatePreview');
        var errorBox = document.getElementById('templateError');
        var editor = CodeMirror.fromTextArea(document.getElementById('templateEditor'), {
            mode: 'htmlmixed',
            lineNumbers: true,
            lineWrapping: false,
            indentUnit: 4,
            tabSize: 4,
            indentWithTabs: false,
            autoCloseBrackets: true,
            matchBrackets: true
        });
        var csrfToken = '';
        var currentHash = '';
        var savedContent = '';
        var loading = false;
        var themeStorageKey = 'myblog.admin.templates.theme.v1';

        try {
            var storedTheme = localStorage.getItem(themeStorageKey);
            if (storedTheme === 'default' || storedTheme === 'dracula') {
                themeSelect.value = storedTheme;
            }
        } catch (error) {
            console.error('Не удалось загрузить тему редактора.', error);
        }
        editor.setOption('theme', themeSelect.value);
        themeSelect.addEventListener('change', function () {
            editor.setOption('theme', themeSelect.value);
            try {
                localStorage.setItem(themeStorageKey, themeSelect.value);
            } catch (error) {
                console.error('Не удалось сохранить тему редактора.', error);
                window.AdminNotifications.show('warning', 'Выбор темы применён, но не сохранён');
            }
        });

        function showError(message) {
            errorBox.textContent = message;
            errorBox.hidden = !message;
        }

        function request(url, options) {
            return fetch(url, options).then(function (response) {
                return response.json().then(function (data) {
                    if (response.status === 401) {
                        window.location.href = 'login.html';
                        throw new Error('Войдите в админ-панель заново');
                    }
                    if (!response.ok || !data.success) throw new Error(data.error || 'Не удалось выполнить запрос');
                    return data;
                });
            });
        }

        function loadFile() {
            if (!fileSelect.value) return Promise.resolve();
            loading = true;
            saveButton.disabled = true;
            showError('');
            return request('../api/admin/templates?action=read&file=' + encodeURIComponent(fileSelect.value), {
                credentials: 'same-origin'
            }).then(function (data) {
                editor.setValue(data.content);
                savedContent = data.content;
                currentHash = data.hash;
                fileSelect.dataset.previous = fileSelect.value;
                saveButton.disabled = false;
                if (!previewContainer.hidden) updatePreview();
            }).catch(function (error) {
                showError(error.message);
                window.AdminNotifications.show(false, error.message);
            }).finally(function () {
                loading = false;
            });
        }

        function updatePreview() {
            var previewDocument = new DOMParser().parseFromString(editor.getValue(), 'text/html');
            var base = previewDocument.createElement('base');
            base.href = new URL('../', window.location.href).href;
            previewDocument.head.prepend(base);
            previewFrame.srcdoc = '<!doctype html>\n' + previewDocument.documentElement.outerHTML;
        }

        request('../api/auth/csrf', { credentials: 'same-origin' }).then(function (data) {
            csrfToken = data.token;
            return request('../api/admin/templates?action=list', { credentials: 'same-origin' });
        }).then(function (data) {
            fileSelect.innerHTML = data.files.map(function (file) {
                return '<option value="' + file.replace(/[&<>"']/g, '') + '">' + file.replace(/[&<>"']/g, '') + '</option>';
            }).join('');
            return loadFile();
        }).catch(function (error) {
            showError(error.message);
            window.AdminNotifications.show(false, error.message);
        });

        fileSelect.addEventListener('change', function () {
            fileSelect.dataset.previous = fileSelect.value;
            loadFile();
        });

        reloadButton.addEventListener('click', function () {
            if (editor.getValue() !== savedContent &&
                !window.confirm('Перезагрузить файл и потерять несохранённые изменения?')) return;
            loadFile();
        });

        saveButton.addEventListener('click', function () {
            if (loading || !csrfToken) {
                window.AdminNotifications.show(false, 'Редактор ещё загружается. Обновите страницу, если проблема повторится.');
                return;
            }
            saveButton.disabled = true;
            showError('');
            request('../api/admin/templates', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    file: fileSelect.value,
                    content: editor.getValue(),
                    expectedHash: currentHash
                })
            }).then(function (data) {
                savedContent = editor.getValue();
                currentHash = data.hash;
                window.AdminNotifications.show(true, 'Шаблон сохранён. Резервная копия создана.');
            }).catch(function (error) {
                showError(error.message);
                window.AdminNotifications.show(false, error.message);
            }).finally(function () {
                saveButton.disabled = false;
            });
        });

        previewButton.addEventListener('click', function () {
            var show = previewContainer.hidden;
            editor.getWrapperElement().hidden = show;
            previewContainer.hidden = !show;
            previewButton.setAttribute('aria-pressed', String(show));
            previewButton.innerHTML = show
                ? '<i class="bi bi-eye-slash"></i>'
                : '<i class="bi bi-eye"></i>';
            previewButton.setAttribute('aria-label', show ? 'Скрыть предпросмотр' : 'Предпросмотр');
            previewButton.title = show ? 'Скрыть предпросмотр' : 'Предпросмотр';
            if (show) {
                updatePreview();
            } else {
                window.requestAnimationFrame(function () {
                    editor.refresh();
                });
            }
            window.AdminNotifications.show('info', show ? 'Предпросмотр включён' : 'Предпросмотр скрыт');
        });

        editor.on('change', function () {
            if (!previewContainer.hidden) updatePreview();
        });

        window.addEventListener('beforeunload', function (event) {
            if (editor.getValue() !== savedContent) {
                event.preventDefault();
                event.returnValue = '';
            }
        });
    });
}());
