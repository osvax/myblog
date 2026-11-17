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
                document.getElementById('currentCover').textContent = 'JPEG, PNG или WebP, до 10 МБ.';
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
                var url = window.prompt('Введите адрес ссылки (http, https или mailto):');
                if (!url) return;
                url = url.trim();
                if (!/^(https?:\/\/|mailto:)/i.test(url)) {
                    showToast('Ссылка должна начинаться с http://, https:// или mailto:');
                    return;
                }
                document.execCommand(command, false, url);
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
        var url = window.prompt('Введите ссылку на видео или ролик YouTube / Vimeo:');
        if (!url) return;
        try {
            insertHtml(videoMarkup(url));
        } catch (error) {
            showToast(error.message);
        }
    });
    audioInsertButton.addEventListener('mousedown', function (event) { event.preventDefault(); });
    audioInsertButton.addEventListener('click', function () {
        saveActiveSelection();
        var url = window.prompt('Введите прямую ссылку на аудиофайл (MP3, OGG, WAV и другие форматы браузера):');
        if (!url) return;
        try {
            insertHtml(audioMarkup(url));
        } catch (error) {
            showToast(error.message);
        }
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
