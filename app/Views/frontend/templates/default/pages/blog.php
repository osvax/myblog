<!-- ================= ШАПКА / МЕНЮ ================= -->
    

    <!-- ================= ЗАГОЛОВОК СТРАНИЦЫ ================= -->
    <?php $heading = 'Блог'; $headingLead = 'Последние записи и полезные материалы'; require APP_ROOT . '/app/Views/frontend/templates/default/partials/page-heading.php'; ?>

    <!-- Волна: оранжевый → светло-оранжевый -->
    <?php $waveColors = ['#ff8c1a', '#fff3e6']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= БЛОГ ================= -->
    <section class="py-5 bg-orange-light">
        <div class="container">
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

            <!-- Пагинация -->
            
        </div>
    </section>

    <!-- Волна: светло-оранжевый → тёмный -->
    <?php $waveColors = ['#fff3e6', '#353535']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ПОДВАЛ ================= -->
