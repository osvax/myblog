<div class="d-flex justify-content-between align-items-center mb-4">
    <a class="btn btn-outline-secondary fw-bold" href="users.html"><i class="bi bi-arrow-left me-1"></i> К списку</a>
</div>

<div id="editError" class="alert alert-danger" style="display:none;"></div>
<div id="editSuccess" class="alert alert-success" style="display:none;"></div>

<div class="row g-4">
    <!-- Левая колонка — карточка профиля -->
    <div class="col-lg-4">
        <div class="card admin-card">
            <div class="card-body text-center p-4">
                <!-- Аватар с кнопкой замены -->
                <div class="position-relative d-inline-block" id="userAvatarWrap"
                     style="width: 144px; height: 144px; margin: 0 auto 1rem;">
                    <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                         id="userAvatarPlaceholder" style="width: 144px; height: 144px; border: 4px solid var(--orange-light); font-size: 4rem;">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <img id="userAvatarImage" class="d-none" alt="Аватар"
                         style="width: 144px; height: 144px; border-radius: 50%; border: 4px solid var(--orange-light); object-fit: cover; position: absolute; top: 0; left: 0;">
                    <label for="userAvatarFile" title="Заменить аватар"
                           class="btn btn-orange btn-action rounded-circle position-absolute d-none d-flex align-items-center justify-content-center"
                           id="userAvatarBtn" style="width: 34px; height: 34px; right: -4px; bottom: -4px; cursor: pointer; padding: 0;">
                        <i class="bi bi-camera"></i>
                    </label>
                    <input type="file" id="userAvatarFile" accept="image/jpeg,image/png,image/webp" class="d-none">
                </div>
                <h2 class="h5 fw-bold mb-1" id="userCardName">Загрузка…</h2>
                <p class="text-muted fw-bold mb-2 text-break" id="userCardEmail"></p>
                <div class="d-flex justify-content-center flex-wrap gap-2 mb-3">
                    <span class="badge fs-6" id="userCardRole"></span>
                    <span class="badge fs-6" id="userCardStatus"></span>
                </div>
                <hr>
                <div class="text-start small fw-bold text-muted">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="bi bi-calendar-event me-1"></i>Регистрация</span>
                        <span id="userCardCreated">—</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="bi bi-clock-history me-1"></i>Последний вход</span>
                        <span id="userCardLastVisit">—</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Правая колонка — формы -->
    <div class="col-lg-8">
        <div class="card admin-card mb-4">
            <div class="card-header bg-white">
                <h2 class="h5 fw-bold mb-0"><i class="bi bi-person-lines-fill me-2 text-orange"></i>Данные пользователя</h2>
            </div>
            <div class="card-body">
                <form id="userEditForm" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="userName">Имя</label>
                            <input class="form-control" id="userName" name="name" maxlength="160" required>
                            <div class="invalid-feedback">Укажите имя пользователя.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="userEmail">Email</label>
                            <input class="form-control" type="email" id="userEmail" name="email" maxlength="254" required>
                            <div class="invalid-feedback">Укажите корректный email.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="userRole">Роль</label>
                            <select class="form-select" id="userRole" name="role" required>
                                <option value="user">Пользователь</option>
                                <option value="editor">Редактор</option>
                                <option value="manager">Менеджер</option>
                                <option value="admin">Администратор</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="userStatus">Статус</label>
                            <select class="form-select" id="userStatus" name="status">
                                <option value="1">Активен</option>
                                <option value="0">Заблокирован</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button class="btn btn-orange fw-bold" type="submit" id="saveUserBtn"><i class="bi bi-save me-1"></i> Сохранить изменения</button>
                        <a class="btn btn-outline-secondary fw-bold" href="users.html">Отмена</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Смена пароля (не для собственного аккаунта) -->
        <div class="card admin-card" id="passwordCard">
            <div class="card-header bg-white">
                <h2 class="h5 fw-bold mb-0"><i class="bi bi-key me-2 text-orange"></i>Смена пароля</h2>
            </div>
            <div class="card-body">
                <form id="passwordForm" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="newPassword">Новый пароль</label>
                            <input class="form-control" type="password" id="newPassword" autocomplete="new-password" minlength="8" placeholder="Минимум 8 символов">
                            <div class="form-text">Минимум 8 символов.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="newPasswordConfirm">Повторите пароль</label>
                            <input class="form-control" type="password" id="newPasswordConfirm" autocomplete="new-password" minlength="8" placeholder="Ещё раз">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button class="btn btn-orange fw-bold" type="submit" id="savePasswordBtn"><i class="bi bi-key me-1"></i> Сменить пароль</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
