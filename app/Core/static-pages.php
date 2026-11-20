<?php

/**
 * Статические страницы (раздел «Управление страницами»): рендер тела
 * для публичной части сайта. Админ вставляет в тело произвольный HTML
 * плюс плейсхолдеры, которые подставляются при открытии страницы.
 *
 * Поддерживаемая синтаксическая разметка:
 *   {{field:banner_title}}  — значение пользовательского поля
 *                             (раздел «Пользовательские поля»);
 *   {{menu:main}}           — готовое меню из раздела «Меню»
 *                             (список <ul> с пунктами и выпадашками);
 *   {{asset:css/style.css}} — тег <link> для CSS или <script> для JS
 *                             из общих файлов (раздел «Шаблоны»,
 *                             папки /css и /js в public).
 *
 * Плейсхолдер, которому не соответствует ни один известный ресурс
 * (поле/меню/файл не найдены), подставляется пустым, а синтаксис
 * с некорректным ключом остаётся в тексте как есть — чтобы админ
 * заметил опечатку в предпросмотре.
 */
function render_static_page(string $html): string
{
    // Пользовательские поля (раздел «Пользовательские поля»)
    $html = preg_replace_callback('/\{\{\s*field\s*:\s*([A-Za-z0-9_]+)\s*\}\}/u', static function (array $m): string {
        return (string) custom_field($m[1], '');
    }, $html);

    // Меню сайта (раздел «Меню»)
    $html = preg_replace_callback('/\{\{\s*menu\s*:\s*([A-Za-z0-9_-]+)\s*\}\}/u', static function (array $m): string {
        return render_menu($m[1]);
    }, $html);

    // Общие CSS/JS (раздел «Шаблоны»)
    $html = preg_replace_callback('/\{\{\s*asset\s*:\s*([A-Za-z0-9._\/-]+)\s*\}\}/u', static function (array $m): string {
        return static_page_asset_tag($m[1]);
    }, $html);

    return $html;
}

/**
 * Тег <link>/<script> для общего файла /css или /js из public.
 * Путь вне этих папок (или несуществующий файл) — пустая строка.
 */
function static_page_asset_tag(string $path): string
{
    $path = '/' . ltrim($path, '/');
    if (preg_match('#^/(css|js)/[A-Za-z0-9._-]+\.(css|js)$#', $path) !== 1) {
        return '';
    }
    $root = realpath(APP_ROOT . '/public');
    $file = realpath(APP_ROOT . '/public' . $path);
    if ($root === false || $file === false || is_link($file)
        || strpos($file, $root . DIRECTORY_SEPARATOR) !== 0) {
        return '';
    }
    $versioned = htmlspecialchars(asset_version($path), ENT_QUOTES, 'UTF-8');
    if (substr($path, -4) === '.css') {
        return '<link rel="stylesheet" href="' . $versioned . '">';
    }
    return '<script src="' . $versioned . '"></script>';
}
