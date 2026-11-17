<?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/article-heading.php'; ?>
<?php $waveColors = ['#ff8c1a', '#ffffff']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <article>
                    <?php if (!empty($article['cover'])): ?>
                        <img src="/<?= htmlspecialchars(ltrim($article['cover'], '/'), ENT_QUOTES, 'UTF-8') ?>" class="img-fluid rounded mb-4" alt="<?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
                    <div class="article-content"><?= $article['content'] ?></div>
                </article>
                <div class="mt-5">
                    <?php require APP_ROOT . '/app/Views/frontend/templates/default/components/comments-section.php'; ?>
                </div>
            </div>
        </div>
    </div>
</section>
