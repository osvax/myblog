// ============================================================
// Страница входа в админ-панель — Vite entry
// Порт public/admin/js/login.js
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('adminLoginForm');
    const submit = document.getElementById('adminLoginSubmit');
    const error = document.getElementById('loginError');
    if (!form || !submit || !error) return;

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!form.reportValidity()) return;

        error.hidden = true;
        submit.disabled = true;
        submit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Проверка...';

        fetch('/api/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({
                email: form.elements.email.value,
                password: form.elements.password.value
            })
        }).then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Не удалось выполнить вход');
                }
                window.location.href = 'index.html';
            });
        }).catch(function (requestError) {
            error.textContent = requestError.message || 'Сервер недоступен. Попробуйте позже.';
            error.hidden = false;
            // На странице входа window.AdminNotifications может быть не загружен
            if (window.AdminNotifications) {
                window.AdminNotifications.show('error', error.textContent);
            }
        }).finally(function () {
            submit.disabled = false;
            submit.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Войти';
        });
    });
});
