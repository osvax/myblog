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
                    <h3 class="fw-bold mb-4">Оставить комментарий</h3>
                    <form action="/api/forms/comment" method="POST" class="card border-0 shadow-sm p-4 order-form">
                        <input type="hidden" name="article" value="<?= htmlspecialchars($articleSlug, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label fw-bold" for="commentName">Имя</label><input id="commentName" name="name" class="form-control" required maxlength="160"></div>
                            <div class="col-md-6"><label class="form-label fw-bold" for="commentEmail">Email</label><input id="commentEmail" name="email" type="email" class="form-control" required maxlength="254"></div>
                            <div class="col-12"><label class="form-label fw-bold" for="commentMessage">Комментарий</label><textarea id="commentMessage" name="message" rows="4" class="form-control" required maxlength="10000"></textarea></div>
                            <div class="col-12"><button class="btn btn-orange fw-bold" type="submit">Отправить комментарий</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
