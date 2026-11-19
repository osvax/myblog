<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Response;
use App\Models\ContentRepository;

class ContentController extends Controller
{
    private $content;

    public function __construct()
    {
        $this->content = new ContentRepository();
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
        $article = $this->articleInput($request->json(), $params['id'] ?? null);
        if ($article === null) {
            return $this->json(['success' => false, 'error' => 'Проверьте обязательные поля статьи'], 400);
        }
        return $this->json(['success' => true, 'article' => $this->content->saveArticle($article)]);
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
        $project = $this->projectInput($request->json(), $params['id'] ?? null);
        if ($project === null) {
            return $this->json(['success' => false, 'error' => 'Проверьте обязательные поля проекта'], 400);
        }
        return $this->json(['success' => true, 'project' => $this->content->saveProject($project)]);
    }

    public function orders($request, array $params = [])
    {
        if (!$this->authorized()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() === 'GET') {
            return $this->json(['success' => true, 'orders' => $this->content->orderList()]);
        }
        if ($request->method() === 'DELETE') {
            \admin_require_csrf();
            return $this->content->deleteOrder($params['id'] ?? '')
                ? $this->json(['success' => true])
                : $this->json(['success' => false, 'error' => 'Заказ не найден'], 404);
        }
        if ($request->method() !== 'PUT' && $request->method() !== 'PATCH') {
            return $this->methodNotAllowed(['GET', 'PUT', 'PATCH', 'DELETE']);
        }
        \admin_require_csrf();
        $data = $request->json();
        $id = $params['id'] ?? '';
        if ($request->method() === 'PATCH' && ctype_digit((string) $id)) {
            $existing = $this->content->orderById((int) $id);
            if ($existing === null) {
                return $this->json(['success' => false, 'error' => 'Заказ не найден'], 404);
            }
            $data = array_merge($existing, $data);
        }
        if (!ctype_digit((string) $id) || !isset($data['clientName'], $data['email'], $data['budget'], $data['orderDate'], $data['status']) ||
            !is_string($data['clientName']) || trim($data['clientName']) === '' ||
            !is_string($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL) ||
            !is_numeric($data['budget']) || (float) $data['budget'] < 0 ||
            !in_array($data['status'], ['Новый', 'В работе', 'Завершён', 'Отменён'], true) ||
            !$this->validDate($data['orderDate'])) {
            return $this->json(['success' => false, 'error' => 'Проверьте данные заказа'], 400);
        }
        $data = array_merge([
            'phone' => '',
            'projectType' => '',
            'deadline' => '',
            'completedAt' => '',
            'paidAmount' => 0,
        ], $data);
        if ((!is_string($data['deadline']) && $data['deadline'] !== null) ||
            ($data['deadline'] !== '' && $data['deadline'] !== null &&
                (!$this->validDate($data['deadline']) || $data['deadline'] < $data['orderDate'])) ||
            (!is_string($data['completedAt']) && $data['completedAt'] !== null) ||
            ($data['completedAt'] !== '' && $data['completedAt'] !== null &&
                (!$this->validDate($data['completedAt']) || $data['completedAt'] < $data['orderDate'])) ||
            !is_numeric($data['paidAmount']) || (float) $data['paidAmount'] < 0 ||
            (isset($data['phone']) && !is_string($data['phone'])) ||
            (isset($data['projectType']) && !is_string($data['projectType']))) {
            return $this->json(['success' => false, 'error' => 'Проверьте даты и дополнительные данные заказа'], 400);
        }
        $order = $this->content->saveOrder((int) $id, $data);
        return $order
            ? $this->json(['success' => true, 'order' => $order])
            : $this->json(['success' => false, 'error' => 'Заказ не найден'], 404);
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
        $orders = array_values(array_filter($this->content->orderList(), function ($order) use ($from, $to) {
            return $order['createdAt'] >= $from && $order['createdAt'] <= $to;
        }));
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

    public function categories($request, array $params = [])
    {
        if (!$this->authorized()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() === 'GET') {
            return $this->json(['success' => true, 'categories' => $this->content->categories()]);
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
        $data = $request->json();
        $name = $data['name'] ?? '';
        $slug = $data['slug'] ?? '';
        $description = $data['description'] ?? '';
        $id = $params['id'] ?? null;
        if (!is_string($name) || trim($name) === '' || !is_string($slug) ||
            !preg_match('/^[a-z0-9-]{1,140}$/', $slug) || !is_string($description)) {
            return $this->json(['success' => false, 'error' => 'Проверьте название, адрес и описание рубрики'], 400);
        }
        $saved = $this->content->saveCategory($id, trim($name), $slug, trim($description));
        return $saved
            ? $this->json(['success' => true, 'id' => $saved])
            : $this->json(['success' => false, 'error' => 'Рубрика не найдена'], 404);
    }

    public function messages($request, array $params = [])
    {
        if (!$this->authorized()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() === 'GET') {
            return $this->json(['success' => true, 'messages' => $this->content->messages()]);
        }
        \admin_require_csrf();
        $id = $params['id'] ?? '';
        if (!ctype_digit((string) $id)) {
            return $this->json(['success' => false, 'error' => 'Некорректный идентификатор обращения'], 400);
        }
        if ($request->method() === 'DELETE') {
            return $this->content->deleteMessage((int) $id)
                ? $this->json(['success' => true])
                : $this->json(['success' => false, 'error' => 'Обращение не найдено'], 404);
        }
        if ($request->method() !== 'PATCH') {
            return $this->methodNotAllowed(['GET', 'PATCH', 'DELETE']);
        }
        $data = $request->json();
        if (!isset($data['isRead'], $data['isApproved']) ||
            !is_bool($data['isRead']) || !is_bool($data['isApproved'])) {
            return $this->json(['success' => false, 'error' => 'Некорректный статус обращения'], 400);
        }
        return $this->content->updateMessage((int) $id, $data)
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

        $data = $request->json();
        $name = isset($data['name']) && is_string($data['name']) ? trim($data['name']) : '';
        $body = isset($data['body']) && is_string($data['body']) ? trim($data['body']) : '';

        if (mb_strlen($name) < 2 || mb_strlen($name) > 160) {
            return $this->json(['success' => false, 'error' => 'Укажите имя автора (от 2 до 160 символов)'], 400);
        }
        if ($body === '' || mb_strlen($body) > 10000) {
            return $this->json(['success' => false, 'error' => 'Введите текст ответа (до 10000 символов)'], 400);
        }

        $result = $this->content->staffReply((int) $id, $name, $body);
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => $result['error'] ?? 'Не удалось сохранить ответ'], 400);
        }

        return $this->json(['success' => true, 'id' => $result['id']]);
    }

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
