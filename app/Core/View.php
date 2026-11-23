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

        $templateKey = $area === 'frontend' ? 'frontendTemplate' : 'adminTemplate';
        try {
            $theme = (string) (new \App\Models\SiteSettings())->get($templateKey, 'default');
        } catch (\Throwable $error) {
            $theme = 'default';
        }
        if (!preg_match('/^[a-z0-9_-]+$/i', $theme)) {
            $theme = 'default';
        }
        $templateDirectory = APP_ROOT . '/app/Views/' . $area . '/templates/' . $theme;
        if (!is_dir($templateDirectory)) {
            $theme = 'default';
        }

        $base = $area . '/templates/' . $theme;
        // Название сайта, SEO-настройки и текст подвала — из настроек; контроллеры могут переопределить
        if ($area === 'frontend') {
            try {
                $siteSettings = new \App\Models\SiteSettings();
                $data['siteName'] = $data['siteName'] ?? ((string) $siteSettings->get('siteName', '') ?: 'МойБлог');
                $data['footerText'] = $data['footerText'] ?? ((string) $siteSettings->get('footerText', '') ?: '© {year} МойБлог. Все права защищены.');
                $data['siteTitle'] = $data['siteTitle'] ?? trim((string) $siteSettings->get('siteTitle', ''));
                $data['siteDescription'] = $data['siteDescription'] ?? trim((string) $siteSettings->get('siteDescription', ''));
                $data['siteLanguage'] = $data['siteLanguage'] ?? trim((string) $siteSettings->get('siteLanguage', ''));
                $data['siteKeywords'] = $data['siteKeywords'] ?? trim((string) $siteSettings->get('keywords', ''));
            } catch (\Throwable $error) {
                $data['siteName'] = $data['siteName'] ?? 'МойБлог';
                $data['footerText'] = $data['footerText'] ?? '© {year} МойБлог. Все права защищены.';
            }
        }

        // «Оформление» (Настройки → Оформление): тема цветов и шрифта
        // для сайта и админки — инлайн-блок в <head> лейаута
        try {
            self::applyTheme($data);
        } catch (\Throwable $themeError) {
            // Без темы сайт работает на стандартных цветах
        }

        // Vite manifest for production asset loading
        $data['viteManifest'] = \vite_manifest();

        $pageTemplate = $base . '/pages/' . $page;
        if (!is_file(APP_ROOT . '/app/Views/' . $pageTemplate . '.php')) {
            $pageTemplate = $area . '/templates/default/pages/' . $page;
        }
        $data['content'] = self::capture($pageTemplate, $data);

        // Заголовок вкладки: страница передаёт свою часть, лейаут прибивает siteTitle
        $data['title'] = $data['title'] ?? ($area === 'admin' ? 'Администрирование' : (trim((string) ($data['siteTitle'] ?? '')) !== '' ? $data['siteTitle'] : 'МойБлог'));
        // Meta description: в первую очередь «Описание сайта (SEO description)» из настроек,
        // description страницы — только запасной вариант при пустой настройке
        $siteDescription = trim((string) ($data['siteDescription'] ?? ''));
        $data['description'] = $siteDescription !== ''
            ? $siteDescription
            : ($data['description'] ?? 'Личный блог о веб-разработке и дизайне.');
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
        
        // Vite manifest for production asset loading
        $data['viteManifest'] = \vite_manifest();
        
        return self::render($base . '/layout', $data);
    }

    /**
     * Тема «Оформление» из site_settings: цвета (с валидацией #rrggbb)
     * и шрифт (CDN-ссылка или загруженный файл). Лейаут выводит это
     * в <style> в <head>; SCSS ссылается на переменные через var() с фолбэками.
     */
    private static function applyTheme(array &$data): void
    {
        $all = (new \App\Models\SiteSettings())->all();
        $color = static function (string $key, string $default) use ($all): string {
            $value = trim((string) ($all[$key] ?? ''));
            return preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1 ? $value : $default;
        };

        $themePrimary = $color('themePrimary', '#ff8c1a');
        $data['themePrimary'] = $themePrimary;
        // Светлый тон основного цвета (для фоновых секций и волн)
        $data['themePrimaryLight'] = self::tintColor($themePrimary, 0.85);
        $data['themeBackground'] = $color('themeBackground', '#ffffff');
        $data['themeBlockBg'] = $color('themeBlockBg', '#ffffff');
        $data['themeSidebarBg'] = $color('themeSidebarBg', '#ffffff');
        $data['themeText'] = $color('themeText', '#353535');

        // Размеры: базовый текст (px), меню (px), заголовки (масштаб от 100%)
        $num = static function (string $key, int $default, int $min, int $max) use ($all): int {
            $value = (int) round((float) ($all[$key] ?? $default));
            return max($min, min($max, $value));
        };
        $data['themeFontSize'] = $num('themeFontSize', 16, 12, 24);
        $data['themeNavSize'] = $num('themeNavSize', 16, 10, 24);
        $data['themeHeadingScale'] = $num('themeHeadingScale', 100, 60, 200) / 100;

        // Шрифт: имя семейства + источник (CDN-ссылка или файл из /uploads/fonts/)
        $data['themeFontFamily'] = trim((string) ($all['themeFontFamily'] ?? ''));
        $data['themeFontUrl'] = trim((string) ($all['themeFontUrl'] ?? ''));
        $data['themeFontFile'] = trim((string) ($all['themeFontFile'] ?? ''));
    }

    /**
     * Смешать цвет с белым: $whiteRatio 0.0–1.0 (1.0 — чистый белый).
     */
    private static function tintColor(string $hex, float $whiteRatio): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return '#fff3e6';
        }
        $mix = static function (string $part) use ($whiteRatio): int {
            return (int) round(hexdec($part) + (255 - hexdec($part)) * $whiteRatio);
        };
        return sprintf('#%02x%02x%02x', $mix(substr($hex, 0, 2)), $mix(substr($hex, 2, 2)), $mix(substr($hex, 4, 2)));
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
