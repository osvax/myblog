<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\SiteSettings;

class SiteSettingsController extends Controller
{
    /**
     * Получить все настройки.
     */
    public function index($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $settings = (new SiteSettings())->all();
        $grouped = (new SiteSettings())->grouped();

        return $this->json([
            'success' => true,
            'settings' => $settings,
            'grouped' => $grouped,
        ]);
    }

    /**
     * Сохранить все настройки.
     */
    public function save($request)
    {
        if ($request->method() !== 'PUT') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $input = $request->json();
        if (empty($input)) {
            return $this->json(['success' => false, 'error' => 'Нет данных'], 400);
        }

        $result = (new \App\Models\SiteSettings())->saveAllWithType($input);
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => $result['error'] ?? 'Не удалось сохранить'], 500);
        }

        return $this->json(['success' => true]);
    }

    /**
     * Добавить пользовательское поле.
     */
    public function addField($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $input = $request->json();
        $key = isset($input['key']) && is_string($input['key']) ? trim($input['key']) : '';
        $value = isset($input['value']) && is_string($input['value']) ? $input['value'] : '';
        $type = isset($input['type']) && is_string($input['type']) ? $input['type'] : 'text';
        $tabGroup = isset($input['tab_group']) && is_string($input['tab_group']) ? $input['tab_group'] : 'general';
        $placeholder = isset($input['placeholder']) && is_string($input['placeholder']) ? $input['placeholder'] : '';

        if (strlen($key) < 1 || strlen($key) > 100) {
            return $this->json(['success' => false, 'error' => 'Неверное имя поля'], 400);
        }
        if (!in_array($type, ['text', 'textarea', 'number', 'email', 'tel', 'url', 'color', 'checkbox', 'select'], true)) {
            return $this->json(['success' => false, 'error' => 'Неверный тип поля'], 400);
        }

        $result = (new SiteSettings())->addCustomField($key, $value, $type, $tabGroup, $placeholder);
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => $result['error'] ?? 'Не удалось добавить'], 500);
        }

        return $this->json(['success' => true, 'id' => $result['id']]);
    }

    /**
     * Удалить пользовательское поле.
     */
    public function deleteField($request, $parameters)
    {
        if ($request->method() !== 'DELETE') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $key = isset($parameters['key']) && is_string($parameters['key']) ? $parameters['key'] : '';
        if (empty($key)) {
            return $this->json(['success' => false, 'error' => 'Не указано имя поля'], 400);
        }

        $result = (new SiteSettings())->deleteCustomField($key);
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => $result['error'] ?? 'Не удалось удалить'], 404);
        }

        return $this->json(['success' => true]);
    }

    /**
     * Тест отправки email через SMTP.
     */
    public function testEmail($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $result = \App\Core\Mailer::testConnection();
        return $this->json($result);
    }

    /**
     * Загрузить файл шрифта (вкладка «Оформление»): woff/woff2/ttf/otf
     * до 2 МБ. Файл кладётся в /uploads/fonts/, путь — в настройку
     * themeFontFile (лейауты генерируют из него @font-face).
     */
    public function font($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $file = $_FILES['font_file'] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return $this->json(['success' => false, 'error' => 'Не передан файл шрифта'], 400);
        }
        if ((int) $file['size'] < 1 || (int) $file['size'] > 2 * 1024 * 1024) {
            return $this->json(['success' => false, 'error' => 'Размер файла шрифта должен быть от 1 байта до 2 МБ'], 400);
        }
        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['woff', 'woff2', 'ttf', 'otf'], true)) {
            return $this->json(['success' => false, 'error' => 'Допустимые форматы шрифтов: woff, woff2, ttf, otf'], 400);
        }

        $targetDir = APP_ROOT . '/public/uploads/fonts';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            return $this->json(['success' => false, 'error' => 'Не удалось создать каталог для шрифтов'], 500);
        }

        $oldPath = trim((string) (new SiteSettings())->get('themeFontFile', ''));
        $newName = md5(uniqid('', true)) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $targetDir . '/' . $newName)) {
            return $this->json(['success' => false, 'error' => 'Не удалось сохранить файл шрифта'], 500);
        }

        (new SiteSettings())->saveAllWithType(['themeFontFile' => 'uploads/fonts/' . $newName]);

        // Удаляем прежний файл (только если он лежит в uploads/fonts/)
        if ($oldPath !== '' && preg_match('#^uploads/fonts/[A-Za-z0-9._-]+\.(woff|woff2|ttf|otf)$#i', $oldPath)) {
            $oldFile = APP_ROOT . '/public/' . $oldPath;
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        \App\Core\Logger::write('security', 'Загружен файл шрифта: ' . $newName);
        return $this->json(['success' => true, 'path' => 'uploads/fonts/' . $newName, 'file' => $newName]);
    }

    /**
     * Последние записи журнала событий (вкладка «Логи»).
     */
    public function logs($request)
    {
        if ($request->method() !== 'GET') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $result = \App\Core\Logger::recent(50);
        return $this->json(['success' => true] + $result);
    }

    /**
     * Очистить журнал событий.
     */
    public function clearLogs($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $deleted = \App\Core\Logger::clear();
        return $this->json(['success' => true, 'deleted' => $deleted]);
    }
}
