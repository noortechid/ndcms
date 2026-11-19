<?php

namespace App\Services;

use App\Core\Database;
use InvalidArgumentException;
use PDO;
use Throwable;

class TransactionService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Membuat transaksi penjualan beserta pengurangan stok.
     *
     * $data:
     * - user_id
     * - shift_id
     * - items: [['product_id' => 1, 'quantity' => 2]]
     * - discount
     * - payment_method
     * - amount_paid
     */
    public function create(array $data): int
    {
        $userId = (int) ($data['user_id'] ?? 0);
        $shiftId = (int) ($data['shift_id'] ?? 0);
        $items = $data['items'] ?? [];
        $paymentMethod = $data['payment_method'] ?? '';
        $discount = $this->toCents($data['discount'] ?? '0');
        $amountPaid = $this->toCents($data['amount_paid'] ?? '0');

        if ($userId < 1 || $shiftId < 1) {
            throw new InvalidArgumentException('Kasir atau shift tidak valid.');
        }

        if (!is_array($items) || empty($items)) {
            throw new InvalidArgumentException('Keranjang masih kosong.');
        }

        if (!in_array($paymentMethod, ['cash', 'qris', 'debit'], true)) {
            throw new InvalidArgumentException('Metode pembayaran tidak valid.');
        }

        if ($discount < 0 || $amountPaid < 0) {
            throw new InvalidArgumentException('Nominal pembayaran tidak valid.');
        }

        $this->db->beginTransaction();

        try {
            // 1. Kunci dan validasi shift.
            $stmt = $this->db->prepare(
                "SELECT id, user_id, status
                 FROM cashier_shifts
                 WHERE id = :id
                 FOR UPDATE"
            );

            $stmt->execute(['id' => $shiftId]);
            $shift = $stmt->fetch();

            if (
                !$shift ||
                (int) $shift['user_id'] !== $userId ||
                $shift['status'] !== 'open'
            ) {
                throw new InvalidArgumentException(
                    'Shift kasir tidak ditemukan atau sudah ditutup.'
                );
            }

            // 2. Validasi dan normalisasi keranjang.
            $cart = [];

foreach ($items as $item) {
    if (!is_array($item)) {
        throw new InvalidArgumentException(
            'Format item keranjang tidak valid.'
        );
    }

    $productId = filter_var(
        $item['product_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $quantity = filter_var(
        $item['quantity'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (
        !$productId || $productId < 1 ||
        !$quantity || $quantity < 1 || $quantity > 999
    ) {
        throw new InvalidArgumentException(
            'Produk atau jumlah pembelian tidak valid.'
        );
    }

    if (isset($cart[$productId])) {
        throw new InvalidArgumentException(
            'Produk yang sama tidak boleh muncul dua kali dalam keranjang.'
        );
    }

    $cart[$productId] = $quantity;
}

            ksort($cart);

            // 3. Kunci dan validasi produk.
            $products = [];
            $subtotal = 0;

$productStmt = $this->db->prepare(
    "SELECT
        p.id,
        p.name,
        p.price,
        p.is_active,
        p.is_available,
        p.requires_recipe,
        c.is_active AS category_is_active
     FROM products p
     INNER JOIN categories c ON c.id = p.category_id
     WHERE p.id = :id
     FOR UPDATE"
);

            foreach ($cart as $productId => $quantity) {
                $productStmt->execute(['id' => $productId]);
                $product = $productStmt->fetch();

if (
    !$product ||
    !(bool) $product['is_active'] ||
    !(bool) $product['is_available'] ||
    !(bool) $product['category_is_active']
) {

                    throw new InvalidArgumentException(
                        'Salah satu produk tidak aktif atau tidak tersedia.'
                    );
                }

                $unitPrice = $this->toCents($product['price']);
                $lineSubtotal = $unitPrice * $quantity;

$products[$productId] = [
    'name' => $product['name'],
    'quantity' => $quantity,
    'unit_price' => $unitPrice,
    'subtotal' => $lineSubtotal,
    'requires_recipe' => (bool) $product['requires_recipe'],
];
                $subtotal += $lineSubtotal;
            }

            if ($discount > $subtotal) {
                throw new InvalidArgumentException(
                    'Diskon tidak boleh melebihi subtotal.'
                );
            }

            $total = $subtotal - $discount;

            if ($total <= 0) {
                throw new InvalidArgumentException(
                    'Total transaksi harus lebih dari nol.'
                );
            }

            if ($paymentMethod === 'cash' && $amountPaid < $total) {
                throw new InvalidArgumentException(
                    'Uang pembayaran masih kurang.'
                );
            }

            if ($paymentMethod !== 'cash' && $amountPaid !== $total) {
                throw new InvalidArgumentException(
                    'Nominal pembayaran non-tunai harus sama dengan total.'
                );
            }

            $change = $paymentMethod === 'cash'
                ? $amountPaid - $total
                : 0;

            // 4. Hitung kebutuhan bahan baku dari resep.
            $requiredIngredients = [];

$recipeStmt = $this->db->prepare(
    "SELECT
        r.id AS recipe_id,
        ri.ingredient_id,
        ri.quantity
     FROM recipes r
     LEFT JOIN recipe_items ri
        ON ri.recipe_id = r.id
     WHERE r.product_id = :product_id
     ORDER BY ri.ingredient_id"
);

foreach ($cart as $productId => $quantity) {
    $recipeStmt->execute(['product_id' => $productId]);

    $recipeItems = $recipeStmt->fetchAll();

    // Produk non-racikan boleh dijual tanpa resep.
    if (!$products[$productId]['requires_recipe']) {
        continue;
    }

    // Produk racikan wajib memiliki resep dan bahan baku.
    if (
        empty($recipeItems) ||
        $recipeItems[0]['recipe_id'] === null ||
        $recipeItems[0]['ingredient_id'] === null
    ) {
        throw new InvalidArgumentException(
            'Produk ' . $products[$productId]['name'] .
            ' wajib memiliki resep dengan minimal satu bahan baku.'
        );
    }

    foreach ($recipeItems as $recipeItem) {
        $ingredientId = (int) $recipeItem['ingredient_id'];

        $required = round(
            (float) $recipeItem['quantity'] * $quantity,
            3
        );

        $requiredIngredients[$ingredientId] = round(
            ($requiredIngredients[$ingredientId] ?? 0) + $required,
            3
        );
    }
}

            ksort($requiredIngredients);

            $ingredientStmt = $this->db->prepare(
                "SELECT id, name, current_stock, is_active
                 FROM ingredients
                 WHERE id = :id
                 FOR UPDATE"
            );

            $updateStockStmt = $this->db->prepare(
                "UPDATE ingredients
                 SET current_stock = :stock
                 WHERE id = :id"
            );

            $movementStmt = $this->db->prepare(
                "INSERT INTO stock_movements (
                    ingredient_id,
                    user_id,
                    movement_type,
                    quantity,
                    stock_before,
                    stock_after,
                    notes
                ) VALUES (
                    :ingredient_id,
                    :user_id,
                    'out',
                    :quantity,
                    :stock_before,
                    :stock_after,
                    :notes
                )"
            );

            // Periksa semua stok terlebih dahulu.
            $stockChanges = [];

            foreach ($requiredIngredients as $ingredientId => $required) {
                $ingredientStmt->execute(['id' => $ingredientId]);
                $ingredient = $ingredientStmt->fetch();

                if (!$ingredient || !(bool) $ingredient['is_active']) {
                    throw new InvalidArgumentException(
                        'Bahan baku resep tidak ditemukan atau tidak aktif.'
                    );
                }

                $before = (float) $ingredient['current_stock'];

                if ($before + 0.0000001 < $required) {
                    throw new InvalidArgumentException(
                        'Stok ' . $ingredient['name'] .
                        ' tidak mencukupi. Dibutuhkan ' .
                        rtrim(rtrim(number_format($required, 3, '.', ''), '0'), '.') .
                        ', tersedia ' .
                        rtrim(rtrim(number_format($before, 3, '.', ''), '0'), '.') .
                        '.'
                    );
                }

                $stockChanges[$ingredientId] = [
                    'before' => $before,
                    'after' => round($before - $required, 3),
                    'quantity' => $required,
                ];
            }

            // 6. Buat kode transaksi.
            $transactionCode = 'TRX-' .
                date('Ymd-His') . '-' .
                strtoupper(bin2hex(random_bytes(3)));

            // 7. Simpan transaksi utama.
            $transactionStmt = $this->db->prepare(
                "INSERT INTO transactions (
                    transaction_code,
                    user_id,
                    shift_id,
                    subtotal,
                    discount,
                    total,
                    payment_method,
                    amount_paid,
                    change_amount,
                    status
                ) VALUES (
                    :code,
                    :user_id,
                    :shift_id,
                    :subtotal,
                    :discount,
                    :total,
                    :payment_method,
                    :amount_paid,
                    :change_amount,
                    'completed'
                )"
            );

            $transactionStmt->execute([
                'code' => $transactionCode,
                'user_id' => $userId,
                'shift_id' => $shiftId,
                'subtotal' => $this->fromCents($subtotal),
                'discount' => $this->fromCents($discount),
                'total' => $this->fromCents($total),
                'payment_method' => $paymentMethod,
                'amount_paid' => $this->fromCents($amountPaid),
                'change_amount' => $this->fromCents($change),
            ]);

            $transactionId = (int) $this->db->lastInsertId();

            // 8. Simpan detail transaksi.
            $itemStmt = $this->db->prepare(
                "INSERT INTO transaction_items (
                    transaction_id,
                    product_id,
                    product_name,
                    quantity,
                    unit_price,
                    subtotal
                ) VALUES (
                    :transaction_id,
                    :product_id,
                    :product_name,
                    :quantity,
                    :unit_price,
                    :subtotal
                )"
            );

            foreach ($products as $productId => $product) {
                $itemStmt->execute([
                    'transaction_id' => $transactionId,
                    'product_id' => $productId,
                    'product_name' => $product['name'],
                    'quantity' => $product['quantity'],
                    'unit_price' => $this->fromCents($product['unit_price']),
                    'subtotal' => $this->fromCents($product['subtotal']),
                ]);
            }

            // 9. Kurangi stok dan catat pergerakannya.
            foreach ($stockChanges as $ingredientId => $changeData) {
                $updateStockStmt->execute([
                    'stock' => $changeData['after'],
                    'id' => $ingredientId,
                ]);

                $movementStmt->execute([
                    'ingredient_id' => $ingredientId,
                    'user_id' => $userId,
                    'quantity' => $changeData['quantity'],
                    'stock_before' => $changeData['before'],
                    'stock_after' => $changeData['after'],
                    'notes' => 'Penjualan ' . $transactionCode,
                ]);
            }

            // 10. Perbarui rekap shift.
            $shiftColumn = $paymentMethod === 'cash'
                ? 'cash_sales'
                : 'non_cash_sales';

            $shiftUpdate = $this->db->prepare(
                "UPDATE cashier_shifts
                 SET {$shiftColumn} = {$shiftColumn} + :total
                 WHERE id = :id"
            );

            $shiftUpdate->execute([
                'total' => $this->fromCents($total),
                'id' => $shiftId,
            ]);

            $this->db->commit();

            return $transactionId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Konversi nominal uang menjadi integer sen.
     */
    private function toCents(mixed $value): int
    {
        $value = (string) $value;

        if (!preg_match('/^\d{1,13}(\.\d{1,2})?$/', $value)) {
            throw new InvalidArgumentException('Format nominal uang tidak valid.');
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '0');

        return ((int) $whole * 100) +
            (int) str_pad($fraction, 2, '0');
    }

    /**
     * Konversi integer sen menjadi nominal desimal.
     */
    private function fromCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
