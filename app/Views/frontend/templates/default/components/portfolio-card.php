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
// Год и клиент — из настроек (Настройки → Портфолио)
$cardMeta = [];
if (!empty($showPortfolioDate)) {
    $cardYear = !empty($project['project_year']) ? (string) $project['project_year'] : '';
    if ($cardYear === '' && !empty($project['created_at'])) {
        $cardYear = date('Y', strtotime((string) $project['created_at']));
    }
    if ($cardYear !== '') {
        $cardMeta[] = $cardYear;
    }
}
if (!empty($showPortfolioClient) && !empty($project['client'])) {
    $cardMeta[] = (string) $project['client'];
}
// Ширина карточки в ряду (Настройки → Портфолио → «Работ в ряд»):
// 1 — во всю ширину, 2 — половина, 3 — текущая треть; мозаика — без колонок
$portfolioCardClass = !empty($portfolioMasonry)
    ? 'work-masonry-item'
    : ((isset($portfolioCardCol) && is_string($portfolioCardCol) && $portfolioCardCol !== '') ? $portfolioCardCol : 'col-md-6 col-lg-4');
?>
<div class="<?= $portfolioCardClass ?>">
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
                <?php if (!empty($showPortfolioTags)): ?>
                <small class="fw-bold text-muted"><?= htmlspecialchars($project['technologies'] ?? $project['description'], ENT_QUOTES, 'UTF-8') ?> <i class="bi bi-arrow-right"></i></small>
                <?php endif; ?>
                <?php if (!empty($cardMeta)): ?>
                <small class="fw-bold text-muted d-block mt-1"><?= htmlspecialchars(implode(' · ', $cardMeta), ENT_QUOTES, 'UTF-8') ?></small>
                <?php endif; ?>
            </div>
        </div>
    </a>
</div>
