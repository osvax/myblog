<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            <?php if (!empty($user['avatar_path'])): ?>
                <img src="<?= htmlspecialchars($user['avatar_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Аватар">
            <?php else: ?>
                <div class="avatar-placeholder">
                    <i class="bi bi-person"></i>
                </div>
            <?php endif; ?>
        </div>
        <h1 class="profile-name"><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="profile-email"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php if ($user['role'] === 'admin'): ?>
            <span class="badge bg-warning text-dark">Администратор</span>
        <?php endif; ?>
    </div>

    <div class="profile-section">
        <h2 class="section-title">Редактировать профиль</h2>
        <form id="profileForm" class="profile-form">
            <div class="form-group">
                <label for="profileName" class="form-label">Имя</label>
                <input type="text" id="profileName" name="name" class="form-control" value="<?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>" required minlength="2" maxlength="160">
            </div>
            <div id="profileError" class="alert alert-danger" style="display:none;"></div>
            <div id="profileSuccess" class="alert alert-success" style="display:none;"></div>
            <button type="submit" class="btn btn-orange">Сохранить</button>
        </form>
    </div>

    <div class="profile-section">
        <h2 class="section-title">Сменить пароль</h2>
        <form id="passwordForm" class="profile-form">
            <div class="form-group">
                <label for="oldPassword" class="form-label">Текущий пароль</label>
                <input type="password" id="oldPassword" name="old_password" class="form-control" placeholder="Введите текущий пароль" required minlength="8">
            </div>
            <div class="form-group">
                <label for="newPassword" class="form-label">Новый пароль</label>
                <input type="password" id="newPassword" name="new_password" class="form-control" placeholder="Минимум 8 символов" required minlength="8">
            </div>
            <div class="form-group">
                <label for="newPasswordConfirm" class="form-label">Подтвердите новый пароль</label>
                <input type="password" id="newPasswordConfirm" name="new_password_confirm" class="form-control" placeholder="Повторите новый пароль" required minlength="8">
            </div>
            <div id="passwordError" class="alert alert-danger" style="display:none;"></div>
            <div id="passwordSuccess" class="alert alert-success" style="display:none;"></div>
            <button type="submit" class="btn btn-orange">Сменить пароль</button>
        </form>
    </div>

    <div class="profile-section profile-danger-zone">
        <h2 class="section-title text-danger">Опасная зона</h2>
        <p class="text-muted">Удаление аккаунта необратимо. Все ваши данные будут удалены.</p>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">Удалить аккаунт</button>
    </div>
</div>

<!-- Модальное окно удаления -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteAccountModalLabel">Удалить аккаунт</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <p>Введите ваш пароль для подтверждения удаления:</p>
                <input type="password" id="deletePassword" class="form-control" placeholder="Пароль" required minlength="8">
                <div id="deleteError" class="alert alert-danger mt-2" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Удалить аккаунт</button>
            </div>
        </div>
    </div>
</div>
