<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;

final class AuthController extends Controller
{
    public function login(): void
    {
        $this->view('auth/login', [
            'pageTitle' => 'Login',
            'csrfToken' => Csrf::token(),
            'error' => $_SESSION['_flash_error'] ?? null,
            'oldUsername' => $_SESSION['_old_username'] ?? '',
        ], 'layouts/guest');

        unset(
            $_SESSION['_flash_error'],
            $_SESSION['_old_username']
        );
    }

    public function authenticate(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            http_response_code(419);
            exit('419 - Invalid or expired CSRF token.');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $_SESSION['_flash_error'] = 'Username dan password wajib diisi.';
            $_SESSION['_old_username'] = $username;

            header('Location: /login');
            exit;
        }

        if (!Auth::attempt($username, $password)) {
            $_SESSION['_flash_error'] = 'Username atau password tidak valid.';
            $_SESSION['_old_username'] = $username;

            header('Location: /login');
            exit;
        }

        Csrf::regenerate();

        header('Location: /');
        exit;
    }

    public function logout(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            http_response_code(419);
            exit('419 - Invalid or expired CSRF token.');
        }

        Auth::logout();

        header('Location: /login');
        exit;
    }
}
