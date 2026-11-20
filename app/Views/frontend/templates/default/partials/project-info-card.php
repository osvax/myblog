<?php
    // Карточка «Информация о проекте» — значения из БД (клиент, год, сроки, стек).
    $projectInfoRows = [
        ['label' => 'Клиент', 'icon' => 'bi-person', 'value' => trim((string) ($project['client'] ?? ''))],
        ['label' => 'Год', 'icon' => 'bi-calendar3', 'value' => trim((string) ($project['year'] ?? $project['project_year'] ?? ''))],
        ['label' => 'Сроки', 'icon' => 'bi-clock-history', 'value' => trim((string) ($project['duration'] ?? ''))],
        ['label' => 'Стек', 'icon' => 'bi-tools', 'value' => trim((string) ($project['stack'] ?? ''))],
    ];
    $projectInfoRows = array_values(array_filter($projectInfoRows, static function ($row) {
        return $row['value'] !== '';
    }));
?>
<div class="card border-0 shadow-sm p-4 mb-4">
    <h5 class="fw-bold mb-3">Информация о проекте</h5>
    <?php foreach ($projectInfoRows as $projectRowIndex => $projectInfoRow): ?>
        <p class="fw-bold <?= $projectRowIndex === count($projectInfoRows) - 1 ? 'mb-3' : 'mb-2' ?>">
            <i class="bi <?= $projectInfoRow['icon'] ?> text-orange"></i>
            <strong><?= $projectInfoRow['label'] ?>:</strong>
            <?= htmlspecialchars($projectInfoRow['value'], ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endforeach; ?>
    <?php if (!empty($showPortfolioExtraFields)): ?>
    <?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-info-items.php'; ?>
    <?php endif; ?>
    <a href="order.html" class="btn btn-orange fw-bold">Хочу похожий проект</a>
</div>
