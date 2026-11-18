(function () {
    'use strict';

    var form = document.getElementById('userEditForm');
    if (!form) return;

    var nameEl = document.getElementById('userName');
    var emailEl = document.getElementById('userEmail');
    var roleEl = document.getElementById('userRole');
    var statusEl = document.getElementById('userStatus');
    var saveBtn = document.getElementById('saveUserBtn');

    var avatarImageEl = document.getElementById('userAvatarImage');
    var avatarPlaceholderEl = document.getElementById('userAvatarPlaceholder');
    var avatarBtnEl = document.getElementById('userAvatarBtn');
    var avatarFileEl = document.getElementById('userAvatarFile');

    var passwordCard = document.getElementById('passwordCard');
    var passwordForm = document.getElementById('passwordForm');
    var newPasswordEl = document.getElementById('newPassword');
    var newConfirmEl = document.getElementById('newPasswordConfirm');
    var savePasswordBtn = document.getElementById('savePasswordBtn');

    var cardNameEl = document.getElementById('userCardName');
    var cardEmailEl = document.getElementById('userCardEmail');
    var cardRoleEl = document.getElementById('userCardRole');
    var cardStatusEl = document.getElementById('userCardStatus');
    var cardCreatedEl = document.getElementById('userCardCreated');
    var cardLastVisitEl = document.getElementById('userCardLastVisit');

    var errorEl = document.getElementById('editError');
    var successEl = document.getElementById('editSuccess');
    var roleNames = {};
    var currentUser = null;
    var currentUserId = parseInt(window.ADMIN_USER_ID || 0, 10);

    var params = new URLSearchParams(window.location.search);
    var userId = params.get('id');
    if (!userId) {
        errorEl.textContent = 'Не указан ID пользователя';
        errorEl.style.display = 'block';
        return;
    }
    userId = parseInt(userId, 10);
    var isSelf = userId === currentUserId;

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (char) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
        });
    }

    function formatDate(value) {
        if (!value) return '—';
        var d = new Date(value);
        return Number.isNaN(d.getTime()) ? '—' : d.toLocaleDateString('ru-RU');
    }

    function roleBadgeClass(role) {
        if (role === 'admin') return 'bg-warning text-dark';
        if (role === 'editor') return 'bg-info text-white';
        if (role === 'manager') return 'bg-success text-white';
        return 'bg-secondary';
    }

    function showError(message) {
        errorEl.textContent = message;
        errorEl.style.display = 'block';
        successEl.style.display = 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function showSuccess(message) {
        successEl.textContent = message;
        successEl.style.display = 'block';
        errorEl.style.display = 'none';
    }

    function setAvatar(path) {
        if (path) {
            avatarImageEl.src = path + (path.indexOf('?') === -1 ? '?' : '&') + 'v=' + Date.now();
            avatarImageEl.classList.remove('d-none');
            avatarPlaceholderEl.classList.add('d-none');
        } else {
            avatarImageEl.classList.add('d-none');
            avatarPlaceholderEl.classList.remove('d-none');
        }
    }

    function renderCard(user) {
        cardNameEl.textContent = user.name || 'Пользователь';
        cardEmailEl.textContent = user.email || '';
        cardRoleEl.textContent = user.role_name || roleNames[user.role] || user.role;
        cardRoleEl.className = 'badge fs-6 ' + roleBadgeClass(user.role);
        cardStatusEl.textContent = user.is_active ? 'Активен' : 'Заблокирован';
        cardStatusEl.className = 'badge fs-6 ' + (user.is_active ? 'bg-success' : 'bg-danger');
        cardCreatedEl.textContent = formatDate(user.created_at);
        cardLastVisitEl.textContent = user.last_visit ? formatDate(user.last_visit) : 'ещё не заходил';
        setAvatar(user.avatar_path);
    }

    // Роли из таблицы ролей
    function loadRoles() {
        return window.AdminApi.get('/api/admin/roles').then(function (data) {
            var roles = data.roles || [];
            if (!roles.length) return;
            roleNames = {};
            roleEl.innerHTML = roles.map(function (role) {
                roleNames[role.role_key] = role.name;
                return '<option value="' + escapeHtml(role.role_key) + '">' + escapeHtml(role.name) + '</option>';
            }).join('');
        }).catch(function (error) {
            console.error('Не удалось загрузить роли.', error);
        });
    }

    // ---------- Загрузка данных ----------
    loadRoles().then(function () {
        return window.AdminApi.get('/api/admin/users/' + userId).then(function (data) {
            var user = data.user;
            if (!user) {
                showError('Пользователь не найден');
                return;
            }
            currentUser = user;

            nameEl.value = user.name || '';
            emailEl.value = user.email || '';
            roleEl.value = user.role || 'user';
            if (roleEl.selectedIndex === -1 && roleEl.options.length) {
                roleEl.selectedIndex = 0;
            }
            statusEl.value = user.is_active ? '1' : '0';
            renderCard(user);

            // Свою роль и статус менять нельзя (защита от блокировки) — блокируем контролы
            if (isSelf) {
                roleEl.disabled = true;
                statusEl.disabled = true;
                if (passwordCard) passwordCard.classList.add('d-none');
            }
        });
    }).catch(function (error) {
        console.error('Не удалось загрузить пользователя.', error);
        showError(error.message || 'Не удалось загрузить данные пользователя');
    });

    // ---------- Замена аватара ----------
    if (avatarFileEl) {
        avatarBtnEl.classList.remove('d-none');
        avatarFileEl.addEventListener('change', function () {
            var file = avatarFileEl.files && avatarFileEl.files[0];
            avatarFileEl.value = '';
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                showError('Аватар слишком большой (макс. 2 МБ)');
                return;
            }
            var formData = new FormData();
            formData.append('avatar', file);
            avatarBtnEl.disabled = true;
            window.AdminApi.save('/api/admin/users/' + userId + '/avatar', 'POST', formData).then(function (data) {
                if (currentUser) currentUser.avatar_path = data.avatar_path;
                setAvatar(data.avatar_path);
                showSuccess('Аватар обновлён');
            }).catch(function (error) {
                console.error('Не удалось загрузить аватар.', error);
                showError(error.message || 'Не удалось загрузить аватар');
            }).finally(function () {
                avatarBtnEl.disabled = false;
            });
        });
    }

    // ---------- Сохранение данных ----------
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        errorEl.style.display = 'none';
        successEl.style.display = 'none';

        var name = nameEl.value.trim();
        var email = emailEl.value.trim();
        var role = roleEl.value;
        var isActive = statusEl.value === '1';

        if (name.length < 2 || name.length > 160) {
            showError('Имя должно содержать от 2 до 160 символов');
            return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError('Введите корректный email');
            return;
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Сохранение…';

        window.AdminApi.save('/api/admin/users/' + userId, 'PUT', {
            name: name,
            email: email,
            role: role,
            is_active: isActive
        }).then(function () {
            showSuccess('Пользователь успешно обновлён');
            if (currentUser) {
                currentUser.name = name;
                currentUser.email = email;
                currentUser.role = role;
                currentUser.role_name = roleNames[role] || role;
                currentUser.is_active = isActive;
                renderCard(currentUser);
            }
        }).catch(function (error) {
            console.error('Не удалось сохранить пользователя.', error);
            showError(error.message || 'Не удалось сохранить изменения');
        }).finally(function () {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-save me-1"></i> Сохранить изменения';
        });
    });

    // ---------- Смена пароля (чужого) ----------
    if (passwordForm) {
        passwordForm.addEventListener('submit', function (event) {
            event.preventDefault();
            errorEl.style.display = 'none';
            successEl.style.display = 'none';

            var password = newPasswordEl.value;
            var confirm = newConfirmEl.value;

            if (password.length < 8) {
                showError('Пароль должен содержать минимум 8 символов');
                return;
            }
            if (password !== confirm) {
                showError('Пароли не совпадают');
                return;
            }

            savePasswordBtn.disabled = true;
            savePasswordBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Смена…';

            window.AdminApi.post('/api/admin/users/' + userId + '/password', { password: password }).then(function () {
                showSuccess('Пароль пользователя обновлён');
                newPasswordEl.value = '';
                newConfirmEl.value = '';
            }).catch(function (error) {
                console.error('Не удалось сменить пароль.', error);
                showError(error.message || 'Не удалось сменить пароль');
            }).finally(function () {
                savePasswordBtn.disabled = false;
                savePasswordBtn.innerHTML = '<i class="bi bi-key me-1"></i> Сменить пароль';
            });
        });
    }
}());
