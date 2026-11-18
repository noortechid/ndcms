<?php

use App\Core\Csrf;

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$escape = fn ($value) => htmlspecialchars(
    (string) ($value ?? ''),
    ENT_QUOTES,
    'UTF-8'
);
?>

<div class="mx-auto max-w-6xl space-y-6">

    <!-- Header -->
    <div>
        <a
            href="/purchases"
            class="text-sm font-medium text-pink-600 hover:text-pink-700">
            ← Kembali ke Purchase Orders
        </a>

        <h1 class="mt-3 text-2xl font-semibold text-stone-800">
            Buat Purchase Order
        </h1>

        <p class="mt-1 text-sm text-stone-500">
            Buat pesanan bahan baku baru kepada supplier.
        </p>
    </div>

    <!-- Error Message -->
    <?php if ($flashError): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <?= $escape($flashError) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($suppliers)): ?>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            Belum ada supplier aktif. Tambahkan supplier terlebih dahulu sebelum membuat Purchase Order.

            <a href="/suppliers/create" class="ml-1 font-semibold underline">
                Tambah Supplier
            </a>
        </div>
    <?php endif; ?>

    <?php if (empty($ingredients)): ?>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            Belum ada data bahan baku. Tambahkan bahan baku terlebih dahulu sebelum membuat Purchase Order.

            <a href="/ingredients/create" class="ml-1 font-semibold underline">
                Tambah Bahan Baku
            </a>
        </div>
    <?php endif; ?>

    <form
        action="/purchases/store"
        method="POST"
        id="purchaseForm"
        class="space-y-6">

        <input
            type="hidden"
            name="_token"
            value="<?= $escape(Csrf::token()) ?>">

        <!-- Purchase Information -->
        <section class="overflow-hidden rounded-2xl border border-pink-100 bg-white shadow-sm">

            <div class="border-b border-stone-100 px-6 py-5">
                <h2 class="font-semibold text-stone-800">
                    Informasi Pembelian
                </h2>

                <p class="mt-1 text-sm text-stone-500">
                    Tentukan supplier dan jadwal pembelian.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">

                <div class="sm:col-span-2">
                    <label
                        for="supplier_id"
                        class="mb-2 block text-sm font-medium text-stone-700">
                        Supplier <span class="text-red-500">*</span>
                    </label>

                    <select
                        name="supplier_id"
                        id="supplier_id"
                        required
                        class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100">

                        <option value="">Pilih Supplier</option>

                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= (int) $supplier['id'] ?>">
                                <?= $escape($supplier['name']) ?>
                                <?php if (!empty($supplier['supplier_code'])): ?>
                                    — <?= $escape($supplier['supplier_code']) ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div>
                    <label
                        for="purchase_date"
                        class="mb-2 block text-sm font-medium text-stone-700">
                        Tanggal Pembelian <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="date"
                        name="purchase_date"
                        id="purchase_date"
                        value="<?= date('Y-m-d') ?>"
                        required
                        class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100">
                </div>

                <div>
                    <label
                        for="expected_date"
                        class="mb-2 block text-sm font-medium text-stone-700">
                        Estimasi Penerimaan
                    </label>

                    <input
                        type="date"
                        name="expected_date"
                        id="expected_date"
                        min="<?= date('Y-m-d') ?>"
                        class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100">

                    <p class="mt-1 text-xs text-stone-400">
                        Opsional, dapat dikosongkan.
                    </p>
                </div>

                <div class="sm:col-span-2">
                    <label
                        for="notes"
                        class="mb-2 block text-sm font-medium text-stone-700">
                        Catatan Pembelian
                    </label>

                    <textarea
                        name="notes"
                        id="notes"
                        rows="3"
                        maxlength="5000"
                        placeholder="Catatan tambahan untuk pesanan ini..."
                        class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100"></textarea>
                </div>

            </div>

        </section>

        <!-- Purchase Items -->
        <section class="overflow-hidden rounded-2xl border border-pink-100 bg-white shadow-sm">

            <div class="flex flex-col gap-3 border-b border-stone-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="font-semibold text-stone-800">
                        Detail Bahan Baku
                    </h2>

                    <p class="mt-1 text-sm text-stone-500">
                        Tambahkan bahan baku yang ingin dipesan.
                    </p>
                </div>

                <button
                    type="button"
                    id="addItem"
                    <?= empty($ingredients) ? 'disabled' : '' ?>
                    class="rounded-xl border border-pink-200 bg-pink-50 px-4 py-2.5 text-sm font-semibold text-pink-600 transition hover:bg-pink-100 disabled:cursor-not-allowed disabled:opacity-50">

                    + Tambah Bahan

                </button>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full min-w-[850px] text-left text-sm">

                    <thead class="bg-stone-50 text-xs uppercase tracking-wider text-stone-500">

                        <tr>
                            <th class="px-5 py-4 font-semibold">Bahan Baku</th>
                            <th class="px-5 py-4 font-semibold">Kuantitas</th>
                            <th class="px-5 py-4 font-semibold">Harga Satuan</th>
                            <th class="px-5 py-4 font-semibold">Subtotal</th>
                            <th class="px-5 py-4 text-center font-semibold">Aksi</th>
                        </tr>

                    </thead>

                    <tbody id="purchaseItems" class="divide-y divide-stone-100">

                    </tbody>

                </table>

            </div>

            <div
                id="emptyItems"
                class="px-6 py-10 text-center">

                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-pink-50 text-xl text-pink-500">
                    +
                </div>

                <p class="mt-3 font-medium text-stone-700">
                    Belum ada bahan baku
                </p>

                <p class="mt-1 text-sm text-stone-400">
                    Klik tombol Tambah Bahan untuk memulai.
                </p>

            </div>

            <!-- Total -->
            <div class="border-t border-stone-100 bg-stone-50/70 px-6 py-5">

                <div class="ml-auto max-w-sm space-y-3">

                    <div class="flex items-center justify-between text-sm text-stone-500">
                        <span>Total Jenis Bahan</span>
                        <span id="totalTypes">0</span>
                    </div>

                    <div class="flex items-center justify-between border-t border-stone-200 pt-4">
                        <span class="font-semibold text-stone-800">
                            Total Pembelian
                        </span>

                        <span
                            id="grandTotal"
                            class="text-xl font-bold text-pink-600">
                            Rp 0
                        </span>
                    </div>

                </div>

            </div>

        </section>

        <!-- Actions -->
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

            <a
                href="/purchases"
                class="rounded-xl border border-stone-200 bg-white px-5 py-3 text-center text-sm font-medium text-stone-600 transition hover:bg-stone-50">
                Batal
            </a>

            <button
                type="submit"
                id="submitPurchase"
                <?= empty($suppliers) || empty($ingredients) ? 'disabled' : '' ?>
                class="rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">
                Simpan sebagai Draft
            </button>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const ingredients = <?= json_encode(
        array_map(
            fn ($ingredient) => [
                'id' => (int) $ingredient['id'],
                'name' => $ingredient['name'],
                'code' => $ingredient['ingredient_code'] ?? '',
                'unit' => $ingredient['unit'],
                'cost' => (float) $ingredient['cost_per_unit'],
            ],
            $ingredients
        ),
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_AMP |
        JSON_HEX_QUOT
    ) ?>;

    const itemsContainer = document.getElementById('purchaseItems');
    const addButton = document.getElementById('addItem');
    const emptyItems = document.getElementById('emptyItems');
    const totalTypes = document.getElementById('totalTypes');
    const grandTotal = document.getElementById('grandTotal');
    const purchaseDate = document.getElementById('purchase_date');
    const expectedDate = document.getElementById('expected_date');
    const form = document.getElementById('purchaseForm');

    let itemIndex = 0;

    const formatRupiah = function (value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0
        }).format(value);
    };

    const escapeHtml = function (value) {
        return String(value).replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    };

    const updateTotals = function () {
        const rows = itemsContainer.querySelectorAll('tr');
        let total = 0;

        rows.forEach(function (row) {
            const quantity = parseFloat(
                row.querySelector('.quantity').value
            ) || 0;

            const unitCost = parseFloat(
                row.querySelector('.unit-cost').value
            ) || 0;

            const subtotal = quantity * unitCost;

            row.querySelector('.item-subtotal').textContent =
                formatRupiah(subtotal);

            total += subtotal;
        });

        totalTypes.textContent = rows.length;
        grandTotal.textContent = formatRupiah(total);

        emptyItems.classList.toggle('hidden', rows.length > 0);

        return rows.length;
    };

    const addItem = function () {

        const index = itemIndex++;

        const ingredientOptions = ingredients.map(function (ingredient) {

            const label = ingredient.code
                ? ingredient.code + ' — ' + ingredient.name
                : ingredient.name;

            return `
                <option
                    value="${ingredient.id}"
                    data-cost="${ingredient.cost}">
                    ${escapeHtml(label)} (${escapeHtml(ingredient.unit)})
                </option>
            `;

        }).join('');

        const row = document.createElement('tr');

        row.innerHTML = `
            <td class="px-5 py-4">
                <select
                    name="items[${index}][ingredient_id]"
                    required
                    class="ingredient-select w-full rounded-lg border border-stone-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-pink-400">

                    <option value="">Pilih bahan baku</option>
                    ${ingredientOptions}

                </select>
            </td>

            <td class="px-5 py-4">
                <div class="flex items-center gap-2">
                    <input
                        type="number"
                        name="items[${index}][quantity]"
                        min="0.001"
                        max="999999999999.999"
                        step="0.001"
                        required
                        placeholder="0"
                        class="quantity w-28 rounded-lg border border-stone-200 px-3 py-2.5 text-sm outline-none focus:border-pink-400">

                </div>
            </td>

            <td class="px-5 py-4">
                <input
                    type="number"
                    name="items[${index}][unit_cost]"
                    min="0"
                    max="99999999999.9999"
                    step="0.0001"
                    required
                    placeholder="0"
                    class="unit-cost w-36 rounded-lg border border-stone-200 px-3 py-2.5 text-sm outline-none focus:border-pink-400">
            </td>

            <td class="item-subtotal whitespace-nowrap px-5 py-4 font-semibold text-stone-700">
                Rp 0
            </td>

            <td class="px-5 py-4 text-center">
                <button
                    type="button"
                    class="remove-item rounded-lg px-3 py-2 text-xs font-semibold text-red-500 transition hover:bg-red-50">
                    Hapus
                </button>
            </td>
        `;

        itemsContainer.appendChild(row);

        const ingredientSelect = row.querySelector('.ingredient-select');
        const unitCostInput = row.querySelector('.unit-cost');

        ingredientSelect.addEventListener('change', function () {

            const selectedOption = this.options[this.selectedIndex];

            if (this.value !== '') {
                unitCostInput.value = selectedOption.dataset.cost || 0;
            }

            updateTotals();
        });

        row.querySelectorAll('.quantity, .unit-cost').forEach(function (input) {
            input.addEventListener('input', updateTotals);
        });

        row.querySelector('.remove-item').addEventListener('click', function () {
            row.remove();
            updateTotals();
        });

        updateTotals();
    };

    addButton.addEventListener('click', addItem);

    purchaseDate.addEventListener('change', function () {
        expectedDate.min = this.value;

        if (expectedDate.value && expectedDate.value < this.value) {
            expectedDate.value = '';
        }
    });

    expectedDate.min = purchaseDate.value;

    form.addEventListener('submit', function (event) {

        const rows = itemsContainer.querySelectorAll('tr');

        if (rows.length === 0) {
            event.preventDefault();
            alert('Tambahkan minimal satu bahan baku.');
            return;
        }

        const usedIngredients = new Set();

        for (const row of rows) {
            const ingredientId = row.querySelector('.ingredient-select').value;

            if (usedIngredients.has(ingredientId)) {
                event.preventDefault();
                alert('Bahan baku yang sama tidak boleh ditambahkan lebih dari satu kali.');
                return;
            }

            usedIngredients.add(ingredientId);
        }

        const submitButton = document.getElementById('submitPurchase');

        submitButton.disabled = true;
        submitButton.textContent = 'Menyimpan...';
    });

});
</script>
