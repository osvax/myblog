<div class="filter-bar card-header bg-white py-2">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <!-- Поиск -->
        <div class="filter-bar-search flex-grow-1" style="min-width: 200px;">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" class="form-control filter-bar-input"
                       placeholder="<?= htmlspecialchars($filterPlaceholder ?? 'Поиск…', ENT_QUOTES, 'UTF-8') ?>"
                       aria-label="<?= htmlspecialchars($filterAriaLabel ?? 'Фильтр', ENT_QUOTES, 'UTF-8') ?>">
                <button class="btn btn-outline-secondary filter-bar-clear" type="button" title="Очистить" hidden>
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <!-- Статус -->
        <?php if (!empty($filterStatuses)): ?>
        <div class="filter-bar-status">
            <select class="form-select form-select-sm" aria-label="Фильтр по статусу">
                <option value=""><?= htmlspecialchars($filterStatusLabel ?? 'Все статусы', ENT_QUOTES, 'UTF-8') ?></option>
                <?php foreach ($filterStatuses as $value => $label): ?>
                    <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Категория / Тип -->
        <?php if (!empty($filterCategories)): ?>
        <div class="filter-bar-category">
            <select class="form-select form-select-sm" aria-label="Фильтр по категории">
                <option value=""><?= htmlspecialchars($filterCategoryLabel ?? 'Все категории', ENT_QUOTES, 'UTF-8') ?></option>
                <?php foreach ($filterCategories as $value => $label): ?>
                    <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Сброс -->
        <button class="btn btn-sm btn-outline-secondary filter-bar-reset" type="button" title="Сбросить фильтры" hidden>
            <i class="bi bi-arrow-counterclockwise me-1"></i>Сбросить
        </button>

        <!-- Счётчик результатов -->
        <span class="filter-bar-count small text-muted fw-bold" aria-live="polite"></span>
    </div>
</div>
