<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\ApiResource;
use App\Core\ApplicationLogger;
use App\Models\AdminUser;

class AuthController extends Controller
{
    private $adminUser;
    private $logger;

    public function __construct(AdminUser $adminUser, ApplicationLogger $logger)
    {
        $this->adminUser = $adminUser;
        $this->logger = $logger;
    }

    public function login($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        $input = $request->json();
        $email = isset($input['email']) && is_string($input['email']) ? trim($input['email']) : '';
        $password = isset($input['password']) && is_string($input['password']) ? $input['password'] : '';
        $result = $this->adminUser->verifyCredentials($email, $password);
        if (!$result['configured']) {
            return $this->json(ApiResource::error('Проверьте email входа в админ-панель в настройках сайта'), 503);
        }
        if (!$result['valid']) {
            return $this->json(ApiResource::error($result['error'] ?? 'Неверный email или пароль'), 401);
        }

        \admin_start_session();
        if (!session_regenerate_id(true)) {
            return $this->json(ApiResource::error('Не удалось создать защищённую сессию'), 500);
        }
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_email'] = $result['email'];
        $_SESSION['admin_user_id'] = $result['user_id'] ?? 0;
        $_SESSION['admin_name'] = $result['name'] ?? '';
        $_SESSION['admin_role'] = $result['role'] ?? 'admin';
        $_SESSION['admin_authenticated_at'] = time();
        try {
            $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
        } catch (\Throwable $error) {
            $_SESSION = [];
            session_destroy();
            $this->logger->logException($error, 'Could not generate an admin CSRF token');
            return $this->json(ApiResource::error('Не удалось создать защищённую сессию'), 500);
        }

        return $this->json(ApiResource::success());
    }

    public function csrf($request)
    {
        if ($request->method() !== 'GET') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if (empty($_SESSION['admin_csrf_token'])) {
            return $this->json(ApiResource::error('Не удалось получить токен защищённого запроса'), 500);
        }
        return $this->json(ApiResource::success(['token' => $_SESSION['admin_csrf_token']]));
    }

    public function check($request)
    {
        return \admin_is_authenticated()
            ? new \App\Core\Response('', 204)
            : new \App\Core\Response('', 401);
    }

    public function logout($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }
        if (!\admin_is_authenticated()) {
            return \App\Core\Response::redirect('/admin/login.html', 303);
        }
        \admin_require_csrf();
        \admin_start_session();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Strict',
            ]);
        }

        session_destroy();
        return \App\Core\Response::redirect('/admin/login.html', 303);
    }
}
