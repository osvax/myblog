<!-- ================= ШАПКА / МЕНЮ ================= -->


    <!-- ================= ЗАГОЛОВОК СТРАНИЦЫ ================= -->
    <?php
    // В режиме рубрики показываем её название и ссылку на все статьи
    $blogIsCategory = !empty($categoryName);
    $heading = $blogHeading ?? 'Блог';
    $headingLead = $blogIsCategory ? 'Рубрика «' . $categoryName . '»' : ($blogHeadingLead ?? 'Последние записи и полезные материалы');
    require APP_ROOT . '/app/Views/frontend/templates/default/partials/page-heading.php';
    ?>

    <!-- Волна: оранжевый → светло-оранжевый -->
    <?php $waveColors = ['#ff8c1a', '#fff3e6']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= БЛОГ ================= -->
    <section class="py-5 bg-orange-light">
        <div class="container">
            <?php
            // Раскладка (Настройки → Статьи): статей в ряд и сайдбар слева/справа
            $blogPerRow = max(1, min(3, (int) ($articlesPerRow ?? 3)));
            $blogCardCol = ['1' => 'col-12', '2' => 'col-md-6', '3' => 'col-md-4'][$blogPerRow] ?? 'col-md-4';
            $blogSide = in_array($blogSidebar ?? 'none', ['left', 'right'], true) ? $blogSidebar : 'none';
            ?>
            <div class="row g-4">
                <?php if ($blogSide === 'left'): ?>
                    <aside class="col-lg-4 sidebar-block">
                        <?php require APP_ROOT . '/app/Views/frontend/templates/default/components/article-sidebar.php'; ?>
                    </aside>
                <?php endif; ?>
                <div class="<?= $blogSide === 'none' ? 'col-12' : 'col-lg-8' ?>">
            <?php if ($blogIsCategory): ?>
                <div class="text-center mb-4">
                    <span class="badge bg-orange fw-bold p-2 mb-2">Рубрика</span>
                    <?php if (!empty($categoryDescription)): ?>
                        <p class="fw-bold text-muted mb-2"><?= htmlspecialchars($categoryDescription, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <a href="/blog.html" class="fw-bold text-orange link-orange text-decoration-none"><i class="bi bi-arrow-left"></i> Все статьи блога</a>
                </div>
            <?php endif; ?>
            <!-- ================= ФОРМА ПОИСКА ================= -->
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8">
                    <form class="search-form" id="blogSearchForm" onsubmit="return false;">
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-white border-0"><i class="bi bi-search text-orange"></i></span>
                            <input type="text" class="form-control border-0 fw-bold search-input" id="blogSearchInput"
                                   placeholder="Поиск по статьям: например, «Bootstrap» или «Git»...">
                            <button class="btn btn-orange fw-bold px-4" type="submit" id="blogSearchBtn">Найти</button>
                        </div>
                    </form>
                    <p class="fw-bold text-muted text-center mt-2 mb-0" id="blogSearchInfo"></p>
                </div>
            </div>

            <div class="row g-4" id="blogPosts">
                <?php foreach (($articles ?? []) as $articleSlug => $article): ?>
                    <?php require APP_ROOT . '/app/Views/frontend/templates/default/components/article-card.php'; ?>
                <?php endforeach; ?>
            </div>

            <!-- Сообщение «ничего не найдено» -->
            <div class="text-center py-5 d-none" id="noResults">
                <i class="bi bi-search-heart display-1 text-orange"></i>
                <h2 class="fw-bold display-4 mt-4">К сожалению ничего не найдено</h2>
                <p class="fw-bold text-muted lead">Попробуйте изменить поисковый запрос</p>
            </div>

            <!-- Пагинация (скрывается при активном поиске в blog-search.js) -->
            <?php $blogPagination = is_array($pagination ?? null) ? $pagination : null; ?>
            <?php if ($blogPagination !== null && (int) $blogPagination['pages'] > 1): ?>
                <?php
                $blogBaseUrl = !empty($categorySlug) ? '/category/' . rawurlencode($categorySlug) . '.html' : '/blog.html';
                ?>
                <nav class="mt-5" aria-label="Постраничная навигация">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= (int) $blogPagination['current'] <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link fw-bold" href="<?= $blogBaseUrl ?>?page=<?= max(1, (int) $blogPagination['current'] - 1) ?>" aria-label="Предыдущая страница">Назад</a>
                        </li>
                        <?php for ($blogPage = 1; $blogPage <= (int) $blogPagination['pages']; $blogPage++): ?>
                            <li class="page-item <?= $blogPage === (int) $blogPagination['current'] ? 'active' : '' ?>">
                                <a class="page-link fw-bold" href="<?= $blogBaseUrl ?>?page=<?= $blogPage ?>"><?= $blogPage ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= (int) $blogPagination['current'] >= (int) $blogPagination['pages'] ? 'disabled' : '' ?>">
                            <a class="page-link fw-bold" href="<?= $blogBaseUrl ?>?page=<?= min((int) $blogPagination['pages'], (int) $blogPagination['current'] + 1) ?>" aria-label="Следующая страница">Вперёд</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
                </div>
                <?php if ($blogSide === 'right'): ?>
                    <aside class="col-lg-4 sidebar-block">
                        <?php require APP_ROOT . '/app/Views/frontend/templates/default/components/article-sidebar.php'; ?>
                    </aside>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Волна: светло-оранжевый → тёмный -->
    <?php $waveColors = ['#fff3e6', '#353535']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ПОДВАЛ ================= -->
