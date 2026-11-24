<header class="page-header bg-orange text-white text-center py-5">
    <div class="container">
        <i class="bi <?= htmlspecialchars($project['icon'], ENT_QUOTES, 'UTF-8') ?> display-3 mb-3"></i>
        <h1 class="fw-bold mb-2"><?= htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if (trim((string) ($project['technologies'] ?? '')) !== ''): ?>
            <p class="lead fw-bold mb-0"><?= htmlspecialchars(trim((string) $project['technologies']), ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>
</header>
