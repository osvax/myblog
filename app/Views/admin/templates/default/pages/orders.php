<section class="card admin-card mb-4">
                <div id="ordersFilterBar">
                <div class="card-header bg-white py-2">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div style="min-width: 200px; flex-grow: 1;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="search" class="form-control filter-bar-input" id="ordersSearchInput"
                                       placeholder="Поиск: клиент, email, телефон…" aria-label="Поиск по заказам">
                                <button class="btn btn-outline-secondary filter-bar-clear" type="button" title="Очистить" hidden>
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                        <span class="filter-bar-count small text-muted fw-bold" id="ordersFilterCount" aria-live="polite"></span>
                    </div>
                </div>
                </div>
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h2 class="h5 fw-bold mb-1">Аналитика заказов</h2>
                        <p class="small text-muted fw-bold mb-0">Демонстрационные заказы: оборот показан по датам поступления заявок.</p>
                    </div>
                    <div class="d-flex flex-wrap align-items-end gap-2">
                        <div>
                            <label class="form-label fw-bold small mb-1" for="ordersDateFrom">С даты</label>
                            <input class="form-control form-control-sm" type="date" id="ordersDateFrom">
                        </div>
                        <div>
                            <label class="form-label fw-bold small mb-1" for="ordersDateTo">По дату</label>
                            <input class="form-control form-control-sm" type="date" id="ordersDateTo">
                        </div>
                        <button class="btn btn-success btn-sm fw-bold" type="button" id="exportOrders">
                            <i class="bi bi-file-earmark-excel me-1"></i> Скачать Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <h3 class="h6 fw-bold">Оборот по периодам</h3>
                            <div class="orders-chart-wrap">
                                <div id="ordersLineChart" class="orders-chart" role="img" aria-label="Линейный график оборота заказов"></div>
                                <div class="orders-chart-wheel-hint" aria-hidden="true">
                                    <i class="bi bi-mouse"></i><span>Колесико мыши — масштаб</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <h3 class="h6 fw-bold">Заказы по статусам</h3>
                            <div id="ordersStatusChart" class="orders-chart orders-donut-chart" role="img" aria-label="Круговая диаграмма заказов по статусам"></div>
                            <div id="ordersStatusLegend" class="small"></div>
                        </div>
                    </div>
                    <p class="small text-muted mb-0 mt-3" id="ordersAnalyticsSummary"></p>
                    <p class="small text-danger mb-0 mt-2" id="ordersError" role="status" hidden></p>
                </div>
            </section>

            <div class="card admin-card">
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead><tr><th>Клиент</th><th>Email / Телефон</th><th>Тип проекта</th><th>Бюджет</th><th>Срок</th><th>Статус</th><th class="text-end">Действия</th></tr></thead>
                        <tbody id="ordersTableBody"></tbody>
                    </table>
                </div>
                <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <small class="text-muted fw-bold" id="ordersPageInfo" aria-live="polite"></small>
                    <nav aria-label="Страницы заказов"><ul class="pagination pagination-sm mb-0" id="ordersPagination"></ul></nav>
                </div>
            </div>
