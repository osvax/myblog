<section class="card activity-card mb-4" id="activityCard">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-activity text-orange"></i>
            <h2 class="h5 fw-bold mb-0">Последняя активность</h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="small text-muted fw-bold" id="activityRefreshHint">Обновляется автоматически</span>
            <button class="btn btn-outline-secondary activity-refresh-btn" type="button" id="activityRefreshBtn" title="Обновить">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>
    <div id="activityBody">
        <div class="activity-empty">
            <i class="bi bi-hourglass-split"></i>
            Загрузка…
        </div>
    </div>
</section>
