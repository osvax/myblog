<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Администрирование', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    
    <?php if (!empty($viteManifest['resources/js/admin.js'])): ?>
        <?php // Vite CSS админки: порт admin.css + тема сайта (login-страницы используют тот же бандл) ?>
        <?php foreach (vite_css('resources/js/admin.js') as $css): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($css, ENT_QUOTES, 'UTF-8') ?>">
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Dev-режим: CSS админки — легаси -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <link rel="stylesheet" href="<?= asset_version('/css/style.css') ?>">
        <link rel="stylesheet" href="<?= asset_version('/admin/css/admin.css') ?>">
    <?php endif; ?>
</head>
<body>
<?= $content ?? '' ?>

<!-- Bootstrap — CDN в обоих режимах: page-скрипты обращаются к window.bootstrap вне DOMContentLoaded -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php foreach (($pageScripts ?? []) as $script): ?>
    <?php
    // Vite entry для standalone-страниц: admin (forgot-password, register) и login (login)
    $viteEntry = null;
    if ($script === '/admin/js/admin.js' && !empty($viteManifest['resources/js/admin.js'])) {
        $viteEntry = 'resources/js/admin.js';
    } elseif ($script === '/admin/js/login.js' && !empty($viteManifest['resources/js/login.js'])) {
        $viteEntry = 'resources/js/login.js';
    }
    ?>
    <?php if ($viteEntry): ?>
        <?php foreach (vite_imports($viteEntry) as $import): ?>
            <link rel="modulepreload" href="<?= htmlspecialchars($import, ENT_QUOTES, 'UTF-8') ?>">
        <?php endforeach; ?>
        <script type="module" src="<?= vite_asset($viteEntry) ?>"></script>
    <?php else: ?>
        <script src="<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endif; ?>
<?php endforeach; ?>
</body>
</html>
