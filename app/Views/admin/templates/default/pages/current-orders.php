<div class="d-flex justify-content-end flex-wrap gap-2 mb-3"><a href="orders.html" class="btn btn-outline-light fw-bold"><i class="bi bi-list-ul me-1"></i> Все заказы</a></div><div class="alert alert-info fw-bold" role="status">Здесь показаны проекты, которые уже находятся в работе. Изменение статуса сразу обновляет список.</div>
            <div class="card admin-card">
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead><tr><th>Клиент</th><th>Email / Телефон</th><th>Тип проекта</th><th>Бюджет</th><th>Срок</th><th>Статус</th><th class="text-end">Действия</th></tr></thead>
                        <tbody id="currentOrdersTableBody"><tr><td colspan="7" class="text-center text-muted py-4">Загрузка заказов…</td></tr></tbody>
                    </table>
                </div>
                <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <small class="text-muted fw-bold" id="currentOrdersPageInfo" aria-live="polite"></small>
                    <nav aria-label="Страницы текущих заказов"><ul class="pagination pagination-sm mb-0" id="currentOrdersPagination"></ul></nav>
                </div>
            </div>
            <p class="small text-danger mt-3 mb-0" id="currentOrdersError" role="alert" hidden></p>
