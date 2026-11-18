<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Employee;

final class EmployeeController extends Controller
{
    private Employee $employee;

    public function __construct()
    {
        $this->employee = new Employee();
    }

    public function index(): void
    {
        $keyword = trim($_GET['search'] ?? '');
        $employees = $this->employee->all();

        if ($keyword !== '') {
            $employees = array_filter(
                $employees,
                fn ($employee) =>
                    stripos($employee['name'], $keyword) !== false
                    || stripos($employee['employee_code'], $keyword) !== false
                    || stripos($employee['position'], $keyword) !== false
            );
        }

        $this->view('employees/index', [
            'employees' => $employees,
            'keyword' => $keyword,
            'csrfToken' => Csrf::token(),
            'user' => Auth::user(),
        ]);
    }

    public function create(): void
    {
        $this->view('employees/create', [
            'csrfToken' => Csrf::token(),
            'user' => Auth::user(),
        ]);
    }

    public function store(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            http_response_code(419);
            exit('419 - Invalid CSRF Token');
        }

        $data = $this->validatedData();

        if ($data === null) {
            header('Location: /employees/create');
            exit;
        }

        try {
            $this->employee->create($data);
            $_SESSION['flash_success'] = 'Karyawan berhasil ditambahkan.';
            header('Location: /employees');
            exit;
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                $_SESSION['flash_error'] = 'Kode karyawan atau email sudah digunakan.';
                header('Location: /employees/create');
                exit;
            }

            throw $e;
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $employee = $this->employee->findById($id);

        if (!$employee) {
            http_response_code(404);
            exit('Employee not found.');
        }

        $this->view('employees/edit', [
            'employee' => $employee,
            'csrfToken' => Csrf::token(),
            'user' => Auth::user(),
        ]);
    }

    public function update(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            http_response_code(419);
            exit('419 - Invalid CSRF Token');
        }

        $id = (int) ($_POST['id'] ?? 0);

        if (!$this->employee->findById($id)) {
            http_response_code(404);
            exit('Employee not found.');
        }

        $data = $this->validatedData();

        if ($data === null) {
            header('Location: /employees/edit?id=' . $id);
            exit;
        }

        try {
            $this->employee->update($id, $data);
            $_SESSION['flash_success'] = 'Data karyawan berhasil diperbarui.';
            header('Location: /employees');
            exit;
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                $_SESSION['flash_error'] = 'Kode karyawan atau email sudah digunakan.';
                header('Location: /employees/edit?id=' . $id);
                exit;
            }

            throw $e;
        }
    }

    public function toggleStatus(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            http_response_code(419);
            exit('419 - Invalid CSRF Token');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $employee = $this->employee->findById($id);

        if (!$employee) {
            http_response_code(404);
            exit('Employee not found.');
        }

        $newStatus = $employee['employment_status'] === 'active'
            ? 'inactive'
            : 'active';

        $this->employee->setStatus($id, $newStatus);

        $_SESSION['flash_success'] = 'Status karyawan berhasil diperbarui.';
        header('Location: /employees');
        exit;
    }

    private function validatedData(): ?array
    {
        $data = [
            'employee_code' => trim($_POST['employee_code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'position' => trim($_POST['position'] ?? ''),
            'hire_date' => trim($_POST['hire_date'] ?? ''),
            'employment_status' => $_POST['employment_status'] ?? 'active',
        ];

        if (!in_array($data['position'], [
            'owner',
            'admin',
            'cashier',
            'inventory_staff',
        ], true)) {
            $_SESSION['flash_error'] = 'Jabatan tidak valid.';
            return null;
        }

        if (
            $data['employee_code'] === ''
            || $data['name'] === ''
            || $data['position'] === ''
        ) {
            $_SESSION['flash_error'] = 'Kode, nama, dan jabatan wajib diisi.';
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
            !in_array($data['employment_status'], ['active', 'inactive'], true)
        ) {
            $_SESSION['flash_error'] = 'Status karyawan tidak valid.';
            return null;
        }

        if (
            $data['hire_date'] !== ''
            && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['hire_date'])
        ) {
            $_SESSION['flash_error'] = 'Format tanggal masuk tidak valid.';
            return null;
        }

        return $data;
    }

}
