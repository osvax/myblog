<?php
/**
 * Карточка «Рубрики» в сайдбаре статей: список рубрик из базы со
 * ссылками на страницы рубрик и количеством статей.
 *
 * Ожидаемые переменные:
 * - $sidebarCategories — массив рубрик (name, slug, articles)
 */
$sidebarCategories = (isset($sidebarCategories) && is_array($sidebarCategories)) ? $sidebarCategories : [];
?>
<div class="card border-0 shadow-sm p-4 mb-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-tags text-orange"></i> Рубрики</h5>
    <?php $rubricMenuModel = new \App\Models\Menu(); ?>
    <?php $rubricMenuModel->syncRubricMenu('main'); ?>
    <?php $rubricMenus = $rubricMenuModel->menusByPrefix('rubric_'); ?>
    <?php if ($rubricMenus && render_menu_rubric('main', 'nav flex-column', 'nav-item mb-2', 'fw-bold text-orange link-orange') !== ''): ?>
        <?= render_menu_rubric('main', 'nav flex-column', 'nav-item mb-2', 'fw-bold text-orange link-orange') ?>
    <?php else: ?>
    <ul class="list-unstyled mb-0">
        <?php $sidebarIndex = 0;
        foreach ($sidebarCategories as $sidebarCategory): ?>
            <li<?= $sidebarIndex < count($sidebarCategories) - 1 ? ' class="mb-2"' : '' ?>><a href="/category/<?= htmlspecialchars($sidebarCategory['slug'], ENT_QUOTES, 'UTF-8') ?>.html" class="fw-bold text-orange link-orange d-flex justify-content-between"><span><?= htmlspecialchars($sidebarCategory['name'], ENT_QUOTES, 'UTF-8') ?></span><span class="badge bg-orange-light text-orange"><?= (int) $sidebarCategory['articles'] ?></span></a></li>
        <?php $sidebarIndex++; endforeach; ?>
        <?php if (empty($sidebarCategories)): ?>
            <li class="fw-bold text-muted">Рубрик пока нет</li>
        <?php endif; ?>
    </ul>
    <?php endif; ?>
</div>
