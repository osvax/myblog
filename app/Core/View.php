<?php

namespace App\Core;

use RuntimeException;

class View
{
    public static function render($template, array $data = [])
    {
        return new Response(self::capture($template, $data), 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public static function renderPage($area, $page, array $data = [])
    {
        if (!in_array($area, ['frontend', 'admin'], true) ||
            !preg_match('/^[a-z0-9_-]+$/i', $page)) {
            throw new RuntimeException('Invalid view area or page.');
        }

        $configPath = APP_ROOT . '/config/views.php';
        $config = is_file($configPath) ? require $configPath : [];
        $theme = is_array($config) ? ($config[$area . '_template'] ?? 'default') : 'default';
        if (!is_string($theme) || !preg_match('/^[a-z0-9_-]+$/i', $theme)) {
            throw new RuntimeException('Invalid view template configuration.');
        }

        $base = $area . '/templates/' . $theme;
        $data['content'] = self::capture($base . '/pages/' . $page, $data);
        $data['title'] = $data['title'] ?? ($area === 'admin' ? 'Администрирование' : 'МойБлог');
        return self::render($base . '/layout', $data);
    }

    public static function renderStandaloneAdminPage($page, array $data = [])
    {
        if (!preg_match('/^[a-z0-9_-]+$/i', $page)) {
            throw new RuntimeException('Invalid standalone admin page.');
        }

        $configPath = APP_ROOT . '/config/admin-pages.php';
        $config = is_file($configPath) ? require $configPath : [];
        if (!isset($config[$page]['standalone']) || $config[$page]['standalone'] !== true) {
            throw new RuntimeException('Standalone admin page is not configured.');
        }

        $base = 'admin/templates/default/standalone';
        $data['content'] = self::capture($base . '/' . $page, $data);
        $data['pageScripts'] = [];
        foreach ($config[$page]['scripts'] ?? [] as $script) {
            if ($page === 'login' && $script === 'js/admin.js') {
                continue;
            }
            if (strpos($script, '://') !== false || strpos($script, '/') === 0) {
                $data['pageScripts'][] = $script;
            } elseif (strpos($script, '../') === 0) {
                $data['pageScripts'][] = '/' . ltrim(substr($script, 3), '/');
            } else {
                $data['pageScripts'][] = '/admin/' . ltrim($script, '/');
            }
        }
        $data['title'] = $data['title'] ?? ($page === 'login' ? 'Авторизация — Админ-панель' : 'Администрирование');
        return self::render($base . '/layout', $data);
    }

    private static function capture($template, array $data)
    {
        if (!preg_match('#^[a-z0-9_/-]+$#i', $template)) {
            throw new RuntimeException('Invalid view path.');
        }
        $path = APP_ROOT . '/app/Views/' . $template . '.php';
        if (!is_file($path)) {
            throw new RuntimeException('View not found: ' . $template);
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        return ob_get_clean();
    }
}
