<?php
/**
 * Секция комментариев на странице статьи.
 *
 * Ожидаемые переменные:
 * - $article          — данные статьи (нужен slug)
 * - $comments         — одобренные комментарии (пустой массив, если просмотр запрещён)
 * - $commentsCount    — всего одобренных комментариев
 * - $commentsVisible  — может ли текущий посетитель видеть комментарии
 */
$commentsSectionSlug = isset($article['slug']) ? $article['slug'] : '';
$commentsSectionVisible = !empty($commentsVisible);
?>
<div class="mt-5">
    <h3 class="fw-bold mb-4">Комментарии<?php if ($commentsSectionVisible): ?> <span class="text-orange">(<?= (int) $commentsCount ?>)</span><?php endif; ?></h3>

    <?php if (!$commentsSectionVisible): ?>
        <!-- Комментарии скрыты: доступ настраивается в админке (Настройки → Блог → commentsAccess) -->
        <div class="card border-0 shadow-sm p-4 text-center">
            <i class="bi bi-lock display-6 text-orange"></i>
            <p class="fw-bold mb-3">Комментарии доступны только зарегистрированным пользователям.<br>Войдите или создайте аккаунт, чтобы читать и оставлять комментарии.</p>
            <div>
                <a href="/login.html" class="btn btn-orange fw-bold me-2 mb-2">Войти <i class="bi bi-box-arrow-in-right"></i></a>
                <a href="/register.html" class="btn btn-outline-dark fw-bold mb-2">Регистрация</a>
            </div>
        </div>
    <?php else: ?>
        <?php if (empty($comments)): ?>
            <p class="fw-bold text-muted">Пока нет комментариев — будьте первым!</p>
        <?php else: ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment-item p-3 mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-person-circle fs-4 text-orange me-2"></i>
                        <strong class="fw-bold me-2"><?= htmlspecialchars($comment['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <small class="fw-bold text-muted"><?= htmlspecialchars(date('d.m.Y, H:i', strtotime($comment['created_at'])), ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                    <p class="fw-bold mb-0"><?= nl2br(htmlspecialchars($comment['body'], ENT_QUOTES, 'UTF-8')) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Форма комментария -->
        <h5 class="fw-bold mt-4 mb-3">Оставить комментарий</h5>
        <form action="/api/forms/comment" method="POST" class="card border-0 shadow-sm p-4 order-form">
            <input type="hidden" name="article" value="<?= htmlspecialchars($commentsSectionSlug, ENT_QUOTES, 'UTF-8') ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="commentName" class="form-label fw-bold">Ваше имя</label>
                    <input type="text" class="form-control" id="commentName" name="name" placeholder="Имя" required>
                </div>
                <div class="col-md-6">
                    <label for="commentEmail" class="form-label fw-bold">Email (не публикуется)</label>
                    <input type="email" class="form-control" id="commentEmail" name="email" placeholder="you@example.ru" required>
                </div>
                <div class="col-12">
                    <label for="commentText" class="form-label fw-bold">Комментарий</label>
                    <textarea class="form-control" id="commentText" name="message" rows="3" placeholder="Ваш комментарий..." required></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-orange fw-bold">
                        Отправить комментарий <i class="bi bi-chat-square-text"></i>
                    </button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>
