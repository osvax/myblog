<?php

declare(strict_types=1);

namespace App\Transformers;

use App\DTO\SiteSettingsDTO;

/**
 * SiteSettingsTransformer — трансформер для SiteSettingsDTO.
 *
 * Настройки сайта — это плоский массив key => value с метаданными типов.
 * Трансформер предоставляет плоское и сгруппированное представления.
 */
final class SiteSettingsTransformer extends Transformer
{
    /**
     * Плоское представление настроек (для API: key => value).
     *
     * @param object $dto SiteSettingsDTO
     * @return array<string, mixed>
     */
    public function transform(object $dto): array
    {
        assert($dto instanceof SiteSettingsDTO);
        return $dto->toApiArray();
    }

    /**
     * Сгруппированное представление по вкладкам (для админки).
     *
     * @param SiteSettingsDTO $dto
     * @return array<string, array>
     */
    public function transformGrouped(SiteSettingsDTO $dto): array
    {
        return $dto->toGroupedArray();
    }

    /**
     * Представление одной настройки (для создания/обновления отдельного поля).
     *
     * @param SiteSettingsDTO $dto
     * @param string $key
     * @return array|null
     */
    public function transformSingle(SiteSettingsDTO $dto, string $key): ?array
    {
        if (!$dto->has($key)) {
            return null;
        }

        $meta = $dto->getMetaForSave()[$key] ?? [
            'type' => SiteSettingsDTO::TYPE_TEXT,
            'tab_group' => SiteSettingsDTO::TAB_GENERAL,
            'label' => $key,
            'placeholder' => '',
            'options' => [],
            'display_order' => 0,
        ];

        return [
            'key' => $key,
            'value' => $dto->get($key),
            'type' => $meta['type'],
            'tab_group' => $meta['tab_group'],
            'label' => $meta['label'],
            'placeholder' => $meta['placeholder'],
            'options' => $meta['options'],
            'display_order' => $meta['display_order'],
        ];
    }

    /**
     * Список системных ключей (не удаляемых через админку).
     *
     * @return string[]
     */
    public function getSystemKeys(): array
    {
        return SiteSettingsDTO::getSystemKeys();
    }

    /**
     * Список поддерживаемых типов полей.
     *
     * @return string[]
     */
    public function getSupportedTypes(): array
    {
        return SiteSettingsDTO::getSupportedTypes();
    }
}
