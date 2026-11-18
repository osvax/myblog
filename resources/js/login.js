// ============================================================
// Admin login page entry point
// ============================================================

import '../scss/admin.scss';

// Handle admin login form
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('adminLoginForm');
  const submit = document.getElementById('adminLoginSubmit');
  const errorEl = document.getElementById('loginError');

  if (!form) return;

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;

    errorEl.hidden = true;
    submit.disabled = true;
    submit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Проверка...';

    try {
      const response = await fetch('/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({
          email: form.elements.email.value,
          password: form.elements.password.value
        })
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        throw new Error(data.error || 'Не удалось выполнить вход');
      }

      window.location.href = '/admin/index.html';
    } catch (err) {
      errorEl.textContent = err.message || 'Сервер недоступен. Попробуйте позже.';
      errorEl.hidden = false;
    } finally {
      submit.disabled = false;
      submit.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Войти';
    }
  });
});