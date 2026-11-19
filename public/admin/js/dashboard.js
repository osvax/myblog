(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var clock = document.getElementById('dashboardClock');
        var date = document.getElementById('dashboardDate');
        var timezoneLabel = document.getElementById('dashboardTimezone');
        var chart = document.getElementById('salesChart');
        var systemStatus = document.getElementById('systemInfoStatus');
        var systemError = document.getElementById('systemInfoError');

        function updateClock() {
            var now = new Date();
            clock.textContent = now.toLocaleTimeString('ru-RU', { hour12: false });
            date.textContent = now.toLocaleDateString('ru-RU', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
            timezoneLabel.textContent = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
            clock.dateTime = now.toISOString();
            date.dateTime = now.toISOString();
        }

        // График продаж строится из реальных заказов базы данных
        function renderSalesChart(orders) {
            var monthNames = [];
            var monthKeys = [];
            var currentMonth = new Date();
            for (var monthOffset = 5; monthOffset >= 0; monthOffset--) {
                var monthDate = new Date(currentMonth.getFullYear(), currentMonth.getMonth() - monthOffset, 1);
                monthNames.push(monthDate.toLocaleDateString('ru-RU', { month: 'short' }).replace('.', ''));
                monthKeys.push(monthDate.getFullYear() + '-' + String(monthDate.getMonth() + 1).padStart(2, '0'));
            }

            // Суммируем бюджеты заказов по месяцам
            var totals = {};
            monthKeys.forEach(function (key) { totals[key] = 0; });
            (orders || []).forEach(function (order) {
                var key = String(order.orderDate || order.order_date || '').substring(0, 7);
                if (Object.prototype.hasOwnProperty.call(totals, key)) {
                    totals[key] += Number(order.budget) || 0;
                }
            });
            var points = monthKeys.map(function (key, index) {
                return { label: monthNames[index], value: Math.round(totals[key]) };
            });

            var totalRevenue = points.reduce(function (sum, point) { return sum + point.value; }, 0);
            var emptyBox = document.getElementById('salesChartEmpty');
            if (emptyBox) emptyBox.hidden = totalRevenue > 0;
            if (totalRevenue === 0) {
                chart.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-bar-chart-line fs-1"></i></div>';
                return;
            }

            // Подбираем красивый шаг шкалы под максимум
            var maxValue = Math.max.apply(null, points.map(function (point) { return point.value; }));
            var steps = [1000, 2000, 5000, 10000, 20000, 50000, 100000, 200000, 500000, 1000000];
            var step = steps.find(function (candidate) { return maxValue / candidate <= 5; }) || 1000000;
            var max = Math.ceil(maxValue / step) * step;
            var width = 560;
            var height = 220;
            var padding = { top: 16, right: 12, bottom: 34, left: 58 };
            var plotWidth = width - padding.left - padding.right;
            var plotHeight = height - padding.top - padding.bottom;
            var coordinates = points.map(function (point, index) {
                return {
                    x: padding.left + index * plotWidth / (points.length - 1),
                    y: padding.top + plotHeight - point.value / max * plotHeight,
                    point: point
                };
            });
            var grid = [];
            for (var tick = 0; tick <= max; tick += step) {
                var y = padding.top + plotHeight - tick / max * plotHeight;
                grid.push('<line x1="' + padding.left + '" y1="' + y + '" x2="' + (width - padding.right) +
                    '" y2="' + y + '" stroke="#e9ecef" />');
                grid.push('<text x="' + (padding.left - 8) + '" y="' + (y + 4) +
                    '" text-anchor="end" fill="#6c757d" font-size="11">' +
                    tick.toLocaleString('ru-RU') + '</text>');
            }
            var line = coordinates.map(function (point, index) {
                return (index ? 'L' : 'M') + point.x + ',' + point.y;
            }).join(' ');
            var area = line + ' L' + coordinates[coordinates.length - 1].x + ',' +
                (padding.top + plotHeight) + ' L' + coordinates[0].x + ',' +
                (padding.top + plotHeight) + ' Z';
            var markers = coordinates.map(function (point) {
                return '<circle cx="' + point.x + '" cy="' + point.y + '" r="4" fill="#ff8c1a">' +
                    '<title>' + point.point.label + ': ' + point.point.value.toLocaleString('ru-RU') + ' ₽</title></circle>' +
                    '<text x="' + point.x + '" y="' + (height - 10) +
                    '" text-anchor="middle" fill="#6c757d" font-size="12">' + point.point.label + '</text>';
            }).join('');

            chart.innerHTML = '<svg viewBox="0 0 ' + width + ' ' + height +
                '" role="img" aria-label="Выручка по заказам за последние шесть месяцев">' +
                grid.join('') + '<path d="' + area + '" fill="#ff8c1a" fill-opacity=".12"></path>' +
                '<path d="' + line + '" fill="none" stroke="#ff8c1a" stroke-width="3" ' +
                'stroke-linecap="round" stroke-linejoin="round"></path>' + markers + '</svg>';
        }

        function setSystemInfo(data) {
            document.getElementById('systemWebServer').textContent = data.webServer;
            document.getElementById('systemPhpVersion').textContent = data.phpVersion;
            document.getElementById('systemOs').textContent = data.os;
            document.getElementById('systemTimezone').textContent = data.timezone;
            document.getElementById('systemUploadLimit').textContent = data.uploadLimit;
            document.getElementById('systemMemoryLimit').textContent = data.memoryLimit;
            systemStatus.textContent = 'Работает';
            systemStatus.className = 'badge bg-success';
        }

        function updateOrderCounts(orders) {
            var newCount = orders.filter(function (order) { return order.status === 'Новый'; }).length;
            var inProgressCount = orders.filter(function (order) { return order.status === 'В работе'; }).length;
            document.getElementById('dashboardNewOrdersValue').textContent = String(newCount);
            document.getElementById('dashboardCurrentOrdersCount').textContent = String(inProgressCount);
            var badge = document.getElementById('dashboardNewOrdersCount');
            badge.textContent = newCount > 99 ? '99+' : String(newCount);
            badge.setAttribute('aria-label', newCount + ' новых заказов');
            badge.hidden = newCount === 0;
            document.querySelector('.dashboard-new-orders-icon').classList.toggle('has-new-orders', newCount > 0);
        }

        // Пользователи — из базы (раньше брались из localStorage)
        function updateUserCount() {
            window.AdminApi.get('/api/admin/users').then(function (data) {
                document.getElementById('dashboardUsersCount').textContent = String((data.users || []).length);
            }).catch(function (error) {
                console.error('Не удалось получить количество пользователей.', error);
                document.getElementById('dashboardUsersCount').textContent = '—';
            });
        }

        function updateCommentCount() {
            window.AdminApi.get('/api/admin/messages').then(function (data) {
                var commentCount = data.messages.filter(function (message) {
                    return message.message_type === 'comment';
                }).length;
                document.getElementById('dashboardCommentsCount').textContent = String(commentCount);
            }).catch(function (error) {
                console.error('Не удалось обновить количество комментариев.', error);
                document.getElementById('dashboardCommentsCount').textContent = '—';
            });
        }

        function loadOrderCounts() {
            window.AdminApi.get('/api/admin/orders').then(function (data) {
                updateOrderCounts(data.orders);
                renderSalesChart(data.orders);
            }).catch(function (error) {
                console.error('Не удалось обновить счётчики заказов.', error);
                document.getElementById('dashboardNewOrdersValue').textContent = '—';
                document.getElementById('dashboardCurrentOrdersCount').textContent = '—';
            });
        }

        // ---------- Трафик сайта ----------
        var BROWSERS = {
            chrome: { icon: 'bi-browser-chrome', label: 'Chrome' },
            edge: { icon: 'bi-browser-edge', label: 'Edge' },
            firefox: { icon: 'bi-browser-firefox', label: 'Firefox' },
            safari: { icon: 'bi-browser-safari', label: 'Safari' },
            opera: { icon: 'bi-globe2', label: 'Opera' },
            other: { icon: 'bi-globe2', label: 'Браузер' }
        };

        // Фильтры и пагинация трафика: значения читаются из полей формы
        var trafficPage = 1;
        var trafficPages = 1;
        var trafficFilterFields = {
            date_from: document.getElementById('trafficDateFrom'),
            date_to: document.getElementById('trafficDateTo'),
            time_from: document.getElementById('trafficTimeFrom'),
            time_to: document.getElementById('trafficTimeTo'),
            browser: document.getElementById('trafficBrowser'),
            section: document.getElementById('trafficSection')
        };
        var trafficPagination = {
            box: document.getElementById('trafficPagination'),
            info: document.getElementById('trafficPaginationInfo'),
            prev: document.getElementById('trafficPrevPageBtn'),
            next: document.getElementById('trafficNextPageBtn')
        };

        function trafficQueryString() {
            var params = [];
            Object.keys(trafficFilterFields).forEach(function (key) {
                var value = trafficFilterFields[key].value;
                if (value) {
                    params.push(key + '=' + encodeURIComponent(value));
                }
            });
            if (trafficPage > 1) {
                params.push('page=' + trafficPage);
            }
            return '/api/admin/dashboard/statistics' + (params.length ? '?' + params.join('&') : '');
        }

        // Любое изменение фильтра — сброс на первую страницу
        function applyTrafficFilter() {
            trafficPage = 1;
            loadStatistics();
        }

        function resetTrafficFilters() {
            Object.keys(trafficFilterFields).forEach(function (key) {
                trafficFilterFields[key].value = '';
            });
            trafficPage = 1;
            loadStatistics();
        }

        function renderTrafficPagination(pagination) {
            if (!trafficPagination.box || !pagination) return;
            trafficPages = Math.max(1, parseInt(pagination.pages, 10) || 1);
            if (trafficPage > trafficPages) {
                trafficPage = trafficPages;
            }
            if (trafficPages <= 1) {
                trafficPagination.box.hidden = true;
                return;
            }
            trafficPagination.box.hidden = false;
            trafficPagination.info.textContent = 'Стр. ' + trafficPage + ' из ' + trafficPages;
            trafficPagination.prev.disabled = trafficPage <= 1;
            trafficPagination.next.disabled = trafficPage >= trafficPages;
        }

        function detectBrowser(userAgent) {
            var ua = String(userAgent || '');
            // Edge определяется первым — его UA также содержит Chrome
            if (ua.indexOf('Edg/') !== -1) return 'edge';
            if (ua.indexOf('OPR/') !== -1 || ua.indexOf('Opr/') !== -1) return 'opera';
            if (ua.indexOf('Firefox/') !== -1) return 'firefox';
            if (ua.indexOf('Chrome/') !== -1) return 'chrome';
            if (ua.indexOf('Safari/') !== -1) return 'safari';
            return 'other';
        }

        function escapeHtml(value) {
            var div = document.createElement('div');
            div.textContent = value == null ? '' : String(value);
            return div.innerHTML;
        }

        function renderTraffic(rows) {
            var body = document.getElementById('trafficBody');
            if (!body) return;
            if (!rows || !rows.length) {
                body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Посещений пока нет</td></tr>';
                return;
            }
            var html = '';
            rows.forEach(function (row) {
                var visited = row.visited_at ? new Date(String(row.visited_at).replace(' ', 'T')) : null;
                var browser = BROWSERS[detectBrowser(row.user_agent)] || BROWSERS.other;
                html += '<tr>' +
                    '<td class="text-nowrap">' + escapeHtml(row.ip_address) + '</td>' +
                    '<td class="text-nowrap">' + (visited ? visited.toLocaleDateString('ru-RU') : '—') + '</td>' +
                    '<td class="text-nowrap">' + (visited ? visited.toLocaleTimeString('ru-RU', { hour12: false }) : '—') + '</td>' +
                    '<td class="text-nowrap"><i class="bi ' + browser.icon + ' me-1" aria-hidden="true"></i>' + browser.label + '</td>' +
                    '<td class="text-break">' + (row.referrer ? escapeHtml(row.referrer) : '—') + '</td>' +
                    '<td class="text-break">' + escapeHtml(row.page_url || '—') + '</td>' +
                    '</tr>';
            });
            body.innerHTML = html;
        }

        // Онлайн/сегодня и таблица трафика: первичная загрузка и опрос раз в 30 секунд
        function loadStatistics() {
            window.AdminApi.get(trafficQueryString()).then(function (data) {
                document.getElementById('dashboardOnlineCount').textContent = String(data.online);
                document.getElementById('dashboardTodayCount').textContent = String(data.today);
                renderTraffic(data.traffic || []);
                renderTrafficPagination(data.pagination);
            }).catch(function (error) {
                console.error('Не удалось обновить статистику посещений.', error);
                document.getElementById('dashboardOnlineCount').textContent = '—';
                document.getElementById('dashboardTodayCount').textContent = '—';
            });
        }

        // «Посетители онлайн» — лёгкий опрос каждые 3 секунды (реальное время);
        // пока вкладка скрыта — не запрашиваем
        var onlinePollVisible = !document.hidden;
        function loadOnlineCount() {
            if (!onlinePollVisible) {
                return;
            }
            window.AdminApi.get('/api/admin/dashboard/online').then(function (data) {
                var el = document.getElementById('dashboardOnlineCount');
                if (el) {
                    el.textContent = String(data.online);
                }
            }).catch(function (error) {
                // Сбой одного опроса не сбрасывает последнее значение
                console.error('Не удалось обновить посетителей онлайн.', error);
            });
        }
        document.addEventListener('visibilitychange', function () {
            onlinePollVisible = !document.hidden;
            if (onlinePollVisible) {
                loadOnlineCount();
            }
        });

        // ---------- Обработчики фильтров и пагинации трафика ----------
        Object.keys(trafficFilterFields).forEach(function (key) {
            trafficFilterFields[key].addEventListener('change', applyTrafficFilter);
        });
        document.getElementById('trafficResetBtn').addEventListener('click', resetTrafficFilters);
        trafficPagination.prev.addEventListener('click', function () {
            if (trafficPage > 1) {
                trafficPage--;
                loadStatistics();
            }
        });
        trafficPagination.next.addEventListener('click', function () {
            if (trafficPage < trafficPages) {
                trafficPage++;
                loadStatistics();
            }
        });

        updateClock();
        window.setInterval(updateClock, 1000);
        updateUserCount();
        updateCommentCount();
        loadOrderCounts();
        loadStatistics();
        window.setInterval(loadStatistics, 30000);
        // Посетители онлайн — обновляем каждые 3 секунды
        window.setInterval(loadOnlineCount, 3000);
        window.addEventListener('admin-orders-state-changed', loadOrderCounts);
        window.addEventListener('storage', function (event) {
            if (event.key === 'myblog.admin.orders.state.v1') loadOrderCounts();
        });
        window.AdminApi.get('/api/admin/system')
            .then(setSystemInfo)
            .catch(function (error) {
                systemStatus.textContent = 'Недоступно';
                systemStatus.className = 'badge bg-danger';
                systemError.textContent = error.message || 'Не удалось загрузить сведения о сервере';
                systemError.hidden = false;
            });
    });
}());
