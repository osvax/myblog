(function () {
    'use strict';

    var storageKey = 'myblog.calculator.config.v1';
    var defaults = {
        currency: '₽',
        types: [
            { id: 'visitka', name: 'Сайт-визитка', price: 0, days: 0, features: [
                { name: 'До 5 страниц', price: 15000, days: 7 },
                { name: 'Адаптивная вёрстка', price: 5000, days: 3 },
                { name: 'Форма обратной связи', price: 3000, days: 2 },
                { name: 'SEO-базовый', price: 4000, days: 3 }
            ] },
            { id: 'portfolio', name: 'Сайт-портфолио', price: 0, days: 0, features: [
                { name: 'До 10 страниц', price: 25000, days: 10 },
                { name: 'Галерея работ', price: 8000, days: 5 },
                { name: 'Фильтрация по категориям', price: 6000, days: 4 },
                { name: 'Анимации и эффекты', price: 7000, days: 5 }
            ] },
            { id: 'blog', name: 'Блог', price: 0, days: 0, features: [
                { name: 'CMS-админ-панель', price: 30000, days: 14 },
                { name: 'Рубрики и метки', price: 8000, days: 5 },
                { name: 'Комментарии', price: 5000, days: 4 },
                { name: 'Поиск по статьям', price: 4000, days: 3 }
            ] },
            { id: 'shop', name: 'Интернет-магазин', price: 0, days: 0, features: [
                { name: 'Каталог товаров', price: 40000, days: 20 },
                { name: 'Корзина и оформление', price: 15000, days: 10 },
                { name: 'Онлайн-оплата', price: 12000, days: 8 },
                { name: 'Управление складом', price: 10000, days: 7 },
                { name: 'Личный кабинет', price: 12000, days: 8 }
            ] },
            { id: 'landing', name: 'Landing Page', price: 0, days: 0, features: [
                { name: 'До 8 блоков', price: 20000, days: 7 },
                { name: 'Форма заявки', price: 5000, days: 3 },
                { name: 'Анимации при скролле', price: 6000, days: 4 },
                { name: 'A/B тестирование', price: 8000, days: 5 }
            ] },
            { id: 'services', name: 'Сайт услуг', price: 0, days: 0, features: [
                { name: 'До 10 страниц', price: 35000, days: 14 },
                { name: 'Калькулятор услуг', price: 10000, days: 7 },
                { name: 'Запись онлайн', price: 8000, days: 5 },
                { name: 'Отзывы клиентов', price: 5000, days: 3 }
            ] }
        ]
    };

    function validConfig(value) {
        return value && typeof value.currency === 'string' && value.currency.trim() &&
            Array.isArray(value.types) && value.types.length > 0 &&
            value.types.every(function (type) {
                return typeof type.id === 'string' && typeof type.name === 'string' &&
                    Number.isFinite(type.price) && type.price >= 0 &&
                    Number.isFinite(type.days) && type.days >= 0 &&
                    Array.isArray(type.features) && type.features.every(function (feature) {
                        return typeof feature.name === 'string' && feature.name.trim() &&
                            Number.isFinite(feature.price) && feature.price >= 0 &&
                            Number.isFinite(feature.days) && feature.days >= 0;
                    });
            });
    }

    function defaultConfig() {
        return JSON.parse(JSON.stringify(defaults));
    }

    window.CalculatorConfig = {
        load: function () {
            try {
                var stored = localStorage.getItem(storageKey);
                var config = stored ? JSON.parse(stored) : defaultConfig();
                return validConfig(config) ? config : defaultConfig();
            } catch (error) {
                console.error('Не удалось загрузить настройки калькулятора.', error);
                return defaultConfig();
            }
        },
        save: function (config) {
            if (!validConfig(config)) {
                throw new Error('Проверьте настройки типов, функций, цен и сроков.');
            }
            localStorage.setItem(storageKey, JSON.stringify(config));
            window.dispatchEvent(new CustomEvent('calculator-config-updated'));
        },
        reset: function () {
            localStorage.removeItem(storageKey);
            window.dispatchEvent(new CustomEvent('calculator-config-updated'));
        }
    };
}());
