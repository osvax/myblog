<?php
/**
 * Генерация уникального ключа приложения (APP_KEY)
 * Формат: base64:xxxx (как в Laravel)
 * 
 * Запуск: php scripts/generate_app_key.php
 */

// Генерируем 32 случайных байта и кодируем в base64
$bytes = random_bytes(32);
$appKey = 'base64:' . base64_encode($bytes);

echo "Сгенерированный APP_KEY:\n";
echo $appKey . "\n\n";
echo "Добавьте в файл .env:\n";
echo "APP_KEY=" . $appKey . "\n";