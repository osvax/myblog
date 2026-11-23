<?php
// Волна между секциями. Цвета синхронизированы с темой сайта.
$waveColors = (isset($waveColors) && is_array($waveColors) && count($waveColors) === 2) ? $waveColors : ['#ff8c1a', '#fff3e6'];
$waveColorMap = [
    '#ff8c1a' => 'var(--theme-primary, #ff8c1a)',
    '#fff3e6' => 'var(--theme-primary-light, #fff3e6)',
    '#ffffff' => 'var(--theme-bg, #ffffff)',
    '#353535' => '#353535',
];
foreach ($waveColors as $index => $color) {
    $waveColors[$index] = $waveColorMap[strtolower((string) $color)] ?? $color;
}
?>
<svg class="wave" viewBox="0 0 1440 100" preserveAspectRatio="none" aria-hidden="true">
    <rect width="1440" height="100" fill="<?= htmlspecialchars($waveColors[0], ENT_QUOTES, 'UTF-8') ?>"></rect>
    <path d="M0,60 C240,110 480,10 720,55 C960,100 1200,15 1440,60 L1440,100 L0,100 Z" fill="<?= htmlspecialchars($waveColors[1], ENT_QUOTES, 'UTF-8') ?>"></path>
</svg>
