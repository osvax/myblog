<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\ApiResource;
use App\Models\SiteSettings;

/**
 * API калькулятора заказа: конфигурация типов и функций хранится
 * в базе (site_settings, ключ calculatorConfig) и отдаётся
 * публичной странице заказа.
 */
class CalculatorController extends Controller
{
    private $settings;

    public function __construct(SiteSettings $settings)
    {
        $this->settings = $settings;
    }

    public function get($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }
        return $this->json(ApiResource::success(['config' => \calculator_config()]));
    }

    public function save($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }
        \admin_require_csrf();

        $input = $request->json();
        $config = $this->validateConfig($input);
        if ($config === null) {
            return $this->json(ApiResource::error('Проверьте настройки: валюту, названия, цены и сроки'), 400);
        }

        $result = $this->settings->saveAllWithType([
            'calculatorConfig' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
        if (!$result['success']) {
            return $this->json(ApiResource::error('Не удалось сохранить настройки'), 500);
        }

        return $this->json(ApiResource::success(['config' => $config]));
    }

    /**
     * Валидация конфигурации калькулятора.
     * Возвращает нормализованный массив либо null при ошибках.
     */
    private function validateConfig($input): ?array
    {
        if (!is_array($input) ||
            !isset($input['currency']) || !is_string($input['currency']) ||
            mb_strlen(trim($input['currency'])) < 1 || mb_strlen(trim($input['currency'])) > 8) {
            return null;
        }
        if (!isset($input['types']) || !is_array($input['types']) ||
            count($input['types']) < 1 || count($input['types']) > 50) {
            return null;
        }

        $types = [];
        $names = [];
        $ids = [];
        foreach ($input['types'] as $type) {
            if (!is_array($type) || !isset($type['name'], $type['price'], $type['days'], $type['features']) ||
                !is_string($type['name']) || !is_array($type['features']) || count($type['features']) > 50) {
                return null;
            }
            $name = trim($type['name']);
            if (mb_strlen($name) < 1 || mb_strlen($name) > 80) {
                return null;
            }
            $nameKey = mb_strtolower($name);
            if (isset($names[$nameKey])) {
                return null;
            }
            $names[$nameKey] = true;

            // id сохраняем, если корректен и уникален; иначе генерируем новый
            $id = isset($type['id']) && is_string($type['id']) && preg_match('/^[a-z0-9-]{1,40}$/', $type['id'])
                && !isset($ids[$type['id']])
                ? $type['id']
                : 'type-' . bin2hex(random_bytes(6));
            $ids[$id] = true;

            $features = [];
            foreach ($type['features'] as $feature) {
                if (!is_array($feature) || !isset($feature['name'], $feature['price'], $feature['days']) ||
                    !is_string($feature['name'])) {
                    return null;
                }
                $featureName = trim($feature['name']);
                if (mb_strlen($featureName) < 1 || mb_strlen($featureName) > 100) {
                    return null;
                }
                $price = filter_var($feature['price'], FILTER_VALIDATE_INT);
                $days = filter_var($feature['days'], FILTER_VALIDATE_INT);
                if ($price === false || $price < 0 || $price > 10000000 ||
                    $days === false || $days < 0 || $days > 3650) {
                    return null;
                }
                $features[] = ['name' => $featureName, 'price' => $price, 'days' => $days];
            }

            $price = filter_var($type['price'], FILTER_VALIDATE_INT);
            $days = filter_var($type['days'], FILTER_VALIDATE_INT);
            if ($price === false || $price < 0 || $price > 10000000 ||
                $days === false || $days < 0 || $days > 3650) {
                return null;
            }

            $types[] = [
                'id' => $id,
                'name' => $name,
                'price' => $price,
                'days' => $days,
                'features' => $features,
            ];
        }

        return [
            'currency' => trim($input['currency']),
            'types' => $types,
        ];
    }
}
