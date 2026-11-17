(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var templateSelect = document.getElementById('templateName');
        var fileSelect = document.getElementById('templateFile');
        var themeSelect = document.getElementById('editorTheme');
        var saveButton = document.getElementById('saveTemplate');
        var reloadButton = document.getElementById('reloadTemplate');
        var previewButton = document.getElementById('togglePreview');
        var previewContainer = document.getElementById('previewContainer');
        var previewFrame = document.getElementById('templatePreview');
        var errorBox = document.getElementById('templateError');
        var activeBadge = document.getElementById('templateActiveBadge');
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
        var currentHash = '';
        var currentFile = null; // {type: 'file'|'asset'|'page', ...}
        var pageState = null;   // данные статической страницы (title, is_active, edit_id)
        var loading = false;
        var themeStorageKey = 'myblog.admin.templates.theme.v1';

        // Светлая (default) и тёмная (material-darker) темы CodeMirror
        try {
            var storedTheme = localStorage.getItem(themeStorageKey);
            if (storedTheme === 'default' || storedTheme === 'material-darker') {
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

        // Режим подсветки по типу файла
        function modeFor(file) {
            if (!file) return 'htmlmixed';
            if (file.type === 'page') return 'htmlmixed';
            if (file.type === 'asset') return file.path.toLowerCase().endsWith('.css') ? 'text/css' : 'javascript';
            var lower = file.path.toLowerCase();
            if (lower.endsWith('.css')) return 'text/css';
            if (lower.endsWith('.js')) return 'javascript';
            return 'application/x-httpd-php';
        }

        function loadTemplates() {
            return request('../api/admin/templates?action=templates', { credentials: 'same-origin' }).then(function (data) {
                templateSelect.textContent = '';
                (data.templates || []).forEach(function (name) {
                    var option = document.createElement('option');
                    option.value = name;
                    var labels = [];
                    if (data.active && data.active.frontend === name) labels.push('фронтенд');
                    if (data.active && data.active.admin === name) labels.push('админка');
                    option.textContent = labels.length ? name + ' (активен: ' + labels.join(' + ') + ')' : name;
                    templateSelect.appendChild(option);
                });
                updateActiveBadge(data.active);
            });
        }

        function updateActiveBadge(active) {
            if (!activeBadge) return;
            activeBadge.textContent = active
                ? 'Активный шаблон: ' + (active.frontend || 'default') + ' (сайт) / ' + (active.admin || 'default') + ' (админка)'
                : '';
        }

        function optionFor(value, label) {
            var option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            return option;
        }

        function loadFiles() {
            var template = templateSelect.value || 'default';
            showError('');
            return request('../api/admin/templates?action=files&template=' + encodeURIComponent(template), {
                credentials: 'same-origin'
            }).then(function (data) {
                fileSelect.textContent = '';
                var frontendGroup = document.createElement('optgroup');
                frontendGroup.label = 'PHP — фронтенд сайта';
                (data.php || []).forEach(function (file) {
                    if (file.area === 'frontend') {
                        frontendGroup.appendChild(optionFor('file:frontend:' + file.path, file.path));
                    }
                });
                var adminGroup = document.createElement('optgroup');
                adminGroup.label = 'PHP — админ-панель';
                (data.php || []).forEach(function (file) {
                    if (file.area === 'admin') {
                        adminGroup.appendChild(optionFor('file:admin:' + file.path, file.path));
                    }
                });
                var assetsGroup = document.createElement('optgroup');
                assetsGroup.label = 'Общие CSS и JS';
                (data.assets || []).forEach(function (asset) {
                    assetsGroup.appendChild(optionFor('asset:' + asset.path, asset.path));
                });
                if (frontendGroup.children.length) fileSelect.appendChild(frontendGroup);
                if (adminGroup.children.length) fileSelect.appendChild(adminGroup);
                if (assetsGroup.children.length) fileSelect.appendChild(assetsGroup);

                // Статические страницы — отдельной группой
                return request('../api/admin/pages', { credentials: 'same-origin' }).then(function (pagesData) {
                    var pagesGroup = document.createElement('optgroup');
                    pagesGroup.label = 'Статические страницы (база данных)';
                    (pagesData.pages || []).forEach(function (page) {
                        pagesGroup.appendChild(optionFor('page:' + page.slug, '/' + page.slug + '.html — ' + (page.title || page.slug)));
                    });
                    if (pagesGroup.children.length) fileSelect.appendChild(pagesGroup);
                    if (!fileSelect.children.length) {
                        fileSelect.appendChild(optionFor('', 'Файлы не найдены'));
                    }
                });
            });
        }

        function applyContent(content, hash) {
            editor.setValue(content);
            editor.setOption('mode', modeFor(currentFile));
            currentHash = hash || '';
            fileSelect.dataset.previous = fileSelect.value;
            saveButton.disabled = false;
            if (!previewContainer.hidden) updatePreview();
        }

        function loadFile() {
            if (!fileSelect.value || fileSelect.value.indexOf(':') === -1) return Promise.resolve();
            loading = true;
            saveButton.disabled = true;
            showError('');

            var parts = fileSelect.value.split(':');
            var type = parts[0];

            // PHP-файл шаблона
            if (type === 'file') {
                currentFile = { type: 'file', area: parts[1], path: parts.slice(2).join(':') };
                pageState = null;
                return request('../api/admin/templates?action=read&template=' + encodeURIComponent(templateSelect.value) +
                    '&area=' + encodeURIComponent(currentFile.area) + '&path=' + encodeURIComponent(currentFile.path), {
                    credentials: 'same-origin'
                }).then(function (data) {
                    applyContent(data.content, data.hash);
                }).catch(handleLoadError).finally(function () { loading = false; });
            }

            // Общий CSS/JS
            if (type === 'asset') {
                currentFile = { type: 'asset', path: parts.slice(1).join(':') };
                pageState = null;
                return request('../api/admin/templates?action=read&template=' + encodeURIComponent(templateSelect.value) +
                    '&area=assets&path=' + encodeURIComponent(currentFile.path), {
                    credentials: 'same-origin'
                }).then(function (data) {
                    applyContent(data.content, data.hash);
                }).catch(handleLoadError).finally(function () { loading = false; });
            }

            // Статическая страница из базы
            if (type === 'page') {
                var slug = parts.slice(1).join(':');
                currentFile = { type: 'page', slug: slug };
                return request('../api/admin/pages/' + encodeURIComponent(slug), { credentials: 'same-origin' })
                    .then(function (data) {
                        pageState = {
                            editId: data.page ? data.page.id : 0,
                            title: data.page ? data.page.title : slug,
                            isActive: data.page ? Number(data.page.is_active) === 1 : true
                        };
                        applyContent(data.page ? (data.page.html_content || '') : '', '');
                    }).catch(handleLoadError).finally(function () { loading = false; });
            }

            loading = false;
            return Promise.resolve();
        }

        function handleLoadError(error) {
            showError(error.message || 'Не удалось загрузить файл');
            window.AdminNotifications.show('error', error.message || 'Не удалось загрузить файл');
        }

        function saveFile() {
            if (loading || !currentFile) return;
            showError('');
            var content = editor.getValue();

            // AdminApi.save сам добавляет CSRF-токен и JSON-заголовок
            var requestPromise;
            if (currentFile.type === 'page') {
                requestPromise = window.AdminApi.save('/api/admin/pages', 'POST', {
                    slug: currentFile.slug,
                    title: (pageState && pageState.title) || currentFile.slug,
                    html_content: content,
                    is_active: pageState ? (pageState.isActive ? 1 : 0) : 1,
                    edit_id: pageState ? (pageState.editId || 0) : 0
                }).then(function (data) {
                    if (pageState) pageState.editId = data.id || pageState.editId;
                    currentHash = '';
                });
            } else {
                requestPromise = window.AdminApi.save('/api/admin/templates', 'POST', {
                    template: templateSelect.value,
                    area: currentFile.type === 'asset' ? 'assets' : currentFile.area,
                    path: currentFile.path,
                    content: content,
                    expectedHash: currentHash
                }).then(function (data) {
                    currentHash = data.hash || '';
                });
            }

            requestPromise.then(function () {
                savedFlash();
                window.AdminNotifications.show('success', 'Файл сохранён');
            }).catch(function (error) {
                showError(error.message || 'Не удалось сохранить файл');
                window.AdminNotifications.show('error', error.message || 'Не удалось сохранить файл');
            });
        }

        function savedFlash() {
            saveButton.disabled = false;
            if (!previewContainer.hidden) updatePreview();
        }

        function updatePreview() {
            var content = editor.getValue();
            if (currentFile && currentFile.type === 'asset' && currentFile.path.toLowerCase().endsWith('.css')) {
                previewFrame.setAttribute('srcdoc', '<style>' + content + '</style><div class="container py-4"><h1>Заголовок</h1><p>Пример текста и <a href="#">ссылка</a>.</p><button class="btn btn-primary">Кнопка</button></div>');
            } else if (currentFile && currentFile.type === 'asset') {
                previewFrame.setAttribute('srcdoc', '<script>try{' + content + '}catch(e){document.body.textContent="Ошибка JS: "+e.message;}<\/script>');
            } else {
                previewFrame.setAttribute('srcdoc', content);
            }
        }

        templateSelect.addEventListener('change', function () {
            loadFiles().then(function () {
                return loadFile();
            });
        });
        fileSelect.addEventListener('change', function () {
            if (fileSelect.dataset.previous === fileSelect.value) return;
            loadFile();
        });
        saveButton.addEventListener('click', saveFile);
        reloadButton.addEventListener('click', function () {
            loadFile();
        });
        previewButton.addEventListener('click', function () {
            previewContainer.hidden = !previewContainer.hidden;
            previewButton.setAttribute('aria-pressed', String(!previewContainer.hidden));
            if (!previewContainer.hidden) updatePreview();
        });

        loadTemplates()
            .then(loadFiles)
            .then(loadFile)
            .catch(function (error) {
                showError(error.message || 'Не удалось загрузить редактор');
            });
    });
}());
