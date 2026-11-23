<!-- ================= ШАПКА / МЕНЮ ================= -->

<!-- Конфигурация калькулятора из базы; JSON-флаги исключают вырывание из тега script -->
<script>window.CalculatorData = <?= json_encode(is_array($calculatorConfig ?? null) ? $calculatorConfig : [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;</script>

    <!-- ================= ЗАГОЛОВОК СТРАНИЦЫ ================= -->
    <?php $heading = $orderHeading ?? 'Заказать'; $headingLead = $orderHeadingLead ?? 'Расскажите о своей задаче — я свяжусь с вами'; require APP_ROOT . '/app/Views/frontend/templates/default/partials/page-heading.php'; ?>
    <?php $waveColors = ['#2CDBE8', '#ffffff']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <form action="/api/forms/order" method="POST" enctype="multipart/form-data" class="order-form">
    <!-- ================= КАЛЬКУЛЯТОР СТОИМОСТИ ================= -->
    <section class="py-5 bg-white">
        <div class="container">
            <h2 class="fw-bold text-center mb-4">Рассчитайте стоимость</h2>
            <p class="text-center text-muted fw-bold mb-4">Выберите тип сайта и нужные функции — цена посчитается автоматически</p>

            <div class="row g-4">
                <!-- Тип сайта -->
                <div class="col-lg-4">
                    <div class="card admin-card h-100">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3"><i class="bi bi-window text-orange"></i> Тип сайта</h6>
                            <select class="form-select fw-bold" id="calcType"></select>
                        </div>
                    </div>
                </div>

                <!-- Функции -->
                <div class="col-lg-8">
                    <div class="card admin-card h-100">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3"><i class="bi bi-puzzle text-orange"></i> Дополнительные функции</h6>
                            <div id="calcFeatures" class="row g-3">
                                <!-- Динамически заполняется -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Итого -->
                <div class="col-12">
                    <div class="card admin-card bg-orange-light">
                        <div class="card-body text-center">
                            <h5 class="fw-bold mb-2">Итого:</h5>
                            <div class="d-flex align-items-center justify-content-center gap-3">
                                <h2 class="fw-bold text-orange mb-0" id="calcPrice">0 ₽</h2>
                                <span class="badge bg-orange fs-6 rounded-pill px-3 py-2" id="calcDays">7 дней</span>
                            </div>
                            <p class="text-muted small mt-2 fw-bold">Точная стоимость обсуждается после уточнения деталей</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= ФОРМА ЗАКАЗА ================= -->
    <section class="py-5 bg-orange-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="orderName" class="form-label fw-bold">Ваше имя</label>
                                <input type="text" class="form-control" id="orderName" name="name" placeholder="Иван Иванов" required>
                            </div>
                            <div class="col-md-6">
                                <label for="orderEmail" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" id="orderEmail" name="email" placeholder="ivan@example.ru" required>
                            </div>
                            <div class="col-md-6">
                                <label for="orderPhone" class="form-label fw-bold">Телефон</label>
                                <input type="tel" class="form-control" id="orderPhone" name="phone" placeholder="+7 (900) 123-45-67">
                            </div>
                            <div class="col-md-6">
                                <label for="orderContactPerson" class="form-label fw-bold">Контактное лицо</label>
                                <input type="text" class="form-control" id="orderContactPerson" name="contact_person" placeholder="Имя контактного лица" maxlength="160">
                            </div>
                            <input type="hidden" id="orderType" name="type">
                            <div class="col-md-4">
                                <label for="orderBudget" class="form-label fw-bold">Бюджет</label>
                                <input type="text" class="form-control" id="orderBudget" name="budget" placeholder="Например: 50 000 ₽">
                            </div>
                            <input type="hidden" id="calculatorDetails" name="calculator_details">
                            <div class="col-md-4">
                                <label for="orderDeadlineFrom" class="form-label fw-bold">Срок от</label>
                                <input type="date" class="form-control" id="orderDeadlineFrom" name="deadline_from">
                            </div>
                            <div class="col-md-4">
                                <label for="orderDeadlineTo" class="form-label fw-bold">Срок до</label>
                                <input type="date" class="form-control" id="orderDeadlineTo" name="deadline_to">
                            </div>
                            <div class="col-12">
                                <label for="orderMessage" class="form-label fw-bold">Описание задачи</label>
                                <textarea class="form-control" id="orderMessage" name="message" rows="4" placeholder="Опишите, какой сайт вам нужен..."></textarea>
                            </div>
                            <div class="col-12">
                                <label for="orderAttachment" class="form-label fw-bold">Дополнительные материалы</label>
                                <input class="form-control" type="file" id="orderAttachment" name="attachment" accept=".zip,application/zip">
                                <small class="form-text text-muted">При необходимости приложите ZIP-архив с дополнительной информацией, файлами или примерами для проекта. Максимальный размер — 10 МБ.</small>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="orderAgree" required>
                                    <label class="form-check-label fw-bold" for="orderAgree">
                                        Согласен на обработку персональных данных
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-orange btn-lg fw-bold px-5">
                                    Отправить заявку <i class="bi bi-send"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </form>

    <!-- Волна: светло-оранжевый → тёмный -->
    <?php $waveColors = ['#fff3e6', '#353535']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ПОДВАЛ ================= -->
