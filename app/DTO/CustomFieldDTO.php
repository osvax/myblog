<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * CustomFieldDTO — объект переноса данных для пользовательских полей.
 *
 * === СТРУКТУРА ТАБЛИЦЫ va_custom_fields ===
 * - id (INT, PK, AI)
 * - field_key (VARCHAR 100, UNIQUE) — уникальный ключ поля (например: banner_title, phone)
 * - label (VARCHAR 200) — название для админки
 * - field_type (VARCHAR 32) — тип: text, textarea, number, url, image
 * - field_value (TEXT) — значение поля
 * - field_group (VARCHAR 100) — группа: general, header, footer, etc.
 * - is_active (TINYINT 1) — активность
 * - display_order (INT) — порядок отображения
 * - created_at (TIMESTAMP)
 * - updated_at (TIMESTAMP)
 */
final class CustomFieldDTO
{
    /** @var int|null ID записи (NULL для новой) */
    public ?int $id = null;

    /** @var string Ключ поля */
    public string $fieldKey = '';

    /** @var string Название для админки */
    public string $label = '';

    /** @var string Тип поля: text, textarea, number, url, image */
    public string $fieldType = 'text';

    /** @var string Значение поля */
    public string $fieldValue = '';

    /** @var string Группа поля */
    public string $fieldGroup = 'general';

    /** @var int Активность (0/1) */
    public int $isActive = 1;

    /** @var int Порядок отображения */
    public int $displayOrder = 0;

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
        $dto->fieldKey = $row['field_key'] ?? '';
        $dto->label = $row['label'] ?? '';
        $dto->fieldType = $row['field_type'] ?? 'text';
        $dto->fieldValue = $row['field_value'] ?? '';
        $dto->fieldGroup = $row['field_group'] ?? 'general';
        $dto->isActive = isset($row['is_active']) ? (int) $row['is_active'] : 1;
        $dto->displayOrder = isset($row['display_order']) ? (int) $row['display_order'] : 0;
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
            'field_key' => $this->fieldKey,
            'label' => $this->label,
            'field_type' => $this->fieldType,
            'field_value' => $this->fieldValue,
            'field_group' => $this->fieldGroup,
            'is_active' => $this->isActive,
            'display_order' => $this->displayOrder,
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
            'field_key' => $this->fieldKey,
            'label' => $this->label,
            'field_type' => $this->fieldType,
            'field_value' => $this->fieldValue,
            'field_group' => $this->fieldGroup,
            'is_active' => $this->isActive,
            'display_order' => $this->displayOrder,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
