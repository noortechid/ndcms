<?php

use App\Core\Csrf;

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;

unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$escape = fn ($value) => htmlspecialchars(
    (string) ($value ?? ''),
    ENT_QUOTES,
    'UTF-8'
);

$statusLabels = [
    'draft' => 'Draft',
    'ordered' => 'Menunggu Barang',
    'partially_received' => 'Diterima Sebagian',
    'received' => 'Selesai',
    'cancelled' => 'Dibatalkan',
];

$statusClasses = [
    'draft' => 'bg-stone-100 text-stone-600',
    'ordered' => 'bg-blue-50 text-blue-700',
    'partially_received' => 'bg-amber-50 text-amber-700',
    'received' => 'bg-emerald-50 text-emerald-700',
    'cancelled' => 'bg-red-50 text-red-700',
];

$status = $purchase['status'];
$statusLabel = $statusLabels[$status] ?? ucfirst($status);
$statusClass = $statusClasses[$status] ?? 'bg-stone-100 text-stone-600';

$totalQuantity = array_sum(
    array_map(
        fn ($item) => (float) $item['quantity'],
        $purchase['items']
    )
);
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

        <div>
            <a
                href="/purchases"
                class="text-sm font-medium text-pink-600 hover:text-pink-700">
                ← Kembali ke Purchase Orders
            </a>

            <div class="mt-3 flex flex-wrap items-center gap-3">

                <h1 class="text-2xl font-semibold text-stone-800">
                    Purchase Order
                </h1>

                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?= $statusClass ?>">
                    <?= $escape($statusLabel) ?>
                </span>

            </div>

            <p class="mt-1 text-sm text-stone-500">
                Detail informasi pemesanan bahan baku.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">

            <?php if ($status === 'draft'): ?>

                <a
                    href="/purchases/edit?id=<?= (int) $purchase['id'] ?>"
                    class="rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-sm font-semibold text-stone-600 transition hover:bg-stone-50">
                    Edit PO
                </a>

                <form
                    action="/purchases/status"
                    method="POST"
                    onsubmit="return confirm('Kirim Purchase Order ini ke supplier?')">

                    <input
                        type="hidden"
                        name="_token"
                        value="<?= $escape(Csrf::token()) ?>">

                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int) $purchase['id'] ?>">

                    <input
                        type="hidden"
                        name="status"
                        value="ordered">

                    <button
                        type="submit"
                        class="rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                        Kirim ke Supplier
                    </button>

                </form>

                <form
                    action="/purchases/status"
                    method="POST"
                    onsubmit="return confirm('Yakin ingin membatalkan Purchase Order ini?')">

                    <input
                        type="hidden"
                        name="_token"
                        value="<?= $escape(Csrf::token()) ?>">

                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int) $purchase['id'] ?>">

                    <input
                        type="hidden"
                        name="status"
                        value="cancelled">

                    <button
                        type="submit"
                        class="rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                        Batalkan PO
                    </button>

                </form>

            <?php elseif (in_array($status, ['ordered', 'partially_received'], true)): ?>

                <a
                    href="/purchases/receive?id=<?= (int) $purchase['id'] ?>"
                    class="rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                    Terima Barang
                </a>

            <?php endif; ?>

        </div>

    </div>

    </div>

    <!-- Flash Messages -->
    <?php if ($flashSuccess): ?>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <?= $escape($flashSuccess) ?>
        </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <?= $escape($flashError) ?>
        </div>
    <?php endif; ?>

    <!-- Purchase Number -->
    <section class="rounded-2xl border border-pink-100 bg-white p-6 shadow-sm">

        <p class="text-sm text-stone-500">
            Nomor Purchase Order
        </p>

        <div class="mt-2 flex flex-wrap items-center justify-between gap-3">

            <h2 class="text-xl font-bold tracking-wide text-stone-800">
                <?= $escape($purchase['purchase_number']) ?>
            </h2>

            <span class="rounded-lg bg-pink-50 px-3 py-2 text-xs font-semibold text-pink-600">
                ND Coffee Purchasing
            </span>

        </div>

    </section>

    <!-- Information Cards -->
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

        <!-- Supplier Information -->
        <section class="rounded-2xl border border-pink-100 bg-white p-6 shadow-sm">

            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-pink-50 text-pink-600">
                    ♙
                </div>

                <div>
                    <h2 class="font-semibold text-stone-800">
                        Informasi Supplier
                    </h2>

                    <p class="text-xs text-stone-400">
                        Pemasok bahan baku
                    </p>
                </div>
            </div>

            <div class="mt-6 space-y-4">

                <div>
                    <p class="text-xs text-stone-400">Nama Supplier</p>
                    <p class="mt-1 font-semibold text-stone-800">
                        <?= $escape($purchase['supplier_name']) ?>
                    </p>
                </div>

                <div>
                    <p class="text-xs text-stone-400">Kode Supplier</p>
                    <p class="mt-1 text-sm text-stone-700">
                        <?= $escape($purchase['supplier_code'] ?: '-') ?>
                    </p>
                </div>

                <div>
                    <p class="text-xs text-stone-400">Nomor Telepon</p>
                    <p class="mt-1 text-sm text-stone-700">
                        <?= $escape($purchase['supplier_phone'] ?: '-') ?>
                    </p>
                </div>

                <div>
                    <p class="text-xs text-stone-400">Email</p>
                    <p class="mt-1 text-sm text-stone-700">
                        <?= $escape($purchase['supplier_email'] ?: '-') ?>
                    </p>
                </div>

            </div>

        </section>

        <!-- Purchase Information -->
        <section class="rounded-2xl border border-pink-100 bg-white p-6 shadow-sm">

            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-pink-50 text-pink-600">
                    ▧
                </div>

                <div>
                    <h2 class="font-semibold text-stone-800">
                        Informasi Pembelian
                    </h2>

                    <p class="text-xs text-stone-400">
                        Jadwal dan pencatatan pesanan
                    </p>
                </div>
            </div>

            <div class="mt-6 space-y-4">

                <div>
                    <p class="text-xs text-stone-400">Tanggal Pembelian</p>
                    <p class="mt-1 text-sm font-medium text-stone-700">
                        <?= date('d M Y', strtotime($purchase['purchase_date'])) ?>
                    </p>
                </div>

                <div>
                    <p class="text-xs text-stone-400">Estimasi Penerimaan</p>
                    <p class="mt-1 text-sm font-medium text-stone-700">
                        <?= !empty($purchase['expected_date'])
                            ? date('d M Y', strtotime($purchase['expected_date']))
                            : '-' ?>
                    </p>
                </div>

                <div>
                    <p class="text-xs text-stone-400">Dibuat Oleh</p>
                    <p class="mt-1 text-sm font-medium text-stone-700">
                        <?= $escape($purchase['created_by_name']) ?>
                    </p>
                </div>

                <div>
                    <p class="text-xs text-stone-400">Catatan</p>
                    <p class="mt-1 whitespace-pre-line text-sm text-stone-700">
                        <?= $escape($purchase['notes'] ?: '-') ?>
                    </p>
                </div>

            </div>

        </section>

    </div>

    <!-- Purchase Items -->
    <section class="overflow-hidden rounded-2xl border border-pink-100 bg-white shadow-sm">

        <div class="border-b border-stone-100 px-6 py-5">

            <h2 class="font-semibold text-stone-800">
                Rincian Bahan Baku
            </h2>

            <p class="mt-1 text-sm text-stone-500">
                Daftar bahan baku yang dipesan dalam Purchase Order ini.
            </p>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full min-w-[750px] text-left text-sm">

                <thead class="bg-stone-50 text-xs uppercase tracking-wider text-stone-500">

                    <tr>
                        <th class="px-6 py-4 font-semibold">Bahan Baku</th>
                        <th class="px-6 py-4 font-semibold">Kode</th>
                        <th class="px-6 py-4 font-semibold text-right">Kuantitas</th>
                        <th class="px-6 py-4 font-semibold text-right">Harga Satuan</th>
                        <th class="px-6 py-4 font-semibold text-right">Subtotal</th>
                    </tr>

                </thead>

                <tbody class="divide-y divide-stone-100">

                    <?php foreach ($purchase['items'] as $item): ?>

                        <?php
                        $subtotal = (float) $item['quantity'] *
                            (float) $item['unit_cost'];
                        ?>

                        <tr class="hover:bg-pink-50/30">

                            <td class="px-6 py-4 font-medium text-stone-800">
                                <?= $escape($item['ingredient_name']) ?>
                            </td>

                            <td class="px-6 py-4 text-stone-500">
                                <?= $escape($item['ingredient_code'] ?: '-') ?>
                            </td>

                            <td class="px-6 py-4 text-right text-stone-700">
                                <?= number_format((float) $item['quantity'], 3, ',', '.') ?>
                                <?= $escape($item['unit']) ?>
                            </td>

                            <td class="px-6 py-4 text-right text-stone-700">
                                Rp <?= number_format(
                                    (float) $item['unit_cost'],
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </td>

                            <td class="px-6 py-4 text-right font-semibold text-stone-800">
                                Rp <?= number_format(
                                    $subtotal,
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <!-- Summary -->
        <div class="border-t border-stone-100 bg-stone-50/70 px-6 py-6">

            <div class="ml-auto max-w-sm space-y-3">

                <div class="flex justify-between text-sm text-stone-500">
                    <span>Total Jenis Bahan</span>
                    <span><?= number_format(count($purchase['items'])) ?></span>
                </div>

                <div class="flex justify-between text-sm text-stone-500">
                    <span>Total Kuantitas</span>
                    <span><?= number_format($totalQuantity, 3, ',', '.') ?></span>
                </div>

                <div class="flex items-center justify-between border-t border-stone-200 pt-4">

                    <span class="font-semibold text-stone-800">
                        Total Pembelian
                    </span>

                    <span class="text-xl font-bold text-pink-600">
                        Rp <?= number_format(
                            (float) $purchase['total_amount'],
                            0,
                            ',',
                            '.'
                        ) ?>
                    </span>

                </div>

            </div>

        </div>

    </section>

    <!-- Status Information -->
    <?php if ($status === 'ordered'): ?>

        <div class="rounded-xl border border-blue-100 bg-blue-50 p-5">

            <h3 class="font-semibold text-blue-800">
                Purchase Order telah dikirim
            </h3>

<p class="mt-1 text-sm text-blue-700">
    Purchase Order telah dikirim ke supplier. Kamu dapat mencatat penerimaan barang melalui tombol Terima Barang.
</p>

        </div>

    <?php elseif ($status === 'partially_received'): ?>

        <div class="rounded-xl border border-amber-100 bg-amber-50 p-5">

            <h3 class="font-semibold text-amber-800">
                Penerimaan sebagian
            </h3>

<p class="mt-1 text-sm text-amber-700">
    Sebagian bahan baku telah diterima. Kamu dapat melanjutkan penerimaan untuk sisa pesanan.
</p>

        </div>

    <?php elseif ($status === 'received'): ?>

        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-5">

            <h3 class="font-semibold text-emerald-800">
                Purchase Order selesai
            </h3>

            <p class="mt-1 text-sm text-emerald-700">
                Seluruh barang dalam pesanan telah diterima.
            </p>

        </div>

    <?php elseif ($status === 'cancelled'): ?>

        <div class="rounded-xl border border-red-100 bg-red-50 p-5">

            <h3 class="font-semibold text-red-800">
                Purchase Order dibatalkan
            </h3>

            <p class="mt-1 text-sm text-red-700">
                Pesanan ini telah dibatalkan dan tidak dapat diproses lebih lanjut.
            </p>

        </div>

    <?php endif; ?>

</div>
