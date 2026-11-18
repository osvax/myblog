<!-- Панель действий: поиск + добавление -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div class="position-relative" style="max-width: 300px; flex: 1 1 220px;">
        <i class="bi bi-search position-absolute text-muted" style="left: 0.75rem; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
        <input type="search" class="form-control form-control-sm ps-4" id="usersSearch"
               placeholder="Поиск по имени или email…" aria-label="Поиск по пользователям">
    </div>
    <button type="button" class="btn btn-orange fw-bold" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-person-plus me-1"></i> Добавить пользователя
    </button>
</div>

<!-- Таблица пользователей -->
<div class="card admin-card">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <span class="fw-bold">Все пользователи</span>
        <span class="badge bg-orange" id="usersCount">0</span>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
            <tr>
                <th style="width: 56px;"></th>
                <th>Имя</th>
                <th>Email</th>
                <th>Роль</th>
                <th>Статус</th>
                <th>Регистрация</th>
                <th class="text-end" style="width: 110px;">Действия</th>
            </tr>
            </thead>
            <tbody id="usersTableBody"></tbody>
        </table>
    </div>
</div>

<!-- Модальное окно добавления пользователя -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Добавить пользователя</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <!-- Аватар (необязательно) -->
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="position-relative" style="width: 64px; height: 64px; flex: 0 0 auto;">
                        <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                             id="newUserAvatarPreview" style="width: 64px; height: 64px; font-size: 1.6rem;">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <label for="newUserAvatarFile" title="Загрузить аватар"
                               class="btn btn-orange btn-action rounded-circle position-absolute d-flex align-items-center justify-content-center"
                               style="width: 26px; height: 26px; right: -6px; bottom: -6px; cursor: pointer; padding: 0;">
                            <i class="bi bi-camera" style="font-size: 0.7rem;"></i>
                        </label>
                        <input type="file" id="newUserAvatarFile" accept="image/jpeg,image/png,image/webp" class="d-none">
                    </div>
                    <div>
                        <div class="fw-bold">Аватар</div>
                        <div class="form-text">JPEG, PNG или WebP, до 2 МБ — необязательно</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold" for="newUserName">Имя</label>
                    <input type="text" id="newUserName" class="form-control" placeholder="Имя пользователя" required minlength="2" maxlength="160">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" for="newUserEmail">Email</label>
                    <input type="email" id="newUserEmail" class="form-control" placeholder="email@example.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" for="newUserPassword">Пароль</label>
                    <input type="password" id="newUserPassword" class="form-control" placeholder="Минимум 8 символов" required minlength="8" autocomplete="new-password">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" for="newUserRole">Роль</label>
                    <select id="newUserRole" class="form-select">
                        <option value="user">Пользователь</option>
                        <option value="editor">Редактор</option>
                        <option value="manager">Менеджер</option>
                        <option value="admin">Администратор</option>
                    </select>
                </div>
                <div id="addUserError" class="alert alert-danger" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-orange" id="addUserBtn">Создать</button>
            </div>
        </div>
    </div>
</div>
