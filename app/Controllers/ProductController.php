<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Category;
use App\Models\Product;
use PDOException;
use RuntimeException;

final class ProductController extends Controller
{
    private Product $products;
    private Category $categories;

    private const MAX_IMAGE_SIZE = 2 * 1024 * 1024;

    private const ALLOWED_IMAGE_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private const IMAGE_DIRECTORY = '/assets/images/products/';

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

        $imagePath = null;

        try {
            $imagePath = $this->uploadImage();
            $data['image_path'] = $imagePath;

            $this->products->create($data);

            $_SESSION['flash_success'] = 'Produk berhasil ditambahkan.';
        } catch (RuntimeException $e) {
            if ($imagePath !== null) {
                $this->deleteImage($imagePath);
            }

            $_SESSION['flash_error'] = $e->getMessage();

            header('Location: /products/create');
            exit;
        } catch (PDOException $e) {
            if ($imagePath !== null) {
                $this->deleteImage($imagePath);
            }

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

        $newImagePath = null;
        $oldImagePath = $product['image_path'] ?? null;

        try {
            $newImagePath = $this->uploadImage();

            // Pertahankan gambar lama jika tidak ada upload baru.
            $data['image_path'] = $newImagePath ?? $oldImagePath;

            $this->products->update($id, $data);

            // Hapus gambar lama setelah update database berhasil.
            if ($newImagePath !== null && $oldImagePath !== null) {
                $this->deleteImage($oldImagePath);
            }

            $_SESSION['flash_success'] = 'Produk berhasil diperbarui.';
        } catch (RuntimeException $e) {
            if ($newImagePath !== null) {
                $this->deleteImage($newImagePath);
            }

            $_SESSION['flash_error'] = $e->getMessage();

            header('Location: /products/edit?id=' . $id);
            exit;
        } catch (PDOException $e) {
            if ($newImagePath !== null) {
                $this->deleteImage($newImagePath);
            }

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

        $available = filter_var(
            $input['is_available'] ?? null,
            FILTER_VALIDATE_INT
        );

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
        } else {
            $category = $this->categories->findById($categoryId);

            if ($category && !(bool) $category['is_active']) {
                $errors[] = 'Kategori yang dipilih sedang nonaktif.';
            }
        }

        if (
            !is_numeric($price)
            || !is_finite((float) $price)
            || (float) $price < 0
        ) {
            $errors[] = 'Harga produk tidak valid.';
        }

        if (
            is_numeric($price)
            && (float) $price > 9999999999999
        ) {
            $errors[] = 'Harga produk terlalu besar.';
        }

        if (!in_array($available, [0, 1], true)) {
            $errors[] = 'Status ketersediaan tidak valid.';
        }

        if (!in_array($requiresRecipe, [0, 1], true)) {
            $errors[] = 'Jenis pengelolaan resep tidak valid.';
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

    /**
     * Memvalidasi dan menyimpan gambar produk.
     *
     * @return string|null Relative path gambar atau null jika tidak ada upload.
     */
    private function uploadImage(): ?string
    {
        if (!isset($_FILES['image'])) {
            return null;
        }

        $file = $_FILES['image'];

        if (!is_array($file) || is_array($file['error'] ?? null)) {
            throw new RuntimeException('Data upload gambar tidak valid.');
        }

        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(
                match ($error) {
                    UPLOAD_ERR_INI_SIZE,
                    UPLOAD_ERR_FORM_SIZE => 'Ukuran gambar melebihi batas upload server.',
                    UPLOAD_ERR_PARTIAL => 'Gambar hanya terunggah sebagian.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary upload tidak tersedia.',
                    UPLOAD_ERR_CANT_WRITE => 'Server gagal menyimpan gambar.',
                    UPLOAD_ERR_EXTENSION => 'Upload gambar dihentikan oleh konfigurasi server.',
                    default => 'Terjadi kesalahan saat mengunggah gambar.',
                }
            );
        }

        $tmpName = $file['tmp_name'] ?? '';
        $fileSize = (int) ($file['size'] ?? 0);

        if (
            $tmpName === ''
            || !is_uploaded_file($tmpName)
        ) {
            throw new RuntimeException('File upload tidak valid.');
        }

        if ($fileSize <= 0 || $fileSize > self::MAX_IMAGE_SIZE) {
            throw new RuntimeException('Ukuran gambar maksimal 2 MB.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmpName);

        if (!isset(self::ALLOWED_IMAGE_TYPES[$mimeType])) {
            throw new RuntimeException(
                'Format gambar harus JPG, PNG, atau WebP.'
            );
        }

        $imageInfo = @getimagesize($tmpName);

        if (
            $imageInfo === false
            || ($imageInfo['mime'] ?? '') !== $mimeType
        ) {
            throw new RuntimeException('File yang diunggah bukan gambar yang valid.');
        }

        $extension = self::ALLOWED_IMAGE_TYPES[$mimeType];
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        $projectRoot = dirname(__DIR__, 2);
        $uploadDirectory = $projectRoot . '/public' . self::IMAGE_DIRECTORY;

        if (
            !is_dir($uploadDirectory)
            && !mkdir($uploadDirectory, 0755, true)
            && !is_dir($uploadDirectory)
        ) {
            throw new RuntimeException('Folder penyimpanan gambar gagal dibuat.');
        }

        if (!is_writable($uploadDirectory)) {
            throw new RuntimeException('Folder penyimpanan gambar tidak memiliki izin tulis.');
        }

        $destination = $uploadDirectory . $filename;

        if (!move_uploaded_file($tmpName, $destination)) {
            throw new RuntimeException('Gagal menyimpan gambar produk.');
        }

        return self::IMAGE_DIRECTORY . $filename;
    }

    /**
     * Menghapus gambar produk yang tersimpan di folder produk.
     */
    private function deleteImage(?string $imagePath): void
    {
        if (
            !$imagePath
            || !str_starts_with($imagePath, self::IMAGE_DIRECTORY)
        ) {
            return;
        }

        $filename = substr($imagePath, strlen(self::IMAGE_DIRECTORY));

        // Hanya izinkan nama file acak dengan ekstensi gambar yang didukung.
        if (!preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $filename)) {
            return;
        }

        $projectRoot = dirname(__DIR__, 2);
        $filePath = $projectRoot . '/public' . self::IMAGE_DIRECTORY . $filename;

        if (is_file($filePath) && !is_link($filePath)) {
            @unlink($filePath);
        }
    }

    private function verifyCsrf(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            http_response_code(419);
            exit('419 - Invalid CSRF Token');
        }
    }
}
