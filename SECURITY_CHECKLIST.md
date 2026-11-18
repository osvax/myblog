# Security / Production-Ready Checklist

## 1. Environment Variables (.env) ✅
- [x] Установить `vlucas/phpdotenv` через Composer
- [x] Создать `.env.example` с шаблоном
- [x] Вынести DB credentials, APP_KEY, SMTP настройки в `.env`
- [x] Обновить `config/database.php` для чтения из `$_ENV`
- [x] Добавить `.env` в `.gitignore`

## 2. CSRF Protection for Public Forms ✅
- [x] Добавить генерацию CSRF токена в сессию (`user_start_session`)
- [x] Создать хелпер `csrf_token()` / `csrf_field()` для форм
- [x] Добавить проверку в `FormController` (order, feedback, comment)
- [x] Обновить `forms.js` для отправки токена
- [x] Добавить meta тег с токеном в layout

## 3. SMTP Integration for Password Reset ✅
- [x] Установить `symfony/mailer` + `symfony/mime`
- [x] Добавить SMTP настройки в `.env` / `SiteSettings` (админка)
- [x] Рефактор `AuthController::sendResetEmail()` на Symfony Mailer
- [x] Рефактор `FormController::sendMail()` на Symfony Mailer
- [x] Добавить тест отправки в админке (Настройки → Почта) - `/api/admin/settings/test-email`

## 4. Rate Limiting on Auth & Public POST ✅
- [x] Создать `RateLimiter` класс (Redis / файл / БД)
- [x] Применить к `/api/auth/user/login`, `/api/auth/register`, `/api/auth/forgot-password`, `/api/auth/reset-password`
- [x] Применить к публичным POST: заказ, контакты, комментарии
- [x] Вернуть 429 с `Retry-After` заголовком

## 5. XSS Protection for Landing Page HTML ✅
- [x] Установить `ezyang/htmlpurifier`
- [x] Санитизировать `html_content` при сохранении в `LandingPage::save()`
- [x] Санитизировать при выводе в `HomeController` (defense in depth)

## 6. Uploads Protection (.htaccess / nginx) ✅
- [x] Создать `public/uploads/.htaccess` с `deny from all`
- [x] Маршрут `/uploads/{scope}/{id}/{filename}` с проверкой прав уже существует в `MediaController::serve()`
- [x] MVC route отдаёт файлы через `file_get_contents` с проверками безопасности
- [x] FileManagerController тоже отдаёт файлы через API с проверкой админской авторизации

---

## 7. Frontend Modernization (Vite + SCSS + ES Modules) ✅
- [x] Настроить `package.json` с Vite, SCSS, Bootstrap 5
- [x] Создать `vite.config.js` с code-splitting entry points
- [x] Создать `postcss.config.js` (autoprefixer, postcss-nested)
- [x] SCSS структура: variables, mixins, layout, components
- [x] `app.scss` — основной фронтенд бандл
- [x] `admin.scss` — админ-панель
- [x] JS модули: app.js, admin.js, forms.js, consultant.js, calculator.js, auth.js
- [x] JS админ-модули: navigation, tables, forms, media, dashboard, settings
- [x] Lazy-loading изображений (IntersectionObserver)
- [x] Scroll animations (AOS-like через data-aos)
- [x] Обновить layout.php (фронтенд) для Vite assets
- [x] Обновить admin layout.php для Vite assets
- [x] Добавить Vite manifest helpers в assets.php

---

## Progress Log

| Date | Task | Status |
|------|------|--------|
| 2026-11-18 | Environment Variables (.env) | ✅ Done |
| 2026-11-18 | CSRF Protection for Public Forms | ✅ Done |
| 2026-11-18 | SMTP Integration for Password Reset | ✅ Done |
| 2026-11-18 | Rate Limiting on Auth & Public POST | ✅ Done |
| 2026-11-18 | XSS Protection for Landing Page HTML | ✅ Done |
| 2026-11-18 | Uploads Protection (.htaccess / nginx) | ✅ Done |
| 2026-11-18 | Frontend Modernization (Vite + SCSS + ES Modules) | ✅ Done |