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
                        <img src="https://placehold.co/800x400/ff8c1a/ffffff?text=Bootstrap+5" class="img-fluid rounded mb-4" alt="Обложка статьи: Верстаем на Bootstrap 5">
                        <h2 class="fw-bold mb-3">Зачем нужен Bootstrap</h2>
                        <p class="fw-bold">
                            Bootstrap — самый популярный CSS-фреймворк в мире. Он даёт готовую сетку,
                            десятки компонентов и утилитарные классы, которые позволяют собрать
                            аккуратный адаптивный сайт за вечер вместо недели. В пятой версии
                            команда отказалась от jQuery, добавила RTL-поддержку и обновила
                            дизайн-систему.
                        </p>
                        <p class="fw-bold">
                            В этой статье разберём ключевые инструменты Bootstrap 5 на практике:
                            сетку, компоненты и утилиты — от навигации до карточек.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Сетка: основа основ</h2>
                        <p class="fw-bold">
                            Сетка Bootstrap построена на flexbox и состоит из контейнера, строк
                            и колонок. Достаточно запомнить три класса:
                        </p>
                        <ul class="fw-bold">
                            <li><code>.container</code> — центрирует контент и задаёт максимальную ширину;</li>
                            <li><code>.row</code> — строка, внутри которой лежат колонки;</li>
                            <li><code>.col-*</code> — колонка, число задаёт долю из 12 частей.</li>
                        </ul>
                        <p class="fw-bold">
                            Например, три равные колонки на средних экранах: каждая получает
                            класс <code>.col-md-4</code>. На мобильных колонки сложатся в стек —
                            адаптивность получается «бесплатно».
                        </p>

                        <img src="https://placehold.co/800x300/353535/ff8c1a?text=Сетка+Bootstrap" class="img-fluid rounded my-4" alt="Пример сетки Bootstrap">
                        <h2 class="fw-bold mt-4 mb-3">Компоненты: навигация и карточки</h2>
                        <p class="fw-bold">
                            Навбар — самый часто используемый компонент. Он сворачивается в
                            «бургер» на мобильных устройствах и раскладывается в горизонтальное
                            меню на больших экранах. Карточки (<code>.card</code>) — универсальный
                            контейнер для любых блоков: статей, товаров, отзывов.
                        </p>
                        <p class="fw-bold">
                            Полезно держать под рукой официальную документацию: почти любая
                            типовая задача решается копированием готового примера и лёгкой
                            доработкой.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Утилиты: магия классов</h2>
                        <p class="fw-bold">
                            Утилиты — короткие классы вида <code>.mt-3</code>, <code>.fw-bold</code>,
                            <code>.text-center</code>. Они заменяют большинство самописных правил
                            и позволяют верстать почти без собственного CSS. Главное — не
                            злоупотреблять: если классы начинают дублироваться, лучше вынести
                            повторяющийся блок в свой компонент.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Вывод</h2>
                        <p class="fw-bold">
                            Bootstrap 5 — отличный выбор для быстрой вёрстки типовых сайтов.
                            Сетка решает адаптивность, компоненты — интерфейс, утилиты —
                            мелкие правки. Начните с копирования примеров из документации,
                            а со временем переходите к переопределению темы через Sass.
                        </p>

                        <!-- Метки -->
                        <div class="mt-4">
                            <span class="badge bg-orange-light text-orange fw-bold me-1 p-2">#bootstrap</span>
                            <span class="badge bg-orange-light text-orange fw-bold me-1 p-2">#вёрстка</span>
                            <span class="badge bg-orange-light text-orange fw-bold p-2">#css</span>
                        </div>

                        <!-- Навигация между статьями -->
                        <div class="d-flex justify-content-between mt-5">
                            <a href="blog.html" class="btn btn-outline-dark fw-bold"><i class="bi bi-arrow-left"></i> Все статьи</a>
                            <a href="post-ui.html" class="btn btn-orange fw-bold">Следующая статья <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </article>

                    <!-- ================= КОММЕНТАРИИ ================= -->
                    <div class="mt-5">
                        <h3 class="fw-bold mb-4">Комментарии <span class="text-orange">(2)</span></h3>

                        <div class="comment-item p-3 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-person-circle fs-4 text-orange me-2"></i>
                                <strong class="fw-bold me-2">Мария</strong>
                                <small class="fw-bold text-muted">10 ноября 2026, 14:30</small>
                            </div>
                            <p class="fw-bold mb-0">Отличная статья! Наконец понял, как работает сетка Bootstrap. Жду продолжение про Sass!</p>
                            <div class="comment-reply">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-person-badge fs-6 text-orange me-2"></i>
                                    <strong class="fw-bold me-2">Автор</strong>
                                    <span class="badge bg-orange fw-bold">Админ</span>
                                    <small class="fw-bold text-muted ms-2">10 ноября 2026, 15:10</small>
                                </div>
                                <p class="fw-bold mb-0">Мария, спасибо! Статья про Sass и переопределение темы Bootstrap уже в работе — выйдет на следующей неделе.</p>
                            </div>
                        </div>

                        <div class="comment-item p-3 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-person-circle fs-4 text-orange me-2"></i>
                                <strong class="fw-bold me-2">Дмитрий</strong>
                                <small class="fw-bold text-muted">10 ноября 2026, 18:05</small>
                            </div>
                            <p class="fw-bold mb-0">Спасибо! Утилиты правда экономят кучу времени. Подскажите, как правильно переопределить цвета темы?</p>
                            <div class="comment-reply">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-person-badge fs-6 text-orange me-2"></i>
                                    <strong class="fw-bold me-2">Автор</strong>
                                    <span class="badge bg-orange fw-bold">Админ</span>
                                    <small class="fw-bold text-muted ms-2">10 ноября 2026, 18:40</small>
                                </div>
                                <p class="fw-bold mb-0">Дмитрий, проще всего через переменные Sass: скопируйте файл настроек из документации и переопределите $primary. Альтернатива без Sass — CSS-переменные и класс-обёртка. Подробно разберу в следующей статье!</p>
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
