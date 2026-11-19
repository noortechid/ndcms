<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    private static ?array $user = null;

    public static function user(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        if (self::$user !== null) {
            return self::$user;
        }

        $user = (new User())->findById(
            (int) $_SESSION['user_id']
        );

        if (!$user || !(bool) $user['is_active']) {
            unset($_SESSION['user_id']);
            self::$user = null;

            return null;
        }

        self::$user = $user;

        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function attempt(
        string $username,
        string $password
    ): bool {
        $user = (new User())->findByUsername($username);

        if (!$user || !$user['is_active']) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];

        self::$user = null;

        (new User())->updateLastLogin((int) $user['id']);

        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]
            );
        }

        session_destroy();

        self::$user = null;
    }

    public static function hasRole(array $roles): bool
    {
        $user = self::user();

        return $user !== null
            && in_array($user['role'], $roles, true);
    }

    public static function can(string $permission): bool
    {
        return Authorization::allows($permission);
    }
}
