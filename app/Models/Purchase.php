<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use RuntimeException;
use Throwable;

final class Purchase
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Mengambil seluruh Purchase Order.
     */
    public function all(): array
    {
        $stmt = $this->db->query(
            "SELECT
                p.*,
                s.name AS supplier_name,
                u.name AS created_by_name,
                COUNT(pi.id) AS total_items,
                COALESCE(SUM(pi.quantity * pi.unit_cost), 0) AS total_amount
             FROM purchases p
             JOIN suppliers s ON s.id = p.supplier_id
             JOIN users u ON u.id = p.created_by
             LEFT JOIN purchase_items pi ON pi.purchase_id = p.id
             GROUP BY p.id
             ORDER BY p.created_at DESC, p.id DESC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil detail Purchase Order beserta informasi penerimaan.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT
                p.*,
                s.name AS supplier_name,
                s.supplier_code,
                s.phone AS supplier_phone,
                s.email AS supplier_email,
                u.name AS created_by_name
             FROM purchases p
             JOIN suppliers s ON s.id = p.supplier_id
             JOIN users u ON u.id = p.created_by
             WHERE p.id = :id
             LIMIT 1"
        );

        $stmt->execute(['id' => $id]);

        $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$purchase) {
            return null;
        }

        $items = $this->db->prepare(
            "SELECT
                pi.*,
                i.ingredient_code,
                i.name AS ingredient_name,
                i.unit,
                COALESCE(
                    (
                        SELECT SUM(pri.quantity)
                        FROM purchase_receipt_items pri
                        JOIN purchase_receipts pr
                            ON pr.id = pri.receipt_id
                        WHERE pri.purchase_item_id = pi.id
                          AND pr.purchase_id = :receipt_purchase_id
                    ),
                    0
                ) AS received_quantity
             FROM purchase_items pi
             JOIN ingredients i ON i.id = pi.ingredient_id
             WHERE pi.purchase_id = :purchase_id
             ORDER BY i.name ASC"
        );

        $items->execute([
            'receipt_purchase_id' => $id,
            'purchase_id' => $id,
        ]);

        $purchase['items'] = $items->fetchAll(PDO::FETCH_ASSOC);

        $totalAmount = 0.0;

        foreach ($purchase['items'] as &$item) {
            $ordered = (float) $item['quantity'];
            $received = (float) $item['received_quantity'];

            $item['remaining_quantity'] = max(
                0,
                round($ordered - $received, 3)
            );

            $totalAmount += $ordered * (float) $item['unit_cost'];
        }

        unset($item);

        $purchase['total_amount'] = $totalAmount;

        return $purchase;
    }

    /**
     * Membuat Purchase Order beserta item.
     */
    public function create(array $data, array $items): int
    {
        if (empty($items)) {
            throw new RuntimeException(
                'Purchase Order harus memiliki minimal satu bahan baku.'
            );
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO purchases (
                    purchase_number,
                    supplier_id,
                    created_by,
                    purchase_date,
                    expected_date,
                    status,
                    notes
                ) VALUES (
                    :purchase_number,
                    :supplier_id,
                    :created_by,
                    :purchase_date,
                    :expected_date,
                    'draft',
                    :notes
                )"
            );

            $stmt->execute([
                'purchase_number' => $data['purchase_number'],
                'supplier_id' => $data['supplier_id'],
                'created_by' => $data['created_by'],
                'purchase_date' => $data['purchase_date'],
                'expected_date' => $data['expected_date'] ?: null,
                'notes' => $data['notes'] ?: null,
            ]);

            $purchaseId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare(
                "INSERT INTO purchase_items (
                    purchase_id,
                    ingredient_id,
                    quantity,
                    unit_cost
                ) VALUES (
                    :purchase_id,
                    :ingredient_id,
                    :quantity,
                    :unit_cost
                )"
            );

            foreach ($items as $item) {
                $itemStmt->execute([
                    'purchase_id' => $purchaseId,
                    'ingredient_id' => $item['ingredient_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                ]);
            }

            $this->db->commit();

            return $purchaseId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Memperbarui Purchase Order yang masih draft.
     */
    public function updateDraft(
        int $id,
        array $data,
        array $items
    ): bool {
        if (empty($items)) {
            throw new RuntimeException(
                'Purchase Order harus memiliki minimal satu bahan baku.'
            );
        }

        $this->db->beginTransaction();

        try {
            $check = $this->db->prepare(
                "SELECT id
                 FROM purchases
                 WHERE id = :id
                   AND status = 'draft'
                 FOR UPDATE"
            );

            $check->execute(['id' => $id]);

            if (!$check->fetchColumn()) {
                throw new RuntimeException(
                    'Purchase Order tidak ditemukan atau tidak dapat diedit.'
                );
            }

            $stmt = $this->db->prepare(
                "UPDATE purchases
                 SET
                    supplier_id = :supplier_id,
                    purchase_date = :purchase_date,
                    expected_date = :expected_date,
                    notes = :notes
                 WHERE id = :id
                   AND status = 'draft'"
            );

            $stmt->execute([
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'expected_date' => $data['expected_date'] ?: null,
                'notes' => $data['notes'] ?: null,
                'id' => $id,
            ]);

            $deleteItems = $this->db->prepare(
                "DELETE FROM purchase_items
                 WHERE purchase_id = :purchase_id"
            );

            $deleteItems->execute(['purchase_id' => $id]);

            $itemStmt = $this->db->prepare(
                "INSERT INTO purchase_items (
                    purchase_id,
                    ingredient_id,
                    quantity,
                    unit_cost
                ) VALUES (
                    :purchase_id,
                    :ingredient_id,
                    :quantity,
                    :unit_cost
                )"
            );

            foreach ($items as $item) {
                $itemStmt->execute([
                    'purchase_id' => $id,
                    'ingredient_id' => $item['ingredient_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                ]);
            }

            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Mencatat penerimaan barang dari Purchase Order.
     *
     * Mendukung penerimaan parsial dan bertahap.
     * Stok dan stock movement diperbarui secara atomik.
     */
    public function receive(
        int $purchaseId,
        int $userId,
        array $items,
        ?string $notes = null
    ): int {
        if (
            $purchaseId < 1 ||
            $userId < 1 ||
            empty($items)
        ) {
            throw new RuntimeException(
                'Data penerimaan barang tidak valid.'
            );
        }

        $this->db->beginTransaction();

        try {
            /*
             * 1. Kunci Purchase Order.
             */
            $purchaseStmt = $this->db->prepare(
                "SELECT
                    id,
                    purchase_number,
                    status
                 FROM purchases
                 WHERE id = :id
                 FOR UPDATE"
            );

            $purchaseStmt->execute([
                'id' => $purchaseId,
            ]);

            $purchase = $purchaseStmt->fetch(PDO::FETCH_ASSOC);

            if (!$purchase) {
                throw new RuntimeException(
                    'Purchase Order tidak ditemukan.'
                );
            }

            if (!in_array(
                $purchase['status'],
                ['ordered', 'partially_received'],
                true
            )) {
                throw new RuntimeException(
                    'Purchase Order belum dapat menerima barang.'
                );
            }

            /*
             * 2. Normalisasi dan validasi input.
             */
            $normalizedItems = [];

            foreach ($items as $item) {
                if (!is_array($item)) {
                    throw new RuntimeException(
                        'Format item penerimaan tidak valid.'
                    );
                }

                $itemId = (int) ($item['purchase_item_id'] ?? 0);
                $quantity = $item['quantity'] ?? null;

                if (
                    $itemId < 1 ||
                    !is_numeric($quantity) ||
                    !is_finite((float) $quantity) ||
                    (float) $quantity <= 0 ||
                    (float) $quantity > 999999999999.999
                ) {
                    throw new RuntimeException(
                        'Jumlah barang yang diterima tidak valid.'
                    );
                }

                if (isset($normalizedItems[$itemId])) {
                    throw new RuntimeException(
                        'Item penerimaan tidak boleh duplikat.'
                    );
                }

                $normalizedItems[$itemId] = (float) $quantity;
            }

            /*
             * 3. Siapkan query.
             */
            $itemStmt = $this->db->prepare(
                "SELECT
                    pi.id,
                    pi.ingredient_id,
                    pi.quantity,
                    i.name AS ingredient_name,
                    i.current_stock,
                    i.is_active
                 FROM purchase_items pi
                 JOIN ingredients i ON i.id = pi.ingredient_id
                 WHERE pi.id = :item_id
                   AND pi.purchase_id = :purchase_id
                 FOR UPDATE"
            );

            $receivedStmt = $this->db->prepare(
                "SELECT COALESCE(SUM(pri.quantity), 0)
                 FROM purchase_receipt_items pri
                 JOIN purchase_receipts pr
                    ON pr.id = pri.receipt_id
                 WHERE pri.purchase_item_id = :item_id
                   AND pr.purchase_id = :purchase_id"
            );

            $receiptStmt = $this->db->prepare(
                "INSERT INTO purchase_receipts (
                    receipt_number,
                    purchase_id,
                    received_by,
                    notes
                 ) VALUES (
                    :receipt_number,
                    :purchase_id,
                    :received_by,
                    :notes
                 )"
            );

            $receiptItemStmt = $this->db->prepare(
                "INSERT INTO purchase_receipt_items (
                    receipt_id,
                    purchase_item_id,
                    quantity
                 ) VALUES (
                    :receipt_id,
                    :purchase_item_id,
                    :quantity
                 )"
            );

            $stockUpdateStmt = $this->db->prepare(
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
                    'in',
                    :quantity,
                    :stock_before,
                    :stock_after,
                    :notes
                 )"
            );

            /*
             * 4. Buat nomor penerimaan.
             */
            $receiptNumber = 'RCV-' .
                date('Ymd-His') . '-' .
                strtoupper(bin2hex(random_bytes(3)));

            $cleanNotes = $notes !== null && trim($notes) !== ''
                ? trim($notes)
                : null;

            $receiptStmt->execute([
                'receipt_number' => $receiptNumber,
                'purchase_id' => $purchaseId,
                'received_by' => $userId,
                'notes' => $cleanNotes,
            ]);

            $receiptId = (int) $this->db->lastInsertId();

            /*
             * 5. Proses setiap item penerimaan.
             */
            foreach ($normalizedItems as $itemId => $quantity) {
                $itemStmt->execute([
                    'item_id' => $itemId,
                    'purchase_id' => $purchaseId,
                ]);

                $purchaseItem = $itemStmt->fetch(PDO::FETCH_ASSOC);

                if (!$purchaseItem) {
                    throw new RuntimeException(
                        'Item Purchase Order tidak ditemukan.'
                    );
                }

                if (!(bool) $purchaseItem['is_active']) {
                    throw new RuntimeException(
                        'Bahan baku ' .
                        $purchaseItem['ingredient_name'] .
                        ' tidak aktif.'
                    );
                }

                /*
                 * Hitung jumlah yang sudah diterima.
                 */
                $receivedStmt->execute([
                    'item_id' => $itemId,
                    'purchase_id' => $purchaseId,
                ]);

                $previouslyReceived = (float) $receivedStmt->fetchColumn();

                $ordered = (float) $purchaseItem['quantity'];

                $remaining = max(
                    0,
                    round($ordered - $previouslyReceived, 3)
                );

                if ($quantity > $remaining + 0.0000001) {
                    throw new RuntimeException(
                        'Jumlah penerimaan melebihi sisa pesanan untuk ' .
                        $purchaseItem['ingredient_name'] . '.'
                    );
                }

                /*
                 * Hitung stok baru.
                 */
                $before = (float) $purchaseItem['current_stock'];

                $after = round($before + $quantity, 3);

                if ($after > 999999999999.999) {
                    throw new RuntimeException(
                        'Stok bahan baku melebihi batas maksimum.'
                    );
                }

                /*
                 * Simpan detail penerimaan.
                 */
                $receiptItemStmt->execute([
                    'receipt_id' => $receiptId,
                    'purchase_item_id' => $itemId,
                    'quantity' => number_format(
                        $quantity,
                        3,
                        '.',
                        ''
                    ),
                ]);

                /*
                 * Perbarui stok bahan baku.
                 */
                $stockUpdateStmt->execute([
                    'stock' => number_format(
                        $after,
                        3,
                        '.',
                        ''
                    ),
                    'id' => $purchaseItem['ingredient_id'],
                ]);

                /*
                 * Catat stock movement.
                 */
                $movementNotes = 'Penerimaan ' .
                    $receiptNumber .
                    ' - PO ' . $purchase['purchase_number'];

                if ($cleanNotes !== null) {
                    $movementNotes .= ' - ' . $cleanNotes;
                }

                $movementStmt->execute([
                    'ingredient_id' => $purchaseItem['ingredient_id'],
                    'user_id' => $userId,
                    'quantity' => number_format(
                        $quantity,
                        3,
                        '.',
                        ''
                    ),
                    'stock_before' => number_format(
                        $before,
                        3,
                        '.',
                        ''
                    ),
                    'stock_after' => number_format(
                        $after,
                        3,
                        '.',
                        ''
                    ),
                    'notes' => $movementNotes,
                ]);
            }

            /*
             * 6. Hitung ulang status Purchase Order.
             */
            $statusStmt = $this->db->prepare(
                "SELECT
                    pi.quantity AS ordered_quantity,
                    COALESCE(received.total_received, 0) AS received_quantity
                 FROM purchase_items pi
                 LEFT JOIN (
                    SELECT
                        pri.purchase_item_id,
                        SUM(pri.quantity) AS total_received
                    FROM purchase_receipt_items pri
                    JOIN purchase_receipts pr
                        ON pr.id = pri.receipt_id
                    WHERE pr.purchase_id = :receipt_purchase_id
                    GROUP BY pri.purchase_item_id
                 ) received
                    ON received.purchase_item_id = pi.id
                 WHERE pi.purchase_id = :purchase_id"
            );

            $statusStmt->execute([
                'receipt_purchase_id' => $purchaseId,
                'purchase_id' => $purchaseId,
            ]);

            $statusRows = $statusStmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($statusRows)) {
                throw new RuntimeException(
                    'Purchase Order tidak memiliki item.'
                );
            }

            $allReceived = true;
            $hasReceived = false;

            foreach ($statusRows as $row) {
                $ordered = (float) $row['ordered_quantity'];
                $received = (float) $row['received_quantity'];

                if ($received > 0) {
                    $hasReceived = true;
                }

                if ($received + 0.0000001 < $ordered) {
                    $allReceived = false;
                }
            }

            if ($allReceived) {
                $newStatus = 'received';
            } elseif ($hasReceived) {
                $newStatus = 'partially_received';
            } else {
                $newStatus = 'ordered';
            }

            $updateStatusStmt = $this->db->prepare(
                "UPDATE purchases
                 SET status = :status
                 WHERE id = :id"
            );

            $updateStatusStmt->execute([
                'status' => $newStatus,
                'id' => $purchaseId,
            ]);

            /*
             * 7. Commit seluruh proses.
             */
            $this->db->commit();

            return $receiptId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Memperbarui status Purchase Order.
     */
    public function updateStatus(int $id, string $status): bool
    {
        $allowedStatuses = [
            'draft',
            'ordered',
            'partially_received',
            'received',
            'cancelled',
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new RuntimeException(
                'Status Purchase Order tidak valid.'
            );
        }

        $stmt = $this->db->prepare(
            "UPDATE purchases
             SET status = :status
             WHERE id = :id"
        );

        return $stmt->execute([
            'status' => $status,
            'id' => $id,
        ]);
    }
}
