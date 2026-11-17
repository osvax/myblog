<?php

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
