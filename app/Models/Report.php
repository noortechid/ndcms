<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Report
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Ringkasan penjualan berdasarkan rentang tanggal.
     */
    public function summary(string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total_transactions,
                COALESCE(SUM(total), 0) AS total_sales,
                COALESCE(SUM(subtotal), 0) AS total_subtotal,
                COALESCE(SUM(discount), 0) AS total_discount,
                COALESCE(AVG(total), 0) AS average_transaction
             FROM transactions
             WHERE status = 'completed'
               AND created_at >= :start_date
               AND created_at < DATE_ADD(:end_date, INTERVAL 1 DAY)"
        );

        $stmt->execute([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $stmt->fetch();
    }

public function paymentSummary(
    string $startDate,
    string $endDate
): array {
    $stmt = $this->db->prepare(
        "SELECT
            payment_method,
            COUNT(*) AS total_transactions,
            COALESCE(SUM(total), 0) AS total_sales
         FROM transactions
         WHERE status = 'completed'
           AND created_at >= :start_date
           AND created_at < DATE_ADD(:end_date, INTERVAL 1 DAY)
         GROUP BY payment_method
         ORDER BY total_sales DESC"
    );

    $stmt->execute([
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    return $stmt->fetchAll();
}

public function dailySales(
    string $startDate,
    string $endDate
): array {
    $stmt = $this->db->prepare(
        "SELECT
            DATE(created_at) AS sale_date,
            COUNT(*) AS total_transactions,
            COALESCE(SUM(total), 0) AS total_sales
         FROM transactions
         WHERE status = 'completed'
           AND created_at >= :start_date
           AND created_at < DATE_ADD(:end_date, INTERVAL 1 DAY)
         GROUP BY DATE(created_at)
         ORDER BY sale_date ASC"
    );

    $stmt->execute([
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    return $stmt->fetchAll();
}

public function topProducts(
    string $startDate,
    string $endDate,
    int $limit = 10
): array {
    $limit = max(1, min($limit, 100));

    $stmt = $this->db->prepare(
        "SELECT
            ti.product_id,
            ti.product_name,
            SUM(ti.quantity) AS total_quantity,
            SUM(ti.subtotal) AS total_sales
         FROM transaction_items ti
         INNER JOIN transactions t
            ON t.id = ti.transaction_id
         WHERE t.status = 'completed'
           AND t.created_at >= :start_date
           AND t.created_at < DATE_ADD(:end_date, INTERVAL 1 DAY)
         GROUP BY ti.product_id, ti.product_name
         ORDER BY total_quantity DESC, total_sales DESC
         LIMIT :limit"
    );

    $stmt->bindValue(':start_date', $startDate);
    $stmt->bindValue(':end_date', $endDate);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll();
}

public function transactionHistory(
    string $startDate,
    string $endDate,
    int $limit = 100
): array {
    $limit = max(1, min($limit, 500));

    $stmt = $this->db->prepare(
        "SELECT
            t.id,
            t.transaction_code,
            t.created_at,
            t.subtotal,
            t.discount,
            t.total,
            t.payment_method,
            t.status,
            u.username AS cashier_name
         FROM transactions t
         INNER JOIN users u ON u.id = t.user_id
         WHERE t.created_at >= :start_date
           AND t.created_at < DATE_ADD(:end_date, INTERVAL 1 DAY)
         ORDER BY t.created_at DESC
         LIMIT :limit"
    );

    $stmt->bindValue(':start_date', $startDate);
    $stmt->bindValue(':end_date', $endDate);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll();
}

}
