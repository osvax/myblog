<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\ApiResource;
use App\Models\CustomField;

/**
 * API пользовательских полей: произвольные данные и изображения,
 * используемые в публичных шаблонах через custom_field('ключ').
 */
class CustomFieldController extends Controller
{
    private $customField;

    public function __construct(CustomField $customField)
    {
        $this->customField = $customField;
    }

    /**
     * Список всех полей.
     */
    public function index($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        return $this->json(ApiResource::success(['fields' => $this->customField->all()]));
    }

    /**
     * Создать или обновить поле.
     */
    public function save($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $input = $request->json();
        $id = isset($input['id']) ? (int) $input['id'] : 0;
        $key = isset($input['field_key']) && is_string($input['field_key']) ? trim($input['field_key']) : '';
        $label = isset($input['label']) && is_string($input['label']) ? trim($input['label']) : '';
        $type = isset($input['field_type']) && is_string($input['field_type']) ? trim($input['field_type']) : 'text';
        $value = isset($input['field_value']) && is_string($input['field_value']) ? trim($input['field_value']) : '';
        $group = isset($input['field_group']) && is_string($input['field_group']) ? trim($input['field_group']) : 'general';
        $isActive = isset($input['is_active']) ? (int) $input['is_active'] : 1;
        $displayOrder = isset($input['display_order']) ? (int) $input['display_order'] : 0;

        if (!preg_match('/^[a-z0-9_]{1,100}$/', $key)) {
            return $this->json(ApiResource::error('Ключ: только строчные латинские буквы, цифры и _, до 100 символов'), 400);
        }
        if (mb_strlen($label) < 1 || mb_strlen($label) > 200) {
            return $this->json(ApiResource::error('Укажите название поля (до 200 символов)'), 400);
        }
        if (!in_array($type, ['text', 'textarea', 'number', 'url', 'image'], true)) {
            return $this->json(ApiResource::error('Неверный тип поля'), 400);
        }
        if ($type === 'number' && $value !== '' && !is_numeric($value)) {
            return $this->json(ApiResource::error('Для числового поля нужно число'), 400);
        }
        if ($type === 'url' && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            return $this->json(ApiResource::error('Укажите корректный URL'), 400);
        }
        if (mb_strlen($group) > 100) {
            return $this->json(ApiResource::error('Слишком длинное имя группы'), 400);
        }

        $result = $this->customField->save($id, $key, $label, $type, $value, $group, $isActive ? 1 : 0, $displayOrder);
        if (!$result['success']) {
            return $this->json(ApiResource::error($result['error'] ?? 'Не удалось сохранить'), 400);
        }

        return $this->json(ApiResource::success(['id' => $result['id'], 'action' => $result['action']]));
    }

    /**
     * Загрузить изображение для поля.
     */
    public function uploadImage($request, $parameters)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(ApiResource::error('Неверный ID поля'), 400);
        }

        $fieldRow = $this->findFieldById($this->customField, $id);
        if ($fieldRow === null) {
            return $this->json(ApiResource::error('Поле не найдено'), 404);
        }

        if (!isset($_FILES['image']) || !is_array($_FILES['image']) ||
            !is_int($_FILES['image']['error'] ?? null) || !is_string($_FILES['image']['tmp_name'] ?? null)) {
            return $this->json(ApiResource::error('Выберите изображение'), 400);
        }

        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->json(ApiResource::error('Не удалось принять файл'), 400);
        }
        if (!is_uploaded_file($file['tmp_name']) || $file['size'] < 1 || $file['size'] > 10 * 1024 * 1024) {
            return $this->json(ApiResource::error('Изображение должно быть не больше 10 МБ'), 400);
        }
        if (!extension_loaded('fileinfo') || !function_exists('getimagesize')) {
            return $this->json(ApiResource::error('На сервере не настроена проверка изображений'), 500);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $imageInfo = @getimagesize($file['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!is_string($mime) || !isset($extensions[$mime]) ||
            $imageInfo === false || $imageInfo['mime'] !== $mime) {
            return $this->json(ApiResource::error('Разрешены только изображения JPEG, PNG и WebP'), 400);
        }

        $width = (int) $imageInfo[0];
        $height = (int) $imageInfo[1];
        if ($width < 1 || $height < 1 || $width > 8000 || $height > 8000 || $width * $height > 40000000) {
            return $this->json(ApiResource::error('Слишком большой размер изображения'), 400);
        }

        // Храним в закрытой по умолчанию папке uploads/custom с разрешением чтения картинок
        $root = APP_ROOT . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
        $directory = $root . DIRECTORY_SEPARATOR . 'custom';
        if (is_link($root) || is_link($directory)) {
            return $this->json(ApiResource::error('Некорректная папка для загрузки'), 500);
        }
        foreach ([$root, $directory] as $path) {
            if (!is_dir($path) && !mkdir($path, 0755) && !is_dir($path)) {
                return $this->json(ApiResource::error('Не удалось создать папку для изображений'), 500);
            }
        }
        $realRoot = realpath($root);
        $realDirectory = realpath($directory);
        if ($realRoot === false || $realDirectory === false || dirname($realDirectory) !== $realRoot) {
            return $this->json(ApiResource::error('Некорректная папка для загрузки'), 500);
        }

        try {
            $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        } catch (\Throwable $error) {
            return $this->json(ApiResource::error('Не удалось подготовить имя файла'), 500);
        }
        if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
            return $this->json(ApiResource::error('Не удалось сохранить изображение'), 500);
        }

        // Удаляем предыдущий файл, если он был из той же папки
        $oldValue = (string) ($fieldRow['field_value'] ?? '');
        if (preg_match('#^/uploads/custom/([a-f0-9]{32}\.(?:jpg|png|webp))$#', $oldValue, $match) &&
            is_file($directory . DIRECTORY_SEPARATOR . $match[1])) {
            @unlink($directory . DIRECTORY_SEPARATOR . $match[1]);
        }

        $url = '/uploads/custom/' . $filename;
        $this->customField->save(
            $id,
            (string) $fieldRow['field_key'],
            (string) $fieldRow['label'],
            'image',
            $url,
            (string) $fieldRow['field_group'],
            (int) $fieldRow['is_active'],
            (int) $fieldRow['display_order']
        );

        return $this->json(ApiResource::success(['url' => $url]));
    }

    /**
     * Удалить поле.
     */
    public function delete($request, $parameters)
    {
        if ($request->method() !== 'DELETE') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(ApiResource::error('Неверный ID поля'), 400);
        }

        $result = $this->customField->delete($id);
        if (!$result['success']) {
            return $this->json(ApiResource::error('Поле не найдено'), 404);
        }

        return $this->json(ApiResource::success());
    }

    /**
     * Переключить активность поля.
     */
    public function toggleActive($request, $parameters)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(ApiResource::error('Неверный ID поля'), 400);
        }

        $result = $this->customField->toggleActive($id);
        if (!$result['success']) {
            return $this->json(ApiResource::error('Поле не найдено'), 404);
        }

        return $this->json(ApiResource::success());
    }

    /**
     * Найти поле по id.
     */
    private function findFieldById(CustomField $model, int $id): ?array
    {
        foreach ($model->all() as $row) {
            if ((int) $row['id'] === $id) {
                return $row;
            }
        }
        return null;
    }
}
