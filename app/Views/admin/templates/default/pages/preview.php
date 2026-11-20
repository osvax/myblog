<?php
/**
 * Предпросмотр на полной странице (без всплывающих окон).
 *
 * Режимы:
 *  - ?page={id}&title={...} — статическая страница: iframe на /api/admin/pages/{id}/preview
 *    (рендер с раскрытыми плейсхолдерами, работает и для неактивных страниц).
 *  - ?title={...} — содержимое передаётся из sessionStorage («Шаблоны»):
 *    ключ myblog.admin.preview.v1, JSON {title, html}.
 */
$previewPageId = isset($_GET['page']) ? (int) $_GET['page'] : 0;
$previewTitle = isset($_GET['title']) && is_string($_GET['title']) ? trim($_GET['title']) : '';
if ($previewTitle === '') {
    $previewTitle = $previewPageId > 0 ? 'Страница #' . $previewPageId : 'Предпросмотр';
}
?>
<div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary btn-sm" type="button" id="previewBack">
            <i class="bi bi-arrow-left me-1"></i>Назад
        </button>
        <h1 class="h5 fw-bold mb-0"><i class="bi bi-eye me-2"></i><?= htmlspecialchars($previewTitle, ENT_QUOTES, 'UTF-8') ?></h1>
    </div>
    <?php if ($previewPageId > 0): ?>
        <a class="btn btn-outline-secondary btn-sm"
           href="/api/admin/pages/<?= $previewPageId ?>/preview" target="_blank" rel="noopener">
            <i class="bi bi-box-arrow-up-right me-1"></i>Открыть в новой вкладке
        </a>
    <?php endif; ?>
</div>
<div class="card admin-card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if ($previewPageId > 0): ?>
            <iframe id="previewFrame" class="preview-frame w-100 border-0 bg-white"
                    src="/api/admin/pages/<?= $previewPageId ?>/preview" title="Предпросмотр страницы"></iframe>
        <?php else: ?>
            <iframe id="previewFrame" class="preview-frame w-100 border-0 bg-white" title="Предпросмотр"></iframe>
            <script>
                (function () {
                    'use strict';
                    var frame = document.getElementById('previewFrame');
                    var data = null;
                    try {
                        var raw = sessionStorage.getItem('myblog.admin.preview.v1');
                        if (raw) {
                            data = JSON.parse(raw);
                            sessionStorage.removeItem('myblog.admin.preview.v1');
                        }
                    } catch (error) {
                        data = null;
                    }
                    if (data && typeof data.html === 'string') {
                        frame.setAttribute('srcdoc', data.html);
                    } else {
                        frame.setAttribute('srcdoc', '<body style="font-family: sans-serif; padding: 2rem; color: #666;">'
                            + 'Данных предпросмотра нет. Вернитесь в раздел и нажмите кнопку «Предпросмотр» снова.</body>');
                    }
                })();
            </script>
        <?php endif; ?>
    </div>
</div>
<script>
    (function () {
        'use strict';
        var back = document.getElementById('previewBack');
        back.addEventListener('click', function () {
            // Если пришли с раздела админки — назад; если открыли напрямую — на дашборд
            if (window.history.length > 1 && (document.referrer || '').indexOf('/admin/') === 0) {
                window.history.back();
            } else {
                window.location.href = '/admin/index.html';
            }
        });
    })();
</script>
