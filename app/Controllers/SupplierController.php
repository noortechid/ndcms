<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Supplier;
use InvalidArgumentException;
use Throwable;

final class SupplierController extends Controller
{
    private Supplier $supplier;

    public function __construct()
    {
        $this->supplier = new Supplier();
    }

    /**
     * Display supplier list.
     */
    public function index(): void
    {
        if (!Auth::user()) {
            header('Location: /login');
            exit;
        }

        $this->view('suppliers/index', [
            'suppliers' => $this->supplier->all(),
        ]);
    }

    /**
     * Display create form.
     */
    public function create(): void
    {
        if (!Auth::user()) {
            header('Location: /login');
            exit;
        }

        $this->view('suppliers/create');
    }

    /**
     * Store new supplier.
     */
    public function store(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            http_response_code(419);
            exit('CSRF token tidak valid.');
        }

        if (!Auth::user()) {
            header('Location: /login');
            exit;
        }

        try {
            $data = $this->validateSupplierData();

            $this->supplier->create($data);

            $_SESSION['flash_success'] =
                'Supplier berhasil ditambahkan.';

            header('Location: /suppliers');
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['flash_error'] = $e->getMessage();

            header('Location: /suppliers/create');
            exit;

        } catch (Throwable $e) {
            error_log($e->getMessage());

            $_SESSION['flash_error'] =
                'Terjadi kesalahan saat menambahkan supplier.';

            header('Location: /suppliers/create');
            exit;
        }
    }

    /**
     * Display edit form.
     */
    public function edit(): void
    {
        if (!Auth::user()) {
            header('Location: /login');
            exit;
        }

        $id = filter_var(
            $_GET['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id || $id < 1) {
            $_SESSION['flash_error'] = 'ID supplier tidak valid.';

            header('Location: /suppliers');
            exit;
        }

        $supplier = $this->supplier->find($id);

        if (!$supplier) {
            $_SESSION['flash_error'] = 'Supplier tidak ditemukan.';

            header('Location: /suppliers');
            exit;
        }

        $this->view('suppliers/edit', [
            'supplier' => $supplier,
        ]);
    }

    /**
     * Update supplier.
     */
    public function update(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            http_response_code(419);
            exit('CSRF token tidak valid.');
        }

        if (!Auth::user()) {
            header('Location: /login');
            exit;
        }

        $id = filter_var(
            $_POST['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id || $id < 1) {
            $_SESSION['flash_error'] = 'ID supplier tidak valid.';

            header('Location: /suppliers');
            exit;
        }

        if (!$this->supplier->find($id)) {
            $_SESSION['flash_error'] = 'Supplier tidak ditemukan.';

            header('Location: /suppliers');
            exit;
        }

        try {
            $data = $this->validateSupplierData();

            $this->supplier->update($id, $data);

            $_SESSION['flash_success'] =
                'Data supplier berhasil diperbarui.';

            header('Location: /suppliers');
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['flash_error'] = $e->getMessage();

            header('Location: /suppliers/edit?id=' . $id);
            exit;

        } catch (Throwable $e) {
            error_log($e->getMessage());

            $_SESSION['flash_error'] =
                'Terjadi kesalahan saat memperbarui supplier.';

            header('Location: /suppliers/edit?id=' . $id);
            exit;
        }
    }

    /**
     * Activate or deactivate supplier.
     */
    public function status(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            http_response_code(419);
            exit('CSRF token tidak valid.');
        }

        if (!Auth::user()) {
            header('Location: /login');
            exit;
        }

        $id = filter_var(
            $_POST['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $status = $_POST['is_active'] ?? null;

        if (!$id || $id < 1 || !in_array($status, ['0', '1'], true)) {
            $_SESSION['flash_error'] = 'Data status supplier tidak valid.';

            header('Location: /suppliers');
            exit;
        }

        try {
            if (!$this->supplier->find($id)) {
                $_SESSION['flash_error'] = 'Supplier tidak ditemukan.';

                header('Location: /suppliers');
                exit;
            }

            $this->supplier->setActive($id, $status === '1');

            $_SESSION['flash_success'] = $status === '1'
                ? 'Supplier berhasil diaktifkan.'
                : 'Supplier berhasil dinonaktifkan.';

        } catch (Throwable $e) {
            error_log($e->getMessage());

            $_SESSION['flash_error'] =
                'Terjadi kesalahan saat mengubah status supplier.';
        }

        header('Location: /suppliers');
        exit;
    }

    /**
     * Validate supplier input.
     */
    private function validateSupplierData(): array
    {
        $data = [
            'supplier_code' => trim($_POST['supplier_code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'contact_person' => trim($_POST['contact_person'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
        ];

        if ($data['name'] === '') {
            throw new InvalidArgumentException(
                'Nama supplier wajib diisi.'
            );
        }

        $maxLengths = [
            'supplier_code' => 30,
            'name' => 150,
            'contact_person' => 100,
            'phone' => 30,
            'email' => 100,
        ];

        foreach ($maxLengths as $field => $maxLength) {
            $length = function_exists('mb_strlen')
                ? mb_strlen($data[$field])
                : strlen($data[$field]);

            if ($length > $maxLength) {
                throw new InvalidArgumentException(
                    ucfirst(str_replace('_', ' ', $field))
                    . ' maksimal ' . $maxLength . ' karakter.'
                );
            }
        }

        if (
            $data['email'] !== ''
            && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)
        ) {
            throw new InvalidArgumentException(
                'Format email supplier tidak valid.'
            );
        }

        return $data;
    }
}
