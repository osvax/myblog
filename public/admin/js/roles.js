(function () {
    'use strict';

    var tableBody = document.getElementById('rolesTableBody');
    if (!tableBody) return;

    var roles = [];

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (char) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
        });
    }

    function showError(message) {
        var el = document.getElementById('rolesError');
        el.textContent = message;
        el.style.display = 'block';
    }

    function hideError() {
        document.getElementById('rolesError').style.display = 'none';
    }

    function render() {
        tableBody.innerHTML = '';
        if (!roles.length) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Ролей пока нет</td></tr>';
            return;
        }

        roles.forEach(function (role) {
            var systemBadge = role.is_system ? ' <span class="badge bg-secondary">стартовая</span>' : '';
            var row = document.createElement('tr');
            row.innerHTML =
                '<td class="fw-bold">' + escapeHtml(role.name) + systemBadge + '</td>' +
                '<td><code>' + escapeHtml(role.role_key) + '</code></td>' +
                '<td>' + escapeHtml(role.description || '—') + '</td>' +
                '<td>' + role.users_count + '</td>' +
                '<td class="text-end text-nowrap">' +
                    '<a class="btn btn-outline-dark btn-action me-1" href="role-edit.html?id=' + role.id + '" title="Права и название"><i class="bi bi-pencil"></i></a>' +
                    (role.is_system ? '' : '<button type="button" class="btn btn-danger btn-action" data-role-delete="' + role.id + '" title="Удалить"><i class="bi bi-trash"></i></button>') +
                '</td>';
            tableBody.appendChild(row);
        });
    }

    function loadRoles() {
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Загрузка…</td></tr>';
        window.AdminApi.get('/api/admin/roles').then(function (data) {
            roles = data.roles || [];
            render();
        }).catch(function (error) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">' + escapeHtml(error.message) + '</td></tr>';
        });
    }

    tableBody.addEventListener('click', function (event) {
        var button = event.target.closest('[data-role-delete]');
        if (!button) return;
        var roleId = parseInt(button.dataset.roleDelete, 10);
        if (isNaN(roleId)) return;

        window.AdminApi.remove('/api/admin/roles/' + roleId).then(function () {
            roles = roles.filter(function (r) { return r.id !== roleId; });
            render();
            window.AdminNotifications.show('success', 'Роль удалена');
        }).catch(function (error) {
            window.AdminNotifications.show('error', error.message);
        });
    });

    // ---------- Создание роли (инлайн-форма) ----------
    var addRoleBtn = document.getElementById('addRoleBtn');
    if (addRoleBtn) {
        addRoleBtn.addEventListener('click', function () {
            hideError();
            var keyEl = document.getElementById('newRoleKey');
            var nameEl = document.getElementById('newRoleName');
            var descEl = document.getElementById('newRoleDescription');

            var payload = {
                role_key: keyEl.value.trim().toLowerCase(),
                name: nameEl.value.trim(),
                description: descEl.value.trim()
            };
            if (payload.role_key.length < 2) {
                showError('Укажите ключ роли (минимум 2 символа, латиница)');
                return;
            }
            if (payload.name.length < 2) {
                showError('Укажите название роли');
                return;
            }

            addRoleBtn.disabled = true;
            window.AdminApi.post('/api/admin/roles', payload).then(function (data) {
                window.AdminNotifications.show('success', 'Роль создана — настройте её права');
                keyEl.value = '';
                nameEl.value = '';
                descEl.value = '';
                var collapse = bootstrap.Collapse.getInstance(document.getElementById('addRoleForm'));
                if (collapse) collapse.hide();
                window.location.href = 'role-edit.html?id=' + data.id;
            }).catch(function (error) {
                showError(error.message || 'Не удалось создать роль');
            }).finally(function () {
                addRoleBtn.disabled = false;
            });
        });
    }

    loadRoles();
}());
