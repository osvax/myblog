(function () {
    'use strict';

    var grid = document.getElementById('portfolioGrid');
    if (!grid) return;

    var items = [];

    function notify(type, message) {
        if (window.AdminNotifications) {
            window.AdminNotifications.show(type, message);
        } else {
            console[type === 'error' ? 'error' : 'log'](message);
        }
    }

    function validScreenshotPath(path) {
        return typeof path === 'string' &&
            /^(?:uploads\/portfolio\/[A-Za-z0-9_-]+\/[a-f0-9]{32}\.(?:jpg|png|webp)|uploads\/images\/portfolio\/[a-z0-9][a-z0-9-]*\.(?:jpg|jpeg|png))$/.test(path);
    }

    function render() {
        grid.textContent = '';
        if (!items.length) {
            var empty = document.createElement('p');
            empty.className = 'text-muted fw-bold';
            empty.textContent = 'Работ в портфолио пока нет.';
            grid.appendChild(empty);
            return;
        }
        items.forEach(function (item) {
            var column = document.createElement('div');
            column.className = 'col-md-6 col-lg-4';
            var card = document.createElement('div');
            card.className = 'card admin-card h-100';
            var imageArea = document.createElement('div');
            imageArea.className = 'portfolio-image-area';
            var cover = Array.isArray(item.screenshots) ? item.screenshots.find(validScreenshotPath) : null;
            if (cover) {
                var image = document.createElement('img');
                image.src = '/' + cover.split('/').map(encodeURIComponent).join('/');
                image.alt = item.title || 'Обложка работы';
                image.className = 'portfolio-image';
                image.addEventListener('error', function () { renderImagePlaceholder(imageArea); }, { once: true });
                imageArea.appendChild(image);
            } else {
                renderImagePlaceholder(imageArea);
            }
            var body = document.createElement('div');
            body.className = 'card-body';
            var title = document.createElement('h5');
            title.className = 'fw-bold mb-1';
            title.textContent = item.title || 'Без названия';
            var technologies = document.createElement('p');
            technologies.className = 'fw-bold text-muted small mb-3';
            technologies.textContent = item.technologies || '';
            var edit = document.createElement('a');
            edit.href = 'portfolio-edit.html?id=' + encodeURIComponent(item.id);
            edit.className = 'btn btn-outline-dark btn-action';
            edit.setAttribute('aria-label', 'Изменить ' + (item.title || 'работу'));
            edit.innerHTML = '<i class="bi bi-pencil"></i>';
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-danger btn-action';
            remove.dataset.portfolioDelete = item.id;
            remove.setAttribute('aria-label', 'Удалить ' + (item.title || 'работу'));
            remove.innerHTML = '<i class="bi bi-trash"></i>';
            var actions = document.createElement('div');
            actions.className = 'portfolio-actions d-flex align-items-center gap-2';
            actions.append(edit, remove);
            body.append(title, technologies, actions);
            card.append(imageArea, body);
            column.appendChild(card);
            grid.appendChild(column);
        });
    }

    function renderImagePlaceholder(container) {
        container.textContent = '';
        container.classList.add('portfolio-image-placeholder');
        var icon = document.createElement('i');
        icon.className = 'bi bi-image';
        icon.setAttribute('aria-hidden', 'true');
        var label = document.createElement('span');
        label.textContent = 'Нет изображения';
        container.append(icon, label);
    }

    function load() {
        grid.innerHTML = '<p class="text-muted fw-bold">Загрузка портфолио…</p>';
        window.AdminApi.get('/api/admin/projects').then(function (data) {
            items = data.projects;
            render();
        }).catch(function (error) {
            console.error('Не удалось загрузить список портфолио.', error);
            grid.textContent = error.message;
            grid.classList.add('text-danger');
        });
    }

    grid.addEventListener('click', function (event) {
        var button = event.target.closest('[data-portfolio-delete]');
        if (!button) return;
        window.AdminApi.remove('/api/admin/projects/' + encodeURIComponent(button.dataset.portfolioDelete))
            .then(function () {
                notify('success', 'Работа удалена.');
                load();
            }).catch(function (error) {
                console.error('Не удалось удалить работу портфолио.', error);
                notify('error', error.message);
            });
    });

    load();
}());
