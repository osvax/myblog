<?php

namespace App\Models;

class AdminUser
{
    public function verifyCredentials($email, $password)
    {
        $config = require APP_ROOT . '/config/admin-auth.php';
        $configuredEmail = strtolower(trim((string) ($config['email'] ?? '')));
        $configuredHash = (string) ($config['password_hash'] ?? '');
        $email = strtolower(trim((string) $email));

        if (!filter_var($configuredEmail, FILTER_VALIDATE_EMAIL)
            || !password_get_info($configuredHash)['algo']) {
            return ['valid' => false, 'configured' => false];
        }

        $db = \App\Core\Database::connection();

        $stmt = $db->prepare("SELECT * FROM " . db_table('users') . " WHERE LOWER(email) = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['valid' => false, 'configured' => true];
        }

        if (!$user['is_active']) {
            return ['valid' => false, 'configured' => true, 'error' => 'Аккаунт заблокирован'];
        }

        $passwordValid = password_verify($password, $user['password_hash']);
        if (!$passwordValid && hash_equals($configuredEmail, $email)) {
            $passwordValid = password_verify($password, $configuredHash);
        }
        if (!$passwordValid) {
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
