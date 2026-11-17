<div class="d-flex justify-content-end flex-wrap gap-2 mb-3">
    <button type="button" class="btn btn-orange fw-bold" data-bs-toggle="modal" data-bs-target="#customFieldModal">
        <i class="bi bi-plus-lg"></i> Добавить поле
    </button>
</div>

<!-- Таблица пользовательских полей -->
<div class="card admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead><tr><th>Группа</th><th>Ключ</th><th>Название</th><th>Тип</th><th>Значение</th><th class="text-center">Активно</th><th class="text-end">Действия</th></tr></thead>
            <tbody id="customFieldsTableBody"></tbody>
        </table>
    </div>
</div>
<p class="small text-muted mt-3">
    <i class="bi bi-info-circle"></i> Значения доступны в шаблонах сайта через
    <code>custom_field('ключ')</code>. Ключ — строчные латинские буквы, цифры и <code>_</code>.
</p>

<!-- Модальное окно добавления/редактирования поля -->
<div class="modal fade" id="customFieldModal" tabindex="-1" aria-labelledby="customFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customFieldModalLabel">Добавить поле</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="customFieldId" value="0">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="customFieldKey">Ключ *</label>
                        <input type="text" id="customFieldKey" class="form-control" placeholder="banner_title" required maxlength="100" pattern="[a-z0-9_]{1,100}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="customFieldLabel">Название *</label>
                        <input type="text" id="customFieldLabel" class="form-control" placeholder="Заголовок баннера" required maxlength="200">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="customFieldType">Тип</label>
                        <select id="customFieldType" class="form-select">
                            <option value="text">Текст</option>
                            <option value="textarea">Длинный текст</option>
                            <option value="number">Число</option>
                            <option value="url">Ссылка</option>
                            <option value="image">Изображение</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="customFieldGroup">Группа</label>
                        <input type="text" id="customFieldGroup" class="form-control" placeholder="general" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="customFieldOrder">Порядок</label>
                        <input type="number" id="customFieldOrder" class="form-control" value="0">
                    </div>
                    <div class="col-12" id="customFieldValueWrap">
                        <label class="form-label fw-bold" for="customFieldValue">Значение</label>
                        <textarea id="customFieldValue" class="form-control" rows="3" maxlength="10000"></textarea>
                    </div>
                    <div class="col-12" id="customFieldImageWrap" hidden>
                        <label class="form-label fw-bold" for="customFieldImageFile">Файл изображения (JPEG, PNG, WebP, до 10 МБ)</label>
                        <input type="file" id="customFieldImageFile" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <div class="mt-3 text-center" id="customFieldImagePreview"></div>
                        <button type="button" class="btn btn-orange fw-bold mt-3" id="customFieldUploadBtn">
                            <i class="bi bi-cloud-arrow-up"></i> Загрузить изображение
                        </button>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" checked id="customFieldActive">
                            <label class="form-check-label fw-bold" for="customFieldActive">Активно</label>
                        </div>
                    </div>
                </div>
                <div id="customFieldError" class="alert alert-danger mt-3" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-orange" id="customFieldSaveBtn">Сохранить</button>
            </div>
        </div>
    </div>
</div>
