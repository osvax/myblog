<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\ApiResource;
use App\Models\Role;

/**
 * API управления ролями и правами. Раздел «Роли и права».
 */
final class RolesController extends Controller
{
    private $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    /** Список ролей с правами + справочник разделов. */
    public function index($request)
    {
        if ($request->method() !== 'GET') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        $roles = array_map(function ($role) {
            return [
                'id' => (int) $role['id'],
                'role_key' => $role['role_key'],
                'name' => $role['name'],
                'description' => $role['description'],
                'is_system' => (bool) $role['is_system'],
                'users_count' => (int) $role['users_count'],
                'permissions' => $this->role->permissions((int) $role['id']),
            ];
        }, $this->role->all());

        return $this->json(ApiResource::success([
            'roles' => $roles,
            'sections' => require APP_ROOT . '/config/admin-navigation.php',
        ]));
    }

    /** Создать роль. */
    public function create($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $input = $request->json();
        $key = isset($input['role_key']) && is_string($input['role_key']) ? strtolower(trim($input['role_key'])) : '';
        $name = isset($input['name']) && is_string($input['name']) ? trim($input['name']) : '';
        $description = isset($input['description']) && is_string($input['description']) ? trim($input['description']) : '';

        $result = $this->role->create($key, $name, $description);
        if (!$result['success']) {
            return $this->json(ApiResource::error($result['error']), 400);
        }
        return $this->json(ApiResource::success(['id' => $result['id']]));
    }

    /** Обновить роль: название, описание и права одним запросом. */
    public function update($request, $parameters)
    {
        if ($request->method() !== 'PUT') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(ApiResource::error('Неверный ID'), 400);
        }

        $input = $request->json();
        $name = isset($input['name']) && is_string($input['name']) ? trim($input['name']) : '';
        $description = isset($input['description']) && is_string($input['description']) ? trim($input['description']) : '';
        $permissions = isset($input['permissions']) && is_array($input['permissions']) ? $input['permissions'] : null;

        $result = $this->role->update($id, $name, $description, $permissions);
        if (!$result['success']) {
            return $this->json(ApiResource::error($result['error']), 400);
        }
        return $this->json(ApiResource::success());
    }

    /** Удалить роль (не системную и не назначенную пользователям). */
    public function delete($request, $parameters)
    {
        if ($request->method() !== 'DELETE') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(ApiResource::error('Неверный ID'), 400);
        }

        $result = $this->role->delete($id);
        if (!$result['success']) {
            return $this->json(ApiResource::error($result['error']), 400);
        }
        return $this->json(ApiResource::success());
    }
}
