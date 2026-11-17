<?php

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Models\ContentRepository;

class FormController extends Controller
{
    private $content;

    public function __construct()
    {
        $this->content = new ContentRepository();
    }

    public function order($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }
        $data = $this->fields([
            'name', 'email', 'phone', 'contact_person', 'type', 'budget',
            'deadline_from', 'deadline_to', 'message', 'calculator_details',
        ]);
        if ($data['name'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'error' => 'Заполните имя и укажите корректный email'], 400);
        }
        if ($data['deadline_from'] !== '' && !$this->validDate($data['deadline_from']) ||
            $data['deadline_to'] !== '' && !$this->validDate($data['deadline_to']) ||
            $data['deadline_from'] !== '' && $data['deadline_to'] !== '' &&
            $data['deadline_from'] > $data['deadline_to']) {
            return $this->json(['success' => false, 'error' => 'Укажите корректный срок выполнения'], 400);
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
                return $this->json(['success' => false, 'error' => 'Прикрепите корректный ZIP-архив размером не более 10 МБ'], 400);
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
        $this->sendMail(
            'Новая заявка с сайта',
            "Имя: {$data['name']}\nEmail: {$data['email']}\nТелефон: {$data['phone']}\nТип проекта: {$data['type']}\nБюджет: {$data['budget']}\nОписание:\n{$data['message']}"
        , $data['email'], $data['name']);
        return $this->json(['success' => true, 'orderId' => $order['id'] ?? null]);
    }

    public function feedback($request)
    {
        return $this->message($request, 'feedback');
    }

    public function comment($request)
    {
        return $this->message($request, 'comment');
    }

    private function message($request, $type)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }
        $data = $this->fields(['name', 'email', 'phone', 'subject', 'message', 'article']);
        if ($data['name'] === '' || $data['message'] === '' ||
            !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'error' => 'Заполните обязательные поля и укажите корректный email'], 400);
        }
        $articleSlug = '';
        if ($type === 'comment') {
            $articleSlug = $data['article'];
            if (!preg_match('/^[a-z0-9-]{1,180}$/', $articleSlug)) {
                return $this->json(['success' => false, 'error' => 'Не удалось определить статью для комментария'], 400);
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
        ]);
        $this->sendMail(
            $type === 'comment' ? 'Новый комментарий — ' . $articleSlug : 'Сообщение с сайта',
            "Имя: {$data['name']}\nEmail: {$data['email']}\nТелефон: {$data['phone']}\nТема: {$subject}\n\n{$data['message']}",
            $data['email'],
            $data['name']
        );
        return $this->json(['success' => true]);
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

    private function sendMail($subject, $body, $email, $name)
    {
        $safeName = str_replace(["\r", "\n"], '', $name);
        $safeEmail = str_replace(["\r", "\n"], '', $email);
        $headers = "From: МойБлог <no-reply@moyblog.ru>\r\n" .
            'Reply-To: ' . $safeName . ' <' . $safeEmail . ">\r\n" .
            "Content-Type: text/plain; charset=UTF-8\r\n" .
            "MIME-Version: 1.0\r\n";
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        if (!mail('osvax@yandex.ru', $encodedSubject, $body, $headers)) {
            error_log('Could not send notification email for a saved website form submission.');
        }
    }
}
