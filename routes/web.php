<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\EmployeeController;
use App\Controllers\UserController;
use App\Controllers\ShiftController;
use App\Controllers\CategoryController;
use App\Controllers\ProductController;
use App\Controllers\IngredientController;
use App\Controllers\RecipeController;
use App\Controllers\InventoryController;
use App\Controllers\TransactionController;
use App\Controllers\ReportController;
use App\Controllers\SupplierController;
use App\Controllers\PurchaseController;

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\GuestMiddleware;
use App\Core\Middleware\PermissionMiddleware;

// Guest
$app->router()->get('/login', [
    AuthController::class,
    'login',
], [
    GuestMiddleware::class,
]);

$app->router()->post('/login', [
    AuthController::class,
    'authenticate',
], [
    GuestMiddleware::class,
]);

// Logout
$app->router()->post('/logout', [
    AuthController::class,
    'logout',
], [
    AuthMiddleware::class,
]);

// Dashboard
$app->router()->get('/', [
    HomeController::class,
    'index',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['dashboard.view']],
]);

// Employees
$app->router()->get('/employees', [
    EmployeeController::class,
    'index',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['employees.view']],
]);

$app->router()->get('/employees/create', [
    EmployeeController::class,
    'create',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['employees.manage']],
]);

$app->router()->post('/employees', [
    EmployeeController::class,
    'store',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['employees.manage']],
]);

$app->router()->get('/employees/edit', [
    EmployeeController::class,
    'edit',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['employees.manage']],
]);

$app->router()->post('/employees/update', [
    EmployeeController::class,
    'update',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['employees.manage']],
]);

$app->router()->post('/employees/status', [
    EmployeeController::class,
    'toggleStatus',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['employees.manage']],
]);

// Users
$app->router()->get('/users', [
    UserController::class,
    'index',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['users.manage']],
]);

$app->router()->get('/users/create', [
    UserController::class,
    'create',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['users.manage']],
]);

$app->router()->post('/users', [
    UserController::class,
    'store',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['users.manage']],
]);

$app->router()->get('/users/edit', [
    UserController::class,
    'edit',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['users.manage']],
]);

$app->router()->post('/users/update', [
    UserController::class,
    'update',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['users.manage']],
]);

$app->router()->post('/users/status', [
    UserController::class,
    'toggleStatus',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['users.manage']],
]);

// Shifts
$app->router()->get('/shifts', [
    ShiftController::class,
    'index',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['shifts.view']],
]);

$app->router()->post('/shifts/open', [
    ShiftController::class,
    'open',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['shifts.open']],
]);

$app->router()->post('/shifts/close', [
    ShiftController::class,
    'close',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['shifts.close']],
]);

// Categories
$app->router()->get('/categories', [
    CategoryController::class,
    'index',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['categories.view']],
]);

$app->router()->get('/categories/create', [
    CategoryController::class,
    'create',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['categories.manage']],
]);

$app->router()->post('/categories', [
    CategoryController::class,
    'store',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['categories.manage']],
]);

$app->router()->get('/categories/edit', [
    CategoryController::class,
    'edit',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['categories.manage']],
]);

$app->router()->post('/categories/update', [
    CategoryController::class,
    'update',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['categories.manage']],
]);

$app->router()->post('/categories/status', [
    CategoryController::class,
    'toggleStatus',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['categories.manage']],
]);

// Products
$app->router()->get('/products', [
    ProductController::class,
    'index',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['products.view']],
]);

$app->router()->get('/products/create', [
    ProductController::class,
    'create',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['products.manage']],
]);

$app->router()->post('/products', [
    ProductController::class,
    'store',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['products.manage']],
]);

$app->router()->get('/products/edit', [
    ProductController::class,
    'edit',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['products.manage']],
]);

$app->router()->post('/products/update', [
    ProductController::class,
    'update',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['products.manage']],
]);

$app->router()->post('/products/status', [
    ProductController::class,
    'toggleStatus',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['products.manage']],
]);

$app->router()->post('/products/availability', [
    ProductController::class,
    'toggleAvailability',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['products.manage']],
]);

// Ingredients
$app->router()->get('/ingredients', [
    IngredientController::class,
    'index',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['ingredients.view']],
]);

$app->router()->get('/ingredients/create', [
    IngredientController::class,
    'create',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['ingredients.manage']],
]);

$app->router()->post('/ingredients', [
    IngredientController::class,
    'store',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['ingredients.manage']],
]);

$app->router()->get('/ingredients/edit', [
    IngredientController::class,
    'edit',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['ingredients.manage']],
]);

$app->router()->post('/ingredients/update', [
    IngredientController::class,
    'update',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['ingredients.manage']],
]);

$app->router()->post('/ingredients/status', [
    IngredientController::class,
    'toggleStatus',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['ingredients.manage']],
]);

// Recipes
$app->router()->get('/recipes', [
    RecipeController::class,
    'index',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['recipes.view']],
]);

$app->router()->get('/recipes/create', [
    RecipeController::class,
    'create',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['recipes.manage']],
]);

$app->router()->post('/recipes', [
    RecipeController::class,
    'store',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['recipes.manage']],
]);

$app->router()->get('/recipes/edit', [
    RecipeController::class,
    'edit',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['recipes.manage']],
]);

$app->router()->post('/recipes/update', [
    RecipeController::class,
    'update',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['recipes.manage']],
]);

// Inventory
$app->router()->get('/inventory', [
    InventoryController::class,
    'index',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['inventory.view']],
]);

$app->router()->post('/inventory/stock-in', [
    InventoryController::class,
    'stockIn',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['inventory.manage']],
]);

$app->router()->post('/inventory/stock-out', [
    InventoryController::class,
    'stockOut',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['inventory.manage']],
]);

$app->router()->post('/inventory/adjust', [
    InventoryController::class,
    'adjust',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['inventory.manage']],
]);

// Point of Sale
$app->router()->get('/pos', [
    TransactionController::class,
    'index',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['pos.access']],
]);

$app->router()->post('/transactions', [
    TransactionController::class,
    'store',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['transactions.create']],
]);

$app->router()->get('/transactions', [
    TransactionController::class,
    'history',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['transactions.view']],
]);

// Transactions
$app->router()->get('/transactions/show', [
    TransactionController::class,
    'show',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['transactions.view']],
]);

// Reports
$app->router()->get('/reports', [
    ReportController::class,
    'index',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['reports.view']],
]);

// Suppliers
$app->router()->get('/suppliers', [
    SupplierController::class,
    'index',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['purchasing.view']],
]);

$app->router()->get('/suppliers/create', [
    SupplierController::class,
    'create',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['purchasing.manage']],
]);

$app->router()->post('/suppliers/store', [
    SupplierController::class,
    'store',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['purchasing.manage']],
]);

$app->router()->get('/suppliers/edit', [
    SupplierController::class,
    'edit',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['purchasing.manage']],
]);

$app->router()->post('/suppliers/update', [
    SupplierController::class,
    'update',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['purchasing.manage']],
]);

$app->router()->post('/suppliers/status', [
    SupplierController::class,
    'status',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['purchasing.manage']],
]);

// Purchases
$app->router()->get('/purchases', [
    PurchaseController::class,
    'index',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['purchasing.view']],
]);

$app->router()->get('/purchases/create', [
    PurchaseController::class,
    'create',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['purchasing.manage']],
]);

$app->router()->post('/purchases/store', [
    PurchaseController::class,
    'store',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['purchasing.manage']],
]);

$app->router()->get('/purchases/show', [
    PurchaseController::class,
    'show',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['purchasing.view']],
]);

$app->router()->get('/purchases/edit', [
    PurchaseController::class,
    'edit',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['purchasing.manage']],
]);

$app->router()->post('/purchases/update', [
    PurchaseController::class,
    'update',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['purchasing.manage']],
]);

$app->router()->post('/purchases/status', [
    PurchaseController::class,
    'status',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['purchasing.manage']],
]);

$app->router()->get('/purchases/receive', [
    PurchaseController::class,
    'receive',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['purchasing.manage']],
]);

$app->router()->post('/purchases/receive/store', [
    PurchaseController::class,
    'storeReceipt',
], [
    AuthMiddleware::class,
    [PermissionMiddleware::class, ['purchasing.manage']],
]);
