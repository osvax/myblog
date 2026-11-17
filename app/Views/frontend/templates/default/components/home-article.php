<a href="/<?= htmlspecialchars($articleSlug, ENT_QUOTES, 'UTF-8') ?>.html" class="text-decoration-none">
    <div class="lp-post d-flex align-items-center p-3 mb-3">
        <div class="lp-post-icon bg-orange-light text-center me-3"><i class="bi <?= htmlspecialchars($article['icon'], ENT_QUOTES, 'UTF-8') ?> fs-3 text-orange"></i></div>
        <div>
            <h3 class="h5 fw-bold mb-1 text-orange"><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <small class="fw-bold text-muted"><i class="bi bi-calendar3"></i> <?= htmlspecialchars($article['date'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($article['category'], ENT_QUOTES, 'UTF-8') ?></small>
        </div>
    </div>
</a>
