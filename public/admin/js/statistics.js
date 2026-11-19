(function () {
    'use strict';

    // ---------- Константы ----------
    var PALETTE = ['#ff8c1a', '#2563eb', '#10b981', '#8b5cf6', '#ef4444', '#6c757d'];
    var SECTION_LABELS = {
        home: 'Главная',
        blog: 'Блог',
        portfolio: 'Портфолио',
        auth: 'Аккаунт',
        other: 'Другое'
    };
    var BROWSER_LABELS = {
        chrome: 'Chrome',
        edge: 'Edge',
        firefox: 'Firefox',
        safari: 'Safari',
        opera: 'Opera',
        other: 'Другой'
    };
    var BROWSER_ICONS = {
        chrome: 'bi-browser-chrome',
        edge: 'bi-browser-edge',
        firefox: 'bi-browser-firefox',
        safari: 'bi-browser-safari',
        opera: 'bi-globe2',
        other: 'bi-globe2'
    };

    var SVG_NS = 'http://www.w3.org/2000/svg';

    // ---------- Утилиты ----------
    function fmt(value) {
        return Number(value || 0).toLocaleString('ru-RU');
    }

    function notify(type, message) {
        window.AdminNotifications.show(type, message);
    }

    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }

    function svgEl(name, attrs) {
        var el = document.createElementNS(SVG_NS, name);
        for (var key in attrs) {
            if (Object.prototype.hasOwnProperty.call(attrs, key)) {
                el.setAttribute(key, attrs[key]);
            }
        }
        return el;
    }

    // «Красивый» максимум оси: шаг так, чтобы линий было не больше четырёх
    function niceScale(maxValue) {
        var steps = [1, 2, 5, 10, 20, 50, 100, 200, 500, 1000, 2000, 5000, 10000, 20000, 50000, 100000, 200000, 500000, 1000000, 2000000, 5000000];
        for (var i = 0; i < steps.length; i++) {
            if (maxValue / steps[i] <= 4) {
                return { step: steps[i], max: Math.max(steps[i], Math.ceil(maxValue / steps[i]) * steps[i]) };
            }
        }
        return { step: 10000000, max: Math.ceil(maxValue / 10000000) * 10000000 };
    }

    function monthShort(key) {
        // key: '2026-07'
        var parts = String(key).split('-');
        var d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, 1);
        return d.toLocaleDateString('ru-RU', { month: 'short' }).replace('.', '');
    }

    function dayShort(key) {
        // key: '2026-11-05'
        var parts = String(key).split('-');
        return parts[2] + '.' + parts[1];
    }

    function formatDateTime(value) {
        var d = new Date(String(value).replace(' ', 'T'));
        if (isNaN(d.getTime())) return String(value);
        return d.toLocaleDateString('ru-RU') + ' ' + d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
    }

    // Общая сетка оси Y: линии и подписи
    function appendYGrid(parent, scale, pad, plotWidth, plotHeight) {
        for (var tick = 0; tick <= scale.max; tick += scale.step) {
            var y = pad.top + plotHeight - tick / scale.max * plotHeight;
            parent.appendChild(svgEl('line', {
                x1: pad.left, y1: y, x2: pad.left + plotWidth, y2: y, stroke: '#e9ecef'
            }));
            var label = svgEl('text', {
                x: pad.left - 8, y: y + 4, 'text-anchor': 'end', fill: '#6c757d', 'font-size': 11
            });
            label.textContent = fmt(tick);
            parent.appendChild(label);
        }
    }

    // ---------- График-область: по дням ----------
    function renderAreaChart(containerId, emptyId, orderedKeys, values) {
        var container = document.getElementById(containerId);
        var emptyBox = document.getElementById(emptyId);
        var total = values.reduce(function (sum, v) { return sum + v; }, 0);
        emptyBox.hidden = total > 0;
        if (total === 0) {
            container.innerHTML = '';
            return;
        }

        var width = 560;
        var height = 240;
        var pad = { top: 14, right: 10, bottom: 30, left: 48 };
        var plotWidth = width - pad.left - pad.right;
        var plotHeight = height - pad.top - pad.bottom;
        var scale = niceScale(Math.max.apply(null, values));

        var svg = svgEl('svg', { viewBox: '0 0 ' + width + ' ' + height, 'aria-hidden': 'false' });
        appendYGrid(svg, scale, pad, plotWidth, plotHeight);

        var n = values.length;
        var coords = values.map(function (value, i) {
            return {
                x: pad.left + (n > 1 ? i * plotWidth / (n - 1) : plotWidth / 2),
                y: pad.top + plotHeight - value / scale.max * plotHeight
            };
        });

        var line = coords.map(function (p, i) { return (i ? 'L' : 'M') + p.x + ',' + p.y; }).join(' ');
        var area = line + ' L' + coords[n - 1].x + ',' + (pad.top + plotHeight) + ' L' + coords[0].x + ',' + (pad.top + plotHeight) + ' Z';
        svg.appendChild(svgEl('path', { d: area, fill: '#ff8c1a', 'fill-opacity': '.12' }));
        svg.appendChild(svgEl('path', {
            d: line, fill: 'none', stroke: '#ff8c1a', 'stroke-width': 2.5,
            'stroke-linecap': 'round', 'stroke-linejoin': 'round'
        }));

        coords.forEach(function (p, i) {
            var marker = svgEl('circle', { cx: p.x, cy: p.y, r: 2.5, fill: '#ff8c1a' });
            var title = svgEl('title', {});
            title.textContent = orderedKeys[i] + ': ' + fmt(values[i]) + ' посещений';
            marker.appendChild(title);
            svg.appendChild(marker);

            // Подписи X: каждый пятый день
            if (i % 5 === 0 || i === n - 1) {
                var text = svgEl('text', {
                    x: p.x, y: height - 8, 'text-anchor': 'middle', fill: '#6c757d', 'font-size': 11
                });
                text.textContent = dayShort(orderedKeys[i]);
                svg.appendChild(text);
            }
        });

        container.innerHTML = '';
        container.appendChild(svg);
    }

    // ---------- Столбчатый график: по месяцам / по часам ----------
    function renderBarChart(containerId, emptyId, labels, values, labelStride) {
        var container = document.getElementById(containerId);
        var emptyBox = document.getElementById(emptyId);
        var total = values.reduce(function (sum, v) { return sum + v; }, 0);
        emptyBox.hidden = total > 0;
        if (total === 0) {
            container.innerHTML = '';
            return;
        }

        var width = 560;
        var height = 240;
        var pad = { top: 14, right: 10, bottom: 30, left: 48 };
        var plotWidth = width - pad.left - pad.right;
        var plotHeight = height - pad.top - pad.bottom;
        var scale = niceScale(Math.max.apply(null, values));

        var svg = svgEl('svg', { viewBox: '0 0 ' + width + ' ' + height, 'aria-hidden': 'false' });
        appendYGrid(svg, scale, pad, plotWidth, plotHeight);

        var n = values.length;
        var slot = plotWidth / n;
        var barWidth = Math.min(slot * 0.62, 42);
        var maxValue = Math.max.apply(null, values);

        values.forEach(function (value, i) {
            var x = pad.left + slot * i + (slot - barWidth) / 2;
            var barHeight = value / scale.max * plotHeight;
            var y = pad.top + plotHeight - barHeight;
            // Цвет столбика — от красного (мало) к зелёному (много)
            var ratio = maxValue > 0 ? value / maxValue : 0;
            var hue = Math.round(ratio * 120);
            var bar = svgEl('rect', {
                x: x, y: y, width: barWidth, height: Math.max(barHeight, 0), rx: 3,
                fill: 'hsl(' + hue + ', 72%, 45%)'
            });
            var title = svgEl('title', {});
            title.textContent = labels[i] + ': ' + fmt(value);
            bar.appendChild(title);
            svg.appendChild(bar);

            if (i % labelStride === 0) {
                var text = svgEl('text', {
                    x: pad.left + slot * i + slot / 2, y: height - 8,
                    'text-anchor': 'middle', fill: '#6c757d', 'font-size': 10
                });
                text.textContent = labels[i];
                svg.appendChild(text);
            }
        });

        container.innerHTML = '';
        container.appendChild(svg);
    }

    // ---------- Кольцевая диаграмма с легендой ----------
    function renderDonut(containerId, legendId, emptyId, items) {
        var container = document.getElementById(containerId);
        var legend = document.getElementById(legendId);
        var emptyBox = document.getElementById(emptyId);
        var total = items.reduce(function (sum, item) { return sum + item.value; }, 0);
        emptyBox.hidden = total > 0;
        legend.innerHTML = '';
        if (total === 0) {
            container.innerHTML = '';
            return;
        }

        var size = 180;
        var center = size / 2;
        var radius = 62;
        var strokeWidth = 26;
        var circumference = 2 * Math.PI * radius;

        var svg = svgEl('svg', { viewBox: '0 0 ' + size + ' ' + size, 'aria-hidden': 'false' });
        var group = svgEl('g', { transform: 'rotate(-90 ' + center + ' ' + center + ')' });

        var start = 0;
        items.forEach(function (item, i) {
            var fraction = item.value / total;
            var circle = svgEl('circle', {
                cx: center, cy: center, r: radius, fill: 'none',
                stroke: PALETTE[i % PALETTE.length], 'stroke-width': strokeWidth,
                'stroke-dasharray': fraction * circumference + ' ' + circumference,
                'stroke-dashoffset': -start * circumference
            });
            var title = svgEl('title', {});
            title.textContent = item.label + ': ' + fmt(item.value) + ' (' + Math.round(fraction * 100) + '%)';
            circle.appendChild(title);
            group.appendChild(circle);
            start += fraction;
        });
        svg.appendChild(group);

        var totalText = svgEl('text', {
            x: center, y: center - 2, 'text-anchor': 'middle', fill: '#212529',
            'font-size': 24, 'font-weight': '700'
        });
        totalText.textContent = fmt(total);
        svg.appendChild(totalText);
        var caption = svgEl('text', {
            x: center, y: center + 18, 'text-anchor': 'middle', fill: '#6c757d', 'font-size': 12
        });
        caption.textContent = 'визитов';
        svg.appendChild(caption);

        container.innerHTML = '';
        container.appendChild(svg);

        items.forEach(function (item, i) {
            var row = document.createElement('div');
            row.className = 'd-flex align-items-center gap-2 small mb-1';
            var swatch = document.createElement('span');
            swatch.className = 'stat-legend-swatch';
            swatch.style.background = PALETTE[i % PALETTE.length];
            var name = document.createElement('span');
            name.className = 'flex-grow-1 text-truncate';
            name.textContent = item.label;
            name.title = item.label;
            var count = document.createElement('b');
            count.textContent = fmt(item.value);
            var percent = document.createElement('span');
            percent.className = 'text-muted';
            percent.textContent = Math.round(item.value / total * 100) + '%';
            row.appendChild(swatch);
            row.appendChild(name);
            row.appendChild(count);
            row.appendChild(percent);
            legend.appendChild(row);
        });
    }

    // ---------- Сводка и графики ----------
    function renderOverview(data) {
        var orderedDateKeys = Object.keys(data.byDate || {});
        var dateValues = orderedDateKeys.map(function (key) { return data.byDate[key]; });

        document.getElementById('statTotalVisits').textContent = fmt(data.total);
        document.getElementById('statTodayVisits').textContent = orderedDateKeys.length ? fmt(data.byDate[orderedDateKeys[orderedDateKeys.length - 1]]) : '0';
        var last30 = dateValues.reduce(function (sum, v) { return sum + v; }, 0);
        document.getElementById('statLast30Visits').textContent = fmt(last30);
        document.getElementById('statAvgPerDay').textContent = fmt(Math.round(last30 / 30));

        var monthKeys = Object.keys(data.byMonth || {});
        var hourKeys = Object.keys(data.byHour || {});

        renderAreaChart('statDailyChart', 'statDailyChartEmpty', orderedDateKeys, dateValues);

        renderBarChart('statMonthlyChart', 'statMonthlyChartEmpty',
            monthKeys.map(monthShort),
            monthKeys.map(function (key) { return data.byMonth[key]; }), 1);

        renderBarChart('statHourlyChart', 'statHourlyChartEmpty',
            hourKeys,
            hourKeys.map(function (key) { return data.byHour[key]; }), 3);

        renderDonut('statSectionChart', 'statSectionLegend', 'statSectionChartEmpty',
            (data.bySection || []).map(function (row) {
                return { label: SECTION_LABELS[row.section] || row.section, value: Number(row.c) };
            }));
        renderDonut('statBrowserChart', 'statBrowserLegend', 'statBrowserChartEmpty',
            (data.byBrowser || []).map(function (row) {
                return { label: BROWSER_LABELS[row.browser] || row.browser, value: Number(row.c) };
            }));

        document.getElementById('statRecordsCount').textContent = fmt(data.total);
        document.getElementById('statLastClean').textContent = data.lastClean ? formatDateTime(data.lastClean) : 'ещё не было';

        // Настройки хранения — из ответа overview
        document.getElementById('statRetentionDays').value = String(data.retentionDays);
        document.getElementById('statAutoClean').checked = !!data.autoClean;
    }

    function loadOverview() {
        window.AdminApi.get('/api/admin/statistics/overview').then(renderOverview).catch(function (error) {
            console.error('Не удалось загрузить статистику посещений.', error);
            notify('error', error.message || 'Не удалось загрузить статистику посещений');
        });
    }

    // ---------- Таблица «Трафик сайта» ----------
    var trafficPage = 1;
    var trafficPages = 1;
    var trafficFilterFields = {
        date_from: document.getElementById('statTrafficDateFrom'),
        date_to: document.getElementById('statTrafficDateTo'),
        time_from: document.getElementById('statTrafficTimeFrom'),
        time_to: document.getElementById('statTrafficTimeTo'),
        browser: document.getElementById('statTrafficBrowser'),
        section: document.getElementById('statTrafficSection')
    };
    var trafficPagination = {
        box: document.getElementById('statTrafficPagination'),
        info: document.getElementById('statTrafficPaginationInfo'),
        prev: document.getElementById('statTrafficPrevPageBtn'),
        next: document.getElementById('statTrafficNextPageBtn')
    };

    function detectBrowser(userAgent) {
        var ua = String(userAgent || '');
        if (ua.indexOf('Edg/') !== -1) return 'edge';
        if (ua.indexOf('OPR/') !== -1 || ua.indexOf('Opr/') !== -1) return 'opera';
        if (ua.indexOf('Firefox/') !== -1) return 'firefox';
        if (ua.indexOf('Chrome/') !== -1) return 'chrome';
        if (ua.indexOf('Safari/') !== -1) return 'safari';
        return 'other';
    }

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
        return '/api/admin/statistics/traffic' + (params.length ? '?' + params.join('&') : '');
    }

    function renderTraffic(rows) {
        var body = document.getElementById('statTrafficBody');
        if (!rows || !rows.length) {
            body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Посещений по выбранным условиям нет</td></tr>';
            return;
        }
        var html = '';
        rows.forEach(function (row) {
            var visited = row.visited_at ? new Date(String(row.visited_at).replace(' ', 'T')) : null;
            var browser = detectBrowser(row.user_agent);
            html += '<tr>' +
                '<td class="text-nowrap">' + escapeHtml(row.ip_address) + '</td>' +
                '<td class="text-nowrap">' + (visited ? visited.toLocaleDateString('ru-RU') : '—') + '</td>' +
                '<td class="text-nowrap">' + (visited ? visited.toLocaleTimeString('ru-RU', { hour12: false }) : '—') + '</td>' +
                '<td class="text-nowrap"><i class="bi ' + (BROWSER_ICONS[browser] || BROWSER_ICONS.other) + ' me-1" aria-hidden="true"></i>' + (BROWSER_LABELS[browser] || 'Браузер') + '</td>' +
                '<td class="text-break">' + (row.referrer ? escapeHtml(row.referrer) : '—') + '</td>' +
                '<td class="text-break">' + escapeHtml(row.page_url || '—') + '</td>' +
                '</tr>';
        });
        body.innerHTML = html;
    }

    function renderTrafficPagination(pagination) {
        if (!pagination) return;
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

    function loadTraffic() {
        window.AdminApi.get(trafficQueryString()).then(function (data) {
            renderTraffic(data.traffic || []);
            renderTrafficPagination(data.pagination);
        }).catch(function (error) {
            console.error('Не удалось загрузить трафик сайта.', error);
            notify('error', error.message || 'Не удалось загрузить трафик сайта');
        });
    }

    function applyTrafficFilter() {
        trafficPage = 1;
        loadTraffic();
    }

    function resetTrafficFilters() {
        Object.keys(trafficFilterFields).forEach(function (key) {
            trafficFilterFields[key].value = '';
        });
        trafficPage = 1;
        loadTraffic();
    }

    // ---------- Хранение данных ----------
    function bindRetentionControls() {
        var retentionSelect = document.getElementById('statRetentionDays');
        var autoClean = document.getElementById('statAutoClean');

        document.getElementById('statSaveRetentionBtn').addEventListener('click', function () {
            window.AdminApi.save('/api/admin/settings', 'PUT', {
                statisticsRetentionDays: retentionSelect.value,
                statisticsAutoClean: autoClean.checked ? '1' : '0'
            }).then(function () {
                notify('success', 'Настройки хранения данных сохранены');
            }).catch(function (error) {
                notify('error', error.message || 'Не удалось сохранить настройки хранения');
            });
        });

        document.getElementById('statCleanNowBtn').addEventListener('click', function () {
            var days = parseInt(retentionSelect.value, 10) || 0;
            if (days <= 0) {
                notify('warning', 'Срок хранения «Без ограничения» — очистка недоступна. Установите срок хранения.');
                return;
            }
            if (!window.confirm('Удалить все записи посещений старше ' + days + ' дней? Это действие нельзя отменить.')) {
                return;
            }
            var button = this;
            button.disabled = true;
            window.AdminApi.post('/api/admin/statistics/clean', {}).then(function (data) {
                notify('success', 'Очистка выполнена: удалено ' + fmt(data.deleted) + ' записей');
                loadOverview();
                loadTraffic();
            }).catch(function (error) {
                notify('error', error.message || 'Не удалось очистить старые записи');
            }).then(function () {
                button.disabled = false;
            });
        });
    }

    // ---------- Инициализация ----------
    document.addEventListener('DOMContentLoaded', function () {
        Object.keys(trafficFilterFields).forEach(function (key) {
            trafficFilterFields[key].addEventListener('change', applyTrafficFilter);
        });
        document.getElementById('statTrafficResetBtn').addEventListener('click', resetTrafficFilters);
        trafficPagination.prev.addEventListener('click', function () {
            if (trafficPage > 1) {
                trafficPage--;
                loadTraffic();
            }
        });
        trafficPagination.next.addEventListener('click', function () {
            if (trafficPage < trafficPages) {
                trafficPage++;
                loadTraffic();
            }
        });

        bindRetentionControls();
        loadOverview();
        loadTraffic();
    });
}());
