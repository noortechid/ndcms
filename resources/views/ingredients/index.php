<?php
use App\Core\Auth;
?>

<div class="space-y-3">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-stone-800">Bahan Baku</h1>
            <p class="text-sm text-stone-500 mt-1">
                Kelola bahan baku dan biaya dasar produksi.
            </p>
        </div>

        <a href="/ingredients/create"
           class="bg-primary text-white px-5 py-3 rounded-xl text-sm font-semibold">
            + Tambah Bahan
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

    <div class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
        <form method="GET" action="/ingredients"
              class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($filters['search']) ?>"
                placeholder="Cari nama atau kode bahan..."
                class="rounded-xl border border-stone-200 px-4 py-3 text-sm focus:border-pink-400 focus:outline-none"
            >

            <select name="status"
                    class="rounded-xl border border-stone-200 px-4 py-3 text-sm focus:border-pink-400 focus:outline-none">
                <option value="">Semua Status</option>
                <option value="1" <?= $filters['status'] === '1' ? 'selected' : '' ?>>
                    Aktif
                </option>
                <option value="0" <?= $filters['status'] === '0' ? 'selected' : '' ?>>
                    Nonaktif
                </option>
            </select>

            <button type="submit"
                    class="rounded-xl bg-stone-800 px-4 py-3 text-sm font-medium text-white hover:bg-stone-700">
                Terapkan Filter
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-pink-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-pink-50 text-stone-600">
                    <tr>
                        <th class="px-5 py-4 font-medium">Bahan Baku</th>
                        <th class="px-5 py-4 font-medium">Kode</th>
                        <th class="px-5 py-4 font-medium">Satuan</th>
                        <th class="px-5 py-4 font-medium">Biaya / Satuan</th>
                        <th class="px-5 py-4 font-medium">Status</th>
                        <th class="px-5 py-4 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php if (empty($ingredients)): ?>
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-stone-400">
                                Belum ada data bahan baku.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ingredients as $ingredient): ?>
                            <tr class="hover:bg-pink-50/40">
                                <td class="px-5 py-4">
                                    <p class="font-medium text-stone-800">
                                        <?= htmlspecialchars($ingredient['name']) ?>
                                    </p>
                                    <p class="mt-1 text-xs text-stone-400">
                                        <?= htmlspecialchars($ingredient['description'] ?? '') ?>
                                    </p>
                                </td>
                                <td class="px-5 py-4 text-stone-500">
                                    <?= htmlspecialchars($ingredient['ingredient_code'] ?: '-') ?>
                                </td>
                                <td class="px-5 py-4 text-stone-600">
                                    <?= htmlspecialchars($ingredient['unit']) ?>
                                </td>
                                <td class="px-5 py-4 font-medium text-stone-700">
                                    Rp <?= number_format((float) $ingredient['cost_per_unit'], 2, ',', '.') ?>
                                </td>
                                <td class="px-5 py-4">
                                    <?php if ($ingredient['is_active']): ?>
                                        <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">
                                            Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-medium text-stone-500">
                                            Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4">
                                <div class="flex flex-col items-start gap-2">
                                        <a href="/ingredients/edit?id=<?= (int) $ingredient['id'] ?>"
                                       class="text-primary-dark font-semibold">
                                            Edit
                                        </a>

                                        <form method="POST" action="/ingredients/status"
                                              onsubmit="return confirm('Ubah status bahan baku ini?')">
                                            <input type="hidden" name="_token"
                                                   value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="id"
                                                   value="<?= (int) $ingredient['id'] ?>">
                                            <input type="hidden" name="is_active"
                                                   value="<?= $ingredient['is_active'] ? '0' : '1' ?>">
                                            <button class="text-xs text-text-secondary font-semibold">
                                                <?= $ingredient['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
