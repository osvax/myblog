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

        return $this->page('frontend', $slug, [
            'title' => $article['title'] . ' — МойБлог',
            'description' => $article['description'],
            'activePage' => 'blog',
            'article' => $article,
            'prevArticle' => $prev,
            'nextArticle' => $next,
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
