<?php

namespace App\Models;

use App\Core\Database;

/**
 * Меню сайта: произвольное количество меню (главное, боковые,
 * для разделов), пункты с иконками, классами и подменю.
 */
class Menu
{
    /**
     * Все меню с количеством пунктов.
     */
    public function menus(): array
    {
        return $this->menusByPrefix('');
    }

    public function menusByPrefix(string $prefix): array
    {
        $db = Database::connection();
        $where = $prefix === '' ? ' WHERE LEFT(m.menu_name, 7) <> :rubric_prefix' : ' WHERE LEFT(m.menu_name, 7) = :prefix';
        $stmt = $db->prepare(
            'SELECT m.id, m.menu_name, m.title, m.css_class,
                    COUNT(i.id) AS items_count
             FROM ' . db_table('menus') . ' m LEFT JOIN ' . db_table('menu_items') . ' i ON i.menu_id = m.id AND i.parent_id IS NULL' . $where . '
             GROUP BY m.id ORDER BY m.id'
        );
        if ($prefix === '') {
            $stmt->execute(['rubric_prefix' => 'rubric_']);
        } else {
            $stmt->execute(['prefix' => rtrim($prefix, '_') . '_']);
        }
        return $stmt->fetchAll();
    }

    public function menuByName(string $name): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM ' . db_table('menus') . ' WHERE menu_name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function menuById(int $id): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM ' . db_table('menus') . ' WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Пункты меню (плоский список в порядке отображения).
     */
    public function items(int $menuId, bool $activeOnly = false): array
    {
        $db = Database::connection();
        $sql = 'SELECT * FROM ' . db_table('menu_items') . ' WHERE menu_id = :menu_id';
        if ($activeOnly) {
            $sql .= ' AND is_active = 1';
        }
        $stmt = $db->prepare($sql . ' ORDER BY display_order ASC, id ASC');
        $stmt->execute(['menu_id' => $menuId]);
        return $stmt->fetchAll();
    }

    /**
     * Дерево активных пунктов: до двух уровней (пункт → подменю).
     */
    public function tree(string $menuName): array
    {
        $menu = $this->menuByName($menuName);
        if (!$menu) {
            return [];
        }
        $roots = [];
        $children = [];
        foreach ($this->items((int) $menu['id'], true) as $item) {
            if ($item['parent_id'] === null) {
                $roots[] = $item;
            } else {
                $children[(int) $item['parent_id']][] = $item;
            }
        }
        return ['menu' => $menu, 'roots' => $roots, 'children' => $children];
    }

    public function itemById(int $id): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM ' . db_table('menu_items') . ' WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Создать или обновить пункт меню.
     */
    public function saveItem(
        int $id,
        int $menuId,
        ?int $parentId,
        string $title,
        string $url,
        string $icon,
        string $cssClass,
        int $isActive
    ): array {
        $db = Database::connection();

        // Родитель должен принадлежать тому же меню и быть корневым пунктом
        if ($parentId !== null) {
            $parent = $this->itemById($parentId);
            if (!$parent || (int) $parent['menu_id'] !== $menuId || $parent['parent_id'] !== null) {
                return ['success' => false, 'error' => 'Родительский пункт не найден'];
            }
        }

        if ($id <= 0) {
            $orderStmt = $db->prepare('SELECT COALESCE(MAX(display_order), 0) + 1 FROM ' . db_table('menu_items') . ' WHERE menu_id = :menu_id');
            $orderStmt->execute(['menu_id' => $menuId]);
            $order = (int) $orderStmt->fetchColumn();
            $stmt = $db->prepare(
                'INSERT INTO ' . db_table('menu_items') . ' (menu_id, parent_id, title, url, icon, css_class, display_order, is_active)
                 VALUES (:menu_id, :parent_id, :title, :url, :icon, :css_class, :display_order, :is_active)'
            );
            $stmt->execute([
                'menu_id' => $menuId,
                'parent_id' => $parentId,
                'title' => $title,
                'url' => $url,
                'icon' => $icon,
                'css_class' => $cssClass,
                'display_order' => $order,
                'is_active' => $isActive,
            ]);
            return ['success' => true, 'id' => (int) $db->lastInsertId()];
        }

        $stmt = $db->prepare(
            'UPDATE ' . db_table('menu_items') . ' SET menu_id = :menu_id, parent_id = :parent_id, title = :title, url = :url,
                    icon = :icon, css_class = :css_class, is_active = :is_active
             WHERE id = :id'
        );
        $stmt->execute([
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'title' => $title,
            'url' => $url,
            'icon' => $icon,
            'css_class' => $cssClass,
            'is_active' => $isActive,
            'id' => $id,
        ]);
        return ['success' => true, 'id' => $id];
    }

    /**
     * Сохранить порядок пунктов одного уровня меню.
     */
    public function reorderItems(int $menuId, ?int $parentId, array $itemIds): array
    {
        $db = Database::connection();
        $sql = 'SELECT id FROM ' . db_table('menu_items') . ' WHERE menu_id = :menu_id AND ' . ($parentId === null ? 'parent_id IS NULL' : 'parent_id = :parent_id');
        $stmt = $db->prepare($sql);
        $stmt->execute(array_filter(['menu_id' => $menuId, 'parent_id' => $parentId], static function ($value): bool {
            return $value !== null;
        }));
        $allowed = array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
        $itemIds = array_values(array_unique(array_map('intval', $itemIds)));
        sort($allowed);
        $sortedIds = $itemIds;
        sort($sortedIds);
        if ($allowed !== $sortedIds) {
            return ['success' => false, 'error' => 'Некорректный порядок пунктов'];
        }
        $db->beginTransaction();
        try {
            $update = $db->prepare('UPDATE ' . db_table('menu_items') . ' SET display_order = :display_order WHERE id = :id AND menu_id = :menu_id');
            foreach ($itemIds as $order => $itemId) {
                $update->execute(['display_order' => $order + 1, 'id' => $itemId, 'menu_id' => $menuId]);
            }
            $db->commit();
        } catch (\Throwable $error) {
            $db->rollBack();
            throw $error;
        }
        return ['success' => true];
    }

    /**
     * Переместить пункт вверх/вниз среди соседей того же уровня.
     */
    public function moveItem(int $id, string $direction): array
    {
        $item = $this->itemById($id);
        if (!$item) {
            return ['success' => false, 'error' => 'Пункт не найден'];
        }
        $db = Database::connection();
        $stmt = $db->prepare(
            'SELECT id, display_order FROM ' . db_table('menu_items') . '
             WHERE menu_id = :menu_id AND ' . ($item['parent_id'] === null ? 'parent_id IS NULL' : 'parent_id = :parent_id') . '
             ORDER BY display_order ASC, id ASC'
        );
        $stmt->execute(array_filter([
            'menu_id' => $item['menu_id'],
            'parent_id' => $item['parent_id'],
        ]));
        $siblings = $stmt->fetchAll();
        $index = null;
        foreach ($siblings as $position => $sibling) {
            if ((int) $sibling['id'] === $id) {
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
        $update = $db->prepare('UPDATE ' . db_table('menu_items') . ' SET display_order = :order WHERE id = :id');
        $update->execute(['order' => (int) $siblings[$targetIndex]['display_order'], 'id' => $id]);
        $update->execute(['order' => (int) $siblings[$index]['display_order'], 'id' => (int) $siblings[$targetIndex]['id']]);
        return ['success' => true];
    }

    public function toggleActive(int $id): array
    {
        $db = Database::connection();
        $stmt = $db->prepare('UPDATE ' . db_table('menu_items') . ' SET is_active = 1 - is_active WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return ['success' => $stmt->rowCount() > 0];
    }

    public function deleteItem(int $id): array
    {
        $db = Database::connection();
        $stmt = $db->prepare('DELETE FROM ' . db_table('menu_items') . ' WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return ['success' => $stmt->rowCount() > 0];
    }

    public function createMenu(string $name, string $title, string $cssClass = ''): array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT id FROM ' . db_table('menus') . ' WHERE menu_name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Меню с таким идентификатором уже существует'];
        }
        $stmt = $db->prepare('INSERT INTO ' . db_table('menus') . ' (menu_name, title, css_class) VALUES (:name, :title, :css_class)');
        $stmt->execute(['name' => $name, 'title' => $title, 'css_class' => $cssClass]);
        return ['success' => true, 'id' => (int) $db->lastInsertId()];
    }

    public function deleteMenu(string $name): array
    {
        $db = Database::connection();
        $menu = $this->menuByName($name);
        if (!$menu || $menu['menu_name'] === 'main') {
            return ['success' => false, 'error' => 'Главное меню удалить нельзя'];
        }
        $stmt = $db->prepare('DELETE FROM ' . db_table('menus') . ' WHERE id = :id');
        $stmt->execute(['id' => $menu['id']]);
        return ['success' => $stmt->rowCount() > 0];
    }
}
