<div class="alert alert-warning fw-bold">
    <i class="bi bi-exclamation-triangle"></i>
    Изменения сразу затрагивают публичные файлы. PHP-шаблоны проверяются на синтаксис, перед сохранением создаётся резервная копия. Статические страницы хранятся в базе и доступны по своим URL.
</div>
<section class="card admin-card template-editor-card">
    <div class="card-header bg-white">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold" for="templateName">Шаблон</label>
                <select class="form-select" id="templateName"></select>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-bold" for="templateFile">Файл (php, css, js и статические страницы)</label>
                <select class="form-select" id="templateFile"></select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="editorTheme">Тема редактора</label>
                <select class="form-select" id="editorTheme">
                    <option value="default">Светлая</option>
                    <option value="material-darker">Тёмная</option>
                </select>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2">
                <button class="btn btn-orange fw-bold" type="button" id="saveTemplate"><i class="bi bi-save"></i> Сохранить</button>
                <button class="btn btn-outline-secondary fw-bold" type="button" id="reloadTemplate">Перезагрузить</button>
                <button class="btn btn-outline-secondary" type="button" id="togglePreview" aria-label="Предпросмотр" title="Предпросмотр" aria-pressed="false"><i class="bi bi-eye"></i></button>
                <span class="ms-auto small fw-bold text-muted align-self-center" id="templateActiveBadge"></span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="templateError" class="alert alert-danger m-3" role="alert" hidden></div>
        <textarea id="templateEditor" aria-label="Исходный код выбранного файла шаблона"></textarea>
        <div class="p-3 border-top" id="previewContainer" hidden>
            <h2 class="h6 fw-bold">Предпросмотр (скрипты отключены)</h2>
            <iframe id="templatePreview" title="Предпросмотр шаблона" sandbox="allow-same-origin" class="template-preview"></iframe>
        </div>
    </div>
</section>
