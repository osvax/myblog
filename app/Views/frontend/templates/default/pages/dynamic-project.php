<?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-heading.php'; ?>
<?php $waveColors = ['#ff8c1a', '#ffffff'];
require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>
<?php
// Сайдбар страницы проекта (Настройки → Портфолио): слева / справа / выключен
// (выключен — инфо-карточка и «Другие проекты» выводятся под описанием)
$projectSide = in_array($projectPageSidebar ?? 'right', ['left', 'right'], true) ? $projectPageSidebar : ($projectPageSidebar === 'none' ? 'none' : 'right');
ob_start();
require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-info-card.php';
require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-other-projects.php';
$projectSidebarHtml = ob_get_clean();
?>
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <?php if ($projectSide === 'left'): ?>
                <aside class="col-lg-4 sidebar-block"><?= $projectSidebarHtml ?></aside>
            <?php endif; ?>
            <div class="<?= $projectSide === 'none' ? 'col-12' : 'col-lg-8' ?>">
                <?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-brief.php'; ?>
                <?php
                    // Подписи к снимкам — из таблицы project_gallery (описания из админки)
                    $projectGalleryMap = [];
foreach ($project['gallery'] ?? [] as $entry) {
    if (is_array($entry) && isset($entry['path']) && is_string($entry['path'])) {
        $projectGalleryMap[$entry['path']] = $entry['description'] ?? '';
    }
}
?>
                <?php if (!empty($project['screenshots'])): ?>
                    <h2 class="fw-bold mt-5 mb-3">Галерея проекта</h2>
                    <div class="row g-3"><?php foreach ($project['screenshots'] as $image): ?>
                        <div class="col-md-6"><img src="/<?= htmlspecialchars(ltrim($image, '/'), ENT_QUOTES, 'UTF-8') ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8') ?>"><?php if (!empty($projectGalleryMap[$image])): ?><p class="fw-bold text-muted mt-1 mb-0"><?= htmlspecialchars($projectGalleryMap[$image], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?></div>
                    <?php endforeach; ?></div>
                <?php endif; ?>
            </div>
            <?php if ($projectSide === 'right'): ?>
                <aside class="col-lg-4 sidebar-block"><?= $projectSidebarHtml ?></aside>
            <?php elseif ($projectSide === 'none'): ?>
                <div class="col-12 sidebar-block"><?= $projectSidebarHtml ?></div>
            <?php endif; ?>
        </div>
    </div>
</section>
