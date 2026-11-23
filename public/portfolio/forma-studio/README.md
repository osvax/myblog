# FORMA STUDIO — Лендинг архитектурной студии (перевёрстка с SCSS, BEM, префиксом va_)

Готовая адаптивная вёрстка одностраничного сайта студии текстильного дизайна и архитектуры полного цикла.
Перевёрстано с нуля с внедрением современных практик: **SCSS (7-1 architecture)**, **BEM**, **префикс уникальности `va_`**.

> **Оригинальный макет:** `Architecture.pdf` (десктоп 1920 px)  
> **Год создания:** 2025  
> **Стек:** HTML5, SCSS (Dart Sass), BEM, CSS Custom Properties, Vanilla JS (ES6+)

---

## 🚀 Быстрый старт

### Вариант 1: Просто открыть (без сборки)
```bash
# Дважды кликните по файлу в проводнике
forma-studio/index.html
```
Сайт работает как статическая страница — сервер не требуется.

### Вариант 2: Локальный сервер (для разработки)
```bash
cd forma-studio
npx http-server -p 8080
# или
npx live-server
```
Откроется на `http://localhost:8080`

### Вариант 3: Режим разработки с авто-сборкой SCSS
```bash
cd forma-studio
# Терминал 1: слежение за SCSS
npx sass --watch scss/style.scss:css/style.css --style=expanded --no-source-map

# Терминал 2: локальный сервер
npx http-server -p 8080
```

> **Требования:** Node.js (включает `npx`), Dart Sass (`npm i -g sass` или через `npx`)

---

## 📁 Структура проекта

```
forma-studio/
├── index.html              # Разметка всех 8 секций (классы va-*)
├── css/
│   └── style.css           # Скомпилированные стили (production-ready)
├── js/
│   └── app.js              # Вся клиентская логика (IIFE, Vanilla JS)
├── images/                 # Изображения (оптимизированы, из PDF-макета)
│   ├── hero.jpg
│   ├── project-1.jpg ... project-4.jpg
│   ├── steps-interior.jpg
│   ├── catalog.jpg
│   ├── gallery-1.jpg ... gallery-8.jpg
│   └── cta-house.jpg
├── scss/                   # ИСХОДНИКИ SCSS (для правок)
│   ├── style.scss          # Точка входа
│   ├── abstracts/          # Токены и миксины
│   │   ├── _index.scss
│   │   ├── _variables.scss # $va-c-*, $va-gutter, $va-bp-*, $va-font-family
│   │   └── _mixins.scss    # va-mq(), va-card-width(), BEM-хелперы (va-e, va-m, va-is)
│   ├── base/               # Фундамент
│   │   ├── _index.scss
│   │   ├── _root.scss      # :root { --va-* } — CSS-переменные для runtime
│   │   ├── _reset.scss     # Нормализация, .va-container, a11y-утилиты
│   │   ├── _typography.scss# .va-section, .va-section-title, .va-btn (--light/--dark)
│   │   └── _animations.scss# .va-reveal / .is-visible, prefers-reduced-motion
│   ├── components/         # UI-блоки (порядок в _index.scss важен)
│   │   ├── _index.scss
│   │   ├── _header.scss    # .va-header, .va-nav, .va-burger, .va-logo
│   │   ├── _hero.scss      # .va-hero, __bg, __inner, __title, __text
│   │   ├── _services.scss  # .va-services, __list, __row, __name, __desc
│   │   ├── _projects.scss  # .va-projects, .va-card, .va-arrow, карусель
│   │   ├── _steps.scss     # .va-steps, .va-step, __num, __title, __desc
│   │   ├── _catalog.scss   # .va-catalog, форма получения каталога
│   │   ├── _forms.scss     # .va-form, .va-field, .va-checkbox, __success
│   │   ├── _gallery.scss   # .va-gallery, __grid (4×2)
│   │   ├── _cta.scss       # .va-cta, __panel, __box, __form, __msg
│   │   └── _footer.scss    # .va-footer, __grid, __brand, __col, __contacts
│   └── utils/
│       ├── _index.scss
│       └── _responsive.scss# Все @media (1440/1200/1024/768/480) в одном месте
├── job/                    # Документация для портфолио
│   ├── description.md      # Описание работы (О проекте, Задача, Решение, Результат)
│   └── TZ-template.md      # Техническое задание (шаблон)
└── README.md               # Этот файл
```

---

## 🎯 Секции лендинга

| # | Секция | Блок | Особенности |
|---|--------|------|-------------|
| 1 | **Hero** | `.va-hero` | 100svh, фоновое фото + градиент, H1 clamp(38px→110px), CTA |
| 2 | **Услуги** | `.va-services` | Тёмная тема, 4 строки grid 1fr 1fr, разделители |
| 3 | **Проекты** | `.va-projects` | Карусель 4 карточки, scroll-snap, стрелки, drag, touch-swipe |
| 4 | **Этапы** | `.va-steps` | 8 шагов с нумерацией, вертикальная линия, фото справа |
| 5 | **Каталог** | `.va-catalog` | Фото + форма (email + чекбокс согласия), валидация |
| 6 | **Галерея** | `.va-gallery` | CSS Grid 4×2, aspect-ratio 1/1, lazy-loading |
| 7 | **CTA** | `.va-cta` | Фоновое фото, backdrop-filter, форма заявки |
| 8 | **Подвал** | `.va-footer` | 5 колонок → адаптивно до 1, контакты, соцсети, адрес |

---

## ⚙️ Интерактивные сценарии (js/app.js)

1. **Шапка** — фон при скролле > 40px (`is-scrolled`), backdrop-filter
2. **Бургер-меню** — < 1024px, фокус-ловушка, ESC, aria-атрибуты, `is-locked` на body
3. **Плавный скролл** — к якорям с учётом высоты шапки
4. **Карусель проектов** — нативный scroll-snap + JS управления:
   - Кнопки prev/next (disabled на краях)
   - Drag мышью (pointer events)
   - Нативный touch-свайп на мобильных
   - Авто-обновление состояния стрелок при resize/scroll
5. **Reveal-анимации** — IntersectionObserver, `.va-reveal` → `.is-visible`, каскадная задержка `index % 4 * 60ms`
6. **Формы** — клиентская валидация:
   - Email regex, чекбокс согласия
   - Инлайн-ошибки (`.is-invalid`), `role="status"` для успеха
   - Работают без бэкенда, готовы к `fetch()`
7. **Активный пункт меню** — подсветка при скролле (`is-active`)

---

## ♿ Доступность (a11y)

- ✅ Семантическая HTML5: `header`, `main`, `section`, `nav`, `footer`, `form`
- ✅ Skip-link (`.va-skip-link`) — первый фокус на странице
- ✅ ARIA: `aria-label`, `aria-controls`, `aria-expanded`, `aria-hidden`
- ✅ `:focus-visible` — видимый контур для клавиатуры
- ✅ `prefers-reduced-motion` — отключает все анимации/переходы
- ✅ `alt` на всех изображениях (описательные, не пустые)
- ✅ `visually-hidden` для подписей инпутов
- ✅ Контрастность ≥ 4.5:1 (WCAG 2.1 AA)
- ✅ Клавиатурная навигация по всем интерактивным элементам

---

## 📱 Адаптивность (брейкпоинты)

| Брейкпоинт | Ширина | Ключевые изменения |
|------------|--------|-------------------|
| Desktop XL | ≥1440px | Базовые стили, контейнер 1720px, gap 100px |
| Desktop L | ≤1440px | Отступы секций 100px, футер 4 колонки |
| Desktop | ≤1200px | Карточки 2 в ряд, шаги в 1 колонку, галерея gap 14px |
| Tablet | ≤1024px | **Бургер-меню**, каталог/галерея/ктa — мобильный layout |
| Mobile | ≤768px | Секции 64px, hero 620px, услуги/карточки/формы — 1 колонка |
| Mobile S | ≤480px | Галерея gap 10px, карточки 92%, карусель gap 16px |

> **Проверено:** 320 / 375 / 768 / 1024 / 1440 / 1920 px — **без горизонтального скролла**

---

## 🛠 Компиляция SCSS

```bash
# Однократная сборка
npx sass scss/style.scss css/style.css --style=expanded --no-source-map

# Режим наблюдения (автопересборка при сохранении)
npx sass --watch scss/style.scss:css/style.css --style=expanded --no-source-map
```

**Выходной CSS:** `css/style.css` — expanded, без source-map, готов к продакшену (можно дополнительно минифицировать).

---

## 🎨 Дизайн-токены (scss/abstracts/_variables.scss)

```scss
// Цвета
$va-c-dark: #141414;
$va-c-black: #0d0d0d;
$va-c-white: #ffffff;
$va-c-text: #1a1a1a;
$va-c-muted: #7c7c7c;
$va-c-line-light: #e3e3e3;
$va-c-line-dark: rgba(255, 255, 255, 0.22);
$va-c-error: #c0392b;

// Сетка
$va-container-width: 1720px;
$va-gutter: clamp(20px, 5.2vw, 100px);
$va-card-gap: 20px;

// Типографика
$va-font-family: "Inter", "Helvetica Neue", Helvetica, Arial, sans-serif;

// Анимации
$va-ease: cubic-bezier(0.4, 0, 0.2, 1);

// Брейкпоинты (max-width)
$va-bp-xl: 1440px;
$va-bp-lg: 1200px;
$va-bp-md: 1024px;
$va-bp-sm: 768px;
$va-bp-xs: 480px;
```

Все токены доступны в CSS как `--va-*` переменные (см. `base/_root.scss`).

---

## 📸 Скриншоты для портфолио

Создайте 4 качественных скриншота (рекомендуемое разрешение 1920×1080 или выше):

| # | Название | Что capturar |
|---|----------|--------------|
| 1 | `hero.png` | Главный экран (Hero) — H1, текст, кнопка, фон |
| 2 | `projects.png` | Секция «Наши проекты» — карусель 4 карточки, стрелки навигации |
| 3 | `steps-catalog.png` | Блок «Этапы работы» + «Каталог» (два рядом на десктопе) |
| 4 | `gallery-footer.png` | Галерея 4×2 + Подвал (5 колонок) |

> Сохраните в `job/screenshots/` (папку создать при необходимости)

---

## 📄 Документация для портфолио (папка `job/`)

- **`description.md`** — полное описание работы по структуре: О проекте / Задача / Решение / Результат + стек, год, сроки
- **`TZ-template.md`** — техническое задание-шаблон для подобных задач (входные данные, требования, критерии приёмки, план работ, риски)

---

## 🔧 Замена изображений

Положите свой файл с **тем же именем** в `images/` — стили подхватят автоматически (пропорции заданы через `aspect-ratio` в CSS):

| Файл | Соотношение | Использование |
|------|-------------|---------------|
| `hero.jpg` | ~16:9 | Фон Hero |
| `project-1..4.jpg` | 4:3 | Карточки карусели |
| `steps-interior.jpg` | ~3:4 | Блок этапов (справа) |
| `catalog.jpg` | 1.57:1 | Блок каталога (слева) |
| `gallery-1..8.jpg` | 1:1 | Галерея (grid 4×2) |
| `cta-house.jpg` | ~16:9 | Фон CTA-баннера |

---

## ⚡ Производительность

- **CSS:** ~25 KB (gzipped ~5 KB) — без фреймворков, только свои стили
- **JS:** ~8 KB (gzipped ~3 KB) — Vanilla, нет зависимостей
- **Images:** lazy-loading (`loading="lazy"`) для всего ниже фолда
- **CLS prevention:** `aspect-ratio` на всех медиа-контейнерах
- **Fonts:** `font-display: swap` (через Google Fonts URL), system-ui фолбэк
- **No render-blocking:** критический CSS инлайн не нужен (малый объём), шрифт предзагружен (`preconnect`)

---

## 🧪 Тестирование (чек-лист)

- [ ] Открытие `index.html` двойным кликом — работает
- [ ] `npx sass scss/style.scss css/style.css` — компиляция без ошибок
- [ ] 6 брейкпоинтов в DevTools — нет горизонтального скролла
- [ ] Бургер (<1024px) — открытие, закрытие, ESC, фокус, aria
- [ ] Карусель — стрелки, drag, swipe, snap, resize
- [ ] Reveal — плавное появление, каскад, reduced-motion
- [ ] Формы — валидация email, чекбокс, ошибки, успех
- [ ] Плавный скролл — все якоря, учётом шапки
- [ ] Активный пункт меню — подсвечивается при скролле
- [ ] Tab-навигация — focus-visible везде, skip-link работает
- [ ] Консоль — 0 ошибок, 0 404, 0 предупреждений
- [ ] Lighthouse — Performance ≥ 90, Accessibility ≥ 95, Best Practices ≥ 90, SEO ≥ 90

---

## 📝 Лицензия

Проект создан в образовательных/портфолио целях. Изображения из оригинального макета `Architecture.pdf`. Шрифт Inter — SIL Open Font License.

---

## 👨‍💻 Автор

**Senior Frontend-разработчик / UI-инженер**  
Перевёрстка с внедрением SCSS (7-1), BEM, префикса `va_`, полной адаптивности и доступности.  
2025 год, 5 рабочих дней.