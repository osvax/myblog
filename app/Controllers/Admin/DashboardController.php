<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Response;

class DashboardController extends Controller
{
    public function index($request)
    {
        if (!\admin_is_authenticated()) {
            return Response::redirect('/admin/login.html');
        }
        $navigation = [
            ['url' => '/mvc/admin', 'icon' => 'bi-speedometer2', 'label' => 'Дашборд', 'active' => true],
            ['url' => '/admin/categories.html', 'icon' => 'bi-tags', 'label' => 'Рубрики', 'divider' => true],
            ['url' => '/admin/blog.html', 'icon' => 'bi-journal-text', 'label' => 'Блог'],
            ['url' => '/admin/portfolio.html', 'icon' => 'bi-briefcase', 'label' => 'Портфолио'],
            ['url' => '/admin/messages.html', 'icon' => 'bi-chat-left-text', 'label' => 'Комментарии'],
            ['url' => '/admin/consultant.html', 'icon' => 'bi-headset', 'label' => 'Консультант', 'divider' => true],
            ['url' => '/admin/orders.html', 'icon' => 'bi-cart-check', 'label' => 'Заказы'],
            ['url' => '/admin/current-orders.html', 'icon' => 'bi-hourglass-split', 'label' => 'Текущие'],
            ['url' => '/admin/new-orders.html', 'icon' => 'bi-bell', 'label' => 'Новые', 'divider' => true],
            ['url' => '/admin/calculator.html', 'icon' => 'bi-calculator', 'label' => 'Калькулятор'],
            ['url' => '/admin/files.html', 'icon' => 'bi-folder2-open', 'label' => 'Файлы'],
            ['url' => '/admin/templates.html', 'icon' => 'bi-code-slash', 'label' => 'Шаблоны'],
            ['url' => '/admin/settings.html', 'icon' => 'bi-gear', 'label' => 'Настройки сайта', 'divider' => true],
            ['url' => '/admin/users.html', 'icon' => 'bi-people', 'label' => 'Пользователи'],
        ];
        $statistics = [
            ['url' => '/admin/users.html', 'icon' => 'bi-people', 'value' => '—', 'label' => 'Пользователей online'],
            ['url' => '/admin/current-orders.html', 'icon' => 'bi-hourglass-split', 'value' => '—', 'label' => 'Текущие заказы'],
            ['url' => '/admin/new-orders.html', 'icon' => 'bi-bell-fill', 'value' => '—', 'label' => 'Новые заказы'],
            ['url' => '/admin/messages.html', 'icon' => 'bi-chat-left-text', 'value' => '—', 'label' => 'Комментарии'],
        ];
        $actions = [
            ['url' => '/admin/blog.html', 'icon' => 'bi-plus-circle', 'label' => 'Добавить статью'],
            ['url' => '/admin/portfolio.html', 'icon' => 'bi-plus-circle', 'label' => 'Добавить работу'],
            ['url' => '/admin/users.html', 'icon' => 'bi-person-plus', 'label' => 'Добавить пользователя'],
        ];
        return $this->page('admin', 'home', [
            'title' => 'Дашборд — Админ-панель',
            'pageTitle' => 'Дашборд',
            'navigation' => $navigation,
            'statistics' => $statistics,
            'actions' => $actions,
        ]);
    }
}
