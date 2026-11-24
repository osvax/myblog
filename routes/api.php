<?php

use App\Core\Router;

return function (Router $router) {
    // Controllers from container
    $auth = container(\App\Controllers\Admin\AuthController::class);
    $consultant = container(\App\Controllers\Api\ConsultantController::class);
    $content = container(\App\Controllers\Admin\ContentController::class);
    $system = container(\App\Controllers\Admin\SystemController::class);
    $files = container(\App\Controllers\Admin\FileManagerController::class);
    $media = container(\App\Controllers\Admin\MediaController::class);
    $templates = container(\App\Controllers\Admin\TemplateController::class);
    $forms = container(\App\Controllers\Api\FormController::class);
    $users = container(\App\Controllers\Admin\UsersController::class);
    $profile = container(\App\Controllers\Admin\AdminProfileController::class);
    $siteSettings = container(\App\Controllers\Admin\SiteSettingsController::class);
    $customFields = container(\App\Controllers\Admin\CustomFieldController::class);
    $calculator = container(\App\Controllers\Admin\CalculatorController::class);
    $menus = container(\App\Controllers\Admin\MenuController::class);
    $roles = container(\App\Controllers\Admin\RolesController::class);
    $landingPage = container(\App\Controllers\Admin\LandingPageController::class);
    $landingPageAdmin = container(\App\Controllers\Admin\LandingPageAdminController::class);

    $router->post('/api/auth/login', [$auth, 'login']);
    $router->get('/api/auth/csrf', [$auth, 'csrf']);
    $router->get('/api/auth/check', [$auth, 'check']);
    $router->post('/api/auth/logout', [$auth, 'logout']);
    $router->post('/api/consultant', [$consultant, 'handle']);

    $router->post('/api/admin/messages/{id}/reply', [$content, 'replyToMessage']);
    $router->post('/api/admin/articles/{id}/duplicate', [$content, 'duplicateArticle']);
    $router->post('/api/admin/projects/{id}/duplicate', [$content, 'duplicateProject']);
    $router->get('/api/admin/articles', [$content, 'articles']);
    $router->post('/api/admin/articles', [$content, 'articles']);
    $router->get('/api/admin/articles/{id}', [$content, 'articles']);
    $router->put('/api/admin/articles/{id}', [$content, 'articles']);
    $router->delete('/api/admin/articles/{id}', [$content, 'articles']);

    $router->get('/api/admin/projects', [$content, 'projects']);
    $router->post('/api/admin/projects', [$content, 'projects']);
    $router->get('/api/admin/projects/{id}', [$content, 'projects']);
    $router->put('/api/admin/projects/{id}', [$content, 'projects']);
    $router->delete('/api/admin/projects/{id}', [$content, 'projects']);

    $router->get('/api/admin/categories', [$content, 'categories']);
    $router->post('/api/admin/categories', [$content, 'categories']);
    $router->put('/api/admin/categories/{id}', [$content, 'categories']);
    $router->delete('/api/admin/categories/{id}', [$content, 'categories']);
    $router->get('/api/admin/categories/{id}/navigation', [$content, 'categoryNavigation']);
    $router->post('/api/admin/categories/{id}/navigation', [$content, 'categoryNavigation']);
    $router->patch('/api/admin/categories/{id}/navigation', [$content, 'categoryNavigation']);
    $router->put('/api/admin/categories/{id}/navigation/{item_id}', [$content, 'categoryNavigation']);
    $router->delete('/api/admin/categories/{id}/navigation/{item_id}', [$content, 'categoryNavigation']);

    $router->get('/api/admin/orders', [$content, 'orders']);
    $router->post('/api/admin/orders/export', [$content, 'exportOrders']);
    $router->put('/api/admin/orders/{id}', [$content, 'orders']);
    $router->patch('/api/admin/orders/{id}', [$content, 'orders']);
    $router->delete('/api/admin/orders/{id}', [$content, 'orders']);

    $router->get('/api/admin/messages', [$content, 'messages']);
    $router->patch('/api/admin/messages/{id}', [$content, 'messages']);
    $router->delete('/api/admin/messages/{id}', [$content, 'messages']);
    $router->get('/api/admin/system', [$system, 'info']);
    $router->get('/api/admin/dashboard/statistics', [$system, 'statistics']);
    $router->get('/api/admin/dashboard/online', [$system, 'online']);
    $router->get('/api/admin/statistics/overview', [$system, 'overview']);
    $router->get('/api/admin/statistics/hourly', [$system, 'hourly']);
    $router->get('/api/admin/statistics/traffic', [$system, 'traffic']);
    $router->post('/api/admin/statistics/clean', [$system, 'clean']);
    $router->get('/api/admin/files', [$files, 'handle']);
    $router->post('/api/admin/files', [$files, 'handle']);
    $router->post('/api/admin/media', [$media, 'upload']);
    $router->post('/api/admin/avatar', [$media, 'avatar']);
    $router->get('/api/admin/avatar/{filename}', [$media, 'avatar']);
    $router->head('/api/admin/avatar/{filename}', [$media, 'avatar']);
    $router->get('/uploads/{scope}/{id}/{filename}', [$media, 'serve']);
    $router->head('/uploads/{scope}/{id}/{filename}', [$media, 'serve']);
    $router->get('/api/admin/templates', [$templates, 'handle']);
    $router->post('/api/admin/templates', [$templates, 'handle']);

    // ---------- Калькулятор заказа ----------
    $router->get('/api/admin/calculator', [$calculator, 'get']);
    $router->post('/api/admin/calculator', [$calculator, 'save']);

    // ---------- Меню сайта ----------
    $router->get('/api/admin/menus', [$menus, 'handle']);
    $router->post('/api/admin/menus', [$menus, 'handle']);
    $router->get('/api/admin/rubric-menus', [$menus, 'handleRubric']);
    $router->post('/api/admin/rubric-menus', [$menus, 'handleRubric']);
    $router->delete('/api/admin/menus/{id}/delete-item', [$menus, 'deleteItem']);
    $router->delete('/api/admin/menus/{name}/delete-menu', [$menus, 'deleteMenu']);
    $router->delete('/api/admin/rubric-menus/{id}/delete-item', [$menus, 'deleteRubricItem']);
    $router->delete('/api/admin/rubric-menus/{name}/delete-menu', [$menus, 'deleteRubricMenu']);

    $router->post('/api/forms/order', [$forms, 'order']);
    $router->post('/api/forms/feedback', [$forms, 'feedback']);
    $router->post('/api/forms/comment', [$forms, 'comment']);

    // ---------- Пользователи ----------
    $router->get('/api/admin/users', [$users, 'users']);
    $router->get('/api/admin/users/{id}', [$users, 'getUser']);
    $router->post('/api/admin/users', [$users, 'createUser']);
    $router->put('/api/admin/users/{id}', [$users, 'updateUser']);
    $router->delete('/api/admin/users/{id}', [$users, 'deleteUser']);
    $router->post('/api/admin/users/{id}/avatar', [$users, 'uploadAvatar']);
    $router->post('/api/admin/users/{id}/password', [$users, 'resetPassword']);

    // ---------- Роли и права ----------
    $router->get('/api/admin/roles', [$roles, 'index']);
    $router->post('/api/admin/roles', [$roles, 'create']);
    $router->put('/api/admin/roles/{id}', [$roles, 'update']);
    $router->delete('/api/admin/roles/{id}', [$roles, 'delete']);

    // ---------- Профиль пользователя ----------
    $router->get('/api/admin/profile/{id}', [$profile, 'show']);
    $router->put('/api/admin/profile/{id}', [$profile, 'update']);
    $router->post('/api/admin/profile/{id}/avatar', [$profile, 'uploadAvatar']);
    $router->post('/api/admin/profile/{id}/password', [$profile, 'changePassword']);

    // ---------- Настройки сайта ----------
    $router->get('/api/admin/settings', [$siteSettings, 'index']);
    $router->put('/api/admin/settings', [$siteSettings, 'save']);
    $router->post('/api/admin/settings/fields', [$siteSettings, 'addField']);
    $router->delete('/api/admin/settings/fields/{key}', [$siteSettings, 'deleteField']);
    $router->post('/api/admin/settings/test-email', [$siteSettings, 'testEmail']);
    $router->post('/api/admin/settings/font', [$siteSettings, 'font']);
    $router->post('/api/admin/settings/contacts-about-photo', [$siteSettings, 'contactsAboutPhoto']);
    $router->get('/uploads/contacts/{filename}', [$siteSettings, 'serveContactsAboutPhoto']);
    $router->head('/uploads/contacts/{filename}', [$siteSettings, 'serveContactsAboutPhoto']);
    $router->get('/api/admin/settings/logs', [$siteSettings, 'logs']);
    $router->post('/api/admin/settings/logs/clear', [$siteSettings, 'clearLogs']);

    // ---------- Дополнительные настройки лендинга ----------
    $router->get('/api/admin/landing-page', [$landingPage, 'index']);
    $router->post('/api/admin/landing-page', [$landingPage, 'create']);
    $router->delete('/api/admin/landing-page/{id}', [$landingPage, 'delete']);
    $router->post('/api/admin/landing-page/{id}/toggle', [$landingPage, 'toggleActive']);

    // ---------- Управление страницами ----------
    $router->get('/api/admin/pages', [$landingPageAdmin, 'index']);
    $router->get('/api/admin/pages/{slug}', [$landingPageAdmin, 'show']);
    $router->get('/api/admin/pages/{id}/preview', [$landingPageAdmin, 'preview']);
    $router->post('/api/admin/pages', [$landingPageAdmin, 'create']);
    $router->post('/api/admin/pages/{slug}/generate', [$landingPageAdmin, 'generate']);
    $router->delete('/api/admin/pages/{id}', [$landingPageAdmin, 'delete']);
    $router->post('/api/admin/pages/{id}/toggle', [$landingPageAdmin, 'toggleActive']);

    // ---------- Пользовательские поля ----------
    $router->get('/api/admin/custom-fields', [$customFields, 'index']);
    $router->post('/api/admin/custom-fields', [$customFields, 'save']);
    $router->post('/api/admin/custom-fields/{id}/image', [$customFields, 'uploadImage']);
    $router->post('/api/admin/custom-fields/{id}/toggle', [$customFields, 'toggleActive']);
    $router->delete('/api/admin/custom-fields/{id}', [$customFields, 'delete']);
};