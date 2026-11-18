<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class StockMovement
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Simpan riwayat pergerakan stok.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO stock_movements (
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
                :movement_type,
                :quantity,
                :stock_before,
                :stock_after,
                :notes
            )'
        );

        $stmt->execute([
            'ingredient_id' => $data['ingredient_id'],
            'user_id' => $data['user_id'],
            'movement_type' => $data['movement_type'],
            'quantity' => $data['quantity'],
            'stock_before' => $data['stock_before'],
            'stock_after' => $data['stock_after'],
            'notes' => $data['notes'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Ambil seluruh riwayat pergerakan stok.
     */
    public function all(int $limit = 100): array
    {
        $limit = max(1, min($limit, 500));

        $stmt = $this->db->query(
            "SELECT
                sm.*,
                i.name AS ingredient_name,
                i.unit AS ingredient_unit,
                u.username
            FROM stock_movements sm
            INNER JOIN ingredients i
                ON i.id = sm.ingredient_id
            INNER JOIN users u
                ON u.id = sm.user_id
            ORDER BY sm.created_at DESC, sm.id DESC
            LIMIT {$limit}"
        );

        return $stmt->fetchAll();
    }

    /**
     * Ambil riwayat berdasarkan bahan baku.
     */
    public function allByIngredient(int $ingredientId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                sm.*,
                i.name AS ingredient_name,
                i.unit AS ingredient_unit,
                u.username
            FROM stock_movements sm
            INNER JOIN ingredients i
                ON i.id = sm.ingredient_id
            INNER JOIN users u
                ON u.id = sm.user_id
            WHERE sm.ingredient_id = :ingredient_id
            ORDER BY sm.created_at DESC, sm.id DESC'
        );

        $stmt->execute([
            'ingredient_id' => $ingredientId,
        ]);

        return $stmt->fetchAll();
    }
}
