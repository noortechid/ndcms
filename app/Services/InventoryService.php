<?php

namespace App\Services;

use App\Core\Database;
use App\Models\StockMovement;
use InvalidArgumentException;
use PDO;
use Throwable;

class InventoryService
{
    private PDO $db;
    private StockMovement $movements;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->movements = new StockMovement();
    }

    /**
     * Tambah stok bahan baku.
     */
    public function stockIn(
        int $ingredientId,
        int $userId,
        float $quantity,
        ?string $notes = null
    ): int {
        return $this->move(
            $ingredientId,
            $userId,
            'in',
            $quantity,
            $notes
        );
    }

    /**
     * Kurangi stok bahan baku.
     */
    public function stockOut(
        int $ingredientId,
        int $userId,
        float $quantity,
        ?string $notes = null
    ): int {
        return $this->move(
            $ingredientId,
            $userId,
            'out',
            $quantity,
            $notes
        );
    }

    /**
     * Koreksi stok berdasarkan hasil stock opname.
     *
     * Quantity merupakan stok aktual hasil penghitungan,
     * bukan jumlah selisih.
     */
    public function adjustStock(
        int $ingredientId,
        int $userId,
        float $actualStock,
        ?string $notes = null
    ): int {
        if ($actualStock < 0) {
            throw new InvalidArgumentException(
                'Stok aktual tidak boleh negatif.'
            );
        }

        return $this->move(
            $ingredientId,
            $userId,
            'adjustment',
            $actualStock,
            $notes
        );
    }

    /**
     * Proses perubahan stok.
     */
    private function move(
        int $ingredientId,
        int $userId,
        string $type,
        float $quantity,
        ?string $notes
    ): int {
        if ($ingredientId < 1 || $userId < 1) {
            throw new InvalidArgumentException(
                'Ingredient atau pengguna tidak valid.'
            );
        }

        if (!is_finite($quantity) || $quantity <= 0 && $type !== 'adjustment') {
            throw new InvalidArgumentException(
                'Jumlah stok harus lebih dari nol.'
            );
        }

        if (!is_finite($quantity) || $quantity < 0) {
            throw new InvalidArgumentException(
                'Jumlah stok tidak valid.'
            );
        }

        if (!in_array($type, ['in', 'out', 'adjustment'], true)) {
            throw new InvalidArgumentException(
                'Jenis pergerakan stok tidak valid.'
            );
        }

        if ($type === 'adjustment' && $notes === null) {
            throw new InvalidArgumentException(
                'Catatan wajib diisi untuk koreksi stok.'
            );
        }

        if ($notes !== null) {
            $notes = trim($notes);

            if ($notes === '') {
                $notes = null;
            }
        }

        if ($type === 'adjustment' && $notes === null) {
            throw new InvalidArgumentException(
                'Catatan wajib diisi untuk koreksi stok.'
            );
        }

        try {
            $this->db->beginTransaction();

            // Kunci baris ingredient selama proses perubahan stok.
            $stmt = $this->db->prepare(
                'SELECT id, current_stock, is_active
                 FROM ingredients
                 WHERE id = :id
                 FOR UPDATE'
            );

            $stmt->execute([
                'id' => $ingredientId,
            ]);

            $ingredient = $stmt->fetch();

            if (!$ingredient) {
                throw new InvalidArgumentException(
                    'Bahan baku tidak ditemukan.'
                );
            }

            if (!(bool) $ingredient['is_active']) {
                throw new InvalidArgumentException(
                    'Bahan baku tidak aktif.'
                );
            }

            $before = (float) $ingredient['current_stock'];

            if ($type === 'in') {
                $after = $before + $quantity;
            } elseif ($type === 'out') {
                if ($quantity > $before) {
                    throw new InvalidArgumentException(
                        'Stok tidak mencukupi.'
                    );
                }

                $after = $before - $quantity;
            } else {
                $after = $quantity;
            }

            // Pastikan nilai tidak melebihi kapasitas DECIMAL(15,3).
            if ($after > 999999999999.999) {
                throw new InvalidArgumentException(
                    'Jumlah stok melebihi batas maksimum.'
                );
            }

            // Simpan stok terbaru.
            $update = $this->db->prepare(
                'UPDATE ingredients
                 SET current_stock = :stock
                 WHERE id = :id'
            );

            $update->execute([
                'stock' => number_format($after, 3, '.', ''),
                'id' => $ingredientId,
            ]);

            // Catat riwayat perubahan stok.
            $movementId = $this->movements->create([
                'ingredient_id' => $ingredientId,
                'user_id' => $userId,
                'movement_type' => $type,
                'quantity' => number_format(
                    $type === 'adjustment'
                        ? abs($after - $before)
                        : $quantity,
                    3,
                    '.',
                    ''
                ),
                'stock_before' => number_format($before, 3, '.', ''),
                'stock_after' => number_format($after, 3, '.', ''),
                'notes' => $notes,
            ]);

            $this->db->commit();

            return $movementId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }
}
