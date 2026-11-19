<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Ingredient;
use PDOException;

class IngredientController extends Controller
{
    private Ingredient $ingredients;

    public function __construct()
    {
        $this->ingredients = new Ingredient();
    }

    public function index(): void
    {
        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'status' => $_GET['status'] ?? '',
        ];

        $this->view('ingredients/index', [
            'pageTitle' => 'Bahan Baku',
            'user' => Auth::user(),
            'csrfToken' => Csrf::token(),
            'ingredients' => $this->ingredients->filter($filters),
            'filters' => $filters,
        ]);
    }

    public function create(): void
    {
        $this->view('ingredients/create', [
            'pageTitle' => 'Tambah Bahan Baku',
            'user' => Auth::user(),
            'csrfToken' => Csrf::token(),
            'ingredient' => [
                'ingredient_code' => '',
                'name' => '',
                'unit' => 'g',
                'cost_per_unit' => '',
                'minimum_stock' => '0',
                'description' => '',
            ],
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Permintaan tidak valid. Silakan coba lagi.';
            header('Location: /ingredients/create');
            exit;
        }

        $data = $this->getInput();
        $errors = $this->validate($data);

        if ($errors) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            header('Location: /ingredients/create');
            exit;
        }

        try {
            $this->ingredients->create($data);

            $_SESSION['flash_success'] = 'Bahan baku berhasil ditambahkan.';
            header('Location: /ingredients');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $_SESSION['flash_error'] = 'Kode bahan baku sudah digunakan.';
            } else {
                throw $e;
            }

            header('Location: /ingredients/create');
            exit;
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $ingredient = $this->ingredients->findById($id);

        if (!$ingredient) {
            http_response_code(404);
            echo 'Bahan baku tidak ditemukan.';
            return;
        }

        $this->view('ingredients/edit', [
            'pageTitle' => 'Edit Bahan Baku',
            'user' => Auth::user(),
            'csrfToken' => Csrf::token(),
            'ingredient' => $ingredient,
            'errors' => [],
        ]);
    }

    public function update(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Permintaan tidak valid. Silakan coba lagi.';
            header('Location: /ingredients');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $ingredient = $this->ingredients->findById($id);

        if (!$ingredient) {
            $_SESSION['flash_error'] = 'Bahan baku tidak ditemukan.';
            header('Location: /ingredients');
            exit;
        }

        $data = $this->getInput();
        $errors = $this->validate($data);

        if ($errors) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            header('Location: /ingredients/edit?id=' . $id);
            exit;
        }

        try {
            $this->ingredients->update($id, $data);

            $_SESSION['flash_success'] = 'Bahan baku berhasil diperbarui.';
            header('Location: /ingredients');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $_SESSION['flash_error'] = 'Kode bahan baku sudah digunakan.';
            } else {
                throw $e;
            }

            header('Location: /ingredients/edit?id=' . $id);
            exit;
        }
    }

    public function toggleStatus(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Permintaan tidak valid.';
            header('Location: /ingredients');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['is_active'] ?? null;

        if (!in_array((string) $status, ['0', '1'], true)) {
            $_SESSION['flash_error'] = 'Status bahan baku tidak valid.';
            header('Location: /ingredients');
            exit;
        }

        if (!$this->ingredients->findById($id)) {
            $_SESSION['flash_error'] = 'Bahan baku tidak ditemukan.';
            header('Location: /ingredients');
            exit;
        }

if (
    $status === '0' &&
    $this->ingredients->hasRecipes($id)
) {
    $_SESSION['flash_error'] =
        'Bahan baku tidak dapat dinonaktifkan karena masih digunakan dalam resep.';

    header('Location: /ingredients');
    exit;
}

        $this->ingredients->setActive($id, (bool) $status);

        $_SESSION['flash_success'] = 'Status bahan baku berhasil diperbarui.';
        header('Location: /ingredients');
        exit;
    }

    private function getInput(): array
    {
        return [
            'ingredient_code' => trim($_POST['ingredient_code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'unit' => trim($_POST['unit'] ?? ''),
            'cost_per_unit' => trim($_POST['cost_per_unit'] ?? ''),
            'minimum_stock' => trim($_POST['minimum_stock'] ?? '0'),
            'description' => trim($_POST['description'] ?? ''),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors[] = 'Nama bahan baku wajib diisi.';
        } elseif (mb_strlen($data['name']) > 150) {
            $errors[] = 'Nama bahan baku maksimal 150 karakter.';
        }

        if (
            $data['ingredient_code'] !== '' &&
            mb_strlen($data['ingredient_code']) > 30
        ) {
            $errors[] = 'Kode bahan baku maksimal 30 karakter.';
        }

        if (!in_array($data['unit'], ['g', 'ml', 'pcs'], true)) {
            $errors[] = 'Satuan harus berupa g, ml, atau pcs.';
        }

        if (!preg_match('/^\d{1,11}(\.\d{1,4})?$/', $data['cost_per_unit'])) {
            $errors[] = 'Harga per satuan harus berupa angka positif dengan maksimal 4 angka desimal.';
        }

        if (!preg_match('/^\d{1,12}(\.\d{1,3})?$/', $data['minimum_stock'])) {
            $errors[] = 'Minimum stok harus berupa angka positif dengan maksimal 3 angka desimal.';
        }

        return $errors;
    }
}
