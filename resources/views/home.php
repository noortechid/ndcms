<?php
$pageTitle = 'Dashboard';
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$currentUser = $user ?? [];
$userName = is_array($currentUser) ? ($currentUser['name'] ?? 'Pengguna') : 'Pengguna';
$summary = $statistics ?? [];
$todayRevenue = (float) ($summary['today_revenue'] ?? 0);
$todayTransactions = (int) ($summary['today_transactions'] ?? 0);
$activeProducts = (int) ($summary['active_products'] ?? 0);
$lowStockCount = (int) ($summary['low_stock_count'] ?? 0);
$formatCurrency = static fn ($amount): string => 'Rp ' . number_format((float) $amount, 0, ',', '.');
$weeklySales = $weeklySales ?? [];
$recentTransactions = $recentTransactions ?? [];
$lowStockIngredients = $lowStockIngredients ?? [];

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
    $chartData[] = ['label' => $weekDays[$i], 'date' => $dateKey, 'revenue' => $salesByDate[$dateKey] ?? 0];
}
$maxRevenue = max(array_column($chartData, 'revenue') ?: [0]);
$weeklyTotal = array_sum(array_column($chartData, 'revenue'));
$today = date('Y-m-d');
$chartPoints = [];
foreach ($chartData as $index => $day) {
    $x = 55 + ($index * 85);
    $y = $maxRevenue > 0 ? 175 - (($day['revenue'] / $maxRevenue) * 135) : 175;
    $chartPoints[] = ['x' => $x, 'y' => $y, 'label' => $day['label'], 'revenue' => $day['revenue']];
}
$linePath = '';
foreach ($chartPoints as $index => $point) {
    $linePath .= ($index === 0 ? 'M' : ' L') . $point['x'] . ' ' . round($point['y'], 1);
}
$areaPath = $linePath . ' L565 190 L55 190 Z';
$statCards = [
    ['label' => 'Omzet Hari Ini', 'value' => $formatCurrency($todayRevenue), 'description' => 'Penjualan selesai hari ini', 'icon' => 'wallet', 'width' => 72],
    ['label' => 'Transaksi Hari Ini', 'value' => number_format($todayTransactions, 0, ',', '.'), 'description' => 'Transaksi berhasil', 'icon' => 'receipt', 'width' => 58],
    ['label' => 'Produk Aktif', 'value' => number_format($activeProducts, 0, ',', '.'), 'description' => 'Produk tersedia', 'icon' => 'coffee', 'width' => 80],
    ['label' => 'Stok Menipis', 'value' => number_format($lowStockCount, 0, ',', '.'), 'description' => 'Bahan baku perlu dicek', 'icon' => 'package', 'width' => 42],
];
?>

<div class="dashboard-stack">
    <section class="dashboard-welcome">
        <div>
            <h2>Selamat datang, <?= $escape($userName) ?></h2>
            <p>Ringkasan aktivitas operasional ND Coffee hari ini.</p>
        </div>
        <a href="/pos" class="dashboard-action"><i data-lucide="plus" class="h-4 w-4"></i> Transaksi Baru</a>
    </section>

    <section class="dashboard-stats" aria-label="Ringkasan statistik">
        <?php foreach ($statCards as $card): ?>
            <article class="stat-card">
                <div class="stat-top">
                    <span class="stat-icon"><i data-lucide="<?= $escape($card['icon']) ?>"></i></span>
                    <div><p class="stat-label"><?= $escape($card['label']) ?></p><p class="stat-description"><?= $escape($card['description']) ?></p></div>
                </div>
                <div class="stat-value-row">
                    <strong class="stat-value"><?= $escape($card['value']) ?></strong>
                    <span class="stat-track"><span style="width: <?= (int) $card['width'] ?>%"></span></span>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="dashboard-middle">
        <article class="dashboard-panel">
            <div class="panel-heading">
                <div><h2>Sales Analytics</h2><p>Performa penjualan ND Coffee minggu ini</p></div>
                <a href="/reports" class="panel-link">Lihat laporan</a>
            </div>
            <div class="flex items-center justify-between gap-4 mb-2">
                <p class="text-xs text-[#95877f]">Total minggu ini</p>
                <strong class="text-sm font-semibold text-[#49372e]"><?= $escape($formatCurrency($weeklyTotal)) ?></strong>
            </div>
            <svg class="sales-chart" viewBox="0 0 600 220" role="img" aria-label="Grafik penjualan mingguan">
                <defs><linearGradient id="salesArea" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#c58a70" stop-opacity=".25"/><stop offset="100%" stop-color="#c58a70" stop-opacity="0"/></linearGradient></defs>
                <g stroke="#eee3dd" stroke-width="1" stroke-dasharray="3 5">
                    <?php foreach ([35, 75, 115, 155, 195] as $y): ?><line x1="42" y1="<?= $y ?>" x2="575" y2="<?= $y ?>" /><?php endforeach; ?>
                </g>
                <g fill="#9a8e87" font-size="11" text-anchor="end"><text x="33" y="39">100%</text><text x="33" y="79">75%</text><text x="33" y="119">50%</text><text x="33" y="159">25%</text><text x="33" y="199">0</text></g>
                <path d="<?= $escape($areaPath) ?>" fill="url(#salesArea)" />
                <path d="<?= $escape($linePath) ?>" fill="none" stroke="#bd7959" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                <?php foreach ($chartPoints as $point): ?>
                    <circle cx="<?= $point['x'] ?>" cy="<?= round($point['y'], 1) ?>" r="<?= $point['date'] === $today ? 5 : 3.5 ?>" fill="#bd7959" stroke="#fffdfb" stroke-width="2"><title><?= $escape($point['label'] . ': ' . $formatCurrency($point['revenue'])) ?></title></circle>
                <?php endforeach; ?>
                <g fill="#92847d" font-size="11" text-anchor="middle">
                    <?php foreach ($chartPoints as $point): ?><text x="<?= $point['x'] ?>" y="215"><?= $escape($point['label']) ?></text><?php endforeach; ?>
                </g>
            </svg>
            <div class="mt-1 flex items-center justify-between border-t border-[#f0e7e2] pt-3">
                <p class="text-[10px] text-[#95877f]">Data berdasarkan transaksi selesai.</p>
                <span class="text-[10px] font-medium text-[#8b705f]">Senin – Minggu</span>
            </div>
        </article>

        <article class="dashboard-panel">
            <div class="panel-heading">
                <div><h2>Peringatan Stok</h2><p>Bahan baku yang perlu diperhatikan</p></div>
                <span class="rounded-full bg-[#f8eee9] px-3 py-1 text-[10px] font-semibold text-[#90583f]"><?= $lowStockCount ?></span>
            </div>
            <?php if (empty($lowStockIngredients)): ?>
                <div class="table-empty">Stok aman. Tidak ada bahan baku yang mencapai batas minimum.</div>
            <?php else: ?>
                <div class="stock-list">
                    <?php foreach (array_slice($lowStockIngredients, 0, 5) as $ingredient): ?>
                        <div class="stock-item">
                            <div class="stock-item-top"><strong><?= $escape($ingredient['name']) ?></strong><span class="stock-status">Menipis</span></div>
                            <div class="stock-item-meta"><span>Stok: <?= $escape($ingredient['current_stock']) ?> <?= $escape($ingredient['unit']) ?></span><span>Min: <?= $escape($ingredient['minimum_stock']) ?> <?= $escape($ingredient['unit']) ?></span></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <a href="/inventory" class="panel-link mt-4 inline-flex">Buka inventory →</a>
        </article>
    </section>

    <section class="dashboard-panel table-panel">
        <div class="panel-heading">
            <div><h2>Recent Transactions</h2><p>Aktivitas transaksi terakhir</p></div>
            <a href="/transactions" class="panel-link">Lihat semua</a>
        </div>
        <?php if (empty($recentTransactions)): ?>
            <div class="table-empty">Belum ada transaksi. Transaksi yang berhasil akan tampil di sini.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="dashboard-table">
                    <thead><tr><th>Kode Transaksi</th><th>Kasir</th><th>Date & Time</th><th>Pembayaran</th><th>Status</th><th class="text-right">Total</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentTransactions as $transaction): ?>
                        <?php
                        $paymentLabels = ['cash' => 'Tunai', 'qris' => 'QRIS', 'debit' => 'Debit'];
                        $paymentMethod = $paymentLabels[$transaction['payment_method']] ?? $transaction['payment_method'];
                        $isVoided = $transaction['status'] === 'voided';
                        ?>
                        <tr>
                            <td><strong><?= $escape($transaction['transaction_code']) ?></strong></td>
                            <td><?= $escape($transaction['cashier_name']) ?></td>
                            <td><?= $escape(date('d M Y, H:i', strtotime($transaction['created_at']))) ?></td>
                            <td><?= $escape($paymentMethod) ?></td>
                            <td><span class="<?= $isVoided ? 'text-red-600' : 'text-green-700' ?> font-medium"><?= $isVoided ? 'Dibatalkan' : 'Selesai' ?></span></td>
                            <td class="text-right"><strong><?= $escape($formatCurrency($transaction['total'])) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
