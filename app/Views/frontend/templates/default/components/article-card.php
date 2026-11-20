<?php
// Анонс — с настраиваемой длиной (Настройки → Статьи → «Длина анонса»)
$cardExcerpt = (string) ($article['description'] ?? '');
$cardExcerptLength = (int) ($excerptLength ?? 0);
if ($cardExcerptLength > 0 && mb_strlen($cardExcerpt) > $cardExcerptLength) {
    $cardExcerpt = mb_substr($cardExcerpt, 0, $cardExcerptLength) . '…';
}
$showCardCategories = !empty($showArticleCategories);
// Ширина карточки в ряду (Настройки → Статьи → «Статей в ряд»):
// 1 — во всю ширину, 2 — половина, 3 — текущая треть
$cardColClass = (isset($blogCardCol) && is_string($blogCardCol) && $blogCardCol !== '') ? $blogCardCol : 'col-md-4';
?>
<div class="<?= $cardColClass ?>">
    <div class="card h-100 shadow-sm border-0 post-card">
        <?php $cardCover = !empty($article['cover']) ? (string) $article['cover'] : ''; ?>
        <a href="/<?= htmlspecialchars($articleSlug, ENT_QUOTES, 'UTF-8') ?>.html" class="card-img-top post-card-cover text-decoration-none" tabindex="-1" aria-hidden="true">
            <span class="post-card-cover-icon">
                <i class="bi <?= htmlspecialchars($article['icon'], ENT_QUOTES, 'UTF-8') ?> display-4 text-orange" aria-hidden="true"></i>
            </span>
            <?php if ($cardCover): ?>
                <!-- Обложка из поля «Обложка статьи»; при ошибке загрузки остаётся иконка -->
                <img src="/<?= htmlspecialchars(ltrim($cardCover, '/'), ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?>"
                     loading="lazy" onerror="this.remove()">
            <?php endif; ?>
        </a>
        <div class="card-body">
            <h2 class="h5 card-title fw-bold"><a href="/<?= htmlspecialchars($articleSlug, ENT_QUOTES, 'UTF-8') ?>.html" class="link-orange text-decoration-none"><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></a></h2>
            <p class="card-text"><?= htmlspecialchars($cardExcerpt, ENT_QUOTES, 'UTF-8') ?></p>
            <a href="/<?= htmlspecialchars($articleSlug, ENT_QUOTES, 'UTF-8') ?>.html" class="fw-bold text-orange link-orange">Читать далее →</a>
        </div>
        <div class="card-footer bg-transparent border-0 small fw-bold text-muted">
            <i class="bi bi-calendar3"></i> <?= htmlspecialchars($article['date'], ENT_QUOTES, 'UTF-8') ?>
            <?php if ($showCardCategories): ?>
            · <i class="bi bi-tag"></i>
            <?php if (!empty($article['category_slug'])): ?>
                <a href="/category/<?= htmlspecialchars($article['category_slug'], ENT_QUOTES, 'UTF-8') ?>.html" class="fw-bold text-orange link-orange text-decoration-none"><?= htmlspecialchars($article['category'], ENT_QUOTES, 'UTF-8') ?></a>
            <?php else: ?>
                <?= htmlspecialchars($article['category'], ENT_QUOTES, 'UTF-8') ?>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
