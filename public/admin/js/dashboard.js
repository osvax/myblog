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

        function renderSalesChart() {
            var monthNames = [];
            var currentMonth = new Date();
            for (var monthOffset = 5; monthOffset >= 0; monthOffset--) {
                var monthDate = new Date(currentMonth.getFullYear(), currentMonth.getMonth() - monthOffset, 1);
                monthNames.push(monthDate.toLocaleDateString('ru-RU', { month: 'short' }).replace('.', ''));
            }
            var points = [
                { label: monthNames[0], value: 85000 },
                { label: monthNames[1], value: 122000 },
                { label: monthNames[2], value: 97000 },
                { label: monthNames[3], value: 164000 },
                { label: monthNames[4], value: 138000 },
                { label: monthNames[5], value: 205000 }
            ];
            var width = 560;
            var height = 220;
            var padding = { top: 16, right: 12, bottom: 34, left: 58 };
            var max = Math.ceil(Math.max.apply(null, points.map(function (point) { return point.value; })) / 50000) * 50000;
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
            for (var tick = 0; tick <= max; tick += 50000) {
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
                '" role="img" aria-label="Демонстрационные продажи за шесть месяцев">' +
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

        function updateUserCount() {
            try {
                var stored = localStorage.getItem('myblog.admin.users.v1');
                var users = stored ? JSON.parse(stored) : null;
                document.getElementById('dashboardUsersCount').textContent =
                    String(Array.isArray(users) ? users.length : 4);
            } catch (error) {
                console.error('Не удалось получить количество пользователей.', error);
                document.getElementById('dashboardUsersCount').textContent = '—';
            }
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

        updateClock();
        window.setInterval(updateClock, 1000);
        renderSalesChart();
        updateUserCount();
        updateCommentCount();
        loadOrderCounts();
        window.addEventListener('admin-orders-state-changed', loadOrderCounts);
        window.addEventListener('storage', function (event) {
            if (event.key === 'myblog.admin.orders.state.v1') loadOrderCounts();
            if (event.key === 'myblog.admin.users.v1') updateUserCount();
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
