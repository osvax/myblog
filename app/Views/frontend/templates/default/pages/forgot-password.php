<div class="auth-card">
    <p class="auth-description">Введите email, привязанный к вашему аккаунту. Мы отправим ссылку для сброса пароля.</p>
    <form id="forgotForm" class="auth-form">
        <div class="form-group">
            <label for="forgotEmail" class="form-label">Email</label>
            <input type="email" id="forgotEmail" name="email" class="form-control" placeholder="your@email.com" required autocomplete="email">
        </div>
        <div id="forgotSuccess" class="alert alert-success" style="display:none;"></div>
        <div id="forgotError" class="alert alert-danger" style="display:none;"></div>
        <button type="submit" class="btn btn-orange btn-block">Отправить ссылку</button>
    </form>
    <div class="auth-links">
        <p><a href="/login.html">Вернуться ко входу</a></p>
    </div>
</div>
