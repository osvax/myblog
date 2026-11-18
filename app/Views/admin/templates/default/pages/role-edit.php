<div class="d-flex justify-content-between align-items-center mb-4">
    <a class="btn btn-outline-light fw-bold" href="roles.html"><i class="bi bi-arrow-left me-1"></i> К списку ролей</a>
    <h1 class="h4 mb-0" id="pageTitle">Настройка роли</h1>
</div>

<div id="roleError" class="alert alert-danger" style="display:none;"></div>
<div id="roleSuccess" class="alert alert-success" style="display:none;"></div>

<div class="card admin-card">
    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label fw-bold" for="roleKey">Ключ</label>
                <input class="form-control" id="roleKey" disabled>
                <div class="form-text text-muted">Ключ изменить нельзя — он хранится у пользователей</div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold" for="roleName">Название</label>
                <input class="form-control" id="roleName" maxlength="120">
            </div>
            <div class="col-md-5">
                <label class="form-label fw-bold" for="roleDescription">Описание</label>
                <input class="form-control" id="roleDescription" maxlength="500">
            </div>
        </div>

        <h2 class="h5 fw-bold mb-3"><i class="bi bi-shield-check text-orange"></i> Доступные разделы</h2>
        <p class="small text-muted mb-3">Отметьте разделы, которые эта роль увидит в меню и сможет открывать (включая API-запросы раздела).</p>
        <div id="roleSections" class="row g-3"></div>

        <div class="mt-4 d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-orange fw-bold" id="saveRoleBtn"><i class="bi bi-save me-1"></i> Сохранить</button>
            <a class="btn btn-outline-secondary fw-bold" href="roles.html">Отмена</a>
            <button type="button" class="btn btn-outline-danger fw-bold ms-auto d-none" id="deleteRoleBtn"><i class="bi bi-trash me-1"></i> Удалить роль</button>
        </div>
    </div>
</div>
