<?php

namespace App\DTO;

/**
 * OrderDTO — объект переноса данных для заказов (Orders).
 *
 * === ПОТОК ДАННЫХ (Data Flow) ===
 *
 * 1. ВХОД (Request → DTO):
 *    - POST /api/admin/orders (создание из калькулятора/формы)
 *    - PUT/PATCH /api/admin/orders/{id} (обновление статуса/данных администратором)
 *    - Тело запроса: {
 *        "name": "Иван", "email": "ivan@test.ru", "phone": "+7...",
 *        "type": "Сайт-визитка", "budget": 50000, "budgetDisplay": "50 000 ₽",
 *        "deadlineFrom": "2025-02-01", "deadlineTo": "2025-03-01",
 *        "message": "Описание задачи...",
 *        "contactPerson": "Иван", "calculatorDetails": {...}
 *      }
 *    - Контроллер: OrderDTO::fromRequest($request->json(), $id?)
 *
 * 2. ВАЛИДАЦИЯ (DTO → Errors):
 *    - $dto->validate() проверяет правила
 *    - Ошибки → $dto->getErrors()
 *    - Контроллер возвращает 400 + errors если невалидно
 *
 * 3. СОХРАНЕНИЕ (DTO → Database):
 *    - Repository: saveOrderFromDTO(OrderDTO $dto)
 *    - DTO::toArray() → массив для БД (details_json кодируется в JSON)
 *    - INSERT или UPDATE в va_orders
 *
 * 4. ЧТЕНИЕ (Database → DTO):
 *    - Repository: orderList(), orderById()
 *    - SELECT * FROM va_orders ORDER BY order_date DESC
 *    - OrderDTO::fromDatabase($row) → DTO с распарсенным details_json
 *
 * 5. ВЫХОД (DTO → JSON Response):
 *    - DTO::toApiArray() → массив для json()
 *    - Контроллер: return $this->json(['success' => true, 'order' => $dto->toApiArray()])
 *
 * === СТРУКТУРА ТАБЛИЦЫ va_orders ===
 * - id (INT, PK, AI)
 * - client_name (VARCHAR 255) — имя клиента
 * - email (VARCHAR 255) — email клиента
 * - phone (VARCHAR 50) — телефон (опционально)
 * - project_type (VARCHAR 100) — тип проекта (опционально)
 * - budget (DECIMAL 12,2) — бюджет
 * - deadline (DATE, NULL) — дедлайн (опционально)
 * - order_date (DATE) — дата заказа
 * - status (VARCHAR 50) — статус: 'Новый', 'В работе', 'Завершён', 'Отменён'
 * - details_json (JSON) — все остальные поля формы (контактное лицо, дедлайн из/до, сообщение, калькулятор)
 * - created_at, updated_at (TIMESTAMP) — автоматически
 *
 * === ПОЛЯ details_json (вложенная структура) ===
 * - source: источник (например, 'Форма заказа на сайте')
 * - contactPerson: контактное лицо
 * - projectTitle / projectType: название/тип проекта
 * - orderDate: дата заказа
 * - deadlineFrom / deadlineTo: диапазон дедлайнов
 * - status: статус
 * - technicalBrief: техническое задание / сообщение
 * - budget / budgetDisplay: бюджет (число и отображение)
 * - paidAmount: оплаченная сумма
 * - paymentHistory: история платежей
 * - notes: заметки менеджера
 * - calculatorDetails: данные калькулятора
 */
class OrderDTO
{
    // === Основные поля (колонки va_orders) ===

    /** @var int|null ID заказа (NULL для нового) */
    public ?int $id = null;

    /** @var string Имя клиента (обязательно, 2..160) */
    public string $clientName = '';

    /** @var string Email клиента (обязательно, валидный email) */
    public string $email = '';

    /** @var string Телефон (опционально) */
    public string $phone = '';

    /** @var string Тип проекта (опционально) */
    public string $projectType = '';

    /** @var float Бюджет (обязательно, >= 0) */
    public float $budget = 0.0;

    /** @var string|null Дедлайн в формате Y-m-d (опционально) */
    public ?string $deadline = null;

    /** @var string Дата заказа в формате Y-m-d (обязательно) */
    public string $orderDate = '';

    /** @var string Статус (обязательно: 'Новый'|'В работе'|'Завершён'|'Отменён') */
    public string $status = 'Новый';

    /** @var string|null Дата завершения (опционально) */
    public ?string $completedAt = null;

    /** @var float Оплаченная сумма */
    public float $paidAmount = 0.0;

    // === Вложенные данные из details_json ===

    /** @var string Источник заказа */
    public string $source = 'Форма заказа на сайте';

    /** @var string Контактное лицо */
    public string $contactPerson = '';

    /** @var string Название проекта (для отображения) */
    public string $projectTitle = '';

    /** @var string Дедлайн "от" (Y-m-d) */
    public string $deadlineFrom = '';

    /** @var string История платежей */
    public string $paymentHistory = '';

    /** @var string Заметки менеджера */
    public string $notes = '';

    /** @var array Данные калькулятора */
    public array $calculatorDetails = [];

    /** @var string Бюджет для отображения (форматированная строка) */
    public string $budgetDisplay = '';

    /** @var string Техническое задание / сообщение клиента */
    public string $technicalBrief = '';

    // === Ошибки валидации ===
    private array $errors = [];

    // === Константы ===
    public const STATUS_VALUES = ['Новый', 'В работе', 'Завершён', 'Отменён'];
    public const CLIENT_NAME_MAX = 160;
    public const CLIENT_NAME_MIN = 2;
    public const PROJECT_TYPE_MAX = 100;
    public const SOURCE_MAX = 100;

    /**
     * Создаёт DTO из массива запроса (JSON / POST).
     *
     * ИСПОЛЬЗОВАНИЕ В КОНТРОЛЛЕРЕ:
     * ```php
     * // Создание заказа (POST)
     * $jsonData = $request->json(); // Приходит из калькулятора/формы
     * $dto = OrderDTO::fromRequest($jsonData);
     *
     * // Обновление заказа (PUT/PATCH)
     * $jsonData = $request->json();
     * $dto = OrderDTO::fromRequest($jsonData, $params['id']);
     * ```
     *
     * @param array $data Данные из $request->json()
     *                Ожидаемые ключи:
     *                - name, email, phone, type, budget, budgetDisplay
     *                - deadlineFrom, deadlineTo, message, contactPerson
     *                - calculatorDetails (array)
     *                - clientName, projectType, deadline, completedAt, paidAmount (для админки)
     * @param int|null $id ID из URL параметра (для PUT/PATCH)
     * @return self Готовый DTO (нужно вызвать validate())
     */
    public static function fromRequest(array $data, ?int $id = null): self
    {
        $dto = new self();
        $dto->id = $id ?? (isset($data['id']) && is_numeric($data['id']) ? (int)$data['id'] : null);

        // --- Поля из публичной формы / калькулятора ---
        $dto->clientName = trim((string)($data['name'] ?? $data['clientName'] ?? ''));
        $dto->email = trim((string)($data['email'] ?? ''));
        $dto->phone = trim((string)($data['phone'] ?? ''));
        $dto->projectType = trim((string)($data['type'] ?? $data['projectType'] ?? ''));
        $dto->projectTitle = trim((string)($data['projectTitle'] ?? $data['type'] ?? ''));
        $dto->budget = isset($data['budget']) && is_numeric($data['budget']) ? (float)$data['budget'] : 0.0;
        $dto->budgetDisplay = trim((string)($data['budgetDisplay'] ?? ''));
        $dto->deadlineFrom = trim((string)($data['deadlineFrom'] ?? ''));
        $dto->deadline = trim((string)($data['deadlineTo'] ?? $data['deadline'] ?? ''));
        $dto->technicalBrief = trim((string)($data['message'] ?? $data['technicalBrief'] ?? ''));
        $dto->contactPerson = trim((string)($data['contactPerson'] ?? $data['name'] ?? ''));
        $dto->calculatorDetails = is_array($data['calculatorDetails'] ?? null) ? $data['calculatorDetails'] : [];
        $dto->source = trim((string)($data['source'] ?? 'Форма заказа на сайте'));

        // --- Поля из админки (PUT/PATCH) ---
        if (isset($data['orderDate'])) {
            $dto->orderDate = trim((string)$data['orderDate']);
        } elseif ($dto->orderDate === '') {
            $dto->orderDate = date('Y-m-d');
        }

        if (isset($data['status'])) {
            $dto->status = trim((string)$data['status']);
        }

        if (isset($data['completedAt']) && $data['completedAt'] !== '' && $data['completedAt'] !== null) {
            $dto->completedAt = trim((string)$data['completedAt']);
        }

        if (isset($data['paidAmount']) && is_numeric($data['paidAmount'])) {
            $dto->paidAmount = (float)$data['paidAmount'];
        }

        if (isset($data['notes'])) {
            $dto->notes = trim((string)$data['notes']);
        }

        if (isset($data['paymentHistory'])) {
            $dto->paymentHistory = trim((string)$data['paymentHistory']);
        }

        return $dto;
    }

    /**
     * Создаёт DTO из строки БД (результат SELECT * FROM va_orders).
     *
     * ИСПОЛЬЗОВАНИЕ В РЕПОЗИТОРИИ:
     * ```php
     * $row = $this->db->query('SELECT * FROM va_orders WHERE id = ?', [$id])->fetch();
     * $dto = OrderDTO::fromDatabase($row);
     * // $row содержит: id, client_name, email, phone, project_type, budget,
     * // deadline, order_date, status, details_json, completed_at, paid_amount
     * ```
     *
     * @param array $row Ассоциативный массив из БД
     * @return self
     */
    public static function fromDatabase(array $row): self
    {
        $dto = new self();
        $dto->id = isset($row['id']) ? (int)$row['id'] : null;
        $dto->clientName = $row['client_name'] ?? '';
        $dto->email = $row['email'] ?? '';
        $dto->phone = $row['phone'] ?? '';
        $dto->projectType = $row['project_type'] ?? '';
        $dto->budget = isset($row['budget']) ? (float)$row['budget'] : 0.0;
        $dto->deadline = $row['deadline'] ?? null;
        $dto->orderDate = $row['order_date'] ?? '';
        $dto->status = $row['status'] ?? 'Новый';
        $dto->completedAt = $row['completed_at'] ?? null;
        $dto->paidAmount = isset($row['paid_amount']) ? (float)$row['paid_amount'] : 0.0;

        // Парсим details_json
        $details = [];
        if (!empty($row['details_json'])) {
            $decoded = json_decode($row['details_json'], true);
            if (is_array($decoded)) {
                $details = $decoded;
            }
        }

        // Хелпер: безопасно декодирует JSON-строку в массив, если это строка
        $decodeIfJsonString = function ($value) {
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
            return is_array($value) ? $value : [];
        };

        // Заполняем вложенные поля из details_json
        $dto->source = $details['source'] ?? 'Форма заказа на сайте';
        $dto->contactPerson = $details['contactPerson'] ?? $details['clientName'] ?? $dto->clientName;
        $dto->projectTitle = $details['projectTitle'] ?? $details['projectType'] ?? $dto->projectType;
        $dto->orderDate = $details['orderDate'] ?? $dto->orderDate;
        $dto->deadlineFrom = $details['deadlineFrom'] ?? '';
        $dto->paymentHistory = $details['paymentHistory'] ?? '';
        $dto->notes = $details['notes'] ?? '';
        $dto->calculatorDetails = $decodeIfJsonString($details['calculatorDetails'] ?? []);
        $dto->budgetDisplay = $details['budgetDisplay'] ?? '';
        $dto->technicalBrief = $details['technicalBrief'] ?? '';

        return $dto;
    }

    /**
     * Валидирует DTO, заполняет $this->errors.
     *
     * ПРАВИЛА ВАЛИДАЦИИ:
     * - clientName: обязателен, 2..160 символов
     * - email: обязателен, валидный email
     * - phone: опционально, строка
     * - projectType: опционально, макс 100 символов
     * - budget: обязателен, число >= 0
     * - deadline: опционально, формат Y-m-d
     * - orderDate: обязателен, формат Y-m-d
     * - status: обязателен, одно из STATUS_VALUES
     * - completedAt: опционально, формат Y-m-d, >= orderDate
     * - paidAmount: число >= 0
     *
     * @return bool true если валидно
     */
    public function validate(): bool
    {
        $this->errors = [];

        // --- clientName ---
        if ($this->clientName === '') {
            $this->errors['clientName'] = 'Имя клиента обязательно';
        } elseif (mb_strlen($this->clientName) < self::CLIENT_NAME_MIN) {
            $this->errors['clientName'] = 'Имя клиента не короче ' . self::CLIENT_NAME_MIN . ' символов';
        } elseif (mb_strlen($this->clientName) > self::CLIENT_NAME_MAX) {
            $this->errors['clientName'] = 'Имя клиента не длиннее ' . self::CLIENT_NAME_MAX . ' символов';
        }

        // --- email ---
        if ($this->email === '') {
            $this->errors['email'] = 'Email обязателен';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Неверный формат email';
        }

        // --- phone ---
        // Опционально, просто строка

        // --- projectType ---
        if ($this->projectType !== '' && mb_strlen($this->projectType) > self::PROJECT_TYPE_MAX) {
            $this->errors['projectType'] = 'Тип проекта не длиннее ' . self::PROJECT_TYPE_MAX . ' символов';
        }

        // --- budget ---
        if ($this->budget < 0) {
            $this->errors['budget'] = 'Бюджет не может быть отрицательным';
        }

        // --- deadline ---
        if ($this->deadline !== null && $this->deadline !== '' && !$this->validDate($this->deadline)) {
            $this->errors['deadline'] = 'Дедлайн: неверный формат даты (ожидается Y-m-d)';
        }

        // --- orderDate ---
        if ($this->orderDate === '') {
            $this->errors['orderDate'] = 'Дата заказа обязательна';
        } elseif (!$this->validDate($this->orderDate)) {
            $this->errors['orderDate'] = 'Дата заказа: неверный формат (ожидается Y-m-d)';
        }

        // --- status ---
        if ($this->status === '') {
            $this->errors['status'] = 'Статус обязателен';
        } elseif (!in_array($this->status, self::STATUS_VALUES, true)) {
            $this->errors['status'] = 'Недопустимый статус. Допустимые: ' . implode(', ', self::STATUS_VALUES);
        }

        // --- completedAt ---
        if ($this->completedAt !== null && $this->completedAt !== '' && !$this->validDate($this->completedAt)) {
            $this->errors['completedAt'] = 'Дата завершения: неверный формат (ожидается Y-m-d)';
        }
        if ($this->completedAt !== null && $this->completedAt !== '' && $this->orderDate !== '' && $this->validDate($this->orderDate)) {
            if ($this->completedAt < $this->orderDate) {
                $this->errors['completedAt'] = 'Дата завершения не может быть раньше даты заказа';
            }
        }

        // --- paidAmount ---
        if ($this->paidAmount < 0) {
            $this->errors['paidAmount'] = 'Оплаченная сумма не может быть отрицательной';
        }

        // --- deadlineFrom (из details) ---
        if ($this->deadlineFrom !== '' && !$this->validDate($this->deadlineFrom)) {
            $this->errors['deadlineFrom'] = 'Дедлайн "от": неверный формат даты';
        }

        // --- source ---
        if (mb_strlen($this->source) > self::SOURCE_MAX) {
            $this->errors['source'] = 'Источник не длиннее ' . self::SOURCE_MAX . ' символов';
        }

        return empty($this->errors);
    }

    /**
     * Проверка формата даты Y-m-d.
     */
    private function validDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d !== false && $d->format('Y-m-d') === $date;
    }

    /**
     * Возвращает ошибки валидации.
     *
     * @return array ['field' => 'message', ...]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Проверяет, прошла ли валидация.
     */
    public function isValid(): bool
    {
        if (empty($this->errors)) {
            $this->validate();
        }
        return empty($this->errors);
    }

    /**
     * Преобразует в массив для сохранения в БД (таблица va_orders).
     *
     * ВСЕ вложенные поля упаковываются в details_json.
     *
     * @return array ['column' => value, ...]
     */
    public function toArray(): array
    {
        $details = [
            'source' => $this->source,
            'contactPerson' => $this->contactPerson,
            'projectTitle' => $this->projectTitle,
            'projectType' => $this->projectType,
            'orderDate' => $this->orderDate,
            'deadlineFrom' => $this->deadlineFrom,
            'deadlineTo' => $this->deadline,
            'status' => $this->status,
            'technicalBrief' => $this->technicalBrief,
            'budget' => $this->budget,
            'budgetDisplay' => $this->budgetDisplay,
            'paidAmount' => $this->paidAmount,
            'paymentHistory' => $this->paymentHistory,
            'notes' => $this->notes,
            'calculatorDetails' => $this->calculatorDetails,
        ];

        $data = [
            'id' => $this->id,
            'client_name' => $this->clientName,
            'email' => $this->email,
            'phone' => $this->phone,
            'project_type' => $this->projectType,
            'budget' => $this->budget,
            'deadline' => $this->deadline === '' ? null : $this->deadline,
            'order_date' => $this->orderDate,
            'status' => $this->status,
            'details_json' => json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'completed_at' => $this->completedAt === '' ? null : $this->completedAt,
            'paid_amount' => $this->paidAmount,
        ];

        return $data;
    }

    /**
     * Преобразует в массив для JSON API ответа.
     *
     * СТРУКТУРА ОТВЕТА (совместима с текущим фронтендом):
     * [
     *   'id' => 1,
     *   'client' => 'Иван',
     *   'clientName' => 'Иван',
     *   'email' => 'ivan@test.ru',
     *   'phone' => '+7...',
     *   'type' => 'Сайт-визитка',
     *   'projectType' => 'Сайт-визитка',
     *   'budget' => 50000.00,
     *   'deadline' => '2025-03-01',
     *   'orderDate' => '2025-01-15',
     *   'createdAt' => '2025-01-15',
     *   'status' => 'Новый',
     *   'completedAt' => null,
     *   'paidAmount' => 0,
     *   // вложенные из details_json:
     *   'source' => 'Форма заказа на сайте',
     *   'contactPerson' => 'Иван',
     *   'projectTitle' => 'Сайт-визитка',
     *   'deadlineFrom' => '2025-02-01',
     *   'budgetDisplay' => '50 000 ₽',
     *   'technicalBrief' => 'Описание задачи...',
     *   'calculatorDetails' => [...],
     *   'notes' => '',
     *   'paymentHistory' => '',
     * ]
     *
     * @return array
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'client' => $this->clientName,
            'clientName' => $this->clientName,
            'email' => $this->email,
            'phone' => $this->phone,
            'type' => $this->projectType,
            'projectType' => $this->projectType,
            'budget' => $this->budget,
            'deadline' => $this->deadline ?? '',
            'orderDate' => $this->orderDate,
            'createdAt' => $this->orderDate,
            'status' => $this->status,
            'completedAt' => $this->completedAt ?? '',
            'paidAmount' => $this->paidAmount,
            // вложенные
            'source' => $this->source,
            'contactPerson' => $this->contactPerson,
            'projectTitle' => $this->projectTitle,
            'projectType' => $this->projectType,
            'orderDate' => $this->orderDate,
            'deadlineFrom' => $this->deadlineFrom,
            'deadlineTo' => $this->deadline ?? '',
            'budgetDisplay' => $this->budgetDisplay,
            'technicalBrief' => $this->technicalBrief,
            'calculatorDetails' => $this->calculatorDetails,
            'notes' => $this->notes,
            'paymentHistory' => $this->paymentHistory,
        ];
    }
}