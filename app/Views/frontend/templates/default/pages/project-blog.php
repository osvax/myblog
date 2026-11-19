<!-- ================= ШАПКА / МЕНЮ ================= -->


    <!-- ================= ЗАГОЛОВОК ПРОЕКТА ================= -->
    <?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-heading.php'; ?>

    <!-- Волна: оранжевый → белый -->
    <?php $waveColors = ['#ff8c1a', '#ffffff']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ОПИСАНИЕ ПРОЕКТА ================= -->
    <section class="py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-8">
                    <?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-brief.php'; ?>
                </div>

                <!-- Боковая панель -->
                <div class="col-lg-4">
                    <?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-info-card.php'; ?>
                    <?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-other-projects.php'; ?>
                </div>
            </div>

        </div>
    </section>

    <!-- Волна: белый → светло-оранжевый -->
    <?php $waveColors = ['#ffffff', '#fff3e6']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= СКРИНШОТЫ ПРОЕКТА ================= -->
    <?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-screenshots.php'; ?>

    <!-- Волна: светло-оранжевый → тёмный -->
    <?php $waveColors = ['#fff3e6', '#353535']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ПОДВАЛ ================= -->
