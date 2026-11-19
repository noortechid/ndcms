<?php
$success = $_SESSION['flash_success'] ?? null;
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="space-y-3">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Categories</h1>
            <p class="text-sm text-text-secondary mt-1">Kelola kategori menu ND Coffee.</p>
        </div>
        <a href="/categories/create"
           class="bg-primary text-white px-5 py-3 rounded-xl text-sm font-semibold">
            + Tambah Kategori
        </a>
    </div>

    <?php if ($success): ?>
        <div class="bg-green-50 text-green-700 p-4 rounded-xl text-sm">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-700 p-4 rounded-xl text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white border border-border rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-border">
            <h2 class="font-bold">Daftar Kategori</h2>
            <p class="text-xs text-text-secondary mt-1">
                <?= count($categories) ?> kategori terdaftar
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-background text-text-secondary">
                    <tr>
                        <th class="px-5 py-4">Nama Kategori</th>
                        <th class="px-5 py-4">Deskripsi</th>
                        <th class="px-5 py-4">Jumlah Produk</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <?php if (!$categories): ?>
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-text-secondary">
                                Belum ada kategori.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td class="px-5 py-4 font-semibold">
                                <?= htmlspecialchars($category['name']) ?>
                            </td>
                            <td class="px-5 py-4 text-text-secondary">
                                <?= htmlspecialchars($category['description'] ?? '-') ?>
                            </td>
                            <td class="px-5 py-4">
                                <?= (int) $category['product_count'] ?> produk
                            </td>
                            <td class="px-5 py-4">
                                <?php if ($category['is_active']): ?>
                                    <span class="bg-green-50 text-green-700 px-3 py-1 rounded-full text-xs">
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs">
                                        Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <a href="/categories/edit?id=<?= (int) $category['id'] ?>"
                                       class="text-primary-dark font-semibold">
                                        Edit
                                    </a>

                                    <form method="POST" action="/categories/status">
                                        <input type="hidden" name="_token"
                                               value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="id"
                                               value="<?= (int) $category['id'] ?>">
                                        <input type="hidden" name="is_active"
                                               value="<?= $category['is_active'] ? 0 : 1 ?>">

                                        <button class="text-sm font-semibold text-text-secondary"
                                                onclick="return confirm('Ubah status kategori ini?')">
                                            <?= $category['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
