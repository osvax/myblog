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
                return new Response('Method not allowed', 405, ['Allow' => implode(', ', $allowed)]);
            }
            return new Response('Not found', 404, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        return new Response('Not found', 404, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    private function invoke(callable $handler, Request $request, array $parameters)
    {
        try {
            $response = call_user_func($handler, $request, $parameters);
            return $response instanceof Response ? $response : new Response('', 204);
        } catch (\Throwable $error) {
            error_log('MVC request failed: ' . $error->getMessage() . ' in ' . $error->getFile() . ':' . $error->getLine() . "\n" . $error->getTraceAsString());
            return Response::json(['success' => false, 'error' => 'Внутренняя ошибка сервера.'], 500);
        }
    }
}
