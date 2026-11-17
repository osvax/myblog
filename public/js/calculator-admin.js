(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('calculatorSettingsForm');
        var typeList = document.getElementById('calculatorTypeList');
        var typeSelect = document.getElementById('editType');
        var typeNameInput = document.getElementById('editTypeName');
        var currencyInput = document.getElementById('editCurrency');
        var basePrice = document.getElementById('editBasePrice');
        var baseDays = document.getElementById('editBaseDays');
        var featureList = document.getElementById('calculatorFeatureList');
        var addTypeForm = document.getElementById('addTypeForm');
        var addFeatureButton = document.getElementById('addCalculatorFeature');
        var config = window.CalculatorConfig.load();
        var editingTypeId = null;

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, function (char) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
            });
        }

        function money(value) {
            return Number(value).toLocaleString('ru-RU') + ' ' + config.currency;
        }

        function persist(message) {
            try {
                config.currency = currencyInput.value.trim();
                window.CalculatorConfig.save(config);
                render();
                if (message) window.AdminNotifications.show(true, message);
                return true;
            } catch (error) {
                window.AdminNotifications.show(false, error.message);
                return false;
            }
        }

        function render() {
            typeSelect.innerHTML = config.types.map(function (type) {
                return '<option value="' + escapeHtml(type.id) + '">' + escapeHtml(type.name) + '</option>';
            }).join('');
            typeList.innerHTML = config.types.map(function (type) {
                return '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                    '<span class="fw-bold">' + escapeHtml(type.name) + '</span>' +
                    '<span><span class="text-muted me-2">' + escapeHtml(money(type.price)) + '</span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger" data-delete-type="' +
                    escapeHtml(type.id) + '" aria-label="Удалить ' + escapeHtml(type.name) + '">' +
                    '<i class="bi bi-trash"></i></button></span></li>';
            }).join('');
            currencyInput.value = config.currency;
            renderEditor();
        }

        function renderEditor() {
            var type = config.types.find(function (item) { return item.id === typeSelect.value; }) || config.types[0];
            if (!type) {
                editingTypeId = null;
                featureList.innerHTML = '<p class="text-muted mb-0">Добавьте тип сайта, чтобы настроить функции.</p>';
                basePrice.value = '';
                baseDays.value = '';
                return;
            }
            typeSelect.value = type.id;
            editingTypeId = type.id;
            typeNameInput.value = type.name;
            basePrice.value = type.price;
            baseDays.value = type.days;
            featureList.innerHTML = type.features.map(function (feature, index) {
                return '<div class="row g-2 align-items-center mb-2" data-feature-index="' + index + '">' +
                    '<div class="col-md-6"><input class="form-control" data-feature-name required value="' +
                    escapeHtml(feature.name) + '" aria-label="Название функции"></div>' +
                    '<div class="col-5 col-md-2"><input class="form-control" type="number" min="0" step="1" required data-feature-price value="' +
                    feature.price + '" aria-label="Цена"></div>' +
                    '<div class="col-5 col-md-2"><input class="form-control" type="number" min="0" step="1" required data-feature-days value="' +
                    feature.days + '" aria-label="Срок в днях"></div>' +
                    '<div class="col-2"><button type="button" class="btn btn-outline-danger btn-action" data-delete-feature="' +
                    index + '" aria-label="Удалить функцию"><i class="bi bi-trash"></i></button></div></div>';
            }).join('');
        }

        function captureEditor() {
            var type = config.types.find(function (item) { return item.id === editingTypeId; });
            if (!type) return;
            type.name = typeNameInput.value.trim();
            type.price = Number(basePrice.value);
            type.days = Number(baseDays.value);
            type.features = Array.from(featureList.querySelectorAll('[data-feature-index]')).map(function (row) {
                return {
                    name: row.querySelector('[data-feature-name]').value.trim(),
                    price: Number(row.querySelector('[data-feature-price]').value),
                    days: Number(row.querySelector('[data-feature-days]').value)
                };
            });
            typeNameInput.value = type.name;
        }

        function saveConfig() {
            captureEditor();
            if (config.types.some(function (type) {
                return type.id !== editingTypeId && type.name.toLocaleLowerCase() === typeNameInput.value.trim().toLocaleLowerCase();
            })) {
                window.AdminNotifications.show(false, 'Тип с таким названием уже существует');
                return false;
            }
            return persist('Настройки калькулятора сохранены в этом браузере');
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            if (!form.reportValidity()) return;
            saveConfig();
        });
        typeSelect.addEventListener('change', function () {
            captureEditor();
            renderEditor();
        });
        typeList.addEventListener('click', function (event) {
            var button = event.target.closest('[data-delete-type]');
            if (!button) return;
            if (config.types.length === 1) {
                window.AdminNotifications.show(false, 'Должен остаться хотя бы один тип сайта');
                return;
            }
            captureEditor();
            config.types = config.types.filter(function (type) { return type.id !== button.dataset.deleteType; });
            persist('Тип сайта удалён');
        });
        featureList.addEventListener('click', function (event) {
            var button = event.target.closest('[data-delete-feature]');
            if (!button) return;
            var type = config.types.find(function (item) { return item.id === typeSelect.value; });
            type.features.splice(Number(button.dataset.deleteFeature), 1);
            renderEditor();
            window.AdminNotifications.show('warning', 'Функция удалена из формы. Сохраните настройки, чтобы применить изменение.');
        });
        addTypeForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var name = document.getElementById('newTypeName').value.trim();
            if (!name || !addTypeForm.reportValidity()) return;
            if (config.types.some(function (type) { return type.name.toLocaleLowerCase() === name.toLocaleLowerCase(); })) {
                window.AdminNotifications.show(false, 'Тип с таким названием уже существует');
                return;
            }
            captureEditor();
            var id = 'type-' + Date.now().toString(36);
            config.types.push({ id: id, name: name, price: 0, days: 0, features: [] });
            if (!persist('Тип сайта добавлен')) return;
            typeSelect.value = id;
            renderEditor();
            addTypeForm.reset();
        });
        addFeatureButton.addEventListener('click', function () {
            var nameInput = document.getElementById('newFeatureName');
            var priceInput = document.getElementById('newFeaturePrice');
            var daysInput = document.getElementById('newFeatureDays');
            if (!nameInput.value.trim() || priceInput.value === '' || daysInput.value === '' ||
                !priceInput.reportValidity() || !daysInput.reportValidity()) {
                window.AdminNotifications.show(false, 'Укажите название, цену и срок функции');
                return;
            }
            var type = config.types.find(function (item) { return item.id === typeSelect.value; });
            if (!type) return;
            type.features.push({
                name: nameInput.value.trim(),
                price: Number(priceInput.value),
                days: Number(daysInput.value)
            });
            renderEditor();
            nameInput.value = '';
            priceInput.value = '0';
            daysInput.value = '0';
            window.AdminNotifications.show('info', 'Функция добавлена. Сохраните настройки, чтобы применить изменение.');
        });
        document.getElementById('resetCalculator').addEventListener('click', function () {
            if (!window.confirm('Вернуть стандартные настройки калькулятора?')) return;
            window.CalculatorConfig.reset();
            config = window.CalculatorConfig.load();
            render();
            window.AdminNotifications.show('warning', 'Стандартные настройки восстановлены в этом браузере');
        });

        render();
    });
}());
