<?php

declare(strict_types=1);

$filters = $filters ?? [];
$transactions = $transactions ?? [];

$summary = $summary ?? [
    'total_transactions' => 0,
    'completed_transactions' => 0,
    'voided_transactions' => 0,
    'total_sales' => 0,
];

$keyword = $filters['keyword'] ?? '';
$dateFrom = $filters['date_from'] ?? '';
$dateTo = $filters['date_to'] ?? '';
$status = $filters['status'] ?? '';
$paymentMethod = $filters['payment_method'] ?? '';

$formatCurrency = static function ($amount): string {
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
};

$formatDate = static function (string $date): string {
    return date('d M Y, H:i', strtotime($date));
};

?>

<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#59443E]">
                Riwayat Transaksi
            </h1>
            <p class="mt-1 text-sm text-[#9A8178]">
                Kelola dan pantau seluruh aktivitas transaksi ND Coffee.
            </p>
        </div>

        <a
            href="/pos"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#A96F75] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#925C63]"
        >
            <i class="fa-solid fa-cash-register"></i>
            Buka POS
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-2xl border border-[#F0E5E1] bg-white p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-[#9A8178]">
                    Total Transaksi
                </p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#F8EDEF] text-[#A96F75]">
                    <i class="fa-solid fa-receipt"></i>
                </span>
            </div>

            <p class="mt-4 text-2xl font-bold text-[#59443E]">
                <?= number_format((int) $summary['total_transactions']) ?>
            </p>
        </div>

        <div class="rounded-2xl border border-[#F0E5E1] bg-white p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-[#9A8178]">
                    Transaksi Selesai
                </p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#EDF5EE] text-[#62866A]">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
            </div>

            <p class="mt-4 text-2xl font-bold text-[#59443E]">
                <?= number_format((int) $summary['completed_transactions']) ?>
            </p>
        </div>

        <div class="rounded-2xl border border-[#F0E5E1] bg-white p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-[#9A8178]">
                    Transaksi Void
                </p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FFF2E8] text-[#C18455]">
                    <i class="fa-solid fa-ban"></i>
                </span>
            </div>

            <p class="mt-4 text-2xl font-bold text-[#59443E]">
                <?= number_format((int) $summary['voided_transactions']) ?>
            </p>
        </div>

        <div class="rounded-2xl border border-[#F0E5E1] bg-white p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-[#9A8178]">
                    Total Penjualan
                </p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#F8EDEF] text-[#A96F75]">
                    <i class="fa-solid fa-wallet"></i>
                </span>
            </div>

            <p class="mt-4 text-2xl font-bold text-[#59443E]">
                <?= $formatCurrency($summary['total_sales']) ?>
            </p>
        </div>

    </div>

    <!-- Filters -->
    <div class="rounded-2xl border border-[#F0E5E1] bg-white p-5">

        <div class="mb-5">
            <h2 class="font-semibold text-[#59443E]">
                Filter Transaksi
            </h2>
            <p class="mt-1 text-sm text-[#9A8178]">
                Temukan transaksi berdasarkan kata kunci, tanggal, dan status.
            </p>
        </div>

        <form action="/transactions" method="GET">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">

                <div>
                    <label class="mb-2 block text-sm font-medium text-[#705951]">
                        Pencarian
                    </label>
                    <input
                        type="text"
                        name="keyword"
                        value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Kode transaksi / kasir"
                        class="w-full rounded-xl border border-[#EADBD5] px-4 py-3 text-sm text-[#59443E] outline-none transition focus:border-[#B98288] focus:ring-2 focus:ring-[#F8EDEF]"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-[#705951]">
                        Dari Tanggal
                    </label>
                    <input
                        type="date"
                        name="date_from"
                        value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>"
                        class="w-full rounded-xl border border-[#EADBD5] px-4 py-3 text-sm text-[#59443E] outline-none focus:border-[#B98288]"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-[#705951]">
                        Sampai Tanggal
                    </label>
                    <input
                        type="date"
                        name="date_to"
                        value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>"
                        class="w-full rounded-xl border border-[#EADBD5] px-4 py-3 text-sm text-[#59443E] outline-none focus:border-[#B98288]"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-[#705951]">
                        Status
                    </label>
                    <select
                        name="status"
                        class="w-full rounded-xl border border-[#EADBD5] px-4 py-3 text-sm text-[#59443E] outline-none focus:border-[#B98288]"
                    >
                        <option value="">Semua Status</option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>
                            Selesai
                        </option>
                        <option value="voided" <?= $status === 'voided' ? 'selected' : '' ?>>
                            Void
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-[#705951]">
                        Pembayaran
                    </label>
                    <select
                        name="payment_method"
                        class="w-full rounded-xl border border-[#EADBD5] px-4 py-3 text-sm text-[#59443E] outline-none focus:border-[#B98288]"
                    >
                        <option value="">Semua Metode</option>
                        <option value="cash" <?= $paymentMethod === 'cash' ? 'selected' : '' ?>>
                            Cash
                        </option>
                        <option value="qris" <?= $paymentMethod === 'qris' ? 'selected' : '' ?>>
                            QRIS
                        </option>
                        <option value="debit" <?= $paymentMethod === 'debit' ? 'selected' : '' ?>>
                            Debit
                        </option>
                    </select>
                </div>

            </div>

            <div class="mt-5 flex flex-wrap gap-3">
                <button
                    type="submit"
                    class="rounded-xl bg-[#A96F75] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#925C63]"
                >
                    <i class="fa-solid fa-filter mr-2"></i>
                    Terapkan Filter
                </button>

                <a
                    href="/transactions"
                    class="rounded-xl border border-[#EADBD5] px-5 py-2.5 text-sm font-semibold text-[#80665E] transition hover:bg-[#FBF7F5]"
                >
                    Reset
                </a>
            </div>
        </form>

    </div>

    <!-- Transaction Table -->
    <div class="overflow-hidden rounded-2xl border border-[#F0E5E1] bg-white">

        <div class="border-b border-[#F0E5E1] px-5 py-4">
            <h2 class="font-semibold text-[#59443E]">
                Daftar Transaksi
            </h2>
            <p class="mt-1 text-sm text-[#9A8178]">
                <?= count($transactions) ?> transaksi ditemukan.
            </p>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full min-w-[900px] text-left">

                <thead class="bg-[#FBF7F5]">
                    <tr class="text-xs font-semibold uppercase tracking-wide text-[#9A8178]">
                        <th class="px-5 py-4">Transaksi</th>
                        <th class="px-5 py-4">Tanggal</th>
                        <th class="px-5 py-4">Kasir</th>
                        <th class="px-5 py-4">Pembayaran</th>
                        <th class="px-5 py-4">Total</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F4ECE8]">

                    <?php if (empty($transactions)): ?>

                        <tr>
                            <td colspan="7" class="px-5 py-14 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#F8EDEF] text-[#A96F75]">
                                    <i class="fa-solid fa-receipt text-xl"></i>
                                </div>

                                <p class="mt-4 font-semibold text-[#59443E]">
                                    Belum ada transaksi
                                </p>

                                <p class="mt-1 text-sm text-[#9A8178]">
                                    Transaksi yang sesuai filter akan muncul di sini.
                                </p>
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($transactions as $transaction): ?>

                            <tr class="transition hover:bg-[#FFFCFB]">

                                <td class="px-5 py-4">
                                    <p class="font-semibold text-[#59443E]">
                                        <?= htmlspecialchars($transaction['transaction_code'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                    <p class="mt-1 text-xs text-[#A38D85]">
                                        #<?= (int) $transaction['id'] ?>
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-sm text-[#80665E]">
                                    <?= $formatDate($transaction['created_at']) ?>
                                </td>

                                <td class="px-5 py-4 text-sm text-[#80665E]">
                                    <?= htmlspecialchars($transaction['cashier_username'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td class="px-5 py-4">
                                    <?php
                                    $paymentLabels = [
                                        'cash' => 'Cash',
                                        'qris' => 'QRIS',
                                        'debit' => 'Debit',
                                    ];
                                    ?>
                                    <span class="rounded-lg bg-[#F8F2EF] px-3 py-1.5 text-xs font-medium text-[#80665E]">
                                        <?= $paymentLabels[$transaction['payment_method']] ?? htmlspecialchars($transaction['payment_method'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-sm font-semibold text-[#59443E]">
                                    <?= $formatCurrency($transaction['total']) ?>
                                </td>

                                <td class="px-5 py-4">
                                    <?php if ($transaction['status'] === 'completed'): ?>
                                        <span class="inline-flex rounded-full bg-[#EDF5EE] px-3 py-1.5 text-xs font-semibold text-[#62866A]">
                                            Selesai
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex rounded-full bg-[#FFF0ED] px-3 py-1.5 text-xs font-semibold text-[#B96D62]">
                                            Void
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <a
                                        href="/transactions/show?id=<?= (int) $transaction['id'] ?>"
                                        class="inline-flex items-center gap-2 rounded-lg border border-[#EADBD5] px-3 py-2 text-xs font-semibold text-[#A96F75] transition hover:bg-[#F8EDEF]"
                                    >
                                        <i class="fa-solid fa-eye"></i>
                                        Detail
                                    </a>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>
