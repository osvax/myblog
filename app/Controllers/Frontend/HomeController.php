<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Controllers\Controller;
use App\Models\ContentRepository;
use App\Models\LandingPage;
use App\Models\SiteSettings;

final class HomeController extends Controller
{
    private $content;
    private $settings;
    private $landingPage;

    public function __construct(
        ContentRepository $content,
        SiteSettings $settings,
        LandingPage $landingPage
    ) {
        $this->content = $content;
        $this->settings = $settings;
        $this->landingPage = $landingPage;
    }

    public function index($request)
    {
        $home = $this->settings->all();

        // Статическая страница по умолчанию: настройка «Что показывать на главной».
        // main — шаблон главной; static — лендинг из БД, а если он не активен — 404.
        $homePageMode = ($home['homePageMode'] ?? 'main') === 'static' ? 'static' : 'main';
        if ($homePageMode === 'static') {
            $page = $this->landingPage->findActive('home');
            if ($page && !empty($page['html_content'])) {
                return render_static_page($page['html_content']);
            }
            return \App\Core\ErrorPages::page(404, 'Главная страница не найдена', 'Статическая главная страница не активна.', '/');
        }

        $articles = [];
        foreach ($this->content->articles(true) as $article) {
            $article['date'] = $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '';
            $articles[$article['slug']] = $article;
        }
        $projects = [];
        foreach ($this->content->projects(true) as $project) {
            $projects[$project['slug']] = $project;
        }
        $user = current_user();

        // Количество свежих статей на главной настраивается в админке (homeFeaturedCount)
        $featuredCount = max(1, min(12, (int) ($home['homeFeaturedCount'] ?? 3)));
        $featuredArticles = [];
        foreach (array_slice(array_values($articles), 0, $featuredCount) as $article) {
            $featuredArticles[$article['slug']] = $article;
        }
        $featuredProjects = [];
        foreach (['project-shop', 'project-blog', 'project-landing', 'project-community'] as $slug) {
            if (isset($projects[$slug])) {
                $featuredProjects[$slug] = $projects[$slug];
            }
        }

        return $this->page('frontend', 'home', [
            'title' => 'Главная',
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
