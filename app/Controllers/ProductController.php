<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Category;
use App\Models\Product;
use PDOException;

final class ProductController extends Controller
{
    private Product $products;
    private Category $categories;

    public function __construct()
    {
        $this->products = new Product();
        $this->categories = new Category();
    }

    public function index(): void
    {
        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'category_id' => $_GET['category_id'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];

        $this->view('products/index', [
            'products' => $this->products->filter($filters),
            'categories' => $this->categories->all(),
            'filters' => $filters,
            'user' => Auth::user(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function create(): void
    {
        $this->view('products/create', [
            'categories' => $this->categories->active(),
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
            header('Location: /products/create');
            exit;
        }

        try {
            $this->products->create($data);
            $_SESSION['flash_success'] = 'Produk berhasil ditambahkan.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = $e->getCode() === '23000'
                ? 'Kode produk sudah digunakan atau kategori tidak valid.'
                : 'Terjadi kesalahan saat menyimpan produk.';
        }

        header('Location: /products');
        exit;
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $product = $this->products->findById($id);

        if (!$product) {
            http_response_code(404);
            exit('Product not found.');
        }

        $this->view('products/edit', [
            'product' => $product,
            'categories' => $this->categories->active(),
            'user' => Auth::user(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function update(): void
    {
        $this->verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        $product = $this->products->findById($id);

        if (!$product) {
            http_response_code(404);
            exit('Product not found.');
        }

        $data = $this->validate($_POST);

        if (isset($data['errors'])) {
            $_SESSION['flash_error'] = implode(' ', $data['errors']);
            header('Location: /products/edit?id=' . $id);
            exit;
        }

        try {
            $this->products->update($id, $data);
            $_SESSION['flash_success'] = 'Produk berhasil diperbarui.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = $e->getCode() === '23000'
                ? 'Kode produk sudah digunakan atau kategori tidak valid.'
                : 'Terjadi kesalahan saat memperbarui produk.';
        }

        header('Location: /products');
        exit;
    }

    public function toggleStatus(): void
    {
        $this->verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        $product = $this->products->findById($id);

        if (!$product) {
            http_response_code(404);
            exit('Product not found.');
        }

        $active = (int) ($_POST['is_active'] ?? -1);

        if (!in_array($active, [0, 1], true)) {
            $_SESSION['flash_error'] = 'Status produk tidak valid.';
            header('Location: /products');
            exit;
        }

        $this->products->setActive($id, (bool) $active);

        $_SESSION['flash_success'] = 'Status produk berhasil diperbarui.';

        header('Location: /products');
        exit;
    }

    public function toggleAvailability(): void
    {
        $this->verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        $product = $this->products->findById($id);

        if (!$product) {
            http_response_code(404);
            exit('Product not found.');
        }

        $available = (int) ($_POST['is_available'] ?? -1);

        if (!in_array($available, [0, 1], true)) {
            $_SESSION['flash_error'] = 'Status ketersediaan tidak valid.';
            header('Location: /products');
            exit;
        }

if (!in_array($requiresRecipe, [0, 1], true)) {
    $errors[] = 'Jenis pengelolaan resep tidak valid.';
}

        $this->products->setAvailability($id, (bool) $available);

        $_SESSION['flash_success'] = 'Ketersediaan produk diperbarui.';

        header('Location: /products');
        exit;
    }

    private function validate(array $input): array
    {
        $name = trim($input['name'] ?? '');
        $code = trim($input['product_code'] ?? '');
        $description = trim($input['description'] ?? '');
        $categoryId = filter_var(
            $input['category_id'] ?? null,
            FILTER_VALIDATE_INT
        );
        $price = $input['price'] ?? '';
        $available = (int) ($input['is_available'] ?? 0);

$requiresRecipe = filter_var(
    $input['requires_recipe'] ?? null,
    FILTER_VALIDATE_INT
);

        $errors = [];

        if ($name === '') {
            $errors[] = 'Nama produk wajib diisi.';
        } elseif (mb_strlen($name) > 150) {
            $errors[] = 'Nama produk maksimal 150 karakter.';
        }

        if ($code !== '' && mb_strlen($code) > 30) {
            $errors[] = 'Kode produk maksimal 30 karakter.';
        }

        if (!$categoryId || !$this->categories->findById($categoryId)) {
            $errors[] = 'Kategori produk tidak valid.';
        }

        if ($categoryId) {
            $category = $this->categories->findById($categoryId);

        if ($category && !(bool) $category['is_active']) {
            $errors[] = 'Kategori yang dipilih sedang nonaktif.';
        }
}

        if (
            !is_numeric($price)
            || (float) $price < 0
            || !is_finite((float) $price)
        ) {
            $errors[] = 'Harga produk tidak valid.';
        }

        if ((float) $price > 9999999999999) {
            $errors[] = 'Harga produk terlalu besar.';
        }

        if (!in_array($available, [0, 1], true)) {
            $errors[] = 'Status ketersediaan tidak valid.';
        }

        if (mb_strlen($description) > 5000) {
            $errors[] = 'Deskripsi terlalu panjang.';
        }

        if ($errors) {
            return ['errors' => $errors];
        }

        return [
            'category_id' => $categoryId,
            'product_code' => $code,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'is_available' => $available,
            'requires_recipe' => $requiresRecipe,
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
