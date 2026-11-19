<div class="max-w-2xl space-y-6">
    <div>
        <a href="/products" class="text-sm text-primary-dark font-medium">
            ← Kembali ke Produk
        </a>
        <h1 class="text-2xl font-bold mt-3">Tambah Produk</h1>
        <p class="text-sm text-text-secondary mt-1">Tambahkan menu baru ke katalog ND Coffee.</p>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="bg-red-50 text-red-700 p-4 rounded-xl text-sm">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <?php if (!$categories): ?>
        <div class="bg-yellow-50 text-yellow-800 p-4 rounded-xl text-sm">
            Belum ada kategori aktif. Tambahkan kategori terlebih dahulu.
            <a href="/categories/create" class="font-semibold underline">Buat kategori</a>
        </div>
    <?php else: ?>

        <form method="POST" action="/products"
              class="bg-white border border-border rounded-2xl p-6 space-y-5">
            <input type="hidden" name="_token"
                   value="<?= htmlspecialchars($csrfToken) ?>">

            <div>
                <label class="text-sm font-medium">Nama Produk *</label>
                <input type="text" name="name" required maxlength="150"
                       placeholder="Contoh: Caramel Latte"
                       class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Kode Produk</label>
                <input type="text" name="product_code" maxlength="30"
                       placeholder="Contoh: CF-001"
                       class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
                <p class="text-xs text-text-secondary mt-1">
                    Opsional, harus unik jika diisi.
                </p>
            </div>

            <div>
                <label class="text-sm font-medium">Kategori *</label>
                <select name="category_id" required
                        class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
                    <option value="">Pilih Kategori</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Harga Jual (Rp) *</label>
                <input type="number" name="price" min="0" step="0.01"
                       required placeholder="25000"
                       class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Deskripsi</label>
                <textarea name="description" rows="3" maxlength="5000"
                          class="mt-2 w-full border border-border rounded-xl p-3 text-sm"
                          placeholder="Deskripsi singkat produk..."></textarea>
            </div>

            <div>
                <label class="text-sm font-medium">Ketersediaan</label>
                <select name="is_available"
                        class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
                    <option value="1">Tersedia</option>
                    <option value="0">Habis</option>
                </select>
            </div>

<div>
    <label class="text-sm font-medium">Pengelolaan Resep *</label>
    <select name="requires_recipe" required
            class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
        <option value="1" selected>Produk Racikan</option>
        <option value="0">Produk Non-racikan</option>
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
                    Simpan Produk
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>
