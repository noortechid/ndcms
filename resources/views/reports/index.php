<?php

declare(strict_types=1);

$formatRupiah = static fn ($value): string =>
    'Rp ' . number_format((float) $value, 0, ',', '.');

$formatNumber = static fn ($value): string =>
    number_format((float) $value, 0, ',', '.');

$totalTransactions = (int) ($summary['total_transactions'] ?? 0);
$totalSales = (float) ($summary['total_sales'] ?? 0);
$totalDiscount = (float) ($summary['total_discount'] ?? 0);
$averageTransaction = (float) ($summary['average_transaction'] ?? 0);

$paymentLabels = [
    'cash' => 'Tunai',
    'qris' => 'QRIS',
    'debit' => 'Debit',
];

$paymentStyles = [
    'cash' => 'bg-amber-50 text-amber-700',
    'qris' => 'bg-rose-50 text-rose-700',
    'debit' => 'bg-stone-100 text-stone-700',
];

$chartSales = array_map(
    static fn (array $row): float => (float) ($row['total_sales'] ?? 0),
    $dailySales
);

$maxSales = max($chartSales ?: [0]);
$maxSales = $maxSales > 0 ? $maxSales : 1;

$periodLabel = (new DateTimeImmutable($startDate))->format('d M Y')
    . ' - '
    . (new DateTimeImmutable($endDate))->format('d M Y');
?>

<style>
    @page {
        size: A4 portrait;
        margin: 12mm;
    }

    @media print {
        body {
            background: white !important;
        }

        body * {
            visibility: hidden !important;
        }

        #report-print-area,
        #report-print-area * {
            visibility: visible !important;
        }

        #report-print-area {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            max-width: none !important;
            padding: 0 !important;
            margin: 0 !important;
            color: #292524 !important;
            background: white !important;
        }

        #report-print-area .print-card {
            border: 1px solid #e7e5e4 !important;
            box-shadow: none !important;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        #report-print-area .print-section {
            break-inside: auto;
            page-break-inside: auto;
        }

        #report-print-area table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 9pt !important;
        }

        #report-print-area th,
        #report-print-area td {
            padding: 7px 8px !important;
            border-bottom: 1px solid #e7e5e4 !important;
            color: #292524 !important;
        }

        #report-print-area thead {
            display: table-header-group;
        }

        #report-print-area tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        #report-print-area .print-muted {
            color: #78716c !important;
        }

        #report-print-area .print-hide {
            display: none !important;
        }

        #report-print-area .print-heading {
            color: #292524 !important;
        }

        #report-print-area .print-bar {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        #report-print-area .print-badge {
            border: 1px solid #d6d3d1 !important;
            background: white !important;
            color: #44403c !important;
        }
    }
</style>

<div class="space-y-3">

    <!-- Action Header -->
    <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between print-hide">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-rose-500">
                Analytics & Reports
            </p>

            <h1 class="mt-1 text-2xl font-bold tracking-tight text-stone-800">
                Laporan Penjualan
            </h1>

            <p class="mt-2 text-sm text-stone-500">
                Pantau performa penjualan dan aktivitas transaksi ND Coffee.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-sm font-semibold text-stone-700 transition hover:bg-stone-50"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8">
                    <path d="M6 9V3h12v6"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <path d="M6 14h12v7H6z"/>
                </svg>
                Print
            </button>

            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-600"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8">
                    <path d="M12 3v12"/>
                    <path d="m7 10 5 5 5-5"/>
                    <path d="M5 21h14"/>
                </svg>
                Export PDF
            </button>
        </div>
    </section>

    <!-- Date Filter -->
    <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm print-hide">
        <div class="mb-4">
            <h2 class="text-sm font-semibold text-stone-800">
                Periode Laporan
            </h2>
            <p class="mt-1 text-xs text-stone-500">
                Pilih rentang tanggal untuk melihat laporan penjualan.
            </p>
        </div>

        <form method="GET" action="/reports"
              class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto] lg:items-end">

            <div>
                <label for="start_date" class="mb-2 block text-xs font-medium text-stone-600">
                    Dari Tanggal
                </label>

                <input
                    type="date"
                    id="start_date"
                    name="start_date"
                    value="<?= htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8') ?>"
                    required
                    class="w-full rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 text-sm text-stone-700 outline-none transition focus:border-rose-300 focus:ring-2 focus:ring-rose-100"
                >
            </div>

            <div>
                <label for="end_date" class="mb-2 block text-xs font-medium text-stone-600">
                    Sampai Tanggal
                </label>

                <input
                    type="date"
                    id="end_date"
                    name="end_date"
                    value="<?= htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8') ?>"
                    required
                    class="w-full rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 text-sm text-stone-700 outline-none transition focus:border-rose-300 focus:ring-2 focus:ring-rose-100"
                >
            </div>

            <button
                type="submit"
                class="rounded-xl bg-rose-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-600"
            >
                Terapkan Filter
            </button>
        </form>
    </section>

    <!-- Printable Report -->
    <div id="report-print-area" class="space-y-6">

        <!-- Report Identity -->
        <section class="border-b border-stone-200 pb-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-rose-500">
                        ND Coffee
                    </p>

                    <h2 class="mt-1 text-xl font-bold text-stone-800 print-heading">
                        Laporan Penjualan
                    </h2>

                    <p class="mt-1 text-sm text-stone-500 print-muted">
                        Ringkasan performa penjualan dan aktivitas transaksi.
                    </p>
                </div>

                <div class="sm:text-right">
                    <p class="text-xs text-stone-400 print-muted">
                        Periode Laporan
                    </p>
                    <p class="mt-1 text-sm font-semibold text-stone-700 print-heading">
                        <?= htmlspecialchars($periodLabel, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <p class="mt-1 text-xs text-stone-400 print-muted">
                        Dicetak: <?= date('d/m/Y H:i') ?>
                    </p>
                </div>
            </div>
        </section>

        <!-- Summary -->
        <section class="print-section">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-stone-800 print-heading">
                    Ringkasan Penjualan
                </h3>
                <p class="mt-1 text-xs text-stone-500 print-muted">
                    Ikhtisar performa pada periode yang dipilih.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                <div class="print-card rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium text-stone-500 print-muted">
                        Total Penjualan
                    </p>
                    <p class="mt-3 text-2xl font-bold text-stone-800 print-heading">
                        <?= $formatRupiah($totalSales) ?>
                    </p>
                    <p class="mt-2 text-xs text-stone-400 print-muted">
                        Omzet transaksi selesai
                    </p>
                </div>

                <div class="print-card rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium text-stone-500 print-muted">
                        Total Transaksi
                    </p>
                    <p class="mt-3 text-2xl font-bold text-stone-800 print-heading">
                        <?= $formatNumber($totalTransactions) ?>
                    </p>
                    <p class="mt-2 text-xs text-stone-400 print-muted">
                        Transaksi berhasil
                    </p>
                </div>

                <div class="print-card rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium text-stone-500 print-muted">
                        Rata-rata Transaksi
                    </p>
                    <p class="mt-3 text-2xl font-bold text-stone-800 print-heading">
                        <?= $formatRupiah($averageTransaction) ?>
                    </p>
                    <p class="mt-2 text-xs text-stone-400 print-muted">
                        Rata-rata nilai pembelian
                    </p>
                </div>

                <div class="print-card rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium text-stone-500 print-muted">
                        Total Diskon
                    </p>
                    <p class="mt-3 text-2xl font-bold text-stone-800 print-heading">
                        <?= $formatRupiah($totalDiscount) ?>
                    </p>
                    <p class="mt-2 text-xs text-stone-400 print-muted">
                        Diskon yang diberikan
                    </p>
                </div>

            </div>
        </section>

        <!-- Daily Sales -->
        <section class="print-card rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-6">
                <h3 class="text-base font-semibold text-stone-800 print-heading">
                    Tren Penjualan Harian
                </h3>
                <p class="mt-1 text-xs text-stone-500 print-muted">
                    Perkembangan omzet berdasarkan tanggal transaksi.
                </p>
            </div>

            <?php if (empty($dailySales)): ?>
                <p class="py-8 text-center text-sm text-stone-400 print-muted">
                    Belum ada data penjualan pada periode ini.
                </p>
            <?php else: ?>
                <div class="space-y-5">
                    <?php foreach ($dailySales as $day): ?>
                        <?php
                        $sales = (float) ($day['total_sales'] ?? 0);
                        $percentage = min(100, ($sales / $maxSales) * 100);
                        $date = new DateTimeImmutable($day['sale_date']);
                        ?>

                        <div>
                            <div class="mb-2 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-stone-700 print-heading">
                                        <?= htmlspecialchars($date->format('d M Y'), ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                    <p class="text-xs text-stone-400 print-muted">
                                        <?= $formatNumber($day['total_transactions'] ?? 0) ?>
                                        transaksi
                                    </p>
                                </div>

                                <p class="text-sm font-semibold text-stone-800 print-heading">
                                    <?= $formatRupiah($sales) ?>
                                </p>
                            </div>

                            <div class="print-bar h-3 overflow-hidden rounded-full bg-rose-50">
                                <div
                                    class="print-bar h-full rounded-full bg-rose-400"
                                    style="width: <?= $percentage ?>%"
                                ></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Payment Summary -->
        <section class="print-section">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-stone-800 print-heading">
                    Metode Pembayaran
                </h3>
                <p class="mt-1 text-xs text-stone-500 print-muted">
                    Ringkasan transaksi berdasarkan metode pembayaran.
                </p>
            </div>

            <?php if (empty($paymentSummary)): ?>
                <div class="print-card rounded-2xl border border-stone-200 bg-white p-5">
                    <p class="text-center text-sm text-stone-400 print-muted">
                        Belum ada data pembayaran pada periode ini.
                    </p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <?php foreach ($paymentSummary as $payment): ?>
                        <?php
                        $method = (string) ($payment['payment_method'] ?? '');
                        $label = $paymentLabels[$method] ?? ucfirst($method);
                        $style = $paymentStyles[$method] ?? 'bg-stone-100 text-stone-700';
                        ?>

                        <div class="print-card rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-stone-700 print-heading">
                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                </p>

                                <span class="print-badge rounded-full px-3 py-1 text-xs font-semibold <?= $style ?>">
                                    <?= $formatNumber($payment['total_transactions'] ?? 0) ?>
                                    transaksi
                                </span>
                            </div>

                            <p class="mt-4 text-xl font-bold text-stone-800 print-heading">
                                <?= $formatRupiah($payment['total_sales'] ?? 0) ?>
                            </p>

                            <p class="mt-1 text-xs text-stone-400 print-muted">
                                Total pembayaran
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Top Products -->
        <section class="print-card overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm print-section">
            <div class="border-b border-stone-100 px-5 py-5 sm:px-6">
                <h3 class="text-base font-semibold text-stone-800 print-heading">
                    Produk Terlaris
                </h3>
                <p class="mt-1 text-xs text-stone-500 print-muted">
                    Produk dengan jumlah penjualan tertinggi.
                </p>
            </div>

            <?php if (empty($topProducts)): ?>
                <p class="px-5 py-8 text-center text-sm text-stone-400 print-muted">
                    Belum ada produk terjual pada periode ini.
                </p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[520px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Produk</th>
                                <th class="px-6 py-3 text-center font-semibold">Terjual</th>
                                <th class="px-6 py-3 text-right font-semibold">Total Penjualan</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-stone-100">
                            <?php foreach ($topProducts as $product): ?>
                                <tr>
                                    <td class="px-6 py-4 font-medium text-stone-700 print-heading">
                                        <?= htmlspecialchars($product['product_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td class="px-6 py-4 text-center text-stone-600 print-heading">
                                        <?= $formatNumber($product['total_quantity'] ?? 0) ?>
                                    </td>

                                    <td class="px-6 py-4 text-right font-semibold text-stone-800 print-heading">
                                        <?= $formatRupiah($product['total_sales'] ?? 0) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <!-- Transaction History -->
        <section class="print-card overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm print-section">
            <div class="border-b border-stone-100 px-5 py-5 sm:px-6">
                <h3 class="text-base font-semibold text-stone-800 print-heading">
                    Riwayat Transaksi
                </h3>
                <p class="mt-1 text-xs text-stone-500 print-muted">
                    Daftar transaksi pada periode yang dipilih.
                </p>
            </div>

            <?php if (empty($transactions)): ?>
                <p class="px-5 py-8 text-center text-sm text-stone-400 print-muted">
                    Tidak ada transaksi pada periode ini.
                </p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[850px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Kode Transaksi</th>
                                <th class="px-5 py-3 font-semibold">Tanggal</th>
                                <th class="px-5 py-3 font-semibold">Kasir</th>
                                <th class="px-5 py-3 font-semibold">Pembayaran</th>
                                <th class="px-5 py-3 text-right font-semibold">Total</th>
                                <th class="px-5 py-3 text-center font-semibold">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-stone-100">
                            <?php foreach ($transactions as $transaction): ?>
                                <?php
                                $status = (string) ($transaction['status'] ?? '');
                                $isCompleted = $status === 'completed';

                                $statusLabel = $isCompleted ? 'Selesai' : 'Void';
                                $statusStyle = $isCompleted
                                    ? 'bg-emerald-50 text-emerald-700'
                                    : 'bg-red-50 text-red-700';

                                $method = (string) ($transaction['payment_method'] ?? '');
                                $paymentLabel = $paymentLabels[$method] ?? ucfirst($method);
                                ?>

                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-medium text-stone-700 print-heading">
                                        <?= htmlspecialchars($transaction['transaction_code'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-stone-500 print-heading">
                                        <?= htmlspecialchars(
                                            (new DateTimeImmutable($transaction['created_at']))->format('d/m/Y H:i'),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-stone-600 print-heading">
                                        <?= htmlspecialchars($transaction['cashier_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-stone-600 print-heading">
                                        <?= htmlspecialchars($paymentLabel, ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-stone-800 print-heading">
                                        <?= $formatRupiah($transaction['total'] ?? 0) ?>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-center">
                                        <span class="print-badge inline-flex rounded-full px-3 py-1 text-xs font-semibold <?= $statusStyle ?>">
                                            <?= $statusLabel ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="border-t border-stone-100 bg-stone-50/70 px-5 py-3 sm:px-6">
                <p class="text-xs text-stone-400 print-muted">
                    Menampilkan maksimal 100 transaksi terbaru.
                </p>
            </div>
        </section>

        <!-- Report Footer -->
        <footer class="border-t border-stone-200 pt-4 text-center">
            <p class="text-xs text-stone-400 print-muted">
                ND Coffee Management System
            </p>
            <p class="mt-1 text-xs text-stone-400 print-muted">
                Laporan ini dihasilkan secara otomatis oleh sistem.
            </p>
        </footer>

    </div>
</div>
