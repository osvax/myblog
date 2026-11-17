<?php
/**
 * Секция комментариев на странице статьи.
 *
 * Ожидаемые переменные:
 * - $article          — данные статьи (нужен slug)
 * - $comments         — одобренные комментарии: корневые и ответы (parent_id), пустой массив, если просмотр запрещён
 * - $commentsCount    — всего одобренных комментариев
 * - $commentsVisible  — может ли текущий посетитель видеть комментарии
 *
 * Ответы показываются на один уровень вложенности под родительским комментарием.
 * Формы (корневая и формы ответа) отправляются через forms.js — AJAX с тостом.
 */
$commentsSectionSlug = isset($article['slug']) ? $article['slug'] : '';
$commentsSectionVisible = !empty($commentsVisible);

// Группируем комментарии: корневые и сгруппированные по родителю ответы
$commentsRoot = [];
$commentsByParent = [];
foreach ((array) ($comments ?? []) as $commentItem) {
    if (!empty($commentItem['parent_id'])) {
        $commentsByParent[(int) $commentItem['parent_id']][] = $commentItem;
    } else {
        $commentsRoot[] = $commentItem;
    }
}
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
        <?php if (empty($commentsRoot)): ?>
            <p class="fw-bold text-muted">Пока нет комментариев — будьте первым!</p>
        <?php else: ?>
            <?php foreach ($commentsRoot as $commentRoot): ?>
                <?php $commentRootId = (int) $commentRoot['id']; ?>
                <div class="comment-item p-3 mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-person-circle fs-4 text-orange me-2"></i>
                        <strong class="fw-bold me-2"><?= htmlspecialchars($commentRoot['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <small class="fw-bold text-muted"><?= htmlspecialchars(date('d.m.Y, H:i', strtotime($commentRoot['created_at'])), ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                    <p class="fw-bold mb-2"><?= nl2br(htmlspecialchars($commentRoot['body'], ENT_QUOTES, 'UTF-8')) ?></p>

                    <!-- Ответы на этот комментарий -->
                    <?php foreach ($commentsByParent[$commentRootId] ?? [] as $commentReply): ?>
                        <div class="comment-reply">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-<?= !empty($commentReply['is_staff']) ? 'person-badge' : 'person-circle' ?> fs-6 text-orange me-2"></i>
                                <strong class="fw-bold me-2"><?= htmlspecialchars($commentReply['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php if (!empty($commentReply['is_staff'])): ?>
                                    <span class="badge bg-orange fw-bold">Админ</span>
                                <?php endif; ?>
                                <small class="fw-bold text-muted ms-2"><?= htmlspecialchars(date('d.m.Y, H:i', strtotime($commentReply['created_at'])), ENT_QUOTES, 'UTF-8') ?></small>
                            </div>
                            <p class="fw-bold mb-0"><?= nl2br(htmlspecialchars($commentReply['body'], ENT_QUOTES, 'UTF-8')) ?></p>
                        </div>
                    <?php endforeach; ?>

                    <!-- Кнопка и форма ответа конкретному пользователю -->
                    <button type="button" class="btn btn-link btn-sm p-0 fw-bold text-orange link-orange" data-reply-toggle="<?= $commentRootId ?>">
                        <i class="bi bi-reply"></i> Ответить
                    </button>
                    <div data-reply-form="<?= $commentRootId ?>" hidden>
                        <form action="/api/forms/comment" method="POST" class="card border-0 shadow-sm p-3 mt-2 order-form">
                            <input type="hidden" name="article" value="<?= htmlspecialchars($commentsSectionSlug, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="parent" value="<?= $commentRootId ?>">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" for="replyName-<?= $commentRootId ?>">Ваше имя</label>
                                    <input type="text" class="form-control" id="replyName-<?= $commentRootId ?>" name="name" placeholder="Имя" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" for="replyEmail-<?= $commentRootId ?>">Email (не публикуется)</label>
                                    <input type="email" class="form-control" id="replyEmail-<?= $commentRootId ?>" name="email" placeholder="you@example.ru" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold" for="replyText-<?= $commentRootId ?>">Ответ</label>
                                    <textarea class="form-control" id="replyText-<?= $commentRootId ?>" name="message" rows="2" placeholder="Ваш ответ..." required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-orange btn-sm fw-bold">
                                        Отправить ответ <i class="bi bi-chat-square-text"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
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

<script>
// Показ и скрытие формы ответа под комментарием
document.addEventListener('click', function (event) {
    var button = event.target.closest('[data-reply-toggle]');
    if (!button) return;
    var wrap = document.querySelector('[data-reply-form="' + button.dataset.replyToggle + '"]');
    if (!wrap) return;
    wrap.hidden = !wrap.hidden;
    if (!wrap.hidden) {
        var firstField = wrap.querySelector('input[type="text"]');
        if (firstField) firstField.focus();
    }
});
</script>
