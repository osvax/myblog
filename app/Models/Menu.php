<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\QueryBuilder;
use App\DTO\MenuDTO;
use App\DTO\MenuItemDTO;
use PDO;

/**
 * Меню сайта: произвольное количество меню (главное, боковые,
 * для разделов), пункты с иконками, классами и подменю.
 *
 * === МИГРАЦИЯ ===
 * - Конструктор принимает PDO (для тестов — SQLite :memory:)
 * - QueryBuilder для SELECT/INSERT/UPDATE/DELETE
 * - Возвращает MenuDTO / MenuItemDTO вместо массивов
 * - Статические методы-обёртки для обратной совместимости
 */
final class Menu
{
    private PDO $db;
    private string $menusTable;
    private string $itemsTable;

    /**
     * @param PDO|null $db PDO соединение (по умолчанию Database::connection())
     */
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? \App\Core\Database::connection();
        $this->menusTable = db_table('menus');
        $this->itemsTable = db_table('menu_items');
    }

    // ===================== ОБРАТНАЯ СОВМЕСТИМОСТЬ (статические) =====================

    /** @return array<int, array<string, mixed>> Все меню с количеством пунктов (старый API) */
    public static function menus(): array
    {
        return array_map(fn (MenuDTO $dto) => $dto->toApiArray(), (new self())->menusNew());
    }

    /** @return array<int, array<string, mixed>> Меню по префиксу (старый API) */
    public static function menusByPrefix(string $prefix): array
    {
        return array_map(fn (MenuDTO $dto) => $dto->toApiArray(), (new self())->menusByPrefixNew($prefix));
    }

    /** @return array<string, mixed>|null Меню по имени (старый API) */
    public static function menuByName(string $name): ?array
    {
        return (new self())->menuByNameNew($name)?->toApiArray();
    }

    /** @return array<string, mixed>|null Меню по ID (старый API) */
    public static function menuById(int $id): ?array
    {
        return (new self())->menuByIdNew($id)?->toApiArray();
    }

    /** @return array<string, mixed>|null Синхронизация меню рубрик (старый API) */
    public static function syncRubricMenu(string $name = 'main'): ?array
    {
        return (new self())->syncRubricMenuNew($name)?->toApiArray();
    }

    /** @return array<int, array<string, mixed>> Пункты меню плоским списком (старый API) */
    public static function items(int $menuId, bool $activeOnly = false): array
    {
        return array_map(fn (MenuItemDTO $dto) => $dto->toApiArray(), (new self())->itemsNew($menuId, $activeOnly));
    }

    /** @return array<string, mixed> Дерево меню (старый API) */
    public static function tree(string $menuName): array
    {
        return (new self())->treeNew($menuName);
    }

    /** @return array<string, mixed>|null Пункт по ID (старый API) */
    public static function itemById(int $id): ?array
    {
        return (new self())->itemByIdNew($id)?->toApiArray();
    }

    /** @return array<string, mixed> Сохранить пункт (старый API) */
    public static function saveItem(
        int $id,
        int $menuId,
        ?int $parentId,
        string $title,
        string $url,
        string $icon,
        string $cssClass,
        int $isActive
    ): array {
        $dto = new MenuItemDTO();
        $dto->id = $id > 0 ? $id : null;
        $dto->menuId = $menuId;
        $dto->parentId = $parentId;
        $dto->title = $title;
        $dto->url = $url;
        $dto->icon = $icon;
        $dto->cssClass = $cssClass;
        $dto->isActive = $isActive === 1;
        return (new self())->saveItemNew($dto);
    }

    /** @return array<string, mixed> Переупорядочить пункты (старый API) */
    public static function reorderItems(int $menuId, ?int $parentId, array $itemIds): array
    {
        return (new self())->reorderItemsNew($menuId, $parentId, $itemIds);
    }

    /** @return array<string, mixed> Переместить пункт вверх/вниз (старый API) */
    public static function moveItem(int $id, string $direction): array
    {
        return (new self())->moveItemNew($id, $direction);
    }

    /** @return array<string, mixed> Переключить активность (старый API) */
    public static function toggleActive(int $id): array
    {
        return (new self())->toggleActiveNew($id);
    }

    /** @return array<string, mixed> Удалить пункт (старый API) */
    public static function deleteItem(int $id): array
    {
        return (new self())->deleteItemNew($id);
    }

    /** @return array<string, mixed> Создать меню (старый API) */
    public static function createMenu(string $name, string $title, string $cssClass = ''): array
    {
        $dto = new MenuDTO();
        $dto->menuName = $name;
        $dto->title = $title;
        $dto->cssClass = $cssClass;
        return (new self())->createMenuNew($dto);
    }

    /** @return array<string, mixed> Удалить меню (старый API) */
    public static function deleteMenu(string $name): array
    {
        return (new self())->deleteMenuNew($name);
    }

    // ===================== НОВЫЙ API (DTO-based) =====================

    /** @return MenuDTO[] Все меню с количеством корневых пунктов */
    public function menusNew(): array
    {
        return $this->menusByPrefixNew('');
    }

    /** @return MenuDTO[] Меню по префиксу (исключая rubric_* или включая) */
    public function menusByPrefixNew(string $prefix): array
    {
        $where = $prefix === '' ? 'LEFT(m.menu_name, 7) != \'rubric_\'' : 'LEFT(m.menu_name, 7) = ' . $this->db->quote(rtrim($prefix, '_') . '_');

        $sql = <<<SQL
            SELECT m.*, COALESCE(i.items_count, 0) AS items_count
            FROM {$this->menusTable} m
            LEFT JOIN (
                SELECT menu_id, COUNT(*) AS items_count
                FROM {$this->itemsTable}
                WHERE parent_id IS NULL
                GROUP BY menu_id
            ) i ON i.menu_id = m.id
            WHERE {$where}
            ORDER BY m.id ASC
        SQL;

        $rows = $this->db->query($sql)->fetchAll();
        return array_map([MenuDTO::class, 'fromDatabase'], $rows);
    }

    /** @return MenuDTO|null Меню по имени */
    public function menuByNameNew(string $name): ?MenuDTO
    {
        $row = (new QueryBuilder($this->db, $this->menusTable))
            ->select(['*'])
            ->where('menu_name', '=', $name)
            ->first();

        return $row ? MenuDTO::fromDatabase($row) : null;
    }

    /** @return MenuDTO|null Меню по ID */
    public function menuByIdNew(int $id): ?MenuDTO
    {
        $row = (new QueryBuilder($this->db, $this->menusTable))
            ->select(['*'])
            ->where('id', '=', $id)
            ->first();

        return $row ? MenuDTO::fromDatabase($row) : null;
    }

    /** @return MenuDTO|null Синхронизация меню рубрик */
    public function syncRubricMenuNew(string $name = 'main'): ?MenuDTO
    {
        $menuName = 'rubric_' . $name;
        $menu = $this->menuByNameNew($menuName);

        if ($menu === null) {
            $result = $this->createMenuNew((new MenuDTO())->set($menuName, ($name === 'main' ? 'Рубрики блога' : $name)));
            if (!$result['success']) {
                return null;
            }
            $menu = $this->menuByNameNew($menuName);
            if ($menu === null) {
                return null;
            }
        }

        // Получаем категории
        $categories = (new QueryBuilder($this->db, 'categories'))
            ->select(['id', 'name', 'slug'])
            ->orderBy('id', 'ASC')
            ->get();

        // Существующие URL
        $existingUrls = (new QueryBuilder($this->db, $this->itemsTable))
            ->select(['url'])
            ->where('menu_id', '=', $menu->id)
            ->get();
        $existingUrlSet = array_fill_keys(array_column($existingUrls, 'url'), true);

        // Максимальный order
        $maxOrderRow = (new QueryBuilder($this->db, $this->itemsTable))
            ->select(['COALESCE(MAX(display_order), 0) AS max_order'])
            ->where('menu_id', '=', $menu->id)
            ->first();
        $order = (int)($maxOrderRow['max_order'] ?? 0);

        $insertStmt = $this->db->prepare(
            "INSERT INTO {$this->itemsTable} (menu_id, parent_id, title, url, icon, css_class, display_order, is_active)
             VALUES (:menu_id, NULL, :title, :url, :icon, '', :display_order, 1)"
        );

        foreach ($categories as $category) {
            $url = '/category/' . rawurlencode((string)$category['slug']) . '.html';
            if (isset($existingUrlSet[$url])) {
                continue;
            }
            $insertStmt->execute([
                'menu_id' => $menu->id,
                'title' => $category['name'],
                'url' => $url,
                'icon' => 'bi-tag',
                'display_order' => ++$order,
            ]);
            $existingUrlSet[$url] = true;
        }

        return $menu;
    }

    /** @return MenuItemDTO[] Пункты меню (плоский список в порядке отображения) */
    public function itemsNew(int $menuId, bool $activeOnly = false): array
    {
        $qb = (new QueryBuilder($this->db, $this->itemsTable))
            ->select(['*'])
            ->where('menu_id', '=', $menuId)
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC');

        if ($activeOnly) {
            $qb->where('is_active', '=', 1);
        }

        $rows = $qb->get();
        return array_map([MenuItemDTO::class, 'fromDatabase'], $rows);
    }

    /** @return array<string, mixed> Дерево активных пунктов: до двух уровней */
    public function treeNew(string $menuName): array
    {
        $menu = $this->menuByNameNew($menuName);
        if ($menu === null) {
            return ['menu' => null, 'roots' => [], 'children' => []];
        }

        $items = $this->itemsNew($menu->id, true);

        // Группируем по parent_id
        $roots = [];
        $childrenMap = [];
        foreach ($items as $item) {
            if ($item->parentId === null) {
                $roots[] = $item;
            } else {
                $childrenMap[$item->parentId][] = $item;
            }
        }

        // Прикрепляем детей к родителям
        foreach ($roots as $root) {
            if (isset($childrenMap[$root->id])) {
                $root->setChildren($childrenMap[$root->id]);
            }
        }

        return [
            'menu' => $menu->toApiArray(),
            'roots' => array_map(fn (MenuItemDTO $dto) => $dto->toApiArray(), $roots),
            'children' => $childrenMap, // для совместимости со старым API
        ];
    }

    /** @return MenuItemDTO|null Пункт по ID */
    public function itemByIdNew(int $id): ?MenuItemDTO
    {
        $row = (new QueryBuilder($this->db, $this->itemsTable))
            ->select(['*'])
            ->where('id', '=', $id)
            ->first();

        return $row ? MenuItemDTO::fromDatabase($row) : null;
    }

    /** @return array<string, mixed> Создать или обновить пункт из DTO */
    public function saveItemNew(MenuItemDTO $dto): array
    {
        // Валидация
        $isNew = $dto->id === null;
        if ($isNew) {
            if (!$dto->validate()) {
                return ['success' => false, 'error' => implode('; ', $dto->getErrors())];
            }
        } else {
            if (!$dto->validateUpdate()) {
                return ['success' => false, 'error' => implode('; ', $dto->getErrors())];
            }
        }

        // Проверка parent_id
        if ($dto->parentId !== null) {
            $parent = $this->itemByIdNew($dto->parentId);
            if (!$parent || $parent->menuId !== $dto->menuId || $parent->parentId !== null) {
                return ['success' => false, 'error' => 'Родительский пункт не найден'];
            }
        }

        try {
            if ($isNew) {
                // Авто-order
                $maxOrderRow = (new QueryBuilder($this->db, $this->itemsTable))
                    ->select(['COALESCE(MAX(display_order), 0) + 1 AS max_order'])
                    ->where('menu_id', '=', $dto->menuId)
                    ->first();
                $dto->displayOrder = (int)($maxOrderRow['max_order'] ?? 1);

                $data = $dto->toArray();
                unset($data['id']);

                $columns = implode(',', array_keys($data));
                $placeholders = ':' . implode(',:', array_keys($data));
                $sql = "INSERT INTO {$this->itemsTable} ($columns) VALUES ($placeholders)";

                $stmt = $this->db->prepare($sql);
                $stmt->execute($data);

                $dto->id = (int)$this->db->lastInsertId();

                return ['success' => true, 'id' => $dto->id];
            } else {
                $data = [
                    'menu_id' => $dto->menuId,
                    'parent_id' => $dto->parentId,
                    'title' => $dto->title,
                    'url' => $dto->url,
                    'icon' => $dto->icon,
                    'css_class' => $dto->cssClass,
                    'is_active' => $dto->isActive ? 1 : 0,
                    'id' => $dto->id,
                ];
                $stmt = $this->db->prepare(
                    "UPDATE {$this->itemsTable} SET menu_id = :menu_id, parent_id = :parent_id, title = :title, url = :url,
                            icon = :icon, css_class = :css_class, is_active = :is_active
                     WHERE id = :id"
                );
                $stmt->execute($data);

                return ['success' => true, 'id' => $dto->id];
            }
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** @return array<string, mixed> Сохранить порядок пунктов одного уровня */
    public function reorderItemsNew(int $menuId, ?int $parentId, array $itemIds): array
    {
        // Проверяем что все пункты принадлежат этому меню и уровню
        $qb = (new QueryBuilder($this->db, $this->itemsTable))
            ->select(['id'])
            ->where('menu_id', '=', $menuId);

        if ($parentId === null) {
            $qb->where('parent_id', 'IS', null);
        } else {
            $qb->where('parent_id', '=', $parentId);
        }

        $allowed = array_column($qb->get(), 'id');
        $itemIds = array_values(array_unique(array_map('intval', $itemIds)));
        sort($allowed);
        $sortedIds = $itemIds;
        sort($sortedIds);

        if ($allowed !== $sortedIds) {
            return ['success' => false, 'error' => 'Некорректный порядок пунктов'];
        }

        try {
            $this->db->beginTransaction();

            $update = $this->db->prepare("UPDATE {$this->itemsTable} SET display_order = :display_order WHERE id = :id AND menu_id = :menu_id");
            foreach ($itemIds as $order => $itemId) {
                $update->execute(['display_order' => $order + 1, 'id' => $itemId, 'menu_id' => $menuId]);
            }

            $this->db->commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /** @return array<string, mixed> Переместить пункт вверх/вниз среди соседей */
    public function moveItemNew(int $id, string $direction): array
    {
        $item = $this->itemByIdNew($id);
        if ($item === null) {
            return ['success' => false, 'error' => 'Пункт не найден'];
        }

        $qb = (new QueryBuilder($this->db, $this->itemsTable))
            ->select(['id', 'display_order'])
            ->where('menu_id', '=', $item->menuId)
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC');

        if ($item->parentId === null) {
            $qb->where('parent_id', 'IS', null);
        } else {
            $qb->where('parent_id', '=', $item->parentId);
        }

        $siblings = $qb->get();
        $index = null;
        foreach ($siblings as $position => $sibling) {
            if ((int)$sibling['id'] === $id) {
                $index = $position;
                break;
            }
        }

        if ($index === null) {
            return ['success' => false, 'error' => 'Пункт не найден'];
        }

        $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;
        if ($targetIndex < 0 || $targetIndex >= count($siblings)) {
            return ['success' => true];
        }

        try {
            $update = $this->db->prepare("UPDATE {$this->itemsTable} SET display_order = :order WHERE id = :id");
            $update->execute(['order' => (int)$siblings[$targetIndex]['display_order'], 'id' => $id]);
            $update->execute(['order' => (int)$siblings[$index]['display_order'], 'id' => (int)$siblings[$targetIndex]['id']]);
            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** @return array<string, mixed> Переключить активность пункта */
    public function toggleActiveNew(int $id): array
    {
        $stmt = $this->db->prepare("UPDATE {$this->itemsTable} SET is_active = 1 - is_active WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return ['success' => $stmt->rowCount() > 0];
    }

    /** @return array<string, mixed> Удалить пункт */
    public function deleteItemNew(int $id): array
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->itemsTable} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return ['success' => $stmt->rowCount() > 0];
    }

    /** @return array<string, mixed> Создать меню из DTO */
    public function createMenuNew(MenuDTO $dto): array
    {
        if (!$dto->validate()) {
            return ['success' => false, 'error' => implode('; ', $dto->getErrors())];
        }

        // Проверка уникальности
        if ($this->menuByNameNew($dto->menuName) !== null) {
            return ['success' => false, 'error' => 'Меню с таким идентификатором уже существует'];
        }

        try {
            $data = $dto->toArray();
            unset($data['id']);

            $columns = implode(',', array_keys($data));
            $placeholders = ':' . implode(',:', array_keys($data));
            $sql = "INSERT INTO {$this->menusTable} ($columns) VALUES ($placeholders)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);

            $dto->id = (int)$this->db->lastInsertId();

            return ['success' => true, 'id' => $dto->id];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** @return array<string, mixed> Удалить меню */
    public function deleteMenuNew(string $name): array
    {
        $menu = $this->menuByNameNew($name);
        if ($menu === null || $menu->menuName === 'main') {
            return ['success' => false, 'error' => 'Главное меню удалить нельзя'];
        }

        // Сначала удаляем пункты
        $stmt = $this->db->prepare("DELETE FROM {$this->itemsTable} WHERE menu_id = :id");
        $stmt->execute(['id' => $menu->id]);

        // Потом меню
        $stmt = $this->db->prepare("DELETE FROM {$this->menusTable} WHERE id = :id");
        $stmt->execute(['id' => $menu->id]);

        return ['success' => $stmt->rowCount() > 0];
    }
}
