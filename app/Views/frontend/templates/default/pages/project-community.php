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
                        Сайт городского сообщества любителей настольных игр: каталог клубов,
                        календарь встреч и мероприятий, профили участников и форум. Проект
                        начинался с полного цикла UI/UX — от исследования аудитории до
                        интерактивного прототипа, а затем перешёл в разработку.
                    </p>
                    <p class="fw-bold">
                        Аудитория самая разная — от школьников до людей старшего возраста,
                        поэтому интерфейс сделан максимально простым и понятным: крупные
                        элементы управления, понятные подписи и минимум вложенности.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3">Задача</h2>
                    <ul class="fw-bold">
                        <li>Провести интервью с участниками сообщества и собрать требования</li>
                        <li>Спроектировать прототип ключевых экранов в Figma</li>
                        <li>Сверстать календарь событий и карточки клубов</li>
                        <li>Реализовать форму записи на мероприятие</li>
                    </ul>

                    <h2 class="fw-bold mt-5 mb-3">Решение</h2>
                    <p class="fw-bold">
                        Прототип был протестирован на пяти участниках сообщества: по итогам
                        переработали навигацию — объединили разделы «Клубы» и «Карта» в один
                        экран с фильтрами. Вёрстка выполнена на Bootstrap 5: календарь собран
                        на адаптивной сетке, форма записи — на штатных компонентах форм
                        с валидацией.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3">Результат</h2>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">2 500+</h3>
                                <p class="fw-bold mb-0">участников сообщества</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">40+</h3>
                                <p class="fw-bold mb-0">мероприятий в месяц</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">4,8/5</h3>
                                <p class="fw-bold mb-0">оценка удобства от пользователей</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Боковая панель -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 mb-4">
                        <h5 class="fw-bold mb-3">Информация о проекте</h5>
                        <p class="fw-bold mb-2"><i class="bi bi-person text-orange"></i> <strong>Клиент:</strong> сообщество «Игровой вечер»</p>
                        <p class="fw-bold mb-2"><i class="bi bi-calendar3 text-orange"></i> <strong>Год:</strong> 2025</p>
                        <p class="fw-bold mb-2"><i class="bi bi-clock-history text-orange"></i> <strong>Сроки:</strong> 8 недель</p>
                        <p class="fw-bold mb-3"><i class="bi bi-tools text-orange"></i> <strong>Стек:</strong> Figma, HTML5, CSS3, Bootstrap 5</p>
                        <a href="order.html" class="btn btn-orange fw-bold">Хочу похожий проект</a>
                    </div>
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3">Другие проекты</h5>
                        <a href="project-shop.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Интернет-магазин</a>
                        <a href="project-blog.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Корпоративный блог</a>
                        <a href="project-landing.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Лендинг приложения</a>
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
                        <i class="bi bi-house display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">Главная страница — img/community-home.webp</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="screenshot bg-orange-light rounded d-flex align-items-center justify-content-center flex-column p-4">
                        <i class="bi bi-calendar-week display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">Календарь событий — img/community-calendar.webp</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="screenshot bg-orange-light rounded d-flex align-items-center justify-content-center flex-column p-4">
                        <i class="bi bi-pin-map display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">Клубы на карте — img/community-map.webp</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="screenshot bg-orange-light rounded d-flex align-items-center justify-content-center flex-column p-4">
                        <i class="bi bi-chat-left-text display-4 text-orange mb-2"></i>
                        <p class="fw-bold text-muted mb-0">Форум сообщества — img/community-forum.webp</p>
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
