(function () {
    'use strict';

    var tableBody = document.getElementById('usersTableBody');
    if (!tableBody) return;

    var countEl = document.getElementById('usersCount');
    var searchEl = document.getElementById('usersSearch');
    var allUsers = [];
    var allRoles = []; // [{role_key, name}]
    var currentUserId = parseInt(window.ADMIN_USER_ID || 0, 10);

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

    function initials(name) {
        var parts = String(name || '').trim().split(/\s+/).filter(Boolean);
        if (!parts.length) return '?';
        return parts.map(function (part) { return part[0]; }).slice(0, 2).join('').toUpperCase();
    }

    // Аватар 36px: картинка или оранжевый круг с инициалами
    function avatarHtml(user) {
        if (user.avatar_path) {
            return '<img src="' + escapeHtml(user.avatar_path) + '" alt="Аватар" ' +
                'style="width:36px;height:36px;border-radius:50%;object-fit:cover;display:block;">';
        }
        return '<div style="width:36px;height:36px;border-radius:50%;background:#ff8c1a;color:#fff;' +
            'display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:700;">' +
            escapeHtml(initials(user.name)) + '</div>';
    }

    function filteredUsers() {
        var query = (searchEl && searchEl.value || '').trim().toLowerCase();
        if (!query) return allUsers;
        return allUsers.filter(function (user) {
            return (user.name || '').toLowerCase().indexOf(query) !== -1 ||
                (user.email || '').toLowerCase().indexOf(query) !== -1;
        });
    }

    function render() {
        tableBody.innerHTML = '';
        var users = filteredUsers();

        if (countEl) {
            countEl.textContent = allUsers.length;
        }
        if (!users.length) {
            var emptyText = allUsers.length ? 'Ничего не найдено' : 'Пользователей пока нет';
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">' + escapeHtml(emptyText) + '</td></tr>';
            return;
        }

        users.forEach(function (user) {
            var isSelf = user.id === currentUserId;
            var roleOptions = allRoles.map(function (role) {
                return '<option value="' + escapeHtml(role.role_key) + '"' +
                    (role.role_key === user.role ? ' selected' : '') + '>' + escapeHtml(role.name) + '</option>';
            }).join('');

            var row = document.createElement('tr');
            row.innerHTML =
                '<td>' + avatarHtml(user) + '</td>' +
                '<td class="fw-bold">' + escapeHtml(user.name) +
                    (isSelf ? ' <span class="badge bg-secondary" title="Это вы">вы</span>' : '') + '</td>' +
                '<td>' + escapeHtml(user.email) + '</td>' +
                '<td><select class="form-select form-select-sm user-role-select" data-user-id="' + user.id + '" style="max-width: 170px;">' +
                    roleOptions + '</select></td>' +
                '<td><div class="form-check form-switch mb-0 d-inline-block">' +
                    '<input class="form-check-input user-status-switch" type="checkbox" data-user-id="' + user.id + '"' +
                    (user.is_active ? ' checked' : '') + ' aria-label="Статус: ' + (user.is_active ? 'активен' : 'заблокирован') + '">' +
                    '</div></td>' +
                '<td class="text-nowrap">' + escapeHtml(formatDate(user.created_at)) + '</td>' +
                '<td class="text-end text-nowrap">' +
                    '<a class="btn btn-outline-dark btn-action me-1" href="user-edit.html?id=' + user.id + '" title="Карточка пользователя"><i class="bi bi-person-gear"></i></a> ' +
                    '<button type="button" class="btn btn-danger btn-action" data-user-delete="' + user.id + '" title="Удалить"><i class="bi bi-trash"></i></button>' +
                '</td>';
            tableBody.appendChild(row);
        });
    }

    function loadUsers() {
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Загрузка…</td></tr>';
        window.AdminApi.get('/api/admin/users').then(function (data) {
            allUsers = data.users || [];
            render();
        }).catch(function (error) {
            console.error('Не удалось загрузить пользователей.', error);
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">' + escapeHtml(error.message) + '</td></tr>';
        });
    }

    // Роли из таблицы ролей — для селектов в строках и в модалке
    function loadRoles() {
        return window.AdminApi.get('/api/admin/roles').then(function (data) {
            allRoles = data.roles || [];
            var roleSelect = document.getElementById('newUserRole');
            if (roleSelect && allRoles.length) {
                roleSelect.innerHTML = allRoles.map(function (role) {
                    return '<option value="' + escapeHtml(role.role_key) + '">' + escapeHtml(role.name) + '</option>';
                }).join('');
            }
        }).catch(function (error) {
            console.error('Не удалось загрузить роли.', error);
        });
    }

    // Обновить пользователя: имя/email/роль/статус берутся из текущих данных строки
    function saveUser(user, changes) {
        return window.AdminApi.save('/api/admin/users/' + user.id, 'PUT', {
            name: user.name,
            email: user.email,
            role: changes.role !== undefined ? changes.role : user.role,
            is_active: changes.is_active !== undefined ? changes.is_active : user.is_active
        }).then(function () {
            Object.keys(changes).forEach(function (key) { user[key] = changes[key]; });
        });
    }

    // ---------- События таблицы: инлайн-смена роли, статуса, удаление ----------
    tableBody.addEventListener('change', function (event) {
        var target = event.target;

        if (target.classList.contains('user-role-select')) {
            var userId = parseInt(target.dataset.userId, 10);
            var user = allUsers.filter(function (u) { return u.id === userId; })[0];
            if (!user) return;
            saveUser(user, { role: target.value }).then(function () {
                window.AdminNotifications.show('success', 'Роль обновлена');
            }).catch(function (error) {
                console.error('Не удалось обновить роль.', error);
                window.AdminNotifications.show('error', error.message);
                loadUsers(); // откатить значение селекта
            });
            return;
        }

        if (target.classList.contains('user-status-switch')) {
            var uid = parseInt(target.dataset.userId, 10);
            var targetUser = allUsers.filter(function (u) { return u.id === uid; })[0];
            if (!targetUser) return;
            var statusText = target.checked ? 'Активен' : 'Заблокирован';
            saveUser(targetUser, { is_active: target.checked }).then(function () {
                window.AdminNotifications.show(target.checked ? 'success' : 'warning', 'Пользователь: ' + statusText);
            }).catch(function (error) {
                console.error('Не удалось обновить статус.', error);
                window.AdminNotifications.show('error', error.message);
                loadUsers();
            });
            return;
        }
    });

    tableBody.addEventListener('click', function (event) {
        var button = event.target.closest('[data-user-delete]');
        if (!button) return;
        var deleteId = parseInt(button.dataset.userDelete, 10);
        if (isNaN(deleteId)) return;

        window.AdminApi.remove('/api/admin/users/' + deleteId).then(function () {
            allUsers = allUsers.filter(function (u) { return u.id !== deleteId; });
            render();
            window.AdminNotifications.show('success', 'Пользователь удалён');
        }).catch(function (error) {
            console.error('Не удалось удалить пользователя.', error);
            window.AdminNotifications.show('error', error.message);
        });
    });

    // ---------- Поиск ----------
    if (searchEl) {
        searchEl.addEventListener('input', render);
    }

    // ---------- Добавление пользователя (модалка) ----------
    var addUserBtn = document.getElementById('addUserBtn');
    var avatarFileEl = document.getElementById('newUserAvatarFile');
    var avatarPreviewEl = document.getElementById('newUserAvatarPreview');

    // Предпросмотр аватара в модалке
    if (avatarFileEl && avatarPreviewEl) {
        avatarFileEl.addEventListener('change', function () {
            var file = avatarFileEl.files && avatarFileEl.files[0];
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                window.AdminNotifications.show('error', 'Аватар слишком большой (макс. 2 МБ)');
                avatarFileEl.value = '';
                return;
            }
            avatarPreviewEl.innerHTML = '<img src="' + URL.createObjectURL(file) + '" alt="Превью аватара" ' +
                'style="width:100%;height:100%;border-radius:50%;object-fit:cover;">';
        });
    }

    if (addUserBtn) {
        addUserBtn.addEventListener('click', function () {
            var nameEl = document.getElementById('newUserName');
            var emailEl = document.getElementById('newUserEmail');
            var passwordEl = document.getElementById('newUserPassword');
            var roleEl = document.getElementById('newUserRole');
            var errorEl = document.getElementById('addUserError');

            errorEl.style.display = 'none';

            var name = nameEl.value.trim();
            var email = emailEl.value.trim();
            var password = passwordEl.value;
            var role = roleEl.value;

            if (name.length < 2) {
                errorEl.textContent = 'Имя должно содержать минимум 2 символа';
                errorEl.style.display = 'block';
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errorEl.textContent = 'Введите корректный email';
                errorEl.style.display = 'block';
                return;
            }
            if (password.length < 8) {
                errorEl.textContent = 'Пароль должен содержать минимум 8 символов';
                errorEl.style.display = 'block';
                return;
            }

            var avatarFile = avatarFileEl && avatarFileEl.files ? avatarFileEl.files[0] : null;
            if (avatarFile && avatarFile.size > 2 * 1024 * 1024) {
                errorEl.textContent = 'Аватар слишком большой (макс. 2 МБ)';
                errorEl.style.display = 'block';
                return;
            }

            addUserBtn.disabled = true;
            addUserBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Создание…';

            window.AdminApi.post('/api/admin/users', { name: name, email: email, password: password, role: role })
                .then(function (data) {
                    // Аватар (если выбран) загружается отдельным запросом после создания
                    if (avatarFile) {
                        var formData = new FormData();
                        formData.append('avatar', avatarFile);
                        return window.AdminApi.save('/api/admin/users/' + data.user_id + '/avatar', 'POST', formData);
                    }
                    return null;
                })
                .then(function () {
                    window.AdminNotifications.show('success', 'Пользователь создан');
                    var modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
                    if (modal) modal.hide();
                    nameEl.value = '';
                    emailEl.value = '';
                    passwordEl.value = '';
                    if (avatarFileEl) {
                        avatarFileEl.value = '';
                        avatarPreviewEl.innerHTML = '<i class="bi bi-person-circle"></i>';
                    }
                    loadUsers();
                })
                .catch(function (error) {
                    console.error('Не удалось создать пользователя.', error);
                    errorEl.textContent = error.message || 'Не удалось создать пользователя';
                    errorEl.style.display = 'block';
                })
                .finally(function () {
                    addUserBtn.disabled = false;
                    addUserBtn.innerHTML = 'Создать';
                });
        });
    }

    loadRoles().then(loadUsers);
}());
