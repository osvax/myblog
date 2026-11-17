(function () {
    'use strict';

    var tableBody = document.getElementById('usersTableBody');
    if (!tableBody) return;

    var pageSize = 10;
    var page = 1;
    var allUsers = [];

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (char) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
        });
    }

    function formatDate(value) {
        if (!value) return '—';
        var d = new Date(value);
        return Number.isNaN(d.getTime()) ? '—' : d.toLocaleDateString('ru-RU');
    }

    function avatarThumb(avatarPath) {
        if (avatarPath) {
            return '<img src="' + escapeHtml(avatarPath) + '" alt="Аватар" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">';
        }
        return '<div style="width:36px;height:36px;border-radius:50%;background:#f97316;color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.9rem;">?</div>';
    }

    function render() {
        tableBody.innerHTML = '';
        var pageCount = Math.max(1, Math.ceil(allUsers.length / pageSize));
        page = Math.min(page, pageCount);
        var start = (page - 1) * pageSize;
        var pageUsers = allUsers.slice(start, start + pageSize);

        if (!allUsers.length) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Пользователей пока нет</td></tr>';
            return;
        }

        pageUsers.forEach(function (user) {
            var statusClass = user.is_active ? 'bg-success' : 'bg-danger';
            var statusText = user.is_active ? 'Активен' : 'Заблокирован';
            var roleLabel = user.role === 'admin' ? 'Администратор' : (user.role === 'editor' ? 'Редактор' : (user.role === 'manager' ? 'Менеджер' : 'Пользователь'));
            var roleClass = user.role === 'admin' ? 'bg-warning text-dark' : (user.role === 'editor' ? 'bg-info text-white' : (user.role === 'manager' ? 'bg-success text-white' : 'bg-secondary'));
            var createdAt = formatDate(user.created_at);

            var row = document.createElement('tr');
            row.innerHTML =
                '<td>' + avatarThumb(user.avatar_path) + '</td>' +
                '<td>' + escapeHtml(user.name) + '</td>' +
                '<td>' + escapeHtml(user.email) + '</td>' +
                '<td><span class="badge ' + roleClass + '">' + escapeHtml(roleLabel) + '</span></td>' +
                '<td><span class="badge ' + statusClass + '">' + escapeHtml(statusText) + '</span></td>' +
                '<td>' + escapeHtml(createdAt) + '</td>' +
                '<td class="text-end text-nowrap">' +
                    '<a class="btn btn-outline-dark btn-action me-1" href="profile.html?id=' + user.id + '" title="Редактировать"><i class="bi bi-pencil"></i></a> ' +
                    '<button type="button" class="btn btn-danger btn-action" data-user-delete="' + user.id + '" title="Удалить"><i class="bi bi-trash"></i></button>' +
                '</td>';
            tableBody.appendChild(row);
        });
    }

    function loadUsers() {
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Загрузка…</td></tr>';
        window.AdminApi.get('/api/admin/users').then(function (data) {
            allUsers = data.users || [];
            page = 1;
            render();
        }).catch(function (error) {
            console.error('Не удалось загрузить пользователей.', error);
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">' + escapeHtml(error.message) + '</td></tr>';
        });
    }

    tableBody.addEventListener('click', function (event) {
        var button = event.target.closest('[data-user-delete]');
        if (!button) return;
        var userId = parseInt(button.dataset.userDelete, 10);
        if (isNaN(userId)) return;

        window.AdminApi.remove('/api/admin/users/' + userId).then(function () {
            allUsers = allUsers.filter(function (u) { return u.id !== userId; });
            render();
            window.AdminNotifications.show('success', 'Пользователь удалён');
        }).catch(function (error) {
            console.error('Не удалось удалить пользователя.', error);
            window.AdminNotifications.show('error', error.message);
        });
    });

    // ---------- Добавление пользователя ----------
    var addUserBtn = document.getElementById('addUserBtn');
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

            addUserBtn.disabled = true;
            addUserBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Создание…';

            window.AdminApi.post('/api/admin/users', { name: name, email: email, password: password, role: role })
                .then(function () {
                    window.AdminNotifications.show('success', 'Пользователь создан');
                    // Закрыть модалку и перезагрузить список
                    var modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
                    if (modal) modal.hide();
                    nameEl.value = '';
                    emailEl.value = '';
                    passwordEl.value = '';
                    loadUsers();
                })
                .catch(function (error) {
                    errorEl.textContent = error.message || 'Не удалось создать пользователя';
                    errorEl.style.display = 'block';
                })
                .finally(function () {
                    addUserBtn.disabled = false;
                    addUserBtn.innerHTML = 'Создать';
                });
        });
    }

    loadUsers();
}());
