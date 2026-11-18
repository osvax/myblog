# QWEN.md — правила работы с проектом myblog.loc

Персональный блог/CRM на самописном PHP MVC (без фреймворков), окружение OSPanel (Windows).

## Среда
- OSPanel: Apache + MariaDB, PHP 8.5.1. CLI PHP: `C:\PHP\php.exe` — PHP-скрипты запускать только через него.
- БД: `val_myblog`, логин/пароль `val` — реквизиты в `config/database.php`.
- Локальный домен: `myblog.loc`. Каталог сессий PHP: `E:\OSPanel\temp\PHP-8.5\default\session\`.
- Vite 5: `npm run build` (входы: app, admin, forms, auth, consultant, calculator, login; outDir `public/build`).
- Ветка разработки: `dev`.

## Жёсткие правила
1. **Коммитить и пушить только когда пользователь явно попросил.**
2. **Текст коммита — на русском.** Писать его в UTF-8 файл и делать `git commit -F <файл>` (cmd.exe портит кириллицу и кавычки в прямом аргументе).
3. **`config/database.php` никогда не коммитить** (уже в .gitignore; при `git add` проверять).
4. **Комментарии в коде — на русском.**
5. **После каждой задачи НЕ гонять полные e2e-прогоны** — только минимальные точечные проверки (явное пожелание пользователя).
6. **После отладки чистить свои временные файлы** (D:\TMP и %TEMP%: `qwen_*`, `pw_*`, `dbg_*`, `p_*`, `a_*`, `r_*`) и проверять, что `git status` показывает только ожидаемые изменения.
7. **Тестовые данные в БД (пароли пользователей, сущности) обязательно восстанавливать** после проверки.

## Архитектура и конвенции кода
- Структура: `app/Core` (Router, View, Response, auth.php, admin-functions.php, Mailer, RateLimiter, Sanitizer), `app/Controllers/{Admin,Api,Frontend}`, `app/Models`, `app/Views/{admin,frontend}/templates/default`.
- Router: сначала точное совпадение метод+путь, потом маршруты с `{param}`. Метод-обработчик получает `($request, array $parameters)`.
- Админ-API `/api/admin/*`: section-guard по ролям и страницам проверяется ДО роутера (карта в `admin-functions.php` + `public/index.php`).
- CSRF админки: заголовок `X-CSRF-Token` ИЛИ POST-поле `csrf_token` (для обычных HTML-форм). `admin_csrf_token` в сессии создаётся только при логине в админку.
- Сессия админки: `admin_authenticated`, `admin_authenticated_at`, `admin_user_id`, `admin_email`, `admin_name`, `admin_role`, `admin_csrf_token`.
- Сессия публичной части: `user_authenticated`, `user_id`, `user_email`, `user_name`, `user_role`, `user_authenticated_at`, `csrf_token`.
- Auth-эндпоинты (`/api/auth/register`, `/api/auth/user/login`, смена пароля профилей) принимают только JSON-тело.
- `Response::redirect($location, $status = 302)`; редирект после POST — 303.

## Vite — гибрид загрузки ассетов
- Production-ветка лейаутов грузит бандлы строго через манифест (`$viteManifest`, инжектится во все лейауты через `View.php`); dev-ветка — CDN + legacy-фолбэк.
- **В production-бандле нет `window.bootstrap`** (bootstrap ESM глобал не ставит): во фронтенд-коде всегда `import { Toast } from 'bootstrap'` (общий shared-chunk между входами).
- Портировано на Vite: вся админка (CSS + JS), `forms.js`, auth, consultant, calculator, login. В legacy остались мелкие inline-скрипты (site-settings, blog-search) — при правках не плодить дубли: новый JS только в `resources/js/`.

## UI-конвенции
- Админка: простые правки — инлайн в списке, **без модалок**; **никаких статичных баннеров-подсказок** вверху админ-страниц; уведомления — через `AdminNotifications`/тосты.
- Раздел «Сообщения» админки: карточки ответов с тумблером.
- Иконки Bootstrap Icons: проверять существование имени (напр. `bi-heading` НЕ существует).
- Заголовки страниц во фронтенд-партиале `page-heading` — это `<div>`, не `<h1>`.
- В шаблонах админки избегать `getOrCreateInstance(null)` вне модалок — ломает инициализацию скриптов.

## E2E/отладка на Windows (если всё же нужны)
- Ложная PHP-сессия для тестов: файл `sess_<32 hex>` в каталоге сессий, содержимое — точная сериализация массива (лучше генерировать `serialize()`).
- PowerShell 5.1: алиас `Curl` перекрывает функции (использовать `curl.exe` напрямую); JSON-тело писать в файл и шлать `curl -d @file` (PS ломает кавычки в аргументах нативных команд); в cmd.exe разделитель команд — `&&` (НЕ `;`); .ps1-скрипты — только ASCII.
- cmd.exe раскрывает `%VAR%` по всей строке — не передавать через него переменные окружения в curl.
