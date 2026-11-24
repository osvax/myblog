<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Controllers\Controller;
use App\Models\SiteSettings;

final class OrderController extends Controller
{
    private $settings;

    public function __construct(SiteSettings $settings)
    {
        $this->settings = $settings;
    }

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
            // Заголовок и подзаголовок из настроек (Настройки → Заголовки → Заказать)
            'orderHeading' => trim((string) $this->settings->get('orderTitle', '')) ?: 'Заказать',
            'orderHeadingLead' => trim((string) $this->settings->get('orderLead', '')) ?: 'Расскажите о своей задаче — я свяжусь с вами',
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
        ]);
    }
}
