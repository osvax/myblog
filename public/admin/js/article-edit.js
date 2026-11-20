(function () {
    'use strict';

    var form = document.getElementById('articleForm');
    if (!form) return;

    var fields = {
        title: document.getElementById('articleTitle'),
        slug: document.getElementById('articleSlug'),
        category: document.getElementById('articleCategory'),
        description: document.getElementById('articleDescription'),
        content: document.getElementById('articleContent'),
        author: document.getElementById('articleAuthor'),
        date: document.getElementById('articleDate'),
        active: document.getElementById('articleActive'),
        tags: document.getElementById('articleTags'),
        cover: document.getElementById('articleCover')
    };
    var params = new URLSearchParams(window.location.search);
    var articleId = params.get('id');
    var recordId = articleId || (window.crypto && crypto.randomUUID
        ? crypto.randomUUID().replace(/-/g, '')
        : String(Date.now()));
    var isEditing = Boolean(articleId);
    var initialSlug = '';
    var currentCover = '';
    var coverPreviewUrl = '';
    var coverPreview = document.getElementById('articleCoverPreview');
    var coverPlaceholder = document.getElementById('articleCoverPlaceholder');
    var contentEditor = document.getElementById('articleContentEditor');
    var sourceToggle = document.getElementById('articleSourceToggle');
    var imageInsertButton = document.getElementById('articleImageInsert');
    var inlineImageInput = document.getElementById('articleInlineImage');
    var videoInsertButton = document.getElementById('articleVideoInsert');
    var audioInsertButton = document.getElementById('articleAudioInsert');
    var codeInsertButton = document.getElementById('articleCodeInsert');
    var savedEditorRange = null;
    var savedSourceSelection = null;
    var sourceMode = false;
    var codeModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('articleCodeModal'));

    // Модалки файлового менеджера и placeholder-изображения (с guard-проверками:
    // без них getOrCreateInstance(null) убивает весь скрипт страницы)
    var filePickerButton = document.getElementById('articleFilePickerButton');
    var filePickerModalEl = document.getElementById('articleFilePickerModal');
    var filePickerModal = filePickerModalEl ? bootstrap.Modal.getOrCreateInstance(filePickerModalEl) : null;
    var filePickerGrid = document.getElementById('filePickerGrid');
    var filePickerInput = document.getElementById('filePickerInput');
    var filePickerUploadButton = document.getElementById('filePickerUploadButton');
    var filePickerRefresh = document.getElementById('filePickerRefresh');
    var placeholderButton = document.getElementById('articlePlaceholderButton');
    var placeholderModalEl = document.getElementById('articlePlaceholderModal');
    var placeholderModal = placeholderModalEl ? bootstrap.Modal.getOrCreateInstance(placeholderModalEl) : null;
    var placeholderForm = document.getElementById('articlePlaceholderForm');
    var placeholderWidth = document.getElementById('placeholderWidth');
    var placeholderHeight = document.getElementById('placeholderHeight');
    var placeholderBg = document.getElementById('placeholderBg');
    var placeholderFg = document.getElementById('placeholderFg');
    var placeholderText = document.getElementById('placeholderText');
    var placeholderPreview = document.getElementById('placeholderPreview');
    var placeholderUrl = document.getElementById('placeholderUrl');
    var csrfPromise = null;
    var transliteration = {
        а: 'a', б: 'b', в: 'v', г: 'g', д: 'd', е: 'e', ё: 'e', ж: 'zh', з: 'z', и: 'i', й: 'y',
        к: 'k', л: 'l', м: 'm', н: 'n', о: 'o', п: 'p', р: 'r', с: 's', т: 't', у: 'u', ф: 'f',
        х: 'h', ц: 'ts', ч: 'ch', ш: 'sh', щ: 'sch', ъ: '', ы: 'y', ь: '', э: 'e', ю: 'yu', я: 'ya'
    };

    function showToast(message, kind) {
        var container = document.getElementById('articleToastContainer');
        var toast = document.createElement('div');
        toast.className = 'toast align-items-center text-bg-' + (kind || 'danger') + ' border-0';
        toast.setAttribute('role', 'alert');
        toast.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Закрыть"></button></div>';
        toast.querySelector('.toast-body').textContent = message;
        container.appendChild(toast);
        toast.addEventListener('hidden.bs.toast', function () { toast.remove(); });
        bootstrap.Toast.getOrCreateInstance(toast, { delay: 4000 }).show();
    }

    function uploadCover(itemId, file) {
        return fetch('../api/auth/csrf', { credentials: 'same-origin' })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || !data.success || !data.token) {
                        throw new Error(data.error || 'Не удалось получить токен защищённого запроса.');
                    }
                    return data.token;
                });
            })
            .then(function (token) {
                var data = new FormData();
                data.append('scope', 'blog');
                data.append('id', itemId);
                data.append('files[]', file);
                return fetch('../api/admin/media', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'X-CSRF-Token': token },
                    body: data
                });
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || !data.success || !Array.isArray(data.files) || !data.files[0]) {
                        throw new Error(data.error || 'Не удалось загрузить обложку.');
                    }
                    return data.files[0];
                });
            });
    }

    function toSlug(value) {
        return value.toLowerCase().split('').map(function (char) {
            return Object.prototype.hasOwnProperty.call(transliteration, char) ? transliteration[char] : char;
        }).join('').normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }

    function getLocalDate() {
        var now = new Date();
        return now.getFullYear() + '-' +
            String(now.getMonth() + 1).padStart(2, '0') + '-' +
            String(now.getDate()).padStart(2, '0');
    }

    function showCoverPlaceholder() {
        coverPreview.removeAttribute('src');
        coverPreview.hidden = true;
        coverPlaceholder.hidden = false;
    }

    function showCover(path) {
        if (typeof path !== 'string' ||
            !/^uploads\/blog\/[A-Za-z0-9_-]+\/[a-f0-9]{32}\.(jpg|png|webp)$/.test(path)) {
            showCoverPlaceholder();
            return;
        }
        coverPreview.onload = function () {
            coverPreview.hidden = false;
            coverPlaceholder.hidden = true;
        };
        coverPreview.onerror = showCoverPlaceholder;
        coverPreview.src = '../' + path.split('/').map(encodeURIComponent).join('/');
    }

    function setArticleContent(html) {
        fields.content.value = html || '';
        contentEditor.innerHTML = fields.content.value;
    }

    function getArticleContent() {
        return sourceMode ? fields.content.value : contentEditor.innerHTML;
    }

    function hasArticleContent(html) {
        var text = document.createElement('div');
        text.innerHTML = html;
        return Boolean((text.textContent || '').replace(/\u00a0/g, ' ').trim()) ||
            Boolean(text.querySelector('img, video, audio, iframe'));
    }

    function setSourceMode(enabled) {
        if (enabled === sourceMode) return;
        if (enabled) {
            fields.content.value = contentEditor.innerHTML;
            contentEditor.hidden = true;
            fields.content.hidden = false;
        } else {
            contentEditor.innerHTML = fields.content.value;
            fields.content.hidden = true;
            contentEditor.hidden = false;
        }
        sourceMode = enabled;
        sourceToggle.setAttribute('aria-pressed', String(enabled));
        sourceToggle.innerHTML = enabled
            ? '<i class="bi bi-eye"></i> Визуально'
            : '<i class="bi bi-code-slash"></i> HTML';
        document.querySelectorAll('[data-editor-command], [data-editor-insert], #articleBlockFormat').forEach(function (control) {
            control.disabled = enabled;
        });
    }

    function saveActiveSelection() {
        if (sourceMode) {
            savedSourceSelection = {
                start: fields.content.selectionStart,
                end: fields.content.selectionEnd
            };
        } else {
            saveEditorSelection();
        }
    }

    function saveEditorSelection() {
        var selection = window.getSelection();
        if (selection && selection.rangeCount && contentEditor.contains(selection.anchorNode)) {
            savedEditorRange = selection.getRangeAt(0).cloneRange();
        } else {
            savedEditorRange = null;
        }
    }

    function insertVisualImage(url, alt) {
        contentEditor.focus();
        var selection = window.getSelection();
        var range = savedEditorRange;
        if (!range || !contentEditor.contains(range.startContainer)) {
            range = document.createRange();
            range.selectNodeContents(contentEditor);
            range.collapse(false);
        }
        selection.removeAllRanges();
        selection.addRange(range);
        var image = document.createElement('img');
        image.src = url;
        image.alt = alt;
        image.className = 'img-fluid';
        range.deleteContents();
        range.insertNode(image);
        range.setStartAfter(image);
        range.collapse(true);
        selection.removeAllRanges();
        selection.addRange(range);
        savedEditorRange = range.cloneRange();
    }

    function insertSourceImage(url, alt) {
        var textarea = fields.content;
        var selection = savedSourceSelection || {
            start: textarea.value.length,
            end: textarea.value.length
        };
        var escapedUrl = url.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
        var escapedAlt = alt.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
        var markup = '<img src="' + escapedUrl + '" alt="' + escapedAlt + '" class="img-fluid">';
        textarea.value = textarea.value.slice(0, selection.start) + markup + textarea.value.slice(selection.end);
        var cursor = selection.start + markup.length;
        textarea.focus();
        textarea.setSelectionRange(cursor, cursor);
    }

    function insertHtml(markup) {
        if (sourceMode) {
            var selection = savedSourceSelection || {
                start: fields.content.value.length,
                end: fields.content.value.length
            };
            fields.content.value = fields.content.value.slice(0, selection.start) +
                markup + fields.content.value.slice(selection.end);
            var cursor = selection.start + markup.length;
            fields.content.focus();
            fields.content.setSelectionRange(cursor, cursor);
            return;
        }

        contentEditor.focus();
        var selection = window.getSelection();
        var range = savedEditorRange;
        if (!range || !contentEditor.contains(range.startContainer)) {
            range = document.createRange();
            range.selectNodeContents(contentEditor);
            range.collapse(false);
        }
        selection.removeAllRanges();
        selection.addRange(range);
        range.deleteContents();
        var fragment = range.createContextualFragment(markup);
        var lastNode = fragment.lastChild;
        range.insertNode(fragment);
        if (lastNode) {
            range.setStartAfter(lastNode);
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
            savedEditorRange = range.cloneRange();
        }
    }

    function escapeHtml(value) {
        return value.replace(/[&<>"']/g, function (char) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
        });
    }

    function normalizeMediaUrl(value) {
        var url;
        try {
            url = new URL(value.trim());
        } catch (error) {
            throw new Error('Введите корректную ссылку, начинающуюся с http:// или https://.');
        }
        if (url.protocol !== 'https:' && url.protocol !== 'http:') {
            throw new Error('Разрешены только ссылки http:// и https://.');
        }
        return url;
    }

    function videoMarkup(value) {
        var url = normalizeMediaUrl(value);
        var host = url.hostname.toLowerCase().replace(/^www\./, '');
        var videoId = '';
        if (host === 'youtu.be') {
            videoId = url.pathname.split('/').filter(Boolean)[0] || '';
        } else if (host === 'youtube.com' || host === 'm.youtube.com') {
            if (url.pathname === '/watch') videoId = url.searchParams.get('v') || '';
            else videoId = url.pathname.match(/^\/(?:embed|shorts)\/([A-Za-z0-9_-]+)/)?.[1] || '';
        }
        if (videoId && /^[A-Za-z0-9_-]{6,20}$/.test(videoId)) {
            return '<p><iframe src="https://www.youtube-nocookie.com/embed/' + videoId +
                '" title="Видео YouTube" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" ' +
                'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></p>';
        }
        if (host === 'vimeo.com' || host === 'player.vimeo.com') {
            var vimeoId = url.pathname.match(/\/(?:video\/)?([0-9]+)/)?.[1];
            if (vimeoId) {
                return '<p><iframe src="https://player.vimeo.com/video/' + vimeoId +
                    '" title="Видео Vimeo" loading="lazy" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></p>';
            }
        }
        return '<p><video controls preload="metadata" style="max-width:100%"><source src="' +
            escapeHtml(url.href) + '">Ваш браузер не поддерживает воспроизведение видео.</video></p>';
    }

    function audioMarkup(value) {
        var url = normalizeMediaUrl(value);
        return '<p><audio controls preload="metadata"><source src="' + escapeHtml(url.href) +
            '">Ваш браузер не поддерживает воспроизведение аудио.</audio></p>';
    }

    function fillForm(article) {
        Object.keys(fields).forEach(function (key) {
            if (key === 'active') {
                fields.active.checked = article.active !== false;
            } else if (key === 'cover') {
                currentCover = article.cover || '';
                document.getElementById('currentCover').textContent =
                    'JPEG, PNG или WebP, до 10 МБ. Оптимальный размер обложки: 1200×675 px (16:9) — так обложка чётко смотрится в карточках блога.';
                showCover(currentCover);
            } else if (key === 'content') {
                setArticleContent(article.content || '');
            } else if (key !== 'slug' || article.slug) {
                fields[key].value = article[key] || '';
            }
        });
        initialSlug = fields.slug.value;
    }

    try {
        if (isEditing) {
            window.AdminApi.get('/api/admin/articles/' + encodeURIComponent(articleId)).then(function (result) {
                var article = result.article;
                recordId = article.id;
                document.getElementById('articlePageTitle').textContent = 'Редактирование статьи';
                document.getElementById('articleDateLabel').textContent = 'Дата публикации *';
                fillForm(article);
            }).catch(function (error) {
                console.error('Не удалось загрузить статью.', error);
                showToast(error.message || 'Не удалось загрузить статью.');
                window.setTimeout(function () { window.location.replace('blog.html'); }, 1200);
            });
        } else {
            fields.date.value = getLocalDate();
            fields.author.value = 'Администратор';
            initialSlug = '';
            showCoverPlaceholder();
        }
    } catch (error) {
        console.error('Не удалось загрузить статью.', error);
        showToast(error.message || 'Не удалось загрузить статью.');
        return;
    }

    fields.title.addEventListener('input', function () {
        if (!isEditing || fields.slug.value === initialSlug || !fields.slug.value) {
            fields.slug.value = toSlug(fields.title.value);
        }
    });
    fields.slug.addEventListener('input', function () {
        fields.slug.value = fields.slug.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
    });
    imageInsertButton.addEventListener('mousedown', function (event) {
        event.preventDefault();
    });
    imageInsertButton.addEventListener('click', function () {
        saveActiveSelection();
        inlineImageInput.click();
    });
    inlineImageInput.addEventListener('change', function () {
        var file = inlineImageInput.files[0];
        inlineImageInput.value = '';
        if (!file) return;
        imageInsertButton.disabled = true;
        uploadCover(recordId, file).then(function (path) {
            var imageUrl = '/' + path.replace(/^\/+/, '').split('/').map(encodeURIComponent).join('/');
            var alt = file.name.replace(/\.[^.]+$/, '');
            if (sourceMode) insertSourceImage(imageUrl, alt);
            else insertVisualImage(imageUrl, alt);
            showToast('Изображение загружено и вставлено в текст.', 'success');
        }).catch(function (error) {
            console.error('Не удалось загрузить изображение в текст статьи.', error);
            showToast(error.message || 'Не удалось загрузить изображение статьи.');
        }).finally(function () {
            imageInsertButton.disabled = false;
        });
    });
    document.querySelectorAll('[data-editor-command]').forEach(function (button) {
        button.addEventListener('mousedown', function (event) {
            event.preventDefault();
        });
        button.addEventListener('click', function () {
            if (sourceMode) return;
            var command = button.dataset.editorCommand;
            if (command === 'createLink') {
                window.AdminConfirm.ask('Введите адрес ссылки (http, https или mailto):', {
                    title: 'Вставка ссылки',
                    confirmLabel: 'Вставить',
                    danger: false,
                    input: { label: 'Адрес ссылки', placeholder: 'https://example.com' }
                }).then(function (result) {
                    if (!result.ok) return;
                    var url = result.value.trim();
                    if (!url) return;
                    if (!/^(https?:\/\/|mailto:)/i.test(url)) {
                        showToast('Ссылка должна начинаться с http://, https:// или mailto:');
                        return;
                    }
                    document.execCommand(command, false, url);
                });
                return;
            }
            document.execCommand(command, false, null);
            contentEditor.focus();
        });
    });
    document.getElementById('articleBlockFormat').addEventListener('change', function () {
        if (!sourceMode) {
            contentEditor.focus();
            document.execCommand('formatBlock', false, this.value);
        }
    });
    videoInsertButton.addEventListener('mousedown', function (event) { event.preventDefault(); });
    videoInsertButton.addEventListener('click', function () {
        saveActiveSelection();
        window.AdminConfirm.ask('Введите ссылку на видео или ролик YouTube / Vimeo:', {
            title: 'Вставка видео',
            confirmLabel: 'Вставить',
            danger: false,
            input: { label: 'Ссылка на видео', placeholder: 'https://www.youtube.com/watch?v=…' }
        }).then(function (result) {
            if (!result.ok) return;
            var url = result.value.trim();
            if (!url) return;
            try {
                insertHtml(videoMarkup(url));
            } catch (error) {
                showToast(error.message);
            }
        });
    });
    audioInsertButton.addEventListener('mousedown', function (event) { event.preventDefault(); });
    audioInsertButton.addEventListener('click', function () {
        saveActiveSelection();
        window.AdminConfirm.ask('Введите прямую ссылку на аудиофайл (MP3, OGG, WAV и другие форматы браузера):', {
            title: 'Вставка аудио',
            confirmLabel: 'Вставить',
            danger: false,
            input: { label: 'Ссылка на аудиофайл', placeholder: 'https://example.com/music.mp3' }
        }).then(function (result) {
            if (!result.ok) return;
            var url = result.value.trim();
            if (!url) return;
            try {
                insertHtml(audioMarkup(url));
            } catch (error) {
                showToast(error.message);
            }
        });
    });
    codeInsertButton.addEventListener('mousedown', function (event) { event.preventDefault(); });
    codeInsertButton.addEventListener('click', function () {
        saveActiveSelection();
        document.getElementById('articleCodeText').value = '';
        codeModal.show();
        window.setTimeout(function () { document.getElementById('articleCodeText').focus(); }, 150);
    });
    document.getElementById('articleCodeForm').addEventListener('submit', function (event) {
        event.preventDefault();
        var code = document.getElementById('articleCodeText').value;
        if (!code.trim()) {
            showToast('Введите код программы.');
            return;
        }
        var language = document.getElementById('articleCodeLanguage').value;
        insertHtml('<pre><code class="language-' + language + '">' + escapeHtml(code) + '</code></pre>');
        codeModal.hide();
    });

    // ---------- Файловый менеджер: выбор и загрузка изображений ----------
    function getCsrfToken() {
        if (!csrfPromise) {
            csrfPromise = fetch('../api/auth/csrf', { credentials: 'same-origin' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        if (!response.ok || !data.success || !data.token) {
                            throw new Error(data.error || 'Не удалось получить токен защищённого запроса.');
                        }
                        csrfPromise = null;
                        return data.token;
                    });
                })
                .catch(function (error) {
                    csrfPromise = null;
                    throw error;
                });
        }
        return csrfPromise;
    }

    function publicFileUrl(relativePath) {
        // Путь файлового менеджера «images/blog/xxx.jpg» → адрес на сайте «/uploads/images/blog/xxx.jpg»
        return '/uploads/' + String(relativePath).split('/').map(encodeURIComponent).join('/');
    }

    function insertFileManagerImage(path, name) {
        if (!path) {
            // Защита от повторения бага «/uploads/undefined»: путь не получен — не вставляем
            showToast('Не удалось получить путь к файлу — повторите выбор.');
            return false;
        }
        var imageUrl = publicFileUrl(path);
        var alt = String(name || '').replace(/\.[^.]+$/, '');
        if (sourceMode) {
            insertSourceImage(imageUrl, alt);
        } else {
            insertVisualImage(imageUrl, alt);
        }
        return true;
    }

    // Весь раздел «Файлы» во всплывающем окне: вкладки типов + фильтр по разделам.
    // Клик по картинке вставляет её в статью; остальные файлы — только просмотр.
    var pickerFiles = [];
    var pickerFilter = { category: 'all', section: 'all' };
    var pickerSectionLabels = {
        site: 'Сайт', blog: 'Блог', portfolio: 'Портфолио', profile: 'Профиль', orders: 'Заказы'
    };
    var pickerCategoryIcons = {
        images: 'bi-image',
        documents: 'bi-file-earmark-word',
        spreadsheets: 'bi-file-earmark-excel',
        xml: 'bi-filetype-xml',
        archives: 'bi-file-earmark-zip'
    };
    var filePickerSection = document.getElementById('filePickerSection');
    var pickerCategoryTabs = document.getElementById('filePickerCategoryTabs');

    function renderFilePicker() {
        var files = (pickerFiles || []).filter(function (file) {
            return (pickerFilter.category === 'all' || file.category === pickerFilter.category) &&
                (pickerFilter.section === 'all' || file.section === pickerFilter.section);
        });
        if (!files.length) {
            filePickerGrid.innerHTML = '<div class="col-12 text-center text-muted py-3">Файлов пока нет — загрузите первый кнопкой выше</div>';
            return;
        }
        filePickerGrid.innerHTML = files.map(function (file) {
            if (file.category === 'images') {
                var preview = '../api/admin/files?action=preview&path=' + encodeURIComponent(file.path);
                return '<div class="col-6 col-md-4 col-lg-3">' +
                    '<button type="button" class="btn w-100 overflow-hidden rounded border p-0" ' +
                    'data-picker-path="' + escapeHtml(file.path) + '" data-picker-name="' + escapeHtml(file.name) + '" ' +
                    'title="Вставить в статью: ' + escapeHtml(file.name) + '">' +
                    '<img src="' + preview + '" alt="' + escapeHtml(file.name) + '" loading="lazy" ' +
                    'class="w-100 d-block" style="aspect-ratio:4/3;object-fit:cover;">' +
                    '</button></div>';
            }
            var icon = pickerCategoryIcons[file.category] || 'bi-file-earmark';
            return '<div class="col-6 col-md-4 col-lg-3">' +
                '<div class="picker-file-card" title="В статью можно вставить только изображения">' +
                '<i class="bi ' + icon + ' picker-file-card-icon"></i>' +
                '<span class="picker-file-card-name" title="' + escapeHtml(file.name) + '">' + escapeHtml(file.name) + '</span>' +
                '<span class="picker-file-card-meta">' + escapeHtml(pickerSectionLabels[file.section] || file.section) + '</span>' +
                '</div></div>';
        }).join('');
    }

    function loadFilePicker() {
        if (!filePickerGrid) return;
        filePickerGrid.innerHTML = '<div class="col-12 text-center text-muted py-3">Загрузка списка файлов...</div>';
        window.AdminApi.get('/api/admin/files?action=list').then(function (data) {
            pickerFiles = Array.isArray(data.files) ? data.files : [];
            renderFilePicker();
        }).catch(function (error) {
            filePickerGrid.innerHTML = '<div class="col-12 text-center text-danger py-3">' +
                escapeHtml(error.message || 'Не удалось загрузить список файлов') + '</div>';
        });
    }

    if (pickerCategoryTabs) {
        pickerCategoryTabs.addEventListener('click', function (event) {
            var button = event.target.closest('[data-picker-category]');
            if (!button) return;
            pickerFilter.category = button.dataset.pickerCategory;
            pickerCategoryTabs.querySelectorAll('[data-picker-category]').forEach(function (tab) {
                tab.classList.toggle('active', tab === button);
            });
            renderFilePicker();
        });
    }
    if (filePickerSection) {
        filePickerSection.addEventListener('change', function () {
            pickerFilter.section = this.value;
            renderFilePicker();
        });
    }

    function uploadPickerFile(file) {
        return getCsrfToken().then(function (token) {
            var data = new FormData();
            data.append('action', 'upload');
            data.append('category', 'images');
            data.append('section', 'blog');
            data.append('file', file);
            return fetch('../api/admin/files', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-Token': token },
                body: data
            });
        }).then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok || !data.success || !data.file) {
                    throw new Error(data.error || 'Не удалось загрузить изображение.');
                }
                return data.file;
            });
        });
    }

    if (filePickerGrid) {
        filePickerGrid.addEventListener('click', function (event) {
            var button = event.target.closest('[data-picker-path]');
            if (!button) return;
            // dataset отдаёт camelCase-имена: data-picker-path → pickerPath
            // (dataset['picker-path'] вернул бы undefined — баг «/uploads/undefined»)
            var inserted = insertFileManagerImage(button.dataset.pickerPath, button.dataset.pickerName);
            if (!inserted) return;
            if (filePickerModal) filePickerModal.hide();
            showToast('Изображение вставлено в статью.', 'success');
        });
    }
    if (filePickerUploadButton && filePickerInput) {
        filePickerUploadButton.addEventListener('click', function () {
            filePickerInput.click();
        });
        filePickerInput.addEventListener('change', function () {
            var file = filePickerInput.files[0];
            filePickerInput.value = '';
            if (!file) return;
            filePickerUploadButton.disabled = true;
            uploadPickerFile(file).then(function (uploaded) {
                insertFileManagerImage(uploaded.path, uploaded.name);
                showToast('Изображение загружено и вставлено в статью.', 'success');
                loadFilePicker();
            }).catch(function (error) {
                console.error('Не удалось загрузить изображение из файлового менеджера.', error);
                showToast(error.message || 'Не удалось загрузить изображение.');
            }).finally(function () {
                filePickerUploadButton.disabled = false;
            });
        });
    }
    if (filePickerRefresh) {
        filePickerRefresh.addEventListener('click', loadFilePicker);
    }
    if (filePickerButton) {
        filePickerButton.addEventListener('mousedown', function (event) {
            event.preventDefault();
        });
        filePickerButton.addEventListener('click', function () {
            saveActiveSelection();
            if (!filePickerModal) return;
            loadFilePicker();
            filePickerModal.show();
        });
    }

    // ---------- Правая кнопка по картинке: обрезка, поворот, масштаб ----------
    // Результат обработки сохраняется в файловый менеджер (images/blog),
    // src картинки в редакторе заменяется на новый путь
    var imgMenu = document.createElement('div');
    imgMenu.id = 'articleImgEditMenu';
    imgMenu.className = 'article-img-edit-menu';
    imgMenu.innerHTML =
        '<button type="button" data-img-action="crop"><i class="bi bi-crop"></i> Обрезать</button>' +
        '<button type="button" data-img-action="rotateLeft"><i class="bi bi-arrow-counterclockwise"></i> Повернуть влево</button>' +
        '<button type="button" data-img-action="rotateRight"><i class="bi bi-arrow-clockwise"></i> Повернуть вправо</button>' +
        '<button type="button" data-img-action="enlarge"><i class="bi bi-plus-circle"></i> Увеличить</button>' +
        '<button type="button" data-img-action="shrink"><i class="bi bi-dash-circle"></i> Уменьшить</button>';
    document.body.appendChild(imgMenu);
    var imgMenuTarget = null;

    function hideImgMenu() {
        imgMenu.style.display = 'none';
        imgMenuTarget = null;
    }

    function showImgMenu(img, x, y) {
        imgMenuTarget = img;
        imgMenu.style.display = 'block';
        var menuRect = imgMenu.getBoundingClientRect();
        var left = Math.max(8, Math.min(x, window.innerWidth - menuRect.width - 8));
        var top = Math.max(8, Math.min(y, window.innerHeight - menuRect.height - 8));
        imgMenu.style.left = left + 'px';
        imgMenu.style.top = top + 'px';
    }

    if (contentEditor) {
        contentEditor.addEventListener('contextmenu', function (event) {
            var target = event.target;
            if (sourceMode || !target || target.tagName !== 'IMG' || !contentEditor.contains(target)) {
                return;
            }
            event.preventDefault();
            showImgMenu(target, event.clientX, event.clientY);
        });
    }
    imgMenu.addEventListener('click', function (event) {
        var button = event.target.closest('[data-img-action]');
        if (!button || !imgMenuTarget) return;
        var img = imgMenuTarget;
        var action = button.dataset.imgAction;
        hideImgMenu();
        processEditorImage(img, action);
    });
    document.addEventListener('click', function (event) {
        if (imgMenu.style.display !== 'none' && !imgMenu.contains(event.target)) hideImgMenu();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') hideImgMenu();
    });
    window.addEventListener('resize', hideImgMenu);
    window.addEventListener('scroll', hideImgMenu, true);

    // Загружаем картинку в Image: crossOrigin=anonymous позволяет обработать
    // и placehold.co (отдаёт CORS-заголовки), без CORS-заголовков изображение
    // не загрузится и показываем понятную ошибку
    function loadEditorImage(img) {
        return new Promise(function (resolve, reject) {
            var source = new Image();
            source.crossOrigin = 'anonymous';
            source.onload = function () { resolve(source); };
            source.onerror = function () {
                reject(new Error('Не удалось загрузить изображение для обработки — проверьте ссылку.'));
            };
            source.src = img.currentSrc || img.src;
        });
    }

    function transformForAction(source, action) {
        var w = source.naturalWidth;
        var h = source.naturalHeight;
        var newW = w;
        var newH = h;
        var rotate = null;
        if (action === 'rotateLeft' || action === 'rotateRight') {
            newW = h;
            newH = w;
            rotate = action;
        } else if (action === 'enlarge') {
            newW = Math.min(4000, Math.round(w * 1.25));
            newH = Math.min(4000, Math.round(h * 1.25));
        } else if (action === 'shrink') {
            newW = Math.max(8, Math.round(w * 0.8));
            newH = Math.max(8, Math.round(h * 0.8));
        }
        var canvas = document.createElement('canvas');
        canvas.width = newW;
        canvas.height = newH;
        var ctx = canvas.getContext('2d');
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        if (rotate === 'rotateRight') {
            ctx.translate(newW, 0);
            ctx.rotate(Math.PI / 2);
        } else if (rotate === 'rotateLeft') {
            ctx.translate(0, newH);
            ctx.rotate(-Math.PI / 2);
        }
        ctx.drawImage(source, 0, 0, newW, newH);
        return canvas;
    }

    // Формат сохраняем как у исходной картинки: PNG остаётся PNG, остальное — JPEG
    function saveEditedImage(img, canvas) {
        var mime = /\.png(\?|#|$)/i.test(img.src || '') ? 'image/png' : 'image/jpeg';
        var blobPromise;
        try {
            blobPromise = new Promise(function (resolve, reject) {
                canvas.toBlob(function (blob) {
                    blob ? resolve(blob) : reject(new Error('Не удалось сохранить изображение.'));
                }, mime, 0.9);
            });
        } catch (error) {
            // Canvas «запятнан» внешним источником без CORS
            showToast('Изображение с другого сайта нельзя обработать — загрузите его в файловый менеджер.');
            return;
        }
        blobPromise.then(function (blob) {
            var base = 'izobrazhenie';
            try {
                var last = decodeURIComponent((img.src || '').split('/').pop().split('?')[0] || '');
                base = last.replace(/\.[^.]+$/, '') || 'izobrazhenie';
            } catch (error) { /* имя из адреса не декодируется — оставляем запасное */ }
            var name = base.slice(0, 60) + (mime === 'image/png' ? '.png' : '.jpg');
            var file = new File([blob], name, { type: mime });
            uploadPickerFile(file).then(function (uploaded) {
                img.src = publicFileUrl(uploaded.path);
                showToast('Изображение обновлено и сохранено.', 'success');
            }).catch(function (error) {
                console.error('Не удалось загрузить отредактированное изображение.', error);
                showToast(error.message || 'Не удалось загрузить отредактированное изображение.');
            });
    }).catch(function (error) {
        showToast(error.message || 'Не удалось сохранить изображение.');
    });
    }

    function processEditorImage(img, action) {
        if (sourceMode) {
            showToast('Правка изображений доступна в визуальном режиме.');
            return;
        }
        if (action === 'crop') {
            openCropModal(img);
            return;
        }
        loadEditorImage(img).then(function (source) {
            saveEditedImage(img, transformForAction(source, action));
        }).catch(function (error) {
            showToast(error.message || 'Не удалось обработать изображение.');
        });
    }

    // ---------- Обрезка: рамка выделения в модальном окне ----------
    var cropModalEl = document.getElementById('articleCropModal');
    var cropModal = cropModalEl ? bootstrap.Modal.getOrCreateInstance(cropModalEl) : null;
    var cropStage = document.getElementById('articleCropStage');
    var cropImage = document.getElementById('articleCropImage');
    var cropSelection = document.getElementById('articleCropSelection');
    var cropApply = document.getElementById('articleCropApply');
    var cropImageEl = null;   // картинка в редакторе, которую режем
    var cropSource = null;    // загруженный Image (naturalWidth/Height)
    var cropRect = null;      // рамка в координатах отображения {x, y, w, h}
    var cropDrag = null;      // состояние перетаскивания рамки

    function updateCropSelection() {
        if (!cropRect) {
            cropSelection.hidden = true;
            return;
        }
        cropSelection.hidden = false;
        cropSelection.style.left = cropRect.x + 'px';
        cropSelection.style.top = cropRect.y + 'px';
        cropSelection.style.width = cropRect.w + 'px';
        cropSelection.style.height = cropRect.h + 'px';
    }

    function onCropReady() {
        cropImage.hidden = false;
        cropSelection.hidden = false;
        // Рамка по умолчанию — вся картинка (с небольшим отступом)
        var insetX = Math.max(0, Math.round(cropImage.clientWidth * 0.02));
        var insetY = Math.max(0, Math.round(cropImage.clientHeight * 0.02));
        cropRect = {
            x: insetX,
            y: insetY,
            w: Math.max(8, cropImage.clientWidth - insetX * 2),
            h: Math.max(8, cropImage.clientHeight - insetY * 2)
        };
        updateCropSelection();
    }

    function openCropModal(img) {
        if (!cropModal) return;
        cropImageEl = img;
        cropRect = null;
        cropSelection.hidden = true;
        loadEditorImage(img).then(function (source) {
            cropSource = source;
            cropImage.hidden = true;
            cropModal.show();
            cropImage.onload = onCropReady;
            cropImage.onerror = function () {
                cropModal.hide();
                showToast('Не удалось загрузить изображение для обрезки.');
            };
            cropImage.src = source.src;
            if (cropImage.complete && cropImage.naturalWidth) onCropReady();
        }).catch(function (error) {
            showToast(error.message || 'Не удалось открыть обрезку.');
        });
    }

    function cropStagePoint(event) {
        var rect = cropStage.getBoundingClientRect();
        return {
            x: Math.min(rect.width, Math.max(0, event.clientX - rect.left)),
            y: Math.min(rect.height, Math.max(0, event.clientY - rect.top))
        };
    }

    if (cropStage) {
        var CROP_HANDLE = 8; // зона угла рамки для изменения размера

        cropStage.addEventListener('pointerdown', function (event) {
            if (!cropSource || !cropImage || cropImage.hidden || event.button !== 0) return;
            event.preventDefault();
            cropStage.setPointerCapture(event.pointerId);
            var point = cropStagePoint(event);
            var corner = null;
            var rect = cropRect;
            if (rect && rect.w > 0 && rect.h > 0) {
                var corners = {
                    tl: { x: rect.x, y: rect.y },
                    tr: { x: rect.x + rect.w, y: rect.y },
                    bl: { x: rect.x, y: rect.y + rect.h },
                    br: { x: rect.x + rect.w, y: rect.y + rect.h }
                };
                Object.keys(corners).forEach(function (key) {
                    if (corner) return;
                    var c = corners[key];
                    if (Math.abs(point.x - c.x) <= CROP_HANDLE && Math.abs(point.y - c.y) <= CROP_HANDLE) corner = key;
                });
            }
            if (corner) {
                cropDrag = { mode: 'resize', corner: corner, orig: { x: rect.x, y: rect.y, w: rect.w, h: rect.h } };
            } else if (rect && point.x > rect.x && point.x < rect.x + rect.w &&
                       point.y > rect.y && point.y < rect.y + rect.h) {
                cropDrag = { mode: 'move', offsetX: point.x - rect.x, offsetY: point.y - rect.y };
            } else {
                cropRect = { x: point.x, y: point.y, w: 0, h: 0 };
                cropDrag = { mode: 'new', anchor: point };
                updateCropSelection();
            }
        });

        cropStage.addEventListener('pointermove', function (event) {
            if (!cropDrag || !cropSource || !cropImage) return;
            var point = cropStagePoint(event);
            var W = cropImage.clientWidth;
            var H = cropImage.clientHeight;
            if (cropDrag.mode === 'move') {
                cropRect.x = Math.min(W - cropRect.w, Math.max(0, point.x - cropDrag.offsetX));
                cropRect.y = Math.min(H - cropRect.h, Math.max(0, point.y - cropDrag.offsetY));
            } else if (cropDrag.mode === 'new') {
                var a = cropDrag.anchor;
                cropRect.x = Math.min(a.x, point.x);
                cropRect.y = Math.min(a.y, point.y);
                cropRect.w = Math.abs(point.x - a.x);
                cropRect.h = Math.abs(point.y - a.y);
            } else {
                var o = cropDrag.orig;
                var x1 = o.x, y1 = o.y, x2 = o.x + o.w, y2 = o.y + o.h;
                if (cropDrag.corner === 'tl') { x1 = point.x; y1 = point.y; }
                else if (cropDrag.corner === 'tr') { x2 = point.x; y1 = point.y; }
                else if (cropDrag.corner === 'bl') { x1 = point.x; y2 = point.y; }
                else { x2 = point.x; y2 = point.y; }
                cropRect.x = Math.min(x1, x2);
                cropRect.y = Math.min(y1, y2);
                cropRect.w = Math.abs(x2 - x1);
                cropRect.h = Math.abs(y2 - y1);
            }
            cropRect.x = Math.max(0, Math.min(cropRect.x, W - 1));
            cropRect.y = Math.max(0, Math.min(cropRect.y, H - 1));
            cropRect.w = Math.max(1, Math.min(cropRect.w, W - cropRect.x));
            cropRect.h = Math.max(1, Math.min(cropRect.h, H - cropRect.y));
            updateCropSelection();
        });

        function endCropDrag() {
            if (!cropDrag) return;
            cropDrag = null;
            // Слишком маленькая рамка — сбрасываем на всю картинку
            if (cropRect && (cropRect.w < 4 || cropRect.h < 4)) {
                cropRect = { x: 0, y: 0, w: cropImage.clientWidth, h: cropImage.clientHeight };
                updateCropSelection();
            }
        }
        cropStage.addEventListener('pointerup', endCropDrag);
        cropStage.addEventListener('pointercancel', endCropDrag);
    }

    if (cropApply) {
        cropApply.addEventListener('click', function () {
            if (!cropSource || !cropRect || !cropImageEl || !cropImage.clientWidth) return;
            // Пересчитываем рамку в естественные координаты изображения
            var scaleX = cropSource.naturalWidth / cropImage.clientWidth;
            var scaleY = cropSource.naturalHeight / cropImage.clientHeight;
            var sx = Math.max(0, Math.min(Math.round(cropRect.x * scaleX), cropSource.naturalWidth - 1));
            var sy = Math.max(0, Math.min(Math.round(cropRect.y * scaleY), cropSource.naturalHeight - 1));
            var sw = Math.max(1, Math.min(Math.round(cropRect.w * scaleX), cropSource.naturalWidth - sx));
            var sh = Math.max(1, Math.min(Math.round(cropRect.h * scaleY), cropSource.naturalHeight - sy));
            var canvas = document.createElement('canvas');
            canvas.width = sw;
            canvas.height = sh;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(cropSource, sx, sy, sw, sh, 0, 0, sw, sh);
            var img = cropImageEl;
            if (cropModal) cropModal.hide();
            saveEditedImage(img, canvas);
        });
    }

    // ---------- Placeholder-изображение (placehold.co) ----------
    function buildPlaceholderUrl() {
        var width = Math.max(1, Math.min(4000, parseInt(placeholderWidth.value, 10) || 1200));
        var height = Math.max(1, Math.min(4000, parseInt(placeholderHeight.value, 10) || 675));
        var bg = String(placeholderBg.value || '#ffffff').replace(/^#/, '').toLowerCase();
        var fg = String(placeholderFg.value || '#333333').replace(/^#/, '').toLowerCase();
        var text = (placeholderText.value || 'Placeholder').trim() || 'Placeholder';
        return 'https://placehold.co/' + width + 'x' + height + '/' + bg + '/' + fg + '?text=' + encodeURIComponent(text);
    }

    function refreshPlaceholderPreview() {
        if (!placeholderPreview || !placeholderUrl) return;
        var url = buildPlaceholderUrl();
        placeholderPreview.src = url;
        placeholderUrl.textContent = url;
    }

    if (placeholderForm) {
        [placeholderWidth, placeholderHeight, placeholderBg, placeholderFg, placeholderText].forEach(function (field) {
            if (field) field.addEventListener('input', refreshPlaceholderPreview);
        });
        placeholderForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var url = buildPlaceholderUrl();
            var alt = (placeholderText.value || 'Placeholder').trim() || 'Placeholder';
            insertHtml('<p><img src="' + escapeHtml(url) + '" alt="' + escapeHtml(alt) + '" class="img-fluid"></p>');
            if (placeholderModal) placeholderModal.hide();
            showToast('Placeholder-изображение вставлено в статью.', 'success');
        });
    }
    if (placeholderButton) {
        placeholderButton.addEventListener('mousedown', function (event) {
            event.preventDefault();
        });
        placeholderButton.addEventListener('click', function () {
            saveActiveSelection();
            if (!placeholderModal) return;
            refreshPlaceholderPreview();
            placeholderModal.show();
        });
    }
    sourceToggle.addEventListener('click', function () {
        setSourceMode(!sourceMode);
    });
    fields.cover.addEventListener('change', function () {
        if (coverPreviewUrl) {
            URL.revokeObjectURL(coverPreviewUrl);
            coverPreviewUrl = '';
        }
        if (!fields.cover.files.length) {
            showCover(currentCover);
            return;
        }
        coverPreviewUrl = URL.createObjectURL(fields.cover.files[0]);
        coverPreview.onload = function () {
            coverPreview.hidden = false;
            coverPlaceholder.hidden = true;
        };
        coverPreview.onerror = showCoverPlaceholder;
        coverPreview.src = coverPreviewUrl;
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var articleContent = getArticleContent();
        if (!hasArticleContent(articleContent)) {
            showToast('Введите текст статьи.');
            (sourceMode ? fields.content : contentEditor).focus();
            return;
        }
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        try {
            var slug = fields.slug.value.trim();
            var savedId = recordId;
            var saved = {
                id: savedId,
                title: fields.title.value.trim(),
                slug: slug,
                category: fields.category.value,
                description: fields.description.value.trim(),
                content: articleContent,
                author: fields.author.value.trim(),
                date: fields.date.value,
                active: fields.active.checked,
                tags: fields.tags.value.trim(),
                cover: currentCover,
                icon: 'bi-file-text'
            };
            var saveArticle = function () {
                return window.AdminApi.save(
                    '/api/admin/articles' + (isEditing ? '/' + encodeURIComponent(savedId) : ''),
                    isEditing ? 'PUT' : 'POST',
                    saved
                ).then(function () {
                    window.location.href = 'blog.html';
                }).catch(function (error) {
                    console.error('Не удалось сохранить статью.', error);
                    showToast(error.message || 'Не удалось сохранить статью.');
                });
            };
            var submitButton = form.querySelector('[type="submit"]');
            submitButton.disabled = true;
            if (fields.cover.files.length) {
                uploadCover(savedId, fields.cover.files[0]).then(function (coverPath) {
                    saved.cover = coverPath;
                    return saveArticle();
                }).catch(function (error) {
                    console.error('Не удалось загрузить обложку статьи.', error);
                    showToast(error.message || 'Не удалось загрузить обложку статьи.');
                }).finally(function () {
                    submitButton.disabled = false;
                });
            } else {
                saveArticle().finally(function () {
                    submitButton.disabled = false;
                });
            }
        } catch (error) {
            console.error('Не удалось сохранить статью.', error);
            showToast(error.message || 'Не удалось сохранить статью.');
        }
    });
})();
