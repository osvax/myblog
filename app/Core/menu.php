<?php

declare(strict_types=1);

/**
 * Меню сайта: данные и рендер. Пункты хранятся в базе и управляются
 * в админке (раздел «Меню»).
 *
 * Примеры в шаблонах:
 *   <?= menu_items_html('main') ?> — только <li>-пункты (вставить в готовый ul);
 *   <?= render_menu('sidebar-about', 'nav flex-column') ?> — готовое меню с <ul>.
 * Пункт верхнего уровня с подчинёнными пунктами становится выпадающим.
 */
function menu_tree(string $name): array
{
    static $cache = [];
    if (!isset($cache[$name])) {
        try {
            $cache[$name] = (new \App\Models\Menu())->tree($name);
        } catch (\Throwable $error) {
            $cache[$name] = [];
        }
    }
    return $cache[$name];
}

/**
 * Пункты меню (без обёртки <ul>). Если меню пустое — стандартные
 * пять пунктов шапки, чтобы сайт не остался без навигации.
 */
function menu_items_html(string $name): string
{
    return menu_items_html_from_tree(menu_tree($name), $name);
}

function menu_items_html_from_tree(array $tree, string $name): string
{
    if (empty($tree['roots'])) {
        if ($name !== 'main') {
            return '';
        }
        // Резервная навигация, пока главное меню не заполнено
        return '<li class="nav-item"><a class="nav-link" href="/">Главная</a></li>'
            . '<li class="nav-item"><a class="nav-link" href="/blog.html">Блог</a></li>'
            . '<li class="nav-item"><a class="nav-link" href="/portfolio.html">Портфолио</a></li>'
            . '<li class="nav-item"><a class="nav-link" href="/order.html">Заказать</a></li>'
            . '<li class="nav-item"><a class="nav-link" href="/contacts.html">Контакты</a></li>';
    }

    // Текущий путь — для подсветки активного пункта
    $currentPath = '/' . trim((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'), '/');

    $html = '';
    foreach ($tree['roots'] as $item) {
        $itemClassFull = trim('nav-item' . (!empty($item['css_class']) ? ' ' . $item['css_class'] : ''));
        $url = (string) $item['url'];
        $isActive = $currentPath === $url;
        $children = $tree['children'][(int) $item['id']] ?? [];

        if ($children) {
            // Подменю — выпадающий список
            $html .= '<li class="' . htmlspecialchars(trim($itemClassFull . ' dropdown'), ENT_QUOTES, 'UTF-8') . '">'
                . '<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
            if ($item['icon'] !== '') {
                $html .= '<i class="bi ' . htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') . '"></i> ';
            }
            $html .= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') . '</a>'
                . '<ul class="dropdown-menu">';
            foreach ($children as $child) {
                $html .= '<li><a class="dropdown-item" href="' . htmlspecialchars((string) $child['url'], ENT_QUOTES, 'UTF-8') . '">'
                    . htmlspecialchars($child['title'], ENT_QUOTES, 'UTF-8') . '</a></li>';
            }
            $html .= '</ul></li>';
            continue;
        }

        $linkClass = 'nav-link';
        $html .= '<li class="' . htmlspecialchars($itemClassFull, ENT_QUOTES, 'UTF-8') . '">'
            . '<a class="' . htmlspecialchars(trim($linkClass . ($isActive ? ' active' : '')), ENT_QUOTES, 'UTF-8') . '"'
            . ($isActive ? ' aria-current="page"' : '')
            . ' href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">';
        if ($item['icon'] !== '') {
            $html .= '<i class="bi ' . htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') . '"></i> ';
        }
        $html .= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') . '</a></li>';
    }
    return $html;
}

/**
 * Готовое меню с обёрткой <ul> — для боковых и произвольных меню.
 *
 * @param string $name      идентификатор меню
 * @param string $class     классы тега ul
 * @param string $itemClass класс пункта (li) верхнего уровня
 * @param string $linkClass класс ссылки (a) пункта
 */
function menu_rubric_tree(string $name): array
{
    return menu_tree('rubric_' . $name);
}

function menu_rubric_items_html(string $name): string
{
    return menu_items_html_from_tree(menu_rubric_tree($name), 'rubric_' . $name);
}

function render_menu_rubric(string $name, string $class = 'navbar-nav', string $itemClass = 'nav-item', string $linkClass = 'nav-link'): string
{
    return render_menu_tree(menu_rubric_tree($name), $class, $itemClass, $linkClass);
}

function render_menu(string $name, string $class = 'navbar-nav', string $itemClass = 'nav-item', string $linkClass = 'nav-link'): string
{
    return render_menu_tree(menu_tree($name), $class, $itemClass, $linkClass);
}

function render_menu_tree(array $tree, string $class = 'navbar-nav', string $itemClass = 'nav-item', string $linkClass = 'nav-link'): string
{
    if (empty($tree['roots'])) {
        return '';
    }
    $currentPath = '/' . trim((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'), '/');
    $menuClass = (string) ($tree['menu']['css_class'] ?? '');

    $html = '<ul class="' . htmlspecialchars(trim($class . ($menuClass !== '' ? ' ' . $menuClass : '')), ENT_QUOTES, 'UTF-8') . '">';
    foreach ($tree['roots'] as $item) {
        $itemClassFull = trim($itemClass . (!empty($item['css_class']) ? ' ' . $item['css_class'] : ''));
        $url = (string) $item['url'];
        $isActive = $currentPath === $url;
        $children = $tree['children'][(int) $item['id']] ?? [];

        if ($children) {
            $html .= '<li class="' . htmlspecialchars(trim($itemClassFull . ' dropdown'), ENT_QUOTES, 'UTF-8') . '">'
                . '<a class="' . htmlspecialchars(trim($linkClass . ' dropdown-toggle'), ENT_QUOTES, 'UTF-8') . '" href="#" role="button"'
                . ' data-bs-toggle="dropdown" aria-expanded="false">';
            if ($item['icon'] !== '') {
                $html .= '<i class="bi ' . htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') . '"></i> ';
            }
            $html .= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') . '</a>'
                . '<ul class="dropdown-menu">';
            foreach ($children as $child) {
                $html .= '<li><a class="dropdown-item" href="' . htmlspecialchars((string) $child['url'], ENT_QUOTES, 'UTF-8') . '">'
                    . htmlspecialchars($child['title'], ENT_QUOTES, 'UTF-8') . '</a></li>';
            }
            $html .= '</ul></li>';
            continue;
        }

        $html .= '<li class="' . htmlspecialchars($itemClassFull, ENT_QUOTES, 'UTF-8') . '">'
            . '<a class="' . htmlspecialchars(trim($linkClass . ($isActive ? ' active' : '')), ENT_QUOTES, 'UTF-8') . '"'
            . ($isActive ? ' aria-current="page"' : '')
            . ' href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">';
        if ($item['icon'] !== '') {
            $html .= '<i class="bi ' . htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') . '"></i> ';
        }
        $html .= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') . '</a></li>';
    }
    return $html . '</ul>';
}
