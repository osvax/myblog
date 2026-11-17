<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Администрирование', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_version('/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset_version('/admin/css/admin.css') ?>">
</head>
<body class="admin-page">
<div class="admin-wrapper">
    <!-- Текущий ID пользователя для JS -->
    <script>
        window.ADMIN_USER_ID = <?= (int) ($currentUserId ?? 0) ?>;
    </script>
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-brand p-3">
            <i class="bi bi-lightning-charge-fill text-orange"></i> МойБлог
            <?php
            $roleLabels = [
                'admin' => 'Администратор',
                'editor' => 'Редактор',
                'manager' => 'Менеджер',
                'user' => 'Пользователь',
            ];
            $roleLabel = $roleLabels[$userRole ?? ''] ?? 'Администратор';
            $roleBadgeClass = $userRole === 'admin' ? 'bg-warning text-dark' : ($userRole === 'editor' ? 'bg-info text-white' : ($userRole === 'manager' ? 'bg-success text-white' : 'bg-secondary'));
            ?>
            <span class="badge <?= $roleBadgeClass ?> fw-bold"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <nav class="nav flex-column p-2" aria-label="Административное меню">
            <?php foreach (($navigation ?? []) as $item): ?>
                <?php if (!empty($item['allowed'])): ?>
                    <a class="admin-link<?= !empty($item['active']) ? ' active' : '' ?>" href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>"></i> <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                    <?php if (!empty($item['divider'])): ?><hr class="admin-divider"><?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            <a class="admin-link" href="/"><i class="bi bi-box-arrow-left"></i> Вернуться на сайт</a>
        </nav>
    </aside>
    <main class="admin-main">
        <header class="admin-topbar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-orange btn-action d-lg-none" id="sidebarToggle" type="button" aria-label="Открыть меню"><i class="bi bi-list fs-5"></i></button>
                <button class="btn btn-outline-light btn-action d-none d-lg-inline-flex" id="sidebarCollapseToggle" type="button" aria-label="Свернуть меню" aria-pressed="false"><i class="bi bi-chevron-bar-left"></i></button>
                <h1 class="h4 fw-bold mb-0"><?= htmlspecialchars($pageTitle ?? 'Панель управления', ENT_QUOTES, 'UTF-8') ?></h1>
            </div>
            <div class="dropdown admin-user-menu">
                <?php
                $userName = $adminUser['name'] ?? 'Администратор';
                $userAvatar = '';
                if (!empty($adminUser['avatar_path'])) {
                    $userAvatar = $adminUser['avatar_path'];
                }
                ?>
                <a href="#" class="d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown">
                    <?php if ($userAvatar): ?>
                        <img src="<?= htmlspecialchars($userAvatar, ENT_QUOTES, 'UTF-8') ?>"
                             alt="Аватар"
                             style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <i class="bi bi-person-circle fs-4 text-orange"></i>
                    <?php endif; ?>
                    <span class="fw-bold text-dark d-none d-md-inline"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item fw-bold" href="/admin/profile.html"><i class="bi bi-person-circle"></i> Профиль</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="post" action="/api/auth/logout">
                            <button class="dropdown-item fw-bold text-danger" type="submit"><i class="bi bi-box-arrow-right"></i> Выйти</button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>
        <div class="admin-content container-fluid p-4">
            <?= $content ?? '' ?>
        </div>
        <footer class="text-center py-3 fw-bold text-muted small">© <?= date('Y') ?> МойБлог — административная панель</footer>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset_version('/admin/js/admin.js') ?>"></script>
<script src="<?= asset_version('/admin/js/admin-api.js') ?>"></script>
<?php foreach (($pageScripts ?? []) as $script): ?>
    <script src="<?= asset_version((string) $script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
