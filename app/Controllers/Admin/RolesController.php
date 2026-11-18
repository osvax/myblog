<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\Role;

/**
 * API управления ролями и правами. Раздел «Роли и права».
 */
class RolesController extends Controller
{
    /** Список ролей с правами + справочник разделов. */
    public function index($request)
    {
        if ($request->method() !== 'GET') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $model = new Role();
        $roles = array_map(function ($role) use ($model) {
            return [
                'id' => (int) $role['id'],
                'role_key' => $role['role_key'],
                'name' => $role['name'],
                'description' => $role['description'],
                'is_system' => (bool) $role['is_system'],
                'users_count' => (int) $role['users_count'],
                'permissions' => $model->permissions((int) $role['id']),
            ];
        }, $model->all());

        return $this->json([
            'success' => true,
            'roles' => $roles,
            'sections' => require APP_ROOT . '/config/admin-navigation.php',
        ]);
    }

    /** Создать роль. */
    public function create($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $input = $request->json();
        $key = isset($input['role_key']) && is_string($input['role_key']) ? strtolower(trim($input['role_key'])) : '';
        $name = isset($input['name']) && is_string($input['name']) ? trim($input['name']) : '';
        $description = isset($input['description']) && is_string($input['description']) ? trim($input['description']) : '';

        $result = (new Role())->create($key, $name, $description);
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => $result['error']], 400);
        }
        return $this->json(['success' => true, 'id' => $result['id']]);
    }

    /** Обновить роль: название, описание и права одним запросом. */
    public function update($request, $parameters)
    {
        if ($request->method() !== 'PUT') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(['success' => false, 'error' => 'Неверный ID'], 400);
        }

        $input = $request->json();
        $name = isset($input['name']) && is_string($input['name']) ? trim($input['name']) : '';
        $description = isset($input['description']) && is_string($input['description']) ? trim($input['description']) : '';
        $permissions = isset($input['permissions']) && is_array($input['permissions']) ? $input['permissions'] : null;

        $result = (new Role())->update($id, $name, $description, $permissions);
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => $result['error']], 400);
        }
        return $this->json(['success' => true]);
    }

    /** Удалить роль (не системную и не назначенную пользователям). */
    public function delete($request, $parameters)
    {
        if ($request->method() !== 'DELETE') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(['success' => false, 'error' => 'Неверный ID'], 400);
        }

        $result = (new Role())->delete($id);
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => $result['error']], 400);
        }
        return $this->json(['success' => true]);
    }
}
