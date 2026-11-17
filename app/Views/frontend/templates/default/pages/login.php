<div class="auth-card">
    <form id="loginForm" class="auth-form">
        <div class="form-group">
            <label for="loginEmail" class="form-label">Email</label>
            <input type="email" id="loginEmail" name="email" class="form-control" placeholder="your@email.com" required autocomplete="email">
        </div>
        <div class="form-group">
            <label for="loginPassword" class="form-label">Пароль</label>
            <input type="password" id="loginPassword" name="password" class="form-control" placeholder="Введите пароль" required minlength="8" autocomplete="current-password">
        </div>
        <div id="loginError" class="alert alert-danger" style="display:none;"></div>
        <button type="submit" class="btn btn-orange btn-block">Войти</button>
    </form>
    <div class="auth-links">
        <p>Нет аккаунта? <a href="/register.html">Зарегистрироваться</a></p>
        <p><a href="/forgot-password.html">Забыли пароль?</a></p>
    </div>
</div>
