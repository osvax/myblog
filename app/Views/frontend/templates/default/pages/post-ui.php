<!-- ================= ШАПКА / МЕНЮ ================= -->
    

    <!-- ================= ЗАГОЛОВОК СТАТЬИ ================= -->
    <?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/article-heading.php'; ?>

    <!-- Волна: оранжевый → белый -->
    <?php $waveColors = ['#ff8c1a', '#ffffff']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= СТАТЬЯ + САЙДБАР ================= -->
    <section class="py-5">
        <div class="container">
            <div class="row g-5">

                <!-- Статья -->
                <div class="col-lg-8">
                    <article>
                        <img src="https://placehold.co/800x400/ff8c1a/ffffff?text=UI-дизайн" class="img-fluid rounded mb-4" alt="Обложка статьи: Основы UI-дизайна">
                        <h2 class="fw-bold mb-3">Что такое UI-дизайн</h2>
                        <p class="fw-bold">
                            UI (User Interface) — это визуальная часть интерфейса: кнопки, поля,
                            иконки, шрифты и цвета. Хорошего UI пользователь не замечает, плохого —
                            не прощает: неудобный интерфейс теряет посетителей независимо от
                            качества контента.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Цвет: меньше — лучше</h2>
                        <p class="fw-bold">
                            Правило простое: один акцентный цвет и пара нейтральных. Акцентный
                            цвет выделяет главное — кнопки, ссылки, важные элементы. Например,
                            оранжевый на светлом фоне отлично «поднимает» конверсию, потому что
                            создаёт ощущение энергии и тепла.
                        </p>
                        <p class="fw-bold">
                            Обязательно проверяйте контрастность текста и фона — стандарт
                            WCAG рекомендует не менее 4.5:1 для основного текста. Тёмно-серый
                            #353535 на белом фоне читается гораздо легче, чем чистый чёрный.
                        </p>

                        <img src="https://placehold.co/800x300/353535/ff8c1a?text=Цвет+и+контраст" class="img-fluid rounded my-4" alt="Иллюстрация: цвет и контраст в UI">
                        <h2 class="fw-bold mt-4 mb-3">Типографика</h2>
                        <ul class="fw-bold">
                            <li>Не более двух шрифтовых семейств на сайте;</li>
                            <li>Межстрочный интервал 1.4–1.6 для основного текста;</li>
                            <li>Длина строки 60–80 символов — так текст читается комфортнее;</li>
                            <li>Заголовки образуют иерархию: H1 один на странице, H2 — разделы.</li>
                        </ul>

                        <h2 class="fw-bold mt-4 mb-3">Композиция и отступы</h2>
                        <p class="fw-bold">
                            Выравнивайте элементы по сетке и держите единый шаг отступов
                            (например, 8px: 8, 16, 24, 32). Воздух в макете — не потерянное место,
                            а инструмент: разделы, разделённые отступами, воспринимаются легче,
                            чем разделённые линиями.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Вывод</h2>
                        <p class="fw-bold">
                            Хороший UI строится на трёх китах: ограниченная палитра, аккуратная
                            типографика и дисциплина отступов. Проверяйте макет вопросом
                            «понятно ли за 5 секунд, что здесь делать?» — если да, всё сделано
                            правильно.
                        </p>

                        <!-- Метки -->
                        <div class="mt-4">
                            <span class="badge bg-orange-light text-orange fw-bold me-1 p-2">#ui</span>
                            <span class="badge bg-orange-light text-orange fw-bold me-1 p-2">#дизайн</span>
                            <span class="badge bg-orange-light text-orange fw-bold p-2">#типографика</span>
                        </div>

                        <!-- Навигация между статьями -->
                        <div class="d-flex justify-content-between mt-5">
                            <a href="post-bootstrap.html" class="btn btn-outline-dark fw-bold"><i class="bi bi-arrow-left"></i> Предыдущая</a>
                            <a href="post-js.html" class="btn btn-orange fw-bold">Следующая статья <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </article>

                    <?php require APP_ROOT . '/app/Views/frontend/templates/default/components/comments-section.php'; ?>
                </div>

                <!-- ================= САЙДБАР ================= -->
                <aside class="col-lg-4">
                    <?php require APP_ROOT . '/app/Views/frontend/templates/default/components/categories-sidebar.php'; ?>

                    <div class="card border-0 shadow-sm p-4 mb-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-orange"></i> Свежие записи</h5>
                        <a href="post-bootstrap.html" class="fw-bold text-orange link-orange d-block mb-2">Верстаем на Bootstrap 5</a>
                        <a href="post-ui.html" class="fw-bold text-orange link-orange d-block mb-2">Основы UI-дизайна</a>
                        <a href="post-js.html" class="fw-bold text-orange link-orange d-block mb-2">JavaScript для начинающих</a>
                        <a href="post-adaptive.html" class="fw-bold text-orange link-orange d-block mb-2">Адаптивная вёрстка</a>
                        <a href="post-git.html" class="fw-bold text-orange link-orange d-block">Git за один вечер</a>
                    </div>

                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-envelope text-orange"></i> Подписка</h5>
                        <p class="fw-bold small">Раз в неделю — письмо с новыми статьями. Без спама.</p>
                        <form>
                            <input type="email" class="form-control mb-2" placeholder="Ваш email" required>
                            <button type="submit" class="btn btn-orange fw-bold w-100">Подписаться</button>
                        </form>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <!-- Волна: белый → тёмный -->
    <?php $waveColors = ['#ffffff', '#353535']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ПОДВАЛ ================= -->
