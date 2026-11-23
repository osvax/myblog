<?php
// Сайдбар статей: рубрики, похожие статьи, свежие записи.
// Выводятся виджеты, для которых переданы данные (используется на
// странице списка статей и на странице полной статьи).
//
// Ожидаемые переменные (все необязательные):
// - $sidebarCategories — рубрики из БД (name, slug, articles)
// - $relatedArticles   — похожие статьи (slug, title)
// - $latestArticles    — свежие записи (slug, title, date)
// - $showArticleCategories / $showRelatedArticles — тумблеры настроек
?>
<div class="article-sidebar">
    <?php if (!empty($showArticleCategories)): ?>
        <?php require APP_ROOT . '/app/Views/frontend/templates/default/components/categories-sidebar.php'; ?>
    <?php endif; ?>

    <?php if (!empty($showRelatedArticles) && !empty($relatedArticles)): ?>
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-collection text-orange"></i> Похожие статьи</h5>
            <ul class="list-unstyled mb-0">
                <?php foreach ($relatedArticles as $relatedArticle): ?>
                    <li class="mb-2"><a href="/<?= htmlspecialchars($relatedArticle['slug'], ENT_QUOTES, 'UTF-8') ?>.html" class="fw-bold text-orange link-orange"><?= htmlspecialchars($relatedArticle['title'], ENT_QUOTES, 'UTF-8') ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($latestArticles)): ?>
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-orange"></i> Свежие записи</h5>
            <?php foreach ((array) $latestArticles as $latestArticle): ?>
                <a href="/<?= htmlspecialchars($latestArticle['slug'], ENT_QUOTES, 'UTF-8') ?>.html" class="fw-bold text-orange link-orange d-block mb-2"><?= htmlspecialchars($latestArticle['title'], ENT_QUOTES, 'UTF-8') ?></a>
            <?php endforeach; ?>
            <?php if (empty($latestArticles)): ?>
                <span class="fw-bold text-muted">Пока нет других записей</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
