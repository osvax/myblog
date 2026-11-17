(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var tableBody = document.getElementById('ordersTableBody');
        var pagination = document.getElementById('ordersPagination');
        var pageInfo = document.getElementById('ordersPageInfo');
        var dateFromInput = document.getElementById('ordersDateFrom');
        var dateToInput = document.getElementById('ordersDateTo');
        var exportButton = document.getElementById('exportOrders');
        var errorBox = document.getElementById('ordersError');
        var orders = [];
        var page = 1;
        var pageSize = 10;
        var chartZoom = 1;
        var chartZoomMin = 0.6;
        var chartZoomMax = 3.5;
        var chartPan = 0;
        var chartDragState = null;
        var statuses = ['Новый', 'В работе', 'Завершён', 'Отменён'];
        var statusColors = {
            'Новый': '#ff8c1a',
            'В работе': '#0d6efd',
            'Завершён': '#198754',
            'Отменён': '#6c757d'
        };
        var state = { status: {}, deleted: [], clients: {} };
        var searchFilter = '';

        function escapeHtml(value) {
            return String(value).replace(/[&<>\"']/g, function (char) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
            });
        }

        function formatMoney(value) {
            return Number(value).toLocaleString('ru-RU') + ' ₽';
        }

        function formatDate(value) {
            var parts = value.split('-');
            return parts.length === 3 ? parts[2] + '.' + parts[1] + '.' + parts[0] : value;
        }

        function toDateInputValue(date) {
            return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') +
                '-' + String(date.getDate()).padStart(2, '0');
        }

        // Поиск по клиенту/email/телефону
        function getFilteredOrders() {
            var q = (searchFilter || '').toLowerCase().trim();
            if (!q) return orders;
            return orders.filter(function (o) {
                var text = ((o.client || '') + ' ' + (o.email || '') + ' ' + (o.phone || '')).toLowerCase();
                return text.indexOf(q) !== -1;
            });
        }

        // Заказы за выбранный период (для графиков)
        function getOrdersByDateRange() {
            var from = dateFromInput.value;
            var to = dateToInput.value;
            return orders.filter(function (order) {
                return order.createdAt >= from && order.createdAt <= to;
            });
        }

        function bindOrdersSearch() {
            var searchInput = document.getElementById('ordersSearchInput');
            var clearBtn = document.querySelector('#ordersFilterBar .filter-bar-clear');
            var countEl = document.getElementById('ordersFilterCount');
            if (!searchInput) return;

            function updateClear() {
                if (clearBtn) clearBtn.hidden = !searchFilter;
            }

            var timer = null;
            searchInput.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    searchFilter = searchInput.value;
                    page = 1;
                    renderTable();
                    updateClear();
                }, 200);
            });

            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    searchInput.value = '';
                    searchFilter = '';
                    page = 1;
                    renderTable();
                    updateClear();
                    searchInput.focus();
                });
            }
        }

        function renderTable() {
            var visible = getFilteredOrders();
            var pageCount = Math.max(1, Math.ceil(visible.length / pageSize));
            page = Math.min(page, pageCount);
            var start = (page - 1) * pageSize;
            var pageOrders = visible.slice(start, start + pageSize);

            tableBody.innerHTML = pageOrders.map(function (order) {
                var client = order.clientName || order.client;
                var email = order.email;
                var phone = order.phone;
                var type = order.projectType || order.type;
                var budget = order.budget !== undefined ? order.budget : 0;
                var deadline = order.deadline || '';
                var status = order.status;
                return '<tr data-order-row="' + order.id + '">' +
                    '<td>' + escapeHtml(client) + '</td>' +
                    '<td>' + escapeHtml(email) + '<br><small class="text-muted">' +
                    escapeHtml(phone || '—') + '</small></td>' +
                    '<td>' + escapeHtml(type) + '</td>' +
                    '<td>' + formatMoney(budget) + '</td>' +
                    '<td>' + escapeHtml(formatDate(deadline)) + '</td>' +
                    '<td><select class="form-select form-select-sm order-status" data-order-status="' + order.id + '">' +
                    statuses.map(function (item) {
                        return '<option' + (item === status ? ' selected' : '') + '>' + item + '</option>';
                    }).join('') + '</select></td>' +
                    '<td class="text-end text-nowrap">' +
                    '<a class="btn btn-outline-secondary btn-action me-1" href="order-edit.html?id=' + order.id +
                    '" aria-label="Редактировать клиента ' + escapeHtml(client || '') + '"><i class="bi bi-pencil"></i></a>' +
                    '<button type="button" class="btn btn-danger btn-action" data-order-delete="' +
                    order.id + '" aria-label="Удалить заказ ' + escapeHtml(client || '') + '">' +
                    '<i class="bi bi-trash"></i></button></td></tr>';
            }).join('');

            pageInfo.textContent = visible.length
                ? 'Показано ' + (start + 1) + '–' + Math.min(start + pageSize, visible.length) +
                    ' из ' + visible.length + ' заказов'
                : 'Нет заказов';
            renderPagination(pageCount);
        }

        function renderPagination(pageCount) {
            pagination.innerHTML = '';
            if (pageCount <= 1) return;

            function addPage(label, target, disabled, current, ariaLabel) {
                var item = document.createElement('li');
                item.className = 'page-item' + (disabled ? ' disabled' : '') + (current ? ' active' : '');
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'page-link';
                button.textContent = label;
                button.disabled = disabled || current;
                button.setAttribute('aria-label', ariaLabel || label);
                if (current) button.setAttribute('aria-current', 'page');
                button.addEventListener('click', function () {
                    page = target;
                    renderTable();
                });
                item.appendChild(button);
                pagination.appendChild(item);
            }

            addPage('‹', page - 1, page === 1, false, 'Предыдущая страница');
            for (var index = 1; index <= pageCount; index++) {
                addPage(String(index), index, false, index === page, 'Страница ' + index);
            }
            addPage('›', page + 1, page === pageCount, false, 'Следующая страница');
        }

        function makeBuckets(from, to) {
            var start = new Date(from + 'T12:00:00');
            var end = new Date(to + 'T12:00:00');
            var days = Math.floor((end - start) / 86400000) + 1;
            var unit = days <= 62 ? 'day' : days <= 280 ? 'week' : days <= 1095 ? 'month' : 'year';
            var count = unit === 'day' ? days : unit === 'week' ? Math.ceil(days / 7) :
                unit === 'month' ? (end.getFullYear() - start.getFullYear()) * 12 + end.getMonth() - start.getMonth() + 1 :
                    end.getFullYear() - start.getFullYear() + 1;
            var buckets = [];
            for (var index = 0; index < count; index++) {
                var date = new Date(start.getFullYear(), start.getMonth(), start.getDate());
                if (unit === 'day') date.setDate(date.getDate() + index);
                else if (unit === 'week') date.setDate(date.getDate() + index * 7);
                else if (unit === 'month') date = new Date(start.getFullYear(), start.getMonth() + index, 1);
                else date = new Date(start.getFullYear() + index, 0, 1);
                buckets.push({
                    label: unit === 'year' ? String(date.getFullYear()) :
                        date.toLocaleDateString('ru-RU', unit === 'month'
                            ? { month: 'short', year: '2-digit' }
                            : { day: 'numeric', month: 'short' }),
                    total: 0
                });
            }
            return {
                buckets: buckets,
                unit: unit,
                start: start,
                indexFor: function (date) {
                    if (unit === 'day') return Math.floor((date - start) / 86400000);
                    if (unit === 'week') return Math.floor((date - start) / (7 * 86400000));
                    if (unit === 'month') return (date.getFullYear() - start.getFullYear()) * 12 +
                        date.getMonth() - start.getMonth();
                    return date.getFullYear() - start.getFullYear();
                }
            };
        }

        function renderLineChart(periodOrders, from, to) {
            var grouped = makeBuckets(from, to);
            var revenue = 0;
            periodOrders.forEach(function (order) {
                if (!order.createdAt) return;
                var created = new Date(order.createdAt + 'T12:00:00');
                var bucket = grouped.buckets[grouped.indexFor(created)];
                if (bucket) {
                    bucket.total += Number(order.budget) || 0;
                    revenue += Number(order.budget) || 0;
                }
            });

            var points = grouped.buckets;
            var width = 760;
            var height = 265;
            var padding = { top: 16, right: 16, bottom: 42, left: 76 };
            var plotWidth = width - padding.left - padding.right;
            var plotHeight = height - padding.top - padding.bottom;
            var maximum = Math.max.apply(null, points.map(function (point) { return point.total; }));
            var step = maximum > 0 ? Math.pow(10, Math.floor(Math.log10(maximum))) / 2 : 50000;
            var axisMax = Math.max(step, Math.ceil(maximum / step) * step);
            var coordinates = points.map(function (point, index) {
                var baseX = points.length === 1 ? padding.left + plotWidth / 2 :
                    padding.left + index * plotWidth / (points.length - 1);
                return {
                    x: padding.left + plotWidth / 2 + (baseX - padding.left - plotWidth / 2) * chartZoom +
                        chartPan,
                    y: padding.top + plotHeight - point.total / axisMax * plotHeight,
                    point: point
                };
            });
            var grid = [];
            for (var tick = 0; tick <= 4; tick++) {
                var value = axisMax * tick / 4;
                var y = padding.top + plotHeight - tick / 4 * plotHeight;
                grid.push('<line x1="' + padding.left + '" y1="' + y + '" x2="' + (width - padding.right) +
                    '" y2="' + y + '" stroke="#e9ecef"></line><text x="' + (padding.left - 8) +
                    '" y="' + (y + 4) + '" text-anchor="end" fill="#6c757d" font-size="11">' +
                    Math.round(value).toLocaleString('ru-RU') + '</text>');
            }
            var line = coordinates.map(function (point, index) {
                return (index ? 'L' : 'M') + point.x + ',' + point.y;
            }).join(' ');
            var markers = coordinates.map(function (point, index) {
                var marker = '<circle cx="' + point.x + '" cy="' + point.y + '" r="3.5" fill="#606060">' +
                    '<title>' + point.point.label + ': ' + formatMoney(point.point.total) + '</title></circle>';
                var labelInterval = Math.max(1, Math.ceil(coordinates.length / 10));
                if (index % labelInterval === 0 || index === coordinates.length - 1) {
                    marker += '<text x="' + point.x + '" y="' + (height - 12) +
                        '" text-anchor="middle" fill="#6c757d" font-size="11">' +
                        escapeHtml(point.point.label) + '</text>';
                }
                return marker;
            }).join('');

            document.getElementById('ordersLineChart').innerHTML =
                '<svg viewBox="0 0 ' + width + ' ' + height + '" role="img" aria-label="Оборот заказов: ' +
                formatMoney(revenue) + '">' + grid.join('') +
                '<path d="' + line + '" fill="none" stroke="#ff8c1a" stroke-width="3" ' +
                'stroke-linecap="round" stroke-linejoin="round"></path>' + markers + '</svg>';
            return revenue;
        }

        function renderStatusChart(periodOrders) {
            var counts = {};
            statuses.forEach(function (status) { counts[status] = 0; });
            periodOrders.forEach(function (order) {
                var status = order.status;
                counts[status] = (counts[status] || 0) + 1;
            });
            var total = periodOrders.length;
            var cursor = 0;
            var segments = statuses.map(function (status) {
                var start = cursor;
                cursor += total ? counts[status] / total * 100 : 0;
                return statusColors[status] + ' ' + start + '% ' + cursor + '%';
            });
            var background = total ? 'conic-gradient(' + segments.join(', ') + ')' : '#e9ecef';
            document.getElementById('ordersStatusChart').innerHTML =
                '<div class="orders-donut" style="--orders-donut-background:' + background +
                '" role="img" aria-label="Всего заказов за период: ' + total + '">' +
                '<span><strong>' + total + '</strong><small>заказов</small></span></div>';
            document.getElementById('ordersStatusLegend').innerHTML = statuses.map(function (status) {
                return '<div class="d-flex align-items-center gap-2 mb-1"><span class="orders-legend-dot" style="background:' +
                    statusColors[status] + '"></span><span>' + status + '</span><strong class="ms-auto">' +
                    (counts[status] || 0) + '</strong></div>';
            }).join('');
        }

        function renderAnalytics() {
            var periodOrders = getOrdersByDateRange();
            var revenue = renderLineChart(periodOrders, dateFromInput.value, dateToInput.value);
            renderStatusChart(periodOrders);
            document.getElementById('ordersAnalyticsSummary').textContent =
                'За ' + formatDate(dateFromInput.value) + ' — ' + formatDate(dateToInput.value) +
                ': ' + periodOrders.length + ' заказов, общий бюджет заявок — ' + formatMoney(revenue) + '.';
        }

        function applyDateFilter() {
            errorBox.hidden = true;
            if (!dateFromInput.value || !dateToInput.value) {
                errorBox.textContent = 'Укажите начальную и конечную даты.';
                errorBox.hidden = false;
                return;
            }
            if (dateFromInput.value > dateToInput.value) {
                errorBox.textContent = 'Начальная дата не может быть позже конечной.';
                errorBox.hidden = false;
                return;
            }
            page = 1;
            chartZoom = 1;
            chartPan = 0;
            render();
        }

        function render() {
            renderTable();
            renderAnalytics();
        }

        var lineChart = document.getElementById('ordersLineChart');
        lineChart.addEventListener('wheel', function (event) {
            if (!orders.length) return;
            event.preventDefault();
            var zoomFactor = event.deltaY < 0 ? 1.15 : 1 / 1.15;
            chartZoom = Math.min(chartZoomMax, Math.max(chartZoomMin, chartZoom * zoomFactor));
            chartPan = 0;
            renderAnalytics();
        }, { passive: false });
        lineChart.addEventListener('pointerdown', function (event) {
            if (event.button !== 0 || chartZoom <= 1) return;
            chartDragState = { pointerId: event.pointerId, x: event.clientX, pan: chartPan };
            lineChart.setPointerCapture(event.pointerId);
            lineChart.classList.add('is-dragging');
            event.preventDefault();
        });
        lineChart.addEventListener('pointermove', function (event) {
            if (!chartDragState || chartDragState.pointerId !== event.pointerId) return;
            var plotWidth = 668;
            var maxPan = plotWidth * (chartZoom - 1) / 2;
            var dragDistance = (event.clientX - chartDragState.x) * 760 / lineChart.clientWidth;
            chartPan = Math.max(-maxPan, Math.min(maxPan,
                chartDragState.pan + dragDistance));
            renderAnalytics();
        });
        function stopChartDrag(event) {
            if (!chartDragState || (event && chartDragState.pointerId !== event.pointerId)) return;
            if (event && lineChart.hasPointerCapture(event.pointerId)) {
                lineChart.releasePointerCapture(event.pointerId);
            }
            chartDragState = null;
            lineChart.classList.remove('is-dragging');
        }
        lineChart.addEventListener('pointerup', stopChartDrag);
        lineChart.addEventListener('pointercancel', stopChartDrag);
        lineChart.addEventListener('lostpointercapture', stopChartDrag);

        dateFromInput.addEventListener('change', applyDateFilter);
        dateToInput.addEventListener('change', applyDateFilter);
        tableBody.addEventListener('change', function (event) {
            var select = event.target.closest('[data-order-status]');
            if (!select) return;
            var order = orders.find(function (item) { return String(item.id) === select.dataset.orderStatus; });
            if (!order) return;
            window.AdminApi.save('/api/admin/orders/' + encodeURIComponent(order.id), 'PATCH', { status: select.value })
                .then(function (result) {
                    Object.assign(order, result.order);
                    window.dispatchEvent(new Event('admin-orders-state-changed'));
                    page = 1;
                    renderTable();
                    renderAnalytics();
                    window.AdminNotifications.show('success', 'Статус заказа сохранён');
                }).catch(function (error) {
                    select.value = order.status;
                    window.AdminNotifications.show('error', error.message || 'Не удалось сохранить статус заказа');
                });
        });
        tableBody.addEventListener('click', function (event) {
            var button = event.target.closest('[data-order-delete]');
            if (!button) return;
            if (!window.confirm('Удалить заказ?')) return;
            window.AdminApi.remove('/api/admin/orders/' + encodeURIComponent(button.dataset.orderDelete))
                .then(function () {
                    orders = orders.filter(function (item) {
                        return String(item.id) !== button.dataset.orderDelete;
                    });
                    window.dispatchEvent(new Event('admin-orders-state-changed'));
                    page = Math.min(page, Math.max(1, Math.ceil(orders.length / pageSize)));
                    render();
                    window.AdminNotifications.show('success', 'Заказ удалён');
                }).catch(function (error) {
                    window.AdminNotifications.show('error', error.message || 'Не удалось удалить заказ');
                });
        });

        exportButton.addEventListener('click', function () {
            if (!dateFromInput.value || !dateToInput.value || dateFromInput.value > dateToInput.value) {
                applyDateFilter();
                window.AdminNotifications.show('error', 'Выберите корректный диапазон дат для экспорта');
                return;
            }
            exportButton.disabled = true;
            window.AdminApi.request('/api/admin/orders/export', {
                method: 'POST',
                body: { from: dateFromInput.value, to: dateToInput.value }
            }).then(function (response) {
                var disposition = response.headers.get('Content-Disposition') || '';
                var match = disposition.match(/filename="([^"]+)"/);
                return response.blob().then(function (blob) {
                    return { blob: blob, filename: match ? match[1] : 'orders.xls' };
                });
            }).then(function (file) {
                var url = URL.createObjectURL(file.blob);
                var link = document.createElement('a');
                link.href = url;
                link.download = file.filename;
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(url);
                window.AdminNotifications.show('success', 'Excel-файл заказов скачан');
            }).catch(function (error) {
                window.AdminNotifications.show('error', error.message || 'Не удалось скачать Excel-файл');
            }).finally(function () {
                exportButton.disabled = false;
            });
        });

        window.AdminApi.get('/api/admin/orders').then(function (data) {
            orders = data.orders;
            var today = new Date();
            var yearAgo = new Date(today.getFullYear(), today.getMonth(), today.getDate());
            yearAgo.setFullYear(yearAgo.getFullYear() - 1);
            var todayValue = toDateInputValue(today);
            dateFromInput.max = todayValue;
            dateToInput.max = todayValue;
            dateFromInput.value = toDateInputValue(yearAgo);
            dateToInput.value = todayValue;
            bindOrdersSearch();
            render();
        }).catch(function (error) {
            errorBox.textContent = error.message || 'Не удалось загрузить заказы';
            errorBox.hidden = false;
            window.AdminNotifications.show('error', errorBox.textContent);
        });
    });
}());
