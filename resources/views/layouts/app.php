<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?>
        | <?= htmlspecialchars($appName ?? 'ND Coffee') ?>
    </title>

    <link rel="stylesheet" href="/assets/css/app.css">
    <script src="/assets/js/app.js" defer></script>
</head>

<body class="min-h-screen">

    <?php
    $navigationGroups = [
        [
            'label' => 'General',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'href' => '/',
                    'permission' => 'dashboard.view',
                    'icon' => '▦',
                ],
            ],
        ],
        [
            'label' => 'Sales Management',
            'items' => [
                [
                    'label' => 'Point of Sale',
                    'href' => '/pos',
                    'permission' => 'pos.access',
                    'icon' => '▣',
                ],
                [
                    'label' => 'Transactions',
                    'href' => '/transactions',
                    'permission' => 'transactions.view',
                    'icon' => '▤',
                ],
                [
                    'label' => 'Shifts',
                    'href' => '/shifts',
                    'permission' => 'shifts.view',
                    'icon' => '◷',
                ],
            ],
        ],
        [
            'label' => 'Product Management',
            'items' => [
                [
                    'label' => 'Products',
                    'href' => '/products',
                    'permission' => 'products.view',
                    'icon' => '◇',
                ],
                [
                    'label' => 'Categories',
                    'href' => '/categories',
                    'permission' => 'categories.manage',
                    'icon' => '◈',
                ],
                [
                    'label' => 'Recipes & HPP',
                    'href' => '/recipes',
                    'permission' => 'recipes.view',
                    'icon' => '♨',
                ],
            ],
        ],
        [
            'label' => 'Inventory Management',
            'items' => [
                [
                    'label' => 'Inventory',
                    'href' => '/inventory',
                    'permission' => 'inventory.view',
                    'icon' => '▥',
                ],
                [
                    'label' => 'Ingredients',
                    'href' => '/ingredients',
                    'permission' => 'ingredients.view',
                    'icon' => '♧',
                ],
            ],
        ],
        [
            'label' => 'Purchasing',
            'items' => [
                [
                    'label' => 'Purchase Orders',
                    'href' => '/purchases',
                    'permission' => 'purchasing',
                    'icon' => '▧',
                ],
                [
                    'label' => 'Suppliers',
                    'href' => '/suppliers',
                    'permission' => 'purchasing',
                    'icon' => '♙',
                ],
            ],
        ],
        [
            'label' => 'Reports & Administration',
            'items' => [
                [
                    'label' => 'Reports',
                    'href' => '/reports',
                    'permission' => 'reports.view',
                    'icon' => '▨',
                ],
                [
                    'label' => 'Employees',
                    'href' => '/employees',
                    'permission' => 'employees.view',
                    'icon' => '♟',
                ],
                [
                    'label' => 'User Management',
                    'href' => '/users',
                    'permission' => 'users.manage',
                    'icon' => '♧',
                ],
            ],
        ],
    ];

    $currentPath = parse_url(
        $_SERVER['REQUEST_URI'] ?? '/',
        PHP_URL_PATH
    ) ?: '/';
    ?>

    <div class="min-h-screen flex">

        <!-- Sidebar -->
        <aside
            id="sidebar"
            class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-border
                   flex flex-col -translate-x-full transition-transform duration-300
                   lg:translate-x-0 lg:static">

            <!-- Brand -->
            <div class="h-20 px-7 flex items-center gap-3 border-b border-border shrink-0">

                <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center">
                    <span class="text-white text-lg font-bold">N</span>
                </div>

                <div>
                    <h1 class="font-bold text-lg tracking-tight">ND Coffee</h1>
                    <p class="text-xs text-text-secondary">Management System</p>
                </div>

            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-6">

                <?php foreach ($navigationGroups as $group): ?>

                    <?php
                    $visibleItems = array_filter(
                        $group['items'],
                        fn ($item) => \App\Core\Authorization::allows(
                            $item['permission']
                        )
                    );
                    ?>

                    <?php if (!empty($visibleItems)): ?>

                        <div class="space-y-2">

                            <!-- Group Label -->
                            <p class="px-3 mb-3 text-xs font-semibold uppercase
                                      tracking-wider text-text-secondary">
                                <?= htmlspecialchars($group['label']) ?>
                            </p>

                            <!-- Group Items -->
                            <?php foreach ($visibleItems as $item): ?>

                                <?php
                                $href = $item['href'];

                                $active = $href === '/'
                                    ? $currentPath === '/'
                                    : (
                                        $currentPath === $href ||
                                        str_starts_with($currentPath, $href . '/')
                                    );
                                ?>

                                <a
                                    href="<?= htmlspecialchars($href) ?>"
                                    class="flex items-center gap-3 px-4 py-3 rounded-xl
                                           transition
                                           <?= $active
                                               ? 'bg-primary/15 text-primary-dark font-semibold'
                                               : 'text-text-secondary hover:bg-background hover:text-primary-dark' ?>"
                                    <?= $active ? 'aria-current="page"' : '' ?>>

                                    <span class="w-5 text-center">
                                        <?= htmlspecialchars($item['icon']) ?>
                                    </span>

                                    <span>
                                        <?= htmlspecialchars($item['label']) ?>
                                    </span>

                                    <?php if ($active): ?>
                                        <span class="ml-auto w-1.5 h-1.5 rounded-full bg-primary"></span>
                                    <?php endif; ?>

                                </a>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                <?php endforeach; ?>

            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-border shrink-0">

                <div class="rounded-xl bg-background p-4">
                    <p class="text-sm font-semibold">ND Coffee</p>
                    <p class="text-xs text-text-secondary mt-1">
                        Management System
                    </p>
                </div>

            </div>

        </aside>

        <!-- Main Content -->
        <div class="flex-1 min-w-0">

            <!-- Header -->
            <header class="h-20 bg-white border-b border-border px-5 lg:px-8
                           flex items-center justify-between">

                <div class="flex items-center gap-4">

                    <button
                        id="sidebarToggle"
                        class="lg:hidden p-2 rounded-lg hover:bg-background"
                        aria-label="Toggle sidebar">
                        ☰
                    </button>

                    <div>
                        <h2 class="font-semibold text-lg">
                            <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?>
                        </h2>

                        <p class="text-xs text-text-secondary">
                            ND Coffee Management System
                        </p>
                    </div>

                </div>

                <div class="flex items-center gap-3">

                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold">Administrator</p>
                        <p class="text-xs text-text-secondary">Admin</p>
                    </div>

                    <div class="w-10 h-10 rounded-full bg-secondary flex items-center justify-center
                                text-primary-dark font-semibold">
                        A
                    </div>

                </div>

            </header>

            <!-- Page Content -->
            <main class="p-5 lg:p-8">
                <?= $content ?>
            </main>

        </div>

    </div>

    <!-- Mobile Sidebar Overlay -->
    <div
        id="sidebarOverlay"
        class="fixed inset-0 z-30 bg-black/30 hidden lg:hidden">
    </div>

</body>
</html>
