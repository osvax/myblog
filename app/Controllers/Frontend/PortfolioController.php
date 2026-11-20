<?php

namespace App\Controllers\Frontend;

use App\Controllers\Controller;
use App\Core\Response;
use App\Models\ContentRepository;

class PortfolioController extends Controller
{
    public function index($request)
    {
        $user = current_user();
        $content = new ContentRepository();
        $settings = (new \App\Models\SiteSettings())->all();
        $projects = [];
        foreach ($content->projects(true) as $project) {
            $projects[$project['slug']] = $project;
        }

        // Работ на странице (Настройки → Портфолио)
        $perPage = max(1, min(100, (int) ($settings['portfolioPerPage'] ?? 12)));
        $total = count($projects);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($pages, (int) ($_GET['page'] ?? 1)));
        $pageProjects = array_slice($projects, ($page - 1) * $perPage, $perPage, true);

        // Сайдбар страницы портфолио (Настройки → Портфолио): список проектов
        $sidebarProjects = [];
        foreach ($projects as $sidebarProject) {
            $sidebarProjects[] = ['slug' => $sidebarProject['slug'], 'title' => $sidebarProject['title']];
        }

        return $this->page('frontend', 'portfolio', [
            'title' => 'Портфолио',
            'description' => 'Мои проекты: вёрстка, дизайн, разработка.',
            'activePage' => 'portfolio',
            'projects' => $pageProjects,
            'pagination' => ['total' => $total, 'pages' => $pages, 'current' => $page],
            'portfolioHeading' => (string) ($settings['portfolioTitle'] ?? '') ?: 'Портфолио',
            'portfolioHeadingLead' => (string) ($settings['portfolioLead'] ?? '') ?: 'Мои лучшие работы и проекты',
            'portfolioLayout' => (string) ($settings['portfolioLayout'] ?? 'grid') === 'masonry' ? 'masonry' : 'grid',
            'showPortfolioTags' => (bool) ($settings['portfolioShowTags'] ?? true),
            'showPortfolioDate' => (bool) ($settings['portfolioShowDate'] ?? true),
            'showPortfolioClient' => (bool) ($settings['portfolioShowClient'] ?? true),
            'projectsPerRow' => (int) ($settings['projectsPerRow'] ?? 3),
            'portfolioSidebar' => (string) ($settings['portfolioSidebar'] ?? 'none'),
            'sidebarProjects' => $sidebarProjects,
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
        ]);
    }

    public function project($request, $slug)
    {
        $user = current_user();
        $content = new ContentRepository();
        $project = $content->projectBySlug($slug);
        if (!$project) {
            return \App\Core\ErrorPages::page(404, 'Проект не найден', 'Такого проекта нет или он был удалён.', '/');
        }

        $project['screenshots'] = $this->parseScreenshots($project['screenshots_json'] ?? null);
        $project['results'] = $this->parseResults($project['results_json'] ?? null);

        $allProjects = [];
        foreach ($content->projects(true) as $p) {
            $allProjects[$p['slug']] = [
                'slug' => $p['slug'],
                'title' => $p['title'],
            ];
        }
        $slugs = array_keys($allProjects);
        $currentIndex = array_search($slug, $slugs, true);
        $prev = $currentIndex > 0 ? $allProjects[$slugs[$currentIndex - 1]] : null;
        $next = $currentIndex !== false && $currentIndex < count($slugs) - 1 ? $allProjects[$slugs[$currentIndex + 1]] : null;

        // Остальные опубликованные проекты для карточки «Другие проекты»
        $otherProjects = [];
        foreach ($allProjects as $otherSlug => $other) {
            if ($otherSlug === $slug) {
                continue;
            }
            $otherProjects[] = $other;
        }

        // Для проектов, созданных в админке, отдельного шаблона может не быть —
        // используем универсальный dynamic-project.php
        $template = 'dynamic-project';
        if (preg_match('/^[a-z0-9-]+$/', (string) $project['slug']) &&
            is_file(APP_ROOT . '/app/Views/frontend/templates/default/pages/' . $project['slug'] . '.php')) {
            $template = $project['slug'];
        }

        return $this->page('frontend', $template, [
            'title' => $project['title'],
            'description' => $project['description'],
            'activePage' => 'portfolio',
            'project' => $project,
            'otherProjects' => $otherProjects,
            'prevProject' => $prev,
            'nextProject' => $next,
            // Сайдбар страницы проекта (Настройки → Портфолио)
            'projectPageSidebar' => (string) ((new \App\Models\SiteSettings())->get('projectPageSidebar', 'right') ?: 'right'),
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
        ]);
    }

    public function publicProject($request, $slug)
    {
        $user = current_user();
        $content = new ContentRepository();
        $project = $content->projectBySlug($slug);
        if (!$project) {
            return \App\Core\ErrorPages::page(404, 'Проект не найден', 'Такого проекта нет или он был удалён.', '/');
        }

        $project['screenshots'] = $this->parseScreenshots($project['screenshots_json'] ?? null);
        $project['results'] = $this->parseResults($project['results_json'] ?? null);

        return $this->page('frontend', $slug, [
            'title' => $project['title'],
            'description' => $project['description'],
            'activePage' => 'portfolio',
            'project' => $project,
            'otherProjects' => [],
            // Сайдбар страницы проекта (Настройки → Портфолио)
            'projectPageSidebar' => (string) ((new \App\Models\SiteSettings())->get('projectPageSidebar', 'right') ?: 'right'),
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
        ]);
    }

    public function publicArticle($request, $slug)
    {
        $user = current_user();
        $content = new ContentRepository();
        $article = $content->articleBySlug($slug);
        if (!$article) {
            return \App\Core\ErrorPages::page(404, 'Статья не найдена', 'Такой статьи нет или она была удалена.', '/blog.html');
        }

        $article['date'] = $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '';
        $article['body'] = $this->prepareArticleBody($article['body_html'] ?? '');

        return $this->page('frontend', $slug, [
            'title' => $article['title'],
            'description' => $article['description'],
            'activePage' => 'blog',
            'article' => $article,
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
        ]);
    }

    public function indexPage($request, $pageSlug)
    {
        $user = current_user();
        $allowedPages = ['messages', 'orders', 'settings'];
        if (!in_array($pageSlug, $allowedPages, true)) {
            return \App\Core\ErrorPages::page(404, 'Страница не найдена', 'Такой страницы нет или она была перемещена.', '/');
        }

        return $this->page('frontend', $pageSlug, [
            'title' => ucfirst($pageSlug),
            'activePage' => $pageSlug,
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
        ]);
    }

    private function parseScreenshots($json)
    {
        if ($json === null || $json === '') {
            return [];
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function parseResults($json)
    {
        if ($json === null || $json === '') {
            return [];
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function prepareArticleBody($bodyHtml)
    {
        if ($bodyHtml === null || $bodyHtml === '') {
            return '';
        }
        return preg_replace('/<img([^>]*)src=["\']uploads\//', '<img$1src="/uploads/', $bodyHtml);
    }
}
