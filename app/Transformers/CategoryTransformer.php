<?php

namespace App\Transformers;

use App\DTO\CategoryDTO;

/**
 * CategoryTransformer — трансформер для CategoryDTO.
 */
class CategoryTransformer extends Transformer
{
    /**
     * Полное представление рубрики.
     *
     * @param object $dto CategoryDTO
     * @return array
     */
    public function transform(object $dto): array
    {
        assert($dto instanceof CategoryDTO);
        return [
            'id' => $dto->id,
            'name' => $dto->name,
            'slug' => $dto->slug,
            'description' => $dto->description,
            'articles_count' => $dto->articles_count,
        ];
    }

    /**
     * Представление для списков (то же, что полное — рубрики лёгкие).
     *
     * @param CategoryDTO $dto
     * @return array
     */
    public function transformList(CategoryDTO $dto): array
    {
        return $this->transform($dto);
    }

    /**
     * Публичное представление.
     *
     * @param CategoryDTO $dto
     * @return array
     */
    public function transformPublic(CategoryDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'name' => $dto->name,
            'slug' => $dto->slug,
            'description' => $dto->description,
            'articles_count' => $dto->articles_count,
        ];
    }

    /**
     * Мини-представление для селектов/ссылок.
     *
     * @param CategoryDTO $dto
     * @return array
     */
    public function transformMinimal(CategoryDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'name' => $dto->name,
            'slug' => $dto->slug,
        ];
    }
}