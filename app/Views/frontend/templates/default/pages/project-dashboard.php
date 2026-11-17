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
                        Внутренняя панель аналитики для интернет-магазина: сводные метрики продаж,
                        динамика по месяцам, воронка заказов и топ товаров. Панель заменила
                        набор разрозненных Excel-таблиц и стала ежедневным рабочим инструментом
                        отдела маркетинга.
                    </p>
                    <p class="fw-bold">
                        Данные подгружаются из внутреннего API и визуализируются графиками:
                        линейные графики динамики, столбчатые диаграммы по категориям
                        и кольцевые диаграммы распределения заказов.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3">Задача</h2>
                    <ul class="fw-bold">
                        <li>Сверстать дашборд с KPI-карточками и графиками</li>
                        <li>Реализовать фильтры по периоду и категориям</li>
                        <li>Добавить тёмную тему интерфейса</li>
                        <li>Обеспечить корректное отображение на планшетах</li>
                    </ul>

                    <h2 class="fw-bold mt-5 mb-3">Решение</h2>
                    <p class="fw-bold">
                        Каркас панели собран на Bootstrap 5: сайдбар навигации, сетка карточек
                        и модальные окна с детализацией метрик. Для графиков использована
                        библиотека Chart.js — она лёгкая и отлично сочетается с адаптивной
                        сеткой Bootstrap. Тёмная тема реализована через CSS-переменные
                        и переключатель в шапке.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3">Результат</h2>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">15+</h3>
                                <p class="fw-bold mb-0">метрик на одном экране</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">−70%</h3>
                                <p class="fw-bold mb-0">времени на подготовку отчётов</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">24/7</h3>
                                <p class="fw-bold mb-0">доступ к данным онлайн</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Боковая панель -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 mb-4">
                        <h5 class="fw-bold mb-3">Информация о проекте</h5>
                        <p class="fw-bold mb-2"><i class="bi bi-person text-orange"></i> <strong>Клиент:</strong> магазин «ТехноСити»</p>
                        <p class="fw-bold mb-2"><i class="bi bi-calendar3 text-orange"></i> <strong>Год:</strong> 2024</p>
                        <p class="fw-bold mb-2"><i class="bi bi-clock-history text-orange"></i> <strong>Сроки:</strong> 5 недель</p>
                        <p class="fw-bold mb-3"><i class="bi bi-tools text-orange"></i> <strong>Стек:</strong> Bootstrap 5, Chart.js, JavaScript, REST API</p>
                        <a href="order.html" class="btn btn-orange fw-bold">Хочу похожий проект</a>
                    </div>
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3">Другие проекты</h5>
                        <a href="project-shop.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Интернет-магазин</a>
                        <a href="project-blog.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Корпоративный блог</a>
                        <a href="project-landing.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Лендинг приложения</a>
                        <a href="project-community.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Сайт сообщества</a>
                        <a href="project-photo.html" class="fw-bold text-orange link-orange d-block"><i class="bi bi-arrow-right"></i> Портфолио фотографа</a>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Волна: белый → светло-оранжевый -->
    <?php $waveColors = ['#ffffff', '#fff3e6']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= СКРИНШОТЫ ПРОЕКТА ================= -->
    <section class="py-5 bg-orange-light">
        <div class="container">
            <h2 class="fw-bold mb-4 text-center">Скриншоты проекта</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="screenshot bg-orange-light rounded d-flex align-items-center justify-content-center flex-column p-4">
                        <i class="bi bi-speedometer display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">Главный экран дашборда — img/dashboard-main.webp</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="screenshot bg-orange-light rounded d-flex align-items-center justify-content-center flex-column p-4">
                        <i class="bi bi-graph-up display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">График динамики продаж — img/dashboard-sales.webp</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="screenshot bg-orange-light rounded d-flex align-items-center justify-content-center flex-column p-4">
                        <i class="bi bi-funnel display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">Воронка заказов — img/dashboard-funnel.webp</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="screenshot bg-orange-light rounded d-flex align-items-center justify-content-center flex-column p-4">
                        <i class="bi bi-moon-stars display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">Тёмная тема — img/dashboard-dark.webp</p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5">
                <a href="portfolio.html" class="btn btn-outline-dark btn-lg fw-bold">
                    <i class="bi bi-arrow-left"></i> Вернуться в портфолио
                </a>
            </div>
        </div>
    </section>

    <!-- Волна: светло-оранжевый → тёмный -->
    <?php $waveColors = ['#fff3e6', '#353535']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ПОДВАЛ ================= -->
