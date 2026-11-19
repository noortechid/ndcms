<?php

declare(strict_types=1);

namespace App\Core;

final class Authorization
{
    private const PERMISSIONS = [
        'owner' => ['*'],

        'admin' => [
            'dashboard.view',

            // Sales
            'pos.access',
            'transactions.view',
            'transactions.create',

            // Products & Categories
            'products.view',
            'products.manage',
            'categories.view',
            'categories.manage',

            // Inventory & Ingredients
            'inventory.view',
            'inventory.manage',
            'ingredients.view',
            'ingredients.manage',

            // Recipes
            'recipes.view',
            'recipes.manage',

            // Purchasing
            'purchasing.view',
            'purchasing.manage',

            // Employees
            'employees.view',
            'employees.manage',

            // Shifts
            'shifts.view',
            'shifts.manage',
            'shifts.open',
            'shifts.close',

            // Reports
            'reports.view',
        ],

        'cashier' => [
            'dashboard.view',
            'pos.access',
            'transactions.view',
            'transactions.create',
            'products.view',
            'shifts.view',
            'shifts.open',
            'shifts.close',
        ],

        'inventory_staff' => [
            'dashboard.view',

            // Product reference
            'products.view',

            // Inventory & Ingredients
            'inventory.view',
            'inventory.manage',
            'ingredients.view',
            'ingredients.manage',

            // Recipe reference
            'recipes.view',

            // Purchasing
            'purchasing.view',
            'purchasing.manage',
        ],
    ];

    public static function allows(string $permission): bool
    {
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        $role = $user['role'] ?? '';
        $permissions = self::PERMISSIONS[$role] ?? [];

        return in_array('*', $permissions, true)
            || in_array($permission, $permissions, true);
    }

    public static function permissionsFor(string $role): array
    {
        return self::PERMISSIONS[$role] ?? [];
    }
}
