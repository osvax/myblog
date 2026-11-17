<div class="d-flex justify-content-end flex-wrap gap-2 mb-3"><a class="btn btn-orange fw-bold" href="portfolio-edit.html">
                    <i class="bi bi-plus-lg"></i> Добавить работу
                </a></div><div class="card admin-card">
                <?php
                $portfolioStatuses = [
                    'true' => 'Опубликован',
                    'false' => 'Черновик',
                ];
                $filterVars = [
                    'filterPlaceholder' => 'Поиск: название, технологии…',
                    'filterAriaLabel' => 'Фильтр работ',
                    'filterStatusLabel' => 'Все статусы',
                    'filterStatuses' => $portfolioStatuses,
                ];
                extract($filterVars);
                ?>
                <?php include __DIR__ . '/../partials/filter-bar.php'; ?>
                <div class="card-body p-4">
                    <div class="row g-4" id="portfolioGrid"></div>
                </div>
            </div>