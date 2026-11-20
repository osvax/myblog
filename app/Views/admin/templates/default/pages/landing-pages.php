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

<!-- Редактор страницы: карточка + тело (заготовка) -->
<div id="pageEditor" class="card admin-card page-editor-card" hidden>
    <div class="card-header bg-white d-flex justify-content-between align-items-center gap-2 flex-wrap">
        <h2 class="h5 fw-bold mb-0"><i class="bi bi-pencil-square me-2"></i><span id="pageEditorTitle">Добавить страницу</span></h2>
        <div class="d-flex align-items-center gap-2">
            <label class="small fw-bold text-muted d-none d-md-inline" for="editorTheme">Тема редактора</label>
            <select class="form-select form-select-sm w-auto" id="editorTheme" aria-label="Тема редактора">
                <option value="default">Светлая</option>
                <option value="material-darker">Тёмная</option>
            </select>
            <button class="btn btn-outline-secondary btn-sm" type="button" id="closeEditor">
                <i class="bi bi-x"></i> Закрыть
            </button>
        </div>
    </div>
    <div class="card-body p-4">
        <form id="pageForm" novalidate>
            <input type="hidden" id="pageEditId" value="">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-bold" for="pageSlug">Slug (ключ)</label>
                    <input class="form-control" id="pageSlug" name="slug" maxlength="100" pattern="[a-z0-9-]{1,100}" placeholder="about" required>
                    <div class="invalid-feedback">Строчные латинские буквы, цифры и дефис</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" for="pageTitle">Заголовок</label>
                    <input class="form-control" id="pageTitle" name="title" maxlength="254" placeholder="О проекте" required>
                    <div class="invalid-feedback">Укажите заголовок</div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="pageActive" checked>
                        <label class="form-check-label fw-bold" for="pageActive">Активна</label>
                    </div>
                </div>
            </div>

            <label class="form-label fw-bold mt-4 d-flex justify-content-between" for="pageHtmlBody">
                <span>Тело страницы (HTML)</span>
                <button class="btn btn-link btn-sm p-0 align-items-center gap-1" type="button" id="fillStarterBtn" title="Вставить заготовку заново (текущее тело будет заменено)">
                    <i class="bi bi-file-earmark-plus"></i> Вставить заготовку
                </button>
            </label>
            <textarea id="pageHtmlBody" class="form-control font-monospace" rows="18"
                      placeholder="<!DOCTYPE html>
<html lang=&quot;ru&quot;>
…"></textarea>
            <p class="small text-muted mt-2 mb-0">
                Можно вставить любой HTML. Плейсхолдеры подставляются при открытии страницы на сайте:
                <code>{{field:ключ}}</code> — пользовательское поле (раздел «Пользовательские поля»),
                <code>{{menu:имя}}</code> — меню (раздел «Меню»),
                <code>{{asset:css/style.css}}</code> — общий CSS/JS (раздел «Шаблоны»).
                Страница откроется посетителям по адресу <code>/slug.html</code>, если она активна.
            </p>

            <div class="d-flex flex-wrap gap-2 mt-4">
                <button class="btn btn-orange fw-bold" type="submit">
                    <i class="bi bi-save me-1"></i> Сохранить
                </button>
                <button class="btn btn-outline-secondary fw-bold" type="button" id="cancelPageBtn">
                    Отмена
                </button>
            </div>
        </form>
    </div>
</div>

