<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\ApplicationLogger;
use App\Core\QueryBuilder;
use App\DTO\SiteSettingsDTO;
use PDO;

final class SettingsRepository
{
    private $db;
    private $logger;

    public function __construct(?PDO $db = null, ?ApplicationLogger $logger = null)
    {
        $this->db = $db ?? \App\Core\Database::connection();
        $this->logger = $logger ?? (function_exists('container') ? container(ApplicationLogger::class) : null);
    }

    /**
     * Базовый QueryBuilder для site_settings.
     */
    private function baseQuery(): QueryBuilder
    {
        return new QueryBuilder($this->db, 'site_settings');
    }

    /**
     * Все настройки (плоский массив key => value) — для обратной совместимости.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $rows = $this->baseQuery()
            ->select(['setting_key', 'setting_value', 'setting_type'])
            ->orderBy('tab_group', 'ASC')
            ->orderBy('display_order', 'ASC')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $this->castValue($row['setting_value'], $row['setting_type']);
        }

        return $result;
    }

    /**
     * Настройки сгруппированные по табам — для админки (обратная совместимость).
     *
     * @return array
     */
    public function grouped(): array
    {
        $rows = $this->baseQuery()
            ->select(['*'])
            ->orderBy('tab_group', 'ASC')
            ->orderBy('display_order', 'ASC')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $tabGroup = $row['tab_group'] ?? 'custom';
            $result[$tabGroup][] = [
                'id' => (int) $row['id'],
                'key' => $row['setting_key'],
                'value' => $this->castValue($row['setting_value'], $row['setting_type']),
                'type' => $row['setting_type'],
                'tab_group' => $tabGroup,
                'label' => $row['setting_key'], // Можно расширить: добавить label в БД
                'placeholder' => $row['placeholder'] ?? '',
                'options' => $row['options'] ? json_decode($row['options'], true) : [],
                'display_order' => (int) ($row['display_order'] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Все настройки как SiteSettingsDTO (новый API).
     *
     * @return SiteSettingsDTO
     */
    public function allAsDto(): SiteSettingsDTO
    {
        $rows = $this->baseQuery()
            ->select(['*'])
            ->orderBy('tab_group', 'ASC')
            ->orderBy('display_order', 'ASC')
            ->get();

        return SiteSettingsDTO::fromDatabase($rows);
    }

    /**
     * Настройки сгруппированные по табам как массив для API (новый API).
     *
     * @return array
     */
    public function groupedAsDto(): array
    {
        $dto = $this->allAsDto();
        return $dto->toGroupedArray();
    }

    /**
     * Получение одной настройки по ключу.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $stmt = $this->db->prepare(
            'SELECT setting_value, setting_type FROM ' . db_table('site_settings') . ' WHERE setting_key = :key LIMIT 1'
        );
        $stmt->execute([':key' => $key]);
        $row = $stmt->fetch();

        if (!$row) {
            return $default;
        }

        return $this->castValue($row['setting_value'], $row['setting_type']);
    }

    /**
     * Сохраняет все настройки из SiteSettingsDTO (новый предпочтительный способ).
     * Делает UPSERT для каждого ключа.
     *
     * @param SiteSettingsDTO $dto
     * @return array['success' => bool, 'error' => string|null]
     */
    public function saveAllFromDTO(SiteSettingsDTO $dto): array
    {
        if (!$dto->validate()) {
            return ['success' => false, 'error' => 'Ошибки валидации: ' . json_encode($dto->getErrors())];
        }

        $dbArray = $dto->toDbArray(); // ['key' => ['value' => ..., 'type' => ...], ...]
        $meta = $dto->getMetaForSave();

        try {
            foreach ($dbArray as $key => $item) {
                $value = $item['value'];
                $type = $item['type'];
                $metaItem = $meta[$key] ?? [];

                $this->upsertSetting($key, $value, $type, $metaItem);
            }
            return ['success' => true];
        } catch (\Throwable $e) {
            if ($this->logger) {
                $this->logger->logException($e, 'Settings save error');
            } else {
                error_log('Settings save error: ' . $e->getMessage());
            }
            return ['success' => false, 'error' => 'Ошибка сохранения настроек: ' . $e->getMessage()];
        }
    }

    /**
     * Сохраняет настройки из массива (обратная совместимость).
     *
     * @param array $data Плоский массив key => value
     * @return array['success' => bool, 'error' => string|null]
     */
    public function saveAllWithType(array $data): array
    {
        // Получаем существующие метаданные из БД
        $existingMeta = [];
        $rows = $this->db->query('SELECT setting_key, setting_type, tab_group, display_order, placeholder, options FROM ' . db_table('site_settings'))->fetchAll();
        foreach ($rows as $row) {
            $existingMeta[$row['setting_key']] = [
                'type' => $row['setting_type'],
                'tab_group' => $row['tab_group'],
                'placeholder' => $row['placeholder'] ?? '',
                'options' => $row['options'] ? json_decode($row['options'], true) : [],
                'display_order' => (int) ($row['display_order'] ?? 0),
            ];
        }

        $dto = SiteSettingsDTO::fromRequest($data, $existingMeta);
        return $this->saveAllFromDTO($dto);
    }

    /**
     * Добавляет пользовательское поле (обратная совместимость).
     *
     * @param string $key
     * @param string $value
     * @param string $type
     * @param string $tabGroup
     * @param string $placeholder
     * @return array['success' => bool, 'id' => int|null, 'error' => string|null]
     */
    public function addCustomField(string $key, string $value, string $type, string $tabGroup, string $placeholder): array
    {
        // Проверяем, не существует ли уже
        $exists = $this->db->prepare('SELECT id FROM ' . db_table('site_settings') . ' WHERE setting_key = :key');
        $exists->execute([':key' => $key]);
        if ($exists->fetch()) {
            return ['success' => false, 'error' => 'Поле с таким ключом уже существует'];
        }

        // Получаем максимальный display_order для группы
        $orderStmt = $this->db->prepare(
            'SELECT COALESCE(MAX(display_order), 0) + 1 FROM ' . db_table('site_settings') . ' WHERE tab_group = :tab_group'
        );
        $orderStmt->execute([':tab_group' => $tabGroup]);
        $displayOrder = (int) $orderStmt->fetchColumn();

        $stmt = $this->db->prepare(
            'INSERT INTO ' . db_table('site_settings') . ' (setting_key, setting_value, setting_type, tab_group, display_order, placeholder)
             VALUES (:key, :value, :type, :tab_group, :display_order, :placeholder)'
        );
        $stmt->execute([
            ':key' => $key,
            ':value' => $value,
            ':type' => $type,
            ':tab_group' => $tabGroup,
            ':display_order' => $displayOrder,
            ':placeholder' => $placeholder,
        ]);

        return ['success' => true, 'id' => (int) $this->db->lastInsertId()];
    }

    /**
     * Удаляет пользовательское поле (обратная совместимость).
     *
     * @param string $key
     * @return array['success' => bool, 'error' => string|null]
     */
    public function deleteCustomField(string $key): array
    {
        // Проверяем, является ли системным
        if (in_array($key, SiteSettingsDTO::getSystemKeys(), true)) {
            return ['success' => false, 'error' => 'Системные поля нельзя удалять'];
        }

        $stmt = $this->db->prepare('DELETE FROM ' . db_table('site_settings') . ' WHERE setting_key = :key');
        $stmt->execute([':key' => $key]);

        if ($stmt->rowCount() > 0) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Поле не найдено'];
    }

    /**
     * UPSERT одной настройки.
     */
    private function upsertSetting(string $key, string $value, string $type, array $meta): void
    {
        $tabGroup = $meta['tab_group'] ?? SiteSettingsDTO::TAB_CUSTOM;
        $displayOrder = $meta['display_order'] ?? 0;
        $placeholder = $meta['placeholder'] ?? '';
        $options = $meta['options'] ?? [];

        // Проверяем, существует ли настройка
        $exists = $this->db->prepare('SELECT id FROM ' . db_table('site_settings') . ' WHERE setting_key = :key');
        $exists->execute([':key' => $key]);
        $row = $exists->fetch();

        if ($row) {
            // UPDATE
            $stmt = $this->db->prepare(
                'UPDATE ' . db_table('site_settings') . '
                 SET setting_value = :value, setting_type = :type, tab_group = :tab_group,
                     display_order = :display_order, placeholder = :placeholder,
                     options = :options, updated_at = NOW()
                 WHERE setting_key = :key'
            );
            $stmt->execute([
                ':value' => $value,
                ':type' => $type,
                ':tab_group' => $tabGroup,
                ':display_order' => $displayOrder,
                ':placeholder' => $placeholder,
                ':options' => $options ? json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                ':key' => $key,
            ]);
        } else {
            // INSERT
            $stmt = $this->db->prepare(
                'INSERT INTO ' . db_table('site_settings') . '
                 (setting_key, setting_value, setting_type, tab_group, display_order, placeholder, options)
                 VALUES (:key, :value, :type, :tab_group, :display_order, :placeholder, :options)'
            );
            $stmt->execute([
                ':key' => $key,
                ':value' => $value,
                ':type' => $type,
                ':tab_group' => $tabGroup,
                ':display_order' => $displayOrder,
                ':placeholder' => $placeholder,
                ':options' => $options ? json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            ]);
        }
    }

    /**
     * Приводит строковое значение из БД к правильному PHP типу.
     */
    private function castValue(?string $value, string $type): mixed
    {
        if ($value === null || $value === '') {
            return match ($type) {
                SiteSettingsDTO::TYPE_CHECKBOX => false,
                SiteSettingsDTO::TYPE_NUMBER => 0,
                SiteSettingsDTO::TYPE_JSON => [],
                default => '',
            };
        }

        return match ($type) {
            SiteSettingsDTO::TYPE_CHECKBOX => (bool) $value,
            SiteSettingsDTO::TYPE_NUMBER => (int) $value,
            SiteSettingsDTO::TYPE_JSON => json_decode($value, true) ?? [],
            default => $value,
        };
    }

    /**
     * Проверяет, является ли ключ системным.
     *
     * @param string $key
     * @return bool
     */
    public function isSystemKey(string $key): bool
    {
        return in_array($key, SiteSettingsDTO::getSystemKeys(), true);
    }

    /**
     * Возвращает список системных ключей.
     *
     * @return string[]
     */
    public function getSystemKeys(): array
    {
        return SiteSettingsDTO::getSystemKeys();
    }
}
