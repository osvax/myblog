<?php
// Сайдбар портфолио: список всех проектов со ссылками.
// Ожидаемая переменная:
// - $sidebarProjects — массив проектов (slug, title)
$sidebarProjects = (isset($sidebarProjects) && is_array($sidebarProjects)) ? $sidebarProjects : [];
?>
<div class="portfolio-sidebar">
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-collection text-orange"></i> Проекты</h5>
        <ul class="list-unstyled mb-0">
            <?php $sidebarProjectIndex = 0; foreach ($sidebarProjects as $sidebarProject): ?>
                <li<?= $sidebarProjectIndex < count($sidebarProjects) - 1 ? ' class="mb-2"' : '' ?>><a href="/<?= htmlspecialchars($sidebarProject['slug'], ENT_QUOTES, 'UTF-8') ?>.html" class="fw-bold text-orange link-orange"><?= htmlspecialchars($sidebarProject['title'], ENT_QUOTES, 'UTF-8') ?></a></li>
            <?php $sidebarProjectIndex++; endforeach; ?>
            <?php if (empty($sidebarProjects)): ?>
                <li class="fw-bold text-muted">Проектов пока нет</li>
            <?php endif; ?>
        </ul>
    </div>
</div>
