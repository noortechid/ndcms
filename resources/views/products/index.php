<?php
use App\Core\Auth;
$success = $_SESSION['flash_success'] ?? null;
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="space-y-3">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Products</h1>
            <p class="text-sm text-text-secondary mt-1">Kelola menu dan harga jual ND Coffee.</p>
        </div>
<?php if (Auth::can('products.manage')): ?>
        <a href="/products/create"
           class="bg-primary text-white px-5 py-3 rounded-xl text-sm font-semibold">
            + Tambah Produk
        </a>
<?php endif; ?>
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

    <form method="GET" action="/products"
          class="bg-white border border-border rounded-2xl p-4 flex flex-wrap gap-3">
        <input type="text" name="search"
               value="<?= htmlspecialchars($filters['search']) ?>"
               placeholder="Cari nama atau kode produk..."
               class="flex-1 min-w-48 border border-border rounded-xl p-3 text-sm">

        <select name="category_id"
                class="border border-border rounded-xl p-3 text-sm">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>"
                    <?= (string) $filters['category_id'] === (string) $category['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status" class="border border-border rounded-xl p-3 text-sm">
            <option value="">Semua Status</option>
            <option value="1" <?= $filters['status'] === '1' ? 'selected' : '' ?>>
                Aktif
            </option>
            <option value="0" <?= $filters['status'] === '0' ? 'selected' : '' ?>>
                Nonaktif
            </option>
        </select>

        <button class="bg-primary text-white px-5 py-3 rounded-xl text-sm font-semibold">
            Filter
        </button>
        <a href="/products"
           class="border border-border px-5 py-3 rounded-xl text-sm font-semibold">
            Reset
        </a>
    </form>

    <div class="bg-white border border-border rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-border">
            <h2 class="font-bold">Daftar Produk</h2>
            <p class="text-xs text-text-secondary mt-1">
                <?= count($products) ?> produk ditemukan
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-background text-text-secondary">
                    <tr>
                        <th class="px-5 py-4">Produk</th>
                        <th class="px-5 py-4">Kode</th>
                        <th class="px-5 py-4">Kategori</th>
                        <th class="px-5 py-4">Harga</th>
                        <th class="px-5 py-4">Ketersediaan</th>
                        <th class="px-5 py-4">Status</th>
<?php if (Auth::can('products.manage')): ?>
                        <th class="px-5 py-4">Aksi</th>
<?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <?php if (!$products): ?>
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-text-secondary">
                                Produk tidak ditemukan.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td class="px-5 py-4 font-semibold">
                                <?= htmlspecialchars($product['name']) ?>
                            </td>
                            <td class="px-5 py-4 text-text-secondary">
                                <?= htmlspecialchars($product['product_code'] ?: '-') ?>
                            </td>
                            <td class="px-5 py-4">
                                <?= htmlspecialchars($product['category_name']) ?>
                            </td>
                            <td class="px-5 py-4 font-semibold">
                                Rp <?= number_format((float) $product['price'], 0, ',', '.') ?>
                            </td>
                            <td class="px-5 py-4">
                                <?= $product['is_available'] ? 'Tersedia' : 'Habis' ?>
                            </td>
                            <td class="px-5 py-4">
                                <?php if ($product['is_active']): ?>
                                    <span class="bg-green-50 text-green-700 px-3 py-1 rounded-full text-xs">
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs">
                                        Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>
<?php if (Auth::can('products.manage')): ?>
                            <td class="px-5 py-4">
                                <div class="flex flex-col items-start gap-2">
                                    <a href="/products/edit?id=<?= (int) $product['id'] ?>"
                                       class="text-primary-dark font-semibold">
                                        Edit
                                    </a>

                                    <form method="POST" action="/products/availability">
                                        <input type="hidden" name="_token"
                                               value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="id"
                                               value="<?= (int) $product['id'] ?>">
                                        <input type="hidden" name="is_available"
                                               value="<?= $product['is_available'] ? 0 : 1 ?>">
                                        <button class="text-xs text-text-secondary font-semibold">
                                            <?= $product['is_available'] ? 'Tandai Habis' : 'Tandai Tersedia' ?>
                                        </button>
                                    </form>

                                    <form method="POST" action="/products/status">
                                        <input type="hidden" name="_token"
                                               value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="id"
                                               value="<?= (int) $product['id'] ?>">
                                        <input type="hidden" name="is_active"
                                               value="<?= $product['is_active'] ? 0 : 1 ?>">
                                        <button class="text-xs text-text-secondary font-semibold"
                                                onclick="return confirm('Ubah status produk ini?')">
                                            <?= $product['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
<?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
