<!-- Статистика -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card p-3 d-flex align-items-center gap-3 h-100">
            <div class="stat-icon"><i class="bi bi-activity"></i></div>
            <div><h3 class="fw-bold mb-0" id="dashboardOnlineCount">—</h3><p class="fw-bold text-muted mb-0">Посетители онлайн</p></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card p-3 d-flex align-items-center gap-3 h-100">
            <div class="stat-icon"><i class="bi bi-calendar-day"></i></div>
            <div><h3 class="fw-bold mb-0" id="dashboardTodayCount">—</h3><p class="fw-bold text-muted mb-0">Посетители сегодня</p></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a class="stat-card p-3 d-flex align-items-center gap-3 text-decoration-none" href="users.html">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div><h3 class="fw-bold mb-0" id="dashboardUsersCount">—</h3><p class="fw-bold text-muted mb-0">Пользователей</p></div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a class="stat-card p-3 d-flex align-items-center gap-3 text-decoration-none" href="messages.html">
            <div class="stat-icon"><i class="bi bi-chat-left-text"></i></div>
            <div><h3 class="fw-bold mb-0" id="dashboardCommentsCount">—</h3><p class="fw-bold text-muted mb-0">Комментарии</p></div>
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <a class="stat-card p-3 d-flex align-items-center gap-3 text-decoration-none" href="current-orders.html">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div><h3 class="fw-bold mb-0" id="dashboardCurrentOrdersCount">—</h3><p class="fw-bold text-muted mb-0">Текущие заказы</p></div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a class="stat-card p-3 d-flex align-items-center gap-3 text-decoration-none" href="new-orders.html">
            <div class="stat-icon dashboard-new-orders-icon"><i class="bi bi-bell-fill" aria-hidden="true"></i><span id="dashboardNewOrdersCount" class="dashboard-new-orders-count" aria-label="Количество новых заказов">0</span></div>
            <div><h3 class="fw-bold mb-0" id="dashboardNewOrdersValue">—</h3><p class="fw-bold text-muted mb-0">Новые заказы</p></div>
        </a>
    </div>
    <div class="col-12 col-xl-6">
        <div class="stat-card dashboard-clock-card p-3 d-flex align-items-center gap-3 h-100">
            <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
            <div class="flex-grow-1 text-center">
                <time id="dashboardClock" class="dashboard-clock fw-bold">--:--:--</time>
                <time id="dashboardDate" class="small text-muted fw-bold">Загрузка даты…</time>
            </div>
            <span id="dashboardTimezone" class="small text-muted"></span>
        </div>
    </div>
</div>

<div class="row g-4 mb-4 dashboard-overview">
    <div class="col-xl-6">
        <section class="card admin-card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center gap-2">
                <h2 class="h5 fw-bold mb-0">Продажи по месяцам</h2>
                <a href="orders.html" class="btn btn-sm btn-orange fw-bold">Все заказы</a>
            </div>
            <div class="card-body">
                <div id="salesChart" class="sales-chart mt-3" role="img" aria-label="Выручка по заказам за последние шесть месяцев"></div>
                <p class="small text-muted mt-3 mb-0" id="salesChartEmpty" hidden>Заказов за последние полгода ещё нет — график появится с первыми продажами.</p>
            </div>
        </section>
    </div>
    <div class="col-xl-6">
        <section class="card admin-card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 fw-bold mb-0">О системе</h2>
                <span class="badge bg-secondary" id="systemInfoStatus">Загрузка</span>
            </div>
            <div class="card-body">
                <dl class="row mb-0 system-info-list">
                    <dt class="col-sm-5">Веб-сервер</dt><dd class="col-sm-7" id="systemWebServer">—</dd>
                    <dt class="col-sm-5">Версия PHP</dt><dd class="col-sm-7" id="systemPhpVersion">—</dd>
                    <dt class="col-sm-5">Операционная система</dt><dd class="col-sm-7" id="systemOs">—</dd>
                    <dt class="col-sm-5">База данных</dt><dd class="col-sm-7" id="systemDb">—</dd>
                    <dt class="col-sm-5">Время на сервере</dt><dd class="col-sm-7" id="systemServerTime">—</dd>
                    <dt class="col-sm-5">Часовой пояс</dt><dd class="col-sm-7" id="systemTimezone">—</dd>
                    <dt class="col-sm-5">Лимит загрузки</dt><dd class="col-sm-7" id="systemUploadLimit">—</dd>
                    <dt class="col-sm-5">Память PHP</dt><dd class="col-sm-7" id="systemMemoryLimit">—</dd>
                </dl>
                <p class="small text-danger mt-3 mb-0" id="systemInfoError" role="status" hidden></p>
            </div>
        </section>
    </div>
</div>

<!-- Трафик сайта -->
<section class="card admin-card">
    <div class="card-header bg-white">
        <h2 class="h5 fw-bold mb-0"><i class="bi bi-activity me-2"></i>Трафик сайта</h2>
    </div>
    <div class="card-body">
        <!-- Фильтры -->
        <div class="row g-2 mb-3 align-items-end">
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label small fw-bold mb-1" for="trafficDateFrom">Дата с</label>
                <input type="date" class="form-control form-control-sm" id="trafficDateFrom">
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label small fw-bold mb-1" for="trafficDateTo">Дата по</label>
                <input type="date" class="form-control form-control-sm" id="trafficDateTo">
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label small fw-bold mb-1" for="trafficTimeFrom">Время с</label>
                <input type="time" class="form-control form-control-sm" id="trafficTimeFrom">
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label small fw-bold mb-1" for="trafficTimeTo">Время до</label>
                <input type="time" class="form-control form-control-sm" id="trafficTimeTo">
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label small fw-bold mb-1" for="trafficBrowser">Браузер</label>
                <select class="form-select form-select-sm" id="trafficBrowser">
                    <option value="">Все</option>
                    <option value="chrome">Chrome</option>
                    <option value="edge">Edge</option>
                    <option value="firefox">Firefox</option>
                    <option value="safari">Safari</option>
                    <option value="opera">Opera</option>
                    <option value="other">Другой</option>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label small fw-bold mb-1" for="trafficSection">Раздел</label>
                <select class="form-select form-select-sm" id="trafficSection">
                    <option value="">Все</option>
                    <option value="home">Главная</option>
                    <option value="blog">Блог</option>
                    <option value="portfolio">Портфолио</option>
                    <option value="auth">Аккаунт</option>
                    <option value="other">Другое</option>
                </select>
            </div>
            <div class="col-12 col-xl-auto">
                <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" id="trafficResetBtn">
                    <i class="bi bi-x-circle me-1"></i>Сбросить
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:140px;">IP-адрес</th>
                        <th style="width:110px;">Дата</th>
                        <th style="width:90px;">Время</th>
                        <th style="width:150px;">Браузер</th>
                        <th>Страница входа</th>
                        <th>Страница посещения</th>
                    </tr>
                </thead>
                <tbody id="trafficBody">
                    <tr><td colspan="6" class="text-center text-muted py-4">Загрузка…</td></tr>
                </tbody>
            </table>
        </div>
        <!-- Пагинация -->
        <div class="d-flex justify-content-center align-items-center gap-2 p-3 border-top" id="trafficPagination" hidden>
            <button class="btn btn-outline-secondary btn-sm" type="button" id="trafficPrevPageBtn">
                <i class="bi bi-chevron-left"></i>
            </button>
            <span class="small text-muted" id="trafficPaginationInfo"></span>
            <button class="btn btn-outline-secondary btn-sm" type="button" id="trafficNextPageBtn">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
</section>
