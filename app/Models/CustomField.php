<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Пользовательские поля — произвольные данные и изображения,
 * редактируемые в админке и доступные шаблонам через custom_field().
 */
final class CustomField
{
    /**
     * Все поля (для админки).
     */
    public function all(): array
    {
        $db = Database::connection();
        return $db->query(
            'SELECT * FROM ' . db_table('custom_fields') . ' ORDER BY field_group ASC, display_order ASC, id ASC'
        )->fetchAll();
    }

    /**
     * Найти поле по ключу.
     */
    public function byKey(string $key): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM ' . db_table('custom_fields') . ' WHERE field_key = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Значение поля для публичных шаблонов.
     * Неактивное или отсутствующее поле возвращает значение по умолчанию.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function value(string $key, $default = '')
    {
        $field = $this->byKey($key);
        if (!$field || (int) $field['is_active'] !== 1) {
            return $default;
        }
        $value = (string) ($field['field_value'] ?? '');
        return $value !== '' ? $value : $default;
    }

    /**
     * Создать или обновить поле.
     *
     * @param int $id 0 — создать, иначе обновить запись с этим id
     */
    public function save(
        int $id,
        string $key,
        string $label,
        string $type,
        string $value,
        string $group,
        int $isActive,
        int $displayOrder
    ): array {
        $db = Database::connection();

        // Проверяем уникальность ключа
        $stmt = $db->prepare('SELECT id FROM ' . db_table('custom_fields') . ' WHERE field_key = :key AND id != :id LIMIT 1');
        $stmt->execute(['key' => $key, 'id' => $id]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Поле с таким ключом уже существует'];
        }

        if ($id <= 0) {
            $stmt = $db->prepare(
                'INSERT INTO ' . db_table('custom_fields') . ' (field_key, label, field_type, field_value, field_group, is_active, display_order)
                 VALUES (:key, :label, :type, :value, :group, :active, :order)'
            );
            $stmt->execute([
                'key' => $key,
                'label' => $label,
                'type' => $type,
                'value' => $value,
                'group' => $group,
                'active' => $isActive,
                'order' => $displayOrder,
            ]);
            return ['success' => true, 'id' => (int) $db->lastInsertId(), 'action' => 'created'];
        }

        $stmt = $db->prepare(
            'UPDATE ' . db_table('custom_fields') . '
             SET field_key = :key, label = :label, field_type = :type, field_value = :value,
                 field_group = :group, is_active = :active, display_order = :order
             WHERE id = :id'
        );
        $stmt->execute([
            'key' => $key,
            'label' => $label,
            'type' => $type,
            'value' => $value,
            'group' => $group,
            'active' => $isActive,
            'order' => $displayOrder,
            'id' => $id,
        ]);

        return ['success' => true, 'id' => $id, 'action' => 'updated'];
    }

    /**
     * Удалить поле.
     */
    public function delete(int $id): array
    {
        $db = Database::connection();
        $stmt = $db->prepare('DELETE FROM ' . db_table('custom_fields') . ' WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return ['success' => $stmt->rowCount() > 0];
    }

    /**
     * Переключить активность поля.
     */
    public function toggleActive(int $id): array
    {
        $db = Database::connection();
        $stmt = $db->prepare('UPDATE ' . db_table('custom_fields') . ' SET is_active = 1 - is_active WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return ['success' => $stmt->rowCount() > 0];
    }
}
