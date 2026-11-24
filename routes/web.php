<?php

declare(strict_types=1);

use App\Core\Response;
use App\Core\Router;

return function (Router $router) {
    // Health check
    $router->get('/health', function () {
        return Response::json(['success' => true, 'app' => 'myblog']);
    });

    // Frontend controllers from container
    $frontend = container(\App\Controllers\Frontend\HomeController::class);
    $blog = container(\App\Controllers\Frontend\BlogController::class);
    $portfolio = container(\App\Controllers\Frontend\PortfolioController::class);
    $contacts = container(\App\Controllers\Frontend\ContactsController::class);
    $order = container(\App\Controllers\Frontend\OrderController::class);
    $auth = container(\App\Controllers\Frontend\AuthController::class);
    $profile = container(\App\Controllers\Frontend\ProfileController::class);
    $seo = container(\App\Controllers\Frontend\SeoController::class);

    // Admin page controller
    $admin = container(\App\Controllers\Admin\PageController::class);

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
    // Страница рубрики блога (важно: до catch-all /{slug}.html)
    $router->get('/category/{slug}.html', [$blog, 'category']);

    $router->get('/robots.txt', [$seo, 'robots']);
    $router->get('/sitemap.xml', [$seo, 'sitemap']);

    // Заглушка режима обслуживания
    $router->get('/maintenance.html', function ($request) {
        $settings = container(\App\Models\SiteSettings::class);
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
                return Response::redirect($next, 302);
            }
            return Response::redirect('/', 302);
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

    // Catch-all для статей, проектов и статических страниц
    $router->get('/{slug}.html', function ($request, $parameters) use ($blog, $portfolio) {
        $slug = $parameters['slug'] ?? '';
        $content = container(\App\Models\ContentRepository::class);
        if ($content->articleBySlug($slug) !== null) {
            return $blog->article($request, $slug);
        }
        // Простые страницы и лендинги из раздела «Страницы» админки:
        // тело — произвольный HTML с плейсхолдерами, раскрываются при рендере
        $staticPage = container(\App\Models\LandingPage::class)->findActive($slug);
        if ($staticPage !== null && trim((string) ($staticPage['html_content'] ?? '')) !== '') {
            return render_static_page($staticPage['html_content']);
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
    $router->get('/api/auth/yandex', [$auth, 'yandexRedirect']);
    $router->get('/api/auth/yandex/callback', [$auth, 'yandexCallback']);
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
    $router->post('/api/profile/avatar', [$profile, 'uploadAvatar']);
    $router->get('/uploads/profile/{filename}', [$profile, 'serveAvatar']);
    $router->head('/uploads/profile/{filename}', [$profile, 'serveAvatar']);
    $router->post('/api/profile/change-password', [$profile, 'changePassword']);
    $router->post('/api/profile/delete', [$profile, 'deleteAccount']);
};
