<?php

namespace App\Controllers\Frontend;

use App\Controllers\Controller;
use App\Core\Response;
use App\Models\ContentRepository;

class BlogController extends Controller
{
    public function index($request)
    {
        $user = current_user();
        $content = new ContentRepository();
        $articles = [];
        foreach ($content->articles(true) as $article) {
            $article['date'] = $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '';
            $articles[$article['slug']] = $article;
        }
        $categories = $content->categories();
        $categoriesMap = [];
        foreach ($categories as $cat) {
            $categoriesMap[$cat['id']] = $cat['name'];
        }

        return $this->page('frontend', 'blog', [
            'title' => 'Блог — МойБлог',
            'description' => 'Статьи о веб-разработке, дизайне и инструментах.',
            'activePage' => 'blog',
            'articles' => $articles,
            'categories' => $categoriesMap,
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
        ]);
    }

    /**
     * Страница рубрики: только статьи выбранной рубрики.
     */
    public function category($request, array $parameters = [])
    {
        $user = current_user();
        $content = new ContentRepository();
        $slug = isset($parameters['slug']) ? (string) $parameters['slug'] : '';
        $category = $content->categoryBySlug($slug);
        if (!$category) {
            return new Response('Рубрика не найдена', 404);
        }

        $articles = [];
        foreach ($content->articlesByCategory($slug) as $article) {
            $article['date'] = $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '';
            $articles[$article['slug']] = $article;
        }
        $categories = $content->categories();
        $categoriesMap = [];
        foreach ($categories as $cat) {
            $categoriesMap[$cat['id']] = $cat['name'];
        }

        return $this->page('frontend', 'blog', [
            'title' => 'Рубрика «' . $category['name'] . '» — МойБлог',
            'description' => 'Статьи рубрики «' . $category['name'] . '».',
            'activePage' => 'blog',
            'articles' => $articles,
            'categories' => $categoriesMap,
            'categoryName' => $category['name'],
            'categorySlug' => $category['slug'],
            'categoryDescription' => $category['description'] ?? '',
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
        ]);
    }

    public function article($request, $slug)
    {
        $user = current_user();
        $content = new ContentRepository();
        $article = $content->articleBySlug($slug);
        if (!$article) {
            return new Response('Статья не найдена', 404);
        }

        $article['date'] = $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '';
        $article['body'] = $this->prepareArticleBody($article['body_html'] ?? '');
        $article['readingTime'] = max(1, (int) ceil(str_word_count(strip_tags($article['body_html'] ?? '')) / 200));

        // Для статей, созданных в админке, отдельного шаблона может не быть —
        // используем универсальный dynamic-article.php
        $template = 'dynamic-article';
        if (preg_match('/^[a-z0-9-]+$/', (string) $article['slug']) &&
            is_file(APP_ROOT . '/app/Views/frontend/templates/default/pages/' . $article['slug'] . '.php')) {
            $template = $article['slug'];
        }

        $allArticles = [];
        foreach ($content->articles(true) as $a) {
            $allArticles[$a['slug']] = [
                'slug' => $a['slug'],
                'title' => $a['title'],
                'date' => $a['published_at'] ? date('d.m.Y', strtotime($a['published_at'])) : '',
            ];
        }
        $slugs = array_keys($allArticles);
        $currentIndex = array_search($slug, $slugs, true);
        $prev = $currentIndex > 0 ? $allArticles[$slugs[$currentIndex - 1]] : null;
        $next = $currentIndex !== false && $currentIndex < count($slugs) - 1 ? $allArticles[$slugs[$currentIndex + 1]] : null;

        // Рубрики для сайдбара страницы статьи (динамически из базы)
        $sidebarCategories = $content->categories();

        // Видимость комментариев настраивается в админке (Настройки → Блог → commentsAccess)
        $commentsAccess = (string) ((new \App\Models\SiteSettings())->get('commentsAccess', 'users') ?: 'users');
        $commentsVisible = match ($commentsAccess) {
            'guests' => true,
            'staff' => $user && in_array($user['role'], ['admin', 'editor', 'manager'], true),
            default => $user !== null,
        };
        $commentsCount = $content->commentsCount($slug);
        $comments = $commentsVisible ? $content->commentsByArticle($slug) : [];

        return $this->page('frontend', $template, [
            'title' => $article['title'] . ' — МойБлог',
            'description' => $article['description'],
            'activePage' => 'blog',
            'article' => $article,
            'prevArticle' => $prev,
            'nextArticle' => $next,
            'comments' => $comments,
            'commentsCount' => $commentsCount,
            'commentsVisible' => $commentsVisible,
            'sidebarCategories' => $sidebarCategories,
            // Форма комментария отправляется через AJAX forms.js с тостом,
            // иначе браузер уходит на JSON-ответ обработчика
            'includeFormsScript' => true,
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
        ]);
    }

    private function prepareArticleBody($bodyHtml)
    {
        if ($bodyHtml === null || $bodyHtml === '') {
            return '';
        }
        return preg_replace('/<img([^>]*)src=["\']uploads\//', '<img$1src="/uploads/', $bodyHtml);
    }
}
