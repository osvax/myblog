<?php

namespace App\Controllers\Frontend;

use App\Controllers\Controller;
use App\Models\SiteSettings;

class ContactsController extends Controller
{
    private $settings;

    public function __construct(SiteSettings $settings)
    {
        $this->settings = $settings;
    }

    public function index($request)
    {
        $user = current_user();

        // Контактные данные редактируются в админке (Настройки → Контакты)
        $contacts = $this->settings->all();

        return $this->page('frontend', 'contacts', [
            'title' => 'Контакты',
            'description' => 'Контактная информация и форма обратной связи.',
            'activePage' => 'contacts',
            'includeFormsScript' => true,
            'contacts' => $contacts,
            'contactsHeading' => (string) ($contacts['contactsTitle'] ?? '') ?: 'Контакты',
            'contactsHeadingLead' => (string) ($contacts['contactsLead'] ?? '') ?: 'Буду рад связи и сотрудничеству',
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
        ]);
    }
}
