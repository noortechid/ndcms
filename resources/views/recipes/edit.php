<div class="mx-auto max-w-4xl space-y-6">
    <div>
        <a href="/recipes" class="text-sm text-pink-600 hover:text-pink-700">
            ← Kembali ke Recipe & HPP
        </a>
        <h1 class="mt-3 text-2xl font-semibold text-stone-800">Edit Resep</h1>
        <p class="mt-1 text-sm text-stone-500">
            Produk: <?= htmlspecialchars($recipe['product_name']) ?>
        </p>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form action="/recipes/update" method="POST"
          class="space-y-6" id="recipeForm">
        <input type="hidden" name="_token"
               value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="product_id"
               value="<?= (int) $recipe['product_id'] ?>">

        <div class="rounded-2xl border border-pink-100 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-stone-800">Informasi Produk</h2>

            <p class="mt-2 text-sm text-stone-600">
                <?= htmlspecialchars($recipe['product_name']) ?>
            </p>

            <label class="mb-2 mt-5 block text-sm font-medium text-stone-700">
                Catatan Resep
            </label>
            <textarea name="notes" rows="2"
                      class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:border-pink-400 focus:outline-none"><?= htmlspecialchars($recipe['notes'] ?? '') ?></textarea>
        </div>

        <div class="rounded-2xl border border-pink-100 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-stone-800">Komposisi Bahan</h2>
                    <p class="mt-1 text-xs text-stone-500">
                        Atur takaran bahan untuk satu porsi.
                    </p>
                </div>

                <button type="button" id="addIngredient"
                        class="rounded-xl bg-pink-50 px-4 py-2 text-sm font-medium text-pink-600 hover:bg-pink-100">
                    + Tambah Bahan
                </button>
            </div>

            <div id="ingredientList" class="space-y-3">
                <?php foreach ($recipe['items'] as $item): ?>
                    <div class="ingredient-row grid grid-cols-1 gap-3 rounded-xl bg-stone-50 p-4 sm:grid-cols-[1fr_150px_auto]">
                        <select name="ingredient_id[]" required
                                class="ingredient-select rounded-xl border border-stone-200 bg-white px-3 py-3 text-sm">
                            <option value="">Pilih bahan baku</option>

                            <?php foreach ($ingredients as $ingredient): ?>
                                <option value="<?= (int) $ingredient['id'] ?>"
                                        data-unit="<?= htmlspecialchars($ingredient['unit']) ?>"
                                    <?= (int) $ingredient['id'] === (int) $item['ingredient_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ingredient['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div>
                            <input type="number" name="quantity[]" min="0.001"
                                   step="0.001" required
                                   value="<?= htmlspecialchars((string) $item['quantity']) ?>"
                                   class="w-full rounded-xl border border-stone-200 px-3 py-3 text-sm">

                            <p class="unit-label mt-1 text-xs text-stone-400">
                                Satuan: <?= htmlspecialchars($item['unit']) ?>
                            </p>
                        </div>

                        <button type="button"
                                class="remove-ingredient text-sm text-red-500 hover:text-red-700">
                            Hapus
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="/recipes"
               class="rounded-xl border border-stone-200 px-5 py-3 text-sm text-stone-600">
                Batal
            </a>
            <button type="submit"
                    class="rounded-xl bg-pink-500 px-5 py-3 text-sm font-medium text-white hover:bg-pink-600">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
(() => {
    const list = document.getElementById('ingredientList');
    const addButton = document.getElementById('addIngredient');

    const options = <?= json_encode(array_map(
        fn ($ingredient) => [
            'id' => (int) $ingredient['id'],
            'name' => $ingredient['name'],
            'unit' => $ingredient['unit'],
        ],
        $ingredients
    ), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    function createRow() {
        const row = document.createElement('div');
        row.className = 'ingredient-row grid grid-cols-1 gap-3 rounded-xl bg-stone-50 p-4 sm:grid-cols-[1fr_150px_auto]';

        const select = document.createElement('select');
        select.name = 'ingredient_id[]';
        select.required = true;
        select.className = 'ingredient-select rounded-xl border border-stone-200 bg-white px-3 py-3 text-sm';
        select.add(new Option('Pilih bahan baku', ''));

        options.forEach(item => {
            select.add(new Option(item.name, item.id));
            select.options[select.options.length - 1].dataset.unit = item.unit;
        });

        const wrapper = document.createElement('div');
        const input = document.createElement('input');

        input.type = 'number';
        input.name = 'quantity[]';
        input.min = '0.001';
        input.step = '0.001';
        input.required = true;
        input.placeholder = 'Takaran';
        input.className = 'w-full rounded-xl border border-stone-200 px-3 py-3 text-sm';

        const label = document.createElement('p');
        label.className = 'unit-label mt-1 text-xs text-stone-400';
        label.textContent = 'Satuan: -';

        wrapper.append(input, label);

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'remove-ingredient text-sm text-red-500 hover:text-red-700';
        remove.textContent = 'Hapus';

        row.append(select, wrapper, remove);
        return row;
    }

    addButton.addEventListener('click', () => {
        list.appendChild(createRow());
    });

    list.addEventListener('click', event => {
        const button = event.target.closest('.remove-ingredient');

        if (button && list.querySelectorAll('.ingredient-row').length > 1) {
            button.closest('.ingredient-row').remove();
        }
    });

    list.addEventListener('change', event => {
        if (event.target.matches('.ingredient-select')) {
            const selected = event.target.selectedOptions[0];
            const label = event.target.closest('.ingredient-row')
                .querySelector('.unit-label');

            label.textContent = selected.value
                ? 'Satuan: ' + selected.dataset.unit
                : 'Satuan: -';
        }
    });
})();
</script>
