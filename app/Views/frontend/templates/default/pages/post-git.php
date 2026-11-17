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

                    <?php require APP_ROOT . '/app/Views/frontend/templates/default/components/comments-section.php'; ?>
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
