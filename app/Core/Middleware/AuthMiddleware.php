<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Auth;

final class AuthMiddleware
{
    public function handle(callable $next): void
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        $next();
    }
}
