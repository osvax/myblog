<?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-heading.php'; ?>
<?php $waveColors = ['#ff8c1a', '#ffffff']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-3">О проекте</h2>
                <div class="fw-bold"><?= nl2br(htmlspecialchars($project['description'], ENT_QUOTES, 'UTF-8')) ?></div>
                <?php if (!empty($project['task'])): ?>
                    <h2 class="fw-bold mt-5 mb-3">Задача</h2>
                    <ul class="fw-bold"><?php foreach ($project['task'] as $task): ?><li><?= htmlspecialchars($task, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
                <?php endif; ?>
                <?php if (!empty($project['solution'])): ?>
                    <h2 class="fw-bold mt-5 mb-3">Решение</h2>
                    <div class="fw-bold"><?= nl2br(htmlspecialchars($project['solution'], ENT_QUOTES, 'UTF-8')) ?></div>
                <?php endif; ?>
                <?php if (!empty($project['results'])): ?>
                    <h2 class="fw-bold mt-5 mb-3">Результаты</h2>
                    <div class="row g-3"><?php foreach ($project['results'] as $result): ?>
                        <div class="col-sm-4"><div class="feature-item p-3 text-center h-100"><h3 class="fw-bold text-orange mb-1"><?= htmlspecialchars($result['value'] ?? '', ENT_QUOTES, 'UTF-8') ?></h3><p class="fw-bold mb-0"><?= htmlspecialchars($result['label'] ?? '', ENT_QUOTES, 'UTF-8') ?></p></div></div>
                    <?php endforeach; ?></div>
                <?php endif; ?>
                <?php if (!empty($project['screenshots'])): ?>
                    <h2 class="fw-bold mt-5 mb-3">Галерея проекта</h2>
                    <div class="row g-3"><?php foreach ($project['screenshots'] as $image): ?>
                        <div class="col-md-6"><img src="/<?= htmlspecialchars(ltrim($image, '/'), ENT_QUOTES, 'UTF-8') ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8') ?>"></div>
                    <?php endforeach; ?></div>
                <?php endif; ?>
            </div>
            <aside class="col-lg-4">
                <div class="card border-0 shadow-sm p-4">
                    <h5 class="fw-bold mb-3">Информация о проекте</h5>
                    <?php foreach (['client' => 'Клиент', 'project_year' => 'Год', 'duration' => 'Сроки', 'stack' => 'Стек'] as $field => $label): ?>
                        <?php if (!empty($project[$field])): ?><p class="fw-bold mb-2"><strong><?= $label ?>:</strong> <?= htmlspecialchars((string) $project[$field], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                    <?php endforeach; ?>
                    <a href="/order.html" class="btn btn-orange fw-bold mt-2">Хочу похожий проект</a>
                </div>
            </aside>
        </div>
    </div>
</section>
