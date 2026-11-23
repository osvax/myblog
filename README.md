# myblog.loc

Персональный блог, портфолио и CRM-система на самописном PHP MVC без фреймворков.

Проект объединяет публичный сайт, блог, портфолио проектов, формы обратной связи, заказы и полноценную административную панель с ролями, правами доступа, статистикой и управлением контентом.

![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-10.9%2B-003545?logo=mariadb&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-5-646CFF?logo=vite&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/license-private-lightgrey)

---

## Содержание

- [Возможности](#возможности)
- [Технологический стек](#технологический-стек)
- [Минимальные требования](#минимальные-требования)
- [Структура проекта](#структура-проекта)
- [Быстрый запуск в OSPanel](#быстрый-запуск-в-ospanel)
- [Установка на обычный сервер](#установка-на-обычный-сервер)
- [Переменные окружения](#переменные-окружения)
- [База данных и миграции](#база-данных-и-миграции)
- [Frontend-сборка](#frontend-сборка)
- [Импорт проектов портфолио](#импорт-проектов-портфолио)
- [Административная панель](#административная-панель)
- [Демонстрационный пользователь](#демонстрационный-пользователь)
- [Безопасность](#безопасность)
- [Команды разработки](#команды-разработки)
- [Маршруты](#маршруты)
- [Резервное копирование](#резервное-копирование)
- [Решение типовых проблем](#решение-типовых-проблем)
- [Лицензия](#лицензия)

---

## Возможности

### Публичная часть

- Главная страница с настраиваемыми блоками.
- Блог со статьями, рубриками, тегами, поиском и пагинацией.
- Страницы отдельных статей с комментариями.
- Портфолио с карточками проектов и детальными страницами.
- Галерея скриншотов проектов.
- Описание проекта по блокам:
  - «О проекте»;
  - «Задача»;
  - «Решение»;
  - «Результат»;
  - показатели результата.
- Ссылки на демонстрацию проекта и техническое задание.
- Контактные формы, обратная связь и заявки на заказ.
- Публичный профиль пользователя.
- Авторизация по email и паролю.
- OAuth-вход через Яндекс.
- Восстановление пароля по email.
- Двухфакторная авторизация.
- Режим обслуживания сайта.
- SEO-маршруты `robots.txt` и `sitemap.xml`.
- Переключение публичной темы.

### Административная панель

- Дашборд с показателями сайта.
- Статистика посещений и трафика.
- Управление статьями, рубриками и комментариями.
- Управление проектами портфолио.
- Управление страницами и HTML-контентом.
- Управление меню сайта и навигацией рубрик.
- Управление файлами и изображениями.
- Настройки сайта, SEO, почты, авторизации и безопасности.
- Настройка калькулятора.
- Работа с заказами и сообщениями.
- Консультант.
- Роли и права доступа.
- Пользователи, профили, аватары и пароли.
- Редактор шаблонов с предпросмотром.
- Поддержка нескольких frontend- и admin-тем.

### Роли

В проекте используется RBAC-модель с правами на разделы административной панели:

- `admin` — полный доступ;
- `editor` — управление контентом;
- `manager` — заказы, сообщения, консультант и калькулятор;
- `user` — без доступа к админке;
- `readonly_demo` — просмотр всех разделов без создания, изменения и удаления данных.

---

## Технологический стек

### Backend

- PHP 8.1+.
- Самописная MVC-архитектура без Laravel/Symfony Framework.
- PDO и MariaDB/MySQL.
- Composer.
- PHP-Dotenv.
- Symfony Mailer и Symfony Mime.
- HTMLPurifier.
- Predis для Redis rate limiting и кеширования.
- PHPUnit, PHPStan, PHP_CodeSniffer.

### Frontend

- HTML5.
- CSS3 и SCSS.
- JavaScript ES modules.
- Bootstrap 5.3.
- Bootstrap Icons.
- Vite 5.
- PostCSS и Autoprefixer.
- Highlight.js для подсветки кода.
- CodeMirror для редакторов HTML.

### Сервер

- Apache или Nginx.
- PHP-FPM для Nginx либо PHP-модуль Apache.
- MariaDB 10.9+ или совместимый MySQL.
- Redis рекомендуется для production rate limiting.
- HTTPS рекомендуется для production и обязателен для OAuth-сценариев.

---

## Минимальные требования

### Для локальной разработки

| Компонент | Минимум | Рекомендуется для проекта |
|---|---:|---:|
| ОС | Windows 10 / Linux / macOS | Windows 10/11 + OSPanel |
| CPU | 2 ядра | 4 ядра |
| RAM | 4 ГБ | 8 ГБ |
| Свободное место | 2 ГБ | 5 ГБ |
| PHP | 8.1 | 8.5.1 |
| MariaDB/MySQL | MariaDB 10.5 / MySQL 8.0 | MariaDB 10.9 |
| Node.js | 18 LTS | 20 LTS или новее |
| npm | 9 | 10 или новее |
| Composer | 2.x | последняя стабильная версия |
| Redis | необязательно | Redis 6+ |

### Для production

- PHP 8.1 или новее.
- PHP extensions:
  - `PDO`;
  - `pdo_mysql`;
  - `mbstring`;
  - `openssl`;
  - `curl`;
  - `fileinfo`;
  - `gd` или Imagick для изображений;
  - `json`;
  - `zip` для работы с архивами тем.
- Веб-сервер должен использовать каталог `public` как document root.
- Пользователь PHP должен иметь права записи на:
  - `public/uploads`;
  - `var`;
  - каталог сессий PHP.
- Для отправки писем необходим рабочий SMTP-сервер.

---

## Структура проекта

```text
myblog.loc/
├── app/
│   ├── Controllers/
│   │   ├── Admin/              # Контроллеры административной панели
│   │   ├── Api/                # API-контроллеры
│   │   └── Frontend/           # Публичная часть сайта
│   ├── Core/                   # Router, Request, Response, View, Database, auth
│   ├── Models/                 # Модели и репозитории
│   └── Views/
│       ├── admin/              # Шаблоны административной панели
│       └── frontend/           # Публичные шаблоны и темы
├── config/                    # Конфигурация приложения
├── database/
│   ├── migrations/             # SQL-миграции и CLI-скрипты
│   ├── import_portfolio_projects.php
│   └── reset-admin-password.php
├── public/
│   ├── admin/                  # Legacy-ассеты и страницы админки
│   ├── build/                  # Production-сборка Vite, создаётся npm run build
│   ├── img/                    # Общие изображения
│   ├── portfolio/              # Исходные папки проектов портфолио
│   ├── uploads/                # Загружаемые файлы, не коммитить
│   └── index.php               # Front controller
├── resources/
│   ├── js/                    # Vite JavaScript entries
│   └── scss/                  # SCSS-исходники
├── routes/
│   ├── api.php                # API-маршруты
│   └── web.php                # Публичные и admin web-маршруты
├── tests/                     # Точечные проверки
├── .env.example               # Пример переменных окружения
├── composer.json
├── package.json
├── vite.config.js
└── README.md
```

---

## Быстрый запуск в OSPanel

Проект рассчитан на OSPanel под Windows.

### 1. Разместить проект

Каталог проекта:

```text
E:\OSPanel\www-html\myblog.loc
```

В настройках домена OSPanel document root должен указывать на:

```text
E:\OSPanel\www-html\myblog.loc\public
```

Локальный адрес:

```text
http://myblog.loc
```

HTTPS-адрес при настроенном сертификате:

```text
https://myblog.loc
```

### 2. Установить зависимости

В командной строке из корня проекта:

```cmd
composer install
npm install
```

Для проекта используется CLI PHP:

```cmd
C:\PHP\php.exe
```

### 3. Создать `.env`

```cmd
copy .env.example .env
```

После этого отредактировать `.env` и указать настоящие реквизиты БД, SMTP и приложения.

### 4. Создать базу данных

Создать базу данных с кодировкой `utf8mb4`:

```sql
CREATE DATABASE val_myblog
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

### 5. Применить миграции

Миграции 001–015 являются историческими SQL-миграциями. Для существующего проекта актуальные операции выполняются специализированными CLI-скриптами из `database/migrations`.

Основные скрипты:

```cmd
C:\PHP\php.exe database\migrations\run_006.php
C:\PHP\php.exe database\migrations\run_008.php
C:\PHP\php.exe database\migrations\run_009.php
C:\PHP\php.exe database\migrations\run_010_roles.php
C:\PHP\php.exe database\migrations\run_014.php
C:\PHP\php.exe database\migrations\run_015.php
C:\PHP\php.exe database\migrations\run_016.php
C:\PHP\php.exe database\migrations\run_017.php
C:\PHP\php.exe database\migrations\run_018.php
C:\PHP\php.exe database\migrations\run_019.php
```

Запускайте только миграции, которые ещё не применены к конкретной базе. Идемпотентные скрипты можно запускать повторно, но перед повторным развёртыванием рекомендуется сделать резервную копию БД.

### 6. Заполнить настройки сайта

```cmd
C:\PHP\php.exe database\migrations\run_seed_settings.php
```

### 7. Собрать frontend

```cmd
npm run build
```

### 8. Проверить приложение

```cmd
curl.exe http://myblog.loc/health
```

Ожидаемый ответ:

```json
{"success":true,"app":"myblog"}
```

После этого открыть:

```text
http://myblog.loc/
http://myblog.loc/blog.html
http://myblog.loc/portfolio.html
http://myblog.loc/admin/login.html
```

---

## Установка на обычный сервер

### Apache

Document root виртуального хоста должен указывать на каталог `public`:

```apache
<VirtualHost *:80>
    ServerName myblog.example.com
    DocumentRoot /var/www/myblog.loc/public

    <Directory /var/www/myblog.loc/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx + PHP-FPM

Минимальная схема:

```nginx
server {
    listen 80;
    server_name myblog.example.com;
    root /var/www/myblog.loc/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass 127.0.0.1:9000;
    }

    location ~ /\. {
        deny all;
    }
}
```

После настройки сервера:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

На production-файлы `.env`, `config/database.php`, `public/uploads` и SMTP-секреты не должны попадать в Git.

---

## Переменные окружения

Основные переменные находятся в `.env.example`.

### Приложение

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
APP_URL=https://myblog.example.com
```

### База данных

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=val_myblog
DB_USERNAME=val
DB_PASSWORD=change_me
DB_PREFIX=va_
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

Все новые SQL-запросы должны использовать `db_table('table_name')`, а не жёстко заданный префикс `va_`.

### Сессии

```dotenv
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false
```

Для HTTPS production:

```dotenv
SESSION_SECURE_COOKIE=true
```

Протокол также определяется через `request_is_https()` с учётом `HTTPS`, `X-Forwarded-Proto` и порта 443.

### SMTP

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="My Blog"
```

Пароли SMTP не хранить в README, исходном коде или Git.

### Redis и rate limiting

```dotenv
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DB=0
```

Если Redis недоступен, проект использует предусмотренный приложением fallback-механизм rate limiting. Для production рекомендуется Redis.

---

## База данных и миграции

Префикс таблиц берётся из `DB_PREFIX`. В текущем окружении используется:

```text
va_
```

Основные таблицы:

| Таблица | Назначение |
|---|---|
| `va_users` | Пользователи и администраторы |
| `va_roles` | Роли админки |
| `va_role_permissions` | Права ролей на разделы |
| `va_articles` | Статьи блога |
| `va_categories` | Рубрики |
| `va_portfolio_projects` | Проекты портфолио |
| `va_project_gallery` | Скриншоты проектов |
| `va_project_info_items` | Ссылки, документы и дополнительные поля проектов |
| `va_messages` | Обращения, комментарии и ответы |
| `va_orders` | Заказы |
| `va_site_settings` | Настройки сайта |
| `va_user_activities` | Посещения и действия пользователей |

Миграции портфолио:

```cmd
C:\PHP\php.exe database\migrations\run_014.php
C:\PHP\php.exe database\migrations\run_019.php
```

`run_019.php` добавляет отдельное текстовое поле `result` для описания результата проекта.

---

## Frontend-сборка

В проекте используется Vite 5.

### Разработка

```cmd
npm run dev
```

### Production-сборка

```cmd
npm run build
```

Результат появляется в:

```text
public/build
```

### Предпросмотр production-сборки

```cmd
npm run preview
```

### Проверка JavaScript

```cmd
npm run lint:js
```

### Проверка SCSS

```cmd
npm run lint:css
```

Production-загрузка ассетов определяется наличием:

```text
public/build/.vite/manifest.json
```

Не удаляйте `defer` у legacy-скриптов админки: часть скриптов зависит от `window.AdminApi`, который создаётся Vite-бандлом.

---

## Импорт проектов портфолио

Исходные папки проектов размещаются так:

```text
public/portfolio/<project-folder>/
├── index.html
└── job/
    ├── description.md
    ├── screenshots/
    │   ├── 01-image.png
    │   └── 02-image.jpg
    └── TZ-template.pdf
```

Импорт выполняется вручную:

```cmd
C:\PHP\php.exe database\import_portfolio_projects.php
```

Импортёр:

- ищет новые папки с `job/description.md`;
- создаёт проект в БД;
- читает разделы Markdown;
- импортирует задачу, решение и результат;
- копирует PNG/JPG/JPEG из `job/screenshots`;
- добавляет скриншоты в галерею;
- добавляет ссылку на корневой `index.html`;
- добавляет ссылку на `TZ-template.pdf/html/doc/docx`;
- не создаёт дубликаты при повторном запуске;
- пропускает символические ссылки и неподходящие файлы.

Проверка импортёра:

```cmd
C:\PHP\php.exe tests\portfolio-importer.php
```

Для автоматического запуска импорт можно добавить в Планировщик заданий Windows или cron. Не подключайте его к обычному GET-запросу сайта: просмотр страницы не должен изменять базу данных.

---

## Административная панель

Адрес:

```text
/admin/login.html
```

Email администратора хранится в настройке сайта:

```text
site_settings.adminLoginEmail
```

Пароль хранится только в виде хеша в таблице пользователей. Для безопасного сброса пароля используйте:

```cmd
C:\PHP\php.exe database\reset-admin-password.php
```

Или укажите email явно:

```cmd
C:\PHP\php.exe database\reset-admin-password.php admin@example.com
```

Скрипт запрашивает пароль интерактивно и не выводит его в лог.

---

## Демонстрационный пользователь

В локальной базе создана read-only учётная запись:

```text
Email: demo@user.test
Роль: readonly_demo
```

Учётная запись может просматривать все разделы административной панели, но сервер блокирует изменяющие запросы. Пароль не хранится в README и исходном коде.

Для повторного создания или смены пароля используйте CLI-скрипт:

```cmd
C:\PHP\php.exe database\migrations\run_readonly_demo.php
```

Пароль передаётся через стандартный ввод. Не передавайте его в командной строке production-системы и не сохраняйте в истории shell.

---

## Безопасность

В проекте предусмотрены:

- CSRF-защита административных и публичных форм.
- `HttpOnly` и `SameSite=Strict` для сессионных cookie.
- `Secure` cookie при HTTPS.
- Проверка авторизации до выполнения admin API.
- RBAC с правами на отдельные разделы.
- Read-only серверная защита демонстрационной роли.
- Rate limiting для входа, регистрации, восстановления пароля, комментариев, заказов и контактных форм.
- Санитизация HTML-контента страниц через HTMLPurifier.
- Ограничение путей загружаемых файлов.
- Защита `public/uploads` от прямого доступа через `.htaccess`/Nginx.
- Проверка расширения и MIME-типа изображений.
- Безопасная выдача публичных файлов через MVC-маршруты.
- Защита от подстановки SQL-значений через prepared statements.
- Использование `db_table()` для таблиц с префиксом.

Перед production-развёртыванием:

1. Установите уникальные пароли БД и SMTP.
2. Установите `APP_DEBUG=false`.
3. Включите HTTPS.
4. Удалите тестовые учётные записи или смените их пароли.
5. Проверьте права записи только для необходимых каталогов.
6. Не коммитьте `.env`, `config/database.php`, `public/uploads` и резервные копии.
7. Проверьте настройки SMTP и отправку тестового письма из админки.

---

## Команды разработки

### Composer

```cmd
composer install
composer update
composer dump-autoload
composer validate
```

### PHP-анализ

```cmd
composer test
composer phpstan
composer cs
composer cs:fix
```

Эквивалентные команды:

```cmd
vendor\bin\phpunit
vendor\bin\phpstan analyse app --level=5
vendor\bin\phpcs --standard=PSR12 app
vendor\bin\phpcbf --standard=PSR12 app
```

### Node/Vite

```cmd
npm install
npm run dev
npm run build
npm run preview
npm run lint:js
npm run lint:css
```

### Проверка PHP-файла

```cmd
C:\PHP\php.exe -l path\to\file.php
```

Например:

```cmd
C:\PHP\php.exe -l public\index.php
```

### Проверка JavaScript

```cmd
node --check public\admin\js\portfolio-edit.js
```

---

## Маршруты

### Публичные страницы

```text
GET /
GET /index.html
GET /blog.html
GET /portfolio.html
GET /contacts.html
GET /order.html
GET /robots.txt
GET /sitemap.xml
GET /{article-slug}.html
GET /{project-slug}.html
GET /admin/login.html
```

### Авторизация

```text
POST /api/auth/login
GET  /api/auth/check
GET  /api/auth/csrf
POST /api/auth/logout
POST /api/auth/user/login
POST /api/auth/user/logout
POST /api/auth/register
POST /api/auth/forgot-password
POST /api/auth/reset-password
GET  /api/auth/yandex
GET  /api/auth/yandex/callback
```

Auth-endpoints пользователя принимают JSON:

```http
Content-Type: application/json
```

### Формы

```text
POST /api/forms/order
POST /api/forms/feedback
POST /api/forms/comment
POST /api/consultant
```

### Админ API

Основные разделы:

```text
/api/admin/articles
/api/admin/projects
/api/admin/categories
/api/admin/orders
/api/admin/messages
/api/admin/statistics
/api/admin/files
/api/admin/templates
/api/admin/users
/api/admin/roles
/api/admin/settings
/api/admin/menus
/api/admin/rubric-menus
/api/admin/calculator
/api/admin/pages
/api/admin/landing-page
/api/admin/custom-fields
```

Изменяющие admin API-запросы требуют авторизованную сессию и CSRF-токен в заголовке:

```http
X-CSRF-Token: <token>
```

---

## Резервное копирование

Перед миграциями и массовым импортом создавайте резервную копию БД.

### MariaDB/MySQL

```cmd
mysqldump -h MariaDB-10.9 -P 3306 -u val -p val_myblog > backup.sql
```

В Linux:

```bash
mysqldump -h 127.0.0.1 -P 3306 -u val -p val_myblog > backup.sql
```

Не добавляйте `backup.sql` в Git.

Для восстановления:

```cmd
mysql -h MariaDB-10.9 -P 3306 -u val -p val_myblog < backup.sql
```

Также отдельно сохраняйте:

- `.env`;
- `public/uploads`;
- пользовательские темы;
- конфигурацию Nginx/Apache;
- SSL-сертификаты и настройки PHP-FPM.

---

## Решение типовых проблем

### `500 Internal Server Error` после чистой установки

Проверьте:

1. Запущены Apache/Nginx, PHP и MariaDB.
2. Создан `.env`.
3. Установлены Composer-зависимости.
4. Применены миграции.
5. Document root указывает на `public`.
6. PHP CLI запускается через нужный бинарник.

Проверка:

```cmd
C:\PHP\php.exe -v
C:\PHP\php.exe -l public\index.php
```

### Страница открывается, но стили или JS не обновились

Пересоберите frontend:

```cmd
npm run build
```

Затем очистите кеш браузера. Проверьте наличие:

```text
public/build/.vite/manifest.json
```

### Ошибка подключения к базе данных

Проверьте значения:

```dotenv
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
DB_PREFIX
```

В OSPanel имя хоста MariaDB может быть `MariaDB-10.9`, а не `127.0.0.1`.

### Не отправляются письма

Проверьте SMTP-настройки в `.env` или разделе настроек сайта и выполните тест отправки из административной панели.

### OAuth Яндекс возвращает ошибку callback

Полный callback URL должен совпадать в настройках приложения Яндекс и сайта:

```text
https://myblog.loc/api/auth/yandex/callback
```

Для production замените домен на фактический HTTPS-домен.

### Проект портфолио не импортируется

Проверьте структуру:

```text
public/portfolio/<folder>/job/description.md
```

Запустите:

```cmd
C:\PHP\php.exe database\import_portfolio_projects.php
```

Подробности об ошибке будут выведены CLI-скриптом.

---

## Рабочий процесс Git

Основная ветка разработки:

```text
dev
```

Перед началом работы:

```cmd
git status
git pull --rebase origin dev
```

Проверка изменений:

```cmd
git diff HEAD
git diff --check
git log -n 3
```

Не добавляйте в Git:

```text
.env
config/database.php
public/uploads/
var/template-backups/
backup.sql
```

Перед коммитом проверьте только файлы текущей задачи и не используйте широкое добавление всех изменений, если в рабочем дереве есть пользовательские WIP-правки.

---

## Лицензия

Проект является частным программным обеспечением. Права на исходный код, дизайн, контент, изображения и материалы портфолио принадлежат владельцу проекта и соответствующим правообладателям.

Перед публикацией репозитория в открытом доступе удалите приватные данные, тестовые учётные записи, локальные материалы портфолио и любые сторонние ассеты, для которых нет разрешения на распространение.
