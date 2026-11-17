(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var currentPages = [];
        var currentPage = null;

        // ---------- DOM элементы ----------
        var pagesTableBody = document.getElementById('pagesTableBody');
        var generateBtn = document.getElementById('generateBtn');
        var addPageBtn = document.getElementById('addPageBtn');
        var pageEditor = document.getElementById('pageEditor');
        var pageForm = document.getElementById('pageForm');
        var pageEditId = document.getElementById('pageEditId');
        var pageEditorTitle = document.getElementById('pageEditorTitle');
        var closeEditor = document.getElementById('closeEditor');
        var cancelPageBtn = document.getElementById('cancelPageBtn');
        var htmlEditor = document.getElementById('htmlEditor');
        var pageHtmlContent = document.getElementById('pageHtmlContent');
        var saveHtmlBtn = document.getElementById('saveHtmlBtn');
        var closeHtmlEditor = document.getElementById('closeHtmlEditor');
        var pagesError = document.getElementById('pagesError');
        var pagesSuccess = document.getElementById('pagesSuccess');
        var openHtmlFromEditor = document.getElementById('openHtmlFromEditor');

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
                        '<button type="button" class="btn btn-outline-primary btn-sm page-edit-btn me-1" data-id="' + page.id + '" data-slug="' + escapeHtml(page.slug) + '" title="Редактировать"><i class="bi bi-pencil"></i></button> ' +
                        '<button type="button" class="btn btn-outline-info btn-sm page-html-btn me-1" data-id="' + page.id + '" data-slug="' + escapeHtml(page.slug) + '" title="HTML"><i class="bi bi-code-slash"></i></button> ' +
                        '<button type="button" class="btn btn-outline-danger btn-sm page-delete-btn" data-id="' + page.id + '" title="Удалить"><i class="bi bi-trash"></i></button>' +
                    '</td>' +
                    '</tr>';
            });
            pagesTableBody.innerHTML = html;

            // Обработчики
            pagesTableBody.querySelectorAll('.page-edit-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var page = currentPages.find(function (p) { return p.id == this.dataset.id; });
                    if (!page) return;
                    openEditPage(page);
                });
            });

            pagesTableBody.querySelectorAll('.page-html-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var page = currentPages.find(function (p) { return p.id == this.dataset.id; });
                    if (!page) return;
                    openHtmlEditor(page);
                });
            });

            pagesTableBody.querySelectorAll('.page-delete-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var page = currentPages.find(function (p) { return p.id == this.dataset.id; });
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
                    togglePageActive(parseInt(this.dataset.id, 10));
                });
            });
        }

        // ---------- Открыть редактор страницы ----------
        function openEditPage(page) {
            pageEditId.value = page.id;
            document.getElementById('pageSlug').value = page.slug;
            document.getElementById('pageTitle').value = page.title;
            document.getElementById('pageActive').checked = page.is_active == 1;
            pageEditorTitle.textContent = 'Редактировать страницу';
            pageEditor.hidden = false;
            pageEditor.scrollIntoView({ behavior: 'smooth' });
        }

        // ---------- Открыть HTML редактор ----------
        function openHtmlEditor(page) {
            currentPage = page;
            window.AdminApi.get('/api/admin/pages/' + page.slug).then(function (data) {
                if (!data.page) return;
                pageHtmlContent.value = data.page.html_content || '';
                htmlEditor.hidden = false;
                htmlEditor.scrollIntoView({ behavior: 'smooth' });
            }).catch(function (error) {
                showError('Не удалось загрузить HTML: ' + error.message);
            });
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

        // ---------- Сохранить страницу ----------
        function savePage() {
            if (!pageForm.checkValidity()) {
                pageForm.classList.add('was-validated');
                showError('Заполните обязательные поля');
                return;
            }

            var data = {
                slug: document.getElementById('pageSlug').value.trim(),
                title: document.getElementById('pageTitle').value.trim(),
                is_active: document.getElementById('pageActive').checked ? 1 : 0
            };

            if (pageEditId.value) {
                data.edit_id = pageEditId.value;
            }

            window.AdminApi.save('/api/admin/pages', 'POST', data).then(function () {
                pageEditor.hidden = true;
                pageEditId.value = '';
                pageForm.classList.remove('was-validated');
                loadPages();
                showSuccess('Страница сохранена');
            }).catch(function (error) {
                showError('Не удалось сохранить: ' + error.message);
            });
        }

        // ---------- Сохранить HTML ----------
        function saveHtml() {
            if (!currentPage) return;

            var data = {
                slug: currentPage.slug,
                title: currentPage.title,
                html_content: pageHtmlContent.value,
                is_active: currentPage.is_active == 1 ? 1 : 0,
                edit_id: currentPage.id
            };

            window.AdminApi.save('/api/admin/pages', 'POST', data).then(function () {
                htmlEditor.hidden = true;
                loadPages();
                showSuccess('HTML сохранён');
            }).catch(function (error) {
                showError('Не удалось сохранить: ' + error.message);
            });
        }

        // ---------- Сгенерировать из настроек ----------
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
            pageEditId.value = '';
            document.getElementById('pageSlug').value = '';
            document.getElementById('pageTitle').value = '';
            document.getElementById('pageActive').checked = true;
            pageEditorTitle.textContent = 'Добавить страницу';
            pageForm.classList.remove('was-validated');
            pageEditor.hidden = false;
            pageEditor.scrollIntoView({ behavior: 'smooth' });
        });

        closeEditor.addEventListener('click', function () {
            pageEditor.hidden = true;
            pageEditId.value = '';
            pageForm.classList.remove('was-validated');
        });

        cancelPageBtn.addEventListener('click', function () {
            pageEditor.hidden = true;
            pageEditId.value = '';
            pageForm.classList.remove('was-validated');
        });

        openHtmlFromEditor.addEventListener('click', function () {
            var page = currentPages.find(function (p) { return p.id == pageEditId.value; });
            if (!page) return;
            pageEditor.hidden = true;
            openHtmlEditor(page);
        });

        pageForm.addEventListener('submit', function (e) {
            e.preventDefault();
            savePage();
        });

        saveHtmlBtn.addEventListener('click', saveHtml);

        closeHtmlEditor.addEventListener('click', function () {
            htmlEditor.hidden = true;
        });

        generateBtn.addEventListener('click', generateFromSettings);

        // ---------- Инициализация ----------
        loadPages();
    });
}());
