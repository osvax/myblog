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
                    <h2 class="fw-bold mb-3">О проекте</h2>
                    <p class="fw-bold">
                        Сайт-визитка свадебного и портретного фотографа. Главная задача сайта —
                        показать работы так, чтобы фотографии говорили сами за себя: галерея
                        занимает большую часть экранов, а текстовые блоки сведены к минимуму.
                    </p>
                    <p class="fw-bold">
                        Сайт решает и практическую задачу: посетитель может посмотреть портфолио,
                        ознакомиться с пакетами услуг и ценами и сразу оставить заявку
                        на свободную дату съёмки.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3">Задача</h2>
                    <ul class="fw-bold">
                        <li>Сделать галерею с фильтром по жанрам съёмки</li>
                        <li>Реализовать лайтбокс с полноэкранным просмотром фото</li>
                        <li>Сверстать блок тарифов и форму заявки на дату</li>
                        <li>Оптимизировать загрузку тяжёлых фотографий</li>
                    </ul>

                    <h2 class="fw-bold mt-5 mb-3">Решение</h2>
                    <p class="fw-bold">
                        Галерея построена на адаптивной сетке Bootstrap 5 с фильтрацией по
                        жанрам на клиенте. Для полноэкранного просмотра реализован лайтбокс
                        с навигацией стрелками и жестами, без тяжёлых внешних плагинов.
                        Фотографии конвертированы в WebP и отдаются в нескольких размерах
                        через атрибут srcset — на смартфонах грузится уменьшенная копия.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3">Результат</h2>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">300+</h3>
                                <p class="fw-bold mb-0">фотографий в галерее</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">+50%</h3>
                                <p class="fw-bold mb-0">заявок на съёмку за 3 месяца</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">100</h3>
                                <p class="fw-bold mb-0">балл PageSpeed на десктопе</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Боковая панель -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 mb-4">
                        <h5 class="fw-bold mb-3">Информация о проекте</h5>
                        <p class="fw-bold mb-2"><i class="bi bi-person text-orange"></i> <strong>Клиент:</strong> фотограф Анна Смирнова</p>
                        <p class="fw-bold mb-2"><i class="bi bi-calendar3 text-orange"></i> <strong>Год:</strong> 2024</p>
                        <p class="fw-bold mb-2"><i class="bi bi-clock-history text-orange"></i> <strong>Сроки:</strong> 3 недели</p>
                        <p class="fw-bold mb-3"><i class="bi bi-tools text-orange"></i> <strong>Стек:</strong> HTML5, CSS3, Bootstrap 5, WebP, srcset</p>
                        <?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-info-items.php'; ?>
                        <a href="order.html" class="btn btn-orange fw-bold">Хочу похожий проект</a>
                    </div>
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3">Другие проекты</h5>
                        <a href="project-shop.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Интернет-магазин</a>
                        <a href="project-blog.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Корпоративный блог</a>
                        <a href="project-landing.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Лендинг приложения</a>
                        <a href="project-community.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Сайт сообщества</a>
                        <a href="project-dashboard.html" class="fw-bold text-orange link-orange d-block"><i class="bi bi-arrow-right"></i> Панель аналитики</a>
                    </div>
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
