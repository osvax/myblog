<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * AdminUserDTO — объект переноса данных для проверки админ-доступа.
 */
final class AdminUserDTO
{
    /** @var bool Успешность проверки */
    public bool $valid = false;

    /** @var bool Настроен ли доступ к админке */
    public bool $configured = false;

    /** @var string|null Email пользователя */
    public ?string $email = null;

    /** @var int|null ID пользователя */
    public ?int $userId = null;

    /** @var string|null Имя пользователя */
    public ?string $name = null;

    /** @var string|null Роль (ключ) */
    public ?string $role = null;

    /** @var string|null Название роли */
    public ?string $roleName = null;

    /** @var string|null Ошибка, если valid = false */
    public ?string $error = null;

    /**
     * Массив для API ответа / сессии.
     *
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        return [
            'valid' => $this->valid,
            'configured' => $this->configured,
            'email' => $this->email,
            'user_id' => $this->userId,
            'name' => $this->name,
            'role' => $this->role,
            'role_name' => $this->roleName,
            'error' => $this->error,
        ];
    }
}
