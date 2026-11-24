<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\QueryBuilder;
use App\DTO\OrderDTO;
use PDO;

final class OrderRepository
{
    private $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? \App\Core\Database::connection();
    }

    /**
     * Базовый QueryBuilder для orders.
     */
    private function baseQuery(): QueryBuilder
    {
        return new QueryBuilder($this->db, 'orders');
    }

    /**
     * Список всех заказов (для обратной совместимости — возвращает массивы).
     *
     * @return array
     */
    public function orderList(): array
    {
        $rows = $this->baseQuery()
            ->select(['*'])
            ->orderBy('order_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

        return array_map([$this, 'orderRow'], $rows);
    }

    /**
     * Заказ по ID (для обратной совместимости — возвращает массив).
     *
     * @param int $id
     * @return array|null
     */
    public function orderById(int $id): ?array
    {
        $row = $this->baseQuery()
            ->select(['*'])
            ->where('id', '=', $id)
            ->first();

        return $row ? $this->orderRow($row) : null;
    }

    /**
     * Список заказов как массив OrderDTO (новый API).
     *
     * @return OrderDTO[]
     */
    public function ordersAsDto(): array
    {
        $rows = $this->baseQuery()
            ->select(['*'])
            ->orderBy('order_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

        return array_map([OrderDTO::class, 'fromDatabase'], $rows);
    }

    /**
     * Заказ по ID как OrderDTO (новый API).
     *
     * @param int $id
     * @return OrderDTO|null
     */
    public function orderDtoById(int $id): ?OrderDTO
    {
        $row = $this->baseQuery()
            ->select(['*'])
            ->where('id', '=', $id)
            ->first();

        return $row ? OrderDTO::fromDatabase($row) : null;
    }

    /**
     * Сохраняет заказ из OrderDTO (новый предпочтительный способ).
     *
     * @param OrderDTO $dto
     * @return array|null Сохранённый заказ в формате массива (для совместимости)
     */
    public function saveOrderFromDTO(OrderDTO $dto): ?array
    {
        // Валидируем DTO перед сохранением
        if (!$dto->validate()) {
            throw new \InvalidArgumentException('OrderDTO validation failed: ' . json_encode($dto->getErrors()));
        }

        $data = $dto->toArray();
        $table = db_table('orders');

        if ($dto->id === null) {
            // INSERT — новый заказ
            unset($data['id']);
            $columns = array_keys($data);
            $placeholders = implode(',', array_fill(0, count($columns), '?'));
            $colList = implode(',', $columns);

            $sql = "INSERT INTO $table ($colList) VALUES ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($data));

            $dto->id = (int)$this->db->lastInsertId();
        } else {
            // UPDATE — существующий заказ
            $updateParts = [];
            foreach ($data as $col => $val) {
                if ($col !== 'id') {
                    $updateParts[] = "$col = ?";
                }
            }
            $sql = "UPDATE $table SET " . implode(', ', $updateParts) . ' WHERE id = ?';

            $values = [];
            foreach ($data as $col => $val) {
                if ($col !== 'id') {
                    $values[] = $val;
                }
            }
            $values[] = $dto->id;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);

            // Проверяем, существует ли запись (rowCount=0 может быть если данные не изменились)
            if ($stmt->rowCount() === 0) {
                $exists = $this->db->prepare("SELECT 1 FROM $table WHERE id = ?");
                $exists->execute([$dto->id]);
                if ($exists->fetchColumn() === false) {
                    return null;
                }
            }
        }

        return $this->orderById($dto->id);
    }

    /**
     * Сохраняет заказ из массива (обратная совместимость).
     * Преобразует массив в OrderDTO и делегирует saveOrderFromDTO.
     *
     * @param int $id
     * @param array $data
     * @return array|null
     */
    public function saveOrder(int $id, array $data): ?array
    {
        $dto = OrderDTO::fromRequest($data, $id);
        return $this->saveOrderFromDTO($dto);
    }

    /**
     * Создаёт новый заказ из массива (обратная совместимость для публичной формы).
     *
     * @param array $data
     * @return array|null
     */
    public function createOrder(array $data): ?array
    {
        $dto = OrderDTO::fromRequest($data);
        return $this->saveOrderFromDTO($dto);
    }

    /**
     * Удаляет заказ.
     *
     * @param int $id
     * @return bool
     */
    public function deleteOrder(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ' . db_table('orders') . ' WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Экспорт заказов в диапазоне дат.
     *
     * @param string $from Y-m-d
     * @param string $to Y-m-d
     * @return array
     */
    public function exportOrders(string $from, string $to): array
    {
        $rows = $this->baseQuery()
            ->select(['*'])
            ->where('order_date', '>=', $from)
            ->where('order_date', '<=', $to)
            ->orderBy('order_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

        return array_map([$this, 'orderRow'], $rows);
    }

    /**
     * Преобразует строку БД в массив для совместимости со старым фронтендом.
     *
     * @param array $row
     * @return array
     */
    private function orderRow(array $row): array
    {
        $details = json_decode($row['details_json'] ?? '{}', true);
        if (!is_array($details)) {
            $details = [];
        }
        return array_merge($details, [
            'id' => (int) $row['id'],
            'client' => $row['client_name'],
            'clientName' => $row['client_name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'type' => $row['project_type'],
            'projectType' => $row['project_type'],
            'budget' => (float) $row['budget'],
            'deadline' => $row['deadline'] ?? '',
            'orderDate' => $row['order_date'],
            'createdAt' => $row['order_date'],
            'status' => $row['status'],
        ]);
    }
}
