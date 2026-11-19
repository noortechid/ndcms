<?php
$success = $_SESSION['flash_success'] ?? null;
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="space-y-3">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Employee Management</h1>
            <p class="text-sm text-text-secondary mt-1">
                Kelola data karyawan ND Coffee.
            </p>
        </div>

        <?php if (\App\Core\Auth::can('employees.manage')): ?>
            <a href="/employees/create"
               class="bg-primary hover:bg-primary-dark text-white px-5 py-3 rounded-xl text-sm font-semibold text-center">
                + Tambah Karyawan
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

    <div class="bg-white border border-border rounded-2xl p-5">
        <form method="GET" action="/employees" class="flex gap-3">
            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($keyword) ?>"
                placeholder="Cari nama, kode, atau jabatan..."
                class="flex-1 min-w-0 border border-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
            >

            <button class="bg-primary text-white px-5 rounded-xl text-sm font-semibold">
                Cari
            </button>
        </form>
    </div>

    <div class="bg-white border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-background text-text-secondary">
                    <tr>
                        <th class="px-6 py-4">Karyawan</th>
                        <th class="px-6 py-4">Kode</th>
                        <th class="px-6 py-4">Jabatan</th>
                        <th class="px-6 py-4">Tanggal Masuk</th>
                        <th class="px-6 py-4">Status</th>
                        <?php if (\App\Core\Auth::can('employees.manage')): ?>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>

                <tbody class="divide-y divide-border">
                    <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-text-secondary">
                                Belum ada data karyawan.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($employees as $employee): ?>
                        <tr class="hover:bg-background/70">
                            <td class="px-6 py-4">
                                <div class="font-semibold">
                                    <?= htmlspecialchars($employee['name']) ?>
                                </div>
                                <div class="text-xs text-text-secondary mt-1">
                                    <?= htmlspecialchars($employee['email'] ?? '-') ?>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <?= htmlspecialchars($employee['employee_code']) ?>
                            </td>

                            <td class="px-6 py-4">
                                <?= htmlspecialchars($employee['position']) ?>
                            </td>

                            <td class="px-6 py-4">
                                <?= $employee['hire_date']
                                    ? date('d M Y', strtotime($employee['hire_date']))
                                    : '-' ?>
                            </td>

                            <td class="px-6 py-4">
                                <?php if ($employee['employment_status'] === 'active'): ?>
                                    <span class="bg-green-50 text-green-700 px-3 py-1 rounded-full text-xs">
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs">
                                        Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>

                            <?php if (\App\Core\Auth::can('employees.manage')): ?>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end items-center gap-3">
                                        <a href="/employees/edit?id=<?= (int) $employee['id'] ?>"
                                           class="text-primary-dark font-semibold">
                                            Edit
                                        </a>

                                        <form method="POST" action="/employees/status">
                                            <input type="hidden" name="_token"
                                                   value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="id"
                                                   value="<?= (int) $employee['id'] ?>">

                                            <button class="text-xs font-semibold text-text-secondary"
                                                onclick="return confirm('Ubah status karyawan ini?')">
                                                <?= $employee['employment_status'] === 'active'
                                                    ? 'Nonaktifkan'
                                                    : 'Aktifkan' ?>
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
