<div class="d-flex justify-content-end flex-wrap gap-2 mb-3">
    <button type="button" class="btn btn-orange fw-bold" data-bs-toggle="collapse" data-bs-target="#addRoleForm" aria-expanded="false">
        <i class="bi bi-plus-circle"></i> Создать роль
    </button>
</div>

<!-- Инлайн-форма создания роли (без модального окна) -->
<div class="collapse mb-3" id="addRoleForm">
    <div class="card admin-card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold" for="newRoleKey">Ключ (латиницей)</label>
                    <input class="form-control" id="newRoleKey" maxlength="64" placeholder="content_editor">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" for="newRoleName">Название</label>
                    <input class="form-control" id="newRoleName" maxlength="120" placeholder="Контент-редактор">
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold" for="newRoleDescription">Описание</label>
                    <input class="form-control" id="newRoleDescription" maxlength="500" placeholder="Что может эта роль">
                </div>
            </div>
            <div class="alert alert-danger mt-3 mb-0" id="addRoleError" style="display:none;"></div>
            <div class="mt-3 d-flex gap-2">
                <button type="button" class="btn btn-orange fw-bold" id="addRoleBtn"><i class="bi bi-save"></i> Создать</button>
                <button type="button" class="btn btn-outline-secondary fw-bold" data-bs-toggle="collapse" data-bs-target="#addRoleForm">Отмена</button>
            </div>
        </div>
    </div>
</div>

<div id="rolesError" class="alert alert-danger" style="display:none;"></div>

<div class="card admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead><tr><th>Роль</th><th>Ключ</th><th>Описание</th><th>Пользователей</th><th class="text-end">Действия</th></tr></thead>
            <tbody id="rolesTableBody"></tbody>
        </table>
    </div>
</div>
