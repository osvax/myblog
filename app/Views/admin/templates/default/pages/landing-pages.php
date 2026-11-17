<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0"><i class="bi bi-window-desktop me-2"></i>Управление страницами</h1>
    <button class="btn btn-orange fw-bold" type="button" id="generateBtn" disabled>
        <i class="bi bi-magic me-1"></i> Сгенерировать из настроек
    </button>
</div>

<div id="pagesError" class="alert alert-danger" style="display:none;"></div>
<div id="pagesSuccess" class="alert alert-success" style="display:none;"></div>

<!-- Список страниц -->
<section class="card admin-card mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h5 fw-bold mb-0"><i class="bi bi-file-earmark-text me-2"></i>Страницы</h2>
        <button class="btn btn-outline-orange btn-sm fw-bold" type="button" id="addPageBtn">
            <i class="bi bi-plus-lg me-1"></i> Добавить страницу
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th style="width:150px;">Slug</th>
                        <th>Заголовок</th>
                        <th style="width:80px;" class="text-center">Активна</th>
                        <th style="width:150px;" class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody id="pagesTableBody">
                    <tr><td colspan="4" class="text-center text-muted py-4">Загрузка…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Редактор страницы -->
<div id="pageEditor" class="card admin-card" hidden>
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h5 fw-bold mb-0"><i class="bi bi-pencil-square me-2"></i><span id="pageEditorTitle">Редактировать страницу</span></h2>
        <button class="btn btn-outline-secondary btn-sm" type="button" id="closeEditor">
            <i class="bi bi-x"></i> Закрыть
        </button>
    </div>
    <div class="card-body p-4">
        <form id="pageForm" novalidate>
            <input type="hidden" id="pageEditId" value="">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold" for="pageSlug">Slug (ключ)</label>
                    <input class="form-control" id="pageSlug" name="slug" maxlength="100" placeholder="home" required>
                    <div class="invalid-feedback">Укажите slug</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" for="pageTitle">Заголовок</label>
                    <input class="form-control" id="pageTitle" name="title" maxlength="254" placeholder="Главная страница" required>
                    <div class="invalid-feedback">Укажите заголовок</div>
                </div>
                <div class="col-md-12">
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" id="pageActive" checked>
                        <label class="form-check-label fw-bold" for="pageActive">Активна</label>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-4">
                <button class="btn btn-orange fw-bold" type="submit">
                    <i class="bi bi-save me-1"></i> Сохранить
                </button>
                <button class="btn btn-outline-info fw-bold" type="button" id="openHtmlFromEditor">
                    <i class="bi bi-code-slash me-1"></i> Редактировать HTML
                </button>
                <button class="btn btn-outline-secondary fw-bold" type="button" id="cancelPageBtn">
                    Отмена
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Редактор HTML -->
<div id="htmlEditor" class="card admin-card mt-4" hidden>
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h5 fw-bold mb-0"><i class="bi bi-code-slash me-2"></i>HTML страницы</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-orange btn-sm fw-bold" type="button" id="saveHtmlBtn">
                <i class="bi bi-save me-1"></i> Сохранить HTML
            </button>
            <button class="btn btn-outline-secondary btn-sm" type="button" id="closeHtmlEditor">
                <i class="bi bi-x"></i> Закрыть
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <textarea id="pageHtmlContent" class="form-control font-monospace" rows="20" style="border: none; resize: none;"></textarea>
    </div>
</div>
