<!doctype html>
<html lang="<?php $layoutLang = stripos((string) ($siteLanguage ?? ''), 'english') !== false ? 'en' : 'ru'; echo htmlspecialchars($layoutLang, ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    // Заголовок вкладки: «Страница — Заголовок вкладки (SEO)» из настроек сайта
    $layoutSiteTitle = trim((string) ($siteTitle ?? ''));
    $layoutFullTitle = $layoutSiteTitle !== '' ? $title . ' — ' . $layoutSiteTitle : $title;
    ?>
    <title><?= htmlspecialchars($layoutFullTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($description ?? 'Личный блог о веб-разработке и дизайне.', ENT_QUOTES, 'UTF-8') ?>">
    <?php if (trim((string) ($siteKeywords ?? '')) !== ''): ?>
    <meta name="keywords" content="<?= htmlspecialchars(trim((string) $siteKeywords), ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

    <!-- Preconnect for fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">

    <?php
    // «Оформление» (Настройки → Оформление): шрифт из CDN-ссылки или
    // загруженного файла + CSS-переменные темы для SCSS (var(--theme-*))
    $themeFontFamily = trim((string) ($themeFontFamily ?? ''));
    $themeFontUrl = trim((string) ($themeFontUrl ?? ''));
    $themeFontFile = trim((string) ($themeFontFile ?? ''));
    $themeUrlSafe = preg_match('#^https?://#i', $themeFontUrl) === 1;
    $themeFileSafe = $themeFontFamily !== ''
        && preg_match('#^uploads/fonts/[A-Za-z0-9._-]+\.(woff|woff2|ttf|otf)$#i', $themeFontFile) === 1
        && is_file(APP_ROOT . '/public/' . $themeFontFile);
    $themeFontCssName = str_replace(['"', '\\', ';', '{', '}'], '', $themeFontFamily);
    ?>
    <?php if ($themeUrlSafe): ?>
    <!-- Шрифт по ссылке из настроек (CDN, например Google Fonts) -->
    <link rel="stylesheet" href="<?= htmlspecialchars($themeFontUrl, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php if ($themeFileSafe): ?>
    <!-- Шрифт, загруженный в настройках (файл из /uploads/fonts/) -->
    <style id="site-theme-font">
        @font-face {
            font-family: '<?= htmlspecialchars($themeFontCssName, ENT_QUOTES, 'UTF-8') ?>';
            src: url('/<?= htmlspecialchars($themeFontFile, ENT_QUOTES, 'UTF-8') ?>') format('<?= strtolower(pathinfo($themeFontFile, PATHINFO_EXTENSION)) ?>');
            font-display: swap;
        }
    </style>
    <?php endif; ?>
    <style id="site-theme-vars">
        :root {
            --theme-primary: <?= $themePrimary ?? '#ff8c1a' ?>;
            --theme-primary-light: <?= $themePrimaryLight ?? '#fff3e6' ?>;
            --theme-bg: <?= $themeBackground ?? '#ffffff' ?>;
            --theme-block-bg: <?= $themeBlockBg ?? '#ffffff' ?>;
            --theme-sidebar-bg: <?= $themeSidebarBg ?? '#ffffff' ?>;
            --theme-text: <?= $themeText ?? '#353535' ?>;
            --theme-dark: <?= $themeText ?? '#353535' ?>;
            --theme-font-size: <?= (int) ($themeFontSize ?? 16) ?>px;
            --theme-nav-size: <?= (int) ($themeNavSize ?? 16) ?>px;
            --theme-heading-scale: <?= (float) ($themeHeadingScale ?? 1) ?>;
        }
        <?php if ($themeFontFamily !== ''): ?>
        body, button, input, select, textarea {
            font-family: '<?= htmlspecialchars($themeFontCssName, ENT_QUOTES, 'UTF-8') ?>', 'Montserrat', 'Segoe UI', Arial, sans-serif !important;
        }
        <?php endif; ?>
    </style>

    <?php if (has_vite_manifest() && isset($viteManifest['resources/js/app.js'])): ?>
        <!-- Production: Vite built assets (CSS + modulepreload) -->
        <?php foreach (vite_css('resources/js/app.js') as $css): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($css, ENT_QUOTES, 'UTF-8') ?>">
        <?php endforeach; ?>
        <?php foreach (vite_imports('resources/js/app.js') as $import): ?>
            <link rel="modulepreload" href="<?= htmlspecialchars($import, ENT_QUOTES, 'UTF-8') ?>">
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Development: Vite dev server or fallback -->
        <link rel="stylesheet" href="<?= asset_version('/css/style.css') ?>">
    <?php endif; ?>
</head>
<body id="top" class="<?= !empty($authPage) ? 'auth-page' : '' ?>">
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
                                    <form method="post" action="/logout.html" style="display:inline;">
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

    <?php if (has_vite_manifest() && isset($viteManifest['resources/js/app.js'])): ?>
        <!-- Production: Vite-бандл ядра (bootstrap JS, навигация, lazy-картинки, анимации, консультант) -->
        <script type="module" src="<?= htmlspecialchars(vite_asset('resources/js/app.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
        <!-- Остальные скрипты пока не в бандле — грузим легаси (как в dev-ветке) -->
        <?php if (!empty($includeFormsScript)): ?>
            <?php if (isset($viteManifest['resources/js/forms.js'])): ?>
                <!-- Формы: Vite-порт (заказ, обратная связь, комментарии) -->
                <?php foreach (vite_imports('resources/js/forms.js') as $import): ?>
                    <link rel="modulepreload" href="<?= htmlspecialchars($import, ENT_QUOTES, 'UTF-8') ?>">
                <?php endforeach; ?>
                <script type="module" src="<?= htmlspecialchars(vite_asset('resources/js/forms.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
            <?php else: ?>
                <script src="<?= asset_version('/js/forms.js') ?>"></script>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (!empty($includeSiteSettings)): ?>
            <script src="<?= asset_version('/js/site-settings.js') ?>"></script>
        <?php endif; ?>
        <?php if (!empty($includeAuthScript)): ?>
            <?php if (isset($viteManifest['resources/js/auth.js'])): ?>
                <!-- Авторизация: Vite-порт (вход + 2FA, регистрация, восстановление, профиль) -->
                <?php foreach (vite_imports('resources/js/auth.js') as $import): ?>
                    <link rel="modulepreload" href="<?= htmlspecialchars($import, ENT_QUOTES, 'UTF-8') ?>">
                <?php endforeach; ?>
                <script type="module" src="<?= htmlspecialchars(vite_asset('resources/js/auth.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
            <?php else: ?>
                <script src="<?= asset_version('/js/auth.js') ?>"></script>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (!empty($includeArticleScript)): ?>
            <?php if (isset($viteManifest['resources/js/article.js'])): ?>
                <!-- Страница статьи: подсветка синтаксиса кода (highlight.js) -->
                <?php foreach (vite_css('resources/js/article.js') as $css): ?>
                    <link rel="stylesheet" href="<?= htmlspecialchars($css, ENT_QUOTES, 'UTF-8') ?>">
                <?php endforeach; ?>
                <?php foreach (vite_imports('resources/js/article.js') as $import): ?>
                    <link rel="modulepreload" href="<?= htmlspecialchars($import, ENT_QUOTES, 'UTF-8') ?>">
                <?php endforeach; ?>
                <script type="module" src="<?= htmlspecialchars(vite_asset('resources/js/article.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
            <?php endif; ?>
        <?php endif; ?>
        <?php foreach (($pageScripts ?? []) as $script): ?>
            <script src="<?= asset_version((string) $script) ?>"></script>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Development: Fallback to individual scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
    <?php endif; ?>
    
    <!-- Session expiry for warning -->
    <?php if (!empty($user)): ?>
        <meta name="session-expiry" content="<?= (int) (3600 * 24) ?>"> <!-- 24 hours -->
    <?php endif; ?>
</body>
</html>