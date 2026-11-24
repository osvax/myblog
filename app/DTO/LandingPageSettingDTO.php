<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * LandingPageSettingDTO — объект переноса данных для настроек лендинга.
 *
 * === СТРУКТУРА ТАБЛИЦЫ va_landing_page_settings ===
 * - id (INT, PK, AI)
 * - section (VARCHAR 100) — название секции (например: Hero, About, Features)
 * - section_key (VARCHAR 100) — ключ секции для группировки (например: hero, about)
 * - field_name (VARCHAR 100) — имя поля (например: title, subtitle, button_text)
 * - field_value (TEXT) — значение поля
 * - field_type (VARCHAR 32) — тип поля: text, textarea, number, email, tel, url, image, video, audio, color, checkbox, select, rich_text
 * - field_group (VARCHAR 64) — группа поля: general, hero, about, features, etc.
 * - display_order (INT) — порядок отображения
 * - placeholder (VARCHAR 255) — подсказка в инпуте
 * - options (TEXT, NULL) — JSON-опции для select
 * - is_active (TINYINT 1) — активность
 * - created_at (TIMESTAMP)
 * - updated_at (TIMESTAMP)
 */
final class LandingPageSettingDTO
{
    /** @var int|null ID записи (NULL для новой) */
    public ?int $id = null;

    /** @var string Название секции */
    public string $section = '';

    /** @var string Ключ секции */
    public string $sectionKey = '';

    /** @var string Имя поля */
    public string $fieldName = '';

    /** @var string Значение поля */
    public string $fieldValue = '';

    /** @var string Тип поля */
    public string $fieldType = 'text';

    /** @var string Группа поля */
    public string $fieldGroup = 'general';

    /** @var int Порядок отображения */
    public int $displayOrder = 0;

    /** @var string Подсказка в инпуте */
    public string $placeholder = '';

    /** @var string|null JSON-опции для select */
    public ?string $options = null;

    /** @var int Активность (0/1) */
    public int $isActive = 1;

    /** @var string Время создания */
    public string $createdAt = '';

    /** @var string Время обновления */
    public string $updatedAt = '';

    /**
     * Создаёт DTO из строки БД.
     *
     * @param array<string, mixed> $row
     */
    public static function fromDatabase(array $row): self
    {
        $dto = new self();
        $dto->id = isset($row['id']) ? (int) $row['id'] : null;
        $dto->section = $row['section'] ?? '';
        $dto->sectionKey = $row['section_key'] ?? '';
        $dto->fieldName = $row['field_name'] ?? '';
        $dto->fieldValue = $row['field_value'] ?? '';
        $dto->fieldType = $row['field_type'] ?? 'text';
        $dto->fieldGroup = $row['field_group'] ?? 'general';
        $dto->displayOrder = isset($row['display_order']) ? (int) $row['display_order'] : 0;
        $dto->placeholder = $row['placeholder'] ?? '';
        $dto->options = $row['options'] ?? null;
        $dto->isActive = isset($row['is_active']) ? (int) $row['is_active'] : 1;
        $dto->createdAt = $row['created_at'] ?? '';
        $dto->updatedAt = $row['updated_at'] ?? '';

        return $dto;
    }

    /**
     * Массив для INSERT/UPDATE в БД.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'section' => $this->section,
            'section_key' => $this->sectionKey,
            'field_name' => $this->fieldName,
            'field_value' => $this->fieldValue,
            'field_type' => $this->fieldType,
            'field_group' => $this->fieldGroup,
            'display_order' => $this->displayOrder,
            'placeholder' => $this->placeholder,
            'options' => $this->options,
            'is_active' => $this->isActive,
        ];
    }

    /**
     * Массив для API ответа / JSON.
     *
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'section' => $this->section,
            'section_key' => $this->sectionKey,
            'field_name' => $this->fieldName,
            'field_value' => $this->fieldValue,
            'field_type' => $this->fieldType,
            'field_group' => $this->fieldGroup,
            'display_order' => $this->displayOrder,
            'placeholder' => $this->placeholder,
            'options' => $this->options,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
