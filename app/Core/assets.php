<?php

declare(strict_types=1);

/**
 * Версионированный URL статического файла: ?v=<mtime>.
 * При каждом изменении файла браузер получает свежую версию вместо кэша.
 *
 * Понимает пути конфига страниц: '../js/x.js' → '/js/x.js',
 * 'js/x.js' → '/admin/js/x.js', абсолютные и CDN-ссылки не трогает.
 *
 * @param string $path
 * @return string
 */
function asset_version(string $path): string
{
    $path = (string) $path;
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    if ($path[0] !== '/') {
        if (str_starts_with($path, '../')) {
            $path = '/' . ltrim(preg_replace('#^(?:\.\./)+#', '', $path), '/');
        } else {
            $path = '/admin/' . ltrim($path, '/');
        }
    }
    $file = APP_ROOT . '/public' . $path;
    return is_file($file) ? $path . '?v=' . filemtime($file) : $path;
}

/**
 * Загружает Vite manifest.json для production сборки.
 * Возвращает массив или null если manifest не найден.
 */
function vite_manifest(): ?array
{
    static $manifest = null;

    if ($manifest !== null) {
        return $manifest;
    }

    // Vite outputs manifest.json in .vite subfolder within outDir
    $manifestPath = APP_ROOT . '/public/build/.vite/manifest.json';

    if (!is_file($manifestPath)) {
        return $manifest = null;
    }

    $content = file_get_contents($manifestPath);
    $manifest = json_decode($content, true);

    return $manifest ?? null;
}

/**
 * Проверяет, доступен ли Vite manifest (production сборка).
 */
function has_vite_manifest(): bool
{
    return vite_manifest() !== null;
}

/**
 * Получает URL для Vite entry point в production.
 */
function vite_asset(string $entry): string
{
    $manifest = vite_manifest();

    if (!$manifest || !isset($manifest[$entry])) {
        return '';
    }

    return asset_version('/build/' . $manifest[$entry]['file']);
}

/**
 * Получает сырые CSS пути из манифеста для entry point (включая импорты).
 *
 * @return string[] пути вида 'assets/xxx.css'
 */
function vite_css_raw(string $entry): array
{
    $manifest = vite_manifest();

    if (!$manifest || !isset($manifest[$entry])) {
        return [];
    }

    $css = $manifest[$entry]['css'] ?? [];

    // Рекурсивно собираем CSS из импортов
    foreach ($manifest[$entry]['imports'] ?? [] as $import) {
        $css = array_merge($css, vite_css_raw($import));
    }

    return array_unique($css);
}

/**
 * Получает CSS URLs для Vite entry point (включая CSS из импортов).
 *
 * @return string[]
 */
function vite_css(string $entry): array
{
    $raw = vite_css_raw($entry);

    return array_map('asset_version', array_map(
        fn ($c) => '/build/' . $c,
        $raw
    ));
}

/**
 * Получает сырые IDs импортов из манифеста для entry point рекурсивно.
 *
 * @return string[] IDs вида 'resources/js/xxx.js' или '_forms-BIuUhW4a.js'
 */
function vite_imports_raw(string $entry): array
{
    $manifest = vite_manifest();

    if (!$manifest || !isset($manifest[$entry])) {
        return [];
    }

    $imports = $manifest[$entry]['imports'] ?? [];
    $allImports = $imports;

    // Рекурсивно собираем все транзитивные импорты
    foreach ($imports as $import) {
        $allImports = array_merge($allImports, vite_imports_raw($import));
    }

    return array_unique($allImports);
}

/**
 * Получает URLs импортов для Vite entry point рекурсивно (для modulepreload).
 *
 * @return string[]
 */
function vite_imports(string $entry): array
{
    $manifest = vite_manifest();
    if (!$manifest) {
        return [];
    }

    $raw = vite_imports_raw($entry);

    return array_map(
        fn ($import) => asset_version('/build/' . $manifest[$import]['file']),
        $raw
    );
}

/**
 * Рендерит теги для Vite entry point (CSS + JS + modulepreload).
 */
function vite_entry_tags(string $entry): string
{
    $manifest = vite_manifest();

    if (!$manifest || !isset($manifest[$entry])) {
        return '';
    }

    $tags = [];

    // CSS
    foreach (vite_css($entry) as $css) {
        $tags[] = '<link rel="stylesheet" href="' . htmlspecialchars($css, ENT_QUOTES, 'UTF-8') . '">';
    }

    // Module preload for imports
    foreach (vite_imports($entry) as $import) {
        $tags[] = '<link rel="modulepreload" href="' . htmlspecialchars($import, ENT_QUOTES, 'UTF-8') . '">';
    }

    // JS entry
    $jsUrl = vite_asset($entry);
    if ($jsUrl) {
        $tags[] = '<script type="module" src="' . htmlspecialchars($jsUrl, ENT_QUOTES, 'UTF-8') . '"></script>';
    }

    return implode("\n", $tags);
}

/**
 * Глобальная переменная для шаблонов - Vite manifest.
 */
function get_vite_manifest(): ?array
{
    return vite_manifest();
}
