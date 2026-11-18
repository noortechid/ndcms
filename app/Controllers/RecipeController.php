<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use InvalidArgumentException;
use Throwable;

class RecipeController extends Controller
{
    private Recipe $recipes;
    private Product $products;
    private Ingredient $ingredients;

    public function __construct()
    {
        $this->recipes = new Recipe();
        $this->products = new Product();
        $this->ingredients = new Ingredient();
    }

    public function index(): void
    {
        $this->view('recipes/index', [
            'pageTitle' => 'Recipe & HPP',
            'user' => Auth::user(),
            'recipes' => $this->recipes->all(),
        ]);
    }

    public function create(): void
    {
        $this->view('recipes/create', [
            'pageTitle' => 'Tambah Resep',
            'user' => Auth::user(),
            'csrfToken' => Csrf::token(),
            'products' => $this->getActiveProducts(),
            'ingredients' => $this->getActiveIngredients(),
            'recipe' => null,
        ]);
        
$existingRecipeIds = $this->recipes->productIdsWithRecipes();

$products = array_filter(
    $this->getActiveProducts(),
    fn ($product) => !in_array(
        (int) $product['id'],
        $existingRecipeIds,
        true
    )
);
    }

    public function edit(): void
    {
        $productId = (int) ($_GET['product_id'] ?? 0);

        $recipe = $this->recipes->findByProductId($productId);

        if (!$recipe) {
            $_SESSION['flash_error'] = 'Resep tidak ditemukan.';
            header('Location: /recipes');
            exit;
        }

        $this->view('recipes/edit', [
            'pageTitle' => 'Edit Resep',
            'user' => Auth::user(),
            'csrfToken' => Csrf::token(),
            'products' => $this->getActiveProducts(),
            'ingredients' => $this->getActiveIngredients(),
            'recipe' => $recipe,
        ]);
    }

    public function store(): void
    {
        if (!$this->verifyCsrf()) {
            $_SESSION['flash_error'] = 'Permintaan tidak valid.';
            header('Location: /recipes/create');
            exit;
        }

        $productId = (int) ($_POST['product_id'] ?? 0);
        $items = $this->getItems();
        $notes = trim($_POST['notes'] ?? '');

$productId = (int) ($_POST['product_id'] ?? 0);

if (in_array($productId, $this->recipes->productIdsWithRecipes(), true)) {
    $_SESSION['flash_error'] = 'Produk ini sudah memiliki resep.';
    header('Location: /recipes');
    exit;
}

        try {
            $this->recipes->saveForProduct(
                $productId,
                $items,
                $notes
            );

            $_SESSION['flash_success'] = 'Resep berhasil disimpan.';
            header('Location: /recipes');
            exit;
        } catch (InvalidArgumentException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: /recipes/create');
            exit;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function update(): void
    {
        if (!$this->verifyCsrf()) {
            $_SESSION['flash_error'] = 'Permintaan tidak valid.';
            header('Location: /recipes');
            exit;
        }

        $productId = (int) ($_POST['product_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $items = $this->getItems();

        try {
            $this->recipes->saveForProduct(
                $productId,
                $items,
                $notes
            );

            $_SESSION['flash_success'] = 'Resep berhasil diperbarui.';
            header('Location: /recipes');
            exit;
        } catch (InvalidArgumentException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: /recipes/edit?product_id=' . $productId);
            exit;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    private function verifyCsrf(): bool
    {
        return Csrf::verify($_POST['_token'] ?? null);
    }

    private function getItems(): array
    {
        $ingredientIds = $_POST['ingredient_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];

        if (!is_array($ingredientIds) || !is_array($quantities)) {
            return [];
        }

        if (count($ingredientIds) !== count($quantities)) {
            return [];
        }

        $items = [];

        foreach ($ingredientIds as $index => $ingredientId) {
            $items[] = [
                'ingredient_id' => $ingredientId,
                'quantity' => $quantities[$index] ?? null,
            ];
        }

        return $items;
    }

    private function getActiveProducts(): array
    {
        return array_values(array_filter(
            $this->products->all(),
            fn ($product) => (bool) $product['is_active']
        ));
    }

    private function getActiveIngredients(): array
    {
        return array_values(array_filter(
            $this->ingredients->all(),
            fn ($ingredient) => (bool) $ingredient['is_active']
        ));
    }
}
