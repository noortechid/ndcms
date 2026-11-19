<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Category;
use RuntimeException;
use PDOException;

final class CategoryController extends Controller
{
    private Category $categories;

    public function __construct()
    {
        $this->categories = new Category();
    }

    public function index(): void
    {
        $this->view('categories/index', [
            'categories' => $this->categories->all(),
            'user' => Auth::user(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function create(): void
    {
        $this->view('categories/create', [
            'user' => Auth::user(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function store(): void
    {
        $this->verifyCsrf();

        $data = $this->validate($_POST);

        if (isset($data['errors'])) {
            $_SESSION['flash_error'] = implode(' ', $data['errors']);
            header('Location: /categories/create');
            exit;
        }

        try {
            $this->categories->create($data);

            $_SESSION['flash_success'] = 'Kategori berhasil ditambahkan.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = $e->getCode() === '23000'
                ? 'Nama kategori sudah digunakan.'
                : 'Terjadi kesalahan saat menyimpan kategori.';
        }

        header('Location: /categories');
        exit;
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $category = $this->categories->findById($id);

        if (!$category) {
            http_response_code(404);
            exit('Category not found.');
        }

        $this->view('categories/edit', [
            'category' => $category,
            'user' => Auth::user(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function update(): void
    {
        $this->verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        $category = $this->categories->findById($id);

        if (!$category) {
            http_response_code(404);
            exit('Category not found.');
        }

        $data = $this->validate($_POST);

        if (isset($data['errors'])) {
            $_SESSION['flash_error'] = implode(' ', $data['errors']);
            header('Location: /categories/edit?id=' . $id);
            exit;
        }

        try {
            $this->categories->update($id, $data);
            $_SESSION['flash_success'] = 'Kategori berhasil diperbarui.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = $e->getCode() === '23000'
                ? 'Nama kategori sudah digunakan.'
                : 'Terjadi kesalahan saat memperbarui kategori.';
        }

        header('Location: /categories');
        exit;
    }

    public function toggleStatus(): void
    {
        $this->verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        $category = $this->categories->findById($id);

        if (!$category) {
            http_response_code(404);
            exit('Category not found.');
        }

        $active = (int) ($_POST['is_active'] ?? -1);

        if (!in_array($active, [0, 1], true)) {
            $_SESSION['flash_error'] = 'Status kategori tidak valid.';
            header('Location: /categories');
            exit;
        }

        if ($active === 0 && $this->categories->hasProducts($id)) {
            $_SESSION['flash_error'] =
                'Kategori masih memiliki produk. Nonaktifkan atau pindahkan produknya terlebih dahulu.';
            header('Location: /categories');
            exit;
        }

        $this->categories->setActive($id, (bool) $active);

        $_SESSION['flash_success'] = 'Status kategori berhasil diperbarui.';

        header('Location: /categories');
        exit;
    }

    private function validate(array $input): array
    {
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');

        $errors = [];

        if ($name === '') {
            $errors[] = 'Nama kategori wajib diisi.';
        } elseif (mb_strlen($name) > 100) {
            $errors[] = 'Nama kategori maksimal 100 karakter.';
        }

        if (mb_strlen($description) > 5000) {
            $errors[] = 'Deskripsi terlalu panjang.';
        }

        if ($errors) {
            return ['errors' => $errors];
        }

        return [
            'name' => $name,
            'description' => $description,
        ];
    }

    private function verifyCsrf(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            http_response_code(419);
            exit('419 - Invalid CSRF Token');
        }
    }
}
