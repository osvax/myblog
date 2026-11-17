(function () {
    'use strict';

    // ---------- Утилиты ----------
    function showError(containerId, message) {
        var el = document.getElementById(containerId);
        if (!el) return;
        el.textContent = message;
        el.style.display = 'block';
    }

    function hideError(containerId) {
        var el = document.getElementById(containerId);
        if (!el) return;
        el.style.display = 'none';
    }

    function showSuccess(containerId, message) {
        var el = document.getElementById(containerId);
        if (!el) return;
        el.textContent = message;
        el.style.display = 'block';
    }

    // ---------- Форма входа ----------
    var loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();
            hideError('loginError');

            var email = document.getElementById('loginEmail').value.trim();
            var password = document.getElementById('loginPassword').value;

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
                .then(function () {
                    window.location.href = '/';
                })
                .catch(function (error) {
                    showError('loginError', error.message);
                });
        });
    }

    // ---------- Форма регистрации ----------
    var registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            e.preventDefault();
            hideError('registerError');

            var name = document.getElementById('registerName').value.trim();
            var email = document.getElementById('registerEmail').value.trim();
            var password = document.getElementById('registerPassword').value;
            var passwordConfirm = document.getElementById('registerPasswordConfirm').value;

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
    var forgotForm = document.getElementById('forgotForm');
    if (forgotForm) {
        forgotForm.addEventListener('submit', function (e) {
            e.preventDefault();
            hideError('forgotError');
            hideError('forgotSuccess');

            var email = document.getElementById('forgotEmail').value.trim();

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
    var resetForm = document.getElementById('resetForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function (e) {
            e.preventDefault();
            hideError('resetError');
            hideError('resetSuccess');

            var token = document.getElementById('resetToken').value;
            var password = document.getElementById('resetPassword').value;
            var passwordConfirm = document.getElementById('resetPasswordConfirm').value;

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
                body: JSON.stringify({ token: token, password: password })
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
    var profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function (e) {
            e.preventDefault();
            hideError('profileError');
            hideError('profileSuccess');

            var name = document.getElementById('profileName').value.trim();

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

    // ---------- Форма смены пароля ----------
    var passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function (e) {
            e.preventDefault();
            hideError('passwordError');
            hideError('passwordSuccess');

            var oldPassword = document.getElementById('oldPassword').value;
            var newPassword = document.getElementById('newPassword').value;
            var newPasswordConfirm = document.getElementById('newPasswordConfirm').value;

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
    var confirmDeleteBtn = document.getElementById('confirmDelete');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function () {
            hideError('deleteError');

            var password = document.getElementById('deletePassword').value;

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
})();
