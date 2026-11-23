document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('categoryNavigationForm');
    if (!form) return;

    const categorySelect = document.getElementById('navigationCategorySelect');
    const list = document.getElementById('categoryNavigationList');
    const source = document.getElementById('categoryNavigationSource');
    const picker = document.getElementById('categoryNavigationPicker');
    const pickerWrap = document.getElementById('categoryNavigationPickerWrap');
    const id = document.getElementById('categoryNavigationId');
    const title = document.getElementById('categoryNavigationTitle');
    const url = document.getElementById('categoryNavigationUrl');
    const icon = document.getElementById('categoryNavigationIcon');
    const active = document.getElementById('categoryNavigationActive');
    const submit = document.getElementById('categoryNavigationSubmit');
    const cancel = document.getElementById('categoryNavigationCancel');
    let items = [];
    let targets = null;

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
    }

    function resetForm() {
        form.reset();
        id.value = '';
        pickerWrap.hidden = true;
        submit.innerHTML = '<i class="bi bi-plus-lg"></i> Добавить пункт';
        cancel.hidden = true;
        active.checked = true;
    }

    function render() {
        if (!items.length) {
            list.innerHTML = '<div class="text-muted small">Пунктов навигации пока нет.</div>';
            return;
        }
        list.innerHTML = items.map((item) => '<div class="d-flex align-items-center gap-2 border rounded p-2 mb-2" draggable="true" data-navigation-row="' + item.id + '">' +
            '<i class="bi ' + escapeHtml(item.icon || 'bi-link-45deg') + ' text-orange"></i>' +
            '<strong>' + escapeHtml(item.title) + '</strong><code class="small">' + escapeHtml(item.url) + '</code>' +
            '<span class="badge ' + (Number(item.is_active) ? 'bg-success' : 'bg-secondary') + ' ms-auto">' + (Number(item.is_active) ? 'Активен' : 'Скрыт') + '</span>' +
            '<button type="button" class="btn btn-sm btn-outline-dark" data-navigation-edit="' + item.id + '" title="Редактировать"><i class="bi bi-pencil"></i></button>' +
            '<button type="button" class="btn btn-sm btn-outline-danger" data-navigation-delete="' + item.id + '" title="Удалить"><i class="bi bi-trash"></i></button></div>').join('');
        bindDragAndDrop();
    }

    function loadItems() {
        if (!categorySelect.value) {
            items = [];
            render();
            return Promise.resolve();
        }
        return window.AdminApi.get('/api/admin/categories/' + categorySelect.value + '/navigation').then((data) => {
            items = data.items || [];
            render();
        });
    }

    function bindDragAndDrop() {
        let dragged = null;
        list.querySelectorAll('[data-navigation-row]').forEach((row) => {
            row.addEventListener('dragstart', (event) => {
                dragged = row;
                row.classList.add('opacity-50');
                event.dataTransfer.effectAllowed = 'move';
            });
            row.addEventListener('dragend', () => {
                row.classList.remove('opacity-50');
                dragged = null;
            });
            row.addEventListener('dragover', (event) => {
                if (!dragged || dragged === row) return;
                event.preventDefault();
                row.classList.add('border-orange');
            });
            row.addEventListener('dragleave', () => row.classList.remove('border-orange'));
            row.addEventListener('drop', (event) => {
                event.preventDefault();
                row.classList.remove('border-orange');
                if (!dragged || dragged === row) return;
                const rows = Array.from(list.querySelectorAll('[data-navigation-row]'));
                const from = rows.indexOf(dragged);
                const to = rows.indexOf(row);
                if (from < to) row.after(dragged); else row.before(dragged);
                saveOrder(Array.from(list.querySelectorAll('[data-navigation-row]')).map((entry) => Number(entry.dataset.navigationRow)));
            });
        });
    }

    function saveOrder(itemIds) {
        window.AdminApi.save('/api/admin/categories/' + categorySelect.value + '/navigation', 'PATCH', { item_ids: itemIds })
            .then(loadItems)
            .then(() => window.AdminNotifications.show(true, 'Порядок пунктов сохранён'))
            .catch((error) => {
                window.AdminNotifications.show('error', error.message || 'Не удалось сохранить порядок пунктов');
                loadItems();
            });
    }

    function syncTarget() {
        const option = picker.selectedOptions[0];
        if (!option) return;
        title.value = option.dataset.title;
        url.value = option.value;
    }

    function fillPicker() {
        picker.replaceChildren();
        const entries = targets[source.value === 'page' ? 'pages' : source.value === 'category' ? 'categories' : 'articles'];
        entries.forEach((entry) => {
            const option = document.createElement('option');
            const name = entry.name || entry.title || entry.slug;
            option.value = (source.value === 'category' ? '/category/' : '/') + entry.slug + '.html';
            option.textContent = name + ' (' + option.value + ')';
            option.dataset.title = name;
            picker.appendChild(option);
        });
        syncTarget();
    }

    function loadTargets() {
        return targets ? Promise.resolve(targets) : window.AdminApi.get('/api/admin/categories?action=pickers').then((data) => {
            targets = data;
            return targets;
        });
    }

    categorySelect.addEventListener('change', () => {
        resetForm();
        loadItems();
    });
    source.addEventListener('change', () => {
        pickerWrap.hidden = source.value === 'link';
        if (!pickerWrap.hidden) loadTargets().then(fillPicker).catch((error) => window.AdminNotifications.show('error', error.message || 'Не удалось загрузить цели навигации'));
    });
    picker.addEventListener('change', syncTarget);
    cancel.addEventListener('click', resetForm);
    form.addEventListener('reset', () => {
        window.setTimeout(() => {
            id.value = '';
            pickerWrap.hidden = true;
            submit.innerHTML = '<i class="bi bi-plus-lg"></i> Добавить пункт';
            cancel.hidden = true;
            active.checked = true;
        }, 0);
    });
    list.addEventListener('click', (event) => {
        const edit = event.target.closest('[data-navigation-edit]');
        if (edit) {
            const item = items.find((entry) => String(entry.id) === edit.dataset.navigationEdit);
            if (!item) return;
            id.value = item.id;
            title.value = item.title || '';
            url.value = item.url || '';
            icon.value = item.icon || '';
            active.checked = Number(item.is_active) === 1;
            source.value = 'link';
            pickerWrap.hidden = true;
            submit.innerHTML = '<i class="bi bi-save"></i> Сохранить изменения';
            cancel.hidden = false;
            title.focus();
            return;
        }
        const remove = event.target.closest('[data-navigation-delete]');
        if (!remove) return;
        window.AdminApi.remove('/api/admin/categories/' + categorySelect.value + '/navigation/' + remove.dataset.navigationDelete)
            .then(loadItems)
            .then(() => window.AdminNotifications.show(true, 'Пункт удалён'))
            .catch((error) => window.AdminNotifications.show('error', error.message || 'Не удалось удалить пункт'));
    });
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!categorySelect.value) {
            window.AdminNotifications.show('warning', 'Сначала создайте рубрику');
            return;
        }
        const itemId = id.value;
        window.AdminApi.save('/api/admin/categories/' + categorySelect.value + '/navigation' + (itemId ? '/' + itemId : ''), itemId ? 'PUT' : 'POST', {
            title: title.value.trim(),
            url: url.value.trim(),
            icon: icon.value.trim(),
            is_active: active.checked ? 1 : 0
        }).then(() => {
            resetForm();
            return loadItems();
        }).then(() => window.AdminNotifications.show(true, 'Пункт навигации сохранён'))
            .catch((error) => window.AdminNotifications.show('error', error.message || 'Не удалось сохранить пункт'));
    });

    window.CategoryNavigation = { setCategories(categories) {
        categorySelect.innerHTML = categories.map((category) => '<option value="' + category.id + '">' + escapeHtml(category.name) + '</option>').join('');
        loadItems();
    } };
});
