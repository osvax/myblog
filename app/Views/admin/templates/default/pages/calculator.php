<div class="row g-4">
                <div class="col-lg-4">
                    <section class="card admin-card h-100">
                        <div class="card-header bg-white"><h2 class="h5 fw-bold mb-0">Типы сайтов</h2></div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush mb-4" id="calculatorTypeList"></ul>
                            <form id="addTypeForm">
                                <label class="form-label fw-bold" for="newTypeName">Добавить тип сайта</label>
                                <div class="input-group">
                                    <input class="form-control" id="newTypeName" maxlength="80" required placeholder="Например, каталог">
                                    <button class="btn btn-orange fw-bold" type="submit" aria-label="Добавить тип"><i class="bi bi-plus-lg"></i></button>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>
                <div class="col-lg-8">
                    <form id="calculatorSettingsForm">
                        <section class="card admin-card mb-4">
                            <div class="card-header bg-white"><h2 class="h5 fw-bold mb-0">Общие параметры и базовая стоимость</h2></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold" for="editCurrency">Валюта</label>
                                        <input class="form-control" id="editCurrency" maxlength="8" required value="₽">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold" for="editType">Тип сайта</label>
                                        <select class="form-select" id="editType"></select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold" for="editBasePrice">Базовая цена</label>
                                        <input class="form-control" type="number" id="editBasePrice" min="0" step="1" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold" for="editBaseDays">Базовый срок (дней)</label>
                                        <input class="form-control" type="number" id="editBaseDays" min="0" step="1" required>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <section class="card admin-card mb-4">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h2 class="h5 fw-bold mb-0">Функции выбранного типа</h2>
                                <span class="small text-muted">Название · цена · срок</span>
                            </div>
                            <div class="card-body">
                                <div id="calculatorFeatureList" class="mb-3"></div>
                                <div class="border-top pt-3">
                                    <h3 class="h6 fw-bold">Добавить функцию</h3>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="newFeatureName">Название</label>
                                            <input class="form-control" id="newFeatureName" maxlength="100">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label fw-bold" for="newFeaturePrice">Цена</label>
                                            <input class="form-control" type="number" id="newFeaturePrice" min="0" step="1" value="0">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label fw-bold" for="newFeatureDays">Дней</label>
                                            <input class="form-control" type="number" id="newFeatureDays" min="0" step="1" value="0">
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <button class="btn btn-orange fw-bold w-100" type="button" id="addCalculatorFeature"><i class="bi bi-plus-lg"></i> Добавить</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-orange fw-bold" type="submit"><i class="bi bi-save"></i> Сохранить настройки</button>
                            <button class="btn btn-outline-secondary fw-bold" id="resetCalculator" type="button">Сбросить настройки</button>
                        </div>
                    </form>
                </div>
            </div>
