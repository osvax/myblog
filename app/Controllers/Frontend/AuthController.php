<?php

namespace App\Controllers\Frontend;

use App\Controllers\Controller;
use App\Models\UserRepository;
use App\Models\SiteSettings;
use App\Core\Mailer;
use App\Core\RateLimiter;

class AuthController extends Controller
{
    private $users;
    private $settings;
    private $mailer;
    private $rateLimiter;

    public function __construct(
        UserRepository $users,
        SiteSettings $settings,
        Mailer $mailer,
        RateLimiter $rateLimiter
    ) {
        $this->users = $users;
        $this->settings = $settings;
        $this->mailer = $mailer;
        $this->rateLimiter = $rateLimiter;
    }
    /**
     * Показать форму входа.
     */
    public function login($request)
    {
        $user = current_user();

        return $this->page('frontend', 'login', [
            'title' => 'Вход',
            'pageTitle' => 'Вход в аккаунт',
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
            'includeAuthScript' => true,
            'authPage' => true,
        ]);
    }

    public function yandexRedirect($request)
    {
        \user_start_session();
        if (!(bool) $this->settings->get('yandexAuthEnabled', false)) return \App\Core\Response::redirect('/login.html', 302);
        $clientId = trim((string) $this->settings->get('yandexClientId', ''));
        $redirect = trim((string) $this->settings->get('yandexRedirectUri', ''));
        if ($clientId === '' || $redirect === '') return new \App\Core\Response('Яндекс OAuth не настроен', 503);
        $_SESSION['yandex_oauth_state'] = bin2hex(random_bytes(16));
        $url = 'https://oauth.yandex.ru/authorize?' . http_build_query(['response_type'=>'code','client_id'=>$clientId,'redirect_uri'=>$redirect,'state'=>$_SESSION['yandex_oauth_state']]);
        return \App\Core\Response::redirect($url, 302);
    }

    public function yandexCallback($request)
    {
        \user_start_session();

        if (!empty($_GET['error'])) {
            return new \App\Core\Response('Авторизация через Яндекс отменена', 401);
        }

        $state = (string) ($_GET['state'] ?? '');
        if ($state === '' || !hash_equals((string) ($_SESSION['yandex_oauth_state'] ?? ''), $state)) {
            return new \App\Core\Response('Некорректный OAuth state', 400);
        }
        unset($_SESSION['yandex_oauth_state']);

        $code = trim((string) ($_GET['code'] ?? ''));
        if ($code === '') {
            return new \App\Core\Response('Яндекс не вернул код авторизации', 401);
        }

        $clientId = trim((string) $this->settings->get('yandexClientId', ''));
        $redirectUri = trim((string) $this->settings->get('yandexRedirectUri', ''));
        $token = $this->oauthPost('https://oauth.yandex.ru/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $this->settings->get('yandexClientSecret', ''),
            'redirect_uri' => $redirectUri,
        ]);
        if (empty($token['access_token'])) {
            \App\Core\Logger::write('auth', 'Яндекс OAuth: не удалось получить access_token: ' . ($token['error_description'] ?? $token['error'] ?? 'неизвестная ошибка'));
            return new \App\Core\Response('Не удалось получить токен Яндекс', 401);
        }

        $profile = $this->oauthGet('https://login.yandex.ru/info?format=json', $token['access_token']);
        $yandexId = trim((string) ($profile['id'] ?? ''));
        $email = strtolower(trim((string) ($profile['default_email'] ?? '')));
        if ($yandexId === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            \App\Core\Logger::write('auth', 'Яндекс OAuth: профиль не содержит корректные id/email');
            return new \App\Core\Response('Яндекс не вернул email', 401);
        }

        $user = $this->users->findByYandexId($yandexId) ?: $this->users->findByEmail($email);
        if (!$user) {
            $name = trim((string) ($profile['real_name'] ?? $profile['display_name'] ?? $email));
            $created = $this->users->create($email, password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT), $name);
            if (empty($created['success']) || empty($created['user_id'])) {
                return new \App\Core\Response('Не удалось создать пользователя: ' . ($created['error'] ?? 'ошибка базы данных'), 500);
            }
            $this->users->setRole((int) $created['user_id'], 'user');
            $user = $this->users->findById((int) $created['user_id']);
        }

        if (!$user || empty($user['id'])) {
            return new \App\Core\Response('Пользователь не найден после авторизации', 500);
        }

        $this->users->linkYandex((int) $user['id'], $yandexId);
        $user = $this->users->findById((int) $user['id']);
        if (!$user) {
            return new \App\Core\Response('Не удалось загрузить профиль пользователя', 500);
        }

        $this->completeLogin($user);
        return \App\Core\Response::redirect('/', 302);
    }

    private function oauthPost(string $url, array $data): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 15,
        ]);
        $raw = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw === false || $error !== '') {
            \App\Core\Logger::write('auth', 'Яндекс OAuth HTTP ошибка: ' . $error);
            return [];
        }
        $response = json_decode($raw, true);
        if ($status < 200 || $status >= 300 || !is_array($response)) {
            \App\Core\Logger::write('auth', 'Яндекс OAuth HTTP статус ' . $status);
            return is_array($response) ? $response : [];
        }
        return $response;
    }

    private function oauthGet(string $url, string $token): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: OAuth ' . $token],
            CURLOPT_TIMEOUT => 15,
        ]);
        $raw = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw === false || $error !== '') {
            \App\Core\Logger::write('auth', 'Яндекс profile HTTP ошибка: ' . $error);
            return [];
        }
        $response = json_decode($raw, true);
        if ($status < 200 || $status >= 300 || !is_array($response)) {
            \App\Core\Logger::write('auth', 'Яндекс profile HTTP статус ' . $status);
            return [];
        }
        return $response;
    }

    /**
     * Проверить rate limit для auth эндпоинтов.
     *
     * @param string $endpoint
     * @param int $maxAttempts
     * @param int $decayMinutes
     * @return array|null Возвращает null если OK, или массив с ошибкой для json ответа
     */
    private function checkRateLimit(string $endpoint, int $maxAttempts, int $decayMinutes): ?array
    {
        $key = \App\Core\RateLimiter::makeKey($endpoint);
        $result = \App\Core\RateLimiter::hit($key, $maxAttempts, $decayMinutes);

        if (!$result['allowed']) {
            return [
                'success' => false,
                'error' => 'Слишком много попыток. Попробуйте через ' . $result['retryAfter'] . ' секунд.',
                'retryAfter' => $result['retryAfter'],
            ];
        }

        // Добавляем заголовки rate limit
        header('X-RateLimit-Limit: ' . $maxAttempts);
        header('X-RateLimit-Remaining: ' . $result['remaining']);
        if ($result['retryAfter']) {
            header('Retry-After: ' . $result['retryAfter']);
        }

        return null;
    }

    /**
     * Показать форму регистрации.
     */
    public function register($request)
    {
        $user = current_user();

        return $this->page('frontend', 'register', [
            'title' => 'Регистрация',
            'pageTitle' => 'Создать аккаунт',
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
            'includeAuthScript' => true,
            'authPage' => true,
        ]);
    }

    /**
     * Показать форму восстановления пароля.
     */
    public function forgotPassword($request)
    {
        $user = current_user();

        return $this->page('frontend', 'forgot-password', [
            'title' => 'Восстановление пароля',
            'pageTitle' => 'Восстановить пароль',
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
            'includeAuthScript' => true,
            'authPage' => true,
        ]);
    }

    /**
     * Показать форму сброса пароля по токену.
     */
    public function resetPassword($request, $parameters)
    {
        $token = $parameters['token'] ?? '';
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return new \App\Core\Response('Неверный токен сброса пароля', 400);
        }

        $user = current_user();

        return $this->page('frontend', 'reset-password', [
            'title' => 'Сброс пароля',
            'pageTitle' => 'Новый пароль',
            'token' => $token,
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
            'includeAuthScript' => true,
            'authPage' => true,
        ]);
    }

    /**
     * Обработать вход.
     */
    public function handleLogin($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        // Rate limiting: лимит попыток и время блокировки — из настроек (Настройки → Безопасность)
        $maxLoginAttempts = max(1, (int) $this->settings->get('maxLoginAttempts', (int) ($_ENV['RATE_LIMIT_LOGIN'] ?? 5) ?: 5));
        $lockoutMinutes = max(1, (int) $this->settings->get('lockoutMinutes', 15));
        if ($error = $this->checkRateLimit('auth:login', $maxLoginAttempts, $lockoutMinutes)) {
            return $this->json($error, 429);
        }

        $input = $request->json();
        $email = isset($input['email']) && is_string($input['email']) ? trim($input['email']) : '';
        $password = isset($input['password']) && is_string($input['password']) ? $input['password'] : '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'error' => 'Введите корректный email'], 400);
        }
        if (strlen($password) < 8) {
            return $this->json(['success' => false, 'error' => 'Пароль должен содержать минимум 8 символов'], 400);
        }

        $result = $this->users->verifyCredentials($email, $password);
        if (!$result['valid']) {
            \App\Core\Logger::write('auth', 'Неудачная попытка входа: ' . $email);
            return $this->json(['success' => false, 'error' => $result['error']], 401);
        }

        \user_start_session();

        // Двухфакторная аутентификация: на первом шаге сессию не открываем,
        // ждём код из приложения-аутентификатора (5 минут на ввод)
        if (!empty($result['user']['totp_enabled'])) {
            $_SESSION['user_2fa_pending'] = [
                'id' => $result['user']['id'],
                'expires' => time() + 300,
            ];
            return $this->json(['success' => true, 'require_2fa' => true]);
        }

        return $this->completeLogin($result['user']);
    }

    /**
     * Подтвердить вход кодом TOTP (второй шаг двухфакторной аутентификации).
     */
    public function handleVerify2fa($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \user_start_session();

        $pending = $_SESSION['user_2fa_pending'] ?? null;
        if (!is_array($pending) || (int) ($pending['expires'] ?? 0) < time()) {
            unset($_SESSION['user_2fa_pending']);
            return $this->json(['success' => false, 'error' => 'Время на ввод кода истекло — войдите заново'], 401);
        }

        $input = $request->json();
        $code = isset($input['code']) && is_string($input['code']) ? trim($input['code']) : '';
        if ($code === '') {
            return $this->json(['success' => false, 'error' => 'Введите код из приложения'], 400);
        }

        $user = $this->users->findById((int) $pending['id']);
        if (!$user || empty($user['totp_enabled']) || empty($user['totp_secret'])) {
            unset($_SESSION['user_2fa_pending']);
            return $this->json(['success' => false, 'error' => 'Двухфакторная аутентификация недоступна — войдите заново'], 401);
        }

        if (!\App\Core\Totp::verify($user['totp_secret'], $code)) {
            return $this->json(['success' => false, 'error' => 'Неверный код'], 401);
        }

        unset($_SESSION['user_2fa_pending']);

        return $this->completeLogin([
            'id' => (int) $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'role' => $user['role'],
        ]);
    }

    /**
     * Завершить вход: создать защищённую сессию пользователя.
     */
    private function completeLogin(array $user)
    {
        if (!session_regenerate_id(true)) {
            return $this->json(['success' => false, 'error' => 'Не удалось создать сессию'], 500);
        }

        $_SESSION['user_authenticated'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_authenticated_at'] = time();

        \App\Core\Logger::write('auth', 'Вход: ' . $user['name'] . ' (' . $user['email'] . ')', (int) $user['id']);

        return $this->json(['success' => true]);
    }

    /**
     * Сгенерировать секрет TOTP для включения двухфакторной аутентификации.
     */
    public function setup2fa($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $user = current_user();
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        $secret = \App\Core\Totp::generateSecret();
        $this->users->setTotpSecret((int) $user['id'], $secret);

        return $this->json([
            'success' => true,
            'secret' => $secret,
            'uri' => \App\Core\Totp::uri($secret, $user['email']),
        ]);
    }

    /**
     * Включить двухфакторную аутентификацию после подтверждения кода.
     */
    public function enable2fa($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $user = current_user();
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        $input = $request->json();
        $code = isset($input['code']) && is_string($input['code']) ? trim($input['code']) : '';

        $full = $this->users->findById((int) $user['id']);
        if (!$full || empty($full['totp_secret'])) {
            return $this->json(['success' => false, 'error' => 'Сначала запустите настройку'], 400);
        }
        if (!\App\Core\Totp::verify($full['totp_secret'], $code)) {
            return $this->json(['success' => false, 'error' => 'Неверный код'], 401);
        }

        $this->users->setTotpEnabled((int) $user['id'], true);
        return $this->json(['success' => true]);
    }

    /**
     * Выключить двухфакторную аутентификацию (требуется код).
     */
    public function disable2fa($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $user = current_user();
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        $input = $request->json();
        $code = isset($input['code']) && is_string($input['code']) ? trim($input['code']) : '';

        $full = $this->users->findById((int) $user['id']);
        if (!$full || empty($full['totp_secret']) || empty($full['totp_enabled'])) {
            return $this->json(['success' => false, 'error' => 'Двухфакторная аутентификация не включена'], 400);
        }
        if (!\App\Core\Totp::verify($full['totp_secret'], $code)) {
            return $this->json(['success' => false, 'error' => 'Неверный код'], 401);
        }

        $this->users->setTotpEnabled((int) $user['id'], false);
        $this->users->setTotpSecret((int) $user['id'], '');
        return $this->json(['success' => true]);
    }

    /**
     * Обработать регистрацию.
     */
    public function handleRegister($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        // Rate limiting
        $maxRegisterAttempts = (int) ($_ENV['RATE_LIMIT_REGISTER'] ?? 3);
        if ($error = $this->checkRateLimit('auth:register', $maxRegisterAttempts, 60)) {
            return $this->json($error, 429);
        }

        $input = $request->json();
        $name = isset($input['name']) && is_string($input['name']) ? trim($input['name']) : '';
        $email = isset($input['email']) && is_string($input['email']) ? trim($input['email']) : '';
        $password = isset($input['password']) && is_string($input['password']) ? $input['password'] : '';
        $passwordConfirm = isset($input['password_confirm']) && is_string($input['password_confirm']) ? $input['password_confirm'] : '';

        if (strlen($name) < 2 || strlen($name) > 160) {
            return $this->json(['success' => false, 'error' => 'Имя должно содержать от 2 до 160 символов'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'error' => 'Введите корректный email'], 400);
        }
        if (strlen($password) < 8) {
            return $this->json(['success' => false, 'error' => 'Пароль должен содержать минимум 8 символов'], 400);
        }
        if ($password !== $passwordConfirm) {
            return $this->json(['success' => false, 'error' => 'Пароли не совпадают'], 400);
        }

        // Хешируем пароль
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $result = $this->users->create($email, $passwordHash, $name);
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => $result['error']], 400);
        }

        // Флаг «Уведомлять о новых пользователях» (Настройки → Уведомления)
        if ((bool) $this->settings->get('notifyUsers', false)) {
            $adminEmail = trim((string) $this->settings->get('adminEmail', ''));
            if (filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                $this->mailer->send(
                    'Новый пользователь на сайте',
                    "Имя: {$name}\nEmail: {$email}",
                    $adminEmail,
                    'Admin'
                );
            }
        }

        \App\Core\Logger::write('auth', 'Регистрация: ' . $name . ' (' . $email . ')');

        return $this->json(['success' => true]);
    }

    /**
     * Обработать запрос на восстановление пароля.
     */
    public function handleForgotPassword($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        // Rate limiting
        $maxForgotAttempts = (int) ($_ENV['RATE_LIMIT_FORGOT_PASSWORD'] ?? 2);
        if ($error = $this->checkRateLimit('auth:forgot-password', $maxForgotAttempts, 60)) {
            return $this->json($error, 429);
        }

        $input = $request->json();
        $email = isset($input['email']) && is_string($input['email']) ? trim($input['email']) : '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'error' => 'Введите корректный email'], 400);
        }

        $user = $this->users->findByEmail($email);
        if (!$user) {
            // Для защиты от enumeration возвращаем успех даже если пользователь не найден
            return $this->json(['success' => true, 'message' => 'Если пользователь с таким email найден, мы отправили ссылку для сброса пароля']);
        }

        // Генерируем токен
        $result = $this->users->updateResetToken((int) $user['id']);
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => 'Не удалось создать токен сброса'], 500);
        }

        // Формируем ссылку для сброса
        $resetLink = sprintf(
            '%s/reset-password/%s',
            rtrim($_SERVER['HTTP_HOST'] ?? 'localhost', '/'),
            $result['token']
        );

        // Отправляем письмо
        $this->sendResetEmail($email, $result['token'], $resetLink);

        return $this->json(['success' => true, 'message' => 'Ссылка для сброса пароля отправлена на ' . $email]);
    }

    /**
     * Обработать сброс пароля.
     */
    public function handleResetPassword($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        // Rate limiting
        $maxResetAttempts = (int) ($_ENV['RATE_LIMIT_FORGOT_PASSWORD'] ?? 2);
        if ($error = $this->checkRateLimit('auth:reset-password', $maxResetAttempts, 60)) {
            return $this->json($error, 429);
        }

        $input = $request->json();
        $token = isset($input['token']) && is_string($input['token']) ? $input['token'] : '';
        $password = isset($input['password']) && is_string($input['password']) ? $input['password'] : '';
        $passwordConfirm = isset($input['password_confirm']) && is_string($input['password_confirm']) ? $input['password_confirm'] : '';

        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return $this->json(['success' => false, 'error' => 'Неверный токен'], 400);
        }
        if (strlen($password) < 8) {
            return $this->json(['success' => false, 'error' => 'Пароль должен содержать минимум 8 символов'], 400);
        }
        if ($password !== $passwordConfirm) {
            return $this->json(['success' => false, 'error' => 'Пароли не совпадают'], 400);
        }

        // Находим пользователя по токену
        $user = $this->users->findByResetToken($token);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Токен недействителен или истёк'], 400);
        }

        // Хешируем новый пароль
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Обновляем пароль
        $result = $this->users->updatePassword((int) $user['id'], $passwordHash);
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => 'Не удалось обновить пароль'], 500);
        }

        return $this->json(['success' => true]);
    }

    /**
     * Обработать выход.
     */
    public function handleLogout($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \user_start_session();

        // Запоминаем, кто выходит (до очистки сессии)
        \App\Core\Logger::write('auth', 'Выход: ' . ($_SESSION['user_name'] ?? '?') . ' (' . ($_SESSION['user_email'] ?? '?') . ')', (int) ($_SESSION['user_id'] ?? 0));

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Lax',
            ]);
        }

        session_destroy();
        return $this->json(['success' => true]);
    }

    /**
     * Выход из аккаунта из обычной HTML-формы (кнопка «Выйти» в тулбаре).
     * Закрывает сессию и редиректит на главную — форма не может получить
     * JSON-ответ, поэтому здесь именно HTTP-редирект, а не JSON.
     */
    public function logout($request)
    {
        if ($request->method() !== 'POST') {
            return new \App\Core\Response('Метод не поддерживается', 405);
        }

        \user_start_session();

        // Запоминаем, кто выходит (до очистки сессии)
        \App\Core\Logger::write('auth', 'Выход: ' . ($_SESSION['user_name'] ?? '?') . ' (' . ($_SESSION['user_email'] ?? '?') . ')', (int) ($_SESSION['user_id'] ?? 0));

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Lax',
            ]);
        }

        session_destroy();
        return \App\Core\Response::redirect('/', 303);
    }

    /**
     * Проверить авторизацию пользователя.
     */
    public function checkAuth()
    {
        \user_start_session();

        if (!empty($_SESSION['user_authenticated'])) {
            return $this->json([
                'authenticated' => true,
                'user' => [
                    'id' => (int) ($_SESSION['user_id'] ?? 0),
                    'email' => $_SESSION['user_email'] ?? '',
                    'name' => $_SESSION['user_name'] ?? '',
                    'role' => $_SESSION['user_role'] ?? 'user',
                ],
            ]);
        }

        return $this->json(['authenticated' => false]);
    }

    /**
     * Отправить письмо для сброса пароля.
     *
     * @param string $to
     * @param string $token
     * @param string $resetLink
     */
    private function sendResetEmail(string $to, string $token, string $resetLink)
    {
        $subject = 'Восстановление пароля — МойБлог';
        $body = "Для сброса пароля перейдите по ссылке:\n\n";
        $body .= $resetLink . "\n\n";
        $body .= "Ссылка действует 1 час.\n";

        $htmlBody = '<p>Для сброса пароля перейдите по ссылке:</p>';
        $htmlBody .= '<p><a href="' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '</a></p>';
        $htmlBody .= '<p>Ссылка действует 1 час.</p>';

        $this->mailer->send($subject, $body, $to, '', $htmlBody);
    }
}
