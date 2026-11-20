<!-- Заглушка «Сайт на обслуживании» — в общий дизайн сайта -->

    <!-- ================= ЗАГОЛОВОК СТРАНИЦЫ ================= -->
    <?php $heading = 'Сайт на обслуживании'; $headingLead = 'Скоро вернёмся!'; require APP_ROOT . '/app/Views/frontend/templates/default/partials/page-heading.php'; ?>

    <!-- Волна: оранжевый → светло-оранжевый -->
    <?php $waveColors = ['#ff8c1a', '#fff3e6']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ЗАГЛУШКА ================= -->
    <section class="py-5 bg-orange-light">
        <div class="container text-center py-4">
            <i class="bi bi-tools display-1 text-orange"></i>
            <h2 class="fw-bold display-6 mt-4">Ведутся технические работы</h2>
            <p class="fw-bold text-muted lead mb-4">
                <?= htmlspecialchars($maintenanceMessage ?? 'Сайт временно закрыт на техническое обслуживание. Скоро вернёмся!', ENT_QUOTES, 'UTF-8') ?>
            </p>

            <?php if (!empty($maintenanceEndTs)): ?>
                <!-- Обратный отсчёт до открытия сайта (большие цифры) -->
                <div class="mb-5">
                    <div class="fw-bold text-muted mb-3">До открытия сайта осталось:</div>
                    <div class="d-inline-flex flex-wrap justify-content-center gap-3" id="maintCountdown" data-end="<?= (int) $maintenanceEndTs ?>">
                        <div class="countdown-box">
                            <div class="countdown-num" id="cdDays">0</div>
                            <div class="countdown-label">дней</div>
                        </div>
                        <div class="countdown-box">
                            <div class="countdown-num" id="cdHours">00</div>
                            <div class="countdown-label">часов</div>
                        </div>
                        <div class="countdown-box">
                            <div class="countdown-num" id="cdMinutes">00</div>
                            <div class="countdown-label">минут</div>
                        </div>
                        <div class="countdown-box">
                            <div class="countdown-num" id="cdSeconds">00</div>
                            <div class="countdown-label">секунд</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($user)): ?>
                <p class="fw-bold text-muted">Вы вошли как <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?> — доступ к сайту будет открыт снова после окончания работ.</p>
            <?php else: ?>
                <a href="/contacts.html" class="btn btn-orange btn-lg fw-bold px-4">
                    Связаться с нами <i class="bi bi-envelope"></i>
                </a>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($maintenanceEndTs)): ?>
    <style>
        .countdown-box { background:#fff; border-radius:14px; padding:18px 14px; min-width:96px; box-shadow:0 8px 20px rgba(255,140,26,.18); }
        .countdown-num { font-size:3.2rem; font-weight:800; color:#ff8c1a; line-height:1; font-variant-numeric:tabular-nums; }
        .countdown-label { margin-top:8px; font-size:.85rem; font-weight:700; color:#777; text-transform:uppercase; letter-spacing:.05em; }
        @media (max-width: 575.98px) { .countdown-num { font-size:2.2rem; } .countdown-box { min-width:72px; padding:14px 10px; } }
    </style>
    <script>
    (function () {
        'use strict';
        var el = document.getElementById('maintCountdown');
        if (!el) return;
        var endTs = parseInt(el.getAttribute('data-end'), 10) * 1000;
        var d = document.getElementById('cdDays'),
            h = document.getElementById('cdHours'),
            m = document.getElementById('cdMinutes'),
            s = document.getElementById('cdSeconds');

        function pad(n) { return n < 10 ? '0' + n : String(n); }

        function tick() {
            var diff = endTs - Date.now();
            if (diff <= 0) {
                // Сайт должен быть открыт — уходим на главную
                window.location.replace('/');
                return;
            }
            var total = Math.floor(diff / 1000);
            d.textContent = String(Math.floor(total / 86400));
            h.textContent = pad(Math.floor((total % 86400) / 3600));
            m.textContent = pad(Math.floor((total % 3600) / 60));
            s.textContent = pad(total % 60);
        }

        tick();
        setInterval(tick, 1000);
    })();
    </script>
    <?php endif; ?>

    <!-- Волна: светло-оранжевый → тёмный -->
    <?php $waveColors = ['#fff3e6', '#353535']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>
