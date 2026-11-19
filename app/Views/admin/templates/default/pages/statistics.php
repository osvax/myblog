<!-- Сводка -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card p-3 d-flex align-items-center gap-3 h-100">
            <div class="stat-icon"><i class="bi bi-activity"></i></div>
            <div><h3 class="fw-bold mb-0" id="statTotalVisits">—</h3><p class="fw-bold text-muted mb-0">Всего посещений</p></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card p-3 d-flex align-items-center gap-3 h-100">
            <div class="stat-icon"><i class="bi bi-calendar-day"></i></div>
            <div><h3 class="fw-bold mb-0" id="statTodayVisits">—</h3><p class="fw-bold text-muted mb-0">Посещений сегодня</p></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card p-3 d-flex align-items-center gap-3 h-100">
            <div class="stat-icon"><i class="bi bi-calendar-week"></i></div>
            <div><h3 class="fw-bold mb-0" id="statLast30Visits">—</h3><p class="fw-bold text-muted mb-0">Посещений за 30 дней</p></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card p-3 d-flex align-items-center gap-3 h-100">
            <div class="stat-icon"><i class="bi bi-bar-chart"></i></div>
            <div><h3 class="fw-bold mb-0" id="statAvgPerDay">—</h3><p class="fw-bold text-muted mb-0">В среднем в день (30 дн.)</p></div>
        </div>
    </div>
</div>

<!-- Графики -->
<div class="row g-4 mb-4">
    <div class="col-12 col-xl-6">
        <section class="card admin-card h-100">
            <div class="card-header bg-white">
                <h2 class="h5 fw-bold mb-0"><i class="bi bi-graph-up me-2"></i>Посещения по дням</h2>
            </div>
            <div class="card-body">
                <div id="statDailyChart" class="statistics-chart mt-3" role="img" aria-label="Посещения за последние 30 дней"></div>
                <p class="small text-muted mt-3 mb-0" id="statDailyChartEmpty" hidden>Посещений за последние 30 дней пока нет — график появится с первыми визитами.</p>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-6">
        <section class="card admin-card h-100">
            <div class="card-header bg-white">
                <h2 class="h5 fw-bold mb-0"><i class="bi bi-calendar3 me-2"></i>Посещения по месяцам</h2>
            </div>
            <div class="card-body">
                <div id="statMonthlyChart" class="statistics-chart mt-3" role="img" aria-label="Посещения за последние 12 месяцев"></div>
                <p class="small text-muted mt-3 mb-0" id="statMonthlyChartEmpty" hidden>Посещений за последние 12 месяцев пока нет — график появится с первыми визитами.</p>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-6">
        <section class="card admin-card h-100">
            <div class="card-header bg-white">
                <h2 class="h5 fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Активность по часам</h2>
            </div>
            <div class="card-body">
                <div id="statHourlyChart" class="statistics-chart mt-3" role="img" aria-label="Посещения по часам суток"></div>
                <p class="small text-muted mt-3 mb-0" id="statHourlyChartEmpty" hidden>Данных по часам пока нет — диаграмма появится с первыми визитами.</p>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-6">
        <section class="card admin-card h-100">
            <div class="card-header bg-white">
                <h2 class="h5 fw-bold mb-0"><i class="bi bi-pie-chart me-2"></i>Разделы и браузеры</h2>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row gap-4">
                    <div class="text-center flex-grow-1">
                        <h3 class="h6 fw-bold text-muted">Разделы сайта</h3>
                        <div id="statSectionChart" class="statistics-donut mx-auto" role="img" aria-label="Посещения по разделам сайта"></div>
                        <div id="statSectionLegend" class="stat-legend mt-3"></div>
                        <p class="small text-muted mb-0" id="statSectionChartEmpty" hidden>Нет данных</p>
                    </div>
                    <div class="text-center flex-grow-1">
                        <h3 class="h6 fw-bold text-muted">Браузеры</h3>
                        <div id="statBrowserChart" class="statistics-donut mx-auto" role="img" aria-label="Посещения по браузерам"></div>
                        <div id="statBrowserLegend" class="stat-legend mt-3"></div>
                        <p class="small text-muted mb-0" id="statBrowserChartEmpty" hidden>Нет данных</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Трафик сайта -->
<section class="card admin-card mb-4">
    <div class="card-header bg-white">
        <h2 class="h5 fw-bold mb-0"><i class="bi bi-activity me-2"></i>Трафик сайта</h2>
    </div>
    <div class="card-body">
        <!-- Фильтры -->
        <div class="row g-2 mb-3 align-items-end">
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label small fw-bold mb-1" for="statTrafficDateFrom">Дата с</label>
                <input type="date" class="form-control form-control-sm" id="statTrafficDateFrom">
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label small fw-bold mb-1" for="statTrafficDateTo">Дата по</label>
                <input type="date" class="form-control form-control-sm" id="statTrafficDateTo">
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label small fw-bold mb-1" for="statTrafficTimeFrom">Время с</label>
                <input type="time" class="form-control form-control-sm" id="statTrafficTimeFrom">
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label small fw-bold mb-1" for="statTrafficTimeTo">Время до</label>
                <input type="time" class="form-control form-control-sm" id="statTrafficTimeTo">
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label small fw-bold mb-1" for="statTrafficBrowser">Браузер</label>
                <select class="form-select form-select-sm" id="statTrafficBrowser">
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
                <label class="form-label small fw-bold mb-1" for="statTrafficSection">Раздел</label>
                <select class="form-select form-select-sm" id="statTrafficSection">
                    <option value="">Все</option>
                    <option value="home">Главная</option>
                    <option value="blog">Блог</option>
                    <option value="portfolio">Портфолио</option>
                    <option value="auth">Аккаунт</option>
                    <option value="admin">Админ-панель</option>
                    <option value="other">Другое</option>
                </select>
            </div>
            <div class="col-12 col-xl-auto">
                <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" id="statTrafficResetBtn">
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
                <tbody id="statTrafficBody">
                    <tr><td colspan="6" class="text-center text-muted py-4">Загрузка…</td></tr>
                </tbody>
            </table>
        </div>
        <!-- Пагинация -->
        <div class="d-flex justify-content-center align-items-center gap-2 p-3 border-top" id="statTrafficPagination" hidden>
            <button class="btn btn-outline-secondary btn-sm" type="button" id="statTrafficPrevPageBtn">
                <i class="bi bi-chevron-left"></i>
            </button>
            <span class="small text-muted" id="statTrafficPaginationInfo"></span>
            <button class="btn btn-outline-secondary btn-sm" type="button" id="statTrafficNextPageBtn">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
</section>

<!-- Хранение данных -->
<section class="card admin-card">
    <div class="card-header bg-white">
        <h2 class="h5 fw-bold mb-0"><i class="bi bi-database me-2"></i>Хранение данных</h2>
    </div>
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label fw-bold" for="statRetentionDays">Срок хранения посещений</label>
                <select id="statRetentionDays" class="form-select">
                    <option value="30">30 дней</option>
                    <option value="60">60 дней</option>
                    <option value="90" selected>90 дней</option>
                    <option value="180">180 дней</option>
                    <option value="365">365 дней</option>
                    <option value="0">Без ограничения</option>
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex align-items-end">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="statAutoClean" checked>
                    <label class="form-check-label fw-bold" for="statAutoClean">Автоочистка по сроку (раз в сутки)</label>
                </div>
            </div>
            <div class="col-12 col-md-4 d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-orange fw-bold" id="statSaveRetentionBtn"><i class="bi bi-save me-1"></i>Сохранить</button>
                <button type="button" class="btn btn-outline-danger fw-bold" id="statCleanNowBtn"><i class="bi bi-trash3 me-1"></i>Очистить сейчас</button>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-4 small text-muted mt-3">
            <span><i class="bi bi-hdd me-1"></i>Записей: <b id="statRecordsCount">—</b></span>
            <span><i class="bi bi-clock-history me-1"></i>Последняя очистка: <b id="statLastClean">—</b></span>
        </div>
    </div>
</section>
