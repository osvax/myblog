<?php

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Models\Consultant;
use PDO;

class ConsultantController extends Controller
{
    public function handle($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Метод не поддерживается.'], 405);
        }

        $payload = $request->json();
        if (!$payload) {
            $payload = $_POST;
        }
        $action = isset($payload['action']) && is_string($payload['action']) ? $payload['action'] : '';
        $adminAction = in_array($action, ['getState', 'getTyping', 'getAdminSettings', 'saveSettings', 'deleteAnswered', 'closeThread'], true) ||
            (in_array($action, ['markRead', 'setTyping'], true) && ($payload['role'] ?? '') === 'admin') ||
            ($action === 'sendMessage' && ($payload['role'] ?? '') === 'admin');

        if ($adminAction && !\admin_is_authenticated()) {
            return $this->json(['success' => false, 'message' => 'Требуется авторизация.'], 401);
        }
        if ($adminAction && !in_array($action, ['getState', 'getTyping'], true)) {
            \admin_require_csrf();
        }

        try {
            $pdo = Consultant::connection();
        } catch (\Throwable $error) {
            error_log('Ошибка подключения к базе консультанта: ' . $error->getMessage());
            return $this->json(['success' => false, 'message' => 'Не удалось подключиться к базе консультанта.'], 500);
        }
        $settings = $this->settings($pdo);

        switch ($action) {
            case 'getSettings':
                return $this->json(['success' => true, 'settings' => array_intersect_key($settings, array_flip([
                    'enabled', 'buttonColor', 'buttonTextColor', 'buttonPosition', 'workingHours', 'welcomeMessage',
                ]))]);

            case 'getState':
                return $this->getState($pdo);

            case 'getTyping':
                return $this->getTyping($pdo, $payload);

            case 'getAdminSettings':
                return $this->json(['success' => true, 'settings' => $settings]);

            case 'saveSettings':
                return $this->saveSettings($pdo, $payload);

            case 'deleteAnswered':
                return $this->deleteAnswered($pdo);

            case 'closeThread':
                return $this->closeThread($pdo, $payload);

            case 'markRead':
                return $this->markRead($pdo, $payload);

            case 'setTyping':
                return $this->setTyping($pdo, $payload);

            case 'createThread':
                return $this->createThread($pdo, $settings, $payload);

            case 'getThread':
            case 'sendMessage':
                return $this->thread($pdo, $settings, $action, $payload);

            default:
                return $this->json(['success' => false, 'message' => 'Неизвестное действие.'], 400);
        }
    }

    private function getState(PDO $pdo)
    {
        $users = $pdo->query("SELECT u.id, u.name, u.phone, u.source, u.status, u.created_at, u.updated_at,
                (SELECT m.message FROM consultant_messages m WHERE m.user_id = u.id ORDER BY m.id DESC LIMIT 1) AS lastMessage,
                (SELECT m.id FROM consultant_messages m WHERE m.user_id = u.id ORDER BY m.id DESC LIMIT 1) AS lastMessageId,
                (SELECT m.role FROM consultant_messages m WHERE m.user_id = u.id ORDER BY m.id DESC LIMIT 1) AS lastMessageRole,
                (SELECT m.id FROM consultant_messages m WHERE m.user_id = u.id AND m.role = 'user' ORDER BY m.id DESC LIMIT 1) AS lastUserMessageId,
                (SELECT r.viewed_message_id FROM consultant_message_reads r WHERE r.user_id = u.id AND r.reader_role = 'admin') AS viewedByAdminMessageId,
                (SELECT r.read_message_id FROM consultant_message_reads r WHERE r.user_id = u.id AND r.reader_role = 'admin') AS readByAdminMessageId,
                (SELECT r.viewed_message_id FROM consultant_message_reads r WHERE r.user_id = u.id AND r.reader_role = 'user') AS viewedByUserMessageId,
                (SELECT r.read_message_id FROM consultant_message_reads r WHERE r.user_id = u.id AND r.reader_role = 'user') AS readByUserMessageId,
                (SELECT t.role FROM consultant_typing t WHERE t.user_id = u.id AND t.role != 'admin' AND t.updated_at >= UTC_TIMESTAMP() - INTERVAL 5 SECOND LIMIT 1) AS typingRole
            FROM consultant_users u ORDER BY u.updated_at DESC LIMIT 200")->fetchAll();
        $messageQuery = $pdo->query("SELECT m.id, m.user_id AS userId, m.role, m.message AS text, m.created_at AS createdAt
            FROM consultant_messages m
            JOIN consultant_users u ON u.id = m.user_id
            ORDER BY u.updated_at DESC, m.id ASC");
        $messages = [];
        foreach ($messageQuery as $message) {
            $message['createdAt'] = $this->isoDate($message['createdAt']);
            $messages[$message['userId']][] = [
                'id' => $message['id'],
                'role' => $message['role'],
                'text' => $message['text'],
                'createdAt' => $message['createdAt'],
            ];
        }
        foreach ($users as &$user) {
            $user['createdAt'] = $this->isoDate($user['created_at']);
            $user['updatedAt'] = $this->isoDate($user['updated_at']);
            unset($user['created_at'], $user['updated_at']);
        }
        unset($user);
        return $this->json(['success' => true, 'data' => ['users' => $users, 'messages' => $messages]]);
    }

    private function getTyping(PDO $pdo, array $payload)
    {
        $userId = $this->textValue($payload, 'userId');
        $readerRole = $this->textValue($payload, 'role');
        if (!$this->validUserId($userId) || !in_array($readerRole, ['admin', 'user'], true)) {
            return $this->json(['success' => false, 'message' => 'Не удалось проверить чат.'], 400);
        }
        if ($readerRole === 'user' && !$this->validToken($pdo, $userId, $this->textValue($payload, 'token'))) {
            return $this->json(['success' => false, 'message' => 'Чат не найден.'], 404);
        }
        $query = $pdo->prepare('SELECT role FROM consultant_typing
            WHERE user_id = ? AND role != ? AND updated_at >= UTC_TIMESTAMP() - INTERVAL 5 SECOND LIMIT 1');
        $query->execute([$userId, $readerRole]);
        $typingRole = $query->fetchColumn();
        return $this->json(['success' => true, 'typingRole' => $typingRole === false ? null : $typingRole]);
    }

    private function saveSettings(PDO $pdo, array $payload)
    {
        $incoming = isset($payload['settings']) && is_array($payload['settings']) ? $payload['settings'] : [];
        $settings = array_intersect_key($incoming, $this->defaultSettings());
        foreach (['buttonColor', 'buttonTextColor'] as $color) {
            if (isset($settings[$color]) && (!is_string($settings[$color]) ||
                !preg_match('/^#[0-9a-f]{6}$/i', $settings[$color]))) {
                return $this->json(['success' => false, 'message' => $color === 'buttonColor'
                    ? 'Указан неверный цвет кнопки.'
                    : 'Указан неверный цвет текста кнопки.'], 400);
            }
        }
        if (isset($settings['buttonPosition']) &&
            !in_array($settings['buttonPosition'], ['left', 'right'], true)) {
            return $this->json(['success' => false, 'message' => 'Указано неверное расположение кнопки.'], 400);
        }
        if (isset($settings['notificationEmail']) &&
            (!is_string($settings['notificationEmail']) ||
                ($settings['notificationEmail'] !== '' && !filter_var($settings['notificationEmail'], FILTER_VALIDATE_EMAIL)))) {
            return $this->json(['success' => false, 'message' => 'Указан неверный email для уведомлений.'], 400);
        }
        if (isset($settings['enabled'])) {
            $settings['enabled'] = filter_var($settings['enabled'], FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }
        $statement = $pdo->prepare('INSERT INTO consultant_settings (setting_key, setting_value) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        foreach ($settings as $key => $value) {
            if (!is_scalar($value)) {
                return $this->json(['success' => false, 'message' => 'Некорректное значение настройки.'], 400);
            }
            $statement->execute([$key, (string) $value]);
        }
        return $this->json(['success' => true]);
    }

    private function deleteAnswered(PDO $pdo)
    {
        try {
            $pdo->beginTransaction();
            $delete = $pdo->prepare("DELETE FROM consultant_users WHERE status = 'answered'");
            $delete->execute();
            $deletedCount = $delete->rowCount();
            $pdo->commit();
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Не удалось удалить отвеченные обращения консультанта: ' . $error->getMessage());
            return $this->json(['success' => false, 'message' => 'Не удалось удалить отвеченные обращения.'], 500);
        }
        return $this->json(['success' => true, 'deletedCount' => $deletedCount]);
    }

    private function closeThread(PDO $pdo, array $payload)
    {
        $userId = $this->textValue($payload, 'userId');
        if (!$this->validUserId($userId)) {
            return $this->json(['success' => false, 'message' => 'Не удалось проверить чат.'], 400);
        }
        $close = $pdo->prepare("UPDATE consultant_users SET status = 'closed', updated_at = ? WHERE id = ? AND status != 'closed'");
        $close->execute([gmdate('Y-m-d H:i:s'), $userId]);
        if ($close->rowCount() === 0) {
            $exists = $pdo->prepare('SELECT status FROM consultant_users WHERE id = ?');
            $exists->execute([$userId]);
            if ($exists->fetchColumn() === false) {
                return $this->json(['success' => false, 'message' => 'Чат не найден.'], 404);
            }
        }
        return $this->json(['success' => true]);
    }

    private function markRead(PDO $pdo, array $payload)
    {
        $userId = $this->textValue($payload, 'userId');
        $readerRole = $this->textValue($payload, 'role');
        $level = $this->textValue($payload, 'level');
        if (!$this->validUserId($userId)) {
            return $this->json(['success' => false, 'message' => 'Не удалось проверить чат.'], 400);
        }
        if (!in_array($readerRole, ['admin', 'user'], true) || !in_array($level, ['viewed', 'read'], true)) {
            return $this->json(['success' => false, 'message' => 'Некорректный статус сообщения.'], 400);
        }
        if ($readerRole === 'user' && !$this->validToken($pdo, $userId, $this->textValue($payload, 'token'))) {
            return $this->json(['success' => false, 'message' => 'Чат не найден.'], 404);
        }
        $messageRole = $readerRole === 'admin' ? 'user' : 'admin';
        $query = $pdo->prepare('SELECT id FROM consultant_messages WHERE user_id = ? AND role = ? ORDER BY id DESC LIMIT 1');
        $query->execute([$userId, $messageRole]);
        $messageId = $query->fetchColumn();
        if ($messageId === false) {
            return $this->json(['success' => true]);
        }
        $viewedValue = $level === 'viewed' ? $messageId : null;
        $readValue = $level === 'read' ? $messageId : null;
        // Сохраняем максимальный ID, чтобы более старая вкладка не откатила отметку о прочтении.
        $statement = $pdo->prepare('INSERT INTO consultant_message_reads
                (user_id, reader_role, viewed_message_id, read_message_id, updated_at)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                viewed_message_id = CASE WHEN VALUES(viewed_message_id) IS NULL THEN viewed_message_id
                    ELSE GREATEST(COALESCE(viewed_message_id, 0), VALUES(viewed_message_id)) END,
                read_message_id = CASE WHEN VALUES(read_message_id) IS NULL THEN read_message_id
                    ELSE GREATEST(COALESCE(read_message_id, 0), VALUES(read_message_id)) END,
                updated_at = VALUES(updated_at)');
        $statement->execute([
            $userId,
            $readerRole,
            $level === 'read' ? $messageId : $viewedValue,
            $readValue,
            gmdate('Y-m-d H:i:s'),
        ]);
        return $this->json(['success' => true, 'messageId' => (string) $messageId]);
    }

    private function setTyping(PDO $pdo, array $payload)
    {
        $userId = $this->textValue($payload, 'userId');
        $role = $this->textValue($payload, 'role');
        $isTyping = !empty($payload['isTyping']);
        if (!$this->validUserId($userId) || !in_array($role, ['admin', 'user'], true)) {
            return $this->json(['success' => false, 'message' => 'Не удалось проверить чат.'], 400);
        }
        if ($role === 'user') {
            $query = $pdo->prepare('SELECT token_hash, status FROM consultant_users WHERE id = ?');
            $query->execute([$userId]);
            $user = $query->fetch();
            if (!$user || !$this->matchesToken($user['token_hash'], $this->textValue($payload, 'token'))) {
                return $this->json(['success' => false, 'message' => 'Чат не найден.'], 404);
            }
            if ($isTyping && $user['status'] === 'closed') {
                return $this->json(['success' => false, 'message' => 'Этот чат закрыт.'], 409);
            }
        }
        if ($isTyping) {
            $query = $pdo->prepare('INSERT INTO consultant_typing (user_id, role, updated_at) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at)');
            $query->execute([$userId, $role, gmdate('Y-m-d H:i:s')]);
        } else {
            $query = $pdo->prepare('DELETE FROM consultant_typing WHERE user_id = ? AND role = ?');
            $query->execute([$userId, $role]);
        }
        return $this->json(['success' => true]);
    }

    private function createThread(PDO $pdo, array $settings, array $payload)
    {
        if (!$settings['enabled']) {
            return $this->json(['success' => false, 'message' => 'Онлайн-консультант временно отключён.'], 403);
        }
        $name = $this->normalizeText($payload['name'] ?? '', 100);
        $phone = $this->normalizeText($payload['phone'] ?? '', 40);
        $question = $this->normalizeText($payload['question'] ?? '', 2000);
        if ($name === '' || $phone === '' || $question === '') {
            return $this->json(['success' => false, 'message' => 'Укажите имя, телефон и вопрос.'], 400);
        }
        if (!preg_match('/^[0-9+() .-]{7,40}$/', $phone)) {
            return $this->json(['success' => false, 'message' => 'Проверьте формат телефона.'], 400);
        }
        try {
            $id = bin2hex(random_bytes(16));
            $token = bin2hex(random_bytes(32));
            $now = gmdate('Y-m-d H:i:s');
            $question = $this->censor($question, $settings['censoredWords']);
            $pdo->beginTransaction();
            $userInsert = $pdo->prepare('INSERT INTO consultant_users (id, token_hash, name, phone, source, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $userInsert->execute([
                $id, hash('sha256', $token), $name, $phone, $this->normalizeText($payload['source'] ?? '', 500),
                'new', $now, $now,
            ]);
            $messageInsert = $pdo->prepare('INSERT INTO consultant_messages (user_id, role, message, created_at) VALUES (?, ?, ?, ?)');
            $messageInsert->execute([$id, 'user', $question, $now]);
            $pdo->commit();
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Не удалось создать обращение консультанту: ' . $error->getMessage());
            return $this->json(['success' => false, 'message' => 'Не удалось сохранить обращение.'], 500);
        }
        $this->notifyAdmin($settings['notificationEmail'], $name, $phone, $question);
        return $this->json([
            'success' => true,
            'userId' => $id,
            'token' => $token,
            'messages' => [['role' => 'user', 'text' => $question, 'createdAt' => gmdate(DATE_ATOM)]],
        ]);
    }

    private function thread(PDO $pdo, array $settings, $action, array $payload)
    {
        $userId = $this->textValue($payload, 'userId');
        $role = $action === 'sendMessage' && ($payload['role'] ?? '') === 'admin' ? 'admin' : 'user';
        if (!$this->validUserId($userId)) {
            return $this->json(['success' => false, 'message' => 'Не удалось проверить чат.'], 400);
        }
        $query = $pdo->prepare('SELECT id, token_hash, status FROM consultant_users WHERE id = ?');
        $query->execute([$userId]);
        $user = $query->fetch();
        $token = $this->textValue($payload, 'token');
        if (!$user || ($role !== 'admin' && !$this->matchesToken($user['token_hash'], $token))) {
            return $this->json(['success' => false, 'message' => 'Чат не найден.'], 404);
        }
        if ($action === 'sendMessage' && $user['status'] === 'closed') {
            return $this->json(['success' => false, 'message' => 'Этот чат закрыт, отправка сообщений недоступна.'], 409);
        }
        if ($action === 'getThread') {
            return $this->getThread($pdo, $userId, $user['status']);
        }
        $message = $this->normalizeText($payload['message'] ?? '', 2000);
        if ($message === '') {
            return $this->json(['success' => false, 'message' => 'Сообщение не может быть пустым.'], 400);
        }
        $message = $this->censor($message, $settings['censoredWords']);
        $now = gmdate('Y-m-d H:i:s');
        try {
            $pdo->beginTransaction();
            $insert = $pdo->prepare('INSERT INTO consultant_messages (user_id, role, message, created_at) VALUES (?, ?, ?, ?)');
            $insert->execute([$userId, $role, $message, $now]);
            $update = $pdo->prepare('UPDATE consultant_users SET updated_at = ?, status = ? WHERE id = ?');
            $update->execute([$now, $role === 'admin' ? 'answered' : 'new', $userId]);
            $pdo->commit();
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Не удалось сохранить сообщение консультанта: ' . $error->getMessage());
            return $this->json(['success' => false, 'message' => 'Не удалось сохранить сообщение.'], 500);
        }
        return $this->json(['success' => true, 'message' => [
            'role' => $role,
            'text' => $message,
            'createdAt' => gmdate(DATE_ATOM),
        ]]);
    }

    private function getThread(PDO $pdo, $userId, $status)
    {
        $query = $pdo->prepare('SELECT id, role, message AS text, created_at AS createdAt
            FROM consultant_messages WHERE user_id = ? ORDER BY id ASC');
        $query->execute([$userId]);
        $messages = $query->fetchAll();
        foreach ($messages as &$message) {
            $message['createdAt'] = $this->isoDate($message['createdAt']);
        }
        unset($message);
        $query = $pdo->prepare('SELECT reader_role, viewed_message_id, read_message_id FROM consultant_message_reads WHERE user_id = ?');
        $query->execute([$userId]);
        $readStatus = ['admin' => ['viewed' => null, 'read' => null], 'user' => ['viewed' => null, 'read' => null]];
        foreach ($query->fetchAll() as $row) {
            $readStatus[$row['reader_role']] = [
                'viewed' => $row['viewed_message_id'] === null ? null : (string) $row['viewed_message_id'],
                'read' => $row['read_message_id'] === null ? null : (string) $row['read_message_id'],
            ];
        }
        $query = $pdo->prepare("SELECT role FROM consultant_typing
            WHERE user_id = ? AND role = 'admin' AND updated_at >= UTC_TIMESTAMP() - INTERVAL 5 SECOND LIMIT 1");
        $query->execute([$userId]);
        $typingRole = $query->fetchColumn();
        return $this->json([
            'success' => true,
            'messages' => $messages,
            'readStatus' => $readStatus,
            'typingRole' => $typingRole === false ? null : $typingRole,
            'status' => $status,
        ]);
    }

    private function settings(PDO $pdo)
    {
        $settings = $this->defaultSettings();
        $rows = $pdo->query('SELECT setting_key, setting_value FROM consultant_settings')->fetchAll();
        foreach ($rows as $row) {
            if (!array_key_exists($row['setting_key'], $settings)) {
                continue;
            }
            $settings[$row['setting_key']] = $row['setting_key'] === 'enabled'
                ? $row['setting_value'] === '1'
                : $row['setting_value'];
        }
        return $settings;
    }

    private function defaultSettings()
    {
        return [
            'enabled' => true,
            'buttonColor' => '#ff8c1a',
            'buttonTextColor' => '#ffffff',
            'buttonPosition' => 'right',
            'workingHours' => '09:00-20:00',
            'censoredWords' => '',
            'welcomeMessage' => 'Здравствуйте! Чем могу помочь?',
            'notificationEmail' => '',
        ];
    }

    private function normalizeText($value, $maxLength)
    {
        if (!is_string($value)) {
            return '';
        }
        $value = preg_replace('/\s+/u', ' ', trim($value));
        if (!is_string($value)) {
            return '';
        }
        return function_exists('mb_substr')
            ? mb_substr($value, 0, $maxLength, 'UTF-8')
            : substr($value, 0, $maxLength);
    }

    private function censor($text, $words)
    {
        foreach (array_filter(array_map('trim', explode(',', (string) $words))) as $word) {
            $text = preg_replace('/' . preg_quote($word, '/') . '/iu', '***', $text);
        }
        return $text;
    }

    private function isoDate($value)
    {
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value, new \DateTimeZone('UTC'));
        return $date ? $date->format(DATE_ATOM) : $value;
    }

    private function validUserId($userId)
    {
        return is_string($userId) && (bool) preg_match('/^[a-f0-9]{32}$/', $userId);
    }

    private function validToken(PDO $pdo, $userId, $token)
    {
        $query = $pdo->prepare('SELECT token_hash FROM consultant_users WHERE id = ?');
        $query->execute([$userId]);
        $hash = $query->fetchColumn();
        return $hash !== false && $this->matchesToken($hash, $token);
    }

    private function matchesToken($hash, $token)
    {
        return is_string($hash) && is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token) &&
            hash_equals($hash, hash('sha256', $token));
    }

    private function textValue(array $payload, $key)
    {
        return isset($payload[$key]) && is_string($payload[$key]) ? $payload[$key] : '';
    }

    private function notifyAdmin($email, $name, $phone, $question)
    {
        if ($email === '') {
            return;
        }
        $subject = 'Новый вопрос онлайн-консультанту';
        $body = "Новый вопрос на сайте\nИмя: {$name}\nТелефон: {$phone}\n\nВопрос:\n{$question}\n";
        $headers = "From: МойБлог <no-reply@moyblog.ru>\r\n" .
            "Content-Type: text/plain; charset=UTF-8\r\n" .
            "MIME-Version: 1.0\r\n";
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        if (!mail($email, $encodedSubject, $body, $headers)) {
            error_log('Не удалось отправить уведомление о новом обращении консультанту.');
        }
    }
}
