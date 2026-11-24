<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * ArticleDTO — объект переноса данных для статьи.
 * Инкапсулирует валидацию и преобразование из/в массив/БД.
 * Использует respect/validation (composer require respect/validation).
 */
final class ArticleDTO extends AbstractDTO
{
    // === Поля ===

    public string $id = '';
    public string $slug = '';
    public string $title = '';
    public string $description = '';
    public string $category = '';      // slug категории
    public int $category_id = 0;       // ID категории (для БД)
    public string $content = '';
    public string $author = '';
    public string $date = '';          // Y-m-d
    public bool $active = false;
    public array $tags = [];
    public string $cover = '';
    public string $icon = 'bi-file-text';
    public int $views = 0;
    public ?string $published_at = null;

    /**
     * Создаёт DTO из массива запроса (JSON / POST).
     *
     * @param array $data Данные из $request->json() или $_POST
     * @param string|null $id ID для обновления (генерируется для новой)
     * @return static
     */
    public static function fromRequest(array $data, ?string $id = null): static
    {
        $dto = new self();
        $dto->id = $id ?? bin2hex(random_bytes(16)); // 32 hex chars
        $dto->slug = trim($data['slug'] ?? '');
        $dto->title = trim($data['title'] ?? '');
        $dto->description = trim($data['description'] ?? '');
        $dto->category = trim($data['category'] ?? '');
        $dto->category_id = (int)($data['category_id'] ?? 0);
        $dto->content = $data['content'] ?? ''; // HTML, не трим — сохраняем форматирование
        $dto->author = trim($data['author'] ?? '');
        $dto->date = trim($data['date'] ?? date('Y-m-d'));
        $dto->active = !empty($data['active']);
        $dto->tags = is_array($data['tags'] ?? null) ? $data['tags'] : [];
        $dto->cover = trim($data['cover'] ?? '');
        $dto->icon = trim($data['icon'] ?? 'bi-file-text');
        $dto->views = (int)($data['views'] ?? 0);
        $dto->published_at = $data['published_at'] ?? null;

        return $dto;
    }

    /**
     * Создаёт DTO из строки БД (результат SELECT).
     *
     * @param array $row Ассоциативный массив из БД
     * @return static
     */
    public static function fromDatabase(array $row): static
    {
        $dto = new self();
        $dto->id = $row['id'] ?? '';
        $dto->slug = $row['slug'] ?? '';
        $dto->title = $row['title'] ?? '';
        $dto->description = $row['description'] ?? '';
        $dto->category = $row['category_slug'] ?? $row['category'] ?? '';
        $dto->category_id = (int)($row['category_id'] ?? 0);
        $dto->content = $row['content'] ?? '';
        $dto->author = $row['author'] ?? '';
        $dto->date = $row['date'] ?? ($row['published_at'] ? date('Y-m-d', strtotime($row['published_at'])) : '');
        $dto->active = (bool)($row['is_published'] ?? $row['active'] ?? false);
        $dto->tags = !empty($row['tags']) ? json_decode($row['tags'], true) : [];
        $dto->cover = $row['cover'] ?? '';
        $dto->icon = $row['icon'] ?? 'bi-file-text';
        $dto->views = (int)($row['views'] ?? 0);
        $dto->published_at = $row['published_at'] ?? null;

        return $dto;
    }

    /**
     * Валидирует DTO, заполняет $this->errors.
     *
     * @return bool true если валидно
     */
    public function validate(): bool
    {
        $this->clearErrors();

        // slug: обязателен, до 180 chars, только валидные символы
        if (!$this->slug) {
            $this->addError('slug', 'Slug обязателен');
        } elseif (mb_strlen($this->slug) > 180) {
            $this->addError('slug', 'Slug не длиннее 180 символов');
        } elseif (!preg_match('/^[a-z0-9\-]+$/i', $this->slug)) {
            $this->addError('slug', 'Slug только латиница, цифры, дефис');
        }

        // title: обязателен
        if (!$this->title) {
            $this->addError('title', 'Заголовок обязателен');
        } elseif (mb_strlen($this->title) > 255) {
            $this->addError('title', 'Заголовок не длиннее 255 символов');
        }

        // description: обязателен
        if (!$this->description) {
            $this->addError('description', 'Описание обязательно');
        } elseif (mb_strlen($this->description) > 500) {
            $this->addError('description', 'Описание не длиннее 500 символов');
        }

        // category: обязателен (slug категории)
        if (!$this->category && !$this->category_id) {
            $this->addError('category', 'Выберите категорию');
        }

        // content: обязателен
        if (!$this->content) {
            $this->addError('content', 'Контент обязателен');
        }

        // author: обязателен
        if (!$this->author) {
            $this->addError('author', 'Автор обязателен');
        }

        // date: валидная дата Y-m-d
        if ($this->date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->date)) {
            $this->addError('date', 'Дата в формате Y-m-d');
        }

        // icon: дефолт если пустой
        if (!$this->icon) {
            $this->icon = 'bi-file-text';
        }

        // tags: массив строк
        if (!is_array($this->tags)) {
            $this->tags = [];
        }
        $this->tags = array_filter(array_map('trim', $this->tags));
        $this->tags = array_slice($this->tags, 0, 20); // макс 20 тегов

        return empty($this->errors);
    }

    /**
     * Преобразует в массив для сохранения в БД.
     * Ключи совпадают с колонками таблицы va_articles.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->category_id ?: null,
            'content' => $this->content,
            'author' => $this->author,
            'date' => $this->date,
            'is_published' => $this->active ? 1 : 0,
            'tags' => json_encode($this->tags, JSON_UNESCAPED_UNICODE),
            'cover' => $this->cover,
            'icon' => $this->icon,
            'views' => $this->views,
            'published_at' => $this->published_at,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Преобразует в массив для JSON API ответа.
     *
     * @return array
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'category_id' => $this->category_id,
            'content' => $this->content,
            'author' => $this->author,
            'date' => $this->date,
            'active' => $this->active,
            'tags' => $this->tags,
            'cover' => $this->cover,
            'icon' => $this->icon,
            'views' => $this->views,
            'published_at' => $this->published_at,
        ];
    }
}
