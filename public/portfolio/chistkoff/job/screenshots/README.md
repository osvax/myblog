# Инструкция по созданию скриншотов для портфолио

## Требования
- **Разрешение**: 1200×675px (соотношение 16:9)
- **Формат**: PNG (бесшовный) или JPG (quality 90+)
- **Количество**: 4 скриншота
- **Браузер**: Chrome 120+, масштаб 100%
- **Окно viewport**: ровно 1200×675px

---

## 4 необходимых скриншота

### 1. `portfolio-01-hero-services.png`
**Страница**: `index.html` (или `individual.html`)
**Что показывать**: Hero-секция (промо) + блок «Что будем чистить?» (6 карточек услуг)
**Размер окна**: 1200px ширины (десктоп)
**Прокрутка**: верх страницы (scrollY = 0)

### 2. `portfolio-02-calculator.png`
**Страница**: `index.html` / `individual.html` / `manufactory.html`
**Что показывать**: Калькулятор стоимости в действии — открытые селекты, отмеченные чекбоксы, панель цены
**Размер окна**: 1200px ширины
**Прокрутка**: до секции калькулятора

### 3. `portfolio-03-works-before-after.png`
**Страница**: `index.html` / `manufactory.html`
**Что показывать**: Кейсы «До/После» — ползунок сравнения в среднем положении, видно оба изображения
**Размер окна**: 1200px ширины
**Прокрутка**: до секции «Результаты наших работ»

### 4. `portfolio-04-mobile-menu.png`
**Страница**: Любая (например `index.html`)
**Что показывать**: Мобильное меню в открытом состоянии + адаптивная верстка промо
**Размер окна**: 375px ширины (iPhone 12/13/14) × 675px высоты
**Device Toolbar**: iPhone 12 Pro (390×844) или Responsive 375×675

---

## Как сделать скриншоты (Chrome DevTools)

### Метод 1: Device Toolbar (рекомендуемый)
1. Откройте страницу в Chrome: `http://localhost:8000/index.html`
2. Откройте DevTools (F12)
3. Нажмите `Ctrl+Shift+M` (Device Toolbar) или иконку телефона/планшета
4. Установите **Responsive** → ширину **1200**, высоту **675** (для десктопных)
5. Для мобильного: выберите **iPhone 12 Pro** (390×844) или задайте **375×675**
6. Прокрутите к нужной секции
7. В меню DevTools (⋮) → **More tools** → **Screenshot** / **Capture full size screenshot**
8. Сохраните в папку `job/screenshots/` с правильным именем

### Метод 2: Headless Chrome (автоматизация)
```bash
# Установить puppeteer (если есть Node.js)
npm install puppeteer

# Скрипт для 4 скриншотов
node scripts/screenshots.js
```

Пример `scripts/screenshots.js`:
```javascript
const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');

const screenshots = [
  { name: 'portfolio-01-hero-services.png', url: 'http://localhost:8000/index.html', viewport: { width: 1200, height: 675 }, scroll: 0 },
  { name: 'portfolio-02-calculator.png', url: 'http://localhost:8000/index.html', viewport: { width: 1200, height: 675 }, scroll: 800 },
  { name: 'portfolio-03-works-before-after.png', url: 'http://localhost:8000/index.html', viewport: { width: 1200, height: 675 }, scroll: 2200 },
  { name: 'portfolio-04-mobile-menu.png', url: 'http://localhost:8000/index.html', viewport: { width: 375, height: 675 }, scroll: 0, mobile: true },
];

(async () => {
  const browser = await puppeteer.launch();
  const page = await browser.newPage();

  for (const shot of screenshots) {
    await page.setViewport(shot.viewport);
    await page.goto(shot.url, { waitUntil: 'networkidle2' });
    if (shot.mobile) {
      await page.click('.va_header__switcher'); // открыть мобильное меню
      await page.waitForTimeout(300);
    } else if (shot.scroll) {
      await page.evaluate(y => window.scrollTo(0, y), shot.scroll);
      await page.waitForTimeout(500);
    }
    await page.screenshot({
      path: path.join(__dirname, '../job/screenshots', shot.name),
      fullPage: false
    });
    console.log(`Saved: ${shot.name}`);
  }

  await browser.close();
})();
```

### Метод 3: Расширения браузера
- **GoFullPage** — полностраничные скриншоты
- **Awesome Screenshot** — область экрана
- **FireShot** — с аннотациями

---

## Чек-лист качества

- [ ] Разрешение ровно 1200×675px (проверьте в свойствах файла)
- [ ] Нет лишних элементов: DevTools, закладки браузера, адресная строка
- [ ] Контент реальный, не «Lorem ipsum» (где возможно)
- [ ] Анимации AOS не в процессе (подождите завершения)
- [ ] Слайдеры в правильном состоянии (не в переходе)
- [ ] Мобильное меню открыто (для 4-го скриншота)
- [ ] Файлы названы именно: `portfolio-01-...`, `portfolio-02-...`, `portfolio-03-...`, `portfolio-04-...`
- [ ] Файлы лежат в `job/screenshots/`

---

## Готовые файлы (после создания)

```
job/screenshots/
├── portfolio-01-hero-services.png
├── portfolio-02-calculator.png
├── portfolio-03-works-before-after.png
├── portfolio-04-mobile-menu.png
└── README.md (этот файл)
```

---

*После создания скриншотов обновите README.md в корне проекта, добавив превью:*
```markdown
## 📸 Скриншоты портфолио

| Главная + Услуги | Калькулятор |
|------------------|-------------|
| ![Hero](job/screenshots/portfolio-01-hero-services.png) | ![Calc](job/screenshots/portfolio-02-calculator.png) |

| Кейсы До/После | Мобильное меню |
|----------------|----------------|
| ![Works](job/screenshots/portfolio-03-works-before-after.png) | ![Mobile](job/screenshots/portfolio-04-mobile-menu.png) |
```