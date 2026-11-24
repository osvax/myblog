<?php

namespace App\Transformers;

use App\DTO\MessageDTO;

/**
 * MessageTransformer — трансформер для MessageDTO.
 *
 * Сообщения имеют типы: contact, question, comment.
 * Трансформер предоставляет представления для админки и публичного API.
 */
class MessageTransformer extends Transformer
{
    /**
     * Полное представление сообщения (для админки).
     *
     * @param object $dto MessageDTO
     * @return array
     */
    public function transform(object $dto): array
    {
        assert($dto instanceof MessageDTO);
        return [
            'id' => $dto->id,
            'type' => $dto->type,
            'name' => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'subject' => $dto->subject,
            'body' => $dto->body,
            'articleSlug' => $dto->articleSlug,
            'parentId' => $dto->parentId,
            'isStaff' => $dto->isStaff,
            'isApproved' => $dto->isApproved,
            'isRead' => $dto->isRead,
            'createdAt' => $dto->createdAt,
            'updatedAt' => $dto->updatedAt,
        ];
    }

    /**
     * Представление для списков в админке (то же, что полное — сообщения лёгкие).
     *
     * @param MessageDTO $dto
     * @return array
     */
    public function transformList(MessageDTO $dto): array
    {
        return $this->transform($dto);
    }

    /**
     * Публичное представление комментария (минимальные поля).
     *
     * @param MessageDTO $dto
     * @return array
     */
    public function transformPublicComment(MessageDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'parentId' => $dto->parentId,
            'isStaff' => $dto->isStaff,
            'name' => $dto->name,
            'body' => $dto->body,
            'createdAt' => $dto->createdAt,
        ];
    }

    /**
     * Представление для дерева комментариев (с вложенными ответами).
     *
     * @param MessageDTO $dto
     * @param array $replies Уже преобразованные ответы
     * @return array
     */
    public function transformWithReplies(MessageDTO $dto, array $replies = []): array
    {
        $base = $this->transformPublicComment($dto);
        $base['replies'] = $replies;
        return $base;
    }
}