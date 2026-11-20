<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> | <?= htmlspecialchars($appName ?? 'ND Coffee') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/views.css">
    <script src="/assets/js/app.js" defer></script>
</head>
<body class="app-body">
<?php
$navigationGroups = [
    ['label' => 'General', 'items' => [
        ['label' => 'Dashboard', 'href' => '/', 'permission' => 'dashboard.view', 'icon' => 'layout-dashboard'],
    ]],
    ['label' => 'Sales Management', 'items' => [
        ['label' => 'Point of Sale', 'href' => '/pos', 'permission' => 'pos.access', 'icon' => 'shopping-bag'],
        ['label' => 'Transactions', 'href' => '/transactions', 'permission' => 'transactions.view', 'icon' => 'receipt-text'],
        ['label' => 'Shifts', 'href' => '/shifts', 'permission' => 'shifts.view', 'icon' => 'clock-3'],
    ]],
    ['label' => 'Product Management', 'items' => [
        ['label' => 'Products', 'href' => '/products', 'permission' => 'products.view', 'icon' => 'coffee'],
        ['label' => 'Categories', 'href' => '/categories', 'permission' => 'categories.manage', 'icon' => 'shapes'],
        ['label' => 'Recipes & HPP', 'href' => '/recipes', 'permission' => 'recipes.view', 'icon' => 'book-open'],
    ]],
    ['label' => 'Inventory Management', 'items' => [
        ['label' => 'Inventory', 'href' => '/inventory', 'permission' => 'inventory.view', 'icon' => 'package'],
        ['label' => 'Ingredients', 'href' => '/ingredients', 'permission' => 'ingredients.view', 'icon' => 'wheat'],
    ]],
    ['label' => 'Purchasing', 'items' => [
        ['label' => 'Purchase Orders', 'href' => '/purchases', 'permission' => 'purchasing', 'icon' => 'clipboard-list'],
        ['label' => 'Suppliers', 'href' => '/suppliers', 'permission' => 'purchasing', 'icon' => 'truck'],
    ]],
    ['label' => 'Reports & Administration', 'items' => [
        ['label' => 'Reports', 'href' => '/reports', 'permission' => 'reports.view', 'icon' => 'chart-no-axes-combined'],
        ['label' => 'Employees', 'href' => '/employees', 'permission' => 'employees.view', 'icon' => 'users'],
        ['label' => 'User Management', 'href' => '/users', 'permission' => 'users.manage', 'icon' => 'user-round-cog'],
    ]],
];
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$currentUser = $user ?? [];
$userName = is_array($currentUser) ? ($currentUser['name'] ?? 'Pengguna') : 'Pengguna';
$userInitial = function_exists('mb_substr') ? mb_strtoupper(mb_substr($userName, 0, 1)) : strtoupper(substr($userName, 0, 1));
?>
<div class="app-shell">
    <aside id="sidebar" class="app-sidebar">
        <a href="/" class="brand-lockup">
            <span class="brand-mark">N</span>
            <span class="brand-copy"><strong>ND Coffee</strong><small>Management System</small></span>
        </a>
        <nav class="sidebar-nav" aria-label="Navigasi utama">
            <?php foreach ($navigationGroups as $group): ?>
                <?php $visibleItems = array_filter($group['items'], fn ($item) => \App\Core\Authorization::allows($item['permission'])); ?>
                <?php if ($visibleItems): ?>
                    <div class="nav-group">
                        <p class="nav-group-label"><?= htmlspecialchars($group['label']) ?></p>
                        <?php foreach ($visibleItems as $item): ?>
                            <?php $href = $item['href']; $active = $href === '/' ? $currentPath === '/' : ($currentPath === $href || str_starts_with($currentPath, $href . '/')); ?>
                            <a href="<?= htmlspecialchars($href) ?>" class="nav-link <?= $active ? 'is-active' : '' ?>" <?= $active ? 'aria-current="page"' : '' ?>>
                                <i data-lucide="<?= htmlspecialchars($item['icon']) ?>"></i><span><?= htmlspecialchars($item['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-bottom">
<form action="/logout" method="POST">
    <input
        type="hidden"
        name="_token"
        value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
    >

    <button
        type="submit"
        class="inline-flex items-center gap-2 rounded-xl border border-border
               px-4 py-2.5 text-sm font-medium
               text-text-secondary transition
               hover:text-red-600"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>

        <span>Logout</span>
    </button>
</form>
        </div>
    </aside>

    <div id="sidebarOverlay" class="sidebar-overlay"></div>
    <div class="app-main">
        <header class="topbar">
            <div class="topbar-heading">
                <button id="sidebarToggle" class="mobile-menu-button" aria-label="Buka navigasi"><i data-lucide="menu"></i></button>
                <div><h1>Welcome to NDCMS</h1><p class="mt-1 text-sm text-[#9A8178]">Simplifying Operations, Brewing Better Experiences.</p></div>
            </div>
            <div class="topbar-actions">
                <div class="topbar-date"><i data-lucide="calendar-days"></i><span><?= htmlspecialchars(date('d F Y')) ?></span></div>
                <div class="profile-chip"><span class="profile-avatar"><?= htmlspecialchars($userInitial) ?></span><span class="profile-copy"><strong><?= htmlspecialchars($userName) ?></strong><small><?= htmlspecialchars($currentUser['role_name'] ?? 'Staff') ?></small></span></div>
            </div>
        </header>
        <?php $pageSegment = trim(explode('/', trim($currentPath, '/'))[0] ?? 'dashboard'); $pageSegment = preg_replace('/[^a-z0-9-]/i', '', $pageSegment) ?: 'dashboard'; ?>
        <main class="page-content page-<?= htmlspecialchars($pageSegment, ENT_QUOTES, 'UTF-8') ?>" data-page="<?= htmlspecialchars($pageSegment, ENT_QUOTES, 'UTF-8') ?>"><?= $content ?></main>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });</script>
</body>
</html>
