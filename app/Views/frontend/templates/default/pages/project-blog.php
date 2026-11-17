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
                        Корпоративный блог IT-компании, в котором сотрудники публикуют статьи о
                        технологиях, внутренних процессах и корпоративной культуре. Блог стал
                        частью HR-бренда компании: через него соискатели знакомятся с командой
                        и атмосферой внутри.
                    </p>
                    <p class="fw-bold">
                        Дизайн выдержан в фирменных цветах компании, при этом сохранена единая
                        визуальная система со светлым фоном, крупной типографикой и оранжевыми
                        акцентами для кнопок и ссылок.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3">Задача</h2>
                    <ul class="fw-bold">
                        <li>Сделать удобный каталог статей с рубриками и тегами</li>
                        <li>Добавить поиск по публикациям</li>
                        <li>Сверстать шаблон статьи с галереей и врезками</li>
                        <li>Подключить комментарии и кнопки «Поделиться»</li>
                    </ul>

                    <h2 class="fw-bold mt-5 mb-3">Решение</h2>
                    <p class="fw-bold">
                        Проект собран на Bootstrap 5 с использованием Sass: переменные и миксины
                        позволили быстро переопределить стандартную тему Bootstrap под фирменный
                        стиль заказчика. Поиск по статьям работает мгновенно на клиенте —
                        статей меньше сотни, поэтому index строится прямо в браузере.
                        Шаблон статьи поддерживает оглавление с якорными ссылками и
                        автоматические врезки с цитатами.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3">Результат</h2>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">80+</h3>
                                <p class="fw-bold mb-0">статей опубликовано</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">×2</h3>
                                <p class="fw-bold mb-0">рост времени на сайте</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">98</h3>
                                <p class="fw-bold mb-0">балл PageSpeed</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Боковая панель -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 mb-4">
                        <h5 class="fw-bold mb-3">Информация о проекте</h5>
                        <p class="fw-bold mb-2"><i class="bi bi-person text-orange"></i> <strong>Клиент:</strong> IT-компания «Кодер»</p>
                        <p class="fw-bold mb-2"><i class="bi bi-calendar3 text-orange"></i> <strong>Год:</strong> 2025</p>
                        <p class="fw-bold mb-2"><i class="bi bi-clock-history text-orange"></i> <strong>Сроки:</strong> 4 недели</p>
                        <p class="fw-bold mb-3"><i class="bi bi-tools text-orange"></i> <strong>Стек:</strong> HTML5, Sass, Bootstrap 5, jQuery</p>
                        <a href="order.html" class="btn btn-orange fw-bold">Хочу похожий проект</a>
                    </div>
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3">Другие проекты</h5>
                        <a href="project-shop.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Интернет-магазин</a>
                        <a href="project-landing.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Лендинг приложения</a>
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
                        <i class="bi bi-house-door display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">Главная страница блога — img/blog-home.webp</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="screenshot bg-orange-light rounded d-flex align-items-center justify-content-center flex-column p-4">
                        <i class="bi bi-file-text display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">Страница статьи — img/blog-article.webp</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="screenshot bg-orange-light rounded d-flex align-items-center justify-content-center flex-column p-4">
                        <i class="bi bi-tags display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">Рубрики и теги — img/blog-tags.webp</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="screenshot bg-orange-light rounded d-flex align-items-center justify-content-center flex-column p-4">
                        <i class="bi bi-chat-square-text display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">Комментарии — img/blog-comments.webp</p>
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
