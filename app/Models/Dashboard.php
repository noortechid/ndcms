<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Dashboard
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Ringkasan statistik dashboard.
     */
    public function summary(): array
    {
        $stmt = $this->db->query(
            "SELECT
                (
                    SELECT COALESCE(SUM(total), 0)
                    FROM transactions
                    WHERE status = 'completed'
                    AND DATE(created_at) = CURDATE()
                ) AS today_revenue,

                (
                    SELECT COUNT(*)
                    FROM transactions
                    WHERE status = 'completed'
                    AND DATE(created_at) = CURDATE()
                ) AS today_transactions,

                (
                    SELECT COUNT(*)
                    FROM products
                    WHERE is_active = 1
                ) AS active_products,

                (
                    SELECT COUNT(*)
                    FROM ingredients
                    WHERE is_active = 1
                    AND current_stock <= minimum_stock
                ) AS low_stock_count"
        );

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Penjualan harian selama minggu berjalan.
     */
    public function weeklySales(): array
    {
        $stmt = $this->db->query(
            "SELECT
                DATE(created_at) AS sales_date,
                COALESCE(SUM(total), 0) AS revenue,
                COUNT(*) AS transactions
             FROM transactions
             WHERE status = 'completed'
             AND created_at >= DATE_SUB(
                 CURDATE(),
                 INTERVAL WEEKDAY(CURDATE()) DAY
             )
             AND created_at < DATE_ADD(
                 DATE_SUB(
                     CURDATE(),
                     INTERVAL WEEKDAY(CURDATE()) DAY
                 ),
                 INTERVAL 7 DAY
             )
             GROUP BY DATE(created_at)
             ORDER BY sales_date ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lima transaksi terbaru.
     */
    public function recentTransactions(): array
    {
        $stmt = $this->db->query(
            "SELECT
                t.id,
                t.transaction_code,
                t.total,
                t.payment_method,
                t.status,
                t.created_at,
                u.name AS cashier_name
             FROM transactions t
             INNER JOIN users u ON u.id = t.user_id
             ORDER BY t.created_at DESC, t.id DESC
             LIMIT 5"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lima bahan baku dengan stok menipis.
     */
    public function lowStockIngredients(): array
    {
        $stmt = $this->db->query(
            "SELECT
                id,
                name,
                current_stock,
                minimum_stock,
                unit
             FROM ingredients
             WHERE is_active = 1
             AND current_stock <= minimum_stock
             ORDER BY current_stock ASC, name ASC
             LIMIT 5"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
