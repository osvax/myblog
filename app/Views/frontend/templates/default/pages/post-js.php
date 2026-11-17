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

                    <!-- ================= КОММЕНТАРИИ ================= -->
                    <div class="mt-5">
                        <h3 class="fw-bold mb-4">Комментарии <span class="text-orange">(2)</span></h3>

                        <div class="comment-item p-3 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-person-circle fs-4 text-orange me-2"></i>
                                <strong class="fw-bold me-2">Пётр</strong>
                                <small class="fw-bold text-muted">27 октября 2026, 09:44</small>
                            </div>
                            <p class="fw-bold mb-0">Список задач как первый проект — лучший совет. Сделал за выходные, всё понял!</p>
                            <div class="comment-reply">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-person-badge fs-6 text-orange me-2"></i>
                                    <strong class="fw-bold me-2">Автор</strong>
                                    <span class="badge bg-orange fw-bold">Админ</span>
                                    <small class="fw-bold text-muted ms-2">27 октября 2026, 10:15</small>
                                </div>
                                <p class="fw-bold mb-0">Пётр, отлично! Можете усложнить проект: добавьте фильтрацию задач и сохранение в localStorage.</p>
                            </div>
                        </div>

                        <div class="comment-item p-3 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-person-circle fs-4 text-orange me-2"></i>
                                <strong class="fw-bold me-2">Настя</strong>
                                <small class="fw-bold text-muted">28 октября 2026, 11:20</small>
                            </div>
                            <p class="fw-bold mb-0">А какие задачи порешать после todo-листа? Хочу закрепить события.</p>
                            <div class="comment-reply">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-person-badge fs-6 text-orange me-2"></i>
                                    <strong class="fw-bold me-2">Автор</strong>
                                    <span class="badge bg-orange fw-bold">Админ</span>
                                    <small class="fw-bold text-muted ms-2">28 октября 2026, 12:05</small>
                                </div>
                                <p class="fw-bold mb-0">Настя, попробуйте калькулятор, валидацию формы обратной связи и таймер-помидорку. Все три закрепляют события и работу с DOM.</p>
                            </div>
                        </div>

                        <!-- Форма комментария -->
                        <h5 class="fw-bold mt-4 mb-3">Оставить комментарий</h5>
                        <form action="/api/forms/comment" method="POST" class="card border-0 shadow-sm p-4 order-form">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="commentName" class="form-label fw-bold">Ваше имя</label>
                                    <input type="text" class="form-control" id="commentName" name="name" placeholder="Имя" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="commentEmail" class="form-label fw-bold">Email (не публикуется)</label>
                                    <input type="email" class="form-control" id="commentEmail" name="email" placeholder="you@example.ru" required>
                                </div>
                                <div class="col-12">
                                    <label for="commentText" class="form-label fw-bold">Комментарий</label>
                                    <textarea class="form-control" id="commentText" name="message" rows="3" placeholder="Ваш комментарий..." required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-orange fw-bold">
                                        Отправить комментарий <i class="bi bi-chat-square-text"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ================= САЙДБАР ================= -->
                <aside class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 mb-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-tags text-orange"></i> Рубрики</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><a href="blog.html" class="fw-bold text-orange link-orange d-flex justify-content-between"><span>Вёрстка</span><span class="badge bg-orange-light text-orange">6</span></a></li>
                            <li class="mb-2"><a href="blog.html" class="fw-bold text-orange link-orange d-flex justify-content-between"><span>Дизайн</span><span class="badge bg-orange-light text-orange">3</span></a></li>
                            <li class="mb-2"><a href="blog.html" class="fw-bold text-orange link-orange d-flex justify-content-between"><span>JavaScript</span><span class="badge bg-orange-light text-orange">5</span></a></li>
                            <li class="mb-2"><a href="blog.html" class="fw-bold text-orange link-orange d-flex justify-content-between"><span>Инструменты</span><span class="badge bg-orange-light text-orange">4</span></a></li>
                            <li><a href="blog.html" class="fw-bold text-orange link-orange d-flex justify-content-between"><span>Опыт и карьера</span><span class="badge bg-orange-light text-orange">2</span></a></li>
                        </ul>
                    </div>

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
