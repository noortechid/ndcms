<div class="space-y-3">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-stone-800">Recipe & HPP</h1>
            <p class="mt-1 text-sm text-stone-500">
                Kelola komposisi resep dan biaya produksi produk.
            </p>
        </div>

        <a href="/recipes/create"
           class="bg-primary text-white px-5 py-3 rounded-xl text-sm font-semibold">
            + Tambah Resep
        </a>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="rounded-xl bg-green-50 px-4 py-3 text-sm text-green-700">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-stone-500">Total Resep</p>
            <p class="mt-2 text-2xl font-semibold text-stone-800">
                <?= count($recipes) ?>
            </p>
        </div>

        <div class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-stone-500">Total Produk Terdaftar</p>
            <p class="mt-2 text-2xl font-semibold text-stone-800">
                <?= count($recipes) ?>
            </p>
        </div>

        <div class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-stone-500">Rata-rata HPP</p>
            <p class="mt-2 text-2xl font-semibold text-stone-800">
                <?php
                $averageCost = count($recipes) > 0
                    ? array_sum(array_column($recipes, 'total_cost')) / count($recipes)
                    : 0;
                ?>
                Rp <?= number_format($averageCost, 0, ',', '.') ?>
            </p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-pink-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-pink-50 text-stone-600">
                    <tr>
                        <th class="px-5 py-4 font-medium">Produk</th>
                        <th class="px-5 py-4 font-medium">Jumlah Bahan</th>
                        <th class="px-5 py-4 font-medium">HPP</th>
                        <th class="px-5 py-4 font-medium">Harga Jual</th>
                        <th class="px-5 py-4 font-medium">Laba Kotor</th>
                        <th class="px-5 py-4 text-right font-medium">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-100">
                    <?php if (empty($recipes)): ?>
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-stone-400">
                                Belum ada resep. Silakan tambahkan resep produk.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recipes as $recipe): ?>
                            <?php
                            $profit = (float) $recipe['selling_price']
                                - (float) $recipe['total_cost'];
                            ?>
                            <tr class="hover:bg-pink-50/40">
                                <td class="px-5 py-4 font-medium text-stone-800">
                                    <?= htmlspecialchars($recipe['product_name']) ?>
                                </td>
                                <td class="px-5 py-4 text-stone-600">
                                    <?= (int) $recipe['ingredient_count'] ?> bahan
                                </td>
                                <td class="px-5 py-4 text-stone-700">
                                    Rp <?= number_format((float) $recipe['total_cost'], 0, ',', '.') ?>
                                </td>
                                <td class="px-5 py-4 text-stone-700">
                                    Rp <?= number_format((float) $recipe['selling_price'], 0, ',', '.') ?>
                                </td>
                                <td class="px-5 py-4 font-medium <?= $profit >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                                    Rp <?= number_format($profit, 0, ',', '.') ?>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="/recipes/edit?product_id=<?= (int) $recipe['product_id'] ?>"
                                       class="text-primary-dark font-semibold">
                                        Edit Resep
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
