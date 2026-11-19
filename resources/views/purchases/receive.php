<?php

use App\Core\Csrf;

$status = $purchase['status'] ?? '';

$formatQuantity = static function ($value): string {
    return rtrim(rtrim(number_format((float) $value, 3, '.', ','), '0'), '.');
};

?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a
                href="/purchases/show?id=<?= (int) $purchase['id'] ?>"
                class="text-sm font-medium text-primary-dark hover:underline">
                ← Kembali ke Purchase Order
            </a>

            <h1 class="mt-3 text-2xl font-bold text-text-primary">
                Penerimaan Barang
            </h1>

            <p class="mt-1 text-sm text-text-secondary">
                Catat bahan baku yang diterima dari supplier.
            </p>
        </div>

        <div class="rounded-xl bg-blue-50 px-4 py-3">
            <p class="text-xs font-medium text-blue-700">
                Nomor Purchase Order
            </p>
            <p class="mt-1 font-bold text-blue-900">
                <?= htmlspecialchars($purchase['purchase_number']) ?>
            </p>
        </div>
    </div>

    <!-- Informasi PO -->
    <div class="grid gap-4 md:grid-cols-3">

        <div class="rounded-2xl border border-border bg-white p-5">
            <p class="text-sm text-text-secondary">Supplier</p>
            <p class="mt-2 font-semibold text-text-primary">
                <?= htmlspecialchars($purchase['supplier_name']) ?>
            </p>
        </div>

        <div class="rounded-2xl border border-border bg-white p-5">
            <p class="text-sm text-text-secondary">Tanggal Pembelian</p>
            <p class="mt-2 font-semibold text-text-primary">
                <?= date('d M Y', strtotime($purchase['purchase_date'])) ?>
            </p>
        </div>

        <div class="rounded-2xl border border-border bg-white p-5">
            <p class="text-sm text-text-secondary">Status</p>
            <p class="mt-2 font-semibold text-text-primary">
                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $status))) ?>
            </p>
        </div>

    </div>

    <!-- Form Penerimaan -->
    <form
        action="/purchases/receive/store"
        method="POST"
        class="space-y-6">

        <input
            type="hidden"
            name="_token"
            value="<?= htmlspecialchars(Csrf::token()) ?>">

        <input
            type="hidden"
            name="purchase_id"
            value="<?= (int) $purchase['id'] ?>">

        <!-- Daftar Item -->
        <div class="overflow-hidden rounded-2xl border border-border bg-white">

            <div class="border-b border-border px-5 py-4">
                <h2 class="font-semibold text-text-primary">
                    Daftar Bahan Baku
                </h2>
                <p class="mt-1 text-sm text-text-secondary">
                    Masukkan jumlah aktual yang diterima dari supplier.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">

                    <thead class="bg-background-soft text-text-secondary">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Bahan Baku</th>
                            <th class="px-5 py-3 font-semibold">Dipesan</th>
                            <th class="px-5 py-3 font-semibold">Diterima</th>
                            <th class="px-5 py-3 font-semibold">Sisa</th>
                            <th class="px-5 py-3 font-semibold">Terima Sekarang</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-border">

                        <?php foreach ($purchase['items'] as $index => $item): ?>

                            <?php
                            $ordered = (float) $item['quantity'];
                            $received = (float) $item['received_quantity'];
                            $remaining = (float) $item['remaining_quantity'];
                            ?>

                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-text-primary">
                                        <?= htmlspecialchars($item['ingredient_name']) ?>
                                    </p>
                                    <p class="mt-1 text-xs text-text-secondary">
                                        <?= htmlspecialchars($item['ingredient_code']) ?>
                                    </p>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <?= $formatQuantity($ordered) ?>
                                    <?= htmlspecialchars($item['unit']) ?>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <?= $formatQuantity($received) ?>
                                    <?= htmlspecialchars($item['unit']) ?>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-semibold <?= $remaining > 0 ? 'text-amber-600' : 'text-green-600' ?>">
                                        <?= $formatQuantity($remaining) ?>
                                        <?= htmlspecialchars($item['unit']) ?>
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <?php if ($remaining > 0): ?>

                                        <input
                                            type="hidden"
                                            name="items[<?= $index ?>][purchase_item_id]"
                                            value="<?= (int) $item['id'] ?>">

                                        <input
                                            type="number"
                                            name="items[<?= $index ?>][quantity]"
                                            min="0.001"
                                            max="<?= htmlspecialchars(number_format($remaining, 3, '.', '')) ?>"
                                            step="0.001"
                                            placeholder="0"
                                            class="w-36 rounded-xl border border-border bg-white px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">

                                    <?php else: ?>

                                        <span class="text-xs font-medium text-green-600">
                                            Sudah lengkap
                                        </span>

                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>
            </div>

        </div>

        <!-- Catatan -->
        <div class="rounded-2xl border border-border bg-white p-5">
            <label
                for="notes"
                class="block text-sm font-semibold text-text-primary">
                Catatan Penerimaan
            </label>

            <textarea
                name="notes"
                id="notes"
                rows="3"
                placeholder="Contoh: Barang diterima dalam kondisi baik..."
                class="mt-2 w-full rounded-xl border border-border px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"></textarea>
        </div>

        <!-- Actions -->
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

            <a
                href="/purchases/show?id=<?= (int) $purchase['id'] ?>"
                class="rounded-xl border border-border px-5 py-3 text-center text-sm font-semibold text-text-secondary transition hover:bg-background-soft">
                Batal
            </a>

            <button
                type="submit"
                class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:opacity-90">
                Simpan Penerimaan
            </button>

        </div>

    </form>

</div>
