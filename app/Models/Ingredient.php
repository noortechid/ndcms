<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Ingredient
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        $sql = "SELECT *
                FROM ingredients
                ORDER BY name ASC";

        return $this->db->query($sql)->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM ingredients WHERE id = :id LIMIT 1"
        );

        $stmt->execute(['id' => $id]);

        $ingredient = $stmt->fetch();

        return $ingredient ?: null;
    }

    public function filter(array $filters = []): array
    {
        $sql = "SELECT *
                FROM ingredients
                WHERE 1 = 1";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (
                name LIKE :search_name
                OR ingredient_code LIKE :search_code
            )";

            $search = '%' . $filters['search'] . '%';

            $params['search_name'] = $search;
            $params['search_code'] = $search;
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND is_active = :status";
            $params['status'] = (int) $filters['status'];
        }

        $sql .= " ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO ingredients (
                    ingredient_code,
                    name,
                    unit,
                    cost_per_unit,
                    minimum_stock,
                    description
                ) VALUES (
                    :ingredient_code,
                    :name,
                    :unit,
                    :cost_per_unit,
                    :minimum_stock,
                    :description
                )";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'ingredient_code' => $data['ingredient_code'] ?: null,
            'name' => $data['name'],
            'unit' => $data['unit'],
            'cost_per_unit' => $data['cost_per_unit'],
            'minimum_stock' => $data['minimum_stock'],
            'description' => $data['description'] ?: null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE ingredients SET
                    ingredient_code = :ingredient_code,
                    name = :name,
                    unit = :unit,
                    cost_per_unit = :cost_per_unit,
                    minimum_stock = :minimum_stock,
                    description = :description
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'ingredient_code' => $data['ingredient_code'] ?: null,
            'name' => $data['name'],
            'unit' => $data['unit'],
            'cost_per_unit' => $data['cost_per_unit'],
            'minimum_stock' => $data['minimum_stock'],
            'description' => $data['description'] ?: null,
        ]);
    }

public function hasRecipes(int $id): bool
{
    $stmt = $this->db->prepare(
        "SELECT EXISTS(
            SELECT 1
            FROM recipe_items
            WHERE ingredient_id = :id
        )"
    );

    $stmt->execute(['id' => $id]);

    return (bool) $stmt->fetchColumn();
}

    public function setActive(int $id, bool $active): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE ingredients
             SET is_active = :is_active
             WHERE id = :id"
        );

        return $stmt->execute([
            'id' => $id,
            'is_active' => (int) $active,
        ]);
    }
}
