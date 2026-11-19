<?php

use App\Core\Csrf;

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$escape = fn ($value) => htmlspecialchars(
    (string) ($value ?? ''),
    ENT_QUOTES,
    'UTF-8'
);

$items = $purchase['items'] ?? [];

$ingredientOptions = array_map(
    fn ($ingredient) => [
        'id' => (int) $ingredient['id'],
        'code' => $ingredient['ingredient_code'] ?? '',
        'name' => $ingredient['name'],
        'unit' => $ingredient['unit'],
        'cost' => (float) ($ingredient['cost_per_unit'] ?? 0),
    ],
    $ingredients
);
?>

<div class="space-y-6">

    <div>
        <a
            href="/purchases/show?id=<?= (int) $purchase['id'] ?>"
            class="text-sm font-medium text-pink-600 hover:text-pink-700">
            ← Kembali ke Detail Purchase Order
        </a>

        <h1 class="mt-3 text-2xl font-semibold text-stone-800">
            Edit Purchase Order
        </h1>

        <p class="mt-1 text-sm text-stone-500">
            Perbarui informasi dan rincian pemesanan bahan baku.
        </p>
    </div>

    <?php if ($flashError): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <?= $escape($flashError) ?>
        </div>
    <?php endif; ?>

    <form
        action="/purchases/update"
        method="POST"
        id="purchaseForm"
        class="space-y-6">

        <input
            type="hidden"
            name="_token"
            value="<?= $escape(Csrf::token()) ?>">

        <input
            type="hidden"
            name="id"
            value="<?= (int) $purchase['id'] ?>">

        <!-- Purchase Information -->
        <section class="rounded-2xl border border-pink-100 bg-white p-6 shadow-sm">

            <div class="mb-6">
                <h2 class="font-semibold text-stone-800">
                    Informasi Pembelian
                </h2>

                <p class="mt-1 text-sm text-stone-500">
                    Perbarui supplier dan jadwal pembelian.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-stone-700">
                        Supplier <span class="text-red-500">*</span>
                    </label>

                    <select
                        name="supplier_id"
                        required
                        class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100">

                        <option value="">Pilih Supplier</option>

                        <?php foreach ($suppliers as $supplier): ?>

                            <option
                                value="<?= (int) $supplier['id'] ?>"
                                <?= (int) $purchase['supplier_id'] === (int) $supplier['id'] ? 'selected' : '' ?>>

                                <?= $escape($supplier['name']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-stone-700">
                        Tanggal Pembelian <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="date"
                        name="purchase_date"
                        value="<?= $escape($purchase['purchase_date']) ?>"
                        required
                        class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-stone-700">
                        Estimasi Penerimaan
                    </label>

                    <input
                        type="date"
                        name="expected_date"
                        value="<?= $escape($purchase['expected_date'] ?? '') ?>"
                        class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-stone-700">
                        Catatan
                    </label>

                    <textarea
                        name="notes"
                        rows="3"
                        maxlength="1000"
                        placeholder="Tambahkan catatan pembelian..."
                        class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100"><?= $escape($purchase['notes'] ?? '') ?></textarea>
                </div>

            </div>

        </section>

        <!-- Purchase Items -->
        <section class="overflow-hidden rounded-2xl border border-pink-100 bg-white shadow-sm">

            <div class="flex flex-col gap-3 border-b border-stone-100 p-6 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="font-semibold text-stone-800">
                        Rincian Bahan Baku
                    </h2>

                    <p class="mt-1 text-sm text-stone-500">
                        Perbarui bahan baku dan kuantitas yang dipesan.
                    </p>
                </div>

                <button
                    type="button"
                    id="addItem"
                    class="rounded-xl bg-pink-50 px-4 py-2.5 text-sm font-semibold text-pink-600 transition hover:bg-pink-100">
                    + Tambah Bahan
                </button>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full min-w-[850px] text-left text-sm">

                    <thead class="bg-stone-50 text-xs uppercase tracking-wider text-stone-500">

                        <tr>
                            <th class="px-5 py-4">Bahan Baku</th>
                            <th class="px-5 py-4">Kuantitas</th>
                            <th class="px-5 py-4">Harga Satuan</th>
                            <th class="px-5 py-4 text-right">Subtotal</th>
                            <th class="px-5 py-4 text-center">Aksi</th>
                        </tr>

                    </thead>

                    <tbody id="itemRows" class="divide-y divide-stone-100"></tbody>

                </table>

            </div>

            <div class="border-t border-stone-100 bg-stone-50/70 p-6">

                <div class="ml-auto max-w-sm space-y-3">

                    <div class="flex justify-between text-sm text-stone-500">
                        <span>Total Jenis Bahan</span>
                        <span id="totalTypes">0</span>
                    </div>

                    <div class="flex justify-between text-sm text-stone-500">
                        <span>Total Kuantitas</span>
                        <span id="totalQuantity">0</span>
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
                href="/purchases/show?id=<?= (int) $purchase['id'] ?>"
                class="rounded-xl border border-stone-200 bg-white px-6 py-3 text-center text-sm font-semibold text-stone-600 transition hover:bg-stone-50">
                Batal
            </a>

            <button
                type="submit"
                id="submitButton"
                class="rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white transition hover:opacity-90">
                Simpan Perubahan
            </button>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const ingredients = <?= json_encode(
        $ingredientOptions,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    ) ?>;

    const existingItems = <?= json_encode(
        array_map(
            fn ($item) => [
                'ingredient_id' => (int) $item['ingredient_id'],
                'quantity' => (float) $item['quantity'],
                'unit_cost' => (float) $item['unit_cost'],
            ],
            $items
        ),
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    ) ?>;

    const rows = document.getElementById('itemRows');
    const addButton = document.getElementById('addItem');
    const form = document.getElementById('purchaseForm');
    const submitButton = document.getElementById('submitButton');

    let itemIndex = 0;

    const rupiah = value => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(value);

    const formatQuantity = value =>
        new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 3
        }).format(value);

    function addItem(data = {}) {
        const index = itemIndex++;

        const row = document.createElement('tr');
        row.className = 'item-row';

        const options = ingredients.map(ingredient => {
            const selected =
                Number(data.ingredient_id) === ingredient.id;

            return `
                <option
                    value="${ingredient.id}"
                    ${selected ? 'selected' : ''}>
                    ${ingredient.code ? ingredient.code + ' - ' : ''}${ingredient.name}
                </option>
            `;
        }).join('');

        row.innerHTML = `
            <td class="px-5 py-4">
                <select
                    name="items[${index}][ingredient_id]"
                    required
                    class="ingredient-select w-full min-w-48 rounded-lg border border-stone-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-pink-400">

                    <option value="">Pilih Bahan</option>
                    ${options}
                </select>

                <p class="ingredient-unit mt-1 text-xs text-stone-400"></p>
            </td>

            <td class="px-5 py-4">
                <input
                    type="number"
                    name="items[${index}][quantity]"
                    min="0.001"
                    max="999999999999.999"
                    step="0.001"
                    value="${data.quantity ?? ''}"
                    required
                    class="quantity-input w-32 rounded-lg border border-stone-200 px-3 py-2.5 text-sm outline-none focus:border-pink-400">
            </td>

            <td class="px-5 py-4">
                <input
                    type="number"
                    name="items[${index}][unit_cost]"
                    min="0"
                    max="99999999999.9999"
                    step="0.0001"
                    value="${data.unit_cost ?? ''}"
                    required
                    class="cost-input w-36 rounded-lg border border-stone-200 px-3 py-2.5 text-sm outline-none focus:border-pink-400">
            </td>

            <td class="subtotal-cell px-5 py-4 text-right font-semibold text-stone-700">
                Rp 0
            </td>

            <td class="px-5 py-4 text-center">
                <button
                    type="button"
                    class="remove-item rounded-lg px-3 py-2 text-xs font-semibold text-red-500 hover:bg-red-50">
                    Hapus
                </button>
            </td>
        `;

        rows.appendChild(row);

        const select = row.querySelector('.ingredient-select');
        const quantity = row.querySelector('.quantity-input');
        const cost = row.querySelector('.cost-input');
        const unitLabel = row.querySelector('.ingredient-unit');

        function updateUnit() {
            const ingredient = ingredients.find(
                item => item.id === Number(select.value)
            );

            unitLabel.textContent = ingredient
                ? `Satuan: ${ingredient.unit}`
                : '';
        }

        select.addEventListener('change', () => {
            const ingredient = ingredients.find(
                item => item.id === Number(select.value)
            );

            if (ingredient) {
                if (cost.value === '') {
                    cost.value = ingredient.cost;
                }

                updateUnit();
            }

            calculateTotals();
        });

        quantity.addEventListener('input', calculateTotals);
        cost.addEventListener('input', calculateTotals);

        row.querySelector('.remove-item').addEventListener('click', () => {
            row.remove();
            calculateTotals();
        });

        updateUnit();
        calculateTotals();
    }

    function calculateTotals() {
        let grandTotal = 0;
        let totalQuantity = 0;

        const currentRows = [...rows.querySelectorAll('.item-row')];

        currentRows.forEach(row => {
            const quantity = Number(
                row.querySelector('.quantity-input').value
            ) || 0;

            const cost = Number(
                row.querySelector('.cost-input').value
            ) || 0;

            const subtotal = quantity * cost;

            row.querySelector('.subtotal-cell').textContent =
                rupiah(subtotal);

            grandTotal += subtotal;
            totalQuantity += quantity;
        });

        document.getElementById('totalTypes').textContent =
            currentRows.length;

        document.getElementById('totalQuantity').textContent =
            formatQuantity(totalQuantity);

        document.getElementById('grandTotal').textContent =
            rupiah(grandTotal);
    }

    addButton.addEventListener('click', () => addItem());

    existingItems.forEach(item => addItem(item));

    if (existingItems.length === 0) {
        addItem();
    }

    form.addEventListener('submit', event => {
        const currentRows = [...rows.querySelectorAll('.item-row')];

        if (currentRows.length === 0) {
            event.preventDefault();
            alert('Tambahkan minimal satu bahan baku.');
            return;
        }

        const selectedIds = currentRows.map(row =>
            row.querySelector('.ingredient-select').value
        );

        if (selectedIds.some(id => !id)) {
            event.preventDefault();
            alert('Pilih bahan baku pada setiap baris.');
            return;
        }

        if (new Set(selectedIds).size !== selectedIds.length) {
            event.preventDefault();
            alert('Bahan baku tidak boleh dipilih lebih dari satu kali.');
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = 'Menyimpan...';
    });
});
</script>
