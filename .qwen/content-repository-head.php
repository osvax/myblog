<?php

namespace App\Models;

use App\Core\Database;
class ContentRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function articles($publishedOnly = false)
    {
        $where = $publishedOnly ? 'WHERE a.is_published = 1' : '';
        $statement = $this->db->query(
            'SELECT a.*, c.name AS category, c.slug AS category_slug
             FROM ' . db_table('articles') . ' a LEFT JOIN ' . db_table('categories') . ' c ON c.id = a.category_id ' . $where . '
             ORDER BY a.published_at DESC, a.created_at DESC'
        );
        return array_map([$this, 'articleRow'], $statement->fetchAll());
    }

    public function articleById($id)
    {
        $statement = $this->db->prepare(
            'SELECT a.*, c.name AS category, c.slug AS category_slug
             FROM ' . db_table('articles') . ' a LEFT JOIN ' . db_table('categories') . ' c ON c.id = a.category_id WHERE a.id = :id'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return $row ? $this->articleRow($row) : null;
    }

    public function articleBySlug($slug, $publishedOnly = true)
    {
        $sql = 'SELECT a.*, c.name AS category, c.slug AS category_slug
                FROM ' . db_table('articles') . ' a LEFT JOIN ' . db_table('categories') . ' c ON c.id = a.category_id WHERE a.slug = :slug';
        if ($publishedOnly) {
            $sql .= ' AND a.is_published = 1';
        }
        $statement = $this->db->prepare($sql);
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();
        return $row ? $this->articleRow($row) : null;
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
     * Рубрика по slug.
     */
    public function categoryBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare('SELECT id, name, slug, description FROM ' . db_table('categories') . ' WHERE slug = :slug LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();
        return $row ?: null;
    }

    /**
     * Опубликованные статьи конкретной рубрики (для страницы рубрики).
     */
    public function articlesByCategory(string $categorySlug, bool $publishedOnly = true): array
    {
        $sql = 'SELECT a.*, c.name AS category, c.slug AS category_slug
                FROM ' . db_table('articles') . ' a LEFT JOIN ' . db_table('categories') . ' c ON c.id = a.category_id
                WHERE c.slug = :slug';
        if ($publishedOnly) {
            $sql .= ' AND a.is_published = 1';
        }
        $statement = $this->db->prepare($sql . ' ORDER BY a.published_at DESC, a.created_at DESC');
        $statement->execute(['slug' => $categorySlug]);
        return array_map([$this, 'articleRow'], $statement->fetchAll());
    }

    public function saveArticle(array $article)
    {
        $category = $this->db->prepare('SELECT id FROM ' . db_table('categories') . ' WHERE name = :name');
        $category->execute(['name' => $article['category']]);
        $categoryId = $category->fetchColumn();
        if (!$categoryId) {
            // Автогенерация slug рубрики из названия (кириллица транслитерируется)
            $slug = \slugify($article['category']);
            $insertCategory = $this->db->prepare('INSERT INTO ' . db_table('categories') . ' (name, slug) VALUES (:name, :slug)');
            $insertCategory->execute([
                'name' => $article['category'],
                'slug' => $slug !== '' ? $slug : substr(hash('sha256', $article['category']), 0, 32),
            ]);
            $categoryId = $this->db->lastInsertId();
        }

        $statement = $this->db->prepare(
            'INSERT INTO ' . db_table('articles') . '
                (id, slug, title, description, body_html, category_id, author, published_at, icon, cover_path, tags_json, is_published)
             VALUES
                (:id, :slug, :title, :description, :body_html, :category_id, :author, :published_at, :icon, :cover_path, :tags_json, :is_published)
             ON DUPLICATE KEY UPDATE
                slug = VALUES(slug), title = VALUES(title), description = VALUES(description),
                body_html = VALUES(body_html), category_id = VALUES(category_id), author = VALUES(author),
                published_at = VALUES(published_at), icon = VALUES(icon), cover_path = VALUES(cover_path),
                tags_json = VALUES(tags_json), is_published = VALUES(is_published)'
        );
        $statement->execute([
            'id' => $article['id'],
            'slug' => $article['slug'],
            'title' => $article['title'],
            'description' => $article['description'],
            'body_html' => $article['content'],
            'category_id' => $categoryId,
            'author' => $article['author'],
            'published_at' => $article['date'] !== '' ? $article['date'] : null,
            'icon' => $article['icon'],
            'cover_path' => $article['cover'],
            'tags_json' => json_encode($article['tags'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'is_published' => $article['active'] ? 1 : 0,
        ]);
        return $this->articleById($article['id']);
    }

    public function deleteArticle($id)
    {
        $statement = $this->db->prepare('DELETE FROM ' . db_table('articles') . ' WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }

    public function projects($publishedOnly = false)
    {
        $where = $publishedOnly ? 'WHERE is_published = 1' : '';
        $statement = $this->db->query('SELECT * FROM ' . db_table('portfolio_projects') . ' ' . $where . ' ORDER BY created_at DESC');
        return array_map([$this, 'projectRow'], $statement->fetchAll());
    }

    public function projectById($id)
    {
        $statement = $this->db->prepare('SELECT * FROM ' . db_table('portfolio_projects') . ' WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return $row ? $this->projectRow($row) : null;
    }

    public function projectBySlug($slug, $publishedOnly = true)
    {
        $sql = 'SELECT * FROM ' . db_table('portfolio_projects') . ' WHERE slug = :slug' . ($publishedOnly ? ' AND is_published = 1' : '');
        $statement = $this->db->prepare($sql);
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();
        return $row ? $this->projectRow($row) : null;
    }

    public function saveProject(array $project)
    {
        $statement = $this->db->prepare(
            'INSERT INTO ' . db_table('portfolio_projects') . '
                (id, slug, title, technologies, icon, description, task_json, solution, client, project_year, duration, stack, results_json, screenshots_json, is_published)
             VALUES
                (:id, :slug, :title, :technologies, :icon, :description, :task_json, :solution, :client, :project_year, :duration, :stack, :results_json, :screenshots_json, :is_published)
             ON DUPLICATE KEY UPDATE
                slug = VALUES(slug), title = VALUES(title), technologies = VALUES(technologies),
                icon = VALUES(icon), description = VALUES(description), task_json = VALUES(task_json),
                solution = VALUES(solution), client = VALUES(client), project_year = VALUES(project_year),
                duration = VALUES(duration), stack = VALUES(stack), results_json = VALUES(results_json),
                screenshots_json = VALUES(screenshots_json), is_published = VALUES(is_published)'
        );
        $statement->execute([
            'id' => $project['id'],
            'slug' => $project['slug'],
            'title' => $project['title'],
            'technologies' => $project['technologies'],
            'icon' => $project['icon'],
            'description' => $project['description'],
            'task_json' => json_encode($project['task'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'solution' => $project['solution'],
            'client' => $project['client'],
            'project_year' => $project['year'] ?: null,
            'duration' => $project['duration'],
            'stack' => $project['stack'],
            'results_json' => json_encode($project['results'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'screenshots_json' => json_encode($project['screenshots'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'is_published' => $project['active'] ? 1 : 0,
        ]);
        // Снимки с описаниями и дополнительные поля — отдельные таблицы, заменяются полностью
        $this->saveProjectGallery($project['id'], $project['gallery'] ?? []);
        $this->saveProjectInfoItems($project['id'], $project['infoItems'] ?? []);
        return $this->projectById($project['id']);
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

    public function categories()
    {
        return $this->db->query(
            'SELECT c.id, c.name, c.slug, c.description, COUNT(a.id) AS articles
             FROM ' . db_table('categories') . ' c LEFT JOIN ' . db_table('articles') . ' a ON a.category_id = c.id
             GROUP BY c.id ORDER BY c.name'
        )->fetchAll();
    }

    public function saveCategory($id, $name, $slug, $description)
    {
        if ($id === null) {
            $statement = $this->db->prepare('INSERT INTO ' . db_table('categories') . ' (name, slug, description) VALUES (:name, :slug, :description)');
            $statement->execute(['name' => $name, 'slug' => $slug, 'description' => $description]);
            return (int) $this->db->lastInsertId();
        }
        $statement = $this->db->prepare('UPDATE ' . db_table('categories') . ' SET name = :name, slug = :slug, description = :description WHERE id = :id');
        $statement->execute(['name' => $name, 'slug' => $slug, 'description' => $description, 'id' => $id]);
        if ($statement->rowCount() > 0) {
            return (int) $id;
        }
        $exists = $this->db->prepare('SELECT 1 FROM ' . db_table('categories') . ' WHERE id = :id');
        $exists->execute(['id' => $id]);
        return $exists->fetchColumn() !== false ? (int) $id : false;
    }

    public function deleteCategory($id)
    {
        $statement = $this->db->prepare('DELETE FROM ' . db_table('categories') . ' WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
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
}
