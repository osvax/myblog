<?php

declare(strict_types=1);

namespace App\Models;

use App\DTO\UserDTO;
use PDO;

/**
 * User — legacy модель пользователей.
 *
 * === МИГРАЦИЯ ===
 * - Конструктор принимает PDO (для тестов — SQLite :memory:)
 * - Делегирует работу в UserRepository
 * - Статические методы-обёртки для обратной совместимости
 */
final class User
{
    private UserRepository $repo;

    /**
     * @param PDO|null $db PDO соединение (по умолчанию Database::connection())
     */
    public function __construct(?PDO $db = null)
    {
        $this->repo = new UserRepository($db);
    }

    // ===================== ОБРАТНАЯ СОВМЕСТИМОСТЬ (статические) =====================

    /** @return array */
    public static function create(string $email, string $passwordHash, string $name, string $phone = '', string $position = ''): array
    {
        return (new self())->createNew($email, $passwordHash, $name, $phone, $position);
    }

    /** @return array|null */
    public static function findByEmail(string $email): ?array
    {
        return (new self())->findByEmailNew($email)?->toApiArray();
    }

    /** @return array|null */
    public static function findByYandexId(string $yandexId): ?array
    {
        return (new self())->findByYandexIdNew($yandexId)?->toApiArray();
    }

    /** @return void */
    public static function linkYandex(int $userId, string $yandexId): void
    {
        (new self())->linkYandexNew($userId, $yandexId);
    }

    /** @return void */
    public static function setRole(int $userId, string $role): void
    {
        (new self())->setRoleNew($userId, $role);
    }

    /** @return array */
    public static function verifyCredentials(string $email, string $password): array
    {
        return (new self())->verifyCredentialsNew($email, $password);
    }

    /** @return array|null */
    public static function findById(int $id): ?array
    {
        return (new self())->findByIdNew($id)?->toApiArray();
    }

    /** @return array|null */
    public static function findByResetToken(string $token): ?array
    {
        return (new self())->findByResetTokenNew($token)?->toApiArray();
    }

    /** @return array */
    public static function updatePassword(int $userId, string $newPasswordHash): array
    {
        return (new self())->updatePasswordWithTokenNew($userId, $newPasswordHash);
    }

    /** @return array */
    public static function updateResetToken(int $userId): array
    {
        return (new self())->updateResetTokenNew($userId);
    }

    /** @return bool */
    public static function updateAvatar(int $userId, string $avatarPath): bool
    {
        return (new self())->updateAvatarNew($userId, $avatarPath);
    }

    /** @return array */
    public static function updateFullProfile(
        int $id,
        string $name,
        string $phone = '',
        string $about = '',
        string $position = '',
        string $bio = '',
        string $avatarPath = '',
        string $timezone = '',
        string $language = ''
    ): array {
        return (new self())->updateFullProfileNew($id, $name, $phone, $about, $position, $bio, $avatarPath, $timezone, $language);
    }

    /** @return array */
    public static function logVisit(int $userId, string $pageUrl, string $pageTitle, string $ipAddress, string $userAgent): array
    {
        return (new self())->logVisitNew($userId, $pageUrl, $pageTitle, $ipAddress, $userAgent);
    }

    /** @return array */
    public static function updateProfile(int $id, string $name, string $avatarPath = ''): array
    {
        return (new self())->updateProfileNew($id, $name, $avatarPath);
    }

    /** @return array */
    public static function setTotpSecret(int $userId, string $secret): array
    {
        return (new self())->setTotpSecretNew($userId, $secret);
    }

    /** @return array */
    public static function setTotpEnabled(int $userId, bool $enabled): array
    {
        return (new self())->setTotpEnabledNew($userId, $enabled);
    }

    /** @return array */
    public static function all(): array
    {
        return array_map(fn (UserDTO $dto) => $dto->toApiArray(), (new self())->allNew());
    }

    /** @return bool */
    public static function isAdmin(int $userId): bool
    {
        return (new self())->isAdminNew($userId);
    }

    // ===================== НОВЫЙ API (DTO-based) =====================

    /** @return array ['success' => bool, 'user_id' => int|null, 'error' => string|null] */
    public function createNew(string $email, string $passwordHash, string $name, string $phone = '', string $position = ''): array
    {
        // Проверяем, не занят ли email
        if ($this->repo->emailExists($email)) {
            return ['success' => false, 'user_id' => null, 'error' => 'Пользователь с таким email уже существует'];
        }

        $dto = new UserDTO();
        $dto->email = $email;
        $dto->passwordHash = $passwordHash;
        $dto->name = $name;
        $dto->phone = $phone;
        $dto->position = $position;
        $dto->role = 'user';
        $dto->isActive = true;

        try {
            $id = $this->repo->saveUserFromDTO($dto);
            if ($id === false) {
                return ['success' => false, 'user_id' => null, 'error' => 'Не удалось создать пользователя'];
            }
            return ['success' => true, 'user_id' => $id, 'error' => null];
        } catch (\InvalidArgumentException $e) {
            return ['success' => false, 'user_id' => null, 'error' => $e->getMessage()];
        }
    }

    /** @return UserDTO|null */
    public function findByEmailNew(string $email): ?UserDTO
    {
        return $this->repo->findDtoByEmail($email);
    }

    /** @return UserDTO|null */
    public function findByYandexIdNew(string $yandexId): ?UserDTO
    {
        return $this->repo->findDtoByYandexId($yandexId);
    }

    /** @return void */
    public function linkYandexNew(int $userId, string $yandexId): void
    {
        $this->repo->linkYandex($userId, $yandexId);
    }

    /** @return void */
    public function setRoleNew(int $userId, string $role): void
    {
        $this->repo->setRole($userId, $role);
    }

    /** @return array ['valid' => bool, 'user' => UserDTO|null, 'error' => string|null] */
    public function verifyCredentialsNew(string $email, string $password): array
    {
        return $this->repo->verifyCredentials($email, $password);
    }

    /** @return UserDTO|null */
    public function findByIdNew(int $id): ?UserDTO
    {
        return $this->repo->findDtoById($id);
    }

    /** @return UserDTO|null */
    public function findByResetTokenNew(string $token): ?UserDTO
    {
        return $this->repo->findDtoByResetToken($token);
    }

    /** @return array ['success' => bool] */
    public function updatePasswordWithTokenNew(int $userId, string $newPasswordHash): array
    {
        $ok = $this->repo->updatePasswordWithToken($userId, $newPasswordHash);
        return ['success' => $ok];
    }

    /** @return array ['success' => bool, 'token' => string|null, 'expires' => string|null] */
    public function updateResetTokenNew(int $userId): array
    {
        return $this->repo->updateResetToken($userId);
    }

    /** @return bool */
    public function updateAvatarNew(int $userId, string $avatarPath): bool
    {
        return $this->repo->updateAvatar($userId, $avatarPath);
    }

    /** @return array ['success' => bool] */
    public function updateFullProfileNew(
        int $id,
        string $name,
        string $phone = '',
        string $about = '',
        string $position = '',
        string $bio = '',
        string $avatarPath = '',
        string $timezone = '',
        string $language = ''
    ): array {
        $ok = $this->repo->updateFullProfile($id, $name, $phone, $about, $position, $bio, $avatarPath, $timezone, $language);
        return ['success' => $ok];
    }

    /** @return array ['success' => bool] */
    public function logVisitNew(int $userId, string $pageUrl, string $pageTitle, string $ipAddress, string $userAgent): array
    {
        $ok = $this->repo->logVisit($userId, $pageUrl, $pageTitle, $ipAddress, $userAgent);
        return ['success' => $ok];
    }

    /** @return array ['success' => bool] */
    public function updateProfileNew(int $id, string $name, string $avatarPath = ''): array
    {
        $ok = $this->repo->updateProfile($id, $name, $avatarPath);
        return ['success' => $ok];
    }

    /** @return array ['success' => bool] */
    public function setTotpSecretNew(int $userId, string $secret): array
    {
        $ok = $this->repo->setTotpSecret($userId, $secret);
        return ['success' => $ok];
    }

    /** @return array ['success' => bool] */
    public function setTotpEnabledNew(int $userId, bool $enabled): array
    {
        $ok = $this->repo->setTotpEnabled($userId, $enabled);
        return ['success' => $ok];
    }

    /** @return UserDTO[] */
    public function allNew(): array
    {
        return $this->repo->allAsDto();
    }

    /** @return bool */
    public function isAdminNew(int $userId): bool
    {
        return $this->repo->isAdmin($userId);
    }
}
