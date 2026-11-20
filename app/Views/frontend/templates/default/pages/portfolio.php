<!-- ================= ШАПКА / МЕНЮ ================= -->
    

    <!-- ================= ЗАГОЛОВОК СТРАНИЦЫ ================= -->
    <?php $heading = $portfolioHeading ?? 'Портфолио'; $headingLead = $portfolioHeadingLead ?? 'Мои лучшие работы и проекты'; require APP_ROOT . '/app/Views/frontend/templates/default/partials/page-heading.php'; ?>

    <!-- Волна: оранжевый → светло-оранжевый -->
    <?php $waveColors = ['#ff8c1a', '#fff3e6']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ПОРТФОЛИО ================= -->
    <section class="py-5 bg-orange-light">
        <div class="container">
            <?php if (($portfolioLayout ?? 'grid') === 'masonry'): ?>
                <!-- Мозаика (Настройки → Портфолио → «Тип отображения») -->
                <div class="masonry-grid">
                    <?php $portfolioMasonry = true; ?>
                    <?php foreach (($projects ?? []) as $projectSlug => $project): ?>
                        <?php require APP_ROOT . '/app/Views/frontend/templates/default/components/portfolio-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach (($projects ?? []) as $projectSlug => $project): ?>
                        <?php require APP_ROOT . '/app/Views/frontend/templates/default/components/portfolio-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Пагинация (Работ на странице — Настройки → Портфолио) -->
            <?php $portfolioPagination = is_array($pagination ?? null) ? $pagination : null; ?>
            <?php if ($portfolioPagination !== null && (int) $portfolioPagination['pages'] > 1): ?>
                <nav class="mt-5" aria-label="Постраничная навигация">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= (int) $portfolioPagination['current'] <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link fw-bold" href="/portfolio.html?page=<?= max(1, (int) $portfolioPagination['current'] - 1) ?>" aria-label="Предыдущая страница">Назад</a>
                        </li>
                        <?php for ($portfolioPage = 1; $portfolioPage <= (int) $portfolioPagination['pages']; $portfolioPage++): ?>
                            <li class="page-item <?= $portfolioPage === (int) $portfolioPagination['current'] ? 'active' : '' ?>">
                                <a class="page-link fw-bold" href="/portfolio.html?page=<?= $portfolioPage ?>"><?= $portfolioPage ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= (int) $portfolioPagination['current'] >= (int) $portfolioPagination['pages'] ? 'disabled' : '' ?>">
                            <a class="page-link fw-bold" href="/portfolio.html?page=<?= min((int) $portfolioPagination['pages'], (int) $portfolioPagination['current'] + 1) ?>" aria-label="Следующая страница">Вперёд</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

            <div class="text-center mt-5">
                <a href="order.html" class="btn btn-orange btn-lg fw-bold px-5">
                    Хочу такой проект <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Волна: светло-оранжевый → тёмный -->
    <?php $waveColors = ['#fff3e6', '#353535']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ПОДВАЛ ================= -->
