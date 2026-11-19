<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Authorization;

final class PermissionMiddleware
{
    public function handle(
        callable $next,
        string $permission
    ): void {
        if (!Authorization::allows($permission)) {
            http_response_code(403);

            echo '403 - You do not have permission to access this resource.';

            return;
        }

        $next();
    }
}
