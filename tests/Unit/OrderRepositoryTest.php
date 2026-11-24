<?php

namespace Tests\Unit;

use App\Models\OrderRepository;
use App\DTO\OrderDTO;
use PDO;
use PHPUnit\Framework\TestCase;

class OrderRepositoryTest extends TestCase
{
    private PDO $pdo;
    private OrderRepository $repo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $this->pdo->exec("PRAGMA foreign_keys = ON");

        // Create tables
        $this->pdo->exec("
            CREATE TABLE va_orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                client_name TEXT NOT NULL,
                email TEXT NOT NULL,
                phone TEXT NOT NULL,
                project_type TEXT NOT NULL,
                budget REAL DEFAULT 0,
                deadline TEXT,
                order_date TEXT NOT NULL,
                status TEXT DEFAULT 'Новый',
                details_json TEXT,
                completed_at TEXT,
                paid_amount REAL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->repo = new OrderRepository($this->pdo);
    }

    public function testSaveOrderFromDTO(): void
    {
        $dto = new OrderDTO();
        $dto->clientName = 'Test Client';
        $dto->email = 'test@example.com';
        $dto->phone = '+7 999 123 45 67';
        $dto->projectType = 'Сайт-визитка';
        $dto->budget = 50000;
        $dto->deadline = '2025-12-31';
        $dto->orderDate = '2025-11-01';
        $dto->status = 'Новый';
        $dto->calculatorDetails = [
            'type' => 'Сайт-визитка',
            'features' => [['name' => 'Feature 1', 'price' => 25000, 'days' => 10]],
            'price' => 50000,
            'currency' => '₽',
            'days' => 10,
        ];

        $result = $this->repo->saveOrderFromDTO($dto);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('Test Client', $result['client_name']);
        $this->assertEquals('test@example.com', $result['email']);

        // Проверяем что details_json сохранён корректно
        $details = json_decode($result['details_json'], true);
        $this->assertIsArray($details);
        $this->assertArrayHasKey('calculatorDetails', $details);
        $this->assertIsArray($details['calculatorDetails']);
    }

    public function testOrdersAsDto(): void
    {
        // Insert test orders
        $this->pdo->exec("
            INSERT INTO va_orders (client_name, email, phone, project_type, budget, deadline, order_date, status, details_json)
            VALUES
            ('Client 1', 'c1@test.com', '+7 999 000 00 01', 'Type 1', 10000, '2025-12-01', '2025-11-01', 'Новый', '{}'),
            ('Client 2', 'c2@test.com', '+7 999 000 00 02', 'Type 2', 20000, '2025-12-02', '2025-11-02', 'В работе', '{}'),
            ('Client 3', 'c3@test.com', '+7 999 000 00 03', 'Type 3', 30000, '2025-12-03', '2025-11-03', 'Завершён', '{}')
        ");

        $orders = $this->repo->ordersAsDto();

        $this->assertCount(3, $orders);
        $this->assertInstanceOf(OrderDTO::class, $orders[0]);
        $this->assertEquals('Client 1', $orders[0]->clientName);
        $this->assertEquals('Client 2', $orders[1]->clientName);
        $this->assertEquals('Client 3', $orders[2]->clientName);
    }

    public function testOrderDtoById(): void
    {
        $this->pdo->exec("
            INSERT INTO va_orders (client_name, email, phone, project_type, budget, deadline, order_date, status, details_json)
            VALUES ('Test Client', 'test@test.com', '+7 999 000 00 00', 'Test', 10000, '2025-12-01', '2025-11-01', 'Новый', '{}')
        ");
        $id = $this->pdo->lastInsertId();

        $dto = $this->repo->orderDtoById((int)$id);

        $this->assertInstanceOf(OrderDTO::class, $dto);
        $this->assertEquals((int)$id, $dto->id);
        $this->assertEquals('Test Client', $dto->clientName);
    }

    public function testOrderDtoByIdReturnsNullForMissing(): void
    {
        $dto = $this->repo->orderDtoById(999);
        $this->assertNull($dto);
    }

    public function testSaveOrderFromDTOUpdatesExisting(): void
    {
        // Create order
        $dto = new OrderDTO();
        $dto->clientName = 'Original';
        $dto->email = 'orig@test.com';
        $dto->phone = '+7 999 000 00 00';
        $dto->projectType = 'Original';
        $dto->budget = 10000;
        $dto->orderDate = '2025-11-01';

        $result = $this->repo->saveOrderFromDTO($dto);
        $id = $result['id'];

        // Update order
        $dto->id = $id;
        $dto->clientName = 'Updated';
        $dto->status = 'В работе';

        $result = $this->repo->saveOrderFromDTO($dto);

        $this->assertEquals($id, $result['id']);
        $this->assertEquals('Updated', $result['client_name']);
        $this->assertEquals('В работе', $result['status']);
    }
}