<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * ConsultantSettingsDTO — настройки онлайн-консультанта.
 *
 * === СТРУКТУРА ТАБЛИЦЫ va_consultant_settings ===
 * - setting_key (VARCHAR 64, PK) — ключ настройки
 * - setting_value (TEXT) — значение настройки
 */
final class ConsultantSettingsDTO extends AbstractDTO
{
    /** @var bool Включен ли консультант */
    public bool $enabled = true;

    /** @var string Цвет кнопки */
    public string $buttonColor = '#ff8c1a';

    /** @var string Цвет текста кнопки */
    public string $buttonTextColor = '#ffffff';

    /** @var string Позиция кнопки: left/right */
    public string $buttonPosition = 'right';

    /** @var string Рабочие часы */
    public string $workingHours = '09:00-20:00';

    /** @var string Цензурируемые слова (через запятую) */
    public string $censoredWords = '';

    /** @var string Приветственное сообщение */
    public string $welcomeMessage = 'Здравствуйте! Чем могу помочь?';

    /** @var string Email для уведомлений админа */
    public string $notificationEmail = '';

    /**
     * Создаёт DTO из массива строк БД.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return static
     */
    public static function fromDatabase(array $rows): static
    {
        $dto = new self();
        foreach ($rows as $row) {
            $key = $row['setting_key'] ?? '';
            $value = $row['setting_value'] ?? '';
            if ($key === 'enabled') {
                $dto->enabled = $value === '1';
            } elseif (property_exists($dto, $key)) {
                $dto->$key = $value;
            }
        }
        return $dto;
    }

    /**
     * Массив для INSERT/UPDATE в БД (key => value).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled ? '1' : '0',
            'buttonColor' => $this->buttonColor,
            'buttonTextColor' => $this->buttonTextColor,
            'buttonPosition' => $this->buttonPosition,
            'workingHours' => $this->workingHours,
            'censoredWords' => $this->censoredWords,
            'welcomeMessage' => $this->welcomeMessage,
            'notificationEmail' => $this->notificationEmail,
        ];
    }

    /**
     * Массив для API ответа / JSON.
     *
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'buttonColor' => $this->buttonColor,
            'buttonTextColor' => $this->buttonTextColor,
            'buttonPosition' => $this->buttonPosition,
            'workingHours' => $this->workingHours,
            'censoredWords' => $this->censoredWords,
            'welcomeMessage' => $this->welcomeMessage,
            'notificationEmail' => $this->notificationEmail,
        ];
    }
}
