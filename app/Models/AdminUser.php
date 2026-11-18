<?php

namespace App\Models;

class AdminUser
{
    public function verifyCredentials($email, $password)
    {
        $db = \App\Core\Database::connection();

        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['valid' => false, 'configured' => true];
        }

        if (!$user['is_active']) {
            return ['valid' => false, 'configured' => true, 'error' => 'Аккаунт заблокирован'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['valid' => false, 'configured' => true, 'error' => 'Неверный email или пароль'];
        }

        // В админку пускаем роли, заведённые в БД и имеющие хотя бы один раздел
        // (роль «admin» — всегда). Набор разделов настраивается в «Роли и права».
        $role = (string) $user['role'];
        $roleModel = new \App\Models\Role();
        $roleRow = $roleModel->byKey($role);
        if (!$roleRow || ($role !== 'admin' && !$roleModel->permissions((int) $roleRow['id']))) {
            return ['valid' => false, 'configured' => true, 'error' => 'Доступ запрещён'];
        }

        return [
            'valid' => true,
            'configured' => true,
            'email' => $user['email'],
            'user_id' => (int) $user['id'],
            'name' => $user['name'],
            'role' => $role,
            'role_name' => $roleRow['name'],
        ];
    }
}
