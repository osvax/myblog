<!-- ================= ШАПКА / МЕНЮ ================= -->
    

    <!-- ================= ЗАГОЛОВОК СТРАНИЦЫ ================= -->
    <?php $heading = $portfolioHeading ?? 'Портфолио'; $headingLead = $portfolioHeadingLead ?? 'Мои лучшие работы и проекты'; require APP_ROOT . '/app/Views/frontend/templates/default/partials/page-heading.php'; ?>

    <!-- Волна: оранжевый → светло-оранжевый -->
    <?php $waveColors = ['#ff8c1a', '#fff3e6']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ПОРТФОЛИО ================= -->
    <section class="py-5 bg-orange-light">
        <div class="container">
            <div class="row g-4">
                <?php foreach (($projects ?? []) as $projectSlug => $project): ?>
                    <?php require APP_ROOT . '/app/Views/frontend/templates/default/components/portfolio-card.php'; ?>
                <?php endforeach; ?>
            </div>
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
