<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Employee;
use App\Models\User;
use PDOException;

final class UserController extends Controller
{
    private User $users;
    private Employee $employees;

    private const ROLES = [
        'owner',
        'admin',
        'cashier',
        'inventory_staff',
    ];

    public function __construct()
    {
        $this->users = new User();
        $this->employees = new Employee();
    }

    public function index(): void
    {
        $this->view('users/index', [
            'users' => $this->users->all(),
            'currentUserId' => (int) Auth::user()['id'],
            'csrfToken' => Csrf::token(),
            'user' => Auth::user(),
        ]);
    }

    public function create(): void
    {
        $this->view('users/create', [
            'employees' => $this->employees->availableForUser(),
            'csrfToken' => Csrf::token(),
            'user' => Auth::user(),
        ]);
    }

    public function store(): void
    {
        if (!$this->verifyCsrf()) {
            return;
        }

        $data = $this->validatedData(true);

        if ($data === null) {
            header('Location: /users/create');
            exit;
        }

        try {
            $this->users->create($data);

            $_SESSION['flash_success'] = 'Akun pengguna berhasil dibuat.';
            header('Location: /users');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $_SESSION['flash_error'] = 'Username, email, atau karyawan sudah digunakan.';
                header('Location: /users/create');
                exit;
            }

            throw $e;
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $account = $this->users->findById($id);

        if (!$account) {
            http_response_code(404);
            exit('User not found.');
        }

        $this->view('users/edit', [
            'account' => $account,
            'employees' => $this->employees->availableForUser($id),
            'currentUserId' => (int) Auth::user()['id'],
            'csrfToken' => Csrf::token(),
            'user' => Auth::user(),
        ]);
    }

    public function update(): void
    {
        if (!$this->verifyCsrf()) {
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $account = $this->users->findById($id);

        if (!$account) {
            http_response_code(404);
            exit('User not found.');
        }

        $data = $this->validatedData(false);

        if ($data === null) {
            header('Location: /users/edit?id=' . $id);
            exit;
        }

        $currentUserId = (int) Auth::user()['id'];

        // Owner tidak boleh mengubah role akunnya sendiri.
        if ($id === $currentUserId) {
            $data['role'] = $account['role'];
            $data['is_active'] = 1;
        }

        // Jangan sampai Owner terakhir kehilangan akses.
        if (
            $account['role'] === 'owner'
            && $data['role'] !== 'owner'
            && (bool) $account['is_active']
            && $this->users->countActiveOwners() <= 1
        ) {
            $_SESSION['flash_error'] = 'Tidak dapat mengubah role Owner terakhir.';
            header('Location: /users/edit?id=' . $id);
            exit;
        }

        try {
            $this->users->update($id, $data);

            $password = $_POST['password'] ?? '';

            if ($password !== '') {
                if (strlen($password) < 8) {
                    $_SESSION['flash_error'] = 'Password minimal 8 karakter.';
                    header('Location: /users/edit?id=' . $id);
                    exit;
                }

                $this->users->updatePassword($id, $password);
            }

            $_SESSION['flash_success'] = 'Akun pengguna berhasil diperbarui.';
            header('Location: /users');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $_SESSION['flash_error'] = 'Username, email, atau karyawan sudah digunakan.';
                header('Location: /users/edit?id=' . $id);
                exit;
            }

            throw $e;
        }
    }

    public function toggleStatus(): void
    {
        if (!$this->verifyCsrf()) {
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $account = $this->users->findById($id);

        if (!$account) {
            http_response_code(404);
            exit('User not found.');
        }

        $currentUserId = (int) Auth::user()['id'];

        if ($id === $currentUserId) {
            $_SESSION['flash_error'] = 'Kamu tidak dapat menonaktifkan akun sendiri.';
            header('Location: /users');
            exit;
        }

        $isActive = (bool) $account['is_active'];

        if (
            $isActive
            && $account['role'] === 'owner'
            && $this->users->countActiveOwners() <= 1
        ) {
            $_SESSION['flash_error'] = 'Owner aktif terakhir tidak dapat dinonaktifkan.';
            header('Location: /users');
            exit;
        }

        $this->users->setActive($id, !$isActive);

        $_SESSION['flash_success'] = 'Status akun berhasil diperbarui.';
        header('Location: /users');
        exit;
    }

    private function validatedData(bool $requirePassword): ?array
    {
        $data = [
            'employee_id' => trim($_POST['employee_id'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'role' => $_POST['role'] ?? '',
            'password' => $_POST['password'] ?? '',
            'is_active' => isset($_POST['is_active'])
                ? (int) $_POST['is_active']
                : 1,
        ];

        if (
            $data['name'] === ''
            || $data['username'] === ''
            || !in_array($data['role'], self::ROLES, true)
        ) {
            $_SESSION['flash_error'] = 'Nama, username, dan role wajib diisi dengan benar.';
            return null;
        }

        if (
            $data['email'] !== ''
            && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)
        ) {
            $_SESSION['flash_error'] = 'Format email tidak valid.';
            return null;
        }

        if (
            $requirePassword
            && strlen($data['password']) < 8
        ) {
            $_SESSION['flash_error'] = 'Password minimal 8 karakter.';
            return null;
        }

        if (
            $data['password'] !== ''
            && strlen($data['password']) < 8
        ) {
            $_SESSION['flash_error'] = 'Password minimal 8 karakter.';
            return null;
        }

        if (!in_array($data['is_active'], [0, 1], true)) {
            $_SESSION['flash_error'] = 'Status akun tidak valid.';
            return null;
        }

        $data['employee_id'] = $data['employee_id'] !== ''
            ? (int) $data['employee_id']
            : null;

        return $data;
    }

    private function verifyCsrf(): bool
    {
        if (Csrf::verify($_POST['_token'] ?? null)) {
            return true;
        }

        http_response_code(419);
        exit('419 - Invalid CSRF Token');
    }
}
