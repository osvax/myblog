(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var body = document.getElementById('currentOrdersTableBody');
        var pageInfo = document.getElementById('currentOrdersPageInfo');
        var pagination = document.getElementById('currentOrdersPagination');
        var errorBox = document.getElementById('currentOrdersError');
        var orders = [];
        var page = 1;
        var pageSize = 10;
        var statuses = ['Новый', 'В работе', 'Завершён', 'Отменён'];
        var sectionStatus = window.location.pathname === '/admin/new-orders.html' ? 'Новый' : 'В работе';
        var sectionName = sectionStatus === 'Новый' ? 'Новые заказы' : 'Текущие заказы';

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, function (char) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
            });
        }

        function formatMoney(value) {
            return Number(value).toLocaleString('ru-RU') + ' ₽';
        }

        function formatDate(value) {
            var parts = String(value || '').split('-');
            return parts.length === 3 ? parts[2] + '.' + parts[1] + '.' + parts[0] : '—';
        }

        function renderTable() {
            var list = orders.filter(function (order) { return order.status === sectionStatus; });
            var pageCount = Math.max(1, Math.ceil(list.length / pageSize));
            page = Math.min(page, pageCount);
            var start = (page - 1) * pageSize;
            var current = list.slice(start, start + pageSize);
            if (!current.length) {
                body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">' + sectionName + ' нет</td></tr>';
            } else {
                body.innerHTML = current.map(function (order) {
                    return '<tr><td>' + escapeHtml(order.clientName || order.client) + '</td>' +
                        '<td>' + escapeHtml(order.email) + '<br><small class="text-muted">' + escapeHtml(order.phone || '—') + '</small></td>' +
                        '<td>' + escapeHtml(order.projectType || order.type) + '</td><td>' + formatMoney(order.budget) + '</td>' +
                        '<td>' + escapeHtml(formatDate(order.deadline)) + '</td><td><select class="form-select form-select-sm" data-current-status="' +
                        order.id + '">' + statuses.map(function (status) {
                            return '<option' + (status === order.status ? ' selected' : '') + '>' + status + '</option>';
                        }).join('') + '</select></td><td class="text-end text-nowrap">' +
                        '<a class="btn btn-outline-secondary btn-action me-1" href="order-edit.html?id=' + order.id +
                        '" aria-label="Редактировать клиента ' + escapeHtml(order.clientName || order.client) + '"><i class="bi bi-pencil"></i></a>' +
                        '<button type="button" class="btn btn-danger btn-action" data-order-delete="' + order.id +
                        '" aria-label="Удалить заказ ' + escapeHtml(order.clientName || order.client) + '"><i class="bi bi-trash"></i></button></td></tr>';
                }).join('');
            }
            pageInfo.textContent = list.length
                ? 'Показано ' + (start + 1) + '–' + Math.min(start + pageSize, list.length) + ' из ' + list.length + ' заказов'
                : sectionName + ' нет';
            renderPagination(pageCount);
        }

        function renderPagination(pageCount) {
            pagination.textContent = '';
            if (pageCount <= 1) return;
            for (var index = 1; index <= pageCount; index++) {
                var item = document.createElement('li');
                item.className = 'page-item' + (index === page ? ' active' : '');
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'page-link';
                button.textContent = String(index);
                button.disabled = index === page;
                button.addEventListener('click', function (targetPage) {
                    return function () { page = targetPage; renderTable(); };
                }(index));
                item.appendChild(button);
                pagination.appendChild(item);
            }
        }

        body.addEventListener('change', function (event) {
            var select = event.target.closest('[data-current-status]');
            if (!select) return;
            var order = orders.find(function (item) { return String(item.id) === select.dataset.currentStatus; });
            if (!order) return;
            window.AdminApi.save('/api/admin/orders/' + encodeURIComponent(order.id), 'PATCH', { status: select.value })
                .then(function (result) {
                    Object.assign(order, result.order);
                    window.dispatchEvent(new Event('admin-orders-state-changed'));
                    renderTable();
                    window.AdminNotifications.show('success', 'Статус заказа сохранён');
                }).catch(function (error) {
                    select.value = order.status;
                    window.AdminNotifications.show('error', error.message || 'Не удалось сохранить статус заказа');
                });
        });

        body.addEventListener('click', function (event) {
            var button = event.target.closest('[data-order-delete]');
            if (!button) return;
            window.AdminApi.remove('/api/admin/orders/' + encodeURIComponent(button.dataset.orderDelete))
                .then(function () {
                    orders = orders.filter(function (order) { return String(order.id) !== button.dataset.orderDelete; });
                    window.dispatchEvent(new Event('admin-orders-state-changed'));
                    renderTable();
                    window.AdminNotifications.show('success', 'Заказ удалён');
                }).catch(function (error) {
                    window.AdminNotifications.show('error', error.message || 'Не удалось удалить заказ');
                });
        });

        window.AdminApi.get('/api/admin/orders').then(function (data) {
            orders = data.orders;
            renderTable();
        }).catch(function (error) {
            errorBox.textContent = error.message || 'Не удалось загрузить заказы';
            errorBox.hidden = false;
            body.textContent = '';
            var row = document.createElement('tr');
            var cell = document.createElement('td');
            cell.colSpan = 7;
            cell.className = 'text-center text-danger py-4';
            cell.textContent = errorBox.textContent;
            row.appendChild(cell);
            body.appendChild(row);
            window.AdminNotifications.show('error', errorBox.textContent);
        });
    });
}());
