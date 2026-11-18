(function () {
    'use strict';

    var form = document.getElementById('userEditForm');
    if (!form) return;

    var nameEl = document.getElementById('userName');
    var emailEl = document.getElementById('userEmail');
    var roleEl = document.getElementById('userRole');
    var statusEl = document.getElementById('userStatus');
    var createdAtEl = document.getElementById('userCreatedAt');
    var cardNameEl = document.getElementById('userCardName');
    var cardEmailEl = document.getElementById('userCardEmail');
    var cardRoleEl = document.getElementById('userCardRole');
    var pageTitleEl = document.getElementById('pageTitle');
    var saveBtn = document.getElementById('saveUserBtn');
    var errorEl = document.getElementById('editError');
    var successEl = document.getElementById('editSuccess');
    var csrfToken = '';
    var roleNames = {};
    var currentUserRole = window.ADMIN_USER_ROLE || '';
    var isAdmin = currentUserRole === 'admin';

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (char) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
        });
    }

    function showError(message) {
        errorEl.textContent = message;
        errorEl.style.display = 'block';
        successEl.style.display = 'none';
    }

    function showSuccess(message) {
        successEl.textContent = message;
        successEl.style.display = 'block';
        errorEl.style.display = 'none';
    }

    function hideMessages() {
        errorEl.style.display = 'none';
        successEl.style.display = 'none';
    }

    function renderCard(user) {
        cardNameEl.textContent = user.name || 'Пользователь';
        cardEmailEl.textContent = user.email || '';
        cardRoleEl.textContent = user.role_name || roleNames[user.role] || user.role;
        if (pageTitleEl) {
            pageTitleEl.textContent = 'Редактирование: ' + (user.name || 'пользователь');
        }
    }

    // Селект ролей — из таблицы ролей; менять роль может только администратор
    function loadRoles() {
        return window.AdminApi.get('/api/admin/roles').then(function (data) {
            var roles = data.roles || [];
            if (!roles.length) return;
            roleNames = {};
            roleEl.innerHTML = '';
            roles.forEach(function (role) {
                roleNames[role.role_key] = role.name;
                var option = document.createElement('option');
                option.value = role.role_key;
                option.textContent = role.name;
                roleEl.appendChild(option);
            });
        }).catch(function (error) {
            console.error('Не удалось загрузить роли.', error);
        });
    }

    // Получаем ID из URL
    var params = new URLSearchParams(window.location.search);
    var userId = params.get('id');
    if (!userId) {
        showError('Не указан ID пользователя');
        return;
    }

    // Сначала загружаем данные пользователя (GET не требует CSRF)
    loadRoles().then(function () {
        return window.AdminApi.get('/api/admin/users/' + userId).then(function (data) {
            var user = data.user;
            if (!user) {
                showError('Пользователь не найден');
                return;
            }

            // Заполняем форму
            nameEl.value = user.name || '';
            emailEl.value = user.email || '';
            roleEl.value = user.role || 'user';
            if (roleEl.selectedIndex === -1 && roleEl.options.length) {
                roleEl.selectedIndex = 0;
            }
            statusEl.value = user.is_active ? '1' : '0';
            createdAtEl.value = user.created_at || '—';

            renderCard(user);

            // Права на изменение пользователей — только у роли admin
            if (!isAdmin) {
                nameEl.disabled = true;
                roleEl.disabled = true;
                statusEl.disabled = true;
                saveBtn.classList.add('d-none');
                if (successEl) {
                    successEl.className = 'alert alert-info';
                    successEl.textContent = 'Просмотр карточки: изменение доступно роли «admin».';
                    successEl.style.display = 'block';
                }
            }

            // Теперь получаем CSRF токен (сессия уже установлена)
            return fetch('/api/auth/csrf', { credentials: 'same-origin' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        if (!response.ok || !data.success || !data.token) {
                            throw new Error(data.error || 'Не удалось получить токен');
                        }
                        csrfToken = data.token;
                    });
                });
        });
    }).catch(function (error) {
        console.error('Не удалось загрузить пользователя.', error);
        showError(error.message || 'Не удалось загрузить данные пользователя');
    });

    // Сохранение
    saveBtn.addEventListener('click', function () {
        hideMessages();

        var name = nameEl.value.trim();
        var role = roleEl.value;
        var isActive = statusEl.value === '1';

        if (name.length < 2 || name.length > 160) {
            showError('Имя должно содержать от 2 до 160 символов');
            return;
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Сохранение…';

        window.AdminApi.save('/api/admin/users/' + userId, 'PUT', {
            name: name,
            role: role,
            is_active: isActive
        }).then(function () {
            showSuccess('Пользователь успешно обновлён');
            // Обновляем карточку
            var updatedUser = {
                id: parseInt(userId, 10),
                name: name,
                email: emailEl.value,
                role: role,
                role_name: roleNames[role] || role,
                is_active: isActive
            };
            renderCard(updatedUser);
        }).catch(function (error) {
            showError(error.message || 'Не удалось сохранить изменения');
        }).finally(function () {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-save me-1"></i> Сохранить изменения';
        });
    });
}());
