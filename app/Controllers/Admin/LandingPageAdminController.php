<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\ApiResource;
use App\Models\LandingPage;
use App\Models\LandingPageManager;
use App\Models\SettingsRepository;

class LandingPageAdminController extends Controller
{
    private $landingPage;
    private $landingPageManager;
    private $settings;

    public function __construct(
        LandingPage $landingPage,
        LandingPageManager $landingPageManager,
        SettingsRepository $settings
    ) {
        $this->landingPage = $landingPage;
        $this->landingPageManager = $landingPageManager;
        $this->settings = $settings;
    }

    /**
     * Получить все страницы.
     */
    public function index($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        $pages = $this->landingPage->getAll();

        return $this->json(ApiResource::success(['pages' => $pages]));
    }

    /**
     * Сгенерировать страницу из настроек.
     */
    public function generate($request, $parameters)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $pageSlug = isset($parameters['slug']) ? trim($parameters['slug']) : 'home';
        // ID может быть в query string
        $pageId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        $html = $this->landingPageManager->generate($pageSlug);

        // Получаем заголовок из настроек
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT field_value FROM " . db_table('landing_page_settings') . " WHERE field_group = 'hero' AND field_name = 'title' LIMIT 1");
        $stmt->execute();
        $title = $stmt->fetchColumn() ?: ucfirst($pageSlug);

        $result = $this->landingPage->save($pageSlug, $title, $html, 1, $pageId);

        if (!$result['success']) {
            return $this->json(ApiResource::error($result['error'] ?? 'Не удалось сохранить'), 500);
        }

        return $this->json(ApiResource::success([
            'id' => $result['id'],
            'html_length' => strlen($html),
        ]));
    }

    /**
     * Создать или обновить страницу.
     */
    public function create($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $input = $request->json();
        $slug = isset($input['slug']) && is_string($input['slug']) ? trim($input['slug']) : '';
        $title = isset($input['title']) && is_string($input['title']) ? trim($input['title']) : '';
        $htmlContent = isset($input['html_content']) && is_string($input['html_content']) ? $input['html_content'] : '';
        $isActive = isset($input['is_active']) ? (int) $input['is_active'] : 1;
        $editId = isset($input['edit_id']) ? (int) $input['edit_id'] : 0;

        if (strlen($slug) < 1 || strlen($slug) > 100) {
            return $this->json(ApiResource::error('Неверный slug страницы'), 400);
        }
        if (strlen($title) < 1 || strlen($title) > 254) {
            return $this->json(ApiResource::error('Неверный заголовок'), 400);
        }

        $result = $this->landingPage->save($slug, $title, $htmlContent, $isActive, $editId);

        if (!$result['success']) {
            return $this->json(ApiResource::error($result['error'] ?? 'Не удалось сохранить'), 500);
        }

        return $this->json(ApiResource::success(['id' => $result['id'], 'action' => $result['action']]));
    }

    /**
     * Удалить страницу.
     */
    public function delete($request, $parameters)
    {
        if ($request->method() !== 'DELETE') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $pageId = isset($parameters['id']) ? (int) $parameters['id'] : 0;

        if ($pageId <= 0) {
            return $this->json(ApiResource::error('Неверный ID страницы'), 400);
        }

        $result = $this->landingPage->delete($pageId);

        if (!$result['success']) {
            return $this->json(ApiResource::error('Не удалось удалить'), 404);
        }

        return $this->json(ApiResource::success());
    }

    /**
     * Переключить активность.
     */
    public function toggleActive($request, $parameters)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $pageId = isset($parameters['id']) ? (int) $parameters['id'] : 0;

        if ($pageId <= 0) {
            return $this->json(ApiResource::error('Неверный ID страницы'), 400);
        }

        $result = $this->landingPage->toggleActive($pageId);

        if (!$result['success']) {
            return $this->json(ApiResource::error('Не удалось переключить'), 500);
        }

        return $this->json(ApiResource::success());
    }

    /**
     * Получить страницу по slug.
     */
    public function show($request, $parameters)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        $slug = isset($parameters['slug']) ? trim($parameters['slug']) : 'home';

        $page = $this->landingPage->findBySlug($slug);

        if (!$page) {
            return $this->json(ApiResource::error('Страница не найдена'), 404);
        }

        return $this->json(ApiResource::success(['page' => $page]));
    }

    /**
     * Живой предпросмотр страницы (кнопка «Просмотр» в разделе «Страницы»).
     * Отрендеренное тело с раскрытыми плейсхолдерами отдаётся как HTML —
     * независимо от флага активности, чтобы можно было посмотреть
     * и неактивную страницу. Доступ — как у всего /api/admin/*
     * (авторизация + право на раздел), проверяются до роутера.
     */
    public function preview($request, $parameters)
    {
        if ($request->method() !== 'GET') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }

        $pageId = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($pageId <= 0) {
            return $this->json(ApiResource::error('Неверный ID страницы'), 400);
        }

        $page = $this->landingPage->findById($pageId);
        if (!$page) {
            return $this->json(ApiResource::error('Страница не найдена'), 404);
        }

        $html = trim((string) ($page['html_content'] ?? ''));
        if ($html === '') {
            $html = '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8">'
                . '<title>Пустая страница</title></head>'
                . '<body style="font-family: sans-serif; padding: 3rem; text-align: center; color: #666;">'
                . '<h2>Тело страницы пока пустое</h2>'
                . '<p>Заполните его в разделе «Страницы» и сохраните.</p>'
                . '</body></html>';
        } else {
            $html = render_static_page($html);
        }

        return new \App\Core\Response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
