<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Response;
use App\Models\ContentRepository;
use App\Models\OrderRepository;
use App\Models\MessageRepository;
use App\DTO\ArticleDTO;
use App\DTO\ProjectDTO;
use App\DTO\CategoryDTO;
use App\DTO\OrderDTO;
use App\DTO\MessageDTO;

class ContentController extends Controller
{
    private $content;
    private $orders;
    private $messages;

    public function __construct()
    {
        $this->content = new ContentRepository();
        $this->orders = new OrderRepository();
        $this->messages = new MessageRepository();
    }

    public function articles($request, array $params = [])
    {
        if (!$this->authorized()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() === 'GET') {
            if (isset($params['id'])) {
                $article = $this->content->articleById($params['id']);
                return $article
                    ? $this->json(['success' => true, 'article' => $article])
                    : $this->json(['success' => false, 'error' => 'Статья не найдена'], 404);
            }
            // Число одобренных комментариев — для колонки в списке статей
            $counts = $this->content->commentCounts();
            $articles = $this->content->articles();
            foreach ($articles as &$article) {
                $article['comments'] = $counts[$article['slug']] ?? 0;
            }
            unset($article);
            return $this->json(['success' => true, 'articles' => $articles]);
        }
        if ($request->method() === 'DELETE') {
            \admin_require_csrf();
            return $this->content->deleteArticle($params['id'] ?? '')
                ? $this->json(['success' => true])
                : $this->json(['success' => false, 'error' => 'Статья не найдена'], 404);
        }
        if ($request->method() !== 'POST' && $request->method() !== 'PUT') {
            return $this->methodNotAllowed(['GET', 'POST', 'PUT', 'DELETE']);
        }
        \admin_require_csrf();

        // Новый способ: используем ArticleDTO для валидации
        $jsonData = $request->json();
        $dto = ArticleDTO::fromRequest($jsonData, $params['id'] ?? null);

        if (!$dto->validate()) {
            return $this->json(['success' => false, 'error' => 'Ошибки валидации', 'errors' => $dto->getErrors()], 400);
        }

        try {
            $savedArticle = $this->content->saveArticleFromDTO($dto);
            return $this->json(['success' => true, 'article' => $savedArticle]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            // Логируем ошибку (в будущем — через Monolog)
            error_log('Article save error: ' . $e->getMessage());
            return $this->json(['success' => false, 'error' => 'Ошибка сохранения статьи'], 500);
        }
    }

    public function projects($request, array $params = [])
    {
        if (!$this->authorized()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() === 'GET') {
            if (isset($params['id'])) {
                $project = $this->content->projectById($params['id']);
                return $project
                    ? $this->json(['success' => true, 'project' => $project])
                    : $this->json(['success' => false, 'error' => 'Проект не найден'], 404);
            }
            return $this->json(['success' => true, 'projects' => $this->content->projects()]);
        }
        if ($request->method() === 'DELETE') {
            \admin_require_csrf();
            return $this->content->deleteProject($params['id'] ?? '')
                ? $this->json(['success' => true])
                : $this->json(['success' => false, 'error' => 'Проект не найден'], 404);
        }
        if ($request->method() !== 'POST' && $request->method() !== 'PUT') {
            return $this->methodNotAllowed(['GET', 'POST', 'PUT', 'DELETE']);
        }
        \admin_require_csrf();

        // Новый способ: используем ProjectDTO для валидации
        $jsonData = $request->json();
        $dto = ProjectDTO::fromRequest($jsonData, $params['id'] ?? null);

        if (!$dto->validate()) {
            return $this->json(['success' => false, 'error' => 'Ошибки валидации', 'errors' => $dto->getErrors()], 400);
        }

        try {
            $savedProject = $this->content->saveProjectFromDTO($dto);
            return $this->json(['success' => true, 'project' => $savedProject]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            // Логируем ошибку (в будущем — через Monolog)
            error_log('Project save error: ' . $e->getMessage());
            return $this->json(['success' => false, 'error' => 'Ошибка сохранения проекта'], 500);
        }
    }

    public function orders($request, array $params = [])
    {
        if (!$this->authorized()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() === 'GET') {
            // Новый API: возвращаем DTO с toApiArray для полной структуры
            $orders = $this->orders->ordersAsDto();
            return $this->json(['success' => true, 'orders' => array_map(fn($o) => $o->toApiArray(), $orders)]);
        }
        if ($request->method() === 'DELETE') {
            \admin_require_csrf();
            $id = $params['id'] ?? '';
            if (!ctype_digit((string) $id)) {
                return $this->json(['success' => false, 'error' => 'Некорректный ID заказа'], 400);
            }
            return $this->orders->deleteOrder((int) $id)
                ? $this->json(['success' => true])
                : $this->json(['success' => false, 'error' => 'Заказ не найден'], 404);
        }
        if ($request->method() !== 'PUT' && $request->method() !== 'PATCH') {
            return $this->methodNotAllowed(['GET', 'PUT', 'PATCH', 'DELETE']);
        }
        \admin_require_csrf();

        $id = $params['id'] ?? '';
        if (!ctype_digit((string) $id)) {
            return $this->json(['success' => false, 'error' => 'Некорректный ID заказа'], 400);
        }

        $jsonData = $request->json();

        // Для PATCH сливаем с существующими данными
        if ($request->method() === 'PATCH') {
            $existing = $this->orders->orderDtoById((int) $id);
            if ($existing === null) {
                return $this->json(['success' => false, 'error' => 'Заказ не найден'], 404);
            }
            // Преобразуем существующий DTO в массив и мержим
            $existingArray = $existing->toApiArray();
            $jsonData = array_merge($existingArray, $jsonData);
        }

        // Создаём DTO из запроса
        $dto = OrderDTO::fromRequest($jsonData, (int) $id);

        if (!$dto->validate()) {
            return $this->json(['success' => false, 'error' => 'Ошибки валидации', 'errors' => $dto->getErrors()], 400);
        }

        try {
            $savedOrder = $this->orders->saveOrderFromDTO($dto);
            return $savedOrder
                ? $this->json(['success' => true, 'order' => $savedOrder])
                : $this->json(['success' => false, 'error' => 'Заказ не найден'], 404);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            error_log('Order save error: ' . $e->getMessage());
            return $this->json(['success' => false, 'error' => 'Ошибка сохранения заказа'], 500);
        }
    }

    public function exportOrders($request)
    {
        if (!$this->authorized()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() !== 'POST') {
            return $this->methodNotAllowed(['POST']);
        }
        \admin_require_csrf();
        $input = $request->json();
        $from = $input['from'] ?? '';
        $to = $input['to'] ?? '';
        if (!$this->validDate($from) || !$this->validDate($to) || $from > $to || $to > date('Y-m-d')) {
            return $this->json(['success' => false, 'error' => 'Укажите корректный диапазон дат'], 400);
        }
        $orders = $this->orders->exportOrders($from, $to);
        $headers = ['ID', 'Клиент', 'Email', 'Телефон', 'Тип проекта', 'Бюджет, руб.', 'Дата заявки', 'Срок', 'Статус'];
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<?mso-application progid="Excel.Sheet"?>' . "\n" .
            '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="Заказы"><Table><Row>';
        foreach ($headers as $header) {
            $xml .= $this->xmlCell($header);
        }
        $xml .= '</Row>';
        foreach ($orders as $order) {
            $xml .= '<Row>' .
                $this->xmlCell($order['id'], 'Number') .
                $this->xmlCell($order['client']) .
                $this->xmlCell($order['email']) .
                $this->xmlCell($order['phone']) .
                $this->xmlCell($order['type']) .
                $this->xmlCell($order['budget'], 'Number') .
                $this->xmlCell($order['createdAt']) .
                $this->xmlCell($order['deadline']) .
                $this->xmlCell($order['status']) .
                '</Row>';
        }
        $xml .= '</Table></Worksheet></Workbook>';
        return new Response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="orders-' . str_replace('-', '', $from) . '-' . str_replace('-', '', $to) . '.xls"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function categoryNavigation($request, array $params = [])
    {
        if (!$this->authorized()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        $categoryId = isset($params['id']) && ctype_digit((string) $params['id']) ? (int) $params['id'] : 0;
        if ($categoryId < 1) {
            return $this->json(['success' => false, 'error' => 'Рубрика не найдена'], 404);
        }
        $category = $this->content->categoryById($categoryId);
        if (!$category) {
            return $this->json(['success' => false, 'error' => 'Рубрика не найдена'], 404);
        }
        if ($request->method() === 'GET') {
            return $this->json(['success' => true, 'items' => $this->content->categoryNavigation($categoryId)]);
        }
        if ($request->method() === 'DELETE') {
            \admin_require_csrf();
            $itemId = isset($params['item_id']) && ctype_digit((string) $params['item_id']) ? (int) $params['item_id'] : 0;
            return $itemId > 0 && $this->content->deleteCategoryNavigation($itemId, $categoryId)
                ? $this->json(['success' => true])
                : $this->json(['success' => false, 'error' => 'Пункт навигации не найден'], 404);
        }
        if ($request->method() === 'PATCH') {
            \admin_require_csrf();
            $data = $request->json();
            $itemIds = isset($data['item_ids']) && is_array($data['item_ids']) ? $data['item_ids'] : [];
            return $this->content->reorderCategoryNavigation($categoryId, $itemIds)
                ? $this->json(['success' => true])
                : $this->json(['success' => false, 'error' => 'Некорректный порядок пунктов'], 400);
        }
        if ($request->method() !== 'POST' && $request->method() !== 'PUT') {
            return $this->methodNotAllowed(['GET', 'POST', 'PUT', 'DELETE']);
        }
        \admin_require_csrf();
        $data = $request->json();
        $title = isset($data['title']) && is_string($data['title']) ? trim($data['title']) : '';
        $url = isset($data['url']) && is_string($data['url']) ? trim($data['url']) : '';
        $icon = isset($data['icon']) && is_string($data['icon']) ? trim($data['icon']) : '';
        $itemId = $request->method() === 'PUT' && isset($params['item_id']) && ctype_digit((string) $params['item_id'])
            ? (int) $params['item_id'] : null;
        if ($title === '' || mb_strlen($title) > 160 || $url === '' || mb_strlen($url) > 255 ||
            !preg_match('#^(?:/|https?://)#i', $url) ||
            ($icon !== '' && !preg_match('/^[a-z0-9-]{2,80}$/', $icon))) {
            return $this->json(['success' => false, 'error' => 'Проверьте название, ссылку и иконку'], 400);
        }
        $saved = $this->content->saveCategoryNavigation($itemId, $categoryId, $title, $url, $icon, !empty($data['is_active']) ? 1 : 0);
        return $saved !== null
            ? $this->json(['success' => true, 'id' => $saved])
            : $this->json(['success' => false, 'error' => 'Пункт навигации не найден'], 404);
    }

    public function categories($request, array $params = [])
    {
        if (!$this->authorized()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() === 'GET') {
            if (($_GET['action'] ?? '') === 'pickers') {
                // Для pickers возвращаем простые массивы (совместимость с фронтендом)
                return $this->json([
                    'success' => true,
                    'pages' => (new \App\Models\LandingPage())->getAll(),
                    'categories' => $this->content->categories(),
                    'articles' => $this->content->articles(),
                ]);
            }
            // Обычный список: возвращаем DTO с articles_count
            $categories = $this->content->categoriesAsDto();
            return $this->json(['success' => true, 'categories' => array_map(fn($c) => $c->toApiArray(), $categories)]);
        }
        if ($request->method() === 'DELETE') {
            \admin_require_csrf();
            return $this->content->deleteCategory($params['id'] ?? '')
                ? $this->json(['success' => true])
                : $this->json(['success' => false, 'error' => 'Рубрика не найдена или содержит статьи'], 404);
        }
        if ($request->method() !== 'POST' && $request->method() !== 'PUT') {
            return $this->methodNotAllowed(['GET', 'POST', 'PUT', 'DELETE']);
        }
        \admin_require_csrf();

        // Новый способ: используем CategoryDTO для валидации
        $jsonData = $request->json();
        $id = $params['id'] ?? null; // ID из URL для PUT
        $dto = CategoryDTO::fromRequest($jsonData, $id);

        if (!$dto->validate()) {
            return $this->json(['success' => false, 'error' => 'Ошибки валидации', 'errors' => $dto->getErrors()], 400);
        }

        try {
            $savedId = $this->content->saveCategoryFromDTO($dto);
            if ($savedId === false) {
                return $this->json(['success' => false, 'error' => 'Рубрика не найдена'], 404);
            }
            // Возвращаем сохранённую категорию с articles_count
            $savedCategory = $this->content->categoryDtoById($savedId);
            return $this->json(['success' => true, 'category' => $savedCategory?->toApiArray()]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            error_log('Category save error: ' . $e->getMessage());
            return $this->json(['success' => false, 'error' => 'Ошибка сохранения рубрики'], 500);
        }
    }

    public function messages($request, array $params = [])
    {
        if (!$this->authorized()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() === 'GET') {
            // Новый API: возвращаем DTO с toApiArray
            $messages = $this->messages->messagesAsDto();
            return $this->json(['success' => true, 'messages' => array_map(fn($m) => $m->toApiArray(), $messages)]);
        }
        \admin_require_csrf();
        $id = $params['id'] ?? '';
        if (!ctype_digit((string) $id)) {
            return $this->json(['success' => false, 'error' => 'Некорректный идентификатор обращения'], 400);
        }
        if ($request->method() === 'DELETE') {
            return $this->messages->deleteMessage((int) $id)
                ? $this->json(['success' => true])
                : $this->json(['success' => false, 'error' => 'Обращение не найдено'], 404);
        }
        if ($request->method() !== 'PATCH') {
            return $this->methodNotAllowed(['GET', 'PATCH', 'DELETE']);
        }
        $jsonData = $request->json();

        // Создаём DTO из запроса (для PATCH обычно приходят только isRead, isApproved)
        $dto = MessageDTO::fromRequest($jsonData, (int) $id);

        // Валидация для обновления статусов — используем validate() который проверяет базовые поля
        // Для PATCH мы хотим обновить только isRead/isApproved, поэтому валидация должна быть мягкой
        // Но MessageDTO::validate() требует name, email, body, type — это не подходит для PATCH
        // Поэтому для PATCH используем прямое обновление через репозиторий (обратная совместимость)
        if (!isset($jsonData['isRead'], $jsonData['isApproved']) ||
            !is_bool($jsonData['isRead']) || !is_bool($jsonData['isApproved'])) {
            return $this->json(['success' => false, 'error' => 'Некорректный статус обращения'], 400);
        }

        return $this->messages->updateMessage((int) $id, $jsonData)
            ? $this->json(['success' => true])
            : $this->json(['success' => false, 'error' => 'Обращение не найдено'], 404);
    }

    /**
     * Ответ автора сайта на комментарий (публикуется сразу, помечается как ответ персонала).
     */
    public function replyToMessage($request, array $params = [])
    {
        if (!$this->authorized()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() !== 'POST') {
            return $this->methodNotAllowed(['POST']);
        }
        \admin_require_csrf();

        $id = $params['id'] ?? '';
        if (!ctype_digit((string) $id) || (int) $id < 1) {
            return $this->json(['success' => false, 'error' => 'Некорректный идентификатор комментария'], 400);
        }

        $jsonData = $request->json();
        $name = isset($jsonData['name']) && is_string($jsonData['name']) ? trim($jsonData['name']) : '';
        $body = isset($jsonData['body']) && is_string($jsonData['body']) ? trim($jsonData['body']) : '';

        if (mb_strlen($name) < 2 || mb_strlen($name) > 160) {
            return $this->json(['success' => false, 'error' => 'Укажите имя автора (от 2 до 160 символов)'], 400);
        }
        if ($body === '' || mb_strlen($body) > 10000) {
            return $this->json(['success' => false, 'error' => 'Введите текст ответа (до 10000 символов)'], 400);
        }

        $result = $this->messages->staffReply((int) $id, $name, $body);
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => $result['error'] ?? 'Не удалось сохранить ответ'], 400);
        }

        return $this->json(['success' => true, 'id' => $result['id']]);
    }

    // === Legacy private methods (для обратной совместимости duplicateArticle/duplicateProject) ===

    private function articleInput(array $data, $id)
    {
        foreach (['slug', 'title', 'description', 'category', 'content', 'author', 'date'] as $field) {
            if (!isset($data[$field]) || !is_string($data[$field])) {
                return null;
            }
        }
        if (!preg_match('/^[a-z0-9-]{1,180}$/', $data['slug']) ||
            trim($data['title']) === '' || trim($data['description']) === '' ||
            trim($data['category']) === '' || trim($data['author']) === '' ||
            !$this->validDate($data['date']) ||
            !isset($data['active']) || !is_bool($data['active'])) {
            return null;
        }
        $tags = $data['tags'] ?? '';
        $cover = $data['cover'] ?? '';
        $icon = $data['icon'] ?? 'bi-file-text';
        if (!is_string($tags) || !is_string($cover) || !is_string($icon)) {
            return null;
        }
        $id = $id ?: ($data['id'] ?? bin2hex(random_bytes(16)));
        if (!is_string($id) || !preg_match('/^[a-f0-9-]{1,64}$/i', $id)) {
            return null;
        }
        return [
            'id' => $id,
            'slug' => $data['slug'],
            'title' => trim($data['title']),
            'description' => trim($data['description']),
            'category' => trim($data['category']),
            'content' => $data['content'],
            'author' => trim($data['author']),
            'date' => $data['date'],
            'active' => $data['active'],
            'tags' => preg_split('/\s+/', trim($tags), -1, PREG_SPLIT_NO_EMPTY),
            'cover' => $cover,
            'icon' => $icon,
        ];
    }

    private function projectInput(array $data, $id)
    {
        foreach (['slug', 'title', 'technologies', 'description'] as $field) {
            if (!isset($data[$field]) || !is_string($data[$field])) {
                return null;
            }
        }
        if (!preg_match('/^[a-z0-9-]{1,180}$/', $data['slug']) ||
            trim($data['title']) === '' || trim($data['technologies']) === '' ||
            trim($data['description']) === '') {
            return null;
        }
        $task = $data['task'] ?? [];
        $results = $data['results'] ?? [];
        $screenshots = $data['screenshots'] ?? [];
        if (is_string($task)) {
            $task = preg_split('/\r\n|\r|\n/', $task, -1, PREG_SPLIT_NO_EMPTY);
        }
        if (!is_array($task) || !is_array($results) || !is_array($screenshots)) {
            return null;
        }
        foreach ($screenshots as $image) {
            if (!is_string($image) || !preg_match(self::SCREENSHOT_PATH_REGEX, $image)) {
                return null;
            }
        }
        $galleryItems = $this->galleryInput($data['gallery'] ?? []);
        $infoItems = $this->projectInfoItemsInput($data['infoItems'] ?? []);
        if ($galleryItems === null || $infoItems === null) {
            return null;
        }
        $id = $id ?: ($data['id'] ?? bin2hex(random_bytes(16)));
        if (!is_string($id) || !preg_match('/^[a-f0-9-]{1,64}$/i', $id)) {
            return null;
        }
        return [
            'id' => $id,
            'slug' => $data['slug'],
            'title' => trim($data['title']),
            'technologies' => trim($data['technologies']),
            'icon' => is_string($data['icon'] ?? null) ? $data['icon'] : 'bi-briefcase',
            'description' => trim($data['description']),
            'task' => array_values(array_filter($task, 'is_string')),
            'solution' => is_string($data['solution'] ?? null) ? $data['solution'] : '',
            'result' => is_string($data['result'] ?? null) ? $data['result'] : '',
            'client' => is_string($data['client'] ?? null) ? $data['client'] : '',
            'year' => ctype_digit((string) ($data['year'] ?? '')) ? (int) $data['year'] : null,
            'duration' => is_string($data['duration'] ?? null) ? $data['duration'] : '',
            'stack' => is_string($data['stack'] ?? null) ? $data['stack'] : '',
            'results' => $results,
            'screenshots' => array_values($screenshots),
            'gallery' => $galleryItems,
            'infoItems' => $infoItems,
            'active' => !isset($data['active']) || $data['active'] !== false,
        ];
    }

    /**
     * Допустимые пути снимков проекта: старая папка проекта (uploads/portfolio/{id}/хэш)
     * и раздел «Портфолио» файлового менеджера с читаемым именем.
     */
    const SCREENSHOT_PATH_REGEX = '#^(?:uploads/portfolio/[A-Za-z0-9_-]+/[a-f0-9]{32}\.(?:jpg|png|webp)|uploads/images/portfolio/[a-z0-9][a-z0-9-]*\.(?:jpg|jpeg|png))$#';

    // Прикреплённые файлы дополнительных полей: ТЗ (documents) и сертификаты (images).
    const DOC_FILE_PATH_REGEX = '#^uploads/documents/portfolio/[a-z0-9][a-z0-9-]*\.(?:doc|docx)$#';
    const CERT_FILE_PATH_REGEX = '#^uploads/images/portfolio/[a-z0-9][a-z0-9-]*\.(?:jpg|jpeg|png)$#';

    /**
     * Снимки с описаниями: список {path, description}. Путь — как у screenshots.
     *
     * @return array<int, array{path: string, description: string}|null>
     */
    private function galleryInput($data)
    {
        if (!is_array($data) || count($data) > 50) {
            return null;
        }
        $items = [];
        foreach ($data as $entry) {
            if (!is_array($entry)) {
                return null;
            }
            $path = $entry['path'] ?? '';
            $description = $entry['description'] ?? '';
            if (!is_string($path) || !preg_match(self::SCREENSHOT_PATH_REGEX, $path) ||
                !is_string($description) || mb_strlen($description) > 500) {
                return null;
            }
            $items[] = ['path' => $path, 'description' => trim($description)];
        }
        return $items;
    }

    /**
     * Дополнительные поля проекта: список {kind, name, value}.
     * kind: link (ссылка), document (документ), certificate (сертификат).
     *
     * @return array<int, array{kind: string, name: string, value: string}>|null
     */
    private function projectInfoItemsInput($data)
    {
        $kinds = ['link', 'document', 'certificate'];
        if (!is_array($data) || count($data) > 30) {
            return null;
        }
        $items = [];
        foreach ($data as $entry) {
            if (!is_array($entry)) {
                return null;
            }
            $kind = $entry['kind'] ?? '';
            $name = $entry['name'] ?? '';
            $value = $entry['value'] ?? '';
            $filePath = $entry['file_path'] ?? '';
            if (!in_array($kind, $kinds, true) || !is_string($name) || trim($name) === '' ||
                mb_strlen($name) > 160 || !is_string($value) || mb_strlen($value) > 500 ||
                !is_string($filePath) || mb_strlen($filePath) > 255) {
                return null;
            }
            // ТЗ и сертификаты: прикреплённый файл только из раздела «Портфолио»
            // файлового менеджера; пустой путь — вывод текстового значения.
            if ($filePath !== '') {
                if ($kind === 'document' && !preg_match(self::DOC_FILE_PATH_REGEX, $filePath)) {
                    return null;
                }
                if ($kind === 'certificate' && !preg_match(self::CERT_FILE_PATH_REGEX, $filePath)) {
                    return null;
                }
                if ($kind === 'link') {
                    $filePath = '';
                }
            }
            $items[] = [
                'kind' => $kind,
                'name' => trim($name),
                'value' => trim($value),
                'file_path' => $filePath,
            ];
        }
        return $items;
    }

    /**
     * Дублировать статью: копия со slug «-copy», снятая с публикации.
     */
    public function duplicateArticle($request, array $params = [])
    {
        if (!$this->authorized()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() !== 'POST') {
            return $this->methodNotAllowed(['POST']);
        }
        \admin_require_csrf();

        $article = $this->content->articleById($params['id'] ?? '');
        if (!$article) {
            return $this->json(['success' => false, 'error' => 'Статья не найдена'], 404);
        }
        $saved = $this->content->saveArticle([
            'id' => bin2hex(random_bytes(16)),
            'slug' => $this->uniqueArticleSlug((string) $article['slug']),
            'title' => $article['title'] . ' (копия)',
            'description' => $article['description'] ?? '',
            'category' => $article['category'] ?? '',
            'content' => $article['content'] ?? '',
            'author' => $article['author'] ?? '',
            'date' => date('Y-m-d'),
            'active' => false,
            'tags' => $article['tags'] ?? [],
            'cover' => $article['cover'] ?? '',
            'icon' => $article['icon'] ?? '',
        ]);
        return $this->json(['success' => true, 'article' => $saved]);
    }

    /**
     * Дублировать проект портфолио: копия со slug «-copy», снятая с публикации.
     */
    public function duplicateProject($request, array $params = [])
    {
        if (!$this->authorized()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() !== 'POST') {
            return $this->methodNotAllowed(['POST']);
        }
        \admin_require_csrf();

        $project = $this->content->projectById($params['id'] ?? '');
        if (!$project) {
            return $this->json(['success' => false, 'error' => 'Проект не найден'], 404);
        }
        $slug = $project['slug'] . '-copy';
        $n = 2;
        while ($this->content->projectBySlug($slug) !== null) {
            $slug = $project['slug'] . '-copy-' . $n++;
        }
        $saved = $this->content->saveProject([
            'id' => bin2hex(random_bytes(16)),
            'slug' => $slug,
            'title' => $project['title'] . ' (копия)',
            'technologies' => $project['technologies'] ?? '',
            'icon' => $project['icon'] ?? 'bi-briefcase',
            'description' => $project['description'] ?? '',
            'task' => $project['task'] ?? [],
            'solution' => $project['solution'] ?? '',
            'result' => $project['result'] ?? '',
            'client' => $project['client'] ?? '',
            'year' => $project['year'] ?? null,
            'duration' => $project['duration'] ?? '',
            'stack' => $project['stack'] ?? '',
            'results' => $project['results'] ?? [],
            'screenshots' => $project['screenshots'] ?? [],
            'gallery' => $project['gallery'] ?? [],
            'infoItems' => $project['infoItems'] ?? [],
            'active' => false,
        ]);
        return $this->json(['success' => true, 'project' => $saved]);
    }

    private function uniqueArticleSlug(string $base): string
    {
        $candidate = $base . '-copy';
        $n = 2;
        while ($this->content->articleBySlug($candidate, false) !== null) {
            $candidate = $base . '-copy-' . $n++;
        }
        return $candidate;
    }

    private function authorized()
    {
        return \admin_is_authenticated();
    }

    private function validDate($value)
    {
        if (!is_string($value)) {
            return false;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = \DateTimeImmutable::getLastErrors();
        return $date !== false && ($errors === false || (!$errors['warning_count'] && !$errors['error_count'])) &&
            $date->format('Y-m-d') === $value;
    }

    private function methodNotAllowed(array $methods)
    {
        return new Response('', 405, ['Allow' => implode(', ', $methods)]);
    }

    private function xmlCell($value, $type = 'String')
    {
        return '<Cell><Data ss:Type="' . $type . '">' .
            htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8') .
            '</Data></Cell>';
    }
}