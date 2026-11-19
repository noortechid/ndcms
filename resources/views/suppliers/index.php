<?php

use App\Core\Auth;
use App\Core\Csrf;

$canManage = Auth::can('purchasing');

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;

unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$activeCount = count(array_filter(
    $suppliers,
    fn ($supplier) => (int) $supplier['is_active'] === 1
));

$inactiveCount = count($suppliers) - $activeCount;
?>

<div class="space-y-3">

    <!-- Page Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-800">
                Supplier Management
            </h1>

            <p class="mt-1 text-sm text-stone-500">
                Kelola data pemasok bahan baku ND Coffee.
            </p>
        </div>

        <?php if ($canManage): ?>
            <a
                href="/suppliers/create"
                class="bg-primary text-white px-5 py-3 rounded-xl text-sm font-semibold">
                <span class="text-lg leading-none">+</span>
                Tambah Supplier
            </a>
        <?php endif; ?>
    </div>

    <!-- Flash Messages -->
    <?php if ($flashSuccess): ?>
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            <?= htmlspecialchars($flashSuccess) ?>
        </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <?= htmlspecialchars($flashError) ?>
        </div>
    <?php endif; ?>

    <!-- Summary -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        <div class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-stone-500">
                Total Supplier
            </p>

            <p class="mt-2 text-3xl font-semibold text-stone-800">
                <?= number_format(count($suppliers)) ?>
            </p>

            <p class="mt-1 text-xs text-stone-400">
                Seluruh supplier terdaftar
            </p>
        </div>

        <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-stone-500">
                Supplier Aktif
            </p>

            <p class="mt-2 text-3xl font-semibold text-stone-800">
                <?= number_format($activeCount) ?>
            </p>

            <p class="mt-1 text-xs text-stone-400">
                Dapat digunakan dalam pembelian
            </p>
        </div>

        <div class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-stone-500">
                Supplier Nonaktif
            </p>

            <p class="mt-2 text-3xl font-semibold text-stone-800">
                <?= number_format($inactiveCount) ?>
            </p>

            <p class="mt-1 text-xs text-stone-400">
                Tidak dapat digunakan dalam pembelian
            </p>
        </div>

    </div>

    <!-- Supplier Table -->
    <section class="overflow-hidden rounded-2xl border border-pink-100 bg-white shadow-sm">

        <div class="border-b border-stone-100 px-5 py-4">
            <h2 class="font-semibold text-stone-800">
                Daftar Supplier
            </h2>

            <p class="mt-1 text-sm text-stone-500">
                Informasi pemasok bahan baku ND Coffee.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">

                <thead class="bg-pink-50/60 text-xs uppercase tracking-wide text-stone-500">
                    <tr>
                        <th class="px-5 py-3">Supplier</th>
                        <th class="px-5 py-3">Kontak</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3">Status</th>

                        <?php if ($canManage): ?>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-100">

                    <?php if (empty($suppliers)): ?>
                        <tr>
                            <td
                                colspan="<?= $canManage ? 5 : 4 ?>"
                                class="px-5 py-12 text-center">

                                <div class="mx-auto max-w-sm">
                                    <div class="text-3xl text-stone-300">
                                        ♧
                                    </div>

                                    <p class="mt-3 font-medium text-stone-700">
                                        Belum ada supplier
                                    </p>

                                    <p class="mt-1 text-sm text-stone-400">
                                        Data pemasok bahan baku belum tersedia.
                                    </p>

                                    <?php if ($canManage): ?>
                                        <a
                                            href="/suppliers/create"
                                            class="mt-4 inline-block text-sm font-medium text-pink-600 hover:text-pink-700">
                                            Tambahkan supplier pertama
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($suppliers as $supplier): ?>
                        <?php $isActive = (int) $supplier['is_active'] === 1; ?>

                        <tr class="transition hover:bg-pink-50/30">

                            <td class="px-5 py-4">
                                <p class="font-medium text-stone-800">
                                    <?= htmlspecialchars($supplier['name']) ?>
                                </p>

                                <p class="mt-1 text-xs text-stone-400">
                                    Kode:
                                    <?= htmlspecialchars($supplier['supplier_code'] ?: '-') ?>
                                </p>
                            </td>

                            <td class="px-5 py-4">
                                <p class="text-stone-700">
                                    <?= htmlspecialchars($supplier['contact_person'] ?: '-') ?>
                                </p>

                                <p class="mt-1 text-xs text-stone-400">
                                    <?= htmlspecialchars($supplier['phone'] ?: '-') ?>
                                </p>
                            </td>

                            <td class="px-5 py-4 text-stone-500">
                                <?= htmlspecialchars($supplier['email'] ?: '-') ?>
                            </td>

                            <td class="px-5 py-4">
                                <?php if ($isActive): ?>
                                    <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-medium text-stone-600">
                                        Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>

                            <?php if ($canManage): ?>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">

                                        <a
                                            href="/suppliers/edit?id=<?= (int) $supplier['id'] ?>"
                                            class="rounded-lg border border-stone-200 px-3 py-2 text-xs font-medium text-stone-600 transition hover:bg-stone-50">
                                            Edit
                                        </a>

                                        <form
                                            action="/suppliers/status"
                                            method="POST"
                                            onsubmit="return confirm('<?= $isActive ? 'Nonaktifkan' : 'Aktifkan' ?> supplier ini?')">

                                            <input
                                                type="hidden"
                                                name="_token"
                                                value="<?= htmlspecialchars(Csrf::token()) ?>">

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $supplier['id'] ?>">

                                            <input
                                                type="hidden"
                                                name="is_active"
                                                value="<?= $isActive ? '0' : '1' ?>">

                                            <button
                                                type="submit"
                                                class="rounded-lg px-3 py-2 text-xs font-medium transition <?= $isActive
                                                    ? 'bg-red-50 text-red-600 hover:bg-red-100'
                                                    : 'bg-green-50 text-green-700 hover:bg-green-100' ?>">

                                                <?= $isActive ? 'Nonaktifkan' : 'Aktifkan' ?>

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

    </section>

</div>
