<?php

namespace App\Controllers\Frontend;

use App\Controllers\Controller;

class OrderController extends Controller
{
    public function index($request)
    {
        $user = current_user();

        return $this->page('frontend', 'order', [
            'title' => 'Заказать',
            'description' => 'Оставьте заявку на разработку сайта.',
            'activePage' => 'order',
            'includeFormsScript' => true,
            'pageScripts' => ['/js/calculator-config.js', '/js/order-calculator.js'],
            // Конфигурация калькулятора из базы (редактируется в админке)
            'calculatorConfig' => \calculator_config(),
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
        ]);
    }
}
