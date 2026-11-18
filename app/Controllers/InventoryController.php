<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Controller;
use App\Models\Ingredient;
use App\Models\StockMovement;
use App\Services\InventoryService;
use InvalidArgumentException;
use Throwable;

class InventoryController extends Controller
{
    private Ingredient $ingredients;
    private StockMovement $movements;
    private InventoryService $inventory;

    public function __construct()
    {
        $this->ingredients = new Ingredient();
        $this->movements = new StockMovement();
        $this->inventory = new InventoryService();
    }

    /**
     * Halaman utama inventory.
     */
    public function index(): void
    {
        $ingredients = $this->ingredients->all();
        $movements = $this->movements->all(50);

        $totalIngredients = count($ingredients);

$lowStock = array_filter(
    $ingredients,
    fn ($ingredient) =>
        (float) $ingredient['minimum_stock'] > 0 &&
        (float) $ingredient['current_stock'] <=
        (float) $ingredient['minimum_stock']
);

        $this->view('inventory/index', [
            'ingredients' => $ingredients,
            'movements' => $movements,
            'totalIngredients' => $totalIngredients,
            'lowStockCount' => count($lowStock),
        ]);
    }

    /**
     * Tambah stok.
     */
    public function stockIn(): void
    {
        $this->processMovement('in');
    }

    /**
     * Kurangi stok.
     */
    public function stockOut(): void
    {
        $this->processMovement('out');
    }

    /**
     * Koreksi stok.
     */
    public function adjust(): void
    {
        $this->processMovement('adjustment');
    }

    /**
     * Proses seluruh jenis pergerakan stok.
     */
    private function processMovement(string $type): void
    {
        if (!Csrf::verify($_POST['_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Sesi formulir tidak valid. Silakan coba lagi.';
            header('Location: /inventory');
            exit;
        }

        $ingredientId = filter_var(
            $_POST['ingredient_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $quantity = trim((string) ($_POST['quantity'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));

        if (!$ingredientId || $ingredientId < 1) {
            $_SESSION['flash_error'] = 'Bahan baku tidak valid.';
            header('Location: /inventory');
            exit;
        }

        // Maksimal 12 digit sebelum desimal dan 3 digit sesudahnya.
        if (!preg_match('/^\d{1,12}(\.\d{1,3})?$/', $quantity)) {
            $_SESSION['flash_error'] = 'Jumlah stok tidak valid.';
            header('Location: /inventory');
            exit;
        }

        $amount = (float) $quantity;

        if ($type !== 'adjustment' && $amount <= 0) {
            $_SESSION['flash_error'] = 'Jumlah stok harus lebih dari nol.';
            header('Location: /inventory');
            exit;
        }

        if ($type === 'adjustment' && $notes === '') {
            $_SESSION['flash_error'] = 'Catatan wajib diisi untuk koreksi stok.';
            header('Location: /inventory');
            exit;
        }

        try {
            $user = Auth::user();

            if (!$user) {
                throw new InvalidArgumentException(
                    'Sesi pengguna tidak ditemukan.'
                );
            }

            if ($type === 'in') {
                $this->inventory->stockIn(
                    (int) $ingredientId,
                    (int) $user['id'],
                    $amount,
                    $notes ?: null
                );

                $_SESSION['flash_success'] = 'Stok berhasil ditambahkan.';
            } elseif ($type === 'out') {
                $this->inventory->stockOut(
                    (int) $ingredientId,
                    (int) $user['id'],
                    $amount,
                    $notes ?: null
                );

                $_SESSION['flash_success'] = 'Stok berhasil dikurangi.';
            } else {
                $this->inventory->adjustStock(
                    (int) $ingredientId,
                    (int) $user['id'],
                    $amount,
                    $notes
                );

                $_SESSION['flash_success'] = 'Stok berhasil dikoreksi.';
            }
        } catch (InvalidArgumentException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        } catch (Throwable $e) {
            error_log($e->getMessage());

            $_SESSION['flash_error'] = 'Terjadi kesalahan saat memproses stok.';
        }

        header('Location: /inventory');
        exit;
    }
}
