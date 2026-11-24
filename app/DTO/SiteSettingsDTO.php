<?php

namespace App\DTO;

/**
 * SiteSettingsDTO — объект переноса данных для настроек сайта (ключ-значение с типами).
 *
 * === ПОТОК ДАННЫХ (Data Flow) ===
 *
 * 1. ВХОД (Request → DTO):
 *    - PUT /api/admin/settings (сохранение всех настроек)
 *      Тело: {
 *        "siteTitle": "Мой блог",
 *        "siteDescription": "Описание...",
 *        "themeColor": "#3b82f6",
 *        "enableComments": true,
 *        "postsPerPage": 10,
 *        "statisticsSections": ["visits","orders","messages"],
 *        "contactsAboutHtml": "<p>HTML...</p>",
 *        "themeFontFile": "uploads/fonts/xxx.woff2"
 *      }
 *    - POST /api/admin/settings/fields (добавление пользовательского поля)
 *      Тело: { "key": "customField", "value": "", "type": "text", "tab_group": "general", "placeholder": "" }
 *    - DELETE /api/admin/settings/fields/{key} (удаление поля)
 *    - Контроллер: SiteSettingsDTO::fromRequest($request->json())
 *
 * 2. ВАЛИДАЦИЯ (DTO → Errors):
 *    - $dto->validate() — проверяет каждый ключ по его типу
 *    - Типы: text, textarea, number, email, tel, url, color, checkbox, select, json
 *    - Ошибки → $dto->getErrors()
 *
 * 3. СОХРАНЕНИЕ (DTO → Database):
 *    - Repository: saveAllFromDTO(SiteSettingsDTO $dto)
 *    - DTO::toArray() → массив ['key' => ['value' => ..., 'type' => ...], ...]
 *    - UPSERT в va_site_settings (setting_key, setting_value, setting_type, tab_group, display_order, placeholder, options)
 *
 * 4. ЧТЕНИЕ (Database → DTO):
 *    - Repository: all(), grouped()
 *    - SELECT * FROM va_site_settings ORDER BY tab_group, display_order
 *    - SiteSettingsDTO::fromDatabase($rows) → DTO с разобранными значениями
 *
 * 5. ВЫХОД (DTO → JSON Response):
 *    - DTO::toApiArray() → плоский массив ['key' => value, ...]
 *    - DTO::toGroupedArray() → сгруппированный по tab_group для админки
 *    - Контроллер возвращает json
 *
 * === СТРУКТУРА ТАБЛИЦЫ va_site_settings ===
 * - id (INT, PK, AI)
 * - setting_key (VARCHAR 100, UNIQUE) — ключ настройки
 * - setting_value (TEXT) — значение (сериализованное в строку)
 * - setting_type (VARCHAR 20) — тип: text, textarea, number, email, tel, url, color, checkbox, select, json
 * - tab_group (VARCHAR 50) — группа/вкладка: general, seo, contacts, theme, statistics, custom
 * - display_order (INT) — порядок сортировки в группе
 * - placeholder (VARCHAR 255) — подсказка в поле ввода
 * - options (JSON, NULL) — для select: ["option1", "option2", ...]
 * - created_at, updated_at (TIMESTAMP)
 *
 * === ПРЕДОПРЕДЕЛЁННЫЕ НАСТРОЙКИ (системные, не удаляются) ===
 * general: siteTitle, siteDescription, siteKeywords, adminEmail, timezone, language, maintenanceMode, maintenanceMessage
 * seo: seoTitleTemplate, seoDescriptionTemplate, ogImage, twitterCard
 * contacts: contactsAddress, contactsPhone, contactsEmail, contactsMapUrl, contactsAboutHtml, contactsAboutPhoto
 * theme: themeColor, themeFontFile, themeDarkMode, customCss, customJs
 * statistics: statisticsSections (json массив), gaMeasurementId, yaCounterId
 * custom: пользовательские поля (добавляются через админку)
 */
class SiteSettingsDTO
{
    // === Значения настроек (плоский массив key => value) ===
    /** @var array<string, mixed> */
    private array $values = [];

    // === Метаданные полей (key => ['type', 'tab_group', 'label', 'placeholder', 'options', 'display_order']) ===
    /** @var array<string, array> */
    private array $meta = [];

    // === Ошибки валидации ===
    private array $errors = [];

    // === Константы типов ===
    public const TYPE_TEXT = 'text';
    public const TYPE_TEXTAREA = 'textarea';
    public const TYPE_NUMBER = 'number';
    public const TYPE_EMAIL = 'email';
    public const TYPE_TEL = 'tel';
    public const TYPE_URL = 'url';
    public const TYPE_COLOR = 'color';
    public const TYPE_CHECKBOX = 'checkbox';
    public const TYPE_SELECT = 'select';
    public const TYPE_JSON = 'json';
    public const TYPE_VALUES = [
        self::TYPE_TEXT, self::TYPE_TEXTAREA, self::TYPE_NUMBER,
        self::TYPE_EMAIL, self::TYPE_TEL, self::TYPE_URL,
        self::TYPE_COLOR, self::TYPE_CHECKBOX, self::TYPE_SELECT, self::TYPE_JSON
    ];

    // === Группы вкладок ===
    public const TAB_GENERAL = 'general';
    public const TAB_SEO = 'seo';
    public const TAB_CONTACTS = 'contacts';
    public const TAB_THEME = 'theme';
    public const TAB_STATISTICS = 'statistics';
    public const TAB_CUSTOM = 'custom';
    public const TAB_VALUES = [
        self::TAB_GENERAL, self::TAB_SEO, self::TAB_CONTACTS,
        self::TAB_THEME, self::TAB_STATISTICS, self::TAB_CUSTOM
    ];

    // === Ключи системных настроек (не удаляются через админку) ===
    public const SYSTEM_KEYS = [
        // general
        'siteTitle', 'siteDescription', 'siteKeywords', 'adminEmail',
        'timezone', 'language', 'maintenanceMode', 'maintenanceMessage',
        // seo
        'seoTitleTemplate', 'seoDescriptionTemplate', 'ogImage', 'twitterCard',
        // contacts
        'contactsAddress', 'contactsPhone', 'contactsEmail', 'contactsMapUrl',
        'contactsAboutHtml', 'contactsAboutPhoto',
        // theme
        'themeColor', 'themeFontFile', 'themeDarkMode', 'customCss', 'customJs',
        // statistics
        'statisticsSections', 'gaMeasurementId', 'yaCounterId',
    ];

    /**
     * Создаёт DTO из массива настроек (для сохранения: PUT /api/admin/settings).
     *
     * ИСПОЛЬЗОВАНИЕ:
     * ```php
     * $jsonData = $request->json(); // Плоский массив key => value
     * $dto = SiteSettingsDTO::fromRequest($jsonData);
     * // Метаданные (типы, группы) берутся из БД или дефолтных значений
     * ```
     *
     * @param array $data Плоский массив ['key' => value, ...]
     * @param array $existingMeta Существующие метаданные из БД ['key' => ['type'=>..., 'tab_group'=>...], ...]
     * @return self
     */
    public static function fromRequest(array $data, array $existingMeta = []): self
    {
        $dto = new self();

        // Загружаем значения
        foreach ($data as $key => $value) {
            $dto->values[$key] = $value;
        }

        // Загружаем метаданные (если переданы из БД)
        foreach ($existingMeta as $key => $meta) {
            $dto->meta[$key] = $meta;
        }

        // Для ключей без метаданных — пытаемся угадать тип по ключу/значению
        foreach ($data as $key => $value) {
            if (!isset($dto->meta[$key])) {
                $dto->meta[$key] = $dto->guessMeta($key, $value);
            }
        }

        return $dto;
    }

    /**
     * Создаёт DTO из строк БД (результат SELECT * FROM va_site_settings).
     *
     * @param array $rows Массив строк из БД
     * @return self
     */
    public static function fromDatabase(array $rows): self
    {
        $dto = new self();

        foreach ($rows as $row) {
            $key = $row['setting_key'];
            $type = $row['setting_type'] ?? self::TYPE_TEXT;
            $value = $row['setting_value'] ?? '';

            // Приводим значение к правильному типу
            $dto->values[$key] = $dto->castValue($value, $type);

            // Сохраняем метаданные
            $dto->meta[$key] = [
                'type' => $type,
                'tab_group' => $row['tab_group'] ?? self::TAB_CUSTOM,
                'label' => $row['setting_key'], // Можно расширить: добавить label в БД
                'placeholder' => $row['placeholder'] ?? '',
                'options' => $row['options'] ? json_decode($row['options'], true) : [],
                'display_order' => (int)($row['display_order'] ?? 0),
            ];
        }

        return $dto;
    }

    /**
     * Угадывает метаданные по ключу и значению.
     */
    private function guessMeta(string $key, mixed $value): array
    {
        $type = self::TYPE_TEXT;
        $tabGroup = self::TAB_CUSTOM;

        // Эвристика по имени ключа
        if (str_starts_with($key, 'theme')) {
            $tabGroup = self::TAB_THEME;
        } elseif (str_starts_with($key, 'seo')) {
            $tabGroup = self::TAB_SEO;
        } elseif (str_starts_with($key, 'contacts')) {
            $tabGroup = self::TAB_CONTACTS;
        } elseif (str_starts_with($key, 'statistics')) {
            $tabGroup = self::TAB_STATISTICS;
        } else {
            $tabGroup = self::TAB_GENERAL;
        }

        // Эвристика по типу значения
        if (is_bool($value)) {
            $type = self::TYPE_CHECKBOX;
        } elseif (is_int($value) || (is_string($value) && preg_match('/^\d+$/', $value))) {
            $type = self::TYPE_NUMBER;
        } elseif (is_array($value)) {
            $type = self::TYPE_JSON;
        } elseif (is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            $type = self::TYPE_COLOR;
        } elseif (is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $type = self::TYPE_EMAIL;
        } elseif (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
            $type = self::TYPE_URL;
        } elseif (is_string($value) && mb_strlen($value) > 500) {
            $type = self::TYPE_TEXTAREA;
        }

        return [
            'type' => $type,
            'tab_group' => $tabGroup,
            'label' => $key,
            'placeholder' => '',
            'options' => [],
            'display_order' => 0,
        ];
    }

    /**
     * Приводит строковое значение из БД к правильному PHP типу.
     */
    private function castValue(?string $value, string $type): mixed
    {
        if ($value === null || $value === '') {
            return match ($type) {
                self::TYPE_CHECKBOX => false,
                self::TYPE_NUMBER => 0,
                self::TYPE_JSON => [],
                default => '',
            };
        }

        return match ($type) {
            self::TYPE_CHECKBOX => (bool)$value,
            self::TYPE_NUMBER => (int)$value,
            self::TYPE_JSON => json_decode($value, true) ?? [],
            default => $value,
        };
    }

    /**
     * Форматирует PHP значение в строку для сохранения в БД.
     */
    private function formatValue(mixed $value, string $type): string
    {
        return match ($type) {
            self::TYPE_CHECKBOX => $value ? '1' : '0',
            self::TYPE_NUMBER => (string)(int)$value,
            self::TYPE_COLOR => (is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)) ? $value : '',
            self::TYPE_JSON => is_array($value)
                ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : (string)$value,
            default => (string)$value,
        };
    }

    /**
     * Валидирует все настройки по их типам.
     *
     * @return bool
     */
    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->values as $key => $value) {
            $meta = $this->meta[$key] ?? ['type' => self::TYPE_TEXT];
            $type = $meta['type'] ?? self::TYPE_TEXT;

            $error = $this->validateValue($key, $value, $type);
            if ($error !== null) {
                $this->errors[$key] = $error;
            }
        }

        return empty($this->errors);
    }

    /**
     * Валидация одного значения по типу.
     */
    private function validateValue(string $key, mixed $value, string $type): ?string
    {
        return match ($type) {
            self::TYPE_EMAIL => (!is_string($value) || $value === '' || !filter_var($value, FILTER_VALIDATE_EMAIL))
                ? 'Неверный email' : null,
            self::TYPE_URL => (!is_string($value) || $value === '' || !filter_var($value, FILTER_VALIDATE_URL))
                ? 'Неверный URL' : null,
            self::TYPE_COLOR => (!is_string($value) || $value === '' || !preg_match('/^#[0-9a-fA-F]{6}$/', $value))
                ? 'Цвет в формате #RRGGBB' : null,
            self::TYPE_NUMBER => (!is_numeric($value))
                ? 'Должно быть числом' : null,
            self::TYPE_CHECKBOX => null, // bool всегда валиден
            self::TYPE_JSON => (!is_array($value))
                ? 'Должно быть массивом/объектом' : null,
            self::TYPE_SELECT => null, // строка, валидация опциональна
            default => null, // text, textarea, tel — любая строка
        };
    }

    /**
     * Возвращает ошибки валидации.
     *
     * @return array ['key' => 'message', ...]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Проверяет, прошла ли валидация.
     */
    public function isValid(): bool
    {
        if (empty($this->errors)) {
            $this->validate();
        }
        return empty($this->errors);
    }

    /**
     * Получить значение настройки.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    /**
     * Установить значение настройки.
     */
    public function set(string $key, mixed $value, ?array $meta = null): void
    {
        $this->values[$key] = $value;
        if ($meta !== null) {
            $this->meta[$key] = $meta;
        }
    }

    /**
     * Проверить существование ключа.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    /**
     * Получить все значения (плоский массив для сохранения/API).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->values;
    }

    /**
     * Получить массив для сохранения в БД (с типами).
     *
     * @return array<string, array{value: string, type: string}>
     */
    public function toDbArray(): array
    {
        $result = [];
        foreach ($this->values as $key => $value) {
            $meta = $this->meta[$key] ?? ['type' => self::TYPE_TEXT];
            $type = $meta['type'] ?? self::TYPE_TEXT;
            $result[$key] = [
                'value' => $this->formatValue($value, $type),
                'type' => $type,
            ];
        }
        return $result;
    }

    /**
     * Получить метаданные для сохранения нового поля.
     *
     * @return array<string, array>
     */
    public function getMetaForSave(): array
    {
        return $this->meta;
    }

    /**
     * Преобразует в плоский массив для JSON API (settings).
     *
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        return $this->values;
    }

    /**
     * Преобразует в сгруппированный массив для админки (grouped).
     *
     * СТРУКТУРА ОТВЕТА:
     * [
     *   'general' => [
     *     ['key' => 'siteTitle', 'value' => 'Мой блог', 'type' => 'text', 'label' => 'Название', 'placeholder' => '', 'options' => [], 'display_order' => 1],
     *     ...
     *   ],
     *   'theme' => [...],
     * ]
     *
     * @return array<string, array>
     */
    public function toGroupedArray(): array
    {
        $grouped = [];
        foreach ($this->values as $key => $value) {
            $meta = $this->meta[$key] ?? [
                'type' => self::TYPE_TEXT,
                'tab_group' => self::TAB_GENERAL,
                'label' => $key,
                'placeholder' => '',
                'options' => [],
                'display_order' => 0,
            ];

            $tabGroup = $meta['tab_group'] ?? self::TAB_GENERAL;
            $grouped[$tabGroup][] = [
                'key' => $key,
                'value' => $value,
                'type' => $meta['type'] ?? self::TYPE_TEXT,
                'label' => $meta['label'] ?? $key,
                'placeholder' => $meta['placeholder'] ?? '',
                'options' => $meta['options'] ?? [],
                'display_order' => $meta['display_order'] ?? 0,
            ];
        }

        // Сортируем внутри каждой группы по display_order
        foreach ($grouped as &$group) {
            usort($group, fn($a, $b) => $a['display_order'] <=> $b['display_order']);
        }

        return $grouped;
    }

    /**
     * Проверяет, является ли ключ системным (не удаляемым через админку).
     */
    public function isSystemKey(string $key): bool
    {
        return in_array($key, self::SYSTEM_KEYS, true);
    }

    /**
     * Получить список системных ключей.
     *
     * @return string[]
     */
    public static function getSystemKeys(): array
    {
        return self::SYSTEM_KEYS;
    }

    /**
     * Получить список поддерживаемых типов полей.
     *
     * @return string[]
     */
    public static function getSupportedTypes(): array
    {
        return self::TYPE_VALUES;
    }
}