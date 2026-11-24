<?php

declare(strict_types=1);

namespace App\Transformers;

use App\DTO\OrderDTO;

/**
 * OrderTransformer — трансформер для OrderDTO.
 *
 * Заказ имеет много полей, включая вложенные details_json.
 * Трансформер предоставляет разные уровни детализации.
 */
final class OrderTransformer extends Transformer
{
    /**
     * Полное представление заказа (для админки: детали + история).
     *
     * @param object $dto OrderDTO
     * @return array
     */
    public function transform(object $dto): array
    {
        assert($dto instanceof OrderDTO);
        return [
            'id' => $dto->id,
            'client' => $dto->clientName,
            'clientName' => $dto->clientName,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'type' => $dto->projectType,
            'projectType' => $dto->projectType,
            'budget' => $dto->budget,
            'deadline' => $dto->deadline ?? '',
            'orderDate' => $dto->orderDate,
            'createdAt' => $dto->orderDate,
            'status' => $dto->status,
            'completedAt' => $dto->completedAt ?? '',
            'paidAmount' => $dto->paidAmount,
            // вложенные из details_json
            'source' => $dto->source,
            'contactPerson' => $dto->contactPerson,
            'projectTitle' => $dto->projectTitle,
            'deadlineFrom' => $dto->deadlineFrom,
            'budgetDisplay' => $dto->budgetDisplay,
            'technicalBrief' => $dto->technicalBrief,
            'calculatorDetails' => $dto->calculatorDetails,
            'notes' => $dto->notes,
            'paymentHistory' => $dto->paymentHistory,
        ];
    }

    /**
     * Упрощённое представление для списков заказов.
     *
     * @param OrderDTO $dto
     * @return array
     */
    public function transformList(OrderDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'clientName' => $dto->clientName,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'projectType' => $dto->projectType,
            'budget' => $dto->budget,
            'budgetDisplay' => $dto->budgetDisplay,
            'deadline' => $dto->deadline ?? '',
            'orderDate' => $dto->orderDate,
            'status' => $dto->status,
            'paidAmount' => $dto->paidAmount,
            'source' => $dto->source,
        ];
    }

    /**
     * Представление для публичного API (если нужно).
     * Ограниченный набор полей.
     *
     * @param OrderDTO $dto
     * @return array
     */
    public function transformPublic(OrderDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'clientName' => $dto->clientName,
            'projectType' => $dto->projectType,
            'budget' => $dto->budget,
            'orderDate' => $dto->orderDate,
            'status' => $dto->status,
        ];
    }

    /**
     * Мини-представление (для ссылок/статистики).
     *
     * @param OrderDTO $dto
     * @return array
     */
    public function transformMinimal(OrderDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'clientName' => $dto->clientName,
            'status' => $dto->status,
            'budget' => $dto->budget,
        ];
    }
}
