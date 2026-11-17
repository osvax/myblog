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
                        <img src="https://placehold.co/800x400/ff8c1a/ffffff?text=Git" class="img-fluid rounded mb-4" alt="Обложка статьи: Git за один вечер">
                        <h2 class="fw-bold mb-3">Что такое Git</h2>
                        <p class="fw-bold">
                            Git — система контроля версий. Она сохраняет историю изменений проекта,
                            позволяет вернуться к любой версии и работать над кодом командой без
                            страха потерять файлы. Понимание Git — обязательный навык для любого
                            разработчика: без него не берут ни в одну команду.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Минимальный набор команд</h2>
                        <ul class="fw-bold">
                            <li><code>git init</code> — создать репозиторий в папке проекта;</li>
                            <li><code>git add .</code> — подготовить файлы к сохранению;</li>
                            <li><code>git commit -m "описание"</code> — сохранить снимок изменений;</li>
                            <li><code>git status</code> — посмотреть, что изменилось;</li>
                            <li><code>git log</code> — история коммитов.</li>
                        </ul>
                        <p class="fw-bold">
                            Этого достаточно для личных проектов. Коммитьте часто и пишите
                            понятные сообщения — будущий вы скажет спасибо.
                        </p>

                        <img src="https://placehold.co/800x300/353535/ff8c1a?text=Коммиты+и+ветки" class="img-fluid rounded my-4" alt="Иллюстрация: коммиты и ветки в Git">
                        <h2 class="fw-bold mt-4 mb-3">Ветки: эксперименты без риска</h2>
                        <p class="fw-bold">
                            Ветка — параллельная версия проекта. Создайте ветку
                            <code>git branch feature</code>, переключитесь на неё
                            <code>git checkout feature</code> и спокойно экспериментируйте:
                            основная версия останется нетронутой. Слияние — <code>git merge</code>.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Работа с удалённым репозиторием</h2>
                        <p class="fw-bold">
                            GitHub, GitLab и Bitbucket хранят копию проекта в облаке.
                            <code>git push</code> отправляет коммиты на сервер,
                            <code>git pull</code> забирает чужие изменения. Это основа командной
                            работы и бэкап всех наработок.
                        </p>

                        <h2 class="fw-bold mt-4 mb-3">Вывод</h2>
                        <p class="fw-bold">
                            Не пытайтесь выучить все команды Git — их сотни. Освойте базовый цикл
                            add → commit → push, привыкните коммитить каждый вечер, и через месяц
                            Git станет естественной частью работы, как сохранение файла.
                        </p>

                        <!-- Метки -->
                        <div class="mt-4">
                            <span class="badge bg-orange-light text-orange fw-bold me-1 p-2">#git</span>
                            <span class="badge bg-orange-light text-orange fw-bold me-1 p-2">#инструменты</span>
                            <span class="badge bg-orange-light text-orange fw-bold p-2">#github</span>
                        </div>

                        <!-- Навигация между статьями -->
                        <div class="d-flex justify-content-between mt-5">
                            <a href="post-adaptive.html" class="btn btn-outline-dark fw-bold"><i class="bi bi-arrow-left"></i> Предыдущая</a>
                            <a href="post-speed.html" class="btn btn-orange fw-bold">Следующая статья <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </article>

                    <!-- ================= КОММЕНТАРИИ ================= -->
                    <div class="mt-5">
                        <h3 class="fw-bold mb-4">Комментарии <span class="text-orange">(1)</span></h3>

                        <div class="comment-item p-3 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-person-circle fs-4 text-orange me-2"></i>
                                <strong class="fw-bold me-2">Сергей</strong>
                                <small class="fw-bold text-muted">9 октября 2026, 22:31</small>
                            </div>
                            <p class="fw-bold mb-0">Совет «коммитить каждый вечер» — то, что нужно новичку. Приучился за две недели.</p>
                            <div class="comment-reply">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-person-badge fs-6 text-orange me-2"></i>
                                    <strong class="fw-bold me-2">Автор</strong>
                                    <span class="badge bg-orange fw-bold">Админ</span>
                                    <small class="fw-bold text-muted ms-2">9 октября 2026, 22:50</small>
                                </div>
                                <p class="fw-bold mb-0">Сергей, отличный прогресс! Следующий шаг — правила хороших сообщений к коммитам: короткое описание в повелительном наклонении, например «Добавить форму заказа».</p>
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
