# Подключение js/forms.js ко всем страницам с формами
$files = 'order.html', 'contacts.html', 'post-bootstrap.html', 'post-ui.html',
         'post-js.html', 'post-adaptive.html', 'post-git.html', 'post-speed.html'

$anchor = 'bootstrap.bundle.min.js"></script>'
$addition = 'bootstrap.bundle.min.js"></script>' + "`r`n" + '    <script src="js/forms.js"></script>'

foreach ($f in $files) {
    $c = Get-Content $f -Raw
    if ($c -notmatch 'js/forms\.js') {
        $c = $c.Replace($anchor, $addition)
        Set-Content $f -Value $c -NoNewline -Encoding UTF8
        Write-Host "OK: $f"
    } else {
        Write-Host "SKIP (уже подключён): $f"
    }
}

$count = (Get-ChildItem -Filter *.html | Select-String -Pattern 'js/forms\.js').Count
Write-Host "Всего страниц с подключённым forms.js: $count"
