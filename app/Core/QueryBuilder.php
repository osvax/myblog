<?php

namespace App\Core;

/**
 * QueryBuilder — лёгкая обёртка над PDO для построения SQL запросов.
 * Не является полноценным ORM, просто удобный fluent-интерфейс.
 * Убирает дублирование db_table() и ручных JOIN/WHERE по проекту.
 */
class QueryBuilder
{
    /** @var \PDO */
    private $pdo;

    /** @var string Таблица (уже с префиксом через db_table()) */
    private $table;

    /** @var array Выбираемые колонки */
    private $selects = ['*'];

    /** @var array Условия WHERE: [ [column, operator, value], ... ] */
    private $wheres = [];

    /** @var array Параметры для привязки */
    private $bindings = [];

    /** @var array JOIN предложения */
    private $joins = [];

    /** @var string|null ORDER BY */
    private $orderBy = null;

    /** @var int|null LIMIT */
    private $limit = null;

    /** @var int|null OFFSET */
    private $offset = null;

    /** @var string|null GROUP BY */
    private $groupBy = null;

    /**
     * Конструктор.
     *
     * @param \PDO $pdo      PDO соединение
     * @param string $table  Имя таблицы БЕЗ префикса (префикс добавится автоматически)
     */
    public function __construct(\PDO $pdo, string $table)
    {
        $this->pdo = $pdo;
        // db_table() добавляет префикс из конфига (va_)
        $this->table = db_table($table);
    }

    /**
     * Устанавливает колонки для SELECT.
     *
     * @param array $columns Массив колонок, например ['a.*', 'c.name AS category']
     * @return self
     */
    public function select(array $columns): self
    {
        $this->selects = $columns;
        return $this;
    }

    /**
     * Добавляет условие WHERE.
     *
     * @param string $column   Колонка
     * @param string $operator Оператор: =, !=, >, <, >=, <=, LIKE, NOT LIKE, IN, NOT IN, IS NULL, IS NOT NULL
     * @param mixed  $value    Значение (игнорируется для IS NULL / IS NOT NULL)
     * @return self
     */
    public function where(string $column, string $operator, $value = null): self
    {
        $this->wheres[] = [$column, $operator, $value];
        if ($value !== null) {
            $this->bindings[] = $value;
        }
        return $this;
    }

    /**
     * WHERE IN — удобный сокращённый метод.
     *
     * @param string $column Колонка
     * @param array  $values Массив значений
     * @return self
     */
    public function whereIn(string $column, array $values): self
    {
        if (empty($values)) {
            // WHERE 1=0 — всегда false, но валидный SQL
            $this->wheres[] = ['1', '=', 0];
            return $this;
        }
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->wheres[] = [$column, "IN ($placeholders)", $values];
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    /**
     * WHERE NOT IN.
     *
     * @param string $column Колонка
     * @param array  $values Массив значений
     * @return self
     */
    public function whereNotIn(string $column, array $values): self
    {
        if (empty($values)) {
            return $this; // всегда true, пропускаем
        }
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->wheres[] = [$column, "NOT IN ($placeholders)", $values];
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    /**
     * Добавляет JOIN.
     *
     * @param string $table     Таблица для JOIN (БЕЗ префикса)
     * @param string $first     Первая колонка (например: c.id)
     * @param string $operator  Оператор сравнения (=, !=, ...)
     * @param string $second    Вторая колонка (например: a.category_id)
     * @param string $type      Тип JOIN: INNER, LEFT, RIGHT
     * @return self
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = [
            'type' => strtoupper($type),
            'table' => db_table($table),
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
        ];
        return $this;
    }

    /**
     * LEFT JOIN — сокращение.
     *
     * @return self
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * Устанавливает ORDER BY.
     *
     * @param string $column Колонка
     * @param string $direction ASC или DESC
     * @return self
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = "$column " . strtoupper($direction);
        return $this;
    }

    /**
     * Устанавливает LIMIT.
     *
     * @param int $limit Количество строк
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Устанавливает OFFSET.
     *
     * @param int $offset Смещение
     * @return self
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Устанавливает GROUP BY.
     *
     * @param string $column Колонка для группировки (например: 'c.id')
     * @return self
     */
    public function groupBy(string $column): self
    {
        $this->groupBy = $column;
        return $this;
    }

    /**
     * Условное добавление условий (аналог Laravel when()).
     * Если $condition = true — выполняет $callback($this).
     *
     * @param bool   $condition Условие
     * @param callable $callback Функция, получающая $this
     * @return self
     */
    public function when(bool $condition, callable $callback): self
    {
        if ($condition) {
            $callback($this);
        }
        return $this;
    }

    /**
     * Строит и выполняет SELECT запрос, возвращает все строки.
     *
     * @return array Массив ассоциативных массивов
     */
    public function get(): array
    {
        $sql = $this->buildSelect();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Строит и выполняет SELECT, возвращает первую строку или null.
     *
     * @return array|null
     */
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Выполняет INSERT, возвращает lastInsertId или false.
     *
     * @param array $data Ассоциативный массив [колонка => значение]
     * @return string|false ID вставленной записи
     */
    public function insert(array $data)
    {
        if (empty($data)) {
            return false;
        }

        $columns = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES ($placeholders)";

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute(array_values($data));

        return $result ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Выполняет UPDATE для текущих WHERE условий.
     *
     * @param array $data Ассоциативный массив [колонка => значение]
     * @return int Количество затронутых строк
     */
    public function update(array $data): int
    {
        if (empty($data) || empty($this->wheres)) {
            // Без WHERE не обновляем — защита от случайного UPDATE всей таблицы
            return 0;
        }

        $setParts = [];
        foreach ($data as $column => $value) {
            $setParts[] = "$column = ?";
            $this->bindings[] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . $this->buildWhereClause();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);

        return $stmt->rowCount();
    }

    /**
     * Выполняет DELETE для текущих WHERE условий.
     *
     * @return int Количество удалённых строк
     */
    public function delete(): int
    {
        if (empty($this->wheres)) {
            // Без WHERE не удаляем — защита
            return 0;
        }

        $sql = "DELETE FROM {$this->table}" . $this->buildWhereClause();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);

        return $stmt->rowCount();
    }

    /**
     * Считает строки (COUNT(*)).
     *
     * @return int
     */
    public function count(): int
    {
        $originalSelects = $this->selects;
        $originalOrderBy = $this->orderBy;
        $originalLimit = $this->limit;
        $originalOffset = $this->offset;
        $originalGroupBy = $this->groupBy;

        $this->selects = ['COUNT(*) as cnt'];
        $this->orderBy = null;
        $this->limit = null;
        $this->offset = null;
        $this->groupBy = null; // GROUP BY не нужен для общего COUNT(*)

        $result = $this->first();
        $count = (int)($result['cnt'] ?? 0);

        // Восстанавливаем состояние
        $this->selects = $originalSelects;
        $this->orderBy = $originalOrderBy;
        $this->limit = $originalLimit;
        $this->offset = $originalOffset;
        $this->groupBy = $originalGroupBy;

        return $count;
    }

    /**
     * Проверяет существование записи.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Строит полный SELECT SQL.
     *
     * @return string
     */
    private function buildSelect(): string
    {
        $sql = "SELECT " . implode(', ', $this->selects) . " FROM {$this->table}";

        // JOINs
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }

        // WHERE
        $sql .= $this->buildWhereClause();

        // GROUP BY
        if ($this->groupBy) {
            $sql .= " GROUP BY {$this->groupBy}";
        }

        // ORDER BY
        if ($this->orderBy) {
            $sql .= " ORDER BY {$this->orderBy}";
        }

        // LIMIT / OFFSET
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    /**
     * Строит WHERE часть запроса.
     *
     * @return string
     */
    private function buildWhereClause(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $parts = [];
        foreach ($this->wheres as $where) {
            [$column, $operator, $value] = $where;

            if ($operator === 'IS NULL' || $operator === 'IS NOT NULL') {
                $parts[] = "$column $operator";
            } elseif (is_array($value)) {
                // IN / NOT IN — плейсхолдеры уже встроены в оператор
                $parts[] = "$column $operator";
            } else {
                $parts[] = "$column $operator ?";
            }
        }

        return ' WHERE ' . implode(' AND ', $parts);
    }

    /**
     * Возвращает сырой SQL и биндинги для отладки.
     *
     * @return array ['sql' => string, 'bindings' => array]
     */
    public function toSql(): array
    {
        return [
            'sql' => $this->buildSelect(),
            'bindings' => $this->bindings,
        ];
    }
}