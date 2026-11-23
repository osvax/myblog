document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('rubricMenuSelect');
    if (!select) return;

    const nameInput = document.getElementById('rubricMenuName');
    const createButton = document.getElementById('createRubricMenu');
    const deleteButton = document.getElementById('deleteRubricMenu');
    const itemsList = document.getElementById('rubricMenuItems');
    const form = document.getElementById('rubricMenuItemForm');
    const source = document.getElementById('rubricMenuSource');
    const picker = document.getElementById('rubricMenuPicker');
    const pickerWrap = document.getElementById('rubricMenuPickerWrap');
    const titleInput = document.getElementById('rubricMenuItemTitle');
    const urlInput = document.getElementById('rubricMenuItemUrl');
    const itemId = document.getElementById('rubricMenuItemId');
    const iconInput = document.getElementById('rubricMenuItemIcon');
    const activeInput = document.getElementById('rubricMenuItemActive');
    const submit = document.getElementById('rubricMenuItemSubmit');
    const cancel = document.getElementById('rubricMenuItemCancel');
    let menus = [];
    let targets = {};
    let items = [];

    const escapeHtml = (value) => String(value || '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
    const api = (path, method = 'GET', body = null) => method === 'GET' ? window.AdminApi.get(path) : window.AdminApi.save(path, method, body);

    function resetForm() {
        form.reset();
        itemId.value = '';
        activeInput.checked = true;
        pickerWrap.hidden = true;
        submit.innerHTML = '<i class="bi bi-plus-lg"></i> Добавить пункт';
        cancel.hidden = true;
    }

    function renderItems() {
        if (!items.length) {
            itemsList.innerHTML = '<div class="text-muted small">В этом меню пока нет рубрик.</div>';
            return;
        }
        itemsList.innerHTML = items.map((item) => '<div class="d-flex align-items-center gap-2 border rounded p-2 mb-2" draggable="true" data-rubric-item="' + item.id + '">' +
            '<i class="bi ' + escapeHtml(item.icon || 'bi-tag') + ' text-orange"></i><strong>' + escapeHtml(item.title) + '</strong><code class="small">' + escapeHtml(item.url) + '</code>' +
            '<span class="badge ' + (Number(item.is_active) ? 'bg-success' : 'bg-secondary') + ' ms-auto">' + (Number(item.is_active) ? 'Активен' : 'Скрыт') + '</span>' +
            '<button type="button" class="btn btn-sm btn-outline-dark" data-rubric-edit="' + item.id + '" title="Редактировать"><i class="bi bi-pencil"></i></button>' +
            '<button type="button" class="btn btn-sm btn-outline-danger" data-rubric-delete="' + item.id + '" title="Удалить"><i class="bi bi-trash"></i></button></div>').join('');
        bindDragAndDrop();
    }

    function loadItems() {
        if (!select.value) {
            items = [];
            renderItems();
            return Promise.resolve();
        }
        return api('/api/admin/rubric-menus?action=items&menu=' + encodeURIComponent(select.value)).then((data) => {
            items = data.items || [];
            renderItems();
        });
    }

    function loadMenus(selected = '') {
        return api('/api/admin/rubric-menus?action=menus').then((data) => {
            menus = data.menus || [];
            select.innerHTML = menus.map((menu) => '<option value="' + escapeHtml(menu.menu_name.replace(/^rubric_/, '')) + '">' + escapeHtml(menu.title || menu.menu_name.replace(/^rubric_/, '')) + ' (' + menu.items_count + ')</option>').join('');
            if (!menus.length) select.innerHTML = '<option value="main">Рубрики блога (основное меню)</option>';
            if (selected && Array.from(select.options).some((option) => option.value === selected)) select.value = selected;
            return loadItems();
        });
    }

    function loadCategories() {
        return api('/api/admin/rubric-menus?action=pickers').then((data) => { targets = data; });
    }

    function syncTarget() {
        const option = picker.selectedOptions[0];
        if (!option) return;
        titleInput.value = option.dataset.title;
        urlInput.value = option.value;
    }

    source.addEventListener('change', () => {
        pickerWrap.hidden = source.value === 'link';
        picker.replaceChildren();
        if (pickerWrap.hidden) return;
        const key = { category: 'categories', page: 'pages', article: 'articles', menu: 'menuItems' }[source.value];
        (targets[key] || []).forEach((entry) => {
            const option = document.createElement('option');
            option.value = source.value === 'menu' ? entry.url : (source.value === 'category' ? '/category/' : '/') + entry.slug + '.html';
            option.dataset.title = entry.name || entry.title || entry.slug;
            option.textContent = option.dataset.title + ' (' + option.value + ')';
            picker.appendChild(option);
        });
        syncTarget();
    });
    picker.addEventListener('change', syncTarget);

    function bindDragAndDrop() {
        let dragged = null;
        itemsList.querySelectorAll('[data-rubric-item]').forEach((row) => {
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
                const rows = Array.from(itemsList.querySelectorAll('[data-rubric-item]'));
                if (rows.indexOf(dragged) < rows.indexOf(row)) row.after(dragged); else row.before(dragged);
                api('/api/admin/rubric-menus', 'POST', {
                    action: 'reorder',
                    menu_name: select.value,
                    parent_id: null,
                    item_ids: Array.from(itemsList.querySelectorAll('[data-rubric-item]')).map((entry) => Number(entry.dataset.rubricItem))
                }).then(loadItems).then(() => window.AdminNotifications.show(true, 'Порядок рубрик сохранён'))
                    .catch((error) => { window.AdminNotifications.show('error', error.message || 'Не удалось сохранить порядок'); loadItems(); });
            });
        });
    }

    select.addEventListener('change', () => { resetForm(); loadItems(); });
    createButton.addEventListener('click', () => {
        const name = nameInput.value.trim();
        if (!/^[a-z0-9_-]{1,70}$/.test(name)) {
            window.AdminNotifications.show('warning', 'Укажите идентификатор: латиница, цифры, - и _');
            return;
        }
        api('/api/admin/rubric-menus', 'POST', { action: 'create', menu_name: name, title: name })
            .then(() => { nameInput.value = ''; return loadMenus(name); })
            .then(() => window.AdminNotifications.show(true, 'Меню рубрик создано'))
            .catch((error) => window.AdminNotifications.show('error', error.message || 'Не удалось создать меню'));
    });
    deleteButton.addEventListener('click', () => {
        if (!select.value) return;
        api('/api/admin/rubric-menus/' + encodeURIComponent(select.value) + '/delete-menu', 'DELETE')
            .then(() => loadMenus())
            .then(() => window.AdminNotifications.show(true, 'Меню рубрик удалено'))
            .catch((error) => window.AdminNotifications.show('error', error.message || 'Не удалось удалить меню'));
    });
    itemsList.addEventListener('click', (event) => {
        const edit = event.target.closest('[data-rubric-edit]');
        if (edit) {
            const item = items.find((entry) => String(entry.id) === edit.dataset.rubricEdit);
            if (!item) return;
            source.value = 'link';
            pickerWrap.hidden = true;
            titleInput.value = item.title;
            urlInput.value = item.url;
            itemId.value = item.id;
            iconInput.value = item.icon || '';
            activeInput.checked = Number(item.is_active) === 1;
            submit.innerHTML = '<i class="bi bi-save"></i> Сохранить изменения';
            cancel.hidden = false;
            return;
        }
        const remove = event.target.closest('[data-rubric-delete]');
        if (!remove) return;
        api('/api/admin/rubric-menus/' + remove.dataset.rubricDelete + '/delete-item', 'DELETE')
            .then(loadItems)
            .then(() => window.AdminNotifications.show(true, 'Рубрика удалена из меню'))
            .catch((error) => window.AdminNotifications.show('error', error.message || 'Не удалось удалить рубрику'));
    });
    cancel.addEventListener('click', resetForm);
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!select.value || !titleInput.value.trim() || !urlInput.value.trim()) return;
        const editing = Boolean(itemId.value);
        api('/api/admin/rubric-menus', 'POST', {
            action: 'save', menu_name: select.value, id: editing ? Number(itemId.value) : 0,
            title: titleInput.value.trim(), url: urlInput.value.trim(), icon: iconInput.value.trim(),
            css_class: '', parent_id: null, is_active: activeInput.checked ? 1 : 0
        }).then(() => { resetForm(); return loadItems(); })
            .then(() => window.AdminNotifications.show(true, 'Рубрика сохранена в меню'))
            .catch((error) => window.AdminNotifications.show('error', error.message || 'Не удалось сохранить рубрику'));
    });

    Promise.all([loadCategories(), loadMenus()]).catch((error) => window.AdminNotifications.show('error', error.message || 'Не удалось загрузить меню рубрик'));
});
