<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\LandingPageSetting;

class LandingPageController extends Controller
{
    /**
     * Получить все настройки лендинга.
     */
    public function index($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $sectionKey = isset($_GET['section_key']) && is_string($_GET['section_key']) ? trim($_GET['section_key']) : null;

        $settings = (new LandingPageSetting())->getAll($sectionKey);
        $sections = (new LandingPageSetting())->getSections();

        return $this->json([
            'success' => true,
            'settings' => $settings,
            'sections' => $sections,
        ]);
    }

    /**
     * Добавить или обновить настройку.
     */
    public function create($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $input = $request->json();
        $section = isset($input['section']) && is_string($input['section']) ? trim($input['section']) : '';
        $sectionKey = isset($input['section_key']) && is_string($input['section_key']) ? trim($input['section_key']) : '';
        $fieldName = isset($input['field_name']) && is_string($input['field_name']) ? trim($input['field_name']) : '';
        $fieldValue = isset($input['field_value']) && is_string($input['field_value']) ? $input['field_value'] : '';
        $fieldType = isset($input['field_type']) && is_string($input['field_type']) ? $input['field_type'] : 'text';
        $fieldGroup = isset($input['field_group']) && is_string($input['field_group']) ? trim($input['field_group']) : 'general';
        $displayOrder = isset($input['display_order']) ? (int) $input['display_order'] : 0;
        $placeholder = isset($input['placeholder']) && is_string($input['placeholder']) ? trim($input['placeholder']) : '';
        $options = isset($input['options']) ? $input['options'] : '';
        $isActive = isset($input['is_active']) ? (int) $input['is_active'] : 1;
        $editId = isset($input['edit_id']) ? (int) $input['edit_id'] : 0;

        if (strlen($sectionKey) < 1 || strlen($sectionKey) > 100) {
            return $this->json(['success' => false, 'error' => 'Неверный ключ секции'], 400);
        }
        if (strlen($fieldName) < 1 || strlen($fieldName) > 100) {
            return $this->json(['success' => false, 'error' => 'Неверное имя поля'], 400);
        }
        if (!in_array($fieldType, ['text', 'textarea', 'number', 'email', 'tel', 'url', 'image', 'video', 'audio', 'color', 'checkbox', 'select', 'rich_text'], true)) {
            return $this->json(['success' => false, 'error' => 'Неверный тип поля'], 400);
        }

        $result = (new LandingPageSetting())->upsert(
            $section,
            $sectionKey,
            $fieldName,
            $fieldValue,
            $fieldType,
            $fieldGroup,
            $displayOrder,
            $placeholder,
            $options,
            $isActive,
            $editId
        );

        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => $result['error'] ?? 'Не удалось сохранить'], 500);
        }

        return $this->json(['success' => true, 'id' => $result['id'], 'action' => $result['action']]);
    }

    /**
     * Удалить настройку.
     */
    public function delete($request, $parameters)
    {
        if ($request->method() !== 'DELETE') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $settingId = isset($parameters['id']) ? (int) $parameters['id'] : 0;

        if ($settingId <= 0) {
            return $this->json(['success' => false, 'error' => 'Неверный ID настройки'], 400);
        }

        $result = (new LandingPageSetting())->delete($settingId);

        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => 'Не удалось удалить'], 404);
        }

        return $this->json(['success' => true]);
    }

    /**
     * Переключить активность.
     */
    public function toggleActive($request, $parameters)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $settingId = isset($parameters['id']) ? (int) $parameters['id'] : 0;

        if ($settingId <= 0) {
            return $this->json(['success' => false, 'error' => 'Неверный ID настройки'], 400);
        }

        $result = (new LandingPageSetting())->toggleActive($settingId);

        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => 'Не удалось переключить'], 500);
        }

        return $this->json(['success' => true]);
    }
}
