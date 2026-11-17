// ============================================================
// Обработка отправки форм с всплывающими уведомлениями (toast)
// Данные отправляются на сервер через MVC API.
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    // Контейнер для уведомлений
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
    toastContainer.style.zIndex = '2000';
    document.body.appendChild(toastContainer);

    // Показ всплывающего сообщения
    function showToast(success, text) {
        const el = document.createElement('div');
        el.className = 'toast align-items-center border-0 ' +
            (success ? 'toast-success' : 'toast-error');
        el.setAttribute('role', 'alert');
        el.setAttribute('aria-live', 'assertive');
        el.setAttribute('aria-atomic', 'true');
        const content = document.createElement('div');
        content.className = 'd-flex';
        const body = document.createElement('div');
        body.className = 'toast-body fw-bold';
        const icon = document.createElement('i');
        icon.className = 'bi ' + (success ? 'bi-check-circle-fill' : 'bi-x-circle-fill') + ' me-2';
        body.append(icon, document.createTextNode(text || (success
            ? 'Сообщение успешно отправлено!'
            : 'Не удалось отправить сообщение. Попробуйте ещё раз.')));
        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'btn-close btn-close-white me-2 m-auto';
        close.setAttribute('data-bs-dismiss', 'toast');
        close.setAttribute('aria-label', 'Закрыть');
        content.append(body, close);
        el.appendChild(content);
        toastContainer.appendChild(el);

        const toast = new bootstrap.Toast(el, { delay: 4000 });
        toast.show();

        // Удаляем элемент после скрытия
        el.addEventListener('hidden.bs.toast', function () {
            el.remove();
        });
    }

    function getFieldLabel(field) {
        if (field.id) {
            const label = document.querySelector('label[for="' + CSS.escape(field.id) + '"]');
            if (label) return label.textContent.trim();
        }
        const wrappingLabel = field.closest('label');
        if (wrappingLabel) return wrappingLabel.textContent.trim();
        return field.getAttribute('aria-label') || field.name || 'Поле формы';
    }

    function getValidationMessage(form) {
        const invalidFields = Array.from(form.querySelectorAll('input, select, textarea'))
            .filter(function (field) {
                return field.willValidate && !field.validity.valid;
            });
        if (!invalidFields.length) return 'Проверьте обязательные поля формы.';

        const missing = [];
        const invalid = [];
        invalidFields.forEach(function (field) {
            const label = getFieldLabel(field);
            const isEmpty = field.type === 'checkbox' || field.type === 'radio'
                ? !field.checked
                : !String(field.value || '').trim();
            const target = isEmpty && field.required ? missing : invalid;
            if (target.indexOf(label) === -1) target.push(label);
        });

        const messages = [];
        if (missing.length) messages.push('Не заполнены обязательные поля: ' + missing.join(', ') + '.');
        if (invalid.length) messages.push('Проверьте поля: ' + invalid.join(', ') + '.');
        return messages.join(' ');
    }

    // Отправка формы на PHP-обработчик
    const forms = document.querySelectorAll('form.order-form');
    forms.forEach(function (form) {
        form.noValidate = true;
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!form.reportValidity()) {
                showToast(false, getValidationMessage(form));
                return;
            }

            const action = form.getAttribute('action');
            if (!action) {
                showToast(false, 'Не удалось отправить форму: не указан адрес обработчика.');
                return;
            }

            // Блокируем кнопку на время отправки
            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.dataset.originalText = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Отправка...';
            }

            // Отправка данных на сервер
            fetch(action, { method: 'POST', body: new FormData(form) })
                .then(function (response) {
                    return response.json().then(function (data) {
                        if (!response.ok || !data || !data.success) {
                            throw new Error(data && data.error
                                ? data.error
                                : 'Не удалось отправить сообщение. Попробуйте ещё раз.');
                        }
                        return data;
                    });
                })
                .then(function () {
                    showToast(true);
                    form.reset();
                })
                .catch(function (error) {
                    // Сервер недоступен или ответ не JSON
                    showToast(false, error.message);
                })
                .finally(function () {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = btn.dataset.originalText;
                    }
                });
        });
    });
});
