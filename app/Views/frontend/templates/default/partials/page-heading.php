<header class="page-header bg-orange text-white text-center py-5">
    <div class="container">
        <?php if (!empty($headingIcon)): ?>
            <i class="bi <?= htmlspecialchars($headingIcon, ENT_QUOTES, 'UTF-8') ?> display-3 mb-3"></i>
        <?php endif; ?>
        <h1 class="fw-bold mb-2"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if (!empty($headingLead)): ?>
            <p class="lead fw-bold mb-0"><?= htmlspecialchars($headingLead, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>
</header>
