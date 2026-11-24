<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * CategoryDTO — объект переноса данных для рубрики (категории).
 *
 * === ПОТОК ДАННЫХ (Data Flow) ===
 *
 * 1. ВХОД (Request → DTO):
 *    - POST/PUT /api/admin/categories
 *    - Тело запроса (JSON): { "id"?, "name", "slug", "description" }
 *    - Контроллер вызывает: CategoryDTO::fromRequest($request->json(), $id?)
 *
 * 2. ВАЛИДАЦИЯ (DTO → Errors):
 *    - $dto->validate() проверяет правила
 *    - Ошибки накапливаются в $dto->errors
 *    - Контроллер возвращает 400 + errors если невалидно
 *
 * 3. СОХРАНЕНИЕ (DTO → Database):
 *    - Repository: saveCategoryFromDTO(CategoryDTO $dto)
 *    - DTO::toArray() → массив колонок для БД
 *    - UPSERT: INSERT ... ON DUPLICATE KEY UPDATE
 *
 * 4. ЧТЕНИЕ (Database → DTO):
 *    - Repository: categoryById(), categoryBySlug(), categories()
 *    - SELECT с JOIN для подсчёта статей (articles_count)
 *    - CategoryDTO::fromDatabase($row) → DTO с articles_count
 *
 * 5. ВЫХОД (DTO → JSON Response):
 *    - DTO::toApiArray() → массив для json()
 *    - Контроллер: return $this->json(['success' => true, 'category' => $dto->toApiArray()])
 *
 * === СТРУКТУРА ТАБЛИЦЫ va_categories ===
 * - id (INT, PK, AI)
 * - name (VARCHAR 255) — название рубрики
 * - slug (VARCHAR 140, UNIQUE) — URL-адрес (латиница, цифры, дефис)
 * - description (TEXT) — описание для SEO/страницы рубрики
 * - created_at, updated_at (TIMESTAMP) — автоматически
 *
 * === СВЯЗИ ===
 * - 1 категория → много статей (articles.category_id)
 * - Навигация (category_navigation) ссылается на категории
 */
final class CategoryDTO
{
    // === Основные поля (соответствуют колонкам va_categories) ===

    /** @var int|null ID рубрики (NULL для новой, заполняется после INSERT) */
    public ?int $id = null;

    /** @var string Название рубрики (обязательное, макс 255) */
    public string $name = '';

    /** @var string Slug для URL (обязательный, макс 140, паттерн ^[a-z0-9-]+$) */
    public string $slug = '';

    /** @var string Описание рубрики (опционально, для SEO/страницы рубрики) */
    public string $description = '';

    // === Вычисляемые / присоединённые поля ===

    /** @var int Количество статей в этой рубрике (из LEFT JOIN + COUNT) */
    public int $articles_count = 0;

    // === Ошибки валидации ===
    private array $errors = [];

    // === Константы для валидации ===
    public const SLUG_MAX_LENGTH = 140;
    public const NAME_MAX_LENGTH = 255;
    public const SLUG_PATTERN = '/^[a-z0-9\-]+$/i';

    /**
     * Создаёт DTO из массива запроса (JSON / POST).
     *
     * ИСПОЛЬЗОВАНИЕ В КОНТРОЛЛЕРЕ:
     * ```php
     * $jsonData = $request->json();  // Приходит из JS: {name, slug, description}
     * $dto = CategoryDTO::fromRequest($jsonData, $params['id'] ?? null);
     * // $params['id'] — из роута /api/admin/categories/{id} для PUT
     * ```
     *
     * @param array $data Данные из $request->json() или $_POST
     *                Ожидаемые ключи: name, slug, description, (id опционально)
     * @param int|null $id ID из URL параметра (для PUT), если есть
     * @return self Готовый DTO (невалидированный, нужно вызвать validate())
     */
    public static function fromRequest(array $data, ?int $id = null): self
    {
        $dto = new self();

        // ID: берём из параметра роута (приоритет) или из JSON тела
        // При POST (создание) — null, при PUT (обновление) — число
        $dto->id = $id ?? (isset($data['id']) && is_numeric($data['id']) ? (int)$data['id'] : null);

        // name: триммим, ожидаем непустую строку
        $dto->name = trim((string)($data['name'] ?? ''));

        // slug: триммим, приводим к нижнему регистру для единообразия
        // Важно: slug должен быть уникальным в БД (UNIQUE INDEX)
        $dto->slug = strtolower(trim((string)($data['slug'] ?? '')));

        // description: триммим, может быть пустым
        $dto->description = trim((string)($data['description'] ?? ''));

        return $dto;
    }

    /**
     * Создаёт DTO из строки БД (результат SELECT).
     *
     * ИСПОЛЬЗОВАНИЕ В РЕПОЗИТОРИИ:
     * ```php
     * $row = $this->db->prepare('SELECT ... FROM va_categories WHERE id = ?')->execute([$id])->fetch();
     * $dto = CategoryDTO::fromDatabase($row);
     * // $row содержит: id, name, slug, description, articles_count (из COUNT JOIN)
     * ```
     *
     * @param array $row Ассоциативный массив из БД (fetch_assoc)
     *                Ожидаемые ключи: id, name, slug, description, articles_count (опционально)
     * @return self
     */
    public static function fromDatabase(array $row): self
    {
        $dto = new self();
        $dto->id = isset($row['id']) ? (int)$row['id'] : null;
        $dto->name = $row['name'] ?? '';
        $dto->slug = $row['slug'] ?? '';
        $dto->description = $row['description'] ?? '';
        // articles_count приходит из LEFT JOIN + COUNT(a.id) в запросе categories()
        $dto->articles_count = isset($row['articles']) ? (int)$row['articles'] :
                              (isset($row['articles_count']) ? (int)$row['articles_count'] : 0);

        return $dto;
    }

    /**
     * Валидирует DTO, заполняет $this->errors.
     *
     * ПРАВИЛА ВАЛИДАЦИИ (соответствуют БД и UI):
     * - name: обязателен, 1..255 символов
     * - slug: обязателен, 1..140 символов, только латиница/цифры/дефис, уникален в БД
     * - description: опционально, любая длина (TEXT в БД)
     *
     * ВЫЗЫВАЕТСЯ:
     * - В контроллере ПЕРЕД сохранением: if (!$dto->validate()) return errors 400
     * - Можно вызвать вручную: $dto->validate(); $errors = $dto->getErrors();
     *
     * @return bool true если валидно (errors пуст)
     */
    public function validate(): bool
    {
        $this->errors = [];

        // --- name ---
        if ($this->name === '') {
            $this->errors['name'] = 'Название рубрики обязательно';
        } elseif (mb_strlen($this->name) > self::NAME_MAX_LENGTH) {
            $this->errors['name'] = 'Название не длиннее ' . self::NAME_MAX_LENGTH . ' символов';
        }

        // --- slug ---
        if ($this->slug === '') {
            $this->errors['slug'] = 'Адрес (slug) обязателен';
        } elseif (mb_strlen($this->slug) > self::SLUG_MAX_LENGTH) {
            $this->errors['slug'] = 'Адрес не длиннее ' . self::SLUG_MAX_LENGTH . ' символов';
        } elseif (!preg_match(self::SLUG_PATTERN, $this->slug)) {
            $this->errors['slug'] = 'Адрес: только латиница, цифры, дефис (например: my-category)';
        }
        // Примечание: уникальность slug проверяется в БД (UNIQUE INDEX),
        // здесь не проверяем — будет ошибка при INSERT/UPDATE

        // --- description ---
        // Опционально, валидации нет (TEXT в БД)

        return empty($this->errors);
    }

    /**
     * Возвращает ошибки валидации.
     *
     * ФОРМАТ ОТВЕТА (для JSON API):
     * [
     *   'name' => 'Название рубрики обязательно',
     *   'slug' => 'Адрес: только латиница, цифры, дефис'
     * ]
     *
     * ИСПОЛЬЗОВАНИЕ В КОНТРОЛЛЕРЕ:
     * ```php
     * return $this->json([
     *   'success' => false,
     *   'error' => 'Ошибки валидации',
     *   'errors' => $dto->getErrors()  // <-- сюда
     * ], 400);
     * ```
     *
     * @return array ['field' => 'message', ...]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Проверяет, прошла ли валидация (вызывает validate() если ещё нет).
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if (empty($this->errors)) {
            $this->validate();
        }
        return empty($this->errors);
    }

    /**
     * Преобразует в массив для сохранения в БД (таблица va_categories).
     *
     * КЛЮЧИ МАССИВА = КОЛОНКИ ТАБЛИЦЫ va_categories
     * Используется в Repository::saveCategoryFromDTO():
     * ```php
     * $data = $dto->toArray();
     * // $data = ['id' => 1, 'name' => 'Блог', 'slug' => 'blog', 'description' => '...']
     * // Затем строим UPSERT запрос с этими колонками
     * ```
     *
     * @return array ['column' => value, ...]
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
        ];
    }

    /**
     * Преобразует в массив для JSON API ответа.
     *
     * СТРУКТУРА ОТВЕТА:
     * [
     *   'id' => 1,
     *   'name' => 'Блог',
     *   'slug' => 'blog',
     *   'description' => 'Статьи и новости',
     *   'articles_count' => 42  // Для UI: показать кол-во статей в списке рубрик
     * ]
     *
     * ИСПОЛЬЗОВАНИЕ В КОНТРОЛЛЕРЕ:
     * ```php
     * return $this->json(['success' => true, 'category' => $dto->toApiArray()]);
     * ```
     *
     * @return array
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'articles_count' => $this->articles_count,
        ];
    }
}
