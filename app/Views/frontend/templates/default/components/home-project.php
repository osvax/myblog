<?php $homeProjectIcon = $projectSlug === 'project-community' ? 'bi-people-fill' : (string) ($project['icon'] ?? 'bi-briefcase'); ?>
<div class="col-sm-6 col-lg-3">
    <a href="/<?= htmlspecialchars($projectSlug, ENT_QUOTES, 'UTF-8') ?>.html" class="lp-work d-block p-4 text-center h-100 text-decoration-none text-white">
        <i class="bi <?= htmlspecialchars($homeProjectIcon, ENT_QUOTES, 'UTF-8') ?> display-5 text-orange"></i>
        <h3 class="h5 fw-bold mt-3 mb-0"><?= htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8') ?></h3>
    </a>
</div>
