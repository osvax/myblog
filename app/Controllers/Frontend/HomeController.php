<?php

namespace App\Controllers\Frontend;

use App\Controllers\Controller;
use App\Models\ContentRepository;
use App\Models\LandingPage;
use App\Core\Sanitizer;

class HomeController extends Controller
{
    public function index($request)
    {
        $settings = (new \App\Models\SiteSettings())->all();
        $home = $settings;

        // Статическая страница по умолчанию: настройка «Что показывать на главной».
        // main — шаблон главной; static — лендинг из БД, а если он не активен — 404.
        $homePageMode = ($settings['homePageMode'] ?? 'main') === 'static' ? 'static' : 'main';
        if ($homePageMode === 'static') {
            $page = (new LandingPage())->findActive('home');
            if ($page && !empty($page['html_content'])) {
                // Defense in depth: санитизируем и при выводе
                return Sanitizer::clean($page['html_content']);
            }
            return new \App\Core\Response('Главная страница не найдена', 404, ['Content-Type' => 'text/plain; charset=utf-8']);
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
