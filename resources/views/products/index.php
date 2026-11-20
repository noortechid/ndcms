<?php
use App\Core\Auth;

$success = $_SESSION['flash_success'] ?? null;
$error = $_SESSION['flash_error'] ?? null;

unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$canManage = Auth::can('products.manage');
?>

<div class="space-y-3">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">Products</h1>
            <p class="text-sm text-text-secondary mt-1">
                Kelola menu, gambar, dan harga jual ND Coffee.
            </p>
        </div>

        <?php if ($canManage): ?>
            <a href="/products/create"
               class="inline-flex items-center justify-center gap-2 bg-primary text-white px-5 py-3 rounded-xl text-sm font-semibold hover:bg-primary-dark transition">
                <span class="text-lg leading-none">+</span>
                Tambah Produk
            </a>
        <?php endif; ?>
    </div>

    <!-- Flash Messages -->
    <?php if ($success): ?>
        <div class="bg-green-50 border border-green-100 text-green-700 p-4 rounded-xl text-sm">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-100 text-red-700 p-4 rounded-xl text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <form method="GET" action="/products"
          class="bg-white border border-border rounded-2xl p-5">

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            <div class="xl:col-span-1">
                <label class="block text-sm font-medium text-text-primary mb-2">
                    Cari Produk
                </label>
                <input type="text"
                       name="search"
                       value="<?= htmlspecialchars($filters['search']) ?>"
                       placeholder="Nama atau kode produk..."
                       class="w-full border border-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>

            <div>
                <label class="block text-sm font-medium text-text-primary mb-2">
                    Kategori
                </label>
                <select name="category_id"
                        class="w-full border border-border rounded-xl px-4 py-3 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="">Semua Kategori</option>

                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"
                            <?= (string) $filters['category_id'] === (string) $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-text-primary mb-2">
                    Status
                </label>
                <select name="status"
                        class="w-full border border-border rounded-xl px-4 py-3 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="">Semua Status</option>
                    <option value="1" <?= $filters['status'] === '1' ? 'selected' : '' ?>>
                        Aktif
                    </option>
                    <option value="0" <?= $filters['status'] === '0' ? 'selected' : '' ?>>
                        Nonaktif
                    </option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 bg-primary text-white px-4 py-3 rounded-xl text-sm font-semibold hover:bg-primary-dark transition">
                    Filter
                </button>

                <a href="/products"
                   class="px-4 py-3 border border-border rounded-xl text-sm font-semibold text-text-secondary hover:bg-background transition">
                    Reset
                </a>
            </div>

        </div>
    </form>

    <!-- Product Table -->
    <div class="bg-white border border-border rounded-2xl overflow-hidden">

        <div class="p-5 border-b border-border flex items-center justify-between gap-3">
            <div>
                <h2 class="font-bold text-text-primary">Daftar Produk</h2>
                <p class="text-sm text-text-secondary mt-1">
                    <?= count($products) ?> produk ditemukan
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">

                <thead class="bg-background text-text-secondary">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Produk</th>
                        <th class="px-5 py-4 font-semibold">Kode</th>
                        <th class="px-5 py-4 font-semibold">Kategori</th>
                        <th class="px-5 py-4 font-semibold">Harga</th>
                        <th class="px-5 py-4 font-semibold">Ketersediaan</th>
                        <th class="px-5 py-4 font-semibold">Status</th>

                        <?php if ($canManage): ?>
                            <th class="px-5 py-4 font-semibold">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>

                <tbody class="divide-y divide-border">

                    <?php if (!$products): ?>
                        <tr>
                            <td colspan="<?= $canManage ? 7 : 6 ?>"
                                class="px-5 py-12 text-center text-text-secondary">
                                Belum ada produk yang ditemukan.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($products as $product): ?>

                        <?php
                        $imagePath = trim((string) ($product['image_path'] ?? ''));

                        if ($imagePath !== '' && !str_starts_with($imagePath, '/')) {
                            $imagePath = '/' . $imagePath;
                        }
                        ?>

                        <tr class="hover:bg-background/50 transition-colors">

                            <!-- Product Image & Name -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3 min-w-[220px]">

                                    <div class="w-14 h-14 shrink-0 rounded-xl overflow-hidden bg-[#F8EEE9] border border-border flex items-center justify-center">

                                        <?php if ($imagePath !== ''): ?>
                                            <img
                                                src="<?= htmlspecialchars($imagePath) ?>"
                                                alt="<?= htmlspecialchars($product['name']) ?>"
                                                loading="lazy"
                                                class="w-full h-full object-cover"
                                                onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden');"
                                            >
                                        <?php endif; ?>

                                        <div class="<?= $imagePath !== '' ? 'hidden' : '' ?> w-full h-full flex items-center justify-center text-2xl text-primary">
                                            ☕
                                        </div>

                                    </div>

                                    <div class="min-w-0">
                                        <p class="font-semibold text-text-primary">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </p>
                                        <p class="text-xs text-text-secondary mt-1">
                                            <?= !empty($product['requires_recipe']) ? 'Menggunakan resep' : 'Tanpa resep' ?>
                                        </p>
                                    </div>

                                </div>
                            </td>

                            <!-- Product Code -->
                            <td class="px-5 py-4 text-text-secondary whitespace-nowrap">
                                <?= htmlspecialchars($product['product_code'] ?: '-') ?>
                            </td>

                            <!-- Category -->
                            <td class="px-5 py-4 text-text-primary whitespace-nowrap">
                                <?= htmlspecialchars($product['category_name']) ?>
                            </td>

                            <!-- Price -->
                            <td class="px-5 py-4 font-semibold text-text-primary whitespace-nowrap tabular-nums">
                                Rp <?= number_format((float) $product['price'], 0, ',', '.') ?>
                            </td>

                            <!-- Availability -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <?php if ($product['is_available']): ?>
                                    <span class="inline-flex items-center gap-1.5 text-green-700 text-xs font-medium">
                                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                        Tersedia
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 text-red-600 text-xs font-medium">
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                        Habis
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <?php if ($product['is_active']): ?>
                                    <span class="bg-green-50 text-green-700 px-3 py-1.5 rounded-full text-xs font-medium">
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-xs font-medium">
                                        Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Actions -->
                            <?php if ($canManage): ?>
                                <td class="px-5 py-4">
                                    <div class="flex flex-col items-start gap-2">

                                        <a href="/products/edit?id=<?= (int) $product['id'] ?>"
                                           class="text-primary-dark font-semibold hover:underline">
                                            Edit Produk
                                        </a>

                                        <form method="POST" action="/products/availability">
                                            <input type="hidden" name="_token"
                                                   value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="id"
                                                   value="<?= (int) $product['id'] ?>">
                                            <input type="hidden" name="is_available"
                                                   value="<?= $product['is_available'] ? 0 : 1 ?>">

                                            <button
                                                    class="text-xs text-text-secondary font-medium hover:text-primary-dark">
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

                                            <button
                                                    class="text-xs text-text-secondary font-medium hover:text-primary-dark"
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
