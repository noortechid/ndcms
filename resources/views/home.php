<?php

$pageTitle = 'Dashboard';

$escape = static fn ($value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$currentUser = $user ?? [];
$userName = is_array($currentUser)
    ? ($currentUser['name'] ?? 'Pengguna')
    : 'Pengguna';

$summary = $statistics ?? [];

$todayRevenue = (float) ($summary['today_revenue'] ?? 0);
$todayTransactions = (int) ($summary['today_transactions'] ?? 0);
$activeProducts = (int) ($summary['active_products'] ?? 0);
$lowStockCount = (int) ($summary['low_stock_count'] ?? 0);

$formatCurrency = static fn ($amount): string => 'Rp ' . number_format(
    (float) $amount,
    0,
    ',',
    '.'
);

$weeklySales = $weeklySales ?? [];
$recentTransactions = $recentTransactions ?? [];
$lowStockIngredients = $lowStockIngredients ?? [];

// Susun data penjualan Senin sampai Minggu.
$salesByDate = [];

foreach ($weeklySales as $sale) {
    $salesByDate[$sale['sales_date']] = (float) $sale['revenue'];
}

$weekDays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
$weekStart = new DateTimeImmutable('monday this week');

$chartData = [];

for ($i = 0; $i < 7; $i++) {
    $date = $weekStart->modify("+{$i} days");
    $dateKey = $date->format('Y-m-d');

    $chartData[] = [
        'label' => $weekDays[$i],
        'date' => $dateKey,
        'revenue' => $salesByDate[$dateKey] ?? 0,
    ];
}

$maxRevenue = max(
    array_column($chartData, 'revenue') ?: [0]
);

$weeklyTotal = array_sum(
    array_column($chartData, 'revenue')
);

$today = date('Y-m-d');
?>

<div class="space-y-7">

    <!-- Welcome Header -->
    <section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        <div>
            <p class="text-sm text-primary font-medium mb-2">
                <?= $escape(date('l, d F Y')) ?>
            </p>

            <h1 class="text-2xl font-bold text-text-primary">
                Selamat datang, <?= $escape($userName) ?>!
            </h1>

            <p class="text-sm text-text-secondary mt-2">
                Berikut ringkasan aktivitas operasional ND Coffee hari ini.
            </p>
        </div>

        <a href="/pos"
           class="inline-flex items-center justify-center gap-2 px-5 py-3
                  rounded-xl bg-primary text-white text-sm font-semibold
                  hover:bg-primary-dark transition">
            <span>+</span>
            Transaksi Baru
        </a>

    </section>

    <!-- Statistics -->
    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        <?php
        $statCards = [
            [
                'label' => 'Omzet Hari Ini',
                'value' => $formatCurrency($todayRevenue),
                'description' => 'Total penjualan selesai hari ini',
                'icon' => 'Rp',
                'color' => 'bg-[#FCE8EF] text-[#A64D70]',
            ],
            [
                'label' => 'Transaksi Hari Ini',
                'value' => number_format($todayTransactions, 0, ',', '.'),
                'description' => 'Transaksi berhasil',
                'icon' => 'TRX',
                'color' => 'bg-[#FCEFE3] text-[#A66B3F]',
            ],
            [
                'label' => 'Produk Aktif',
                'value' => number_format($activeProducts, 0, ',', '.'),
                'description' => 'Produk terdaftar dan aktif',
                'icon' => 'PRD',
                'color' => 'bg-[#E9F1E8] text-[#648263]',
            ],
            [
                'label' => 'Stok Menipis',
                'value' => number_format($lowStockCount, 0, ',', '.'),
                'description' => 'Bahan baku perlu diperhatikan',
                'icon' => 'STK',
                'color' => 'bg-[#F9E9E4] text-[#B36B55]',
            ],
        ];
        ?>

        <?php foreach ($statCards as $card): ?>

            <div class="bg-white border border-border rounded-2xl p-5
                        shadow-sm hover:shadow-md transition">

                <div class="flex items-start justify-between gap-3">

                    <div>
                        <p class="text-sm text-text-secondary">
                            <?= $escape($card['label']) ?>
                        </p>

                        <h3 class="text-2xl font-bold text-text-primary mt-3">
                            <?= $escape($card['value']) ?>
                        </h3>
                    </div>

                    <div class="w-11 h-11 rounded-xl flex items-center
                                justify-center text-xs font-bold
                                <?= $escape($card['color']) ?>">
                        <?= $escape($card['icon']) ?>
                    </div>

                </div>

                <p class="text-xs text-text-secondary mt-4">
                    <?= $escape($card['description']) ?>
                </p>

            </div>

        <?php endforeach; ?>

    </section>

    <!-- Sales Overview & Quick Access -->
    <section class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        <!-- Weekly Sales -->
        <div class="xl:col-span-2 bg-white border border-border
                    rounded-2xl p-6 shadow-sm">

            <div class="flex flex-col sm:flex-row sm:items-center
                        sm:justify-between gap-3 mb-7">

                <div>
                    <h2 class="font-bold text-lg text-text-primary">
                        Penjualan Mingguan
                    </h2>

                    <p class="text-sm text-text-secondary mt-1">
                        Performa penjualan ND Coffee minggu ini
                    </p>
                </div>

                <div class="bg-background rounded-xl px-4 py-2">
                    <p class="text-xs text-text-secondary">
                        Total minggu ini
                    </p>

                    <p class="font-bold text-sm text-text-primary mt-1">
                        <?= $escape($formatCurrency($weeklyTotal)) ?>
                    </p>
                </div>

            </div>

            <!-- Chart -->
            <div class="flex items-end gap-3 sm:gap-5 h-56 px-1">

                <?php foreach ($chartData as $day): ?>

                    <?php
                    $barHeight = $maxRevenue > 0
                        ? ($day['revenue'] / $maxRevenue) * 100
                        : 0;

                    $isToday = $day['date'] === $today;
                    ?>

                    <div class="flex-1 h-full flex flex-col items-center
                                justify-end gap-3 min-w-0">

                        <div class="w-full flex-1 flex items-end justify-center">

                            <div
                                class="w-full max-w-12 rounded-t-lg transition-all
                                    <?= $isToday ? 'bg-primary' : 'bg-[#F2D5DF]' ?>"
                                style="height: <?= $barHeight > 0 ? max(4, $barHeight) : 0 ?>%;"
                                title="<?= $escape($day['label'] . ': ' . $formatCurrency($day['revenue'])) ?>">
                            </div>

                        </div>

                        <span class="text-xs font-medium
                            <?= $isToday ? 'text-primary font-bold' : 'text-text-secondary' ?>">
                            <?= $escape($day['label']) ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

            <div class="border-t border-border mt-5 pt-4 flex
                        items-center justify-between">

                <p class="text-xs text-text-secondary">
                    Data berdasarkan transaksi selesai.
                </p>

                <a href="/reports"
                   class="text-xs font-semibold text-primary hover:underline">
                    Lihat Laporan →
                </a>

            </div>

        </div>

        <!-- Quick Access -->
        <div class="bg-white border border-border rounded-2xl
                    p-6 shadow-sm">

            <h2 class="font-bold text-lg text-text-primary">
                Akses Cepat
            </h2>

            <p class="text-sm text-text-secondary mt-1 mb-6">
                Menu yang sering digunakan
            </p>

            <div class="space-y-3">

                <a href="/pos"
                   class="flex items-center gap-4 p-4 rounded-xl
                          bg-[#FCE8EF] hover:bg-[#F7DCE6] transition">

                    <div class="w-10 h-10 rounded-xl bg-white
                                flex items-center justify-center
                                text-primary font-bold text-xs">
                        POS
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-text-primary">
                            Point of Sale
                        </p>

                        <p class="text-xs text-text-secondary mt-1">
                            Kelola transaksi pelanggan
                        </p>
                    </div>

                </a>

                <a href="/products"
                   class="flex items-center gap-4 p-4 rounded-xl
                          bg-background hover:bg-[#F8EDE9] transition">

                    <div class="w-10 h-10 rounded-xl bg-white
                                flex items-center justify-center
                                text-[#A66B3F] font-bold text-xs">
                        PRD
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-text-primary">
                            Produk
                        </p>

                        <p class="text-xs text-text-secondary mt-1">
                            Kelola produk dan kategori
                        </p>
                    </div>

                </a>

                <a href="/inventory"
                   class="flex items-center gap-4 p-4 rounded-xl
                          bg-[#E9F1E8] hover:bg-[#DFEBDD] transition">

                    <div class="w-10 h-10 rounded-xl bg-white
                                flex items-center justify-center
                                text-[#648263] font-bold text-xs">
                        INV
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-text-primary">
                            Inventory
                        </p>

                        <p class="text-xs text-text-secondary mt-1">
                            Pantau stok bahan baku
                        </p>
                    </div>

                </a>

            </div>

        </div>

    </section>

    <!-- Recent Transactions & Low Stock -->
    <section class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        <!-- Recent Transactions -->
        <div class="xl:col-span-2 bg-white border border-border
                    rounded-2xl p-6 shadow-sm">

            <div class="flex items-center justify-between gap-3 mb-6">

                <div>
                    <h2 class="font-bold text-lg text-text-primary">
                        Transaksi Terbaru
                    </h2>

                    <p class="text-sm text-text-secondary mt-1">
                        Aktivitas transaksi terakhir
                    </p>
                </div>

                <a href="/reports"
                   class="text-xs font-semibold text-primary hover:underline">
                    Lihat Semua →
                </a>

            </div>

            <?php if (empty($recentTransactions)): ?>

                <div class="py-12 text-center">

                    <p class="font-semibold text-text-primary">
                        Belum ada transaksi
                    </p>

                    <p class="text-sm text-text-secondary mt-2">
                        Transaksi yang berhasil akan tampil di sini.
                    </p>

                </div>

            <?php else: ?>

                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead>
                            <tr class="border-b border-border text-left">
                                <th class="pb-4 font-medium text-text-secondary">
                                    Kode Transaksi
                                </th>

                                <th class="pb-4 font-medium text-text-secondary">
                                    Kasir
                                </th>

                                <th class="pb-4 font-medium text-text-secondary">
                                    Pembayaran
                                </th>

                                <th class="pb-4 font-medium text-text-secondary text-right">
                                    Total
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-border">

                            <?php foreach ($recentTransactions as $transaction): ?>

                                <tr>
                                    <td class="py-4 pr-3">
                                        <p class="font-semibold text-text-primary">
                                            <?= $escape($transaction['transaction_code']) ?>
                                        </p>

                                        <p class="text-xs text-text-secondary mt-1">
                                            <?= $escape(date(
                                                'd/m/Y H:i',
                                                strtotime($transaction['created_at'])
                                            )) ?>
                                        </p>
                                    </td>

                                    <td class="py-4 pr-3 text-text-secondary">
                                        <?= $escape($transaction['cashier_name']) ?>
                                    </td>

                                    <td class="py-4 pr-3">
                                        <?php
                                        $paymentLabels = [
                                            'cash' => 'Tunai',
                                            'qris' => 'QRIS',
                                            'debit' => 'Debit',
                                        ];

                                        $paymentMethod = $paymentLabels[
                                            $transaction['payment_method']
                                        ] ?? $transaction['payment_method'];
                                        ?>

                                        <span class="text-xs bg-background
                                                     px-2.5 py-1 rounded-lg">
                                            <?= $escape($paymentMethod) ?>
                                        </span>
                                    </td>

                                    <td class="py-4 text-right whitespace-nowrap">
                                        <p class="font-semibold text-text-primary">
                                            <?= $escape($formatCurrency($transaction['total'])) ?>
                                        </p>

                                        <?php if ($transaction['status'] === 'voided'): ?>
                                            <span class="text-xs text-red-500">
                                                Dibatalkan
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-green-600">
                                                Selesai
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

        <!-- Low Stock Alert -->
        <div class="bg-white border border-border rounded-2xl
                    p-6 shadow-sm">

            <div class="flex items-center justify-between mb-6">

                <div>
                    <h2 class="font-bold text-lg text-text-primary">
                        Peringatan Stok
                    </h2>

                    <p class="text-sm text-text-secondary mt-1">
                        Bahan baku menipis
                    </p>
                </div>

                <span class="px-3 py-1 rounded-full text-xs font-semibold
                    <?= $lowStockCount > 0
                        ? 'bg-[#FCE8EF] text-[#A64D70]'
                        : 'bg-[#E9F1E8] text-[#648263]' ?>">
                    <?= $lowStockCount ?>
                </span>

            </div>

            <?php if (empty($lowStockIngredients)): ?>

                <div class="py-10 text-center">

                    <p class="font-semibold text-text-primary">
                        Stok aman
                    </p>

                    <p class="text-xs text-text-secondary mt-2">
                        Tidak ada bahan baku yang mencapai batas minimum.
                    </p>

                </div>

            <?php else: ?>

                <div class="space-y-4">

                    <?php foreach ($lowStockIngredients as $ingredient): ?>

                        <div class="pb-4 border-b border-border last:border-0">

                            <div class="flex items-center justify-between gap-3">

                                <p class="text-sm font-semibold text-text-primary">
                                    <?= $escape($ingredient['name']) ?>
                                </p>

                                <span class="text-xs font-semibold text-[#B36B55]">
                                    Menipis
                                </span>

                            </div>

                            <div class="flex items-center justify-between mt-2">

                                <p class="text-xs text-text-secondary">
                                    Stok:
                                    <?= $escape($ingredient['current_stock']) ?>
                                    <?= $escape($ingredient['unit']) ?>
                                </p>

                                <p class="text-xs text-text-secondary">
                                    Min:
                                    <?= $escape($ingredient['minimum_stock']) ?>
                                    <?= $escape($ingredient['unit']) ?>
                                </p>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

            <a href="/inventory"
               class="block text-center text-xs font-semibold
                      text-primary hover:underline mt-4">
                Buka Inventory →
            </a>

        </div>

    </section>

</div>
