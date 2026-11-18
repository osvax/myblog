<div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4 fw-bold mb-0" id="articlePageTitle">Новая статья</h1>
            </div>
            <div class="card admin-card p-4">
                <form id="articleForm" novalidate>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold" for="articleTitle">Название статьи *</label>
                            <input id="articleTitle" name="title" type="text" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="articleSlug">Идентификатор (slug) *</label>
                            <input id="articleSlug" name="slug" type="text" class="form-control" required pattern="[a-z0-9-]+">
                            <div class="form-text fw-bold">Только латинские буквы в нижнем регистре, цифры и дефис</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold" for="articleDescription">Краткое описание *</label>
                            <input id="articleDescription" name="description" type="text" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="articleCategory">Рубрика *</label>
                            <select id="articleCategory" name="category" class="form-select" required>
                                <option value="">Выберите...</option>
                                <option>Вёрстка</option><option>Дизайн</option><option>JavaScript</option>
                                <option>Инструменты</option><option>Оптимизация</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold" for="articleCover">Обложка статьи</label>
                            <input id="articleCover" name="cover" type="file" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <div class="article-cover-preview mt-2" aria-label="Предпросмотр обложки">
                                <img id="articleCoverPreview" alt="Миниатюра обложки статьи" hidden>
                                <div id="articleCoverPlaceholder" class="article-cover-placeholder">
                                    <i class="bi bi-image" aria-hidden="true"></i>
                                    <span>Обложка не загружена</span>
                                </div>
                            </div>
                            <div class="form-text" id="currentCover">JPEG, PNG или WebP, до 10 МБ. Оптимальный размер обложки: 1200×675 px (16:9) — так обложка чётко смотрится в карточках блога.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold" for="articleContent">Редактирование статьи *</label>
                            <div class="article-editor border rounded">
                                <div class="article-editor-toolbar d-flex flex-wrap gap-1 p-2 border-bottom" role="toolbar" aria-label="Форматирование текста">
                                    <select class="form-select form-select-sm article-editor-block" id="articleBlockFormat" aria-label="Формат абзаца">
                                        <option value="P">Обычный текст</option>
                                        <option value="H2">Заголовок 2</option>
                                        <option value="H3">Заголовок 3</option>
                                        <option value="H4">Заголовок 4</option>
                                        <option value="BLOCKQUOTE">Цитата</option>
                                    </select>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-command="bold" aria-label="Полужирный" title="Полужирный"><i class="bi bi-type-bold"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-command="italic" aria-label="Курсив" title="Курсив"><i class="bi bi-type-italic"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-command="underline" aria-label="Подчёркнутый" title="Подчёркнутый"><i class="bi bi-type-underline"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-command="insertUnorderedList" aria-label="Маркированный список" title="Маркированный список"><i class="bi bi-list-ul"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-command="insertOrderedList" aria-label="Нумерованный список" title="Нумерованный список"><i class="bi bi-list-ol"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-command="createLink" aria-label="Вставить ссылку" title="Вставить ссылку"><i class="bi bi-link-45deg"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-command="unlink" aria-label="Удалить ссылку" title="Удалить ссылку"><i class="bi bi-link"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-command="removeFormat" aria-label="Очистить форматирование" title="Очистить форматирование"><i class="bi bi-eraser"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="articleImageInsert" data-editor-insert aria-label="Вставить изображение" title="Вставить изображение (загрузить и вставить)"><i class="bi bi-image"></i></button>
                                    <input type="file" id="articleInlineImage" accept="image/jpeg,image/png,image/webp" hidden>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="articleFilePickerButton" data-editor-insert aria-label="Изображение из файлового менеджера" title="Изображение из файлового менеджера — клик по картинке вставит её в статью"><i class="bi bi-folder2-open"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="articlePlaceholderButton" data-editor-insert aria-label="Placeholder-изображение" title="Placeholder-изображение (placehold.co) с выбором размера и цвета"><i class="bi bi-window-plus"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="articleVideoInsert" data-editor-insert aria-label="Вставить видео" title="Вставить видео по ссылке"><i class="bi bi-camera-video"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="articleAudioInsert" data-editor-insert aria-label="Вставить аудио" title="Вставить аудио по ссылке"><i class="bi bi-music-note-beamed"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="articleCodeInsert" data-editor-insert aria-label="Вставить код" title="Вставить блок кода"><i class="bi bi-filetype-js"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-dark ms-auto" id="articleSourceToggle" aria-pressed="false"><i class="bi bi-code-slash"></i> HTML</button>
                                </div>
                                <div id="articleContentEditor" class="article-editor-canvas" contenteditable="true" role="textbox" aria-multiline="true" aria-label="Текст статьи"></div>
                                <textarea id="articleContent" name="content" class="form-control article-editor-source" rows="12" aria-label="HTML-код статьи" hidden></textarea>
                            </div>
                            <div class="form-text">Используйте панель форматирования или включите режим HTML для редактирования кода.</div>
                            <div class="form-text">Оптимальный размер изображений в тексте статьи: 1200 px по ширине (JPG, PNG, WebP) — картинки не размоются на широких экранах.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="articleAuthor">Автор *</label>
                            <input id="articleAuthor" name="author" type="text" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="articleDate" id="articleDateLabel">Дата публикации *</label>
                            <input id="articleDate" name="date" type="date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" id="articleActive" name="active" checked>
                                <label class="form-check-label fw-bold" for="articleActive">Статья активна</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold" for="articleTags">Метки</label>
                            <input id="articleTags" name="tags" type="text" class="form-control" placeholder="#bootstrap #css #вёрстка">
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a class="btn btn-outline-dark fw-bold" href="blog.html">Отмена</a>
                        <button class="btn btn-orange fw-bold" type="submit"><i class="bi bi-save"></i> Сохранить статью</button>
                    </div>
                </form>
            </div>

<!-- Модальное окно вставки блока кода (без него article-edit.js падает при инициализации) -->
<div class="modal fade" id="articleCodeModal" tabindex="-1" aria-labelledby="articleCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="articleCodeForm">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="articleCodeModalLabel">Вставить код</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-bold" for="articleCodeLanguage">Язык</label>
                <select class="form-select mb-3" id="articleCodeLanguage">
                    <option value="html">HTML</option>
                    <option value="css">CSS</option>
                    <option value="javascript" selected>JavaScript</option>
                    <option value="php">PHP</option>
                    <option value="sql">SQL</option>
                    <option value="bash">Bash</option>
                </select>
                <label class="form-label fw-bold" for="articleCodeText">Код</label>
                <textarea class="form-control font-monospace" id="articleCodeText" rows="8" spellcheck="false"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-orange fw-bold">Вставить</button>
            </div>
        </form>
    </div>
</div>

<!-- Всплывающее окно файлового менеджера: клик по картинке вставляет её в статью -->
<div class="modal fade" id="articleFilePickerModal" tabindex="-1" aria-labelledby="articleFilePickerTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="articleFilePickerTitle">Файлы — вставить в статью</h5>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="filePickerRefresh" aria-label="Обновить список" title="Обновить список"><i class="bi bi-arrow-clockwise"></i></button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                    <button type="button" class="btn btn-orange fw-bold btn-sm" id="filePickerUploadButton"><i class="bi bi-cloud-arrow-up"></i> Загрузить и вставить</button>
                    <input type="file" id="filePickerInput" accept="image/jpeg,image/png" hidden>
                    <span class="form-text mb-0">JPG или PNG, до 10 МБ — после загрузки картинка сразу вставится в текст статьи</span>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <div class="btn-group flex-wrap" role="group" id="filePickerCategoryTabs">
                        <button type="button" class="btn btn-sm btn-outline-secondary active" data-picker-category="all">Все</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-picker-category="images"><i class="bi bi-image"></i> Изображения</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-picker-category="documents"><i class="bi bi-file-earmark-word"></i> Документы</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-picker-category="spreadsheets"><i class="bi bi-file-earmark-excel"></i> Таблицы</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-picker-category="xml"><i class="bi bi-filetype-xml"></i> XML</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-picker-category="archives"><i class="bi bi-file-earmark-zip"></i> Архивы</button>
                    </div>
                    <select class="form-select form-select-sm w-auto" id="filePickerSection" aria-label="Фильтр по разделам">
                        <option value="all">Все разделы</option>
                        <option value="site">Сайт</option>
                        <option value="blog">Блог</option>
                        <option value="portfolio">Портфолио</option>
                        <option value="profile">Профиль</option>
                        <option value="orders">Заказы</option>
                    </select>
                    <span class="form-text mb-0 ms-auto d-none d-lg-inline">Клик по картинке вставляет её в статью; вставить можно только изображения</span>
                </div>
                <div class="row g-2" id="filePickerGrid"></div>
            </div>
            <div class="modal-footer">
                <a href="files.html" class="btn btn-outline-dark btn-sm fw-bold"><i class="bi bi-folder2-open"></i> Открыть файловый менеджер</a>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно placeholder-изображения (placehold.co) с выбором размера, цвета и текста -->
<div class="modal fade" id="articlePlaceholderModal" tabindex="-1" aria-labelledby="articlePlaceholderTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="articlePlaceholderForm">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="articlePlaceholderTitle">Placeholder-изображение (placehold.co)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label fw-bold" for="placeholderWidth">Ширина, px</label>
                        <input type="number" class="form-control" id="placeholderWidth" min="1" max="4000" value="1200">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold" for="placeholderHeight">Высота, px</label>
                        <input type="number" class="form-control" id="placeholderHeight" min="1" max="4000" value="675">
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label fw-bold" for="placeholderBg">Цвет фона</label>
                        <input type="color" class="form-control form-control-color w-100" id="placeholderBg" value="#fff3e6">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold" for="placeholderFg">Цвет текста</label>
                        <input type="color" class="form-control form-control-color w-100" id="placeholderFg" value="#ff8c1a">
                    </div>
                </div>
                <label class="form-label fw-bold" for="placeholderText">Текст на изображении</label>
                <input type="text" class="form-control mb-3" id="placeholderText" value="Placeholder" maxlength="120">
                <label class="form-label fw-bold">Предпросмотр</label>
                <img id="placeholderPreview" class="img-fluid rounded mb-2" src="https://placehold.co/1200x675/fff3e6/ff8c1a?text=Placeholder" alt="Предпросмотр placeholder-изображения">
                <code class="d-block small text-break" id="placeholderUrl">https://placehold.co/1200x675/fff3e6/ff8c1a?text=Placeholder</code>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-orange fw-bold">Вставить</button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно обрезки изображения (правая кнопка по картинке в редакторе) -->
<div class="modal fade" id="articleCropModal" tabindex="-1" aria-labelledby="articleCropTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="articleCropTitle">Обрезка изображения</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Перетащите рамку по картинке, чтобы выделить область для обрезки. Углы рамки можно двигать, саму рамку — перетаскивать.</p>
                <div class="overflow-auto p-2 bg-light rounded" id="articleCropStageWrap">
                    <div id="articleCropStage" class="article-crop-stage mx-auto">
                        <img id="articleCropImage" class="article-crop-image" alt="Изображение для обрезки">
                        <div id="articleCropSelection" class="article-crop-selection" hidden>
                            <span class="article-crop-handle article-crop-handle-tl"></span>
                            <span class="article-crop-handle article-crop-handle-tr"></span>
                            <span class="article-crop-handle article-crop-handle-bl"></span>
                            <span class="article-crop-handle article-crop-handle-br"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-orange fw-bold" id="articleCropApply"><i class="bi bi-crop"></i> Обрезать</button>
            </div>
        </div>
    </div>
</div>

<!-- Контейнер всплывающих уведомлений редактора -->
<div class="toast-container position-fixed top-0 end-0 p-3" id="articleToastContainer"></div>
