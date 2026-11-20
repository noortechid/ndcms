<?php
$currentImage = $product['image_path'] ?? null;

if ($currentImage && !str_starts_with($currentImage, '/')) {
    $currentImage = '/' . $currentImage;
}
?>

<div class="max-w-3xl space-y-3">

    <!-- Page Header -->
    <div>
        <a href="/products"
           class="inline-flex items-center gap-2 text-sm font-medium text-primary-dark hover:text-primary">
            <span aria-hidden="true">←</span>
            Kembali ke Produk
        </a>

        <div class="mt-4">
            <h1 class="text-2xl font-bold text-text-primary">
                Edit Produk
            </h1>
            <p class="mt-1 text-sm text-text-secondary">
                Perbarui informasi dan gambar menu ND Coffee.
            </p>
        </div>
    </div>

    <!-- Error Message -->
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Product Form -->
    <form method="POST"
          action="/products/update"
          enctype="multipart/form-data"
          class="space-y-6">

        <input type="hidden"
               name="_token"
               value="<?= htmlspecialchars($csrfToken) ?>">

        <input type="hidden"
               name="id"
               value="<?= (int) $product['id'] ?>">

        <!-- Product Image -->
        <section class="rounded-2xl border border-border bg-white p-6 space-y-4">
            <div>
                <h2 class="text-base font-semibold text-text-primary">
                    Gambar Produk
                </h2>
                <p class="mt-1 text-sm text-text-secondary">
                    Ganti gambar produk jika diperlukan.
                </p>
            </div>

            <div class="flex flex-col gap-5 sm:flex-row sm:items-start">

                <!-- Image Preview -->
                <div class="relative flex h-40 w-40 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-border bg-background">

                    <div id="image-placeholder"
                         class="<?= $currentImage ? 'hidden' : 'flex' ?> h-full w-full items-center justify-center text-4xl text-primary">
                        ☕
                    </div>

                    <img id="image-preview"
                         src="<?= htmlspecialchars($currentImage ?? '') ?>"
                         alt="Gambar <?= htmlspecialchars($product['name']) ?>"
                         class="<?= $currentImage ? 'block' : 'hidden' ?> h-full w-full object-cover"
                         onerror="this.classList.add('hidden'); document.getElementById('image-placeholder').classList.remove('hidden'); document.getElementById('image-placeholder').classList.add('flex');">
                </div>

                <!-- Upload Controls -->
                <div class="min-w-0 flex-1 space-y-3">

                    <div>
                        <label for="image"
                               class="text-sm font-semibold text-text-primary">
                            Unggah Gambar Baru
                        </label>

                        <p class="mt-1 text-xs text-text-secondary">
                            Format JPG, PNG, atau WebP. Maksimal 2 MB.
                        </p>
                    </div>

                    <input type="file"
                           name="image"
                           id="image"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                           class="block w-full text-sm text-text-secondary
                                  file:mr-4 file:rounded-lg file:border-0
                                  file:bg-secondary file:px-4 file:py-2
                                  file:text-sm file:font-semibold
                                  file:text-primary-dark hover:file:bg-background">

                    <p id="image-filename"
                       class="hidden break-all text-xs text-text-secondary">
                    </p>

                    <p id="image-error"
                       class="hidden text-xs text-red-600">
                    </p>

                    <button type="button"
                            id="remove-image"
                            class="hidden text-xs font-semibold text-red-600 hover:text-red-700">
                        Batalkan gambar baru
                    </button>

                    <p class="text-xs text-text-secondary">
                        Jika tidak memilih gambar baru, gambar yang sekarang
                        akan tetap digunakan.
                    </p>
                </div>
            </div>
        </section>

        <!-- Basic Information -->
        <section class="rounded-2xl border border-border bg-white p-6 space-y-5">
            <div>
                <h2 class="text-base font-semibold text-text-primary">
                    Informasi Produk
                </h2>
                <p class="mt-1 text-sm text-text-secondary">
                    Informasi dasar menu yang dijual.
                </p>
            </div>

            <div>
                <label for="name" class="text-sm font-medium text-text-primary">
                    Nama Produk *
                </label>

                <input type="text"
                       id="name"
                       name="name"
                       required
                       maxlength="150"
                       value="<?= htmlspecialchars($product['name']) ?>"
                       class="mt-2 w-full rounded-xl border border-border p-3 text-sm text-text-primary focus:border-primary focus:outline-none">
            </div>

            <div>
                <label for="product_code" class="text-sm font-medium text-text-primary">
                    Kode Produk
                </label>

                <input type="text"
                       id="product_code"
                       name="product_code"
                       maxlength="30"
                       value="<?= htmlspecialchars($product['product_code'] ?? '') ?>"
                       class="mt-2 w-full rounded-xl border border-border p-3 text-sm text-text-primary focus:border-primary focus:outline-none">
            </div>

            <div>
                <label for="category_id" class="text-sm font-medium text-text-primary">
                    Kategori *
                </label>

                <select id="category_id"
                        name="category_id"
                        required
                        class="mt-2 w-full rounded-xl border border-border p-3 text-sm text-text-primary focus:border-primary focus:outline-none">

                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"
                            <?= (int) $product['category_id'] === (int) $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div>
                <label for="price" class="text-sm font-medium text-text-primary">
                    Harga Jual (Rp) *
                </label>

                <input type="number"
                       id="price"
                       name="price"
                       min="0"
                       step="0.01"
                       required
                       value="<?= htmlspecialchars((string) $product['price']) ?>"
                       class="mt-2 w-full rounded-xl border border-border p-3 text-sm text-text-primary focus:border-primary focus:outline-none">
            </div>

            <div>
                <label for="description" class="text-sm font-medium text-text-primary">
                    Deskripsi
                </label>

                <textarea id="description"
                          name="description"
                          rows="4"
                          maxlength="5000"
                          class="mt-2 w-full rounded-xl border border-border p-3 text-sm text-text-primary focus:border-primary focus:outline-none"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>
        </section>

        <!-- Product Settings -->
        <section class="rounded-2xl border border-border bg-white p-6 space-y-5">
            <div>
                <h2 class="text-base font-semibold text-text-primary">
                    Pengaturan Produk
                </h2>
                <p class="mt-1 text-sm text-text-secondary">
                    Atur ketersediaan dan pengelolaan stok produk.
                </p>
            </div>

            <div>
                <label for="is_available" class="text-sm font-medium text-text-primary">
                    Ketersediaan
                </label>

                <select id="is_available"
                        name="is_available"
                        class="mt-2 w-full rounded-xl border border-border p-3 text-sm text-text-primary focus:border-primary focus:outline-none">

                    <option value="1" <?= (int) $product['is_available'] === 1 ? 'selected' : '' ?>>
                        Tersedia
                    </option>

                    <option value="0" <?= (int) $product['is_available'] === 0 ? 'selected' : '' ?>>
                        Habis
                    </option>
                </select>
            </div>

            <div>
                <label for="requires_recipe" class="text-sm font-medium text-text-primary">
                    Pengelolaan Resep *
                </label>

                <select id="requires_recipe"
                        name="requires_recipe"
                        required
                        class="mt-2 w-full rounded-xl border border-border p-3 text-sm text-text-primary focus:border-primary focus:outline-none">

                    <option value="1"
                        <?= (int) $product['requires_recipe'] === 1 ? 'selected' : '' ?>>
                        Produk Racikan
                    </option>

                    <option value="0"
                        <?= (int) $product['requires_recipe'] === 0 ? 'selected' : '' ?>>
                        Produk Non-racikan
                    </option>
                </select>

                <p class="mt-2 text-xs leading-relaxed text-text-secondary">
                    Produk racikan wajib memiliki resep agar stok bahan baku
                    otomatis berkurang saat transaksi.
                </p>
            </div>
        </section>

        <!-- Actions -->
        <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">
            <a href="/products"
               class="rounded-xl border border-border px-5 py-3 text-center text-sm font-semibold text-text-primary hover:bg-background">
                Batal
            </a>

            <button type="submit"
                    class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark">
                Simpan Perubahan
            </button>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    const imagePlaceholder = document.getElementById('image-placeholder');
    const filename = document.getElementById('image-filename');
    const error = document.getElementById('image-error');
    const removeButton = document.getElementById('remove-image');

    const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    const maxSize = 2 * 1024 * 1024;

    let previewUrl = null;

    const clearPreviewUrl = () => {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }
    };

    const showError = (message) => {
        error.textContent = message;
        error.classList.remove('hidden');
    };

    const clearError = () => {
        error.textContent = '';
        error.classList.add('hidden');
    };

    imageInput.addEventListener('change', () => {
        clearError();

        const file = imageInput.files[0];

        if (!file) return;

        if (!allowedTypes.includes(file.type)) {
            imageInput.value = '';
            showError('Format gambar harus JPG, PNG, atau WebP.');
            return;
        }

        if (file.size > maxSize) {
            imageInput.value = '';
            showError('Ukuran gambar maksimal 2 MB.');
            return;
        }

        clearPreviewUrl();

        previewUrl = URL.createObjectURL(file);

        imagePreview.src = previewUrl;
        imagePreview.classList.remove('hidden');

        imagePlaceholder.classList.add('hidden');
        imagePlaceholder.classList.remove('flex');

        filename.textContent = file.name;
        filename.classList.remove('hidden');

        removeButton.classList.remove('hidden');
    });

    removeButton.addEventListener('click', () => {
        imageInput.value = '';

        clearPreviewUrl();
        clearError();

        filename.textContent = '';
        filename.classList.add('hidden');

        removeButton.classList.add('hidden');

        // Kembalikan gambar awal dari server.
        const originalImage = <?= json_encode($currentImage) ?>;

        if (originalImage) {
            imagePreview.src = originalImage;
            imagePreview.classList.remove('hidden');

            imagePlaceholder.classList.add('hidden');
            imagePlaceholder.classList.remove('flex');
        } else {
            imagePreview.removeAttribute('src');
            imagePreview.classList.add('hidden');

            imagePlaceholder.classList.remove('hidden');
            imagePlaceholder.classList.add('flex');
        }
    });

    window.addEventListener('beforeunload', clearPreviewUrl);
});
</script>
