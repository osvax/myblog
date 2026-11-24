<?php

namespace App\Transformers;

use App\DTO\ArticleDTO;

/**
 * ArticleTransformer — трансформер для ArticleDTO.
 *
 * Предоставляет разные представления статьи:
 * - transform() — полное представление для админки/деталей
 * - transformList() — упрощённое для списков (без content)
 * - transformPublic() — для публичного API (только опубликованные поля)
 */
class ArticleTransformer extends Transformer
{
    /**
     * Полное представление статьи (для админки и детального просмотра).
     *
     * @param object $dto ArticleDTO
     * @return array
     */
    public function transform(object $dto): array
    {
        assert($dto instanceof ArticleDTO);
        return [
            'id' => $dto->id,
            'slug' => $dto->slug,
            'title' => $dto->title,
            'description' => $dto->description,
            'category' => $dto->category,
            'category_id' => $dto->category_id,
            'content' => $dto->content,
            'author' => $dto->author,
            'date' => $dto->date,
            'active' => $dto->active,
            'tags' => $dto->tags,
            'cover' => $dto->cover,
            'icon' => $dto->icon,
            'views' => $dto->views,
            'published_at' => $dto->published_at,
        ];
    }

    /**
     * Упрощённое представление для списков статей (без content).
     *
     * @param ArticleDTO $dto
     * @return array
     */
    public function transformList(ArticleDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'slug' => $dto->slug,
            'title' => $dto->title,
            'description' => $dto->description,
            'category' => $dto->category,
            'category_id' => $dto->category_id,
            'author' => $dto->author,
            'date' => $dto->date,
            'active' => $dto->active,
            'tags' => $dto->tags,
            'cover' => $dto->cover,
            'icon' => $dto->icon,
            'views' => $dto->views,
            'published_at' => $dto->published_at,
        ];
    }

    /**
     * Публичное представление (только для опубликованных статей).
     * Исключает служебные поля.
     *
     * @param ArticleDTO $dto
     * @return array
     */
    public function transformPublic(ArticleDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'slug' => $dto->slug,
            'title' => $dto->title,
            'description' => $dto->description,
            'category' => $dto->category,
            'author' => $dto->author,
            'date' => $dto->date,
            'tags' => $dto->tags,
            'cover' => $dto->cover,
            'icon' => $dto->icon,
            'views' => $dto->views,
            'published_at' => $dto->published_at,
        ];
    }

    /**
     * Мини-представление для ссылок/выборов (id, title, slug).
     *
     * @param ArticleDTO $dto
     * @return array
     */
    public function transformMinimal(ArticleDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'slug' => $dto->slug,
            'title' => $dto->title,
        ];
    }
}