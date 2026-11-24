<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * MenuDTO — объект переноса данных для меню (навигации).
 *
 * === ПОТОК ДАННЫХ (Data Flow) ===
 *
 * 1. ВХОД (Request → DTO):
 *    - POST /api/admin/menus (создание меню)
 *      Тело: { "menu_name": "footer", "title": "Подвал", "css_class": "footer-nav" }
 *    - PUT /api/admin/menus/{id} (обновление меню)
 *      Тело: { "title": "Подвал сайта", "css_class": "footer-nav" }
 *    - Контроллер: MenuDTO::fromRequest($request->json(), $id?)
 *
 * 2. ВАЛИДАЦИЯ (DTO → Errors):
 *    - $dto->validate() — для создания: menu_name, title
 *    - $dto->validateUpdate() — для обновления: title
 *    - Ошибки → $dto->getErrors()
 *
 * 3. СОХРАНЕНИЕ (DTO → Database):
 *    - Repository: saveMenuFromDTO(MenuDTO $dto)
 *
 * 4. ЧТЕНИЕ (Database → DTO):
 *    - Repository: all(), findById(), findByName()
 *    - MenuDTO::fromDatabase($row) → DTO
 *
 * 5. ВЫХОД (DTO → JSON Response):
 *    - DTO::toApiArray() — для списка/отдельного меню в админке
 *
 * === СТРУКТУРА ТАБЛИЦЫ va_menus ===
 * - id (INT, PK, AI)
 * - menu_name (VARCHAR 64, UNIQUE) — идентификатор меню: 'main', 'footer', 'rubric_main', 'rubric_blog', ...
 * - title (VARCHAR 160) — отображаемое название
 * - css_class (VARCHAR 100) — CSS класс для стилизации
 * - created_at, updated_at (TIMESTAMP)
 */
final class MenuDTO extends AbstractDTO
{
    /** @var int|null ID меню (NULL для нового) */
    public ?int $id = null;

    /** @var string Идентификатор меню (обязателен, уникален, латиница/цифры/_) */
    public string $menuName = '';

    /** @var string Отображаемое название (обязательно, 2..160) */
    public string $title = '';

    /** @var string CSS класс */
    public string $cssClass = '';

    /** @var string|null Дата создания */
    public ?string $createdAt = null;

    /** @var string|null Дата обновления */
    public ?string $updatedAt = null;

    /** @var int Количество корневых пунктов меню */
    public int $itemsCount = 0;

    /**
     * Создаёт DTO из строки БД.
     *
     * @param array<string, mixed> $row
     * @return static
     */
    public static function fromDatabase(array $row): static
    {
        $dto = new self();
        $dto->id = (int) ($row['id'] ?? 0);
        $dto->menuName = (string) ($row['menu_name'] ?? '');
        $dto->title = (string) ($row['title'] ?? '');
        $dto->cssClass = (string) ($row['css_class'] ?? '');
        $dto->createdAt = $row['created_at'] ?? null;
        $dto->updatedAt = $row['updated_at'] ?? null;
        $dto->itemsCount = (int) ($row['items_count'] ?? 0);

        return $dto;
    }

    /**
     * Создаёт DTO из запроса (JSON).
     *
     * @param array<string, mixed> $data
     * @param int|null $id ID меню при обновлении
     * @return static
     */
    public static function fromRequest(array $data, ?int $id = null): static
    {
        $dto = new self();
        $dto->id = $id;
        $dto->menuName = trim((string) ($data['menu_name'] ?? $data['menuName'] ?? ''));
        $dto->title = trim((string) ($data['title'] ?? ''));
        $dto->cssClass = trim((string) ($data['css_class'] ?? $data['cssClass'] ?? ''));

        return $dto;
    }

    /** Валидация для создания меню. */
    public function validate(): bool
    {
        $this->clearErrors();

        if ($this->menuName === '') {
            $this->addError('menu_name', 'Идентификатор меню обязателен');
        } elseif (!preg_match('/^[a-z0-9][a-z0-9_]{1,62}$/', $this->menuName)) {
            $this->addError('menu_name', 'Идентификатор: латинские буквы, цифры, подчёркивание (от 2 символов)');
        }

        if ($this->title === '') {
            $this->addError('title', 'Название меню обязательно');
        } elseif (mb_strlen($this->title) > 160) {
            $this->addError('title', 'Название меню не более 160 символов');
        }

        if ($this->cssClass !== '' && mb_strlen($this->cssClass) > 100) {
            $this->addError('css_class', 'CSS класс не более 100 символов');
        }

        return empty($this->errors);
    }

    /** Валидация для обновления меню (menu_name не меняется). */
    public function validateUpdate(): bool
    {
        $this->clearErrors();

        if ($this->title === '') {
            $this->addError('title', 'Название меню обязательно');
        } elseif (mb_strlen($this->title) > 160) {
            $this->addError('title', 'Название меню не более 160 символов');
        }

        if ($this->cssClass !== '' && mb_strlen($this->cssClass) > 100) {
            $this->addError('css_class', 'CSS класс не более 100 символов');
        }

        return empty($this->errors);
    }

    /**
     * Удобный сеттер для цепочечного вызова.
     *
     * @return $this
     */
    public function set(string $menuName, string $title, string $cssClass = ''): self
    {
        $this->menuName = $menuName;
        $this->title = $title;
        $this->cssClass = $cssClass;
        return $this;
    }

    /**
     * Преобразует в массив для сохранения в БД (va_menus).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'menu_name' => $this->menuName,
            'title' => $this->title,
            'css_class' => $this->cssClass,
        ];

        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        return $data;
    }

    /**
     * Массив для API-ответа (список/детали меню).
     *
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'menu_name' => $this->menuName,
            'title' => $this->title,
            'css_class' => $this->cssClass,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'items_count' => $this->itemsCount,
        ];
    }
}
