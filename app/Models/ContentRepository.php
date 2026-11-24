<?php

namespace App\Models;

use App\Core\Database;
use App\Core\QueryBuilder;
use App\DTO\ArticleDTO;

class ContentRepository
{
    private $db;
    private $qb;

    public function __construct()
    {
        $this->db = Database::connection();
        // QueryBuilder для таблицы articles
        $this->qb = new QueryBuilder($this->db, 'articles');
    }

    /**
     * Внутренний хелпер: создаёт QueryBuilder для articles с базовыми JOIN и select.
     */
    private function baseArticlesQuery(): QueryBuilder
    {
        return (new QueryBuilder($this->db, 'articles a'))
            ->select(['a.*', 'c.name AS category', 'c.slug AS category_slug'])
            ->leftJoin('categories c', 'c.id', '=', 'a.category_id');
    }

    /**
     * Внутренний хелпер: QueryBuilder для portfolio_projects.
     */
    private function baseProjectsQuery(): QueryBuilder
    {
        return new QueryBuilder($this->db, 'portfolio_projects');
    }

    /**
     * Внутренний хелпер: QueryBuilder для categories с подсчётом статей.
     * Делает LEFT JOIN с articles и GROUP BY для получения articles_count.
     *
     * ИСПОЛЬЗУЕТСЯ В:
     * - categories() — список всех рубрик с количеством статей
     * - categoryById(), categoryBySlug() — одиночная рубрика с count
     *
     * @return QueryBuilder Настроенный QB (нужно вызвать get() или first())
     */
    private function baseCategoriesQuery(): QueryBuilder
    {
        return (new QueryBuilder($this->db, 'categories c'))
            ->select([
                'c.id',
                'c.name',
                'c.slug',
                'c.description',
                'COUNT(a.id) AS articles_count'  // алиас для CategoryDTO::fromDatabase()
            ])
            ->leftJoin('articles a', 'a.category_id', '=', 'c.id')
            ->orderBy('c.name', 'ASC');
    }

    public function articles(bool $publishedOnly = false): array
    {
        $query = $this->baseArticlesQuery()
            ->when($publishedOnly, fn($q) => $q->where('a.is_published', '=', 1))
            ->orderBy('a.published_at', 'DESC')
            ->orderBy('a.created_at', 'DESC');

        $rows = $query->get();
        return array_map([$this, 'articleRow'], $rows);
    }

    public function articleById(string $id): ?array
    {
        $row = $this->baseArticlesQuery()
            ->where('a.id', '=', $id)
            ->first();

        return $row ? $this->articleRow($row) : null;
    }

    public function articleBySlug(string $slug, bool $publishedOnly = true): ?array
    {
        $query = $this->baseArticlesQuery()->where('a.slug', '=', $slug);

        if ($publishedOnly) {
            $query->where('a.is_published', '=', 1);
        }

        $row = $query->first();
        return $row ? $this->articleRow($row) : null;
    }

    /**
     * Получает статью как ArticleDTO (для API ответов).
     *
     * @param string $id
     * @return ArticleDTO|null
     */
    public function articleDtoById(string $id): ?ArticleDTO
    {
        $row = $this->baseArticlesQuery()
            ->where('a.id', '=', $id)
            ->first();

        return $row ? ArticleDTO::fromDatabase($row) : null;
    }

    /**
     * Получает статью по slug как ArticleDTO.
     *
     * @param string $slug
     * @param bool $publishedOnly
     * @return ArticleDTO|null
     */
    public function articleDtoBySlug(string $slug, bool $publishedOnly = true): ?ArticleDTO
    {
        $query = $this->baseArticlesQuery()->where('a.slug', '=', $slug);

        if ($publishedOnly) {
            $query->where('a.is_published', '=', 1);
        }

        $row = $query->first();
        return $row ? ArticleDTO::fromDatabase($row) : null;
    }

    /**
     * Список статей как массив ArticleDTO.
     *
     * @param bool $publishedOnly
     * @return ArticleDTO[]
     */
    public function articlesAsDto(bool $publishedOnly = false): array
    {
        $query = $this->baseArticlesQuery()
            ->when($publishedOnly, fn($q) => $q->where('a.is_published', '=', 1))
            ->orderBy('a.published_at', 'DESC')
            ->orderBy('a.created_at', 'DESC');

        $rows = $query->get();
        return array_map([ArticleDTO::class, 'fromDatabase'], $rows);
    }

    /**
     * Количество одобренных комментариев по slug статей.
     *
     * @return array<string, int>
     */
    public function commentCounts(): array
    {
        $rows = $this->db->query(
            "SELECT article_slug, COUNT(*) AS n FROM " . db_table('messages') . "
             WHERE message_type = 'comment' AND is_approved = 1 AND article_slug <> ''
             GROUP BY article_slug"
        )->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['article_slug']] = (int) $row['n'];
        }
        return $map;
    }

    /**
     * Рубрика по ID (возвращает массив для обратной совместимости).
     */
    public function categoryById(int $id): ?array
    {
        $row = $this->baseCategoriesQuery()
            ->where('c.id', '=', $id)
            ->first();

        return $row ?: null;
    }

    /**
     * Рубрика по slug (возвращает массив для обратной совместимости).
     */
    public function categoryBySlug(string $slug): ?array
    {
        $row = $this->baseCategoriesQuery()
            ->where('c.slug', '=', $slug)
            ->first();

        return $row ?: null;
    }

    /**
     * Опубликованные статьи конкретной рубрики (для страницы рубрики).
     */
    public function articlesByCategory(string $categorySlug, bool $publishedOnly = true): array
    {
        $query = $this->baseArticlesQuery()
            ->where('c.slug', '=', $categorySlug)
            ->when($publishedOnly, fn($q) => $q->where('a.is_published', '=', 1))
            ->orderBy('a.published_at', 'DESC')
            ->orderBy('a.created_at', 'DESC');

        $rows = $query->get();
        return array_map([$this, 'articleRow'], $rows);
    }

    /**
     * Сохраняет статью из ArticleDTO (новый предпочтительный способ).
     *
     * @param ArticleDTO $dto
     * @return array|null Сохранённая статья с присоединенными данными категории
     */
    public function saveArticleFromDTO(ArticleDTO $dto): ?array
    {
        // Валидируем DTO перед сохранением
        if (!$dto->validate()) {
            throw new \InvalidArgumentException('ArticleDTO validation failed: ' . json_encode($dto->getErrors()));
        }

        // Резолвим category_id (DTO может иметь category slug или category_id)
        $categoryId = $dto->category_id;
        if (!$categoryId && $dto->category) {
            $category = $this->db->prepare('SELECT id FROM ' . db_table('categories') . ' WHERE slug = :slug');
            $category->execute(['slug' => $dto->category]);
            $categoryId = (int)($category->fetchColumn() ?? 0);

            if (!$categoryId) {
                // Автосоздание категории по slug
                $insertCategory = $this->db->prepare('INSERT INTO ' . db_table('categories') . ' (name, slug) VALUES (:name, :slug)');
                $insertCategory->execute([
                    'name' => $dto->category,
                    'slug' => $dto->category,
                ]);
                $categoryId = (int)$this->db->lastInsertId();
            }
        }

        // Подготовка данных для БД
        $data = [
            'id' => $dto->id,
            'slug' => $dto->slug,
            'title' => $dto->title,
            'description' => $dto->description,
            'body_html' => $dto->content,
            'category_id' => $categoryId ?: null,
            'author' => $dto->author,
            'published_at' => $dto->date !== '' ? $dto->date : null,
            'icon' => $dto->icon,
            'cover_path' => $dto->cover,
            'tags_json' => json_encode($dto->tags, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'is_published' => $dto->active ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // UPSERT: INSERT ... ON DUPLICATE KEY UPDATE
        $table = db_table('articles');
        $columns = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $colList = implode(',', $columns);

        // UPDATE часть (все кроме id)
        $updateParts = [];
        foreach ($columns as $col) {
            if ($col !== 'id') {
                $updateParts[] = "$col = VALUES($col)";
            }
        }

        $sql = "INSERT INTO $table ($colList) VALUES ($placeholders)
                ON DUPLICATE KEY UPDATE " . implode(', ', $updateParts);

        $stmt = $this->db->prepare($sql);
        $values = array_values($data);
        // Для ON DUPLICATE KEY UPDATE значения повторяются
        $stmt->execute(array_merge($values, $values));

        return $this->articleById($dto->id);
    }

    /**
     * Сохраняет статью из массива (обратная совместимость).
     * Преобразует массив в ArticleDTO и делегирует saveArticleFromDTO.
     *
     * @param array $article
     * @return array|null
     */
    public function saveArticle(array $article): ?array
    {
        $dto = ArticleDTO::fromRequest($article, $article['id'] ?? null);
        return $this->saveArticleFromDTO($dto);
    }

    public function deleteArticle($id)
    {
        $statement = $this->db->prepare('DELETE FROM ' . db_table('articles') . ' WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }

    public function projects(bool $publishedOnly = false): array
    {
        $query = $this->baseProjectsQuery()
            ->when($publishedOnly, fn($q) => $q->where('is_published', '=', 1))
            ->orderBy('created_at', 'DESC');

        $rows = $query->get();
        return array_map([$this, 'projectRow'], $rows);
    }

    public function projectById(string $id): ?array
    {
        $row = $this->baseProjectsQuery()
            ->where('id', '=', $id)
            ->first();

        return $row ? $this->projectRow($row) : null;
    }

    public function projectBySlug(string $slug, bool $publishedOnly = true): ?array
    {
        $query = $this->baseProjectsQuery()->where('slug', '=', $slug);

        if ($publishedOnly) {
            $query->where('is_published', '=', 1);
        }

        $row = $query->first();
        return $row ? $this->projectRow($row) : null;
    }

    /**
     * Сохраняет проект из ProjectDTO (новый предпочтительный способ).
     *
     * @param ProjectDTO $dto
     * @return array|null Сохранённый проект с gallery и infoItems
     */
    public function saveProjectFromDTO(ProjectDTO $dto): ?array
    {
        // Валидируем DTO перед сохранением
        if (!$dto->validate()) {
            throw new \InvalidArgumentException('ProjectDTO validation failed: ' . json_encode($dto->getErrors()));
        }

        // Подготовка данных для основной таблицы
        $data = $dto->toArray();

        // UPSERT: INSERT ... ON DUPLICATE KEY UPDATE
        $table = db_table('portfolio_projects');
        $columns = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $colList = implode(',', $columns);

        // UPDATE часть (все кроме id)
        $updateParts = [];
        foreach ($columns as $col) {
            if ($col !== 'id') {
                $updateParts[] = "$col = VALUES($col)";
            }
        }

        $sql = "INSERT INTO $table ($colList) VALUES ($placeholders)
                ON DUPLICATE KEY UPDATE " . implode(', ', $updateParts);

        $stmt = $this->db->prepare($sql);
        $values = array_values($data);
        // Для ON DUPLICATE KEY UPDATE значения повторяются
        $stmt->execute(array_merge($values, $values));

        // Сохраняем связанные данные (gallery, infoItems) — полная замена
        $this->saveProjectGallery($dto->id, $dto->gallery);
        $this->saveProjectInfoItems($dto->id, $dto->infoItems);

        return $this->projectById($dto->id);
    }

    /**
     * Сохраняет проект из массива (обратная совместимость).
     * Преобразует массив в ProjectDTO и делегирует saveProjectFromDTO.
     *
     * @param array $project
     * @return array|null
     */
    public function saveProject(array $project): ?array
    {
        $dto = ProjectDTO::fromRequest($project, $project['id'] ?? null);
        return $this->saveProjectFromDTO($dto);
    }

    /**
     * Сохранить снимки проекта с описаниями (полная замена списка).
     *
     * @param array<int, array{path: string, description?: string}> $items
     */
    private function saveProjectGallery($projectId, array $items)
    {
        $delete = $this->db->prepare('DELETE FROM ' . db_table('project_gallery') . ' WHERE project_id = :id');
        $delete->execute(['id' => $projectId]);
        if (!$items) {
            return;
        }
        $insert = $this->db->prepare(
            'INSERT INTO ' . db_table('project_gallery') . ' (project_id, path, description, sort_order) VALUES (:id, :path, :description, :sort)'
        );
        foreach (array_values($items) as $index => $item) {
            $insert->execute([
                'id' => $projectId,
                'path' => (string) $item['path'],
                'description' => is_string($item['description'] ?? null) ? $item['description'] : '',
                'sort' => $index,
            ]);
        }
    }

    /**
     * Сохранить дополнительные поля проекта (полная замена списка).
     *
     * @param array<int, array{kind: string, name: string, value: string}> $items
     */
    private function saveProjectInfoItems($projectId, array $items)
    {
        $delete = $this->db->prepare('DELETE FROM ' . db_table('project_info_items') . ' WHERE project_id = :id');
        $delete->execute(['id' => $projectId]);
        if (!$items) {
            return;
        }
        $insert = $this->db->prepare(
            'INSERT INTO ' . db_table('project_info_items') . ' (project_id, kind, name, value, file_path, sort_order)
             VALUES (:id, :kind, :name, :value, :filePath, :sort)'
        );
        foreach (array_values($items) as $index => $item) {
            $insert->execute([
                'id' => $projectId,
                'kind' => (string) $item['kind'],
                'name' => (string) $item['name'],
                'value' => (string) ($item['value'] ?? ''),
                'filePath' => (string) ($item['file_path'] ?? ''),
                'sort' => $index,
            ]);
        }
    }

    public function deleteProject($id)
    {
        $statement = $this->db->prepare('DELETE FROM ' . db_table('portfolio_projects') . ' WHERE id = :id');
        $statement->execute(['id' => $id]);
        $galleryDelete = $this->db->prepare('DELETE FROM ' . db_table('project_gallery') . ' WHERE project_id = :id');
        $galleryDelete->execute(['id' => $id]);
        $infoDelete = $this->db->prepare('DELETE FROM ' . db_table('project_info_items') . ' WHERE project_id = :id');
        $infoDelete->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }

    public function categoryNavigation(int $categoryId, bool $activeOnly = false): array
    {
        $sql = 'SELECT id, category_id, title, url, icon, display_order, is_active
                FROM ' . db_table('category_navigation') . ' WHERE category_id = :category_id';
        if ($activeOnly) {
            $sql .= ' AND is_active = 1';
        }
        $sql .= ' ORDER BY display_order ASC, id ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['category_id' => $categoryId]);
        return $statement->fetchAll();
    }

    public function saveCategoryNavigation(?int $id, int $categoryId, string $title, string $url, string $icon, int $isActive): ?int
    {
        if ($id === null) {
            $order = $this->db->prepare(
                'SELECT COALESCE(MAX(display_order), 0) + 1 FROM ' . db_table('category_navigation') . ' WHERE category_id = :category_id'
            );
            $order->execute(['category_id' => $categoryId]);
            $statement = $this->db->prepare(
                'INSERT INTO ' . db_table('category_navigation') . ' (category_id, title, url, icon, display_order, is_active)
                 VALUES (:category_id, :title, :url, :icon, :display_order, :is_active)'
            );
            $statement->execute([
                'category_id' => $categoryId,
                'title' => $title,
                'url' => $url,
                'icon' => $icon,
                'display_order' => (int) $order->fetchColumn(),
                'is_active' => $isActive,
            ]);
            return (int) $this->db->lastInsertId();
        }

        $statement = $this->db->prepare(
            'UPDATE ' . db_table('category_navigation') . '
             SET title = :title, url = :url, icon = :icon, is_active = :is_active, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND category_id = :category_id'
        );
        $statement->execute([
            'id' => $id,
            'category_id' => $categoryId,
            'title' => $title,
            'url' => $url,
            'icon' => $icon,
            'is_active' => $isActive,
        ]);
        return $statement->rowCount() > 0 || $this->navigationItemExists($id, $categoryId) ? $id : null;
    }

    public function reorderCategoryNavigation(int $categoryId, array $itemIds): bool
    {
        $statement = $this->db->prepare(
            'SELECT id FROM ' . db_table('category_navigation') . ' WHERE category_id = :category_id ORDER BY display_order ASC, id ASC'
        );
        $statement->execute(['category_id' => $categoryId]);
        $existingIds = array_map('intval', $statement->fetchAll(\PDO::FETCH_COLUMN));
        $itemIds = array_values(array_unique(array_map('intval', $itemIds)));
        $sortedExistingIds = $existingIds;
        $sortedItemIds = $itemIds;
        sort($sortedExistingIds);
        sort($sortedItemIds);
        if ($sortedExistingIds !== $sortedItemIds) {
            return false;
        }
        $this->db->beginTransaction();
        try {
            $update = $this->db->prepare(
                'UPDATE ' . db_table('category_navigation') . ' SET display_order = :display_order, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND category_id = :category_id'
            );
            foreach ($itemIds as $order => $itemId) {
                $update->execute([
                    'display_order' => $order + 1,
                    'id' => $itemId,
                    'category_id' => $categoryId,
                ]);
            }
            $this->db->commit();
        } catch (\Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
        return true;
    }

    public function deleteCategoryNavigation(int $id, int $categoryId): bool
    {
        $statement = $this->db->prepare(
            'DELETE FROM ' . db_table('category_navigation') . ' WHERE id = :id AND category_id = :category_id'
        );
        $statement->execute(['id' => $id, 'category_id' => $categoryId]);
        return $statement->rowCount() > 0;
    }

    private function navigationItemExists(int $id, int $categoryId): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM ' . db_table('category_navigation') . ' WHERE id = :id AND category_id = :category_id LIMIT 1'
        );
        $statement->execute(['id' => $id, 'category_id' => $categoryId]);
        return $statement->fetchColumn() !== false;
    }

    public function categories(): array
    {
        $rows = $this->baseCategoriesQuery()
            ->groupBy('c.id')  // Добавим groupBy в QueryBuilder
            ->get();

        return $rows;
    }

    public function deleteCategory($id)
    {
        $articles = $this->db->prepare(
            'SELECT COUNT(*) FROM ' . db_table('articles') . ' WHERE category_id = :category_id'
        );
        $articles->execute(['category_id' => $id]);
        if ((int) $articles->fetchColumn() > 0) {
            return false;
        }

        $statement = $this->db->prepare('DELETE FROM ' . db_table('categories') . ' WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }

    // === DTO методы для категорий ===

    /**
     * Сохраняет категорию из CategoryDTO (новый предпочтительный способ).
     *
     * ИСПОЛЬЗОВАНИЕ:
     * ```php
     * $dto = CategoryDTO::fromRequest($request->json(), $id);
     * if (!$dto->validate()) { return errors; }
     * $saved = $this->saveCategoryFromDTO($dto);
     * ```
     *
     * @param CategoryDTO $dto Заполненный и провалидированный DTO
     * @return int|false ID сохранённой категории или false при ошибке
     */
    public function saveCategoryFromDTO(CategoryDTO $dto)
    {
        // Валидируем DTO перед сохранением
        if (!$dto->validate()) {
            throw new \InvalidArgumentException('CategoryDTO validation failed: ' . json_encode($dto->getErrors()));
        }

        $data = $dto->toArray();
        $table = db_table('categories');

        if ($dto->id === null) {
            // INSERT — новая категория
            // Убираем id из данных (AUTO_INCREMENT)
            unset($data['id']);
            $columns = array_keys($data);
            $placeholders = implode(',', array_fill(0, count($columns), '?'));
            $colList = implode(',', $columns);

            $sql = "INSERT INTO $table ($colList) VALUES ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($data));

            return (int)$this->db->lastInsertId();
        } else {
            // UPDATE — существующая категория
            $updateParts = [];
            foreach ($data as $col => $val) {
                if ($col !== 'id') { // id не обновляем
                    $updateParts[] = "$col = ?";
                }
            }
            $sql = "UPDATE $table SET " . implode(', ', $updateParts) . " WHERE id = ?";

            $values = [];
            foreach ($data as $col => $val) {
                if ($col !== 'id') {
                    $values[] = $val;
                }
            }
            $values[] = $dto->id; // для WHERE id = ?

            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);

            if ($stmt->rowCount() > 0) {
                return (int)$dto->id;
            }
            // Проверяем, существует ли запись (rowCount=0 может быть если данные не изменились)
            $exists = $this->db->prepare("SELECT 1 FROM $table WHERE id = ?");
            $exists->execute([$dto->id]);
            return $exists->fetchColumn() !== false ? (int)$dto->id : false;
        }
    }

    /**
     * Сохраняет категорию из массива (обратная совместимость).
     * Преобразует массив в CategoryDTO и делегирует saveCategoryFromDTO.
     *
     * @param int|null $id
     * @param string $name
     * @param string $slug
     * @param string $description
     * @return int|false
     */
    public function saveCategory($id, $name, $slug, $description)
    {
        $dto = new CategoryDTO();
        $dto->id = $id;
        $dto->name = $name;
        $dto->slug = $slug;
        $dto->description = $description;
        return $this->saveCategoryFromDTO($dto);
    }

    /**
     * Получает категорию как CategoryDTO (для API ответов).
     *
     * @param int $id
     * @return CategoryDTO|null
     */
    public function categoryDtoById(int $id): ?CategoryDTO
    {
        $row = $this->baseCategoriesQuery()
            ->where('c.id', '=', $id)
            ->first();

        return $row ? CategoryDTO::fromDatabase($row) : null;
    }

    /**
     * Получает категорию по slug как CategoryDTO.
     *
     * @param string $slug
     * @return CategoryDTO|null
     */
    public function categoryDtoBySlug(string $slug): ?CategoryDTO
    {
        $row = $this->baseCategoriesQuery()
            ->where('c.slug', '=', $slug)
            ->first();

        return $row ? CategoryDTO::fromDatabase($row) : null;
    }

    /**
     * Список категорий как массив CategoryDTO.
     *
     * @return CategoryDTO[]
     */
    public function categoriesAsDto(): array
    {
        $rows = $this->baseCategoriesQuery()
            ->groupBy('c.id')
            ->get();

        return array_map([CategoryDTO::class, 'fromDatabase'], $rows);
    }

    public function orderList()
    {
        $rows = $this->db->query('SELECT * FROM ' . db_table('orders') . ' ORDER BY order_date DESC, id DESC')->fetchAll();
        return array_map([$this, 'orderRow'], $rows);
    }

    public function orderById($id)
    {
        $statement = $this->db->prepare('SELECT * FROM ' . db_table('orders') . ' WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return $row ? $this->orderRow($row) : null;
    }

    public function saveOrder($id, array $data)
    {
        $details = $data;
        $statement = $this->db->prepare(
            'UPDATE ' . db_table('orders') . ' SET client_name = :client_name, email = :email, phone = :phone,
                project_type = :project_type, budget = :budget, deadline = :deadline,
                order_date = :order_date, status = :status, details_json = :details_json
             WHERE id = :id'
        );
        $statement->execute([
            'client_name' => $data['clientName'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'project_type' => $data['projectType'],
            'budget' => $data['budget'],
            'deadline' => $data['deadline'] !== '' ? $data['deadline'] : null,
            'order_date' => $data['orderDate'],
            'status' => $data['status'],
            'details_json' => json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'id' => $id,
        ]);
        return $this->orderById($id);
    }

    public function deleteOrder($id)
    {
        $statement = $this->db->prepare('DELETE FROM ' . db_table('orders') . ' WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }

    public function createOrder(array $data)
    {
        $details = [
            'clientName' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'source' => 'Форма заказа на сайте',
            'contactPerson' => $data['contactPerson'],
            'projectTitle' => $data['type'],
            'projectType' => $data['type'],
            'orderDate' => date('Y-m-d'),
            'deadline' => $data['deadlineTo'],
            'status' => 'Новый',
            'technicalBrief' => $data['message'],
            'budget' => $data['budget'],
            'budgetDisplay' => $data['budgetDisplay'] ?? '',
            'deadlineFrom' => $data['deadlineFrom'] ?? '',
            'paidAmount' => 0,
            'paymentHistory' => '',
            'notes' => '',
            'calculatorDetails' => $data['calculatorDetails'],
        ];
        $statement = $this->db->prepare(
            'INSERT INTO ' . db_table('orders') . ' (client_name, email, phone, project_type, budget, deadline, order_date, status, details_json)
             VALUES (:client_name, :email, :phone, :project_type, :budget, :deadline, :order_date, :status, :details_json)'
        );
        $statement->execute([
            'client_name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'project_type' => $data['type'],
            'budget' => is_numeric($data['budget']) ? (float) $data['budget'] : 0,
            'deadline' => $data['deadlineTo'] !== '' ? $data['deadlineTo'] : null,
            'order_date' => date('Y-m-d'),
            'status' => 'Новый',
            'details_json' => json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
        return $this->orderById($this->db->lastInsertId());
    }

    public function createMessage(array $message)
    {
        $statement = $this->db->prepare(
            'INSERT INTO ' . db_table('messages') . ' (message_type, name, email, phone, subject, body, article_slug, parent_id, is_staff)
             VALUES (:message_type, :name, :email, :phone, :subject, :body, :article_slug, :parent_id, :is_staff)'
        );
        $statement->execute([
            'message_type' => $message['type'],
            'name' => $message['name'],
            'email' => $message['email'],
            'phone' => $message['phone'],
            'subject' => $message['subject'],
            'body' => $message['body'],
            'article_slug' => $message['articleSlug'],
            'parent_id' => $message['parentId'] ?? null,
            'is_staff' => !empty($message['isStaff']) ? 1 : 0,
        ]);
        return $this->db->lastInsertId();
    }

    public function messages()
    {
        return $this->db->query('SELECT * FROM ' . db_table('messages') . ' ORDER BY created_at DESC, id DESC')->fetchAll();
    }

    /**
     * Найти обращение по id.
     */
    public function messageById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM ' . db_table('messages') . ' WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return $row ?: null;
    }

    /**
     * Ответ администратора на корневой комментарий статьи.
     * Ответ публикуется сразу (одобрен) и помечается как ответ автора сайта.
     *
     * @return array{success: bool, id?: int, error?: string}
     */
    public function staffReply(int $rootId, string $name, string $body): array
    {
        $root = $this->messageById($rootId);
        if (!$root || $root['message_type'] !== 'comment' || $root['parent_id'] !== null) {
            return ['success' => false, 'error' => 'Комментарий не найден или уже является ответом'];
        }

        $statement = $this->db->prepare(
            'INSERT INTO ' . db_table('messages') . ' (message_type, name, email, phone, subject, body, article_slug, parent_id, is_staff, is_approved, is_read)
             VALUES (:message_type, :name, :email, :phone, :subject, :body, :article_slug, :parent_id, 1, 1, 1)'
        );
        $statement->execute([
            'message_type' => 'comment',
            'name' => $name,
            'email' => '',
            'phone' => '',
            'subject' => 'Ответ на комментарий #' . $rootId,
            'body' => $body,
            'article_slug' => $root['article_slug'],
            'parent_id' => $rootId,
        ]);
        return ['success' => true, 'id' => (int) $this->db->lastInsertId()];
    }

    /**
     * Одобренные комментарии к статье (для публичного вывода).
     *
     * @param string $slug
     * @return array
     */
    public function commentsByArticle(string $slug): array
    {
        $statement = $this->db->prepare(
            "SELECT id, parent_id, is_staff, name, body, created_at FROM " . db_table('messages') . "
             WHERE message_type = 'comment' AND article_slug = :slug AND is_approved = 1
             ORDER BY created_at ASC, id ASC"
        );
        $statement->execute(['slug' => $slug]);
        return $statement->fetchAll();
    }

    /**
     * Количество одобренных комментариев к статье.
     *
     * @param string $slug
     * @return int
     */
    public function commentsCount(string $slug): int
    {
        $statement = $this->db->prepare(
            "SELECT COUNT(*) FROM " . db_table('messages') . "
             WHERE message_type = 'comment' AND article_slug = :slug AND is_approved = 1"
        );
        $statement->execute(['slug' => $slug]);
        return (int) $statement->fetchColumn();
    }

    public function updateMessage($id, array $data)
    {
        $statement = $this->db->prepare('UPDATE ' . db_table('messages') . ' SET is_read = :is_read, is_approved = :is_approved WHERE id = :id');
        $statement->execute([
            'is_read' => !empty($data['isRead']) ? 1 : 0,
            'is_approved' => !empty($data['isApproved']) ? 1 : 0,
            'id' => $id,
        ]);
        if ($statement->rowCount() > 0) {
            return true;
        }
        $exists = $this->db->prepare('SELECT 1 FROM ' . db_table('messages') . ' WHERE id = :id');
        $exists->execute(['id' => $id]);
        return $exists->fetchColumn() !== false;
    }

    public function deleteMessage($id)
    {
        $statement = $this->db->prepare('DELETE FROM ' . db_table('messages') . ' WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }

    private function articleRow(array $row)
    {
        $row['active'] = (bool) $row['is_published'];
        $row['date'] = $row['published_at'] ?? '';
        $row['content'] = $row['body_html'] ?? '';
        $row['cover'] = $row['cover_path'];
        $row['tags'] = json_decode($row['tags_json'] ?? '[]', true) ?: [];
        return $row;
    }

    private function projectRow(array $row)
    {
        $row['active'] = (bool) $row['is_published'];
        $row['task'] = json_decode($row['task_json'] ?? '[]', true) ?: [];
        $row['result'] = (string) ($row['result'] ?? '');
        $row['results'] = json_decode($row['results_json'] ?? '[]', true) ?: [];
        $row['screenshots'] = json_decode($row['screenshots_json'] ?? '[]', true) ?: [];
        $row['year'] = $row['project_year'] ?? '';
        // Снимки с описаниями и дополнительные поля (ссылки/документы/сертификаты)
        $row['gallery'] = $this->projectGalleryItems((string) $row['id']);
        $row['infoItems'] = $this->projectInfoItems((string) $row['id']);
        return $row;
    }

    /**
     * Снимки проекта с описаниями (таблица project_gallery), в порядке сортировки.
     * @return array<int, array{path: string, description: string}>
     */
    public function projectGalleryItems($projectId)
    {
        $statement = $this->db->prepare(
            'SELECT path, description FROM ' . db_table('project_gallery') . ' WHERE project_id = :id ORDER BY sort_order, id'
        );
        $statement->execute(['id' => $projectId]);
        return $statement->fetchAll();
    }

    /**
     * Дополнительные поля проекта (ссылки, документы, сертификаты), в порядке сортировки.
     * @return array<int, array{kind: string, name: string, value: string}>
     */
    public function projectInfoItems($projectId)
    {
        $statement = $this->db->prepare(
            'SELECT kind, name, value, file_path FROM ' . db_table('project_info_items') . ' WHERE project_id = :id ORDER BY sort_order, id'
        );
        $statement->execute(['id' => $projectId]);
        return $statement->fetchAll();
    }

    private function orderRow(array $row)
    {
        $details = json_decode($row['details_json'] ?? '{}', true);
        if (!is_array($details)) {
            $details = [];
        }
        return array_merge($details, [
            'id' => (int) $row['id'],
            'client' => $row['client_name'],
            'clientName' => $row['client_name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'type' => $row['project_type'],
            'projectType' => $row['project_type'],
            'budget' => (float) $row['budget'],
            'deadline' => $row['deadline'] ?? '',
            'orderDate' => $row['order_date'],
            'createdAt' => $row['order_date'],
            'status' => $row['status'],
        ]);
    }

    // === DTO методы для проектов ===

    /**
     * Получает проект как ProjectDTO (для API ответов).
     *
     * @param string $id
     * @return ProjectDTO|null
     */
    public function projectDtoById(string $id): ?ProjectDTO
    {
        $row = $this->baseProjectsQuery()
            ->where('id', '=', $id)
            ->first();

        return $row ? ProjectDTO::fromDatabase($row) : null;
    }

    /**
     * Получает проект по slug как ProjectDTO.
     *
     * @param string $slug
     * @param bool $publishedOnly
     * @return ProjectDTO|null
     */
    public function projectDtoBySlug(string $slug, bool $publishedOnly = true): ?ProjectDTO
    {
        $query = $this->baseProjectsQuery()->where('slug', '=', $slug);

        if ($publishedOnly) {
            $query->where('is_published', '=', 1);
        }

        $row = $query->first();
        return $row ? ProjectDTO::fromDatabase($row) : null;
    }

    /**
     * Список проектов как массив ProjectDTO.
     *
     * @param bool $publishedOnly
     * @return ProjectDTO[]
     */
    public function projectsAsDto(bool $publishedOnly = false): array
    {
        $query = $this->baseProjectsQuery()
            ->when($publishedOnly, fn($q) => $q->where('is_published', '=', 1))
            ->orderBy('created_at', 'DESC');

        $rows = $query->get();
        return array_map([ProjectDTO::class, 'fromDatabase'], $rows);
    }
}
