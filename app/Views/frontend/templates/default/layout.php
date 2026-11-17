<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'МойБлог', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($description ?? 'Личный блог о веб-разработке и дизайне.', ENT_QUOTES, 'UTF-8') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_version('/css/style.css') ?>">
</head>
<body id="top">
    <nav class="navbar navbar-expand-lg navbar-dark bg-orange sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/"><i class="bi bi-lightning-charge-fill"></i> <?= htmlspecialchars($siteName ?? 'МойБлог', ENT_QUOTES, 'UTF-8') ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="Переключить навигацию">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto fw-bold align-items-center gap-2">
                    <?php
                    // Основное меню из базы (редактируется в админке → «Меню»);
                    // при пустом меню — стандартные пункты
                    echo menu_items_html('main');
                    ?>
                    <?php if (!empty($user)): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/profile.html"><i class="bi bi-person"></i> Профиль</a></li>
                                <?php if (!empty($isAdmin)): ?>
                                    <li><a class="dropdown-item" href="/admin/index.html"><i class="bi bi-shield-lock"></i> Админ-панель</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="post" action="/api/auth/user/logout" style="display:inline;">
                                        <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right"></i> Выйти</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/login.html"><i class="bi bi-box-arrow-in-right"></i> Войти</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?= $content ?? '' ?>

    <!-- ================= ПОДВАЛ ================= -->
    <footer class="bg-353535 text-white py-4">
        <div class="container text-center">
            <p class="mb-1 fw-bold"><?= htmlspecialchars(str_replace('{year}', (string) date('Y'), $footerText ?? '© ' . date('Y') . ' МойБлог. Все права защищены.'), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="mb-0 small fw-bold text-white-50">Сделано с <i class="bi bi-heart-fill text-orange"></i> на Bootstrap 5</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (!empty($includeFormsScript)): ?>
        <script src="<?= asset_version('/js/forms.js') ?>"></script>
    <?php endif; ?>
    <?php if (!empty($includeSiteSettings)): ?>
        <script src="<?= asset_version('/js/site-settings.js') ?>"></script>
    <?php endif; ?>
    <?php if (!empty($includeAuthScript)): ?>
        <script src="<?= asset_version('/js/auth.js') ?>"></script>
    <?php endif; ?>
    <?php foreach (($pageScripts ?? []) as $script): ?>
        <script src="<?= asset_version((string) $script) ?>"></script>
    <?php endforeach; ?>
    <script src="<?= asset_version('/js/consultant.js') ?>"></script>
</body>
</html>
