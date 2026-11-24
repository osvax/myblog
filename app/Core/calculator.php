<?php

declare(strict_types=1);

/**
 * Конфигурация калькулятора заказа: типы сайтов, функции, цены.
 * Хранится в site_settings (ключ calculatorConfig, тип json) и
 * редактируется в админке «Калькулятор»; публичная страница заказа
 * получает её с сервера.
 */
function calculator_default_config(): array
{
    return [
        'currency' => '₽',
        'types' => [
            ['id' => 'visitka', 'name' => 'Сайт-визитка', 'price' => 0, 'days' => 0, 'features' => [
                ['name' => 'До 5 страниц', 'price' => 15000, 'days' => 7],
                ['name' => 'Адаптивная вёрстка', 'price' => 5000, 'days' => 3],
                ['name' => 'Форма обратной связи', 'price' => 3000, 'days' => 2],
                ['name' => 'SEO-базовый', 'price' => 4000, 'days' => 3],
            ]],
            ['id' => 'portfolio', 'name' => 'Сайт-портфолио', 'price' => 0, 'days' => 0, 'features' => [
                ['name' => 'До 10 страниц', 'price' => 25000, 'days' => 10],
                ['name' => 'Галерея работ', 'price' => 8000, 'days' => 5],
                ['name' => 'Фильтрация по категориям', 'price' => 6000, 'days' => 4],
                ['name' => 'Анимации и эффекты', 'price' => 7000, 'days' => 5],
            ]],
            ['id' => 'blog', 'name' => 'Блог', 'price' => 0, 'days' => 0, 'features' => [
                ['name' => 'CMS-админ-панель', 'price' => 30000, 'days' => 14],
                ['name' => 'Рубрики и метки', 'price' => 8000, 'days' => 5],
                ['name' => 'Комментарии', 'price' => 5000, 'days' => 4],
                ['name' => 'Поиск по статьям', 'price' => 4000, 'days' => 3],
            ]],
            ['id' => 'shop', 'name' => 'Интернет-магазин', 'price' => 0, 'days' => 0, 'features' => [
                ['name' => 'Каталог товаров', 'price' => 40000, 'days' => 20],
                ['name' => 'Корзина и оформление', 'price' => 15000, 'days' => 10],
                ['name' => 'Онлайн-оплата', 'price' => 12000, 'days' => 8],
                ['name' => 'Управление складом', 'price' => 10000, 'days' => 7],
                ['name' => 'Личный кабинет', 'price' => 12000, 'days' => 8],
            ]],
            ['id' => 'landing', 'name' => 'Landing Page', 'price' => 0, 'days' => 0, 'features' => [
                ['name' => 'До 8 блоков', 'price' => 20000, 'days' => 7],
                ['name' => 'Форма заявки', 'price' => 5000, 'days' => 3],
                ['name' => 'Анимации при скролле', 'price' => 6000, 'days' => 4],
                ['name' => 'A/B тестирование', 'price' => 8000, 'days' => 5],
            ]],
            ['id' => 'services', 'name' => 'Сайт услуг', 'price' => 0, 'days' => 0, 'features' => [
                ['name' => 'До 10 страниц', 'price' => 35000, 'days' => 14],
                ['name' => 'Калькулятор услуг', 'price' => 10000, 'days' => 7],
                ['name' => 'Запись онлайн', 'price' => 8000, 'days' => 5],
                ['name' => 'Отзывы клиентов', 'price' => 5000, 'days' => 3],
            ]],
        ],
    ];
}

/**
 * Конфигурация калькулятора из базы; при отсутствии или повреждении
 * строки возвращаются стандартные значения.
 */
function calculator_config(): array
{
    try {
        $raw = (new \App\Models\SiteSettings())->get('calculatorConfig', '');
        if (is_string($raw) && $raw !== '') {
            $config = json_decode($raw, true);
            if (is_array($config) && isset($config['currency'], $config['types']) &&
                is_string($config['currency']) && is_array($config['types'])) {
                return $config;
            }
        }
    } catch (\Throwable $error) {
        // База недоступна — отдаём стандартный конфиг
    }
    return calculator_default_config();
}
