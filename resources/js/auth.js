// ============================================================
// Страницы авторизации: вход (+2FA), регистрация, восстановление,
// сброс пароля, профиль
//
// ESM-порт legacy public/js/auth.js под реальные формы
// (id: loginForm, loginStep1/2, registerForm, forgotForm, resetForm,
// profileForm, passwordForm, totp*…) и реальный JSON API:
//   /api/auth/user/login, /api/auth/user/verify-2fa,
//   /api/auth/register, /api/auth/forgot-password, /api/auth/reset-password,
//   /api/auth/user/2fa/{setup,enable,disable},
//   /api/profile/{update,change-password,delete}
// Модуль грузится в production-ветке лейаута, когда у страницы
// $includeAuthScript = true (в dev-ветке грузится legacy /js/auth.js).
// ============================================================

function showError(containerId, message) {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.textContent = message;
    el.style.display = 'block';
}

function hideError(containerId) {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.style.display = 'none';
}

function showSuccess(containerId, message) {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.textContent = message;
    el.style.display = 'block';
}

// ---------- Форма входа (+ шаг 2FA) ----------
let pending2fa = false;
const loginForm = document.getElementById('loginForm');
const loginStep1 = document.getElementById('loginStep1');
const loginStep2 = document.getElementById('loginStep2');
const login2faBack = document.getElementById('login2faBack');
const loginSubmitBtn = loginForm ? loginForm.querySelector('button[type="submit"]') : null;

function show2faStep(show) {
    pending2fa = show;
    if (loginStep1) loginStep1.hidden = show;
    if (loginStep2) loginStep2.hidden = !show;
    if (login2faBack) login2faBack.hidden = !show;
    if (loginSubmitBtn) loginSubmitBtn.textContent = show ? 'Подтвердить' : 'Войти';
    const emailField = document.getElementById('loginEmail');
    if (emailField) emailField.required = !show;
    const passwordField = document.getElementById('loginPassword');
    if (passwordField) passwordField.required = !show;
}

if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();
        hideError('loginError');

        if (pending2fa) {
            const codeEl = document.getElementById('login2faCode');
            const code = codeEl ? codeEl.value.trim() : '';
            if (code === '') {
                showError('loginError', 'Введите код из приложения');
                return;
            }
            fetch('/api/auth/user/verify-2fa', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code: code })
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        if (!response.ok || !data.success) {
                            throw new Error(data.error || 'Неверный код');
                        }
                        return data;
                    });
                })
                .then(function () {
                    window.location.href = '/';
                })
                .catch(function (error) {
                    showError('loginError', error.message);
                });
            return;
        }

        const email = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value;

        fetch('/api/auth/user/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email, password: password })
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || !data.success) {
                        throw new Error(data.error || 'Ошибка входа');
                    }
                    return data;
                });
            })
            .then(function (data) {
                if (data.require_2fa) {
                    show2faStep(true);
                    const codeField = document.getElementById('login2faCode');
                    if (codeField) codeField.focus();
                    return;
                }
                window.location.href = '/';
            })
            .catch(function (error) {
                showError('loginError', error.message);
            });
    });
}

if (login2faBack) {
    login2faBack.addEventListener('click', function () {
        hideError('loginError');
        show2faStep(false);
    });
}

// ---------- Форма регистрации ----------
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', function (e) {
        e.preventDefault();
        hideError('registerError');

        const name = document.getElementById('registerName').value.trim();
        const email = document.getElementById('registerEmail').value.trim();
        const password = document.getElementById('registerPassword').value;
        const passwordConfirm = document.getElementById('registerPasswordConfirm').value;

        if (name.length < 2) {
            showError('registerError', 'Имя должно содержать минимум 2 символа');
            return;
        }
        if (password.length < 8) {
            showError('registerError', 'Пароль должен содержать минимум 8 символов');
            return;
        }
        if (password !== passwordConfirm) {
            showError('registerError', 'Пароли не совпадают');
            return;
        }

        fetch('/api/auth/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: name, email: email, password: password, password_confirm: passwordConfirm })
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || !data.success) {
                        throw new Error(data.error || 'Ошибка регистрации');
                    }
                    return data;
                });
            })
            .then(function () {
                window.location.href = '/login.html?registered=1';
            })
            .catch(function (error) {
                showError('registerError', error.message);
            });
    });
}

// ---------- Форма восстановления пароля ----------
const forgotForm = document.getElementById('forgotForm');
if (forgotForm) {
    forgotForm.addEventListener('submit', function (e) {
        e.preventDefault();
        hideError('forgotError');
        hideError('forgotSuccess');

        const email = document.getElementById('forgotEmail').value.trim();

        fetch('/api/auth/forgot-password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email })
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || !data.success) {
                        throw new Error(data.error || 'Ошибка отправки');
                    }
                    return data;
                });
            })
            .then(function (data) {
                showSuccess('forgotSuccess', data.message || 'Ссылка для сброса пароля отправлена на ' + email);
                document.getElementById('forgotEmail').value = '';
            })
            .catch(function (error) {
                showError('forgotError', error.message);
            });
    });
}

// ---------- Форма сброса пароля ----------
const resetForm = document.getElementById('resetForm');
if (resetForm) {
    resetForm.addEventListener('submit', function (e) {
        e.preventDefault();
        hideError('resetError');
        hideError('resetSuccess');

        const token = document.getElementById('resetToken').value;
        const password = document.getElementById('resetPassword').value;
        const passwordConfirm = document.getElementById('resetPasswordConfirm').value;

        if (password.length < 8) {
            showError('resetError', 'Пароль должен содержать минимум 8 символов');
            return;
        }
        if (password !== passwordConfirm) {
            showError('resetError', 'Пароли не совпадают');
            return;
        }

        fetch('/api/auth/reset-password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            // password_confirm обязателен: сервер проверяет совпадение
            body: JSON.stringify({ token: token, password: password, password_confirm: passwordConfirm })
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || !data.success) {
                        throw new Error(data.error || 'Ошибка сброса пароля');
                    }
                    return data;
                });
            })
            .then(function () {
                showSuccess('resetSuccess', 'Пароль успешно изменён. Перенаправляем на страницу входа...');
                setTimeout(function () {
                    window.location.href = '/login.html';
                }, 2000);
            })
            .catch(function (error) {
                showError('resetError', error.message);
            });
    });
}

// ---------- Форма профиля ----------
const profileForm = document.getElementById('profileForm');
if (profileForm) {
    profileForm.addEventListener('submit', function (e) {
        e.preventDefault();
        hideError('profileError');
        hideError('profileSuccess');

        const name = document.getElementById('profileName').value.trim();

        if (name.length < 2) {
            showError('profileError', 'Имя должно содержать минимум 2 символа');
            return;
        }

        fetch('/api/profile/update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: name })
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || !data.success) {
                        throw new Error(data.error || 'Ошибка обновления');
                    }
                    return data;
                });
            })
            .then(function () {
                showSuccess('profileSuccess', 'Профиль успешно обновлён');
                location.reload();
            })
            .catch(function (error) {
                showError('profileError', error.message);
            });
    });
}

// ---------- Аватар профиля ----------
const avatarForm = document.getElementById('avatarForm');
const avatarInput = document.getElementById('profileAvatar');
if (avatarForm && avatarInput) {
    avatarInput.addEventListener('change', function () {
        if (!avatarInput.files || !avatarInput.files[0]) return;
        const formData = new FormData();
        formData.append('avatar', avatarInput.files[0]);
        fetch('/api/profile/avatar', { method: 'POST', body: formData })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || !data.success) throw new Error(data.error || 'Ошибка загрузки аватара');
                    return data;
                });
            })
            .then(function (data) {
                const avatar = document.querySelector('.profile-avatar img');
                if (avatar) avatar.src = data.avatar_path + '?v=' + Date.now();
                else window.location.reload();
                showSuccess('avatarSuccess', 'Аватар успешно обновлён');
            })
            .catch(function (error) {
                showError('avatarError', error.message);
            })
            .finally(function () {
                avatarInput.value = '';
            });
    });
}

// ---------- Форма смены пароля ----------
const passwordForm = document.getElementById('passwordForm');
if (passwordForm) {
    passwordForm.addEventListener('submit', function (e) {
        e.preventDefault();
        hideError('passwordError');
        hideError('passwordSuccess');

        const oldPassword = document.getElementById('oldPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const newPasswordConfirm = document.getElementById('newPasswordConfirm').value;

        if (oldPassword.length < 8) {
            showError('passwordError', 'Текущий пароль должен содержать минимум 8 символов');
            return;
        }
        if (newPassword.length < 8) {
            showError('passwordError', 'Новый пароль должен содержать минимум 8 символов');
            return;
        }
        if (newPassword !== newPasswordConfirm) {
            showError('passwordError', 'Новые пароли не совпадают');
            return;
        }

        fetch('/api/profile/change-password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                old_password: oldPassword,
                new_password: newPassword,
                new_password_confirm: newPasswordConfirm
            })
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || !data.success) {
                        throw new Error(data.error || 'Ошибка смены пароля');
                    }
                    return data;
                });
            })
            .then(function () {
                showSuccess('passwordSuccess', 'Пароль успешно изменён');
                passwordForm.reset();
            })
            .catch(function (error) {
                showError('passwordError', error.message);
            });
    });
}

// ---------- Удаление аккаунта ----------
const confirmDeleteBtn = document.getElementById('confirmDelete');
if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener('click', function () {
        hideError('deleteError');

        const password = document.getElementById('deletePassword').value;

        if (password.length < 8) {
            showError('deleteError', 'Введите пароль');
            return;
        }

        fetch('/api/profile/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password: password })
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || !data.success) {
                        throw new Error(data.error || 'Ошибка удаления');
                    }
                    return data;
                });
            })
            .then(function () {
                window.location.href = '/';
            })
            .catch(function (error) {
                showError('deleteError', error.message);
            });
    });
}

// ---------- Двухфакторная аутентификация: настройка в профиле ----------
function totpRequest(path, payload) {
    return fetch(path, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload || {})
    }).then(function (response) {
        return response.json().then(function (data) {
            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Ошибка запроса');
            }
            return data;
        });
    });
}

const totpSetupBtn = document.getElementById('totpSetupBtn');
if (totpSetupBtn) {
    totpSetupBtn.addEventListener('click', function () {
        hideError('totpError');
        hideError('totpSuccess');
        totpRequest('/api/auth/user/2fa/setup').then(function (data) {
            const secretEl = document.getElementById('totpSecretText');
            const linkEl = document.getElementById('totpUriLink');
            const detailsEl = document.getElementById('totpSetupDetails');
            if (secretEl) secretEl.textContent = data.secret;
            if (linkEl) linkEl.href = data.uri;
            if (detailsEl) detailsEl.hidden = false;
            totpSetupBtn.hidden = true;
        }).catch(function (error) {
            showError('totpError', error.message);
        });
    });

    const totpEnableBtn = document.getElementById('totpEnableBtn');
    if (totpEnableBtn) {
        totpEnableBtn.addEventListener('click', function () {
            hideError('totpError');
            const codeEl = document.getElementById('totpEnableCode');
            const code = codeEl ? codeEl.value.trim() : '';
            totpRequest('/api/auth/user/2fa/enable', { code: code }).then(function () {
                showSuccess('totpSuccess', 'Двухфакторная аутентификация включена');
                setTimeout(function () { location.reload(); }, 1200);
            }).catch(function (error) {
                showError('totpError', error.message);
            });
        });
    }

    const totpDisableBtn = document.getElementById('totpDisableBtn');
    if (totpDisableBtn) {
        totpDisableBtn.addEventListener('click', function () {
            hideError('totpError');
            const codeEl = document.getElementById('totpDisableCode');
            const code = codeEl ? codeEl.value.trim() : '';
            totpRequest('/api/auth/user/2fa/disable', { code: code }).then(function () {
                showSuccess('totpSuccess', 'Двухфакторная аутентификация отключена');
                setTimeout(function () { location.reload(); }, 1200);
            }).catch(function (error) {
                showError('totpError', error.message);
            });
        });
    }
}
