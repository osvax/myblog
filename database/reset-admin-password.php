<?php

/**
 * Сбросить пароль пользователя-администратора.
 * Запуск: php reset-admin-password.php [email]
 */

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/app/Core/Database.php';

use App\Core\Database;

function read_hidden_password(string $prompt): string
{
    if (PHP_OS_FAMILY === 'Windows') {
        $command = 'powershell.exe -NoProfile -Command "$secure = Read-Host -AsSecureString; $ptr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure); try { [Runtime.InteropServices.Marshal]::PtrToStringBSTR($ptr) } finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($ptr) }"';
        fwrite(STDOUT, $prompt);
        $password = shell_exec($command);
        return is_string($password) ? rtrim($password, "\r\n") : '';
    }

    fwrite(STDOUT, $prompt);
    shell_exec('stty -echo');
    $password = fgets(STDIN);
    shell_exec('stty echo');
    fwrite(STDOUT, PHP_EOL);
    return is_string($password) ? rtrim($password, "\r\n") : '';
}

$config = require APP_ROOT . '/config/admin-auth.php';
$email = $argc >= 2 ? trim((string) $argv[1]) : trim((string) ($config['email'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Ошибка: укажите корректный email администратора.\n");
    exit(1);
}

$password = read_hidden_password('Новый пароль: ');
$passwordConfirmation = read_hidden_password('Повторите пароль: ');

if (strlen($password) < 8) {
    fwrite(STDERR, "Ошибка: пароль должен содержать минимум 8 символов.\n");
    exit(1);
}
if ($password !== $passwordConfirmation) {
    fwrite(STDERR, "Ошибка: пароли не совпадают.\n");
    exit(1);
}

$db = Database::connection();
$stmt = $db->prepare(
    'SELECT id, email, role, is_active FROM ' . db_table('users') . ' WHERE LOWER(email) = :email LIMIT 1'
);
$stmt->execute([':email' => strtolower($email)]);
$user = $stmt->fetch();

if (!$user) {
    fwrite(STDERR, "Ошибка: пользователь с таким email не найден.\n");
    exit(1);
}
if ((string) $user['role'] !== 'admin') {
    fwrite(STDERR, "Ошибка: сброс разрешён только для пользователя с ролью admin.\n");
    exit(1);
}
if (empty($user['is_active'])) {
    fwrite(STDERR, "Ошибка: учётная запись администратора заблокирована.\n");
    exit(1);
}

$update = $db->prepare(
    'UPDATE ' . db_table('users') . ' SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id'
);
$update->execute([
    ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ':id' => (int) $user['id'],
]);

fwrite(STDOUT, "Пароль администратора обновлён: {$user['email']}\n");
