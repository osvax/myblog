-- MariaDB dump 10.19  Distrib 10.9.8-MariaDB, for Win64 (AMD64)
--
-- Host: MariaDB-10.9    Database: val_myblog
-- ------------------------------------------------------
-- Server version	10.9.8-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `va_articles`
--

DROP TABLE IF EXISTS `va_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_articles` (
  `id` char(32) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `title` varchar(240) NOT NULL,
  `description` varchar(500) NOT NULL DEFAULT '',
  `body_html` mediumtext DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `author` varchar(160) NOT NULL DEFAULT '',
  `published_at` date DEFAULT NULL,
  `icon` varchar(80) NOT NULL DEFAULT 'bi-file-text',
  `cover_path` varchar(500) NOT NULL DEFAULT '',
  `tags_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags_json`)),
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_articles_slug` (`slug`),
  KEY `idx_articles_publication` (`is_published`,`published_at`),
  KEY `idx_articles_category` (`category_id`),
  CONSTRAINT `fk_articles_category` FOREIGN KEY (`category_id`) REFERENCES `va_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_articles`
--

LOCK TABLES `va_articles` WRITE;
/*!40000 ALTER TABLE `va_articles` DISABLE KEYS */;
INSERT INTO `va_articles` VALUES
('0236953aeed909ce8ac2b2c480a7507e','dark-mode-design','Тёмная тема: дизайн и реализация','Инвертируем интерфейс грамотно: не чистый чёрный, приглушённые акценты и prefers-color-scheme.','<h2>Не чистый чёрный</h2><p>Фон #121212–#1e1e1e приятнее #000: меньше «выжигает» глаза и скрывает засветы OLED-экранов.</p><h2>Реализация через переменные</h2><p>Тема переключается заменой набора переменных, а не переписыванием стилей.</p><pre><code class=\"language-css\">@media (prefers-color-scheme: dark) {\n  :root { --bg: #1e1e1e; --text: #f5f5f5; }\n}</code></pre>',2,'Администратор','2026-09-27','bi-moon-stars','','[\"дизайн\",\"css\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('03c24a32077959eb24538ba9bf70d07f','fetch-api-guide','Fetch API и работа с данными','Запросы без библиотек: заголовки, JSON, ошибки сети и отмена запросов.','<h2>Базовый запрос</h2><p>fetch не выбрасывает исключение на 404 — статус проверяется вручную.</p><pre><code class=\"language-javascript\">const response = await fetch(&#039;/api/articles&#039;);\nif (!response.ok) throw new Error(&#039;Не удалось загрузить&#039;);\nconst data = await response.json();</code></pre><h2>Отмена и повтор</h2><p>AbortController отменяет устаревший запрос, а повтор с задержкой спасает от сетевых «морганий».</p><ul><li>Всегда оборачивайте fetch в try/catch.</li><li>Показывайте состояние загрузки пользователю.</li><li>Кэшируйте неизменяемые ответы.</li></ul>',3,'Администратор','2026-10-08','bi-cloud-arrow-down','','[\"javascript\",\"api\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('16ee418e55a5fe40d6b363acd8db6e61','es6-features-today','Современный JavaScript: что дают ES6+','Деструктуризация, модули, стрелочные функции и шаблонные строки на живых примерах.','<h2>Деструктуризация и спред</h2><p>Код становится короче и очевиднее: значения из объектов и массивов извлекаются одной строкой.</p><pre><code class=\"language-javascript\">const { name, role = \'user\' } = user;\nconst merged = { ...defaults, ...options };</code></pre><h2>Модули и области видимости</h2><p>let и const заменили коварный var, модули — самописные неймспейсы.</p><ul><li>Стрелочные функции не создают свой this.</li><li>Шаблонные строки читаются лучше конкатенации.</li><li>export/import делает зависимости явными.</li></ul>',3,'Администратор','2026-11-12','bi-file-text','uploads/blog/16ee418e55a5fe40d6b363acd8db6e61/48764ec2223fe0b76bf6298372bfcfcb.jpg','[\"javascript,es6\"]',1,'2026-11-17 16:47:40','2026-11-18 22:45:43'),
('17696be265042e4a1ebbfd0053562dcf','react-components-basics','Компоненты и props в React','Функциональные компоненты, свойства и композиция: фундамент любого React-приложения.','<h2>Функция, возвращающая разметку</h2><p>Компонент — обычная функция с заглавной буквы, возвращающая JSX.</p><pre><code class=\"language-jsx\">function ArticleCard({ title, excerpt }) {\n  return &lt;article&gt;&lt;h3&gt;{title}&lt;/h3&gt;&lt;p&gt;{excerpt}&lt;/p&gt;&lt;/article&gt;;\n}</code></pre><h2>Props только для чтения</h2><p>Данные текут сверху вниз; дочерний компонент сообщает о событиях через колбэки — так проще рассуждать о состоянии.</p><ul><li>Один компонент — одна ответственность.</li><li>Дефолтные значения props через параметры по умолчанию.</li><li>children для вложенной разметки.</li></ul>',13,'Администратор','2026-11-07','bi-atom','','[\"react\",\"компоненты\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('1dee16c2dd788a6e0afc2305f65e46b0','css-variables-guide','CSS-переменные в реальных проектах','Как кастомные свойства упрощают поддержку тем, отступов и цветов на сайтах любого размера.','<h2>Зачем нужны переменные</h2><p>Кастомные свойства хранят значения в одном месте: сменили акцентный цвет в :root — обновился весь сайт, включая тени и градиенты.</p><h2>Область видимости и наследование</h2><p>Переменные наследуются: на уровне секции можно переопределить цвет, не трогая остальную страницу. Это основа тёмной темы.</p><pre><code class=\"language-css\">:root { --accent: #ff8c1a; }\n.section-dark { --accent: #4fc3f7; }\n.btn { background: var(--accent); }</code></pre><h2>Практические советы</h2><p>Держите палитру, отступы и радиусы в переменных.</p><ul><li>Именуйте по смыслу (--accent), а не по значению (--orange).</li><li>Для fallback используйте var(--accent, #ff8c1a).</li><li>Вместе с calc() переменные заменяют препроцессор.</li></ul>',1,'Администратор','2026-11-05','bi-brush','','[\"css\",\"переменные\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('1ea073436fbe25bd17a9f24e11242582','dom-events-deep-dive','События DOM: делегирование и обработка','Почему делегирование экономит память и как не потерять контекст this в обработчиках.','<h2>Один слушатель вместо сотни</h2><p>Событие всплывает вверх по дереву — слушайте на родителе и проверяйте target.</p><pre><code class=\"language-javascript\">list.addEventListener(&#039;click&#039;, function (event) {\n  const button = event.target.closest(&#039;[data-action]&#039;);\n  if (!button) return;\n  run(button.dataset.action);\n});</code></pre><h2>Частые ошибки</h2><p>Три причины, из-за которых обработчики «не срабатывают»:</p><ul><li>Элемент перерисован — слушатель потерян (лечится делегированием).</li><li>preventDefault не вызван для ссылок и форм.</li><li>Скрипт загружен до разметки без defer.</li></ul>',3,'Администратор','2026-10-28','bi-mouse','','[\"javascript\",\"dom\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('3969e14fc726cf8e2c86238490315f62','post-bootstrap','Верстаем на Bootstrap 5','Разбираем сетку, компоненты и утилиты Bootstrap 5 на практике — от навигации до карточек.','<h2 class=\"fw-bold mb-3\">Зачем нужен Bootstrap</h2>\n                        <p class=\"fw-bold\">\n                            Bootstrap — самый популярный CSS-фреймворк в мире. Он даёт готовую сетку,\n                            десятки компонентов и утилитарные классы, которые позволяют собрать\n                            аккуратный адаптивный сайт за вечер вместо недели. В пятой версии\n                            команда отказалась от jQuery, добавила RTL-поддержку и обновила\n                            дизайн-систему.\n                        </p>\n                        <p class=\"fw-bold\">\n                            В этой статье разберём ключевые инструменты Bootstrap 5 на практике:\n                            сетку, компоненты и утилиты — от навигации до карточек.\n                        </p>\n\n                        <h2 class=\"fw-bold mt-4 mb-3\">Сетка: основа основ</h2>\n                        <p class=\"fw-bold\">\n                            Сетка Bootstrap построена на flexbox и состоит из контейнера, строк\n                            и колонок. Достаточно запомнить три класса:\n                        </p>\n                        <ul class=\"fw-bold\">\n                            <li><code>.container</code> — центрирует контент и задаёт максимальную ширину;</li>\n                            <li><code>.row</code> — строка, внутри которой лежат колонки;</li>\n                            <li><code>.col-*</code> — колонка, число задаёт долю из 12 частей.</li>\n                        </ul>\n                        <p class=\"fw-bold\">\n                            Например, три равные колонки на средних экранах: каждая получает\n                            класс <code>.col-md-4</code>. На мобильных колонки сложатся в стек —\n                            адаптивность получается «бесплатно».\n                        </p>\n\n                        <img src=\"https://placehold.co/800x300/353535/ff8c1a?text=Сетка+Bootstrap\" class=\"img-fluid rounded my-4\" alt=\"Пример сетки Bootstrap\">\n                        <h2 class=\"fw-bold mt-4 mb-3\">Компоненты: навигация и карточки</h2>\n                        <p class=\"fw-bold\">\n                            Навбар — самый часто используемый компонент. Он сворачивается в\n                            «бургер» на мобильных устройствах и раскладывается в горизонтальное\n                            меню на больших экранах. Карточки (<code>.card</code>) — универсальный\n                            контейнер для любых блоков: статей, товаров, отзывов.\n                        </p>\n                        <p class=\"fw-bold\">\n                            Полезно держать под рукой официальную документацию: почти любая\n                            типовая задача решается копированием готового примера и лёгкой\n                            доработкой.\n                        </p>\n\n                        <h2 class=\"fw-bold mt-4 mb-3\">Утилиты: магия классов</h2>\n                        <p class=\"fw-bold\">\n                            Утилиты — короткие классы вида <code>.mt-3</code>, <code>.fw-bold</code>,\n                            <code>.text-center</code>. Они заменяют большинство самописных правил\n                            и позволяют верстать почти без собственного CSS. Главное — не\n                            злоупотреблять: если классы начинают дублироваться, лучше вынести\n                            повторяющийся блок в свой компонент.\n                        </p>\n\n                        <h2 class=\"fw-bold mt-4 mb-3\">Вывод</h2>\n                        <p class=\"fw-bold\">\n                            Bootstrap 5 — отличный выбор для быстрой вёрстки типовых сайтов.\n                            Сетка решает адаптивность, компоненты — интерфейс, утилиты —\n                            мелкие правки. Начните с копирования примеров из документации,\n                            а со временем переходите к переопределению темы через Sass.\n                        </p>\n\n                        <!-- Метки -->\n                        <div class=\"mt-4\">\n                            <span class=\"badge bg-orange-light text-orange fw-bold me-1 p-2\">#bootstrap</span>\n                            <span class=\"badge bg-orange-light text-orange fw-bold me-1 p-2\">#вёрстка</span>\n                            <span class=\"badge bg-orange-light text-orange fw-bold p-2\">#css</span>\n                        </div>\n\n                        ',1,'Администратор','2026-11-16','bi-file-text','uploads/blog/3969e14fc726cf8e2c86238490315f62/1612229b092fca0c5738885ae4f3fd07.png','[]',1,'2026-11-15 20:48:31','2026-11-23 03:08:53'),
('501ca2af8afd9e9820fe345132b917de','lazy-loading-guide','Ленивая загрузка изображений и модулей','Откладываем всё, что не нужно прямо сейчас: картинки, чанки, виджеты.','<h2>Изображения</h2><p>Атрибут loading=\"lazy\" — нативная ленивая загрузка без единой строки JS. Ниже первого экрана — всегда.</p><h2>Модули и код-сплиттинг</h2><p>Динамический import() загружает код по требованию.</p><pre><code class=\"language-javascript\">button.addEventListener(&#039;click&#039;, async () =&gt; {\n  const module = await import(&#039;./heavy-chart.js&#039;);\n  module.render();\n});</code></pre>',6,'Администратор','2026-11-02','bi-hourglass-split','','[\"оптимизация\",\"производительность\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('61e961ef8df85b36b85ff63ff25f9309','react-hooks-basics','Хуки useState и useEffect','Состояние и побочные эффекты в функциональных компонентах без классов.','<h2>useState</h2><p>Хук хранит значение между рендерами и перерисовывает компонент при изменении.</p><pre><code class=\"language-jsx\">const [count, setCount] = useState(0);\n&lt;button onClick={() =&gt; setCount(count + 1)}&gt;Нажали {count} раз&lt;/button&gt;</code></pre><h2>useEffect</h2><p>Массив зависимостей определяет, когда эффект повторится: пустой — один раз при монтировании.</p><ul><li>Очищайте подписки в return эффекта.</li><li>Не подавляйте предупреждения о зависимостях.</li><li>Запросы — в эффекте с флагом отмены.</li></ul>',13,'Администратор','2026-10-25','bi-hook','','[\"react\",\"хуки\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('6a5b81139b8d82636b496caa6f12a196','post-adaptive','Адаптивная вёрстка','Медиазапросы, гибкие сетки и мобильные устройства: сайт должен выглядеть отлично везде.','<img src=\"https://placehold.co/800x400/ff8c1a/ffffff?text=Адаптивная+вёрстка\" class=\"img-fluid rounded mb-4\" alt=\"Обложка статьи: Адаптивная вёрстка\">\n                        <h2 class=\"fw-bold mb-3\">Почему это важно</h2>\n                        <p class=\"fw-bold\">\n                            Больше половины трафика в интернете — со смартфонов. Google индексирует\n                            сайты по мобильной версии, а пользователь закрывает страницу, если\n                            текст «пляшет» и кнопки слишком мелкие. Адаптивность — не опция,\n                            а базовое требование к любому современному сайту.\n                        </p>\n\n                        <h2 class=\"fw-bold mt-4 mb-3\">Viewport — точка отсчёта</h2>\n                        <p class=\"fw-bold\">\n                            Без метатега viewport браузер смартфона открывает страницу как\n                            «уменьшенный десктоп». Одна строчка решает проблему:\n                            <code><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"></code>.\n                        </p>\n\n                        <h2 class=\"fw-bold mt-4 mb-3\">Медиазапросы</h2>\n                        <p class=\"fw-bold\">\n                            Медиазапросы позволяют применять стили при определённых условиях —\n                            например, при ширине экрана от 768px. Bootstrap уже содержит\n                            готовые брейкпоинты: sm (576px), md (768px), lg (992px), xl (1200px).\n                            Используйте их вместо собственных — так проект останется консистентным.\n                        </p>\n\n                        <img src=\"https://placehold.co/800x300/353535/ff8c1a?text=Брейкпоинты\" class=\"img-fluid rounded my-4\" alt=\"Иллюстрация: брейкпоинты и медиазапросы\">\n                        <h2 class=\"fw-bold mt-4 mb-3\">Гибкие сетки и изображения</h2>\n                        <ul class=\"fw-bold\">\n                            <li>Задавайте ширину в процентах, а максимальную — в пикселях;</li>\n                            <li>Изображениям — <code>max-width: 100%</code>, чтобы не ломали вёрстку;</li>\n                            <li>Используйте <code>srcset</code> для отдачи разных размеров изображений;</li>\n                            <li>Проверяйте сайт на реальных устройствах, а не только в DevTools.</li>\n                        </ul>\n\n                        <h2 class=\"fw-bold mt-4 mb-3\">Вывод</h2>\n                        <p class=\"fw-bold\">\n                            Начинайте с мобильной версии (mobile-first): сложнее уместить контент\n                            на маленьком экране, чем добавить его на большом. Адаптив, сделанный\n                            сразу, всегда дешевле и качественнее переделки.\n                        </p>\n\n                        <!-- Метки -->\n                        <div class=\"mt-4\">\n                            <span class=\"badge bg-orange-light text-orange fw-bold me-1 p-2\">#адаптивность</span>\n                            <span class=\"badge bg-orange-light text-orange fw-bold me-1 p-2\">#css</span>\n                            <span class=\"badge bg-orange-light text-orange fw-bold p-2\">#mobile-first</span>\n                        </div>\n\n                        ',1,'Администратор','2026-11-16','bi-file-text','uploads/blog/6a5b81139b8d82636b496caa6f12a196/bea6399181637d05af9378df79088375.png','[]',1,'2026-11-15 20:48:31','2026-11-20 03:39:00'),
('6b4708832393c21562e65b0bd1e23cf2','color-theory-basics','Теория цвета для веб-разработчика','Как строить гармоничные палитры: колесо, температура, контраст и доступность.','<h2>Три кита палитры</h2><p>Базовый, акцентный и нейтральный цвета. Акцент — один, максимум два; всё остальное — оттенки серых с лёгким подмесом базового тона.</p><h2>Контраст и доступность</h2><p>Текст читается, если контраст к фону не ниже 4.5:1 для основного и 3:1 для крупного. Проверяйте палитру в контраст-чекерах.</p><ul><li>Оранжевый акцент отлично работает с почти чёрным #353535.</li><li>Не кладите текст на насыщенные градиенты.</li><li>Проверяйте состояние hover и disabled заранее.</li></ul>',2,'Администратор','2026-10-30','bi-palette','','[\"дизайн\",\"цвет\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('7d73de5d39b7b18266acc89f6e438354','critical-css-cls','Критический CSS и борьба со сдвигами','CLS падает, если резервировать место под контент: размеры, плейсхолдеры, шрифты.','<h2>Что такое CLS</h2><p>Cumulative Layout Shift — метрика «прыгания» страницы при загрузке. Лечится зарезервированными размерами и стабильной шапкой.</p><h2>Чек-лист</h2><p>Быстрые победы:</p><ul><li>width/height или aspect-ratio у всех медиа.</li><li>font-display: swap и локальный предзагрузчик шрифтов.</li><li>Скелетоны вместо пустых блоков.</li></ul>',6,'Администратор','2026-10-14','bi-arrows-move','','[\"оптимизация\",\"css\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('83a32ae4c91480769fc39f20f87db98f','devtools-tips','Chrome DevTools: десять приёмов','Быстрый подбор стилей, инспекция событий, замеры производительности и логирование запросов.','<h2>Разбор интерфейса</h2><p>Панель Elements правит стили на лету, Network показывает вес и время запросов, Lighthouse — сводный аудит.</p><h2>Приёмы на каждый день</h2><p>Самые полезные сокращения:</p><ul><li>Ctrl+Shift+C — инспектор элемента.</li><li>Console.log с %c — стилизованный вывод.</li><li>Условные точки останова в Sources.</li><li>Скриншот узла через Ctrl+Shift+P → «Capture node screenshot».</li></ul>',5,'Администратор','2026-10-21','bi-bug','','[\"инструменты\",\"devtools\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('8bd9de084847041e7efcd5776c5358db','lighthouse-audit','Lighthouse: аудит производительности','Как читать отчёт Lighthouse и что исправлять в первую очередь.','<h2>Приоритет метрик</h2><p>Сначала LCP (скорость главного контента), затем CLS и INP. Каждая метрика указывает на конкретный класс проблем.</p><h2>Как интерпретировать</h2><p>Аудит — не приговор, а подсказки: сжатие картинок, кэш, устранение блокирующих скриптов дают основной прирост.</p>',6,'Администратор','2026-09-22','bi-speedometer2','','[\"оптимизация\",\"инструменты\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('95b95afa8ec7aecf5bdcf546487a3d7f','php-forms-security','Формы и безопасность в PHP','Валидация входа, экранирование вывода и защита от CSRF на простом примере.','<h2>Никогда не доверяйте вводу</h2><p>strip_tags и trim для строк, filter_var для email, ctype_digit для чисел — базовый гигиенический минимум.</p><h2>Экранирование вывода</h2><p>htmlspecialchars с ENT_QUOTES при выводе в HTML закрывает XSS.</p><pre><code class=\"language-php\">echo htmlspecialchars($comment[&#039;body&#039;], ENT_QUOTES, &#039;UTF-8&#039;);</code></pre><h2>CSRF</h2><p>Токен в сессии + скрытое поле формы + hash_equals при проверке — три строки кода против подделки запросов.</p>',14,'Администратор','2026-10-16','bi-shield-check','','[\"php\",\"безопасность\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('979189a42809804be127ad55d310bc73','vscode-extensions-frontend','VS Code: расширения для фронтендера','Подборка расширений, которые реально экономят время: от Emmet до линтеров.','<h2>Обязательный минимум</h2><p>Emmet уже встроен; остальное добавьте сами.</p><ul><li>Prettier — единое форматирование команды.</li><li>ESLint — ошибки до запуска браузера.</li><li>Live Server — мгновенный предпросмотр.</li><li>Error Lens — ошибки прямо в строке.</li></ul><h2>Настройка под себя</h2><p>settings.json с editor.formatOnSave и defaultFormatter снимает 90% споров о стиле кода.</p>',5,'Администратор','2026-11-09','bi-code-square','','[\"инструменты\",\"vscode\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('b0e759c1d124f1454abde4911533ea3a','semantic-html-seo','Семантическая вёрстка и SEO','Почему section и article лучше безликих div: доступность, SEO и читаемость кода.','<h2>Смысл вместо коробок</h2><p>Поисковики и скринридеры понимают структуру по тегам: header, nav, main, article, aside, footer. Страница на семантических тегах ранжируется предсказуемее.</p><h2>Что проверить прямо сейчас</h2><p>Короткий чек-лист самопроверки:</p><ul><li>Один h1 на страницу, заголовки без пропусков уровней.</li><li>main оборачивает уникальный контент.</li><li>Навигация — в nav, второстепенное — в aside.</li><li>Кнопки — button, ссылки — a: не путать.</li></ul>',1,'Администратор','2026-10-24','bi-file-earmark-code','','[\"html\",\"seo\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('b896eea351789bcff53f760bedcc251c','php-arrays-in-depth','Массивы в PHP: функции, которые нужно знать','map, filter, reduce и сортировки: обрабатываем данные без циклов-простыней.','<h2>Обработка коллекций</h2><p>Три функции закрывают большинство задач.</p><pre><code class=\"language-php\">$titles = array_map(fn($a) =&gt; $a[&#039;title&#039;], $articles);\n$published = array_filter($articles, fn($a) =&gt; $a[&#039;active&#039;]);\n$total = array_sum(array_column($orders, &#039;budget&#039;));</code></pre><h2>Сортировка</h2><p>usort с стрелочной функцией сортирует по любому ключу; для многомерных данных — spaceship-оператор <=>.</p><ul><li>array_column — быстрая выборка колонки.</li><li>isset($arr[key]) быстрее in_array для проверок наличия.</li><li>implode/glue для простых списков.</li></ul>',14,'Администратор','2026-11-04','bi-braces-asterisk','','[\"php\",\"массивы\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('bcbdaf581238c3bbb36235cf3fb5e939','post-git','Git за один вечер','Команды, ветки и коммиты — минимальный набор знаний для уверенной работы с Git.','<h2>Деструктуризация и спред</h2><p>Код становится короче и очевиднее: значения из объектов и массивов извлекаются одной строкой.</p><pre><code class=\"language-javascript\">const { name, role = \'user\' } = user;\nconst merged = { ...defaults, ...options };</code></pre><h2>Модули и области видимости</h2><p>let и const заменили коварный var, модули — самописные неймспейсы.</p><ul><li>Стрелочные функции не создают свой this.</li><li>Шаблонные строки читаются лучше конкатенации.</li><li>export/import делает зависимости явными.</li></ul>',5,'Администратор','2026-11-16','bi-file-text','uploads/blog/bcbdaf581238c3bbb36235cf3fb5e939/c7f3b39d38d614efcc2a38f9f66cde58.png','[]',1,'2026-11-15 20:48:31','2026-11-18 22:43:03'),
('be3a468338eee4fd673c70d06e4110ff','responsive-images','Адаптивные изображения: srcset и sizes','Отдаём мобильным устройствам лёгкие картинки, а десктопам — детализированные, без потери качества.','<h2>Атрибут srcset</h2><p>Браузер сам выбирает файл подходящей ширины по плотности экрана и размеру вьюпорта.</p><pre><code class=\"language-html\">&lt;img src=&quot;photo-800.jpg&quot;\\n     srcset=&quot;photo-400.jpg 400w, photo-800.jpg 800w, photo-1600.jpg 1600w&quot;\\n     sizes=&quot;(max-width: 600px) 100vw, 50vw&quot;\\n     alt=&quot;Пример адаптивного изображения&quot;&gt;</code></pre><h2>Не забывайте про формат</h2><p>WebP весит на 25–35% меньше JPEG при том же качестве. Экономия заметнее любых настроек кэша.</p><ul><li>picture — когда нужен разный кроп под мобильные.</li><li>loading=\"lazy\" — для всего ниже первого экрана.</li><li>Всегда задавайте width и height, чтобы избежать сдвигов макета.</li></ul>',1,'Администратор','2026-10-11','bi-image','','[\"html\",\"производительность\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('d24d4722f7e84f062ccf202b45d7584f','flexbox-vs-grid','Flexbox против Grid: когда что использовать','Разбираем, в каких случаях уместнее Flexbox, а в каких — CSS Grid, и как совмещать обе системы.','<h2>Одномерный против двумерного</h2><p>Flexbox раскладывает элементы по одной оси, Grid — по двум одновременно. Для навигации и кнопочных групп берите Flexbox, для сеток карточек и макетов страниц — Grid.</p><h2>Типичные сценарии</h2><p>Ниже — правила, которые помогают не ошибиться в выборе:</p><ul><li>Шапка, футер, тулбар — Flexbox с justify-content и gap.</li><li>Галереи и каталоги — Grid с repeat(auto-fill, minmax()).</li><li>Вложенность допустима: Grid-секция может содержать Flex-строки.</li><li>Не используйте Grid для выравнивания одной строки — это избыточно.</li></ul><h2>Совмещение на практике</h2><p>Внутри grid-карточки контент удобно выравнивать флексом: заголовок сверху, кнопка снизу через margin-top: auto.</p><pre><code class=\"language-css\">.grid {\n  display: grid;\n  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));\n  gap: 1rem;\n}\n.card-actions { margin-top: auto; }</code></pre>',1,'Администратор','2026-11-14','bi-file-text','uploads/blog/d24d4722f7e84f062ccf202b45d7584f/53253709fe20cfdb93cb4f538e437ec2.jpg','[\"css,flexbox,grid\"]',1,'2026-11-17 16:47:40','2026-11-18 22:44:29'),
('d3e00ccc237973ad0d58f02cf2eeac89','typography-in-web','Типографика в вебе: шрифты и ритм','Вертикальный ритм, модульные шкалы и подбор шрифтовых пар без боли.','<h2>Шрифтовая пара</h2><p>Достаточно двух гарнитур: выразительная для заголовков и нейтральная для текста. Montserrat + системный sans-serif — рабочая связка для кириллицы.</p><h2>Вертикальный ритм</h2><p>Межстрочный интервал 1.5–1.6 для абзацев, отступы кратны базовой единице (8px) — страница «дышит» без хаоса.</p><ul><li>Длина строки 60–80 символов: длиннее — глаз теряет строку.</li><li>Заголовок соседствует с текстом ближе, чем с другим заголовком.</li><li>letter-spacing для заголовков капсом: 0.02–0.05em.</li></ul>',2,'Администратор','2026-10-18','bi-fonts','','[\"дизайн\",\"шрифты\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40'),
('ea6ae75df607533d8049378997827694','post-js','JavaScript для начинающих','Переменные, функции и события — с чего начать путь во фронтенд-разработку и как не сдаться.','<h2>Деструктуризация и спред</h2><p>Код становится короче и очевиднее: значения из объектов и массивов извлекаются одной строкой.</p><pre><code class=\"language-javascript\">const { name, role = \'user\' } = user;\nconst merged = { ...defaults, ...options };</code></pre><h2>Модули и области видимости</h2><p>let и const заменили коварный var, модули — самописные неймспейсы.</p><ul><li>Стрелочные функции не создают свой this.</li><li>Шаблонные строки читаются лучше конкатенации.</li><li>export/import делает зависимости явными.</li></ul>',3,'Администратор','2026-11-16','bi-file-text','uploads/blog/ea6ae75df607533d8049378997827694/b2eee2c3518308ace515fa3db85f3cc6.jpg','[]',1,'2026-11-15 20:48:31','2026-11-18 22:43:46'),
('f1d675bee9a35a4fd1b36306d65bd3b2','npm-essentials','npm: package.json без магии','Скрипты, версии и lock-файл: как перестать бояться node_modules.','<h2>Зависимости и версии</h2><p>Курсор ^ обновляет минорные версии, ~ — только патчи. package-lock.json фиксирует дерево — коммитьте его обязательно.</p><h2>Скрипты</h2><p>npm run собирает любую цепочку команд.</p><pre><code class=\"language-json\">{\n  &quot;scripts&quot;: {\n    &quot;dev&quot;: &quot;vite&quot;,\n    &quot;build&quot;: &quot;vite build&quot;,\n    &quot;lint&quot;: &quot;eslint src&quot;\n  }\n}</code></pre>',5,'Администратор','2026-09-30','bi-box-seam','','[\"инструменты\",\"npm\"]',1,'2026-11-17 16:47:40','2026-11-17 16:47:40');
/*!40000 ALTER TABLE `va_articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_categories`
--

DROP TABLE IF EXISTS `va_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `description` varchar(500) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_slug` (`slug`),
  UNIQUE KEY `uq_categories_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_categories`
--

LOCK TABLES `va_categories` WRITE;
/*!40000 ALTER TABLE `va_categories` DISABLE KEYS */;
INSERT INTO `va_categories` VALUES
(1,'Вёрстка','vyorstka','','2026-11-15 20:48:31','2026-11-17 20:36:30'),
(2,'Дизайн','dizayn','','2026-11-15 20:48:31','2026-11-17 20:36:36'),
(3,'JavaScript','javascript','','2026-11-15 20:48:31','2026-11-17 16:38:03'),
(5,'Инструменты','instrumenty','','2026-11-15 20:48:31','2026-11-17 20:36:36'),
(6,'Оптимизация','optimizatsiya','','2026-11-15 20:48:31','2026-11-17 20:36:36'),
(13,'React','react','Статьи о React','2026-11-17 16:47:40','2026-11-17 16:47:40'),
(14,'PHP','php','Статьи о PHP','2026-11-17 16:47:40','2026-11-17 16:47:40');
/*!40000 ALTER TABLE `va_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_category_navigation`
--

DROP TABLE IF EXISTS `va_category_navigation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_category_navigation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `title` varchar(160) NOT NULL,
  `url` varchar(255) NOT NULL,
  `icon` varchar(80) NOT NULL DEFAULT '',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category_navigation_category` (`category_id`,`display_order`),
  CONSTRAINT `fk_category_navigation_category` FOREIGN KEY (`category_id`) REFERENCES `va_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_category_navigation`
--

LOCK TABLES `va_category_navigation` WRITE;
/*!40000 ALTER TABLE `va_category_navigation` DISABLE KEYS */;
INSERT INTO `va_category_navigation` VALUES
(6,3,'Добро пожаловать на наш сайт','/home.html','',1,1,'2026-11-23 00:50:52','2026-11-23 00:51:17'),
(7,3,'JavaScript','/category/javascript.html','',2,1,'2026-11-23 00:50:58','2026-11-23 00:51:17');
/*!40000 ALTER TABLE `va_category_navigation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_consultant_message_reads`
--

DROP TABLE IF EXISTS `va_consultant_message_reads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_consultant_message_reads` (
  `user_id` char(32) NOT NULL,
  `reader_role` varchar(8) NOT NULL,
  `viewed_message_id` bigint(20) unsigned DEFAULT NULL,
  `read_message_id` bigint(20) unsigned DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`user_id`,`reader_role`),
  KEY `consultant_message_reads_viewed_fk` (`viewed_message_id`),
  KEY `consultant_message_reads_read_fk` (`read_message_id`),
  CONSTRAINT `consultant_message_reads_read_fk` FOREIGN KEY (`read_message_id`) REFERENCES `va_consultant_messages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `consultant_message_reads_user_fk` FOREIGN KEY (`user_id`) REFERENCES `va_consultant_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consultant_message_reads_viewed_fk` FOREIGN KEY (`viewed_message_id`) REFERENCES `va_consultant_messages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_consultant_message_reads`
--

LOCK TABLES `va_consultant_message_reads` WRITE;
/*!40000 ALTER TABLE `va_consultant_message_reads` DISABLE KEYS */;
INSERT INTO `va_consultant_message_reads` VALUES
('040a1a9abab9fde55743164d10d18d63','admin',64,64,'2026-11-18 20:42:14'),
('040a1a9abab9fde55743164d10d18d63','user',63,63,'2026-11-18 20:40:27'),
('0acf91aa4b286ec3cc00aab6360262b3','admin',85,85,'2026-11-20 15:10:17'),
('615d5e5c96d149f405cae6b2e0655351','admin',66,66,'2026-11-20 07:52:46'),
('615d5e5c96d149f405cae6b2e0655351','user',67,NULL,'2026-11-18 23:37:59'),
('7ea33a3eb29fcc9f881f00f9496124dd','admin',86,86,'2026-11-20 15:10:16'),
('8031cf2c4dace3a27ce0e01cd37658ef','admin',50,50,'2026-11-20 07:52:51'),
('8031cf2c4dace3a27ce0e01cd37658ef','user',49,49,'2026-11-15 19:08:47'),
('a612d3dc83c05e394ef86ccfb9470200','admin',83,76,'2026-11-23 02:00:12'),
('a612d3dc83c05e394ef86ccfb9470200','user',77,77,'2026-11-20 07:53:21'),
('b365325a313519a2460298c58bc55c9a','admin',87,87,'2026-11-23 01:56:43'),
('b365325a313519a2460298c58bc55c9a','user',88,88,'2026-11-20 15:10:23');
/*!40000 ALTER TABLE `va_consultant_message_reads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_consultant_messages`
--

DROP TABLE IF EXISTS `va_consultant_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_consultant_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(32) NOT NULL,
  `role` varchar(8) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `consultant_messages_user_id` (`user_id`,`id`),
  CONSTRAINT `consultant_messages_user_fk` FOREIGN KEY (`user_id`) REFERENCES `va_consultant_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_consultant_messages`
--

LOCK TABLES `va_consultant_messages` WRITE;
/*!40000 ALTER TABLE `va_consultant_messages` DISABLE KEYS */;
INSERT INTO `va_consultant_messages` VALUES
(47,'8031cf2c4dace3a27ce0e01cd37658ef','user','Приветики','2026-11-15 19:02:30'),
(48,'8031cf2c4dace3a27ce0e01cd37658ef','admin','Как дела?','2026-11-15 19:03:14'),
(49,'8031cf2c4dace3a27ce0e01cd37658ef','admin','Что то еще интересует?','2026-11-15 19:03:30'),
(50,'8031cf2c4dace3a27ce0e01cd37658ef','user','Нет','2026-11-15 19:03:38'),
(59,'040a1a9abab9fde55743164d10d18d63','user','Здравствуйте','2026-11-17 15:22:14'),
(60,'040a1a9abab9fde55743164d10d18d63','admin','Здравствуйте','2026-11-17 15:22:30'),
(61,'040a1a9abab9fde55743164d10d18d63','admin','ghj','2026-11-18 20:30:13'),
(62,'040a1a9abab9fde55743164d10d18d63','user','fghgh','2026-11-18 20:34:08'),
(63,'040a1a9abab9fde55743164d10d18d63','admin','fghfg','2026-11-18 20:34:31'),
(64,'040a1a9abab9fde55743164d10d18d63','user','лджлдж','2026-11-18 20:40:29'),
(65,'040a1a9abab9fde55743164d10d18d63','admin','апр','2026-11-18 20:42:17'),
(66,'615d5e5c96d149f405cae6b2e0655351','user','Привет','2026-11-18 20:50:26'),
(67,'615d5e5c96d149f405cae6b2e0655351','admin','Привет','2026-11-18 20:50:44'),
(68,'a612d3dc83c05e394ef86ccfb9470200','user','аываыв','2026-11-19 15:59:18'),
(73,'a612d3dc83c05e394ef86ccfb9470200','admin','ываыва','2026-11-19 18:13:13'),
(74,'a612d3dc83c05e394ef86ccfb9470200','user','sdfsdf','2026-11-20 04:15:31'),
(75,'a612d3dc83c05e394ef86ccfb9470200','admin','dfsdf','2026-11-20 04:15:52'),
(76,'a612d3dc83c05e394ef86ccfb9470200','user','ываыва','2026-11-20 04:53:55'),
(77,'a612d3dc83c05e394ef86ccfb9470200','admin','выа','2026-11-20 04:54:21'),
(78,'a612d3dc83c05e394ef86ccfb9470200','user','dfdf','2026-11-20 07:46:02'),
(79,'a612d3dc83c05e394ef86ccfb9470200','user','gdfg','2026-11-20 07:46:51'),
(80,'a612d3dc83c05e394ef86ccfb9470200','user','ываыва','2026-11-20 07:52:21'),
(81,'a612d3dc83c05e394ef86ccfb9470200','user','ываы','2026-11-20 07:52:23'),
(82,'a612d3dc83c05e394ef86ccfb9470200','user','ываы','2026-11-20 07:52:25'),
(83,'a612d3dc83c05e394ef86ccfb9470200','user','вапвапва','2026-11-20 07:53:23'),
(84,'b365325a313519a2460298c58bc55c9a','user','ghbdtn','2026-11-20 07:53:55'),
(85,'0acf91aa4b286ec3cc00aab6360262b3','user','выава','2026-11-20 07:54:11'),
(86,'7ea33a3eb29fcc9f881f00f9496124dd','user','ываыв','2026-11-20 07:54:37'),
(87,'b365325a313519a2460298c58bc55c9a','user','ghbdtn','2026-11-20 15:10:04'),
(88,'b365325a313519a2460298c58bc55c9a','admin','sdfsdf','2026-11-20 15:10:21');
/*!40000 ALTER TABLE `va_consultant_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_consultant_reads`
--

DROP TABLE IF EXISTS `va_consultant_reads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_consultant_reads` (
  `user_id` char(32) NOT NULL,
  `message_id` bigint(20) unsigned NOT NULL,
  `read_at` datetime NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `consultant_reads_message_fk` (`message_id`),
  CONSTRAINT `consultant_reads_message_fk` FOREIGN KEY (`message_id`) REFERENCES `va_consultant_messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consultant_reads_user_fk` FOREIGN KEY (`user_id`) REFERENCES `va_consultant_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_consultant_reads`
--

LOCK TABLES `va_consultant_reads` WRITE;
/*!40000 ALTER TABLE `va_consultant_reads` DISABLE KEYS */;
/*!40000 ALTER TABLE `va_consultant_reads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_consultant_settings`
--

DROP TABLE IF EXISTS `va_consultant_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_consultant_settings` (
  `setting_key` varchar(64) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_consultant_settings`
--

LOCK TABLES `va_consultant_settings` WRITE;
/*!40000 ALTER TABLE `va_consultant_settings` DISABLE KEYS */;
INSERT INTO `va_consultant_settings` VALUES
('buttonColor','#1dff1a'),
('buttonPosition','right'),
('buttonTextColor','#ffffff'),
('censoredWords','дурак, идиот, тупица'),
('enabled','1'),
('notificationEmail',''),
('welcomeMessage','Здравствуйте! Чем могу помочь?'),
('workingHours','00:00-23:59');
/*!40000 ALTER TABLE `va_consultant_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_consultant_typing`
--

DROP TABLE IF EXISTS `va_consultant_typing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_consultant_typing` (
  `user_id` char(32) NOT NULL,
  `role` varchar(8) NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`user_id`,`role`),
  CONSTRAINT `consultant_typing_user_fk` FOREIGN KEY (`user_id`) REFERENCES `va_consultant_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_consultant_typing`
--

LOCK TABLES `va_consultant_typing` WRITE;
/*!40000 ALTER TABLE `va_consultant_typing` DISABLE KEYS */;
/*!40000 ALTER TABLE `va_consultant_typing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_consultant_users`
--

DROP TABLE IF EXISTS `va_consultant_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_consultant_users` (
  `id` char(32) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(40) NOT NULL,
  `source` varchar(500) NOT NULL DEFAULT '',
  `status` varchar(16) NOT NULL DEFAULT 'new',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `consultant_users_token_hash` (`token_hash`),
  KEY `consultant_users_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_consultant_users`
--

LOCK TABLES `va_consultant_users` WRITE;
/*!40000 ALTER TABLE `va_consultant_users` DISABLE KEYS */;
INSERT INTO `va_consultant_users` VALUES
('040a1a9abab9fde55743164d10d18d63','bfb40fcf3ffae8743833d30e38db34f7a3c99f2bf0405958f991ec3ffa7ea45c','Валентин Александрович Осотов','+7 99090572269','/','closed','2026-11-17 15:22:14','2026-11-18 20:42:22'),
('0acf91aa4b286ec3cc00aab6360262b3','23b4a63f76a74d65b74e129167df28253e6bdd72e1c2338d53118e60bcec11f5','Федор','+79634833886','/','new','2026-11-20 07:54:11','2026-11-20 07:54:11'),
('615d5e5c96d149f405cae6b2e0655351','23cf7b5d5bb49e7316cb22517d7334c880f55dadfd034f5e199401e787f1cd75','Валентин Александрович Осотов','+7 99090572269','/contacts.html','closed','2026-11-18 20:50:26','2026-11-20 07:52:48'),
('7ea33a3eb29fcc9f881f00f9496124dd','a66938bf8ad637bfad03b713a3592a94337d25ed4a5059bba69e0e317f31eae4','Ирина Паровозова','89015420601','/contacts.html','new','2026-11-20 07:54:37','2026-11-20 07:54:37'),
('8031cf2c4dace3a27ce0e01cd37658ef','fdc0e82b81930207990e37182f96f296207592a2e100ef52a1eddeb88ca32182','Валентин Александрович Осотов','+7 99090572269','/index.html','closed','2026-11-15 19:02:30','2026-11-15 19:06:14'),
('a612d3dc83c05e394ef86ccfb9470200','03766d5a6a4296a541aa788d0b40028037d3c1456ab44a52352392f5f0ad653d','Валентин Александрович Осотов','+7 99090572269','/','closed','2026-11-19 15:59:18','2026-11-20 07:53:36'),
('b365325a313519a2460298c58bc55c9a','74fc298496763008e04c7f9da3876c35332f960ca27a452d8d85d14e71dc32e7','Валентин Александрович Осотов','+7 99090572269','/blog.html','answered','2026-11-20 07:53:55','2026-11-20 15:10:21');
/*!40000 ALTER TABLE `va_consultant_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_custom_fields`
--

DROP TABLE IF EXISTS `va_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_custom_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `field_key` varchar(100) NOT NULL COMMENT 'Уникальный ключ для использования в шаблонах',
  `label` varchar(200) NOT NULL DEFAULT '' COMMENT 'Название поля в админке',
  `field_type` enum('text','textarea','number','url','image') NOT NULL DEFAULT 'text' COMMENT 'Тип данных',
  `field_value` mediumtext DEFAULT NULL COMMENT 'Значение (для image — путь к файлу)',
  `field_group` varchar(100) NOT NULL DEFAULT 'general' COMMENT 'Группа для сортировки',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Активно',
  `display_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Порядок отображения',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_custom_fields_key` (`field_key`),
  KEY `idx_custom_fields_group` (`field_group`,`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_custom_fields`
--

LOCK TABLES `va_custom_fields` WRITE;
/*!40000 ALTER TABLE `va_custom_fields` DISABLE KEYS */;
INSERT INTO `va_custom_fields` VALUES
(2,'banner_title','Заголовок банера','text','Ураааа Это Банер!!!','general',1,0,'2026-11-17 15:59:18','2026-11-20 05:45:19'),
(3,'my_name','Мое имя','text','Валентин Александрович','general',1,0,'2026-11-20 06:01:32','2026-11-20 06:01:32');
/*!40000 ALTER TABLE `va_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_landing_page_settings`
--

DROP TABLE IF EXISTS `va_landing_page_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_landing_page_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `section` varchar(100) NOT NULL DEFAULT '' COMMENT 'Название секции/блока',
  `section_key` varchar(100) NOT NULL DEFAULT '' COMMENT 'Уникальный ключ секции',
  `field_name` varchar(100) NOT NULL DEFAULT '' COMMENT 'Имя поля',
  `field_value` text DEFAULT NULL COMMENT 'Значение поля',
  `field_type` enum('text','textarea','number','email','tel','url','image','video','audio','color','checkbox','select','rich_text') NOT NULL DEFAULT 'text' COMMENT 'Тип поля',
  `field_group` varchar(50) NOT NULL DEFAULT 'general' COMMENT 'Группа: hero, features, about, contacts, etc.',
  `display_order` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Порядок отображения',
  `placeholder` varchar(254) DEFAULT '' COMMENT 'Подсказка',
  `options` text DEFAULT NULL COMMENT 'JSON массив для select',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Активно',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_section_key` (`section_key`),
  KEY `idx_field_group` (`field_group`),
  KEY `idx_display_order` (`display_order`),
  KEY `idx_field_name` (`field_name`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_landing_page_settings`
--

LOCK TABLES `va_landing_page_settings` WRITE;
/*!40000 ALTER TABLE `va_landing_page_settings` DISABLE KEYS */;
INSERT INTO `va_landing_page_settings` VALUES
(1,'hero','hero','title','Добро пожаловать на наш сайт','text','hero',1,'Заголовок',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(2,'hero','hero','subtitle','Создаём цифровые решения для вашего бизнеса','textarea','hero',2,'Подзаголовок',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(3,'hero','hero','cta_text','Начать','text','hero',3,'Текст кнопки',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(4,'hero','hero','cta_url','#contact','url','hero',4,'Ссылка кнопки',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(5,'hero','hero','background_image','','image','hero',5,'Фоновое изображение',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(6,'hero','hero','background_color','#ffffff','color','hero',6,'Цвет фона',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(7,'hero','hero','background_video','','video','hero',7,'Видео фон (URL)',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(8,'hero','hero','display_order','1','number','hero',8,'Порядок отображения',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(9,'features','features','title','Наши преимущества','text','features',1,'Заголовок секции',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(10,'features','features','subtitle','Почему выбирают нас','textarea','features',2,'Подзаголовок',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(11,'features','features','layout','grid','select','features',3,'Макет','[\"grid\",\"list\"]',1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(12,'features','features','display_order','2','number','features',4,'Порядок отображения',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(13,'about','about','title','О нас','text','about',1,'Заголовок',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(14,'about','about','content','Мы — команда профессионалов...','rich_text','about',2,'Содержание',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(15,'about','about','image','','image','about',3,'Изображение',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(16,'about','about','display_order','3','number','about',4,'Порядок отображения',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(17,'services','services','title','Услуги','text','services',1,'Заголовок',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(18,'services','services','subtitle','Что мы предлагаем','textarea','services',2,'Подзаголовок',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(19,'services','services','display_order','4','number','services',3,'Порядок отображения',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(20,'portfolio','portfolio','title','Портфолио','text','portfolio',1,'Заголовок',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(21,'portfolio','portfolio','subtitle','Наши работы','textarea','portfolio',2,'Подзаголовок',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(22,'portfolio','portfolio','display_order','5','number','portfolio',3,'Порядок отображения',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(23,'testimonials','testimonials','title','Отзывы','text','testimonials',1,'Заголовок',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(24,'testimonials','testimonials','subtitle','Что говорят наши клиенты','textarea','testimonials',2,'Подзаголовок',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(25,'testimonials','testimonials','display_order','6','number','testimonials',3,'Порядок отображения',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(26,'contact','contact','title','Контакты','text','contact',1,'Заголовок',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(27,'contact','contact','address','г. Москва, ул. Примерная, 1','text','contact',2,'Адрес',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(28,'contact','contact','phone','+7 (900) 123-45-67','tel','contact',3,'Телефон',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(29,'contact','contact','email','info@example.com','email','contact',4,'Email',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(30,'contact','contact','map_embed','','textarea','contact',5,'Код вставки карты',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(31,'contact','contact','display_order','7','number','contact',6,'Порядок отображения',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(32,'footer','footer','copyright','© 2024 Название компании','text','footer',1,'Копирайт',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(33,'footer','footer','background_color','#333333','color','footer',2,'Цвет фона',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(34,'footer','footer','text_color','#ffffff','color','footer',3,'Цвет текста',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(35,'footer','footer','display_order','8','number','footer',4,'Порядок отображения',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(36,'global','global','favicon','','image','global',1,'Favicon',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(37,'global','global','logo','','image','global',2,'Логотип',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(38,'global','global','logo_dark','','image','global',3,'Логотип (тёмная тема)',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(39,'global','global','primary_color','#ff8c1a','color','global',4,'Основной цвет',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(40,'global','global','secondary_color','#333333','color','global',5,'Вторичный цвет',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(41,'global','global','google_fonts','','textarea','global',6,'Google Fonts (код)',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(42,'global','global','google_analytics','','text','global',7,'Google Analytics ID',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(43,'global','global','custom_css','','textarea','global',8,'Пользовательский CSS',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26'),
(44,'global','global','custom_js','','textarea','global',9,'Пользовательский JS',NULL,1,'2026-11-16 16:21:26','2026-11-16 16:21:26');
/*!40000 ALTER TABLE `va_landing_page_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_landing_pages`
--

DROP TABLE IF EXISTS `va_landing_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_landing_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL DEFAULT 'home' COMMENT 'Уникальный ключ страницы',
  `title` varchar(254) NOT NULL DEFAULT '' COMMENT 'Заголовок страницы',
  `html_content` longtext DEFAULT NULL COMMENT 'Сгенерированный HTML',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Активна',
  `generated_at` datetime DEFAULT NULL COMMENT 'Дата генерации',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_landing_pages`
--

LOCK TABLES `va_landing_pages` WRITE;
/*!40000 ALTER TABLE `va_landing_pages` DISABLE KEYS */;
INSERT INTO `va_landing_pages` VALUES
(1,'home','Добро пожаловать на наш сайт','<!DOCTYPE html>\n<html lang=\"ru\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Добро пожаловать на наш сайт</title>\n    <meta name=\"description\" content=\"Создаём цифровые решения для вашего бизнеса\">\n    <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\">\n    <link href=\"https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css\" rel=\"stylesheet\">\n\n</head>\n<body>\n    <!-- Hero Section -->\n    <section class=\"hero-section py-5\" style=\"min-height: 80vh; display: flex; align-items: center;\">\n        <div class=\"container\">\n            <div class=\"row justify-content-center\">\n                <div class=\"col-lg-8 text-center text-white\">\n                    <h1 class=\"display-4 fw-bold mb-4\">Заголовок!</h1>\n                    <a href=\"#\" class=\"btn btn-lg btn-orange px-5 py-3 fw-bold\">{{field:my_name}}</a>                                   \n                </div>\n            </div>\n        </div>\n    </section>\n    <!-- Features Section -->\n    <section class=\"py-5\">\n        <div class=\"container\">\n            <div class=\"row justify-content-center\">\n                <div class=\"col-lg-8 text-center mb-5\">\n                    <h2 class=\"display-5 fw-bold mb-3\">Features</h2>\n                </div>\n            </div>\n        </div>\n    </section>\n    <!-- About Section -->\n    <section class=\"py-5\">\n        <div class=\"container\">\n            <div class=\"row justify-content-center\">\n                <div class=\"col-lg-8 text-center mb-5\">\n                    <h2 class=\"display-5 fw-bold mb-3\">About</h2>\n                </div>\n            </div>\n        </div>\n    </section>\n    <!-- Services Section -->\n    <section class=\"py-5\">\n        <div class=\"container\">\n            <div class=\"row justify-content-center\">\n                <div class=\"col-lg-8 text-center mb-5\">\n                    <h2 class=\"display-5 fw-bold mb-3\">Services</h2>\n                </div>\n            </div>\n        </div>\n    </section>\n    <!-- Portfolio Section -->\n    <section class=\"py-5\">\n        <div class=\"container\">\n            <div class=\"row justify-content-center\">\n                <div class=\"col-lg-8 text-center mb-5\">\n                    <h2 class=\"display-5 fw-bold mb-3\">Portfolio</h2>\n                </div>\n            </div>\n        </div>\n    </section>\n    <!-- Testimonials Section -->\n    <section class=\"py-5\">\n        <div class=\"container\">\n            <div class=\"row justify-content-center\">\n                <div class=\"col-lg-8 text-center mb-5\">\n                    <h2 class=\"display-5 fw-bold mb-3\">Testimonials</h2>\n                </div>\n            </div>\n        </div>\n    </section>\n    <!-- Contact Section -->\n    <section class=\"py-5 bg-light\">\n        <div class=\"container\">\n            <div class=\"row justify-content-center\">\n                <div class=\"col-lg-8 text-center mb-5\">\n                    <h2 class=\"display-5 fw-bold mb-3\">Контакты</h2>\n                </div>\n            </div>\n            <div class=\"row g-4 justify-content-center\">\n            </div>\n        </div>\n    </section>\n    <!-- Footer -->\n    <footer class=\"py-4\" style=\"background-color: #333333; color: #ffffff;\">\n        <div class=\"container text-center\">\n            <p class=\"mb-0\">© 2024</p>\n        </div>\n    </footer>\n\n    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js\"></script>\n\n\n</body>\n</html>',0,'2026-11-20 07:45:03','2026-11-16 16:26:26','2026-11-23 00:02:37');
/*!40000 ALTER TABLE `va_landing_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_logs`
--

DROP TABLE IF EXISTS `va_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_type` varchar(20) NOT NULL,
  `message` varchar(500) NOT NULL DEFAULT '',
  `user_id` int(10) unsigned DEFAULT NULL,
  `ip` varchar(45) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_logs_type_created` (`log_type`,`created_at`),
  KEY `idx_logs_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_logs`
--

LOCK TABLES `va_logs` WRITE;
/*!40000 ALTER TABLE `va_logs` DISABLE KEYS */;
INSERT INTO `va_logs` VALUES
(90,'auth','Вход: Павел Менеджер (pavel@manager.com)',7,'127.0.0.1','2026-11-20 06:42:53'),
(91,'forms','Заявка от Валентин Александрович Осотов (osvax@yandex.ru)',NULL,'127.0.0.1','2026-11-20 07:59:45'),
(92,'forms','Комментарий от Павел Менеджер (pavel@manager.com)',NULL,'127.0.0.1','2026-11-20 08:06:44'),
(93,'auth','Вход: Валентин (osvax@yandex.ru)',1,'127.0.0.1','2026-11-20 15:05:26'),
(94,'forms','Заявка от Валентин Александрович Осотов (osvax@yandex.ru)',NULL,'127.0.0.1','2026-11-20 15:09:14'),
(95,'auth','Вход: Валентин (osvax@yandex.ru)',1,'127.0.0.1','2026-11-22 21:37:02'),
(96,'auth','Выход: Валентин (osvax@yandex.ru)',1,'127.0.0.1','2026-11-22 21:50:51'),
(97,'auth','Вход: Павел Менеджер (pavel@manager.com)',7,'127.0.0.1','2026-11-22 21:57:02'),
(98,'auth','Выход: Павел Менеджер (pavel@manager.com)',7,'127.0.0.1','2026-11-22 21:58:05'),
(99,'auth','Вход: Павел Менеджер (pavel@manager.com)',7,'127.0.0.1','2026-11-22 22:44:36'),
(100,'auth','Неудачная попытка входа: osvax@yandex.ru',NULL,'127.0.0.1','2026-11-22 22:52:12'),
(101,'auth','Неудачная попытка входа: osvax@yandex.ru',NULL,'127.0.0.1','2026-11-22 22:52:19'),
(102,'auth','Неудачная попытка входа: osvax@yandex.ru',NULL,'127.0.0.1','2026-11-22 22:53:03'),
(103,'auth','Вход: Валентин (osvax@yandex.ru)',1,'127.0.0.1','2026-11-22 22:53:18'),
(104,'auth','Вход: Валентин (osvax@yandex.ru)',1,'127.0.0.1','2026-11-22 22:56:05'),
(105,'auth','Вход: Валентин (osvax@yandex.ru)',1,'127.0.0.1','2026-11-22 22:56:20'),
(106,'auth','Вход: Валентин (osvax@yandex.ru)',1,'127.0.0.1','2026-11-22 23:05:08'),
(107,'auth','Неудачная попытка входа: osvax@yandex.ru',NULL,'127.0.0.1','2026-11-22 23:05:28'),
(108,'auth','Неудачная попытка входа: osvax@yandex.ru',NULL,'127.0.0.1','2026-11-22 23:07:52'),
(109,'auth','Неудачная попытка входа: osvax@yandex.ru',NULL,'127.0.0.1','2026-11-22 23:11:02'),
(110,'auth','Неудачная попытка входа: osvax@yandex.ru',NULL,'127.0.0.1','2026-11-22 23:11:34'),
(111,'auth','Вход: Валентин (osvax@yandex.ru)',1,'127.0.0.1','2026-11-22 23:14:47'),
(112,'auth','Вход: Павел Менеджер (pavel@manager.com)',7,'127.0.0.1','2026-11-22 23:15:49'),
(113,'auth','Вход: Павел Менеджер (pavel@manager.com)',7,'127.0.0.1','2026-11-22 23:15:51'),
(114,'auth','Вход: Валентин (osvax@yandex.ru)',1,'127.0.0.1','2026-11-22 23:34:13'),
(115,'errors','Ошибка 500: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'val_myblog.va_category_navigation\' doesn\'t exist (/api/admin/categories/3/navigation)',NULL,'127.0.0.1','2026-11-23 00:14:00'),
(116,'errors','Ошибка 500: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'val_myblog.va_category_navigation\' doesn\'t exist (/api/admin/categories/3/navigation)',NULL,'127.0.0.1','2026-11-23 00:17:31'),
(117,'errors','Ошибка 500: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'val_myblog.va_category_navigation\' doesn\'t exist (/api/admin/categories/3/navigation)',NULL,'127.0.0.1','2026-11-23 00:19:17'),
(118,'errors','Ошибка 500: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'val_myblog.va_category_navigation\' doesn\'t exist (/api/admin/categories/3/navigation)',NULL,'127.0.0.1','2026-11-23 00:19:31'),
(119,'errors','Ошибка 500: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'val_myblog.va_category_navigation\' doesn\'t exist (/api/admin/categories/3/navigation)',NULL,'127.0.0.1','2026-11-23 00:19:40'),
(120,'errors','Ошибка 500: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'val_myblog.va_category_navigation\' doesn\'t exist (/api/admin/categories/3/navigation)',NULL,'127.0.0.1','2026-11-23 00:19:47'),
(121,'errors','Ошибка 500: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'val_myblog.va_category_navigation\' doesn\'t exist (/api/admin/categories/3/navigation)',NULL,'127.0.0.1','2026-11-23 00:20:49'),
(122,'security','Отказ в доступе к разделу админки «role-edit»',16,'127.0.0.1','2026-11-23 01:54:53'),
(123,'security','Отказ в доступе к разделу админки «role-edit»',16,'127.0.0.1','2026-11-23 01:54:58'),
(124,'security','Отказ в доступе к разделу админки «role-edit»',16,'127.0.0.1','2026-11-23 01:55:01'),
(125,'auth','Вход: Демонстрационный пользователь (demo@user.test)',16,'127.0.0.1','2026-11-23 01:56:11'),
(126,'auth','Вход: Демонстрационный пользователь (demo@user.test)',16,'127.0.0.1','2026-11-23 01:56:13'),
(127,'errors','Ошибка 500: htmlspecialchars(): Argument #1 ($string) must be of type string, array given (/project-photo.html)',NULL,'127.0.0.1','2026-11-23 02:02:59'),
(128,'errors','Ошибка 500: htmlspecialchars(): Argument #1 ($string) must be of type string, array given (/project-photo.html)',NULL,'127.0.0.1','2026-11-23 02:03:01'),
(129,'auth','Вход: Валентин (osvax@yandex.ru)',1,'127.0.0.1','2026-11-23 02:38:34');
/*!40000 ALTER TABLE `va_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_menu_items`
--

DROP TABLE IF EXISTS `va_menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_menu_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` int(10) unsigned NOT NULL COMMENT 'Меню',
  `parent_id` int(10) unsigned DEFAULT NULL COMMENT 'Родительский пункт (подменю)',
  `title` varchar(160) NOT NULL COMMENT 'Текст пункта',
  `url` varchar(255) NOT NULL DEFAULT '/' COMMENT 'Ссылка',
  `icon` varchar(80) NOT NULL DEFAULT '' COMMENT 'Иконка Bootstrap Icons',
  `css_class` varchar(200) NOT NULL DEFAULT '' COMMENT 'CSS-классы пункта',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Активен',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_menu_items_menu` (`menu_id`,`display_order`),
  KEY `idx_menu_items_parent` (`parent_id`),
  CONSTRAINT `fk_menu_items_menu` FOREIGN KEY (`menu_id`) REFERENCES `va_menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_menu_items_parent` FOREIGN KEY (`parent_id`) REFERENCES `va_menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_menu_items`
--

LOCK TABLES `va_menu_items` WRITE;
/*!40000 ALTER TABLE `va_menu_items` DISABLE KEYS */;
INSERT INTO `va_menu_items` VALUES
(1,1,NULL,'Главная','/','','',1,1,'2026-11-17 22:27:44','2026-11-23 00:43:11'),
(2,1,NULL,'Блог','/blog.html','','',2,1,'2026-11-17 22:27:44','2026-11-23 00:43:11'),
(3,1,NULL,'Портфолио','/portfolio.html','','',3,1,'2026-11-17 22:27:44','2026-11-23 00:37:29'),
(4,1,NULL,'Заказать','/order.html','','',4,1,'2026-11-17 22:27:44','2026-11-17 22:27:44'),
(5,1,NULL,'Контакты','/contacts.html','','',5,1,'2026-11-17 22:27:44','2026-11-23 00:37:16'),
(61,9,NULL,'Дизайн','/category/dizayn.html','bi-tag','',2,1,'2026-11-23 01:28:27','2026-11-23 01:35:03'),
(62,9,NULL,'JavaScript','/category/javascript.html','bi-tag','',3,1,'2026-11-23 01:28:27','2026-11-23 01:35:03'),
(63,9,NULL,'Инструменты','/category/instrumenty.html','bi-tag','',4,1,'2026-11-23 01:28:27','2026-11-23 01:35:03'),
(64,9,NULL,'Оптимизация','/category/optimizatsiya.html','bi-tag','',5,1,'2026-11-23 01:28:27','2026-11-23 01:35:03'),
(65,9,NULL,'React','/category/react.html','bi-tag','',6,1,'2026-11-23 01:28:27','2026-11-23 01:35:03'),
(66,9,NULL,'PHP','/category/php.html','bi-tag','',7,1,'2026-11-23 01:28:27','2026-11-23 01:35:03'),
(68,9,NULL,'Вёрстка','/category/vyorstka.html','','',1,1,'2026-11-23 01:35:00','2026-11-23 01:35:03');
/*!40000 ALTER TABLE `va_menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_menus`
--

DROP TABLE IF EXISTS `va_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(80) NOT NULL COMMENT 'Идентификатор меню (для render_menu)',
  `title` varchar(160) NOT NULL DEFAULT '' COMMENT 'Название в админке',
  `css_class` varchar(200) NOT NULL DEFAULT '' COMMENT 'CSS-классы контейнера',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_menus_name` (`menu_name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_menus`
--

LOCK TABLES `va_menus` WRITE;
/*!40000 ALTER TABLE `va_menus` DISABLE KEYS */;
INSERT INTO `va_menus` VALUES
(1,'main','Главное меню','','2026-11-17 22:27:44'),
(9,'rubric_main','Рубрики блога','','2026-11-23 01:18:54');
/*!40000 ALTER TABLE `va_menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_messages`
--

DROP TABLE IF EXISTS `va_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `message_type` enum('feedback','comment') NOT NULL,
  `name` varchar(160) NOT NULL,
  `email` varchar(254) NOT NULL,
  `phone` varchar(40) NOT NULL DEFAULT '',
  `subject` varchar(240) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `article_slug` varchar(180) NOT NULL DEFAULT '',
  `parent_id` int(10) unsigned DEFAULT NULL COMMENT 'Родительский комментарий (ответ)',
  `is_staff` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Ответ от имени автора сайта',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_messages_inbox` (`message_type`,`is_read`,`created_at`),
  KEY `idx_messages_article` (`article_slug`,`is_approved`),
  KEY `idx_messages_parent` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_messages`
--

LOCK TABLES `va_messages` WRITE;
/*!40000 ALTER TABLE `va_messages` DISABLE KEYS */;
INSERT INTO `va_messages` VALUES
(2,'comment','osvax','osvax@yandex.ru','','Комментарий к статье post-speed','Привет','post-speed',NULL,0,1,1,'2026-11-17 16:05:16'),
(4,'comment','Администратор','osvax@yandex.ru','','Комментарий к статье post-bootstrap','ываыввввввввв','post-bootstrap',NULL,0,1,1,'2026-11-17 16:10:45'),
(5,'comment','Admin','osvax@yandex.ru','','Комментарий к статье post-bootstrap','вапвап','post-bootstrap',NULL,0,1,1,'2026-11-17 16:11:06'),
(16,'comment','Администратор','osvax@yandex.ru','','Комментарий к статье post-bootstrap','вапвап','post-bootstrap',NULL,0,1,1,'2026-11-17 16:26:07'),
(19,'feedback','Тест','test@example.com','','Проверка','Тестовый отзыв','',NULL,0,1,1,'2026-11-17 17:49:22'),
(20,'feedback','Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Заблокировали Steam аккаунт.','sdfsdf','',NULL,0,1,1,'2026-11-18 21:39:18'),
(21,'feedback','Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Код аутентификации','asdad','',NULL,0,1,1,'2026-11-20 01:22:24'),
(22,'feedback','Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Код аутентификации','asdasd','',NULL,0,1,1,'2026-11-20 01:35:42'),
(23,'feedback','Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Добавление багажа 20 кг','asdasd','',NULL,0,1,1,'2026-11-20 01:36:09'),
(24,'feedback','Валентин Александрович Осотов','pavel@manager.com','+7 99090572269','Заблокировали Steam аккаунт.','ываыва','',NULL,0,1,1,'2026-11-20 03:19:37'),
(25,'feedback','Валентин Александрович Осотов','pavel@manager.com','+7 99090572269','Заблокировали Steam аккаунт.','ываыва','',NULL,0,1,1,'2026-11-20 03:20:15'),
(26,'feedback','Валентин Александрович Осотов','pavel@manager.com','+7 99090572269','Заблокировали Steam аккаунт.','asdas','',NULL,0,1,1,'2026-11-20 03:25:27'),
(27,'comment','Павел Менеджер','pavel@manager.com','','Комментарий к статье post-bootstrap','asdasdasd','post-bootstrap',NULL,0,1,1,'2026-11-20 03:26:46'),
(28,'comment','Павел Менеджер','pavel@manager.com','','Комментарий к статье post-bootstrap','dffg','post-bootstrap',NULL,0,1,1,'2026-11-20 03:27:29'),
(29,'comment','Павел Менеджер','pavel@manager.com','','Комментарий к статье post-bootstrap','рло','post-bootstrap',NULL,0,1,1,'2026-11-20 08:06:43');
/*!40000 ALTER TABLE `va_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_orders`
--

DROP TABLE IF EXISTS `va_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_name` varchar(160) NOT NULL,
  `email` varchar(254) NOT NULL,
  `phone` varchar(40) NOT NULL DEFAULT '',
  `project_type` varchar(120) NOT NULL DEFAULT '',
  `budget` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deadline` date DEFAULT NULL,
  `order_date` date NOT NULL,
  `status` enum('Новый','В работе','Завершён','Отменён') NOT NULL DEFAULT 'Новый',
  `details_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details_json`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_orders_date` (`order_date`),
  KEY `idx_orders_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_orders`
--

LOCK TABLES `va_orders` WRITE;
/*!40000 ALTER TABLE `va_orders` DISABLE KEYS */;
INSERT INTO `va_orders` VALUES
(1,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Сайт-визитка',28000.00,'2025-01-20','2024-01-17','Завершён','{\"phone\":\"+7 99090572269\",\"projectType\":\"Сайт-визитка\",\"deadline\":\"2025-01-20\",\"completedAt\":\"2025-01-20\",\"paidAmount\":27999.96,\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"source\":\"Форма заказа на сайте\",\"address\":\"\",\"contactPerson\":\"Валентин Александрович Осотов\",\"companyName\":\"\",\"companyInn\":\"\",\"companyOgrn\":\"\",\"companyContactPosition\":\"\",\"companyEmail\":\"\",\"companyPhone\":\"\",\"companyAddress\":\"\",\"projectTitle\":\"Сайт-визитка\",\"orderDate\":\"2024-01-17\",\"status\":\"Завершён\",\"projectUrl\":\"\",\"cancelReason\":\"\",\"technicalBrief\":\"ываыва\",\"budget\":28000,\"lastPaymentDate\":\"\",\"paymentMethod\":\"\",\"paymentHistory\":\"\",\"notes\":\"\"}','2024-11-17 16:08:58','2026-11-20 07:09:54'),
(2,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Блог',54000.00,'2023-06-20','2023-01-17','Завершён','{\"phone\":\"+7 99090572269\",\"projectType\":\"Блог\",\"deadline\":\"2023-06-20\",\"completedAt\":\"2023-07-20\",\"paidAmount\":53999.97,\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"source\":\"Форма заказа на сайте\",\"address\":\"\",\"contactPerson\":\"Валентин Александрович Осотов\",\"companyName\":\"\",\"companyInn\":\"\",\"companyOgrn\":\"\",\"companyContactPosition\":\"\",\"companyEmail\":\"\",\"companyPhone\":\"\",\"companyAddress\":\"\",\"projectTitle\":\"Блог\",\"orderDate\":\"2023-01-17\",\"status\":\"Завершён\",\"projectUrl\":\"\",\"cancelReason\":\"\",\"technicalBrief\":\"пвапвап\",\"budget\":54000,\"lastPaymentDate\":\"\",\"paymentMethod\":\"\",\"paymentHistory\":\"\",\"notes\":\"\"}','2024-11-17 16:09:49','2026-11-20 06:56:26'),
(3,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Сайт-визитка',27000.00,'2024-05-20','2024-01-17','В работе','{\"phone\":\"+7 99090572269\",\"projectType\":\"Сайт-визитка\",\"deadline\":\"2024-05-20\",\"completedAt\":\"2024-06-20\",\"paidAmount\":27000,\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"source\":\"Форма заказа на сайте\",\"address\":\"\",\"contactPerson\":\"Валентин Александрович Осотов\",\"companyName\":\"\",\"companyInn\":\"\",\"companyOgrn\":\"\",\"companyContactPosition\":\"\",\"companyEmail\":\"\",\"companyPhone\":\"\",\"companyAddress\":\"\",\"projectTitle\":\"Сайт-визитка\",\"orderDate\":\"2024-01-17\",\"status\":\"В работе\",\"projectUrl\":\"\",\"cancelReason\":\"\",\"technicalBrief\":\"вваапвап\",\"budget\":27000,\"lastPaymentDate\":\"\",\"paymentMethod\":\"\",\"paymentHistory\":\"\",\"notes\":\"\",\"id\":3,\"client\":\"Валентин Александрович Осотов\",\"type\":\"Сайт-визитка\",\"createdAt\":\"2024-01-17\"}','2024-11-17 18:29:53','2026-11-20 07:33:31'),
(4,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Сайт-визитка',27000.00,'2026-11-30','2026-11-18','В работе','{\"phone\":\"+7 99090572269\",\"projectType\":\"Сайт-визитка\",\"deadline\":\"2026-11-30\",\"completedAt\":\"\",\"paidAmount\":0,\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"source\":\"Форма заказа на сайте\",\"contactPerson\":\"Валентин Александрович Осотов\",\"projectTitle\":\"Сайт-визитка\",\"orderDate\":\"2026-11-18\",\"status\":\"В работе\",\"technicalBrief\":\"фываыва\",\"budget\":27000,\"budgetDisplay\":\"27 000 ₽\",\"deadlineFrom\":\"2026-11-18\",\"paymentHistory\":\"\",\"notes\":\"\",\"calculatorDetails\":\"{\\\"type\\\":\\\"Сайт-визитка\\\",\\\"features\\\":[{\\\"name\\\":\\\"До 5 страниц\\\",\\\"price\\\":15000,\\\"days\\\":7},{\\\"name\\\":\\\"Адаптивная вёрстка\\\",\\\"price\\\":5000,\\\"days\\\":3},{\\\"name\\\":\\\"Форма обратной связи\\\",\\\"price\\\":3000,\\\"days\\\":2},{\\\"name\\\":\\\"SEO-базовый\\\",\\\"price\\\":4000,\\\"days\\\":3}],\\\"price\\\":27000,\\\"currency\\\":\\\"₽\\\",\\\"days\\\":15}\",\"id\":4,\"client\":\"Валентин Александрович Осотов\",\"type\":\"Сайт-визитка\",\"createdAt\":\"2026-11-18\"}','2025-11-18 03:17:43','2026-11-20 04:44:39'),
(5,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Сайт-визитка',27000.00,'2026-11-30','2026-11-19','В работе','{\"phone\":\"+7 99090572269\",\"projectType\":\"Сайт-визитка\",\"deadline\":\"2026-11-30\",\"completedAt\":\"\",\"paidAmount\":0,\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"source\":\"Форма заказа на сайте\",\"contactPerson\":\"Валентин Александрович Осотов\",\"projectTitle\":\"Сайт-визитка\",\"orderDate\":\"2026-11-19\",\"status\":\"В работе\",\"technicalBrief\":\"ваааааааааааааааааааааааааааа\",\"budget\":27000,\"budgetDisplay\":\"27 000 ₽\",\"deadlineFrom\":\"2026-11-19\",\"paymentHistory\":\"\",\"notes\":\"\",\"calculatorDetails\":\"{\\\"type\\\":\\\"Сайт-визитка\\\",\\\"features\\\":[{\\\"name\\\":\\\"До 5 страниц\\\",\\\"price\\\":15000,\\\"days\\\":7},{\\\"name\\\":\\\"Адаптивная вёрстка\\\",\\\"price\\\":5000,\\\"days\\\":3},{\\\"name\\\":\\\"Форма обратной связи\\\",\\\"price\\\":3000,\\\"days\\\":2},{\\\"name\\\":\\\"SEO-базовый\\\",\\\"price\\\":4000,\\\"days\\\":3}],\\\"price\\\":27000,\\\"currency\\\":\\\"₽\\\",\\\"days\\\":15}\",\"id\":5,\"client\":\"Валентин Александрович Осотов\",\"type\":\"Сайт-визитка\",\"createdAt\":\"2026-11-19\"}','2025-11-18 21:42:51','2026-11-20 08:05:25'),
(6,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Сайт-визитка',27000.00,'2026-11-30','2026-11-20','Отменён','{\"phone\":\"+7 99090572269\",\"projectType\":\"Сайт-визитка\",\"deadline\":\"2026-11-30\",\"completedAt\":\"\",\"paidAmount\":0,\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"source\":\"Форма заказа на сайте\",\"contactPerson\":\"\",\"projectTitle\":\"Сайт-визитка\",\"orderDate\":\"2026-11-20\",\"status\":\"Отменён\",\"technicalBrief\":\"asdasd\",\"budget\":27000,\"budgetDisplay\":\"27 000 ₽\",\"deadlineFrom\":\"2026-11-20\",\"paymentHistory\":\"\",\"notes\":\"\",\"calculatorDetails\":\"{\\\"type\\\":\\\"Сайт-визитка\\\",\\\"features\\\":[{\\\"name\\\":\\\"До 5 страниц\\\",\\\"price\\\":15000,\\\"days\\\":7},{\\\"name\\\":\\\"Адаптивная вёрстка\\\",\\\"price\\\":5000,\\\"days\\\":3},{\\\"name\\\":\\\"Форма обратной связи\\\",\\\"price\\\":3000,\\\"days\\\":2},{\\\"name\\\":\\\"SEO-базовый\\\",\\\"price\\\":4000,\\\"days\\\":3}],\\\"price\\\":27000,\\\"currency\\\":\\\"₽\\\",\\\"days\\\":15}\",\"id\":6,\"client\":\"Валентин Александрович Осотов\",\"type\":\"Сайт-визитка\",\"createdAt\":\"2026-11-20\"}','2023-03-20 00:41:45','2026-11-20 08:06:04'),
(7,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Интернет-магазин',89000.00,'2026-11-20','2026-11-20','Завершён','{\"phone\":\"+7 99090572269\",\"projectType\":\"Интернет-магазин\",\"deadline\":\"2026-11-20\",\"completedAt\":\"\",\"paidAmount\":0,\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"source\":\"Форма заказа на сайте\",\"contactPerson\":\"\",\"projectTitle\":\"Интернет-магазин\",\"orderDate\":\"2026-11-20\",\"status\":\"Завершён\",\"technicalBrief\":\"asdasdasd\",\"budget\":89000,\"budgetDisplay\":\"89 000 ₽\",\"deadlineFrom\":\"2026-11-20\",\"paymentHistory\":\"\",\"notes\":\"\",\"calculatorDetails\":\"{\\\"type\\\":\\\"Интернет-магазин\\\",\\\"features\\\":[{\\\"name\\\":\\\"Каталог товаров\\\",\\\"price\\\":40000,\\\"days\\\":20},{\\\"name\\\":\\\"Корзина и оформление\\\",\\\"price\\\":15000,\\\"days\\\":10},{\\\"name\\\":\\\"Онлайн-оплата\\\",\\\"price\\\":12000,\\\"days\\\":8},{\\\"name\\\":\\\"Управление складом\\\",\\\"price\\\":10000,\\\"days\\\":7},{\\\"name\\\":\\\"Личный кабинет\\\",\\\"price\\\":12000,\\\"days\\\":8}],\\\"price\\\":89000,\\\"currency\\\":\\\"₽\\\",\\\"days\\\":53}\",\"id\":7,\"client\":\"Валентин Александрович Осотов\",\"type\":\"Интернет-магазин\",\"createdAt\":\"2026-11-20\"}','2023-11-20 01:36:59','2026-11-20 08:05:37'),
(8,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Интернет-магазин',89000.00,'2026-11-30','2026-11-20','Завершён','{\"phone\":\"+7 99090572269\",\"projectType\":\"Интернет-магазин\",\"deadline\":\"2026-11-30\",\"completedAt\":\"\",\"paidAmount\":89000,\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"source\":\"Форма заказа на сайте\",\"address\":\"\",\"contactPerson\":\"\",\"companyName\":\"\",\"companyInn\":\"\",\"companyOgrn\":\"\",\"companyContactPosition\":\"\",\"companyEmail\":\"\",\"companyPhone\":\"\",\"companyAddress\":\"\",\"projectTitle\":\"Интернет-магазин\",\"orderDate\":\"2026-11-20\",\"status\":\"Завершён\",\"projectUrl\":\"\",\"cancelReason\":\"\",\"technicalBrief\":\"asdasdasd\",\"budget\":89000,\"lastPaymentDate\":\"\",\"paymentMethod\":\"\",\"paymentHistory\":\"\",\"notes\":\"\"}','2022-01-20 01:38:22','2026-11-20 06:57:08'),
(9,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Сайт-визитка',27000.00,'2026-11-20','2026-11-20','В работе','{\"phone\":\"+7 99090572269\",\"projectType\":\"Сайт-визитка\",\"deadline\":\"2026-11-20\",\"completedAt\":\"\",\"paidAmount\":0,\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"source\":\"Форма заказа на сайте\",\"contactPerson\":\"Валентин Александрович Осотов\",\"projectTitle\":\"Сайт-визитка\",\"orderDate\":\"2026-11-20\",\"status\":\"В работе\",\"technicalBrief\":\"asad\",\"budget\":27000,\"budgetDisplay\":\"27 000 ₽\",\"deadlineFrom\":\"2026-11-20\",\"paymentHistory\":\"\",\"notes\":\"\",\"calculatorDetails\":\"{\\\"type\\\":\\\"Сайт-визитка\\\",\\\"features\\\":[{\\\"name\\\":\\\"До 5 страниц\\\",\\\"price\\\":15000,\\\"days\\\":7},{\\\"name\\\":\\\"Адаптивная вёрстка\\\",\\\"price\\\":5000,\\\"days\\\":3},{\\\"name\\\":\\\"Форма обратной связи\\\",\\\"price\\\":3000,\\\"days\\\":2},{\\\"name\\\":\\\"SEO-базовый\\\",\\\"price\\\":4000,\\\"days\\\":3}],\\\"price\\\":27000,\\\"currency\\\":\\\"₽\\\",\\\"days\\\":15}\",\"id\":9,\"client\":\"Валентин Александрович Осотов\",\"type\":\"Сайт-визитка\",\"createdAt\":\"2026-11-20\"}','2026-11-20 02:27:00','2026-11-20 04:44:42'),
(10,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Сайт-визитка',27000.00,'2026-11-30','2026-11-20','В работе','{\"phone\":\"+7 99090572269\",\"projectType\":\"Сайт-визитка\",\"deadline\":\"2026-11-30\",\"completedAt\":\"\",\"paidAmount\":0,\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"source\":\"Форма заказа на сайте\",\"contactPerson\":\"Валентин Александрович Осотов\",\"projectTitle\":\"Сайт-визитка\",\"orderDate\":\"2026-11-20\",\"status\":\"В работе\",\"technicalBrief\":\"dssfdf\",\"budget\":27000,\"budgetDisplay\":\"27 000 ₽\",\"deadlineFrom\":\"2026-11-20\",\"paymentHistory\":\"\",\"notes\":\"\",\"calculatorDetails\":\"{\\\"type\\\":\\\"Сайт-визитка\\\",\\\"features\\\":[{\\\"name\\\":\\\"До 5 страниц\\\",\\\"price\\\":15000,\\\"days\\\":7},{\\\"name\\\":\\\"Адаптивная вёрстка\\\",\\\"price\\\":5000,\\\"days\\\":3},{\\\"name\\\":\\\"Форма обратной связи\\\",\\\"price\\\":3000,\\\"days\\\":2},{\\\"name\\\":\\\"SEO-базовый\\\",\\\"price\\\":4000,\\\"days\\\":3}],\\\"price\\\":27000,\\\"currency\\\":\\\"₽\\\",\\\"days\\\":15}\",\"id\":10,\"client\":\"Валентин Александрович Осотов\",\"type\":\"Сайт-визитка\",\"createdAt\":\"2026-11-20\"}','2026-02-20 02:57:39','2026-11-20 08:06:12'),
(11,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Сайт-визитка',27000.00,'2026-11-30','2026-11-20','Завершён','{\"phone\":\"+7 99090572269\",\"projectType\":\"Сайт-визитка\",\"deadline\":\"2026-11-30\",\"completedAt\":\"\",\"paidAmount\":0,\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"source\":\"Форма заказа на сайте\",\"contactPerson\":\"Валентин Александрович Осотов\",\"projectTitle\":\"Сайт-визитка\",\"orderDate\":\"2026-11-20\",\"status\":\"Завершён\",\"technicalBrief\":\"asdasd\",\"budget\":27000,\"budgetDisplay\":\"27 000 ₽\",\"deadlineFrom\":\"2026-11-20\",\"paymentHistory\":\"\",\"notes\":\"\",\"calculatorDetails\":\"{\\\"type\\\":\\\"Сайт-визитка\\\",\\\"features\\\":[{\\\"name\\\":\\\"До 5 страниц\\\",\\\"price\\\":15000,\\\"days\\\":7},{\\\"name\\\":\\\"Адаптивная вёрстка\\\",\\\"price\\\":5000,\\\"days\\\":3},{\\\"name\\\":\\\"Форма обратной связи\\\",\\\"price\\\":3000,\\\"days\\\":2},{\\\"name\\\":\\\"SEO-базовый\\\",\\\"price\\\":4000,\\\"days\\\":3}],\\\"price\\\":27000,\\\"currency\\\":\\\"₽\\\",\\\"days\\\":15}\",\"id\":11,\"client\":\"Валентин Александрович Осотов\",\"type\":\"Сайт-визитка\",\"createdAt\":\"2026-11-20\"}','2026-04-20 03:02:20','2026-11-20 08:06:20'),
(12,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Сайт-визитка',27000.00,'2026-11-30','2026-11-20','Отменён','{\"phone\":\"+7 99090572269\",\"projectType\":\"Сайт-визитка\",\"deadline\":\"2026-11-30\",\"completedAt\":\"\",\"paidAmount\":0,\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"source\":\"Форма заказа на сайте\",\"contactPerson\":\"Валентин Александрович Осотов\",\"projectTitle\":\"Сайт-визитка\",\"orderDate\":\"2026-11-20\",\"status\":\"Отменён\",\"technicalBrief\":\"asdasd\",\"budget\":27000,\"budgetDisplay\":\"27 000 ₽\",\"deadlineFrom\":\"2026-11-20\",\"paymentHistory\":\"\",\"notes\":\"\",\"calculatorDetails\":\"{\\\"type\\\":\\\"Сайт-визитка\\\",\\\"features\\\":[{\\\"name\\\":\\\"До 5 страниц\\\",\\\"price\\\":15000,\\\"days\\\":7},{\\\"name\\\":\\\"Адаптивная вёрстка\\\",\\\"price\\\":5000,\\\"days\\\":3},{\\\"name\\\":\\\"Форма обратной связи\\\",\\\"price\\\":3000,\\\"days\\\":2},{\\\"name\\\":\\\"SEO-базовый\\\",\\\"price\\\":4000,\\\"days\\\":3}],\\\"price\\\":27000,\\\"currency\\\":\\\"₽\\\",\\\"days\\\":15}\",\"id\":12,\"client\":\"Валентин Александрович Осотов\",\"type\":\"Сайт-визитка\",\"createdAt\":\"2026-11-20\"}','2026-11-20 03:26:12','2026-11-20 06:56:42'),
(13,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Сайт-визитка',27000.00,'2024-09-30','2024-09-18','Завершён','{\"phone\":\"+7 99090572269\",\"projectType\":\"Сайт-визитка\",\"deadline\":\"2024-09-30\",\"completedAt\":\"\",\"paidAmount\":0,\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"source\":\"Форма заказа на сайте\",\"contactPerson\":\"Валентин Александрович Осотов\",\"projectTitle\":\"Сайт-визитка\",\"orderDate\":\"2024-09-18\",\"status\":\"Завершён\",\"technicalBrief\":\"фывфы\",\"budget\":27000,\"budgetDisplay\":\"27 000 ₽\",\"deadlineFrom\":\"2024-09-18\",\"paymentHistory\":\"\",\"notes\":\"\",\"calculatorDetails\":\"{\\\"type\\\":\\\"Сайт-визитка\\\",\\\"features\\\":[{\\\"name\\\":\\\"До 5 страниц\\\",\\\"price\\\":15000,\\\"days\\\":7},{\\\"name\\\":\\\"Адаптивная вёрстка\\\",\\\"price\\\":5000,\\\"days\\\":3},{\\\"name\\\":\\\"Форма обратной связи\\\",\\\"price\\\":3000,\\\"days\\\":2},{\\\"name\\\":\\\"SEO-базовый\\\",\\\"price\\\":4000,\\\"days\\\":3}],\\\"price\\\":27000,\\\"currency\\\":\\\"₽\\\",\\\"days\\\":15}\",\"id\":13,\"client\":\"Валентин Александрович Осотов\",\"type\":\"Сайт-визитка\",\"createdAt\":\"2024-09-18\"}','2024-09-18 05:54:24','2026-11-20 06:53:25'),
(14,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Интернет-магазин',89000.00,'2024-10-30','2024-09-18','Завершён','{\"phone\":\"+7 99090572269\",\"projectType\":\"Интернет-магазин\",\"deadline\":\"2024-10-30\",\"completedAt\":\"\",\"paidAmount\":0,\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"source\":\"Форма заказа на сайте\",\"contactPerson\":\"\",\"projectTitle\":\"Интернет-магазин\",\"orderDate\":\"2024-09-18\",\"status\":\"Завершён\",\"technicalBrief\":\"ыфвфыв\",\"budget\":89000,\"budgetDisplay\":\"89 000 ₽\",\"deadlineFrom\":\"2024-09-18\",\"paymentHistory\":\"\",\"notes\":\"\",\"calculatorDetails\":\"{\\\"type\\\":\\\"Интернет-магазин\\\",\\\"features\\\":[{\\\"name\\\":\\\"Каталог товаров\\\",\\\"price\\\":40000,\\\"days\\\":20},{\\\"name\\\":\\\"Корзина и оформление\\\",\\\"price\\\":15000,\\\"days\\\":10},{\\\"name\\\":\\\"Онлайн-оплата\\\",\\\"price\\\":12000,\\\"days\\\":8},{\\\"name\\\":\\\"Управление складом\\\",\\\"price\\\":10000,\\\"days\\\":7},{\\\"name\\\":\\\"Личный кабинет\\\",\\\"price\\\":12000,\\\"days\\\":8}],\\\"price\\\":89000,\\\"currency\\\":\\\"₽\\\",\\\"days\\\":53}\",\"id\":14,\"client\":\"Валентин Александрович Осотов\",\"type\":\"Интернет-магазин\",\"createdAt\":\"2024-09-18\"}','2024-09-18 05:55:59','2026-11-20 05:57:45'),
(15,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Сайт-визитка',27000.00,'2024-09-13','2024-01-18','Завершён','{\"phone\":\"+7 99090572269\",\"projectType\":\"Сайт-визитка\",\"deadline\":\"2024-09-13\",\"completedAt\":\"2024-10-20\",\"paidAmount\":0,\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"source\":\"Форма заказа на сайте\",\"address\":\"\",\"contactPerson\":\"Валентин Александрович Осотов\",\"companyName\":\"\",\"companyInn\":\"\",\"companyOgrn\":\"\",\"companyContactPosition\":\"\",\"companyEmail\":\"\",\"companyPhone\":\"\",\"companyAddress\":\"\",\"projectTitle\":\"Сайт-визитка\",\"orderDate\":\"2024-01-18\",\"status\":\"Завершён\",\"projectUrl\":\"\",\"cancelReason\":\"\",\"technicalBrief\":\"ыфвфыв\",\"budget\":27000,\"lastPaymentDate\":\"\",\"paymentMethod\":\"\",\"paymentHistory\":\"\",\"notes\":\"\",\"id\":15,\"client\":\"Валентин Александрович Осотов\",\"type\":\"Сайт-визитка\",\"createdAt\":\"2024-01-18\"}','2025-03-18 05:56:41','2026-11-20 06:54:33'),
(16,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Сайт-визитка',27000.00,'2025-01-20','2026-11-20','Новый','{\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"phone\":\"+7 99090572269\",\"source\":\"Форма заказа на сайте\",\"contactPerson\":\"Валентин Александрович Осотов\",\"projectTitle\":\"Сайт-визитка\",\"projectType\":\"Сайт-визитка\",\"orderDate\":\"2026-11-20\",\"deadline\":\"2025-01-20\",\"status\":\"Новый\",\"technicalBrief\":\"ываыва\",\"budget\":27000,\"budgetDisplay\":\"27 000 ₽\",\"deadlineFrom\":\"2024-01-20\",\"paidAmount\":0,\"paymentHistory\":\"\",\"notes\":\"\",\"calculatorDetails\":\"{\\\"type\\\":\\\"Сайт-визитка\\\",\\\"features\\\":[{\\\"name\\\":\\\"До 5 страниц\\\",\\\"price\\\":15000,\\\"days\\\":7},{\\\"name\\\":\\\"Адаптивная вёрстка\\\",\\\"price\\\":5000,\\\"days\\\":3},{\\\"name\\\":\\\"Форма обратной связи\\\",\\\"price\\\":3000,\\\"days\\\":2},{\\\"name\\\":\\\"SEO-базовый\\\",\\\"price\\\":4000,\\\"days\\\":3}],\\\"price\\\":27000,\\\"currency\\\":\\\"₽\\\",\\\"days\\\":15}\"}','2026-11-20 07:59:45','2026-11-20 07:59:45'),
(17,'Валентин Александрович Осотов','osvax@yandex.ru','+7 99090572269','Сайт-портфолио',46000.00,'2026-11-30','2026-11-20','Новый','{\"clientName\":\"Валентин Александрович Осотов\",\"email\":\"osvax@yandex.ru\",\"phone\":\"+7 99090572269\",\"source\":\"Форма заказа на сайте\",\"contactPerson\":\"Валентин Александрович Осотов\",\"projectTitle\":\"Сайт-портфолио\",\"projectType\":\"Сайт-портфолио\",\"orderDate\":\"2026-11-20\",\"deadline\":\"2026-11-30\",\"status\":\"Новый\",\"technicalBrief\":\"Нужен сайт\",\"budget\":46000,\"budgetDisplay\":\"46 000 ₽\",\"deadlineFrom\":\"2026-11-20\",\"paidAmount\":0,\"paymentHistory\":\"\",\"notes\":\"\",\"calculatorDetails\":\"{\\\"type\\\":\\\"Сайт-портфолио\\\",\\\"features\\\":[{\\\"name\\\":\\\"До 10 страниц\\\",\\\"price\\\":25000,\\\"days\\\":10},{\\\"name\\\":\\\"Галерея работ\\\",\\\"price\\\":8000,\\\"days\\\":5},{\\\"name\\\":\\\"Фильтрация по категориям\\\",\\\"price\\\":6000,\\\"days\\\":4},{\\\"name\\\":\\\"Анимации и эффекты\\\",\\\"price\\\":7000,\\\"days\\\":5}],\\\"price\\\":46000,\\\"currency\\\":\\\"₽\\\",\\\"days\\\":24}\"}','2026-11-20 15:09:14','2026-11-20 15:09:14');
/*!40000 ALTER TABLE `va_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_portfolio_projects`
--

DROP TABLE IF EXISTS `va_portfolio_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_portfolio_projects` (
  `id` char(32) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `title` varchar(240) NOT NULL,
  `technologies` varchar(500) NOT NULL DEFAULT '',
  `icon` varchar(80) NOT NULL DEFAULT 'bi-briefcase',
  `description` mediumtext NOT NULL,
  `task_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`task_json`)),
  `solution` mediumtext DEFAULT NULL,
  `result` mediumtext DEFAULT NULL,
  `client` varchar(180) NOT NULL DEFAULT '',
  `project_year` smallint(5) unsigned DEFAULT NULL,
  `duration` varchar(120) NOT NULL DEFAULT '',
  `stack` varchar(500) NOT NULL DEFAULT '',
  `results_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`results_json`)),
  `screenshots_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`screenshots_json`)),
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_portfolio_projects_slug` (`slug`),
  KEY `idx_portfolio_publication` (`is_published`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_portfolio_projects`
--

LOCK TABLES `va_portfolio_projects` WRITE;
/*!40000 ALTER TABLE `va_portfolio_projects` DISABLE KEYS */;
INSERT INTO `va_portfolio_projects` VALUES
('0b8d8524604c84a6dbede14544d95d66','project-photo','Портфолио фотографа','Галерея, лайтбокс','bi-camera','Сайт-визитка свадебного и портретного фотографа. Главная задача сайта — показать работы так, чтобы фотографии говорили сами за себя: галерея занимает большую часть экранов, а текстовые блоки сведены к минимуму.\n\nСайт решает и практическую задачу: посетитель может посмотреть портфолио, ознакомиться с пакетами услуг и ценами и сразу оставить заявку на свободную дату съёмки.','[\"Сделать галерею с фильтром по жанрам съёмки\",\"Реализовать лайтбокс с полноэкранным просмотром фото\",\"Сверстать блок тарифов и форму заявки на дату\",\"Оптимизировать загрузку тяжёлых фотографий\"]','Галерея построена на адаптивной сетке Bootstrap 5 с фильтрацией по жанрам на клиенте. Для полноэкранного просмотра реализован лайтбокс с навигацией стрелками и жестами, без тяжёлых внешних плагинов. Фотографии конвертированы в WebP и отдаются в нескольких размерах через атрибут srcset — на смартфонах грузится уменьшенная копия.','Портфолио демонстрирует работы в едином визуальном стиле и помогает посетителям быстрее перейти к заявке на съёмк','фотограф Анна Смирнова',2024,'3 недели','HTML5, CSS3, Bootstrap 5, WebP, srcset','[{\"value\":\"300+\",\"label\":\"фотографий в галерее\"},{\"value\":\"+50%\",\"label\":\"заявок на съёмку за 3 месяца\"},{\"value\":\"100\",\"label\":\"балл PageSpeed на десктопе\"}]','[\"uploads/images/portfolio/photo-galereia-rabot.png\",\"uploads/images/portfolio/photo-sait-vizitka.png\",\"uploads/images/portfolio/photo-tarify-siomki.png\"]',1,'2026-11-15 20:48:31','2026-11-23 02:05:02'),
('917adbac97bfed8fa0ee05ed92f532c8','project-community','Сайт сообщества','UI/UX, прототипирование','bi-people','Сайт городского сообщества любителей настольных игр: каталог клубов, календарь встреч и мероприятий, профили участников и форум. Проект начинался с полного цикла UI/UX — от исследования аудитории до интерактивного прототипа, а затем перешёл в разработку.\n\nАудитория самая разная — от школьников до людей старшего возраста, поэтому интерфейс сделан максимально простым и понятным: крупные элементы управления, понятные подписи и минимум вложенности.','[\"Провести интервью с участниками сообщества и собрать требования\",\"Спроектировать прототип ключевых экранов в Figma\",\"Сверстать календарь событий и карточки клубов\",\"Реализовать форму записи на мероприятие\"]','Прототип был протестирован на пяти участниках сообщества: по итогам переработали навигацию — объединили разделы «Клубы» и «Карта» в один экран с фильтрами. Вёрстка выполнена на Bootstrap 5: календарь собран на адаптивной сетке, форма записи — на штатных компонентах форм с валидацией.','Сообщество получило удобный сценарий публикации событий и общения участников, что упростило регулярное возвращение пользователей.','сообщество «Игровой вечер»',2025,'8 недель','Figma, HTML5, CSS3, Bootstrap 5','[{\"value\":\"2 500+\",\"label\":\"участников сообщества\"},{\"value\":\"40+\",\"label\":\"мероприятий в месяц\"},{\"value\":\"4,8\\/5\",\"label\":\"оценка удобства от пользователей\"}]','[\"uploads/images/portfolio/community-glavnaja-stranica.png\",\"uploads/images/portfolio/community-kluby.png\",\"uploads/images/portfolio/community-kalendar.png\"]',1,'2026-11-15 20:48:31','2026-11-23 02:02:11'),
('a640d5135aa401b3e96d644b558bc84a','project-blog','Корпоративный блог','Bootstrap 5, Sass','bi-journal-richtext','Корпоративный блог IT-компании, в котором сотрудники публикуют статьи о технологиях, внутренних процессах и корпоративной культуре. Блог стал частью HR-бренда компании: через него соискатели знакомятся с командой и атмосферой внутри.\n\nДизайн выдержан в фирменных цветах компании, при этом сохранена единая визуальная система со светлым фоном, крупной типографикой и оранжевыми акцентами для кнопок и ссылок.','[\"Сделать удобный каталог статей с рубриками и тегами\",\"Добавить поиск по публикациям\",\"Сверстать шаблон статьи с галереей и врезками\",\"Подключить комментарии и кнопки «Поделиться»\"]','Проект собран на Bootstrap 5 с использованием Sass: переменные и миксины позволили быстро переопределить стандартную тему Bootstrap под фирменный стиль заказчика. Поиск по статьям работает мгновенно на клиенте — статей меньше сотни, поэтому index строится прямо в браузере. Шаблон статьи поддерживает оглавление с якорными ссылками и автоматические врезки с цитатами.','Публикации стали удобнее для чтения и поиска: контент организован по рубрикам, а ключевые показатели вовлечённости видны владельцу проекта.','IT-компания «Кодер»',2025,'4 недели','HTML5, Sass, Bootstrap 5, jQuery','[{\"value\":\"80+\",\"label\":\"статей опубликовано\"},{\"value\":\"×2\",\"label\":\"рост времени на сайте\"},{\"value\":\"98\",\"label\":\"балл PageSpeed\"}]','[\"uploads/images/portfolio/blog-glavnaja-stranica.png\",\"uploads/images/portfolio/blog-stranica-stati.png\",\"uploads/images/portfolio/blog-rubriki-i-tegi.png\"]',1,'2026-11-15 20:48:31','2026-11-23 02:02:11'),
('acbc1fcd28f38f703dbdab2462bdb33d','project-forma-studio','FORMA STUDIO — Лендинг архитектурной студии','HTML5, CSS3 (Custom Properties, Grid, Flex, clamp), Vanilla JS (ES2017+), Sass (Dart Sass), БЭМ, WCAG 2.1 AA. Без зависимостей и сборщиков.','bi-briefcase','FORMA STUDIO — адаптивный лендинг архитектурного бюро, разработанный по индивидуальному дизайну в Figma. Проект демонстрирует портфолио студии, перечень услуг, этапы работы с клиентом и предоставляет две точки конверсии: подписку на каталог проектов и форму заявки на консультацию.\n\nКлючевые особенности:\nПиксель-перфектная вёрстка по макету 1920×7525 px с соблюдением всех визуальных отступов, типографики и декоративных элементов\nПолностью адаптивный интерфейс (7 брейкпоинтов: 320–1920 px) без горизонтального скролла на любом устройстве\nДве интерактивные формы с клиентской валидацией, инлайн-ошибками и success-уведомлениями (без бэкенда)\nГоризонтальная карусель проектов с поддержкой свайпа, drag-and-drop мышью, клавиатурной навигацией и пагинацией\nReveal-анимации при скролле (IntersectionObserver, stagger 60 мс) с респектом prefers-reduced-motion\nДоступность WCAG 2.1 AA: семантическая HTML5-разметка, ARIA-атрибуты, фокус-стили, skip-link\nЧистый стек: HTML5 + CSS3 (Custom Properties, Grid, Flex, clamp) + Vanilla JS (ES2017+) + Sass — без сборщиков и зависимостей\nИсходники SCSS (21 файл, модульная структура) переданы заказчику для дальнейшего масштабирования дизайн-системы\n\nЦелевая аудитория: частные заказчики (строительство/ремонт), застройщики, дизайнеры интерьеров, архитектурные бюро-партнёры.\n\nБизнес-задача: презентация экспертизы студии, генерация лидов через формы, демонстрация портфолио реализованных объектов.','[\"Сверстать адаптивный лендинг архитектурного бюро FORMA STUDIO по готовому дизайн-макету (файл Architecture.fig, Figma, десктоп 1920×7525 px).\",\"Требования:\",\"Пиксель-перфектная вёрстка по макету (десктоп 1920 px);\",\"Адаптивность спроектировать самостоятельно (мобильные, планшеты, ноутбуки, широкие экраны);\",\"Все тексты перевести на русский язык (бренд «FORMA STUDIO» — оставить латиницей);\",\"Чистый HTML5 + CSS3 + Vanilla JS без сборщиков, фреймворков и библиотек;\",\"Методология БЭМ, семантическая разметка, доступность (WCAG 2.1 AA);\",\"Показать результат в браузере, сохранить готовые файлы в папке проекта;\",\"Подготовить исходники SCSS для дальнейшего редактирования заказчиком.\"]','Анализ макета\nFIG-макет (Figma) — одностраничный растр 1920×7525 px. Дизайн разобран визуально на 7 смысловых срезов, все изображения экспортированы в 2× качестве (Retina-ready).\nСтруктура страницы (8 секций)\nHero — главный экран с фиксированной шапкой, H1, описанием и CTA-кнопкой;\nServices — 4 строки услуг с разделителями на тёмном фоне;\nProjects — горизонтальная карусель из 4 карточек (4-я обрезана краем, как в макете), навигация стрелками, свайп, drag мышью;\nSteps — 8 этапов работы с нумерацией и вертикальной линией + фото интерьера;\nCatalog — заголовок и фото слева, форма подписки с чекбоксом согласия справа;\nGallery — сетка 4×2 интерьеров с зазором 20 px;\nCTA — баннер с фоновым фото, полупрозрачной панелью и формой заявки;\nFooter — 5 колонок: логотип, навигация, услуги, контакты, адрес.\nАдаптивность (6 брейкпоинтов)\n1920 / 1440 / 1200 / 1024 / 768 / 480 / 320 px.\nКонтейнер 1720 px, боковые отступы clamp(20px, 5.2vw, 100px).\nНавигация ≤1024 px — бургер-меню с полноэкранным оверлеем.\nКарусель: 3 карточки ≥1200 px, 2 карточки 768–1199 px, 1 карточка ≤767 px.\nГалерея: 4 / 2 / 1 колонка.\nВсе шрифтовые размеры — clamp() для плавного масштабирования.\nИнтерактив (Vanilla JS, ~300 строк)\nФиксированная шапка: фон и тень при scrollY > 40 px;\nБургер: открытие/закрытие, блокировка скролла body, закрытие по Esc и по ссылке;\nПлавные якоря с учётом высоты шапки;\nКарусель: кнопки Prev/Next (disabled в крайних позициях), свайп, drag мышью;\nReveal-анимации через IntersectionObserver (stagger 60 мс);\nВалидация формы каталога: e-mail + чекбокс, инлайн-ошибки, сообщение об успехе;\nВалидация CTA-формы: e-mail, инлайн-ошибка, успех с автоскрытием через 8 с;\nПодсветка активного пункта меню при скролле.\nИзображения\n16 файлов, экспортированные из Figma: hero.jpg, project-1..4.jpg, steps-interior.jpg, catalog.jpg, gallery-1..8.jpg, cta-house.jpg.\n4-я карточка проектов собрана зеркальным отражением gallery-2.jpg.\nSCSS-источники (папка scss/, 21 файл)\nabstracts/_variables.scss — все дизайн-токены (цвета, шрифты, сетка, брейкпоинты);\nabstracts/_mixins.scss — mq(), card-width();\nbase/ — сброс, типографика, анимации, CSS-переменные;\ncomponents/ — по файлу на блок (header, hero, services, projects, steps, catalog, forms, gallery, cta, footer);\nutils/_responsive.scss — весь адаптив в одном месте;\nКомпиляция: sass scss/style.scss css/style.css --style=expanded.','Что получено заказчиком:\nГотовый к деплою лендинг (index.html, css/style.css, js/app.js, images/) — работает без сборки, открывается двойным кликом.\nИсходники SCSS (scss/, 21 файл) — модульная структура, понятные переменные и миксины для дальнейшего масштабирования дизайн-системы.\nПолная адаптивность на 7 брейкпоинтах (320–1920 px) без горизонтального скролла.\nДоступность WCAG 2.1 AA: семантика, фокус, ARIA, skip-link, prefers-reduced-motion.\nПроизводительность: Lighthouse Desktop 98/100, Mobile 92/100 (Performance).\n4 качественных скриншота для портфолио (3 десктоп, 1 планшет с открытым меню).\nДокументация README.md с инструкцией по запуску и компиляции SCSS.\n\nКлючевые метрики:\n| Метрика | Значение |\n|---------|----------|\n| Размер HTML + CSS + JS (gz) | ~45 КБ |\n| Количество HTTP-запросов (без картинок) | 3 |\n| Время интерактивности (TTI) | < 1.2 с |\n| Lighthouse Accessibility | 100/100 |\n| Поддержка браузеров | Chrome 90+, Firefox 88+, Safari 14+, Edge 90+ |\n\nГотовность к передаче: 100% — проект не требует доработок, готов к хостингу на любом статическом хостинге (Netlify, Vercel, GitHub Pages, VPS).','',2025,'3 дня','HTML5, CSS3 (Custom Properties, Grid, Flex, clamp), Vanilla JS (ES2017+), Sass (Dart Sass), БЭМ, WCAG 2.1 AA. Без зависимостей и сборщиков.','[]','[\"uploads/images/portfolio/project-forma-studio-c16990de47c07bd571fce2fd6fd3e2e8cd6d5261751762f78ef7d7ecaed1c893.png\",\"uploads/images/portfolio/project-forma-studio-78755252c1f12f4bac806c77a231a66fe1ae10cae468d4540ea1ffa7ef56459e.png\",\"uploads/images/portfolio/project-forma-studio-87ff539de267a898404f4d1b144aad4f32f1142435293a419b5335c037b2a076.png\",\"uploads/images/portfolio/project-forma-studio-89d6e301685e6afb21c5f48b9bd4799bf49fbd705fdb9afe556757316de50e10.png\"]',1,'2026-11-23 02:16:33','2026-11-23 02:16:33'),
('c1c7a55926106038bd552b6fd8045621','project-shop','Интернет-магазин','HTML, CSS, Bootstrap','bi-shop','Полноценный интернет-магазин товаров для дома, разработанный по заказу небольшой торговой компании. Магазин позволяет посетителям просматривать каталог, фильтровать товары по категориям и цене, добавлять их в корзину и оформлять заказ без перезагрузки страницы.\n\nИнтерфейс выполнен в лаконичном стиле с акцентом на товары: крупные фотографии, понятные карточки и удобная навигация по категориям. Вёрстка полностью адаптивная — магазин одинаково хорошо работает на смартфонах, планшетах и настольных компьютерах.','[\"Разработать каталог товаров с фильтрацией и сортировкой\",\"Реализовать корзину с пересчётом суммы заказа в реальном времени\",\"Сделать многошаговую форму оформления заказа\",\"Обеспечить скорость загрузки не более 2 секунд\"]','Сетка и компоненты построены на Bootstrap 5, что позволило быстро собрать адаптивный каталог и карточки товаров. Корзина реализована на чистом JavaScript с сохранением состояния в localStorage — заказ не теряется при перезагрузке страницы. Изображения товаров оптимизированы в формате WebP и подгружаются лениво (lazy loading), что заметно ускорило первую загрузку каталога.','Интернет-магазин получил понятный путь от каталога до оформления заказа, а обновлённая структура страниц создала основу для дальнейшего роста продаж.','ООО «ДомТорг»',2026,'6 недель','HTML5, CSS3, Bootstrap 5, JavaScript','[{\"value\":\"1,8 с\",\"label\":\"средняя загрузка страницы\"},{\"value\":\"+35%\",\"label\":\"рост конверсии в заказ\"},{\"value\":\"120+\",\"label\":\"товаров в каталоге\"}]','[\"uploads/images/portfolio/shop-glavnaja-stranica.png\",\"uploads/images/portfolio/shop-katalog-tovarov.png\",\"uploads/images/portfolio/shop-korzina-zakaza.png\"]',1,'2026-11-15 20:48:31','2026-11-23 02:02:11'),
('dbdce8309fa669406cc585c22d5f8eff','project-landing','Лендинг приложения','Адаптивная вёрстка','bi-phone','Продающий лендинг для мобильного приложения-трекера привычек. Целевая аудитория — пользователи смартфонов, поэтому основной упор сделан на мобильную версию: крупные кнопки скачивания, короткие блоки и динамичные иллюстрации экранов приложения.\n\nСтруктура страницы построена по классической воронке: экран с оффером — преимущества — скриншоты — отзывы — тарифы — FAQ — финальный призыв.','[\"Сделать мобильную версию приоритетной (mobile-first вёрстка)\",\"Добавить анимации появления блоков при прокрутке\",\"Сверстать блок тарифов и FAQ-аккордеон\",\"Интегрировать счётчики аналитики и цель «Скачать приложение»\"]','Вёрстка выполнена mobile-first: сначала экран смартфона, затем наращивание блоков для планшетов и десктопа через брейкпоинты Bootstrap. Анимации реализованы лёгким скриптом с IntersectionObserver — блоки плавно появляются при попадании в область видимости без сторонних библиотек. FAQ собран на штатном аккордеоне Bootstrap 5.','Посадочная страница быстрее объясняет ценность приложения и направляет посетителя к установке без лишних шагов.','студия «Хаббит»',2025,'2 недели','HTML5, CSS3, Bootstrap 5, IntersectionObserver','[{\"value\":\"12%\",\"label\":\"конверсия в установку\"},{\"value\":\"50 000+\",\"label\":\"установок за первый месяц\"},{\"value\":\"0,9 с\",\"label\":\"загрузка на мобильном интернете\"}]','[\"uploads/images/portfolio/landing-pervyj-ekran.png\",\"uploads/images/portfolio/landing-tarify.png\",\"uploads/images/portfolio/landing-faq.png\"]',1,'2026-11-15 20:48:31','2026-11-23 02:02:11'),
('de0eab333ff795f3997e7627905eb513','project-agroprospect-master','АГРОПРОСПЕКТ — дилер сельскохозяйственной техники','HTML5, SCSS/Sass (Dart Sass), CSS3, jQuery 3.3.1, Owl Carousel, Picturefill, БЭМ, адаптивная вёрстка, PT Sans, PNG/JPG.','bi-briefcase','Многостраничный сайт дилерского центра сельскохозяйственной техники «Агропроспект» из 5 страниц: главная, «О компании», «Запчасти», «Импортная техника» и страница каталога «Зерновые сеялки». Сайт презентует направления бизнеса — прямые поставки, сервис, запчасти и импортную технику — и приводит заявки через модальную форму «Заказать звонок».','[\"Перевёрстка существующего многостраничного сайта с обновлением архитектуры фронтенда: внедрить SCSS, привести разметку всех 5 страниц к БЭМ и изолировать стили уникальным префиксом va_, сохранив визуальную концепцию, весь контент, исходные изображения и шрифты, а также весь интерактив проекта.\"]','Проведена модульная декомпозиция стилей: 23 SCSS-файла — переменные, миксины, шрифты, reset, контейнер, сетка, 16 компонентов (шапка, навигация, hero-слайдер, преимущества, презентации, схема работы, таблицы, подвал, модалка, формы и др.) и утилиты.\nВсе 5 HTML-страниц перевёрстаны на единые BEM-классы с префиксом va_; добавлены осмысленные title/description/favicon.\nJavaScript переписан с нуля: плавные якорные переходы, выпадающий каталог техники, полноэкранное мобильное меню с каталогом внутри, слайдеры hero и обложек (Owl Carousel), модальная форма с автофокусом и закрытием по Esc/клику вне.\nИсправлены ошибки оригинала: бургер и close-кнопка выходили за экран на мобильных, скрытое подменю каталога занимало место в потоке, клик по href=\"#\" рвал скрипт, мобильная таблица была видна на всех экранах.\nСохранена жидкая типографика оригинала (em/vw) и расширена сетка брейкпоинтов до 360 px; добавлены :focus-visible, ARIA-состояния меню и модалки, блокировка скролла и градиент-оверлей на hero для читаемости текста.','Получен масштабируемый многостраничный сайт с чистой SCSS-архитектурой и изолированными BEM-классами. Все сценарии — слайдеры, каталог, мобильное меню, модалка, плавный скролл — работают без ошибок консоли на desktop, tablet и mobile. Проект готов к размещению на статическом хостинге и дальнейшему расширению.','',2026,'5 рабочих дней','HTML5, SCSS/Sass (Dart Sass), CSS3, jQuery 3.3.1, Owl Carousel, Picturefill, БЭМ, адаптивная вёрстка, PT Sans, PNG/JPG.','[]','[\"uploads/images/portfolio/project-agroprospect-master-5b152c2e43e9a06f575fc2f0241075bddd693c2471fadb42d785662cb9acb519.png\",\"uploads/images/portfolio/project-agroprospect-master-652091fa6dadbd2f577a0e1f7e28acd8f6b898d471dcc9759f1f9f26536fbc8d.png\",\"uploads/images/portfolio/project-agroprospect-master-9094fddd17f9a07755f9b32350c2c606dc447ffd1d9cb22f2cb64631fca7290d.png\",\"uploads/images/portfolio/project-agroprospect-master-747885c9420c458441fe7f7aea7f56c8864daa105047556d1ea330dc4955eb60.png\"]',1,'2026-11-23 02:16:33','2026-11-23 02:16:33'),
('f23f8f2734893187d52a32a0e1bb4ee7','project-textile','TEXTILE — студия текстильного дизайна','HTML5, SCSS/Sass, CSS3, JavaScript ES6+, jQuery-плагины исходного проекта, БЭМ, адаптивная вёрстка, SVG/JPG.','bi-briefcase','Одностраничный сайт студии текстильного дизайна интерьеров. Сайт показывает подход команды, портфолио работ, направления текстильного оформления, отзывы клиентов и помогает получить консультацию.','[\"Перевёрстка существующего лендинга с обновлением архитектуры фронтенда: внедрить SCSS, привести разметку к БЭМ и изолировать стили уникальным префиксом va_, сохранив визуальную концепцию, контент и интерактивность проекта.\"]','Проведена модульная декомпозиция стилей на базовые настройки, переменные, миксины, компоненты и адаптивные правила.\nКлассы организованы по БЭМ и имеют единый namespace va_.\nСохранены фотогалерея, карточки услуг и профилей тканей, отзывы, калькулятор, формы и мобильное меню.\nПодготовлена адаптивная сетка для мобильных устройств, планшетов и широких экранов.\nДобавлены требования к доступности: семантическая разметка, фокус с клавиатуры, ARIA-состояния и поддержка prefers-reduced-motion.','Получен масштабируемый лендинг студии текстильного дизайна с понятной SCSS-структурой и изолированными BEM-классами. Проект готов к дальнейшему расширению, локальному запуску и размещению на статическом хостинге.','',2026,'4 рабочих дня','HTML5, SCSS/Sass, CSS3, JavaScript ES6+, jQuery-плагины исходного проекта, БЭМ, адаптивная вёрстка, SVG/JPG.','[]','[\"uploads/images/portfolio/project-textile-2ce258119f05749011dbfb261c9a2e4da31d69adef456cd60ac455dfffc693bd.png\",\"uploads/images/portfolio/project-textile-ae5fe4a8ba478a5538fdbf65b97787b583800fd868fd4db60b563f8adb9af9bc.png\",\"uploads/images/portfolio/project-textile-a85268756f4b254c57dd7672b70817e598dd96a9a52b8631ebf398d8b65f3f5c.png\",\"uploads/images/portfolio/project-textile-b9ebb8214909407bd3d5a22c74904ecc972148690aa009fb79a4401256053258.png\"]',1,'2026-11-23 02:16:33','2026-11-23 02:16:33');
/*!40000 ALTER TABLE `va_portfolio_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_project_gallery`
--

DROP TABLE IF EXISTS `va_project_gallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_project_gallery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` char(32) NOT NULL,
  `path` varchar(255) NOT NULL,
  `description` varchar(500) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_project_gallery_project` (`project_id`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_project_gallery`
--

LOCK TABLES `va_project_gallery` WRITE;
/*!40000 ALTER TABLE `va_project_gallery` DISABLE KEYS */;
INSERT INTO `va_project_gallery` VALUES
(61,'c1c7a55926106038bd552b6fd8045621','uploads/images/portfolio/shop-glavnaja-stranica.png','Главная страница магазина',0,'2026-11-19 03:30:47'),
(62,'c1c7a55926106038bd552b6fd8045621','uploads/images/portfolio/shop-katalog-tovarov.png','Каталог товаров с фильтрами',1,'2026-11-19 03:30:47'),
(63,'c1c7a55926106038bd552b6fd8045621','uploads/images/portfolio/shop-korzina-zakaza.png','Корзина с пересчётом суммы заказа',2,'2026-11-19 03:30:47'),
(64,'a640d5135aa401b3e96d644b558bc84a','uploads/images/portfolio/blog-glavnaja-stranica.png','Главная страница с карточками статей',0,'2026-11-19 03:30:47'),
(65,'a640d5135aa401b3e96d644b558bc84a','uploads/images/portfolio/blog-stranica-stati.png','Страница статьи с врезками и цитатами',1,'2026-11-19 03:30:47'),
(66,'a640d5135aa401b3e96d644b558bc84a','uploads/images/portfolio/blog-rubriki-i-tegi.png','Облако рубрик и тегов',2,'2026-11-19 03:30:47'),
(67,'dbdce8309fa669406cc585c22d5f8eff','uploads/images/portfolio/landing-pervyj-ekran.png','Первый экран с оффером и кнопкой скачивания',0,'2026-11-19 03:30:47'),
(68,'dbdce8309fa669406cc585c22d5f8eff','uploads/images/portfolio/landing-tarify.png','Блок тарифов с акцентом на популярный план',1,'2026-11-19 03:30:47'),
(69,'dbdce8309fa669406cc585c22d5f8eff','uploads/images/portfolio/landing-faq.png','FAQ-аккордеон с ответами на вопросы',2,'2026-11-19 03:30:47'),
(70,'917adbac97bfed8fa0ee05ed92f532c8','uploads/images/portfolio/community-glavnaja-stranica.png','Главная страница сообщества',0,'2026-11-19 03:30:47'),
(71,'917adbac97bfed8fa0ee05ed92f532c8','uploads/images/portfolio/community-kluby.png','Каталог клубов с фильтрами',1,'2026-11-19 03:30:47'),
(72,'917adbac97bfed8fa0ee05ed92f532c8','uploads/images/portfolio/community-kalendar.png','Календарь мероприятий на месяц',2,'2026-11-19 03:30:47'),
(119,'0b8d8524604c84a6dbede14544d95d66','uploads/images/portfolio/photo-galereia-rabot.png','Галерея работ с фильтрами по жанрам',0,'2026-11-23 02:05:02'),
(120,'0b8d8524604c84a6dbede14544d95d66','uploads/images/portfolio/photo-sait-vizitka.png','Главный экран сайта-визитки',1,'2026-11-23 02:05:02'),
(121,'0b8d8524604c84a6dbede14544d95d66','uploads/images/portfolio/photo-tarify-siomki.png','Тарифы на съёмку с формой заявки',2,'2026-11-23 02:05:02'),
(122,'de0eab333ff795f3997e7627905eb513','uploads/images/portfolio/project-agroprospect-master-5b152c2e43e9a06f575fc2f0241075bddd693c2471fadb42d785662cb9acb519.png','',0,'2026-11-23 02:16:33'),
(123,'de0eab333ff795f3997e7627905eb513','uploads/images/portfolio/project-agroprospect-master-652091fa6dadbd2f577a0e1f7e28acd8f6b898d471dcc9759f1f9f26536fbc8d.png','',1,'2026-11-23 02:16:33'),
(124,'de0eab333ff795f3997e7627905eb513','uploads/images/portfolio/project-agroprospect-master-9094fddd17f9a07755f9b32350c2c606dc447ffd1d9cb22f2cb64631fca7290d.png','',2,'2026-11-23 02:16:33'),
(125,'de0eab333ff795f3997e7627905eb513','uploads/images/portfolio/project-agroprospect-master-747885c9420c458441fe7f7aea7f56c8864daa105047556d1ea330dc4955eb60.png','',3,'2026-11-23 02:16:33'),
(126,'acbc1fcd28f38f703dbdab2462bdb33d','uploads/images/portfolio/project-forma-studio-c16990de47c07bd571fce2fd6fd3e2e8cd6d5261751762f78ef7d7ecaed1c893.png','',0,'2026-11-23 02:16:33'),
(127,'acbc1fcd28f38f703dbdab2462bdb33d','uploads/images/portfolio/project-forma-studio-78755252c1f12f4bac806c77a231a66fe1ae10cae468d4540ea1ffa7ef56459e.png','',1,'2026-11-23 02:16:33'),
(128,'acbc1fcd28f38f703dbdab2462bdb33d','uploads/images/portfolio/project-forma-studio-87ff539de267a898404f4d1b144aad4f32f1142435293a419b5335c037b2a076.png','',2,'2026-11-23 02:16:33'),
(129,'acbc1fcd28f38f703dbdab2462bdb33d','uploads/images/portfolio/project-forma-studio-89d6e301685e6afb21c5f48b9bd4799bf49fbd705fdb9afe556757316de50e10.png','',3,'2026-11-23 02:16:33'),
(130,'f23f8f2734893187d52a32a0e1bb4ee7','uploads/images/portfolio/project-textile-2ce258119f05749011dbfb261c9a2e4da31d69adef456cd60ac455dfffc693bd.png','',0,'2026-11-23 02:16:33'),
(131,'f23f8f2734893187d52a32a0e1bb4ee7','uploads/images/portfolio/project-textile-ae5fe4a8ba478a5538fdbf65b97787b583800fd868fd4db60b563f8adb9af9bc.png','',1,'2026-11-23 02:16:33'),
(132,'f23f8f2734893187d52a32a0e1bb4ee7','uploads/images/portfolio/project-textile-a85268756f4b254c57dd7672b70817e598dd96a9a52b8631ebf398d8b65f3f5c.png','',2,'2026-11-23 02:16:33'),
(133,'f23f8f2734893187d52a32a0e1bb4ee7','uploads/images/portfolio/project-textile-b9ebb8214909407bd3d5a22c74904ecc972148690aa009fb79a4401256053258.png','',3,'2026-11-23 02:16:33');
/*!40000 ALTER TABLE `va_project_gallery` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_project_info_items`
--

DROP TABLE IF EXISTS `va_project_info_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_project_info_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` char(32) NOT NULL,
  `kind` varchar(20) NOT NULL,
  `name` varchar(160) NOT NULL,
  `value` varchar(500) NOT NULL DEFAULT '',
  `file_path` varchar(255) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_project_info_items_project` (`project_id`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_project_info_items`
--

LOCK TABLES `va_project_info_items` WRITE;
/*!40000 ALTER TABLE `va_project_info_items` DISABLE KEYS */;
INSERT INTO `va_project_info_items` VALUES
(40,'c1c7a55926106038bd552b6fd8045621','link','Ссылка на проект','https://domtorg.ru','',0,'2026-11-19 03:30:47'),
(41,'c1c7a55926106038bd552b6fd8045621','document','Техническое задание','','uploads/documents/portfolio/shop-teh-zadanie.docx',1,'2026-11-19 03:30:47'),
(42,'c1c7a55926106038bd552b6fd8045621','certificate','Сертификат','','uploads/images/portfolio/shop-sertifikat.png',2,'2026-11-19 03:30:47'),
(43,'a640d5135aa401b3e96d644b558bc84a','link','Ссылка на проект','https://coder.ru/blog','',0,'2026-11-19 03:30:47'),
(44,'a640d5135aa401b3e96d644b558bc84a','document','Техническое задание','','uploads/documents/portfolio/blog-teh-zadanie.docx',1,'2026-11-19 03:30:47'),
(45,'a640d5135aa401b3e96d644b558bc84a','certificate','Сертификат','','uploads/images/portfolio/blog-sertifikat.png',2,'2026-11-19 03:30:47'),
(46,'dbdce8309fa669406cc585c22d5f8eff','link','Ссылка на проект','https://app.habbit.ru','',0,'2026-11-19 03:30:47'),
(47,'dbdce8309fa669406cc585c22d5f8eff','document','Техническое задание','','uploads/documents/portfolio/landing-teh-zadanie.docx',1,'2026-11-19 03:30:47'),
(48,'dbdce8309fa669406cc585c22d5f8eff','certificate','Сертификат','','uploads/images/portfolio/landing-sertifikat.png',2,'2026-11-19 03:30:47'),
(49,'917adbac97bfed8fa0ee05ed92f532c8','link','Ссылка на проект','https://igrovoy-vecher.ru','',0,'2026-11-19 03:30:47'),
(50,'917adbac97bfed8fa0ee05ed92f532c8','document','Техническое задание','','uploads/documents/portfolio/community-teh-zadanie.docx',1,'2026-11-19 03:30:47'),
(51,'917adbac97bfed8fa0ee05ed92f532c8','certificate','Сертификат','','uploads/images/portfolio/community-sertifikat.png',2,'2026-11-19 03:30:47'),
(63,'0b8d8524604c84a6dbede14544d95d66','link','Ссылка на проект','https://smirnova-photo.ru','',0,'2026-11-23 02:05:02'),
(64,'0b8d8524604c84a6dbede14544d95d66','document','Техническое задание','','uploads/documents/portfolio/photo-teh-zadanie.docx',1,'2026-11-23 02:05:02'),
(65,'0b8d8524604c84a6dbede14544d95d66','certificate','Сертификат','','uploads/images/portfolio/photo-sertifikat.png',2,'2026-11-23 02:05:02'),
(66,'de0eab333ff795f3997e7627905eb513','link','Демонстрация проекта','/portfolio/agroprospect-master/index.html','',0,'2026-11-23 02:16:33'),
(67,'de0eab333ff795f3997e7627905eb513','link','Техническое задание','/portfolio/agroprospect-master/job/TZ-template.pdf','',1,'2026-11-23 02:16:33'),
(70,'acbc1fcd28f38f703dbdab2462bdb33d','link','Демонстрация проекта','/portfolio/forma-studio/index.html','',0,'2026-11-23 02:16:33'),
(71,'acbc1fcd28f38f703dbdab2462bdb33d','link','Техническое задание','/portfolio/forma-studio/job/TZ-template.pdf','',1,'2026-11-23 02:16:33'),
(72,'f23f8f2734893187d52a32a0e1bb4ee7','link','Демонстрация проекта','/portfolio/textile/index.html','',0,'2026-11-23 02:16:33'),
(73,'f23f8f2734893187d52a32a0e1bb4ee7','link','Техническое задание','/portfolio/textile/job/TZ-template.pdf','',1,'2026-11-23 02:16:33');
/*!40000 ALTER TABLE `va_project_info_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_role_permissions`
--

DROP TABLE IF EXISTS `va_role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_role_permissions` (
  `role_id` bigint(20) unsigned NOT NULL,
  `page_slug` varchar(120) NOT NULL,
  PRIMARY KEY (`role_id`,`page_slug`),
  CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `va_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_role_permissions`
--

LOCK TABLES `va_role_permissions` WRITE;
/*!40000 ALTER TABLE `va_role_permissions` DISABLE KEYS */;
INSERT INTO `va_role_permissions` VALUES
(1,'blog'),
(1,'calculator'),
(1,'categories'),
(1,'consultant'),
(1,'current-orders'),
(1,'custom-fields'),
(1,'files'),
(1,'index'),
(1,'landing-pages'),
(1,'menus'),
(1,'messages'),
(1,'new-orders'),
(1,'orders'),
(1,'portfolio'),
(1,'roles'),
(1,'settings'),
(1,'statistics'),
(1,'templates'),
(1,'users'),
(2,'blog'),
(2,'categories'),
(2,'custom-fields'),
(2,'files'),
(2,'index'),
(2,'portfolio'),
(2,'profile'),
(3,'calculator'),
(3,'consultant'),
(3,'current-orders'),
(3,'index'),
(3,'new-orders'),
(3,'order-edit'),
(3,'orders'),
(3,'profile'),
(3,'user-edit'),
(5,'article-edit'),
(5,'blog'),
(5,'calculator'),
(5,'categories'),
(5,'consultant'),
(5,'current-orders'),
(5,'custom-fields'),
(5,'files'),
(5,'index'),
(5,'landing-pages'),
(5,'menus'),
(5,'messages'),
(5,'new-orders'),
(5,'order-edit'),
(5,'orders'),
(5,'portfolio'),
(5,'portfolio-edit'),
(5,'profile'),
(5,'roles'),
(5,'settings'),
(5,'statistics'),
(5,'templates'),
(5,'user-edit'),
(5,'users');
/*!40000 ALTER TABLE `va_role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_roles`
--

DROP TABLE IF EXISTS `va_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_key` varchar(64) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(500) NOT NULL DEFAULT '',
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_key` (`role_key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_roles`
--

LOCK TABLES `va_roles` WRITE;
/*!40000 ALTER TABLE `va_roles` DISABLE KEYS */;
INSERT INTO `va_roles` VALUES
(1,'admin','Администратор','Полный доступ к сайту и настройкам',1,'2026-11-18 00:47:10','2026-11-18 00:47:10'),
(2,'editor','Редактор','Управление контентом: статьи, рубрики, портфолио, файлы',1,'2026-11-18 00:47:10','2026-11-18 00:47:10'),
(3,'manager','Менеджер','Заказы, сообщения, консультант и калькулятор',1,'2026-11-18 00:47:10','2026-11-18 00:47:10'),
(4,'user','Пользователь','Без доступа к админке',1,'2026-11-18 00:47:10','2026-11-18 00:47:10'),
(5,'readonly_demo','Демонстрационный просмотр','Полный просмотр админ-панели без сохранения, создания и удаления данных',1,'2026-11-23 01:52:40','2026-11-23 01:52:40');
/*!40000 ALTER TABLE `va_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_site_settings`
--

DROP TABLE IF EXISTS `va_site_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_site_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(20) NOT NULL DEFAULT 'text' COMMENT 'text, number, checkbox, select, color, textarea, email, tel, url',
  `tab_group` varchar(50) NOT NULL DEFAULT 'general' COMMENT 'group: general, appearance, seo, notifications, consultant, contacts, security, email',
  `display_order` int(10) unsigned NOT NULL DEFAULT 0,
  `placeholder` varchar(254) DEFAULT '',
  `options` text DEFAULT NULL COMMENT 'JSON array for select',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_tab_group` (`tab_group`),
  KEY `idx_display_order` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=24961 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_site_settings`
--

LOCK TABLES `va_site_settings` WRITE;
/*!40000 ALTER TABLE `va_site_settings` DISABLE KEYS */;
INSERT INTO `va_site_settings` VALUES
(1,'siteName','Мой Блог','text','general',1,'Название сайта',NULL,'2026-11-16 00:14:25','2026-11-23 03:12:55'),
(2,'siteTitle','Мой Блог — Портфолио и блог','text','general',2,'Заголовок вкладки (SEO)',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(3,'siteDescription','Портфоли веб-разработчика. Блог о верстке, Bootstrap 5 и современных технологиях.','text','general',3,'SEO description',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(4,'adminEmail','osvax@yandex.ru','email','general',4,'Email администратора',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(5,'siteLanguage','Русский','text','general',5,'Язык сайта','[\"Русский\",\"English\"]','2026-11-16 00:14:26','2026-11-23 03:12:55'),
(6,'primaryColor','#f11b04','color','appearance',1,'Основной цвет',NULL,'2026-11-16 00:14:26','2026-11-17 18:35:23'),
(7,'headingFont','Montserrat','select','appearance',2,'Шрифт заголовков','[\"Montserrat\",\"Roboto\",\"Open Sans\"]','2026-11-16 00:14:26','2026-11-17 18:35:23'),
(8,'darkMode','1','checkbox','appearance',3,'Тёмная тема админки',NULL,'2026-11-16 00:14:26','2026-11-17 18:35:23'),
(9,'sidebarCollapse','0','checkbox','appearance',4,'Свёрнутый сайдбар по умолчанию',NULL,'2026-11-16 00:14:26','2026-11-17 18:35:23'),
(10,'keywords','блог, портфолио, верстка, bootstrap, html, css, javascript','text','seo',1,'Ключевые слова',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(11,'sitemap','1','checkbox','seo',2,'Генерировать sitemap.xml',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(12,'robots','1','checkbox','seo',3,'Разрешить индексацию',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(13,'notifyOrders','1','checkbox','notifications',1,'Новые заказы',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(14,'notifyMessages','1','checkbox','notifications',2,'Новые сообщения',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(15,'notifyComments','1','checkbox','notifications',3,'Новые комментарии',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(16,'notifyUsers','0','checkbox','notifications',4,'Регистрация пользователей',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(17,'consultantEnabled','1','checkbox','consultant',1,'Включить консультанта',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(18,'consultantWorkingHours','00:00-23:59','text','consultant',2,'Время работы',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(19,'consultantButtonColor','#1dff1a','color','consultant',3,'Цвет кнопки',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(20,'consultantButtonTextColor','#ffffff','color','consultant',4,'Цвет текста кнопки',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(21,'consultantButtonPosition','right','select','consultant',5,'Расположение кнопки','[\"right\",\"left\"]','2026-11-16 00:14:26','2026-11-23 03:12:55'),
(22,'consultantWelcomeMessage','Здравствуйте! Чем могу помочь?','text','consultant',6,'Приветствие',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(23,'consultantNotificationEmail','','email','consultant',7,'Email для уведомлений',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(24,'consultantCensoredWords','дурак, идиот, тупица','text','consultant',8,'Слова для цензуры',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(25,'contactAddress','г. Ижевск, ул. Ленина, 1','text','contacts',1,'Адрес',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(26,'contactHours','Пн-Пт: 9:00 — 18:00','text','contacts',2,'Часы работы',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(27,'contactEmail','hello@moyblog.ru','email','contacts',3,'Публичный email',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(28,'contactPhone','+7 (900) 123-45-67','tel','contacts',4,'Телефон',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(29,'socialTelegram','https://t.me/moyblog','url','contacts',5,'Telegram',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(30,'socialVk','https://vk.com/moyblog','url','contacts',6,'VK',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(31,'socialGithub','https://github.com/osvax/myblog','url','contacts',7,'GitHub',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(32,'socialYoutube','https://youtube.com/@moyblog','url','contacts',8,'YouTube',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(33,'siteStatus','online','select','security',1,'Режим сайта','[{\"value\":\"online\",\"label\":\"Работает\"},{\"value\":\"maintenance\",\"label\":\"Обслуживание (заглушка)\"}]','2026-11-16 00:14:26','2026-11-23 03:12:55'),
(34,'twoFactorMethod','off','select','security',2,'Двухфакторная аутентификация','[\"off\",\"authenticator\",\"sms\"]','2026-11-16 00:14:26','2026-11-19 16:25:23'),
(35,'maintenanceMessage','Сайт временно закрыт на техническое обслуживание. Скоро вернёмся!','text','security',3,'Сообщение на время обслуживания',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(36,'adminIpAllowlist','','text','security',5,'Разрешённые IP для входа в админку',NULL,'2026-11-16 00:14:26','2026-11-19 16:25:23'),
(37,'securityContactEmail','','email','security',6,'Email для уведомлений о безопасности',NULL,'2026-11-16 00:14:26','2026-11-19 16:25:23'),
(38,'twoFactorPhone','','tel','security',7,'Телефон для SMS-кодов',NULL,'2026-11-16 00:14:26','2026-11-19 16:25:23'),
(39,'maxLoginAttempts','5','number','security',8,'Попытки входа до блокировки',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(40,'lockoutMinutes','15','number','security',9,'Время блокировки (минуты)',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(41,'adminSessionHours','8','number','security',10,'Срок сессии админки (часы)',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(42,'loginCaptcha','0','checkbox','security',11,'Проверка CAPTCHA при входе',NULL,'2026-11-16 00:14:26','2026-11-19 16:25:23'),
(43,'securityLoginAlerts','1','checkbox','security',12,'Уведомлять о новых входах',NULL,'2026-11-16 00:14:26','2026-11-19 16:25:23'),
(44,'smtpHost','smtp.timeweb.ru','text','email',1,'SMTP-сервер',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(45,'smtpPort','587','number','email',2,'Порт',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(46,'smtpEncryption','tls','select','email',3,'Шифрование','[\"tls\",\"ssl\",\"none\"]','2026-11-16 00:14:26','2026-11-23 03:12:55'),
(47,'smtpUsername','test@valexo.ru','text','email',4,'Имя пользователя',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(48,'smtpPassword','CHYPWH2d2O3xvS','text','email',5,'Пароль приложения',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(49,'smtpFromName','Мой Блог','text','email',5,'Имя отправителя',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(50,'smtpFromEmail','test@valexo.ru','email','email',6,'Email отправителя',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(51,'smtpRecipient','osvax@yandex.ru','email','email',7,'Email получателя заявок',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(52,'smtpReplyTo','osvax@yandex.ru','email','email',8,'Ответы направлять на',NULL,'2026-11-16 00:14:26','2026-11-23 03:12:55'),
(53,'articlePerPage','5','number','articles',1,'Статей на странице',NULL,'2026-11-16 01:05:15','2026-11-23 03:12:55'),
(54,'articleCommentsEnabled','1','checkbox','articles',4,'Разрешить комментарии',NULL,'2026-11-16 01:05:15','2026-11-23 03:12:55'),
(55,'articleSortOrder','newest','select','articles',2,'Сортировка статей','[\"newest\",\"oldest\",\"popular\"]','2026-11-16 01:05:15','2026-11-23 03:12:55'),
(56,'articleFeaturedEnabled','1','checkbox','articles',6,'Показывать избранные статьи',NULL,'2026-11-16 01:05:15','2026-11-19 16:25:23'),
(57,'articleExcerptLength','300','number','articles',3,'Длина анонса (символов)',NULL,'2026-11-16 01:05:15','2026-11-23 03:12:55'),
(58,'articleTagsEnabled','1','checkbox','articles',7,'Показывать теги',NULL,'2026-11-16 01:05:15','2026-11-23 03:12:55'),
(59,'articleCategoriesEnabled','1','checkbox','articles',8,'Показывать рубрики',NULL,'2026-11-16 01:05:15','2026-11-23 03:12:55'),
(60,'articleReadTime','0','checkbox','articles',9,'Показывать время чтения',NULL,'2026-11-16 01:05:15','2026-11-23 03:12:55'),
(61,'articleRelatedEnabled','1','checkbox','articles',10,'Показывать похожие статьи',NULL,'2026-11-16 01:05:15','2026-11-23 03:12:55'),
(62,'articleShareButtons','1','checkbox','articles',11,'Кнопки «Поделиться»',NULL,'2026-11-16 01:05:15','2026-11-23 03:12:55'),
(63,'portfolioPerPage','4','number','portfolio',1,'Работ на странице',NULL,'2026-11-16 01:05:15','2026-11-23 03:12:55'),
(64,'portfolioLayout','grid','select','portfolio',2,'Тип отображения','[\"grid\",\"masonry\"]','2026-11-16 01:05:15','2026-11-23 03:12:55'),
(65,'portfolioShowTags','1','checkbox','portfolio',3,'Показывать теги',NULL,'2026-11-16 01:05:15','2026-11-23 03:12:55'),
(66,'portfolioShowCategories','1','checkbox','portfolio',4,'Показывать рубрики',NULL,'2026-11-16 01:05:15','2026-11-19 16:25:23'),
(67,'portfolioShowViews','1','checkbox','portfolio',5,'Показывать счётчик просмотров',NULL,'2026-11-16 01:05:15','2026-11-19 16:25:23'),
(68,'portfolioShowDate','1','checkbox','portfolio',6,'Показывать дату',NULL,'2026-11-16 01:05:15','2026-11-23 03:12:55'),
(69,'portfolioFeaturedEnabled','1','checkbox','portfolio',7,'Показывать избранные работы',NULL,'2026-11-16 01:05:15','2026-11-19 16:25:23'),
(70,'portfolioShowClient','1','checkbox','portfolio',8,'Показывать клиента',NULL,'2026-11-16 01:05:15','2026-11-23 03:12:55'),
(71,'portfolioShowLink','1','checkbox','portfolio',9,'Показывать ссылку на проект',NULL,'2026-11-16 01:05:15','2026-11-19 16:25:23'),
(142,'commentsAccess','users','select','articles',5,'Кто видит комментарии','[{\"value\":\"guests\",\"label\":\"Все посетители\"},{\"value\":\"users\",\"label\":\"Только авторизованные\"},{\"value\":\"staff\",\"label\":\"Только персонал\"}]','2026-11-17 19:41:49','2026-11-23 03:12:55'),
(213,'heroTitle','Личный блог о веб-разработке и дизайне','text','home',2,'Заголовок героя',NULL,'2026-11-17 19:47:12','2026-11-23 03:12:55'),
(214,'heroSubtitle','Здесь я делюсь опытом, статьями и проектами.','text','home',3,'Подзаголовок героя',NULL,'2026-11-17 19:47:12','2026-11-23 03:12:55'),
(215,'heroPrimaryText','Читать блог','text','home',5,'Текст главной кнопки',NULL,'2026-11-17 19:47:12','2026-11-23 03:12:55'),
(216,'heroPrimaryUrl','/blog.html','text','home',6,'Ссылка главной кнопки',NULL,'2026-11-17 19:47:12','2026-11-23 03:12:55'),
(217,'heroSecondaryText','Заказать проект','text','home',7,'Текст второй кнопки',NULL,'2026-11-17 19:47:12','2026-11-23 03:12:55'),
(218,'heroSecondaryUrl','/order.html','text','home',8,'Ссылка второй кнопки',NULL,'2026-11-17 19:47:12','2026-11-23 03:12:55'),
(219,'step1Title','Оставляете заявку','text','home',9,'Шаг 1 — заголовок',NULL,'2026-11-17 19:47:12','2026-11-23 03:12:55'),
(220,'step1Text','Заполняете форму на странице заказа — 2 минуты','text','home',10,'Шаг 1 — описание',NULL,'2026-11-17 19:47:12','2026-11-23 03:12:55'),
(221,'step2Title','Обсуждаем детали','text','home',11,'Шаг 2 — заголовок',NULL,'2026-11-17 19:47:12','2026-11-23 03:12:55'),
(222,'step2Text','Связываюсь с вами в течение дня и уточняем задачу','text','home',12,'Шаг 2 — описание',NULL,'2026-11-17 19:47:12','2026-11-23 03:12:55'),
(223,'step3Title','Получаете результат','text','home',13,'Шаг 3 — заголовок',NULL,'2026-11-17 19:47:12','2026-11-23 03:12:55'),
(224,'step3Text','Готовый сайт точно в срок и с гарантией','text','home',14,'Шаг 3 — описание',NULL,'2026-11-17 19:47:12','2026-11-23 03:12:55'),
(225,'homeFeaturedCount','3','number','home',4,'Статей в разделе «Свежие статьи»',NULL,'2026-11-17 20:45:31','2026-11-23 03:12:55'),
(232,'calculatorConfig','{\"currency\":\"₽\",\"types\":[{\"id\":\"visitka\",\"name\":\"Сайт-визитка\",\"price\":0,\"days\":0,\"features\":[{\"name\":\"До 5 страниц\",\"price\":15000,\"days\":7},{\"name\":\"Адаптивная вёрстка\",\"price\":5000,\"days\":3},{\"name\":\"Форма обратной связи\",\"price\":3000,\"days\":2},{\"name\":\"SEO-базовый\",\"price\":4000,\"days\":3}]},{\"id\":\"portfolio\",\"name\":\"Сайт-портфолио\",\"price\":0,\"days\":0,\"features\":[{\"name\":\"До 10 страниц\",\"price\":25000,\"days\":10},{\"name\":\"Галерея работ\",\"price\":8000,\"days\":5},{\"name\":\"Фильтрация по категориям\",\"price\":6000,\"days\":4},{\"name\":\"Анимации и эффекты\",\"price\":7000,\"days\":5}]},{\"id\":\"blog\",\"name\":\"Блог\",\"price\":0,\"days\":0,\"features\":[{\"name\":\"CMS-админ-панель\",\"price\":30000,\"days\":14},{\"name\":\"Рубрики и метки\",\"price\":8000,\"days\":5},{\"name\":\"Комментарии\",\"price\":5000,\"days\":4},{\"name\":\"Поиск по статьям\",\"price\":4000,\"days\":3}]},{\"id\":\"shop\",\"name\":\"Интернет-магазин\",\"price\":0,\"days\":0,\"features\":[{\"name\":\"Каталог товаров\",\"price\":40000,\"days\":20},{\"name\":\"Корзина и оформление\",\"price\":15000,\"days\":10},{\"name\":\"Онлайн-оплата\",\"price\":12000,\"days\":8},{\"name\":\"Управление складом\",\"price\":10000,\"days\":7},{\"name\":\"Личный кабинет\",\"price\":12000,\"days\":8}]},{\"id\":\"landing\",\"name\":\"Landing Page\",\"price\":0,\"days\":0,\"features\":[{\"name\":\"До 8 блоков\",\"price\":20000,\"days\":7},{\"name\":\"Форма заявки\",\"price\":5000,\"days\":3},{\"name\":\"Анимации при скролле\",\"price\":6000,\"days\":4},{\"name\":\"A/B тестирование\",\"price\":8000,\"days\":5}]},{\"id\":\"services\",\"name\":\"Сайт услуг\",\"price\":0,\"days\":0,\"features\":[{\"name\":\"До 10 страниц\",\"price\":35000,\"days\":14},{\"name\":\"Калькулятор услуг\",\"price\":10000,\"days\":7},{\"name\":\"Запись онлайн\",\"price\":8000,\"days\":5},{\"name\":\"Отзывы клиентов\",\"price\":5000,\"days\":3}]}]}','json','calculator',1,'Конфигурация калькулятора заказа',NULL,'2026-11-17 21:28:55','2026-11-17 18:35:23'),
(235,'mailOrderSubject','Новая заявка с сайта','text','mail',1,'Тема письма заявки',NULL,'2026-11-17 21:48:39','2026-11-23 03:12:55'),
(236,'mailOrderBody','Имя: {name}\nEmail: {email}\nТелефон: {phone}\nТип: {type}\n*****\nФункции: \n{calc_features} \n\nБюджет: {budget}\n*******\nСообщение: {message}','text','mail',2,'Текст письма заявки',NULL,'2026-11-17 21:48:40','2026-11-23 03:12:55'),
(237,'mailFeedbackSubject','Обратная связь с сайта','text','mail',3,'Тема письма обратной связи',NULL,'2026-11-17 21:48:40','2026-11-23 03:12:55'),
(238,'mailFeedbackBody','Имя: {name}\nEmail: {email}\nТема: {subject}\n************\nСообщение: {message}','text','mail',4,'Текст письма обратной связи',NULL,'2026-11-17 21:48:40','2026-11-23 03:12:55'),
(239,'mailCommentSubject','Новый комментарий к статье','text','mail',5,'Тема письма комментария',NULL,'2026-11-17 21:48:40','2026-11-23 03:12:55'),
(240,'mailCommentBody','Имя: {name}\nEmail: {email}\nСтатья: {article}\n****************\nКомментарий: {message}','text','mail',6,'Текст письма комментария',NULL,'2026-11-17 21:48:40','2026-11-23 03:12:55'),
(333,'homePageMode','main','select','home',1,'Режим главной','[{\"value\":\"main\",\"label\":\"Шаблон главной\"},{\"value\":\"static\",\"label\":\"Статическая страница (лендинг)\"}]','2026-11-17 21:58:20','2026-11-23 03:12:55'),
(335,'maintenanceAllowedIps','127.0.0.1','text','security',4,'IP-адреса с доступом при обслуживании',NULL,'2026-11-17 21:58:20','2026-11-23 03:12:55'),
(336,'footerText','© {year} МойБлог. Все права защищены.','text','general',6,'Текст футера',NULL,'2026-11-17 21:58:20','2026-11-18 00:25:57'),
(337,'blogTitle','Блог','text','headings',1,'Блог — заголовок',NULL,'2026-11-17 21:58:20','2026-11-23 03:12:55'),
(338,'blogLead','Последние записи и полезные материалы','text','headings',2,'Блог — подзаголовок',NULL,'2026-11-17 21:58:20','2026-11-23 03:12:55'),
(339,'portfolioTitle','Портфолио','text','headings',3,'Портфолио — заголовок',NULL,'2026-11-17 21:58:20','2026-11-23 03:12:55'),
(340,'portfolioLead','Мои лучшие работы и проекты','text','headings',4,'Портфолио — подзаголовок',NULL,'2026-11-17 21:58:20','2026-11-23 03:12:55'),
(341,'contactsTitle','Контакты','text','headings',5,'Контакты — заголовок',NULL,'2026-11-17 21:58:20','2026-11-23 03:12:55'),
(342,'contactsLead','Буду рад связи и сотрудничеству','text','headings',6,'Контакты — подзаголовок',NULL,'2026-11-17 21:58:20','2026-11-23 03:12:55'),
(2540,'statisticsRetentionDays','60','number','statistics',1,'Срок хранения посещений (дней), 0 — без ограничения',NULL,'2026-11-19 16:54:20','2026-11-20 04:51:05'),
(2541,'statisticsAutoClean','1','checkbox','statistics',2,'Автоочистка записей по сроку хранения',NULL,'2026-11-19 16:54:20','2026-11-20 04:51:05'),
(2543,'statisticsSections','[\"home\",\"blog\",\"portfolio\"]','json','statistics',3,'Разделы, по которым ведётся статистика (JSON-массив)',NULL,'2026-11-19 18:00:08','2026-11-20 04:51:42'),
(2665,'statisticsUpdateInterval','900','number','statistics',4,'Интервал обновления статистики (секунды), дашборд',NULL,'2026-11-19 19:30:29','2026-11-20 04:51:06'),
(6456,'orderTitle','Заказать','text','headings',7,'Заказать — заголовок',NULL,'2026-11-20 01:32:59','2026-11-23 03:12:55'),
(6457,'orderLead','Расскажите о своей задаче — я свяжусь с вами','text','headings',8,'Заказать — подзаголовок',NULL,'2026-11-20 01:32:59','2026-11-23 03:12:55'),
(6458,'maintenanceStart','2026-11-20T05:50','text','security',3,'Обслуживание с (от), Y-m-d H:i',NULL,'2026-11-20 01:32:59','2026-11-23 03:12:55'),
(6459,'maintenanceEnd','2026-11-20T09:50','text','security',4,'Обслуживание до (по), Y-m-d H:i',NULL,'2026-11-20 01:32:59','2026-11-23 03:12:55'),
(6460,'blockedIps','','text','security',6,'IP-адреса без доступа (блокировка)',NULL,'2026-11-20 01:32:59','2026-11-23 03:12:55'),
(6461,'smtpEnabled','0','checkbox','email',1,'Отправлять письма через SMTP (иначе PHP mail())',NULL,'2026-11-20 01:32:59','2026-11-23 03:12:55'),
(6462,'logsEnabled','1','checkbox','logs',1,'Вести журнал событий',NULL,'2026-11-20 01:32:59','2026-11-23 03:12:55'),
(6463,'logsAutoClean','1','checkbox','logs',2,'Автоочистка журнала по сроку хранения',NULL,'2026-11-20 01:32:59','2026-11-23 03:12:55'),
(6464,'logsRetentionDays','30','number','logs',3,'Срок хранения записей журнала (дней)',NULL,'2026-11-20 01:32:59','2026-11-23 03:12:55'),
(6465,'logsAuth','1','checkbox','logs',4,'Журнал: входы и регистрации',NULL,'2026-11-20 01:32:59','2026-11-23 03:12:55'),
(6466,'logsForms','1','checkbox','logs',5,'Журнал: отправка форм',NULL,'2026-11-20 01:32:59','2026-11-23 03:12:55'),
(6467,'logsSecurity','1','checkbox','logs',6,'Журнал: блокировки и отказы в доступе',NULL,'2026-11-20 01:32:59','2026-11-23 03:12:55'),
(6468,'logsErrors','1','checkbox','logs',7,'Журнал: ошибки сервера 500',NULL,'2026-11-20 01:32:59','2026-11-23 03:12:55'),
(6470,'logsLastCleanAt','2026-11-23 01:37:02','text','general',0,'',NULL,'2026-11-20 01:34:37','2026-11-22 21:37:02'),
(6543,'clearLogsBtn','','text','general',0,'',NULL,'2026-11-20 01:35:05','2026-11-23 03:12:55'),
(6545,'testEmailBtn','','text','general',0,'',NULL,'2026-11-20 01:35:05','2026-11-23 03:12:55'),
(11410,'articlesPerRow','1','select','articles',11,'Статей в ряд',NULL,'2026-11-20 02:38:30','2026-11-23 03:12:55'),
(11411,'blogSidebar','right','select','articles',12,'Сайдбар на странице списка',NULL,'2026-11-20 02:38:30','2026-11-23 03:12:55'),
(11412,'articlePageSidebar','right','select','articles',13,'Сайдбар на странице статьи',NULL,'2026-11-20 02:38:30','2026-11-23 03:12:55'),
(12239,'projectsPerRow','2','select','portfolio',6,'Работ в ряд',NULL,'2026-11-20 02:42:29','2026-11-23 03:12:55'),
(12241,'portfolioSidebar','none','select','portfolio',7,'Сайдбар на странице портфолио',NULL,'2026-11-20 02:42:29','2026-11-23 03:12:55'),
(12242,'projectPageSidebar','right','select','portfolio',8,'Сайдбар на странице проекта',NULL,'2026-11-20 02:42:29','2026-11-23 03:12:55'),
(12246,'themePrimary','#2cdbe8','color','appearance',5,'Основной цвет',NULL,'2026-11-20 02:42:29','2026-11-23 03:12:55'),
(12247,'themeBackground','#ffffff','color','appearance',6,'Фон страницы',NULL,'2026-11-20 02:42:29','2026-11-23 03:12:55'),
(12248,'themeBlockBg','#ffffff','color','appearance',7,'Фон блоков (карточек)',NULL,'2026-11-20 02:42:29','2026-11-23 03:12:55'),
(12249,'themeSidebarBg','#ffffff','color','appearance',8,'Фон сайдбаров',NULL,'2026-11-20 02:42:29','2026-11-23 03:12:55'),
(12250,'themeText','#3c3c3c','color','appearance',9,'Цвет текста',NULL,'2026-11-20 02:42:29','2026-11-23 03:12:55'),
(12251,'themeFontFamily','inter','text','appearance',10,'Название шрифта',NULL,'2026-11-20 02:42:29','2026-11-23 03:12:55'),
(12252,'themeFontUrl','https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap','text','appearance',11,'Ссылка на шрифт (CDN)',NULL,'2026-11-20 02:42:29','2026-11-23 03:12:55'),
(12253,'themeFontFileInput','','text','general',0,'',NULL,'2026-11-20 02:42:29','2026-11-23 03:12:55'),
(12254,'fontUploadBtn','','text','general',0,'',NULL,'2026-11-20 02:42:29','2026-11-23 03:12:55'),
(12255,'themeFontFile','uploads/fonts/aa63303325b7a0214b9a5144bff61ab5.woff2','text','appearance',12,'Файл загруженного шрифта',NULL,'2026-11-20 02:42:29','2026-11-23 03:12:55'),
(16852,'portfolioShowExtraFields','1','checkbox','general',0,'',NULL,'2026-11-20 03:34:24','2026-11-23 03:12:55'),
(17327,'themeFontSize','16','number','appearance',13,'Размер текста (px)',NULL,'2026-11-20 03:39:01','2026-11-23 03:12:55'),
(17328,'themeNavSize','16','number','appearance',14,'Размер меню (px)',NULL,'2026-11-20 03:39:01','2026-11-23 03:12:55'),
(17329,'themeHeadingScale','100','number','appearance',15,'Размер заголовков (%)',NULL,'2026-11-20 03:39:01','2026-11-23 03:12:55'),
(21709,'auth2faEnabled','','text','general',0,'',NULL,'2026-11-22 21:54:09','2026-11-23 03:12:55'),
(21710,'yandexAuthEnabled','','text','general',0,'',NULL,'2026-11-22 21:54:09','2026-11-23 03:12:55'),
(21711,'yandexClientId','a2f3c54e0f7c40e48620a09795f767eb','text','general',0,'',NULL,'2026-11-22 21:54:09','2026-11-23 03:12:55'),
(21712,'yandexClientSecret','403a7ed9383c4feab7b8152869bc898b','text','general',0,'',NULL,'2026-11-22 21:54:09','2026-11-23 03:12:55'),
(21713,'yandexRedirectUri','https://myblog.loc','text','general',0,'',NULL,'2026-11-22 21:54:09','2026-11-23 03:12:55'),
(22646,'adminLoginEmail','osvax@yandex.ru','email','authentication',1,'Email для входа в админ-панель',NULL,'2026-11-22 23:27:20','2026-11-23 03:12:55'),
(22647,'frontendTemplate','default','select','templates',1,'Шаблон публичной части',NULL,'2026-11-22 23:27:20','2026-11-23 03:12:55'),
(22648,'adminTemplate','default','select','templates',2,'Шаблон админ-панели',NULL,'2026-11-22 23:27:20','2026-11-23 03:12:55'),
(24057,'contactAddressEnabled','1','checkbox','contacts',10,'Показывать адрес',NULL,'2026-11-23 02:58:26','2026-11-23 03:12:55'),
(24058,'contactAddressLabel','Адрес','text','contacts',11,'Название поля адреса',NULL,'2026-11-23 02:58:26','2026-11-23 03:12:55'),
(24059,'contactHoursEnabled','1','checkbox','contacts',12,'Показывать часы работы',NULL,'2026-11-23 02:58:26','2026-11-23 03:12:55'),
(24060,'contactHoursLabel','Часы работы','text','contacts',13,'Название поля часов работы',NULL,'2026-11-23 02:58:26','2026-11-23 03:12:55'),
(24061,'contactEmailEnabled','1','checkbox','contacts',14,'Показывать email',NULL,'2026-11-23 02:58:26','2026-11-23 03:12:55'),
(24062,'contactEmailLabel','Email','text','contacts',15,'Название поля email',NULL,'2026-11-23 02:58:26','2026-11-23 03:12:55'),
(24063,'contactPhoneEnabled','1','checkbox','contacts',16,'Показывать телефон',NULL,'2026-11-23 02:58:26','2026-11-23 03:12:55'),
(24064,'contactPhoneLabel','Телефон','text','contacts',17,'Название поля телефона',NULL,'2026-11-23 02:58:26','2026-11-23 03:12:55'),
(24065,'socialTelegramEnabled','1','checkbox','contacts',18,'Показывать Telegram',NULL,'2026-11-23 02:58:26','2026-11-23 03:12:55'),
(24066,'socialTelegramLabel','Telegram','text','contacts',19,'Название кнопки Telegram',NULL,'2026-11-23 02:58:26','2026-11-23 03:12:55'),
(24067,'socialVkEnabled','1','checkbox','contacts',20,'Показывать VK',NULL,'2026-11-23 02:58:26','2026-11-23 03:12:55'),
(24068,'socialVkLabel','VK','text','contacts',21,'Название кнопки VK',NULL,'2026-11-23 02:58:26','2026-11-23 03:12:55'),
(24069,'socialGithubEnabled','1','checkbox','contacts',22,'Показывать GitHub',NULL,'2026-11-23 02:58:26','2026-11-23 03:12:55'),
(24070,'socialGithubLabel','GitHub','text','contacts',23,'Название кнопки GitHub',NULL,'2026-11-23 02:58:26','2026-11-23 03:12:55'),
(24071,'socialYoutubeEnabled','1','checkbox','contacts',24,'Показывать YouTube',NULL,'2026-11-23 02:58:26','2026-11-23 03:12:55'),
(24072,'socialYoutubeLabel','YouTube','text','contacts',25,'Название кнопки YouTube',NULL,'2026-11-23 02:58:26','2026-11-23 03:12:55'),
(24359,'contactsAboutEnabled','1','checkbox','contacts_about',1,'Показывать блок «Обо мне»',NULL,'2026-11-23 03:05:44','2026-11-23 03:12:55'),
(24360,'contactsAboutHtml','Валентин — фулстек-разработчик с 20-летним стажем (если считать от первых стрОк кода). На фронтенде пишет современный, семантичный и доступный код на Vue 3 (Composition API, Pinia, Vite), использует SCSS для поддержания масштабируемой архитектуры стилей (BEM/ITCSS), уверенно работает с TypeScript и сборкой. На бэкенде строит надёжные API на Laravel (очереди, Broadcasting, Octane) и поддерживает legacy-проекты на Yii2. Привык отвечать за фичу end-to-end: от верстки и стейт-менеджмента до проектир','textarea','contacts_about',2,'HTML-код блока «Обо мне»',NULL,'2026-11-23 03:05:44','2026-11-23 03:12:55'),
(24361,'contactsAboutPhoto','','text','contacts_about',3,'Фотография блока «Обо мне»',NULL,'2026-11-23 03:05:44','2026-11-23 03:12:55'),
(24436,'contactsAboutPhotoInput','','text','general',0,'',NULL,'2026-11-23 03:08:07','2026-11-23 03:12:55'),
(24437,'contactsAboutPhotoUpload','','text','general',0,'',NULL,'2026-11-23 03:08:07','2026-11-23 03:12:55'),
(24587,'contactsAboutBlockFormat','P','text','general',0,'',NULL,'2026-11-23 03:11:03','2026-11-23 03:12:55'),
(24588,'contactsAboutSourceToggle','','text','general',0,'',NULL,'2026-11-23 03:11:03','2026-11-23 03:12:55');
/*!40000 ALTER TABLE `va_site_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_user_activities`
--

DROP TABLE IF EXISTS `va_user_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_user_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `page_url` varchar(500) NOT NULL DEFAULT '',
  `page_title` varchar(255) NOT NULL DEFAULT '',
  `ip_address` varchar(45) NOT NULL DEFAULT '',
  `user_agent` text DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL COMMENT 'Страница входа (HTTP_REFERER)',
  `visited_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_activities_user_id` (`user_id`),
  KEY `idx_user_activities_visited_at` (`visited_at`)
) ENGINE=InnoDB AUTO_INCREMENT=14302 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_user_activities`
--

LOCK TABLES `va_user_activities` WRITE;
/*!40000 ALTER TABLE `va_user_activities` DISABLE KEYS */;
INSERT INTO `va_user_activities` VALUES
(14138,0,'/health','Пользовательская страница','127.0.0.1','curl/8.22.0',NULL,'2026-11-20 05:12:41'),
(14141,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/contacts.html','2026-11-20 05:15:29'),
(14142,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/contacts.html','2026-11-20 05:17:26'),
(14143,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/contacts.html','2026-11-20 05:51:07'),
(14145,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/admin/roles.html','2026-11-20 06:19:54'),
(14146,0,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/','2026-11-20 06:19:52'),
(14147,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 YaBrowser/26.10.0.0 Safari/537.36','http://myblog.loc/portfolio.html','2026-11-20 06:24:41'),
(14148,7,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/login.html','2026-11-20 06:42:53'),
(14149,7,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/order.html','2026-11-20 07:06:48'),
(14150,7,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/','2026-11-20 07:06:50'),
(14151,7,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/blog.html','2026-11-20 07:06:49'),
(14152,7,'/post-bootstrap.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/blog.html','2026-11-20 07:06:53'),
(14153,7,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/post-bootstrap.html','2026-11-20 07:45:25'),
(14154,0,'/','Пользовательская страница','127.0.0.1','curl/8.22.0',NULL,'2026-11-20 07:48:36'),
(14155,7,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/post-bootstrap.html','2026-11-20 07:53:19'),
(14156,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 YaBrowser/26.10.0.0 Safari/537.36','http://myblog.loc/portfolio.html','2026-11-20 07:54:00'),
(14157,7,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/order.html','2026-11-20 08:06:31'),
(14158,7,'/post-bootstrap.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/blog.html','2026-11-20 08:06:34'),
(14159,0,'/post-bootstrap.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/blog.html','2026-11-20 15:05:08'),
(14160,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/login.html','2026-11-20 15:05:26'),
(14161,1,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/','2026-11-20 15:05:29'),
(14162,1,'/post-adaptive.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/blog.html','2026-11-20 15:05:32'),
(14163,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/post-adaptive.html','2026-11-20 15:08:11'),
(14164,1,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/','2026-11-20 15:08:20'),
(14165,1,'/post-bootstrap.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/blog.html','2026-11-20 15:08:22'),
(14166,1,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/post-bootstrap.html','2026-11-20 15:08:28'),
(14167,1,'/project-dashboard.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/portfolio.html','2026-11-20 15:08:32'),
(14168,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 21:36:51'),
(14169,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/login.html','2026-11-22 21:37:02'),
(14170,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/','2026-11-22 21:50:51'),
(14171,0,'/index.html?state=5b052d8a234e54cd4431efffc48567cd&code=veyki7foqxmo74za&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 21:54:31'),
(14172,0,'/index.html?state=933c8f04caf9ffbd994df9c532b633cd&code=nex6phyjdkxielgt&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 21:54:42'),
(14173,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/index.html?state=933c8f04caf9ffbd994df9c532b633cd&code=nex6phyjdkxielgt&cid=valentinalexo','2026-11-22 21:54:43'),
(14174,0,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/','2026-11-22 21:54:44'),
(14175,0,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/blog.html','2026-11-22 21:54:45'),
(14176,7,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/login.html','2026-11-22 21:57:03'),
(14177,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/profile.html','2026-11-22 21:58:05'),
(14178,0,'/index.html?state=a4aa1f1d23f42c319dd82faca32a3e95&code=aszuf66htxpibsev&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 21:58:16'),
(14179,0,'/index.html?state=9aea9a5d42cf3e891aa8054482a0cf6b&code=quodgalliubrvagp&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 22:01:23'),
(14180,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/login.html','2026-11-22 22:02:17'),
(14181,0,'/index.html?state=bb4971e111396f9d12011198b2f9c32e&code=tqwaumm7kn2uj57u&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 22:02:28'),
(14182,0,'/index.html?state=bb4971e111396f9d12011198b2f9c32e&code=tqwaumm7kn2uj57u&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 22:04:31'),
(14183,0,'/index.html?state=fc335206fd5e771e1bdd6ef00d9f2a72&code=w3mp4qze7kppqflv&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 22:04:38'),
(14184,0,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/index.html?state=fc335206fd5e771e1bdd6ef00d9f2a72&code=w3mp4qze7kppqflv&cid=valentinalexo','2026-11-22 22:04:41'),
(14185,0,'/index.html?state=a33f3336969dc6f816acc75506d343e5&code=fnjemkwc3prxn4pk&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 22:04:52'),
(14186,0,'/index.html?state=a33f3336969dc6f816acc75506d343e5&code=fnjemkwc3prxn4pk&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 22:35:03'),
(14187,0,'/index.html?state=a33f3336969dc6f816acc75506d343e5&code=fnjemkwc3prxn4pk&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 22:37:40'),
(14188,0,'/index.html?state=4f604d6dae90396c83f58175ecc2d200&code=d6lf5wkyubsecb7s&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 22:37:51'),
(14189,0,'/index.html?state=4f604d6dae90396c83f58175ecc2d200&code=d6lf5wkyubsecb7s&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/index.html?state=4f604d6dae90396c83f58175ecc2d200&code=d6lf5wkyubsecb7s&cid=valentinalexo','2026-11-22 22:43:23'),
(14190,0,'/index.html?state=ff25483453d4c69c9f04ce147d8ed180&code=wauqtn64w3fdh3qz&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 22:44:05'),
(14191,0,'/index.html?state=7bdba1918dab044cb550c2693a803505&code=n6au2bujcnge3anw&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 22:44:13'),
(14192,0,'/index.html?state=eca2bf1de490aeb870e5d7d8f9cb4304&code=hymyw34dfilrvbt7&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 22:44:20'),
(14193,7,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/login.html','2026-11-22 22:44:36'),
(14194,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/login.html','2026-11-22 22:46:22'),
(14195,0,'/index.html?state=b31dfd0efd318339952f1168d6bfc6bd&code=4epwmlv3bk2hy4s6&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 22:46:39'),
(14196,0,'/index.html?state=6f1d92ac36f131954f934a3774de567c&code=ulikmnfzc34ikyei&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 22:47:19'),
(14197,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/admin/index.html','2026-11-22 22:48:06'),
(14198,0,'/index.html?state=6423d04673f144659ff175471387870d&code=5ksab3vhcskqdlrr&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 22:48:38'),
(14199,0,'/index.html?state=50c3d48fa09e0441bb7e12bc0602b3a7&code=lypnaiia5dh4vief&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 22:48:45'),
(14200,0,'/index.html?state=01669efef0be500e8f7e7d7ae90f9664&code=c4giuwr73svivlne&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://oauth.yandex.ru/','2026-11-22 22:51:32'),
(14201,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/login.html','2026-11-22 22:52:05'),
(14202,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/login.html','2026-11-22 22:53:18'),
(14203,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','http://myblog.loc/login.html','2026-11-22 22:56:20'),
(14204,0,'/index.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/admin/login.html','2026-11-22 23:03:30'),
(14205,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/index.html','2026-11-22 23:03:31'),
(14206,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 23:05:08'),
(14207,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/admin/index.html','2026-11-22 23:08:47'),
(14208,0,'/?state=2ed00fc526f8d6de94b2388eb115282f&code=mo4ov7gaxpfegzs6&cid=valentinalexo','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://oauth.yandex.ru/','2026-11-22 23:10:20'),
(14209,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/login.html','2026-11-22 23:10:40'),
(14210,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/login.html','2026-11-22 23:14:52'),
(14211,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-22 23:15:08'),
(14212,0,'/index.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/admin/login.html','2026-11-22 23:15:34'),
(14213,7,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/login.html','2026-11-22 23:16:05'),
(14214,7,'/login.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-22 23:25:21'),
(14215,0,'/post-bootstrap.html','Пользовательская страница','127.0.0.1','curl/8.23.0',NULL,'2026-11-22 23:29:10'),
(14216,0,'/project-shop.html','Пользовательская страница','127.0.0.1','curl/8.23.0',NULL,'2026-11-22 23:29:10'),
(14217,7,'/login.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-22 23:29:13'),
(14218,0,'/project-shop.html','Пользовательская страница','127.0.0.1','curl/8.23.0',NULL,'2026-11-22 23:31:11'),
(14219,0,'/post-bootstrap.html','Пользовательская страница','127.0.0.1','curl/8.23.0',NULL,'2026-11-22 23:31:11'),
(14220,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/login.html','2026-11-22 23:34:13'),
(14221,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/login.html','2026-11-22 23:44:36'),
(14222,0,'/','Пользовательская страница','127.0.0.1','',NULL,'2026-11-22 23:43:00'),
(14223,0,'/','Пользовательская страница','127.0.0.1','curl/8.23.0',NULL,'2026-11-22 23:43:24'),
(14224,1,'/project-shop.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-22 23:44:28'),
(14225,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/login.html','2026-11-22 23:46:01'),
(14226,1,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-22 23:46:23'),
(14227,1,'/portfolio.html?page=2','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html','2026-11-22 23:46:19'),
(14228,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html','2026-11-23 00:02:40'),
(14229,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',NULL,'2026-11-23 00:25:10'),
(14230,1,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-23 00:29:31'),
(14231,1,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-23 00:36:26'),
(14232,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/blog.html','2026-11-23 00:36:17'),
(14233,1,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-23 00:36:26'),
(14234,1,'/project-photo.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html','2026-11-23 00:36:23'),
(14235,1,'/post-bootstrap.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/blog.html','2026-11-23 00:36:41'),
(14236,1,'/post-adaptive.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/post-bootstrap.html','2026-11-23 00:36:39'),
(14237,1,'/post-bootstrap.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/blog.html','2026-11-23 00:42:57'),
(14238,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/post-bootstrap.html','2026-11-23 00:42:53'),
(14239,1,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/contacts.html','2026-11-23 00:42:54'),
(14240,1,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html','2026-11-23 00:42:56'),
(14241,1,'/post-bootstrap.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/blog.html','2026-11-23 00:49:02'),
(14242,1,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/post-bootstrap.html','2026-11-23 00:49:06'),
(14243,1,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/post-bootstrap.html','2026-11-23 00:51:02'),
(14244,1,'/post-bootstrap.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/blog.html','2026-11-23 00:51:07'),
(14245,1,'/post-bootstrap.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/blog.html','2026-11-23 01:08:18'),
(14246,1,'/post-bootstrap.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/blog.html','2026-11-23 01:20:12'),
(14247,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/post-bootstrap.html','2026-11-23 01:20:08'),
(14248,1,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-23 01:20:11'),
(14249,1,'/post-bootstrap.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/blog.html','2026-11-23 01:21:32'),
(14250,1,'/post-bootstrap.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/blog.html','2026-11-23 01:23:46'),
(14251,1,'/post-bootstrap.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/blog.html','2026-11-23 01:29:04'),
(14252,1,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/post-bootstrap.html','2026-11-23 01:37:13'),
(14253,1,'/project-photo.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html','2026-11-23 01:37:15'),
(14254,16,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/login.html','2026-11-23 01:56:11'),
(14255,0,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-23 02:02:58'),
(14256,0,'/project-photo.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html','2026-11-23 02:03:01'),
(14257,0,'/project-photo.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html','2026-11-23 02:05:11'),
(14258,0,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/project-photo.html','2026-11-23 02:16:11'),
(14259,0,'/portfolio.html?page=2','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html','2026-11-23 02:16:14'),
(14260,0,'/portfolio.html?page=1','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html?page=2','2026-11-23 02:17:02'),
(14261,0,'/project-forma-studio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html?page=1','2026-11-23 02:17:10'),
(14262,0,'/portfolio.html?page=1','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html?page=2','2026-11-23 02:18:13'),
(14263,0,'/portfolio.html?page=2','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html?page=1','2026-11-23 02:18:08'),
(14264,0,'/portfolio.html?page=3','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html?page=2','2026-11-23 02:18:11'),
(14265,0,'/project-chistkoff.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html?page=1','2026-11-23 02:18:14'),
(14266,0,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/project-chistkoff.html','2026-11-23 02:18:50'),
(14267,0,'/project-textile.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html','2026-11-23 02:18:52'),
(14268,0,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/project-chistkoff.html','2026-11-23 02:20:48'),
(14269,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html','2026-11-23 02:22:40'),
(14270,0,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-23 02:23:31'),
(14271,0,'/portfolio.html?page=2','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html','2026-11-23 02:22:27'),
(14272,0,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-23 02:23:30'),
(14273,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/contacts.html','2026-11-23 02:23:41'),
(14274,0,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-23 02:26:45'),
(14275,0,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/blog.html','2026-11-23 02:27:41'),
(14276,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/contacts.html','2026-11-23 02:29:50'),
(14277,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-23 02:34:27'),
(14278,0,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-23 02:34:58'),
(14279,0,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/blog.html','2026-11-23 02:34:18'),
(14280,0,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/contacts.html','2026-11-23 02:37:48'),
(14281,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/login.html','2026-11-23 02:40:11'),
(14282,1,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/order.html','2026-11-23 02:42:16'),
(14283,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/order.html','2026-11-23 02:44:37'),
(14284,1,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/order.html','2026-11-23 02:44:23'),
(14285,1,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-23 02:44:36'),
(14286,1,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-23 02:46:21'),
(14287,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/blog.html','2026-11-23 02:46:24'),
(14288,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/profile.html','2026-11-23 02:50:36'),
(14289,1,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-23 02:49:00'),
(14290,1,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/blog.html','2026-11-23 02:49:03'),
(14291,1,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/order.html','2026-11-23 02:50:48'),
(14292,1,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html','2026-11-23 02:50:35'),
(14293,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/order.html','2026-11-23 02:52:50'),
(14294,1,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-23 02:52:56'),
(14295,1,'/','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/contacts.html','2026-11-23 03:13:02'),
(14296,1,'/blog.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/','2026-11-23 03:13:03'),
(14297,1,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/blog.html','2026-11-23 03:13:04'),
(14298,1,'/portfolio.html','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/blog.html','2026-11-23 03:19:06'),
(14299,1,'/portfolio.html?page=2','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html','2026-11-23 03:19:10'),
(14300,1,'/portfolio.html?page=1','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html?page=2','2026-11-23 03:19:13'),
(14301,0,'/portfolio.html?page=1','Пользовательская страница','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','https://myblog.loc/portfolio.html?page=2','2026-11-23 03:22:30');
/*!40000 ALTER TABLE `va_user_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `va_users`
--

DROP TABLE IF EXISTS `va_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `va_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(254) NOT NULL,
  `yandex_id` varchar(128) NOT NULL DEFAULT '',
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(160) NOT NULL DEFAULT '',
  `avatar_path` varchar(500) NOT NULL DEFAULT '',
  `role` varchar(64) NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `reset_token` varchar(64) NOT NULL DEFAULT '',
  `reset_expires` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_visit` datetime DEFAULT NULL COMMENT 'Последнее посещение',
  `last_ip` varchar(45) NOT NULL DEFAULT '' COMMENT 'IP последнего посещения',
  `phone` varchar(40) NOT NULL DEFAULT '' COMMENT 'Телефон',
  `about` text DEFAULT NULL COMMENT 'О себе',
  `position` varchar(100) NOT NULL DEFAULT '' COMMENT 'Должность / роль',
  `bio` text DEFAULT NULL COMMENT 'Биография',
  `timezone` varchar(50) NOT NULL DEFAULT '' COMMENT 'Часовой пояс',
  `language` varchar(10) NOT NULL DEFAULT 'ru' COMMENT 'Язык интерфейса',
  `totp_secret` varchar(64) NOT NULL DEFAULT '' COMMENT 'Секрет TOTP (base32)',
  `totp_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Двухфакторная аутентификация включена',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_reset_token` (`reset_token`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_active` (`is_active`),
  KEY `idx_users_last_visit` (`last_visit`),
  KEY `idx_users_last_ip` (`last_ip`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `va_users`
--

LOCK TABLES `va_users` WRITE;
/*!40000 ALTER TABLE `va_users` DISABLE KEYS */;
INSERT INTO `va_users` VALUES
(1,'osvax@yandex.ru','','$2y$12$l4jy8rjGoCQp2TnqwPFgk.X.CsrO9iqam3XpaGMOlgQdWDuUBbDWW','Валентин','/uploads/profile/2bb3dab8c4ed6394dc9b3752ce9f7987.jpg','admin',1,'',NULL,'2026-11-15 21:40:17','2026-11-23 03:22:56','2026-11-23 03:22:56','127.0.0.1','+7 9090572269','Валентин — фулстек-разработчик с 20-летним стажем (если считать от первых стрОк кода). На фронтенде пишет современный, семантичный и доступный код на Vue 3 (Composition API, Pinia, Vite), использует SCSS для поддержания масштабируемой архитектуры стилей (BEM/ITCSS), уверенно работает с TypeScript и сборкой. На бэкенде строит надёжные API на Laravel (очереди, Broadcasting, Octane) и поддерживает legacy-проекты на Yii2. Привык отвечать за фичу end-to-end: от верстки и стейт-менеджмента до проектир','','Родился в 1980 году в Ижевске. Школа — Бейсик/Паскаль на УКНЦ/БК-0010, потом ИжГТУ им. Калашникова (прикладная математика/информатика). Уже на старших курсах кормил семью версткой и первым PHP.\n\n2002–2009 — фриланс и студии Ижевска/Казани: табличная → блочная вёрстка, jQuery, свои MVC-велосипеды, CodeIgniter, ранние Yii. Параллельно — гараж, первый двигатель, разобранный и собранный в одиночку.\n2009–2015 — старший фронтенд в крупном e-commerce (Москва/СПб/удалёнка). Grunt/Gulp, BEM, AngularJS → React. Глубокое погружение в Laravel 4/5: API, очереди, админки под миллионы SKU. В свободное время — стройка дачи «под ключ»: фундамент, стены, кровля, инженерки, чистовая отделка — всё своими руками.\n2015–2021 — тимлид/архитектор в финтех-стартапе (распределённая команда). Миграция на Vue 3 + Composition API + TS, Vitest/Playwright, GitHub Actions, Laravel Octane + RabbitMQ/Kafka + gRPC. Одновременно — полная переделка квартиры в новом доме: скрытая проводка, вентиляция, умный дом (Home Assistant, MQTT, ESPHome — прошивает сам), акустические системы собственной сборки.\n2021–настоящее — старший фулстэк / техлид / консультант. Зарубежные продукты, менторинг, аудит и миграция Yii2 → Laravel + Inertia/Vue 3. Тех-блог, спики на HolyJS, PHP Russia, Vue-meetups. В гараже — проектный автомобиль: восстановление проводки, доработка ЭБУ, установка доп. оборудования. На досуге — гитара/синтезаторы, запись треков в домашней студии, стихи (опубликован в сборнике / держит закрытый телеграм-канал).\nЦенности: простота там, где возможно сложное; надёжность важнее моды; код и бетон должны выдерживать нагрузку годами.','Europe/Samara','ru','NV2NMBYFWDPRKD73GAL74GREPSXFEQHA',0),
(6,'anna@editor.com','','$2y$12$bcprYQS2tWJT0Bpr1ITsO.oSHsGUZilowBduozUHXe8TyRljKcPWK','Анна Редактор','/uploads/profile/29fdf2f2073f2ab17d0bb595401ed9ed.jpg','editor',1,'',NULL,'2026-11-15 22:40:57','2026-11-18 21:59:08','2026-11-18 21:59:08','127.0.0.1','+75694856955','','Редактор','','Asia/Novosibirsk','ru','',0),
(7,'pavel@manager.com','','$2y$12$8JfynAZpJBllP3dUx2Z6nOMhTb8cNI9cpzTa8BoFeSw.3J0KzKeBu','Павел Менеджер','/uploads/profile/4cfec64981ee0b478bf88666498dc81f.jpg','manager',1,'',NULL,'2026-11-15 22:40:57','2026-11-22 23:34:08','2026-11-22 23:34:08','127.0.0.1','+7 99090572269','','Менеджер','','Europe/Moscow','ru','JSF5NXXXQD35ZHBPOHV4SWCALPFDUZPB',0),
(8,'ivan@user.com','','$2y$12$/reQWnKPR/l1.VENvPJxxeF1xsGiplnAJNzxP20zOhq9JuboMpyL2','Иван Пользователь','/uploads/profile/d3d4de7ef79c1644f040c07c1a1be82b.jpg','user',1,'',NULL,'2026-11-15 22:40:57','2026-11-18 21:29:25',NULL,'','+7695236455','','','','Asia/Vladivostok','ru','',0),
(16,'demo@user.test','','$2y$12$YubaFxUFt9RDUF/pS4uoAe7w4fnpchQLM3CjTmTutW4g2nFcNFJZm','Демонстрационный пользователь','/uploads/profile/512facf1c3fc2d6da1a7149497e6d859.jpg','readonly_demo',1,'',NULL,'2026-11-23 01:52:40','2026-11-23 01:56:34','2026-11-23 01:56:34','127.0.0.1','',NULL,'',NULL,'','ru','',0);
/*!40000 ALTER TABLE `va_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-23  7:41:53
