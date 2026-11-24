<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\ApiResource;
use App\Core\ApplicationLogger;
use App\Models\SiteSettings;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Редактор шаблонов: выбор шаблона (default и другие), правка его PHP-файлов,
 * общих CSS/JS и статических страниц (через API /api/admin/pages).
 */
class TemplateController extends Controller
{
    private $settings;
    private $logger;

    public function __construct(SiteSettings $settings, ApplicationLogger $logger)
    {
        $this->settings = $settings;
        $this->logger = $logger;
    }

    /**
     * Список шаблонов публичной части и админки.
     */
    private function templates(): array
    {
        $templates = [];
        foreach (['frontend', 'admin'] as $area) {
            $directory = APP_ROOT . '/app/Views/' . $area . '/templates';
            if (!is_dir($directory)) {
                continue;
            }
            foreach (scandir($directory) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..' || !is_dir($directory . '/' . $entry)) {
                    continue;
                }
                $templates[$entry] = true;
            }
        }
        $names = array_keys($templates);
        // Default — всегда первым
        usort($names, static function ($a, $b) {
            if ($a === 'default') {
                return -1;
            }
            if ($b === 'default') {
                return 1;
            }
            return strcasecmp($a, $b);
        });
        return $names;
    }

    /**
     * Активные шаблоны из настроек сайта.
     */
    private function activeTemplates(): array
    {
        return [
            'frontend' => (string) $this->settings->get('frontendTemplate', 'default'),
            'admin' => (string) $this->settings->get('adminTemplate', 'default'),
        ];
    }

    private function templateMetadata(string $area, string $template): array
    {
        $path = APP_ROOT . '/app/Views/' . $area . '/templates/' . $template . '/theme.json';
        if (!is_file($path)) {
            return ['name' => $template, 'description' => '', 'version' => ''];
        }
        $metadata = json_decode((string) file_get_contents($path), true);
        return is_array($metadata) ? [
            'name' => (string) ($metadata['name'] ?? $template),
            'description' => (string) ($metadata['description'] ?? ''),
            'version' => (string) ($metadata['version'] ?? ''),
        ] : ['name' => $template, 'description' => '', 'version' => ''];
    }

    private function templateUpload(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Не удалось загрузить архив'];
        }
        if ((int) ($file['size'] ?? 0) > 5 * 1024 * 1024 || strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION)) !== 'zip') {
            return ['success' => false, 'error' => 'Нужен ZIP-архив размером не более 5 МБ'];
        }
        if (!class_exists('ZipArchive')) {
            return ['success' => false, 'error' => 'На сервере недоступно расширение ZipArchive'];
        }

        $archive = new \ZipArchive();
        if ($archive->open($file['tmp_name']) !== true) {
            return ['success' => false, 'error' => 'Архив повреждён или имеет неверный формат'];
        }
        if ($archive->numFiles > 500) {
            $archive->close();
            return ['success' => false, 'error' => 'В архиве слишком много файлов'];
        }

        $entries = [];
        $totalSize = 0;
        for ($i = 0; $i < $archive->numFiles; $i++) {
            $entry = $archive->statIndex($i);
            $name = str_replace('\\', '/', (string) ($entry['name'] ?? ''));
            if ($name === '' || strpos($name, '/') === 0 || preg_match('#(^|/)\.\.?(/|$)#', $name)) {
                $archive->close();
                return ['success' => false, 'error' => 'Архив содержит небезопасный путь'];
            }
            if (isset($entry['size']) && (int) $entry['size'] > 2 * 1024 * 1024) {
                $archive->close();
                return ['success' => false, 'error' => 'Файл в архиве превышает 2 МБ'];
            }
            $totalSize += (int) ($entry['size'] ?? 0);
            if ($totalSize > 20 * 1024 * 1024) {
                $archive->close();
                return ['success' => false, 'error' => 'Распакованный архив превышает 20 МБ'];
            }
            if (substr($name, -1) !== '/' && !preg_match('#^(theme\.json|layout\.php|pages/[a-z0-9_-]+\.php|components/[a-z0-9_-]+\.php|partials/[a-z0-9_-]+\.php|assets/[a-z0-9._-]+\.(css|js|svg|png|jpg|jpeg|webp))$#i', $name)) {
                $archive->close();
                return ['success' => false, 'error' => 'В архиве есть файл недопустимого типа или пути'];
            }
            $entries[] = $name;
        }
        if (!in_array('layout.php', $entries, true)) {
            $archive->close();
            return ['success' => false, 'error' => 'В архиве отсутствует обязательный layout.php'];
        }

        $metadata = json_decode((string) ($archive->getFromName('theme.json') ?: '{}'), true);
        $slug = is_array($metadata) ? (string) ($metadata['slug'] ?? '') : '';
        if (!preg_match('/^[a-z0-9][a-z0-9_-]{1,48}$/', $slug) || $slug === 'default') {
            $archive->close();
            return ['success' => false, 'error' => 'В theme.json указан некорректный slug шаблона'];
        }
        $targetRoot = APP_ROOT . '/app/Views/frontend/templates/' . $slug;
        if (is_dir($targetRoot)) {
            $archive->close();
            return ['success' => false, 'error' => 'Шаблон с таким slug уже существует'];
        }

        $temporaryRoot = APP_ROOT . '/var/template-upload-' . bin2hex(random_bytes(8));
        if (!mkdir($temporaryRoot, 0755, true)) {
            $archive->close();
            return ['success' => false, 'error' => 'Не удалось создать временную папку'];
        }
        if (!$archive->extractTo($temporaryRoot)) {
            $archive->close();
            $this->removeDirectory($temporaryRoot);
            return ['success' => false, 'error' => 'Не удалось распаковать архив'];
        }
        $archive->close();
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($temporaryRoot, RecursiveDirectoryIterator::SKIP_DOTS)) as $extracted) {
            if ($extracted->isFile() && strtolower($extracted->getExtension()) === 'php') {
                try {
                    token_get_all((string) file_get_contents($extracted->getPathname()), TOKEN_PARSE);
                } catch (\ParseError $error) {
                    $this->removeDirectory($temporaryRoot);
                    return ['success' => false, 'error' => 'В PHP-файле шаблона есть синтаксическая ошибка'];
                }
            }
        }
        if (!rename($temporaryRoot, $targetRoot)) {
            $this->removeDirectory($temporaryRoot);
            return ['success' => false, 'error' => 'Не удалось установить шаблон'];
        }
        return ['success' => true, 'template' => $slug, 'description' => (string) ($metadata['description'] ?? '')];
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) return;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($directory);
    }

    public function handle($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }

        if ($request->method() === 'GET') {
            $action = isset($_GET['action']) && is_string($_GET['action']) ? $_GET['action'] : 'files';

            if ($action === 'templates') {
                $names = $this->templates();
                $metadata = [];
                foreach ($names as $name) {
                    $metadata[$name] = $this->templateMetadata('frontend', $name);
                }
                return $this->json(ApiResource::success([
                    'templates' => $names,
                    'active' => $this->activeTemplates(),
                    'metadata' => $metadata,
                ]));
            }

            if ($action === 'files') {
                $template = isset($_GET['template']) && is_string($_GET['template']) ? $_GET['template'] : 'default';
                if (!preg_match('/^[a-z0-9_-]+$/i', $template) || !in_array($template, $this->templates(), true)) {
                    return $this->json(ApiResource::error('Шаблон не найден'), 404);
                }
                return $this->json(ApiResource::success([
                    'php' => $this->phpFiles($template),
                    'assets' => $this->assetFiles(),
                ]));
            }

            if ($action === 'read') {
                $template = isset($_GET['template']) && is_string($_GET['template']) ? $_GET['template'] : 'default';
                $area = isset($_GET['area']) && is_string($_GET['area']) ? $_GET['area'] : '';
                $path = isset($_GET['path']) && is_string($_GET['path']) ? $_GET['path'] : '';
                $content = $this->readFile($template, $area, $path);
                if ($content === null) {
                    return $this->json(ApiResource::error('Файл не найден'), 404);
                }
                return $this->json(ApiResource::success([
                    'file' => $path,
                    'content' => $content,
                    'hash' => hash('sha256', $content),
                ]));
            }

            return $this->json(ApiResource::error('Некорректный запрос'), 400);
        }

        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }
        \admin_require_csrf();

        if (isset($_FILES['template_archive'])) {
            return $this->json($this->templateUpload($_FILES['template_archive']), 201);
        }

        $input = $request->json();
        if (!isset($input['template'], $input['area'], $input['path'], $input['content'], $input['expectedHash']) ||
            !is_string($input['template']) || !is_string($input['area']) || !is_string($input['path']) ||
            !is_string($input['content']) ||
            !is_string($input['expectedHash']) || !preg_match('/^[a-f0-9]{64}$/', $input['expectedHash'])) {
            return $this->json(ApiResource::error('Некорректный запрос на сохранение'), 400);
        }
        if (strlen($input['content']) > 512 * 1024 || preg_match('//u', $input['content']) !== 1) {
            return $this->json(ApiResource::error('Файл должен быть UTF-8 и не превышать 512 КБ'), 400);
        }

        $path = $this->resolvePath($input['template'], $input['area'], $input['path']);
        if ($path === null) {
            return $this->json(ApiResource::error('Файл не найден'), 404);
        }

        // PHP-файлы проверяем на синтаксис — сломанный шаблон положит весь сайт
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'php') {
            try {
                token_get_all($input['content'], TOKEN_PARSE);
            } catch (\ParseError $error) {
                return $this->json(ApiResource::error('В шаблоне есть синтаксическая ошибка: ' . $error->getMessage()), 400);
            }
        }

        $oldContent = file_get_contents($path);
        if ($oldContent === false) {
            return $this->json(ApiResource::error('Не удалось прочитать текущую версию файла'), 500);
        }
        if (!hash_equals($input['expectedHash'], hash('sha256', $oldContent))) {
            return $this->json(ApiResource::error('Файл изменился после загрузки. Перезагрузите его перед сохранением.'), 409);
        }

        $backupDirectory = APP_ROOT . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'template-backups';
        $varDirectory = dirname($backupDirectory);
        if (is_link($varDirectory) || is_link($backupDirectory)) {
            return $this->json(ApiResource::error('Некорректная папка резервных копий'), 500);
        }
        foreach ([$varDirectory, $backupDirectory] as $directory) {
            if (!is_dir($directory) && !mkdir($directory, 0755) && !is_dir($directory)) {
                return $this->json(ApiResource::error('Не удалось создать папку резервных копий'), 500);
            }
        }
        $realBackupDirectory = realpath($backupDirectory);
        if ($realBackupDirectory === false || dirname(dirname($realBackupDirectory)) !== realpath(APP_ROOT)) {
            return $this->json(ApiResource::error('Некорректная папка резервных копий'), 500);
        }
        $backupName = basename($path) . '.' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.bak';
        if (file_put_contents($backupDirectory . DIRECTORY_SEPARATOR . $backupName, $oldContent, LOCK_EX) === false) {
            $this->logger->warning('Не удалось создать резервную копию шаблона перед сохранением', ['backupPath' => $backupDirectory]);
            return $this->json(ApiResource::error('Не удалось создать резервную копию'), 500);
        }

        // Записываем во временный файл, чтобы не оставить шаблон частично обновлённым.
        $temporaryPath = dirname($path) . DIRECTORY_SEPARATOR . '.' . basename($path) . '.' . bin2hex(random_bytes(8)) . '.tmp';
        if (file_put_contents($temporaryPath, $input['content'], LOCK_EX) === false) {
            @unlink($temporaryPath);
            return $this->json(ApiResource::error('Не удалось записать временную версию файла'), 500);
        }
        if (!rename($temporaryPath, $path)) {
            @unlink($temporaryPath);
            $this->logger->warning('Не удалось атомарно заменить файл шаблона сайта', ['temporaryPath' => $temporaryPath, 'path' => $path]);
            return $this->json(ApiResource::error('Не удалось сохранить файл'), 500);
        }

        // Сбрасываем кэш предзагрузки PHP, чтобы изменения применились сразу
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($path, true);
        }

        return $this->json(ApiResource::success([
            'backup' => $backupName,
            'hash' => hash('sha256', $input['content']),
        ]));
    }

    /**
     * PHP-файлы шаблона в обеих областях (frontend и admin).
     */
    private function phpFiles(string $template): array
    {
        $files = [];
        foreach (['frontend', 'admin'] as $area) {
            $base = APP_ROOT . '/app/Views/' . $area . '/templates/' . $template;
            if (!is_dir($base)) {
                continue;
            }
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if (strtolower($file->getExtension()) !== 'php' || is_link($file->getPathname())) {
                    continue;
                }
                $relative = str_replace('\\', '/', substr(str_replace('\\', '/', $file->getPathname()), strlen(str_replace('\\', '/', $base)) + 1));
                $files[] = ['area' => $area, 'path' => $relative];
            }
        }
        usort($files, static function ($a, $b) {
            return strcmp($a['area'] . '/' . $a['path'], $b['area'] . '/' . $b['path']);
        });
        return $files;
    }

    /**
     * Общие файлы оформления шаблона: CSS и JS из public.
     */
    private function assetFiles(): array
    {
        $files = [];
        foreach (['css' => 'css', 'js' => 'js'] as $directory => $type) {
            $full = APP_ROOT . '/public/' . $directory;
            if (!is_dir($full)) {
                continue;
            }
            foreach (scandir($full) ?: [] as $entry) {
                $path = $full . '/' . $entry;
                if ($entry === '.' || $entry === '..' || !is_file($path) || is_link($path)) {
                    continue;
                }
                $extension = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
                if ($extension !== $type) {
                    continue;
                }
                $files[] = ['path' => '/' . $directory . '/' . $entry, 'type' => $type];
            }
        }
        usort($files, static function ($a, $b) {
            return strcmp($a['path'], $b['path']);
        });
        return $files;
    }

    private function readFile(string $template, string $area, string $path): ?string
    {
        $resolved = $this->resolvePath($template, $area, $path);
        if ($resolved === null) {
            return null;
        }
        $content = file_get_contents($resolved);
        return $content === false ? null : $content;
    }

    /**
     * Безопасно разрешить путь к файлу шаблона или ассету.
     *
     * @return string|null абсолютный путь либо null, если файл вне разрешённых папок
     */
    private function resolvePath(string $template, string $area, string $path): ?string
    {
        if (!preg_match('/^[a-z0-9_-]+$/i', $template) || !in_array($template, $this->templates(), true)) {
            return null;
        }
        if ($area === 'assets') {
            if (!preg_match('#^/(css|js)/[a-zA-Z0-9._-]+\.(css|js)$#', $path)) {
                return null;
            }
            $file = realpath(APP_ROOT . '/public' . $path);
            $root = realpath(APP_ROOT . '/public');
            if ($file === false || $root === false || !is_file($file) || is_link($file) ||
                strpos($file, $root . DIRECTORY_SEPARATOR) !== 0) {
                return null;
            }
            return $file;
        }

        if ($area !== 'frontend' && $area !== 'admin') {
            return null;
        }
        // Только php-файлы внутри папки шаблона, без выхода за её пределы
        if (!preg_match('#^[a-zA-Z0-9_/-]+\.php$#', $path)) {
            return null;
        }
        $base = realpath(APP_ROOT . '/app/Views/' . $area . '/templates/' . $template);
        if ($base === false) {
            return null;
        }
        $file = realpath($base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));
        if ($file === false || !is_file($file) || is_link($file) ||
            strpos($file, $base . DIRECTORY_SEPARATOR) !== 0) {
            return null;
        }
        return $file;
    }
}
