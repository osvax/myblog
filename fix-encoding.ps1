# Restoring encoding for the 8 form pages:
# they were double-encoded (UTF-8 text was read as cp1251 and saved as UTF-8).
# Reverse: read as UTF-8, encode to cp1251 bytes, decode as UTF-8, save as UTF-8 (no BOM).

$files = 'order.html', 'contacts.html', 'post-bootstrap.html', 'post-ui.html',
         'post-js.html', 'post-adaptive.html', 'post-git.html', 'post-speed.html'

$cp1251 = [System.Text.Encoding]::GetEncoding(1251)
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)

# Marker of mojibake: Cyrillic capital ER (U+0420) followed by chars in 0x80-0xBF
$marker = [string][char]0x0420
$pattern = [regex]::Escape($marker) + '[\u0080-\u00BF]'

foreach ($f in $files) {
    $path = Join-Path (Get-Location) $f
    $c = [System.IO.File]::ReadAllText($path, [System.Text.Encoding]::UTF8)
    if ($c -match $pattern) {
        $bytes = $cp1251.GetBytes($c)
        $fixed = [System.Text.Encoding]::UTF8.GetString($bytes)
        [System.IO.File]::WriteAllText($path, $fixed, $utf8NoBom)
        Write-Host ("FIXED: " + $f)
    } else {
        Write-Host ("OK: " + $f)
    }
}
