(function () {
    'use strict';

    var form = document.getElementById('portfolioForm');
    if (!form) return;

    var params = new URLSearchParams(window.location.search);
    var requestedId = params.get('id');
    var itemId = requestedId || (window.crypto && crypto.randomUUID
        ? crypto.randomUUID().replace(/-/g, '')
        : String(Date.now()));
    var fields = {
        id: document.getElementById('portfolioId'),
        title: document.getElementById('portfolioTitle'),
        slug: document.getElementById('portfolioSlug'),
        icon: document.getElementById('portfolioIcon'),
        technologies: document.getElementById('portfolioTechnologies'),
        description: document.getElementById('portfolioDescription'),
        task: document.getElementById('portfolioTask'),
        solution: document.getElementById('portfolioSolution'),
        client: document.getElementById('portfolioClient'),
        year: document.getElementById('portfolioYear'),
        duration: document.getElementById('portfolioDuration'),
        stack: document.getElementById('portfolioStack'),
        screenshots: document.getElementById('portfolioScreenshots')
    };
    var resultsContainer = document.getElementById('portfolioResults');
    var existingImagesElement = document.getElementById('portfolioExistingImages');
    var newImagesPreview = document.getElementById('portfolioNewImagesPreview');
    var existingImages = [];
    var selectedFiles = [];
    var isEditing = Boolean(requestedId);
    var publicProjectResults = {
        'portfolio-shop': [
            { value: '1,8 с', label: 'средняя загрузка страницы' },
            { value: '+35%', label: 'рост конверсии в заказ' },
            { value: '120+', label: 'товаров в каталоге' }
        ],
        'portfolio-blog': [
            { value: '80+', label: 'статей опубликовано' },
            { value: '×2', label: 'рост времени на сайте' },
            { value: '98', label: 'балл PageSpeed' }
        ],
        'portfolio-app-landing': [
            { value: '12%', label: 'конверсия в установку' },
            { value: '50 000+', label: 'установок за первый месяц' },
            { value: '0,9 с', label: 'загрузка на мобильном интернете' }
        ],
        'portfolio-community': [
            { value: '2 500+', label: 'участников сообщества' },
            { value: '40+', label: 'мероприятий в месяц' },
            { value: '4,8/5', label: 'оценка удобства от пользователей' }
        ],
        'portfolio-analytics': [
            { value: '15+', label: 'метрик на одном экране' },
            { value: '−70%', label: 'времени на подготовку отчётов' },
            { value: '24/7', label: 'доступ к данным онлайн' }
        ],
        'portfolio-photographer': [
            { value: '300+', label: 'фотографий в галерее' },
            { value: '+50%', label: 'заявок на съёмку за 3 месяца' },
            { value: '100', label: 'балл PageSpeed на десктопе' }
        ]
    };

    function notify(type, message) {
        if (window.AdminNotifications) {
            window.AdminNotifications.show(type, message);
            return;
        }
        var container = document.getElementById('portfolioToastContainer');
        var toast = document.createElement('div');
        toast.className = 'toast align-items-center text-bg-' + (type === 'error' ? 'danger' : 'success') + ' border-0';
        toast.setAttribute('role', 'alert');
        toast.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Закрыть"></button></div>';
        toast.querySelector('.toast-body').textContent = message;
        container.appendChild(toast);
        bootstrap.Toast.getOrCreateInstance(toast, { delay: 4000 }).show();
    }

    function validScreenshotPath(path) {
        return typeof path === 'string' &&
            /^uploads\/portfolio\/[A-Za-z0-9_-]+\/[a-f0-9]{32}\.(jpg|png|webp)$/.test(path);
    }

    function addScreenshotPreview(container, source, alt, removableIndex) {
        var column = document.createElement('div');
        column.className = 'col-6 col-md-4 col-xl-3';
        var card = document.createElement('div');
        card.className = 'portfolio-edit-image-card';
        var image = document.createElement('img');
        image.src = source;
        image.alt = alt;
        image.addEventListener('error', function () {
            card.textContent = 'Не удалось загрузить изображение';
            card.classList.add('portfolio-edit-image-error');
        }, { once: true });
        card.appendChild(image);
        if (removableIndex !== null) {
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-danger portfolio-edit-image-remove';
            remove.textContent = 'Убрать';
            remove.setAttribute('aria-label', 'Убрать изображение ' + (removableIndex + 1));
            remove.addEventListener('click', function () {
                existingImages.splice(removableIndex, 1);
                renderExistingImages();
                updateScreenshotLimit();
            });
            card.appendChild(remove);
        }
        column.appendChild(card);
        container.appendChild(column);
    }

    function renderExistingImages() {
        existingImagesElement.textContent = '';
        existingImages.forEach(function (path, index) {
            addScreenshotPreview(existingImagesElement, '../' + path, 'Скриншот проекта ' + (index + 1), index);
        });
    }

    function renderNewImages() {
        newImagesPreview.textContent = '';
        selectedFiles.forEach(function (file) {
            addScreenshotPreview(newImagesPreview, URL.createObjectURL(file), file.name, null);
        });
        updateScreenshotLimit();
    }

    function updateScreenshotLimit() {
        var remaining = Math.max(0, 10 - existingImages.length);
        fields.screenshots.disabled = remaining === 0;
        fields.screenshots.max = String(remaining);
        fields.screenshots.setCustomValidity(
            existingImages.length + selectedFiles.length > 10
                ? 'Общее количество изображений не должно превышать 10.'
                : ''
        );
    }

    function addResultRow(result) {
        var row = document.createElement('div');
        row.className = 'col-12';
        var fieldsRow = document.createElement('div');
        fieldsRow.className = 'row g-2 align-items-end';

        var valueColumn = document.createElement('div');
        valueColumn.className = 'col-md-4';
        var valueLabel = document.createElement('label');
        valueLabel.className = 'form-label fw-bold';
        valueLabel.textContent = 'Значение';
        var valueInput = document.createElement('input');
        valueInput.type = 'text';
        valueInput.className = 'form-control';
        valueInput.placeholder = 'Например: +35%';
        valueInput.value = result && result.value ? result.value : '';
        valueLabel.appendChild(valueInput);
        valueColumn.appendChild(valueLabel);

        var descriptionColumn = document.createElement('div');
        descriptionColumn.className = 'col';
        var descriptionLabel = document.createElement('label');
        descriptionLabel.className = 'form-label fw-bold';
        descriptionLabel.textContent = 'Описание результата';
        var descriptionInput = document.createElement('input');
        descriptionInput.type = 'text';
        descriptionInput.className = 'form-control';
        descriptionInput.placeholder = 'Например: Рост конверсии в заказ';
        descriptionInput.value = result && result.label ? result.label : '';
        descriptionLabel.appendChild(descriptionInput);
        descriptionColumn.appendChild(descriptionLabel);

        var removeColumn = document.createElement('div');
        removeColumn.className = 'col-auto';
        var removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-outline-danger btn-action';
        removeButton.setAttribute('aria-label', 'Удалить результат');
        removeButton.title = 'Удалить результат';
        removeButton.innerHTML = '<i class="bi bi-dash-lg"></i>';
        removeButton.addEventListener('click', function () {
            row.remove();
        });
        removeColumn.appendChild(removeButton);

        fieldsRow.append(valueColumn, descriptionColumn, removeColumn);
        row.appendChild(fieldsRow);
        resultsContainer.appendChild(row);
    }

    function readResults() {
        return Array.prototype.map.call(resultsContainer.children, function (row) {
            var inputs = row.querySelectorAll('input');
            return { value: inputs[0].value.trim(), label: inputs[1].value.trim() };
        }).filter(function (result) {
            return result.value || result.label;
        });
    }

    function fillForm(item) {
        fields.id.value = item.id;
        fields.title.value = item.title || '';
        fields.slug.value = item.slug || '';
        fields.icon.value = item.icon || 'bi-shop';
        fields.technologies.value = item.technologies || '';
        fields.description.value = item.description || '';
        fields.task.value = Array.isArray(item.task) ? item.task.join('\n') : (item.task || '');
        fields.solution.value = item.solution || '';
        fields.client.value = item.client || '';
        fields.year.value = item.year || '';
        fields.duration.value = item.duration || '';
        fields.stack.value = item.stack || '';

        resultsContainer.textContent = '';
        var results = Array.isArray(item.results)
            ? item.results
            : (publicProjectResults[item.id] || []);
        results.forEach(function (result) {
            addResultRow(result);
        });

        existingImages = Array.isArray(item.screenshots) ? item.screenshots.filter(validScreenshotPath) : [];
        renderExistingImages();
        updateScreenshotLimit();
    }

    function uploadScreenshots(files) {
        return fetch('../api/auth/csrf', { credentials: 'same-origin' })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || !data.success || !data.token) {
                        throw new Error(data.error || 'Не удалось получить токен защищённого запроса.');
                    }
                    return data.token;
                });
            })
            .then(function (token) {
                var data = new FormData();
                data.append('scope', 'portfolio');
                data.append('id', itemId);
                files.forEach(function (file) {
                    data.append('files[]', file);
                });
                return fetch('../api/admin/media', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'X-CSRF-Token': token },
                    body: data
                });
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || !data.success || !Array.isArray(data.files) ||
                        data.files.some(function (path) { return !validScreenshotPath(path); })) {
                        throw new Error(data.error || 'Не удалось загрузить изображения портфолио.');
                    }
                    return data.files;
                });
            });
    }

    if (isEditing) {
        window.AdminApi.get('/api/admin/projects/' + encodeURIComponent(requestedId)).then(function (result) {
            var current = result.project;
            itemId = current.id;
            document.getElementById('portfolioPageTitle').textContent = 'Редактирование проекта';
            fillForm(current);
        }).catch(function (error) {
            console.error('Не удалось загрузить проект портфолио.', error);
            notify('error', error.message || 'Не удалось загрузить проект.');
            window.setTimeout(function () { window.location.replace('portfolio.html'); }, 1200);
        });
    } else {
        fields.id.value = itemId;
        fields.year.value = String(new Date().getFullYear());
    }

    document.getElementById('addPortfolioResult').addEventListener('click', function () {
        addResultRow();
        var inputs = resultsContainer.lastElementChild.querySelectorAll('input');
        inputs[0].focus();
    });

    fields.screenshots.addEventListener('change', function () {
        selectedFiles = Array.prototype.slice.call(fields.screenshots.files);
        fields.screenshots.setCustomValidity('');
        updateScreenshotLimit();
        if (existingImages.length + selectedFiles.length > 10) {
            notify('warning', 'Общее количество изображений проекта не должно превышать 10.');
            selectedFiles = [];
            fields.screenshots.value = '';
        }
        renderNewImages();
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        var saved = {
            id: itemId,
            title: fields.title.value.trim(),
            slug: fields.slug.value.trim(),
            technologies: fields.technologies.value.trim(),
            icon: fields.icon.value,
            description: fields.description.value.trim(),
            task: fields.task.value.split(/\r?\n/).map(function (line) { return line.trim(); }).filter(Boolean),
            solution: fields.solution.value.trim(),
            client: fields.client.value.trim(),
            year: fields.year.value ? Number(fields.year.value) : '',
            duration: fields.duration.value.trim(),
            stack: fields.stack.value.trim(),
            results: readResults(),
            screenshots: existingImages.slice(),
            active: true
        };

        var persist = function (uploaded) {
            saved.screenshots = existingImages.concat(uploaded || []);
            return window.AdminApi.save(
                '/api/admin/projects' + (isEditing ? '/' + encodeURIComponent(itemId) : ''),
                isEditing ? 'PUT' : 'POST',
                saved
            ).then(function () {
                window.location.href = 'portfolio.html';
            });
        };

        var submitButton = form.querySelector('[type="submit"]');
        submitButton.disabled = true;
        if (!selectedFiles.length) {
            persist([]).catch(function (error) {
                console.error('Не удалось сохранить проект портфолио.', error);
                notify('error', error.message || 'Не удалось сохранить проект.');
            }).finally(function () { submitButton.disabled = false; });
            return;
        }

        uploadScreenshots(selectedFiles).then(function (uploaded) {
            return persist(uploaded);
        }).catch(function (error) {
            console.error('Не удалось загрузить изображения проекта.', error);
            notify('error', error.message || 'Не удалось загрузить изображения проекта.');
        }).finally(function () {
            submitButton.disabled = false;
        });
    });
})();
