<?php

use App\Core\Auth;
use App\Core\Csrf;

$canManage = Auth::can('inventory');

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;

unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$formatStock = fn ($value) => rtrim(
    rtrim(number_format((float) $value, 3, ',', '.'), '0'),
    ','
);
?>

<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-800">
                Inventory
            </h1>
            <p class="mt-1 text-sm text-stone-500">
                Pantau stok dan aktivitas bahan baku ND Coffee.
            </p>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($flashSuccess): ?>
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            <?= htmlspecialchars($flashSuccess) ?>
        </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <?= htmlspecialchars($flashError) ?>
        </div>
    <?php endif; ?>

    <!-- Summary -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-stone-500">
                Total Bahan Baku
            </p>

            <p class="mt-2 text-3xl font-semibold text-stone-800">
                <?= number_format($totalIngredients) ?>
            </p>

            <p class="mt-1 text-xs text-stone-400">
                Seluruh bahan yang terdaftar
            </p>
        </div>

        <div class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-stone-500">
                Stok Menipis
            </p>

            <p class="mt-2 text-3xl font-semibold text-orange-600">
                <?= number_format($lowStockCount) ?>
            </p>

            <p class="mt-1 text-xs text-stone-400">
                Stok mencapai atau di bawah batas minimum
            </p>
        </div>
    </div>

    <!-- Stock List -->
    <section class="overflow-hidden rounded-2xl border border-pink-100 bg-white shadow-sm">

        <div class="border-b border-stone-100 px-5 py-4">
            <h2 class="font-semibold text-stone-800">
                Stock Overview
            </h2>
            <p class="mt-1 text-sm text-stone-500">
                Kondisi stok bahan baku saat ini.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-pink-50/60 text-xs uppercase tracking-wide text-stone-500">
                    <tr>
                        <th class="px-5 py-3">Bahan Baku</th>
                        <th class="px-5 py-3">Stok Saat Ini</th>
                        <th class="px-5 py-3">Minimum</th>
                        <th class="px-5 py-3">Status</th>
                        <?php if ($canManage): ?>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-100">
                    <?php if (empty($ingredients)): ?>
                        <tr>
                            <td colspan="<?= $canManage ? 5 : 4 ?>"
                                class="px-5 py-10 text-center text-stone-400">
                                Belum ada bahan baku terdaftar.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($ingredients as $ingredient): ?>
                        <?php
                            $current = (float) $ingredient['current_stock'];
                            $minimum = (float) $ingredient['minimum_stock'];

                            $isLow = $minimum > 0 && $current <= $minimum;
                        ?>

                        <tr class="hover:bg-pink-50/30">
                            <td class="px-5 py-4">
                                <p class="font-medium text-stone-800">
                                    <?= htmlspecialchars($ingredient['name']) ?>
                                </p>

                                <p class="mt-1 text-xs text-stone-400">
                                    <?= htmlspecialchars($ingredient['ingredient_code'] ?? '-') ?>
                                </p>
                            </td>

                            <td class="px-5 py-4 font-medium text-stone-700">
                                <?= $formatStock($current) ?>
                                <?= htmlspecialchars($ingredient['unit']) ?>
                            </td>

                            <td class="px-5 py-4 text-stone-500">
                                <?= $formatStock($minimum) ?>
                                <?= htmlspecialchars($ingredient['unit']) ?>
                            </td>

                            <td class="px-5 py-4">
                                <?php if ($isLow): ?>
                                    <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-medium text-orange-700">
                                        Low Stock
                                    </span>
                                <?php else: ?>
                                    <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">
                                        Normal
                                    </span>
                                <?php endif; ?>
                            </td>

                            <?php if ($canManage): ?>
                                <td class="px-5 py-4 text-right">
                                    <button
                                        type="button"
                                        onclick="openStockModal(
                                            <?= (int) $ingredient['id'] ?>,
                                            <?= htmlspecialchars(json_encode($ingredient['name']), ENT_QUOTES, 'UTF-8') ?>,
                                            <?= htmlspecialchars(json_encode($ingredient['unit']), ENT_QUOTES, 'UTF-8') ?>
                                        )"
                                        class="rounded-lg bg-pink-100 px-3 py-2 text-xs font-medium text-pink-700 hover:bg-pink-200">
                                        Kelola Stok
                                    </button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Movement History -->
    <section class="overflow-hidden rounded-2xl border border-pink-100 bg-white shadow-sm">

        <div class="border-b border-stone-100 px-5 py-4">
            <h2 class="font-semibold text-stone-800">
                Recent Stock Movements
            </h2>

            <p class="mt-1 text-sm text-stone-500">
                Riwayat perubahan stok terbaru.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-pink-50/60 text-xs uppercase tracking-wide text-stone-500">
                    <tr>
                        <th class="px-5 py-3">Waktu</th>
                        <th class="px-5 py-3">Bahan</th>
                        <th class="px-5 py-3">Jenis</th>
                        <th class="px-5 py-3">Perubahan</th>
                        <th class="px-5 py-3">Stok Akhir</th>
                        <th class="px-5 py-3">Pengguna</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-100">
                    <?php if (empty($movements)): ?>
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-stone-400">
                                Belum ada riwayat pergerakan stok.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($movements as $movement): ?>
                        <?php
                            $typeLabels = [
                                'in' => 'Stock In',
                                'out' => 'Stock Out',
                                'adjustment' => 'Adjustment',
                            ];

                            $typeColors = [
                                'in' => 'bg-green-50 text-green-700',
                                'out' => 'bg-red-50 text-red-700',
                                'adjustment' => 'bg-orange-50 text-orange-700',
                            ];

                            $type = $movement['movement_type'];
                        ?>

                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 text-stone-500">
                                <?= htmlspecialchars(date('d/m/Y H:i', strtotime($movement['created_at']))) ?>
                            </td>

                            <td class="px-5 py-4 font-medium text-stone-800">
                                <?= htmlspecialchars($movement['ingredient_name']) ?>
                            </td>

                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-medium <?= $typeColors[$type] ?? 'bg-stone-100 text-stone-600' ?>">
                                    <?= $typeLabels[$type] ?? htmlspecialchars($type) ?>
                                </span>
                            </td>

                            <td class="px-5 py-4 text-stone-700">
                                <?= $formatStock($movement['quantity']) ?>
                                <?= htmlspecialchars($movement['ingredient_unit']) ?>
                            </td>

                            <td class="px-5 py-4 font-medium text-stone-700">
                                <?= $formatStock($movement['stock_after']) ?>
                                <?= htmlspecialchars($movement['ingredient_unit']) ?>
                            </td>

                            <td class="px-5 py-4 text-stone-500">
                                <?= htmlspecialchars($movement['username']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

<?php if ($canManage): ?>
<!-- Stock Management Modal -->
<div
    id="stockModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-stone-900/40 p-4"
    onclick="if (event.target === this) closeStockModal()">

    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">

        <div class="mb-5 flex items-start justify-between">
            <div>
                <h2 class="text-lg font-semibold text-stone-800">
                    Kelola Stok
                </h2>

                <p id="modalIngredient" class="mt-1 text-sm text-stone-500"></p>
            </div>

            <button
                type="button"
                onclick="closeStockModal()"
                class="text-xl text-stone-400 hover:text-stone-700">
                &times;
            </button>
        </div>

        <div class="mb-5 grid grid-cols-3 gap-2">
            <button
                type="button"
                onclick="setMovementType('in')"
                id="typeIn"
                class="rounded-xl border border-stone-200 px-3 py-3 text-sm font-medium text-stone-600">
                Stock In
            </button>

            <button
                type="button"
                onclick="setMovementType('out')"
                id="typeOut"
                class="rounded-xl border border-stone-200 px-3 py-3 text-sm font-medium text-stone-600">
                Stock Out
            </button>

            <button
                type="button"
                onclick="setMovementType('adjustment')"
                id="typeAdjustment"
                class="rounded-xl border border-stone-200 px-3 py-3 text-sm font-medium text-stone-600">
                Adjustment
            </button>
        </div>

        <form id="stockForm" method="POST">
            <input
                type="hidden"
                name="_token"
                value="<?= htmlspecialchars(Csrf::token()) ?>">

            <input
                type="hidden"
                name="ingredient_id"
                id="modalIngredientId">

            <div class="space-y-4">

                <div>
                    <label for="quantity" id="quantityLabel"
                        class="mb-2 block text-sm font-medium text-stone-700">
                        Jumlah
                    </label>

                    <div class="flex items-center gap-2">
                        <input
                            type="number"
                            name="quantity"
                            id="quantity"
                            min="0.001"
                            step="0.001"
                            required
                            class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm outline-none focus:border-pink-400 focus:ring-2 focus:ring-pink-100">

                        <span id="modalUnit" class="text-sm text-stone-500"></span>
                    </div>

                    <p id="quantityHint" class="mt-1 text-xs text-stone-400">
                        Masukkan jumlah stok.
                    </p>
                </div>

                <div>
                    <label for="notes" class="mb-2 block text-sm font-medium text-stone-700">
                        Catatan
                    </label>

                    <textarea
                        name="notes"
                        id="notes"
                        rows="3"
                        maxlength="2000"
                        placeholder="Contoh: Pembelian bahan baku..."
                        class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm outline-none focus:border-pink-400 focus:ring-2 focus:ring-pink-100"></textarea>

                    <p id="notesHint" class="mt-1 text-xs text-stone-400">
                        Opsional.
                    </p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        onclick="closeStockModal()"
                        class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm font-medium text-stone-600 hover:bg-stone-50">
                        Batal
                    </button>

                    <button
                        type="submit"
                        id="submitMovement"
                        class="rounded-xl bg-pink-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-pink-700">
                        Simpan Perubahan
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
    let selectedIngredientId = null;
    let selectedType = 'in';

    const stockRoutes = {
        in: '/inventory/stock-in',
        out: '/inventory/stock-out',
        adjustment: '/inventory/adjust'
    };

    function openStockModal(id, name, unit) {
        selectedIngredientId = id;

        document.getElementById('modalIngredientId').value = id;
        document.getElementById('modalIngredient').textContent = name;
        document.getElementById('modalUnit').textContent = unit;

        document.getElementById('stockForm').reset();
        document.getElementById('modalIngredientId').value = id;

        setMovementType('in');

        const modal = document.getElementById('stockModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeStockModal() {
        const modal = document.getElementById('stockModal');

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function setMovementType(type) {
        selectedType = type;

        const form = document.getElementById('stockForm');
        const quantity = document.getElementById('quantity');
        const label = document.getElementById('quantityLabel');
        const hint = document.getElementById('quantityHint');
        const notesHint = document.getElementById('notesHint');

        form.action = stockRoutes[type];

        document.querySelectorAll('#typeIn, #typeOut, #typeAdjustment')
            .forEach(button => {
                button.className =
                    'rounded-xl border border-stone-200 px-3 py-3 text-sm font-medium text-stone-600';
            });

        const activeButton = document.getElementById(
            type === 'in'
                ? 'typeIn'
                : type === 'out'
                    ? 'typeOut'
                    : 'typeAdjustment'
        );

        activeButton.className =
            'rounded-xl border border-pink-300 bg-pink-50 px-3 py-3 text-sm font-medium text-pink-700';

        if (type === 'in') {
            label.textContent = 'Jumlah Stok Masuk';
            quantity.min = '0.001';
            quantity.step = '0.001';
            hint.textContent = 'Jumlah stok yang akan ditambahkan.';
            notesHint.textContent = 'Opsional. Contoh: Pembelian bahan baku.';
        } else if (type === 'out') {
            label.textContent = 'Jumlah Stok Keluar';
            quantity.min = '0.001';
            quantity.step = '0.001';
            hint.textContent = 'Jumlah stok yang akan dikurangi.';
            notesHint.textContent = 'Opsional. Contoh: Bahan rusak atau terbuang.';
        } else {
            label.textContent = 'Stok Aktual';
            quantity.min = '0';
            quantity.step = '0.001';
            hint.textContent = 'Masukkan jumlah stok fisik hasil stock opname.';
            notesHint.textContent = 'Wajib. Jelaskan alasan koreksi stok.';
        }

        document.getElementById('notes').required =
            type === 'adjustment';
    }
</script>
<?php endif; ?>
