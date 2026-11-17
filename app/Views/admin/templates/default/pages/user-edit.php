<div class="d-flex justify-content-between align-items-center mb-4">
    <a class="btn btn-outline-light fw-bold" href="users.html"><i class="bi bi-arrow-left me-1"></i> К списку</a>
    <h1 class="h4 mb-0" id="pageTitle">Редактирование пользователя</h1>
</div>

<div id="editError" class="alert alert-danger" style="display:none;"></div>
<div id="editSuccess" class="alert alert-success" style="display:none;"></div>

<div class="row g-4">
    <!-- Левая колонка — профиль -->
    <div class="col-lg-4">
        <div class="card admin-card">
            <div class="card-body text-center p-4">
                <div class="avatar-placeholder mb-3" id="userAvatarPlaceholder">
                    <i class="bi bi-person-circle" style="font-size: 4rem;"></i>
                </div>
                <h2 class="h5 fw-bold mb-1" id="userCardName">Загрузка…</h2>
                <p class="text-muted fw-bold mb-2 text-break" id="userCardEmail"></p>
                <span class="badge bg-orange fs-6" id="userCardRole"></span>
            </div>
        </div>
    </div>

    <!-- Правая колонка — форма -->
    <div class="col-lg-8">
        <div class="card admin-card">
            <div class="card-header bg-white">
                <h2 class="h5 fw-bold mb-0">Данные пользователя</h2>
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
                            <input class="form-control" type="email" id="userEmail" name="email" maxlength="254" required disabled>
                            <div class="form-text text-muted">Email нельзя изменить</div>
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
                        <div class="col-12">
                            <label class="form-label fw-bold" for="userCreatedAt">Дата регистрации</label>
                            <input class="form-control" id="userCreatedAt" name="created_at" disabled>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-4">
    <button class="btn btn-orange fw-bold" type="button" id="saveUserBtn"><i class="bi bi-save me-1"></i> Сохранить изменения</button>
    <a class="btn btn-outline-secondary fw-bold" href="users.html">Отмена</a>
</div>
