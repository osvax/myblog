<?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/article-heading.php'; ?>
<?php $waveColors = ['#ff8c1a', '#ffffff']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>
<?php
// Сайдбар страницы статьи (Настройки → Статьи): слева / справа / выключен
$articleSide = in_array($articlePageSidebar ?? 'right', ['left', 'right'], true) ? $articlePageSidebar : ($articlePageSidebar === 'none' ? 'none' : 'right');
ob_start();
if ($articleSide !== 'none') {
    require APP_ROOT . '/app/Views/frontend/templates/default/components/article-sidebar.php';
}
$articleSidebarHtml = ob_get_clean();
?>
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <?php if ($articleSide === 'left'): ?>
                <aside class="col-lg-4 sidebar-block"><?= $articleSidebarHtml ?></aside>
            <?php endif; ?>
            <!-- Статья: полный текст из базы (body_html) -->
            <div class="<?= $articleSide === 'none' ? 'col-12' : 'col-lg-8' ?>">
                <article>
                    <?php if (!empty($article['cover'])): ?>
                        <img src="/<?= htmlspecialchars(ltrim($article['cover'], '/'), ENT_QUOTES, 'UTF-8') ?>" class="img-fluid rounded mb-4" alt="<?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
                    <div class="article-content"><?= $article['body'] ?></div>
                    <?php if (!empty($showArticleTags) && !empty($article['tags'])): ?>
                        <div class="mt-4">
                            <?php foreach ((array) $article['tags'] as $articleTag): ?>
                                <span class="badge bg-orange-light text-orange fw-bold me-1 p-2">#<?= htmlspecialchars($articleTag, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($showShareButtons)): ?>
                        <!-- Кнопки «Поделиться» (Настройки → Статьи) -->
                        <div class="d-flex align-items-center flex-wrap gap-2 mt-4">
                            <span class="fw-bold me-1"><i class="bi bi-share"></i> Поделиться:</span>
                            <a class="btn btn-sm btn-outline-dark fw-bold" target="_blank" rel="noopener noreferrer"
                               href="https://t.me/share/url?url=<?= urlencode($shareUrl) ?>&text=<?= urlencode($article['title']) ?>"><i class="bi bi-telegram"></i> Telegram</a>
                            <a class="btn btn-sm btn-outline-dark fw-bold" target="_blank" rel="noopener noreferrer"
                               href="https://vk.com/share.php?url=<?= urlencode($shareUrl) ?>&title=<?= urlencode($article['title']) ?>"><i class="bi bi-chat-dots"></i> VK</a>
                            <a class="btn btn-sm btn-outline-dark fw-bold" target="_blank" rel="noopener noreferrer"
                               href="https://api.whatsapp.com/send?text=<?= urlencode($article['title'] . ' ' . $shareUrl) ?>"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                            <button type="button" class="btn btn-sm btn-outline-dark fw-bold"
                                    onclick="var b=this;if(navigator.clipboard){navigator.clipboard.writeText('<?= htmlspecialchars($shareUrl, ENT_QUOTES, 'UTF-8') ?>');b.textContent='Скопировано!';setTimeout(function(){b.textContent='Скопировать ссылку';},2000);}">Скопировать ссылку</button>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mt-5">
                        <?php if (!empty($prevArticle)): ?>
                            <a href="/<?= htmlspecialchars($prevArticle['slug'], ENT_QUOTES, 'UTF-8') ?>.html" class="btn btn-outline-dark fw-bold"><i class="bi bi-arrow-left"></i> Предыдущая</a>
                        <?php else: ?><span></span><?php endif; ?>
                        <?php if (!empty($nextArticle)): ?>
                            <a href="/<?= htmlspecialchars($nextArticle['slug'], ENT_QUOTES, 'UTF-8') ?>.html" class="btn btn-orange fw-bold">Следующая статья <i class="bi bi-arrow-right"></i></a>
                        <?php endif; ?>
                    </div>
                </article>
                <div class="mt-5">
                    <?php require APP_ROOT . '/app/Views/frontend/templates/default/components/comments-section.php'; ?>
                </div>
            </div>
            <?php if ($articleSide === 'right'): ?>
                <!-- Сайдбар: рубрики из базы + свежие записи -->
                <aside class="col-lg-4 sidebar-block"><?= $articleSidebarHtml ?></aside>
            <?php endif; ?>
        </div>
    </div>
</section>
