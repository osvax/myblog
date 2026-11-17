(function () {
    'use strict';

    var tableBody = document.getElementById('articlesTableBody');
    if (!tableBody) return;

    var pagination = document.getElementById('articlesPagination');
    var pageInfo = document.getElementById('articlesPageInfo');
    var pageSize = 5;
    var page = 1;
    var articles = [];

    function showToast(message, kind) {
        var container = document.getElementById('articleToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'articleToastContainer';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(container);
        }
        var toast = document.createElement('div');
        toast.className = 'toast align-items-center text-bg-' + (kind || 'success') + ' border-0';
        toast.setAttribute('role', 'status');
        toast.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Закрыть"></button></div>';
        toast.querySelector('.toast-body').textContent = message;
        container.appendChild(toast);
        toast.addEventListener('hidden.bs.toast', function () { toast.remove(); });
        bootstrap.Toast.getOrCreateInstance(toast, { delay: 3500 }).show();
    }

    function escapeAttribute(value) {
        return String(value).replace(/[&"'<>]/g, function (char) {
            return { '&': '&amp;', '"': '&quot;', "'": '&#39;', '<': '&lt;', '>': '&gt;' }[char];
        });
    }

    function formatDate(value) {
        if (!value) return '—';
        var parts = value.split('-');
        return parts.length === 3 ? parts[2] + '.' + parts[1] + '.' + parts[0] : value;
    }

    function render() {
        tableBody.textContent = '';
        var pageCount = Math.max(1, Math.ceil(articles.length / pageSize));
        page = Math.min(page, pageCount);
        var start = (page - 1) * pageSize;
        if (!articles.length) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Статей пока нет</td></tr>';
        } else {
            articles.slice(start, start + pageSize).forEach(function (article) {
                var row = document.createElement('tr');
                var title = document.createElement('td');
                title.textContent = article.title || 'Без названия';
                var category = document.createElement('td');
                category.textContent = article.category || '—';
                var date = document.createElement('td');
                date.textContent = formatDate(article.date);
                var comments = document.createElement('td');
                comments.textContent = '0';
                var actions = document.createElement('td');
                actions.className = 'text-end';
                actions.innerHTML = '<a class="btn btn-outline-dark btn-action me-1" href="article-edit.html?id=' +
                    encodeURIComponent(article.id) + '" aria-label="Изменить ' + escapeAttribute(article.title || '') +
                    '" title="Изменить"><i class="bi bi-pencil"></i></a> ' +
                    '<button type="button" class="btn btn-danger btn-action" data-article-delete="' +
                    escapeAttribute(article.id) + '" aria-label="Удалить ' + escapeAttribute(article.title || '') +
                    '" title="Удалить"><i class="bi bi-trash"></i></button>';
                row.append(title, category, date, comments, actions);
                tableBody.appendChild(row);
            });
        }
        pageInfo.textContent = articles.length
            ? 'Показано ' + (start + 1) + '–' + Math.min(start + pageSize, articles.length) + ' из ' + articles.length + ' статей'
            : 'Статей пока нет';
        renderPagination(pageCount);
    }

    function renderPagination(pageCount) {
        pagination.textContent = '';
        if (pageCount <= 1) return;
        function addPage(label, target, disabled, current, ariaLabel) {
            var item = document.createElement('li');
            item.className = 'page-item' + (disabled ? ' disabled' : '') + (current ? ' active' : '');
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'page-link';
            button.textContent = label;
            button.disabled = disabled || current;
            button.setAttribute('aria-label', ariaLabel || label);
            if (current) button.setAttribute('aria-current', 'page');
            button.addEventListener('click', function () {
                page = target;
                render();
            });
            item.appendChild(button);
            pagination.appendChild(item);
        }
        addPage('‹', page - 1, page === 1, false, 'Предыдущая страница');
        for (var index = 1; index <= pageCount; index++) {
            addPage(String(index), index, false, index === page, 'Страница ' + index);
        }
        addPage('›', page + 1, page === pageCount, false, 'Следующая страница');
    }

    function load() {
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Загрузка статей…</td></tr>';
        window.AdminApi.get('/api/admin/articles').then(function (data) {
            articles = data.articles;
            render();
        }).catch(function (error) {
            console.error('Не удалось загрузить список статей.', error);
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4"></td></tr>';
            tableBody.querySelector('td').textContent = error.message;
        });
    }

    tableBody.addEventListener('click', function (event) {
        var button = event.target.closest('[data-article-delete]');
        if (!button) return;
        if (!window.confirm('Удалить статью?')) return;
        window.AdminApi.remove('/api/admin/articles/' + encodeURIComponent(button.dataset.articleDelete))
            .then(function () {
                showToast('Статья удалена.', 'success');
                load();
            })
            .catch(function (error) {
                console.error('Не удалось удалить статью.', error);
                showToast(error.message, 'danger');
            });
    });

    load();
}());
