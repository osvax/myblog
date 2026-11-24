<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\QueryBuilder;
use App\DTO\SiteSettingsDTO;
use PDO;

/**
 * Настройки сайта (ключ-значение с типами).
 *
 * === МИГРАЦИЯ ===
 * - Конструктор принимает PDO (для тестов — SQLite :memory:)
 * - QueryBuilder для SELECT/INSERT/UPDATE/DELETE
 * - Возвращает SiteSettingsDTO вместо массивов
 * - Статические методы-обёртки для обратной совместимости
 */
final class SiteSettings
{
    private PDO $db;
    private string $table;

    /**
     * @param PDO|null $db PDO соединение (по умолчанию Database::connection())
     */
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? \App\Core\Database::connection();
        $this->table = db_table('site_settings');
    }

    // ===================== ОБРАТНАЯ СОВМЕСТИМОСТЬ (статические) =====================

    /** @return array<string, mixed> Все настройки плоским массивом (старый API) */
    public static function all(): array
    {
        return (new self())->allNew()->toApiArray();
    }

    /** @return array<string, array> Настройки сгруппированными по вкладкам (старый API) */
    public static function grouped(): array
    {
        return (new self())->groupedNew()->toGroupedArray();
    }

    /** @return mixed Значение одной настройки (старый API) */
    public static function get(string $key, mixed $default = null): mixed
    {
        return (new self())->getNew($key, $default);
    }

    /** @return array<string, mixed> Сохранить все настройки (старый API) */
    public static function saveAll(array $settings): array
    {
        return (new self())->saveAllNew($settings);
    }

    /** @return array<string, mixed> Сохранить все настройки с типами (старый API) */
    public static function saveAllWithType(array $settings): array
    {
        return (new self())->saveAllWithTypeNew($settings);
    }

    /** @return array<string, mixed> Добавить пользовательское поле (старый API) */
    public static function addCustomField(
        string $key,
        string $value = '',
        string $type = 'text',
        string $tabGroup = 'general',
        string $placeholder = ''
    ): array {
        return (new self())->addCustomFieldNew($key, $value, $type, $tabGroup, $placeholder);
    }

    /** @return array<string, mixed> Удалить пользовательское поле (старый API) */
    public static function deleteCustomField(string $key): array
    {
        return (new self())->deleteCustomFieldNew($key);
    }

    // ===================== НОВЫЙ API (DTO-based) =====================

    /** @return SiteSettingsDTO Все настройки с метаданными */
    public function allNew(): SiteSettingsDTO
    {
        $rows = (new QueryBuilder($this->db, $this->table))
            ->select(['*'])
            ->orderBy('tab_group', 'ASC')
            ->orderBy('display_order', 'ASC')
            ->get();

        return SiteSettingsDTO::fromDatabase($rows);
    }

    /** @return SiteSettingsDTO Все настройки сгруппированными по вкладкам */
    public function groupedNew(): SiteSettingsDTO
    {
        // То же самое, что allNew() — DTO сам умеет группировать через toGroupedArray()
        return $this->allNew();
    }

    /** @return mixed Значение одной настройки */
    public function getNew(string $key, mixed $default = null): mixed
    {
        $row = (new QueryBuilder($this->db, $this->table))
            ->select(['setting_value', 'setting_type'])
            ->where('setting_key', '=', $key)
            ->first();

        if (!$row) {
            return $default;
        }

        return $this->castValue((string)($row['setting_value'] ?? ''), (string)($row['setting_type'] ?? SiteSettingsDTO::TYPE_TEXT));
    }

    /** Приводит строковое значение из БД к правильному PHP типу. */
    private function castValue(string $value, string $type): mixed
    {
        if ($value === '') {
            return match ($type) {
                SiteSettingsDTO::TYPE_CHECKBOX => false,
                SiteSettingsDTO::TYPE_NUMBER => 0,
                SiteSettingsDTO::TYPE_JSON => [],
                default => '',
            };
        }

        return match ($type) {
            SiteSettingsDTO::TYPE_CHECKBOX => (bool)$value,
            SiteSettingsDTO::TYPE_NUMBER => (int)$value,
            SiteSettingsDTO::TYPE_JSON => json_decode($value, true) ?? [],
            default => $value,
        };
    }

    /** @return array<string, mixed> Сохранить все настройки из плоского массива */
    public function saveAllNew(array $settings): array
    {
        try {
            $this->db->beginTransaction();

            foreach ($settings as $key => $value) {
                $formattedValue = $this->formatValueForSave($value);
                $stmt = $this->db->prepare(
                    "INSERT INTO {$this->table} (setting_key, setting_value, setting_type, updated_at)
                     VALUES (:key, :value, 'text', NOW())
                     ON DUPLICATE KEY UPDATE setting_value = :value2, setting_type = 'text', updated_at = NOW()"
                );
                $stmt->execute([
                    'key' => $key,
                    'value' => $formattedValue,
                    'value2' => $formattedValue,
                ]);
            }

            $this->db->commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** @return array<string, mixed> Сохранить все настройки с сохранением типов (DTO-based) */
    public function saveAllWithTypeNew(array $settings): array
    {
        // Получаем существующие метаданные из БД
        $existingMeta = $this->fetchMeta();

        $dto = SiteSettingsDTO::fromRequest($settings, $existingMeta);

        if (!$dto->validate()) {
            return ['success' => false, 'error' => implode('; ', $dto->getErrors())];
        }

        try {
            $this->db->beginTransaction();

            // Сохраняем значения и типы
            foreach ($dto->toDbArray() as $key => $data) {
                $stmt = $this->db->prepare(
                    "INSERT INTO {$this->table} (setting_key, setting_value, setting_type, updated_at)
                     VALUES (:key, :value, :type, NOW())
                     ON DUPLICATE KEY UPDATE setting_value = :value2, setting_type = :type2, updated_at = NOW()"
                );
                $stmt->execute([
                    'key' => $key,
                    'value' => $data['value'],
                    'type' => $data['type'],
                    'value2' => $data['value'],
                    'type2' => $data['type'],
                ]);
            }

            // Сохраняем метаданные новых полей (tab_group, placeholder, options, display_order)
            $meta = $dto->getMetaForSave();
            foreach ($meta as $key => $m) {
                if ($dto->isSystemKey($key)) {
                    continue; // системные поля не меняем
                }
                $stmt = $this->db->prepare(
                    "UPDATE {$this->table} SET tab_group = :group, placeholder = :placeholder, options = :options, display_order = :order WHERE setting_key = :key"
                );
                $stmt->execute([
                    'group' => $m['tab_group'] ?? SiteSettingsDTO::TAB_CUSTOM,
                    'placeholder' => $m['placeholder'] ?? '',
                    'options' => empty($m['options']) ? null : json_encode($m['options'], JSON_UNESCAPED_UNICODE),
                    'order' => (int)($m['display_order'] ?? 0),
                    'key' => $key,
                ]);
            }

            $this->db->commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** @return array<string, mixed> Добавить пользовательское поле */
    public function addCustomFieldNew(
        string $key,
        string $value = '',
        string $type = 'text',
        string $tabGroup = 'general',
        string $placeholder = ''
    ): array {
        // Проверяем уникальность
        $exists = (new QueryBuilder($this->db, $this->table))
            ->select(['id'])
            ->where('setting_key', '=', $key)
            ->first();

        if ($exists) {
            return ['success' => false, 'error' => 'Поле с таким именем уже существует'];
        }

        // Получаем максимальный порядок для группы
        $maxOrder = (new QueryBuilder($this->db, $this->table))
            ->select(['COALESCE(MAX(display_order), 0) + 1 AS max_order'])
            ->where('tab_group', '=', $tabGroup)
            ->first();

        $order = (int)($maxOrder['max_order'] ?? 1);

        try {
            $formattedValue = $this->formatValueForSave($value, $type);

            $stmt = $this->db->prepare(
                "INSERT INTO {$this->table} (setting_key, setting_value, setting_type, tab_group, display_order, placeholder)
                 VALUES (:key, :value, :type, :group, :order, :placeholder)"
            );
            $stmt->execute([
                'key' => $key,
                'value' => $formattedValue,
                'type' => $type,
                'group' => $tabGroup,
                'order' => $order,
                'placeholder' => $placeholder,
            ]);

            return ['success' => true, 'id' => (int)$this->db->lastInsertId()];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** @return array<string, mixed> Удалить пользовательское поле */
    public function deleteCustomFieldNew(string $key): array
    {
        // Не удаляем системные ключи
        if (in_array($key, SiteSettingsDTO::getSystemKeys(), true)) {
            return ['success' => false, 'error' => 'Системные настройки удалять нельзя'];
        }

        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE setting_key = :key");
        $stmt->execute(['key' => $key]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'error' => 'Поле не найдено'];
        }

        return ['success' => true];
    }

    // ===================== PRIVATE HELPERS =====================

    /** Получает метаданные всех полей из БД. */
    private function fetchMeta(): array
    {
        $rows = (new QueryBuilder($this->db, $this->table))
            ->select(['setting_key', 'setting_type', 'tab_group', 'placeholder', 'options', 'display_order'])
            ->get();

        $meta = [];
        foreach ($rows as $row) {
            $meta[$row['setting_key']] = [
                'type' => $row['setting_type'] ?? SiteSettingsDTO::TYPE_TEXT,
                'tab_group' => $row['tab_group'] ?? SiteSettingsDTO::TAB_CUSTOM,
                'label' => $row['setting_key'],
                'placeholder' => $row['placeholder'] ?? '',
                'options' => $row['options'] ? json_decode($row['options'], true) : [],
                'display_order' => (int)($row['display_order'] ?? 0),
            ];
        }
        return $meta;
    }

    /** Форматирует значение для сохранения в БД (простая версия для saveAll). */
    private function formatValueForSave(mixed $value, string $type = 'text'): string
    {
        return match ($type) {
            'checkbox' => $value ? '1' : '0',
            'number' => (string)(int)$value,
            'color' => (is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)) ? $value : '',
            'json' => is_array($value)
                ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : (string)$value,
            default => (string)$value,
        };
    }
}
