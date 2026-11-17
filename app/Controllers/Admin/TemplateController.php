<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;

class TemplateController extends Controller
{
    private $templates = [
        'index.php' => 'frontend/templates/default/pages/home.php',
        'blog.php' => 'frontend/templates/default/pages/blog.php',
        'contacts.php' => 'frontend/templates/default/pages/contacts.php',
        'order.php' => 'frontend/templates/default/pages/order.php',
        'portfolio.php' => 'frontend/templates/default/pages/portfolio.php',
        'post-adaptive.php' => 'frontend/templates/default/pages/post-adaptive.php',
        'post-bootstrap.php' => 'frontend/templates/default/pages/post-bootstrap.php',
        'post-git.php' => 'frontend/templates/default/pages/post-git.php',
        'post-js.php' => 'frontend/templates/default/pages/post-js.php',
        'post-speed.php' => 'frontend/templates/default/pages/post-speed.php',
        'post-ui.php' => 'frontend/templates/default/pages/post-ui.php',
        'project-blog.php' => 'frontend/templates/default/pages/project-blog.php',
        'project-community.php' => 'frontend/templates/default/pages/project-community.php',
        'project-dashboard.php' => 'frontend/templates/default/pages/project-dashboard.php',
        'project-landing.php' => 'frontend/templates/default/pages/project-landing.php',
        'project-photo.php' => 'frontend/templates/default/pages/project-photo.php',
        'project-shop.php' => 'frontend/templates/default/pages/project-shop.php',
    ];

    public function handle($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() === 'GET') {
            $action = isset($_GET['action']) && is_string($_GET['action']) ? $_GET['action'] : 'list';
            if ($action === 'list') {
                return $this->json(['success' => true, 'files' => array_keys($this->templates)]);
            }
            if ($action !== 'read' || !isset($_GET['file']) || !is_string($_GET['file'])) {
                return $this->json(['success' => false, 'error' => 'Некорректный запрос'], 400);
            }
            $content = $this->readTemplate($_GET['file']);
            if ($content === null) {
                return $this->json(['success' => false, 'error' => 'Шаблон не найден'], 404);
            }
            return $this->json([
                'success' => true,
                'file' => $_GET['file'],
                'content' => $content,
                'hash' => hash('sha256', $content),
            ]);
        }
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }
        \admin_require_csrf();

        $input = $request->json();
        if (!isset($input['file'], $input['content'], $input['expectedHash']) ||
            !is_string($input['file']) || !is_string($input['content']) ||
            !is_string($input['expectedHash']) || !preg_match('/^[a-f0-9]{64}$/', $input['expectedHash'])) {
            return $this->json(['success' => false, 'error' => 'Некорректный запрос на сохранение'], 400);
        }
        if (strlen($input['content']) > 512 * 1024 || preg_match('//u', $input['content']) !== 1) {
            return $this->json(['success' => false, 'error' => 'Шаблон должен быть UTF-8 и не превышать 512 КБ'], 400);
        }
        try {
            token_get_all($input['content'], TOKEN_PARSE);
        } catch (\ParseError $error) {
            return $this->json(['success' => false, 'error' => 'В шаблоне есть синтаксическая ошибка: ' . $error->getMessage()], 400);
        }

        $path = $this->templatePath($input['file']);
        if ($path === null) {
            return $this->json(['success' => false, 'error' => 'Шаблон не найден'], 404);
        }
        $oldContent = file_get_contents($path);
        if ($oldContent === false) {
            return $this->json(['success' => false, 'error' => 'Не удалось прочитать текущую версию шаблона'], 500);
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
        $backupName = $input['file'] . '.' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.bak';
        if (file_put_contents($backupDirectory . DIRECTORY_SEPARATOR . $backupName, $oldContent, LOCK_EX) === false) {
            error_log('Не удалось создать резервную копию шаблона перед сохранением.');
            return $this->json(['success' => false, 'error' => 'Не удалось создать резервную копию'], 500);
        }

        // Записываем во временный файл, чтобы не оставить шаблон частично обновлённым.
        $temporaryPath = dirname($path) . DIRECTORY_SEPARATOR . '.' . basename($path) . '.' . bin2hex(random_bytes(8)) . '.tmp';
        if (file_put_contents($temporaryPath, $input['content'], LOCK_EX) === false) {
            @unlink($temporaryPath);
            return $this->json(['success' => false, 'error' => 'Не удалось записать временную версию шаблона'], 500);
        }
        if (!rename($temporaryPath, $path)) {
            @unlink($temporaryPath);
            error_log('Не удалось атомарно заменить шаблон сайта.');
            return $this->json(['success' => false, 'error' => 'Не удалось сохранить шаблон'], 500);
        }
        return $this->json([
            'success' => true,
            'backup' => $backupName,
            'hash' => hash('sha256', $input['content']),
        ]);
    }

    private function readTemplate($name)
    {
        $path = $this->templatePath($name);
        if ($path === null) {
            return null;
        }
        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }
        return $content;
    }

    private function templatePath($name)
    {
        if (!isset($this->templates[$name])) {
            return null;
        }
        $root = APP_ROOT . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Views';
        $path = realpath($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $this->templates[$name]));
        $realRoot = realpath($root);
        if ($path === false || $realRoot === false || !is_file($path) ||
            strncasecmp($path, $realRoot . DIRECTORY_SEPARATOR, strlen($realRoot) + 1) !== 0 ||
            is_link($path)) {
            return null;
        }
        return $path;
    }
}
