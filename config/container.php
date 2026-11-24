<?php

use App\Core\Config;
use App\Core\Database;
use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Core\Logger;
use App\Core\ApplicationLogger;
use App\Core\RateLimiter;
use App\Core\Mailer;
use App\Core\Sanitizer;
use App\Core\VisitLogger;
use App\Models\ContentRepository;
use App\Models\OrderRepository;
use App\Models\MessageRepository;
use App\Models\UserRepository;
use App\Models\SettingsRepository;
use App\Models\User;
use App\Models\SiteSettings;
use App\Models\Role;
use App\Models\Menu;
use App\Models\LandingPage;
use App\Models\LandingPageManager;
use App\Models\LandingPageSetting;
use App\Models\CustomField;
use App\Models\Consultant;
use App\Models\AdminUser;
use App\Models\PortfolioImporter;
use App\Models\UserActivity;
use App\Controllers\Frontend\HomeController;
use App\Controllers\Frontend\BlogController;
use App\Controllers\Frontend\PortfolioController;
use App\Controllers\Frontend\ContactsController;
use App\Controllers\Frontend\OrderController;
use App\Controllers\Frontend\AuthController;
use App\Controllers\Frontend\ProfileController;
use App\Controllers\Frontend\SeoController;
use App\Controllers\Admin\PageController;
use App\Controllers\Admin\ContentController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\SiteSettingsController;
use App\Controllers\Admin\UsersController;
use App\Controllers\Admin\RolesController;
use App\Controllers\Admin\MenuController;
use App\Controllers\Admin\MediaController;
use App\Controllers\Admin\FileManagerController;
use App\Controllers\Admin\LandingPageController;
use App\Controllers\Admin\LandingPageAdminController;
use App\Controllers\Admin\CustomFieldController;
use App\Controllers\Admin\CalculatorController;
use App\Controllers\Admin\AuthController as AdminAuthController;
use App\Controllers\Admin\AdminProfileController;
use App\Controllers\Admin\TemplateController;
use App\Controllers\Admin\SystemController;
use App\Controllers\Api\FormController;
use App\Controllers\Api\ConsultantController;
use App\Transformers\ArticleTransformer;
use App\Transformers\ProjectTransformer;
use App\Transformers\CategoryTransformer;
use App\Transformers\OrderTransformer;
use App\Transformers\MessageTransformer;
use App\Transformers\UserTransformer;
use App\Transformers\SiteSettingsTransformer;
use DI\ContainerBuilder;

return function (ContainerBuilder $builder) {
    // ============ Core Services ============
    $builder->addDefinitions([
        // Config — singleton, загружается один раз
        Config::class => function () {
            return new Config();
        },

        // Database — lazy singleton через Database::connection()
        Database::class => function () {
            return Database::connection();
        },

        // Router
        Router::class => function () {
            return new Router();
        },

        // Request/Response — новые экземпляры на запрос
        Request::class => function () {
            return Request::fromGlobals();
        },

        Response::class => function () {
            return new Response();
        },

        // View
        View::class => function () {
            return new View();
        },

        // Logger (БД — для админки)
        Logger::class => function () {
            return new Logger();
        },

        // ApplicationLogger (файлы JSON — для ошибок приложения)
        ApplicationLogger::class => function () {
            return new ApplicationLogger();
        },

        // VisitLogger
        VisitLogger::class => function () {
            return new VisitLogger();
        },

        // RateLimiter
        RateLimiter::class => function (Config $config) {
            return new RateLimiter($config->redis());
        },

        // Mailer
        Mailer::class => function (Config $config) {
            return new Mailer($config->mail());
        },

        // Sanitizer
        Sanitizer::class => function () {
            return new Sanitizer();
        },

        // Transformers — stateless, можно как singleton
        ArticleTransformer::class => function () {
            return new ArticleTransformer();
        },

        ProjectTransformer::class => function () {
            return new ProjectTransformer();
        },

        CategoryTransformer::class => function () {
            return new CategoryTransformer();
        },

        OrderTransformer::class => function () {
            return new OrderTransformer();
        },

        MessageTransformer::class => function () {
            return new MessageTransformer();
        },

        UserTransformer::class => function () {
            return new UserTransformer();
        },

        SiteSettingsTransformer::class => function () {
            return new SiteSettingsTransformer();
        },
    ]);

    // ============ Models / Repositories ============
    $builder->addDefinitions([
        // Repositories with constructor injection
        ContentRepository::class => function () {
            return new ContentRepository();
        },

        OrderRepository::class => function () {
            return new OrderRepository();
        },

        MessageRepository::class => function () {
            return new MessageRepository();
        },

        UserRepository::class => function () {
            return new UserRepository();
        },

        SettingsRepository::class => function (ApplicationLogger $logger) {
            return new SettingsRepository(null, $logger);
        },

        // Models using static Database::connection() - no injection needed
        LandingPageSetting::class => function () {
            return new LandingPageSetting();
        },

        User::class => function () {
            return new User();
        },

        SiteSettings::class => function () {
            return new SiteSettings();
        },

        Role::class => function () {
            return new Role();
        },

        Menu::class => function () {
            return new Menu();
        },

        LandingPage::class => function () {
            return new LandingPage();
        },

        LandingPageManager::class => function () {
            return new LandingPageManager();
        },

        CustomField::class => function () {
            return new CustomField();
        },

        Consultant::class => function () {
            return new Consultant();
        },

        AdminUser::class => function () {
            return new AdminUser();
        },

        PortfolioImporter::class => function () {
            return new PortfolioImporter();
        },

        UserActivity::class => function () {
            return new UserActivity();
        },
    ]);

    // ============ Frontend Controllers ============
    $builder->addDefinitions([
        HomeController::class => function (ContentRepository $content, SiteSettings $settings, LandingPage $landingPage) {
            return new HomeController($content, $settings, $landingPage);
        },

        BlogController::class => function (ContentRepository $content, SiteSettings $settings) {
            return new BlogController($content, $settings);
        },

        PortfolioController::class => function (ContentRepository $content, SiteSettings $settings) {
            return new PortfolioController($content, $settings);
        },

        ContactsController::class => function (SiteSettings $settings, Mailer $mailer, RateLimiter $rateLimiter) {
            return new ContactsController($settings, $mailer, $rateLimiter);
        },

        OrderController::class => function (SiteSettings $settings) {
            return new OrderController($settings);
        },

        AuthController::class => function (UserRepository $users, SiteSettings $settings, Mailer $mailer, RateLimiter $rateLimiter) {
            return new AuthController($users, $settings, $mailer, $rateLimiter);
        },

        ProfileController::class => function (UserRepository $users, SiteSettings $settings) {
            return new ProfileController($users, $settings);
        },

        SeoController::class => function (ContentRepository $content, SiteSettings $settings) {
            return new SeoController($content, $settings);
        },
    ]);

    // ============ Admin Controllers ============
    $builder->addDefinitions([
        ContentController::class => function (
            ContentRepository $content,
            OrderRepository $orders,
            MessageRepository $messages,
            ArticleTransformer $articleTransformer,
            ProjectTransformer $projectTransformer,
            CategoryTransformer $categoryTransformer,
            OrderTransformer $orderTransformer,
            MessageTransformer $messageTransformer,
            ApplicationLogger $logger
        ) {
            return new ContentController(
                $content,
                $orders,
                $messages,
                $articleTransformer,
                $projectTransformer,
                $categoryTransformer,
                $orderTransformer,
                $messageTransformer,
                $logger
            );
        },

        DashboardController::class => function (ContentRepository $content, SettingsRepository $settings, UserActivity $activity) {
            return new DashboardController($content, $settings, $activity);
        },

        PageController::class => function (UserRepository $users, Role $role) {
            return new PageController($users, $role);
        },

        SiteSettingsController::class => function (SettingsRepository $settings, UserActivity $activity, SiteSettingsTransformer $transformer) {
            return new SiteSettingsController($settings, $activity, $transformer);
        },

        UsersController::class => function (UserRepository $users, Role $role, SiteSettings $settings, UserTransformer $userTransformer, ApplicationLogger $logger) {
            return new UsersController($users, $role, $settings, $userTransformer, $logger);
        },

        RolesController::class => function (Role $role) {
            return new RolesController($role);
        },

        MenuController::class => function (Menu $menu, ContentRepository $content, LandingPage $landingPage, SiteSettings $settings) {
            return new MenuController($menu, $content, $landingPage, $settings);
        },

        MediaController::class => function (ApplicationLogger $logger) {
            return new MediaController($logger);
        },

        FileManagerController::class => function (ApplicationLogger $logger) {
            return new FileManagerController($logger);
        },

        LandingPageController::class => function (LandingPageSetting $settings) {
            return new LandingPageController($settings);
        },

        LandingPageAdminController::class => function (LandingPage $landingPage, LandingPageManager $landingPageManager, SettingsRepository $settings) {
            return new LandingPageAdminController($landingPage, $landingPageManager, $settings);
        },

        CustomFieldController::class => function (CustomField $customField) {
            return new CustomFieldController($customField);
        },

        CalculatorController::class => function (SiteSettings $settings) {
            return new CalculatorController($settings);
        },

        AdminAuthController::class => function (AdminUser $adminUser, ApplicationLogger $logger) {
            return new AdminAuthController($adminUser, $logger);
        },

        AdminProfileController::class => function (UserRepository $users, UserActivity $activity, UserTransformer $userTransformer) {
            return new AdminProfileController($users, $activity, $userTransformer);
        },

        TemplateController::class => function (SiteSettings $settings, ApplicationLogger $logger) {
            return new TemplateController($settings, $logger);
        },

        SystemController::class => function (UserActivity $activity, SiteSettings $settings) {
            return new SystemController($activity, $settings);
        },
    ]);

    // ============ API Controllers ============
    $builder->addDefinitions([
        FormController::class => function (ContentRepository $content, Mailer $mailer, RateLimiter $rateLimiter, Sanitizer $sanitizer) {
            return new FormController($content, $mailer, $rateLimiter, $sanitizer);
        },

        ConsultantController::class => function (Consultant $consultant, RateLimiter $rateLimiter, ApplicationLogger $logger) {
            return new ConsultantController($logger);
        },
    ]);
};