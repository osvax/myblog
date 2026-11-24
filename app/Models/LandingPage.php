<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\QueryBuilder;
use App\DTO\LandingPageDTO;
use PDO;

/**
 * LandingPage — статические страницы (лендинги) из БД.
 *
 * === МИГРАЦИЯ ===
 * - Конструктор принимает PDO (для тестов — SQLite :memory:)
 * - QueryBuilder для простых запросов
 * - Возвращает LandingPageDTO вместо массивов
 * - Статические методы-обёртки для обратной совместимости
 */
final class LandingPage
{
    private PDO $db;
    private string $table;

    /**
     * @param PDO|null $db PDO соединение (по умолчанию Database::connection())
     */
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? \App\Core\Database::connection();
        $this->table = db_table('landing_pages');
    }

    // ===================== ОБРАТНАЯ СОВМЕСТИМОСТЬ (статические) =====================

    /**
     * Получить все страницы (обратная совместимость — возвращает массивы).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getAll(): array
    {
        return array_map(
            fn (LandingPageDTO $dto) => $dto->toApiArray(),
            (new self())->getAllNew()
        );
    }

    /**
     * Получить страницу по slug (обратная совместимость — возвращает массив).
     *
     * @param string $slug
     * @return array<string, mixed>|null
     */
    public static function findBySlug(string $slug): ?array
    {
        $dto = (new self())->findBySlugNew($slug);
        return $dto ? $dto->toApiArray() : null;
    }

    /**
     * Получить активную страницу по slug (обратная совместимость — возвращает массив).
     *
     * @param string $slug
     * @return array<string, mixed>|null
     */
    public static function findActive(string $slug): ?array
    {
        $dto = (new self())->findActiveNew($slug);
        return $dto ? $dto->toApiArray() : null;
    }

    /**
     * Получить страницу по id (обратная совместимость — возвращает массив).
     *
     * @param int $id
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $dto = (new self())->findByIdNew($id);
        return $dto ? $dto->toApiArray() : null;
    }

    /**
     * Создать или обновить страницу (обратная совместимость — возвращает массив).
     *
     * @param string $slug
     * @param string $title
     * @param string $htmlContent
     * @param int $isActive
     * @param int $editId
     * @return array<string, mixed>
     */
    public static function save(
        string $slug,
        string $title,
        string $htmlContent = '',
        int $isActive = 1,
        int $editId = 0
    ): array {
        return (new self())->saveNew($slug, $title, $htmlContent, $isActive, $editId);
    }

    /**
     * Переключить активность (обратная совместимость — возвращает массив).
     *
     * @param int $pageId
     * @return array<string, mixed>
     */
    public static function toggleActive(int $pageId): array
    {
        return (new self())->toggleActiveNew($pageId);
    }

    /**
     * Удалить страницу (обратная совместимость — возвращает массив).
     *
     * @param int $pageId
     * @return array<string, mixed>
     */
    public static function delete(int $pageId): array
    {
        return (new self())->deleteNew($pageId);
    }

    // ===================== НОВЫЙ API (DTO-based) =====================

    /**
     * Получить все страницы.
     *
     * @return LandingPageDTO[]
     */
    public function getAllNew(): array
    {
        $rows = (new QueryBuilder($this->db, $this->table))
            ->select(['id', 'slug', 'title', 'html_content', 'is_active', 'generated_at', 'created_at', 'updated_at'])
            ->orderBy('slug', 'ASC')
            ->get();

        return array_map([LandingPageDTO::class, 'fromDatabase'], $rows);
    }

    /**
     * Получить страницу по slug.
     *
     * @param string $slug
     * @return LandingPageDTO|null
     */
    public function findBySlugNew(string $slug): ?LandingPageDTO
    {
        $row = (new QueryBuilder($this->db, $this->table))
            ->select(['id', 'slug', 'title', 'html_content', 'is_active', 'generated_at', 'created_at', 'updated_at'])
            ->where('slug', '=', $slug)
            ->first();

        return $row ? LandingPageDTO::fromDatabase($row) : null;
    }

    /**
     * Получить активную страницу по slug.
     *
     * @param string $slug
     * @return LandingPageDTO|null
     */
    public function findActiveNew(string $slug): ?LandingPageDTO
    {
        $row = (new QueryBuilder($this->db, $this->table))
            ->select(['id', 'slug', 'title', 'html_content', 'is_active', 'generated_at', 'created_at', 'updated_at'])
            ->where('slug', '=', $slug)
            ->where('is_active', '=', 1)
            ->first();

        return $row ? LandingPageDTO::fromDatabase($row) : null;
    }

    /**
     * Получить страницу по id.
     *
     * @param int $id
     * @return LandingPageDTO|null
     */
    public function findByIdNew(int $id): ?LandingPageDTO
    {
        $row = (new QueryBuilder($this->db, $this->table))
            ->select(['id', 'slug', 'title', 'html_content', 'is_active', 'generated_at', 'created_at', 'updated_at'])
            ->where('id', '=', $id)
            ->first();

        return $row ? LandingPageDTO::fromDatabase($row) : null;
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
     * @return array{success: bool, id?: int, action?: string, error?: string}
     */
    public function saveNew(
        string $slug,
        string $title,
        string $htmlContent = '',
        int $isActive = 1,
        int $editId = 0
    ): array {
        if ($editId > 0) {
            $stmt = $this->db->prepare(
                'UPDATE ' . $this->table . '
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

        $stmt = $this->db->prepare('SELECT id FROM ' . $this->table . ' WHERE slug = :slug LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $this->db->prepare(
                'UPDATE ' . $this->table . '
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

        $stmt = $this->db->prepare(
            'INSERT INTO ' . $this->table . ' (slug, title, html_content, is_active, generated_at, created_at, updated_at)
             VALUES (:slug, :title, :html, :active, NOW(), NOW(), NOW())'
        );
        $stmt->execute([
            ':slug' => $slug,
            ':title' => $title,
            ':html' => $htmlContent,
            ':active' => $isActive,
        ]);

        return ['success' => true, 'id' => (int) $this->db->lastInsertId(), 'action' => 'created'];
    }

    /**
     * Переключить активность.
     *
     * @param int $pageId
     * @return array{success: bool}
     */
    public function toggleActiveNew(int $pageId): array
    {
        $stmt = $this->db->prepare(
            'UPDATE ' . $this->table . ' SET is_active = NOT is_active, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([':id' => $pageId]);

        return ['success' => $stmt->rowCount() > 0];
    }

    /**
     * Удалить страницу.
     *
     * @param int $pageId
     * @return array{success: bool}
     */
    public function deleteNew(int $pageId): array
    {
        $stmt = $this->db->prepare('DELETE FROM ' . $this->table . ' WHERE id = :id');
        $stmt->execute([':id' => $pageId]);

        return ['success' => $stmt->rowCount() > 0];
    }
}
