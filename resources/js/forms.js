// ============================================================
// Обработка отправки форм с всплывающими уведомлениями (toast).
//
// ESM-порт legacy public/js/forms.js.
// Обрабатывает ВСЕ формы form.order-form:
//   - форма заказа   (action="/api/forms/order", multipart)
//   - обратная связь (action="/api/forms/feedback")
//   - комментарии на статьях (action="/api/forms/comment")
// Данные отправляются на action формы (MVC API), адрес не хардкодится.
//
// Формы авторизации (auth-form) и профиль (profile-form) обрабатывает
// отдельный entry auth.js — здесь они не трогаются, иначе будет
// двойная инициализация.
//
// Модуль выполняется после разбора DOM, DOMContentLoaded не нужен.
// Toast берётся напрямую из bootstrap (ESM) — в production-бандле
// глобального window.bootstrap нет (CDN-бандл грузится только в
// dev-ветке лейаута).
// ============================================================

import { Toast } from 'bootstrap';

// CSRF токен из meta тега
const csrfToken =
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

// Контейнер для уведомлений
const toastContainer = document.createElement('div');
toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
toastContainer.style.zIndex = '2000';
document.body.appendChild(toastContainer);

// Показ всплывающего сообщения
function showToast(success, text) {
    const el = document.createElement('div');
    el.className =
        'toast align-items-center border-0 ' + (success ? 'toast-success' : 'toast-error');
    el.setAttribute('role', 'alert');
    el.setAttribute('aria-live', 'assertive');
    el.setAttribute('aria-atomic', 'true');
    const content = document.createElement('div');
    content.className = 'd-flex';
    const body = document.createElement('div');
    body.className = 'toast-body fw-bold';
    const icon = document.createElement('i');
    icon.className = 'bi ' + (success ? 'bi-check-circle-fill' : 'bi-x-circle-fill') + ' me-2';
    body.append(
        icon,
        document.createTextNode(
            text ||
                (success
                    ? 'Сообщение успешно отправлено!'
                    : 'Не удалось отправить сообщение. Попробуйте ещё раз.'),
        ),
    );
    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'btn-close btn-close-white me-2 m-auto';
    close.setAttribute('data-bs-dismiss', 'toast');
    close.setAttribute('aria-label', 'Закрыть');
    content.append(body, close);
    el.appendChild(content);
    toastContainer.appendChild(el);

    const toast = new Toast(el, { delay: 4000 });
    toast.show();

    // Удаляем элемент после скрытия
    el.addEventListener('hidden.bs.toast', () => el.remove());
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
    const invalidFields = Array.from(form.querySelectorAll('input, select, textarea')).filter(
        (field) => field.willValidate && !field.validity.valid,
    );
    if (!invalidFields.length) return 'Проверьте обязательные поля формы.';

    const missing = [];
    const invalid = [];
    invalidFields.forEach((field) => {
        const label = getFieldLabel(field);
        const isEmpty =
            field.type === 'checkbox' || field.type === 'radio'
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
document.querySelectorAll('form.order-form').forEach((form) => {
    form.noValidate = true;
    form.addEventListener('submit', (e) => {
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
        const formData = new FormData(form);
        if (csrfToken) {
            formData.append('_token', csrfToken);
        }
        fetch(action, { method: 'POST', body: formData })
            .then((response) =>
                response.json().then((data) => {
                    if (!response.ok || !data || !data.success) {
                        throw new Error(
                            (data && data.error) ||
                                'Не удалось отправить сообщение. Попробуйте ещё раз.',
                        );
                    }
                    return data;
                }),
            )
            .then(() => {
                showToast(true);
                form.reset();
            })
            .catch((error) => {
                // Сервер недоступен или ответ не JSON
                showToast(false, error.message);
            })
            .finally(() => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = btn.dataset.originalText;
                }
            });
    });
});
