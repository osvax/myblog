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

        // Счётчики статей и работ портфолио — из базы
        function updateContentCounts() {
            window.AdminApi.get('/api/admin/articles').then(function (data) {
                var published = (data.articles || []).filter(function (article) {
                    return article.active !== false;
                }).length;
                document.getElementById('dashboardArticlesCount').textContent = String(published);
            }).catch(function () {
                document.getElementById('dashboardArticlesCount').textContent = '—';
            });
            window.AdminApi.get('/api/admin/projects').then(function (data) {
                document.getElementById('dashboardProjectsCount').textContent = String((data.projects || []).length);
            }).catch(function () {
                document.getElementById('dashboardProjectsCount').textContent = '—';
            });
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
            }).catch(function (error) {
                console.error('Не удалось обновить счётчики заказов.', error);
                document.getElementById('dashboardNewOrdersValue').textContent = '—';
                document.getElementById('dashboardCurrentOrdersCount').textContent = '—';
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

        // ---------- Активность ----------
        function renderActivity(activities) {
            var body = document.getElementById('activityBody');
            if (!body) return;
            if (!activities || !activities.length) {
                body.innerHTML = '<div class="activity-empty"><i class="bi bi-hourglass-split"></i>Пока нет записей об активности';
                return;
            }
            var list = '<ul class="activity-list">';
            activities.forEach(function (a) {
                var iconClass = 'page-view';
                var icon = 'bi-window';
                var url = (a.page_url || '').toLowerCase();
                if (url.indexOf('login') !== -1 || url.indexOf('auth') !== -1) {
                    iconClass = 'login';
                    icon = 'bi-box-arrow-in-right';
                }
                var timeAgo = timeAgoFromISO(a.visited_at);
                list += '<li class="activity-item">' +
                    '<span class="activity-icon ' + iconClass + '"><i class="bi ' + icon + '"></i></span>' +
                    '<div class="activity-info">' +
                    '<div class="activity-title" title="' + escapeHtml(a.page_title || a.page_url || '') + '">' +
                    escapeHtml(a.page_title || a.page_url || '—') + '</div>' +
                    '<div class="activity-meta">' + escapeHtml(a.name || 'Пользователь') + '</div>' +
                    '</div>' +
                    '<span class="activity-time">' + timeAgo + '</span>' +
                    '</li>';
            });
            list += '</ul>';
            body.innerHTML = list;
        }

        function timeAgoFromISO(iso) {
            if (!iso) return '—';
            var d = new Date(iso);
            if (isNaN(d)) return iso;
            var now = new Date();
            var diff = Math.floor((now - d) / 1000);
            if (diff < 60) return 'только что';
            if (diff < 3600) return Math.floor(diff / 60) + ' мин назад';
            if (diff < 86400) return Math.floor(diff / 3600) + ' ч назад';
            if (diff < 604800) return Math.floor(diff / 86400) + ' дн назад';
            return d.toLocaleDateString('ru-RU');
        }

        function loadActivity() {
            window.AdminApi.get('/api/admin/activity')
                .then(function (data) {
                    renderActivity(data.activities);
                })
                .catch(function (err) {
                    console.error('Не удалось загрузить активность.', err);
                    var body = document.getElementById('activityBody');
                    if (body) body.innerHTML = '<div class="activity-error">Не удалось загрузить</div>';
                });
        }

        // Обновление активности по клику на кнопку
        function bindActivityRefresh() {
            var btn = document.getElementById('activityRefreshBtn');
            if (!btn) return;
            btn.addEventListener('click', function () {
                var icon = btn.querySelector('i');
                if (icon) icon.className = 'bi bi-arrow-clockwise spin';
                loadActivity();
                setTimeout(function () { if (icon) icon.className = 'bi bi-arrow-clockwise'; }, 1500);
            });
        }

        updateClock();
        window.setInterval(updateClock, 1000);
        updateUserCount();
        updateContentCounts();
        updateCommentCount();
        loadOrderCounts();
        loadActivity();
        bindActivityRefresh();
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
