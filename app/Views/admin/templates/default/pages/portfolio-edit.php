<div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 fw-bold mb-0" id="portfolioPageTitle">Новый проект</h1>
        </div>
        <form id="portfolioForm" novalidate>
                <input type="hidden" id="portfolioId">
                <div class="card admin-card p-4 mb-4">
                    <h2 class="h5 fw-bold mb-3">Заголовок проекта</h2>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold" for="portfolioTitle">Название работы *</label>
                            <input id="portfolioTitle" type="text" class="form-control" required placeholder="Например: Интернет-магазин">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="portfolioSlug">Адрес страницы *</label>
                            <input id="portfolioSlug" type="text" class="form-control" required pattern="[a-z0-9-]+" placeholder="project-shop">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="portfolioIcon">Иконка-заглушка</label>
                            <select id="portfolioIcon" class="form-select">
                                <option>bi-shop</option><option>bi-journal-richtext</option><option>bi-phone</option>
                                <option>bi-people</option><option>bi-bar-chart</option><option>bi-camera</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold" for="portfolioTechnologies">Подзаголовок / технологии *</label>
                            <input id="portfolioTechnologies" type="text" class="form-control" required placeholder="HTML, CSS, Bootstrap 5">
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="card admin-card p-4 h-100">
                            <h2 class="h5 fw-bold mb-3">Описание проекта</h2>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="portfolioDescription">О проекте *</label>
                                <textarea id="portfolioDescription" class="form-control" rows="5" required placeholder="Расскажите о проекте и его особенностях"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="portfolioTask">Задача</label>
                                <textarea id="portfolioTask" class="form-control" rows="4" placeholder="Основные задачи проекта — каждая с новой строки"></textarea>
                            </div>
                            <div>
                                <label class="form-label fw-bold" for="portfolioSolution">Решение</label>
                                <textarea id="portfolioSolution" class="form-control" rows="5" placeholder="Опишите принятое решение и ход реализации"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card admin-card p-4 h-100">
                            <h2 class="h5 fw-bold mb-3">Информация о проекте</h2>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="portfolioClient">Клиент</label>
                                <input id="portfolioClient" type="text" class="form-control" placeholder="Название компании или клиента">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="portfolioYear">Год</label>
                                <input id="portfolioYear" type="number" class="form-control" min="1990" max="2100" placeholder="2026">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="portfolioDuration">Сроки</label>
                                <input id="portfolioDuration" type="text" class="form-control" placeholder="Например: 6 недель">
                            </div>
                            <div>
                                <label class="form-label fw-bold" for="portfolioStack">Стек технологий</label>
                                <textarea id="portfolioStack" class="form-control" rows="3" placeholder="HTML5, CSS3, JavaScript"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card admin-card p-4 mb-4">
                    <h2 class="h5 fw-bold mb-3">Результаты проекта</h2>
                    <div class="row g-3" id="portfolioResults"></div>
                    <button class="btn btn-outline-success btn-action mt-3 align-self-start" type="button" id="addPortfolioResult" aria-label="Добавить результат" title="Добавить результат">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>

                <div class="card admin-card p-4 mb-4">
                    <h2 class="h5 fw-bold mb-2">Скриншоты проекта</h2>
                    <p class="form-text mb-3">Загрузите до 10 изображений JPEG, PNG или WebP (до 10 МБ каждое). Они сохраняются в отдельной папке проекта.</p>
                    <div class="row g-3 mb-3" id="portfolioExistingImages"></div>
                    <label class="form-label fw-bold" for="portfolioScreenshots">Добавить изображения</label>
                    <input id="portfolioScreenshots" type="file" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                    <div class="row g-3 mt-1" id="portfolioNewImagesPreview"></div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-dark fw-bold" href="portfolio.html">Отмена</a>
                    <button class="btn btn-orange fw-bold" type="submit"><i class="bi bi-save"></i> Сохранить проект</button>
                </div>
            </form>
