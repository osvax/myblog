<section class="row g-4 mb-4" aria-label="Краткая статистика">
    <?php foreach (($statistics ?? []) as $stat): ?>
        <div class="col-sm-6 col-xl-3">
            <a class="stat-card p-3 d-flex align-items-center gap-3 text-decoration-none" href="<?= htmlspecialchars($stat['url'], ENT_QUOTES, 'UTF-8') ?>">
                <span class="stat-icon"><i class="bi <?= htmlspecialchars($stat['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                <span><strong class="h3 d-block mb-0"><?= htmlspecialchars($stat['value'], ENT_QUOTES, 'UTF-8') ?></strong><span class="fw-bold text-muted"><?= htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8') ?></span></span>
            </a>
        </div>
    <?php endforeach; ?>
</section>

<section class="card admin-card mb-4">
    <div class="card-header bg-white"><h2 class="h5 fw-bold mb-0">Добро пожаловать</h2></div>
    <div class="card-body">
        <p class="mb-0">Это главная страница административной части MVC. Разделы сайта подключаются отдельно контроллерами и моделями.</p>
    </div>
</section>

<h2 class="h5 fw-bold mb-3">Быстрые действия</h2>
<div class="row g-3">
    <?php foreach (($actions ?? []) as $action): ?>
        <div class="col-md-4">
            <a href="<?= htmlspecialchars($action['url'], ENT_QUOTES, 'UTF-8') ?>" class="stat-card p-3 d-flex align-items-center gap-3 text-decoration-none">
                <span class="stat-icon"><i class="bi <?= htmlspecialchars($action['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                <span class="fw-bold text-dark"><?= htmlspecialchars($action['label'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../partials/activity-card.php'; ?>
