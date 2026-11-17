<!-- ================= ШАПКА / МЕНЮ ================= -->
    

    <!-- ================= ГЛАВНАЯ (ГЕРОЙ) ================= -->
    <section class="bg-orange text-white">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-7 py-5">
                    <h1 class="display-4 fw-bold mb-4">Личный блог о веб-разработке и дизайне</h1>
                    <p class="lead fw-bold mb-4">
                        Здесь я делюсь опытом, статьями и проектами.
                        Всё самое интересное — в одном месте.
                    </p>
                    <a href="blog.html" class="btn btn-light btn-lg fw-bold text-orange me-2 mb-2">
                        Читать блог <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="order.html" class="btn btn-outline-light btn-lg fw-bold mb-2">Заказать проект</a>
                </div>
                <div class="col-lg-5 text-center d-none d-lg-block">
                    <img src="/img/hero-terminal.png" class="img-fluid hero-img" alt="Большой терминал с введённым JavaScript-кодом">
                </div>
            </div>
        </div>
    </section>

    <!-- Волна: оранжевый → белый -->
    <?php $waveColors = ['#ff8c1a', '#ffffff']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= РАЗДЕЛ «БЛОГ» (превью статей) ================= -->
    <section id="blog-preview" class="lp-section py-5">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-5">
                    <span class="badge bg-orange fw-bold mb-3 p-2">Раздел «Блог»</span>
                    <h2 class="fw-bold display-5">Свежие статьи</h2>
                    <p class="fw-bold text-muted">
                        Практические материалы по вёрстке, дизайну и разработке.
                        Каждая статья — с примерами кода, иллюстрациями и обсуждением в комментариях.
                    </p>
                    <a href="blog.html" class="btn btn-orange btn-lg fw-bold px-4">
                        Все статьи <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="col-lg-7">
                    <?php foreach (($featuredArticles ?? []) as $articleSlug => $article): ?>
                        <?php require APP_ROOT . '/app/Views/frontend/templates/default/components/home-article.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Волна: белый → тёмный -->
    <?php $waveColors = ['#ffffff', '#353535']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= РАЗДЕЛ «ПОРТФОЛИО» (тёмная секция) ================= -->
    <section id="portfolio-preview" class="lp-portfolio py-5">
        <div class="container text-white">
            <div class="text-center mb-5">
                <span class="badge bg-orange fw-bold mb-3 p-2">Раздел «Портфолио»</span>
                <h2 class="fw-bold display-5">Мои работы</h2>
                <p class="fw-bold text-white-50">Каждый проект — со страницей, описанием и скриншотами</p>
            </div>
            <div class="row g-4">
                <?php foreach (($featuredProjects ?? []) as $projectSlug => $project): ?>
                    <?php require APP_ROOT . '/app/Views/frontend/templates/default/components/home-project.php'; ?>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5">
                <a href="portfolio.html" class="btn btn-orange btn-lg fw-bold px-5">
                    Весь портфолио <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Волна: тёмный → оранжевый -->
    <?php $waveColors = ['#353535', '#ff8c1a']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= РАЗДЕЛ «ЗАКАЗАТЬ» (шаги + CTA) ================= -->
    <section id="order-preview" class="bg-orange text-white py-5">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="badge bg-white text-orange fw-bold mb-3 p-2">Раздел «Заказать»</span>
                <h2 class="fw-bold display-5">Как мы начнём работать</h2>
            </div>
            <div class="row g-4 text-center mb-5">
                <div class="col-md-4">
                    <div class="lp-step p-4 h-100">
                        <div class="lp-step-num mx-auto mb-3">1</div>
                        <h5 class="fw-bold">Оставляете заявку</h5>
                        <p class="fw-bold mb-0">Заполняете форму на странице заказа — 2 минуты</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="lp-step p-4 h-100">
                        <div class="lp-step-num mx-auto mb-3">2</div>
                        <h5 class="fw-bold">Обсуждаем детали</h5>
                        <p class="fw-bold mb-0">Связываюсь с вами в течение дня и уточняем задачу</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="lp-step p-4 h-100">
                        <div class="lp-step-num mx-auto mb-3">3</div>
                        <h5 class="fw-bold">Получаете результат</h5>
                        <p class="fw-bold mb-0">Готовый сайт точно в срок и с гарантией</p>
                    </div>
                </div>
            </div>
            <div class="text-center">
                <a href="order.html" class="btn btn-light btn-lg fw-bold text-orange px-5">
                    Заполнить заявку <i class="bi bi-send"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Волна: оранжевый → белый -->
    <?php $waveColors = ['#ff8c1a', '#ffffff']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= РАЗДЕЛ «КОНТАКТЫ» (карточки + соцсети) ================= -->
    <section id="contacts-preview" class="lp-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-orange fw-bold mb-3 p-2">Раздел «Контакты»</span>
                <h2 class="fw-bold display-5">Связаться со мной</h2>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-4 text-center">
                    <div class="feature-item p-4 h-100">
                        <i class="bi bi-envelope display-6 text-orange"></i>
                        <h5 class="fw-bold mt-3">Email</h5>
                        <p class="fw-bold mb-0">hello@moyblog.ru</p>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="feature-item p-4 h-100">
                        <i class="bi bi-telephone display-6 text-orange"></i>
                        <h5 class="fw-bold mt-3">Телефон</h5>
                        <p class="fw-bold mb-0">+7 (900) 123-45-67</p>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="feature-item p-4 h-100">
                        <i class="bi bi-geo-alt display-6 text-orange"></i>
                        <h5 class="fw-bold mt-3">Адрес</h5>
                        <p class="fw-bold mb-0">г. Самара, ул. Примерная, 1</p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <a href="contacts.html" class="btn btn-orange btn-lg fw-bold px-5">
                    Все контакты и форма связи <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Волна: белый → тёмный подвал -->
    <?php $waveColors = ['#ffffff', '#353535']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ПОДВАЛ ================= -->
