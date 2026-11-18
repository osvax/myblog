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
    <?php foreach (($pageStyles ?? []) as $pageStyle): ?>
        <link rel="stylesheet" href="<?= asset_version((string) $pageStyle) ?>">
    <?php endforeach; ?>
</head>
<body class="admin-page">
<div class="admin-wrapper">
    <!-- Текущий ID пользователя для JS -->
    <script>
        window.ADMIN_USER_ID = <?= (int) ($currentUserId ?? 0) ?>;
        window.ADMIN_USER_ROLE = <?= json_encode((string) ($userRole ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    </script>
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-brand p-3">
            <i class="bi bi-lightning-charge-fill text-orange"></i> МойБлог
            <?php
            $roleLabel = $userRoleName !== '' ? $userRoleName : ($userRole ?? 'Администратор');
            $roleBadgeClass = $userRole === 'admin' ? 'bg-warning text-dark' : ($userRole === 'editor' ? 'bg-info text-white' : ($userRole === 'manager' ? 'bg-success text-white' : 'bg-secondary'));
            ?>
            <span class="badge <?= $roleBadgeClass ?> fw-bold"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <nav class="nav flex-column p-2 admin-nav" id="adminNav" aria-label="Административное меню">
            <input type="search" class="form-control form-control-sm nav-filter mb-2" id="navFilter"
                   placeholder="Поиск по меню…" aria-label="Поиск по пунктам меню">
            <?php foreach (($navigation ?? []) as $entry): ?>
                <?php if (($entry['type'] ?? 'link') === 'group'): ?>
                    <?php
                    // Группа разделов: выпадающий список, открыт у активного пункта
                    $visibleItems = array_values(array_filter($entry['items'], static fn ($i) => !empty($i['allowed'])));
                    if (!$visibleItems) continue;
                    ?>
                    <button class="admin-nav-group-btn" type="button"
                            data-bs-toggle="collapse" data-bs-target="#<?= htmlspecialchars($entry['id'], ENT_QUOTES, 'UTF-8') ?>"
                            aria-expanded="<?= !empty($entry['open']) ? 'true' : 'false' ?>"
                            aria-controls="<?= htmlspecialchars($entry['id'], ENT_QUOTES, 'UTF-8') ?>">
                        <span class="admin-nav-group-label"><i class="bi <?= htmlspecialchars($entry['icon'], ENT_QUOTES, 'UTF-8') ?>"></i> <?= htmlspecialchars($entry['label'], ENT_QUOTES, 'UTF-8') ?></span>
                        <i class="bi bi-chevron-down group-chevron" aria-hidden="true"></i>
                    </button>
                    <div class="collapse<?= !empty($entry['open']) ? ' show' : '' ?>" id="<?= htmlspecialchars($entry['id'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php foreach ($visibleItems as $item): ?>
                            <a class="admin-link admin-link-sub<?= !empty($item['active']) ? ' active' : '' ?>" href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') ?>">
                                <i class="bi <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>"></i> <span class="admin-link-text"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php elseif (!empty($entry['allowed'])): ?>
                    <a class="admin-link<?= !empty($entry['active']) ? ' active' : '' ?>" href="<?= htmlspecialchars($entry['url'], ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi <?= htmlspecialchars($entry['icon'], ENT_QUOTES, 'UTF-8') ?>"></i> <?= htmlspecialchars($entry['label'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
            <hr class="admin-divider">
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
