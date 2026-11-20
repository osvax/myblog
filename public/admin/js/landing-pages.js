(function () {
    'use strict';

    // Заготовка нового тела: полный HTML-документ в стиле сайта.
    // Плейсхолдеры {{menu:...}}, {{field:...}}, {{asset:...}}
    // подставляются сервером при открытии страницы — см. render_static_page().
    var PAGE_STARTER = [
        '<!DOCTYPE html>',
        '<html lang="ru">',
        '<head>',
        '    <meta charset="UTF-8">',
        '    <meta name="viewport" content="width=device-width, initial-scale=1.0">',
        '    <title>Заголовок страницы</title>',
        '    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">',
        '    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">',
        '    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">',
        '</head>',
        '<body>',
        '    <!-- Меню сайта (раздел «Меню»): замените main на имя своего меню -->',
        '    <nav class="bg-white border-bottom py-3">',
        '        <div class="container d-flex justify-content-end">',
        '            {{menu:main}}',
        '        </div>',
        '    </nav>',
        '',
        '    <main class="py-5">',
        '        <div class="container">',
        '            <h1 class="fw-bold">Заголовок страницы</h1>',
        '            <p>Текст страницы. Сюда можно вставить любой HTML: блоки, таблицы, изображения.</p>',
        '            <!-- Пользовательское поле (раздел «Пользовательские поля»): {{field:...}} -->',
        '            <!-- Общий CSS/JS (раздел «Шаблоны»): {{asset:css/style.css}} -->',
        '        </div>',
        '    </main>',
        '',
        '    <footer class="bg-dark text-white py-4">',
        '        <div class="container text-center">',
        '            <p class="mb-0">© ' + (new Date().getFullYear()) + '. Все права защищены.</p>',
        '        </div>',
        '    </footer>',
        '',
        '    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></' + 'script>',
        '</body>',
        '</html>'
    ].join('\n');

    document.addEventListener('DOMContentLoaded', function () {
        var pagesTableBody = document.getElementById('pagesTableBody');
        if (!pagesTableBody) return;

        var generateBtn = document.getElementById('generateBtn');
        var addPageBtn = document.getElementById('addPageBtn');
        var pageEditor = document.getElementById('pageEditor');
        var pageForm = document.getElementById('pageForm');
        var pageEditId = document.getElementById('pageEditId');
        var pageSlug = document.getElementById('pageSlug');
        var pageTitle = document.getElementById('pageTitle');
        var pageActive = document.getElementById('pageActive');
        var pageEditorTitle = document.getElementById('pageEditorTitle');
        var closeEditor = document.getElementById('closeEditor');
        var cancelPageBtn = document.getElementById('cancelPageBtn');
        var fillStarterBtn = document.getElementById('fillStarterBtn');
        var pageHtmlBody = document.getElementById('pageHtmlBody');
        var themeSelect = document.getElementById('editorTheme');
        var pagesError = document.getElementById('pagesError');
        var pagesSuccess = document.getElementById('pagesSuccess');

        var currentPages = [];

        // Код-редактор тела (CodeMirror — подключён для страницы в admin-pages.php);
        // без него — обычный textarea. Инициализируется лениво при первом открытии
        // редактора: в скрытом контейнере CodeMirror некорректно измеряет размеры
        // и тело «не появляется» до повторного нажатия.
        var bodyEditor = null;

        // Единая тема редактора CodeMirror для «Страниц» и «Шаблонов»
        var themeStorageKey = 'myblog.admin.codemirror.theme.v1';

        function currentTheme() {
            try {
                var stored = localStorage.getItem(themeStorageKey);
                if (stored === 'default' || stored === 'material-darker') return stored;
            } catch (error) { /* localStorage недоступен — берём текущий выбор */ }
            return themeSelect && themeSelect.value === 'material-darker' ? 'material-darker' : 'default';
        }

        function ensureBodyEditor() {
            if (bodyEditor || !window.CodeMirror || !pageHtmlBody) return;
            bodyEditor = CodeMirror.fromTextArea(pageHtmlBody, {
                mode: 'htmlmixed',
                theme: currentTheme(),
                lineNumbers: true,
                lineWrapping: false,
                indentUnit: 4,
                tabSize: 4,
                indentWithTabs: false
            });
        }

        function getBody() {
            if (bodyEditor) return bodyEditor.getValue();
            return pageHtmlBody ? pageHtmlBody.value : '';
        }

        function setBody(value) {
            if (bodyEditor) {
                bodyEditor.setValue(value);
            } else if (pageHtmlBody) {
                pageHtmlBody.value = value;
            }
        }

        // ---------- Вспомогательные функции ----------
        function showError(msg) {
            pagesError.textContent = msg;
            pagesError.style.display = 'block';
            setTimeout(function () { pagesError.style.display = 'none'; }, 5000);
        }

        function showSuccess(msg) {
            pagesSuccess.textContent = msg;
            pagesSuccess.style.display = 'block';
            setTimeout(function () { pagesSuccess.style.display = 'none'; }, 3000);
        }

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function findPageById(pageId) {
            return currentPages.find(function (p) { return String(p.id) === String(pageId); });
        }

        // ---------- Загрузка страниц ----------
        function loadPages() {
            pagesTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Загрузка…</td></tr>';

            window.AdminApi.get('/api/admin/pages').then(function (data) {
                currentPages = data.pages || [];
                renderPages();
                if (currentPages.length > 0) {
                    generateBtn.disabled = false;
                }
            }).catch(function (error) {
                pagesTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Ошибка: ' + escapeHtml(error.message) + '</td></tr>';
            });
        }

        // ---------- Отрисовка страниц ----------
        function renderPages() {
            if (currentPages.length === 0) {
                pagesTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Страниц нет</td></tr>';
                return;
            }

            var html = '';
            currentPages.forEach(function (page) {
                html += '<tr>' +
                    '<td class="fw-bold">' + escapeHtml(page.slug) + '</td>' +
                    '<td>' + escapeHtml(page.title) + '</td>' +
                    '<td class="text-center">' +
                        '<div class="form-check form-switch d-flex justify-content-center">' +
                            '<input class="form-check-input page-toggle" type="checkbox" ' + (page.is_active ? 'checked' : '') + ' data-id="' + page.id + '">' +
                        '</div>' +
                    '</td>' +
                    '<td class="text-end">' +
                        '<button type="button" class="btn btn-outline-primary btn-sm page-edit-btn me-1" data-id="' + page.id + '" title="Редактировать" aria-label="Редактировать страницу ' + escapeHtml(page.slug) + '"><i class="bi bi-pencil"></i></button> ' +
                        '<button type="button" class="btn btn-outline-info btn-sm page-preview-btn me-1" data-id="' + page.id + '" title="Просмотр" aria-label="Просмотр страницы ' + escapeHtml(page.slug) + '"><i class="bi bi-eye"></i></button> ' +
                        '<button type="button" class="btn btn-outline-danger btn-sm page-delete-btn" data-id="' + page.id + '" title="Удалить" aria-label="Удалить страницу ' + escapeHtml(page.slug) + '"><i class="bi bi-trash"></i></button>' +
                    '</td>' +
                    '</tr>';
            });
            pagesTableBody.innerHTML = html;

            // Обработчики: id берём из замыкания btn (this в колбэке find — не элемент)
            pagesTableBody.querySelectorAll('.page-edit-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var page = findPageById(btn.dataset.id);
                    if (page) {
                        openEditor(page);
                    }
                });
            });

            pagesTableBody.querySelectorAll('.page-preview-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var page = findPageById(btn.dataset.id);
                    if (page) {
                        openPreview(page);
                    }
                });
            });

            pagesTableBody.querySelectorAll('.page-delete-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var page = findPageById(btn.dataset.id);
                    if (!page || page.slug === 'home') {
                        showError('Нельзя удалить главную страницу');
                        return;
                    }
                    if (!confirm('Удалить страницу "' + page.title + '"?')) return;
                    deletePage(page.id);
                });
            });

            pagesTableBody.querySelectorAll('.page-toggle').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    togglePageActive(parseInt(btn.dataset.id, 10));
                });
            });
        }

        // ---------- Редактор: создание и правка страницы с телом ----------
        function openEditor(page) {
            pageForm.classList.remove('was-validated');
            if (page) {
                // Режим правки: slug не меняется (запись привязана к id)
                pageEditId.value = page.id;
                pageSlug.value = page.slug;
                pageSlug.readOnly = true;
                pageTitle.value = page.title;
                pageActive.checked = page.is_active == 1;
                pageEditorTitle.textContent = 'Редактировать страницу: ' + page.slug;
            } else {
                // Режим создания: тело открывается сразу с заготовкой
                pageEditId.value = '';
                pageSlug.value = '';
                pageSlug.readOnly = false;
                pageTitle.value = '';
                pageActive.checked = true;
                pageEditorTitle.textContent = 'Добавить страницу';
            }
            // Сначала показываем карточку, потом создаём редактор:
            // в скрытом контейнере CodeMirror не отмеряет высоту
            pageEditor.hidden = false;
            ensureBodyEditor();
            setBody(page ? (page.html_content || '') : PAGE_STARTER);
            pageEditor.scrollIntoView({ behavior: 'smooth' });
        }

        function closePageEditor() {
            pageEditor.hidden = true;
            pageEditId.value = '';
            pageForm.classList.remove('was-validated');
        }

        // ---------- Живой предпросмотр: полная страница, без всплывающего окна ----------
        function openPreview(page) {
            var url = '/admin/preview.html?page=' + encodeURIComponent(page.id)
                + '&title=' + encodeURIComponent(page.title || page.slug);
            window.location.href = url;
        }

        // ---------- Удалить страницу ----------
        function deletePage(pageId) {
            window.AdminApi.remove('/api/admin/pages/' + pageId).then(function () {
                loadPages();
                showSuccess('Страница удалена');
            }).catch(function (error) {
                showError('Не удалось удалить: ' + error.message);
            });
        }

        // ---------- Переключить активность ----------
        function togglePageActive(pageId) {
            window.AdminApi.save('/api/admin/pages/' + pageId + '/toggle', 'POST', {}).then(function () {
                loadPages();
            }).catch(function (error) {
                showError('Не удалось переключить: ' + error.message);
            });
        }

        // ---------- Сохранить страницу (карточка + тело) ----------
        function savePage() {
            if (!pageForm.checkValidity()) {
                pageForm.classList.add('was-validated');
                showError('Заполните обязательные поля');
                return;
            }

            var data = {
                slug: pageSlug.value.trim().toLowerCase(),
                title: pageTitle.value.trim(),
                is_active: pageActive.checked ? 1 : 0,
                html_content: getBody()
            };

            if (pageEditId.value) {
                data.edit_id = pageEditId.value;
            }

            window.AdminApi.save('/api/admin/pages', 'POST', data).then(function () {
                closePageEditor();
                loadPages();
                showSuccess('Страница сохранена');
            }).catch(function (error) {
                showError('Не удалось сохранить: ' + error.message);
            });
        }

        // ---------- Сгенерировать из настроек (главная) ----------
        function generateFromSettings() {
            var homePage = currentPages.find(function (p) { return p.slug === 'home'; });
            if (!homePage) {
                showError('Страница "home" не найдена');
                return;
            }

            generateBtn.disabled = true;
            generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Генерация…';

            window.AdminApi.save('/api/admin/pages/home/generate?id=' + homePage.id, 'POST', {}).then(function (data) {
                loadPages();
                showSuccess('Страница сгенерирована (' + data.html_length + ' байт)');
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<i class="bi bi-magic me-1"></i> Сгенерировать из настроек';
            }).catch(function (error) {
                showError('Не удалось сгенерировать: ' + error.message);
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<i class="bi bi-magic me-1"></i> Сгенерировать из настроек';
            });
        }

        // ---------- Обработчики событий ----------
        addPageBtn.addEventListener('click', function () {
            openEditor(null);
        });

        closeEditor.addEventListener('click', closePageEditor);
        cancelPageBtn.addEventListener('click', closePageEditor);

        fillStarterBtn.addEventListener('click', function () {
            if (pageEditId.value && !confirm('Текущее тело страницы будет заменено заготовкой. Продолжить?')) {
                return;
            }
            setBody(PAGE_STARTER);
        });

        pageForm.addEventListener('submit', function (e) {
            e.preventDefault();
            savePage();
        });

        generateBtn.addEventListener('click', generateFromSettings);

        // Переключатель темы CodeMirror (общий с разделом «Шаблоны»)
        if (themeSelect) {
            themeSelect.value = currentTheme();
            themeSelect.addEventListener('change', function () {
                if (bodyEditor) {
                    bodyEditor.setOption('theme', themeSelect.value);
                }
                try {
                    localStorage.setItem(themeStorageKey, themeSelect.value);
                } catch (error) {
                    console.error('Не удалось сохранить тему редактора.', error);
                }
            });
        }

        // ---------- Инициализация ----------
        loadPages();
    });
}());
