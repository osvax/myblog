(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var userId = 0;
        var currentProfile = null;
        var activityPage = 1;

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

        var activityBody = document.getElementById('activityBody');
        var activityPagination = document.getElementById('activityPagination');
        var paginationInfo = document.getElementById('paginationInfo');
        var prevPageBtn = document.getElementById('prevPageBtn');
        var nextPageBtn = document.getElementById('nextPageBtn');

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

        function formatDateTime(dateStr) {
            if (!dateStr) return '—';
            var d = new Date(dateStr);
            return d.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' }) +
                ' ' + d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
        }

        function timeAgo(dateStr) {
            if (!dateStr) return '—';
            var d = new Date(dateStr);
            var now = new Date();
            var diff = Math.floor((now - d) / 1000);
            if (diff < 60) return 'только что';
            if (diff < 3600) return Math.floor(diff / 60) + ' мин. назад';
            if (diff < 86400) return Math.floor(diff / 3600) + ' ч. назад';
            if (diff < 604800) return Math.floor(diff / 86400) + ' дн. назад';
            return formatDateTime(dateStr);
        }

        function roleLabel(role) {
            var labels = { user: 'Пользователь', editor: 'Редактор', manager: 'Менеджер', admin: 'Администратор' };
            return labels[role] || role;
        }

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
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

                // История
                activityPage = 1;
                loadActivity();
            }).catch(function (error) {
                showError('Не удалось загрузить профиль: ' + error.message);
            });
        }

        // ---------- История посещений ----------
        function loadActivity() {
            activityBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Загрузка…</td></tr>';

            AdminApi.get('/api/admin/profile/' + userId + '/activity?page=' + activityPage).then(function (data) {
                var activities = data.activities || [];
                var pagination = data.pagination || {};

                if (activities.length === 0) {
                    activityBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Нет записей о посещениях</td></tr>';
                    activityPagination.hidden = true;
                    return;
                }

                var html = '';
                activities.forEach(function (item) {
                    var userAgent = item.user_agent || '';
                    var browser = '';
                    if (userAgent.indexOf('Chrome') !== -1) browser = 'Chrome';
                    else if (userAgent.indexOf('Firefox') !== -1) browser = 'Firefox';
                    else if (userAgent.indexOf('Safari') !== -1) browser = 'Safari';
                    else if (userAgent.indexOf('Edge') !== -1) browser = 'Edge';
                    else if (userAgent.indexOf('MSIE') !== -1 || userAgent.indexOf('Trident') !== -1) browser = 'IE';
                    else browser = 'Другой';

                    html += '<tr>' +
                        '<td class="text-nowrap">' + formatDateTime(item.visited_at) + '</td>' +
                        '<td><a href="' + escapeHtml(item.page_url) + '" class="text-decoration-none" target="_blank">' + escapeHtml(item.page_title || item.page_url) + '</a></td>' +
                        '<td class="text-nowrap">' + escapeHtml(item.ip_address) + '</td>' +
                        '<td class="text-nowrap d-none d-xl-table-cell">' + browser + '</td>' +
                        '</tr>';
                });
                activityBody.innerHTML = html;

                if (pagination.pages > 1) {
                    activityPagination.hidden = false;
                    paginationInfo.textContent = pagination.page + ' из ' + pagination.pages;
                    prevPageBtn.disabled = pagination.page <= 1;
                    nextPageBtn.disabled = pagination.page >= pagination.pages;
                } else {
                    activityPagination.hidden = true;
                }
            }).catch(function (error) {
                activityBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Ошибка загрузки: ' + escapeHtml(error.message) + '</td></tr>';
                activityPagination.hidden = true;
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

        // ---------- Обработчики событий ----------
        uploadAvatarBtn.addEventListener('click', uploadAvatar);

        profileForm.addEventListener('submit', function (e) {
            e.preventDefault();
            saveProfile();
        });

        resetProfileBtn.addEventListener('click', resetForm);

        prevPageBtn.addEventListener('click', function () {
            if (activityPage > 1) {
                activityPage--;
                loadActivity();
            }
        });

        nextPageBtn.addEventListener('click', function () {
            activityPage++;
            loadActivity();
        });

        // ---------- Инициализация ----------
        loadProfile();
    });
}());
