<div class="max-w-3xl space-y-3">

    <!-- Header -->
    <div>
        <a href="/products"
           class="inline-flex items-center gap-2 text-sm text-primary-dark font-medium hover:underline">
            ← Kembali ke Produk
        </a>

        <h1 class="text-2xl font-bold text-text-primary mt-3">
            Tambah Produk
        </h1>

        <p class="text-sm text-text-secondary mt-1">
            Tambahkan menu baru ke katalog ND Coffee.
        </p>
    </div>

    <!-- Flash Error -->
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="bg-red-50 border border-red-100 text-red-700 p-4 rounded-xl text-sm">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <?php if (!$categories): ?>

        <div class="bg-yellow-50 text-yellow-800 p-4 rounded-xl text-sm">
            Belum ada kategori aktif. Tambahkan kategori terlebih dahulu.
            <a href="/categories/create" class="font-semibold underline">
                Buat kategori
            </a>
        </div>

    <?php else: ?>

        <form method="POST"
              action="/products"
              enctype="multipart/form-data"
              class="bg-white border border-border rounded-2xl p-6 space-y-6">

            <input type="hidden"
                   name="_token"
                   value="<?= htmlspecialchars($csrfToken) ?>">

            <!-- Product Image -->
            <div class="space-y-3">

                <div>
                    <label class="block text-sm font-semibold text-text-primary">
                        Gambar Produk
                    </label>
                    <p class="text-xs text-text-secondary mt-1">
                        Gunakan gambar yang jelas agar menu lebih mudah dikenali.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-5 items-start">

                    <!-- Image Preview -->
                    <div class="w-full sm:w-44 aspect-square rounded-2xl border border-border bg-[#F8EEE9] overflow-hidden flex items-center justify-center shrink-0">

                        <img id="product-image-preview"
                             src=""
                             alt="Preview gambar produk"
                             class="hidden w-full h-full object-cover">

                        <div id="product-image-placeholder"
                             class="flex flex-col items-center gap-2 text-primary">
                            <span class="text-4xl">☕</span>
                            <span class="text-xs text-text-secondary">
                                Belum ada gambar
                            </span>
                        </div>

                    </div>

                    <!-- Upload Control -->
                    <div class="flex-1 w-full space-y-3">

                        <label for="image"
                               class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-border rounded-2xl p-6 cursor-pointer hover:bg-background hover:border-primary transition">

                            <span class="text-2xl text-primary">↑</span>

                            <span class="text-sm font-semibold text-text-primary">
                                Pilih gambar produk
                            </span>

                            <span class="text-xs text-text-secondary text-center">
                                Klik untuk memilih gambar dari perangkat
                            </span>

                            <input type="file"
                                   name="image"
                                   id="image"
                                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                   class="hidden">
                        </label>

                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs text-text-secondary">
                                JPG, JPEG, PNG, atau WebP. Maksimal 2 MB.
                            </p>

                            <button type="button"
                                    id="remove-product-image"
                                    class="hidden text-xs font-semibold text-red-600 hover:underline">
                                Hapus gambar
                            </button>
                        </div>

                        <p id="product-image-filename"
                           class="text-xs text-text-secondary"></p>

                        <p id="product-image-error"
                           class="hidden text-xs text-red-600"></p>

                    </div>
                </div>
            </div>

            <div class="border-t border-border"></div>

            <!-- Product Name -->
            <div>
                <label class="block text-sm font-medium text-text-primary mb-2">
                    Nama Produk *
                </label>

                <input type="text"
                       name="name"
                       required
                       maxlength="150"
                       placeholder="Contoh: Caramel Latte"
                       class="w-full border border-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>

            <!-- Product Code -->
            <div>
                <label class="block text-sm font-medium text-text-primary mb-2">
                    Kode Produk
                </label>

                <input type="text"
                       name="product_code"
                       maxlength="30"
                       placeholder="Contoh: CF-001"
                       class="w-full border border-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">

                <p class="text-xs text-text-secondary mt-2">
                    Opsional, harus unik jika diisi.
                </p>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-sm font-medium text-text-primary mb-2">
                    Kategori *
                </label>

                <select name="category_id"
                        required
                        class="w-full border border-border rounded-xl px-4 py-3 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/20">

                    <option value="">Pilih Kategori</option>

                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <!-- Price -->
            <div>
                <label class="block text-sm font-medium text-text-primary mb-2">
                    Harga Jual (Rp) *
                </label>

                <input type="number"
                       name="price"
                       min="0"
                       step="0.01"
                       required
                       placeholder="25000"
                       class="w-full border border-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-text-primary mb-2">
                    Deskripsi
                </label>

                <textarea name="description"
                          rows="3"
                          maxlength="5000"
                          placeholder="Deskripsi singkat produk..."
                          class="w-full border border-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"></textarea>
            </div>

            <!-- Availability -->
            <div>
                <label class="block text-sm font-medium text-text-primary mb-2">
                    Ketersediaan
                </label>

                <select name="is_available"
                        class="w-full border border-border rounded-xl px-4 py-3 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="1">Tersedia</option>
                    <option value="0">Habis</option>
                </select>
            </div>

            <!-- Recipe -->
            <div>
                <label class="block text-sm font-medium text-text-primary mb-2">
                    Pengelolaan Resep *
                </label>

                <select name="requires_recipe"
                        required
                        class="w-full border border-border rounded-xl px-4 py-3 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/20">

                    <option value="1" selected>Produk Racikan</option>
                    <option value="0">Produk Non-racikan</option>

                </select>

                <p class="text-xs text-text-secondary mt-2">
                    Produk racikan wajib memiliki resep agar stok bahan baku
                    otomatis berkurang saat transaksi.
                </p>
            </div>

            <!-- Actions -->
            <div class="border-t border-border pt-5 flex justify-end gap-3">

                <a href="/products"
                   class="px-5 py-3 rounded-xl border border-border text-sm font-semibold text-text-secondary hover:bg-background transition">
                    Batal
                </a>

                <button type="submit"
                        class="bg-primary text-white px-5 py-3 rounded-xl text-sm font-semibold hover:bg-primary-dark transition">
                    Simpan Produk
                </button>

            </div>

        </form>

    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('image');
    const preview = document.getElementById('product-image-preview');
    const placeholder = document.getElementById('product-image-placeholder');
    const removeButton = document.getElementById('remove-product-image');
    const filename = document.getElementById('product-image-filename');
    const error = document.getElementById('product-image-error');

    if (!input) return;

    const maxSize = 2 * 1024 * 1024;
    const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    let previewUrl = null;

    function clearPreview() {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        preview.src = '';
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
        removeButton.classList.add('hidden');
        filename.textContent = '';
    }

    function showError(message) {
        error.textContent = message;
        error.classList.remove('hidden');
    }

    input.addEventListener('change', () => {
        error.classList.add('hidden');
        clearPreview();

        const file = input.files[0];

        if (!file) return;

        if (!allowedTypes.includes(file.type)) {
            input.value = '';
            showError('Format gambar harus JPG, PNG, atau WebP.');
            return;
        }

        if (file.size > maxSize) {
            input.value = '';
            showError('Ukuran gambar maksimal 2 MB.');
            return;
        }

        previewUrl = URL.createObjectURL(file);

        preview.src = previewUrl;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
        removeButton.classList.remove('hidden');

        filename.textContent = file.name;
    });

    removeButton.addEventListener('click', () => {
        input.value = '';
        error.classList.add('hidden');
        clearPreview();
    });
});
</script>
