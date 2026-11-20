<?php

namespace App\Controllers\Frontend;

use App\Controllers\Controller;

/**
 * SEO-генераторы: /robots.txt и /sitemap.xml.
 * Поведение управляют настройки (Настройки сайта → SEO):
 * «Разрешить индексацию» и «Генерировать sitemap.xml».
 */
class SeoController extends Controller
{
    /**
     * robots.txt: при выключенной индексации закрываем весь сайт,
     * иначе — служебные разделы; при включённом sitemap — ссылка на него.
     */
    public function robots($request)
    {
        $settings = new \App\Models\SiteSettings();
        $indexing = (bool) $settings->get('robots', true);
        $sitemapOn = (bool) $settings->get('sitemap', true);

        $lines = ['User-agent: *'];
        if (!$indexing) {
            $lines[] = 'Disallow: /';
        } else {
            $lines[] = 'Disallow: /admin/';
            $lines[] = 'Disallow: /api/';
            $lines[] = 'Disallow: /login.html';
            $lines[] = 'Disallow: /register.html';
            $lines[] = 'Disallow: /profile.html';
        }
        if ($indexing && $sitemapOn) {
            $lines[] = '';
            $lines[] = 'Sitemap: ' . $this->baseUrl() . '/sitemap.xml';
        }

        return new \App\Core\Response(implode("\n", $lines) . "\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }

    /**
     * sitemap.xml: опубликованные статьи, проекты, рубрики и основные страницы.
     */
    public function sitemap($request)
    {
        $settings = new \App\Models\SiteSettings();
        if (!(bool) $settings->get('sitemap', true)) {
            return new \App\Core\Response('sitemap.xml выключен в настройках сайта', 404, [
                'Content-Type' => 'text/plain; charset=utf-8',
            ]);
        }

        $content = new \App\Models\ContentRepository();
        $today = date('Y-m-d');
        $urls = [
            ['loc' => '/', 'lastmod' => $today],
            ['loc' => '/blog.html', 'lastmod' => $today],
            ['loc' => '/portfolio.html', 'lastmod' => $today],
            ['loc' => '/contacts.html', 'lastmod' => $today],
            ['loc' => '/order.html', 'lastmod' => $today],
        ];

        foreach ($content->categories() as $category) {
            $urls[] = ['loc' => '/category/' . rawurlencode((string) $category['slug']) . '.html', 'lastmod' => $today];
        }

        foreach ($content->articles(true) as $article) {
            $urls[] = [
                'loc' => '/' . rawurlencode((string) $article['slug']) . '.html',
                'lastmod' => $this->lastmod($article['published_at'] ?? null, $article['updated_at'] ?? null),
            ];
        }

        foreach ($content->projects(true) as $project) {
            $urls[] = [
                'loc' => '/' . rawurlencode((string) $project['slug']) . '.html',
                'lastmod' => $this->lastmod($project['updated_at'] ?? null),
            ];
        }

        $base = $this->baseUrl();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($base . $url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        $xml .= '</urlset>';

        return new \App\Core\Response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    /**
     * Абсолютный базовый URL текущего хоста.
     */
    private function baseUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'myblog.loc';
        return $scheme . '://' . $host;
    }

    /**
     * Валидная дата W3C для <lastmod>; некорректная — сегодняшним днём.
     */
    private function lastmod($publishedAt = null, $updatedAt = null): string
    {
        foreach ([$publishedAt, $updatedAt] as $value) {
            $value = (string) $value;
            if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $value, $matches) === 1) {
                return $matches[1];
            }
        }
        return date('Y-m-d');
    }
}
