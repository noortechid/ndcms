<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Product
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT p.*, c.name AS category_name
             FROM products p
             JOIN categories c ON c.id = p.category_id
             ORDER BY p.created_at DESC'
        );

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.name AS category_name
             FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

public function create(array $data): int
{
    $stmt = $this->db->prepare(
        'INSERT INTO products (
            category_id,
            product_code,
            name,
            description,
            price,
            is_available,
            requires_recipe
        ) VALUES (
            :category_id,
            :product_code,
            :name,
            :description,
            :price,
            :is_available,
            :requires_recipe
        )'
    );

    $stmt->execute([
        'category_id' => $data['category_id'],
        'product_code' => $data['product_code'] ?: null,
        'name' => $data['name'],
        'description' => $data['description'] ?: null,
        'price' => $data['price'],
        'is_available' => (int) $data['is_available'],
        'requires_recipe' => (int) $data['requires_recipe'],
    ]);

    return (int) $this->db->lastInsertId();
}

public function update(int $id, array $data): bool
{
    $stmt = $this->db->prepare(
        'UPDATE products SET
            category_id = :category_id,
            product_code = :product_code,
            name = :name,
            description = :description,
            price = :price,
            is_available = :is_available,
            requires_recipe = :requires_recipe
         WHERE id = :id'
    );

    return $stmt->execute([
        'id' => $id,
        'category_id' => $data['category_id'],
        'product_code' => $data['product_code'] ?: null,
        'name' => $data['name'],
        'description' => $data['description'] ?: null,
        'price' => $data['price'],
        'is_available' => (int) $data['is_available'],
        'requires_recipe' => (int) $data['requires_recipe'],
    ]);
}

    public function setActive(int $id, bool $active): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE products
             SET is_active = :is_active
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'is_active' => (int) $active,
        ]);
    }

    public function filter(array $filters = []): array
    {
        $sql = '
            SELECT p.*, c.name AS category_name
            FROM products p
            JOIN categories c ON c.id = p.category_id
            WHERE 1 = 1
        ';

        $params = [];

if (!empty($filters['search'])) {
    $sql .= " AND (
        p.name LIKE :search_name
        OR p.product_code LIKE :search_code
    )";

    $search = '%' . $filters['search'] . '%';

    $params['search_name'] = $search;
    $params['search_code'] = $search;
}

        if (!empty($filters['category_id'])) {
            $sql .= ' AND p.category_id = :category_id';
            $params['category_id'] = $filters['category_id'];
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= ' AND p.is_active = :status';
            $params['status'] = (int) $filters['status'];
        }

        $sql .= ' ORDER BY p.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

public function availableForPos(): array
{
    $stmt = $this->db->query(
        "SELECT
            p.id,
            p.category_id,
            p.name,
            p.product_code,
            p.price,
            p.image_path,
            c.name AS category_name
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         WHERE p.is_active = 1
           AND p.is_available = 1
           AND c.is_active = 1
         ORDER BY c.name ASC, p.name ASC"
    );

    return $stmt->fetchAll();
}

public function setAvailability(int $id, bool $available): bool
{
    $stmt = $this->db->prepare(
        'UPDATE products
         SET is_available = :is_available
         WHERE id = :id'
    );

    return $stmt->execute([
        'id' => $id,
        'is_available' => (int) $available,
    ]);
}
}
