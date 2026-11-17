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
                        <img src="https://placehold.co/800x400/ff8c1a/ffffff?text=Адаптивная+вёрстка" class="img-fluid rounded mb-4" alt="Обложка статьи: Адаптивная вёрстка">
                        <h2 class="fw-bold mb-3">Почему это важно</h2>
                        <p class="fw-bold">
                            Больше половины трафика в интернете — со смартфонов. Google индексирует
                            сайты по мобильной версии, а пользователь закрывает страницу, если
                            текст «пляшет» и кнопки слишком мелкие. Адаптивность — не опция,
                            а базовое требование к любому современному сайту.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Viewport — точка отсчёта</h2>
                        <p class="fw-bold">
                            Без метатега viewport браузер смартфона открывает страницу как
                            «уменьшенный десктоп». Одна строчка решает проблему:
                            <code><meta name="viewport" content="width=device-width, initial-scale=1.0"></code>.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Медиазапросы</h2>
                        <p class="fw-bold">
                            Медиазапросы позволяют применять стили при определённых условиях —
                            например, при ширине экрана от 768px. Bootstrap уже содержит
                            готовые брейкпоинты: sm (576px), md (768px), lg (992px), xl (1200px).
                            Используйте их вместо собственных — так проект останется консистентным.
                        </p>

                        <img src="https://placehold.co/800x300/353535/ff8c1a?text=Брейкпоинты" class="img-fluid rounded my-4" alt="Иллюстрация: брейкпоинты и медиазапросы">
                        <h2 class="fw-bold mt-4 mb-3">Гибкие сетки и изображения</h2>
                        <ul class="fw-bold">
                            <li>Задавайте ширину в процентах, а максимальную — в пикселях;</li>
                            <li>Изображениям — <code>max-width: 100%</code>, чтобы не ломали вёрстку;</li>
                            <li>Используйте <code>srcset</code> для отдачи разных размеров изображений;</li>
                            <li>Проверяйте сайт на реальных устройствах, а не только в DevTools.</li>
                        </ul>

                        <h2 class="fw-bold mt-4 mb-3">Вывод</h2>
                        <p class="fw-bold">
                            Начинайте с мобильной версии (mobile-first): сложнее уместить контент
                            на маленьком экране, чем добавить его на большом. Адаптив, сделанный
                            сразу, всегда дешевле и качественнее переделки.
                        </p>

                        <!-- Метки -->
                        <div class="mt-4">
                            <span class="badge bg-orange-light text-orange fw-bold me-1 p-2">#адаптивность</span>
                            <span class="badge bg-orange-light text-orange fw-bold me-1 p-2">#css</span>
                            <span class="badge bg-orange-light text-orange fw-bold p-2">#mobile-first</span>
                        </div>

                        <!-- Навигация между статьями -->
                        <div class="d-flex justify-content-between mt-5">
                            <a href="post-js.html" class="btn btn-outline-dark fw-bold"><i class="bi bi-arrow-left"></i> Предыдущая</a>
                            <a href="post-git.html" class="btn btn-orange fw-bold">Следующая статья <i class="bi bi-arrow-right"></i></a>
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
