(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('fileUploadForm');
        var fileInput = document.getElementById('filesToUpload');
        var categorySelect = document.getElementById('fileCategory');
        var sectionSelect = document.getElementById('fileSection');
        var tableBody = document.getElementById('fileTableBody');
        var uploadButton = document.getElementById('uploadFilesButton');
        var dropZone = document.getElementById('fileDropZone');
        var previewModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('imagePreviewModal'));
        var previewImage = document.getElementById('imagePreview');
        var previewTitle = document.getElementById('imagePreviewTitle');
        var csrfToken = '';
        var isUploading = false;
        var categoryLabels = {
            images: 'Изображение',
            documents: 'Документ',
            spreadsheets: 'Таблица',
            xml: 'XML',
            archives: 'ZIP-архив'
        };
        var sectionLabels = {
            site: 'Сайт',
            blog: 'Блог',
            portfolio: 'Портфолио',
            profile: 'Профиль',
            orders: 'Заказы'
        };

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, function (char) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
            });
        }

        function formatSize(bytes) {
            if (bytes < 1024) return bytes + ' Б';
            if (bytes < 1024 * 1024) return (bytes / 1024).toLocaleString('ru-RU', { maximumFractionDigits: 1 }) + ' КБ';
            return (bytes / (1024 * 1024)).toLocaleString('ru-RU', { maximumFractionDigits: 1 }) + ' МБ';
        }

        function request(url, options) {
            options = options || {};
            var method = (options.method || 'GET').toUpperCase();
            var headers = Object.assign({}, options.headers || {});
            var body = options.body;
            var tokenPromise = method === 'GET' || method === 'HEAD'
                ? Promise.resolve(null)
                : (csrfToken ? Promise.resolve(csrfToken) : getCsrfToken());

            return tokenPromise.then(function (token) {
                if (token) headers['X-CSRF-Token'] = token;
                if (body && !(body instanceof FormData)) {
                    headers['Content-Type'] = 'application/json';
                    body = JSON.stringify(body);
                }
                return fetch(url, {
                    method: method,
                    credentials: 'same-origin',
                    headers: headers,
                    body: body
                });
            }).then(function (response) {
                if (response.status === 401) {
                    window.location.href = 'login.html';
                    throw new Error('Войдите в админ-панель заново');
                }
                var contentType = response.headers.get('Content-Type') || '';
                if (!contentType.includes('application/json')) {
                    if (!response.ok) throw new Error('Сервер отклонил запрос (' + response.status + ')');
                    return response;
                }
                return response.json().then(function (data) {
                    if (!response.ok || !data.success) throw new Error(data.error || 'Ошибка файлового менеджера');
                    return data;
                });
            });
        }

        function loadFiles(notify) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Загрузка списка файлов...</td></tr>';
            request('../api/admin/files?action=list', { credentials: 'same-origin' })
                .then(function (data) {
                    if (!data.files.length) {
                        tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Файлов пока нет</td></tr>';
                        if (notify) window.AdminNotifications.show('info', 'Список файлов обновлён');
                        return;
                    }
                    tableBody.innerHTML = data.files.map(function (file) {
                        var date = new Date(file.modified * 1000).toLocaleString('ru-RU');
                        var download = '../api/admin/files?action=download&path=' + encodeURIComponent(file.path);
                        var thumbnail = file.category === 'images'
                            ? '<button type="button" class="file-thumbnail-button" data-preview-file="' + escapeHtml(file.path) +
                                '" data-preview-name="' + escapeHtml(file.name) + '" aria-label="Увеличить изображение: ' +
                                escapeHtml(file.name) + '"><img src="../api/admin/files?action=preview&path=' +
                                encodeURIComponent(file.path) + '" alt="" loading="lazy" data-thumbnail-image>' +
                                '<i class="bi bi-image file-thumbnail-placeholder" aria-hidden="true" hidden></i></button>'
                            : '<span class="file-thumbnail-button" aria-hidden="true"><i class="bi ' +
                                (file.category === 'archives' ? 'bi-file-earmark-zip' : 'bi-file-earmark-text') +
                                ' file-thumbnail-placeholder"></i></span>';
                        return '<tr><td>' + thumbnail + '</td><td class="text-break">' + escapeHtml(file.name) + '</td>' +
                            '<td>' + escapeHtml(categoryLabels[file.category] || file.category) + '</td>' +
                            '<td>' + escapeHtml(sectionLabels[file.section] || file.section) + '</td>' +
                            '<td>' + formatSize(file.size) + '</td><td>' + escapeHtml(date) + '</td>' +
                            '<td class="text-end text-nowrap"><a class="btn btn-outline-dark btn-action" href="' +
                            escapeHtml(download) + '" aria-label="Скачать файл"><i class="bi bi-download"></i></a> ' +
                            '<button type="button" class="btn btn-outline-danger btn-action" data-delete-file="' +
                            escapeHtml(file.path) + '" aria-label="Удалить файл"><i class="bi bi-trash"></i></button></td></tr>';
                    }).join('');
                    if (notify) window.AdminNotifications.show('info', 'Список файлов обновлён');
                })
                .catch(function (error) {
                    tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">' +
                        escapeHtml(error.message) + '</td></tr>';
                    window.AdminNotifications.show(false, error.message);
                });
        }

        function getCsrfToken() {
            return request('../api/auth/csrf', { credentials: 'same-origin' }).then(function (data) {
                csrfToken = data.token;
            });
        }

        getCsrfToken().then(loadFiles).catch(function (error) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">' +
                escapeHtml(error.message) + '</td></tr>';
            window.AdminNotifications.show(false, error.message);
        });

        function uploadFiles(files) {
            files = Array.from(files);
            if (!files.length) {
                window.AdminNotifications.show(false, 'Выберите хотя бы один файл');
                return;
            }
            if (!csrfToken) {
                window.AdminNotifications.show(false, 'Не удалось получить токен безопасности. Обновите страницу.');
                return;
            }
            if (isUploading) {
                window.AdminNotifications.show(false, 'Дождитесь завершения текущей загрузки');
                return;
            }

            isUploading = true;
            uploadButton.disabled = true;
            dropZone.setAttribute('aria-busy', 'true');
            var uploaded = 0;
            var chain = Promise.resolve();
            files.forEach(function (file) {
                chain = chain.then(function () {
                    var body = new FormData();
                    body.append('action', 'upload');
                    body.append('category', categorySelect.value);
                    body.append('section', sectionSelect.value);
                    body.append('file', file);
                    return request('../api/admin/files', {
                        method: 'POST',
                        body: body
                    }).then(function () {
                        uploaded++;
                        window.AdminNotifications.show(true, 'Файл «' + file.name + '» загружен');
                    });
                });
            });
            chain.then(function () {
                fileInput.value = '';
                loadFiles();
            }).catch(function (error) {
                window.AdminNotifications.show(false, uploaded
                    ? 'Загружено файлов: ' + uploaded + '. ' + error.message
                    : error.message);
                loadFiles();
            }).finally(function () {
                isUploading = false;
                uploadButton.disabled = false;
                dropZone.removeAttribute('aria-busy');
            });
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            uploadFiles(fileInput.files);
        });

        dropZone.addEventListener('click', function (event) {
            if (event.target.closest('button')) return;
            fileInput.click();
        });
        dropZone.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                fileInput.click();
            }
        });
        fileInput.addEventListener('change', function () {
            if (fileInput.files.length) uploadFiles(fileInput.files);
        });
        ['dragenter', 'dragover'].forEach(function (eventName) {
            dropZone.addEventListener(eventName, function (event) {
                event.preventDefault();
                event.stopPropagation();
                dropZone.classList.add('is-dragging');
            });
        });
        ['dragleave', 'drop'].forEach(function (eventName) {
            dropZone.addEventListener(eventName, function (event) {
                event.preventDefault();
                event.stopPropagation();
                dropZone.classList.remove('is-dragging');
            });
        });
        dropZone.addEventListener('drop', function (event) {
            if (event.dataTransfer && event.dataTransfer.files.length) {
                uploadFiles(event.dataTransfer.files);
            }
        });

        document.getElementById('refreshFiles').addEventListener('click', function () {
            loadFiles(true);
        });
        uploadButton.addEventListener('click', function () {
            fileInput.click();
        });
        tableBody.addEventListener('click', function (event) {
            var previewButton = event.target.closest('[data-preview-file]');
            if (previewButton) {
                previewTitle.textContent = previewButton.dataset.previewName;
                previewImage.src = '../api/admin/files?action=preview&path=' +
                    encodeURIComponent(previewButton.dataset.previewFile);
                previewImage.alt = previewButton.dataset.previewName;
                previewModal.show();
                return;
            }
            var button = event.target.closest('[data-delete-file]');
            if (!button) return;
            if (!csrfToken) {
                window.AdminNotifications.show(false, 'Не удалось получить токен безопасности. Обновите страницу.');
                return;
            }
            var body = new FormData();
            body.append('action', 'delete');
            body.append('path', button.dataset.deleteFile);
            request('../api/admin/files', {
                method: 'POST',
                body: body
            }).then(function () {
                window.AdminNotifications.show(true, 'Файл удалён');
                loadFiles();
            }).catch(function (error) {
                window.AdminNotifications.show(false, error.message);
            });
        });
        tableBody.addEventListener('error', function (event) {
            var image = event.target.closest('[data-thumbnail-image]');
            if (!image) return;
            image.hidden = true;
            var placeholder = image.parentElement.querySelector('.file-thumbnail-placeholder');
            if (placeholder) placeholder.hidden = false;
        }, true);
    });
}());
