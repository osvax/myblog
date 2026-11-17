<div class="auth-card">
    <form id="resetForm" class="auth-form">
        <input type="hidden" id="resetToken" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-group">
            <label for="resetPassword" class="form-label">Новый пароль</label>
            <input type="password" id="resetPassword" name="password" class="form-control" placeholder="Минимум 8 символов" required minlength="8" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label for="resetPasswordConfirm" class="form-label">Подтвердите пароль</label>
            <input type="password" id="resetPasswordConfirm" name="password_confirm" class="form-control" placeholder="Повторите пароль" required minlength="8" autocomplete="new-password">
        </div>
        <div id="resetSuccess" class="alert alert-success" style="display:none;"></div>
        <div id="resetError" class="alert alert-danger" style="display:none;"></div>
        <button type="submit" class="btn btn-orange btn-block">Сменить пароль</button>
    </form>
    <div class="auth-links">
        <p><a href="/login.html">Вернуться ко входу</a></p>
    </div>
</div>
