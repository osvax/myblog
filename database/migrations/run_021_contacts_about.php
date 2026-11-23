<?php

require dirname(__DIR__, 2) . '/app/bootstrap.php';

$db = \App\Core\Database::connection();
$defaults = [
    ['contactsAboutEnabled', '1', 'checkbox', 'contacts_about', 1, 'Показывать блок «Обо мне»'],
    ['contactsAboutHtml', '<h2>Обо мне</h2><p>Расскажите посетителям о себе, своём опыте и подходе к работе.</p>', 'textarea', 'contacts_about', 2, 'HTML-код блока «Обо мне»'],
    ['contactsAboutPhoto', '', 'text', 'contacts_about', 3, 'Фотография блока «Обо мне»'],
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
    echo "Настройки блока «Обо мне» добавлены.\n";
} catch (Throwable $error) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    throw $error;
}
