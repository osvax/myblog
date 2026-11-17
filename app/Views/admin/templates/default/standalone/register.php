<div class="auth-page">
    <div class="auth-card p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="auth-icon mx-auto mb-3"><i class="bi bi-person-plus"></i></div>
            <h1 class="h3 fw-bold mb-1">Регистрация</h1>
            <p class="fw-bold text-muted mb-0">Создание аккаунта администратора</p>
        </div>

        <form class="admin-form">
            <div class="mb-3">
                <label class="form-label fw-bold">Имя и фамилия *</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" placeholder="Иван Иванов" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Email *</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" placeholder="user@moyblog.ru" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Пароль * <small class="text-muted">(минимум 6 символов)</small></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="password" class="form-control" id="regPass" required minlength="6">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold">Подтверждение пароля *</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                    <input type="password" class="form-control" id="regPass2" required minlength="6">
                </div>
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="agree" required>
                <label class="form-check-label fw-bold" for="agree">Согласен на обработку персональных данных</label>
            </div>
            <button type="submit" class="btn btn-orange btn-lg fw-bold w-100"><i class="bi bi-person-plus"></i> Зарегистрироваться</button>
        </form>

        <div class="text-center mt-4">
            <p class="fw-bold mb-1">Уже есть аккаунт? <a href="login.html" class="text-orange link-orange">Войти</a></p>
            <a href="../index.html" class="fw-bold text-muted"><i class="bi bi-arrow-left"></i> Вернуться на сайт</a>
        </div>
    </div>
</div>
