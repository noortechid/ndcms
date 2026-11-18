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

$totalPurchases = count($purchases);
$draftCount = 0;
$orderedCount = 0;
$receivedCount = 0;

foreach ($purchases as $purchase) {
    switch ($purchase['status']) {
        case 'draft':
            $draftCount++;
            break;

        case 'ordered':
        case 'partially_received':
            $orderedCount++;
            break;

        case 'received':
            $receivedCount++;
            break;
    }
}
?>

<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <p class="text-sm font-medium text-pink-600">
                Purchasing Management
            </p>

            <h1 class="mt-1 text-2xl font-semibold text-stone-800">
                Purchase Orders
            </h1>

            <p class="mt-1 text-sm text-stone-500">
                Kelola pemesanan bahan baku dan pantau proses pengadaannya.
            </p>
        </div>

        <a
            href="/purchases/create"
            class="inline-flex items-center justify-center gap-2 rounded-xl
                   bg-primary px-5 py-3 text-sm font-semibold text-white
                   shadow-sm transition hover:opacity-90">

            <span class="text-lg">+</span>
            Buat Purchase Order

        </a>

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

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm text-stone-500">Total Purchase Orders</p>
                <span class="rounded-xl bg-pink-50 p-2 text-pink-600">▧</span>
            </div>

            <p class="mt-4 text-3xl font-semibold text-stone-800">
                <?= number_format($totalPurchases) ?>
            </p>

            <p class="mt-1 text-xs text-stone-400">
                Seluruh pesanan pembelian
            </p>
        </div>

        <div class="rounded-2xl border border-stone-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm text-stone-500">Draft</p>
                <span class="rounded-xl bg-stone-100 p-2 text-stone-600">✎</span>
            </div>

            <p class="mt-4 text-3xl font-semibold text-stone-800">
                <?= number_format($draftCount) ?>
            </p>

            <p class="mt-1 text-xs text-stone-400">
                Belum dikirim ke supplier
            </p>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm text-stone-500">Dalam Proses</p>
                <span class="rounded-xl bg-blue-50 p-2 text-blue-600">◷</span>
            </div>

            <p class="mt-4 text-3xl font-semibold text-stone-800">
                <?= number_format($orderedCount) ?>
            </p>

            <p class="mt-1 text-xs text-stone-400">
                Menunggu atau menerima sebagian
            </p>
        </div>

        <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm text-stone-500">Selesai</p>
                <span class="rounded-xl bg-emerald-50 p-2 text-emerald-600">✓</span>
            </div>

            <p class="mt-4 text-3xl font-semibold text-stone-800">
                <?= number_format($receivedCount) ?>
            </p>

            <p class="mt-1 text-xs text-stone-400">
                Barang telah diterima seluruhnya
            </p>
        </div>

    </div>

    <!-- Purchase Order Table -->
    <div class="overflow-hidden rounded-2xl border border-pink-100 bg-white shadow-sm">

        <div class="flex flex-col gap-3 border-b border-stone-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="font-semibold text-stone-800">
                    Daftar Purchase Orders
                </h2>

                <p class="mt-1 text-sm text-stone-500">
                    Riwayat pemesanan bahan baku ND Coffee.
                </p>
            </div>

            <div class="w-full sm:w-56">
                <select
                    id="statusFilter"
                    class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm text-stone-600 outline-none focus:border-pink-400">

                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="ordered">Menunggu Barang</option>
                    <option value="partially_received">Diterima Sebagian</option>
                    <option value="received">Selesai</option>
                    <option value="cancelled">Dibatalkan</option>

                </select>
            </div>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full min-w-[850px] text-left text-sm">

                <thead class="bg-stone-50 text-xs uppercase tracking-wider text-stone-500">

                    <tr>
                        <th class="px-6 py-4 font-semibold">Purchase Number</th>
                        <th class="px-6 py-4 font-semibold">Supplier</th>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold text-center">Items</th>
                        <th class="px-6 py-4 font-semibold">Total</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>

                </thead>

                <tbody class="divide-y divide-stone-100">

                    <?php if (empty($purchases)): ?>

                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">

                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-pink-50 text-2xl text-pink-500">
                                    ▧
                                </div>

                                <p class="mt-4 font-medium text-stone-700">
                                    Belum ada Purchase Order
                                </p>

                                <p class="mt-1 text-sm text-stone-400">
                                    Mulai dengan membuat pemesanan bahan baku pertama.
                                </p>

                                <a
                                    href="/purchases/create"
                                    class="mt-4 inline-block text-sm font-semibold text-pink-600 hover:text-pink-700">
                                    + Buat Purchase Order
                                </a>

                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($purchases as $purchase): ?>

                            <?php
                            $status = $purchase['status'];
                            $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                            $statusClass = $statusClasses[$status] ?? 'bg-stone-100 text-stone-600';
                            ?>

                            <tr
                                data-status="<?= $escape($status) ?>"
                                class="transition hover:bg-pink-50/30">

                                <td class="px-6 py-4">
                                    <p class="font-semibold text-stone-800">
                                        <?= $escape($purchase['purchase_number']) ?>
                                    </p>

                                    <p class="mt-1 text-xs text-stone-400">
                                        <?= $escape($purchase['created_by_name']) ?>
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="font-medium text-stone-700">
                                        <?= $escape($purchase['supplier_name']) ?>
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-stone-600">
                                    <?= date(
                                        'd M Y',
                                        strtotime($purchase['purchase_date'])
                                    ) ?>
                                </td>

                                <td class="px-6 py-4 text-center text-stone-600">
                                    <?= number_format((int) $purchase['total_items']) ?>
                                </td>

                                <td class="px-6 py-4 font-semibold text-stone-800">
                                    Rp <?= number_format(
                                        (float) $purchase['total_amount'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?= $statusClass ?>">
                                        <?= $escape($statusLabel) ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <a
                                        href="/purchases/show?id=<?= (int) $purchase['id'] ?>"
                                        class="inline-flex items-center rounded-lg border border-pink-200 px-3 py-2 text-xs font-semibold text-pink-600 transition hover:bg-pink-50">
                                        Detail
                                    </a>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <div
            id="emptyFilter"
            class="hidden border-t border-stone-100 px-6 py-8 text-center text-sm text-stone-500">
            Tidak ada Purchase Order dengan status tersebut.
        </div>

        <div class="border-t border-stone-100 bg-stone-50/50 px-6 py-4">
            <p class="text-xs text-stone-400">
                Menampilkan <?= number_format($totalPurchases) ?> Purchase Order.
            </p>
        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filter = document.getElementById('statusFilter');
    const rows = document.querySelectorAll('tbody tr[data-status]');
    const emptyFilter = document.getElementById('emptyFilter');

    if (!filter) {
        return;
    }

    filter.addEventListener('change', function () {
        const selectedStatus = this.value;
        let visibleCount = 0;

        rows.forEach(function (row) {
            const match = !selectedStatus ||
                row.dataset.status === selectedStatus;

            row.classList.toggle('hidden', !match);

            if (match) {
                visibleCount++;
            }
        });

        emptyFilter.classList.toggle(
            'hidden',
            visibleCount > 0 || rows.length === 0
        );
    });
});
</script>
