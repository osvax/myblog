<div class="auth-page">
    <div class="auth-card p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="auth-icon mx-auto mb-3"><i class="bi bi-shield-lock"></i></div>
            <h1 class="h3 fw-bold mb-1">Вход в админ-панель</h1>
            <p class="fw-bold text-muted mb-0">Вход по учётным данным, заданным на сервере</p>
        </div>

        <div class="alert alert-danger fw-bold" id="loginError" role="alert" hidden></div>
        <form id="adminLoginForm">
            <div class="mb-3">
                <label class="form-label fw-bold" for="loginEmail">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" id="loginEmail" name="email" class="form-control" autocomplete="username" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold" for="loginPassword">Пароль</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="password" id="loginPassword" name="password" class="form-control" autocomplete="current-password" required>
                </div>
            </div>
          
            <button type="submit" id="adminLoginSubmit" class="btn btn-orange btn-lg fw-bold w-100"><i class="bi bi-box-arrow-in-right"></i> Войти</button>
        </form>

        <div class="text-center mt-4">
            <a href="../index.html" class="fw-bold text-muted"><i class="bi bi-arrow-left"></i> Вернуться на сайт</a>
        </div>
    </div>
</div>
