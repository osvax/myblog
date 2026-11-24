<?php

namespace Tests\Unit;

use App\DTO\OrderDTO;
use PHPUnit\Framework\TestCase;

class OrderDTOTest extends TestCase
{
    public function testFromDatabaseParsesCalculatorDetails(): void
    {
        // Simulate DB row with double-encoded calculatorDetails (real production data)
        $row = [
            'id' => 1,
            'client_name' => 'Test Client',
            'email' => 'test@example.com',
            'phone' => '+7 999 123 45 67',
            'project_type' => 'Сайт-визитка',
            'budget' => '50000',
            'deadline' => '2025-12-31',
            'order_date' => '2025-11-01',
            'status' => 'Новый',
            'completed_at' => null,
            'paid_amount' => '0',
            'details_json' => json_encode([
                'clientName' => 'Test Client',
                'email' => 'test@example.com',
                'phone' => '+7 999 123 45 67',
                'source' => 'Форма заказа на сайте',
                'contactPerson' => 'Test Client',
                'projectTitle' => 'Сайт-визитка',
                'projectType' => 'Сайт-визитка',
                'orderDate' => '2025-11-01',
                'deadline' => '2025-12-31',
                'status' => 'Новый',
                'technicalBrief' => 'Тестовое ТЗ',
                'budget' => 50000,
                'budgetDisplay' => '50 000 ₽',
                'deadlineFrom' => '2025-11-01',
                'paidAmount' => 0,
                'paymentHistory' => '',
                'notes' => '',
                // calculatorDetails хранится как JSON-строка (двойное кодирование)
                'calculatorDetails' => json_encode([
                    'type' => 'Сайт-визитка',
                    'features' => [
                        ['name' => 'До 10 страниц', 'price' => 25000, 'days' => 10],
                        ['name' => 'Галерея работ', 'price' => 8000, 'days' => 5],
                    ],
                    'price' => 33000,
                    'currency' => '₽',
                    'days' => 15,
                ]),
            ]),
        ];

        $dto = OrderDTO::fromDatabase($row);

        $this->assertEquals(1, $dto->id);
        $this->assertEquals('Test Client', $dto->clientName);
        $this->assertEquals('test@example.com', $dto->email);
        $this->assertEquals('+7 999 123 45 67', $dto->phone);
        $this->assertEquals('Сайт-визитка', $dto->projectType);
        $this->assertEquals(50000.0, $dto->budget);
        $this->assertEquals('Новый', $dto->status);

        // calculatorDetails должен быть массивом, а не строкой
        $this->assertIsArray($dto->calculatorDetails);
        $this->assertEquals('Сайт-визитка', $dto->calculatorDetails['type']);
        $this->assertCount(2, $dto->calculatorDetails['features']);
        $this->assertEquals('До 10 страниц', $dto->calculatorDetails['features'][0]['name']);
    }

    public function testFromDatabaseHandlesMissingCalculatorDetails(): void
    {
        $row = [
            'id' => 2,
            'client_name' => 'Client 2',
            'email' => 'client2@example.com',
            'phone' => '+7 999 000 00 00',
            'project_type' => 'Интернет-магазин',
            'budget' => '100000',
            'deadline' => '2026-01-31',
            'order_date' => '2025-11-15',
            'status' => 'В работе',
            'completed_at' => null,
            'paid_amount' => '50000',
            'details_json' => json_encode([
                'clientName' => 'Client 2',
                'email' => 'client2@example.com',
                // нет calculatorDetails
            ]),
        ];

        $dto = OrderDTO::fromDatabase($row);

        $this->assertIsArray($dto->calculatorDetails);
        $this->assertEmpty($dto->calculatorDetails);
    }

    public function testFromRequestValidatesRequiredFields(): void
    {
        $data = [
            'name' => '',
            'email' => 'invalid-email',
            'phone' => '123',
            'type' => '',
            'budget' => -100,
            'deadlineFrom' => 'invalid-date',
        ];

        $dto = OrderDTO::fromRequest($data);
        $errors = $dto->getErrors();

        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('clientName', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('phone', $errors);
        $this->assertArrayHasKey('projectType', $errors);
        $this->assertArrayHasKey('budget', $errors);
    }

    public function testFromRequestAcceptsValidData(): void
    {
        $data = [
            'name' => 'Иван Иванов',
            'email' => 'ivan@example.com',
            'phone' => '+7 999 123 45 67',
            'type' => 'Сайт-визитка',
            'budget' => 50000,
            'budgetDisplay' => '50 000 ₽',
            'deadlineFrom' => '2025-12-01',
            'deadlineTo' => '2025-12-31',
            'message' => 'Нужен сайт',
            'contactPerson' => 'Иван',
            'calculatorDetails' => [
                'type' => 'Сайт-визитка',
                'features' => [],
                'price' => 50000,
                'currency' => '₽',
                'days' => 10,
            ],
        ];

        $dto = OrderDTO::fromRequest($data);
        $errors = $dto->getErrors();

        $this->assertEmpty($errors);
        $this->assertEquals('Иван Иванов', $dto->clientName);
        $this->assertEquals('ivan@example.com', $dto->email);
        $this->assertEquals('+7 999 123 45 67', $dto->phone);
        $this->assertEquals('Сайт-визитка', $dto->projectType);
        $this->assertEquals(50000.0, $dto->budget);
    }

    public function testToArrayEncodesCalculatorDetailsAsJson(): void
    {
        $dto = new OrderDTO();
        $dto->id = 1;
        $dto->clientName = 'Test';
        $dto->email = 'test@example.com';
        $dto->phone = '+7 999 123 45 67';
        $dto->projectType = 'Сайт-визитка';
        $dto->budget = 50000;
        $dto->calculatorDetails = [
            'type' => 'Сайт-визитка',
            'features' => [],
            'price' => 50000,
            'currency' => '₽',
            'days' => 10,
        ];

        $array = $dto->toArray();

        $this->assertArrayHasKey('details_json', $array);
        $decoded = json_decode($array['details_json'], true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('calculatorDetails', $decoded);
        $this->assertEquals('Сайт-визитка', $decoded['calculatorDetails']['type']);
    }

    public function testValidateRejectsInvalidStatus(): void
    {
        $dto = new OrderDTO();
        $dto->clientName = 'Test';
        $dto->email = 'test@example.com';
        $dto->phone = '+7 999 123 45 67';
        $dto->projectType = 'Test';
        $dto->budget = 1000;
        $dto->status = 'InvalidStatus';

        $isValid = $dto->validate();
        $errors = $dto->getErrors();

        $this->assertFalse($isValid);
        $this->assertArrayHasKey('status', $errors);
    }

    public function testValidateAcceptsValidStatus(): void
    {
        $dto = new OrderDTO();
        $dto->clientName = 'Test';
        $dto->email = 'test@example.com';
        $dto->phone = '+7 999 123 45 67';
        $dto->projectType = 'Test';
        $dto->budget = 1000;
        $dto->status = 'Новый';

        $isValid = $dto->validate();
        $errors = $dto->getErrors();

        $this->assertTrue($isValid);
        $this->assertArrayNotHasKey('status', $errors);
    }
}