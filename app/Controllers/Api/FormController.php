<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Core\ApiResource;
use App\Models\ContentRepository;

final class FormController extends Controller
{
    private $content;

    public function __construct(ContentRepository $content)
    {
        $this->content = $content;
    }

    /**
     * Проверить rate limit для форм.
     */
    private function checkFormRateLimit(string $formType): ?array
    {
        $limits = [
            'order' => (int) ($_ENV['RATE_LIMIT_ORDER'] ?? 5),
            'feedback' => (int) ($_ENV['RATE_LIMIT_CONTACT'] ?? 5),
            'comment' => (int) ($_ENV['RATE_LIMIT_COMMENT'] ?? 10),
        ];
        $decayMinutes = [
            'order' => 60,
            'feedback' => 60,
            'comment' => 10,
        ];

        $maxAttempts = $limits[$formType] ?? 5;
        $decay = $decayMinutes[$formType] ?? 60;

        $key = \App\Core\RateLimiter::makeKey('form:' . $formType);
        $result = \App\Core\RateLimiter::hit($key, $maxAttempts, $decay);

        if (!$result['allowed']) {
            return [
                'success' => false,
                'error' => 'Слишком много отправок. Попробуйте через ' . $result['retryAfter'] . ' секунд.',
                'retryAfter' => $result['retryAfter'],
            ];
        }

        header('X-RateLimit-Limit: ' . $maxAttempts);
        header('X-RateLimit-Remaining: ' . $result['remaining']);
        if ($result['retryAfter']) {
            header('Retry-After: ' . $result['retryAfter']);
        }

        return null;
    }

    public function order($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        // Rate limiting
        if ($error = $this->checkFormRateLimit('order')) {
            return $this->json(ApiResource::error($error['error'], 429, $error['retryAfter']), 429);
        }

        if (!$this->verifyCsrf($request)) {
            return $this->json(ApiResource::error('Неверный CSRF токен'), 419);
        }
        $data = $this->fields([
            'name', 'email', 'phone', 'contact_person', 'type', 'budget',
            'deadline_from', 'deadline_to', 'message', 'calculator_details',
        ]);
        if ($data['name'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json(ApiResource::error('Заполните имя и укажите корректный email'), 400);
        }
        if ($data['deadline_from'] !== '' && !$this->validDate($data['deadline_from']) ||
            $data['deadline_to'] !== '' && !$this->validDate($data['deadline_to']) ||
            $data['deadline_from'] !== '' && $data['deadline_to'] !== '' &&
            $data['deadline_from'] > $data['deadline_to']) {
            return $this->json(ApiResource::error('Укажите корректный срок выполнения'), 400);
        }
        if (isset($_FILES['attachment']) && is_array($_FILES['attachment']) &&
            ($_FILES['attachment']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $error = $_FILES['attachment']['error'] ?? UPLOAD_ERR_NO_FILE;
            $file = $_FILES['attachment']['tmp_name'] ?? '';
            $size = is_string($file) && is_file($file) ? filesize($file) : false;
            $extension = strtolower(pathinfo($_FILES['attachment']['name'] ?? '', PATHINFO_EXTENSION));
            $handle = is_string($file) && is_file($file) ? fopen($file, 'rb') : false;
            $signature = $handle ? fread($handle, 4) : false;
            if ($handle) {
                fclose($handle);
            }
            if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($file) || $extension !== 'zip' ||
                $size === false || $size < 1 || $size > 10 * 1024 * 1024 ||
                !is_string($signature) || !preg_match('/^PK(?:\x03\x04|\x05\x06|\x07\x08)$/', $signature)) {
                return $this->json(ApiResource::error('Прикрепите корректный ZIP-архив размером не более 10 МБ'), 400);
            }
        }

        $budget = preg_replace('/[^0-9.,]/u', '', $data['budget']);
        $budget = str_replace(',', '.', $budget);
        $numericBudget = is_numeric($budget) ? (float) $budget : 0;
        $order = $this->content->createOrder([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'contactPerson' => $data['contact_person'],
            'type' => $data['type'],
            'budget' => $numericBudget,
            'budgetDisplay' => $data['budget'],
            'deadlineFrom' => $data['deadline_from'],
            'deadlineTo' => $data['deadline_to'],
            'message' => $data['message'],
            'calculatorDetails' => $data['calculator_details'],
        ]);
        // Данные калькулятора для письма заявки: тип, выбранные функции, итог
        $calcPlaceholders = ['calc_type' => '', 'calc_features' => '', 'calc_total' => ''];
        if ($data['calculator_details'] !== '') {
            $details = json_decode($data['calculator_details'], true);
            if (is_array($details)) {
                $calcPlaceholders['calc_type'] = isset($details['type']) && is_string($details['type']) ? $details['type'] : '';
                $currency = isset($details['currency']) && is_string($details['currency']) ? $details['currency'] : '₽';
                $lines = [];
                $features = isset($details['features']) && is_array($details['features']) ? $details['features'] : [];
                foreach ($features as $feature) {
                    if (is_array($feature) && isset($feature['name']) && is_string($feature['name'])) {
                        // Новый формат: имя + цена + срок
                        $price = filter_var($feature['price'] ?? null, FILTER_VALIDATE_INT);
                        $days = filter_var($feature['days'] ?? null, FILTER_VALIDATE_INT);
                        $line = '• ' . $feature['name'];
                        if ($price !== false && $price > 0) {
                            $line .= ' — ' . number_format($price, 0, ',', ' ') . ' ' . $currency;
                            if ($days !== false && $days > 0) {
                                $line .= ' (' . $days . ' дн.)';
                            }
                        }
                        $lines[] = $line;
                    } elseif (is_string($feature) && $feature !== '') {
                        // Старый формат: только имена
                        $lines[] = '• ' . $feature;
                    }
                }
                $calcPlaceholders['calc_features'] = implode("\n", $lines);
                $total = filter_var($details['price'] ?? null, FILTER_VALIDATE_INT);
                $calcPlaceholders['calc_total'] = $total !== false && $total > 0
                    ? number_format($total, 0, ',', ' ') . ' ' . $currency
                    : '';
            }
        }

        $this->sendFormMail('order', array_merge([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'subject' => $data['type'],
            'message' => $data['message'],
            'article' => '',
            'type' => $data['type'],
            'budget' => $data['budget'],
        ], $calcPlaceholders), $data['email'], $data['name']);
        return $this->json(ApiResource::success(['orderId' => $order['id'] ?? null]));
    }

    public function feedback($request)
    {
        if (!$this->verifyCsrf($request)) {
            return $this->json(ApiResource::error('Неверный CSRF токен'), 419);
        }

        // Rate limiting
        if ($error = $this->checkFormRateLimit('feedback')) {
            return $this->json(ApiResource::error($error['error'], 429, $error['retryAfter']), 429);
        }

        return $this->message($request, 'feedback');
    }

    public function comment($request)
    {
        if (!$this->verifyCsrf($request)) {
            return $this->json(ApiResource::error('Неверный CSRF токен'), 419);
        }

        // Rate limiting
        if ($error = $this->checkFormRateLimit('comment')) {
            return $this->json(ApiResource::error($error['error'], 429, $error['retryAfter']), 429);
        }

        return $this->message($request, 'comment');
    }

    private function message($request, $type)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }
        $data = $this->fields(['name', 'email', 'phone', 'subject', 'message', 'article']);

        // Авторизованному пользователю email берём из базы (сессии), а не из формы:
        // поле Email в публичной форме для него скрыто, подменить адрес нельзя
        $user = \current_user();
        if ($user !== null) {
            if ($data['name'] === '') {
                $data['name'] = $user['name'];
            }
            $data['email'] = $user['email'];
        }

        if ($data['name'] === '' || $data['message'] === '' ||
            !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json(ApiResource::error('Заполните обязательные поля и укажите корректный email'), 400);
        }
        $articleSlug = '';
        $parentId = 0;
        if ($type === 'comment') {
            $articleSlug = $data['article'];
            if (!preg_match('/^[a-z0-9-]{1,180}$/', $articleSlug)) {
                return $this->json(ApiResource::error('Не удалось определить статью для комментария'), 400);
            }
            // Ответ конкретному пользователю: родитель — существующий корневой
            // одобренный комментарий той же статьи (вложенность один уровень)
            $parentRaw = isset($_POST['parent']) && is_string($_POST['parent']) ? trim($_POST['parent']) : '';
            if ($parentRaw !== '') {
                if (!ctype_digit($parentRaw) || (int) $parentRaw < 1) {
                    return $this->json(ApiResource::error('Некорректный комментарий для ответа'), 400);
                }
                $parent = $this->content->messageById((int) $parentRaw);
                if (!$parent || $parent['message_type'] !== 'comment' ||
                    $parent['article_slug'] !== $articleSlug || $parent['parent_id'] !== null ||
                    (int) $parent['is_approved'] !== 1) {
                    return $this->json(ApiResource::error('Комментарий для ответа недоступен'), 400);
                }
                $parentId = (int) $parentRaw;
            }
        }
        $subject = $type === 'comment' ? 'Комментарий к статье ' . $articleSlug : $data['subject'];
        $this->content->createMessage([
            'type' => $type,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'subject' => $subject,
            'body' => $data['message'],
            'articleSlug' => $articleSlug,
            'parentId' => $parentId ?: null,
        ]);
        $this->sendFormMail($type === 'comment' ? 'comment' : 'feedback', [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'subject' => $subject,
            'message' => $data['message'],
            'article' => $articleSlug,
            'type' => '',
            'budget' => '',
            'calc_type' => '',
            'calc_features' => '',
            'calc_total' => '',
        ], $data['email'], $data['name']);
        return $this->json(ApiResource::success());
    }

    private function fields(array $names)
    {
        $result = [];
        foreach ($names as $name) {
            $value = isset($_POST[$name]) && is_string($_POST[$name]) ? trim($_POST[$name]) : '';
            $result[$name] = trim(strip_tags($value));
        }
        return $result;
    }

    private function validDate($value)
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = \DateTimeImmutable::getLastErrors();
        return $date !== false && ($errors === false || (!$errors['warning_count'] && !$errors['error_count'])) &&
            $date->format('Y-m-d') === $value;
    }

    /**
     * Отправить письмо по редактируемому макету формы
     * (Настройки сайта → Email письма): тема и тело с подстановками {…}.
     * При пустом макете используется стандартный текст.
     */
    private function sendFormMail(string $formType, array $placeholders, string $email, string $name)
    {
        $title = ucfirst($formType); // Order, Feedback, Comment
        $defaults = [
            'order' => [
                'subject' => 'Новая заявка с сайта',
                'body' => "Имя: {name}\nEmail: {email}\nТелефон: {phone}\nТип проекта: {type}\nБюджет: {budget}\nВыбранные функции:\n{calc_features}\nИтого: {calc_total}\nОписание:\n{message}",
            ],
            'feedback' => [
                'subject' => 'Сообщение с сайта',
                'body' => "Имя: {name}\nEmail: {email}\nТелефон: {phone}\nТема: {subject}\n\n{message}",
            ],
            'comment' => [
                'subject' => 'Новый комментарий — {article}',
                'body' => "Имя: {name}\nEmail: {email}\nТелефон: {phone}\nТема: {subject}\n\n{message}",
            ],
        ];
        $settings = new \App\Models\SiteSettings();
        $subject = trim((string) $settings->get('mail' . $title . 'Subject', ''));
        $body = (string) $settings->get('mail' . $title . 'Body', '');
        if ($subject === '') {
            $subject = $defaults[$formType]['subject'];
        }
        if (trim($body) === '') {
            $body = $defaults[$formType]['body'];
        }

        // Подставляем значения в метки вида {name}, неизвестные метки вырезаем.
        // Ключи strtr должны совпадать с шаблоном целиком (с фигурными скобками),
        // иначе внутри скобок останутся подставленные значения: {Тест}.
        $bracedMap = [];
        foreach ($placeholders as $key => $value) {
            $bracedMap['{' . $key . '}'] = (string) $value;
        }
        $rendered = strtr($subject . "\n" . $body, $bracedMap);
        $rendered = preg_replace('/\{[a-z_]+\}/i', '', $rendered) ?? '';
        [$subject, $body] = explode("\n", $rendered, 2);
        $subject = trim($subject);
        $body = ltrim($body, "\r\n");

        // Ключи флагов «Уведомления» для каждой формы
        $notifyKeys = ['order' => 'notifyOrders', 'feedback' => 'notifyMessages', 'comment' => 'notifyComments'];
        $this->sendMail($subject, $body, $email, $name, $notifyKeys[$formType] ?? '');

        $formLabels = ['order' => 'Заявка', 'feedback' => 'Сообщение', 'comment' => 'Комментарий'];
        \App\Core\Logger::write('forms', ($formLabels[$formType] ?? $formType) . ' от ' . $name . ' (' . $email . ')');
    }

    private function sendMail($subject, $body, $email, $name, string $notifyKey = '')
    {
        $safeName = str_replace(["\r", "\n"], '', $name);
        $safeEmail = str_replace(["\r", "\n"], '', $email);
        // Получатель — из настройки «Email администратора» (fallback osvax@yandex.ru)
        $recipient = trim((string) (new \App\Models\SiteSettings())->get('adminEmail', ''));
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $recipient = 'osvax@yandex.ru';
        }
        // Also send copy to sender if valid email
        $copyToSender = filter_var($safeEmail, FILTER_VALIDATE_EMAIL);

        $htmlBody = nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));

        // Письмо администратору — по флагу «Уведомления» (Настройки сайта)
        if ($notifyKey === '' || (bool) (new \App\Models\SiteSettings())->get($notifyKey, true)) {
            \App\Core\Mailer::send($subject, $body, $recipient, 'Admin', $htmlBody);
        }

        // Копия отправителю — только если адрес отличается от адреса
        // администратора, иначе на одну почту придут два одинаковых письма.
        // Тема — та же, что и в письме администратору (из макета в настройках).
        if ($copyToSender && strcasecmp($safeEmail, $recipient) !== 0) {
            $copySubject = $subject;
            $copyBody = "Это копия вашего сообщения, отправленного через форму на сайте.\n\n" . $body;
            $copyHtmlBody = '<p>Это копия вашего сообщения, отправленного через форму на сайте.</p>' . $htmlBody;
            \App\Core\Mailer::send($copySubject, $copyBody, $safeEmail, $safeName, $copyHtmlBody);
        }
    }

    private function verifyCsrf($request): bool
    {
        // Check _token in POST data or X-CSRF-Token header
        $token = '';
        if ($request->method() === 'POST') {
            $token = $_POST['_token'] ?? '';
        }
        if ($token === '') {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        }
        return \csrf_verify($token);
    }
}
