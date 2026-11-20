<?php

namespace App\Controllers\Frontend;

use App\Controllers\Controller;

class OrderController extends Controller
{
    public function index($request)
    {
        $user = current_user();
        $settings = new \App\Models\SiteSettings();

        return $this->page('frontend', 'order', [
            'title' => 'Заказать',
            'description' => 'Оставьте заявку на разработку сайта.',
            'activePage' => 'order',
            'includeFormsScript' => true,
            'pageScripts' => ['/js/calculator-config.js', '/js/order-calculator.js'],
            // Конфигурация калькулятора из базы (редактируется в админке)
            'calculatorConfig' => \calculator_config(),
            // Заголовок и подзаголовок из настроек (Настройки → Заголовки → Заказать)
            'orderHeading' => trim((string) $settings->get('orderTitle', '')) ?: 'Заказать',
            'orderHeadingLead' => trim((string) $settings->get('orderLead', '')) ?: 'Расскажите о своей задаче — я свяжусь с вами',
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
        ]);
    }
}
