(function () {
    'use strict';

    var tableBody = document.getElementById('customFieldsTableBody');
    if (!tableBody) return;

    var typeLabels = {
        text: 'Текст',
        textarea: 'Длинный текст',
        number: 'Число',
        url: 'Ссылка',
        image: 'Изображение'
    };

    var elements = {
        modal: document.getElementById('customFieldModal'),
        id: document.getElementById('customFieldId'),
        key: document.getElementById('customFieldKey'),
        label: document.getElementById('customFieldLabel'),
        type: document.getElementById('customFieldType'),
        group: document.getElementById('customFieldGroup'),
        order: document.getElementById('customFieldOrder'),
        value: document.getElementById('customFieldValue'),
        valueWrap: document.getElementById('customFieldValueWrap'),
        imageWrap: document.getElementById('customFieldImageWrap'),
        imageFile: document.getElementById('customFieldImageFile'),
        imagePreview: document.getElementById('customFieldImagePreview'),
        uploadBtn: document.getElementById('customFieldUploadBtn'),
        active: document.getElementById('customFieldActive'),
        saveBtn: document.getElementById('customFieldSaveBtn'),
        error: document.getElementById('customFieldError'),
        modalTitle: document.getElementById('customFieldModalLabel')
    };

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (char) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
        });
    }

    function showError(message) {
        elements.error.textContent = message;
        elements.error.style.display = 'block';
    }

    function hideError() {
        elements.error.style.display = 'none';
    }

    // Переключение между текстовым значением и загрузкой изображения
    function syncValueType() {
        var isImage = elements.type.value === 'image';
        elements.valueWrap.hidden = isImage;
        elements.imageWrap.hidden = !isImage;
    }

    function openCreate() {
        hideError();
        elements.id.value = '0';
        elements.key.value = '';
        elements.label.value = '';
        elements.type.value = 'text';
        elements.group.value = 'general';
        elements.order.value = '0';
        elements.value.value = '';
        elements.active.checked = true;
        elements.imageFile.value = '';
        elements.imagePreview.innerHTML = '';
        elements.modalTitle.textContent = 'Добавить поле';
        syncValueType();
    }

    function openEdit(field) {
        hideError();
        elements.id.value = field.id;
        elements.key.value = field.field_key;
        elements.label.value = field.label;
        elements.type.value = field.field_type;
        elements.group.value = field.field_group;
        elements.order.value = field.display_order;
        elements.value.value = field.field_type === 'image' ? '' : (field.field_value || '');
        elements.active.checked = Number(field.is_active) === 1;
        elements.imageFile.value = '';
        elements.imagePreview.innerHTML = field.field_type === 'image' && field.field_value
            ? '<img src="' + escapeHtml(field.field_value) + '" alt="Превью" style="max-height:120px;border-radius:8px;">'
            : '';
        elements.modalTitle.textContent = 'Редактировать поле';
        syncValueType();
    }

    function saveField() {
        hideError();
        var payload = {
            id: parseInt(elements.id.value, 10) || 0,
            field_key: elements.key.value.trim(),
            label: elements.label.value.trim(),
            field_type: elements.type.value,
            field_group: elements.group.value.trim() || 'general',
            display_order: parseInt(elements.order.value, 10) || 0,
            is_active: elements.active.checked ? 1 : 0,
            field_value: elements.type.value === 'image'
                ? (elements.imagePreview.dataset.url || '')
                : elements.value.value.trim()
        };

        window.AdminApi.save('/api/admin/custom-fields', 'POST', payload).then(function () {
            var modal = bootstrap.Modal.getInstance(elements.modal);
            if (modal) modal.hide();
            loadFields();
            window.AdminNotifications.show(true, 'Поле сохранено');
        }).catch(function (error) {
            showError(error.message || 'Не удалось сохранить поле');
        });
    }

    function uploadImage() {
        hideError();
        var fileId = parseInt(elements.id.value, 10) || 0;
        if (fileId <= 0) {
            showError('Сначала сохраните поле, затем загрузите изображение');
            return;
        }
        var file = elements.imageFile.files[0];
        if (!file) {
            showError('Выберите файл изображения');
            return;
        }

        elements.uploadBtn.disabled = true;
        elements.uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Загрузка…';

        window.AdminApi.request('/api/auth/csrf').then(function (csrf) {
            if (!csrf.success || !csrf.token) throw new Error('Не удалось получить CSRF-токен');
            var formData = new FormData();
            formData.append('image', file);
            return fetch('/api/admin/custom-fields/' + fileId + '/image', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-Token': csrf.token },
                body: formData
            }).then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || !data.success) throw new Error(data.error || 'Не удалось загрузить изображение');
                    return data;
                });
            });
        }).then(function (data) {
            elements.imagePreview.innerHTML = '<img src="' + escapeHtml(data.url) + '?v=' + Date.now() + '" alt="Превью" style="max-height:120px;border-radius:8px;">';
            elements.imagePreview.dataset.url = data.url;
            window.AdminNotifications.show(true, 'Изображение загружено');
        }).catch(function (error) {
            showError(error.message || 'Не удалось загрузить изображение');
        }).finally(function () {
            elements.uploadBtn.disabled = false;
            elements.uploadBtn.innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Загрузить изображение';
        });
    }

    function render() {
        var fields = currentFields;
        if (!fields.length) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Полей пока нет</td></tr>';
            return;
        }
        var html = '';
        fields.forEach(function (field) {
            var value = field.field_type === 'image' && field.field_value
                ? '<img src="' + escapeHtml(field.field_value) + '" alt="" style="max-height:40px;border-radius:6px;">'
                : escapeHtml(String(field.field_value || '').substring(0, 120));
            html += '<tr>' +
                '<td>' + escapeHtml(field.field_group) + '</td>' +
                '<td class="font-monospace">' + escapeHtml(field.field_key) + '</td>' +
                '<td>' + escapeHtml(field.label) + '</td>' +
                '<td>' + escapeHtml(typeLabels[field.field_type] || field.field_type) + '</td>' +
                '<td style="max-width:280px;word-break:break-all;">' + (value || '<span class="text-muted">—</span>') + '</td>' +
                '<td class="text-center">' +
                    '<div class="form-check form-switch d-flex justify-content-center">' +
                        '<input class="form-check-input field-toggle" type="checkbox" ' + (Number(field.is_active) === 1 ? 'checked' : '') + ' data-id="' + field.id + '">' +
                    '</div>' +
                '</td>' +
                '<td class="text-end text-nowrap">' +
                    '<button type="button" class="btn btn-outline-primary btn-sm field-edit-btn me-1" data-id="' + field.id + '" title="Редактировать"><i class="bi bi-pencil"></i></button> ' +
                    '<button type="button" class="btn btn-outline-danger btn-sm field-delete-btn" data-id="' + field.id + '" title="Удалить"><i class="bi bi-trash"></i></button>' +
                '</td>' +
                '</tr>';
        });
        tableBody.innerHTML = html;

        tableBody.querySelectorAll('.field-edit-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var field = currentFields.find(function (f) { return f.id == btn.dataset.id; });
                if (field) openEdit(field);
            });
        });

        tableBody.querySelectorAll('.field-delete-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!window.confirm('Удалить поле?')) return;
                window.AdminApi.remove('/api/admin/custom-fields/' + btn.dataset.id).then(function () {
                    loadFields();
                    window.AdminNotifications.show(true, 'Поле удалено');
                }).catch(function (error) {
                    window.AdminNotifications.show(false, error.message || 'Не удалось удалить поле');
                });
            });
        });

        tableBody.querySelectorAll('.field-toggle').forEach(function (toggle) {
            toggle.addEventListener('change', function () {
                window.AdminApi.save('/api/admin/custom-fields/' + toggle.dataset.id + '/toggle', 'POST', {}).then(function () {
                    loadFields();
                }).catch(function (error) {
                    window.AdminNotifications.show(false, error.message || 'Не удалось переключить');
                    loadFields();
                });
            });
        });
    }

    var currentFields = [];

    function loadFields() {
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Загрузка…</td></tr>';
        window.AdminApi.get('/api/admin/custom-fields').then(function (data) {
            currentFields = data.fields || [];
            render();
        }).catch(function (error) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">' + escapeHtml(error.message) + '</td></tr>';
        });
    }

    elements.type.addEventListener('change', syncValueType);
    elements.saveBtn.addEventListener('click', saveField);
    elements.uploadBtn.addEventListener('click', uploadImage);
    elements.modal.addEventListener('show.bs.modal', function (event) {
        // При открытии кнопкой «Добавить поле» очищаем форму
        if (!event.relatedTarget) return;
        openCreate();
    });

    loadFields();
}());
