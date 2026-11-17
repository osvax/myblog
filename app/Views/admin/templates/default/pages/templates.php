<div class="alert alert-warning fw-bold">Изменения сразу затрагивают публичные PHP-шаблоны. Доступны только перечисленные файлы представлений. Перед каждым сохранением создаётся резервная копия.</div>
            <section class="card admin-card template-editor-card">
                <div class="card-header bg-white">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-bold" for="templateFile">PHP-шаблон страницы</label>
                            <select class="form-select" id="templateFile"></select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="editorTheme">Тема редактора</label>
                            <select class="form-select" id="editorTheme">
                                <option value="default">Светлая</option>
                                <option value="dracula">Тёмная</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex flex-wrap gap-2">
                            <button class="btn btn-orange fw-bold" type="button" id="saveTemplate"><i class="bi bi-save"></i> Сохранить</button>
                            <button class="btn btn-outline-secondary fw-bold" type="button" id="reloadTemplate">Перезагрузить</button>
                            <button class="btn btn-outline-secondary" type="button" id="togglePreview" aria-label="Предпросмотр" title="Предпросмотр" aria-pressed="false"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="templateError" class="alert alert-danger m-3" role="alert" hidden></div>
                    <textarea id="templateEditor" aria-label="Исходный код выбранного PHP-шаблона"></textarea>
                    <div class="p-3 border-top" id="previewContainer" hidden>
                        <h2 class="h6 fw-bold">Предпросмотр (скрипты отключены)</h2>
                        <iframe id="templatePreview" title="Предпросмотр шаблона" sandbox="allow-same-origin" class="template-preview"></iframe>
                    </div>
                </div>
            </section>
