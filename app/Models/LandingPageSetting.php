<?php

namespace App\Models;

use App\Core\Database;

class LandingPageSetting
{
    /**
     * Получить все настройки лендинга.
     *
     * @param string|null $sectionKey Фильтр по секции
     * @return array
     */
    public function getAll(?string $sectionKey = null): array
    {
        $db = Database::connection();

        if ($sectionKey !== null && $sectionKey !== '') {
            $stmt = $db->prepare(
                "SELECT * FROM " . db_table('landing_page_settings') . " WHERE section_key = :key ORDER BY display_order ASC, created_at ASC"
            );
            $stmt->execute([':key' => $sectionKey]);
        } else {
            $stmt = $db->query(
                "SELECT * FROM " . db_table('landing_page_settings') . " ORDER BY field_group ASC, display_order ASC, created_at ASC"
            );
        }

        return $stmt->fetchAll();
    }

    /**
     * Получить одну настройку.
     *
     * @param string $sectionKey
     * @param string $fieldName
     * @return array|null
     */
    public function get(string $sectionKey, string $fieldName): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "SELECT * FROM " . db_table('landing_page_settings') . " WHERE section_key = :section AND field_name = :name LIMIT 1"
        );
        $stmt->execute([':section' => $sectionKey, ':name' => $fieldName]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Получить все секции.
     *
     * @return array
     */
    public function getSections(): array
    {
        $db = Database::connection();
        $stmt = $db->query(
            "SELECT DISTINCT section, section_key, field_group FROM " . db_table('landing_page_settings') . " ORDER BY display_order ASC"
        );
        return $stmt->fetchAll();
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
     * @return array
     */
    public function upsert(
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
        $db = Database::connection();

        // Если есть edit_id, обновляем существующее
        if ($editId > 0) {
            $stmt = $db->prepare(
                "UPDATE " . db_table('landing_page_settings') . "
                 SET field_value = :value, field_type = :type, field_group = :group,
                     display_order = :order, placeholder = :placeholder, options = :options,
                     is_active = :active, updated_at = NOW()
                 WHERE id = :id"
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
        $stmt = $db->prepare(
            "SELECT id FROM " . db_table('landing_page_settings') . " WHERE section_key = :section AND field_name = :name LIMIT 1"
        );
        $stmt->execute([':section' => $sectionKey, ':name' => $fieldName]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Обновляем
            $stmt = $db->prepare(
                "UPDATE " . db_table('landing_page_settings') . "
                 SET field_value = :value, field_type = :type, field_group = :group,
                     display_order = :order, placeholder = :placeholder, options = :options,
                     is_active = :active, updated_at = NOW()
                 WHERE id = :id"
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
        $stmt = $db->prepare(
            "INSERT INTO " . db_table('landing_page_settings') . "
             (section, section_key, field_name, field_value, field_type, field_group, display_order, placeholder, options, is_active, created_at, updated_at)
             VALUES (:section, :section_key, :name, :value, :type, :group, :order, :placeholder, :options, :active, NOW(), NOW())"
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

        return ['success' => true, 'id' => (int) $db->lastInsertId(), 'action' => 'created'];
    }

    /**
     * Удалить настройку.
     *
     * @param int $settingId
     * @return array
     */
    public function delete(int $settingId): array
    {
        $db = Database::connection();
        $stmt = $db->prepare("DELETE FROM " . db_table('landing_page_settings') . " WHERE id = :id");
        $stmt->execute([':id' => $settingId]);

        return ['success' => $stmt->rowCount() > 0];
    }

    /**
     * Переключить активность.
     *
     * @param int $settingId
     * @return array
     */
    public function toggleActive(int $settingId): array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "UPDATE " . db_table('landing_page_settings') . " SET is_active = NOT is_active, updated_at = NOW() WHERE id = :id"
        );
        $stmt->execute([':id' => $settingId]);

        return ['success' => $stmt->rowCount() > 0];
    }
}
