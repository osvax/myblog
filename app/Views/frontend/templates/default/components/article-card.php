<div class="col-md-4">
    <div class="card h-100 shadow-sm border-0 post-card">
        <a href="/<?= htmlspecialchars($articleSlug, ENT_QUOTES, 'UTF-8') ?>.html" class="card-img-top bg-orange-light text-center py-5 text-decoration-none">
            <i class="bi <?= htmlspecialchars($article['icon'], ENT_QUOTES, 'UTF-8') ?> display-4 text-orange"></i>
        </a>
        <div class="card-body">
            <h2 class="h5 card-title fw-bold"><a href="/<?= htmlspecialchars($articleSlug, ENT_QUOTES, 'UTF-8') ?>.html" class="link-orange text-decoration-none"><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></a></h2>
            <p class="card-text"><?= htmlspecialchars($article['description'], ENT_QUOTES, 'UTF-8') ?></p>
            <a href="/<?= htmlspecialchars($articleSlug, ENT_QUOTES, 'UTF-8') ?>.html" class="fw-bold text-orange link-orange">Читать далее →</a>
        </div>
        <div class="card-footer bg-transparent border-0 small fw-bold text-muted">
            <i class="bi bi-calendar3"></i> <?= htmlspecialchars($article['date'], ENT_QUOTES, 'UTF-8') ?> · <i class="bi bi-tag"></i>
            <?php if (!empty($article['category_slug'])): ?>
                <a href="/category/<?= htmlspecialchars($article['category_slug'], ENT_QUOTES, 'UTF-8') ?>.html" class="fw-bold text-orange link-orange text-decoration-none"><?= htmlspecialchars($article['category'], ENT_QUOTES, 'UTF-8') ?></a>
            <?php else: ?>
                <?= htmlspecialchars($article['category'], ENT_QUOTES, 'UTF-8') ?>
            <?php endif; ?>
        </div>
    </div>
</div>
