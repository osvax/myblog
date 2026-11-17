<!-- Заглушка «Сайт на обслуживании» — в общем дизайне сайта -->

    <!-- ================= ЗАГОЛОВОК СТРАНИЦЫ ================= -->
    <?php $heading = 'Сайт на обслуживании'; $headingLead = 'Скоро вернёмся!'; require APP_ROOT . '/app/Views/frontend/templates/default/partials/page-heading.php'; ?>

    <!-- Волна: оранжевый → светло-оранжевый -->
    <?php $waveColors = ['#ff8c1a', '#fff3e6']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ЗАГЛУШКА ================= -->
    <section class="py-5 bg-orange-light">
        <div class="container text-center py-4">
            <i class="bi bi-tools display-1 text-orange"></i>
            <h2 class="fw-bold display-6 mt-4">Ведутся технические работы</h2>
            <p class="fw-bold text-muted lead mb-4">
                <?= htmlspecialchars($maintenanceMessage ?? 'Сайт временно закрыт на техническое обслуживание. Скоро вернёмся!', ENT_QUOTES, 'UTF-8') ?>
            </p>
            <?php if (!empty($user)): ?>
                <p class="fw-bold text-muted">Вы вошли как <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?> — доступ к сайту будет открыт снова после окончания работ.</p>
            <?php else: ?>
                <a href="/contacts.html" class="btn btn-orange btn-lg fw-bold px-4">
                    Связаться с нами <i class="bi bi-envelope"></i>
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Волна: светло-оранжевый → тёмный -->
    <?php $waveColors = ['#fff3e6', '#353535']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>
