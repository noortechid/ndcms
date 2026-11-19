<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(
        string $path,
        array $handler,
        array $middleware = []
    ): void {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(
        string $path,
        array $handler,
        array $middleware = []
    ): void {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(
        string $method,
        string $path,
        array $handler,
        array $middleware
    ): void {
        $this->routes[$method][$this->normalize($path)] = [
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $path): void
    {
        $route = $this->routes[$method][$this->normalize($path)] ?? null;

        if ($route === null) {
            http_response_code(404);
            echo '404 - Page Not Found';
            return;
        }

        [$controllerClass, $action] = $route['handler'];

        $destination = static function () use (
            $controllerClass,
            $action
        ): void {
            $controller = new $controllerClass();
            $controller->$action();
        };

        $pipeline = array_reduce(
            array_reverse($route['middleware']),
            static function (callable $next, $definition): callable {
                if (is_string($definition)) {
                    $middlewareClass = $definition;
                    $arguments = [];
                } elseif (
                    is_array($definition)
                    && isset($definition[0])
                    && is_string($definition[0])
                ) {
                    $middlewareClass = $definition[0];
                    $arguments = $definition[1] ?? [];
                } else {
                    throw new \InvalidArgumentException(
                        'Invalid middleware definition.'
                    );
                }

                return static function () use (
                    $middlewareClass,
                    $arguments,
                    $next
                ): void {
                    $middleware = new $middlewareClass();

                    $middleware->handle($next, ...$arguments);
                };
            },
            $destination
        );

        $pipeline();
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
