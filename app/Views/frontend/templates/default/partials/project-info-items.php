<?php
    $projectInfoItems = isset($project['infoItems']) && is_array($project['infoItems']) ? $project['infoItems'] : [];
    $projectInfoIcons = [
        'link' => 'bi-box-arrow-up-right',
        'document' => 'bi-file-earmark-text',
        'certificate' => 'bi-patch-check',
    ];
    // Пути прикреплённых файлов: только из разделов файлового менеджера.
    $projectInfoDocRegex = '#^uploads/documents/portfolio/[a-z0-9][a-z0-9-]*\.(?:doc|docx)$#';
    $projectInfoCertRegex = '#^uploads/images/portfolio/[a-z0-9][a-z0-9-]*\.(?:jpg|jpeg|png)$#';
?>
<?php foreach ($projectInfoItems as $item): ?>
    <?php if (empty($item['name'])) { continue; } ?>
    <?php
        $itemKind = isset($item['kind']) && isset($projectInfoIcons[$item['kind']]) ? $item['kind'] : 'link';
        $itemValue = isset($item['value']) && is_string($item['value']) ? $item['value'] : '';
        $itemFilePath = isset($item['file_path']) && is_string($item['file_path']) ? $item['file_path'] : '';
        $isUrl = (bool) preg_match('#^(?:https?://|/portfolio/[a-z0-9_-]+/(?:index\.html|job/TZ-template\.(?:pdf|html|docx?))$)#i', $itemValue);
        $docUrl = ($itemKind === 'document' && preg_match($projectInfoDocRegex, $itemFilePath)) ? $itemFilePath : '';
        $certUrl = ($itemKind === 'certificate' && preg_match($projectInfoCertRegex, $itemFilePath)) ? $itemFilePath : '';
    ?>
    <p class="fw-bold mb-2">
        <i class="bi <?= $projectInfoIcons[$itemKind] ?> text-orange"></i>
        <strong><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>:</strong>
        <?php if ($docUrl): ?>
            <a href="/<?= htmlspecialchars($docUrl, ENT_QUOTES, 'UTF-8') ?>" download class="text-orange link-orange">Скачать файл <i class="bi bi-download"></i></a>
        <?php elseif ($certUrl): ?>
            <a href="/<?= htmlspecialchars($certUrl, ENT_QUOTES, 'UTF-8') ?>" class="cert-thumb d-inline-block align-middle ms-1"
               data-lightbox-src="/<?= htmlspecialchars($certUrl, ENT_QUOTES, 'UTF-8') ?>"
               data-lightbox-alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>">
                <img src="/<?= htmlspecialchars($certUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>" class="rounded border">
            </a>
        <?php elseif ($isUrl): ?>
            <a href="<?= htmlspecialchars($itemValue, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="text-orange link-orange"><?= htmlspecialchars($itemValue, ENT_QUOTES, 'UTF-8') ?></a>
        <?php elseif ($itemValue !== ''): ?>
            <?= htmlspecialchars($itemValue, ENT_QUOTES, 'UTF-8') ?>
        <?php endif; ?>
    </p>
<?php endforeach; ?>
