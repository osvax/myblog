<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * MenuItemDTO — объект переноса данных для пункта меню.
 *
 * === ПОТОК ДАННЫХ (Data Flow) ===
 *
 * 1. ВХОД (Request → DTO):
 *    - POST /api/admin/menus/{menuId}/items (создание пункта)
 *      Тело: { "parent_id": null, "title": "Главная", "url": "/", "icon": "bi-house", "css_class": "", "is_active": true }
 *    - PUT /api/admin/menus/{menuId}/items/{id} (обновление пункта)
 *      Тело: { "title": "Главная страница", "url": "/", "is_active": false }
 *    - Контроллер: MenuItemDTO::fromRequest($request->json(), $id?)
 *
 * 2. ВАЛИДАЦИЯ (DTO → Errors):
 *    - $dto->validate() — для создания: title, url, menu_id (через контекст)
 *    - $dto->validateUpdate() — для обновления: title, url
 *    - Ошибки → $dto->getErrors()
 *
 * 3. СОХРАНЕНИЕ (DTO → Database):
 *    - Repository: saveItemFromDTO(MenuItemDTO $dto)
 *    - Репозиторий проверяет parent_id (тот же меню, корневой пункт)
 *
 * 4. ЧТЕНИЕ (Database → DTO):
 *    - Repository: items(), itemById(), tree()
 *    - MenuItemDTO::fromDatabase($row) → DTO
 *
 * 5. ВЫХОД (DTO → JSON Response):
 *    - DTO::toApiArray() — для списка/деталей пункта
 *
 * === СТРУКТУРА ТАБЛИЦЫ va_menu_items ===
 * - id (INT, PK, AI)
 * - menu_id (INT, FK → va_menus.id)
 * - parent_id (INT, NULL, FK → va_menu_items.id) — NULL для корневых, id родителя для подменю
 * - title (VARCHAR 160) — название пункта
 * - url (VARCHAR 500) — ссылка
 * - icon (VARCHAR 64) — иконка (Bootstrap Icons: bi-house, bi-tag, ...)
 * - css_class (VARCHAR 100) — дополнительный CSS класс
 * - display_order (INT) — порядок сортировки
 * - is_active (TINYINT) — 1 активно, 0 скрыто
 * - created_at, updated_at (TIMESTAMP)
 */
final class MenuItemDTO
{
    /** @var int|null ID пункта (NULL для нового) */
    public ?int $id = null;

    /** @var int ID меню (обязателен) */
    public int $menuId = 0;

    /** @var int|null ID родительского пункта (NULL = корневой) */
    public ?int $parentId = null;

    /** @var string Название пункта (обязательно, 2..160) */
    public string $title = '';

    /** @var string URL (обязательно) */
    public string $url = '';

    /** @var string Иконка (Bootstrap Icons) */
    public string $icon = '';

    /** @var string CSS класс */
    public string $cssClass = '';

    /** @var int Порядок отображения */
    public int $displayOrder = 0;

    /** @var bool Активен */
    public bool $isActive = true;

    /** @var string|null Дата создания */
    public ?string $createdAt = null;

    /** @var string|null Дата обновления */
    public ?string $updatedAt = null;

    // === Связанные данные ===

    /** @var MenuItemDTO[] Дочерние пункты (подменю) */
    public array $children = [];

    // === Валидация ===

    /** @var array<string, string> Ошибки валидации ['field' => 'message'] */
    private array $errors = [];

    /**
     * Создаёт DTO из строки БД.
     *
     * @param array<string, mixed> $row
     */
    public static function fromDatabase(array $row): self
    {
        $dto = new self();
        $dto->id = (int) ($row['id'] ?? 0);
        $dto->menuId = (int) ($row['menu_id'] ?? 0);
        $dto->parentId = $row['parent_id'] !== null ? (int) $row['parent_id'] : null;
        $dto->title = (string) ($row['title'] ?? '');
        $dto->url = (string) ($row['url'] ?? '');
        $dto->icon = (string) ($row['icon'] ?? '');
        $dto->cssClass = (string) ($row['css_class'] ?? '');
        $dto->displayOrder = (int) ($row['display_order'] ?? 0);
        $dto->isActive = (bool) ($row['is_active'] ?? true);
        $dto->createdAt = $row['created_at'] ?? null;
        $dto->updatedAt = $row['updated_at'] ?? null;

        return $dto;
    }

    /**
     * Создаёт DTO из запроса (JSON).
     *
     * @param array<string, mixed> $data
     * @param int|null $id ID пункта при обновлении
     */
    public static function fromRequest(array $data, ?int $id = null): self
    {
        $dto = new self();
        $dto->id = $id;
        $dto->menuId = (int) ($data['menu_id'] ?? $data['menuId'] ?? 0);
        $dto->parentId = isset($data['parent_id']) && $data['parent_id'] !== null && $data['parent_id'] !== ''
            ? (int) $data['parent_id']
            : null;
        $dto->title = trim((string) ($data['title'] ?? ''));
        $dto->url = trim((string) ($data['url'] ?? ''));
        $dto->icon = trim((string) ($data['icon'] ?? ''));
        $dto->cssClass = trim((string) ($data['css_class'] ?? $data['cssClass'] ?? ''));
        $dto->displayOrder = (int) ($data['display_order'] ?? $data['displayOrder'] ?? 0);
        $dto->isActive = (bool) ($data['is_active'] ?? $data['isActive'] ?? true);

        return $dto;
    }

    /** Валидация для создания пункта. */
    public function validate(): bool
    {
        $this->errors = [];

        if ($this->menuId <= 0) {
            $this->errors['menu_id'] = 'ID меню обязателен';
        }

        if ($this->title === '') {
            $this->errors['title'] = 'Название пункта обязательно';
        } elseif (mb_strlen($this->title) > 160) {
            $this->errors['title'] = 'Название пункта не более 160 символов';
        }

        if ($this->url === '') {
            $this->errors['url'] = 'URL обязателен';
        } elseif (mb_strlen($this->url) > 500) {
            $this->errors['url'] = 'URL не более 500 символов';
        }

        if ($this->icon !== '' && mb_strlen($this->icon) > 64) {
            $this->errors['icon'] = 'Иконка не более 64 символов';
        }

        if ($this->cssClass !== '' && mb_strlen($this->cssClass) > 100) {
            $this->errors['css_class'] = 'CSS класс не более 100 символов';
        }

        return empty($this->errors);
    }

    /** Валидация для обновления пункта. */
    public function validateUpdate(): bool
    {
        $this->errors = [];

        if ($this->title === '') {
            $this->errors['title'] = 'Название пункта обязательно';
        } elseif (mb_strlen($this->title) > 160) {
            $this->errors['title'] = 'Название пункта не более 160 символов';
        }

        if ($this->url === '') {
            $this->errors['url'] = 'URL обязателен';
        } elseif (mb_strlen($this->url) > 500) {
            $this->errors['url'] = 'URL не более 500 символов';
        }

        if ($this->icon !== '' && mb_strlen($this->icon) > 64) {
            $this->errors['icon'] = 'Иконка не более 64 символов';
        }

        if ($this->cssClass !== '' && mb_strlen($this->cssClass) > 100) {
            $this->errors['css_class'] = 'CSS класс не более 100 символов';
        }

        return empty($this->errors);
    }

    /** Возвращает ошибки валидации. */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Преобразует в массив для сохранения в БД (va_menu_items).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'menu_id' => $this->menuId,
            'parent_id' => $this->parentId,
            'title' => $this->title,
            'url' => $this->url,
            'icon' => $this->icon,
            'css_class' => $this->cssClass,
            'display_order' => $this->displayOrder,
            'is_active' => $this->isActive ? 1 : 0,
        ];

        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        return $data;
    }

    /**
     * Массив для API-ответа.
     *
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $result = [
            'id' => $this->id,
            'menu_id' => $this->menuId,
            'parent_id' => $this->parentId,
            'title' => $this->title,
            'url' => $this->url,
            'icon' => $this->icon,
            'css_class' => $this->cssClass,
            'display_order' => $this->displayOrder,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];

        if (!empty($this->children)) {
            $result['children'] = array_map(fn (MenuItemDTO $child) => $child->toApiArray(), $this->children);
        }

        return $result;
    }

    /** Добавляет дочерний пункт (для построения дерева). */
    public function addChild(MenuItemDTO $child): void
    {
        $this->children[] = $child;
    }

    /** Устанавливает дочерние пункты. */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }
}
