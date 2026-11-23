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
        $settings = (new \App\Models\SiteSettings())->all();
        $perPage = max(1, min(100, (int) ($settings['articlePerPage'] ?? 12)));

        $all = [];
        foreach ($content->articles(true) as $article) {
            $article['date'] = $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '';
            $all[$article['slug']] = $article;
        }
        // Сортировка — из настроек (Настройки → Статьи)
        $sortOrder = (string) ($settings['articleSortOrder'] ?? 'newest');
        if ($sortOrder === 'oldest') {
            uasort($all, static function (array $a, array $b): int {
                return strcmp((string) $a['published_at'], (string) $b['published_at']);
            });
        } elseif ($sortOrder === 'popular') {
            $commentCounts = $content->commentCounts();
            uasort($all, static function (array $a, array $b) use ($commentCounts): int {
                $countA = (int) ($commentCounts[$a['slug']] ?? 0);
                $countB = (int) ($commentCounts[$b['slug']] ?? 0);
                if ($countA !== $countB) {
                    return $countB <=> $countA;
                }
                return strcmp((string) $a['published_at'], (string) $b['published_at']);
            });
        }
        $total = count($all);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($pages, (int) ($_GET['page'] ?? 1)));
        $articles = array_slice($all, ($page - 1) * $perPage, $perPage, true);
        $categories = $content->categories();
        $categoriesMap = [];
        foreach ($categories as $cat) {
            $categoriesMap[$cat['id']] = $cat['name'];
        }

        // Сайдбар страницы списка (Настройки → Статьи): свежие записи, до 5
        $latestForSidebar = [];
        foreach ($all as $latest) {
            $latestForSidebar[] = ['slug' => $latest['slug'], 'title' => $latest['title']];
            if (count($latestForSidebar) >= 5) {
                break;
            }
        }

        return $this->page('frontend', 'blog', [
            'title' => 'Блог',
            'description' => 'Статьи о веб-разработке, дизайне и инструментах.',
            'activePage' => 'blog',
            'articles' => $articles,
            'categories' => $categoriesMap,
            'pagination' => ['total' => $total, 'pages' => $pages, 'current' => $page],
            'pageScripts' => ['/js/blog-search.js'],
            'blogHeading' => (string) ($settings['blogTitle'] ?? '') ?: 'Блог',
            'blogHeadingLead' => (string) ($settings['blogLead'] ?? '') ?: 'Последние записи и полезные материалы',
            'articleExcerptLength' => max(50, min(2000, (int) ($settings['articleExcerptLength'] ?? 300) ?: 300)),
            'showArticleCategories' => (bool) ($settings['articleCategoriesEnabled'] ?? true),
            'articlesPerRow' => (int) ($settings['articlesPerRow'] ?? 3),
            'blogSidebar' => (string) ($settings['blogSidebar'] ?? 'none'),
            'sidebarCategories' => $categories,
            'latestArticles' => $latestForSidebar,
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
            return \App\Core\ErrorPages::page(404, 'Рубрика не найдена', 'Такой рубрики нет или она была переименована.', '/blog.html');
        }

        $settings = (new \App\Models\SiteSettings())->all();
        $perPage = max(1, min(100, (int) ($settings['articlePerPage'] ?? 12)));

        $all = [];
        foreach ($content->articlesByCategory($slug) as $article) {
            $article['date'] = $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '';
            $all[$article['slug']] = $article;
        }
        $sortOrder = (string) ($settings['articleSortOrder'] ?? 'newest');
        if ($sortOrder === 'oldest') {
            uasort($all, static function (array $a, array $b): int {
                return strcmp((string) $a['published_at'], (string) $b['published_at']);
            });
        } elseif ($sortOrder === 'popular') {
            $commentCounts = $content->commentCounts();
            uasort($all, static function (array $a, array $b) use ($commentCounts): int {
                $countA = (int) ($commentCounts[$a['slug']] ?? 0);
                $countB = (int) ($commentCounts[$b['slug']] ?? 0);
                if ($countA !== $countB) {
                    return $countB <=> $countA;
                }
                return strcmp((string) $a['published_at'], (string) $b['published_at']);
            });
        }
        $total = count($all);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($pages, (int) ($_GET['page'] ?? 1)));
        $articles = array_slice($all, ($page - 1) * $perPage, $perPage, true);

        $categories = $content->categories();
        $categoriesMap = [];
        foreach ($categories as $cat) {
            $categoriesMap[$cat['id']] = $cat['name'];
        }

        // Сайдбар страницы рубрики (Настройки → Статьи): свежие записи, до 5
        $latestForSidebar = [];
        foreach ($all as $latest) {
            $latestForSidebar[] = ['slug' => $latest['slug'], 'title' => $latest['title']];
            if (count($latestForSidebar) >= 5) {
                break;
            }
        }

        return $this->page('frontend', 'blog', [
            'title' => 'Рубрика «' . $category['name'] . '»',
            'description' => 'Статьи рубрики «' . $category['name'] . '».',
            'activePage' => 'blog',
            'articles' => $articles,
            'categories' => $categoriesMap,
            'categoryName' => $category['name'],
            'categorySlug' => $category['slug'],
            'categoryDescription' => $category['description'] ?? '',
            'categoryNavigation' => [],
            'pagination' => ['total' => $total, 'pages' => $pages, 'current' => $page],
            'pageScripts' => ['/js/blog-search.js'],
            'articleExcerptLength' => max(50, min(2000, (int) ($settings['articleExcerptLength'] ?? 300) ?: 300)),
            'showArticleCategories' => (bool) ($settings['articleCategoriesEnabled'] ?? true),
            'articlesPerRow' => (int) ($settings['articlesPerRow'] ?? 3),
            'blogSidebar' => (string) ($settings['blogSidebar'] ?? 'none'),
            'sidebarCategories' => $categories,
            'latestArticles' => $latestForSidebar,
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
            return \App\Core\ErrorPages::page(404, 'Статья не найдена', 'Такой статьи нет или она была удалена.', '/blog.html');
        }

        $article['date'] = $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '';
        $article['body'] = $this->prepareArticleBody($article['body_html'] ?? '');
        $article['readingTime'] = max(1, (int) ceil(str_word_count(strip_tags($article['body_html'] ?? '')) / 200));

        // Универсальный шаблон: полное описание статьи всегда берётся из базы (body_html),
        // статические шаблоны постов её не подменяют
        $template = 'dynamic-article';

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

        // Сайдбар «Свежие записи»: до 5 статей, текущую пропускаем
        $latestArticles = [];
        foreach ($allArticles as $latestSlug => $latestArticle) {
            if ($latestSlug === $slug) {
                continue;
            }
            $latestArticles[] = $latestArticle;
            if (count($latestArticles) >= 5) {
                break;
            }
        }

        // Рубрики для сайдбара страницы статьи (динамически из базы)
        $sidebarCategories = $content->categories();

        // Видимость комментариев настраивается в админке (Настройки → Блог → commentsAccess)
        $commentsSettings = (new \App\Models\SiteSettings());
        $commentsAccess = (string) ($commentsSettings->get('commentsAccess', 'users') ?: 'users');
        $commentsVisible = match ($commentsAccess) {
            'guests' => true,
            'staff' => $user && in_array($user['role'], ['admin', 'editor', 'manager'], true),
            default => $user !== null,
        };
        // Глобальный тумблер «Разрешить комментарии»: выключен — секция не выводится
        $commentsEnabled = (bool) $commentsSettings->get('articleCommentsEnabled', true);
        $commentsCount = $content->commentsCount($slug);
        $comments = $commentsVisible ? $content->commentsByArticle($slug) : [];

        // Тумблеры отображения (Настройки → Статьи)
        $showArticleTags = (bool) $commentsSettings->get('articleTagsEnabled', true);
        $showArticleCategories = (bool) $commentsSettings->get('articleCategoriesEnabled', true);
        $showArticleReadTime = (bool) $commentsSettings->get('articleReadTime', true);
        $showRelatedArticles = (bool) $commentsSettings->get('articleRelatedEnabled', true);
        $showShareButtons = (bool) $commentsSettings->get('articleShareButtons', true);
        $articlePageSidebar = (string) ($commentsSettings->get('articlePageSidebar', 'right') ?: 'right');

        // Похожие статьи: те же рубрики, до 3, текущую пропускаем
        $relatedArticles = [];
        if ($showRelatedArticles && !empty($article['category_slug'])) {
            foreach ($content->articlesByCategory((string) $article['category_slug']) as $related) {
                if ($related['slug'] === $slug) {
                    continue;
                }
                $relatedArticles[] = ['slug' => $related['slug'], 'title' => $related['title']];
                if (count($relatedArticles) >= 3) {
                    break;
                }
            }
        }

        // Абсолютная ссылка на страницу статьи для кнопок «Поделиться»
        $shareScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $shareUrl = $shareScheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'myblog.loc') . '/' . rawurlencode($slug) . '.html';

        return $this->page('frontend', $template, [
            'title' => $article['title'],
            'description' => $article['description'],
            'activePage' => 'blog',
            'article' => $article,
            'prevArticle' => $prev,
            'nextArticle' => $next,
            'latestArticles' => $latestArticles,
            'relatedArticles' => $relatedArticles,
            'shareUrl' => $shareUrl,
            'showArticleTags' => $showArticleTags,
            'showArticleCategories' => $showArticleCategories,
            'showArticleReadTime' => $showArticleReadTime,
            'showRelatedArticles' => $showRelatedArticles,
            'showShareButtons' => $showShareButtons,
            'comments' => $comments,
            'commentsCount' => $commentsCount,
            'commentsVisible' => $commentsVisible,
            'commentsEnabled' => $commentsEnabled,
            'sidebarCategories' => $sidebarCategories,
            'articlePageSidebar' => $articlePageSidebar,
            // Форма комментария отправляется через AJAX forms.js с тостом,
            // иначе браузер уходит на JSON-ответ обработчика
            'includeFormsScript' => true,
            // Подсветка синтаксиса вставленного в редактор кода (Vite entry article)
            'includeArticleScript' => true,
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
