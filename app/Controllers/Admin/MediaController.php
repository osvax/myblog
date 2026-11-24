<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\ApiResource;
use App\Core\ApplicationLogger;
use App\Core\Response;

final class MediaController extends Controller
{
    private $logger;

    public function __construct(ApplicationLogger $logger)
    {
        $this->logger = $logger;
    }

    public function upload($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }
        \admin_require_csrf();

        $scope = isset($_POST['scope']) && is_string($_POST['scope']) ? $_POST['scope'] : '';
        $itemId = isset($_POST['id']) && is_string($_POST['id']) ? $_POST['id'] : '';
        if (!in_array($scope, ['blog', 'portfolio'], true) ||
            !preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{0,63}$/', $itemId)) {
            return $this->json(ApiResource::error('Некорректный раздел или идентификатор записи'), 400);
        }
        if (!$this->hasUploadList()) {
            return $this->json(ApiResource::error('Выберите изображение для загрузки'), 400);
        }

        $fileCount = count($_FILES['files']['name']);
        $maxFiles = $scope === 'blog' ? 1 : 10;
        if ($fileCount < 1 || $fileCount > $maxFiles) {
            return $this->json(ApiResource::error($scope === 'blog'
                ? 'Для статьи можно загрузить одно изображение'
                : 'Для работы можно загрузить не более 10 изображений'), 400);
        }
        if (!extension_loaded('fileinfo') || !function_exists('getimagesize')) {
            $this->logger->error('Для загрузки изображений статей и проектов требуются fileinfo и поддержка изображений');
            return $this->json(ApiResource::error('На сервере не настроена проверка изображений'), 500);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $files = [];
        for ($index = 0; $index < $fileCount; $index++) {
            $file = [
                'name' => $_FILES['files']['name'][$index],
                'tmp_name' => $_FILES['files']['tmp_name'][$index],
                'error' => $_FILES['files']['error'][$index],
                'size' => $_FILES['files']['size'][$index],
            ];
            if (!is_string($file['name']) || !is_string($file['tmp_name']) ||
                !is_int($file['error']) || !is_int($file['size'])) {
                return $this->json(ApiResource::error('Некорректные данные изображения'), 400);
            }
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $message = $file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE
                    ? 'Размер изображения превышает допустимый'
                    : 'Не удалось принять изображение';
                return $this->json(ApiResource::error($message), 400);
            }
            if (!is_uploaded_file($file['tmp_name']) || $file['size'] < 1 || $file['size'] > 10 * 1024 * 1024) {
                return $this->json(ApiResource::error('Размер каждого изображения должен быть не больше 10 МБ'), 400);
            }
            $mime = $finfo->file($file['tmp_name']);
            $imageInfo = @getimagesize($file['tmp_name']);
            if (!is_string($mime) || !isset($extensions[$mime]) ||
                $imageInfo === false || $imageInfo['mime'] !== $mime) {
                return $this->json(ApiResource::error('Разрешены только корректные изображения JPEG, PNG и WebP'), 400);
            }
            $width = (int) $imageInfo[0];
            $height = (int) $imageInfo[1];
            if ($width < 1 || $height < 1 || $width > 8000 || $height > 8000 || $width * $height > 40000000) {
                return $this->json(ApiResource::error('Слишком большой размер изображения'), 400);
            }
            $file['extension'] = $extensions[$mime];
            $files[] = $file;
        }

        $root = APP_ROOT . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
        $scopeDirectory = $root . DIRECTORY_SEPARATOR . $scope;
        $targetDirectory = $scopeDirectory . DIRECTORY_SEPARATOR . $itemId;
        if (is_link($root) || is_link($scopeDirectory) || is_link($targetDirectory)) {
            return $this->json(ApiResource::error('Некорректная папка для загрузки'), 500);
        }
        foreach ([$root, $scopeDirectory, $targetDirectory] as $directory) {
            if (!is_dir($directory) && !mkdir($directory, 0755) && !is_dir($directory)) {
                $this->logger->warning('Не удалось создать папку для загружаемых изображений', ['directory' => $directory]);
                return $this->json(ApiResource::error('Не удалось создать папку для изображений'), 500);
            }
        }
        $realRoot = realpath($root);
        $realScope = realpath($scopeDirectory);
        $realTarget = realpath($targetDirectory);
        if ($realRoot === false || $realScope === false || $realTarget === false ||
            dirname($realScope) !== $realRoot || dirname($realTarget) !== $realScope) {
            return $this->json(ApiResource::error('Некорректная папка для загрузки'), 500);
        }

        $savedPaths = [];
        $result = [];
        foreach ($files as $index => $file) {
            try {
                $filename = bin2hex(random_bytes(16)) . '.' . $file['extension'];
            } catch (\Throwable $error) {
                $this->removeFiles($savedPaths);
                $this->logger->logException($error, 'Не удалось создать безопасное имя загружаемого изображения');
                return $this->json(ApiResource::error('Не удалось подготовить имя файла'), 500);
            }
            $destination = $targetDirectory . DIRECTORY_SEPARATOR . $filename;
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                $this->removeFiles($savedPaths);
                $this->logger->warning('Не удалось переместить загруженное изображение', ['destination' => $destination]);
                return $this->json(ApiResource::error('Не удалось сохранить изображение'), 500);
            }
            $savedPaths[] = $destination;
            $result[$index] = 'uploads/' . $scope . '/' . rawurlencode($itemId) . '/' . $filename;
        }
        return $this->json(ApiResource::success(['files' => $result]));
    }

    public function serve($request, array $params = [])
    {
        if (!in_array($request->method(), ['GET', 'HEAD'], true)) {
            return new Response('', 405, ['Allow' => 'GET, HEAD']);
        }
        $scope = $params['scope'] ?? '';
        $itemId = $params['id'] ?? '';
        $filename = $params['filename'] ?? '';
        if (!in_array($scope, ['blog', 'portfolio'], true) ||
            !preg_match('/^[A-Za-z0-9_-]+$/', $itemId) ||
            !preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $filename)) {
            return new Response('', 404);
        }
        $root = APP_ROOT . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
        $scopeDirectory = $root . DIRECTORY_SEPARATOR . $scope;
        $itemDirectory = $scopeDirectory . DIRECTORY_SEPARATOR . $itemId;
        $path = $itemDirectory . DIRECTORY_SEPARATOR . $filename;
        if (is_link($root) || is_link($scopeDirectory) || is_link($itemDirectory) || is_link($path)) {
            return new Response('', 404);
        }
        $realRoot = realpath($root);
        $realScope = realpath($scopeDirectory);
        $realItem = realpath($itemDirectory);
        $realPath = realpath($path);
        if ($realRoot === false || $realScope === false || $realItem === false || $realPath === false ||
            dirname($realScope) !== $realRoot || dirname($realItem) !== $realScope ||
            dirname($realPath) !== $realItem || !is_file($realPath)) {
            return new Response('', 404);
        }
        if (!extension_loaded('fileinfo')) {
            $this->logger->error('Для выдачи загруженных изображений требуется расширение PHP fileinfo');
            return new Response('', 500);
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($realPath);
        $expected = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!is_string($mime) || !isset($expected[$extension]) || $mime !== $expected[$extension]) {
            return new Response('', 404);
        }
        $body = '';
        if ($request->method() !== 'HEAD') {
            $body = file_get_contents($realPath);
            if ($body === false) {
                $this->logger->warning('Не удалось прочитать публичное изображение', ['path' => $realPath]);
                return new Response('', 500);
            }
        }
        return new Response($body, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) filesize($realPath),
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function avatar($request, array $params = [])
    {
        if (!\admin_is_authenticated()) {
            return new Response('', 401);
        }
        if ($request->method() === 'POST') {
            \admin_require_csrf();
            return $this->uploadAvatar();
        }
        if (!in_array($request->method(), ['GET', 'HEAD'], true)) {
            return new Response('', 405, ['Allow' => 'GET, HEAD, POST']);
        }
        $filename = $params['filename'] ?? '';
        if (!preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $filename)) {
            return new Response('', 404);
        }
        $root = APP_ROOT . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
        $directory = $root . DIRECTORY_SEPARATOR . 'profile';
        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        $realDirectory = realpath($directory);
        $realPath = realpath($path);
        $realRoot = realpath($root);
        if (is_link($root) || is_link($directory) || is_link($path) || $realRoot === false ||
            $realDirectory === false || dirname($realDirectory) !== $realRoot ||
            $realPath === false || dirname($realPath) !== $realDirectory || !is_file($realPath)) {
            return new Response('', 404);
        }
        return $this->imageResponse($request, $realPath, $filename, 3600, true);
    }

    private function uploadAvatar()
    {
        if (!isset($_FILES['avatar']) || !is_array($_FILES['avatar'])) {
            return $this->json(ApiResource::error('Выберите изображение аватара'), 400);
        }
        $file = $_FILES['avatar'];
        if (!isset($file['error'], $file['tmp_name'], $file['size']) ||
            !is_int($file['error']) || !is_string($file['tmp_name']) || !is_int($file['size'])) {
            return $this->json(ApiResource::error('Некорректные данные файла'), 400);
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $message = $file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE
                ? 'Размер изображения превышает допустимый'
                : 'Не удалось принять файл. Попробуйте загрузить его ещё раз';
            return $this->json(ApiResource::error($message), 400);
        }
        if (!is_uploaded_file($file['tmp_name']) || $file['size'] < 1 || $file['size'] > 2 * 1024 * 1024) {
            return $this->json(ApiResource::error('Изображение должно быть не больше 2 МБ'), 400);
        }
        if (!extension_loaded('fileinfo') || !function_exists('getimagesize')) {
            $this->logger->error('Для загрузки аватара требуются fileinfo и поддержка изображений');
            return $this->json(ApiResource::error('На сервере не настроена проверка изображений'), 500);
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $image = @getimagesize($file['tmp_name']);
        if (!is_string($mime) || !isset($extensions[$mime]) || $image === false || $image['mime'] !== $mime) {
            return $this->json(ApiResource::error('Разрешены только изображения JPEG, PNG и WebP'), 400);
        }
        $width = (int) $image[0];
        $height = (int) $image[1];
        if ($width < 1 || $height < 1 || $width > 5000 || $height > 5000 || $width * $height > 20000000) {
            return $this->json(ApiResource::error('Слишком большой размер изображения'), 400);
        }
        $root = APP_ROOT . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
        $directory = $root . DIRECTORY_SEPARATOR . 'profile';
        if (is_link($root) || is_link($directory)) {
            return $this->json(ApiResource::error('Некорректная папка для загрузки'), 500);
        }
        foreach ([$root, $directory] as $path) {
            if (!is_dir($path) && !mkdir($path, 0755) && !is_dir($path)) {
                return $this->json(ApiResource::error('Не удалось создать папку для аватаров'), 500);
            }
        }
        $realRoot = realpath($root);
        $realDirectory = realpath($directory);
        if ($realRoot === false || $realDirectory === false || dirname($realDirectory) !== $realRoot) {
            return $this->json(ApiResource::error('Некорректная папка для загрузки'), 500);
        }
        try {
            $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        } catch (\Throwable $error) {
            $this->logger->logException($error, 'Не удалось создать безопасное имя аватара');
            return $this->json(ApiResource::error('Не удалось подготовить имя файла'), 500);
        }
        if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
            $this->logger->warning('Не удалось сохранить загруженный аватар', ['filename' => $filename]);
            return $this->json(ApiResource::error('Не удалось сохранить изображение'), 500);
        }
        return $this->json(ApiResource::success(['url' => '/api/admin/avatar/' . $filename]));
    }

    private function imageResponse($request, $path, $filename, $maxAge, $private)
    {
        if (!extension_loaded('fileinfo')) {
            return new Response('', 500);
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $types = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!is_string($mime) || !isset($types[$extension]) || $mime !== $types[$extension]) {
            return new Response('', 404);
        }
        $body = $request->method() === 'HEAD' ? '' : file_get_contents($path);
        if ($body === false) {
            $this->logger->warning('Не удалось прочитать изображение профиля', ['path' => $path]);
            return new Response('', 500);
        }
        return new Response($body, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) filesize($path),
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => ($private ? 'private' : 'public') . ', max-age=' . $maxAge,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function hasUploadList()
    {
        if (!isset($_FILES['files']) || !is_array($_FILES['files'])) {
            return false;
        }
        foreach (['name', 'tmp_name', 'error', 'size'] as $key) {
            if (!isset($_FILES['files'][$key]) || !is_array($_FILES['files'][$key])) {
                return false;
            }
        }
        $count = count($_FILES['files']['name']);
        return $count === count($_FILES['files']['tmp_name']) &&
            $count === count($_FILES['files']['error']) && $count === count($_FILES['files']['size']);
    }

    private function removeFiles(array $paths)
    {
        foreach ($paths as $path) {
            @unlink($path);
        }
    }
}
