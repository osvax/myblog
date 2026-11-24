<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class LandingPage
{
    /**
     * Получить все страницы.
     *
     * @return array
     */
    public function getAll(): array
    {
        $db = Database::connection();
        $stmt = $db->query('SELECT * FROM ' . db_table('landing_pages') . ' ORDER BY slug ASC');
        return $stmt->fetchAll();
    }

    /**
     * Получить страницу по slug.
     *
     * @param string $slug
     * @return array|null
     */
    public function findBySlug(string $slug): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM ' . db_table('landing_pages') . ' WHERE slug = :slug LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Получить активную страницу.
     *
     * @param string $slug
     * @return array|null
     */
    public function findActive(string $slug): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM ' . db_table('landing_pages') . ' WHERE slug = :slug AND is_active = 1 LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Получить страницу по id.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM ' . db_table('landing_pages') . ' WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Создать или обновить страницу.
     *
     * HTML тела хранится как есть: страницы пишет авторизованный админ
     * (та же модель доверия, что и у раздела «Шаблоны»), а плейсхолдеры
     * {{field:...}} / {{menu:...}} / {{asset:...}} раскрываются при
     * рендере функцией render_static_page().
     *
     * @param string $slug
     * @param string $title
     * @param string $htmlContent
     * @param int $isActive
     * @param int $editId
     * @return array
     */
    public function save(
        string $slug,
        string $title,
        string $htmlContent = '',
        int $isActive = 1,
        int $editId = 0
    ): array {
        $db = Database::connection();

        if ($editId > 0) {
            $stmt = $db->prepare(
                'UPDATE ' . db_table('landing_pages') . '
                 SET title = :title, html_content = :html, is_active = :active, generated_at = NOW(), updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                ':title' => $title,
                ':html' => $htmlContent,
                ':active' => $isActive,
                ':id' => $editId,
            ]);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'error' => 'Страница не найдена'];
            }

            return ['success' => true, 'id' => $editId, 'action' => 'updated'];
        }

        $stmt = $db->prepare('SELECT id FROM ' . db_table('landing_pages') . ' WHERE slug = :slug LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $db->prepare(
                'UPDATE ' . db_table('landing_pages') . '
                 SET title = :title, html_content = :html, is_active = :active, generated_at = NOW(), updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                ':title' => $title,
                ':html' => $htmlContent,
                ':active' => $isActive,
                ':id' => (int) $existing['id'],
            ]);

            return ['success' => true, 'id' => (int) $existing['id'], 'action' => 'updated'];
        }

        $stmt = $db->prepare(
            'INSERT INTO ' . db_table('landing_pages') . ' (slug, title, html_content, is_active, generated_at, created_at, updated_at)
             VALUES (:slug, :title, :html, :active, NOW(), NOW(), NOW())'
        );
        $stmt->execute([
            ':slug' => $slug,
            ':title' => $title,
            ':html' => $htmlContent,
            ':active' => $isActive,
        ]);

        return ['success' => true, 'id' => (int) $db->lastInsertId(), 'action' => 'created'];
    }

    /**
     * Переключить активность.
     *
     * @param int $pageId
     * @return array
     */
    public function toggleActive(int $pageId): array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            'UPDATE ' . db_table('landing_pages') . ' SET is_active = NOT is_active, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([':id' => $pageId]);

        return ['success' => $stmt->rowCount() > 0];
    }

    /**
     * Удалить страницу.
     *
     * @param int $pageId
     * @return array
     */
    public function delete(int $pageId): array
    {
        $db = Database::connection();
        $stmt = $db->prepare('DELETE FROM ' . db_table('landing_pages') . ' WHERE id = :id');
        $stmt->execute([':id' => $pageId]);

        return ['success' => $stmt->rowCount() > 0];
    }
}
