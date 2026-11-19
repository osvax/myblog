(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var userId = 0;
        var currentProfile = null;

        // ---------- DOM элементы ----------
        var profileAvatar = document.getElementById('profileAvatar');
        var avatarFile = document.getElementById('avatarFile');
        var uploadAvatarBtn = document.getElementById('uploadAvatar');
        var avatarError = document.getElementById('avatarError');
        var avatarSuccess = document.getElementById('avatarSuccess');
        var profileCardName = document.getElementById('profileCardName');
        var profileCardEmail = document.getElementById('profileCardEmail');
        var profileCardRole = document.getElementById('profileCardRole');
        var profileCardPosition = document.getElementById('profileCardPosition');

        var profileForm = document.getElementById('profileForm');
        var resetProfileBtn = document.getElementById('resetProfile');
        var profileError = document.getElementById('profileError');
        var profileSuccess = document.getElementById('profileSuccess');

        var passwordForm = document.getElementById('passwordForm');
        var passwordChangeError = document.getElementById('passwordChangeError');
        var passwordChangeSuccess = document.getElementById('passwordChangeSuccess');

        // ---------- Вспомогательные функции ----------
        function showError(msg) {
            profileError.textContent = msg;
            profileError.style.display = 'block';
            setTimeout(function () { profileError.style.display = 'none'; }, 5000);
        }

        function showSuccess(msg) {
            profileSuccess.textContent = msg;
            profileSuccess.style.display = 'block';
            setTimeout(function () { profileSuccess.style.display = 'none'; }, 3000);
        }

        function setAvatarMessage(error, message) {
            avatarError.hidden = !error;
            avatarSuccess.hidden = error || !message;
            avatarError.textContent = error ? message : '';
            avatarSuccess.textContent = error ? '' : message;
        }

        function roleLabel(role) {
            var labels = { user: 'Пользователь', editor: 'Редактор', manager: 'Менеджер', admin: 'Администратор' };
            return labels[role] || role;
        }

        // ---------- Загрузка данных профиля ----------
        function loadProfile() {
            // Получаем userId из URL параметра ?id=X, или берём текущего пользователя
            var params = new URLSearchParams(window.location.search);
            var urlUserId = parseInt(params.get('id'), 10);
            if (!isNaN(urlUserId) && urlUserId > 0) {
                userId = urlUserId;
            } else if (window.ADMIN_USER_ID && window.ADMIN_USER_ID > 0) {
                userId = window.ADMIN_USER_ID;
            }

            if (!userId) {
                showError('Не указан ID пользователя');
                return;
            }

            AdminApi.get('/api/admin/profile/' + userId).then(function (data) {
                currentProfile = data.user;

                // Заполняем карточку
                profileCardName.textContent = data.user.name || 'Без имени';
                profileCardEmail.textContent = data.user.email;
                profileCardRole.textContent = roleLabel(data.user.role);
                profileCardRole.className = 'badge bg-orange fs-6';
                profileCardPosition.textContent = data.user.position || '';

                // Аватар
                if (data.user.avatar_path) {
                    profileAvatar.src = data.user.avatar_path + '?v=' + Date.now();
                    profileAvatar.onerror = function() {
                        console.error('Ошибка загрузки аватара:', data.user.avatar_path);
                        profileAvatar.src = '/img/avatar-placeholder.svg';
                    };
                } else {
                    profileAvatar.src = '/img/avatar-placeholder.svg';
                }
                console.log('Аватар установлен:', profileAvatar.src);

                // Заполняем форму
                profileForm.elements.namedItem('name').value = data.user.name || '';
                profileForm.elements.namedItem('email').value = data.user.email || '';
                profileForm.elements.namedItem('phone').value = data.user.phone || '';
                profileForm.elements.namedItem('position').value = data.user.position || '';
                profileForm.elements.namedItem('about').value = data.user.about || '';
                profileForm.elements.namedItem('bio').value = data.user.bio || '';
                profileForm.elements.namedItem('timezone').value = data.user.timezone || '';
                profileForm.elements.namedItem('language').value = data.user.language || 'ru';
            }).catch(function (error) {
                showError('Не удалось загрузить профиль: ' + error.message);
            });
        }

        // ---------- Загрузка аватара ----------
        function uploadAvatar() {
            var file = avatarFile.files[0];
            if (!file) {
                setAvatarMessage(true, 'Сначала выберите изображение');
                return;
            }
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
                setAvatarMessage(true, 'Выберите JPEG, PNG или WebP размером не более 2 МБ');
                return;
            }

            uploadAvatarBtn.disabled = true;
            uploadAvatarBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Загрузка...';
            setAvatarMessage(false, '');

            var formData = new FormData();
            formData.append('avatar', file);

            fetch('/api/auth/csrf', { credentials: 'same-origin' })
                .then(function (response) { return response.json(); })
                .then(function (csrfData) {
                    if (!csrfData.success || !csrfData.token) throw new Error('Не удалось получить CSRF-токен');
                    return fetch('/api/admin/profile/' + userId + '/avatar', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'X-CSRF-Token': csrfData.token },
                        body: formData
                    });
                })
                .then(function (response) {
                    return response.json().then(function (data) {
                        if (!response.ok || !data.success) {
                            throw new Error(data.error || 'Не удалось загрузить аватар');
                        }
                        return data;
                    });
                })
                .then(function (data) {
                    profileAvatar.src = data.avatar_path + '?v=' + Date.now();
                    avatarFile.value = '';
                    setAvatarMessage(false, 'Аватар загружен');
                    if (window.AdminNotifications) {
                        window.AdminNotifications.show(true, 'Аватар успешно загружен');
                    }
                })
                .catch(function (error) {
                    setAvatarMessage(true, error.message || 'Не удалось загрузить аватар');
                    if (window.AdminNotifications) {
                        window.AdminNotifications.show(false, error.message || 'Не удалось загрузить аватар');
                    }
                })
                .finally(function () {
                    uploadAvatarBtn.disabled = false;
                    uploadAvatarBtn.innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Загрузить аватар';
                });
        }

        // ---------- Сохранение профиля ----------
        function saveProfile() {
            if (!profileForm.checkValidity()) {
                profileForm.classList.add('was-validated');
                showError('Заполните обязательные поля');
                return;
            }

            var profile = {};
            ['name', 'phone', 'about', 'position', 'bio', 'timezone', 'language'].forEach(function (key) {
                var field = profileForm.elements.namedItem(key);
                profile[key] = field ? field.value.trim() : '';
            });

            AdminApi.save('/api/admin/profile/' + userId, 'PUT', profile).then(function () {
                profileCardName.textContent = profile.name;
                profileCardPosition.textContent = profile.position || '';
                showSuccess('Профиль сохранён');
                if (window.AdminNotifications) {
                    window.AdminNotifications.show(true, 'Профиль успешно сохранён');
                }
            }).catch(function (error) {
                showError('Не удалось сохранить профиль: ' + error.message);
            });
        }

        // ---------- Сброс формы ----------
        function resetForm() {
            loadProfile();
            profileForm.classList.remove('was-validated');
        }

        // ---------- Смена пароля ----------
        function showPasswordMessage(errorEl, successEl, isError, message) {
            if (errorEl) {
                errorEl.textContent = isError ? message : '';
                errorEl.style.display = isError ? 'block' : 'none';
            }
            if (successEl) {
                successEl.textContent = isError ? '' : message;
                successEl.style.display = isError ? 'none' : 'block';
                if (!isError && message) {
                    setTimeout(function () { successEl.style.display = 'none'; }, 4000);
                }
            }
        }

        function changePassword() {
            var oldPassword = passwordForm.elements.namedItem('old_password').value;
            var newPassword = passwordForm.elements.namedItem('new_password').value;
            var newPasswordConfirm = passwordForm.elements.namedItem('new_password_confirm').value;

            showPasswordMessage(passwordChangeError, passwordChangeSuccess, false, '');

            if (!oldPassword) {
                showPasswordMessage(passwordChangeError, passwordChangeSuccess, true, 'Введите текущий пароль');
                return;
            }
            if (newPassword.length < 8) {
                showPasswordMessage(passwordChangeError, passwordChangeSuccess, true, 'Новый пароль должен содержать минимум 8 символов');
                return;
            }
            if (newPassword !== newPasswordConfirm) {
                showPasswordMessage(passwordChangeError, passwordChangeSuccess, true, 'Новые пароли не совпадают');
                return;
            }

            var submitBtn = passwordForm.querySelector('button[type="submit"]');
            var originalHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Смена...';
            }

            AdminApi.save('/api/admin/profile/' + userId + '/password', 'POST', {
                old_password: oldPassword,
                new_password: newPassword,
                new_password_confirm: newPasswordConfirm
            }).then(function () {
                passwordForm.reset();
                showPasswordMessage(passwordChangeError, passwordChangeSuccess, false, 'Пароль успешно изменён');
                if (window.AdminNotifications) {
                    window.AdminNotifications.show(true, 'Пароль успешно изменён');
                }
            }).catch(function (error) {
                showPasswordMessage(passwordChangeError, passwordChangeSuccess, true, error.message || 'Не удалось сменить пароль');
            }).finally(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                }
            });
        }

        // ---------- Обработчики событий ----------
        uploadAvatarBtn.addEventListener('click', uploadAvatar);

        profileForm.addEventListener('submit', function (e) {
            e.preventDefault();
            saveProfile();
        });

        if (passwordForm) {
            passwordForm.addEventListener('submit', function (e) {
                e.preventDefault();
                changePassword();
            });
        }

        resetProfileBtn.addEventListener('click', resetForm);

        // ---------- Инициализация ----------
        loadProfile();
    });
}());
