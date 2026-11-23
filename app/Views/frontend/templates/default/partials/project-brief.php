<?php
    // Блоки «О проекте / Задача / Решение / Результат» — данные проекта из БД.
    $briefDescription = trim((string) ($project['description'] ?? ''));
    $briefDescriptionBlocks = [];
    if ($briefDescription !== '') {
        foreach (preg_split('/\r?\n{2,}/', $briefDescription) as $briefBlock) {
            $briefBlock = trim($briefBlock);
            if ($briefBlock !== '') {
                $briefDescriptionBlocks[] = $briefBlock;
            }
        }
    }
    $briefTasks = [];
    if (isset($project['task']) && is_array($project['task'])) {
        foreach ($project['task'] as $briefTask) {
            $briefTask = trim((string) $briefTask);
            if ($briefTask !== '') {
                $briefTasks[] = $briefTask;
            }
        }
    }
    $briefSolution = trim((string) ($project['solution'] ?? ''));
    $briefResultText = trim((string) ($project['result'] ?? ''));
    $briefResults = [];
    if (isset($project['results']) && is_array($project['results'])) {
        foreach ($project['results'] as $briefResult) {
            if (!is_array($briefResult)) {
                continue;
            }
            $briefResultValue = trim((string) ($briefResult['value'] ?? ''));
            $briefResultLabel = trim((string) ($briefResult['label'] ?? ''));
            if ($briefResultValue === '' && $briefResultLabel === '') {
                continue;
            }
            $briefResults[] = ['value' => $briefResultValue, 'label' => $briefResultLabel];
        }
    }
    $briefFirstHeading = true; // первый блок в колонке без верхнего отступа
?>
<?php if ($briefDescriptionBlocks): ?>
    <h2 class="<?= $briefFirstHeading ? 'fw-bold mb-3' : 'fw-bold mt-5 mb-3' ?>">О проекте</h2>
    <?php $briefFirstHeading = false; ?>
    <?php foreach ($briefDescriptionBlocks as $briefBlock): ?>
        <p class="fw-bold"><?= nl2br(htmlspecialchars($briefBlock, ENT_QUOTES, 'UTF-8')) ?></p>
    <?php endforeach; ?>
<?php endif; ?>
<?php if ($briefTasks): ?>
    <h2 class="<?= $briefFirstHeading ? 'fw-bold mb-3' : 'fw-bold mt-5 mb-3' ?>">Задача</h2>
    <?php $briefFirstHeading = false; ?>
    <ul class="fw-bold">
        <?php foreach ($briefTasks as $briefTask): ?>
            <li><?= htmlspecialchars($briefTask, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php if ($briefSolution !== ''): ?>
    <h2 class="<?= $briefFirstHeading ? 'fw-bold mb-3' : 'fw-bold mt-5 mb-3' ?>">Решение</h2>
    <?php $briefFirstHeading = false; ?>
    <p class="fw-bold"><?= nl2br(htmlspecialchars($briefSolution, ENT_QUOTES, 'UTF-8')) ?></p>
<?php endif; ?>
<?php if ($briefResultText !== ''): ?>
    <h2 class="<?= $briefFirstHeading ? 'fw-bold mb-3' : 'fw-bold mt-5 mb-3' ?>">Результат</h2>
    <p class="fw-bold"><?= nl2br(htmlspecialchars($briefResultText, ENT_QUOTES, 'UTF-8')) ?></p>
    <?php $briefFirstHeading = false; ?>
<?php endif; ?>
<?php if ($briefResults): ?>
    <h2 class="<?= $briefFirstHeading ? 'fw-bold mb-3' : 'fw-bold mt-5 mb-3' ?>">Показатели результата</h2>
    <div class="row g-3">
        <?php foreach ($briefResults as $briefResult): ?>
            <div class="col-sm-4">
                <div class="feature-item p-3 text-center h-100">
                    <?php if ($briefResult['value'] !== ''): ?>
                        <h3 class="fw-bold text-orange mb-1"><?= htmlspecialchars($briefResult['value'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <?php endif; ?>
                    <p class="fw-bold mb-0"><?= htmlspecialchars($briefResult['label'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
