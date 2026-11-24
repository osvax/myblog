<?php

namespace App\Transformers;

/**
 * Transformer — базовый класс для трансформеров DTO в API-массивы.
 *
 * Трансформеры выносят логику сериализации из DTO в отдельные классы,
 * позволяя:
 * - Иметь разные представления одного DTO (админка / публичное API / списки)
 * - Не загрязнять DTO методами toApiArray(), toPublicArray() и т.д.
 * - Легко менять формат ответа без изменения DTO
 */
abstract class Transformer
{
    /**
     * Преобразует один DTO в массив для API.
     *
     * @param object $dto Data Transfer Object
     * @return array
     */
    abstract public function transform(object $dto): array;

    /**
     * Преобразует коллекцию DTO в массив массивов.
     *
     * @param iterable<object> $items
     * @return array
     */
    public function transformCollection(iterable $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = $this->transform($item);
        }
        return $result;
    }

    /**
     * Создаёт метаданные пагинации.
     *
     * @param int $total
     * @param int $page
     * @param int $perPage
     * @return array
     */
    protected function paginationMeta(int $total, int $page, int $perPage): array
    {
        $lastPage = (int)ceil($total / max(1, $perPage));
        return [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage,
            'from' => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
            'to' => min($page * $perPage, $total),
            'has_more' => $page < $lastPage,
        ];
    }
}