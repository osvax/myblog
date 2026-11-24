<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * AbstractDTO — базовый абстрактный класс для всех DTO.
 *
 * Определяет общий контракт:
 * - fromDatabase(array $row): static — создание из строки БД (обязательно)
 * - toArray(): array — массив для INSERT/UPDATE в БД (обязательно)
 * - toApiArray(): array — массив для JSON API ответа (обязательно)
 * - fromRequest(array $data, ?int $id = null): static — создание из HTTP запроса (опционально)
 * - validate(): bool — валидация, заполняет $this->errors (опционально)
 * - getErrors(): array — возвращает ошибки валидации (опционально)
 *
 * Наследники должны:
 * 1. Объявить публичные свойства (типизированные)
 * 2. Реализовать три обязательных абстрактных метода
 * 3. Использовать protected array $errors для хранения ошибок валидации
 * 4. Можно переопределить fromRequest/validate/getErrors если нужна валидация входящих данных
 */
abstract class AbstractDTO
{
    /** @var array<string, string> Ошибки валидации ['field' => 'message'] */
    protected array $errors = [];

    // ===================== ОБЯЗАТЕЛЬНЫЕ МЕТОДЫ (все DTO) =====================

    /**
     * Создаёт DTO из строки БД (результат SELECT).
     *
     * @param array $row Ассоциативный массив из БД
     * @return static
     */
    abstract public static function fromDatabase(array $row): static;

    /**
     * Преобразует в массив для сохранения в БД.
     * Ключи должны совпадать с названиями колонок таблицы.
     *
     * @return array<string, mixed> ['column' => value, ...]
     */
    abstract public function toArray(): array;

    /**
     * Преобразует в массив для JSON API ответа.
     *
     * @return array<string, mixed>
     */
    abstract public function toApiArray(): array;

    // ===================== ОПЦИОНАЛЬНЫЕ МЕТОДЫ (для входящих данных) =====================

    /**
     * Создаёт DTO из массива запроса (JSON / POST / multipart).
     * По умолчанию выбрасывает исключение — переопределите в DTO, которые принимают ввод.
     *
     * @param array $data Данные из $request->json() или $_POST
     * @param int|null $id ID из URL (для PUT/PATCH)
     * @return static
     * @throws \LogicException если не реализован
     */
    public static function fromRequest(array $data, ?int $id = null): static
    {
        throw new \LogicException(static::class . ' does not support fromRequest()');
    }

    /**
     * Валидирует DTO, заполняет $this->errors.
     * По умолчанию всегда валидно — переопределите для DTO с валидацией.
     *
     * @return bool
     */
    public function validate(): bool
    {
        $this->errors = [];
        return true;
    }

    /**
     * Дополнительная валидация для обновления (опционально).
     * По умолчанию делегирует в validate().
     *
     * @return bool
     */
    public function validateUpdate(): bool
    {
        return $this->validate();
    }

    /**
     * Возвращает ошибки валидации.
     *
     * @return array<string, string> ['field' => 'message', ...]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Проверяет, прошла ли валидация (запускает validate() если ошибки пусты).
     */
    public function isValid(): bool
    {
        if (empty($this->errors)) {
            $this->validate();
        }
        return empty($this->errors);
    }

    /**
     * Добавляет ошибку валидации.
     *
     * @param string $field Имя поля (dot notation для вложенных: 'gallery.0.path')
     * @param string $message Сообщение об ошибке
     * @return void
     */
    protected function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    /**
     * Проверяет наличие ошибки для поля.
     *
     * @param string $field
     * @return bool
     */
    protected function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    /**
     * Очищает ошибки валидации.
     *
     * @return void
     */
    protected function clearErrors(): void
    {
        $this->errors = [];
    }

    // ===================== HELPERS для наследников =====================

    /**
     * Безопасно получить строковое значение из массива.
     *
     * @param array $data
     * @param string $key
     * @param string $default
     * @return string
     */
    protected static function str(array $data, string $key, string $default = ''): string
    {
        return trim((string)($data[$key] ?? $default));
    }

    /**
     * Безопасно получить целое значение из массива.
     *
     * @param array $data
     * @param string $key
     * @param int $default
     * @return int
     */
    protected static function int(array $data, string $key, int $default = 0): int
    {
        $val = $data[$key] ?? $default;
        return is_numeric($val) ? (int)$val : $default;
    }

    /**
     * Безопасно получить булево значение из массива.
     *
     * @param array $data
     * @param string $key
     * @param bool $default
     * @return bool
     */
    protected static function bool(array $data, string $key, bool $default = false): bool
    {
        return filter_var($data[$key] ?? $default, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Безопасно получить массив строк из массива (через запятую или уже массив).
     *
     * @param array $data
     * @param string $key
     * @return string[]
     */
    protected static function stringArray(array $data, string $key): array
    {
        $val = $data[$key] ?? [];
        if (is_string($val)) {
            return array_values(array_filter(array_map('trim', explode(',', $val)), 'strlen'));
        }
        if (is_array($val)) {
            return array_values(array_filter($val, 'is_string'));
        }
        return [];
    }

    /**
     * Нормализует массив путей к файлам/картинкам.
     *
     * @param array $raw
     * @param string $pathKey
     * @param string $descKey
     * @return array{path: string, description: string}[]
     */
    protected static function normalizePaths(array $raw, string $pathKey = 'path', string $descKey = 'description'): array
    {
        $result = [];
        foreach ($raw as $item) {
            if (!is_array($item)) {
                continue;
            }
            $path = trim((string)($item[$pathKey] ?? ''));
            if ($path === '') {
                continue;
            }
            $result[] = [
                'path' => $path,
                'description' => is_string($item[$descKey] ?? null) ? $item[$descKey] : '',
            ];
        }
        return $result;
    }

    /**
     * Нормализует массив infoItems (kind, name, value, file_path?).
     *
     * @param array $raw
     * @return array{kind: string, name: string, value: string, file_path?: string}[]
     */
    protected static function normalizeInfoItems(array $raw): array
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
            $normalized = ['kind' => $kind, 'name' => $name, 'value' => $value];
            if (isset($item['file_path']) && is_string($item['file_path']) && $item['file_path'] !== '') {
                $normalized['file_path'] = $item['file_path'];
            }
            $result[] = $normalized;
        }
        return $result;
    }

    /**
     * Валидирует строку по длине.
     *
     * @param string $value
     * @param string $field
     * @param int $min
     * @param int $max
     * @param string|null $label Название для сообщения (если null — $field)
     * @return void
     */
    protected function validateStringLength(string $value, string $field, int $min, int $max, ?string $label = null): void
    {
        $len = mb_strlen($value);
        $label ??= $field;
        if ($len < $min) {
            $this->addError($field, "{$label} не короче {$min} символов");
        } elseif ($len > $max) {
            $this->addError($field, "{$label} не длиннее {$max} символов");
        }
    }

    /**
     * Валидирует email.
     *
     * @param string $email
     * @param string $field
     * @param int $maxLength
     * @return void
     */
    protected function validateEmail(string $email, string $field = 'email', int $maxLength = 255): void
    {
        if ($email === '') {
            $this->addError($field, 'Email обязателен');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'Неверный формат email');
        } elseif (mb_strlen($email) > $maxLength) {
            $this->addError($field, "Email не длиннее {$maxLength} символов");
        }
    }

    /**
     * Валидирует значение из допустимого списка (enum).
     *
     * @param string $value
     * @param string $field
     * @param array<string> $allowed
     * @param string|null $label
     * @return void
     */
    protected function validateEnum(string $value, string $field, array $allowed, ?string $label = null): void
    {
        if (!in_array($value, $allowed, true)) {
            $label ??= $field;
            $this->addError($field, "Недопустимое значение {$label}. Допустимые: " . implode(', ', $allowed));
        }
    }
}