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

    // Геометрия временных графиков: высота фиксированная, ширина — по контейнеру,
    // чтобы график на всю ширину не растягивался по высоте
    var CHART = {
        minWidth: 560,
        height: 240,
        pad: { top: 14, right: 10, bottom: 30, left: 48 }
    };

    // Геометрия viewBox для контейнера (ширина 1:1 с CSS-пикселями)
    function chartGeo(containerId) {
        var container = document.getElementById(containerId);
        var w = container ? container.clientWidth : 0;
        if (!w || w < CHART.minWidth) {
            w = CHART.minWidth;
        }
        return { width: Math.round(w), height: CHART.height, pad: CHART.pad };
    }

    // ---------- Состояние графиков (данные целиком + текущий диапазон зума) ----------
    var chartState = {
        day: { keys: [], values: [], start: 0, end: -1 },
        week: { keys: [], values: [], start: 0, end: -1 },
        month: { keys: [], values: [], start: 0, end: -1 },
        year: { keys: [], values: [], start: 0, end: -1 },
        hourly: { keys: [], values: [], start: 0, end: -1 },
        minutes: { keys: [], values: [], start: 0, end: -1 }
    };
    var hourDrill = null; // 'HH' — час, развёрнутый по минутам

    // Единый график «Посещения»: группировка и способ отображения
    var timelineView = { gran: 'day', mode: 'line' }; // gran: day/week/month/year; mode: line/bar

    // Фильтр «с даты по дату» для графика «Посещения» (как в заказах):
    // пустая граница — без ограничения по этой стороне
    var timelineFilter = { from: '', to: '' };
    // Полные данные по группировкам до применения фильтра (кнопки зума и смена дат работают от них)
    var fullTimeline = null;
    var timelineDateFromInput = document.getElementById('statDateFrom');
    var timelineDateToInput = document.getElementById('statDateTo');

    // Контейнеры зумируемых графиков: id → { state, rerender }.
    // У «по часам» state переключается на уровень «по минутам» в renderHourlyChart();
    // у единого графика — на текущую группировку в renderTimelineChart()
    var wheelZoomContainers = {
        statTimelineChart: { state: null, rerender: renderTimelineChart },
        statHourlyChart: { state: null, rerender: renderHourlyChart }
    };

    // Полный диапазон — от первого значения до последнего
    function isDefaultRange(st) {
        var n = st.keys.length;
        return n > 0 && st.start === 0 && st.end === n - 1;
    }

    function setDefaultRange(st) {
        st.start = 0;
        st.end = st.keys.length - 1;
    }

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

    function isoDateValue(date) {
        return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') +
            '-' + String(date.getDate()).padStart(2, '0');
    }

    // Границы периода бакета (ISO-даты): день/неделя — от ключа, месяц/год — весь период
    function bucketPeriod(key, gran) {
        if (gran === 'month') {
            // key: '2026-07'; день 0 следующего месяца — последний день месяца
            var parts = String(key).split('-');
            return { from: key + '-01', to: isoDateValue(new Date(parseInt(parts[0], 10), parseInt(parts[1], 10), 0)) };
        }
        if (gran === 'year') {
            return { from: key + '-01-01', to: key + '-12-31' };
        }
        if (gran === 'week') {
            // key — понедельник недели
            var sunday = new Date(key + 'T12:00:00');
            sunday.setDate(sunday.getDate() + 6);
            return { from: key, to: isoDateValue(sunday) };
        }
        return { from: key, to: key };
    }

    // Весь диапазон доступных данных по всем группировкам (для значений по умолчанию)
    function fullTimelineBounds() {
        var from = null;
        var to = null;
        ['day', 'week', 'month', 'year'].forEach(function (gran) {
            var keys = fullTimeline[gran].keys;
            if (!keys.length) return;
            var first = bucketPeriod(keys[0], gran);
            var last = bucketPeriod(keys[keys.length - 1], gran);
            if (from === null || first.from < from) from = first.from;
            if (to === null || last.to > to) to = last.to;
        });
        return { from: from || '', to: to || '' };
    }

    // Применяем фильтр дат ко всем группировкам; зум сбрасывается к полному отфильтрованному диапазону
    function applyTimelineDateFilter() {
        if (!fullTimeline) return;
        ['day', 'week', 'month', 'year'].forEach(function (gran) {
            var keys = fullTimeline[gran].keys;
            var values = fullTimeline[gran].values;
            if (!timelineFilter.from && !timelineFilter.to) {
                chartState[gran].keys = keys;
                chartState[gran].values = values;
            } else {
                var keptKeys = [];
                var keptValues = [];
                for (var i = 0; i < keys.length; i++) {
                    // Период оставляем, если пересекается с выбранным диапазоном
                    var period = bucketPeriod(keys[i], gran);
                    if (timelineFilter.from && period.to < timelineFilter.from) continue;
                    if (timelineFilter.to && period.from > timelineFilter.to) continue;
                    keptKeys.push(keys[i]);
                    keptValues.push(values[i]);
                }
                chartState[gran].keys = keptKeys;
                chartState[gran].values = keptValues;
            }
            chartState[gran].start = 0;
            chartState[gran].end = chartState[gran].keys.length - 1;
        });
        renderTimelineChart();
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

    // ---------- Зум графика: выделение участка мышью + клик по точке/столбику ----------
    function attachBrush(svg, count, onZoom, onClickIndex) {
        // Геометрия viewBox — привязана к SVG при рендере (svg._chartGeo)
        var geo = svg._chartGeo || { width: CHART.minWidth, height: CHART.height, pad: CHART.pad };
        var plotLeft = geo.pad.left;
        var plotWidth = geo.width - geo.pad.left - geo.pad.right;
        var plotRight = plotLeft + plotWidth;
        var dragging = false;
        var startX = 0;
        var selRect = null;

        function viewX(evt) {
            var box = svg.getBoundingClientRect();
            if (box.width === 0) {
                return plotLeft;
            }
            var x = (evt.clientX - box.left) / box.width * geo.width;
            return Math.max(plotLeft, Math.min(plotRight, x));
        }

        function indexAt(x) {
            var idx = Math.floor((x - plotLeft) / (plotWidth / count));
            return Math.max(0, Math.min(count - 1, idx));
        }

        svg.style.cursor = 'crosshair';
        svg.addEventListener('mousedown', function (evt) {
            if (evt.button !== 0) {
                return;
            }
            evt.preventDefault();
            dragging = true;
            startX = viewX(evt);
            selRect = svgEl('rect', {
                x: startX, y: geo.pad.top, width: 0,
                height: geo.height - geo.pad.top - geo.pad.bottom,
                fill: 'rgba(255, 140, 26, .18)', stroke: '#ff8c1a', 'stroke-width': 1, 'stroke-dasharray': '4 3'
            });
            svg.appendChild(selRect);
        });
        svg.addEventListener('mousemove', function (evt) {
            if (!dragging || !selRect) {
                return;
            }
            var x = viewX(evt);
            selRect.setAttribute('x', Math.min(startX, x));
            selRect.setAttribute('width', Math.abs(x - startX));
        });
        svg.addEventListener('mouseup', function (evt) {
            if (!dragging) {
                return;
            }
            dragging = false;
            var x = viewX(evt);
            var dx = Math.abs(x - startX);
            if (selRect) {
                selRect.remove();
                selRect = null;
            }
            if (dx < 6) {
                // Короткое нажатие — клик по точке/столбику
                if (onClickIndex) {
                    onClickIndex(indexAt(x));
                }
                return;
            }
            var i1 = indexAt(Math.min(startX, x));
            var i2 = indexAt(Math.max(startX, x));
            if (i2 > i1) {
                if (onZoom) {
                    onZoom(i1, i2);
                }
            } else if (onClickIndex) {
                onClickIndex(i1);
            }
        });
        svg.addEventListener('mouseleave', function () {
            if (!dragging) {
                return;
            }
            dragging = false;
            if (selRect) {
                selRect.remove();
                selRect = null;
            }
        });
    }

    // Колёсико мыши с зажатым Ctrl: масштабировать по ширине (крупность — мелкая),
    // от 1 значения до всего массива; точка под курсором не сдвигается.
    // Единый слушатель на document (не привязывается к перерисовке SVG):
    // пока курсор над областью графика, страница не маштабируется и не скроллится
    // (preventDefault), график не уезжает из вида
    function bindWheelZoom() {
        document.addEventListener('wheel', function (evt) {
            if (!evt.ctrlKey) {
                return;
            }
            // Ищем контейнер графика под курсором (событие приходит из SVG или его элементов)
            var node = evt.target;
            var entry = null;
            while (node && node.nodeType === 1) {
                entry = wheelZoomContainers[node.id] || null;
                if (entry) {
                    break;
                }
                node = node.parentNode;
            }
            if (!entry) {
                return;
            }
            evt.preventDefault();
            var container = node;
            var svg = container.querySelector('svg');
            var state = entry.state;
            if (!svg || !state || typeof entry.rerender !== 'function') {
                return;
            }
            var total = state.keys.length;
            var visible = state.end - state.start + 1;
            if (total < 1 || visible < 1) {
                return;
            }

            // >0 — приближение (меньше точек на экране), <0 — отдаление
            var delta = Math.max(-300, Math.min(300, -evt.deltaY));
            if (delta === 0) {
                return;
            }
            var factor = Math.pow(0.92, delta / 100);
            var newVisible = Math.round(visible * factor);
            // Квантование: если округление не сдвинуло масштаб — один шаг к цели (1 или весь массив)
            if (newVisible === visible) {
                newVisible = factor < 1 ? visible - 1 : visible + 1;
            }
            newVisible = Math.max(1, Math.min(total, newVisible));
            if (newVisible === visible) {
                return;
            }

            // Геометрия viewBox — привязана к SVG при рендере (svg._chartGeo)
            var geo = svg._chartGeo || { width: CHART.minWidth, height: CHART.height, pad: CHART.pad };
            var plotLeft = geo.pad.left;
            var plotWidth = geo.width - geo.pad.left - geo.pad.right;
            var box = svg.getBoundingClientRect();
            var x = box.width > 0 ? (evt.clientX - box.left) / box.width * geo.width : geo.width / 2;
            var fraction = Math.max(0, Math.min(1, (x - plotLeft) / plotWidth));

            var cursorIdx = state.start + fraction * (visible - 1);
            var start = Math.round(cursorIdx - fraction * (newVisible - 1));
            start = Math.max(0, Math.min(total - newVisible, start));
            state.start = start;
            state.end = start + newVisible - 1;
            entry.rerender();
        }, { passive: false });
    }

    // ---------- График-область (линия с точками) ----------
    function renderLineChart(containerId, emptyId, keys, values, labelFn, onZoom) {
        var container = document.getElementById(containerId);
        var emptyBox = document.getElementById(emptyId);
        if (!values.length) {
            container.innerHTML = '';
            return;
        }
        var total = values.reduce(function (sum, v) { return sum + v; }, 0);
        emptyBox.hidden = total > 0;
        if (total === 0) {
            container.innerHTML = '';
            return;
        }

        var geo = chartGeo(containerId);
        var plotWidth = geo.width - geo.pad.left - geo.pad.right;
        var plotHeight = geo.height - geo.pad.top - geo.pad.bottom;
        var scale = niceScale(Math.max.apply(null, values));

        var svg = svgEl('svg', { viewBox: '0 0 ' + geo.width + ' ' + geo.height, 'aria-hidden': 'false' });
        svg._chartGeo = geo;
        appendYGrid(svg, scale, geo.pad, plotWidth, plotHeight);

        var m = values.length;
        var coords = values.map(function (value, i) {
            return {
                x: geo.pad.left + (m > 1 ? i * plotWidth / (m - 1) : plotWidth / 2),
                y: geo.pad.top + plotHeight - value / scale.max * plotHeight
            };
        });

        function smoothPath(points) {
            if (points.length < 2) return points.length ? 'M' + points[0].x + ',' + points[0].y : '';
            var path = 'M' + points[0].x + ',' + points[0].y;
            for (var index = 0; index < points.length - 1; index++) {
                var current = points[index];
                var next = points[index + 1];
                var previous = points[index - 1] || current;
                var following = points[index + 2] || next;
                var control1 = {
                    x: current.x + (next.x - previous.x) / 6,
                    y: current.y + (next.y - previous.y) / 6
                };
                var control2 = {
                    x: next.x - (following.x - current.x) / 6,
                    y: next.y - (following.y - current.y) / 6
                };
                path += ' C' + control1.x + ',' + control1.y + ' ' + control2.x + ',' + control2.y + ' ' + next.x + ',' + next.y;
            }
            return path;
        }

        var line = smoothPath(coords);
        var area = line + ' L' + coords[m - 1].x + ',' + (geo.pad.top + plotHeight) + ' L' + coords[0].x + ',' + (geo.pad.top + plotHeight) + ' Z';
        svg.appendChild(svgEl('path', { d: area, fill: '#ff8c1a', 'fill-opacity': '.12', class: 'stat-line-area' }));
        svg.appendChild(svgEl('path', {
            d: line, fill: 'none', stroke: '#ff8c1a', 'stroke-width': 3,
            'stroke-linecap': 'round', 'stroke-linejoin': 'round', class: 'stat-line-path'
        }));

        container.innerHTML = '';
        container.appendChild(svg);

        var tooltip = document.createElement('div');
        tooltip.className = 'stat-chart-tooltip';
        tooltip.hidden = true;
        container.appendChild(tooltip);
        function showTooltip(point, index) {
            tooltip.textContent = '';
            var period = document.createElement('strong');
            period.textContent = labelFn(keys[index]);
            var count = document.createElement('span');
            count.textContent = fmt(values[index]) + ' посещений';
            tooltip.appendChild(period);
            tooltip.appendChild(count);
            tooltip.hidden = false;
            var left = point.x / geo.width * container.clientWidth;
            var top = Math.max(4, point.y / geo.height * geo.height - 62);
            tooltip.style.left = Math.max(4, Math.min(container.clientWidth - tooltip.offsetWidth - 4, left + 8)) + 'px';
            tooltip.style.top = top + 'px';
        }
        function hideTooltip() {
            tooltip.hidden = true;
        }

        // Точки и расширенные зоны наведения.
        coords.forEach(function (p, i) {
            var hit = svgEl('circle', { cx: p.x, cy: p.y, r: 12, fill: 'transparent', class: 'stat-line-hit' });
            hit.addEventListener('mouseenter', function () { showTooltip(p, i); });
            hit.addEventListener('mouseleave', hideTooltip);
            svg.appendChild(hit);
            var marker = svgEl('circle', { cx: p.x, cy: p.y, r: 4, fill: '#fff', stroke: '#ff8c1a', 'stroke-width': 2, class: 'stat-line-point' });
            svg.appendChild(marker);
        });

        // Подписи X: не больше двенадцати
        var stride = Math.max(1, Math.ceil(m / 12));
        coords.forEach(function (p, i) {
            if (i % stride === 0 || i === m - 1) {
                var text = svgEl('text', {
                    x: p.x, y: geo.height - 8, 'text-anchor': 'middle', fill: '#6c757d', 'font-size': 11
                });
                text.textContent = labelFn(keys[i]);
                svg.appendChild(text);
            }
        });

        attachBrush(svg, m, onZoom || null, null);

    }

    // ---------- Единый график «Посещения»: дни/недели/месяцы/годы × линия/столбики ----------
    function timelineLabel(key) {
        if (timelineView.gran === 'month') {
            return monthShort(key);
        }
        if (timelineView.gran === 'year') {
            return key;
        }
        return dayShort(key);
    }

    function renderTimelineChart() {
        var st = chartState[timelineView.gran] || chartState.day;
        wheelZoomContainers.statTimelineChart.state = st;
        document.getElementById('statTimelineResetBtn').hidden = !isDefaultRange(st);
        // Подсветка активных кнопок группировки и способа отображения
        document.querySelectorAll('.stat-gran-btn').forEach(function (btn) {
            btn.classList.toggle('active', btn.getAttribute('data-gran') === timelineView.gran);
        });
        document.querySelectorAll('.stat-mode-btn').forEach(function (btn) {
            btn.classList.toggle('active', btn.getAttribute('data-mode') === timelineView.mode);
        });

        if (st.keys.length === 0) {
            document.getElementById('statTimelineChart').innerHTML = '';
            document.getElementById('statTimelineChartEmpty').hidden = false;
            return;
        }

        var keys = st.keys.slice(st.start, st.end + 1);
        var values = st.values.slice(st.start, st.end + 1);
        var onZoom = function (i1, i2) {
            st.start += i1;
            st.end += i2;
            renderTimelineChart();
        };
        if (timelineView.mode === 'bar') {
            renderBarChart('statTimelineChart', 'statTimelineChartEmpty', keys.map(timelineLabel), values, { onZoom: onZoom });
        } else {
            renderLineChart('statTimelineChart', 'statTimelineChartEmpty', keys, values, timelineLabel, onZoom);
        }
    }

    // ---------- Столбчатый график: по месяцам / по часам / по минутам ----------
    function renderBarChart(containerId, emptyId, labels, values, options) {
        var container = document.getElementById(containerId);
        var emptyBox = document.getElementById(emptyId);
        options = options || {};
        var total = values.reduce(function (sum, v) { return sum + v; }, 0);
        emptyBox.hidden = total > 0;
        if (total === 0) {
            container.innerHTML = '';
            return;
        }

        var geo = chartGeo(containerId);
        var plotWidth = geo.width - geo.pad.left - geo.pad.right;
        var plotHeight = geo.height - geo.pad.top - geo.pad.bottom;
        var scale = niceScale(Math.max.apply(null, values));

        var svg = svgEl('svg', { viewBox: '0 0 ' + geo.width + ' ' + geo.height, 'aria-hidden': 'false' });
        svg._chartGeo = geo;
        appendYGrid(svg, scale, geo.pad, plotWidth, plotHeight);

        var n = values.length;
        var slot = plotWidth / n;
        var barWidth = Math.min(slot * 0.62, 42);
        var maxValue = Math.max.apply(null, values);
        var labelStride = Math.max(1, Math.ceil(n / 12));

        values.forEach(function (value, i) {
            var x = geo.pad.left + slot * i + (slot - barWidth) / 2;
            var barHeight = value / scale.max * plotHeight;
            var y = geo.pad.top + plotHeight - barHeight;
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
                    x: geo.pad.left + slot * i + slot / 2, y: geo.height - 8,
                    'text-anchor': 'middle', fill: '#6c757d', 'font-size': 10
                });
                text.textContent = labels[i];
                svg.appendChild(text);
            }
        });

        attachBrush(svg, n, options.onZoom || null, options.onClickIndex || null);

        container.innerHTML = '';
        container.appendChild(svg);
    }

    // ---------- График «по часам» с drill-down по минутам ----------
    function renderHourlyChart() {
        var title = document.getElementById('statHourlyTitle');
        var backBtn = document.getElementById('statHourlyBackBtn');
        var resetBtn = document.getElementById('statHourlyResetBtn');
        var emptyBox = document.getElementById('statHourlyChartEmpty');
        var container = document.getElementById('statHourlyChart');

        if (hourDrill === null) {
            title.textContent = 'Активность по часам';
            backBtn.hidden = true;
            emptyBox.textContent = 'Данных по часам пока нет — диаграмма появится с первыми визитами.';
            var st = chartState.hourly;
            wheelZoomContainers.statHourlyChart.state = st;
            resetBtn.hidden = !isDefaultRange(st);
            if (st.keys.length === 0) {
                container.innerHTML = '';
                return;
            }
            renderBarChart('statHourlyChart', 'statHourlyChartEmpty',
                st.keys.slice(st.start, st.end + 1).map(function (h) { return h + ':00'; }),
                st.values.slice(st.start, st.end + 1),
                {
                    onZoom: function (i1, i2) {
                        st.start += i1;
                        st.end += i2;
                        renderHourlyChart();
                    },
                    onClickIndex: function (i) {
                        drillIntoHour(st.keys[st.start + i]);
                    }
                });
        } else {
            title.textContent = 'Активность в ' + hourDrill + ':00 (по минутам)';
            backBtn.hidden = false;
            emptyBox.textContent = 'Нет посещений в этом часе.';
            var ms = chartState.minutes;
            wheelZoomContainers.statHourlyChart.state = ms;
            resetBtn.hidden = !isDefaultRange(ms);
            if (ms.keys.length === 0) {
                container.innerHTML = '';
                emptyBox.hidden = false;
                return;
            }
            renderBarChart('statHourlyChart', 'statHourlyChartEmpty',
                ms.keys.slice(ms.start, ms.end + 1).map(function (m) { return hourDrill + ':' + m; }),
                ms.values.slice(ms.start, ms.end + 1),
                {
                    onZoom: function (i1, i2) {
                        ms.start += i1;
                        ms.end += i2;
                        renderHourlyChart();
                    }
                });
        }
    }

    // Развернуть выбранный час по минутам
    function drillIntoHour(hourKey) {
        hourDrill = hourKey;
        chartState.minutes.keys = [];
        chartState.minutes.values = [];
        chartState.minutes.start = 0;
        chartState.minutes.end = -1;
        renderHourlyChart();

        window.AdminApi.get('/api/admin/statistics/hourly?hour=' + hourKey).then(function (data) {
            var byMinute = data.byMinute || {};
            var keys = Object.keys(byMinute).sort();
            chartState.minutes.keys = keys;
            chartState.minutes.values = keys.map(function (key) { return Number(byMinute[key]) || 0; });
            chartState.minutes.start = 0;
            chartState.minutes.end = keys.length - 1;
            renderHourlyChart();
        }).catch(function (error) {
            console.error('Не удалось загрузить данные по минутам.', error);
            notify('error', error.message || 'Не удалось загрузить данные по минутам');
        });
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

    // ---------- Разделы статистики ----------
    var SECTION_CHECKS = {
        home: document.getElementById('statSectionHome'),
        blog: document.getElementById('statSectionBlog'),
        portfolio: document.getElementById('statSectionPortfolio'),
        auth: document.getElementById('statSectionAuth'),
        other: document.getElementById('statSectionOther')
    };

    function setSections(allowed) {
        Object.keys(SECTION_CHECKS).forEach(function (section) {
            if (SECTION_CHECKS[section]) {
                SECTION_CHECKS[section].checked = allowed.indexOf(section) !== -1;
            }
        });
    }

    function selectedSections() {
        return Object.keys(SECTION_CHECKS).filter(function (section) {
            return SECTION_CHECKS[section] && SECTION_CHECKS[section].checked;
        });
    }

    function bindSectionControls() {
        var saveBtn = document.getElementById('statSaveSectionsBtn');
        if (!saveBtn) {
            return;
        }
        saveBtn.addEventListener('click', function () {
            var selected = selectedSections();
            if (selected.length === 0) {
                notify('warning', 'Включите хотя бы один раздел — иначе статистика не будет вестись');
                return;
            }
            var button = this;
            button.disabled = true;
            window.AdminApi.save('/api/admin/settings', 'PUT', {
                statisticsSections: JSON.stringify(selected)
            }).then(function (data) {
                var message = 'Разделы статистики сохранены';
                if (data && data.deletedRecords > 0) {
                    message += ' — удалено записей по выключенным разделам: ' + fmt(data.deletedRecords);
                }
                notify('success', message);
                loadOverview();
                loadTraffic();
            }).catch(function (error) {
                notify('error', error.message || 'Не удалось сохранить разделы статистики');
            }).then(function () {
                button.disabled = false;
            });
        });
    }

    // ---------- Сводка и графики ----------
    function renderOverview(data) {
        // Единый график: заполняем все группировки, зум — полный диапазон
        fullTimeline = { day: null, week: null, month: null, year: null };
        var fillTimeline = function (gran, source) {
            var keys = Object.keys(source || {});
            chartState[gran].keys = keys;
            chartState[gran].values = keys.map(function (key) { return Number(source[key]) || 0; });
            chartState[gran].start = 0;
            chartState[gran].end = keys.length - 1;
            fullTimeline[gran] = { keys: keys.slice(), values: chartState[gran].values.slice() };
            return keys;
        };
        var dateKeys = fillTimeline('day', data.byDate);
        fillTimeline('week', data.byWeek);
        fillTimeline('month', data.byMonth);
        fillTimeline('year', data.byYear);

        var hourKeys = Object.keys(data.byHour || {});
        chartState.hourly.keys = hourKeys;
        chartState.hourly.values = hourKeys.map(function (key) { return Number(data.byHour[key]) || 0; });
        chartState.hourly.start = 0;
        chartState.hourly.end = hourKeys.length - 1;

        hourDrill = null;
        chartState.minutes.keys = [];
        chartState.minutes.values = [];
        chartState.minutes.start = 0;
        chartState.minutes.end = -1;

        var dateValues = chartState.day.values;
        document.getElementById('statTotalVisits').textContent = fmt(data.total);
        document.getElementById('statTodayVisits').textContent = dateKeys.length ? fmt(dateValues[dateValues.length - 1]) : '0';
        var last30 = dateValues.slice(-30).reduce(function (sum, v) { return sum + v; }, 0);
        document.getElementById('statLast30Visits').textContent = fmt(last30);
        document.getElementById('statAvgPerDay').textContent = fmt(Math.round(last30 / 30));

        // Первый запуск: заполняем фильтр полным диапазоном доступных данных
        if (timelineDateFromInput && timelineDateToInput &&
            !timelineDateFromInput.value && !timelineDateToInput.value) {
            var bounds = fullTimelineBounds();
            timelineDateFromInput.value = bounds.from;
            timelineDateToInput.value = bounds.to;
            timelineFilter.from = bounds.from;
            timelineFilter.to = bounds.to;
        }
        applyTimelineDateFilter();
        renderHourlyChart();

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

        // Настройки — из ответа overview
        document.getElementById('statRetentionDays').value = String(data.retentionDays);
        document.getElementById('statAutoClean').checked = !!data.autoClean;
        document.getElementById('statUpdateInterval').value = String(parseInt(data.updateInterval, 10) || 900);
        setSections(Array.isArray(data.statisticsSections) ? data.statisticsSections : []);
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

    // ---------- Хранение данных и интервал обновления ----------
    function bindRetentionControls() {
        var retentionSelect = document.getElementById('statRetentionDays');
        var autoClean = document.getElementById('statAutoClean');
        var updateInterval = document.getElementById('statUpdateInterval');

        document.getElementById('statSaveRetentionBtn').addEventListener('click', function () {
            var interval = parseInt(updateInterval.value, 10);
            if (!interval || interval < 5) {
                notify('warning', 'Интервал обновления — не менее 5 секунд');
                return;
            }
            updateInterval.value = String(interval);
            window.AdminApi.save('/api/admin/settings', 'PUT', {
                statisticsRetentionDays: retentionSelect.value,
                statisticsAutoClean: autoClean.checked ? '1' : '0',
                statisticsUpdateInterval: String(interval)
            }).then(function () {
                notify('success', 'Настройки хранения и обновления сохранены');
            }).catch(function (error) {
                notify('error', error.message || 'Не удалось сохранить настройки');
            });
        });

        document.getElementById('statCleanNowBtn').addEventListener('click', function () {
            var days = parseInt(retentionSelect.value, 10) || 0;
            if (days <= 0) {
                notify('warning', 'Срок хранения «Без ограничения» — очистка недоступна. Установите срок хранения.');
                return;
            }
            var button = this;
            window.AdminConfirm.ask('Удалить все записи посещений старше ' + days + ' дней? Это действие нельзя отменить.', { title: 'Очистка статистики', confirmLabel: 'Очистить' })
                .then(function (result) {
                    if (!result.ok) return;
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

        // Единый график «Посещения»: группировка и способ отображения
        document.querySelectorAll('.stat-gran-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                timelineView.gran = btn.getAttribute('data-gran') || 'day';
                renderTimelineChart();
            });
        });
        document.querySelectorAll('.stat-mode-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                timelineView.mode = btn.getAttribute('data-mode') || 'line';
                renderTimelineChart();
            });
        });

        // Сброс зума — возврат к виду по умолчанию
        document.getElementById('statTimelineResetBtn').addEventListener('click', function () {
            var st = chartState[timelineView.gran] || chartState.day;
            setDefaultRange(st);
            renderTimelineChart();
        });

        // Фильтр «с даты по дату» для графика «Посещения» (как в заказах)
        if (timelineDateFromInput && timelineDateToInput) {
            var todayValue = isoDateValue(new Date());
            timelineDateFromInput.max = todayValue;
            timelineDateToInput.max = todayValue;
            function applyTimelineDateInputs() {
                var from = timelineDateFromInput.value;
                var to = timelineDateToInput.value;
                if (from && to && from > to) {
                    notify('warning', 'Начальная дата не может быть позже конечной');
                    return;
                }
                timelineFilter.from = from;
                timelineFilter.to = to;
                applyTimelineDateFilter();
            }
            timelineDateFromInput.addEventListener('change', applyTimelineDateInputs);
            timelineDateToInput.addEventListener('change', applyTimelineDateInputs);
        }
        document.getElementById('statHourlyResetBtn').addEventListener('click', function () {
            var st = (hourDrill === null) ? chartState.hourly : chartState.minutes;
            setDefaultRange(st);
            renderHourlyChart();
        });

        // Ctrl+колесо над графиками — единый слушатель (не переподключается при рендере)
        bindWheelZoom();

        // При изменении ширины окна — перерисовать графики (ширина viewBox привязана к контейнеру)
        var resizeTimer = null;
        window.addEventListener('resize', function () {
            if (resizeTimer) {
                clearTimeout(resizeTimer);
            }
            resizeTimer = setTimeout(function () {
                renderTimelineChart();
                renderHourlyChart();
            }, 200);
        });

        // Назад с минут к часам
        document.getElementById('statHourlyBackBtn').addEventListener('click', function () {
            hourDrill = null;
            renderHourlyChart();
        });

        bindRetentionControls();
        bindSectionControls();
        loadOverview();
        loadTraffic();
    });
}());
