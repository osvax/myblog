<div class="row g-4 mb-4 align-items-end">
    <div class="col-lg-4">
        <label class="form-label fw-bold" for="menuSelect">Меню</label>
        <div class="input-group">
            <select class="form-select" id="menuSelect"></select>
            <button class="btn btn-outline-danger" type="button" id="deleteMenuButton" title="Удалить меню"><i class="bi bi-trash"></i></button>
        </div>
    </div>
    <div class="col-lg-4">
        <label class="form-label fw-bold" for="newMenuName">Новое меню (идентификатор)</label>
        <div class="input-group">
            <input class="form-control" id="newMenuName" placeholder="sidebar-about" maxlength="80" pattern="[a-z0-9_-]{1,80}">
            <button class="btn btn-orange" type="button" id="createMenuButton"><i class="bi bi-plus-lg"></i> Создать</button>
        </div>
    </div>
    <div class="col-lg-4">
        <label class="form-label fw-bold" for="presetSelect">Заготовка</label>
        <div class="input-group">
            <select class="form-select" id="presetSelect">
                <option value="sections">Разделы сайта (Блог, Портфолио…)</option>
                <option value="pages">Все статические страницы</option>
                <option value="categories">Рубрики блога</option>
                <option value="social">Соцсети из настроек</option>
            </select>
            <input class="form-control" id="presetMenuName" placeholder="имя нового меню" maxlength="80" pattern="[a-z0-9_-]{1,80}">
            <button class="btn btn-orange" type="button" id="createPresetButton" title="Создать меню из заготовки"><i class="bi bi-magic"></i></button>
        </div>
    </div>
</div>

<div class="alert alert-danger" id="menuError" hidden></div>

<!-- Пункты меню -->
<div class="card admin-card mb-4">
    <div class="card-header bg-white"><h2 class="h5 fw-bold mb-0">Пункты меню <span class="text-muted small" id="menuItemsInfo"></span></h2></div>
    <div class="card-body p-2">
        <div id="menuItemsList"></div>
    </div>
</div>

<!-- Добавление пункта -->
<div class="card admin-card mb-4">
    <div class="card-header bg-white"><h2 class="h5 fw-bold mb-0">Добавить пункт</h2></div>
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold" for="itemSource">Источник</label>
                <select class="form-select" id="itemSource">
                    <option value="link">Свободная ссылка</option>
                    <option value="page">Статическая страница</option>
                    <option value="category">Рубрика блога</option>
                    <option value="article">Статья блога</option>
                </select>
            </div>
            <div class="col-md-4" id="itemPickerWrap" hidden>
                <label class="form-label fw-bold" for="itemPicker">Выберите цель</label>
                <select class="form-select" id="itemPicker"></select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold" for="itemTitle">Название</label>
                <input class="form-control" id="itemTitle" maxlength="160" placeholder="О проекте">
            </div>
            <div class="col-md-5">
                <label class="form-label fw-bold" for="itemUrl">Ссылка</label>
                <input class="form-control" id="itemUrl" maxlength="255" placeholder="/about.html или https://…">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="itemIcon">Иконка</label>
                <input class="form-control" id="itemIcon" maxlength="80" placeholder="bi-house">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold" for="itemClass">CSS-классы</label>
                <input class="form-control" id="itemClass" maxlength="200" placeholder="fw-bold link-orange">
            </div>
            <div class="col-md-5">
                <label class="form-label fw-bold" for="itemParent">Родительский пункт (подменю)</label>
                <select class="form-select" id="itemParent"><option value="">— нет (верхний уровень) —</option></select>
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" checked id="itemActive">
                    <label class="form-check-label fw-bold" for="itemActive">Активен</label>
                </div>
            </div>
            <div class="col-12">
                <button class="btn btn-orange fw-bold" type="button" id="addItemButton"><i class="bi bi-plus-lg"></i> Добавить пункт</button>
                <button class="btn btn-outline-dark fw-bold" type="button" id="updateItemButton" hidden><i class="bi bi-save"></i> Сохранить изменения</button>
                <button class="btn btn-outline-secondary fw-bold" type="button" id="cancelEditButton" hidden>Отмена</button>
            </div>
        </div>
    </div>
</div>

<p class="small text-muted mt-3 mb-0">
    Главное меню <code>main</code> выводится в шапке сайта. Другие меню вставляйте в шаблоны (раздел «Шаблоны»):
    <code>&lt;?= render_menu('имя-меню', 'nav flex-column') ?&gt;</code> — готовым списком,
    или <code>&lt;?= menu_items_html('имя-меню') ?&gt;</code> внутри своего <code>&lt;ul&gt;</code>.
    Пункт с подчинёнными пунктами становится выпадающим списком.
</p>
