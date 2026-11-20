<?php

use App\Core\Router;
use App\Controllers\Frontend\HomeController;
use App\Controllers\Frontend\BlogController;
use App\Controllers\Frontend\PortfolioController;
use App\Controllers\Frontend\ContactsController;
use App\Controllers\Frontend\OrderController;
use App\Controllers\Frontend\AuthController;
use App\Controllers\Frontend\ProfileController;
use App\Controllers\Frontend\SeoController;
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
    // Страница рубрики блога (важно: до catch-all /{slug}.html)
    $router->get('/category/{slug}.html', [$blog, 'category']);

    $seo = new SeoController();
    $router->get('/robots.txt', [$seo, 'robots']);
    $router->get('/sitemap.xml', [$seo, 'sitemap']);

    // Заглушка режима обслуживания (в общем дизайне сайта).
    // Статус определяется только полем «Режим сайта» (siteStatus).
    // Если сайт уже открыт — редирект на страницу, которую открывал
    // пользователь (параметр ?next), или на главную.
    $router->get('/maintenance.html', function ($request) {
        $settings = new \App\Models\SiteSettings();
        // Пустое значение (могло остаться из формы) трактуем как online
        $status = (string) $settings->get('siteStatus', 'online');
        $status = ($status === 'maintenance') ? 'maintenance' : 'online';
        $maintEnd = trim((string) $settings->get('maintenanceEnd', ''));
        $endTs = $maintEnd !== '' ? (int) strtotime(str_replace('T', ' ', $maintEnd)) : 0;

        if ($status !== 'maintenance') {
            // Сайт открыт — заглушка не нужна. Возвращаем на исходную страницу.
            $next = isset($_GET['next']) && is_string($_GET['next']) ? rawurldecode($_GET['next']) : '';
            // Только относительные пути этого сайта, без служебных разделов
            if ($next !== ''
                && $next[0] === '/'
                && strpos($next, '//') === false
                && strpos($next, '\\') === false
                && !preg_match('#^/(admin|api|maintenance\.html)#', $next)) {
                return \App\Core\Response::redirect($next, 302);
            }
            return \App\Core\Response::redirect('/', 302);
        }

        $user = current_user();
        $message = trim((string) $settings->get('maintenanceMessage', ''));
        return \App\Core\View::renderPage('frontend', 'maintenance', [
            'title' => 'Сайт на обслуживании',
            'description' => 'Ведутся технические работы.',
            'activePage' => '',
            'maintenanceMessage' => $message !== '' ? $message : 'Сайт временно закрыт на техническое обслуживание. Скоро вернёмся!',
            // Метка времени конца обслуживания (Unix) для обратного отсчёта на заглушке
            'maintenanceEndTs' => $endTs > 0 ? $endTs : null,
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
        ]);
    });

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
    $router->post('/api/auth/user/verify-2fa', [$auth, 'handleVerify2fa']);
    $router->post('/api/auth/user/2fa/setup', [$auth, 'setup2fa']);
    $router->post('/api/auth/user/2fa/enable', [$auth, 'enable2fa']);
    $router->post('/api/auth/user/2fa/disable', [$auth, 'disable2fa']);
    $router->post('/api/auth/register', [$auth, 'handleRegister']);
    $router->post('/api/auth/forgot-password', [$auth, 'handleForgotPassword']);
    $router->post('/api/auth/reset-password', [$auth, 'handleResetPassword']);
    $router->post('/api/auth/user/logout', [$auth, 'handleLogout']);
    $router->get('/api/auth/user/check', [$auth, 'checkAuth']);

    // Выход из обычной HTML-формы (кнопка «Выйти» в тулбаре): редирект на главную
    $router->post('/logout.html', [$auth, 'logout']);

    // ---------- Маршруты профиля ----------
    $router->get('/profile.html', [$profile, 'show']);
    $router->post('/api/profile/update', [$profile, 'updateProfile']);
    $router->post('/api/profile/change-password', [$profile, 'changePassword']);
    $router->post('/api/profile/delete', [$profile, 'deleteAccount']);
};
