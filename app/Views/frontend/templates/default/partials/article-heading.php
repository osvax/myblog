<header class="page-header bg-orange text-white py-5">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="/" class="text-white fw-bold">Главная</a></li>
                <li class="breadcrumb-item"><a href="/blog.html" class="text-white fw-bold">Блог</a></li>
                <li class="breadcrumb-item text-white-50 fw-bold" aria-current="page"><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></li>
            </ol>
        </nav>
        <h1 class="fw-bold mb-2"><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="fw-bold mb-0"><i class="bi bi-calendar3"></i> <?= htmlspecialchars($article['date'], ENT_QUOTES, 'UTF-8') ?> · <i class="bi bi-tag"></i> <?= htmlspecialchars($article['category'], ENT_QUOTES, 'UTF-8') ?> · <?= (int) $article['readingTime'] ?> мин чтения</p>
    </div>
</header>
