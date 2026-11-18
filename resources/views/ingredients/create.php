<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <a href="/ingredients" class="text-sm text-pink-600 hover:text-pink-700">
            ← Kembali ke Bahan Baku
        </a>
        <h1 class="mt-3 text-2xl font-semibold text-stone-800">Tambah Bahan Baku</h1>
        <p class="mt-1 text-sm text-stone-500">
            Masukkan informasi bahan dan biaya per satuan dasar.
        </p>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form action="/ingredients" method="POST"
          class="space-y-5 rounded-2xl border border-pink-100 bg-white p-6 shadow-sm">
        <input type="hidden" name="_token"
               value="<?= htmlspecialchars($csrfToken) ?>">

        <div>
            <label class="mb-2 block text-sm font-medium text-stone-700">
                Nama Bahan Baku *
            </label>
            <input type="text" name="name" required maxlength="150"
                   class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:border-pink-400 focus:outline-none"
                   placeholder="Contoh: Fresh Milk">
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-stone-700">
                Kode Bahan
            </label>
            <input type="text" name="ingredient_code" maxlength="30"
                   class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:border-pink-400 focus:outline-none"
                   placeholder="Contoh: ING-001">
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-700">
                    Satuan Dasar *
                </label>
                <select name="unit" required
                        class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:border-pink-400 focus:outline-none">
                    <option value="g">Gram (g)</option>
                    <option value="ml">Mililiter (ml)</option>
                    <option value="pcs">Pieces (pcs)</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-stone-700">
                    Biaya per Satuan (Rp) *
                </label>
                <input type="number" name="cost_per_unit" required
                       min="0" step="0.0001"
                       class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:border-pink-400 focus:outline-none"
                       placeholder="Contoh: 20">
            </div>
        </div>

<div>
    <label class="mb-2 block text-sm font-medium text-stone-700">
        Minimum Stock *
    </label>
    <input type="number" name="minimum_stock" required
           min="0" max="999999999999" step="0.001"
           value="<?= htmlspecialchars($ingredient['minimum_stock'] ?? '0') ?>"
           class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:border-pink-400 focus:outline-none"
           placeholder="Contoh: 500">

    <p class="mt-1 text-xs text-stone-400">
        Batas minimum persediaan sebelum bahan ditandai menipis.
    </p>
</div>

        <div>
            <label class="mb-2 block text-sm font-medium text-stone-700">
                Deskripsi
            </label>
            <textarea name="description" rows="3"
                      class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:border-pink-400 focus:outline-none"
                      placeholder="Informasi tambahan bahan baku..."></textarea>
        </div>

        <div class="flex justify-end gap-3 border-t border-stone-100 pt-5">
            <a href="/ingredients"
               class="rounded-xl border border-stone-200 px-5 py-3 text-sm font-medium text-stone-600 hover:bg-stone-50">
                Batal
            </a>
            <button type="submit"
                    class="rounded-xl bg-pink-500 px-5 py-3 text-sm font-medium text-white hover:bg-pink-600">
                Simpan Bahan
            </button>
        </div>
    </form>
</div>
