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
    <link rel="stylesheet" href="/css/style.css">
</head>
<body id="top">
    <nav class="navbar navbar-expand-lg navbar-dark bg-orange sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/"><i class="bi bi-lightning-charge-fill"></i> МойБлог</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="Переключить навигацию">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto fw-bold align-items-center gap-2">
                    <li class="nav-item"><a class="nav-link<?= ($activePage ?? '') === 'home' ? ' active' : '' ?>" href="/">Главная</a></li>
                    <li class="nav-item"><a class="nav-link<?= ($activePage ?? '') === 'blog' ? ' active' : '' ?>" href="/blog.html">Блог</a></li>
                    <li class="nav-item"><a class="nav-link<?= ($activePage ?? '') === 'portfolio' ? ' active' : '' ?>" href="/portfolio.html">Портфолио</a></li>
                    <li class="nav-item"><a class="nav-link<?= ($activePage ?? '') === 'order' ? ' active' : '' ?>" href="/order.html">Заказать</a></li>
                    <li class="nav-item"><a class="nav-link<?= ($activePage ?? '') === 'contacts' ? ' active' : '' ?>" href="/contacts.html">Контакты</a></li>
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
                                    <form method="post" action="/api/auth/logout" style="display:inline;">
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

    <footer class="py-4 bg-dark text-white-50">
        <div class="container d-flex flex-column flex-sm-row justify-content-between gap-2">
            <span>© <?= date('Y') ?> МойБлог</span>
            <a class="link-light" href="#top">Наверх <i class="bi bi-arrow-up"></i></a>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (!empty($includeFormsScript)): ?>
        <script src="/js/forms.js"></script>
    <?php endif; ?>
    <?php if (!empty($includeSiteSettings)): ?>
        <script src="/js/site-settings.js"></script>
    <?php endif; ?>
    <?php if (!empty($includeAuthScript)): ?>
        <script src="/js/auth.js"></script>
    <?php endif; ?>
    <?php foreach (($pageScripts ?? []) as $script): ?>
        <script src="<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endforeach; ?>
    <script src="/js/consultant.js"></script>
</body>
</html>
