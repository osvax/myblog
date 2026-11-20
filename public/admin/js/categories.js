(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var body = document.getElementById('categoriesTableBody');
        var errorBox = document.getElementById('categoriesError');
        var form = document.getElementById('categoryForm');
        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('categoryModal'));
        var categories = [];

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function (char) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
            });
        }

        function render() {
            if (!categories.length) {
                body.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Рубрик пока нет</td></tr>';
                return;
            }
            body.innerHTML = categories.map(function (category) {
                return '<tr><td><i class="bi bi-tags fs-5 text-orange me-2"></i>' + escapeHtml(category.name) +
                    '</td><td><code>' + escapeHtml(category.slug) + '</code></td><td><span class="badge bg-orange fw-bold">' +
                    Number(category.articles) + '</span></td><td class="text-muted">' + escapeHtml(category.description) +
                    '</td><td class="text-end text-nowrap"><button class="btn btn-outline-dark btn-action me-1" type="button" data-category-edit="' +
                    category.id + '" aria-label="Изменить рубрику"><i class="bi bi-pencil"></i></button><button class="btn btn-danger btn-action" type="button" data-category-delete="' +
                    category.id + '" aria-label="Удалить рубрику"><i class="bi bi-trash"></i></button></td></tr>';
            }).join('');
        }

        function load() {
            return window.AdminApi.get('/api/admin/categories').then(function (data) {
                categories = data.categories;
                render();
            });
        }

        document.getElementById('addCategoryButton').addEventListener('click', function () {
            form.reset();
            form.elements.id.value = '';
            slugTouched = false;
            document.getElementById('categoryModalTitle').textContent = 'Новая рубрика';
            modal.show();
        });

        // Автогенерация slug из названия: кириллица транслитерируется,
        // пробелы и прочие символы заменяются тире; ручной ввод приоритетнее
        var slugTouched = false;
        var transliteration = {
            'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo',
            'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
            'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
            'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch',
            'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya'
        };

        function toSlug(value) {
            return value.toLowerCase().split('').map(function (char) {
                return Object.prototype.hasOwnProperty.call(transliteration, char) ? transliteration[char] : char;
            }).join('').replace(/[^a-z0-9]+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
        }

        form.elements.slug.addEventListener('input', function () {
            slugTouched = true;
        });

        form.elements.name.addEventListener('input', function () {
            if (!slugTouched) {
                form.elements.slug.value = toSlug(form.elements.name.value);
            }
        });

        body.addEventListener('click', function (event) {
            var edit = event.target.closest('[data-category-edit]');
            if (edit) {
                var category = categories.find(function (item) { return String(item.id) === edit.dataset.categoryEdit; });
                if (!category) return;
                form.elements.id.value = category.id;
                form.elements.name.value = category.name;
                form.elements.slug.value = category.slug;
                form.elements.description.value = category.description || '';
                slugTouched = true;
                document.getElementById('categoryModalTitle').textContent = 'Редактировать рубрику';
                modal.show();
                return;
            }
            var remove = event.target.closest('[data-category-delete]');
            if (!remove) return;
            window.AdminApi.remove('/api/admin/categories/' + encodeURIComponent(remove.dataset.categoryDelete))
                .then(load).then(function () {
                    window.AdminNotifications.show('success', 'Рубрика удалена');
                }).catch(function (error) {
                    window.AdminNotifications.show('error', error.message || 'Рубрику нельзя удалить: проверьте связанные статьи');
                });
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var id = form.elements.id.value;
            var data = {
                name: form.elements.name.value.trim(),
                slug: form.elements.slug.value.trim(),
                description: form.elements.description.value.trim()
            };
            window.AdminApi.save('/api/admin/categories' + (id ? '/' + encodeURIComponent(id) : ''), id ? 'PUT' : 'POST', data)
                .then(load).then(function () {
                    modal.hide();
                    window.AdminNotifications.show('success', 'Рубрика сохранена');
                }).catch(function (error) {
                    errorBox.textContent = error.message || 'Не удалось сохранить рубрику';
                    errorBox.hidden = false;
                });
        });

        load().catch(function (error) {
            errorBox.textContent = error.message || 'Не удалось загрузить рубрики';
            errorBox.hidden = false;
            body.textContent = '';
        });
    });
}());
