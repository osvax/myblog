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
        result: document.getElementById('portfolioResult'),
        client: document.getElementById('portfolioClient'),
        year: document.getElementById('portfolioYear'),
        duration: document.getElementById('portfolioDuration'),
        stack: document.getElementById('portfolioStack'),
        screenshots: document.getElementById('portfolioScreenshots')
    };
    var resultsContainer = document.getElementById('portfolioResults');
    var infoItemsContainer = document.getElementById('portfolioInfoItems');
    var existingImagesElement = document.getElementById('portfolioExistingImages');
    var newImagesPreview = document.getElementById('portfolioNewImagesPreview');
    // Снимки проекта: [{path, description}] — пути в разделе «Портфолио»
    // файлового менеджера или в старой папке проекта.
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

    // Старая папка проекта (uploads/portfolio/{id}/хэш) и раздел «Портфолио»
    // файлового менеджера с читаемым именем — согласовано с контроллером.
    function validScreenshotPath(path) {
        return typeof path === 'string' &&
            /^(?:uploads\/portfolio\/[A-Za-z0-9_-]+\/[a-f0-9]{32}\.(?:jpg|png|webp)|uploads\/images\/portfolio\/[a-z0-9][a-z0-9-]*\.(?:jpg|jpeg|png))$/.test(path);
    }

    // Прикреплённые файлы дополнительных полей — согласовано с контроллером.
    function validAttachmentPath(path, category) {
        return typeof path === 'string' && (category === 'images'
            ? /^uploads\/images\/portfolio\/[a-z0-9][a-z0-9-]*\.(?:jpg|jpeg|png)$/.test(path)
            : /^uploads\/documents\/portfolio\/[a-z0-9][a-z0-9-]*\.(?:doc|docx)$/.test(path));
    }

    function addScreenshotCard(container, source, alt, description, removeHandler) {
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
        if (typeof removeHandler === 'function') {
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-danger portfolio-edit-image-remove';
            remove.textContent = 'Убрать';
            remove.setAttribute('aria-label', 'Убрать изображение');
            remove.addEventListener('click', removeHandler);
            card.appendChild(remove);
        }
        column.appendChild(card);
        var descriptionInput = document.createElement('input');
        descriptionInput.type = 'text';
        descriptionInput.className = 'form-control form-control-sm mt-2 portfolio-screenshot-description';
        descriptionInput.placeholder = 'Описание снимка';
        descriptionInput.maxLength = 500;
        descriptionInput.value = description || '';
        column.appendChild(descriptionInput);
        container.appendChild(column);
    }

    function readScreenshotDescriptions(container) {
        var inputs = container.querySelectorAll('input.portfolio-screenshot-description');
        return Array.prototype.map.call(inputs, function (input) {
            return input.value.trim();
        });
    }

    function renderExistingImages() {
        existingImagesElement.textContent = '';
        existingImages.forEach(function (image, index) {
            addScreenshotCard(existingImagesElement, '../' + image.path, 'Скриншот проекта ' + (index + 1), image.description, function () {
                existingImages.splice(index, 1);
                renderExistingImages();
                updateScreenshotLimit();
            });
        });
    }

    function renderNewImages() {
        newImagesPreview.textContent = '';
        selectedFiles.forEach(function (file) {
            addScreenshotCard(newImagesPreview, URL.createObjectURL(file), file.name, '', null);
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

    var infoItemKinds = {
        link: 'Ссылка',
        document: 'Документ',
        certificate: 'Сертификат'
    };

    function addInfoItemRow(item) {
        var kind = item && infoItemKinds[item.kind] ? item.kind : 'link';
        var name = item && item.name ? item.name : '';
        var value = item && item.value ? item.value : '';
        var filePath = item && item.file_path ? item.file_path : '';
        var fileName = filePath ? filePath.split('/').pop() : '';

        var row = document.createElement('div');
        row.className = 'row g-2 align-items-end mb-2';

        var kindColumn = document.createElement('div');
        kindColumn.className = 'col-6 col-md-3';
        var kindLabel = document.createElement('label');
        kindLabel.className = 'form-label fw-bold';
        kindLabel.textContent = 'Тип';
        var kindSelect = document.createElement('select');
        kindSelect.className = 'form-select';
        Object.keys(infoItemKinds).forEach(function (key) {
            var option = document.createElement('option');
            option.value = key;
            option.textContent = infoItemKinds[key];
            kindSelect.appendChild(option);
        });
        kindSelect.value = kind;
        // При смене типа пересобираем строку, сохраняя название.
        kindSelect.addEventListener('change', function () {
            var keptName = row.querySelector('.info-name').value;
            row.remove();
            addInfoItemRow({ kind: kindSelect.value, name: keptName });
            infoItemsContainer.lastElementChild.querySelector('.info-name').focus();
        });
        kindLabel.appendChild(kindSelect);
        kindColumn.appendChild(kindLabel);

        var nameColumn = document.createElement('div');
        nameColumn.className = 'col-6 col-md-4';
        var nameLabel = document.createElement('label');
        nameLabel.className = 'form-label fw-bold';
        nameLabel.textContent = 'Название';
        var nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.className = 'form-control info-name';
        nameInput.maxLength = 160;
        nameInput.placeholder = 'Например: Ссылка на проект';
        nameInput.value = name;
        nameLabel.appendChild(nameInput);
        nameColumn.appendChild(nameLabel);

        if (kind === 'link') {
            var valueColumn = document.createElement('div');
            valueColumn.className = 'col-12 col-md-4';
            var valueLabel = document.createElement('label');
            valueLabel.className = 'form-label fw-bold';
            valueLabel.textContent = 'Значение';
            var valueInput = document.createElement('input');
            valueInput.type = 'text';
            valueInput.className = 'form-control info-value';
            valueInput.maxLength = 500;
            valueInput.placeholder = 'URL или текст';
            valueInput.value = value;
            valueLabel.appendChild(valueInput);
            valueColumn.appendChild(valueLabel);
            row.append(kindColumn, nameColumn, valueColumn);
        } else {
            // Документ / сертификат: прикрепление файла из раздела «Портфолио».
            var attachColumn = document.createElement('div');
            attachColumn.className = 'col-12 col-md-4';
            var attachLabel = document.createElement('label');
            attachLabel.className = 'form-label fw-bold';
            attachLabel.textContent = 'Файл';
            var attachBox = document.createElement('div');
            attachBox.className = 'd-flex align-items-center gap-2';
            var attachButton = document.createElement('button');
            attachButton.type = 'button';
            attachButton.className = 'btn btn-outline-dark btn-action';
            attachButton.innerHTML = '<i class="bi bi-paperclip"></i>';
            attachButton.setAttribute('aria-label', 'Прикрепить файл');
            attachButton.title = 'Прикрепить файл';
            attachButton.addEventListener('click', function () {
                openFileInfoModal(row, kind, attachLabel.textContent);
            });
            var fileLabel = document.createElement('span');
            fileLabel.className = 'form-text fw-bold mb-0 info-file-label text-truncate';
            fileLabel.style.maxWidth = '11rem';
            fileLabel.textContent = fileName || 'Файл не выбран';
            var fileInput = document.createElement('input');
            fileInput.type = 'hidden';
            fileInput.className = 'info-file-path';
            fileInput.value = filePath;
            attachBox.append(attachButton, fileLabel);
            attachLabel.appendChild(attachBox);
            attachLabel.appendChild(fileInput);
            attachColumn.appendChild(attachLabel);
            row.append(kindColumn, nameColumn, attachColumn);
        }

        var removeColumn = document.createElement('div');
        removeColumn.className = 'col-12 col-md-auto';
        var removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-outline-danger btn-action';
        removeButton.setAttribute('aria-label', 'Удалить поле');
        removeButton.title = 'Удалить поле';
        removeButton.innerHTML = '<i class="bi bi-dash-lg"></i>';
        removeButton.addEventListener('click', function () {
            row.remove();
        });
        removeColumn.appendChild(removeButton);
        row.appendChild(removeColumn);

        infoItemsContainer.appendChild(row);
    }

    function readInfoItems() {
        return Array.prototype.map.call(infoItemsContainer.children, function (row) {
            var select = row.querySelector('select');
            var nameInput = row.querySelector('.info-name');
            var valueInput = row.querySelector('.info-value');
            var fileInput = row.querySelector('.info-file-path');
            var kind = select.value;
            return {
                kind: kind,
                name: nameInput.value.trim(),
                value: kind === 'link' ? valueInput.value.trim() : '',
                file_path: kind === 'link' ? '' : fileInput.value.trim()
            };
        }).filter(function (item) {
            return item.name;
        });
    }

    // ---------- Файл для дополнительного поля (ТЗ / сертификат) ----------
    var fileInfoModalElement = document.getElementById('portfolioFileInfoModal');
    var fileInfoCategory = 'documents';
    var fileInfoRow = null;
    var fileInfoFiles = null;

    function renderFileInfoList() {
        var list = document.getElementById('portfolioFileInfoList');
        if (!fileInfoFiles) {
            list.textContent = '';
            var loading = document.createElement('div');
            loading.className = 'col-12';
            loading.innerHTML = '<p class="text-muted fw-bold mb-0">Загрузка списка…</p>';
            list.appendChild(loading);
            window.AdminApi.get('/api/admin/files?action=list').then(function (result) {
                fileInfoFiles = (result && Array.isArray(result.files)) ? result.files : [];
                renderFileInfoList();
            }).catch(function (error) {
                list.textContent = '';
                var failed = document.createElement('div');
                failed.className = 'col-12';
                failed.innerHTML = '<p class="text-danger fw-bold mb-0">Не удалось загрузить список файлов.</p>';
                list.appendChild(failed);
            });
            return;
        }
        list.textContent = '';
        var filtered = fileInfoFiles.filter(function (file) {
            return file.section === 'portfolio' && file.category === fileInfoCategory;
        });
        if (!filtered.length) {
            var empty = document.createElement('div');
            empty.className = 'col-12';
            empty.innerHTML = '<p class="text-muted fw-bold mb-0">В этом разделе файлов пока нет.</p>';
            list.appendChild(empty);
            return;
        }
        filtered.forEach(function (file) {
            var column = document.createElement('div');
            column.className = 'col-6 col-md-4';
            var card = document.createElement('button');
            card.type = 'button';
            card.className = 'card admin-card p-2 text-start w-100 file-picker-card';
            card.setAttribute('aria-label', 'Прикрепить файл ' + file.name);
            if (fileInfoCategory === 'images') {
                var thumb = document.createElement('img');
                thumb.src = '../' + file.path;
                thumb.alt = file.name;
                thumb.className = 'img-fluid rounded mb-2';
                thumb.style.maxHeight = '90px';
                card.appendChild(thumb);
            } else {
                var icon = document.createElement('i');
                icon.className = 'bi bi-file-earmark-word display-6 text-orange';
                card.appendChild(icon);
            }
            var caption = document.createElement('span');
            caption.className = 'fw-bold small text-truncate d-block';
            caption.textContent = file.name;
            card.appendChild(caption);
            card.addEventListener('click', function () {
                attachFileInfo('uploads/' + file.path, file.name);
            });
            column.appendChild(card);
            list.appendChild(column);
        });
    }

    function attachFileInfo(path, displayName) {
        if (!fileInfoRow) {
            return;
        }
        fileInfoRow.querySelector('.info-file-path').value = path;
        fileInfoRow.querySelector('.info-file-label').textContent = displayName || path.split('/').pop();
        bootstrap.Modal.getOrCreateInstance(fileInfoModalElement).hide();
    }

    function openFileInfoModal(row, kind, fieldName) {
        fileInfoRow = row;
        fileInfoCategory = kind === 'certificate' ? 'images' : 'documents';
        var uploadInput = document.getElementById('portfolioFileInfoUpload');
        uploadInput.value = '';
        uploadInput.accept = fileInfoCategory === 'images' ? 'image/jpeg,image/png' : '.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        document.getElementById('portfolioFileInfoUploadHint').textContent = fileInfoCategory === 'images'
            ? 'JPG или PNG, до 10 МБ. Файл сохранится в разделе «Портфолио» файлового менеджера.'
            : 'DOC или DOCX, до 10 МБ. Файл сохранится в разделе «Портфолио» файлового менеджера.';
        document.getElementById('portfolioFileInfoModalTitle').textContent =
            'Файл для поля «' + (row.querySelector('.info-name').value || fieldName) + '»';
        renderFileInfoList();
        bootstrap.Modal.getOrCreateInstance(fileInfoModalElement).show();
    }

    document.getElementById('portfolioFileInfoUpload').addEventListener('change', function () {
        var file = this.files && this.files[0];
        if (!file) {
            return;
        }
        var input = this;
        getCsrfToken().then(function (token) {
            return uploadFileToManager(file, token, fileInfoCategory).then(function (result) {
                attachFileInfo(result.path, result.name || file.name);
                input.value = '';
                // Обновляем кэш списка: новый файл должен попасть в список.
                fileInfoFiles = null;
                renderFileInfoList();
            });
        }).catch(function (error) {
            console.error('Не удалось загрузить файл поля.', error);
            notify('error', error.message || 'Не удалось загрузить файл.');
            input.value = '';
        });
    });

    function initialGallery(item) {
        var fromGallery = Array.isArray(item.gallery) ? item.gallery : [];
        var valid = fromGallery.filter(function (entry) {
            return entry && typeof entry.path === 'string' && validScreenshotPath(entry.path);
        });
        if (valid.length) {
            return valid.map(function (entry) {
                return {
                    path: entry.path,
                    description: typeof entry.description === 'string' ? entry.description : ''
                };
            });
        }
        return (Array.isArray(item.screenshots) ? item.screenshots : [])
            .filter(validScreenshotPath)
            .map(function (path) {
                return { path: path, description: '' };
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
        fields.result.value = item.result || '';
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

        infoItemsContainer.textContent = '';
        (Array.isArray(item.infoItems) ? item.infoItems : []).forEach(function (infoItem) {
            addInfoItemRow(infoItem);
        });

        existingImages = initialGallery(item);
        renderExistingImages();
        updateScreenshotLimit();
    }

    // Загрузка одного файла в файловый менеджер (категория: images / documents).
    function uploadFileToManager(file, token, category) {
        var data = new FormData();
        data.append('action', 'upload');
        data.append('category', category || 'images');
        data.append('section', 'portfolio');
        data.append('file', file);
        return fetch('../api/admin/files', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-Token': token },
            body: data
        }).then(function (response) {
            return response.json().then(function (result) {
                if (!response.ok || !result.success || !result.file ||
                    !validAttachmentPath('uploads/' + result.file.path, category || 'images')) {
                    throw new Error(result.error || 'Не удалось загрузить файл.');
                }
                return { path: 'uploads/' + result.file.path, name: result.file.name };
            });
        });
    }

    function getCsrfToken() {
        return fetch('../api/auth/csrf', { credentials: 'same-origin' })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || !data.success || !data.token) {
                        throw new Error(data.error || 'Не удалось получить токен защищённого запроса.');
                    }
                    return data.token;
                });
            });
    }

    // Последовательная загрузка выбранных снимков через файловый менеджер.
    function uploadScreenshots(files) {
        return getCsrfToken().then(function (token) {
            var paths = [];
            var next = function () {
                if (paths.length >= files.length) {
                    return Promise.resolve(paths);
                }
                return uploadFileToManager(files[paths.length], token, 'images').then(function (result) {
                    paths.push(result.path);
                    return next();
                });
            };
            return next();
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

    document.getElementById('addPortfolioInfoItem').addEventListener('click', function () {
        addInfoItemRow();
        var inputs = infoItemsContainer.lastElementChild.querySelectorAll('input');
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

        // Описания снимков считываем из формы до отправки.
        var existingDescriptions = readScreenshotDescriptions(existingImagesElement);
        var newDescriptions = readScreenshotDescriptions(newImagesPreview);

        var saved = {
            id: itemId,
            title: fields.title.value.trim(),
            slug: fields.slug.value.trim(),
            technologies: fields.technologies.value.trim(),
            icon: fields.icon.value,
            description: fields.description.value.trim(),
            task: fields.task.value.split(/\r?\n/).map(function (line) { return line.trim(); }).filter(Boolean),
            solution: fields.solution.value.trim(),
            result: fields.result.value.trim(),
            client: fields.client.value.trim(),
            year: fields.year.value ? Number(fields.year.value) : '',
            duration: fields.duration.value.trim(),
            stack: fields.stack.value.trim(),
            results: readResults(),
            infoItems: readInfoItems(),
            screenshots: existingImages.map(function (image) { return image.path; }),
            gallery: existingImages.map(function (image, index) {
                return { path: image.path, description: existingDescriptions[index] || '' };
            }),
            active: true
        };

        var persist = function (uploaded, uploadedDescriptions) {
            saved.screenshots = saved.screenshots.concat(uploaded || []);
            (uploaded || []).forEach(function (path, index) {
                saved.gallery.push({ path: path, description: uploadedDescriptions[index] || '' });
            });
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
            persist([], []).catch(function (error) {
                console.error('Не удалось сохранить проект портфолио.', error);
                notify('error', error.message || 'Не удалось сохранить проект.');
            }).finally(function () { submitButton.disabled = false; });
            return;
        }

        uploadScreenshots(selectedFiles).then(function (uploaded) {
            return persist(uploaded, newDescriptions);
        }).catch(function (error) {
            console.error('Не удалось загрузить изображения проекта.', error);
            notify('error', error.message || 'Не удалось загрузить изображения проекта.');
        }).finally(function () {
            submitButton.disabled = false;
        });
    });
})();
