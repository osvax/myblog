<?php

declare(strict_types=1);

namespace App\Transformers;

use App\DTO\UserDTO;

/**
 * UserTransformer — трансформер для UserDTO.
 *
 * Пользователи имеют разные представления:
 * - Админка (полное с ролью, статусом, датами)
 * - Публичный профиль (ограниченные поля)
 * - Сессия (для авторизации)
 */
final class UserTransformer extends Transformer
{
    /**
     * Полное представление пользователя (для админки: список/детали).
     *
     * @param object $dto UserDTO
     * @param string|null $roleName Название роли из JOIN (опционально)
     * @return array
     */
    public function transform(object $dto, ?string $roleName = null): array
    {
        assert($dto instanceof UserDTO);
        return [
            'id' => $dto->id,
            'email' => $dto->email,
            'name' => $dto->name,
            'avatar_path' => $dto->avatarPath,
            'role' => $dto->role,
            'role_name' => $roleName ?? $dto->role,
            'is_active' => $dto->isActive,
            'created_at' => $dto->createdAt,
            'updated_at' => $dto->updatedAt,
            'last_visit' => $dto->lastVisit,
            'last_ip' => $dto->lastIp,
        ];
    }

    /**
     * Представление для списков пользователей в админке.
     *
     * @param UserDTO $dto
     * @param string|null $roleName
     * @return array
     */
    public function transformList(UserDTO $dto, ?string $roleName = null): array
    {
        return $this->transform($dto, $roleName);
    }

    /**
     * Публичное представление профиля (ограниченные поля).
     *
     * @param UserDTO $dto
     * @return array
     */
    public function transformPublicProfile(UserDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'name' => $dto->name,
            'avatar_path' => $dto->avatarPath,
            'about' => $dto->about,
            'position' => $dto->position,
            'bio' => $dto->bio,
            'created_at' => $dto->createdAt,
        ];
    }

    /**
     * Представление для сессии (при логине).
     *
     * @param UserDTO $dto
     * @return array
     */
    public function transformSession(UserDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'email' => $dto->email,
            'name' => $dto->name,
            'avatar_path' => $dto->avatarPath,
            'role' => $dto->role,
            'is_active' => $dto->isActive,
            'totp_enabled' => $dto->totpEnabled,
            'timezone' => $dto->timezone,
            'language' => $dto->language,
        ];
    }

    /**
     * Мини-представление (для селектов/ссылок).
     *
     * @param UserDTO $dto
     * @return array
     */
    public function transformMinimal(UserDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'name' => $dto->name,
            'email' => $dto->email,
            'role' => $dto->role,
            'avatar_path' => $dto->avatarPath,
        ];
    }

    /**
     * Полное представление профиля для владельца (AdminProfileController::show).
     * Включает телефон, таймзону, язык, статистику страниц.
     *
     * @param UserDTO $dto
     * @param array $pageStats
     * @param array $recentActivities
     * @return array
     */
    public function transformOwnProfile(
        UserDTO $dto,
        array $pageStats = [],
        array $recentActivities = []
    ): array {
        return [
            'id' => $dto->id,
            'email' => $dto->email,
            'name' => $dto->name,
            'role' => $dto->role,
            'is_active' => $dto->isActive,
            'avatar_path' => $dto->avatarPath,
            'phone' => $dto->phone,
            'about' => $dto->about,
            'position' => $dto->position,
            'bio' => $dto->bio,
            'timezone' => $dto->timezone,
            'language' => $dto->language,
            'created_at' => $dto->createdAt,
            'updated_at' => $dto->updatedAt,
            'last_visit' => $dto->lastVisit,
            'last_ip' => $dto->lastIp,
            'page_stats' => $pageStats,
            'recent_activities' => $recentActivities,
        ];
    }
}
