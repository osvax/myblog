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
                        Продающий лендинг для мобильного приложения-трекера привычек. Целевая
                        аудитория — пользователи смартфонов, поэтому основной упор сделан на
                        мобильную версию: крупные кнопки скачивания, короткие блоки и
                        динамичные иллюстрации экранов приложения.
                    </p>
                    <p class="fw-bold">
                        Структура страницы построена по классической воронке: экран с оффером —
                        преимущества — скриншоты — отзывы — тарифы — FAQ — финальный призыв.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3">Задача</h2>
                    <ul class="fw-bold">
                        <li>Сделать мобильную версию приоритетной (mobile-first вёрстка)</li>
                        <li>Добавить анимации появления блоков при прокрутке</li>
                        <li>Сверстать блок тарифов и FAQ-аккордеон</li>
                        <li>Интегрировать счётчики аналитики и цель «Скачать приложение»</li>
                    </ul>

                    <h2 class="fw-bold mt-5 mb-3">Решение</h2>
                    <p class="fw-bold">
                        Вёрстка выполнена mobile-first: сначала экран смартфона, затем
                        наращивание блоков для планшетов и десктопа через брейкпоинты Bootstrap.
                        Анимации реализованы лёгким скриптом с IntersectionObserver — блоки
                        плавно появляются при попадании в область видимости без сторонних
                        библиотек. FAQ собран на штатном аккордеоне Bootstrap 5.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3">Результат</h2>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">12%</h3>
                                <p class="fw-bold mb-0">конверсия в установку</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">50 000+</h3>
                                <p class="fw-bold mb-0">установок за первый месяц</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">0,9 с</h3>
                                <p class="fw-bold mb-0">загрузка на мобильном интернете</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Боковая панель -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 mb-4">
                        <h5 class="fw-bold mb-3">Информация о проекте</h5>
                        <p class="fw-bold mb-2"><i class="bi bi-person text-orange"></i> <strong>Клиент:</strong> студия «Хаббит»</p>
                        <p class="fw-bold mb-2"><i class="bi bi-calendar3 text-orange"></i> <strong>Год:</strong> 2025</p>
                        <p class="fw-bold mb-2"><i class="bi bi-clock-history text-orange"></i> <strong>Сроки:</strong> 2 недели</p>
                        <p class="fw-bold mb-3"><i class="bi bi-tools text-orange"></i> <strong>Стек:</strong> HTML5, CSS3, Bootstrap 5, IntersectionObserver</p>
                        <a href="order.html" class="btn btn-orange fw-bold">Хочу похожий проект</a>
                    </div>
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3">Другие проекты</h5>
                        <a href="project-shop.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Интернет-магазин</a>
                        <a href="project-blog.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Корпоративный блог</a>
                        <a href="project-community.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Сайт сообщества</a>
                        <a href="project-dashboard.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Панель аналитики</a>
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
                        <i class="bi bi-magic display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">Первый экран с оффером — img/landing-hero.webp</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="screenshot bg-orange-light rounded d-flex align-items-center justify-content-center flex-column p-4">
                        <i class="bi bi-collection display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">Блок преимуществ — img/landing-features.webp</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="screenshot bg-orange-light rounded d-flex align-items-center justify-content-center flex-column p-4">
                        <i class="bi bi-cash-coin display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">Тарифы — img/landing-pricing.webp</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="screenshot bg-orange-light rounded d-flex align-items-center justify-content-center flex-column p-4">
                        <i class="bi bi-patch-question display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">FAQ-аккордеон — img/landing-faq.webp</p>
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
