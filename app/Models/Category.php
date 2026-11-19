<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Category
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT c.*,
                    COUNT(p.id) AS product_count
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id
             GROUP BY c.id
             ORDER BY c.name ASC'
        );

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT *
             FROM categories
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO categories (name, description)
             VALUES (:name, :description)'
        );

        $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?: null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE categories
             SET name = :name,
                 description = :description
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'] ?: null,
        ]);
    }

    public function setActive(int $id, bool $active): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE categories
             SET is_active = :is_active
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'is_active' => (int) $active,
        ]);
    }

    public function hasProducts(int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM products
             WHERE category_id = :id'
        );

        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function active(): array
    {
        $stmt = $this->db->query(
            'SELECT id, name
             FROM categories
             WHERE is_active = 1
             ORDER BY name ASC'
        );

        return $stmt->fetchAll();
    }

public function hasActiveProducts(int $id): bool
{
    $stmt = $this->db->prepare(
        'SELECT COUNT(*)
         FROM products
         WHERE category_id = :id
         AND is_active = 1'
    );

    $stmt->execute(['id' => $id]);

    return (int) $stmt->fetchColumn() > 0;
}
}
