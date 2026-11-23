# TEXTILE — студия текстильного дизайна

Адаптивный одностраничный сайт студии текстильного дизайна интерьеров. Проект перевёрстан с внедрением SCSS, методологии БЭМ и уникального префикса `va_` для изоляции стилей.

## 🎯 Особенности

- **Полная адаптивность** — 8 брейкпоинтов: 320 / 375 / 480 / 768 / 1024 / 1280 / 1440 / 1920 px
- **Семантический HTML5** — `header`, `nav`, `main`, `section`, `article`, `form`, `footer`
- **БЭМ + префикс `va_`** — все проектные классы имеют уникальный namespace
- **Доступность (WCAG 2.1 AA)** — ARIA-атрибуты, фокус с клавиатуры, `prefers-reduced-motion`
- **Без зависимостей** — Vanilla JavaScript, без jQuery, фреймворков и сборщиков
- **SCSS-архитектура** — 21 файл в модульной структуре

## 📁 Структура проекта

```text
textile/
├── index.html              # Точка входа
├── styles/
│   └── style.css           # Скомпилированный CSS (готов к продакшену)
├── scripts/
│   └── main.js             # Вся интерактивность (Vanilla JS)
├── scss/                   # Исходники для разработки
│   ├── style.scss          # Точка входа SCSS
│   ├── abstracts/          # Переменные и миксины
│   │   ├── _variables.scss
│   │   ├── _mixins.scss
│   │   └── _index.scss
│   ├── base/               # Базовые стили
│   │   ├── _reset.scss
│   │   ├── _fonts.scss
│   │   ├── _typography.scss
│   │   └── _index.scss
│   ├── components/         # Блоки интерфейса
│   │   ├── _header.scss
│   │   ├── _hero.scss
│   │   ├── _about.scss
│   │   ├── _works.scss
│   │   ├── _profiles.scss
│   │   ├── _reviews.scss
│   │   ├── _calculator.scss
│   │   ├── _contacts.scss
│   │   ├── _footer.scss
│   │   ├── _legacy.scss    # Перенесённый CSS для совместимости
│   │   └── _index.scss
│   └── utils/              # Адаптивность и утилиты
│       ├── _responsive.scss
│       └── _index.scss
├── images/                 # Все ассеты (JPG, SVG, PNG)
└── fonts/                  # MuseoSansCyrl, PTRoubleSans (WOFF)
```

## 🚀 Быстрый старт

### Вариант 1: Открыть локально (без сборки)
```bash
# Просто откройте index.html в браузере
# Или запустите любой статический сервер:
python -m http.server 8765
# затем http://localhost:8765
```

### Вариант 2: Компиляция SCSS (для разработки)
```bash
# Установка Sass (если не установлен)
npm i -g sass

# Компиляция в CSS
sass scss/style.scss styles/style.css --no-source-map

# Или в режиме наблюдения
sass --watch scss/style.scss:styles/style.css
```

## 🧩 Интерактивность (main.js)

Все сценарии переписаны на нативный JavaScript:

| Функция | Описание |
|---------|----------|
| **Мобильное меню** | Открытие/закрытие, `Escape`, блокировка скролла `body`, `aria-expanded` |
| **Плавные якоря** | Учёт высоты шапки, закрытие меню при переходе |
| **Слайдеры** | Hero (фото + фон), Отзывы — автопереключение, без библиотек |
| **Кастомные select** | Клавиатурное управление, поиск по клику вне |
| **Профили тканей** | Переключение карточек и изображений |
| **Галерея работ** | Показать больше / скрыть |
| **Lightbox** | Открытие изображений по клику (замена Fancybox) |
| **Маска телефона** | `+7 (___) ___-__-__` на вводе |
| **Шапка при скролле** | Добавление фона/тени после 10 px |

## ♿ Доступность

- Видимый `:focus-visible` на всех интерактивных элементах
- `aria-label`, `aria-controls`, `aria-expanded` для меню
- Семантическая разметка (`<main>`, `<nav>`, `<section>`, `<button>`)
- Поддержка `@media (prefers-reduced-motion: reduce)`
- `alt` для всех контентных изображений

## 🎨 Дизайн-система (SCSS переменные)

```scss
$va-color-dark:     #252a2b;   // основной тёмный
$va-color-accent:   #b86f54;   // терракотовый акцент
$va-container:      1200px;    // макс. ширина контейнера
$va-breakpoints: (
  sm: 480px,
  md: 768px,
  lg: 1024px,
  xl: 1440px
);
```

## 📦 Стек технологий

| Категория | Инструменты |
|-----------|-------------|
| Разметка | HTML5 |
| Стили | SCSS / Sass (Dart Sass), CSS3 (Custom Properties, Grid, Flex, `clamp()`) |
| Скрипты | JavaScript ES6+ (IIFE, `const`/`let`, arrow functions) |
| Методология | БЭМ (`block__element--modifier`) с префиксом `va_` |
| Шрифты | MuseoSansCyrl (300, 500, 700), PTRoubleSans |
| Иконки | SVG (inline / background) |
| Изображения | JPG, SVG, PNG (Retina-ready, `@2x` где нужно) |

## 🧪 Проверка качества

```bash
# Синтаксис JS
node --check scripts/main.js

# Компиляция SCSS
sass scss/style.scss styles/style.css --no-source-map

# Ручные проверки
# ✓ Нет горизонтального скролла на всех брейкпоинтах
# ✓ Консоль браузера — 0 ошибок
# ✓ Меню, слайдеры, формы, lightbox работают
# ✓ Клавиатурная навигация (Tab, Enter, Escape)
# ✓ Lighthouse: Performance / Accessibility / Best Practices / SEO
```

## 📄 Портфолио

Готовые материалы для портфолио находятся в папке `job/`:

```
job/
├── prompt.md            # Промпт на перевёрстку
├── description.md       # Описание работы (О ПРОЕКТЕ / ЗАДАЧА / РЕШЕНИЕ / РЕЗУЛЬТАТ)
├── TZ-template.md       # Техническое задание (Markdown)
├── TZ-template.pdf      # Техническое задание (PDF)
└── screenshots/
    ├── 01-hero-studio.png
    ├── 02-gallery-works.png
    ├── 03-textile-profiles.png
    └── 04-about-reviews.png
```

**Стек в портфолио:** HTML5, SCSS/Sass, CSS3, JavaScript ES6+, БЭМ, адаптивная вёрстка, SVG/JPG  
**Год создания:** 2026  
**Срок выполнения:** 4 рабочих дня

## 📝 Лицензия

Проект создан в образовательных/портфолио целях. Шрифты и изображения — согласно их лицензиям.