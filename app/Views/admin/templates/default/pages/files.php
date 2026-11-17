<div class="alert alert-info fw-bold">Файлы хранятся в закрытой папке <code>uploads/</code>; скачивание доступно только после входа. ZIP не распаковывается. Максимальный размер — 10 МБ.</div>
            <section class="card admin-card mb-4">
                <div class="card-header bg-white"><h2 class="h5 fw-bold mb-0">Загрузить файлы</h2></div>
                <div class="card-body">
                    <form id="fileUploadForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="fileCategory">Тип файла</label>
                                <select class="form-select" id="fileCategory" required>
                                    <option value="images">Изображения (JPG, JPEG, PNG)</option>
                                    <option value="documents">Документы (DOC, DOCX)</option>
                                    <option value="spreadsheets">Таблицы (XLS, XLSX)</option>
                                    <option value="xml">XML</option>
                                    <option value="archives">ZIP-архивы</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="fileSection">Раздел</label>
                                <select class="form-select" id="fileSection" required>
                                    <option value="site">Сайт</option>
                                    <option value="blog">Блог</option>
                                    <option value="portfolio">Портфолио</option>
                                    <option value="profile">Профиль</option>
                                    <option value="orders">Заказы</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="filesToUpload">Файлы</label>
                                <input class="visually-hidden" type="file" id="filesToUpload" multiple accept=".jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.xml,.zip">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-orange fw-bold w-100" type="button" id="uploadFilesButton"><i class="bi bi-folder2-open"></i> Выбрать файлы</button>
                            </div>
                        </div>
                        <div class="file-drop-zone mt-3" id="fileDropZone" role="button" tabindex="0" aria-label="Перетащите файлы сюда или нажмите, чтобы выбрать">
                            <i class="bi bi-cloud-arrow-up file-drop-zone-icon" aria-hidden="true"></i>
                            <span class="fw-bold">Перетащите файлы сюда</span>
                            <span class="text-muted">или нажмите, чтобы выбрать — до 10 МБ на файл</span>
                        </div>
                    </form>
                </div>
            </section>
            <section class="card admin-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h5 fw-bold mb-0">Загруженные файлы</h2>
                    <button class="btn btn-outline-secondary btn-sm fw-bold" type="button" id="refreshFiles"><i class="bi bi-arrow-clockwise"></i> Обновить</button>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead><tr><th>Предпросмотр</th><th>Файл</th><th>Тип</th><th>Раздел</th><th>Размер</th><th>Загружен</th><th class="text-end">Действия</th></tr></thead>
                        <tbody id="fileTableBody"><tr><td colspan="7" class="text-center text-muted py-4">Загрузка списка файлов...</td></tr></tbody>
                    </table>
                </div>
            </section>

<!-- Модальное окно предпросмотра изображения (без него files.js падает при инициализации) -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="imagePreviewTitle">Предпросмотр</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imagePreview" src="" alt="Предпросмотр изображения" class="img-fluid" style="max-height: 70vh;">
            </div>
        </div>
    </div>
</div>
