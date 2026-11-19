<?php

declare(strict_types=1);

namespace App\Core;

final class Application
{
    private Router $router;

    public function __construct()
    {
        $this->router = new Router();

        $config = require BASE_PATH . '/config/app.php';

        date_default_timezone_set($config['timezone']);

        $session = $config['session'];

        session_name($session['name']);

        session_set_cookie_params([
            'lifetime' => $session['lifetime'],
            'path' => '/',
            'secure' => $session['secure'],
            'httponly' => $session['httponly'],
            'samesite' => $session['samesite'],
        ]);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $uri = parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        ) ?: '/';

        $this->router->dispatch($method, $uri);
    }
}
