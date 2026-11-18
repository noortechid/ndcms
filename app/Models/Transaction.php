<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Transaction
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Mengambil seluruh transaksi dengan filter.
     * Jika userId diberikan, hanya transaksi milik user tersebut.
     */
    public function all(
        array $filters = [],
        ?int $userId = null
    ): array {
        [$where, $params] = $this->buildFilters($filters, $userId);

        $stmt = $this->db->prepare(
            "SELECT
                t.*,
                u.username AS cashier_username
             FROM transactions t
             INNER JOIN users u ON u.id = t.user_id
             {$where}
             ORDER BY t.created_at DESC, t.id DESC"
        );

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Mengambil ringkasan transaksi berdasarkan filter.
     */
    public function summary(
        array $filters = [],
        ?int $userId = null
    ): array {
        [$where, $params] = $this->buildFilters($filters, $userId);

        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total_transactions,
                COALESCE(
                    SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END),
                    0
                ) AS completed_transactions,
                COALESCE(
                    SUM(CASE WHEN t.status = 'voided' THEN 1 ELSE 0 END),
                    0
                ) AS voided_transactions,
                COALESCE(
                    SUM(
                        CASE
                            WHEN t.status = 'completed' THEN t.total
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_sales
             FROM transactions t
             INNER JOIN users u ON u.id = t.user_id
             {$where}"
        );

        $stmt->execute($params);

        return $stmt->fetch() ?: [
            'total_transactions' => 0,
            'completed_transactions' => 0,
            'voided_transactions' => 0,
            'total_sales' => 0,
        ];
    }

    /**
     * Membuat kondisi filter yang digunakan bersama.
     */
    private function buildFilters(
        array $filters,
        ?int $userId
    ): array {
        $conditions = [];
        $params = [];

        if ($userId !== null) {
            $conditions[] = 't.user_id = :user_id';
            $params['user_id'] = $userId;
        }

        if (!empty($filters['keyword'])) {
            $conditions[] = '(
                t.transaction_code LIKE :keyword_code
                OR u.username LIKE :keyword_cashier
            )';

            $keyword = '%' . trim($filters['keyword']) . '%';

            $params['keyword_code'] = $keyword;
            $params['keyword_cashier'] = $keyword;
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = 't.created_at >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = 't.created_at < DATE_ADD(:date_to, INTERVAL 1 DAY)';
            $params['date_to'] = $filters['date_to'];
        }

        if (in_array(
            $filters['status'] ?? '',
            ['completed', 'voided'],
            true
        )) {
            $conditions[] = 't.status = :status';
            $params['status'] = $filters['status'];
        }

        if (in_array(
            $filters['payment_method'] ?? '',
            ['cash', 'qris', 'debit'],
            true
        )) {
            $conditions[] = 't.payment_method = :payment_method';
            $params['payment_method'] = $filters['payment_method'];
        }

        $where = $conditions
            ? 'WHERE ' . implode(' AND ', $conditions)
            : '';

        return [$where, $params];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT
                t.*,
                u.username AS cashier_username
             FROM transactions t
             INNER JOIN users u ON u.id = t.user_id
             WHERE t.id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);

        $transaction = $stmt->fetch();

        return $transaction ?: null;
    }

    public function items(int $transactionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                transaction_id,
                product_id,
                product_name,
                quantity,
                unit_price,
                subtotal
             FROM transaction_items
             WHERE transaction_id = :transaction_id
             ORDER BY id ASC'
        );

        $stmt->execute([
            'transaction_id' => $transactionId,
        ]);

        return $stmt->fetchAll();
    }
}
