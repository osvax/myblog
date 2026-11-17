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

                    <!-- ================= КОММЕНТАРИИ ================= -->
                    <div class="mt-5">
                        <h3 class="fw-bold mb-4">Комментарии <span class="text-orange">(1)</span></h3>

                        <div class="comment-item p-3 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-person-circle fs-4 text-orange me-2"></i>
                                <strong class="fw-bold me-2">Игорь</strong>
                                <small class="fw-bold text-muted">19 октября 2026, 16:08</small>
                            </div>
                            <p class="fw-bold mb-0">Про srcset не знал — картинки на мобильной версии стали грузиться в разы быстрее.</p>
                            <div class="comment-reply">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-person-badge fs-6 text-orange me-2"></i>
                                    <strong class="fw-bold me-2">Автор</strong>
                                    <span class="badge bg-orange fw-bold">Админ</span>
                                    <small class="fw-bold text-muted ms-2">19 октября 2026, 17:00</small>
                                </div>
                                <p class="fw-bold mb-0">Игорь, здорово! Обратите внимание ещё на атрибут sizes — вместе с srcset он позволяет браузеру точнее выбирать нужный размер изображения.</p>
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
