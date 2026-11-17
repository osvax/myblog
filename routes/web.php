<?php

use App\Core\Router;
use App\Controllers\Frontend\HomeController;
use App\Controllers\Frontend\BlogController;
use App\Controllers\Frontend\PortfolioController;
use App\Controllers\Frontend\ContactsController;
use App\Controllers\Frontend\OrderController;
use App\Controllers\Frontend\AuthController;
use App\Controllers\Frontend\ProfileController;
use App\Controllers\Admin\PageController;

return function (Router $router) {
    $frontend = new HomeController();
    $blog = new BlogController();
    $portfolio = new PortfolioController();
    $contacts = new ContactsController();
    $order = new OrderController();
    $auth = new AuthController();
    $profile = new ProfileController();
    $admin = new PageController();

    $router->get('/health', function () {
        return \App\Core\Response::json(['success' => true, 'app' => 'myblog']);
    });
    $router->get('/', [$frontend, 'index']);
    $router->get('/index.html', [$frontend, 'index']);
    $router->get('/mvc', [$frontend, 'index']);
    $router->get('/blog.html', [$blog, 'index']);
    $router->get('/portfolio.html', [$portfolio, 'index']);
    $router->get('/contacts.html', [$contacts, 'index']);
    $router->get('/order.html', [$order, 'index']);
    $router->get('/messages.html', [$portfolio, 'index']);
    $router->get('/orders.html', [$portfolio, 'index']);
    $router->get('/settings.html', [$portfolio, 'index']);
    $content = require dirname(__DIR__) . '/config/public-content.php';
    foreach (array_keys($content['articles']) as $slug) {
        $router->get('/' . $slug . '.html', function ($request) use ($blog, $slug) {
            return $blog->article($request, $slug);
        });
    }
    foreach (array_keys($content['projects']) as $slug) {
        $router->get('/' . $slug . '.html', function ($request) use ($portfolio, $slug) {
            return $portfolio->project($request, $slug);
        });
    }
    $router->get('/{slug}.html', function ($request, $parameters) use ($blog, $portfolio) {
        $slug = $parameters['slug'] ?? '';
        $content = new \App\Models\ContentRepository();
        if ($content->articleBySlug($slug) !== null) {
            return $blog->article($request, $slug);
        }
        // Простые страницы и лендинги из раздела «Страницы» админки
        $staticPage = (new \App\Models\LandingPage())->findActive($slug);
        if ($staticPage !== null && trim((string) ($staticPage['html_content'] ?? '')) !== '') {
            return $staticPage['html_content'];
        }
        return $portfolio->project($request, $slug);
    });
    $router->get('/mvc/admin', function ($request) use ($admin) {
        return $admin->show($request, 'index');
    });
    $router->get('/admin', function ($request) use ($admin) {
        return $admin->show($request, 'index');
    });
    foreach (array_keys(require dirname(__DIR__) . '/config/admin-pages.php') as $slug) {
        $router->get('/admin/' . $slug . '.html', function ($request) use ($admin, $slug) {
            return $admin->show($request, $slug);
        });
    }

    // ---------- Маршруты авторизации ----------
    $router->get('/login.html', [$auth, 'login']);
    $router->get('/register.html', [$auth, 'register']);
    $router->get('/forgot-password.html', [$auth, 'forgotPassword']);
    $router->get('/reset-password/{token}', [$auth, 'resetPassword']);

    // ---------- API авторизации ----------
    $router->post('/api/auth/user/login', [$auth, 'handleLogin']);
    $router->post('/api/auth/register', [$auth, 'handleRegister']);
    $router->post('/api/auth/forgot-password', [$auth, 'handleForgotPassword']);
    $router->post('/api/auth/reset-password', [$auth, 'handleResetPassword']);
    $router->post('/api/auth/user/logout', [$auth, 'handleLogout']);
    $router->get('/api/auth/user/check', [$auth, 'checkAuth']);

    // ---------- Маршруты профиля ----------
    $router->get('/profile.html', [$profile, 'show']);
    $router->post('/api/profile/update', [$profile, 'updateProfile']);
    $router->post('/api/profile/change-password', [$profile, 'changePassword']);
    $router->post('/api/profile/delete', [$profile, 'deleteAccount']);
};
