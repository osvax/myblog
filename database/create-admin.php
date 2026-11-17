<?php

/**
 * Скрипт для создания первого пользователя-администратора.
 * Запуск: php create-admin.php <email> <password> <имя>
 */

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/app/Core/Database.php';

use App\Core\Database;

if ($argc < 4) {
    echo "Использование: php create-admin.php <email> <пароль> <имя>\n";
    exit(1);
}

$email = $argv[1];
$password = $argv[2];
$name = $argv[3];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Ошибка: неверный формат email\n";
    exit(1);
}

if (strlen($password) < 8) {
    echo "Ошибка: пароль должен содержать минимум 8 символов\n";
    exit(1);
}

$db = Database::connection();

// Проверяем, не существует ли уже пользователь с таким email
$stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
if ($stmt->fetch()) {
    echo "Ошибка: пользователь с email {$email} уже существует\n";
    exit(1);
}

// Создаём пользователя
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $db->prepare(
    "INSERT INTO users (email, password_hash, name, role, is_active, created_at, updated_at)
     VALUES (:email, :password_hash, :name, 'admin', 1, NOW(), NOW())"
);
$stmt->execute([
    ':email' => $email,
    ':password_hash' => $passwordHash,
    ':name' => $name,
]);

echo "Пользователь создан:\n";
echo "  Email: {$email}\n";
echo "  Имя: {$name}\n";
echo "  Роль: admin\n";
echo "  ID: " . $db->lastInsertId() . "\n";
