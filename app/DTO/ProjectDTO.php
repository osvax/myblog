<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * ProjectDTO — объект переноса данных для проекта портфолио.
 * Включает вложенные структуры: галерея, доп. поля, задача, результаты, скриншоты.
 */
final class ProjectDTO
{
    // === Основные поля ===

    public string $id = '';
    public string $slug = '';
    public string $title = '';
    public string $technologies = '';
    public string $icon = 'bi-briefcase';
    public string $description = '';
    public string $solution = '';
    public string $result = '';
    public string $client = '';
    public ?int $year = null;
    public string $duration = '';
    public string $stack = '';
    public bool $active = true;

    // === Массивы / вложенные структуры ===

    /** @var string[] Задача (пункты) */
    public array $task = [];

    /** @var array<string, mixed>[] Результаты (ключ-значение) */
    public array $results = [];

    /** @var string[] Пути к скриншотам */
    public array $screenshots = [];

    /** @var array{path: string, description: string}[] Галерея с описаниями */
    public array $gallery = [];

    /** @var array{kind: string, name: string, value: string, file_path?: string}[] Доп. поля */
    public array $infoItems = [];

    // === Ошибки валидации ===
    private array $errors = [];

    // === Константы для валидации путей ===
    public const SCREENSHOT_PATH_REGEX = '#^(?:uploads/portfolio/[A-Za-z0-9_-]+/[a-f0-9]{32}\.(?:jpg|png|webp)|uploads/images/portfolio/[a-z0-9][a-z0-9-]*\.(?:jpg|jpeg|png))$#';
    public const DOC_FILE_PATH_REGEX = '#^uploads/documents/portfolio/[a-z0-9][a-z0-9-]*\.(?:doc|docx)$#';
    public const IMG_FILE_PATH_REGEX = '#^uploads/images/portfolio/[a-z0-9][a-z0-9-]*\.(?:jpg|jpeg|png|webp)$#';

    /**
     * Создаёт DTO из массива запроса (JSON / POST).
     *
     * @param array $data Данные из $request->json()
     * @param string|null $id ID для обновления (генерируется для новой)
     * @return self
     */
    public static function fromRequest(array $data, ?string $id = null): self
    {
        $dto = new self();
        $dto->id = $id ?? bin2hex(random_bytes(16));
        $dto->slug = trim($data['slug'] ?? '');
        $dto->title = trim($data['title'] ?? '');
        $dto->technologies = trim($data['technologies'] ?? '');
        $dto->icon = trim($data['icon'] ?? 'bi-briefcase');
        $dto->description = trim($data['description'] ?? '');
        $dto->solution = trim($data['solution'] ?? '');
        $dto->result = trim($data['result'] ?? '');
        $dto->client = trim($data['client'] ?? '');
        $dto->year = isset($data['year']) && $data['year'] !== '' ? (int)$data['year'] : null;
        $dto->duration = trim($data['duration'] ?? '');
        $dto->stack = trim($data['stack'] ?? '');
        $dto->active = !isset($data['active']) || $data['active'] !== false;

        // task: может быть строкой (построчно) или массивом
        $task = $data['task'] ?? [];
        if (is_string($task)) {
            $task = preg_split('/\r\n|\r|\n/', $task, -1, PREG_SPLIT_NO_EMPTY);
        }
        $dto->task = is_array($task) ? array_values(array_filter($task, 'is_string')) : [];

        // results: массив объектов/ассоциативных массивов
        $dto->results = is_array($data['results'] ?? null) ? $data['results'] : [];

        // screenshots: массив путей
        $dto->screenshots = is_array($data['screenshots'] ?? null) ? array_values($data['screenshots']) : [];

        // gallery: массив {path, description}
        $dto->gallery = $dto->normalizeGallery($data['gallery'] ?? []);

        // infoItems: массив {kind, name, value, file_path?}
        $dto->infoItems = $dto->normalizeInfoItems($data['infoItems'] ?? []);

        return $dto;
    }

    /**
     * Нормализует массив галереи.
     *
     * @param array $raw
     * @return array{path: string, description: string}[]
     */
    private function normalizeGallery(array $raw): array
    {
        $result = [];
        foreach ($raw as $item) {
            if (!is_array($item)) {
                continue;
            }
            $path = trim((string)($item['path'] ?? ''));
            if ($path === '') {
                continue;
            }
            $result[] = [
                'path' => $path,
                'description' => is_string($item['description'] ?? null) ? $item['description'] : '',
            ];
        }
        return $result;
    }

    /**
     * Нормализует массив доп. полей.
     *
     * @param array $raw
     * @return array{kind: string, name: string, value: string, file_path?: string}[]
     */
    private function normalizeInfoItems(array $raw): array
    {
        $result = [];
        foreach ($raw as $item) {
            if (!is_array($item)) {
                continue;
            }
            $kind = trim((string)($item['kind'] ?? ''));
            $name = trim((string)($item['name'] ?? ''));
            $value = trim((string)($item['value'] ?? ''));
            if ($kind === '' || $name === '' || $value === '') {
                continue;
            }

            $normalized = [
                'kind' => $kind,
                'name' => $name,
                'value' => $value,
            ];
            if (isset($item['file_path']) && is_string($item['file_path']) && $item['file_path'] !== '') {
                $normalized['file_path'] = $item['file_path'];
            }
            $result[] = $normalized;
        }
        return $result;
    }

    /**
     * Создаёт DTO из строки БД (результат SELECT с присоединёнными gallery/infoItems).
     *
     * @param array $row Ассоциативный массив из БД (после projectRow)
     * @return self
     */
    public static function fromDatabase(array $row): self
    {
        $dto = new self();
        $dto->id = $row['id'] ?? '';
        $dto->slug = $row['slug'] ?? '';
        $dto->title = $row['title'] ?? '';
        $dto->technologies = $row['technologies'] ?? '';
        $dto->icon = $row['icon'] ?? 'bi-briefcase';
        $dto->description = $row['description'] ?? '';
        $dto->solution = $row['solution'] ?? '';
        $dto->result = $row['result'] ?? '';
        $dto->client = $row['client'] ?? '';
        $dto->year = isset($row['project_year']) && $row['project_year'] !== '' ? (int)$row['project_year'] : null;
        $dto->duration = $row['duration'] ?? '';
        $dto->stack = $row['stack'] ?? '';
        $dto->active = (bool)($row['is_published'] ?? $row['active'] ?? false);

        // JSON поля
        $dto->task = !empty($row['task'])
            ? (is_array($row['task']) ? $row['task'] : (json_decode($row['task'], true) ?: []))
            : [];
        $dto->results = !empty($row['results'])
            ? (is_array($row['results']) ? $row['results'] : (json_decode($row['results'], true) ?: []))
            : [];
        $dto->screenshots = !empty($row['screenshots'])
            ? (is_array($row['screenshots']) ? $row['screenshots'] : (json_decode($row['screenshots'], true) ?: []))
            : [];

        // Вложенные (уже загружены в projectRow)
        $dto->gallery = $row['gallery'] ?? [];
        $dto->infoItems = $row['infoItems'] ?? [];

        return $dto;
    }

    /**
     * Валидирует DTO, заполняет $this->errors.
     *
     * @return bool true если валидно
     */
    public function validate(): bool
    {
        $this->errors = [];

        // slug: обязателен, до 180 chars, только валидные символы
        if (!$this->slug) {
            $this->errors['slug'] = 'Slug обязателен';
        } elseif (mb_strlen($this->slug) > 180) {
            $this->errors['slug'] = 'Slug не длиннее 180 символов';
        } elseif (!preg_match('/^[a-z0-9\-]+$/i', $this->slug)) {
            $this->errors['slug'] = 'Slug только латиница, цифры, дефис';
        }

        // title: обязателен
        if (!$this->title) {
            $this->errors['title'] = 'Заголовок обязателен';
        } elseif (mb_strlen($this->title) > 255) {
            $this->errors['title'] = 'Заголовок не длиннее 255 символов';
        }

        // technologies: обязателен
        if (!$this->technologies) {
            $this->errors['technologies'] = 'Технологии обязательны';
        } elseif (mb_strlen($this->technologies) > 500) {
            $this->errors['technologies'] = 'Технологии не длиннее 500 символов';
        }

        // description: обязателен
        if (!$this->description) {
            $this->errors['description'] = 'Описание обязательно';
        }

        // screenshots: проверка путей
        foreach ($this->screenshots as $index => $path) {
            if (!preg_match(self::SCREENSHOT_PATH_REGEX, $path)) {
                $this->errors["screenshots.$index"] = "Неверный путь скриншота: $path";
            }
        }

        // gallery: проверка путей (должны совпадать с разрешенными паттернами)
        foreach ($this->gallery as $index => $item) {
            if (!preg_match(self::SCREENSHOT_PATH_REGEX, $item['path'])) {
                $this->errors["gallery.$index.path"] = "Неверный путь изображения галереи: {$item['path']}";
            }
        }

        // infoItems: валидация file_path если есть
        foreach ($this->infoItems as $index => $item) {
            if (!empty($item['file_path'])) {
                $valid = preg_match(self::DOC_FILE_PATH_REGEX, $item['file_path'])
                    || preg_match(self::IMG_FILE_PATH_REGEX, $item['file_path']);
                if (!$valid) {
                    $this->errors["infoItems.$index.file_path"] = "Неверный путь файла: {$item['file_path']}";
                }
            }
        }

        // year: если задан — разумный год
        if ($this->year !== null && ($this->year < 1900 || $this->year > date('Y') + 1)) {
            $this->errors['year'] = 'Неверный год';
        }

        // icon: дефолт если пустой
        if (!$this->icon) {
            $this->icon = 'bi-briefcase';
        }

        return empty($this->errors);
    }

    /**
     * Возвращает ошибки валидации.
     *
     * @return array ['field' => 'message', ...]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Проверяет, прошла ли валидация.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if (empty($this->errors)) {
            $this->validate();
        }
        return empty($this->errors);
    }

    /**
     * Преобразует в массив для сохранения в основную таблицу portfolio_projects.
     * Ключи совпадают с колонками таблицы.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'technologies' => $this->technologies,
            'icon' => $this->icon,
            'description' => $this->description,
            'task_json' => json_encode($this->task, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'solution' => $this->solution,
            'result' => $this->result,
            'client' => $this->client,
            'project_year' => $this->year,
            'duration' => $this->duration,
            'stack' => $this->stack,
            'results_json' => json_encode($this->results, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'screenshots_json' => json_encode($this->screenshots, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'is_published' => $this->active ? 1 : 0,
        ];
    }

    /**
     * Преобразует в массив для JSON API ответа.
     *
     * @return array
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'technologies' => $this->technologies,
            'icon' => $this->icon,
            'description' => $this->description,
            'solution' => $this->solution,
            'result' => $this->result,
            'client' => $this->client,
            'year' => $this->year,
            'duration' => $this->duration,
            'stack' => $this->stack,
            'task' => $this->task,
            'results' => $this->results,
            'screenshots' => $this->screenshots,
            'gallery' => $this->gallery,
            'infoItems' => $this->infoItems,
            'active' => $this->active,
        ];
    }
}
