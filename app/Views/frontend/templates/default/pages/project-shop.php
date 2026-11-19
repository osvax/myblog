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
                        Полноценный интернет-магазин товаров для дома, разработанный по заказу небольшой
                        торговой компании. Магазин позволяет посетителям просматривать каталог,
                        фильтровать товары по категориям и цене, добавлять их в корзину и оформлять заказ
                        без перезагрузки страницы.
                    </p>
                    <p class="fw-bold">
                        Интерфейс выполнен в лаконичном стиле с акцентом на товары: крупные фотографии,
                        понятные карточки и удобная навигация по категориям. Вёрстка полностью адаптивная —
                        магазин одинаково хорошо работает на смартфонах, планшетах и настольных компьютерах.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3">Задача</h2>
                    <ul class="fw-bold">
                        <li>Разработать каталог товаров с фильтрацией и сортировкой</li>
                        <li>Реализовать корзину с пересчётом суммы заказа в реальном времени</li>
                        <li>Сделать многошаговую форму оформления заказа</li>
                        <li>Обеспечить скорость загрузки не более 2 секунд</li>
                    </ul>

                    <h2 class="fw-bold mt-5 mb-3">Решение</h2>
                    <p class="fw-bold">
                        Сетка и компоненты построены на Bootstrap 5, что позволило быстро собрать
                        адаптивный каталог и карточки товаров. Корзина реализована на чистом JavaScript
                        с сохранением состояния в localStorage — заказ не теряется при перезагрузке
                        страницы. Изображения товаров оптимизированы в формате WebP и подгружаются
                        лениво (lazy loading), что заметно ускорило первую загрузку каталога.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3">Результат</h2>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">1,8 с</h3>
                                <p class="fw-bold mb-0">средняя загрузка страницы</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">+35%</h3>
                                <p class="fw-bold mb-0">рост конверсии в заказ</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="feature-item p-3 text-center h-100">
                                <h3 class="fw-bold text-orange mb-1">120+</h3>
                                <p class="fw-bold mb-0">товаров в каталоге</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Боковая панель -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 mb-4">
                        <h5 class="fw-bold mb-3">Информация о проекте</h5>
                        <p class="fw-bold mb-2"><i class="bi bi-person text-orange"></i> <strong>Клиент:</strong> ООО «ДомТорг»</p>
                        <p class="fw-bold mb-2"><i class="bi bi-calendar3 text-orange"></i> <strong>Год:</strong> 2026</p>
                        <p class="fw-bold mb-2"><i class="bi bi-clock-history text-orange"></i> <strong>Сроки:</strong> 6 недель</p>
                        <p class="fw-bold mb-3"><i class="bi bi-tools text-orange"></i> <strong>Стек:</strong> HTML5, CSS3, Bootstrap 5, JavaScript</p>
                        <?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-info-items.php'; ?>
                        <a href="order.html" class="btn btn-orange fw-bold">Хочу похожий проект</a>
                    </div>
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3">Другие проекты</h5>
                        <a href="project-blog.html" class="fw-bold text-orange link-orange d-block mb-2"><i class="bi bi-arrow-right"></i> Корпоративный блог</a>
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
    <?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-screenshots.php'; ?>

    <!-- Волна: светло-оранжевый → тёмный -->
    <?php $waveColors = ['#fff3e6', '#353535']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ПОДВАЛ ================= -->
