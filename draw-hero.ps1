# Генерация растрового изображения: человек за компьютерным столом программирует
Add-Type -AssemblyName System.Drawing

$bmp = New-Object System.Drawing.Bitmap(600, 600)
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias

function New-Color($r, $gr, $b) { [System.Drawing.Color]::FromArgb(255, $r, $gr, $b) }

$bg        = New-Color 255 243 230   # светло-оранжевый фон
$floor     = New-Color 240 220 192   # пол
$dark      = New-Color 53 53 53      # #353535
$orange    = New-Color 255 140 26    # акцентный оранжевый
$orangeDrk = New-Color 230 122 13
$skin      = New-Color 247 197 159
$gray      = New-Color 107 107 107
$deskC     = New-Color 181 101 29
$screenC   = New-Color 30 30 30
$white     = New-Color 255 255 255
$green     = New-Color 126 194 126
$blue      = New-Color 97 175 239
$red       = New-Color 224 108 117
$yellow    = New-Color 235 199 90

function New-Brush($c) { New-Object System.Drawing.SolidBrush($c) }

# Фон
$g.FillRectangle((New-Brush $bg), 0, 0, 600, 600)
# Пол
$g.FillRectangle((New-Brush $floor), 0, 520, 600, 80)

# ---------- СТУЛ ----------
$g.FillRectangle((New-Brush $gray), 500, 255, 55, 170)   # спинка
$g.FillRectangle((New-Brush $gray), 410, 370, 150, 35)   # сиденье
$g.FillRectangle((New-Brush $gray), 430, 405, 14, 120)   # ножка 1
$g.FillRectangle((New-Brush $gray), 540, 405, 14, 120)   # ножка 2

# ---------- СТОЛ ----------
$g.FillRectangle((New-Brush $deskC), 50, 430, 470, 28)   # столешница
$g.FillRectangle((New-Brush $deskC), 70, 458, 22, 102)   # ножка стола 1
$g.FillRectangle((New-Brush $deskC), 478, 458, 22, 102)  # ножка стола 2

# ---------- МОНИТОР ----------
$g.FillRectangle((New-Brush $dark), 140, 408, 110, 20)   # подставка
$g.FillRectangle((New-Brush $dark), 180, 345, 28, 65)    # ножка монитора
$g.FillRectangle((New-Brush $dark), 85, 180, 210, 170)   # рамка
$g.FillRectangle((New-Brush $screenC), 97, 192, 186, 146)# экран

# Строки кода на экране
$codeColors = @($orange, $green, $blue, $red, $yellow, $orange, $blue, $green)
$widths = @(120, 90, 140, 70, 110, 60, 130, 100)
$y = 205
for ($i = 0; $i -lt 8; $i++) {
    $g.FillRectangle((New-Brush $codeColors[$i]), 115, $y, $widths[$i], 9)
    $y += 16
}

# ---------- КЛАВИАТУРА И МЫШЬ ----------
$g.FillRectangle((New-Brush $gray), 300, 412, 120, 20)
$g.FillEllipse((New-Brush $gray), 430, 418, 25, 14)

# ---------- ЧЕЛОВЕК ----------
# Тело (свитер)
$g.FillEllipse((New-Brush $orange), 415, 285, 95, 120)
# Голова
$g.FillEllipse((New-Brush $skin), 430, 215, 70, 70)
# Волосы
$g.FillEllipse((New-Brush $dark), 426, 203, 78, 45)
# Глаз (смотрит на экран)
$g.FillEllipse((New-Brush $dark), 440, 246, 8, 8)
# Руки к клавиатуре
$armPen = New-Object System.Drawing.Pen($orangeDrk, 20)
$armPen.StartCap = [System.Drawing.Drawing2D.LineCap]::Round
$armPen.EndCap = [System.Drawing.Drawing2D.LineCap]::Round
$g.DrawLine($armPen, 440, 320, 370, 415)
$g.DrawLine($armPen, 458, 330, 400, 418)
# Кисти рук
$g.FillEllipse((New-Brush $skin), 358, 405, 24, 20)
$g.FillEllipse((New-Brush $skin), 390, 410, 24, 20)

# ---------- КРУЖКА КОФЕ ----------
$g.FillRectangle((New-Brush $white), 480, 392, 30, 38)
$handlePen = New-Object System.Drawing.Pen($white, 6)
$g.DrawArc($handlePen, 505, 398, 20, 24, -90, 180)
# Пар над кружкой
$steamPen = New-Object System.Drawing.Pen($gray, 3)
$g.DrawBezier($steamPen, 488, 385, 478, 370, 498, 362, 490, 348)
$g.DrawBezier($steamPen, 502, 385, 492, 372, 512, 364, 504, 350)

# ---------- СОХРАНЕНИЕ ----------
if (-not (Test-Path "img")) { New-Item -ItemType Directory -Path "img" | Out-Null }
$bmp.Save((Join-Path (Get-Location) "img\hero-coder.png"), [System.Drawing.Imaging.ImageFormat]::Png)
$g.Dispose()
$bmp.Dispose()
Write-Host "Изображение сохранено: img/hero-coder.png"
