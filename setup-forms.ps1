# Adding action attributes and name attributes to forms (ASCII-safe)

function Update-File($file, $replacements) {
    $path = Join-Path (Get-Location) $file
    $c = [System.IO.File]::ReadAllText($path, [System.Text.Encoding]::UTF8)
    foreach ($key in $replacements.Keys) {
        $c = $c.Replace($key, $replacements[$key])
    }
    $utf8NoBom = New-Object System.Text.UTF8Encoding($false)
    [System.IO.File]::WriteAllText($path, $c, $utf8NoBom)
    Write-Host ("OK: " + $file)
}

# --- order.html ---
Update-File 'order.html' @{
    '<form class="card border-0 shadow-sm p-4 order-form">' = '<form action="php/order.php" method="POST" class="card border-0 shadow-sm p-4 order-form">'
    'id="orderName"'     = 'id="orderName" name="name"'
    'id="orderEmail"'    = 'id="orderEmail" name="email"'
    'id="orderPhone"'    = 'id="orderPhone" name="phone"'
    'id="orderType"'     = 'id="orderType" name="type"'
    'id="orderBudget"'   = 'id="orderBudget" name="budget"'
    'id="orderDeadline"' = 'id="orderDeadline" name="deadline"'
    'id="orderMessage"'  = 'id="orderMessage" name="message"'
}

# --- contacts.html ---
Update-File 'contacts.html' @{
    '<form class="card border-0 shadow-sm p-4 order-form">' = '<form action="php/feedback.php" method="POST" class="card border-0 shadow-sm p-4 order-form">'
    'id="contactName"'    = 'id="contactName" name="name"'
    'id="contactEmail"'   = 'id="contactEmail" name="email"'
    'id="contactSubject"' = 'id="contactSubject" name="subject"'
    'id="contactPhone"'   = 'id="contactPhone" name="phone"'
    'id="contactMessage"' = 'id="contactMessage" name="message"'
}

# --- post pages (comment forms) ---
$posts = 'post-bootstrap.html', 'post-ui.html', 'post-js.html',
         'post-adaptive.html', 'post-git.html', 'post-speed.html'
$postRepl = @{
    '<form class="card border-0 shadow-sm p-4 order-form">' = '<form action="php/comment.php" method="POST" class="card border-0 shadow-sm p-4 order-form">'
    'id="commentName"'  = 'id="commentName" name="name"'
    'id="commentEmail"' = 'id="commentEmail" name="email"'
    'id="commentText"'  = 'id="commentText" name="message"'
}
foreach ($p in $posts) {
    Update-File $p $postRepl
}

Write-Host 'Done.'
