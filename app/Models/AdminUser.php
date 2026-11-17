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

        if (!in_array($user['role'], ['admin', 'editor', 'manager'], true)) {
            return ['valid' => false, 'configured' => true, 'error' => 'Доступ запрещён'];
        }

        return [
            'valid' => true,
            'configured' => true,
            'email' => $user['email'],
            'user_id' => (int) $user['id'],
            'name' => $user['name'],
            'role' => $user['role'],
        ];
    }
}
