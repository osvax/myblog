<?php

require dirname(__DIR__, 2) . '/app/bootstrap.php';

$db = \App\Core\Database::connection();
$defaults = [
    ['contactAddressEnabled', '1', 'checkbox', 'contacts', 10, 'Показывать адрес'],
    ['contactAddressLabel', 'Адрес', 'text', 'contacts', 11, 'Название поля адреса'],
    ['contactHoursEnabled', '1', 'checkbox', 'contacts', 12, 'Показывать часы работы'],
    ['contactHoursLabel', 'Часы работы', 'text', 'contacts', 13, 'Название поля часов работы'],
    ['contactEmailEnabled', '1', 'checkbox', 'contacts', 14, 'Показывать email'],
    ['contactEmailLabel', 'Email', 'text', 'contacts', 15, 'Название поля email'],
    ['contactPhoneEnabled', '1', 'checkbox', 'contacts', 16, 'Показывать телефон'],
    ['contactPhoneLabel', 'Телефон', 'text', 'contacts', 17, 'Название поля телефона'],
    ['socialTelegramEnabled', '1', 'checkbox', 'contacts', 18, 'Показывать Telegram'],
    ['socialTelegramLabel', 'Telegram', 'text', 'contacts', 19, 'Название кнопки Telegram'],
    ['socialVkEnabled', '1', 'checkbox', 'contacts', 20, 'Показывать VK'],
    ['socialVkLabel', 'VK', 'text', 'contacts', 21, 'Название кнопки VK'],
    ['socialGithubEnabled', '1', 'checkbox', 'contacts', 22, 'Показывать GitHub'],
    ['socialGithubLabel', 'GitHub', 'text', 'contacts', 23, 'Название кнопки GitHub'],
    ['socialYoutubeEnabled', '1', 'checkbox', 'contacts', 24, 'Показывать YouTube'],
    ['socialYoutubeLabel', 'YouTube', 'text', 'contacts', 25, 'Название кнопки YouTube'],
];

$stmt = $db->prepare(
    'INSERT IGNORE INTO ' . db_table('site_settings') . '
        (setting_key, setting_value, setting_type, tab_group, display_order, placeholder)
     VALUES (:key, :value, :type, :group_name, :display_order, :placeholder)'
);

$db->beginTransaction();
try {
    foreach ($defaults as $default) {
        $stmt->execute([
            'key' => $default[0],
            'value' => $default[1],
            'type' => $default[2],
            'group_name' => $default[3],
            'display_order' => $default[4],
            'placeholder' => $default[5],
        ]);
    }
    $db->commit();
    echo "Настройки отображения контактов и соцсетей добавлены.\n";
} catch (Throwable $error) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    throw $error;
}
