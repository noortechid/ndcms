<div class="max-w-2xl space-y-6">
    <div>
        <a href="/products" class="text-sm text-primary-dark font-medium">
            ← Kembali ke Produk
        </a>
        <h1 class="text-2xl font-bold mt-3">Edit Produk</h1>
        <p class="text-sm text-text-secondary mt-1">Perbarui informasi menu.</p>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="bg-red-50 text-red-700 p-4 rounded-xl text-sm">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form method="POST" action="/products/update"
          class="bg-white border border-border rounded-2xl p-6 space-y-5">
        <input type="hidden" name="_token"
               value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">

        <div>
            <label class="text-sm font-medium">Nama Produk *</label>
            <input type="text" name="name" required maxlength="150"
                   value="<?= htmlspecialchars($product['name']) ?>"
                   class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
        </div>

        <div>
            <label class="text-sm font-medium">Kode Produk</label>
            <input type="text" name="product_code" maxlength="30"
                   value="<?= htmlspecialchars($product['product_code'] ?? '') ?>"
                   class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
        </div>

        <div>
            <label class="text-sm font-medium">Kategori *</label>
            <select name="category_id" required
                    class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>"
                        <?= (int) $product['category_id'] === (int) $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="text-sm font-medium">Harga Jual (Rp) *</label>
            <input type="number" name="price" min="0" step="0.01"
                   required value="<?= htmlspecialchars((string) $product['price']) ?>"
                   class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
        </div>

        <div>
            <label class="text-sm font-medium">Deskripsi</label>
            <textarea name="description" rows="3" maxlength="5000"
                      class="mt-2 w-full border border-border rounded-xl p-3 text-sm"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>

        <div>
            <label class="text-sm font-medium">Ketersediaan</label>
            <select name="is_available"
                    class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
                <option value="1" <?= $product['is_available'] ? 'selected' : '' ?>>
                    Tersedia
                </option>
                <option value="0" <?= !$product['is_available'] ? 'selected' : '' ?>>
                    Habis
                </option>
            </select>
        </div>

<div>
    <label class="text-sm font-medium">Pengelolaan Resep *</label>
    <select name="requires_recipe" required
            class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
        <option value="1"
            <?= (int) $product['requires_recipe'] === 1 ? 'selected' : '' ?>>
            Produk Racikan
        </option>
        <option value="0"
            <?= (int) $product['requires_recipe'] === 0 ? 'selected' : '' ?>>
            Produk Non-racikan
        </option>
    </select>
    <p class="text-xs text-text-secondary mt-1">
        Produk racikan wajib memiliki resep agar stok bahan baku
        otomatis berkurang saat transaksi.
    </p>
</div>

        <div class="flex justify-end gap-3">
            <a href="/products"
               class="px-5 py-3 rounded-xl border border-border text-sm font-semibold">
                Batal
            </a>
            <button class="bg-primary text-white px-5 py-3 rounded-xl text-sm font-semibold">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
