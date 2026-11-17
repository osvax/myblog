<div class="auth-card">
    <form id="registerForm" class="auth-form">
        <div class="form-group">
            <label for="registerName" class="form-label">Имя</label>
            <input type="text" id="registerName" name="name" class="form-control" placeholder="Ваше имя" required minlength="2" maxlength="160" autocomplete="name">
        </div>
        <div class="form-group">
            <label for="registerEmail" class="form-label">Email</label>
            <input type="email" id="registerEmail" name="email" class="form-control" placeholder="your@email.com" required autocomplete="email">
        </div>
        <div class="form-group">
            <label for="registerPassword" class="form-label">Пароль</label>
            <input type="password" id="registerPassword" name="password" class="form-control" placeholder="Минимум 8 символов" required minlength="8" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label for="registerPasswordConfirm" class="form-label">Подтвердите пароль</label>
            <input type="password" id="registerPasswordConfirm" name="password_confirm" class="form-control" placeholder="Повторите пароль" required minlength="8" autocomplete="new-password">
        </div>
        <div id="registerError" class="alert alert-danger" style="display:none;"></div>
        <button type="submit" class="btn btn-orange btn-block">Зарегистрироваться</button>
    </form>
    <div class="auth-links">
        <p>Уже есть аккаунт? <a href="/login.html">Войти</a></p>
    </div>
</div>
