<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\ApiResource;
use App\Core\Response;
use App\Models\SettingsRepository;
use App\DTO\SiteSettingsDTO;
use App\Models\UserActivity;
use App\Transformers\SiteSettingsTransformer;

class SiteSettingsController extends Controller
{
    private $settings;
    private $activity;
    private $transformer;

    public function __construct(
        SettingsRepository $settings,
        UserActivity $activity,
        SiteSettingsTransformer $transformer
    ) {
        $this->settings = $settings;
        $this->activity = $activity;
        $this->transformer = $transformer;
    }

    /**
     * Получить все настройки.
     */
    public function index($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        // Новый API: используем SiteSettingsDTO через трансформер
        $dto = $this->settings->groupedAsDto();

        return $this->json(ApiResource::success(
            $this->transformer->transformGrouped($dto)
        ));
    }

    /**
     * Сохранить все настройки.
     */
    public function save($request)
    {
        if ($request->method() !== 'PUT') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $input = $request->json();
        if (empty($input)) {
            return $this->json(ApiResource::error('Нет данных'), 400);
        }

        // Очистка HTML для contactsAboutHtml
        if (array_key_exists('contactsAboutHtml', $input) && is_string($input['contactsAboutHtml'])) {
            $input['contactsAboutHtml'] = \App\Core\Sanitizer::get()->purify($input['contactsAboutHtml']);
        }

        // Фото загружается отдельным запросом: пустое значение из общей формы
        // не должно затирать уже сохранённый путь.
        if (array_key_exists('contactsAboutPhoto', $input) && trim((string) $input['contactsAboutPhoto']) === '') {
            unset($input['contactsAboutPhoto']);
        }

        // Если меняется список разделов статистики — читаем старый список
        // до сохранения, чтобы после удалить данные по выключенным разделам
        $oldStatisticsSections = null;
        if (array_key_exists('statisticsSections', $input)) {
            $oldStatisticsSections = $this->settings->get('statisticsSections', '');
        }

        // Сохраняем через новый DTO
        $dto = SiteSettingsDTO::fromRequest($input);
        $result = $this->settings->saveAllFromDTO($dto);

        if (!$result['success']) {
            return $this->json(ApiResource::error($result['error'] ?? 'Не удалось сохранить'), 500);
        }

        $response = ApiResource::success();
        if ($oldStatisticsSections !== null) {
            $oldAllowed = UserActivity::parseSections($oldStatisticsSections);
            $newAllowed = UserActivity::parseSections($input['statisticsSections']);
            $removed = array_values(array_diff($oldAllowed, $newAllowed));
            if ($removed !== []) {
                try {
                    $deleted = (new UserActivity())->deleteSections($removed);
                } catch (\Exception $e) {
                    return $this->json(ApiResource::error('Настройки сохранены, но данные по выключенным разделам не удалены: ' . $e->getMessage()), 500);
                }
                if ($deleted > 0) {
                    $response['deletedRecords'] = $deleted;
                }
            }
        }

        return $this->json($response);
    }

    /**
     * Добавить пользовательское поле.
     */
    public function addField($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $input = $request->json();
        $key = isset($input['key']) && is_string($input['key']) ? trim($input['key']) : '';
        $value = isset($input['value']) && is_string($input['value']) ? $input['value'] : '';
        $type = isset($input['type']) && is_string($input['type']) ? $input['type'] : 'text';
        $tabGroup = isset($input['tab_group']) && is_string($input['tab_group']) ? $input['tab_group'] : 'custom';
        $placeholder = isset($input['placeholder']) && is_string($input['placeholder']) ? $input['placeholder'] : '';

        if (strlen($key) < 1 || strlen($key) > 100) {
            return $this->json(ApiResource::error('Неверное имя поля'), 400);
        }
        if (!in_array($type, SiteSettingsDTO::getSupportedTypes(), true)) {
            return $this->json(ApiResource::error('Неверный тип поля'), 400);
        }

        $result = $this->settings->addCustomField($key, $value, $type, $tabGroup, $placeholder);
        if (!$result['success']) {
            return $this->json(ApiResource::error($result['error'] ?? 'Не удалось добавить'), 500);
        }

        return $this->json(ApiResource::created(['id' => $result['id']]));
    }

    /**
     * Удалить пользовательское поле.
     */
    public function deleteField($request, $parameters)
    {
        if ($request->method() !== 'DELETE') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $key = isset($parameters['key']) && is_string($parameters['key']) ? $parameters['key'] : '';
        if (empty($key)) {
            return $this->json(ApiResource::error('Не указано имя поля'), 400);
        }

        $result = $this->settings->deleteCustomField($key);
        if (!$result['success']) {
            return $this->json(ApiResource::error($result['error'] ?? 'Не удалось удалить'), 404);
        }

        return $this->json(ApiResource::success());
    }

    /**
     * Тест отправки email через SMTP.
     */
    public function testEmail($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $result = \App\Core\Mailer::testConnection();
        return $this->json($result);
    }

    public function serveContactsAboutPhoto($request, array $parameters = [])
    {
        if (!in_array($request->method(), ['GET', 'HEAD'], true)) {
            return new Response('', 405, ['Allow' => 'GET, HEAD']);
        }
        $filename = (string) ($parameters['filename'] ?? '');
        if (!preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $filename)) {
            return new Response('', 404);
        }
        $directory = APP_ROOT . '/public/uploads/contacts';
        $path = $directory . '/' . $filename;
        $realDirectory = realpath($directory);
        $realPath = realpath($path);
        if ($realDirectory === false || $realPath === false || dirname($realPath) !== $realDirectory || is_link($realPath) || !is_file($realPath)) {
            return new Response('', 404);
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($realPath);
        $types = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!is_string($mime) || ($types[$extension] ?? '') !== $mime) {
            return new Response('', 404);
        }
        $body = $request->method() === 'HEAD' ? '' : file_get_contents($realPath);
        return new Response($body === false ? '' : $body, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) filesize($realPath),
            'Cache-Control' => 'public, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function contactsAboutPhoto($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }
        \admin_require_csrf();
        $file = $_FILES['contacts_about_photo'] ?? null;
        if (!is_array($file)) {
            return $this->json(ApiResource::error('Файл не пришёл на сервер. Проверьте выбор файла и повторите загрузку.'), 400);
        }
        $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($uploadError !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'Файл превышает серверный лимит загрузки',
                UPLOAD_ERR_FORM_SIZE => 'Файл превышает допустимый размер формы',
                UPLOAD_ERR_PARTIAL => 'Файл загрузился не полностью',
                UPLOAD_ERR_NO_FILE => 'Файл не выбран',
                UPLOAD_ERR_NO_TMP_DIR => 'На сервере отсутствует временная папка загрузки',
                UPLOAD_ERR_CANT_WRITE => 'Сервер не может записать временный файл загрузки',
                UPLOAD_ERR_EXTENSION => 'Загрузка файла остановлена расширением PHP',
            ];
            return $this->json(ApiResource::error($uploadErrors[$uploadError] ?? 'Ошибка загрузки файла'), 400);
        }
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return $this->json(ApiResource::error('Сервер не распознал загруженный файл. Повторите загрузку.'), 400);
        }
        if ((int) $file['size'] < 1 || (int) $file['size'] > 5 * 1024 * 1024) {
            return $this->json(ApiResource::error('Размер фото должен быть не больше 5 МБ'), 400);
        }
        if (!extension_loaded('fileinfo') || !function_exists('getimagesize')) {
            return $this->json(ApiResource::error('На сервере не настроена проверка изображений'), 500);
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $image = @getimagesize($file['tmp_name']);
        if (!is_string($mime) || !isset($extensions[$mime]) || $image === false || ($image['mime'] ?? '') !== $mime) {
            return $this->json(ApiResource::error('Разрешены только JPEG, PNG и WebP'), 400);
        }
        $directory = APP_ROOT . '/public/uploads/contacts';
        if (is_link(APP_ROOT . '/public/uploads') || is_link($directory)) {
            return $this->json(ApiResource::error('Некорректная папка загрузки'), 500);
        }
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return $this->json(ApiResource::error('Не удалось создать папку для фото'), 500);
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        $target = $directory . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return $this->json(ApiResource::error('Не удалось сохранить фото'), 500);
        }

        // Сохраняем путь через DTO
        $dto = SiteSettingsDTO::fromRequest(['contactsAboutPhoto' => 'uploads/contacts/' . $filename]);
        $saveResult = $this->settings->saveAllFromDTO($dto);
        if (!$saveResult['success']) {
            if (is_file($target) && !is_link($target)) {
                @unlink($target);
            }
            return $this->json(ApiResource::error($saveResult['error'] ?? 'Не удалось сохранить путь к фото'), 500);
        }

        // Удаляем старый файл, если он был в uploads/contacts/
        $oldPath = $this->settings->get('contactsAboutPhoto', '');
        if (preg_match('#^uploads/contacts/[a-f0-9]{32}\.(?:jpg|png|webp)$#', $oldPath)) {
            $oldFile = APP_ROOT . '/public/' . $oldPath;
            if (is_file($oldFile) && !is_link($oldFile)) {
                @unlink($oldFile);
            }
        }

        return $this->json(ApiResource::success(['path' => 'uploads/contacts/' . $filename]));
    }

    /**
     * Загрузить файл шрифта (вкладка «Оформление»): woff/woff2/ttf/otf
     * до 2 МБ. Файл кладётся в /uploads/fonts/, путь — в настройку
     * themeFontFile (лейауты генерируют из него @font-face).
     */
    public function font($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $file = $_FILES['font_file'] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return $this->json(ApiResource::error('Не передан файл шрифта'), 400);
        }
        if ((int) $file['size'] < 1 || (int) $file['size'] > 2 * 1024 * 1024) {
            return $this->json(ApiResource::error('Размер файла шрифта должен быть от 1 байта до 2 МБ'), 400);
        }
        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['woff', 'woff2', 'ttf', 'otf'], true)) {
            return $this->json(ApiResource::error('Допустимые форматы шрифтов: woff, woff2, ttf, otf'), 400);
        }

        $targetDir = APP_ROOT . '/public/uploads/fonts';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            return $this->json(ApiResource::error('Не удалось создать каталог для шрифтов'), 500);
        }

        $oldPath = trim((string) $this->settings->get('themeFontFile', ''));
        $newName = md5(uniqid('', true)) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $targetDir . '/' . $newName)) {
            return $this->json(ApiResource::error('Не удалось сохранить файл шрифта'), 500);
        }

        // Сохраняем путь через DTO
        $dto = SiteSettingsDTO::fromRequest(['themeFontFile' => 'uploads/fonts/' . $newName]);
        $this->settings->saveAllFromDTO($dto);

        // Удаляем прежний файл (только если он лежит в uploads/fonts/)
        if ($oldPath !== '' && preg_match('#^uploads/fonts/[A-Za-z0-9._-]+\.(woff|woff2|ttf|otf)$#i', $oldPath)) {
            $oldFile = APP_ROOT . '/public/' . $oldPath;
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        \App\Core\Logger::write('security', 'Загружен файл шрифта: ' . $newName);
        return $this->json(ApiResource::success(['path' => 'uploads/fonts/' . $newName, 'file' => $newName]));
    }

    /**
     * Последние записи журнала событий (вкладка «Логи»).
     */
    public function logs($request)
    {
        if ($request->method() !== 'GET') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        $result = \App\Core\Logger::recent(50);
        return $this->json(ApiResource::success($result));
    }

    /**
     * Очистить журнал событий.
     */
    public function clearLogs($request)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $deleted = \App\Core\Logger::clear();
        return $this->json(ApiResource::success(['deleted' => $deleted]));
    }
}