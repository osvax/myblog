<?php

namespace App\Core;

/**
 * Страницы ошибок (404 / 403 / 500) для публичной части и админ-панели.
 * Автономный HTML без зависимости от шаблонов — должен работать даже
 * если конфигурация тем или БД недоступна.
 */
class ErrorPages
{
    /**
     * @param int    $code  HTTP-код ошибки (404, 403, 500)
     * @param string $title Заголовок страницы
     * @param string $lead  Короткое пояснение
     * @param string $home  Ссылка кнопки «на главную»
     */
    public static function page(int $code, string $title, string $lead, string $home = '/'): Response
    {
        $code = in_array($code, [404, 403, 500], true) ? $code : 500;
        $icon = $code === 404 ? '🔍' : ($code === 403 ? '🔒' : '⚠️');

        $html = <<<"HTML"
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$code} — {$title}</title>
<meta name="robots" content="noindex">
<style>
  :root { --orange: #ff8c1a; --dark: #353535; --light: #fff3e6; }
  * { box-sizing: border-box; }
  body {
    margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
    font-family: "Segoe UI", Montserrat, Arial, sans-serif; background: var(--light); color: var(--dark);
  }
  .wrap { text-align: center; padding: 24px; max-width: 640px; }
  .badge {
    width: 84px; height: 84px; margin: 0 auto 18px; border-radius: 50%;
    background: var(--orange); color: #fff; font-size: 2.6rem; line-height: 1;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 12px 28px rgba(255, 140, 26, .35);
  }
  .code {
    font-size: clamp(5rem, 16vw, 8rem); font-weight: 800; line-height: 1;
    color: var(--orange); letter-spacing: .05em; margin: 0;
  }
  h1 { font-size: 1.6rem; font-weight: 700; margin: 14px 0 10px; }
  p.lead { font-size: 1.05rem; color: #777; margin: 0 0 28px; }
  a.btn {
    display: inline-block; background: var(--orange); color: #fff; text-decoration: none;
    font-weight: 700; padding: 12px 30px; border-radius: 10px;
    transition: background .15s ease, transform .15s ease;
  }
  a.btn:hover { background: #e67a0c; transform: translateY(-1px); }
  .hint { margin-top: 18px; font-size: .85rem; color: #999; }
</style>
</head>
<body>
  <div class="wrap">
    <div class="badge">{$icon}</div>
    <p class="code">{$code}</p>
    <h1>{$title}</h1>
    <p class="lead">{$lead}</p>
    <a class="btn" href="{$home}">Вернуться на главную</a>
    <div class="hint">Если ошибка возникла неожиданно и повторяется, напишите нам — мы разберёмся.</div>
  </div>
</body>
</html>
HTML;

        return new Response($html, $code, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
