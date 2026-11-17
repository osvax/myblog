<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\AdminUser;

class AuthController extends Controller
{
    public function login($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $input = $request->json();
        error_log('AuthController::login input: ' . json_encode($input));
        $email = isset($input['email']) && is_string($input['email']) ? trim($input['email']) : '';
        $password = isset($input['password']) && is_string($input['password']) ? $input['password'] : '';
        error_log('AuthController::login email=' . $email . ' password_len=' . strlen($password));
        $result = (new AdminUser())->verifyCredentials($email, $password);
        error_log('AuthController::login result: ' . json_encode($result));
        if (!$result['configured']) {
            return $this->json(['success' => false, 'error' => 'Проверьте настройки авторизации в config/admin-auth.php'], 503);
        }
        if (!$result['valid']) {
            return $this->json(['success' => false, 'error' => $result['error'] ?? 'Неверный email или пароль'], 401);
        }

        \admin_start_session();
        if (!session_regenerate_id(true)) {
            return $this->json(['success' => false, 'error' => 'Не удалось создать защищённую сессию'], 500);
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
            error_log('Could not generate an admin CSRF token.');
            return $this->json(['success' => false, 'error' => 'Не удалось создать защищённую сессию'], 500);
        }

        return $this->json(['success' => true]);
    }

    public function csrf($request)
    {
        if ($request->method() !== 'GET') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if (empty($_SESSION['admin_csrf_token'])) {
            return $this->json(['success' => false, 'error' => 'Не удалось получить токен защищённого запроса'], 500);
        }
        return $this->json(['success' => true, 'token' => $_SESSION['admin_csrf_token']]);
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
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
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
