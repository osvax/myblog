# MyBlog — Архитектурный аудит и Roadmap

**Автор:** AI Assistant  
**Дата:** 2024  
**Проект:** `valentin/myblog` — персональный блог, портфолио и CRM на самописном PHP MVC

---

## 1. Текущее состояние проекта

### Стек технологий

| Слой | Технологии |
|------|------------|
| **Backend** | PHP 8.1+, самописный MVC, PDO/MariaDB, Composer, PHP-DI, Symfony Mailer/Mime, HTMLPurifier, Predis (Redis) |
| **Frontend** | Vite 5, Bootstrap 5.3, Bootstrap Icons, Vanilla JS (ES modules), SCSS (ITCSS-подобная структура), Highlight.js, CodeMirror |
| **Инфраструктура** | Apache/Nginx, PHP-FPM, MariaDB 10.9+, Redis (рекомендуется), Docker-ready |
| **Качество кода** | PHPStan Level 5, PSR-12 (phpcs/phpcbf), PHPUnit |

### Возможности

- Публичная часть: блог, портфолио, контакты, заказы, авторизация (email/pass, 2FA, OAuth Яндекс), темы, SEO
- Админка: дашборд, статистика, CRUD статей/проектов/категорий/страниц, файловый менеджер, меню, роли/права (RBAC), лендинг-билдер, калькулятор, консультант (чат), импорт портфолио из Markdown
- Безопасность: CSRF, HttpOnly/SameSite/Secure куки, rate limiting, санитизация HTML, защита загрузок, prepared statements

### Структура проекта

```
MyBlog/
├── app/
│   ├── Controllers/ (Admin, Api, Frontend)
│   ├── Core/ (Router, Request, Response, Database, Auth, RateLimiter, ...)
│   ├── Models/ (ContentRepository, User, SiteSettings, ...)
│   └── Views/ (admin, frontend/themes)
├── config/ (container.php, database.php, admin-*.php)
├── database/migrations/ (SQL + CLI скрипты)
├── public/ (index.php, build/, uploads/, portfolio/)
├── resources/ (js/, scss/)
├── routes/ (web.php, api.php)
├── tests/
├── composer.json, package.json, vite.config.js
└── README.md
```

---

## 2. Рефакторинг / Архитектура (Приоритет: Высокий)

### 2.1 Query Builder

**Проблема:** Raw SQL повсюду, дублирование `db_table()`, ручные JOIN/WHERE.

**Решение:** Лёгкая обёртка над PDO — `App\Core\QueryBuilder`.

```php
// app/Core/QueryBuilder.php
final class QueryBuilder
{
    private PDO $pdo;
    private string $table;
    private array $wheres = [];
    private array $bindings = [];
    private array $joins = [];
    private array $selects = ['*'];
    private ?string $orderBy = null;
    private ?int $limit = null;

    public function __construct(PDO $pdo, string $table) { ... }
    public function select(array $columns): self { ... }
    public function where(string $col, string $op, mixed $val): self { ... }
    public function join(string $table, string $first, string $op, string $second, string $type = 'INNER'): self { ... }
    public function orderBy(string $col, string $dir = 'ASC'): self { ... }
    public function limit(int $n): self { ... }
    public function get(): array { ... }
    public function first(): ?array { ... }
    public function insert(array $data): string|false { ... }
    public function update(array $data): int { ... }
    public function delete(): int { ... }
}
```

**Пример рефакторинга `ContentRepository::articles()`:**

```php
// До
$statement = $this->db->query(
    'SELECT a.*, c.name AS category, c.slug AS category_slug
     FROM ' . db_table('articles') . ' a LEFT JOIN ' . db_table('categories') . ' c ON c.id = a.category_id ' . $where . '
     ORDER BY a.published_at DESC, a.created_at DESC'
);

// После
return $this->qb('articles a')
    ->select(['a.*', 'c.name AS category', 'c.slug AS category_slug'])
    ->join('categories c', 'c.id', '=', 'a.category_id', 'LEFT')
    ->when($publishedOnly, fn($q) => $q->where('a.is_published', '=', 1))
    ->orderBy('a.published_at', 'DESC')
    ->get();
```

### 2.2 DTO + Валидация

**Проблема:** Валидация разброшена по контроллерам (`articleInput`, `projectInput`).

**Решение:** DTO классы с атрибутами валидации.

```php
// app/DTO/ArticleDTO.php
namespace App\DTO;

use Respect\Validation\Validator as v;
use Attribute;

#[Attribute] class NotBlank {}
#[Attribute] class Length { public function __construct(public int $max) {} }
#[Attribute] class Email {}

class ArticleDTO
{
    public string $id;
    #[NotBlank, Length(180)]
    public string $slug;
    #[NotBlank]
    public string $title;
    #[NotBlank]
    public string $description;
    #[NotBlank]
    public string $category;
    public string $content;
    #[NotBlank]
    public string $author;
    public string $date; // Y-m-d
    public bool $active;
    public array $tags = [];
    public string $cover = '';
    public string $icon = 'bi-file-text';

    public static function fromRequest(array $data, ?string $id = null): self
    {
        $dto = new self();
        $dto->id = $id ?? bin2hex(random_bytes(16));
        $dto->slug = $data['slug'] ?? '';
        $dto->title = $data['title'] ?? '';
        // ...
        return $dto;
    }

    public function validate(): array
    {
        $errors = [];
        // Используем respect/validation или свой минимальный валидатор
        return $errors;
    }
}
```

**В контроллере:**

```php
$dto = ArticleDTO::fromRequest($request->json(), $params['id'] ?? null);
$errors = $dto->validate();
if ($errors) return $this->json(['success' => false, 'errors' => $errors], 400);
$article = $this->content->saveArticle($dto->toArray());
```

### 2.3 Миграции с версионированием

**Проблема:** Набор `run_*.php` скриптов — не версиями, а разовыми патчами.

**Решение:** Таблица `va_migrations` + `MigrationRunner`.

```sql
CREATE TABLE va_migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

```php
// app/Core/MigrationRunner.php
class MigrationRunner
{
    public function __construct(private PDO $pdo) {}

    public function getExecuted(): array { ... }
    public function run(string $migrationsPath): void
    {
        $files = glob("$migrationsPath/V*__*.php");
        sort($files);
        $executed = $this->getExecuted();
        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (in_array($name, $executed)) continue;
            require_once $file;
            $class = "Migration_$name";
            (new $class())->up($this->pdo);
            $this->pdo->prepare("INSERT INTO va_migrations (name) VALUES (?)")->execute([$name]);
        }
    }
    public function rollback(int $steps = 1): void { ... }
}
```

**Формат миграции:** `database/migrations/V001__init.php`

```php
class Migration_V001__init
{
    public function up(PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE va_users (...);");
    }
    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE va_users;");
    }
}
```

### 2.4 DI везде

**Проблема:** Контроллеры создаются вручную в `routes/*.php`.

**Решение:** Автовайринг через PHP-DI.

```php
// config/container.php
return [
    App\Core\QueryBuilder::class => \DI\factory(fn() => new App\Core\QueryBuilder(Database::connection(), '')),
    App\Models\ContentRepository::class => \DI\autowire()->constructorParameter('qb', \DI\get(App\Core\QueryBuilder::class)),
    App\Controllers\Admin\ContentController::class => \DI\autowire(),
    // ...
];
```

```php
// routes/api.php
return function (Router $router) {
    $container = require __DIR__ . '/../config/container.php';
    $content = $container->get(App\Controllers\Admin\ContentController::class);
    // ...
};
```

### 2.5 Error Handling + Logging

**Monolog + JSON логи + Sentry.**

```php
// app/bootstrap.php (добавить)
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

$log = new Logger('myblog');
$handler = new StreamHandler(APP_ROOT . '/var/log/app.jsonl');
$handler->setFormatter(new JsonFormatter());
$log->pushHandler($handler);

// В Router::invoke — ловим Throwable, логируем с контекстом
```

### 2.6 API Resources / Transformers

```php
// app/Core/ApiResource.php
class ApiResource
{
    public static function item(mixed $data, string $transformer): array
    {
        return ['success' => true, 'data' => (new $transformer())->transform($data)];
    }
    public static function collection(iterable $items, string $transformer): array
    {
        return ['success' => true, 'data' => array_map(fn($i) => (new $transformer())->transform($i), $items)];
    }
    public static function error(string $message, int $code = 400, array $meta = []): array
    {
        return ['success' => false, 'error' => $message, 'meta' => $meta];
    }
}
```

```php
// app/Transformers/ArticleTransformer.php
class ArticleTransformer
{
    public function transform(array $article): array
    {
        return [
            'id' => $article['id'],
            'slug' => $article['slug'],
            'title' => $article['title'],
            // только нужные поля, никаких внутренних
        ];
    }
}
```

---

## 3. Фронтенд — Миграция на Vue 3 (Постепенная гибридная)

### Стратегия: Острова → Shared UI Kit → Полная миграция

### 3.1 Подготовка

```bash
npm i vue@3 pinia @vitejs/plugin-vue vue-tsc typescript
```

```js
// vite.config.js
import vue from '@vitejs/plugin-vue';
export default defineConfig({
  plugins: [vue()],
  // ...
});
```

### 3.2 Этапы

| Этап | Что делаем | Примеры |
|------|------------|---------|
| **0. Фундамент** | Vue 3, TS, Pinia, Vite plugin | `tsconfig.json`, `vite.config.js` |
| **1. Острова** | Изолированные Vue-приложения в админке | Консультант, Калькулятор, Шаблоны, Файловый менеджер |
| **2. UI Kit** | `resources/js/ui/` — BaseButton, BaseInput, BaseModal, BaseTable, BaseToast, BaseAvatar | `<BaseButton variant="orange" @click="save">` |
| **3. Pinia сторы** | `useAuthStore`, `useConsultantStore`, `useMediaStore` | Централизованный стейт, API слой с `ky` |
| **4. Публичная часть** | `app.js` → `createApp(App).mount('#app')`, `window.__INITIAL_STATE__` | Реактивная навигация, консультант-виджет |
| **5. Очистка** | Удаление Vanilla JS модулей по мере покрытия | Меньше кода, меньше багов |

### 3.3 Важные нюансы

- **Темизация:** CSS переменные `--theme-*` уже работают. Vue-компоненты просто их используют.
- **Bootstrap 5:** Оставляем как CSS-фреймворк. Не ставим `bootstrap-vue-next`. Пишем свои обёртки.
- **CodeMirror 6:** Обернуть в `<CodeMirrorEditor v-model="html" :extensions="[html(), php()]"/>`.
- **Vite entry points:** Останется 2 — `app` (public) + `admin` (админка).
- **TypeScript:** Строгий режим с дня 1.

### 3.4 Генерация TS типов из PHP DTO

```bash
# Скрипт в CI: php artisan dto:generate-types → resources/js/types/api.d.ts
```

```ts
// types/api.d.ts (генерируется)
export interface ArticleDTO {
  id: string;
  slug: string;
  title: string;
  // ...
}
export interface ArticleListResponse { success: true; articles: ArticleDTO[]; }
```

В Vue: `const response = await api.get<ArticleListResponse>('/api/admin/articles')` — полный autocomplete.

---

## 4. Производительность

### 4.1 Кэширование

| Уровень | Что кэшируем | Инструмент | Инвалидация |
|---------|--------------|------------|-------------|
| **HTTP** | Статика (`public/build/*`, `uploads/*`) | Nginx `expires 1y`, `immutable` | Vite manifest hash |
| **API (GET)** | Списки статей, проекты, категории, меню, настройки | Redis + `ETag` / `If-None-Match` | Тэг-кэш: `articles`, `projects`, `categories`, `menus`, `settings` |
| **БД** | Дашборд, статистика | Материализованные представления / таблицы-кэш | На запись в исходные таблицы |
| **PHP** | OPcache, конфиг | OPcache (включён) | Деплой |
| **Фрагменты HTML** | Блоки сайдбара, меню, виджеты | Redis + теги | Соответствующие сущности |

**Пример кэша списка статей:**

```php
$cacheKey = 'admin:articles:list';
$cached = $this->redis->get($cacheKey);
if ($cached) return json_decode($cached, true);

$articles = $this->qb(...)->get();
$this->redis->setex($cacheKey, 300, json_encode($articles));
$this->redis->sAdd('cache:tags:articles', $cacheKey);
return $articles;
```

**Инвалидация:**

```php
$this->redis->sMembers('cache:tags:articles')->each(fn($key) => $this->redis->del($key));
$this->redis->del('cache:tags:articles');
```

### 4.2 Lazy-loading / Оптимизация запросов

| Проблема | Решение |
|----------|---------|
| `N+1` в `projectRow` → `gallery` + `infoItems` | `JOIN` + `JSON_ARRAYAGG` в одном запросе |
| `articles()` + `commentCounts()` — 2 запроса | `LEFT JOIN (SELECT article_slug, COUNT(*) ...) cm ON cm.article_slug = a.slug` |
| Дашборд: 10+ запросов | Один агрегирующий SQL / материализованное представление `va_dashboard_stats` |
| `LandingPage::findActive($slug)` на каждом роуте | Кэш в Redis на 60с + тег `landing_pages` |
| Изображения портфолио | WebP/AVIF через `vite-imagetools` или `sharp` в очереди, `srcset` + `loading=lazy` |

### 4.3 БД индексы (проверить `EXPLAIN`)

```sql
CREATE INDEX idx_articles_published_slug ON va_articles (is_published, slug);
CREATE INDEX idx_articles_category_published ON va_articles (category_id, is_published, published_at DESC);
CREATE INDEX idx_messages_type_approved_slug ON va_messages (message_type, is_approved, article_slug);
CREATE INDEX idx_user_activities_created_at ON va_user_activities (created_at);
CREATE INDEX idx_orders_status_date ON va_orders (status, order_date);
```

---

## 5. Новые фичи

### 5.1 API (публичное / партнёрское)

- **Версионирование:** `/api/v1/` + `Accept: application/vnd.myblog.v1+json`
- **DTO Resources:** `ApiResource::item($article, ArticleResource::class)`
- **OpenAPI/Swagger:** `zircote/swagger-php` атрибуты → `public/docs/openapi.json` → Swagger UI в `/api/docs`
- **Rate Limiting:** Разные лимиты: `api:public` (60/min), `api:auth` (300/min), `api:write` (30/min)
- **Auth:** Personal Access Tokens (`va_api_tokens`), скопы (`articles:read`, `orders:write`)

### 5.2 Вебхуки

```
POST /api/webhooks/{provider} → WebhookController::handle()
```

- Таблица `va_webhook_endpoints (id, url, events_json, secret, active, retry_count, last_delivery_at)`
- Подпись: `X-Signature: sha256(payload + secret)`
- Очередь доставки с экспоненциальным бэкоффом
- События: `article.published`, `order.created`, `message.received`, `user.registered`

### 5.3 Очередь задач (Job Queue)

**Таблица `va_jobs`:**

```sql
CREATE TABLE va_jobs (
  id CHAR(36) PRIMARY KEY,
  queue VARCHAR(64) NOT NULL,
  payload JSON NOT NULL,
  attempts TINYINT UNSIGNED DEFAULT 0,
  max_attempts TINYINT UNSIGNED DEFAULT 3,
  reserved_at DATETIME NULL,
  available_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_jobs_queue_available ON va_jobs (queue, available_at);
```

**Worker (`bin/queue-worker.php`):**

```php
while (true) {
    $job = $pdo->prepare("
        UPDATE va_jobs SET reserved_at = NOW()
        WHERE id = (
            SELECT id FROM va_jobs
            WHERE queue = ? AND available_at <= NOW() AND attempts < max_attempts
            ORDER BY available_at ASC LIMIT 1
            FOR UPDATE SKIP LOCKED
        )
        RETURNING *
    ");
    $job->execute(['default']);
    if ($row = $job->fetch()) {
        $handler = JobRegistry::get($row['payload']['job']);
        try { $handler->handle($row['payload']['data']); $pdo->exec("DELETE FROM va_jobs WHERE id = '{$row['id']}'"); }
        catch (\Throwable $e) { /* retry logic, backoff */ }
    } else { sleep(1); }
}
```

**Что в очередь:** Email, вебхуки, импорт портфолио, генерация sitemap, пересчёт статистики, обработка изображений.

### 5.4 Тесты

| Уровень | Инструмент | Что тестируем |
|---------|------------|---------------|
| **Unit** | PHPUnit | DTO валидация, QueryBuilder, slugify, калькулятор, утилиты |
| **Integration** | PHPUnit + SQLite `:memory:` | Repository, миграции, API контроллеры |
| **E2E** | Playwright (TypeScript) | Критичные флоу: логин → статья → публикация; заказ → админка → статус |
| **Frontend Unit** | Vitest + @vue/test-utils | Pinia сторы, UI Kit, API клиент |
| **Static** | PHPStan 5, psalm, eslint, stylelint, vue-tsc | CI gate |

**Цель покрытия:** 80% Unit/Integration, 100% публичные API эндпоинты.

---

## 6. CI/CD / Деплой

### 6.1 GitHub Actions (`.github/workflows/ci.yml`)

```yaml
name: CI
on: [push, pull_request]
jobs:
  php:
    runs-on: ubuntu-latest
    services:
      mariadb: { image: mariadb:10.9, env: { MYSQL_ROOT_PASSWORD: test, MYSQL_DATABASE: test }, ports: [3306:3306] }
      redis: { image: redis:7-alpine, ports: [6379:6379] }
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.3', extensions: pdo_mysql, redis, mbstring, gd, zip }
      - run: composer install --prefer-dist --no-progress
      - run: composer phpstan
      - run: composer cs
      - run: composer test
  node:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with: { node-version: '20', cache: 'npm' }
      - run: npm ci
      - run: npm run lint:js
      - run: npm run lint:css
      - run: npm run build
  e2e:
    needs: [php, node]
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: npx playwright install --with-deps chromium
      - run: npx playwright test
```

### 6.2 Деплой (Zero-downtime)

| Окружение | Как |
|-----------|-----|
| **Staging** | Авто-деплой из `dev` ветки. `docker compose -f docker-compose.staging.yml up -d --build`. Миграции в `entrypoint.sh`. |
| **Production** | Ручной триггер (GitHub Environment `production` + approval). Blue/Green или Rolling update. |
| **Миграции** | `php database/migrate.php up` в `initContainer` / `preStart` hook. |
| **Ассеты** | `npm run build` в CI → артефакт `public/build/` → копируется в образ. `manifest.json` для кэш-бастинга. |
| **Secrets** | GitHub Environments / Vault / `.env` на сервере. `APP_KEY` генерируется один раз. |
| **Healthcheck** | `GET /health` → `{"success":true,"app":"myblog"}`. Nginx/K8s probe. |
| **Rollback** | `kubectl rollout undo` / `docker compose up -d --force-recreate` предыдущий образ. |

### 6.3 Dockerfile

```dockerfile
# build stage
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources/ ./resources/
COPY vite.config.js postcss.config.js ./
RUN npm run build

# php stage
FROM php:8.3-fpm-alpine
RUN apk add --no-cache linux-headers $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis pdo_mysql gd opcache
WORKDIR /var/www/html
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer*.php ./
RUN composer install --no-dev --optimize-autoloader --no-interaction
COPY . .
COPY --from=frontend /app/public/build ./public/build
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/public/uploads
USER www-data
CMD ["php-fpm"]
```

---

## 7. Roadmap (План действий)

| Неделя | Фокус | Делаблы |
|--------|-------|---------|
| **1** | **Архитектура** | QueryBuilder, DTO/Validation, DI в routes, API Resource базовый класс |
| **2** | **Миграции + CI** | MigrationRunner, baseline, GitHub Actions (php/node/lint/test) |
| **3** | **Кэш + БД** | Redis кэш с тегами, индексы, N+1 фиксы, дашборд → материализованка |
| **4** | **Очередь + Вебхуки** | `va_jobs`, worker, email/webhook jobs, retry/backoff |
| **5** | **Vue 3: Админ-острова** | Setup Vue+TS+Pinia, UI Kit, перенос Консультанта + Калькулятора |
| **6** | **Vue 3: Публика** | Монтирование `app.js`, общий стейт, консультант-виджет, ленивые импорты |
| **7** | **API v1 + OpenAPI** | `/api/v1/`, токены, сваггер, rate limits |
| **8** | **E2E + Deploy** | Playwright тесты, Dockerfile, staging/prod pipelines, healthchecks |
| **9+** | **Наблюдаемость** | Sentry, структурированные логи, метрики (Prometheus + Grafana) |

---

## 8. С чего начать прямо сейчас

1. **QueryBuilder + DTO** — готовую реализацию для `ContentRepository::articles()` и `ArticleDTO`.
2. **MigrationRunner** — класс + baseline-скрипт для существующих `run_*.php`.
3. **Vue 3 setup** — `vite.config.js`, `tsconfig.json`, `resources/js/ui/BaseButton.vue`, Pinia store boilerplate.

---

*Документ сгенерирован на основе аудита кодбейза `valentin/myblog`.*