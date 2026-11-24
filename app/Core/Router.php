<?php

namespace App\Core;

class Router
{
    private $routes = [];

    public function get($path, callable $handler)
    {
        $this->add('GET', $path, $handler);
    }

    public function head($path, callable $handler)
    {
        $this->add('HEAD', $path, $handler);
    }

    public function post($path, callable $handler)
    {
        $this->add('POST', $path, $handler);
    }

    public function put($path, callable $handler)
    {
        $this->add('PUT', $path, $handler);
    }

    public function patch($path, callable $handler)
    {
        $this->add('PATCH', $path, $handler);
    }

    public function delete($path, callable $handler)
    {
        $this->add('DELETE', $path, $handler);
    }

    private function add($method, $path, callable $handler)
    {
        $normalizedPath = '/' . trim((string) $path, '/');
        if ($normalizedPath === '//') {
            $normalizedPath = '/';
        }
        $this->routes[$method][$normalizedPath] = $handler;
    }

    public function dispatch(Request $request)
    {
        $path = $request->path();
        $handler = $this->routes[$request->method()][$path] ?? null;
        $parameters = [];
        if ($handler !== null) {
            return $this->invoke($handler, $request, $parameters);
        }

        foreach ($this->routes[$request->method()] ?? [] as $routePath => $routeHandler) {
            if (strpos($routePath, '{') === false) {
                continue;
            }
            $names = [];
            $parts = preg_split('/(\{[a-zA-Z][a-zA-Z0-9_]*\})/', $routePath, -1, PREG_SPLIT_DELIM_CAPTURE);
            $pattern = '';
            foreach ($parts as $part) {
                if (preg_match('/^\{([a-zA-Z][a-zA-Z0-9_]*)\}$/', $part, $match)) {
                    $names[] = $match[1];
                    $pattern .= '([^/]+)';
                } else {
                    $pattern .= preg_quote($part, '#');
                }
            }
            if (preg_match('#^' . $pattern . '$#', $path, $matches)) {
                foreach ($names as $index => $name) {
                    $parameters[$name] = rawurldecode($matches[$index + 1]);
                }
                return $this->invoke($routeHandler, $request, $parameters);
            }
        }

        if ($handler === null) {
            $allowed = [];
            foreach ($this->routes as $method => $routes) {
                if (isset($routes[$path])) {
                    $allowed[] = $method;
                }
            }
            if ($allowed) {
                return $this->methodNotAllowed($request->path());
            }
            return $this->notFound($request->path());
        }

        return $this->notFound($request->path());
    }

    /**
     * 404: JSON для API, красивая страница для HTML-маршрутов.
     */
    private function notFound(string $path): Response
    {
        if (strpos($path, '/api/') === 0) {
            return Response::json(['success' => false, 'error' => 'Не найдено'], 404);
        }
        // Для админ-путей: если нет ключа доступа, проверяем аутентификацию
        // Если неавторизован — редирект на страницу входа, иначе 404
        if (strpos($path, '/admin') === 0) {
            if (!\admin_is_authenticated()) {
                return Response::redirect('/admin/login.html', 302);
            }
            return ErrorPages::page(404, 'Страница не найдена', 'Такой страницы нет или она была перемещена.', '/admin/index.html');
        }
        $home = '/';
        return ErrorPages::page(404, 'Страница не найдена', 'Такой страницы нет или она была перемещена.', $home);
    }

    /**
     * 405: JSON для API, красивая страница для HTML-маршрутов.
     */
    private function methodNotAllowed(string $path): Response
    {
        if (strpos($path, '/api/') === 0) {
            return new Response('Method not allowed', 405, ['Allow' => 'GET, POST, PUT, DELETE', 'Content-Type' => 'application/json; charset=utf-8']);
        }
        // Для админ-путей: если нет ключа доступа, проверяем аутентификацию
        if (strpos($path, '/admin') === 0) {
            if (!\admin_is_authenticated()) {
                return Response::redirect('/admin/login.html', 302);
            }
            return ErrorPages::page(403, 'Метод не поддерживается', 'Эта страница не отвечает на такой способ запроса. Вернитесь на главную.', '/admin/index.html');
        }
        $home = '/';
        return ErrorPages::page(403, 'Метод не поддерживается', 'Эта страница не отвечает на такой способ запроса. Вернитесь на главную.', $home);
    }

    private function invoke(callable $handler, Request $request, array $parameters)
    {
        try {
            $response = call_user_func($handler, $request, $parameters);
            if ($response instanceof Response) {
                return $response;
            }
            // Контроллер вернул готовый HTML строкой — отдаём его как страницу,
            // а не превращаем в пустой ответ 204 (например, сгенерированный лендинг в HomeController).
            if (is_string($response) && $response !== '') {
                return new Response($response, 200, ['Content-Type' => 'text/html; charset=utf-8']);
            }
            return new Response('', 204);
        } catch (\Throwable $error) {
            error_log('MVC request failed: ' . $error->getMessage() . ' in ' . $error->getFile() . ':' . $error->getLine() . "\n" . $error->getTraceAsString());
            \App\Core\Logger::write('errors', 'Ошибка 500: ' . $error->getMessage() . ' (' . $request->path() . ')');
            if (strpos($request->path(), '/api/') === 0) {
                return Response::json(['success' => false, 'error' => 'Внутренняя ошибка сервера.'], 500);
            }
            $home = strpos($request->path(), '/admin') === 0 ? '/admin/index.html' : '/';
            return ErrorPages::page(500, 'Внутренняя ошибка сервера', 'Что-то пошло не так. Попробуйте обновить страницу через минуту.', $home);
        }
    }
}
