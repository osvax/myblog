<?php

/**
 * Migration: portfolio_result
 * Created: 2025-11-24 00:00:00
 * Description: Колонка result в portfolio_projects (итоги проекта)
 * Применяется ПОСЛЕ 001_initial_schema
 */

return [
    'up' => function (\PDO $pdo): void {
        $stmt = $pdo->query("SHOW COLUMNS FROM `va_portfolio_projects` LIKE 'result'");
        if (!$stmt->fetch()) {
            $pdo->exec(<<<'SQL'
                ALTER TABLE `va_portfolio_projects`
                ADD COLUMN `result` MEDIUMTEXT NULL COMMENT 'Итоги/результаты проекта' AFTER `solution`;
            SQL);
        }
    },

    'down' => function (\PDO $pdo): void {
        $stmt = $pdo->query("SHOW COLUMNS FROM `va_portfolio_projects` LIKE 'result'");
        if ($stmt->fetch()) {
            $pdo->exec('ALTER TABLE `va_portfolio_projects` DROP COLUMN `result`;');
        }
    },

    'description' => 'Add result column to portfolio_projects'
];