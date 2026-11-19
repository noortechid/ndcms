<div class="max-w-2xl space-y-6">
    <div>
        <a href="/categories" class="text-sm text-primary-dark font-medium">
            ← Kembali ke Kategori
        </a>
        <h1 class="text-2xl font-bold mt-3">Edit Kategori</h1>
        <p class="text-sm text-text-secondary mt-1">
            Perbarui informasi kategori.
        </p>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="bg-red-50 text-red-700 p-4 rounded-xl text-sm">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form method="POST" action="/categories/update"
          class="bg-white border border-border rounded-2xl p-6 space-y-5">
        <input type="hidden" name="_token"
               value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">

        <div>
            <label class="text-sm font-medium">Nama Kategori *</label>
            <input type="text" name="name" required maxlength="100"
                   value="<?= htmlspecialchars($category['name']) ?>"
                   class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
        </div>

        <div>
            <label class="text-sm font-medium">Deskripsi</label>
            <textarea name="description" rows="4" maxlength="5000"
                      class="mt-2 w-full border border-border rounded-xl p-3 text-sm"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="/categories"
               class="px-5 py-3 rounded-xl border border-border text-sm font-semibold">
                Batal
            </a>
            <button class="bg-primary text-white px-5 py-3 rounded-xl text-sm font-semibold">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
