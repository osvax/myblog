<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * LandingPageDTO — объект переноса данных для статических страниц (лендингов).
 *
 * === СТРУКТУРА ТАБЛИЦЫ va_landing_pages ===
 * - id (INT, PK, AI)
 * - slug (VARCHAR 100, UNIQUE) — идентификатор страницы (например: home, about)
 * - title (VARCHAR 254) — заголовок
 * - html_content (TEXT) — HTML тело с плейсхолдерами {{field:...}} / {{menu:...}} / {{asset:...}}
 * - is_active (TINYINT 1) — активность
 * - generated_at (TIMESTAMP, NULL) — время генерации из настроек
 * - created_at (TIMESTAMP)
 * - updated_at (TIMESTAMP)
 */
final class LandingPageDTO extends AbstractDTO
{
    /** @var int|null ID записи (NULL для новой) */
    public ?int $id = null;

    /** @var string Slug страницы */
    public string $slug = '';

    /** @var string Заголовок */
    public string $title = '';

    /** @var string HTML контент */
    public string $htmlContent = '';

    /** @var int Активность (0/1) */
    public int $isActive = 1;

    /** @var string|null Время генерации */
    public ?string $generatedAt = null;

    /** @var string Время создания */
    public string $createdAt = '';

    /** @var string Время обновления */
    public string $updatedAt = '';

    /**
     * Создаёт DTO из строки БД.
     *
     * @param array<string, mixed> $row
     * @return static
     */
    public static function fromDatabase(array $row): static
    {
        $dto = new self();
        $dto->id = isset($row['id']) ? (int) $row['id'] : null;
        $dto->slug = $row['slug'] ?? '';
        $dto->title = $row['title'] ?? '';
        $dto->htmlContent = $row['html_content'] ?? '';
        $dto->isActive = isset($row['is_active']) ? (int) $row['is_active'] : 1;
        $dto->generatedAt = $row['generated_at'] ?? null;
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
            'slug' => $this->slug,
            'title' => $this->title,
            'html_content' => $this->htmlContent,
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
            'slug' => $this->slug,
            'title' => $this->title,
            'html_content' => $this->htmlContent,
            'is_active' => $this->isActive,
            'generated_at' => $this->generatedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
