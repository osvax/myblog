<?php

declare(strict_types=1);

namespace App\Models;

use App\DTO\AdminUserDTO;
use PDO;

/**
 * AdminUser — проверка доступа к админ-панели.
 *
 * === МИГРАЦИЯ ===
 * - Конструктор принимает PDO (для тестов — SQLite :memory:)
 * - QueryBuilder для простых запросов
 * - Возвращает AdminUserDTO вместо массивов
 * - Статический метод-обёртка для обратной совместимости
 */
final class AdminUser
{
    private PDO $db;

    /**
     * @param PDO|null $db PDO соединение (по умолчанию Database::connection())
     */
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? \App\Core\Database::connection();
    }

    // ===================== ОБРАТНАЯ СОВМЕСТИМОСТЬ (статический) =====================

    /**
     * Проверить учётные данные для входа в админку (обратная совместимость — возвращает массив).
     *
     * @param string $email
     * @param string $password
     * @return array<string, mixed>
     */
    public static function verifyCredentials(string $email, string $password): array
    {
        return (new self())->verifyCredentialsNew($email, $password)->toApiArray();
    }

    // ===================== НОВЫЙ API (DTO-based) =====================

    /**
     * Проверить учётные данные для входа в админку.
     *
     * @param string $email
     * @param string $password
     * @return AdminUserDTO
     */
    public function verifyCredentialsNew(string $email, string $password): AdminUserDTO
    {
        $settings = new SiteSettings();
        $configuredEmail = strtolower(trim((string) $settings->get('adminLoginEmail', '')));
        if ($configuredEmail === '') {
            $configuredEmail = strtolower(trim((string) $settings->get('adminEmail', '')));
        }
        $email = strtolower(trim((string) $email));

        $dto = new AdminUserDTO();
        $dto->configured = filter_var($configuredEmail, FILTER_VALIDATE_EMAIL) !== false;

        if (!$dto->configured) {
            return $dto;
        }

        $stmt = $this->db->prepare(
            'SELECT * FROM ' . db_table('users') . ' WHERE LOWER(email) = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            return $dto;
        }

        if (!$user['is_active']) {
            $dto->error = 'Аккаунт заблокирован';
            return $dto;
        }

        if (!password_verify($password, $user['password_hash'])) {
            $dto->error = 'Неверный email или пароль';
            return $dto;
        }

        // В админку пускаем роли, заведённые в БД и имеющие хотя бы один раздел
        // (роль «admin» — всегда). Набор разделов настраивается в «Роли и права».
        $role = (string) $user['role'];
        $roleModel = new Role($this->db);
        $roleRow = $roleModel->byKey($role);
        if (!$roleRow || ($role !== 'admin' && !$roleModel->permissions((int) $roleRow['id']))) {
            $dto->error = 'Доступ запрещён';
            return $dto;
        }

        $dto->valid = true;
        $dto->email = $user['email'];
        $dto->userId = (int) $user['id'];
        $dto->name = $user['name'];
        $dto->role = $role;
        $dto->roleName = $roleRow['name'];

        return $dto;
    }
}
