<?php

namespace App\Controllers\Frontend;

use App\Controllers\Controller;

class ContactsController extends Controller
{
    public function index($request)
    {
        $user = current_user();

        // Контактные данные редактируются в админке (Настройки → Контакты)
        $contacts = (new \App\Models\SiteSettings())->all();

        return $this->page('frontend', 'contacts', [
            'title' => 'Контакты — Мой Блог',
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
