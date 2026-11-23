<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\ContentRepository;
use App\Models\LandingPage;
use App\Models\Menu;

/**
 * API построителя меню: редактирование текущего меню, создание новых,
 * заготовки, пункты-ссылки и пункты из статических страниц/рубрик/статей.
 */
class MenuController extends Controller
{
    private string $menuPrefix = '';

    private function scopedMenuName(string $name): string
    {
        return $this->menuPrefix . $name;
    }

    private function scopedMenu(string $name, Menu $model): ?array
    {
        $scopedName = $this->scopedMenuName($name);
        return preg_match('/^[a-z0-9_-]{1,80}$/', $scopedName) ? $model->menuByName($scopedName) : null;
    }

    public function handleRubric($request)
    {
        $this->menuPrefix = 'rubric_';
        return $this->handle($request);
    }

    public function deleteRubricItem($request, $parameters)
    {
        $this->menuPrefix = 'rubric_';
        return $this->deleteItem($request, $parameters);
    }

    public function deleteRubricMenu($request, $parameters)
    {
        $this->menuPrefix = 'rubric_';
        return $this->deleteMenu($request, $parameters);
    }

    public function handle($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }

        if ($request->method() === 'GET') {
            $action = isset($_GET['action']) && is_string($_GET['action']) ? $_GET['action'] : 'items';
            if ($action === 'menus') {
                return $this->json(['success' => true, 'menus' => (new Menu())->menusByPrefix($this->menuPrefix)]);
            }
            if ($action === 'items') {
                $menuName = isset($_GET['menu']) && is_string($_GET['menu']) ? $_GET['menu'] : 'main';
                $scopedMenuName = $this->scopedMenuName($menuName);
                if (!preg_match('/^[a-z0-9_-]{1,80}$/', $scopedMenuName)) {
                    return $this->json(['success' => false, 'error' => 'Некорректный идентификатор меню'], 400);
                }
                $model = new Menu();
                $menu = $model->menuByName($scopedMenuName);
                return $this->json([
                    'success' => true,
                    'menu' => $menu,
                    'items' => $menu ? $model->items((int) $menu['id']) : [],
                ]);
            }
            if ($action === 'pickers') {
                // Источники для пунктов: статические страницы, рубрики, статьи
                $pages = (new LandingPage())->getAll();
                $content = new ContentRepository();
                return $this->json([
                    'success' => true,
                    'pages' => $pages,
                    'categories' => $content->categories(),
                    'articles' => $content->articles(),
                    'menuItems' => $this->menuPrefix === 'rubric_' ? array_merge([], ...array_map(static function (array $menu): array {
                        return (new Menu())->items((int) $menu['id']);
                    }, (new Menu())->menus())) : [],
                ]);
            }
            return $this->json(['success' => false, 'error' => 'Некорректный запрос'], 400);
        }

        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }
        \admin_require_csrf();

        $input = $request->json();
        $action = isset($input['action']) && is_string($input['action']) ? $input['action'] : 'save';
        $model = new Menu();

        // Создать пустое меню
        if ($action === 'create') {
            $name = isset($input['menu_name']) && is_string($input['menu_name']) ? trim($input['menu_name']) : '';
            $title = isset($input['title']) && is_string($input['title']) ? trim($input['title']) : '';
            if (!preg_match('/^[a-z0-9_-]{1,80}$/', $name)) {
                return $this->json(['success' => false, 'error' => 'Идентификатор: латиница, цифры, - и _ (до 80)'], 400);
            }
            $scopedName = $this->scopedMenuName($name);
            if (!preg_match('/^[a-z0-9_-]{1,80}$/', $scopedName)) {
                return $this->json(['success' => false, 'error' => 'Слишком длинный идентификатор меню'], 400);
            }
            $result = $model->createMenu($scopedName, $title !== '' ? $title : ucfirst($name));
            if (!$result['success']) {
                return $this->json(['success' => false, 'error' => $result['error']], 400);
            }
            return $this->json(['success' => true, 'menu_name' => $name]);
        }

        // Создать меню из заготовки
        if ($action === 'preset') {
            if ($this->menuPrefix !== '') {
                return $this->json(['success' => false, 'error' => 'Заготовки доступны только для обычных меню'], 400);
            }
            return $this->createPreset($model, $input);
        }

        // Сохранить порядок пунктов перетаскиванием.
        if ($action === 'reorder') {
            $menuName = isset($input['menu_name']) && is_string($input['menu_name']) ? $input['menu_name'] : '';
            $menu = $this->scopedMenu($menuName, $model);
            $itemIds = isset($input['item_ids']) && is_array($input['item_ids']) ? $input['item_ids'] : [];
            $parentId = isset($input['parent_id']) && $input['parent_id'] !== null ? (int) $input['parent_id'] : null;
            if (!$menu || !$itemIds) {
                return $this->json(['success' => false, 'error' => 'Некорректный порядок пунктов'], 400);
            }
            $result = $model->reorderItems((int) $menu['id'], $parentId, $itemIds);
            return $result['success']
                ? $this->json(['success' => true])
                : $this->json(['success' => false, 'error' => $result['error']], 400);
        }

        // Переместить пункт
        if ($action === 'move') {
            $id = isset($input['id']) ? (int) $input['id'] : 0;
            $direction = isset($input['direction']) && $input['direction'] === 'up' ? 'up' : 'down';
            $result = $model->moveItem($id, $direction);
            if (!$result['success']) {
                return $this->json(['success' => false, 'error' => $result['error']], 400);
            }
            return $this->json(['success' => true]);
        }

        // Переключить активность пункта
        if ($action === 'toggle') {
            $id = isset($input['id']) ? (int) $input['id'] : 0;
            $result = $model->toggleActive($id);
            if (!$result['success']) {
                return $this->json(['success' => false, 'error' => 'Пункт не найден'], 404);
            }
            return $this->json(['success' => true]);
        }

        // Создать/обновить пункт
        if ($action === 'save') {
            $id = isset($input['id']) ? (int) $input['id'] : 0;
            $menuName = isset($input['menu_name']) && is_string($input['menu_name']) ? $input['menu_name'] : '';
            $menu = $this->scopedMenu($menuName, $model);
            if (!$menu && $this->menuPrefix === 'rubric_' && $menuName === 'main' && !$model->menusByPrefix('rubric_')) {
                $model->createMenu('rubric_main', 'Рубрики блога');
                $menu = $model->menuByName('rubric_main');
            }
            if (!$menu) {
                return $this->json(['success' => false, 'error' => 'Меню не найдено'], 404);
            }
            $title = isset($input['title']) && is_string($input['title']) ? trim($input['title']) : '';
            $url = isset($input['url']) && is_string($input['url']) ? trim($input['url']) : '';
            $icon = isset($input['icon']) && is_string($input['icon']) ? trim($input['icon'], 'bi ') : '';
            $cssClass = isset($input['css_class']) && is_string($input['css_class']) ? trim($input['css_class']) : '';
            $parentId = isset($input['parent_id']) && $input['parent_id'] !== null && $input['parent_id'] !== '' ? (int) $input['parent_id'] : null;
            // По умолчанию пункт активен, если поле не передано
            $isActive = isset($input['is_active']) ? (!empty($input['is_active']) ? 1 : 0) : 1;

            if ($this->menuPrefix === 'rubric_' && !preg_match('#^(?:/(?!/)|https?://|\#)#i', $url)) {
                return $this->json(['success' => false, 'error' => 'Укажите локальную ссылку, якорь или URL с http/https'], 400);
            }
            if (mb_strlen($title) < 1 || mb_strlen($title) > 160) {
                return $this->json(['success' => false, 'error' => 'Укажите название пункта (до 160 символов)'], 400);
            }
            if (mb_strlen($url) < 1 || mb_strlen($url) > 255) {
                return $this->json(['success' => false, 'error' => 'Укажите ссылку пункта'], 400);
            }
            if ($icon !== '' && !preg_match('/^[a-z0-9-]{2,80}$/', $icon)) {
                return $this->json(['success' => false, 'error' => 'Иконка: имя Bootstrap Icons, например bi-house'], 400);
            }
            if (mb_strlen($cssClass) > 200) {
                return $this->json(['success' => false, 'error' => 'Слишком длинный CSS-класс'], 400);
            }

            if ($id > 0) {
                $existingItem = $model->itemById($id);
                if (!$existingItem || (int) $existingItem['menu_id'] !== (int) $menu['id']) {
                    return $this->json(['success' => false, 'error' => 'Пункт не найден в выбранном меню'], 404);
                }
            }
            $result = $model->saveItem($id, (int) $menu['id'], $parentId, $title, $url, $icon, $cssClass, $isActive);
            if (!$result['success']) {
                return $this->json(['success' => false, 'error' => $result['error']], 400);
            }
            return $this->json(['success' => true, 'id' => $result['id']]);
        }

        return $this->json(['success' => false, 'error' => 'Неизвестное действие'], 400);
    }

    /**
     * Удалить пункт (подчинённые удалятся каскадом).
     */
    public function deleteItem($request, $parameters)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() !== 'DELETE') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }
        \admin_require_csrf();

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        $model = new Menu();
        $item = $model->itemById($id);
        $itemMenu = $item ? $model->menuById((int) $item['menu_id']) : null;
        if (!$item || !$itemMenu || ($this->menuPrefix !== '' && strpos((string) $itemMenu['menu_name'], $this->menuPrefix) !== 0)) {
            return $this->json(['success' => false, 'error' => 'Пункт не найден'], 404);
        }
        $result = $model->deleteItem($id);
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => 'Пункт не найден'], 404);
        }
        return $this->json(['success' => true]);
    }

    /**
     * Удалить меню целиком (кроме главного).
     */
    public function deleteMenu($request, $parameters)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() !== 'DELETE') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }
        \admin_require_csrf();

        $name = isset($parameters['name']) && is_string($parameters['name']) ? $parameters['name'] : '';
        $model = new Menu();
        $menu = $this->scopedMenu($name, $model);
        if (!$menu || ($this->menuPrefix === '' && $menu['menu_name'] !== $name)) {
            return $this->json(['success' => false, 'error' => 'Меню не найдено'], 404);
        }
        $result = $model->deleteMenu((string) $menu['menu_name']);
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => 'Меню не найдено или главное'], 404);
        }
        return $this->json(['success' => true]);
    }

    /**
     * Заготовки меню: типовые наборы пунктов в один клик.
     */
    private function createPreset(Menu $model, array $input)
    {
        $menuName = isset($input['menu_name']) && is_string($input['menu_name']) ? trim($input['menu_name']) : '';
        $preset = isset($input['preset']) && is_string($input['preset']) ? $input['preset'] : '';
        if (!preg_match('/^[a-z0-9_-]{1,80}$/', $menuName)) {
            return $this->json(['success' => false, 'error' => 'Идентификатор: латиница, цифры, - и _ (до 80)'], 400);
        }
        if ($model->menuByName($menuName)) {
            return $this->json(['success' => false, 'error' => 'Меню с таким идентификатором уже существует'], 400);
        }

        $items = [];
        if ($preset === 'sections') {
            $items = [
                ['title' => 'Главная', 'url' => '/'],
                ['title' => 'Блог', 'url' => '/blog.html'],
                ['title' => 'Портфолио', 'url' => '/portfolio.html'],
                ['title' => 'Заказать', 'url' => '/order.html'],
                ['title' => 'Контакты', 'url' => '/contacts.html'],
            ];
        } elseif ($preset === 'social') {
            // Соцсети из настроек сайта — только заполненные
            $settings = new \App\Models\SiteSettings();
            $socials = [
                ['key' => 'socialTelegram', 'title' => 'Telegram', 'icon' => 'bi-telegram'],
                ['key' => 'socialVk', 'title' => 'VK', 'icon' => 'bi-chat-dots'],
                ['key' => 'socialGithub', 'title' => 'GitHub', 'icon' => 'bi-github'],
                ['key' => 'socialYoutube', 'title' => 'YouTube', 'icon' => 'bi-youtube'],
            ];
            foreach ($socials as $social) {
                $url = trim((string) $settings->get($social['key'], ''));
                if (preg_match('#^https?://#i', $url)) {
                    $items[] = ['title' => $social['title'], 'url' => $url, 'icon' => $social['icon']];
                }
            }
        } elseif ($preset === 'pages') {
            foreach ((new LandingPage())->getAll() as $page) {
                if ((int) ($page['is_active'] ?? 0) === 1 && ($page['slug'] ?? '') !== 'home') {
                    $items[] = ['title' => $page['title'], 'url' => '/' . $page['slug'] . '.html'];
                }
            }
        } elseif ($preset === 'categories') {
            foreach ((new ContentRepository())->categories() as $category) {
                $items[] = ['title' => $category['name'], 'url' => '/category/' . $category['slug'] . '.html', 'icon' => 'bi-tag'];
            }
        }

        if ($items === []) {
            return $this->json(['success' => false, 'error' => 'Заготовка не найдена или пуста (для соцсетей заполните ссылки в настройках)'], 400);
        }

        $result = $model->createMenu($menuName, ucfirst($menuName));
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => $result['error']], 400);
        }
        $menu = $model->menuByName($menuName);
        foreach ($items as $index => $item) {
            $model->saveItem(0, (int) $menu['id'], null, $item['title'], $item['url'], $item['icon'] ?? '', '', 1);
        }
        return $this->json(['success' => true, 'menu_name' => $menuName, 'items' => count($items)]);
    }
}
