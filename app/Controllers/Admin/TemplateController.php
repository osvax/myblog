<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Редактор шаблонов: выбор шаблона (default и другие), правка его PHP-файлов,
 * общих CSS/JS и статических страниц (через API /api/admin/pages).
 */
class TemplateController extends Controller
{
    /**
     * Список шаблонов: пересечение папок frontend и admin templates.
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
     * Активный шаблон из config/views.php.
     */
    private function activeTemplates(): array
    {
        $config = is_file(APP_ROOT . '/config/views.php') ? require APP_ROOT . '/config/views.php' : [];
        return [
            'frontend' => is_array($config) ? ($config['frontend_template'] ?? 'default') : 'default',
            'admin' => is_array($config) ? ($config['admin_template'] ?? 'default') : 'default',
        ];
    }

    public function handle($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }

        if ($request->method() === 'GET') {
            $action = isset($_GET['action']) && is_string($_GET['action']) ? $_GET['action'] : 'files';

            if ($action === 'templates') {
                return $this->json([
                    'success' => true,
                    'templates' => $this->templates(),
                    'active' => $this->activeTemplates(),
                ]);
            }

            if ($action === 'files') {
                $template = isset($_GET['template']) && is_string($_GET['template']) ? $_GET['template'] : 'default';
                if (!preg_match('/^[a-z0-9_-]+$/i', $template) || !in_array($template, $this->templates(), true)) {
                    return $this->json(['success' => false, 'error' => 'Шаблон не найден'], 404);
                }
                return $this->json([
                    'success' => true,
                    'php' => $this->phpFiles($template),
                    'assets' => $this->assetFiles(),
                ]);
            }

            if ($action === 'read') {
                $template = isset($_GET['template']) && is_string($_GET['template']) ? $_GET['template'] : 'default';
                $area = isset($_GET['area']) && is_string($_GET['area']) ? $_GET['area'] : '';
                $path = isset($_GET['path']) && is_string($_GET['path']) ? $_GET['path'] : '';
                $content = $this->readFile($template, $area, $path);
                if ($content === null) {
                    return $this->json(['success' => false, 'error' => 'Файл не найден'], 404);
                }
                return $this->json([
                    'success' => true,
                    'file' => $path,
                    'content' => $content,
                    'hash' => hash('sha256', $content),
                ]);
            }

            return $this->json(['success' => false, 'error' => 'Некорректный запрос'], 400);
        }

        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }
        \admin_require_csrf();

        $input = $request->json();
        if (!isset($input['template'], $input['area'], $input['path'], $input['content'], $input['expectedHash']) ||
            !is_string($input['template']) || !is_string($input['area']) || !is_string($input['path']) ||
            !is_string($input['content']) ||
            !is_string($input['expectedHash']) || !preg_match('/^[a-f0-9]{64}$/', $input['expectedHash'])) {
            return $this->json(['success' => false, 'error' => 'Некорректный запрос на сохранение'], 400);
        }
        if (strlen($input['content']) > 512 * 1024 || preg_match('//u', $input['content']) !== 1) {
            return $this->json(['success' => false, 'error' => 'Файл должен быть UTF-8 и не превышать 512 КБ'], 400);
        }

        $path = $this->resolvePath($input['template'], $input['area'], $input['path']);
        if ($path === null) {
            return $this->json(['success' => false, 'error' => 'Файл не найден'], 404);
        }

        // PHP-файлы проверяем на синтаксис — сломанный шаблон положит весь сайт
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'php') {
            try {
                token_get_all($input['content'], TOKEN_PARSE);
            } catch (\ParseError $error) {
                return $this->json(['success' => false, 'error' => 'В шаблоне есть синтаксическая ошибка: ' . $error->getMessage()], 400);
            }
        }

        $oldContent = file_get_contents($path);
        if ($oldContent === false) {
            return $this->json(['success' => false, 'error' => 'Не удалось прочитать текущую версию файла'], 500);
        }
        if (!hash_equals($input['expectedHash'], hash('sha256', $oldContent))) {
            return $this->json(['success' => false, 'error' => 'Файл изменился после загрузки. Перезагрузите его перед сохранением.'], 409);
        }

        $backupDirectory = APP_ROOT . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'template-backups';
        $varDirectory = dirname($backupDirectory);
        if (is_link($varDirectory) || is_link($backupDirectory)) {
            return $this->json(['success' => false, 'error' => 'Некорректная папка резервных копий'], 500);
        }
        foreach ([$varDirectory, $backupDirectory] as $directory) {
            if (!is_dir($directory) && !mkdir($directory, 0755) && !is_dir($directory)) {
                return $this->json(['success' => false, 'error' => 'Не удалось создать папку резервных копий'], 500);
            }
        }
        $realBackupDirectory = realpath($backupDirectory);
        if ($realBackupDirectory === false || dirname(dirname($realBackupDirectory)) !== realpath(APP_ROOT)) {
            return $this->json(['success' => false, 'error' => 'Некорректная папка резервных копий'], 500);
        }
        $backupName = basename($path) . '.' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.bak';
        if (file_put_contents($backupDirectory . DIRECTORY_SEPARATOR . $backupName, $oldContent, LOCK_EX) === false) {
            error_log('Не удалось создать резервную копию шаблона перед сохранением.');
            return $this->json(['success' => false, 'error' => 'Не удалось создать резервную копию'], 500);
        }

        // Записываем во временный файл, чтобы не оставить шаблон частично обновлённым.
        $temporaryPath = dirname($path) . DIRECTORY_SEPARATOR . '.' . basename($path) . '.' . bin2hex(random_bytes(8)) . '.tmp';
        if (file_put_contents($temporaryPath, $input['content'], LOCK_EX) === false) {
            @unlink($temporaryPath);
            return $this->json(['success' => false, 'error' => 'Не удалось записать временную версию файла'], 500);
        }
        if (!rename($temporaryPath, $path)) {
            @unlink($temporaryPath);
            error_log('Не удалось атомарно заменить файл шаблона сайта.');
            return $this->json(['success' => false, 'error' => 'Не удалось сохранить файл'], 500);
        }

        // Сбрасываем кэш предзагрузки PHP, чтобы изменения применились сразу
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($path, true);
        }

        return $this->json([
            'success' => true,
            'backup' => $backupName,
            'hash' => hash('sha256', $input['content']),
        ]);
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
