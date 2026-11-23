(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var menuSelect = document.getElementById('menuSelect');
        var itemsList = document.getElementById('menuItemsList');
        var itemsInfo = document.getElementById('menuItemsInfo');
        var errorBox = document.getElementById('menuError');
        var itemSource = document.getElementById('itemSource');
        var itemPickerWrap = document.getElementById('itemPickerWrap');
        var itemPicker = document.getElementById('itemPicker');
        var itemTitle = document.getElementById('itemTitle');
        var itemUrl = document.getElementById('itemUrl');
        var itemIcon = document.getElementById('itemIcon');
        var itemClass = document.getElementById('itemClass');
        var itemParent = document.getElementById('itemParent');
        var itemActive = document.getElementById('itemActive');
        var addItemButton = document.getElementById('addItemButton');
        var updateItemButton = document.getElementById('updateItemButton');
        var cancelEditButton = document.getElementById('cancelEditButton');
        var pickers = { pages: [], categories: [], articles: [] };
        var currentItems = [];
        var editingId = 0;

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, function (char) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
            });
        }

        function showError(message) {
            errorBox.textContent = message;
            errorBox.hidden = !message;
        }

        // ---------- Загрузка ----------
        function loadMenus(selectName) {
            return window.AdminApi.get('/api/admin/menus?action=menus').then(function (data) {
                menuSelect.textContent = '';
                (data.menus || []).forEach(function (menu) {
                    var option = document.createElement('option');
                    option.value = menu.menu_name;
                    option.textContent = (menu.title || menu.menu_name) + ' (' + menu.items_count + ')';
                    menuSelect.appendChild(option);
                });
                if (selectName) menuSelect.value = selectName;
                if (!menuSelect.value && menuSelect.options.length) menuSelect.value = menuSelect.options[0].value;
            });
        }

        function loadItems() {
            if (!menuSelect.value) {
                itemsList.innerHTML = '<p class="text-muted fw-bold mb-0">Создайте меню.</p>';
                return Promise.resolve();
            }
            return window.AdminApi.get('/api/admin/menus?action=items&menu=' + encodeURIComponent(menuSelect.value))
                .then(function (data) {
                    currentItems = data.items || [];
                    renderItems();
                });
        }

        function loadPickers() {
            return window.AdminApi.get('/api/admin/menus?action=pickers').then(function (data) {
                pickers = {
                    pages: data.pages || [],
                    categories: data.categories || [],
                    articles: data.articles || []
                };
                return;
            });
        }

        function fillParentOptions(excludeId) {
            itemParent.textContent = '';
            var empty = document.createElement('option');
            empty.value = '';
            empty.textContent = '— нет (верхний уровень) —';
            itemParent.appendChild(empty);
            currentItems.filter(function (item) { return !item.parent_id && item.id !== excludeId; })
                .forEach(function (item) {
                    var option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.title;
                    itemParent.appendChild(option);
                });
        }

        // ---------- Отрисовка пунктов ----------
        function itemRow(item, isChild) {
            var active = Number(item.is_active) === 1;
            return '<div class="border rounded p-2 mb-2' + (isChild ? ' ms-4' : '') + '" data-item-row="' + item.id + '" data-parent-id="' + (item.parent_id || '') + '" draggable="true">' +
                '<div class="d-flex align-items-center gap-2 flex-wrap">' +
                (item.icon ? '<i class="bi ' + escapeHtml(item.icon) + ' text-orange"></i>' : '<i class="bi bi-link-45deg text-muted"></i>') +
                '<strong class="fw-bold">' + escapeHtml(item.title) + '</strong>' +
                '<code class="small">' + escapeHtml(item.url) + '</code>' +
                (item.css_class ? '<span class="badge bg-secondary">' + escapeHtml(item.css_class) + '</span>' : '') +
                '<span class="badge ' + (active ? 'bg-success' : 'bg-secondary') + '">' + (active ? 'Активен' : 'Скрыт') + '</span>' +
                '<span class="ms-auto d-flex gap-1">' +
                '<button type="button" class="btn btn-sm btn-outline-dark" data-item-edit="' + item.id + '" title="Редактировать" aria-label="Редактировать"><i class="bi bi-pencil"></i></button>' +
                '<button type="button" class="btn btn-sm btn-outline-danger" data-item-delete="' + item.id + '" title="Удалить" aria-label="Удалить пункт с подчинёнными"><i class="bi bi-trash"></i></button>' +
                '</span></div>' +
                '<div class="row g-2 mt-2" data-item-form="' + item.id + '" hidden>' +
                '<div class="col-md-3"><label class="form-label small fw-bold">Название</label><input class="form-control form-control-sm" data-edit-title value="' + escapeHtml(item.title) + '" maxlength="160"></div>' +
                '<div class="col-md-3"><label class="form-label small fw-bold">Ссылка</label><input class="form-control form-control-sm" data-edit-url value="' + escapeHtml(item.url) + '" maxlength="255"></div>' +
                '<div class="col-md-2"><label class="form-label small fw-bold">Иконка</label><input class="form-control form-control-sm" data-edit-icon value="' + escapeHtml(item.icon) + '" maxlength="80"></div>' +
                '<div class="col-md-2"><label class="form-label small fw-bold">CSS-классы</label><input class="form-control form-control-sm" data-edit-class value="' + escapeHtml(item.css_class) + '" maxlength="200"></div>' +
                '<div class="col-md-2 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" data-edit-active' + (active ? ' checked' : '') + '><label class="form-check-label small fw-bold">Активен</label></div></div>' +
                '<div class="col-12"><button type="button" class="btn btn-sm btn-orange fw-bold" data-item-save="' + item.id + '"><i class="bi bi-save"></i> Сохранить</button></div>' +
                '</div></div>';
        }

        function renderItems() {
            if (!currentItems.length) {
                itemsList.innerHTML = '<p class="text-muted fw-bold mb-0">В меню пока нет пунктов — добавьте первый ниже.</p>';
                itemsInfo.textContent = '';
                return;
            }
            fillParentOptions(0);
            var roots = currentItems.filter(function (item) { return !item.parent_id; });
            var html = '';
            roots.forEach(function (item) {
                html += itemRow(item, false);
                currentItems.filter(function (child) { return Number(child.parent_id) === Number(item.id); })
                    .forEach(function (child) {
                        html += itemRow(child, true);
                    });
            });
            itemsList.innerHTML = html;
            itemsInfo.textContent = '(' + currentItems.length + ')';
            bindDragAndDrop();
        }

        function bindDragAndDrop() {
            var dragged = null;
            itemsList.querySelectorAll('[data-item-row]').forEach(function (row) {
                row.addEventListener('dragstart', function (event) {
                    dragged = row;
                    row.classList.add('opacity-50');
                    event.dataTransfer.effectAllowed = 'move';
                });
                row.addEventListener('dragend', function () {
                    row.classList.remove('opacity-50');
                    dragged = null;
                });
                row.addEventListener('dragover', function (event) {
                    if (!dragged || dragged === row || dragged.dataset.parentId !== row.dataset.parentId) return;
                    event.preventDefault();
                    row.classList.add('border-orange');
                });
                row.addEventListener('dragleave', function () {
                    row.classList.remove('border-orange');
                });
                row.addEventListener('drop', function (event) {
                    event.preventDefault();
                    row.classList.remove('border-orange');
                    if (!dragged || dragged === row || dragged.dataset.parentId !== row.dataset.parentId) return;
                    var rows = Array.prototype.slice.call(itemsList.querySelectorAll('[data-item-row][data-parent-id="' + dragged.dataset.parentId + '"]'));
                    var from = rows.indexOf(dragged);
                    var to = rows.indexOf(row);
                    if (from < 0 || to < 0) return;
                    if (from < to) row.after(dragged); else row.before(dragged);
                    saveDraggedOrder(dragged.dataset.parentId);
                });
            });
        }

        function saveDraggedOrder(parentId) {
            var rows = Array.prototype.slice.call(itemsList.querySelectorAll('[data-item-row][data-parent-id="' + parentId + '"]'));
            window.AdminApi.save('/api/admin/menus', 'POST', {
                action: 'reorder',
                menu_name: menuSelect.value,
                parent_id: parentId === '' ? null : Number(parentId),
                item_ids: rows.map(function (row) { return Number(row.dataset.itemRow); })
            }).then(loadItems).then(function () {
                window.AdminNotifications.show(true, 'Порядок пунктов сохранён');
            }).catch(function (error) {
                showError(error.message || 'Не удалось сохранить порядок пунктов');
                loadItems();
            });
        }

        // ---------- Формы ----------
        function fillPicker() {
            var source = itemSource.value;
            itemPicker.textContent = '';
            pickers[source === 'page' ? 'pages' : source === 'category' ? 'categories' : 'articles']
                .forEach(function (entry) {
                    var option = document.createElement('option');
                    if (source === 'page') {
                        option.value = '/' + entry.slug + '.html';
                        option.textContent = (entry.title || entry.slug) + ' (/' + entry.slug + '.html)';
                    } else if (source === 'category') {
                        option.value = '/category/' + entry.slug + '.html';
                        option.textContent = entry.name + ' (' + entry.articles + ')';
                    } else {
                        option.value = '/' + entry.slug + '.html';
                        option.textContent = entry.title;
                    }
                    itemPicker.appendChild(option);
                });
        }

        function resetItemForm() {
            editingId = 0;
            itemTitle.value = '';
            itemUrl.value = '';
            itemIcon.value = '';
            itemClass.value = '';
            itemActive.checked = true;
            itemSource.value = 'link';
            itemPickerWrap.hidden = true;
            fillParentOptions(0);
            addItemButton.hidden = false;
            updateItemButton.hidden = true;
            cancelEditButton.hidden = true;
            showError('');
        }

        function saveItem() {
            var payload = {
                action: 'save',
                menu_name: menuSelect.value,
                id: editingId,
                parent_id: itemParent.value === '' ? null : Number(itemParent.value),
                title: itemTitle.value.trim(),
                url: itemUrl.value.trim() || '/',
                icon: itemIcon.value.trim(),
                css_class: itemClass.value.trim(),
                is_active: itemActive.checked ? 1 : 0
            };
            if (!payload.title) {
                showError('Укажите название пункта.');
                return;
            }
            window.AdminApi.save('/api/admin/menus', 'POST', payload).then(function () {
                resetItemForm();
                return loadMenus(menuSelect.value).then(loadItems);
            }).then(function () {
                window.AdminNotifications.show(true, 'Пункт меню сохранён');
            }).catch(function (error) {
                showError(error.message || 'Не удалось сохранить пункт');
            });
        }

        // ---------- События ----------
        menuSelect.addEventListener('change', function () {
            resetItemForm();
            loadItems();
        });

        itemSource.addEventListener('change', function () {
            var isLink = itemSource.value === 'link';
            itemPickerWrap.hidden = isLink;
            if (!isLink) {
                fillPicker();
                itemPicker.value = itemPicker.options.length ? itemPicker.options[0].value : '';
                syncFromPicker();
            }
        });

        itemPicker.addEventListener('change', syncFromPicker);

        function syncFromPicker() {
            if (itemPickerWrap.hidden || !itemPicker.value) return;
            var option = itemPicker.options[itemPicker.selectedIndex];
            itemTitle.value = option.textContent.replace(/\s*\(.*\)$/, '').trim();
            itemUrl.value = option.value;
        }

        addItemButton.addEventListener('click', saveItem);

        updateItemButton.addEventListener('click', saveItem);

        cancelEditButton.addEventListener('click', resetItemForm);

        itemsList.addEventListener('click', function (event) {
            var edit = event.target.closest('[data-item-edit]');
            if (edit) {
                editingId = Number(edit.dataset.itemEdit);
                var row = itemsList.querySelector('[data-item-row="' + editingId + '"]');
                fillParentOptions(editingId);
                var item = currentItems.find(function (item) { return Number(item.id) === editingId; });
                if (item) {
                    itemTitle.value = item.title;
                    itemUrl.value = item.url;
                    itemIcon.value = item.icon;
                    itemClass.value = item.css_class;
                    itemActive.checked = Number(item.is_active) === 1;
                    itemParent.value = item.parent_id || '';
                }
                addItemButton.hidden = true;
                updateItemButton.hidden = false;
                cancelEditButton.hidden = false;
                itemSource.value = 'link';
                itemPickerWrap.hidden = true;
                window.scrollTo({ top: itemsList.getBoundingClientRect().bottom + window.scrollY, behavior: 'smooth' });
                return;
            }

            var save = event.target.closest('[data-item-save]');
            if (save) {
                editingId = Number(save.dataset.itemSave);
                var form = itemsList.querySelector('[data-item-form="' + editingId + '"]');
                itemTitle.value = form.querySelector('[data-edit-title]').value;
                itemUrl.value = form.querySelector('[data-edit-url]').value;
                itemIcon.value = form.querySelector('[data-edit-icon]').value;
                itemClass.value = form.querySelector('[data-edit-class]').value;
                itemActive.checked = form.querySelector('[data-edit-active]').checked;
                itemParent.value = '';
                saveItem();
                return;
            }


            var remove = event.target.closest('[data-item-delete]');
            if (remove) {
                window.AdminApi.remove('/api/admin/menus/' + Number(remove.dataset.itemDelete) + '/delete-item').then(loadItems)
                    .then(function () {
                        window.AdminNotifications.show(true, 'Пункт удалён');
                    }).catch(function (error) {
                        showError(error.message || 'Не удалось удалить пункт');
                    });
            }
        });

        document.getElementById('createMenuButton').addEventListener('click', function () {
            var name = document.getElementById('newMenuName').value.trim();
            if (!name) {
                showError('Введите идентификатор нового меню.');
                return;
            }
            window.AdminApi.save('/api/admin/menus', 'POST', { action: 'create', menu_name: name })
                .then(function () {
                    document.getElementById('newMenuName').value = '';
                    return loadMenus(name).then(loadItems);
                }).then(function () {
                    window.AdminNotifications.show(true, 'Меню создано');
                }).catch(function (error) {
                    showError(error.message || 'Не удалось создать меню');
                });
        });

        document.getElementById('createPresetButton').addEventListener('click', function () {
            var name = document.getElementById('presetMenuName').value.trim();
            if (!name) {
                showError('Введите имя для нового меню из заготовки.');
                return;
            }
            window.AdminApi.save('/api/admin/menus', 'POST', {
                action: 'preset',
                menu_name: name,
                preset: document.getElementById('presetSelect').value
            }).then(function () {
                document.getElementById('presetMenuName').value = '';
                return loadMenus(name).then(loadItems);
            }).then(function () {
                window.AdminNotifications.show(true, 'Меню создано из заготовки');
            }).catch(function (error) {
                showError(error.message || 'Не удалось создать меню');
            });
        });

        document.getElementById('deleteMenuButton').addEventListener('click', function () {
            if (!menuSelect.value) return;
            if (menuSelect.value === 'main') {
                showError('Главное меню удалить нельзя.');
                return;
            }
            window.AdminApi.remove('/api/admin/menus/' + encodeURIComponent(menuSelect.value) + '/delete-menu')
                .then(function () {
                    return loadMenus().then(loadItems);
                }).then(function () {
                    window.AdminNotifications.show(true, 'Меню удалено');
                }).catch(function (error) {
                    showError(error.message || 'Не удалось удалить меню');
                });
        });

        loadMenus().then(loadItems).then(loadPickers).catch(function (error) {
            showError(error.message || 'Не удалось загрузить меню');
        });
    });
}());
