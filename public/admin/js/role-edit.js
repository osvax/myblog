(function () {
    'use strict';

    var sectionsEl = document.getElementById('roleSections');
    if (!sectionsEl) return;

    var keyEl = document.getElementById('roleKey');
    var nameEl = document.getElementById('roleName');
    var descEl = document.getElementById('roleDescription');
    var saveBtn = document.getElementById('saveRoleBtn');
    var deleteBtn = document.getElementById('deleteRoleBtn');
    var pageTitleEl = document.getElementById('pageTitle');
    var errorEl = document.getElementById('roleError');
    var successEl = document.getElementById('roleSuccess');

    var params = new URLSearchParams(window.location.search);
    var roleId = parseInt(params.get('id'), 10);
    var role = null;
    var sections = [];

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

    function renderSections(permissions) {
        sectionsEl.innerHTML = '';
        var has = {};
        permissions.forEach(function (slug) { has[slug] = true; });

        sections.forEach(function (entry) {
            var wrapper = document.createElement('div');
            wrapper.className = 'col-md-6 col-xl-4';

            if ((entry.type || 'link') === 'group') {
                var itemsHtml = (entry.items || []).map(function (item) {
                    return '<div class="form-check">' +
                        '<input class="form-check-input" type="checkbox" id="perm-' + escapeHtml(item.slug) + '" value="' + escapeHtml(item.slug) + '"' + (has[item.slug] ? ' checked' : '') + '>' +
                        '<label class="form-check-label fw-bold" for="perm-' + escapeHtml(item.slug) + '"><i class="bi ' + escapeHtml(item.icon) + '"></i> ' + escapeHtml(item.label) + '</label>' +
                        '</div>';
                }).join('');
                wrapper.innerHTML =
                    '<div class="card admin-card h-100"><div class="card-body">' +
                    '<h6 class="fw-bold mb-3"><i class="bi ' + escapeHtml(entry.icon) + ' text-orange"></i> ' + escapeHtml(entry.label) + '</h6>' +
                    itemsHtml + '</div></div>';
            } else {
                wrapper.innerHTML =
                    '<div class="card admin-card h-100"><div class="card-body d-flex align-items-center">' +
                    '<div class="form-check">' +
                    '<input class="form-check-input" type="checkbox" id="perm-' + escapeHtml(entry.slug) + '" value="' + escapeHtml(entry.slug) + '"' + (has[entry.slug] ? ' checked' : '') + '>' +
                    '<label class="form-check-label fw-bold" for="perm-' + escapeHtml(entry.slug) + '"><i class="bi ' + escapeHtml(entry.icon) + '"></i> ' + escapeHtml(entry.label) + '</label>' +
                    '</div></div></div>';
            }
            sectionsEl.appendChild(wrapper);
        });
    }

    function load() {
        if (!roleId) {
            showError('Не указан ID роли');
            return;
        }
        window.AdminApi.get('/api/admin/roles').then(function (data) {
            sections = data.sections || [];
            role = (data.roles || []).filter(function (r) { return r.id === roleId; })[0];
            if (!role) {
                showError('Роль не найдена');
                return;
            }
            keyEl.value = role.role_key;
            nameEl.value = role.name;
            descEl.value = role.description || '';
            pageTitleEl.textContent = 'Роль: ' + role.name;
            renderSections(role.permissions || []);
            if (!role.is_system) {
                deleteBtn.classList.remove('d-none');
            }
            if (role.role_key === 'admin') {
                // Права суперпользователя не ограничиваются — подсказка вместо чекбоксов
                sectionsEl.insertAdjacentHTML('afterbegin',
                    '<div class="col-12"><div class="alert alert-warning fw-bold mb-0">Роль «admin» всегда имеет полный доступ ко всем разделам. Отметки ниже на неё не влияют.</div></div>');
            }
        }).catch(function (error) {
            showError(error.message || 'Не удалось загрузить роль');
        });
    }

    saveBtn.addEventListener('click', function () {
        hideMessages();
        if (!role) return;
        var name = nameEl.value.trim();
        if (name.length < 2) {
            showError('Название роли должно быть не короче 2 символов');
            return;
        }
        var permissions = Array.prototype.map.call(
            sectionsEl.querySelectorAll('input[type="checkbox"]:checked'),
            function (input) { return input.value; }
        );

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Сохранение…';
        window.AdminApi.save('/api/admin/roles/' + roleId, 'PUT', {
            name: name,
            description: descEl.value.trim(),
            permissions: permissions
        }).then(function () {
            role.name = name;
            pageTitleEl.textContent = 'Роль: ' + name;
            showSuccess('Роль сохранена');
            window.AdminNotifications.show('success', 'Роль сохранена');
        }).catch(function (error) {
            showError(error.message || 'Не удалось сохранить роль');
        }).finally(function () {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-save me-1"></i> Сохранить';
        });
    });

    deleteBtn.addEventListener('click', function () {
        if (!role || !window.confirm('Удалить роль «' + role.name + '»?')) return;
        window.AdminApi.remove('/api/admin/roles/' + roleId).then(function () {
            window.location.href = 'roles.html';
        }).catch(function (error) {
            showError(error.message || 'Не удалось удалить роль');
        });
    });

    load();
}());
