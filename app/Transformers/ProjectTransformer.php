<?php

namespace App\Transformers;

use App\DTO\ProjectDTO;

/**
 * ProjectTransformer — трансформер для ProjectDTO.
 *
 * Предоставляет разные представления проекта портфолио:
 * - transform() — полное представление для админки/деталей
 * - transformList() — упрощённое для списков
 * - transformPublic() — для публичного API
 */
class ProjectTransformer extends Transformer
{
    /**
     * Полное представление проекта (для админки и детального просмотра).
     *
     * @param object $dto ProjectDTO
     * @return array
     */
    public function transform(object $dto): array
    {
        assert($dto instanceof ProjectDTO);
        return [
            'id' => $dto->id,
            'slug' => $dto->slug,
            'title' => $dto->title,
            'technologies' => $dto->technologies,
            'icon' => $dto->icon,
            'description' => $dto->description,
            'solution' => $dto->solution,
            'result' => $dto->result,
            'client' => $dto->client,
            'year' => $dto->year,
            'duration' => $dto->duration,
            'stack' => $dto->stack,
            'task' => $dto->task,
            'results' => $dto->results,
            'screenshots' => $dto->screenshots,
            'gallery' => $dto->gallery,
            'infoItems' => $dto->infoItems,
            'active' => $dto->active,
        ];
    }

    /**
     * Упрощённое представление для списков проектов.
     *
     * @param ProjectDTO $dto
     * @return array
     */
    public function transformList(ProjectDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'slug' => $dto->slug,
            'title' => $dto->title,
            'technologies' => $dto->technologies,
            'icon' => $dto->icon,
            'description' => $dto->description,
            'client' => $dto->client,
            'year' => $dto->year,
            'duration' => $dto->duration,
            'stack' => $dto->stack,
            'active' => $dto->active,
            'screenshots' => $dto->screenshots,
        ];
    }

    /**
     * Публичное представление (для опубликованных проектов).
     *
     * @param ProjectDTO $dto
     * @return array
     */
    public function transformPublic(ProjectDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'slug' => $dto->slug,
            'title' => $dto->title,
            'technologies' => $dto->technologies,
            'icon' => $dto->icon,
            'description' => $dto->description,
            'solution' => $dto->solution,
            'result' => $dto->result,
            'client' => $dto->client,
            'year' => $dto->year,
            'duration' => $dto->duration,
            'stack' => $dto->stack,
            'task' => $dto->task,
            'results' => $dto->results,
            'screenshots' => $dto->screenshots,
            'gallery' => $dto->gallery,
            'infoItems' => $dto->infoItems,
        ];
    }

    /**
     * Мини-представление для ссылок/выборов.
     *
     * @param ProjectDTO $dto
     * @return array
     */
    public function transformMinimal(ProjectDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'slug' => $dto->slug,
            'title' => $dto->title,
            'icon' => $dto->icon,
        ];
    }
}