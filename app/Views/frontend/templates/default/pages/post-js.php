<!-- ================= ШАПКА / МЕНЮ ================= -->
    

    <!-- ================= ЗАГОЛОВОК СТАТЬИ ================= -->
    <?php require APP_ROOT . '/app/Views/frontend/templates/default/partials/article-heading.php'; ?>

    <!-- Волна: оранжевый → белый -->
    <?php $waveColors = ['#ff8c1a', '#ffffff'];
    require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= СТАТЬЯ + САЙДБАР ================= -->
    <section class="py-5">
        <div class="container">
            <div class="row g-5">

                <!-- Статья -->
                <div class="col-lg-8">
                    <article>
                        <img src="https://placehold.co/800x400/ff8c1a/ffffff?text=JavaScript" class="img-fluid rounded mb-4" alt="Обложка статьи: JavaScript для начинающих">
                        <h2 class="fw-bold mb-3">С чего начать</h2>
                        <p class="fw-bold">
                            JavaScript — язык, который оживляет веб-страницы: слайдеры, валидация
                            форм, корзины интернет-магазинов — всё это написано на JS. Хорошая
                            новость: для старта не нужно ничего, кроме браузера и редактора кода.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Переменные и типы данных</h2>
                        <p class="fw-bold">
                            Объявляйте переменные через <code>const</code>, если значение не будет
                            меняться, и <code>let</code> в остальных случаях. <code>var</code> —
                            устаревший способ, забудьте про него. Основные типы: числа, строки,
                            булевы значения, объекты и массивы.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Функции</h2>
                        <p class="fw-bold">
                            Функция — переиспользуемый блок кода. Современный синтаксис стрелочных
                            функций короче и удобнее: <code>const sum = (a, b) => a + b;</code>.
                            Начинайте с обычных функций — стрелочные поймёте по ходу дела.
                        </p>

                        <img src="https://placehold.co/800x300/353535/ff8c1a?text=Функции+и+события" class="img-fluid rounded my-4" alt="Иллюстрация: функции и события в JavaScript">
                        <h2 class="fw-bold mt-4 mb-3">События: как страница реагирует</h2>
                        <ul class="fw-bold">
                            <li><code>addEventListener('click', ...)</code> — реакция на клик;</li>
                            <li><code>addEventListener('input', ...)</code> — отслеживание ввода в поля;</li>
                            <li><code>addEventListener('submit', ...)</code> — перехват отправки форм.</li>
                        </ul>
                        <p class="fw-bold">
                            Свяжите HTML и JavaScript через метод <code>document.querySelector()</code>
                            — так вы получаете доступ к любому элементу страницы и меняете его
                            содержимое, стили и классы.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Как не сдаться</h2>
                        <p class="fw-bold">
                            Главный секрет — практика на мини-проектах: калькулятор, список задач,
                            таймер. Каждый такой проект закрепляет десяток концепций лучше, чем
                            недели чтения теории. Не пытайтесь выучить весь язык — 20% знаний
                            покрывают 80% типовых задач.
                        </p>

                        <!-- Метки -->
                        <div class="mt-4">
                            <span class="badge bg-orange-light text-orange fw-bold me-1 p-2">#javascript</span>
                            <span class="badge bg-orange-light text-orange fw-bold me-1 p-2">#frontend</span>
                            <span class="badge bg-orange-light text-orange fw-bold p-2">#обучение</span>
                        </div>

                        <!-- Навигация между статьями -->
                        <div class="d-flex justify-content-between mt-5">
                            <a href="post-ui.html" class="btn btn-outline-dark fw-bold"><i class="bi bi-arrow-left"></i> Предыдущая</a>
                            <a href="post-adaptive.html" class="btn btn-orange fw-bold">Следующая статья <i class="bi bi-arrow-right"></i></a>
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
    <?php $waveColors = ['#ffffff', '#353535'];
    require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ПОДВАЛ ================= -->
