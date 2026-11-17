<!-- Статистика -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <a class="stat-card p-3 d-flex align-items-center gap-3 text-decoration-none" href="users.html">
                        <div class="stat-icon"><i class="bi bi-people"></i></div>
                        <div><h3 class="fw-bold mb-0" id="dashboardUsersCount">—</h3><p class="fw-bold text-muted mb-0">Пользователей online</p></div>
                    </a>
                </div>
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
                <div class="col-sm-6 col-xl-3">
                    <a class="stat-card p-3 d-flex align-items-center gap-3 text-decoration-none" href="messages.html">
                        <div class="stat-icon"><i class="bi bi-chat-left-text"></i></div>
                        <div><h3 class="fw-bold mb-0" id="dashboardCommentsCount">—</h3><p class="fw-bold text-muted mb-0">Комментарии</p></div>
                    </a>
                </div>
            </div>

            <div class="row g-4 mb-4 dashboard-overview">
                <div class="col-xl-5">
                    <section class="card admin-card h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center gap-2">
                            <h2 class="h5 fw-bold mb-0">Статистика заказов</h2>
                            <a href="orders.html" class="btn btn-sm btn-orange fw-bold">Все заказы</a>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <span class="text-muted fw-bold">Продажи по месяцам</span>
                                <span class="small text-muted">₽</span>
                            </div>
                            <div id="salesChart" class="sales-chart mt-3" role="img" aria-label="Демонстрационный график продаж за последние шесть месяцев"></div>
                            <p class="small text-muted mb-0 mt-2">Демонстрационный график: заказы пока не сохраняются в базе данных.</p>
                        </div>
                    </section>
                </div>
                <div class="col-xl-2">
                    <section class="card admin-card dashboard-clock-card h-100 text-center">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-clock-history text-orange fs-3 mb-2" aria-hidden="true"></i>
                            <time id="dashboardClock" class="dashboard-clock fw-bold">--:--:--</time>
                            <time id="dashboardDate" class="small text-muted fw-bold mt-2">Загрузка даты…</time>
                            <span id="dashboardTimezone" class="small text-muted mt-1"></span>
                        </div>
                    </section>
                </div>
                <div class="col-xl-5">
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
                                <dt class="col-sm-5">Часовой пояс</dt><dd class="col-sm-7" id="systemTimezone">—</dd>
                                <dt class="col-sm-5">Лимит загрузки</dt><dd class="col-sm-7" id="systemUploadLimit">—</dd>
                                <dt class="col-sm-5">Память PHP</dt><dd class="col-sm-7" id="systemMemoryLimit">—</dd>
                            </dl>
                            <p class="small text-danger mt-3 mb-0" id="systemInfoError" role="status" hidden></p>
                        </div>
                    </section>
                </div>
            </div>

            <!-- Быстрые действия -->
            <h5 class="fw-bold mb-3">Быстрые действия</h5>
            <div class="row g-3">
                <div class="col-md-4"><a href="blog.html" class="text-decoration-none"><div class="stat-card p-3 d-flex align-items-center gap-3"><div class="stat-icon"><i class="bi bi-plus-circle"></i></div><span class="fw-bold text-dark">Добавить статью</span></div></a></div>
                <div class="col-md-4"><a href="portfolio.html" class="text-decoration-none"><div class="stat-card p-3 d-flex align-items-center gap-3"><div class="stat-icon"><i class="bi bi-plus-circle"></i></div><span class="fw-bold text-dark">Добавить работу</span></div></a></div>
                <div class="col-md-4"><a href="users.html" class="text-decoration-none"><div class="stat-card p-3 d-flex align-items-center gap-3"><div class="stat-icon"><i class="bi bi-person-plus"></i></div><span class="fw-bold text-dark">Добавить пользователя</span></div></a></div>
            </div>
