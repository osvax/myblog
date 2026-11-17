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
