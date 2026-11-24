<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\QueryBuilder;
use App\DTO\LandingPageSettingDTO;
use PDO;

/**
 * LandingPageSetting — настройки лендинга (поля для генерации страниц).
 *
 * === МИГРАЦИЯ ===
 * - Конструктор принимает PDO (для тестов — SQLite :memory:)
 * - QueryBuilder для простых запросов
 * - Возвращает LandingPageSettingDTO вместо массивов
 * - Статические методы-обёртки для обратной совместимости
 */
final class LandingPageSetting
{
    private PDO $db;
    private string $table;

    /**
     * @param PDO|null $db PDO соединение (по умолчанию Database::connection())
     */
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? \App\Core\Database::connection();
        $this->table = db_table('landing_page_settings');
    }

    // ===================== ОБРАТНАЯ СОВМЕСТИМОСТЬ (статические) =====================

    /**
     * Получить все настройки лендинга (обратная совместимость — возвращает массивы).
     *
     * @param string|null $sectionKey Фильтр по секции
     * @return array<int, array<string, mixed>>
     */
    public static function getAll(?string $sectionKey = null): array
    {
        return array_map(
            fn (LandingPageSettingDTO $dto) => $dto->toApiArray(),
            (new self())->getAllNew($sectionKey)
        );
    }

    /**
     * Получить одну настройку (обратная совместимость — возвращает массив).
     *
     * @param string $sectionKey
     * @param string $fieldName
     * @return array<string, mixed>|null
     */
    public static function get(string $sectionKey, string $fieldName): ?array
    {
        $dto = (new self())->getNew($sectionKey, $fieldName);
        return $dto ? $dto->toApiArray() : null;
    }

    /**
     * Получить все секции (обратная совместимость — возвращает массивы).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getSections(): array
    {
        return array_map(
            fn (LandingPageSettingDTO $dto) => $dto->toApiArray(),
            (new self())->getSectionsNew()
        );
    }

    /**
     * Добавить или обновить настройку (обратная совместимость — возвращает массив).
     *
     * @param string $section
     * @param string $sectionKey
     * @param string $fieldName
     * @param string $fieldValue
     * @param string $fieldType
     * @param string $fieldGroup
     * @param int $displayOrder
     * @param string $placeholder
     * @param string $options
     * @param int $isActive
     * @param int $editId
     * @return array<string, mixed>
     */
    public static function upsert(
        string $section,
        string $sectionKey,
        string $fieldName,
        string $fieldValue = '',
        string $fieldType = 'text',
        string $fieldGroup = 'general',
        int $displayOrder = 0,
        string $placeholder = '',
        string $options = '',
        int $isActive = 1,
        int $editId = 0
    ): array {
        return (new self())->upsertNew(
            $section,
            $sectionKey,
            $fieldName,
            $fieldValue,
            $fieldType,
            $fieldGroup,
            $displayOrder,
            $placeholder,
            $options,
            $isActive,
            $editId
        );
    }

    /**
     * Удалить настройку (обратная совместимость — возвращает массив).
     *
     * @param int $settingId
     * @return array{success: bool}
     */
    public static function delete(int $settingId): array
    {
        return (new self())->deleteNew($settingId);
    }

    /**
     * Переключить активность (обратная совместимость — возвращает массив).
     *
     * @param int $settingId
     * @return array{success: bool}
     */
    public static function toggleActive(int $settingId): array
    {
        return (new self())->toggleActiveNew($settingId);
    }

    // ===================== НОВЫЙ API (DTO-based) =====================

    /**
     * Получить все настройки лендинга.
     *
     * @param string|null $sectionKey Фильтр по секции
     * @return LandingPageSettingDTO[]
     */
    public function getAllNew(?string $sectionKey = null): array
    {
        $qb = (new QueryBuilder($this->db, $this->table))
            ->select(['id', 'section', 'section_key', 'field_name', 'field_value', 'field_type', 'field_group', 'display_order', 'placeholder', 'options', 'is_active', 'created_at', 'updated_at'])
            ->orderBy('field_group', 'ASC')
            ->orderBy('display_order', 'ASC')
            ->orderBy('created_at', 'ASC');

        if ($sectionKey !== null && $sectionKey !== '') {
            $qb->where('section_key', '=', $sectionKey);
        }

        $rows = $qb->get();
        return array_map([LandingPageSettingDTO::class, 'fromDatabase'], $rows);
    }

    /**
     * Получить одну настройку.
     *
     * @param string $sectionKey
     * @param string $fieldName
     * @return LandingPageSettingDTO|null
     */
    public function getNew(string $sectionKey, string $fieldName): ?LandingPageSettingDTO
    {
        $row = (new QueryBuilder($this->db, $this->table))
            ->select(['id', 'section', 'section_key', 'field_name', 'field_value', 'field_type', 'field_group', 'display_order', 'placeholder', 'options', 'is_active', 'created_at', 'updated_at'])
            ->where('section_key', '=', $sectionKey)
            ->where('field_name', '=', $fieldName)
            ->first();

        return $row ? LandingPageSettingDTO::fromDatabase($row) : null;
    }

    /**
     * Получить все секции.
     *
     * @return LandingPageSettingDTO[]
     */
    public function getSectionsNew(): array
    {
        $rows = (new QueryBuilder($this->db, $this->table))
            ->select(['DISTINCT section, section_key, field_group'])
            ->orderBy('display_order', 'ASC')
            ->get();

        return array_map([LandingPageSettingDTO::class, 'fromDatabase'], $rows);
    }

    /**
     * Добавить или обновить настройку.
     *
     * @param string $section
     * @param string $sectionKey
     * @param string $fieldName
     * @param string $fieldValue
     * @param string $fieldType
     * @param string $fieldGroup
     * @param int $displayOrder
     * @param string $placeholder
     * @param string $options
     * @param int $isActive
     * @param int $editId
     * @return array{success: bool, id?: int, action?: string, error?: string}
     */
    public function upsertNew(
        string $section,
        string $sectionKey,
        string $fieldName,
        string $fieldValue = '',
        string $fieldType = 'text',
        string $fieldGroup = 'general',
        int $displayOrder = 0,
        string $placeholder = '',
        string $options = '',
        int $isActive = 1,
        int $editId = 0
    ): array {
        // Если есть edit_id, обновляем существующее
        if ($editId > 0) {
            $stmt = $this->db->prepare(
                'UPDATE ' . $this->table . '
                 SET field_value = :value, field_type = :type, field_group = :group,
                     display_order = :order, placeholder = :placeholder, options = :options,
                     is_active = :active, updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                ':value' => $fieldValue,
                ':type' => $fieldType,
                ':group' => $fieldGroup,
                ':order' => $displayOrder,
                ':placeholder' => $placeholder,
                ':options' => $options ?: null,
                ':active' => $isActive,
                ':id' => $editId,
            ]);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'error' => 'Настройка не найдена'];
            }

            return ['success' => true, 'id' => $editId, 'action' => 'updated'];
        }

        // Проверяем, существует ли уже такое поле
        $stmt = $this->db->prepare(
            'SELECT id FROM ' . $this->table . ' WHERE section_key = :section AND field_name = :name LIMIT 1'
        );
        $stmt->execute([':section' => $sectionKey, ':name' => $fieldName]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Обновляем
            $stmt = $this->db->prepare(
                'UPDATE ' . $this->table . '
                 SET field_value = :value, field_type = :type, field_group = :group,
                     display_order = :order, placeholder = :placeholder, options = :options,
                     is_active = :active, updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                ':value' => $fieldValue,
                ':type' => $fieldType,
                ':group' => $fieldGroup,
                ':order' => $displayOrder,
                ':placeholder' => $placeholder,
                ':options' => $options ?: null,
                ':active' => $isActive,
                ':id' => (int) $existing['id'],
            ]);

            return ['success' => true, 'id' => (int) $existing['id'], 'action' => 'updated'];
        }

        // Создаём новое
        $stmt = $this->db->prepare(
            'INSERT INTO ' . $this->table . '
             (section, section_key, field_name, field_value, field_type, field_group, display_order, placeholder, options, is_active, created_at, updated_at)
             VALUES (:section, :section_key, :name, :value, :type, :group, :order, :placeholder, :options, :active, NOW(), NOW())'
        );
        $stmt->execute([
            ':section' => $section,
            ':section_key' => $sectionKey,
            ':name' => $fieldName,
            ':value' => $fieldValue,
            ':type' => $fieldType,
            ':group' => $fieldGroup,
            ':order' => $displayOrder,
            ':placeholder' => $placeholder,
            ':options' => $options ?: null,
            ':active' => $isActive,
        ]);

        return ['success' => true, 'id' => (int) $this->db->lastInsertId(), 'action' => 'created'];
    }

    /**
     * Удалить настройку.
     *
     * @param int $settingId
     * @return array{success: bool}
     */
    public function deleteNew(int $settingId): array
    {
        $stmt = $this->db->prepare('DELETE FROM ' . $this->table . ' WHERE id = :id');
        $stmt->execute([':id' => $settingId]);

        return ['success' => $stmt->rowCount() > 0];
    }

    /**
     * Переключить активность.
     *
     * @param int $settingId
     * @return array{success: bool}
     */
    public function toggleActiveNew(int $settingId): array
    {
        $stmt = $this->db->prepare(
            'UPDATE ' . $this->table . ' SET is_active = NOT is_active, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([':id' => $settingId]);

        return ['success' => $stmt->rowCount() > 0];
    }
}
