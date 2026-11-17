# Генерация растрового изображения: большой терминал с JavaScript-кодом
# Фон прозрачный — на главной странице просвечивает оранжевый градиент героя
Add-Type -AssemblyName System.Drawing

$bmp = New-Object System.Drawing.Bitmap(600, 520)
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias

function New-Color($r, $gr, $b) { [System.Drawing.Color]::FromArgb(255, $r, $gr, $b) }
function New-Brush($c) { New-Object System.Drawing.SolidBrush($c) }

$window   = New-Color 53 53 53       # рамка терминала #353535
$titlebar = New-Color 74 74 74       # заголовок окна
$screen   = New-Color 30 30 30       # область кода
$white    = New-Color 255 255 255
$gray     = New-Color 130 130 130
$orange   = New-Color 255 140 26     # акцент
$purple   = New-Color 198 120 221    # ключевые слова
$green    = New-Color 126 194 126    # строки
$blue     = New-Color 97 175 239     # функции
$yellow   = New-Color 235 199 90     # переменные
$comment  = New-Color 106 153 85     # комментарии
$red      = New-Color 255 95 87
$amber    = New-Color 254 188 46
$lime     = New-Color 40 200 64

function New-RoundedRect($x, $y, $w, $h, $r) {
    $p = New-Object System.Drawing.Drawing2D.GraphicsPath
    $p.AddArc($x, $y, 2*$r, 2*$r, 180, 90)
    $p.AddArc($x+$w-2*$r, $y, 2*$r, 2*$r, 270, 90)
    $p.AddArc($x+$w-2*$r, $y+$h-2*$r, 2*$r, 2*$r, 0, 90)
    $p.AddArc($x, $y+$h-2*$r, 2*$r, 2*$r, 90, 90)
    $p.CloseFigure()
    return $p
}

# ---------- ОКНО ТЕРМИНАЛА ----------
$winPath = New-RoundedRect 10 10 580 500 22
$g.FillPath((New-Brush $window), $winPath)

# Заголовок окна
$titlePath = New-RoundedRect 10 10 580 56 22
$g.FillPath((New-Brush $titlebar), $titlePath)
$g.FillRectangle((New-Brush $titlebar), 10, 40, 580, 26)

# Кнопки управления окном
$g.FillEllipse((New-Brush $red),   34, 28, 20, 20)
$g.FillEllipse((New-Brush $amber), 64, 28, 20, 20)
$g.FillEllipse((New-Brush $lime),  94, 28, 20, 20)

# Подпись файла
$font = New-Object System.Drawing.Font('Segoe UI', 13, [System.Drawing.FontStyle]::Bold)
$g.DrawString('app.js — JavaScript', $font, (New-Brush $white), 200, 26)

# ---------- ОБЛАСТЬ КОДА ----------
$codePath = New-RoundedRect 24 80 552 416 14
$g.FillPath((New-Brush $screen), $codePath)

# Строка кода: плоский массив пар (цвет, ширина)
function Draw-Line($y, $indent, $tokens) {
    # номер строки
    $g.FillRectangle((New-Brush $gray), 44, $y, 14, 10)
    $x = 80 + $indent
    for ($i = 0; $i -lt $tokens.Count; $i += 2) {
        $c = $tokens[$i]
        $w = [int]$tokens[$i + 1]
        $g.FillRectangle((New-Brush $c), [int]$x, [int]$y, $w, 10)
        $x += $w + 12
    }
}

# JavaScript-код
Draw-Line 110 0  @($purple,52, $yellow,74, $orange,20, $blue,60, $white,18, $orange,16)
Draw-Line 140 0  @($white,18, $purple,64, $yellow,80, $green,90, $orange,14)
Draw-Line 170 24 @($blue,66, $white,14, $yellow,96, $white,14, $green,110, $orange,16)
Draw-Line 200 24 @($white,14, $yellow,84, $blue,56, $green,100)
Draw-Line 230 24 @($purple,44, $white,16, $yellow,70, $white,14, $red,52, $green,80)
Draw-Line 260 0  @($white,16)
Draw-Line 290 0  @($comment,300)                                     # комментарий
Draw-Line 320 0  @($purple,58, $blue,64, $white,18, $yellow,90, $orange,20)
Draw-Line 350 24 @($yellow,80, $white,14, $blue,56, $white,14, $green,84)
Draw-Line 380 24 @($purple,46, $white,16, $yellow,64, $white,14, $red,48)
Draw-Line 410 0  @($orange,20, $white,16, $yellow,110, $green,96)

# Курсор на последней строке
$g.FillRectangle((New-Brush $orange), 330, 440, 14, 14)

# ---------- СОХРАНЕНИЕ ----------
if (-not (Test-Path "img")) { New-Item -ItemType Directory -Path "img" | Out-Null }
$bmp.Save((Join-Path (Get-Location) "img\hero-terminal.png"), [System.Drawing.Imaging.ImageFormat]::Png)
$g.Dispose()
$bmp.Dispose()
Write-Host "Изображение сохранено: img/hero-terminal.png"
