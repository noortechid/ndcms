<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Ingredient;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class PurchaseController extends Controller
{
    private Purchase $purchase;
    private Supplier $supplier;
    private Ingredient $ingredient;

    public function __construct()
    {
        $this->purchase = new Purchase();
        $this->supplier = new Supplier();
        $this->ingredient = new Ingredient();
    }

    /**
     * Daftar Purchase Order.
     */
    public function index(): void
    {
        $this->view('purchases/index', [
            'purchases' => $this->purchase->all(),
        ]);
    }

    /**
     * Form tambah Purchase Order.
     */
    public function create(): void
    {
        $this->view('purchases/create', [
            'suppliers' => $this->supplier->active(),
            'ingredients' => $this->ingredient->all(),
        ]);
    }

    /**
     * Simpan Purchase Order baru.
     */
    public function store(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            exit('Invalid CSRF token.');
        }

        try {
            $data = $this->validatePurchaseData($_POST);

            $purchaseId = $this->purchase->create(
                [
                    'purchase_number' => $this->generatePurchaseNumber(),
                    'supplier_id' => $data['supplier_id'],
                    'created_by' => (int) Auth::user()['id'],
                    'purchase_date' => $data['purchase_date'],
                    'expected_date' => $data['expected_date'],
                    'notes' => $data['notes'],
                ],
                $data['items']
            );

            $_SESSION['flash_success'] = 'Purchase Order berhasil dibuat.';

            $this->redirect('/purchases/show?id=' . $purchaseId);
        } catch (InvalidArgumentException | RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $this->redirect('/purchases/create');
} catch (Throwable $e) {
    error_log('[PurchaseController::store] ' . $e->getMessage());
    error_log($e->getTraceAsString());

    $_SESSION['flash_error'] = 'Gagal membuat Purchase Order.';
    $this->redirect('/purchases/create');
}
    }

    /**
     * Detail Purchase Order.
     */
    public function show(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            $this->redirect('/purchases');
            return;
        }

        $purchase = $this->purchase->find($id);

        if (!$purchase) {
            $_SESSION['flash_error'] = 'Purchase Order tidak ditemukan.';
            $this->redirect('/purchases');
            return;
        }

        $this->view('purchases/show', [
            'purchase' => $purchase,
        ]);
    }

public function receive(): void
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        $this->redirect('/purchases');
        return;
    }

    $purchase = $this->purchase->find($id);

    if (
        !$purchase ||
        !in_array(
            $purchase['status'],
            ['ordered', 'partially_received'],
            true
        )
    ) {
        $_SESSION['flash_error'] =
            'Purchase Order tidak tersedia untuk penerimaan barang.';

        $this->redirect('/purchases/show?id=' . $id);
        return;
    }

    $this->view('purchases/receive', [
        'purchase' => $purchase,
    ]);
}

public function storeReceipt(): void
{
    if (!Csrf::verify($_POST['_token'] ?? '')) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }

    $id = filter_var(
        $_POST['purchase_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$id || $id < 1) {
        $_SESSION['flash_error'] = 'Purchase Order tidak valid.';
        $this->redirect('/purchases');
        return;
    }

    try {
        $items = $_POST['items'] ?? [];

        if (!is_array($items) || empty($items)) {
            throw new InvalidArgumentException(
                'Masukkan minimal satu jumlah barang yang diterima.'
            );
        }

        $notes = trim($_POST['notes'] ?? '');

        if (strlen($notes) > 5000) {
            throw new InvalidArgumentException(
                'Catatan maksimal 5000 karakter.'
            );
        }

        $receiptId = $this->purchase->receive(
            $id,
            (int) Auth::user()['id'],
            $items,
            $notes !== '' ? $notes : null
        );

        $_SESSION['flash_success'] =
            'Penerimaan barang berhasil dicatat.';

        $this->redirect('/purchases/show?id=' . $id);
    } catch (InvalidArgumentException | RuntimeException $e) {
        $_SESSION['flash_error'] = $e->getMessage();

        $this->redirect('/purchases/receive?id=' . $id);
    } catch (Throwable $e) {
        error_log(
            '[PurchaseController::storeReceipt] ' . $e->getMessage()
        );

        error_log($e->getTraceAsString());

        $_SESSION['flash_error'] =
            'Gagal menyimpan penerimaan barang.';

        $this->redirect('/purchases/show?id=' . $id);
    }
}

    /**
     * Form edit Purchase Order.
     */
    public function edit(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            $this->redirect('/purchases');
            return;
        }

        $purchase = $this->purchase->find($id);

        if (!$purchase || $purchase['status'] !== 'draft') {
            $_SESSION['flash_error'] = 'Purchase Order tidak dapat diedit.';
            $this->redirect('/purchases');
            return;
        }

        $this->view('purchases/edit', [
            'purchase' => $purchase,
            'suppliers' => $this->supplier->active(),
            'ingredients' => $this->ingredient->all(),
        ]);
    }

    /**
     * Update Purchase Order draft.
     */
    public function update(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            exit('Invalid CSRF token.');
        }

        try {
            $id = filter_var(
                $_POST['id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$id || $id < 1) {
                throw new InvalidArgumentException('Purchase Order tidak valid.');
            }

            $data = $this->validatePurchaseData($_POST);

            $this->purchase->updateDraft(
                $id,
                [
                    'supplier_id' => $data['supplier_id'],
                    'purchase_date' => $data['purchase_date'],
                    'expected_date' => $data['expected_date'],
                    'notes' => $data['notes'],
                ],
                $data['items']
            );

            $_SESSION['flash_success'] = 'Purchase Order berhasil diperbarui.';

            $this->redirect('/purchases/show?id=' . $id);
        } catch (InvalidArgumentException | RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $this->redirect('/purchases');
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = 'Gagal memperbarui Purchase Order.';
            $this->redirect('/purchases');
        }
    }

    /**
     * Ubah status Purchase Order.
     */
    public function status(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            exit('Invalid CSRF token.');
        }

        try {
            $id = filter_var(
                $_POST['id'] ?? null,
                FILTER_VALIDATE_INT
            );

            $status = $_POST['status'] ?? '';

            if (!$id || $id < 1) {
                throw new InvalidArgumentException('Purchase Order tidak valid.');
            }

            $purchase = $this->purchase->find($id);

            if (!$purchase) {
                throw new RuntimeException('Purchase Order tidak ditemukan.');
            }

            if (
                $purchase['status'] !== 'draft' ||
                !in_array($status, ['ordered', 'cancelled'], true)
            ) {
                throw new RuntimeException('Perubahan status tidak diizinkan.');
            }

            $this->purchase->updateStatus($id, $status);

            $_SESSION['flash_success'] = $status === 'ordered'
                ? 'Purchase Order berhasil dikirim ke supplier.'
                : 'Purchase Order berhasil dibatalkan.';

            $this->redirect('/purchases/show?id=' . $id);
        } catch (InvalidArgumentException | RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $this->redirect('/purchases');
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = 'Gagal mengubah status Purchase Order.';
            $this->redirect('/purchases');
        }
    }

    /**
     * Validasi data Purchase Order.
     */
    private function validatePurchaseData(array $input): array
    {
        $supplierId = filter_var(
            $input['supplier_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $purchaseDate = trim($input['purchase_date'] ?? '');
        $expectedDate = trim($input['expected_date'] ?? '');
        $notes = trim($input['notes'] ?? '');

        if (!$supplierId || $supplierId < 1) {
            throw new InvalidArgumentException('Supplier wajib dipilih.');
        }

        $supplier = $this->supplier->find($supplierId);

        if (!$supplier || (int) $supplier['is_active'] !== 1) {
            throw new InvalidArgumentException('Supplier tidak aktif atau tidak ditemukan.');
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $purchaseDate);

        if (!$date || $date->format('Y-m-d') !== $purchaseDate) {
            throw new InvalidArgumentException('Tanggal pembelian tidak valid.');
        }

        if ($expectedDate !== '') {
            $expected = \DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $expectedDate
            );

            if (
                !$expected ||
                $expected->format('Y-m-d') !== $expectedDate ||
                $expectedDate < $purchaseDate
            ) {
                throw new InvalidArgumentException('Tanggal estimasi penerimaan tidak valid.');
            }
        }

        if (strlen($notes) > 5000) {
            throw new InvalidArgumentException('Catatan maksimal 5000 karakter.');
        }

        $rawItems = $input['items'] ?? [];

        if (!is_array($rawItems) || empty($rawItems)) {
            throw new InvalidArgumentException('Tambahkan minimal satu bahan baku.');
        }

        $items = [];
        $usedIngredients = [];

        foreach ($rawItems as $item) {
            if (!is_array($item)) {
                throw new InvalidArgumentException('Data bahan baku tidak valid.');
            }

            $ingredientId = filter_var(
                $item['ingredient_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            $quantity = $item['quantity'] ?? null;
            $unitCost = $item['unit_cost'] ?? null;

            if (!$ingredientId || $ingredientId < 1) {
                throw new InvalidArgumentException('Bahan baku tidak valid.');
            }

            if (
                !is_numeric($quantity) ||
                !is_numeric($unitCost) ||
                !is_finite((float) $quantity) ||
                !is_finite((float) $unitCost) ||
                (float) $quantity <= 0 ||
                (float) $unitCost < 0 ||
                (float) $quantity > 999999999999.999 ||
                (float) $unitCost > 99999999999.9999
            ) {
                throw new InvalidArgumentException('Jumlah atau harga bahan baku tidak valid.');
            }

            if (isset($usedIngredients[$ingredientId])) {
                throw new InvalidArgumentException('Bahan baku tidak boleh duplikat.');
            }

            $ingredient = $this->ingredient->findById($ingredientId);

            if (!$ingredient || (int) $ingredient['is_active'] !== 1) {
                throw new InvalidArgumentException('Bahan baku tidak aktif atau tidak ditemukan.');
            }

            $usedIngredients[$ingredientId] = true;

            $items[] = [
                'ingredient_id' => $ingredientId,
                'quantity' => round((float) $quantity, 3),
                'unit_cost' => round((float) $unitCost, 4),
            ];
        }

        return [
            'supplier_id' => $supplierId,
            'purchase_date' => $purchaseDate,
            'expected_date' => $expectedDate !== '' ? $expectedDate : null,
            'notes' => $notes,
            'items' => $items,
        ];
    }

    /**
     * Membuat nomor Purchase Order.
     */
    private function generatePurchaseNumber(): string
    {
        return 'PO-' . date('Ymd-His') . '-' . strtoupper(
            bin2hex(random_bytes(3))
        );
    }

    private function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}
}
