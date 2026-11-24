<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\ApiResource;
use App\Core\ApplicationLogger;
use App\Core\Response;

final class FileManagerController extends Controller
{
    private $logger;

    public function __construct(ApplicationLogger $logger)
    {
        $this->logger = $logger;
    }

    private $categories = [
        'images' => ['jpg', 'jpeg', 'png'],
        'documents' => ['doc', 'docx'],
        'spreadsheets' => ['xls', 'xlsx'],
        'xml' => ['xml'],
        'archives' => ['zip'],
    ];
    private $sections = ['site', 'blog', 'portfolio', 'profile', 'orders'];

    public function handle($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }

        if ($request->method() === 'GET') {
            $action = isset($_GET['action']) && is_string($_GET['action']) ? $_GET['action'] : 'list';
            if ($action === 'list') {
                return $this->listFiles();
            }
            if ($action === 'download' || $action === 'preview') {
                return $this->readFile($action);
            }
            return $this->json(ApiResource::error('Неизвестное действие'), 400);
        }

        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }
        \admin_require_csrf();

        $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';
        if ($action === 'delete') {
            return $this->deleteFile($_POST['path'] ?? '');
        }
        if ($action === 'upload') {
            return $this->uploadFile();
        }
        return $this->json(ApiResource::error('Неизвестное действие'), 400);
    }

    private function listFiles()
    {
        $root = $this->uploadsPath();
        if (!is_dir($root)) {
            return $this->json(ApiResource::success(['files' => []]));
        }
        if (is_link($root)) {
            return $this->json(ApiResource::error('Некорректная папка загрузок'), 500);
        }

        $files = [];
        foreach ($this->categories as $category => $extensions) {
            foreach ($this->sections as $section) {
                $directory = $root . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR . $section;
                if (!is_dir($directory)) {
                    continue;
                }
                if (is_link($directory)) {
                    return $this->json(ApiResource::error('Некорректная папка загрузок'), 500);
                }
                $entries = scandir($directory);
                if ($entries === false) {
                    return $this->json(ApiResource::error('Не удалось прочитать папку загрузок'), 500);
                }
                foreach ($entries as $entry) {
                    // Имя файла: человекочитаемый slug (латиница, цифры, тире)
                    // — сюда же попадают старые имена-хэши [a-f0-9]{32}
                    if ($entry === '.' || $entry === '..' ||
                        !preg_match('/^[a-z0-9][a-z0-9-]*\.(jpg|jpeg|png|doc|docx|xls|xlsx|xml|zip)$/', $entry) ||
                        !in_array(strtolower(pathinfo($entry, PATHINFO_EXTENSION)), $extensions, true)) {
                        continue;
                    }
                    $path = $directory . DIRECTORY_SEPARATOR . $entry;
                    if (is_link($path) || !is_file($path)) {
                        continue;
                    }
                    $files[] = [
                        'path' => $category . '/' . $section . '/' . $entry,
                        'name' => $this->originalName($path, $entry),
                        'category' => $category,
                        'section' => $section,
                        'size' => filesize($path),
                        'modified' => filemtime($path),
                    ];
                    if (count($files) >= 1000) {
                        break 3;
                    }
                }
            }
        }
        usort($files, function ($left, $right) {
            return $right['modified'] <=> $left['modified'];
        });
        return $this->json(ApiResource::success(['files' => $files]));
    }

    private function readFile($action)
    {
        $relativePath = isset($_GET['path']) && is_string($_GET['path']) ? $_GET['path'] : '';
        $path = $this->safePath($relativePath);
        if ($path === null) {
            return $this->json(['success' => false, 'error' => 'Файл не найден'], 404);
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($action === 'preview') {
            $imageMimes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
            if (!isset($imageMimes[$extension]) || !extension_loaded('fileinfo') ||
                !function_exists('getimagesize')) {
                return $this->json(['success' => false, 'error' => 'Изображение не найдено'], 404);
            }
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
            $imageInfo = @getimagesize($path);
            if ($mime !== $imageMimes[$extension] || $imageInfo === false || $imageInfo['mime'] !== $mime) {
                return $this->json(['success' => false, 'error' => 'Изображение не найдено'], 404);
            }
            $disposition = 'inline';
        } else {
            $mime = 'application/octet-stream';
            if (extension_loaded('fileinfo')) {
                $detected = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
                if (is_string($detected)) {
                    $mime = $detected;
                }
            }
            $disposition = 'attachment';
        }

        $content = file_get_contents($path);
        if ($content === false) {
            $this->logger->warning('Не удалось прочитать файл из файлового менеджера', ['path' => $path]);
            return $this->json(['success' => false, 'error' => 'Не удалось прочитать файл'], 500);
        }
        return new Response($content, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition . '; filename="' . basename($path) . '"',
            'Content-Length' => (string) filesize($path),
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function deleteFile($relativePath)
    {
        $path = $this->safePath($relativePath);
        if ($path === null) {
            return $this->json(ApiResource::error('Файл не найден'), 404);
        }
        if (!unlink($path)) {
            $this->logger->warning('Не удалось удалить файл, загруженный через файловый менеджер', ['path' => $path]);
            return $this->json(ApiResource::error('Не удалось удалить файл'), 500);
        }
        $metadataPath = $this->metadataPath($path);
        if (is_file($metadataPath) && !is_link($metadataPath) && !unlink($metadataPath)) {
            $this->logger->warning('Не удалось удалить метаданные загруженного файла', ['metadataPath' => $metadataPath]);
        }
        return $this->json(ApiResource::success());
    }

    private function uploadFile()
    {
        if (!isset($_FILES['file'], $_POST['category'], $_POST['section']) ||
            !is_array($_FILES['file']) || !is_string($_POST['category']) || !is_string($_POST['section'])) {
            return $this->json(ApiResource::error('Передайте файл, тип и раздел'), 400);
        }
        $category = $_POST['category'];
        $section = $_POST['section'];
        $file = $_FILES['file'];
        if (!isset($this->categories[$category]) || !in_array($section, $this->sections, true)) {
            return $this->json(ApiResource::error('Неизвестный тип файла или раздел'), 400);
        }
        if (!isset($file['error'], $file['tmp_name'], $file['size'], $file['name']) ||
            !is_int($file['error']) || !is_string($file['tmp_name']) ||
            !is_int($file['size']) || !is_string($file['name'])) {
            return $this->json(ApiResource::error('Некорректные данные файла'), 400);
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $message = $file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE
                ? 'Размер файла превышает допустимый'
                : 'Не удалось принять файл';
            return $this->json(ApiResource::error($message), 400);
        }
        if (!is_uploaded_file($file['tmp_name']) || $file['size'] < 1 || $file['size'] > 10 * 1024 * 1024) {
            return $this->json(ApiResource::error('Размер файла должен быть не больше 10 МБ'), 400);
        }

        $originalName = basename(str_replace('\\', '/', $file['name']));
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->categories[$category], true)) {
            return $this->json(ApiResource::error('Расширение файла не соответствует выбранному типу'), 400);
        }
        if (!extension_loaded('fileinfo')) {
            $this->logger->error('Для загрузок файлового менеджера требуется расширение PHP fileinfo');
            return $this->json(ApiResource::error('На сервере не настроена проверка файлов'), 500);
        }
        $validation = $this->validateUploadedFile($file['tmp_name'], $extension);
        if ($validation !== null) {
            return $this->json(ApiResource::error($validation), 400);
        }

        $root = $this->uploadsPath();
        $categoryDirectory = $root . DIRECTORY_SEPARATOR . $category;
        $targetDirectory = $categoryDirectory . DIRECTORY_SEPARATOR . $section;
        if (is_link($root) || is_link($categoryDirectory) || is_link($targetDirectory)) {
            return $this->json(ApiResource::error('Некорректная папка загрузки'), 500);
        }
        foreach ([$root, $categoryDirectory, $targetDirectory] as $directory) {
            if (!is_dir($directory) && !mkdir($directory, 0755) && !is_dir($directory)) {
                return $this->json(ApiResource::error('Не удалось создать папку загрузок'), 500);
            }
        }
        $this->ensureImagesSecurityHtaccess($categoryDirectory);
        $realRoot = realpath($root);
        $realTarget = realpath($targetDirectory);
        if ($realRoot === false || $realTarget === false || dirname(dirname($realTarget)) !== $realRoot) {
            return $this->json(ApiResource::error('Некорректная папка загрузки'), 500);
        }
        $filename = $this->readableFilename($originalName, $extension, $targetDirectory);
        $destination = $targetDirectory . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->logger->warning('Не удалось переместить загруженный файл в файловый менеджер', ['destination' => $destination]);
            return $this->json(ApiResource::error('Не удалось сохранить файл'), 500);
        }
        $name = preg_replace('/[\x00-\x1F\x7F]/', '', $originalName);
        if (!is_string($name) || preg_match('//u', $name) !== 1) {
            $name = $filename;
        }
        $metadata = json_encode(['originalName' => $name], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($metadata === false || file_put_contents($this->metadataPath($destination), $metadata, LOCK_EX) === false) {
            @unlink($destination);
            $this->logger->warning('Не удалось сохранить исходное имя загруженного файла', ['destination' => $destination]);
            return $this->json(ApiResource::error('Не удалось сохранить имя файла'), 500);
        }
        return $this->json(ApiResource::success([
            'file' => [
                'path' => $category . '/' . $section . '/' . $filename,
                'name' => $name,
                'category' => $category,
                'section' => $section,
                'size' => filesize($destination),
                'modified' => filemtime($destination),
            ]
        ]));
    }

    private function validateUploadedFile($path, $extension)
    {
        $allowedMimes = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'doc' => ['application/msword', 'application/vnd.ms-office', 'application/x-ole-storage', 'application/CDFV2'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/x-zip'],
            'xls' => ['application/vnd.ms-excel', 'application/vnd.ms-office', 'application/x-ole-storage', 'application/CDFV2'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/x-zip'],
            'xml' => ['application/xml', 'text/xml', 'text/plain'],
            'zip' => ['application/zip', 'application/x-zip', 'application/x-zip-compressed'],
        ];
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!is_string($mime) || !in_array($mime, $allowedMimes[$extension], true)) {
            return 'Тип содержимого файла не соответствует разрешённому формату';
        }
        if (in_array($extension, ['jpg', 'jpeg', 'png'], true) &&
            (!function_exists('getimagesize') || @getimagesize($path) === false)) {
            return 'Файл не является корректным изображением';
        }
        if ($extension === 'xml') {
            $xml = file_get_contents($path);
            if ($xml === false || preg_match('/<!DOCTYPE|<!ENTITY/i', $xml)) {
                return 'XML-файл содержит запрещённые объявления';
            }
        }
        return null;
    }

    /**
     * Человекочитаемое имя файла из исходного: транслитерация через slugify,
     * нижний регистр, суффиксы -2/-3 при коллизиях, запасной хэш если имя пустое.
     * Запрещённые Windows-имена (con, prn, aux, nul, com1-9, lpt1-9) не используются.
     */
    private function readableFilename($originalName, $extension, $directory)
    {
        $reserved = ['con', 'prn', 'aux', 'nul'];
        for ($i = 1; $i <= 9; $i++) {
            $reserved[] = 'com' . $i;
            $reserved[] = 'lpt' . $i;
        }

        $base = slugify(pathinfo($originalName, PATHINFO_FILENAME));
        $base = substr($base, 0, 60);
        $base = rtrim($base, '-');
        if ($base === '' || in_array($base, $reserved, true)) {
            $base = bin2hex(random_bytes(16));
        }

        $filename = $base . '.' . $extension;
        $counter = 2;
        while (file_exists($directory . DIRECTORY_SEPARATOR . $filename) && $counter < 1000) {
            $filename = $base . '-' . $counter . '.' . $extension;
            $counter++;
        }
        if ($counter >= 1000) {
            $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        }
        return $filename;
    }

    /**
     * Обеспечиваем .htaccess в папке изображений файлового менеджера:
     * родительский uploads/.htaccess закрывает доступ (Require all denied),
     * без вложенного .htaccess все файлы images/* отдавались бы 403.
     * Файл закоммичен в репозиторий; дописываем, если папка была воссоздана без него.
     */
    private function ensureImagesSecurityHtaccess($imagesDirectory)
    {
        $expected = $this->uploadsPath() . DIRECTORY_SEPARATOR . 'images';
        if ($imagesDirectory !== $expected || is_file($imagesDirectory . DIRECTORY_SEPARATOR . '.htaccess')) {
            return;
        }
        $content = "# Запрет исполнения PHP в директории изображений файлового менеджера\n" .
            '<FilesMatch "\.(php|php2|php3|php4|php5|php6|php7|php8|phtml|phar)$">' . "\n" .
            "    Require all denied\n" .
            "</FilesMatch>\n\n" .
            "# Запрет просмотра директории\n" .
            "Options -Indexes\n\n" .
            "# Разрешаем чтение изображений; остальные файлы (документы, метаданные .meta.json)\n" .
            "# выдаются только через админ-API /api/admin/files с проверкой прав\n" .
            '<FilesMatch "\.(jpg|jpeg|png|gif|webp|svg|ico)$">' . "\n" .
            "    Require all granted\n" .
            "</FilesMatch>\n";
        @file_put_contents($imagesDirectory . DIRECTORY_SEPARATOR . '.htaccess', $content, LOCK_EX);
    }

    private function safePath($relativePath)
    {
        if (!is_string($relativePath) || strpos($relativePath, "\0") !== false || strpos($relativePath, '\\') !== false) {
            return null;
        }
        $parts = explode('/', $relativePath);
        if (count($parts) !== 3 || !isset($this->categories[$parts[0]]) ||
            !in_array($parts[1], $this->sections, true) ||
            // Имя: латиница/цифры/тире (человекочитаемый slug; старые хэши тоже проходят)
            !preg_match('/^[a-z0-9][a-z0-9-]*\.(jpg|jpeg|png|doc|docx|xls|xlsx|xml|zip)$/', $parts[2]) ||
            !in_array(strtolower(pathinfo($parts[2], PATHINFO_EXTENSION)), $this->categories[$parts[0]], true)) {
            return null;
        }
        $root = $this->uploadsPath();
        $category = $root . DIRECTORY_SEPARATOR . $parts[0];
        $section = $category . DIRECTORY_SEPARATOR . $parts[1];
        if (is_link($root) || is_link($category) || is_link($section)) {
            return null;
        }
        $path = realpath($section . DIRECTORY_SEPARATOR . $parts[2]);
        // Проверяем реальный путь, чтобы символьная ссылка не вывела запрос за пределы uploads.
        if ($path === false || !is_file($path) || dirname(dirname(dirname($path))) !== realpath($root) ||
            dirname($path) !== realpath($section)) {
            return null;
        }
        return $path;
    }

    private function originalName($path, $default)
    {
        $metadataPath = $this->metadataPath($path);
        if (!is_file($metadataPath) || is_link($metadataPath)) {
            return $default;
        }
        $metadata = json_decode((string) file_get_contents($metadataPath), true);
        return is_array($metadata) && isset($metadata['originalName']) && is_string($metadata['originalName'])
            ? $metadata['originalName']
            : $default;
    }

    private function metadataPath($path)
    {
        return substr($path, 0, strrpos($path, '.')) . '.meta.json';
    }

    private function uploadsPath()
    {
        return APP_ROOT . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
    }
}
