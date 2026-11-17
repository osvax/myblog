<?php

namespace App\Models;

use App\Core\Database;

class SiteSettings
{
    /**
     * Получить все настройки.
     *
     * @return array
     */
    public function all(): array
    {
        $db = Database::connection();
        $stmt = $db->query("SELECT * FROM site_settings ORDER BY tab_group, display_order");
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $this->castValue($row['setting_value'], $row['setting_type']);
        }

        return $result;
    }

    /**
     * Получить настройки по группам (табам).
     *
     * @return array
     */
    public function grouped(): array
    {
        $db = Database::connection();
        $stmt = $db->query("SELECT * FROM site_settings ORDER BY tab_group, display_order");
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['tab_group']][] = [
                'key' => $row['setting_key'],
                'value' => $this->castValue($row['setting_value'], $row['setting_type']),
                'type' => $row['setting_type'],
                'label' => $row['setting_key'],
                'placeholder' => $row['placeholder'],
                'options' => $row['options'] ? json_decode($row['options'], true) : [],
                'display_order' => (int) $row['display_order'],
            ];
        }

        return $result;
    }

    /**
     * Получить одну настройку.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $db = Database::connection();
        $stmt = $db->prepare("SELECT setting_value, setting_type FROM site_settings WHERE setting_key = :key LIMIT 1");
        $stmt->execute([':key' => $key]);
        $row = $stmt->fetch();

        if (!$row) {
            return $default;
        }

        return $this->castValue($row['setting_value'], $row['setting_type']);
    }

    /**
     * Сохранить все настройки.
     *
     * @param array $settings
     * @return array
     */
    public function saveAll(array $settings): array
    {
        $db = Database::connection();
        $db->beginTransaction();

        try {
            foreach ($settings as $key => $value) {
                $stmt = $db->prepare(
                    "INSERT INTO site_settings (setting_key, setting_value, setting_type, updated_at)
                     VALUES (:key, :value, 'text', NOW())
                     ON DUPLICATE KEY UPDATE setting_value = :value2, setting_type = 'text', updated_at = NOW()"
                );
                $stmt->execute([
                    ':key' => $key,
                    ':value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    ':value2' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                ]);
            }

            $db->commit();
            return ['success' => true];
        } catch (\Exception $e) {
            $db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Сохранить все настройки с сохранением типов.
     *
     * @param array $settings
     * @return array
     */
    public function saveAllWithType(array $settings): array
    {
        $db = Database::connection();
        $db->beginTransaction();

        try {
            foreach ($settings as $key => $value) {
                // Определяем тип поля
                $stmt = $db->prepare("SELECT setting_type FROM site_settings WHERE setting_key = :key LIMIT 1");
                $stmt->execute([':key' => $key]);
                $row = $stmt->fetch();

                $type = $row ? $row['setting_type'] : 'text';

                // Форматируем значение в зависимости от типа
                $formattedValue = $this->formatValue($value, $type);

                $stmt = $db->prepare(
                    "INSERT INTO site_settings (setting_key, setting_value, setting_type, updated_at)
                     VALUES (:key, :value, :type, NOW())
                     ON DUPLICATE KEY UPDATE setting_value = :value2, setting_type = :type2, updated_at = NOW()"
                );
                $stmt->execute([
                    ':key' => $key,
                    ':value' => $formattedValue,
                    ':type' => $type,
                    ':value2' => $formattedValue,
                    ':type2' => $type,
                ]);
            }

            $db->commit();
            return ['success' => true];
        } catch (\Exception $e) {
            $db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Форматировать значение по типу.
     */
    private function formatValue($value, string $type)
    {
        switch ($type) {
            case 'checkbox':
                return $value ? '1' : '0';
            case 'number':
                return (string) (int) $value;
            case 'color':
                return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $value : '';
            case 'select':
                return (string) $value;
            default:
                return (string) $value;
        }
    }

    /**
     * Добавить пользовательское поле.
     *
     * @param string $key
     * @param string $value
     * @param string $type
     * @param string $tabGroup
     * @param string $placeholder
     * @return array
     */
    public function addCustomField(
        string $key,
        string $value = '',
        string $type = 'text',
        string $tabGroup = 'general',
        string $placeholder = ''
    ): array {
        $db = Database::connection();

        // Проверяем уникальность
        $stmt = $db->prepare("SELECT id FROM site_settings WHERE setting_key = :key LIMIT 1");
        $stmt->execute([':key' => $key]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Поле с таким именем уже существует'];
        }

        // Получаем максимальный порядок для группы
        $stmt = $db->prepare("SELECT COALESCE(MAX(display_order), 0) + 1 AS max_order FROM site_settings WHERE tab_group = :group");
        $stmt->execute([':group' => $tabGroup]);
        $order = (int) $stmt->fetch()['max_order'];

        $stmt = $db->prepare(
            "INSERT INTO site_settings (setting_key, setting_value, setting_type, tab_group, display_order, placeholder)
             VALUES (:key, :value, :type, :group, :order, :placeholder)"
        );
        $stmt->execute([
            ':key' => $key,
            ':value' => $value,
            ':type' => $type,
            ':group' => $tabGroup,
            ':order' => $order,
            ':placeholder' => $placeholder,
        ]);

        return ['success' => true, 'id' => (int) $db->lastInsertId()];
    }

    /**
     * Удалить пользовательское поле.
     *
     * @param string $key
     * @return array
     */
    public function deleteCustomField(string $key): array
    {
        $db = Database::connection();
        $stmt = $db->prepare("DELETE FROM site_settings WHERE setting_key = :key");
        $stmt->execute([':key' => $key]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'error' => 'Поле не найдено'];
        }

        return ['success' => true];
    }

    /**
     * Привести значение к правильному типу.
     *
     * @param string|null $value
     * @param string $type
     * @return mixed
     */
    private function castValue(?string $value, string $type)
    {
        if ($value === null || $value === '') {
            return $type === 'checkbox' ? false : '';
        }

        switch ($type) {
            case 'checkbox':
                return (bool) $value;
            case 'number':
                return (int) $value;
            case 'select':
                return $value;
            default:
                return $value;
        }
    }
}
