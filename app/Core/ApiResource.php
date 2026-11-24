<?php

declare(strict_types=1);

namespace App\Core;

/**
 * ApiResource — единый формат ответа API.
 *
 * Обеспечивает консистентную структуру JSON-ответов для всех эндпоинтов:
 * - success: bool — успех/неуспех операции
 * - data: mixed — полезная нагрузка (объект, массив или null)
 * - meta: array|null — метаданные (пагинация, счётчики, интервалы обновления)
 * - errors: array|null — ошибки валидации/бизнеса (только при success=false)
 * - message: string|null — человекочитаемое сообщение (для уведомлений)
 */
final class ApiResource
{
    /**
     * Создаёт успешный ответ с данными.
     *
     * @param mixed $data Данные для ответа (DTO, массив DTO, примитив)
     * @param array|null $meta Метаданные (пагинация, etc.)
     * @param string|null $message Сообщение для UI
     * @return array
     */
    public static function success(mixed $data = null, ?array $meta = null, ?string $message = null): array
    {
        $response = [
            'success' => true,
            'data' => $data,
        ];

        if ($meta !== null) {
            $response['meta'] = $meta;
        }
        if ($message !== null) {
            $response['message'] = $message;
        }

        return $response;
    }

    /**
     * Создаёт ответ об ошибке.
     *
     * @param string $error Основное сообщение об ошибке
     * @param array|null $errors Детальные ошибки валидации ['field' => 'message']
     * @param int $code HTTP код ответа (для логирования, не устанавливает заголовок)
     * @param array|null $meta Метаданные
     * @return array
     */
    public static function error(string $error, ?array $errors = null, int $code = 400, ?array $meta = null): array
    {
        $response = [
            'success' => false,
            'error' => $error,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        if ($meta !== null) {
            $response['meta'] = $meta;
        }
        if ($code !== 400) {
            $response['meta']['http_code'] = $code;
        }

        return $response;
    }

    /**
     * Создаёт ответ для пагинированных коллекций.
     *
     * @param array $items Массив преобразованных элементов (уже через трансформер)
     * @param int $total Общее количество записей
     * @param int $page Текущая страница (1-based)
     * @param int $perPage Элементов на странице
     * @param array|null $extraMeta Дополнительные метаданные
     * @return array
     */
    public static function collection(array $items, int $total, int $page, int $perPage, ?array $extraMeta = null): array
    {
        $lastPage = (int)ceil($total / max(1, $perPage));

        $meta = [
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
                'from' => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
                'to' => min($page * $perPage, $total),
                'has_more' => $page < $lastPage,
            ],
        ];

        if ($extraMeta !== null) {
            $meta = array_merge($meta, $extraMeta);
        }

        return self::success($items, $meta);
    }

    /**
     * Создаёт пустой успешный ответ (например, для DELETE).
     *
     * @param string|null $message
     * @return array
     */
    public static function empty(?string $message = null): array
    {
        return self::success(null, null, $message);
    }

    /**
     * Создаёт ответ "создано" (201).
     *
     * @param mixed $data Созданный ресурс
     * @param string|null $message
     * @return array
     */
    public static function created(mixed $data, ?string $message = null): array
    {
        $response = self::success($data, null, $message);
        $response['meta']['http_code'] = 201;
        return $response;
    }

    /**
     * Создаёт ответ "нет контента" (204).
     *
     * @return array
     */
    public static function noContent(): array
    {
        return ['success' => true, 'data' => null];
    }
}
