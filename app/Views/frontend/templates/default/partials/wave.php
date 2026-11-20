<?php
// Волна между секциями. Цвет «оранжевый» берётся из темы
// (Настройки → Оформление), остальные — как переданы страницей.
$waveColors = (isset($waveColors) && is_array($waveColors) && count($waveColors) === 2) ? $waveColors : ['#ff8c1a', '#fff3e6'];
if ($waveColors[0] === '#ff8c1a') {
    $waveColors[0] = 'var(--theme-primary, #ff8c1a)';
}
if ($waveColors[1] === '#fff3e6') {
    $waveColors[1] = 'var(--theme-primary-light, #fff3e6)';
}
?>
<svg class="wave" viewBox="0 0 1440 100" preserveAspectRatio="none" aria-hidden="true">
    <rect width="1440" height="100" fill="<?= htmlspecialchars($waveColors[0], ENT_QUOTES, 'UTF-8') ?>"></rect>
    <path d="M0,60 C240,110 480,10 720,55 C960,100 1200,15 1440,60 L1440,100 L0,100 Z" fill="<?= htmlspecialchars($waveColors[1], ENT_QUOTES, 'UTF-8') ?>"></path>
</svg>
