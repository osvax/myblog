<?php
$layoutLang = stripos((string) ($siteLanguage ?? ''), 'english') !== false ? 'en' : 'ru';
$layoutSiteTitle = trim((string) ($siteTitle ?? ''));
$layoutFullTitle = $layoutSiteTitle !== '' ? $title . ' — ' . $layoutSiteTitle : $title;
$themeFontFamily = trim((string) ($themeFontFamily ?? ''));
?>
<!doctype html>
<html lang="<?= htmlspecialchars($layoutLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($layoutFullTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($description ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <?php if (trim((string) ($siteKeywords ?? '')) !== ''): ?><meta name="keywords" content="<?= htmlspecialchars($siteKeywords, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <?php if (has_vite_manifest() && isset($viteManifest['resources/js/app.js'])): ?>
        <?php foreach (vite_css('resources/js/app.js') as $css): ?><link rel="stylesheet" href="<?= htmlspecialchars($css, ENT_QUOTES, 'UTF-8') ?>"><?php endforeach; ?>
    <?php else: ?>
        <link rel="stylesheet" href="<?= asset_version('/css/style.css') ?>">
    <?php endif; ?>
    <style>
        :root { --test-primary: #4677d5; --test-accent: #e8efff; --test-ink: #18233a; }
        body { background: #f5f8ff; color: var(--test-ink); }
        .test-light-nav { background: #fff; border-bottom: 1px solid #dfe8fb; }
        .test-light-brand { color: var(--test-primary); letter-spacing: .02em; }
        .test-light-main { min-height: 70vh; }
        .test-light-footer { background: #18233a; color: #dfe8fb; }
        <?php if ($themeFontFamily !== ''): ?>body, button, input, select, textarea { font-family: '<?= htmlspecialchars(str_replace(['"', '\\', ';', '{', '}'], '', $themeFontFamily), ENT_QUOTES, 'UTF-8') ?>', sans-serif; }<?php endif; ?>
    </style>
</head>
<body>
<header class="test-light-nav py-3">
    <div class="container d-flex justify-content-between align-items-center gap-3">
        <a class="test-light-brand fw-bold text-decoration-none" href="/"><?= htmlspecialchars($siteName ?? 'МойБлог', ENT_QUOTES, 'UTF-8') ?></a>
        <nav><?= menu_items_html('main') ?></nav>
    </div>
</header>
<main class="test-light-main">
    <?= $content ?? '' ?>
</main>
<footer class="test-light-footer py-4">
    <div class="container text-center small">
        <?= htmlspecialchars(str_replace('{year}', (string) date('Y'), $footerText ?? ''), ENT_QUOTES, 'UTF-8') ?>
    </div>
</footer>
<?php if (has_vite_manifest() && isset($viteManifest['resources/js/app.js'])): ?><script type="module" src="<?= htmlspecialchars(vite_asset('resources/js/app.js'), ENT_QUOTES, 'UTF-8') ?>"></script><?php endif; ?>
</body>
</html>
