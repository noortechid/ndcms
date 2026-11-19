<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Shift
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findOpenByUser(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM cashier_shifts
             WHERE user_id = :user_id
             AND status = 'open'
             ORDER BY id DESC
             LIMIT 1"
        );

        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, u.name AS cashier_name,
                    c.name AS closed_by_name
             FROM cashier_shifts s
             JOIN users u ON u.id = s.user_id
             LEFT JOIN users c ON c.id = s.closed_by
             WHERE s.id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT s.*, u.name AS cashier_name,
                    c.name AS closed_by_name
             FROM cashier_shifts s
             JOIN users u ON u.id = s.user_id
             LEFT JOIN users c ON c.id = s.closed_by
             ORDER BY s.opened_at DESC'
        );

        return $stmt->fetchAll();
    }

    public function allByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, u.name AS cashier_name,
                    c.name AS closed_by_name
             FROM cashier_shifts s
             JOIN users u ON u.id = s.user_id
             LEFT JOIN users c ON c.id = s.closed_by
             WHERE s.user_id = :user_id
             ORDER BY s.opened_at DESC'
        );

        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }
}
