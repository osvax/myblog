<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted fw-bold mb-0">Рубрики блога и количество связанных статей</p>
    <button class="btn btn-orange fw-bold" type="button" id="addCategoryButton"><i class="bi bi-plus-lg"></i> Добавить рубрику</button>
</div>
<div id="categoriesError" class="alert alert-danger" role="alert" hidden></div>
<div class="card admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead><tr><th>Рубрика</th><th>Идентификатор (slug)</th><th>Статей</th><th>Описание</th><th class="text-end">Действия</th></tr></thead>
            <tbody id="categoriesTableBody"><tr><td colspan="5" class="text-center text-muted py-4">Загрузка рубрик…</td></tr></tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="categoryForm">
            <div class="modal-header">
                <h2 class="modal-title fs-5 fw-bold" id="categoryModalTitle">Новая рубрика</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id">
                <label class="form-label fw-bold" for="categoryName">Название</label>
                <input class="form-control mb-3" id="categoryName" name="name" required maxlength="120">
                <label class="form-label fw-bold" for="categorySlug">Идентификатор (латиница)</label>
                <input class="form-control mb-3" id="categorySlug" name="slug" required pattern="[a-z0-9-]{1,140}" maxlength="140">
                <label class="form-label fw-bold" for="categoryDescription">Описание</label>
                <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-orange fw-bold">Сохранить</button>
            </div>
        </form>
    </div>
</div>
