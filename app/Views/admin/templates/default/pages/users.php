<div class="d-flex justify-content-end flex-wrap gap-2 mb-3">
    <button type="button" class="btn btn-orange fw-bold" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-person-plus"></i> Добавить пользователя
    </button>
</div>

<!-- Таблица пользователей -->
<div class="card admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead><tr><th>Аватар</th><th>Имя</th><th>Email</th><th>Роль</th><th>Статус</th><th>Дата регистрации</th><th class="text-end">Действия</th></tr></thead>
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
                <div class="mb-3">
                    <label class="form-label fw-bold">Имя</label>
                    <input type="text" id="newUserName" class="form-control" placeholder="Имя пользователя" required minlength="2" maxlength="160">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" id="newUserEmail" class="form-control" placeholder="email@example.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Пароль</label>
                    <input type="password" id="newUserPassword" class="form-control" placeholder="Минимум 8 символов" required minlength="8">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Роль</label>
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
