<div class="d-flex justify-content-end flex-wrap gap-2 mb-3"><a class="btn btn-outline-light fw-bold" href="orders.html"><i class="bi bi-arrow-left me-1"></i> К заказам</a></div>
            <div id="clientError" class="alert alert-danger" role="alert" hidden></div>
            <form id="clientForm" novalidate>
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <section class="card admin-card h-100">
                            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div><h2 class="h5 fw-bold mb-1" id="clientNameHeading">Данные клиента</h2><span class="small text-muted" id="orderNumber"></span></div>
                                <span class="badge bg-orange fs-6" id="clientStatusBadge">Заказ</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label fw-bold" for="clientName">ФИО / название клиента</label><input class="form-control" id="clientName" name="clientName" maxlength="160" required></div>
                                    <div class="col-md-6"><label class="form-label fw-bold" for="clientEmail">Email</label><input class="form-control" id="clientEmail" name="email" type="email" maxlength="254"></div>
                                    <div class="col-md-6"><label class="form-label fw-bold" for="clientPhone">Телефон</label><input class="form-control" id="clientPhone" name="phone" type="tel" maxlength="40"></div>
                                    <div class="col-md-6"><label class="form-label fw-bold" for="clientSource">Источник обращения</label><input class="form-control" id="clientSource" name="source" maxlength="120" placeholder="Например: форма на сайте"></div>
                                    <div class="col-md-6"><label class="form-label fw-bold" for="clientAddress">Адрес клиента</label><input class="form-control" id="clientAddress" name="address" maxlength="240"></div>
                                    <div class="col-md-6"><label class="form-label fw-bold" for="clientContactPerson">Контактное лицо</label><input class="form-control" id="clientContactPerson" name="contactPerson" maxlength="160"></div>
                                    <div class="col-md-6"><label class="form-label fw-bold" for="companyName">Название организации</label><input class="form-control" id="companyName" name="companyName" maxlength="180"></div>
                                    <div class="col-md-6"><label class="form-label fw-bold" for="companyInn">ИНН</label><input class="form-control" id="companyInn" name="companyInn" maxlength="12" inputmode="numeric"></div>
                                    <div class="col-md-6"><label class="form-label fw-bold" for="companyOgrn">ОГРН / ОГРНИП</label><input class="form-control" id="companyOgrn" name="companyOgrn" maxlength="15" inputmode="numeric"></div>
                                    <div class="col-md-6"><label class="form-label fw-bold" for="companyContactPosition">Должность контактного лица</label><input class="form-control" id="companyContactPosition" name="companyContactPosition" maxlength="120"></div>
                                    <div class="col-md-6"><label class="form-label fw-bold" for="companyEmail">Email организации</label><input class="form-control" id="companyEmail" name="companyEmail" type="email" maxlength="254"></div>
                                    <div class="col-md-6"><label class="form-label fw-bold" for="companyPhone">Телефон организации</label><input class="form-control" id="companyPhone" name="companyPhone" type="tel" maxlength="40"></div>
                                    <div class="col-12"><label class="form-label fw-bold" for="companyAddress">Юридический адрес</label><textarea class="form-control" id="companyAddress" name="companyAddress" rows="2" maxlength="500"></textarea></div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <section class="card admin-card mb-4">
                    <div class="card-header bg-white"><h2 class="h5 fw-bold mb-0">Проект и техническое задание</h2></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label fw-bold" for="projectTitle">Название проекта</label><input class="form-control" id="projectTitle" name="projectTitle" maxlength="180"></div>
                            <div class="col-md-6"><label class="form-label fw-bold" for="projectType">Тип проекта</label><input class="form-control" id="projectType" name="projectType" maxlength="120"></div>
                            <div class="col-md-4"><label class="form-label fw-bold" for="orderDate">Дата заказа</label><input class="form-control" id="orderDate" name="orderDate" type="date" required></div>
                            <div class="col-md-4"><label class="form-label fw-bold" for="projectDeadline">Плановая дата завершения</label><input class="form-control" id="projectDeadline" name="deadline" type="date"></div>
                            <div class="col-md-4"><label class="form-label fw-bold" for="completedAt">Фактическая дата выполнения</label><input class="form-control" id="completedAt" name="completedAt" type="date"></div>
                            <div class="col-md-6"><label class="form-label fw-bold" for="projectStatus">Статус проекта</label>
                                <select class="form-select" id="projectStatus" name="status"><option>Новый</option><option>В работе</option><option>Завершён</option><option>Отменён</option></select>
                            </div>
                            <div class="col-md-6"><label class="form-label fw-bold" for="projectUrl">Ссылка на действующий проект</label><input class="form-control" id="projectUrl" name="projectUrl" type="url" maxlength="500" placeholder="https://"></div>
                            <div class="col-12" id="cancelReasonGroup"><label class="form-label fw-bold" for="cancelReason">Причина отмены</label><textarea class="form-control" id="cancelReason" name="cancelReason" rows="2" maxlength="2000"></textarea></div>
                            <div class="col-12">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <label class="form-label fw-bold mb-0" for="technicalBrief">Техническое задание</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button class="btn btn-outline-primary btn-sm fw-bold" type="button" id="previewBrief"><i class="bi bi-eye me-1"></i> Открыть / прочитать</button>
                                        <button class="btn btn-outline-secondary btn-sm fw-bold" type="button" id="downloadBrief"><i class="bi bi-file-earmark-pdf me-1"></i> Печать / сохранить PDF</button>
                                    </div>
                                </div>
                                <textarea class="form-control" id="technicalBrief" name="technicalBrief" rows="7" maxlength="20000" placeholder="Опишите цели проекта, функциональность, требования к дизайну и контенту."></textarea>
                                <div class="form-text">Кнопка PDF открывает окно печати: выберите «Сохранить как PDF» в списке принтеров браузера.</div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="card admin-card mb-4">
                    <div class="card-header bg-white"><h2 class="h5 fw-bold mb-0">Стоимость и оплаты</h2></div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4"><label class="form-label fw-bold" for="projectBudget">Стоимость проекта, ₽</label><input class="form-control" id="projectBudget" name="budget" type="number" min="0" step="0.01" required></div>
                            <div class="col-md-4"><label class="form-label fw-bold" for="paidAmount">Получено, ₽</label><input class="form-control" id="paidAmount" name="paidAmount" type="number" min="0" step="0.01"></div>
                            <div class="col-md-4"><div class="crm-balance-box"><span class="small text-muted fw-bold">Остаток / долг</span><strong id="projectBalance">—</strong><span id="paymentState" class="small fw-bold"></span></div></div>
                            <div class="col-md-4"><label class="form-label fw-bold" for="lastPaymentDate">Дата последней оплаты</label><input class="form-control" id="lastPaymentDate" name="lastPaymentDate" type="date"></div>
                            <div class="col-md-4"><label class="form-label fw-bold" for="paymentMethod">Способ оплаты</label><input class="form-control" id="paymentMethod" name="paymentMethod" maxlength="100" placeholder="Перевод, наличные..."></div>
                            <div class="col-md-4"><label class="form-label fw-bold" for="paymentHistory">История оплат</label><textarea class="form-control" id="paymentHistory" name="paymentHistory" rows="2" maxlength="2000" placeholder="Дата — сумма — комментарий"></textarea></div>
                        </div>
                    </div>
                </section>

                <section class="card admin-card mb-4">
                    <div class="card-header bg-white"><h2 class="h5 fw-bold mb-0">Заметки по клиенту</h2></div>
                    <div class="card-body"><label class="form-label fw-bold" for="clientNotes">Внутренние заметки менеджера</label><textarea class="form-control" id="clientNotes" name="notes" rows="4" maxlength="5000"></textarea></div>
                </section>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    <button class="btn btn-orange fw-bold" type="submit"><i class="bi bi-save me-1"></i> Сохранить карточку</button>
                    <a class="btn btn-outline-secondary fw-bold" href="orders.html">К списку заказов</a>
                </div>
            </form>
