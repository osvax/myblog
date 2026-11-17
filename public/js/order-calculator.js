(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var typeSelect = document.getElementById('calcType');
        var orderType = document.getElementById('orderType');
        var featuresContainer = document.getElementById('calcFeatures');
        var priceDisplay = document.getElementById('calcPrice');
        var daysDisplay = document.getElementById('calcDays');
        var budgetInput = document.getElementById('orderBudget');
        var detailsInput = document.getElementById('calculatorDetails');
        var config = window.CalculatorConfig.load();

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, function (char) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
            });
        }

        function formatMoney(amount) {
            return amount.toLocaleString('ru-RU') + ' ' + config.currency;
        }

        function selectedType() {
            return config.types.find(function (type) { return type.id === typeSelect.value; }) || config.types[0];
        }

        function calculateTotal() {
            var type = selectedType();
            var total = type.price;
            var totalDays = type.days;
            var selected = [];
            featuresContainer.querySelectorAll('input[type="checkbox"]:checked').forEach(function (checkbox) {
                var feature = type.features[Number(checkbox.dataset.index)];
                total += feature.price;
                totalDays += feature.days;
                selected.push(feature.name);
            });

            priceDisplay.textContent = formatMoney(total);
            daysDisplay.textContent = totalDays + ' дней';
            budgetInput.value = formatMoney(total);
            detailsInput.value = JSON.stringify({ type: type.name, features: selected, price: total, currency: config.currency });
        }

        function render() {
            config = window.CalculatorConfig.load();
            typeSelect.innerHTML = '';
            config.types.forEach(function (type) {
                var calcOption = document.createElement('option');
                calcOption.value = type.id;
                calcOption.textContent = type.name;
                typeSelect.appendChild(calcOption);
            });

            var activeId = typeSelect.dataset.activeType;
            typeSelect.value = config.types.some(function (type) { return type.id === activeId; })
                ? activeId
                : config.types[0].id;
            typeSelect.dataset.activeType = typeSelect.value;
            orderType.value = selectedType().name;

            var type = selectedType();
            featuresContainer.innerHTML = '';
            if (type.price > 0 || type.days > 0) {
                var base = document.createElement('p');
                base.className = 'col-12 fw-bold text-muted mb-0';
                base.textContent = 'Базовая стоимость: ' + formatMoney(type.price) +
                    (type.days ? ' · ' + type.days + ' дней' : '');
                featuresContainer.appendChild(base);
            }
            type.features.forEach(function (feature, index) {
                var col = document.createElement('div');
                col.className = 'col-md-6';
                col.innerHTML =
                    '<div class="form-check">' +
                    '<input class="form-check-input" type="checkbox" id="feature' + index + '" data-index="' + index + '" checked>' +
                    '<label class="form-check-label fw-bold" for="feature' + index + '">' +
                    escapeHtml(feature.name) +
                    '<span class="badge bg-orange ms-2">' + escapeHtml(formatMoney(feature.price)) +
                    (feature.days ? ' · ' + feature.days + ' дн.' : '') + '</span>' +
                    '</label></div>';
                featuresContainer.appendChild(col);
            });
            calculateTotal();
        }

        typeSelect.addEventListener('change', function () {
            typeSelect.dataset.activeType = typeSelect.value;
            render();
        });
        featuresContainer.addEventListener('change', calculateTotal);
        window.addEventListener('calculator-config-updated', render);
        document.querySelector('form.order-form').addEventListener('reset', function () {
            window.setTimeout(render, 0);
        });
        render();
    });
}());
