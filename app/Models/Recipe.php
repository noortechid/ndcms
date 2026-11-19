<?php

namespace App\Models;

use App\Core\Database;
use InvalidArgumentException;
use PDO;
use Throwable;

class Recipe
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Menampilkan semua resep beserta HPP.
     */
    public function all(): array
    {
        $sql = "SELECT
                    r.id,
                    r.product_id,
                    r.notes,
                    p.name AS product_name,
                    p.price AS selling_price,
                    COUNT(ri.id) AS ingredient_count,
                    COALESCE(
                        SUM(ri.quantity * i.cost_per_unit),
                        0
                    ) AS total_cost
                FROM recipes r
                INNER JOIN products p ON p.id = r.product_id
                LEFT JOIN recipe_items ri ON ri.recipe_id = r.id
                LEFT JOIN ingredients i ON i.id = ri.ingredient_id
                GROUP BY r.id, r.product_id, r.notes,
                         p.name, p.price
                ORDER BY p.name ASC";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Mengambil resep berdasarkan produk.
     */
    public function findByProductId(int $productId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT
                r.id,
                r.product_id,
                r.notes,
                p.name AS product_name,
                p.price AS selling_price
             FROM recipes r
             INNER JOIN products p ON p.id = r.product_id
             WHERE r.product_id = :product_id
             LIMIT 1"
        );

        $stmt->execute(['product_id' => $productId]);

        $recipe = $stmt->fetch();

        if (!$recipe) {
            return null;
        }

        $items = $this->getItems((int) $recipe['id']);

        $recipe['items'] = $items;
        $recipe['total_cost'] = array_sum(
            array_column($items, 'subtotal')
        );

        return $recipe;

    }

    /**
     * Mengambil detail bahan dalam resep.
     */
    public function getItems(int $recipeId): array
    {
        $sql = "SELECT
                    ri.id,
                    ri.ingredient_id,
                    ri.quantity,
                    i.name AS ingredient_name,
                    i.unit,
                    i.cost_per_unit,
                    (ri.quantity * i.cost_per_unit) AS subtotal
                FROM recipe_items ri
                INNER JOIN ingredients i
                    ON i.id = ri.ingredient_id
                WHERE ri.recipe_id = :recipe_id
                ORDER BY i.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['recipe_id' => $recipeId]);

        return $stmt->fetchAll();
    }

    /**
     * Menyimpan atau memperbarui resep produk.
     *
     * Format items:
     * [
     *     ['ingredient_id' => 1, 'quantity' => 18],
     *     ['ingredient_id' => 2, 'quantity' => 150],
     * ]
     */
    public function saveForProduct(
        int $productId,
        array $items,
        ?string $notes = null
    ): int {
        if ($productId <= 0) {
            throw new InvalidArgumentException('Produk tidak valid.');
        }

        if (empty($items)) {
            throw new InvalidArgumentException(
                'Resep harus memiliki minimal satu bahan baku.'
            );
        }

        $this->db->beginTransaction();

        try {
            // Pastikan produk tersedia.
            $productStmt = $this->db->prepare(
                "SELECT id FROM products WHERE id = :id LIMIT 1"
            );

            $productStmt->execute(['id' => $productId]);

            if (!$productStmt->fetch()) {
                throw new InvalidArgumentException(
                    'Produk tidak ditemukan.'
                );
            }

            // Cari resep yang sudah ada.
            $recipeStmt = $this->db->prepare(
                "SELECT id
                 FROM recipes
                 WHERE product_id = :product_id
                 FOR UPDATE"
            );

            $recipeStmt->execute(['product_id' => $productId]);

            $recipe = $recipeStmt->fetch();

            if ($recipe) {
                $recipeId = (int) $recipe['id'];

                $updateRecipe = $this->db->prepare(
                    "UPDATE recipes
                     SET notes = :notes
                     WHERE id = :id"
                );

                $updateRecipe->execute([
                    'notes' => $notes ?: null,
                    'id' => $recipeId,
                ]);

                $deleteItems = $this->db->prepare(
                    "DELETE FROM recipe_items
                     WHERE recipe_id = :recipe_id"
                );

                $deleteItems->execute([
                    'recipe_id' => $recipeId,
                ]);
            } else {
                $insertRecipe = $this->db->prepare(
                    "INSERT INTO recipes (product_id, notes)
                     VALUES (:product_id, :notes)"
                );

                $insertRecipe->execute([
                    'product_id' => $productId,
                    'notes' => $notes ?: null,
                ]);

                $recipeId = (int) $this->db->lastInsertId();
            }

            $ingredientCheck = $this->db->prepare(
                "SELECT id
                 FROM ingredients
                 WHERE id = :id
                   AND is_active = 1
                 LIMIT 1"
            );

            $insertItem = $this->db->prepare(
                "INSERT INTO recipe_items (
                    recipe_id,
                    ingredient_id,
                    quantity
                ) VALUES (
                    :recipe_id,
                    :ingredient_id,
                    :quantity
                )"
            );

            $usedIngredients = [];

            foreach ($items as $item) {
                $ingredientId = (int) ($item['ingredient_id'] ?? 0);
                $quantity = $item['quantity'] ?? null;

                if (
                    $ingredientId <= 0 ||
                    !is_numeric($quantity) ||
                    (float) $quantity <= 0
                ) {
                    throw new InvalidArgumentException(
                        'Bahan baku atau takaran resep tidak valid.'
                    );
                }

                if (isset($usedIngredients[$ingredientId])) {
                    throw new InvalidArgumentException(
                        'Bahan baku tidak boleh dimasukkan dua kali.'
                    );
                }

                $usedIngredients[$ingredientId] = true;

                $ingredientCheck->execute([
                    'id' => $ingredientId,
                ]);

                if (!$ingredientCheck->fetch()) {
                    throw new InvalidArgumentException(
                        'Bahan baku tidak ditemukan atau sudah nonaktif.'
                    );
                }

                $insertItem->execute([
                    'recipe_id' => $recipeId,
                    'ingredient_id' => $ingredientId,
                    'quantity' => $quantity,
                ]);
            }

            $this->db->commit();

            return $recipeId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

public function productIdsWithRecipes(): array
{
    $stmt = $this->db->query(
        'SELECT product_id FROM recipes'
    );

    return array_map(
        'intval',
        $stmt->fetchAll(PDO::FETCH_COLUMN)
    );
}
}
