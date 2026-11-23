<?php $homeSettings = is_array($home ?? null) ? $home : []; ?>
<section class="py-5" style="background: linear-gradient(135deg, #e8efff 0%, #ffffff 58%);">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-8">
                <span class="badge rounded-pill text-bg-light border mb-3">Test Light · 1.0.0</span>
                <h1 class="display-4 fw-bold mb-4"><?= htmlspecialchars($homeSettings['heroTitle'] ?? 'Личный блог о веб-разработке и дизайне', ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="lead mb-4"><?= htmlspecialchars($homeSettings['heroSubtitle'] ?? 'Светлая тестовая тема с чистой типографикой и спокойной палитрой.', ENT_QUOTES, 'UTF-8') ?></p>
                <a class="btn btn-primary btn-lg rounded-pill px-4" href="<?= htmlspecialchars($homeSettings['heroPrimaryUrl'] ?? '/blog.html', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($homeSettings['heroPrimaryText'] ?? 'Читать блог', ENT_QUOTES, 'UTF-8') ?></a>
            </div>
            <div class="col-lg-4">
                <div class="p-4 rounded-4 shadow-sm" style="background:#fff;border:1px solid #dfe8fb;">
                    <div class="small text-uppercase text-primary fw-bold mb-2">Описание шаблона</div>
                    <p class="mb-0">Минимальная тема-наследник: она переопределяет только главную страницу и layout, а остальные страницы берет из default.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <h2 class="fw-bold mb-4">Как это работает</h2>
        <div class="row g-4">
            <?php for ($stepNumber = 1; $stepNumber <= 3; $stepNumber++): ?>
                <div class="col-md-4"><div class="p-4 h-100 rounded-4" style="background:#fff;border:1px solid #dfe8fb;"><div class="display-6 fw-bold text-primary mb-3">0<?= $stepNumber ?></div><h3 class="h5 fw-bold"><?= htmlspecialchars($homeSettings['step' . $stepNumber . 'Title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h3><p class="mb-0 text-secondary"><?= htmlspecialchars($homeSettings['step' . $stepNumber . 'Text'] ?? '', ENT_QUOTES, 'UTF-8') ?></p></div></div>
            <?php endfor; ?>
        </div>
    </div>
</section>
