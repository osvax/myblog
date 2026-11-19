<?php
    // Карточка «Другие проекты» — остальные опубликованные проекты из БД.
    $otherProjects = isset($otherProjects) && is_array($otherProjects) ? array_values($otherProjects) : [];
?>
<?php if ($otherProjects): ?>
    <div class="card border-0 shadow-sm p-4">
        <h5 class="fw-bold mb-3">Другие проекты</h5>
        <?php foreach ($otherProjects as $otherIndex => $otherProject): ?>
            <a href="<?= htmlspecialchars($otherProject['slug'], ENT_QUOTES, 'UTF-8') ?>.html"
               class="fw-bold text-orange link-orange d-block <?= $otherIndex < count($otherProjects) - 1 ? 'mb-2' : '' ?>">
                <i class="bi bi-arrow-right"></i> <?= htmlspecialchars($otherProject['title'], ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
