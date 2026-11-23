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

<div class="card admin-card mt-4" id="rubricMenusEditor">
    <div class="card-header bg-white"><h2 class="h5 fw-bold mb-0">Меню рубрик</h2></div>
    <div class="card-body">
        <div class="row g-2 mb-3 align-items-end">
            <div class="col-md-5"><label class="form-label fw-bold" for="rubricMenuSelect">Меню рубрик</label><select class="form-select" id="rubricMenuSelect"></select></div>
            <div class="col-md-5"><label class="form-label fw-bold" for="rubricMenuName">Новое меню (идентификатор)</label><input class="form-control" id="rubricMenuName" maxlength="70" pattern="[a-z0-9_-]{1,70}"></div>
            <div class="col-md-2"><button class="btn btn-orange w-100 fw-bold" type="button" id="createRubricMenu"><i class="bi bi-plus-lg"></i> Создать</button></div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2"><strong>Пункты выбранного меню</strong><button class="btn btn-sm btn-outline-danger" type="button" id="deleteRubricMenu"><i class="bi bi-trash"></i> Удалить меню</button></div>
        <div id="rubricMenuItems" class="mb-4"></div>
        <form id="rubricMenuItemForm" class="row g-3 align-items-end">
            <input type="hidden" id="rubricMenuItemId">
            <div class="col-md-4"><label class="form-label fw-bold" for="rubricMenuSource">Источник</label><select class="form-select" id="rubricMenuSource"><option value="link">Свободная ссылка</option><option value="category">Рубрика блога</option><option value="page">Статическая страница</option><option value="article">Статья блога</option><option value="menu">Пункт меню сайта</option></select></div>
            <div class="col-md-5" id="rubricMenuPickerWrap" hidden><label class="form-label fw-bold" for="rubricMenuPicker">Выберите цель</label><select class="form-select" id="rubricMenuPicker"></select></div>
            <div class="col-md-5"><label class="form-label fw-bold" for="rubricMenuItemTitle">Название</label><input class="form-control" id="rubricMenuItemTitle" maxlength="160" required></div>
            <div class="col-md-7"><label class="form-label fw-bold" for="rubricMenuItemUrl">Ссылка</label><input class="form-control" id="rubricMenuItemUrl" maxlength="255" placeholder="/about.html или https://…" required></div>
            <div class="col-md-3"><label class="form-label fw-bold" for="rubricMenuItemIcon">Иконка</label><input class="form-control" id="rubricMenuItemIcon" maxlength="80" placeholder="bi-tag"></div>
            <div class="col-md-2"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="rubricMenuItemActive" checked><label class="form-check-label fw-bold">Активен</label></div></div>
            <div class="col-12"><button class="btn btn-orange fw-bold" type="submit" id="rubricMenuItemSubmit"><i class="bi bi-plus-lg"></i> Добавить рубрику</button><button class="btn btn-outline-secondary ms-2" type="reset" id="rubricMenuItemCancel" hidden>Отмена</button></div>
        </form>
        <p class="small text-muted mt-3 mb-0">В шаблонах: <code>&lt;?= render_menu_rubric('имя-меню-рубрик', 'nav flex-column') ?&gt;</code> или <code>&lt;?= menu_rubric_items_html('имя-меню-рубрик') ?&gt;</code>.</p>
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
