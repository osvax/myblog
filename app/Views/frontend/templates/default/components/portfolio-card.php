<?php
    // Обложка карточки — первый снимок проекта из галереи.
    $projectCover = null;
    $projectCoverRegex = '#^(?:uploads/portfolio/[A-Za-z0-9_-]+/[a-f0-9]{32}\.(?:jpg|png|webp)|uploads/images/portfolio/[a-z0-9][a-z0-9-]*\.(?:jpg|jpeg|png))$#';
    foreach ((array) (isset($project['screenshots']) ? $project['screenshots'] : []) as $shot) {
        if (is_string($shot) && preg_match($projectCoverRegex, $shot)) {
            $projectCover = $shot;
            break;
        }
    }
?>
<div class="col-md-6 col-lg-4">
    <a href="/<?= htmlspecialchars($projectSlug, ENT_QUOTES, 'UTF-8') ?>.html" class="text-decoration-none">
        <div class="card h-100 border-0 shadow-sm work-card overflow-hidden">
            <?php if ($projectCover): ?>
                <div class="post-card-cover">
                    <img src="/<?= htmlspecialchars($projectCover, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                </div>
            <?php else: ?>
                <div class="card-body bg-orange-light text-center py-5">
                    <i class="bi <?= htmlspecialchars($project['icon'], ENT_QUOTES, 'UTF-8') ?> display-3 text-orange"></i>
                </div>
            <?php endif; ?>
            <div class="card-footer bg-white border-0">
                <h2 class="h5 fw-bold mb-1 text-orange"><?= htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <small class="fw-bold text-muted"><?= htmlspecialchars($project['technologies'] ?? $project['description'], ENT_QUOTES, 'UTF-8') ?> <i class="bi bi-arrow-right"></i></small>
            </div>
        </div>
    </a>
</div>
