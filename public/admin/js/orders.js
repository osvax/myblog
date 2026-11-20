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
        var CHART_WIDTH = 760;
        var CHART_HEIGHT = 265;
        var CHART_PAD = { top: 16, right: 16, bottom: 42, left: 76 };
        var chartGran = 'month';
        var chartZoomRange = null;
        var chartSelectState = null;
        var chartSelectRect = null;
        var lastChartPointCount = 0;
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

        var MONTH_NAMES = ['янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'];

        function makeBucketLabel(date, unit) {
            if (unit === 'year') return String(date.getFullYear());
            if (unit === 'month') return MONTH_NAMES[date.getMonth()] + ' ' + String(date.getFullYear()).slice(-2);
            return date.getDate() + ' ' + MONTH_NAMES[date.getMonth()];
        }

        function bucketIndex(createdAt, rangeStart, unit) {
            var date = new Date(createdAt + 'T12:00:00');
            if (unit === 'day') return Math.floor((date - rangeStart) / 86400000);
            if (unit === 'week') return Math.floor((date - rangeStart) / (7 * 86400000));
            if (unit === 'month') return (date.getFullYear() - rangeStart.getFullYear()) * 12 +
                date.getMonth() - rangeStart.getMonth();
            return date.getFullYear() - rangeStart.getFullYear();
        }

        // Суммы бюджетов заказов по периодам выбранной группировки
        function buildRevenueBuckets(periodOrders, from, to) {
            var rangeStart = new Date(from + 'T12:00:00');
            var rangeEnd = new Date(to + 'T12:00:00');
            var unit = chartGran;
            var days = Math.max(1, Math.floor((rangeEnd - rangeStart) / 86400000) + 1);
            var buckets = [];
            var index;
            if (unit === 'day') {
                for (index = 0; index < days; index++) {
                    buckets.push({
                        label: makeBucketLabel(new Date(rangeStart.getFullYear(), rangeStart.getMonth(), rangeStart.getDate() + index), unit),
                        total: 0
                    });
                }
            } else if (unit === 'week') {
                for (index = 0; index < Math.ceil(days / 7); index++) {
                    buckets.push({
                        label: makeBucketLabel(new Date(rangeStart.getFullYear(), rangeStart.getMonth(), rangeStart.getDate() + index * 7), unit),
                        total: 0
                    });
                }
            } else if (unit === 'month') {
                var cursor = new Date(rangeStart.getFullYear(), rangeStart.getMonth(), 1);
                while (cursor <= rangeEnd) {
                    buckets.push({ label: makeBucketLabel(cursor, unit), total: 0 });
                    cursor = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 1);
                }
            } else {
                for (index = rangeStart.getFullYear(); index <= rangeEnd.getFullYear(); index++) {
                    buckets.push({ label: makeBucketLabel(new Date(index, 0, 1), unit), total: 0 });
                }
            }

            var revenue = 0;
            periodOrders.forEach(function (order) {
                if (!order.createdAt) return;
                var amount = Number(order.budget) || 0;
                var bucket = buckets[bucketIndex(order.createdAt, rangeStart, unit)];
                if (bucket) {
                    bucket.total += amount;
                    revenue += amount;
                }
            });
            return { buckets: buckets, revenue: revenue };
        }

        function niceScale(maxValue) {
            if (maxValue <= 0) return { step: 250, max: 1000 };
            var magnitude = Math.pow(10, Math.floor(Math.log10(maxValue / 4)));
            var step = magnitude * 10;
            [1, 2, 2.5, 5].forEach(function (factor) {
                if (Math.ceil(maxValue / (magnitude * factor)) <= 6) step = magnitude * factor;
            });
            return { step: step, max: step * Math.ceil(maxValue / step) };
        }

        function renderLineChart(periodOrders) {
            var chartBox = document.getElementById('ordersLineChart');
            if (!periodOrders.length) {
                chartBox.innerHTML = '<div class="orders-chart-empty">За выбранный период заказов нет — график появится с первыми заявками.</div>';
                lastChartPointCount = 0;
                return 0;
            }
            var grouped = buildRevenueBuckets(periodOrders, dateFromInput.value, dateToInput.value);
            var allBuckets = grouped.buckets;
            var viewStart = 0;
            var viewEnd = allBuckets.length - 1;
            if (chartZoomRange) {
                viewStart = Math.max(0, Math.min(chartZoomRange.start, viewEnd));
                viewEnd = Math.max(viewStart, Math.min(chartZoomRange.end, viewEnd));
            }
            var points = allBuckets.slice(viewStart, viewEnd + 1);
            lastChartPointCount = points.length;
            var scale = niceScale(points.reduce(function (max, point) { return Math.max(max, point.total); }, 0));
            var width = CHART_WIDTH;
            var height = CHART_HEIGHT;
            var pad = CHART_PAD;
            var plotWidth = width - pad.left - pad.right;
            var plotHeight = height - pad.top - pad.bottom;
            var slot = points.length > 1 ? plotWidth / (points.length - 1) : plotWidth;
            var grid = '';
            var linesCount = Math.round(scale.max / scale.step);
            for (var tick = 0; tick <= linesCount; tick++) {
                var gridValue = tick * scale.step;
                var gridY = pad.top + plotHeight - gridValue / scale.max * plotHeight;
                grid += '<line x1="' + pad.left + '" y1="' + gridY + '" x2="' + (width - pad.right) +
                    '" y2="' + gridY + '" stroke="#e9ecef"></line>' +
                    '<text x="' + (pad.left - 8) + '" y="' + (gridY + 4) +
                    '" text-anchor="end" fill="#6c757d" font-size="11">' + gridValue.toLocaleString('ru-RU') + '</text>';
            }
            var coordinates = points.map(function (point, index) {
                var x = points.length === 1 ? pad.left + plotWidth / 2 : pad.left + index * slot;
                return {
                    x: x,
                    y: pad.top + plotHeight - point.total / scale.max * plotHeight,
                    point: point
                };
            });
            var line = coordinates.map(function (point, index) {
                return (index ? 'L' : 'M') + point.x.toFixed(1) + ',' + point.y.toFixed(1);
            }).join(' ');
            var dotRadius = points.length <= 48 ? 3.5 : points.length <= 120 ? 2 : 0;
            var hitWidth = Math.max(6, Math.min(slot * 0.5, 18));
            var markers = coordinates.map(function (point) {
                var marker = '<rect x="' + (point.x - hitWidth / 2).toFixed(1) + '" y="' + pad.top +
                    '" width="' + hitWidth.toFixed(1) + '" height="' + plotHeight +
                    '" fill="transparent"><title>' + escapeHtml(point.point.label) + ': ' +
                    formatMoney(point.point.total) + '</title></rect>';
                if (dotRadius > 0) {
                    marker += '<circle cx="' + point.x.toFixed(1) + '" cy="' + point.y.toFixed(1) +
                        '" r="' + dotRadius + '" fill="#606060"></circle>';
                }
                return marker;
            }).join('');
            var labelStride = Math.max(1, Math.ceil(points.length / 10));
            var xLabels = coordinates.map(function (point, index) {
                if (index % labelStride !== 0 && index !== points.length - 1) return '';
                return '<text x="' + point.x.toFixed(1) + '" y="' + (height - 12) +
                    '" text-anchor="middle" fill="#6c757d" font-size="11">' + escapeHtml(point.point.label) + '</text>';
            }).join('');
            chartBox.innerHTML =
                '<svg viewBox="0 0 ' + width + ' ' + height + '" role="img" aria-label="Оборот заказов по периодам">' +
                grid +
                '<path d="' + line + '" fill="none" stroke="#ff8c1a" stroke-width="3" ' +
                'stroke-linecap="round" stroke-linejoin="round"></path>' + markers + xLabels + '</svg>';
            return grouped.revenue;
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
            var revenue = renderLineChart(periodOrders);
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
            chartZoomRange = null;
            updateZoomReset();
            render();
        }

        function render() {
            renderTable();
            renderAnalytics();
        }

        var lineChart = document.getElementById('ordersLineChart');
        var chartResetBtn = document.getElementById('ordersZoomReset');
        var granButtons = document.querySelectorAll('.orders-gran-btn[data-orders-gran]');

        function updateZoomReset() {
            if (chartResetBtn) chartResetBtn.hidden = !chartZoomRange;
        }

        // Переключатели группировки: дни/недели/месяцы/годы
        granButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                if (chartGran === button.dataset.ordersGran) return;
                chartGran = button.dataset.ordersGran;
                chartZoomRange = null;
                granButtons.forEach(function (other) {
                    other.classList.toggle('active', other === button);
                });
                updateZoomReset();
                renderAnalytics();
            });
        });

        if (chartResetBtn) {
            chartResetBtn.addEventListener('click', function () {
                chartZoomRange = null;
                updateZoomReset();
                renderAnalytics();
            });
        }

        // Зум: выделение области мышью
        function clientToChartX(clientX) {
            var svg = lineChart.querySelector('svg');
            if (!svg) return null;
            var rect = svg.getBoundingClientRect();
            if (!rect.width) return null;
            return (clientX - rect.left) / rect.width * CHART_WIDTH;
        }

        function cleanupSelection() {
            if (chartSelectRect && chartSelectRect.parentNode) {
                chartSelectRect.parentNode.removeChild(chartSelectRect);
            }
            chartSelectRect = null;
        }

        lineChart.addEventListener('pointerdown', function (event) {
            if (event.button !== 0 || !lineChart.querySelector('svg')) return;
            chartSelectState = { pointerId: event.pointerId, startClient: event.clientX, moved: false };
            lineChart.setPointerCapture(event.pointerId);
            lineChart.classList.add('is-dragging');
            chartSelectRect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            chartSelectRect.setAttribute('fill', 'rgba(255, 140, 26, 0.12)');
            chartSelectRect.setAttribute('stroke', '#ff8c1a');
            chartSelectRect.setAttribute('stroke-dasharray', '4 3');
            chartSelectRect.setAttribute('y', String(CHART_PAD.top));
            chartSelectRect.setAttribute('height', String(CHART_HEIGHT - CHART_PAD.top - CHART_PAD.bottom));
            chartSelectRect.setAttribute('x', '0');
            chartSelectRect.setAttribute('width', '0');
            lineChart.querySelector('svg').appendChild(chartSelectRect);
            event.preventDefault();
        });
        lineChart.addEventListener('pointermove', function (event) {
            if (!chartSelectState || chartSelectState.pointerId !== event.pointerId) return;
            var startClient = chartSelectState.startClient;
            var currentClient = event.clientX;
            if (Math.abs(currentClient - startClient) > 3) chartSelectState.moved = true;
            if (!chartSelectRect) return;
            var x1 = clientToChartX(startClient);
            var x2 = clientToChartX(currentClient);
            if (x1 === null || x2 === null) return;
            chartSelectRect.setAttribute('x', String(Math.min(x1, x2)));
            chartSelectRect.setAttribute('width', String(Math.abs(x2 - x1)));
        });
        function finishSelection(event) {
            if (!chartSelectState || chartSelectState.pointerId !== event.pointerId) return;
            var wasMoved = chartSelectState.moved;
            if (lineChart.hasPointerCapture(event.pointerId)) {
                lineChart.releasePointerCapture(event.pointerId);
            }
            chartSelectState = null;
            lineChart.classList.remove('is-dragging');
            if (!chartSelectRect || !wasMoved || lastChartPointCount < 2) {
                cleanupSelection();
                return;
            }
            var selectionX = Number(chartSelectRect.getAttribute('x')) || 0;
            var selectionWidth = Number(chartSelectRect.getAttribute('width')) || 0;
            cleanupSelection();
            if (selectionWidth < 8) return;
            var plotWidth = CHART_WIDTH - CHART_PAD.left - CHART_PAD.right;
            var slot = plotWidth / (lastChartPointCount - 1);
            var start = Math.max(0, Math.min(lastChartPointCount - 1,
                Math.round((selectionX - CHART_PAD.left) / slot)));
            var end = Math.max(start, Math.min(lastChartPointCount - 1,
                Math.round((selectionX + selectionWidth - CHART_PAD.left) / slot)));
            if (end - start < 1) return;
            chartZoomRange = (start === 0 && end === lastChartPointCount - 1)
                ? null
                : { start: start, end: end };
            updateZoomReset();
            renderAnalytics();
        }
        lineChart.addEventListener('pointerup', finishSelection);
        lineChart.addEventListener('pointercancel', finishSelection);
        lineChart.addEventListener('lostpointercapture', function (event) {
            if (chartSelectState && chartSelectState.pointerId === event.pointerId) {
                chartSelectState = null;
                lineChart.classList.remove('is-dragging');
                cleanupSelection();
            }
        });

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
