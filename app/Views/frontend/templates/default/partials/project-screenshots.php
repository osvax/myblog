<?php
$projectGallery = isset($project['gallery']) && is_array($project['gallery']) ? $project['gallery'] : [];
$projectGalleryMap = [];
foreach ($projectGallery as $entry) {
    if (is_array($entry) && isset($entry['path']) && is_string($entry['path'])) {
        $projectGalleryMap[$entry['path']] = isset($entry['description']) && is_string($entry['description']) ? $entry['description'] : '';
    }
}
$projectScreenshots = isset($project['screenshots']) && is_array($project['screenshots']) ? $project['screenshots'] : [];
?>
<section class="py-5 bg-orange-light">
    <div class="container">
        <h2 class="fw-bold mb-4 text-center">Скриншоты проекта</h2>
        <div class="row g-4">
            <?php if ($projectScreenshots): ?>
                <?php foreach ($projectScreenshots as $screenshot): ?>
                    <div class="col-md-6">
                        <div class="screenshot bg-orange-light rounded">
                            <img src="/<?= htmlspecialchars($screenshot) ?>" alt="<?= htmlspecialchars(isset($project['title']) ? $project['title'] : 'Скриншот проекта') ?>" class="img-fluid rounded mb-2">
                            <?php if (!empty($projectGalleryMap[$screenshot])): ?>
                                <div class="px-3 pb-3">
                                    <p class="fw-bold text-muted mb-0"><?= htmlspecialchars($projectGalleryMap[$screenshot]) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="screenshot bg-orange-light rounded d-flex align-items-center justify-content-center flex-column p-4">
                        <i class="bi <?= htmlspecialchars(isset($project['icon']) ? $project['icon'] : 'bi-image') ?> display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">Скриншоты проекта скоро появятся</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="portfolio.html" class="btn btn-outline-dark btn-lg fw-bold">
                <i class="bi bi-arrow-left"></i> Вернуться в портфолио
            </a>
        </div>
    </div>
</section>
