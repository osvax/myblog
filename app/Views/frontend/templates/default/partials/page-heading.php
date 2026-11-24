<header class="page-header <?= !empty($orderHeading) ? 'page-header-order' : 'bg-orange' ?> text-white text-center py-5">
    <div class="container">
        <?php if (!empty($headingIcon)): ?>
            <i class="bi <?= htmlspecialchars($headingIcon, ENT_QUOTES, 'UTF-8') ?> display-3 mb-3"></i>
        <?php endif; ?>
        <!-- Заголовок — стилизованный div вместо h1 (по решению владельца сайта) -->
        <div class="fw-bold mb-2" style="font-size: calc(2.5rem * var(--theme-heading-scale, 1)); line-height: 1.2;"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></div>
        <?php if (!empty($headingLead)): ?>
            <div class="lead fw-bold mb-0"><?= htmlspecialchars($headingLead, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </div>
</header>
