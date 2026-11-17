<?php

namespace App\Controllers\Frontend;

use App\Controllers\Controller;

class ContactsController extends Controller
{
    public function index($request)
    {
        $user = current_user();

        return $this->page('frontend', 'contacts', [
            'title' => 'Контакты — Мой Блог',
            'description' => 'Контактная информация и форма обратной связи.',
            'activePage' => 'contacts',
            'includeFormsScript' => true,
            'includeSiteSettings' => true,
            'user' => $user,
            'isAdmin' => $user && $user['role'] === 'admin',
        ]);
    }
}
