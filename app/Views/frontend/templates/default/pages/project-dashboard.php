<!-- ================= ШАПКА / МЕНЮ ================= -->


    <!-- ================= ЗАГОЛОВОК ПРОЕКТА ================= -->
    <?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-heading.php'; ?>

    <!-- Волна: оранжевый → белый -->
    <?php $waveColors = ['#ff8c1a', '#ffffff'];
    require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ОПИСАНИЕ ПРОЕКТА ================= -->
    <section class="py-5">
        <div class="container">
            <?php
            // Сайдбар страницы проекта (Настройки → Портфолио): слева / справа / выключен
            // (выключен — инфо-карточка и «Другие проекты» выводятся под описанием)
            $projectSide = in_array($projectPageSidebar ?? 'right', ['left', 'right'], true) ? $projectPageSidebar : ($projectPageSidebar === 'none' ? 'none' : 'right');
    ob_start();
    require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-info-card.php';
    require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-other-projects.php';
    $projectSidebarHtml = ob_get_clean();
    ?>
            <div class="row g-5">
                <?php if ($projectSide === 'left'): ?>
                    <aside class="col-lg-4 sidebar-block"><?= $projectSidebarHtml ?></aside>
                <?php endif; ?>
                <div class="<?= $projectSide === 'none' ? 'col-12' : 'col-lg-8' ?>">
                    <?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-brief.php'; ?>
                </div>
                <?php if ($projectSide === 'right'): ?>
                    <aside class="col-lg-4 sidebar-block"><?= $projectSidebarHtml ?></aside>
                <?php elseif ($projectSide === 'none'): ?>
                    <div class="col-12 sidebar-block"><?= $projectSidebarHtml ?></div>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <!-- Волна: белый → светло-оранжевый -->
    <?php $waveColors = ['#ffffff', '#fff3e6'];
    require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= СКРИНШОТЫ ПРОЕКТА ================= -->
    <?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-screenshots.php'; ?>

    <!-- Волна: светло-оранжевый → тёмный -->
    <?php $waveColors = ['#fff3e6', '#353535'];
    require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ПОДВАЛ ================= -->
