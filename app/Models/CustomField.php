<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\QueryBuilder;
use App\DTO\CustomFieldDTO;
use PDO;

/**
 * CustomField — пользовательские поля (произвольные данные и изображения).
 *
 * === МИГРАЦИЯ ===
 * - Конструктор принимает PDO (для тестов — SQLite :memory:)
 * - QueryBuilder для простых запросов
 * - Возвращает CustomFieldDTO вместо массивов
 * - Статические методы-обёртки для обратной совместимости (хотя статических вызовов нет, паттерн сохранён)
 */
final class CustomField
{
    private PDO $db;
    private string $table;

    /**
     * @param PDO|null $db PDO соединение (по умолчанию Database::connection())
     */
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? \App\Core\Database::connection();
        $this->table = db_table('custom_fields');
    }

    // ===================== ОБРАТНАЯ СОВМЕСТИМОСТЬ (статические) =====================

    /**
     * Все поля (для админки) — обратная совместимость.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return array_map(
            fn (CustomFieldDTO $dto) => $dto->toApiArray(),
            (new self())->allNew()
        );
    }

    /**
     * Найти поле по ключу — обратная совместимость.
     *
     * @param string $key
     * @return array<string, mixed>|null
     */
    public static function byKey(string $key): ?array
    {
        $dto = (new self())->byKeyNew($key);
        return $dto ? $dto->toApiArray() : null;
    }

    /**
     * Значение поля для публичных шаблонов — обратная совместимость.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function value(string $key, $default = '')
    {
        return (new self())->valueNew($key, $default);
    }

    /**
     * Создать или обновить поле — обратная совместимость.
     *
     * @param int $id 0 — создать, иначе обновить запись с этим id
     * @return array<string, mixed>
     */
    public static function save(
        int $id,
        string $key,
        string $label,
        string $type,
        string $value,
        string $group,
        int $isActive,
        int $displayOrder
    ): array {
        return (new self())->saveNew($id, $key, $label, $type, $value, $group, $isActive, $displayOrder);
    }

    /**
     * Удалить поле — обратная совместимость.
     *
     * @param int $id
     * @return array{success: bool}
     */
    public static function delete(int $id): array
    {
        return (new self())->deleteNew($id);
    }

    /**
     * Переключить активность поля — обратная совместимость.
     *
     * @param int $id
     * @return array{success: bool}
     */
    public static function toggleActive(int $id): array
    {
        return (new self())->toggleActiveNew($id);
    }

    // ===================== НОВЫЙ API (DTO-based) =====================

    /**
     * Все поля (для админки).
     *
     * @return CustomFieldDTO[]
     */
    public function allNew(): array
    {
        $rows = (new QueryBuilder($this->db, $this->table))
            ->select(['id', 'field_key', 'label', 'field_type', 'field_value', 'field_group', 'is_active', 'display_order', 'created_at', 'updated_at'])
            ->orderBy('field_group', 'ASC')
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();

        return array_map([CustomFieldDTO::class, 'fromDatabase'], $rows);
    }

    /**
     * Найти поле по ключу.
     *
     * @param string $key
     * @return CustomFieldDTO|null
     */
    public function byKeyNew(string $key): ?CustomFieldDTO
    {
        $row = (new QueryBuilder($this->db, $this->table))
            ->select(['id', 'field_key', 'label', 'field_type', 'field_value', 'field_group', 'is_active', 'display_order', 'created_at', 'updated_at'])
            ->where('field_key', '=', $key)
            ->first();

        return $row ? CustomFieldDTO::fromDatabase($row) : null;
    }

    /**
     * Найти поле по ID.
     *
     * @param int $id
     * @return CustomFieldDTO|null
     */
    public function findByIdNew(int $id): ?CustomFieldDTO
    {
        $row = (new QueryBuilder($this->db, $this->table))
            ->select(['id', 'field_key', 'label', 'field_type', 'field_value', 'field_group', 'is_active', 'display_order', 'created_at', 'updated_at'])
            ->where('id', '=', $id)
            ->first();

        return $row ? CustomFieldDTO::fromDatabase($row) : null;
    }

    /**
     * Значение поля для публичных шаблонов.
     * Неактивное или отсутствующее поле возвращает значение по умолчанию.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function valueNew(string $key, $default = '')
    {
        $field = $this->byKeyNew($key);
        if (!$field || $field->isActive !== 1) {
            return $default;
        }
        $value = (string) $field->fieldValue;
        return $value !== '' ? $value : $default;
    }

    /**
     * Создать или обновить поле.
     *
     * @param int $id 0 — создать, иначе обновить запись с этим id
     * @return array{success: bool, id?: int, action?: string, error?: string}
     */
    public function saveNew(
        int $id,
        string $key,
        string $label,
        string $type,
        string $value,
        string $group,
        int $isActive,
        int $displayOrder
    ): array {
        // Проверяем уникальность ключа
        $stmt = $this->db->prepare('SELECT id FROM ' . $this->table . ' WHERE field_key = :key AND id != :id LIMIT 1');
        $stmt->execute(['key' => $key, 'id' => $id]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Поле с таким ключом уже существует'];
        }

        if ($id <= 0) {
            $stmt = $this->db->prepare(
                'INSERT INTO ' . $this->table . ' (field_key, label, field_type, field_value, field_group, is_active, display_order)
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
            return ['success' => true, 'id' => (int) $this->db->lastInsertId(), 'action' => 'created'];
        }

        $stmt = $this->db->prepare(
            'UPDATE ' . $this->table . '
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
     *
     * @param int $id
     * @return array{success: bool}
     */
    public function deleteNew(int $id): array
    {
        $stmt = $this->db->prepare('DELETE FROM ' . $this->table . ' WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return ['success' => $stmt->rowCount() > 0];
    }

    /**
     * Переключить активность поля.
     *
     * @param int $id
     * @return array{success: bool}
     */
    public function toggleActiveNew(int $id): array
    {
        $stmt = $this->db->prepare('UPDATE ' . $this->table . ' SET is_active = 1 - is_active WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return ['success' => $stmt->rowCount() > 0];
    }
}
