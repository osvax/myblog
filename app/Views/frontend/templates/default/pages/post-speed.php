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
                        <img src="https://placehold.co/800x400/ff8c1a/ffffff?text=Скорость+сайта" class="img-fluid rounded mb-4" alt="Обложка статьи: Ускоряем загрузку сайта">
                        <h2 class="fw-bold mb-3">Почему скорость — это деньги</h2>
                        <p class="fw-bold">
                            Если сайт грузится дольше трёх секунд, больше половины посетителей
                            уходят, так и не увидев контент. Скорость влияет и на позиции в поиске:
                            Google прямо учитывает Core Web Vitals в ранжировании. Ускорение сайта —
                            самая «дешёвая» оптимизация конверсии.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Шаг 1. Измерьте текущее состояние</h2>
                        <p class="fw-bold">
                            Прогоните сайт через Google PageSpeed Insights и Lighthouse. Вы получите
                            список конкретных проблем с приоритетами: от тяжёлых изображений
                            до блокирующих скриптов. Без измерения оптимизация — гадание.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Шаг 2. Оптимизируйте изображения</h2>
                        <ul class="fw-bold">
                            <li>Конвертируйте в WebP — вес падает на 30–50% без потери качества;</li>
                            <li>Задавайте атрибуты <code>width</code> и <code>height</code>, чтобы не было «прыжков» макета;</li>
                            <li>Включите ленивую загрузку атрибутом <code>loading="lazy"</code>;</li>
                            <li>Отдавайте разные размеры через <code>srcset</code>.</li>
                        </ul>

                        <img src="https://placehold.co/800x300/353535/ff8c1a?text=PageSpeed+Insights" class="img-fluid rounded my-4" alt="Иллюстрация: проверка скорости через PageSpeed Insights">
                        <h2 class="fw-bold mt-4 mb-3">Шаг 3. Кэширование и сжатие</h2>
                        <p class="fw-bold">
                            Включите на сервере gzip или brotli — текстовые файлы (HTML, CSS, JS)
                            сжимаются в 3–4 раза. Настройте заголовки кэширования: вернувшийся
                            посетитель не должен заново скачивать одни и те же файлы.
                            Подключите CDN, если аудитория географически распределена.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Шаг 4. Уменьшите объём кода</h2>
                        <p class="fw-bold">
                            Минифицируйте CSS и JavaScript, уберите неиспользуемые библиотеки.
                            Скрипты подключайте с атрибутом <code>defer</code>, чтобы они не
                            блокировали отрисовку страницы. Часто одно отключение лишнего
                            плагина даёт больше, чем час тонкой настройки.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Вывод</h2>
                        <p class="fw-bold">
                            Цельтесь в метрику «меньше 2 секунд на мобильном интернете». Начните
                            с изображений — это самый большой и самый простой выигрыш. Дальше
                            кэширование, сжатие и чистка кода. Измеряйте до и после — цифры
                            лучшая мотивация.
                        </p>

                        <!-- Метки -->
                        <div class="mt-4">
                            <span class="badge bg-orange-light text-orange fw-bold me-1 p-2">#оптимизация</span>
                            <span class="badge bg-orange-light text-orange fw-bold me-1 p-2">#pagespeed</span>
                            <span class="badge bg-orange-light text-orange fw-bold p-2">#webp</span>
                        </div>

                        <!-- Навигация между статьями -->
                        <div class="d-flex justify-content-between mt-5">
                            <a href="post-git.html" class="btn btn-outline-dark fw-bold"><i class="bi bi-arrow-left"></i> Предыдущая</a>
                            <a href="blog.html" class="btn btn-orange fw-bold">Все статьи <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </article>

                    <!-- ================= КОММЕНТАРИИ ================= -->
                    <div class="mt-5">
                        <h3 class="fw-bold mb-4">Комментарии <span class="text-orange">(1)</span></h3>

                        <div class="comment-item p-3 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-person-circle fs-4 text-orange me-2"></i>
                                <strong class="fw-bold me-2">Алексей</strong>
                                <small class="fw-bold text-muted">2 октября 2026, 13:15</small>
                            </div>
                            <p class="fw-bold mb-0">Конвертировал картинки в WebP — PageSpeed вырос с 54 до 89 баллов. Магия!</p>
                            <div class="comment-reply">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-person-badge fs-6 text-orange me-2"></i>
                                    <strong class="fw-bold me-2">Автор</strong>
                                    <span class="badge bg-orange fw-bold">Админ</span>
                                    <small class="fw-bold text-muted ms-2">2 октября 2026, 14:00</small>
                                </div>
                                <p class="fw-bold mb-0">Алексей, впечатляющий результат! Добавьте к этому lazy loading — ещё немного баллов заберёте почти бесплатно.</p>
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
