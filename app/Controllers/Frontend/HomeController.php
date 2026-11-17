<?php

namespace App\Controllers\Frontend;

use App\Controllers\Controller;
use App\Models\ContentRepository;
use App\Models\LandingPage;

class HomeController extends Controller
{
    public function index($request)
    {
        // Проверяем, есть ли сгенерированная страница
        $page = (new LandingPage())->findActive('home');
        if ($page && !empty($page['html_content'])) {
            return $page['html_content'];
        }

        $content = new ContentRepository();
        $articles = [];
        foreach ($content->articles(true) as $article) {
            $article['date'] = $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '';
            $articles[$article['slug']] = $article;
        }
        $projects = [];
        foreach ($content->projects(true) as $project) {
            $projects[$project['slug']] = $project;
        }
        $user = current_user();

        // Тексты главной страницы редактируются в админке (Настройки → Главная)
        $home = (new \App\Models\SiteSettings())->all();

        // Количество свежих статей на главной настраивается в админке (homeFeaturedCount)
        $featuredCount = max(1, min(12, (int) ($home['homeFeaturedCount'] ?? 3)));
        $featuredArticles = [];
        foreach (array_slice(array_values($articles), 0, $featuredCount) as $article) {
            $featuredArticles[$article['slug']] = $article;
        }
        $featuredProjects = [];
        foreach (['project-shop', 'project-blog', 'project-landing', 'project-dashboard'] as $slug) {
            if (isset($projects[$slug])) {
                $featuredProjects[$slug] = $projects[$slug];
            }
        }

        return $this->page('frontend', 'home', [
            'title' => 'Главная — МойБлог',
            'description' => 'Личный блог о веб-разработке и дизайне, статьи, проекты и услуги.',
            'activePage' => 'home',
            'featuredArticles' => $featuredArticles,
            'featuredProjects' => $featuredProjects,
            'home' => $home,
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
        ]);
    }
}
