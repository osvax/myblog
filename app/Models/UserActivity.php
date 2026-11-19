<?php

namespace App\Models;

use App\Core\Database;

class UserActivity
{
    /**
     * Записать посещение страницы пользователем.
     *
     * @param int $userId
     * @param string $pageUrl
     * @param string $pageTitle
     * @param string $ipAddress
     * @param string $userAgent
     * @return bool
     */
    public function logVisit(int $userId, string $pageUrl, string $pageTitle, string $ipAddress, string $userAgent): bool
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "INSERT INTO " . db_table('user_activities') . " (user_id, page_url, page_title, ip_address, user_agent, visited_at)
             VALUES (:user_id, :page_url, :page_title, :ip_address, :user_agent, NOW())"
        );
        return $stmt->execute([
            ':user_id' => $userId,
            ':page_url' => $pageUrl,
            ':page_title' => $pageTitle,
            ':ip_address' => $ipAddress,
            ':user_agent' => $userAgent,
        ]);
    }

    /**
     * Обновить последнее посещение и IP.
     *
     * @param int $userId
     * @param string $ipAddress
     * @return bool
     */
    public function updateLastVisit(int $userId, string $ipAddress): bool
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "UPDATE " . db_table('users') . " SET last_visit = NOW(), last_ip = :ip_address WHERE id = :id"
        );
        return $stmt->execute([
            ':ip_address' => $ipAddress,
            ':id' => $userId,
        ]);
    }

    /**
     * Получить историю посещений пользователя.
     *
     * @param int $userId
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getHistory(int $userId, int $page = 1, int $perPage = 20): array
    {
        $db = Database::connection();
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare(
            "SELECT id, page_url, page_title, ip_address, user_agent, visited_at
             FROM " . db_table('user_activities') . "
             WHERE user_id = :user_id
             ORDER BY visited_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Получить общее количество записей.
     *
     * @param int $userId
     * @return int
     */
    public function getTotalCount(int $userId): int
    {
        $db = Database::connection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM " . db_table('user_activities') . " WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch();
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Получить статистику по страницам.
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getPageStats(int $userId, int $limit = 10): array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "SELECT page_url, page_title, COUNT(*) as visits
             FROM " . db_table('user_activities') . "
             WHERE user_id = :user_id
             GROUP BY page_url, page_title
             ORDER BY visits DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
